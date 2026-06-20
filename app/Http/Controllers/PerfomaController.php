<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\BankAccount;
use App\Models\Broker;
use App\Models\Party;
use App\Models\PartyGroup;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Models\AppSetting;
use App\Support\TransactionNumberPrefix;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PerfomaController extends Controller
{
    public function proformaInvoice(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $dateRange = $request->get('date_range', 'custom');
        $partyId = $request->get('party_id', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $dateRangeLabel = $this->formatProformaDateRangeText($dateRange, $fromDate, $toDate);

        $baseQuery = Sale::with(['items', 'payments', 'party'])
            ->where('type', 'proforma')
            ->orderByDesc('created_at');

        if ($dateRange !== 'all') {
            $range = $this->resolveProformaDateRange($dateRange, $fromDate, $toDate);
            if ($range) {
                $baseQuery->whereBetween('invoice_date', [$range['from']->toDateString(), $range['to']->toDateString()]);
            }
        }

        if ($partyId !== 'all') {
            $baseQuery->where('party_id', $partyId);
        }

        $allProformas = (clone $baseQuery)->get();

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('bill_number', 'like', "%{$search}%")
                    ->orWhereHas('party', function ($partyQuery) use ($search) {
                        $partyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $proformas = $baseQuery->get();

        $partyOptions = Party::whereIn('id', $allProformas->pluck('party_id')->filter()->unique())
            ->orderBy('name')
            ->get();

        $convertedSales = Sale::where('type', 'invoice')
            ->whereNotNull('reference_id')
            ->whereIn('reference_id', $allProformas->pluck('id'))
            ->pluck('bill_number', 'reference_id');

        $convertedSaleOrders = Sale::where('type', 'sale_order')
            ->whereNotNull('reference_id')
            ->whereIn('reference_id', $allProformas->pluck('id'))
            ->pluck('bill_number', 'reference_id');

    
        return view('dashboard.perfoma.perfoma-invoice', compact(
            'proformas',
            'allProformas',
            'search',
            'dateRange',
            'dateRangeLabel',
            'fromDate',
            'toDate',
            'partyId',
            'partyOptions',
            'convertedSales',
            'convertedSaleOrders',
            
        ));
    }

    private function resolveProformaDateRange(string $dateRange, ?string $fromDate = null, ?string $toDate = null): ?array
    {
        $today = Carbon::today();

        switch ($dateRange) {
            case 'custom':
                try {
                    $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $today->copy()->startOfMonth();
                    $to = $toDate ? Carbon::parse($toDate)->endOfDay() : $today->copy()->endOfMonth();
                } catch (\Throwable $exception) {
                    return null;
                }

                if ($from->gt($to)) {
                    [$from, $to] = [$to, $from];
                }

                return ['from' => $from, 'to' => $to];
            case 'this_month':
                return ['from' => $today->copy()->startOfMonth(), 'to' => $today->copy()->endOfMonth()];
            case 'last_month':
                $previous = $today->copy()->subMonthNoOverflow();
                return ['from' => $previous->copy()->startOfMonth(), 'to' => $previous->copy()->endOfMonth()];
            case 'this_quarter':
                return ['from' => $today->copy()->startOfQuarter(), 'to' => $today->copy()->endOfQuarter()];
            case 'this_year':
                return ['from' => $today->copy()->startOfYear(), 'to' => $today->copy()->endOfYear()];
            default:
                return null;
        }
    }

    private function formatProformaDateRangeText(string $dateRange, ?string $fromDate = null, ?string $toDate = null): string
    {
        $range = $this->resolveProformaDateRange($dateRange, $fromDate, $toDate);
        if (! $range) {
            return 'All dates';
        }

        return $range['from']->format('d/m/Y') . ' To ' . $range['to']->format('d/m/Y');
    }

    public function createProformaInvoice()
    {
        return $this->renderProformaForm();
    }

    public function edit(Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);

        $sale->load(['items', 'party', 'details']);

        return $this->renderProformaForm($sale);
    }

    public function duplicate(Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);

        $sale->load(['items', 'party', 'details']);

        return $this->renderProformaForm(null, $sale);
    }

    public function store(Request $request)
    {
        $this->normalizeJsonInputs($request);
        $data = $this->validateProformaRequest($request);
        $uploadedImagePaths = $this->storeAttachmentFiles($request->file('images', []), 'proforma/images');
        $uploadedDocumentPaths = $this->storeAttachmentFiles($request->file('documents', []), 'proforma/documents');

        $sale = Sale::create($this->buildSalePayload($data, $uploadedImagePaths, $uploadedDocumentPaths));
        $this->syncDetails($sale, $data);
        $this->syncItems($sale, $data['items']);

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => route('proforma-invoice.react', [
                'sale' => $sale->id,
                'type' => 'proforma-invoice',
            ]),
            'share_url' => route('proforma-invoice.react', [
                'sale' => $sale->id,
                'type' => 'proforma-invoice',
            ]),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);
        if ($sale->status === 'converted') {
            return response()->json([
                'success' => false,
                'message' => 'This Data is Converted please close the Tab',
            ], 422);
        }

        $this->normalizeJsonInputs($request);
        $data = $this->validateProformaRequest($request);
        $existingImagePaths = array_values(array_filter(array_merge($sale->image_paths ?? [], $data['image_paths'] ?? [])));
        $existingDocumentPaths = array_values(array_filter(array_merge($sale->document_paths ?? [], $data['document_paths'] ?? [])));
        $uploadedImagePaths = $this->storeAttachmentFiles($request->file('images', []), 'proforma/images');
        $uploadedDocumentPaths = $this->storeAttachmentFiles($request->file('documents', []), 'proforma/documents');

        $sale->update($this->buildSalePayload(
            $data,
            array_values(array_filter(array_merge($existingImagePaths, $uploadedImagePaths))),
            array_values(array_filter(array_merge($existingDocumentPaths, $uploadedDocumentPaths))),
            $sale
        ));
        $this->syncDetails($sale, $data);
        $sale->items()->delete();
        $this->syncItems($sale, $data['items']);

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => route('proforma-invoice.react', [
                'sale' => $sale->id,
                'type' => 'proforma-invoice',
            ]),
            'share_url' => route('proforma-invoice.react', [
                'sale' => $sale->id,
                'type' => 'proforma-invoice',
            ]),
        ]);
    }

    public function destroy(Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);

        $sale->items()->delete();
        $sale->payments()->delete();
        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proforma invoice deleted successfully.',
        ]);
    }

    public function preview(Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);
        return redirect()->route('sale.invoice-preview', ['sale' => $sale->id]);
    }

    public function print(Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);
        return redirect()->route('sale.invoice-preview', ['sale' => $sale->id, 'print' => 1]);
    }

    public function pdf(Sale $sale)
    {
        abort_unless($sale->type === 'proforma', 404);
        return redirect()->route('sale.invoice-pdf', ['sale' => $sale->id, 'download' => 1]);
    }

    private function renderProformaForm(?Sale $proforma = null, ?Sale $duplicateProforma = null)
    {
        $items = Item::active()->with('category')->orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        $brokers = Broker::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $customerPoDetailsEnabled = AppSetting::getValue('customer_po_details_enabled', '0') === '1';
        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $nextInvoiceNumber = TransactionNumberPrefix::format('proforma_invoice', $nextSaleId);
        return view('dashboard.perfoma.create_proforma_invoice', compact(
            'items',
            'parties',
            'brokers',
            'partyGroups',
            'bankAccounts',
            'warehouses',
            'customerPoDetailsEnabled',
            'nextInvoiceNumber',
            'proforma',
            'duplicateProforma'
        ));
    }

    private function validateProformaRequest(Request $request): array
    {
        return $request->validate([
            'type' => 'nullable|in:proforma',
            'party_id' => 'nullable|exists:parties,id',
            'phone' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:1000',
            'bill_number' => 'required|string|max:100',
            'invoice_date' => 'nullable|date',
            'order_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'deal_days' => 'nullable|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'city' => 'nullable|string|max:255',
            'party_no' => 'nullable|string|max:255',
            'goods_name' => 'nullable|string|max:255',
            'details_extra' => 'nullable|string|max:255',
            'bilti_gari_no' => 'nullable|string|max:255',
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
            'image_paths' => 'nullable|array',
            'image_paths.*' => 'nullable|string|max:255',
            'document_paths' => 'nullable|array',
            'document_paths.*' => 'nullable|string|max:255',
            'custom_expenses' => 'nullable|array',
            'custom_expenses.*.mode' => 'nullable|in:+,-,same,S',
            'custom_expenses.*.percentage' => 'nullable|numeric|min:0',
            'custom_expenses.*.pct' => 'nullable|numeric|min:0',
            'custom_expenses.*.amount' => 'nullable|numeric|min:0',
            'custom_expenses.*.value' => 'nullable|numeric|min:0',
            'custom_expenses.*.details' => 'nullable|string',
            'custom_expenses.*.heading' => 'nullable|string|max:255',
            'custom_expenses.*.title' => 'nullable|string|max:255',
            'custom_expenses.*.account_type' => 'nullable|in:party,broker,item',
            'custom_expenses.*.account_id' => 'nullable|integer|min:1',
            'custom_expenses.*.account_name' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
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
        ]);
    }

    private function buildSalePayload(array $data, array $imagePaths = [], array $documentPaths = [], ?Sale $existingSale = null): array
    {
        $grandTotal = (float) ($data['grand_total'] ?? 0);
        $partyTransferDeduction = min($this->calculatePartyTransferDeduction($data), $grandTotal);

        return [
            'type' => 'proforma',
            'party_id' => $data['party_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'bill_number' => $data['bill_number'],
            'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
            'order_date' => $data['order_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'deal_days' => $data['deal_days'] ?? 0,
            'total_qty' => $data['total_qty'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'discount_pct' => $data['discount_pct'] ?? 0,
            'discount_rs' => $data['discount_rs'] ?? 0,
            'tax_pct' => $data['tax_pct'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'round_off' => $data['round_off'] ?? 0,
            'grand_total' => $grandTotal,
            'received_amount' => 0,
            'balance' => max(0, $grandTotal - $partyTransferDeduction),
            'status' => 'open',
            'description' => $data['description'] ?? null,
            'image_path' => $imagePaths[0] ?? ($data['image_path'] ?? null),
            'document_path' => $documentPaths[0] ?? ($data['document_path'] ?? null),
            'image_paths' => !empty($imagePaths) ? array_values($imagePaths) : null,
            'document_paths' => !empty($documentPaths) ? array_values($documentPaths) : null,
            'invoice_theme' => $data['invoice_theme'] ?? ($existingSale?->invoice_theme ?? $this->defaultInvoiceTheme()),
        ];
    }

    private function defaultInvoiceTheme(): array
    {
        return [
            'mode' => 'regular',
            'regularThemeId' => 1,
            'thermalThemeId' => 1,
            'accent' => '#1f4e79',
            'accent2' => '#ff981f',
        ];
    }

    private function syncDetails(Sale $sale, array $data): void
    {
        $sale->details()->updateOrCreate(
            ['sale_id' => $sale->id],
            [
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'city' => $data['city'] ?? null,
                'party_no' => $data['party_no'] ?? null,
                'goods_name' => $data['goods_name'] ?? null,
                'details_extra' => $data['details_extra'] ?? null,
                'bilti_gari_no' => $data['bilti_gari_no'] ?? null,
                'custom_expenses' => $data['custom_expenses'] ?? null,
            ]
        );
    }

    private function normalizeJsonInputs(Request $request): void
    {
        foreach (['items', 'image_paths', 'document_paths', 'custom_expenses'] as $field) {
            $value = $request->input($field);
            if (! is_string($value)) {
                continue;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge([$field => $decoded]);
            }
        }
    }

    private function calculatePartyTransferDeduction(array $data): float
    {
        return collect($this->normalizeAdjustmentRows($data['custom_expenses'] ?? []))
            ->filter(function (array $row) {
                return in_array($row['mode'] ?? null, ['-', 'S'], true)
                    && (float) ($row['amount'] ?? 0) > 0;
            })
            ->sum(fn (array $row) => (float) ($row['amount'] ?? 0));
    }

    private function normalizeAdjustmentRows($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];

        foreach (array_values($rows) as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $mode = strtoupper((string) ($row['mode'] ?? $row['operator'] ?? '+'));
            $mode = in_array($mode, ['+', '-', 'S'], true) ? $mode : '+';
            $amount = isset($row['amount'])
                ? (float) $row['amount']
                : (isset($row['value']) ? (float) $row['value'] : 0);

            $normalized[] = [
                'mode' => $mode,
                'amount' => round(max(0, $amount), 2),
                'sort_order' => $index,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     * @return array<int, string>
     */
    private function storeAttachmentFiles($files, string $directory): array
    {
        $storedPaths = [];

        foreach ((array) $files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $storedPaths[] = $file->store($directory, 'public');
        }

        return $storedPaths;
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
}
