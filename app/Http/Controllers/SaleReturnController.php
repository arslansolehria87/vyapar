<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Models\Item;
use App\Models\Party;
use App\Models\Sale;
use App\Support\TransactionNumberPrefix;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function saleReturn(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $saleReturns = Sale::with(['items', 'payments', 'party'])
            ->where('type', 'sale_return')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('bill_number', 'like', '%' . $search . '%')
                        ->orWhereHas('party', function ($partyQuery) use ($search) {
                            $partyQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.sales.sale-return', compact('saleReturns', 'search'));
    }

    public function salereturncreate(Request $request)
    {
        $sourceSale = null;
        $duplicateSaleReturn = null;
        $prefilledSaleReturnData = null;

        if ($request->filled('duplicate_sale_id')) {
            $duplicateSaleReturn = Sale::with(['items', 'payments', 'party', 'details'])
                ->where('type', 'sale_return')
                ->findOrFail($request->integer('duplicate_sale_id'));

            return $this->renderSaleReturnForm(null, $duplicateSaleReturn);
        }

        if ($request->filled('sale_id')) {
            $sourceSale = Sale::with(['items', 'payments', 'party', 'details'])
                ->where('type', 'invoice')
                ->findOrFail($request->integer('sale_id'));

            $prefilledSaleReturnData = [
                'source_sale_id' => $sourceSale->id,
                'party_id' => $sourceSale->party_id,
                'phone' => $sourceSale->phone,
                'billing_address' => $sourceSale->billing_address,
                'shipping_address' => $sourceSale->shipping_address,
                'bill_number' => null,
                'reference_bill_number' => $sourceSale->bill_number,
                'invoice_date' => optional($sourceSale->invoice_date)->format('Y-m-d') ?: now()->toDateString(),
                'order_date' => optional($sourceSale->invoice_date)->format('Y-m-d') ?: now()->toDateString(),
                'due_date' => optional($sourceSale->due_date)->format('Y-m-d') ?: now()->toDateString(),
                'discount_pct' => $sourceSale->discount_pct,
                'discount_rs' => $sourceSale->discount_rs,
                'tax_pct' => $sourceSale->tax_pct,
                'tax_amount' => $sourceSale->tax_amount,
                'round_off' => $sourceSale->round_off,
                'grand_total' => $sourceSale->grand_total,
                'balance' => $sourceSale->grand_total,
                'description' => $sourceSale->description,
                'details' => $sourceSale->details?->toArray(),
                'custom_expenses' => $sourceSale->details?->custom_expenses,
                'items' => $sourceSale->items->map(function ($item) {
                    return [
                        'item_id' => $item->item_id,
                        'item_name' => $item->item_name,
                        'item_category' => $item->item_category,
                        'item_code' => $item->item_code,
                        'item_description' => $item->item_description,
                        'tafseel' => $item->tafseel,
                        'quantity' => $item->quantity,
                        'gross_w' => $item->gross_w,
                        'net_w' => $item->net_w,
                        'unit' => $item->unit,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'amount' => $item->amount,
                    ];
                })->values()->all(),
                'payments' => [],
            ];
        }

        return $this->renderSaleReturnForm(null, null, $sourceSale, $prefilledSaleReturnData);
    }

    public function edit(Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);

        $sale->load(['items', 'payments', 'party', 'details']);

        return $this->renderSaleReturnForm($sale);
    }

    public function duplicate(Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);

        $sale->load(['items', 'payments', 'party', 'details']);

        return $this->renderSaleReturnForm(null, $sale);
    }

    public function store(Request $request)
    {
        $data = $this->validateSaleReturnRequest($request);
        $existingBill = Sale::where('bill_number', $data['bill_number'])->exists();

        if ($existingBill) {
            return response()->json([
                'message' => 'Invoice ID already exists. Please enter a unique Invoice ID.',
            ], 422);
        }

        $receivedAmount = $this->calculateReceivedAmount($data['payments'] ?? []);
        $grandTotal = (float) ($data['grand_total'] ?? 0);

        $sale = DB::transaction(function () use ($data, $receivedAmount, $grandTotal) {
            $sale = Sale::create($this->buildSalePayload(
                $data,
                $receivedAmount,
                max(0, $grandTotal - $receivedAmount),
                $this->resolvePaymentStatus($receivedAmount, $grandTotal)
            ));

            $this->syncItems($sale, $data['items']);
            $this->syncPayments($sale, $data['payments'] ?? []);

            if (!empty($data['source_sale_id'])) {
                Sale::whereKey($data['source_sale_id'])
                    ->where('type', 'invoice')
                    ->update(['status' => 'returned']);
            }

            return $sale;
        });

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => route('invoice', [
                'sale_id' => $sale->id,
                'type' => 'return-order',
            ]),
            'share_url' => route('invoice', [
                'sale_id' => $sale->id,
                'type' => 'return-order',
            ]),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);

        $data = $this->validateSaleReturnRequest($request);
        $existingReceived = (float) ($sale->received_amount ?? 0);
        $newReceived = $this->calculateReceivedAmount($data['payments'] ?? []);
        $receivedAmount = $existingReceived + $newReceived;
        $grandTotal = (float) ($data['grand_total'] ?? 0);

        DB::transaction(function () use ($sale, $data, $receivedAmount, $grandTotal) {
            $sale->update($this->buildSalePayload(
                $data,
                $receivedAmount,
                max(0, $grandTotal - $receivedAmount),
                $this->resolvePaymentStatus($receivedAmount, $grandTotal)
            ));

            $sale->items()->delete();
            $this->syncItems($sale, $data['items']);
            $this->syncPayments($sale, $data['payments'] ?? []);
        });

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => route('invoice', [
                'sale_id' => $sale->id,
                'type' => 'return-order',
            ]),
            'share_url' => route('invoice', [
                'sale_id' => $sale->id,
                'type' => 'return-order',
            ]),
        ]);
    }

    public function destroy(Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);

        $sale->items()->delete();
        $sale->payments()->delete();
        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale return deleted successfully.',
        ]);
    }

    public function preview(Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);
        $sale->load(['items', 'payments', 'party']);

        return view('dashboard.sales.sale-return-preview', compact('sale'));
    }

    public function print(Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);
        $sale->load(['items', 'payments', 'party']);

        return view('dashboard.sales.sale-return-preview', ['sale' => $sale, 'autoPrint' => true]);
    }

    public function pdf(Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);
        $sale->loadMissing(['items', 'payments.bankAccount', 'party', 'details']);

        $themeMode = (string) request()->query('mode', 'regular');
        $themeId = (int) request()->query('theme_id', 1);
        $themeConfig = $this->resolveSaleReturnThemeConfig($themeMode, $themeId);
        $previewData = $this->mapSaleReturnToThemePreviewData($sale);
        $signatureImage = (string) request()->query('signature_image', '');

        $payload = [
            'sale' => $sale,
            'invoicePreviewData' => $previewData,
            'themeConfig' => $themeConfig,
            'saleOrderThemeApplied' => false,
            'accent' => '#1f4e79',
            'accent2' => '#ff981f',
            'signatureImage' => $signatureImage,
            'pageTitle' => 'Sale Return PDF',
            'browserTabLabel' => 'Sale Return #' . ($sale->bill_number ?: $sale->id),
            'saveCloseUrl' => route('sale-return'),
        ];

        $fileName = 'sale-return-' . ($sale->bill_number ?: $sale->id) . '.pdf';
        $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', $payload);

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

    public function bankHistory(Sale $sale)
    {
        abort_unless($sale->type === 'sale_return', 404);

        $sale->loadMissing(['payments.bankAccount']);

        $transactions = BankTransaction::with(['fromBankAccount'])
            ->where('reference_type', 'sale_return')
            ->where('reference_id', $sale->id)
            ->orderByDesc('transaction_date')
            ->get()
            ->map(function ($transaction) {
                return [
                    'bank_name' => $transaction->fromBankAccount?->display_name ?: '-',
                    'amount' => (float) ($transaction->amount ?? 0),
                    'type' => (string) ($transaction->type ?: 'sale_return_refund'),
                    'reference' => (string) ($transaction->description ?: '-'),
                    'date' => $this->formatPreviewDate($transaction->transaction_date),
                ];
            });

        if ($transactions->isEmpty()) {
            $transactions = $sale->payments->map(function ($payment) use ($sale) {
                return [
                    'bank_name' => $payment->bankAccount?->display_name ?: '-',
                    'amount' => (float) ($payment->amount ?? 0),
                    'type' => 'sale_return_refund',
                    'reference' => $payment->reference ?: '-',
                    'date' => $this->formatPreviewDate($sale->invoice_date ?: $sale->created_at),
                ];
            });
        }

        return response()->json([
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number ?: $sale->id,
            'entries' => $transactions->values(),
        ]);
    }

    private function renderSaleReturnForm(
    ?Sale $saleReturn = null,
    ?Sale $duplicateSaleReturn = null,
    ?Sale $sourceSale = null,
    ?array $prefilledSaleReturnData = null
)
{
    $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
    $items = Item::active()->orderBy('name')->get();
    $parties = Party::orderBy('name')->get();
    $brokers = Party::where('party_type', 'broker')->orderBy('name')->get();
    $partyGroups = \App\Models\PartyGroup::orderBy('name')->get();
    $nextInvoiceNumber = $this->generateUniqueSaleReturnInvoiceNumber();

    return view('dashboard.sales.create-sale-return', compact(
        'bankAccounts',
        'items',
        'parties',
        'brokers',
        'partyGroups',
        'nextInvoiceNumber',
        'saleReturn',
        'duplicateSaleReturn',
        'sourceSale',
        'prefilledSaleReturnData'
    ));
}

    private function generateUniqueSaleReturnInvoiceNumber(): string
    {
        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $candidate = TransactionNumberPrefix::format('credit_note', $nextSaleId);

        while (Sale::where('bill_number', $candidate)->exists()) {
            $nextSaleId++;
            $candidate = TransactionNumberPrefix::format('credit_note', $nextSaleId);
        }

        return $candidate;
    }

    private function validateSaleReturnRequest(Request $request): array
    {
        return $request->validate([
            'source_sale_id' => 'nullable|exists:sales,id',
            'party_id' => 'nullable|exists:parties,id',
            'phone' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:1000',
            'bill_number' => 'required|string|max:100',
            'reference_bill_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
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
            'description' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
            'document_path' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.item_category' => 'nullable|string|max:255',
            'items.*.item_code' => 'nullable|string|max:255',
            'items.*.item_description' => 'nullable|string',
            'items.*.tafseel' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|integer|min:0',
            'items.*.gross_w' => 'nullable|numeric|min:0',
            'items.*.net_w' => 'nullable|numeric|min:0',
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

    private function buildSalePayload(array $data, float $receivedAmount, float $balance, string $status): array
    {
        return [
            'type' => 'sale_return',
            'party_id' => $data['party_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'bill_number' => $data['bill_number'],
            'reference_bill_number' => $data['reference_bill_number'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
            'order_date' => $data['order_date'] ?? ($data['invoice_date'] ?? now()->toDateString()),
            'due_date' => $data['due_date'] ?? ($data['order_date'] ?? $data['invoice_date'] ?? now()->toDateString()),
            'total_qty' => $data['total_qty'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'discount_pct' => $data['discount_pct'] ?? 0,
            'discount_rs' => $data['discount_rs'] ?? 0,
            'tax_pct' => $data['tax_pct'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'round_off' => $data['round_off'] ?? 0,
            'grand_total' => $data['grand_total'] ?? 0,
            'received_amount' => $receivedAmount,
            'balance' => $balance,
            'status' => $status,
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'document_path' => $data['document_path'] ?? null,
        ];
    }

    private function syncItems(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            $sale->items()->create([
                'item_name' => $item['item_name'] ?? null,
                'item_category' => $item['item_category'] ?? null,
                'item_code' => $item['item_code'] ?? null,
                'item_description' => $item['item_description'] ?? null,
                'tafseel' => $item['tafseel'] ?? null,
                'quantity' => $item['quantity'] ?? 0,
                'gross_w' => $item['gross_w'] ?? 0,
                'net_w' => $item['net_w'] ?? 0,
                'unit' => $item['unit'] ?? null,
                'unit_price' => $item['unit_price'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'amount' => $item['amount'] ?? 0,
            ]);
        }
    }

    private function syncPayments(Sale $sale, array $payments): void
    {
        foreach ($payments as $payment) {
            $paymentAmount = (float) ($payment['amount'] ?? 0);
            $rawPaymentType = (string) ($payment['payment_type'] ?? '');
            $normalizedPaymentType = strtolower($rawPaymentType);
            $isCash = $normalizedPaymentType === 'cash';
            $bankId = $payment['bank_account_id'] ?? null;
            $storePaymentType = $isCash ? 'cash' : $rawPaymentType;

            if ($paymentAmount <= 0) {
                continue;
            }

            if (!$isCash && empty($bankId)) {
                continue;
            }

            $cashAccount = null;
            if ($isCash) {
                $cashAccount = BankAccount::cashAccount();
                $bankId = $cashAccount->id;
            }

            $sale->payments()->create([
                'payment_type' => $storePaymentType,
                'bank_account_id' => $bankId,
                'amount' => $paymentAmount,
                'reference' => $payment['reference'] ?? null,
            ]);

            $bank = $isCash ? $cashAccount : BankAccount::find($bankId);
            if ($bank) {
                $bank->opening_balance = ($bank->opening_balance ?? 0) - $paymentAmount;
                $bank->save();

                BankTransaction::create([
                    'from_bank_account_id' => $bank->id,
                    'to_bank_account_id' => null,
                    'type' => $isCash ? 'cash_out' : 'sale_return_refund',
                    'amount' => $paymentAmount,
                    'transaction_date' => $sale->invoice_date ?? now()->toDateString(),
                    'reference_type' => 'sale_return',
                    'reference_id' => $sale->id,
                    'description' => $isCash ? 'Cash refund for sale return' : 'Sale return refund to party',
                    'meta' => [
                        'party_id' => $sale->party_id,
                    'reference_bill_number' => $sale->reference_bill_number,
                    'payment_type' => $storePaymentType,
                ],
            ]);
            }
        }
    }

    private function calculateReceivedAmount(array $payments): float
    {
        $receivedAmount = 0;

        foreach ($payments as $payment) {
            $paymentType = strtolower((string) ($payment['payment_type'] ?? ''));
            if (!empty($payment['bank_account_id']) || $paymentType === 'cash') {
                $receivedAmount += (float) ($payment['amount'] ?? 0);
            }
        }

        return $receivedAmount;
    }

    private function resolvePaymentStatus(float $receivedAmount, float $grandTotal): string
    {
        if ($receivedAmount >= $grandTotal && $grandTotal > 0) {
            return 'Paid';
        }

        if ($receivedAmount > 0 && $receivedAmount < $grandTotal) {
            return 'Partial';
        }

        return 'Unpaid';
    }

    private function formatPreviewDate($value): string
    {
        if (empty($value)) {
            return now()->format('d/m/Y');
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return now()->format('d/m/Y');
        }
    }

    private function formatAmountInWords(float $amount): string
    {
        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
            $whole = (int) floor(abs($amount));
            $fraction = (int) round((abs($amount) - $whole) * 100);

            $words = ucfirst((string) $formatter->format($whole)) . ' Rupees';

            if ($fraction > 0) {
                $words .= ' and ' . (string) $formatter->format($fraction) . ' Paisa';
            }

            return trim($words) . ' only';
        }

        return 'Rupees ' . number_format($amount, 2);
    }

    private function mapSaleReturnToThemePreviewData(Sale $sale): array
    {
        $items = $sale->items->map(function ($item) use ($sale) {
            $amount = (float) ($item->amount ?? 0);
            $rate = (float) ($item->unit_price ?? 0);
            $quantity = (float) ($item->quantity ?? 0);

            if ($amount <= 0 && $quantity > 0 && $rate > 0) {
                $amount = round($quantity * $rate, 2);
            }

            return [
                'name' => $item->item_name ?: 'Item',
                'hsn' => (string) ($item->item_code ?: ''),
                'qty' => (string) ($item->quantity ?? 0),
                'gross_w' => (float) ($item->gross_w ?? 0),
                'net_w' => (float) ($item->net_w ?? 0),
                'unit' => (string) ($item->unit ?: ''),
                'rate' => $rate,
                'disc' => number_format((float) ($item->discount ?? 0), 2, '.', ''),
                'gst' => $this->formatPercentValue($sale->tax_pct),
                'amt' => $amount,
                'amount' => $amount,
            ];
        })->values()->all();

        if (empty($items)) {
            $items[] = [
                'name' => 'Item',
                'hsn' => '',
                'qty' => '0',
                'gross_w' => 0,
                'net_w' => 0,
                'unit' => '',
                'rate' => 0,
                'disc' => '0.00',
                'gst' => $this->formatPercentValue($sale->tax_pct),
                'amt' => 0,
                'amount' => 0,
            ];
        }

        $invoiceDate = $this->formatPreviewDate($sale->invoice_date ?: $sale->created_at);
        $dueDate = $this->formatPreviewDate($sale->due_date ?: $sale->invoice_date ?: $sale->created_at);
        $createdAt = $sale->created_at instanceof Carbon ? $sale->created_at : Carbon::parse($sale->created_at);
        $businessName = trim((string) config('app.name', 'My Company')) ?: 'My Company';
        $partyName = $sale->display_party_name !== '-' ? $sale->display_party_name : 'Walk-in Customer';
        $subtotal = (float) ($sale->total_amount ?? collect($items)->sum('amt'));
        $totalAmount = (float) ($sale->grand_total ?? max($subtotal + (float) ($sale->tax_amount ?? 0) - (float) ($sale->discount_rs ?? 0), 0));
        $receivedAmount = (float) ($sale->received_amount ?? 0);
        $balance = (float) ($sale->balance ?? max($totalAmount - $receivedAmount, 0));

        return [
            'title' => 'Sale Return',
            'businessName' => $businessName,
            'phone' => (string) ($sale->phone ?: ($sale->party?->phone ?: '')),
            'invoiceNo' => (string) ($sale->bill_number ?: $sale->id),
            'date' => $invoiceDate,
            'time' => $createdAt->format('h:i A'),
            'dueDate' => $dueDate,
            'billTo' => $partyName,
            'billAddress' => (string) ($sale->billing_address ?: ''),
            'billPhone' => (string) ($sale->phone ?: ($sale->party?->phone ?: '')),
            'shipTo' => (string) ($sale->shipping_address ?: $sale->billing_address ?: ''),
            'items' => $items,
            'description' => (string) ($sale->description ?: 'Thanks for doing business with us!'),
            'subtotal' => $subtotal,
            'discount' => (float) ($sale->discount_rs ?? 0),
            'taxAmount' => (float) ($sale->tax_amount ?? 0),
            'total' => $totalAmount,
            'received' => $receivedAmount,
            'balance' => $balance,
            'totalInWords' => $this->formatAmountInWords($totalAmount),
            'termsText' => trim((string) ($sale->details?->terms_condition_text ?: $sale->description ?: 'Thanks for doing business with us!')),
            'signatureText' => 'Authorized Signatory',
        ];
    }

    private function formatPercentValue($value): string
    {
        $numeric = (float) ($value ?? 0);
        $formatted = number_format($numeric, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return ($formatted === '' ? '0' : $formatted) . '%';
    }

    private function resolveSaleReturnThemeConfig(string $mode, int $themeId): array
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

    public function nextInvoiceNumber()
    {
        return response()->json([
            'number' => $this->generateUniqueSaleReturnInvoiceNumber(),
        ]);
    }

}
