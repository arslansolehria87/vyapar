<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Item;
use App\Models\Party;
use App\Models\Purchase;
use App\Support\TransactionNumberPrefix;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('dashboard.purchases.purchase-return.purchase-return-preview', compact('purchase'));
    }

    public function print(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $purchase->load(['items', 'payments.bankAccount', 'party']);

        return view('dashboard.purchases.purchase-return.purchase-return-preview', [
            'purchase' => $purchase,
            'autoPrint' => true,
        ]);
    }

    public function pdf(Purchase $purchase)
    {
        abort_unless($purchase->type === 'purchase_return', 404);
        $purchase->load(['items', 'payments.bankAccount', 'party']);

        $themeDefaults = $this->resolveStoredPurchaseThemeConfig($purchase, request());
        $themeConfig = $this->resolvePurchaseReturnThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        $fileName = 'purchase-return-' . ($purchase->bill_number ?: $purchase->id) . '.pdf';
        $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', [
            'purchase' => $purchase,
            'invoicePreviewData' => $this->mapPurchaseReturnToThemePreviewData($purchase),
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
            'pageTitle' => 'Purchase Return PDF',
            'browserTabLabel' => 'Purchase Return #' . ($purchase->bill_number ?: $purchase->id),
            'saveCloseUrl' => route('purchase-return'),
        ]);

        if (($themeConfig['mode'] ?? 'regular') === 'thermal') {
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
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

            return $purchase->fresh(['items', 'payments.bankAccount', 'party']);
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

        $regularThemeId = (int) $request->query(
            'theme_id',
            (int) ($stored['regularThemeId'] ?? ($stored['theme_id'] ?? 1))
        );
        $thermalThemeId = (int) $request->query(
            'theme_id',
            (int) ($stored['thermalThemeId'] ?? ($stored['theme_id'] ?? 1))
        );
        $accent = (string) $request->query('accent', (string) ($stored['accent'] ?? '#1f4e79'));
        $accent2 = (string) $request->query('accent2', (string) ($stored['accent2'] ?? '#ff981f'));

        return [
            'mode' => $mode,
            'regularThemeId' => $regularThemeId > 0 ? $regularThemeId : 1,
            'thermalThemeId' => $thermalThemeId > 0 ? $thermalThemeId : 1,
            'accent' => $accent !== '' ? $accent : '#1f4e79',
            'accent2' => $accent2 !== '' ? $accent2 : '#ff981f',
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
