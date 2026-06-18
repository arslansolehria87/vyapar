<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Item;
use App\Models\Party;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Transaction;
use App\Support\TransactionNumberPrefix;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $purchaseReturns = Purchase::with(['items', 'payments.bankAccount', 'party'])
            ->where('type', 'purchase_return')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('bill_number', 'like', '%' . $search . '%')
                        ->orWhere('party_name', 'like', '%' . $search . '%')
                        ->orWhereHas('party', function ($partyQuery) use ($search) {
                            $partyQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('bill_date')
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.purchases.purchase-return.index', compact('purchaseReturns', 'search'));
    }

    public function create()
    {
        return $this->renderPurchaseReturnForm();
    }

    public function edit(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $purchase->load(['items', 'payments']);

        return $this->renderPurchaseReturnForm($purchase);
    }

    public function duplicate(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $purchase->load(['items', 'payments']);

        return $this->renderPurchaseReturnForm(null, $purchase);
    }

    public function store(Request $request)
    {
        $data = $this->validatePurchaseReturn($request);
        $purchaseReturn = $this->savePurchaseReturn(new Purchase(), $data);
        $invoiceUrl = route('invoice', [
            'purchase_id' => $purchaseReturn->id,
            'type' => 'purchase-return',
        ]);

        return response()->json([
            'success' => true,
            'purchase_id' => $purchaseReturn->id,
            'bill_number' => $purchaseReturn->bill_number,
            'redirect_url' => $invoiceUrl,
            'share_url' => $invoiceUrl,
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $data = $this->validatePurchaseReturn($request);
        $purchaseReturn = $this->savePurchaseReturn($purchase, $data);
        $invoiceUrl = route('invoice', [
            'purchase_id' => $purchaseReturn->id,
            'type' => 'purchase-return',
        ]);

        return response()->json([
            'success' => true,
            'purchase_id' => $purchaseReturn->id,
            'bill_number' => $purchaseReturn->bill_number,
            'redirect_url' => $invoiceUrl,
            'share_url' => $invoiceUrl,
        ]);
    }

    public function storeInvoiceTheme(Request $request, Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);

        $data = $request->validate([
            'mode' => 'required|in:regular,thermal',
            'theme' => 'nullable|string|max:60',
            'regularThemeId' => 'nullable|integer|min:1',
            'thermalThemeId' => 'nullable|integer|min:1',
            'accent' => 'nullable|string|max:30',
            'accent2' => 'nullable|string|max:30',
        ]);

        $currentTheme = $purchase->invoice_theme;
        if (is_string($currentTheme)) {
            $currentTheme = json_decode($currentTheme, true);
        }
        $currentTheme = is_array($currentTheme) ? $currentTheme : [];

        $purchase->forceFill([
            'invoice_theme' => [
                'mode' => $data['mode'],
                'theme' => $data['theme'] ?? ($currentTheme['theme'] ?? null),
                'regularThemeId' => (int) ($data['regularThemeId'] ?? ($currentTheme['regularThemeId'] ?? 1)),
                'thermalThemeId' => (int) ($data['thermalThemeId'] ?? ($currentTheme['thermalThemeId'] ?? 1)),
                'accent' => $data['accent'] ?? '#1f4e79',
                'accent2' => $data['accent2'] ?? '#ff981f',
            ],
        ])->save();

        return response()->json([
            'success' => true,
            'invoice_theme' => $purchase->invoice_theme,
        ]);
    }

    public function destroy(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $purchase->load('payments');

        DB::transaction(function () use ($purchase) {
            $this->revertBankAdjustments($purchase);
            $purchase->items()->delete();
            $purchase->payments()->delete();
            $purchase->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Purchase return deleted successfully.',
        ]);
    }

    public function preview(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $purchase->load(['items', 'payments.bankAccount', 'party']);

        $themeDefaults = $this->resolveStoredPurchaseThemeConfig($purchase, request());
        $themeConfig = $this->resolvePurchaseReturnThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        return view('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $this->mapPurchaseReturnToThemePreviewData($purchase),
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
            'autoPrint' => request()->boolean('print'),
        ]);
    }

    public function print(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        request()->merge(['print' => true]);

        return $this->preview($purchase);
    }

    public function pdf(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $purchase->load(['items', 'payments.bankAccount', 'party']);

        $themeDefaults = $this->resolveStoredPurchaseThemeConfig($purchase, request());
        $fileName = 'purchase-return-' . ($purchase->bill_number ?: $purchase->id) . '.pdf';
        $reactAssets = $this->resolveReactInvoiceAssets();

        abort_unless($reactAssets['css_path'] && $reactAssets['js_path'], 500, 'React invoice assets not found for PDF generation.');

        $viewData = [
            'purchase' => $purchase,
            'invoicePreviewData' => $this->mapPurchaseReturnToThemePreviewData($purchase),
            'pageTitle' => 'Purchase Return PDF',
            'browserTabLabel' => 'Purchase Return #' . ($purchase->bill_number ?: $purchase->id),
            'saveCloseUrl' => route('purchase-return'),
            'themeSaveUrl' => route('purchase-return.invoice-theme.store', $purchase),
            'documentType' => 'purchase-return',
            'initialMode' => $themeDefaults['mode'],
            'initialRegularThemeId' => $themeDefaults['regularThemeId'],
            'initialThermalThemeId' => $themeDefaults['thermalThemeId'],
            'initialAccent' => $themeDefaults['accent'],
            'initialAccent2' => $themeDefaults['accent2'],
            'pdfDirectDownload' => true,
            'reactCssInline' => File::get($reactAssets['css_path']),
            'reactJsInline' => File::get($reactAssets['js_path']),
            'reactCss' => $reactAssets['css_url'],
            'reactJs' => $reactAssets['js_url'],
        ];

        $htmlDirectory = storage_path('app/purchase-return-pdf');
        File::ensureDirectoryExists($htmlDirectory);

        $htmlPath = $htmlDirectory . DIRECTORY_SEPARATOR . 'purchase-return-' . $purchase->id . '-' . uniqid() . '.html';
        $pdfPath = $htmlDirectory . DIRECTORY_SEPARATOR . 'purchase-return-' . $purchase->id . '-' . uniqid() . '.pdf';

        File::put($htmlPath, view('invoice.index', $viewData)->render());

        $chromePath = $this->resolveChromeExecutable();
        abort_unless($chromePath !== null, 500, 'Chrome/Edge executable not found for PDF generation.');

        $process = new Process([
            $chromePath,
            '--headless=new',
            '--disable-gpu',
            '--disable-extensions',
            '--disable-sync',
            '--no-pdf-header-footer',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=2500',
            '--print-to-pdf=' . $pdfPath,
            'file:///' . str_replace('\\', '/', $htmlPath),
        ]);

        $process->setTimeout(60);
        $process->run();

        File::delete($htmlPath);

        if (! $process->isSuccessful() || ! File::exists($pdfPath)) {
            File::delete($pdfPath);
            return view('invoice.index', array_merge($viewData, [
                'pdfDirectDownload' => false,
                'clientPdfAutoOpen' => true,
                'clientPdfFileName' => $fileName,
                'pdfGenerationError' => trim($process->getErrorOutput()),
            ]));
        }

        if (request()->boolean('download')) {
            return response()->download($pdfPath, $fileName)->deleteFileAfterSend(true);
        }

        return response()->file($pdfPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ])->deleteFileAfterSend(true);
    }

    public function email(Request $request, Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);

        $data = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        $purchase->load(['items', 'payments.bankAccount', 'party']);
        $themeDefaults = $this->resolveStoredPurchaseThemeConfig($purchase, $request);
        $themeConfig = $this->resolvePurchaseReturnThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $this->mapPurchaseReturnToThemePreviewData($purchase),
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
        ]);

        if (($themeConfig['mode'] ?? 'regular') === 'thermal') {
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        $billNumber = $purchase->bill_number ?: $purchase->id;
        $subject = trim((string) ($data['subject'] ?? '')) ?: 'Purchase Return PDF - ' . $billNumber;
        $message = trim((string) ($data['message'] ?? '')) ?: "Dear Sir,\n\nPlease find the purchase return PDF attached below.\n\nThank you for doing business with us.\nThanks and regards.";
        $fileName = 'purchase-return-' . $billNumber . '.pdf';

        try {
            Mail::raw($message, function ($mail) use ($data, $subject, $pdf, $fileName) {
                $mail->to($data['email'])
                    ->subject($subject)
                    ->attachData($pdf->output(), $fileName, ['mime' => 'application/pdf']);

                $fromAddress = config('mail.from.address');
                if (!empty($fromAddress)) {
                    $mail->from($fromAddress, config('mail.from.name'));
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send email right now.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully.',
        ]);
    }

    private function renderPurchaseReturnForm(?Purchase $purchaseReturn = null, ?Purchase $duplicatePurchaseReturn = null)
    {
        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $items = Item::active()->orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        $nextInvoiceNumber = TransactionNumberPrefix::format('purchase_return', (Purchase::where('type', 'purchase_return')->max('id') ?? 0) + 1);

        return view('dashboard.purchases.purchase-return.create-purchase-return', compact(
            'bankAccounts',
            'items',
            'parties',
            'nextInvoiceNumber',
            'purchaseReturn',
            'duplicatePurchaseReturn'
        ));
    }

    private function validatePurchaseReturn(Request $request): array
    {
        return $request->validate([
            'party_id' => 'nullable|exists:parties,id',
            'party_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string|max:1000',
            'bill_number' => 'nullable|string|max:100',
            'reference_bill_number' => 'nullable|string|max:100',
            'bill_date' => 'nullable|date',
            'order_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'total_qty' => 'nullable|integer|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'discount_pct' => 'nullable|numeric|min:0',
            'discount_rs' => 'nullable|numeric|min:0',
            'tax_pct' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'grand_total' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
            'document_path' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.item_category' => 'nullable|string|max:255',
            'items.*.item_code' => 'nullable|string|max:255',
            'items.*.item_description' => 'nullable|string',
            'items.*.quantity' => 'nullable|integer|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.payment_type' => 'required|string|max:50',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string|max:255',
            'linked_payments' => 'nullable|array',
            'linked_payments.*.purchase_id' => 'nullable|integer',
            'linked_payments.*.sale_id' => 'nullable|integer',
            'linked_payments.*.transaction_id' => 'nullable|integer',
            'linked_payments.*.amount' => 'required_with:linked_payments|numeric|min:0.01',
        ]);
    }

    private function savePurchaseReturn(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {
            $isExistingPurchaseReturn = $purchase->exists;

            if ($isExistingPurchaseReturn) {
                $purchase->loadMissing('payments');
                $this->revertBankAdjustments($purchase);
            }

            $purchase->fill([
                'type' => 'purchase_return',
                'party_id' => $data['party_id'] ?? null,
                'party_name' => $data['party_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'bill_number' => $data['bill_number'] ?? null,
                'bill_date' => $data['bill_date'] ?? $data['order_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? ($data['bill_date'] ?? $data['order_date'] ?? now()->toDateString()),
                'total_qty' => $data['total_qty'] ?? 0,
                'total_amount' => $data['total_amount'] ?? 0,
                'discount_pct' => $data['discount_pct'] ?? 0,
                'discount_rs' => $data['discount_rs'] ?? 0,
                'tax_pct' => $data['tax_pct'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'shipping_charge' => 0,
                'round_off' => $data['round_off'] ?? 0,
                'grand_total' => $data['grand_total'] ?? 0,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'balance' => $data['balance'] ?? 0,
                'description' => $data['description'] ?? null,
                'image_path' => $data['image_path'] ?? null,
                'document_path' => $data['document_path'] ?? null,
            ]);

            $purchase->save();

            if (empty($purchase->bill_number)) {
                $purchase->bill_number = TransactionNumberPrefix::format('purchase_return', $purchase->id);
                $purchase->save();
            }

            $purchase->items()->delete();
            foreach ($data['items'] as $item) {
                $purchase->items()->create([
                    'item_id' => $item['item_id'] ?? null,
                    'item_name' => $item['item_name'] ?? null,
                    'item_category' => $item['item_category'] ?? null,
                    'item_code' => $item['item_code'] ?? null,
                    'item_description' => $item['item_description'] ?? null,
                    'quantity' => $item['quantity'] ?? 0,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'amount' => $item['amount'] ?? 0,
                ]);
            }

            $purchase->payments()->delete();
            foreach ($data['payments'] ?? [] as $payment) {
                $paymentRecord = $purchase->payments()->create([
                    'payment_type' => $payment['payment_type'],
                    'bank_account_id' => $payment['bank_account_id'] ?? null,
                    'amount' => $payment['amount'] ?? 0,
                    'reference' => $payment['reference'] ?? null,
                ]);

                $this->applyBankAdjustment($purchase, $paymentRecord);
            }

            if (!$isExistingPurchaseReturn && !empty($data['linked_payments'])) {
                $this->applyLinkedPayments($purchase, $data['linked_payments'], $data['payments'][0] ?? null);
            }

            return $purchase->fresh(['items', 'payments.bankAccount', 'party']);
        });
    }

    private function applyLinkedPayments(Purchase $purchaseReturn, array $linkedRows, ?array $sourcePayment = null): void
    {
        $paymentType = $sourcePayment['payment_type'] ?? 'Purchase Return';
        $bankAccountId = $sourcePayment['bank_account_id'] ?? null;
        $receiptNo = $purchaseReturn->bill_number;

        collect($linkedRows)
            ->map(function ($row) {
                return [
                    'purchase_id' => (int) ($row['purchase_id'] ?? 0),
                    'sale_id' => (int) ($row['sale_id'] ?? 0),
                    'transaction_id' => (int) ($row['transaction_id'] ?? 0),
                    'amount' => round((float) ($row['amount'] ?? 0), 2),
                ];
            })
            ->filter(fn ($row) => ($row['purchase_id'] > 0 || $row['sale_id'] > 0 || $row['transaction_id'] > 0) && $row['amount'] > 0)
            ->each(function ($linkedRow) use ($purchaseReturn, $paymentType, $bankAccountId, $receiptNo) {
                if ($linkedRow['sale_id'] > 0) {
                    $sale = DB::table('sales')->lockForUpdate()->find($linkedRow['sale_id']);
                    if (!$sale) {
                        return;
                    }

                    $saleGrandTotal = (float) ($sale->grand_total ?? $sale->total_amount ?? 0);
                    $saleReceived = (float) ($sale->received_amount ?? 0);
                    $saleBalance = max(0, $saleGrandTotal - $saleReceived);
                    $allocate = min($linkedRow['amount'], $saleBalance);

                    if ($allocate > 0) {
                        $newReceivedAmount = round($saleReceived + $allocate, 2);
                        $newBalance = max(0, round($saleGrandTotal - $newReceivedAmount, 2));
                        DB::table('sales')->where('id', $sale->id)->update([
                            'received_amount' => $newReceivedAmount,
                            'balance' => $newBalance,
                            'status' => $newBalance <= 0 ? 'paid' : ($newReceivedAmount > 0 ? 'partial' : 'unpaid'),
                            'updated_at' => now(),
                        ]);
                    }

                    return;
                }

                if ($linkedRow['transaction_id'] > 0) {
                    $txn = Transaction::query()->lockForUpdate()->find($linkedRow['transaction_id']);
                    if (!$txn) {
                        return;
                    }

                    $txnTotal = (float) ($txn->total ?? 0);
                    $txnPaid = (float) ($txn->paid_amount ?? 0);
                    $txnBalance = max(0, $txnTotal - $txnPaid);
                    $allocate = min($linkedRow['amount'], $txnBalance);

                    if ($allocate > 0) {
                        $newPaidAmount = round($txnPaid + $allocate, 2);
                        $newBalance = max(0, round($txnTotal - $newPaidAmount, 2));
                        $txn->update([
                            'paid_amount' => $newPaidAmount,
                            'balance' => $newBalance,
                            'status' => $newBalance <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                        ]);
                    }

                    return;
                }

                if ($linkedRow['purchase_id'] > 0) {
                    $purchase = Purchase::query()->lockForUpdate()->find($linkedRow['purchase_id']);
                    if (!$purchase || (int) $purchase->id === (int) $purchaseReturn->id) {
                        return;
                    }

                    $purchaseGrandTotal = (float) ($purchase->grand_total ?? $purchase->total_amount ?? 0);
                    $purchasePaid = (float) ($purchase->paid_amount ?? 0);
                    $purchaseBalance = max(0, $purchaseGrandTotal - $purchasePaid);
                    $allocate = min($linkedRow['amount'], $purchaseBalance);

                    if ($allocate > 0) {
                        PurchasePayment::create([
                            'purchase_id' => $purchase->id,
                            'payment_type' => $paymentType,
                            'bank_account_id' => $bankAccountId,
                            'amount' => $allocate,
                            'reference' => 'purchase_return:' . $purchaseReturn->id,
                            'receipt_no' => $receiptNo,
                        ]);

                        $newPaidAmount = round($purchasePaid + $allocate, 2);
                        $newBalance = max(0, round($purchaseGrandTotal - $newPaidAmount, 2));
                        $purchase->update([
                            'paid_amount' => $newPaidAmount,
                            'balance' => $newBalance,
                        ]);
                    }
                }
            });
    }

    private function mapPurchaseReturnToThemePreviewData(Purchase $purchase): array
    {
        $items = $purchase->items->map(function ($item) {
            $quantity = (float) ($item->quantity ?? 0);
            $rate = (float) ($item->unit_price ?? 0);
            $amount = (float) ($item->amount ?? ($quantity * $rate));

            return [
                'name' => (string) ($item->item_name ?: 'Item'),
                'hsn' => (string) ($item->item_code ?? ''),
                'qty' => $quantity,
                'unit' => (string) ($item->unit ?? ''),
                'rate' => $rate,
                'disc' => (string) ($item->discount ?? '0'),
                'gst' => (float) ($purchase->tax_pct ?? 0) . '%',
                'amt' => $amount,
                'customFieldSummary' => trim((string) ($item->item_description ?? '')),
            ];
        })->values()->all();

        $billDate = $purchase->bill_date ?: $purchase->created_at;
        $dueDate = $purchase->due_date ?: $billDate;
        $firstBankPayment = $purchase->payments->first(fn ($payment) => $payment->bankAccount);

        return [
            'title' => 'Purchase Return / Debit Note',
            'businessName' => config('app.name', 'Vyapar'),
            'phone' => '',
            'invoiceNo' => (string) ($purchase->bill_number ?: $purchase->id),
            'date' => optional($billDate)->format('d/m/Y') ?: '',
            'time' => optional($purchase->created_at)->format('h:i A') ?: '',
            'dueDate' => optional($dueDate)->format('d/m/Y') ?: '',
            'billTo' => (string) ($purchase->party_name ?: ($purchase->party?->name ?? '')),
            'billAddress' => (string) ($purchase->billing_address ?? ''),
            'billPhone' => (string) ($purchase->phone ?? ''),
            'shipTo' => (string) ($purchase->billing_address ?? ''),
            'items' => $items,
            'subtotal' => (float) ($purchase->total_amount ?? 0),
            'discount' => (float) ($purchase->discount_rs ?? 0),
            'taxAmount' => (float) ($purchase->tax_amount ?? 0),
            'total' => (float) ($purchase->grand_total ?? 0),
            'received' => (float) ($purchase->paid_amount ?? 0),
            'balance' => (float) ($purchase->balance ?? 0),
            'description' => (string) ($purchase->description ?: 'Thanks for doing business with us!'),
            'bankName' => (string) ($firstBankPayment?->bankAccount?->display_name ?? ''),
            'bankAccountNumber' => (string) ($firstBankPayment?->bankAccount?->account_number ?? ''),
            'bankAccountHolder' => (string) ($firstBankPayment?->bankAccount?->account_holder_name ?? ''),
        ];
    }

    private function resolveStoredPurchaseThemeConfig(Purchase $purchase, Request $request): array
    {
        $stored = $purchase->invoice_theme;
        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }
        $stored = is_array($stored) ? $stored : [];

        $mode = (string) $request->query('mode', $stored['mode'] ?? 'regular');
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';
        $theme = (string) $request->query('theme', (string) ($stored['theme'] ?? ''));
        $themeIds = $this->purchaseReturnThemeIdsByKey();

        $regularThemeId = (int) $request->query(
            'theme_id',
            (int) ($themeIds['regular'][$theme] ?? ($stored['regularThemeId'] ?? ($stored['theme_id'] ?? 1)))
        );
        $thermalThemeId = (int) $request->query(
            'theme_id',
            (int) ($themeIds['thermal'][$theme] ?? ($stored['thermalThemeId'] ?? ($stored['theme_id'] ?? 1)))
        );
        $accent = (string) $request->query('accent', (string) ($stored['accent'] ?? '#1f4e79'));
        $accent2 = (string) $request->query('accent2', (string) ($stored['accent2'] ?? '#ff981f'));

        return [
            'mode' => $mode,
            'theme' => $theme,
            'regularThemeId' => $regularThemeId > 0 ? $regularThemeId : 1,
            'thermalThemeId' => $thermalThemeId > 0 ? $thermalThemeId : 1,
            'accent' => $accent !== '' ? $accent : '#1f4e79',
            'accent2' => $accent2 !== '' ? $accent2 : '#ff981f',
        ];
    }

    private function purchaseReturnThemeIdsByKey(): array
    {
        return [
            'regular' => [
                'tally' => 1,
                'LandScapeTheme1' => 2,
                'LandScapeTheme2' => 3,
                'tax1' => 4,
                'tax2' => 5,
                'tax3' => 6,
                'tax4' => 7,
                'tax5' => 8,
                'tax6' => 9,
                'divine' => 10,
                'french' => 11,
                'theme1' => 12,
                'theme2' => 13,
                'theme3' => 14,
                'theme4' => 15,
            ],
            'thermal' => [
                'thermal1' => 1,
                'thermal2' => 2,
                'thermal3' => 3,
                'thermal4' => 4,
                'thermal5' => 5,
            ],
        ];
    }

    private function resolveChromeExecutable(): ?string
    {
        $candidates = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveReactInvoiceAssets(): array
    {
        $resolved = null;

        foreach ($this->reactInvoiceAssetCandidates() as $source => $candidate) {
            $assetDirectory = $candidate['path'];
            if (! File::isDirectory($assetDirectory)) {
                continue;
            }

            $cssFile = collect(File::glob($assetDirectory . DIRECTORY_SEPARATOR . 'index-*.css'))
                ->sortByDesc(fn ($path) => filemtime($path))
                ->first();
            $jsFile = collect(File::glob($assetDirectory . DIRECTORY_SEPARATOR . 'index-*.js'))
                ->sortByDesc(fn ($path) => filemtime($path))
                ->first();

            if (! $cssFile || ! $jsFile) {
                continue;
            }

            $latestTimestamp = max(
                @filemtime($cssFile) ?: 0,
                @filemtime($jsFile) ?: 0
            );

            $payload = [
                'timestamp' => $latestTimestamp,
                'css_path' => $cssFile,
                'js_path' => $jsFile,
                'css_url' => route('invoice.react-asset', ['source' => $source, 'file' => basename($cssFile)]) . '?v=' . (@filemtime($cssFile) ?: time()),
                'js_url' => route('invoice.react-asset', ['source' => $source, 'file' => basename($jsFile)]) . '?v=' . (@filemtime($jsFile) ?: time()),
            ];

            if ($resolved === null || $latestTimestamp > $resolved['timestamp']) {
                $resolved = $payload;
            }
        }

        return $resolved
            ? [
                'css_path' => $resolved['css_path'],
                'js_path' => $resolved['js_path'],
                'css_url' => $resolved['css_url'],
                'js_url' => $resolved['js_url'],
            ]
            : [
                'css_path' => null,
                'js_path' => null,
                'css_url' => null,
                'js_url' => null,
            ];
    }

    private function reactInvoiceAssetCandidates(): array
    {
        return [
            'root' => [
                'path' => base_path('react-invoice/assets'),
            ],
            'public_parent' => [
                'path' => dirname(public_path()) . DIRECTORY_SEPARATOR . 'react-invoice' . DIRECTORY_SEPARATOR . 'assets',
            ],
            'base_parent' => [
                'path' => dirname(base_path()) . DIRECTORY_SEPARATOR . 'react-invoice' . DIRECTORY_SEPARATOR . 'assets',
            ],
            'dist' => [
                'path' => base_path('dist/assets'),
            ],
            'public' => [
                'path' => public_path('react-invoice/assets'),
            ],
            'nested_public' => [
                'path' => public_path('react-invoice/react-invoice/assets'),
            ],
        ];
    }

    private function resolvePurchaseReturnThemeConfig(string $mode, int $themeId): array
    {
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';
        $themes = $mode === 'thermal'
            ? [
                1 => ['name' => 'Thermal Theme 1', 'variant' => 'thermal1'],
                2 => ['name' => 'Thermal Theme 2', 'variant' => 'thermal2'],
                3 => ['name' => 'Thermal Theme 3', 'variant' => 'thermal3'],
                4 => ['name' => 'Thermal Theme 4', 'variant' => 'thermal4'],
                5 => ['name' => 'Thermal Theme 5', 'variant' => 'thermal5'],
            ]
            : [
                1 => ['name' => 'Telly Theme', 'variant' => 'classicA'],
                2 => ['name' => 'Landscape Theme 1', 'variant' => 'purpleA'],
                3 => ['name' => 'Landscape Theme 2', 'variant' => 'classicB'],
                4 => ['name' => 'Tax Theme 1', 'variant' => 'purpleB'],
                5 => ['name' => 'Tax Theme 2', 'variant' => 'classicC'],
                6 => ['name' => 'Tax Theme 3', 'variant' => 'modernPurple'],
                7 => ['name' => 'Tax Theme 4', 'variant' => 'purpleC'],
                8 => ['name' => 'Tax Theme 5', 'variant' => 'classicSale'],
                9 => ['name' => 'Tax Theme 6', 'variant' => 'taxTheme6'],
                10 => ['name' => 'Double Divine', 'variant' => 'doubleDivine'],
                11 => ['name' => 'French Elite', 'variant' => 'frenchElite'],
                12 => ['name' => 'Theme 1', 'variant' => 'theme1'],
                13 => ['name' => 'Theme 2', 'variant' => 'theme2'],
                14 => ['name' => 'Theme 3', 'variant' => 'theme3'],
                15 => ['name' => 'Theme 4', 'variant' => 'theme4'],
            ];

        $theme = $themes[$themeId] ?? reset($themes);

        return [
            'id' => $themeId,
            'mode' => $mode,
            'name' => $theme['name'],
            'variant' => $theme['variant'],
        ];
    }

    private function applyBankAdjustment(Purchase $purchase, $payment): void
    {
        if (empty($payment->bank_account_id) || empty($payment->amount)) {
            return;
        }

        $bank = BankAccount::find($payment->bank_account_id);
        if (!$bank) {
            return;
        }

        $bank->opening_balance = ($bank->opening_balance ?? 0) + (float) $payment->amount;
        $bank->save();

        BankTransaction::create([
            'from_bank_account_id' => null,
            'to_bank_account_id' => $bank->id,
            'type' => 'purchase_return_refund',
            'amount' => (float) $payment->amount,
            'transaction_date' => $purchase->bill_date ?? now()->toDateString(),
            'reference_type' => 'purchase_return',
            'reference_id' => $purchase->id,
            'description' => 'Purchase return refund received from supplier',
            'meta' => [
                'party_id' => $purchase->party_id,
                'bill_number' => $purchase->bill_number,
                'payment_type' => $payment->payment_type ?? null,
                'reference' => $payment->reference ?? null,
            ],
        ]);
    }

    private function revertBankAdjustments(Purchase $purchase): void
    {
        foreach ($purchase->payments as $payment) {
            if (empty($payment->bank_account_id) || empty($payment->amount)) {
                continue;
            }

            $bank = BankAccount::find($payment->bank_account_id);
            if ($bank) {
                $bank->opening_balance = ($bank->opening_balance ?? 0) - (float) $payment->amount;
                $bank->save();
            }
        }

        BankTransaction::where('reference_type', 'purchase_return')
            ->where('reference_id', $purchase->id)
            ->delete();
    }
}
