<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AppSetting;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Broker;
use App\Models\Cheque;
use App\Support\TransactionNumberPrefix;
use App\Models\Item;
use App\Models\Party;
use App\Models\PartyGroup;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleItem;
use App\Models\SaleTermsCondition;
use App\Models\Transaction;
use App\Models\TransactionAdjustment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $saleFormSettings = $this->getSaleFormSettings();
        $salesQuery = Sale::with(['payments', 'bankAccount', 'party'])
            ->where('type', 'invoice')
            ->whereNotIn('status', ['returned']);

        $period = (string) $request->query('period', 'all');
        $firm = (string) $request->query('firm', '');
        $from = $request->query('from');
        $to = $request->query('to');

        if ($firm !== '') {
            $salesQuery->whereHas('party', function ($query) use ($firm) {
                $query->where('name', $firm);
            });
        }

        $today = now();
        if ($period === 'this_month') {
            $salesQuery->whereDate('invoice_date', '>=', $today->copy()->startOfMonth()->toDateString())
                ->whereDate('invoice_date', '<=', $today->copy()->endOfMonth()->toDateString());
        } elseif ($period === 'last_month') {
            $last = $today->copy()->subMonth();
            $salesQuery->whereDate('invoice_date', '>=', $last->copy()->startOfMonth()->toDateString())
                ->whereDate('invoice_date', '<=', $last->copy()->endOfMonth()->toDateString());
        } elseif ($period === 'this_quarter') {
            $start = $today->copy()->firstOfQuarter();
            $end = $today->copy()->lastOfQuarter();
            $salesQuery->whereDate('invoice_date', '>=', $start->toDateString())
                ->whereDate('invoice_date', '<=', $end->toDateString());
        } elseif ($period === 'this_year') {
            $salesQuery->whereDate('invoice_date', '>=', $today->copy()->startOfYear()->toDateString())
                ->whereDate('invoice_date', '<=', $today->copy()->endOfYear()->toDateString());
        } elseif ($period === 'custom' && $from && $to) {
            $salesQuery->whereDate('invoice_date', '>=', $from)
                ->whereDate('invoice_date', '<=', $to);
        }

        if ($request->boolean('overdue')) {
            $salesQuery
                ->where('balance', '>', 0)
                ->whereDate('due_date', '<', now()->toDateString());
        }

        $sales = $salesQuery
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('dashboard.sales.sale_index', [
            'sales' => $sales,
            'showOverdueOnly' => $request->boolean('overdue'),
            'period' => $period,
            'firm' => $firm,
            'from' => $from,
            'to' => $to,
            'transactionPasscodeEnabled' => !empty(data_get($saleFormSettings, 'more_transaction_features.passcode_enabled'))
                && filled(data_get($saleFormSettings, 'more_transaction_features.transaction_passcode_hash')),
        ]);
    }

    public function storeInvoiceTheme(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'mode' => 'required|in:regular,thermal',
            'regularThemeId' => 'nullable|integer|min:1',
            'thermalThemeId' => 'nullable|integer|min:1',
            'accent' => 'nullable|string|max:30',
            'accent2' => 'nullable|string|max:30',
        ]);

        $currentTheme = $sale->invoice_theme;
        if (is_string($currentTheme)) {
            $currentTheme = json_decode($currentTheme, true);
        }
        $currentTheme = is_array($currentTheme) ? $currentTheme : [];

        $sale->forceFill([
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
            'invoice_theme' => $sale->invoice_theme,
        ]);
    }

    public function create(Request $request, string $type = 'invoice')
    {
        
        $type = (string) $request->query('type', $type ?: 'invoice');
        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $brokers = Broker::orderBy('name')->get();

        // Product/Item items for sale
        $saleItemsSource = Item::with('category')
            ->active()
            ->where(function ($query) {
                $query->where('type', 'product')
                    ->orWhereNull('type');
            })
            ->orderBy('name')
            ->get();

        // Service items for sale
        $serviceItemsSource = Item::with('category')
            ->active()
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $parties = Party::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $saleCategoryOptions = $categories->pluck('name')->toArray();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $customerPoDetailsEnabled = \App\Models\AppSetting::getValue('transaction_customer_po_enabled', '0') === '1';
        $saleFormSettings = $this->getSaleFormSettings();
        $itemFormSettings = $this->getItemFormSettings();
        $termsConditionTemplates = $this->getTermsConditionTemplates();

        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $prefixTypeMap = [
            'invoice' => 'invoice',
            'estimate' => 'estimate',
            'sale_order' => 'sale_order',
            'proforma' => 'proforma_invoice',
            'delivery_challan' => 'delivery_challan',
            'sale_return' => 'credit_note',
            'pos' => 'invoice',
        ];
        $nextInvoiceNumber = TransactionNumberPrefix::format($prefixTypeMap[$type] ?? 'invoice', $nextSaleId);

        $convertedSaleData = null;
        if ($request->filled('duplicate_sale_id')) {
            $sourceSale = Sale::with(['items', 'payments', 'party', 'details'])->findOrFail($request->integer('duplicate_sale_id'));
            $convertedSaleData = $sourceSale->toArray();
            $convertedSaleData['bill_number'] = $nextInvoiceNumber;
            $convertedSaleData['invoice_date'] = now()->toDateString();
            $convertedSaleData['order_date'] = $sourceSale->order_date?->format('Y-m-d') ?: now()->toDateString();
            $convertedSaleData['deal_days'] = $sourceSale->deal_days ?? 0;
            $convertedSaleData['due_date'] = $sourceSale->due_date?->format('Y-m-d') ?: now()->toDateString();
            $convertedSaleData['received_amount'] = 0;
            $convertedSaleData['balance'] = $sourceSale->grand_total ?? $sourceSale->total_amount ?? 0;
            $convertedSaleData['status'] = $type === 'estimate' ? 'draft' : 'unpaid';
            $convertedSaleData['payments'] = [];
        }

        return view('dashboard.sales.create', compact('bankAccounts', 'brokers', 'saleItemsSource', 'serviceItemsSource', 'saleCategoryOptions', 'parties', 'partyGroups', 'categories', 'warehouses', 'customerPoDetailsEnabled', 'saleFormSettings', 'itemFormSettings', 'termsConditionTemplates', 'nextInvoiceNumber', 'type', 'convertedSaleData'));
    }

    public function createFromEstimate(Sale $sale)
    {
        if ($sale->type !== 'estimate') {
            abort(404);
        }

        if ($sale->status === 'converted') {
            return redirect()
                ->route('sale.estimate')
                ->with('error', 'This estimate is already converted to sale invoice #' . ($sale->reference_id ?? ''));
        }

        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $brokers = Broker::orderBy('name')->get();

        // Product/Item items for sale
        $saleItemsSource = Item::active()->orderBy('name')->get();

        // Service items for sale
        $serviceItemsSource = Item::with('category')
            ->active()
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $parties = Party::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $saleCategoryOptions = $categories->pluck('name')->toArray();
        $saleFormSettings = $this->getSaleFormSettings();
        $itemFormSettings = $this->getItemFormSettings();
        $termsConditionTemplates = $this->getTermsConditionTemplates();

        $sale->load(['items', 'details', 'challanDetail', 'party']);

        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $nextInvoiceNumber = TransactionNumberPrefix::format('invoice', $nextSaleId);
        $convertedSaleData = $this->mapEstimateToSaleDraft($sale, $nextInvoiceNumber);
        $type = 'invoice';

        return view('dashboard.sales.create', compact(
            'bankAccounts',
            'brokers',
            'saleItemsSource',
            'serviceItemsSource',
            'saleCategoryOptions',
            'parties',
            'partyGroups',
            'categories',
            'saleFormSettings',
            'itemFormSettings',
            'termsConditionTemplates',
            'nextInvoiceNumber',
            'convertedSaleData',
            'type'
        ));
    }

    public function createFromSaleOrder(Sale $sale)
    {
     
        if ($sale->type !== 'sale_order') {
            abort(404);
        }

        if ($sale->status === 'completed') {
            return redirect()
                ->route('sale-order')
                ->with('error', 'This sale order is already converted to invoice #' . ($sale->reference_id ?? ''));
        }

        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $brokers = Broker::orderBy('name')->get();

        // Product/Item items for sale
        $saleItemsSource = Item::active()->orderBy('name')->get();

        // Service items for sale
        $serviceItemsSource = Item::with('category')
            ->active()
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $parties = Party::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $saleCategoryOptions = $categories->pluck('name')->toArray();
        $saleFormSettings = $this->getSaleFormSettings();
        $itemFormSettings = $this->getItemFormSettings();
        $termsConditionTemplates = $this->getTermsConditionTemplates();

        $sale->load(['items', 'details']);

        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $nextInvoiceNumber = TransactionNumberPrefix::format('invoice', $nextSaleId);
        $convertedSaleData = $this->mapSaleOrderToSaleDraft($sale, $nextInvoiceNumber);
        $type = 'invoice';
        // return view('dashboard.saleorder.create-sale-order', compact(

        return view('dashboard.sales.create', compact(
            'bankAccounts',
            'brokers',
            'saleItemsSource',
            'serviceItemsSource',
            'saleCategoryOptions',
            'parties',
            'partyGroups',
            'categories',
            'saleFormSettings',
            'itemFormSettings',
            'termsConditionTemplates',
            'nextInvoiceNumber',
            'convertedSaleData',
            'type'
        ));
    }

    public function bulkConvertSaleOrders(Request $request)
    {
        $data = $request->validate([
            'sale_order_ids' => 'required|array|min:1',
            'sale_order_ids.*' => 'integer|exists:sales,id',
        ]);

        $saleOrders = Sale::with(['items', 'payments'])
            ->where('type', 'sale_order')
            ->whereIn('id', $data['sale_order_ids'])
            ->get();

        if ($saleOrders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No sale orders found for conversion.',
            ], 422);
        }

        $createdSaleIds = [];

        DB::transaction(function () use ($saleOrders, &$createdSaleIds) {
            $nextSaleId = (Sale::max('id') ?? 0) + 1;

            foreach ($saleOrders as $saleOrder) {
                if ($saleOrder->status === 'completed') {
                    continue;
                }

                $nextInvoiceNumber = TransactionNumberPrefix::format('invoice', $nextSaleId);
                $nextSaleId += 1;

                $draft = $this->mapSaleOrderToSaleDraft($saleOrder, $nextInvoiceNumber);

                $paymentsReceived = (float) $saleOrder->payments->sum('amount');
                $receivedAmount = (float) ($saleOrder->received_amount ?? 0);
                $grandTotal = (float) ($draft['grand_total'] ?? $saleOrder->grand_total ?? 0);
                $balanceStored = (float) ($saleOrder->balance ?? 0);
                $receivedFromBalance = $grandTotal > 0 ? max($grandTotal - $balanceStored, 0) : 0;
                $receivedAmount = max($receivedAmount, $paymentsReceived, $receivedFromBalance);
                $balance = max(0, $grandTotal - $receivedAmount);

                $sale = Sale::create([
                    'type' => 'invoice',
                    'party_id' => $draft['party_id'] ?? $saleOrder->party_id,
                    'broker_id' => $saleOrder->broker_id,
                    'phone' => $draft['phone'] ?? $saleOrder->phone,
                    'billing_address' => $draft['billing_address'] ?? $saleOrder->billing_address,
                    'shipping_address' => $draft['shipping_address'] ?? $saleOrder->shipping_address,
                    'bill_number' => $draft['bill_number'] ?? $nextInvoiceNumber,
                    'invoice_date' => $draft['invoice_date'] ?? now()->toDateString(),
                    'order_date' => $saleOrder->order_date,
                    'deal_days' => $saleOrder->deal_days,
                    'due_date' => $saleOrder->due_date,
                    'reference_id' => $saleOrder->id,
                    'tadad' => $draft['tadad'] ?? $saleOrder->tadad,
                    'total_wazan' => $draft['total_wazan'] ?? $saleOrder->total_wazan,
                    'safi_wazan' => $draft['safi_wazan'] ?? $saleOrder->safi_wazan,
                    'rate' => $draft['rate'] ?? $saleOrder->rate,
                    'deo' => $draft['deo'] ?? $saleOrder->deo,
                    'total_qty' => $draft['total_qty'] ?? $saleOrder->total_qty,
                    'total_amount' => $draft['total_amount'] ?? $saleOrder->total_amount,
                    'discount_pct' => $draft['discount_pct'] ?? $saleOrder->discount_pct,
                    'discount_rs' => $draft['discount_rs'] ?? $saleOrder->discount_rs,
                    'tax_pct' => $draft['tax_pct'] ?? $saleOrder->tax_pct,
                    'tax_amount' => $draft['tax_amount'] ?? $saleOrder->tax_amount,
                    'round_off' => $draft['round_off'] ?? $saleOrder->round_off,
                    'grand_total' => $grandTotal,
                    'received_amount' => $receivedAmount,
                    'balance' => $balance,
                    'status' => $balance <= 0 ? 'Paid' : 'Unpaid',
                    'description' => $draft['description'] ?? $saleOrder->description,
                    'image_path' => $draft['image_path'] ?? $saleOrder->image_path,
                ]);

                foreach ($draft['items'] as $item) {
                    $itemRecord = $this->resolveSaleItemRecord($item);
                    SaleItem::create($this->buildSaleItemPayload($sale->id, $item, $itemRecord));
                }

                $saleOrder->update(['status' => 'completed']);
                $sale->load('payments');
                $this->syncSaleLedgerEntries($sale, []);
                $this->recalculatePartyLedgerBalances($sale->party_id);

                $createdSaleIds[] = $sale->id;
            }
        });

        if (empty($createdSaleIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected sale orders are already converted.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'created_sale_ids' => $createdSaleIds,
        ]);
    }

    public function createFromDeliveryChallan(Sale $sale)
    {
        if ($sale->type !== 'delivery_challan') {
            abort(404);
        }

        if ($sale->status === 'closed') {
            return redirect()
                ->route('delivery-challan')
                ->with('error', 'This delivery challan is already converted to invoice #' . ($sale->reference_id ?? ''));
        }

        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $brokers = Broker::orderBy('name')->get();

        // Product/Item items for sale
        $saleItemsSource = Item::active()->orderBy('name')->get();

        // Service items for sale
        $serviceItemsSource = Item::with('category')
            ->active()
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $parties = Party::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $saleCategoryOptions = $categories->pluck('name')->toArray();
        $saleFormSettings = $this->getSaleFormSettings();
        $itemFormSettings = $this->getItemFormSettings();
        $termsConditionTemplates = $this->getTermsConditionTemplates();

        $sale->load(['items', 'details']);

        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $nextInvoiceNumber = TransactionNumberPrefix::format('invoice', $nextSaleId);
        $convertedSaleData = $this->mapDeliveryChallanToSaleDraft($sale, $nextInvoiceNumber);
        $type = 'invoice';

        return view('dashboard.sales.create', compact(
            'bankAccounts',
            'brokers',
            'saleItemsSource',
            'serviceItemsSource',
            'saleCategoryOptions',
            'parties',
            'partyGroups',
            'categories',
            'saleFormSettings',
            'itemFormSettings',
            'termsConditionTemplates',
            'nextInvoiceNumber',
            'convertedSaleData',
            'type'
        ));
    }
    public function createFromProforma(Sale $sale)
    {
        if ($sale->type !== 'proforma') {
            abort(404);
        }

        if ($sale->status === 'converted') {
            return redirect()
                ->route('proforma-invoice')
                ->with('error', 'This proforma is already converted to sale invoice #' . ($sale->reference_id ?? ''));
        }

        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $brokers = Broker::orderBy('name')->get();

        // Product/Item items for sale
        $saleItemsSource = Item::active()->orderBy('name')->get();

        // Service items for sale
        $serviceItemsSource = Item::with('category')
            ->active()
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $parties = Party::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $saleCategoryOptions = $categories->pluck('name')->toArray();
        $saleFormSettings = $this->getSaleFormSettings();
        $itemFormSettings = $this->getItemFormSettings();
        $termsConditionTemplates = $this->getTermsConditionTemplates();

        $sale->load(['items', 'details']);

        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $nextInvoiceNumber = TransactionNumberPrefix::format('invoice', $nextSaleId);
        $convertedSaleData = $this->mapProformaToSaleDraft($sale, $nextInvoiceNumber);
        $type = 'invoice';

        return view('dashboard.sales.create', compact(
            'bankAccounts',
            'brokers',
            'saleItemsSource',
            'serviceItemsSource',
            'saleCategoryOptions',
            'parties',
            'partyGroups',
            'categories',
            'saleFormSettings',
            'itemFormSettings',
            'termsConditionTemplates',
            'nextInvoiceNumber',
            'convertedSaleData',
            'type'
        ));
    }

    public function duplicate(Sale $sale)
    {
        if (!in_array($sale->type, ['invoice', 'pos'], true)) {
            abort(404);
        }

        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $brokers = Broker::orderBy('name')->get();

        // Product/Item items for sale
        $saleItemsSource = Item::active()->orderBy('name')->get();

        // Service items for sale
        $serviceItemsSource = Item::with('category')
            ->active()
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $parties = Party::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $saleCategoryOptions = $categories->pluck('name')->toArray();
        $saleFormSettings = $this->getSaleFormSettings();
        $itemFormSettings = $this->getItemFormSettings();
        $termsConditionTemplates = $this->getTermsConditionTemplates();

        $sale->load(['items', 'details']);

        $nextSaleId = (Sale::max('id') ?? 0) + 1;
        $nextInvoiceNumber = TransactionNumberPrefix::format('invoice', $nextSaleId);
        $convertedSaleData = $this->mapInvoiceToSaleDraft($sale, $nextInvoiceNumber);
        $type = $sale->type === 'pos' ? 'pos' : 'invoice';

        return view('dashboard.sales.create', compact(
            'bankAccounts',
            'brokers',
            'saleItemsSource',
            'serviceItemsSource',
            'saleCategoryOptions',
            'parties',
            'partyGroups',
            'categories',
            'saleFormSettings',
            'itemFormSettings',
            'termsConditionTemplates',
            'nextInvoiceNumber',
            'convertedSaleData',
            'type'
        ));
    }

    // ── POS METHODS ─────────────────────────────────────────────────────────────
    // Both pos() and pos1() share the same helper so data is consistent.

    /**
     * Shared data loader for all POS views.
     * Passes:
     *   $items        – all product-type items (for the search/scan bar)
     *   $parties      – all parties/customers (for the customer dropdown)
     *   $paymentModes – available payment method names (for the Payment Mode select)
     */
private function posData(): array
{
    $items = Item::active()
        ->where('type', 'product')
        ->orderBy('name')
        ->get([
            'id', 'name', 'item_code', 'unit',
            'sale_price', 'purchase_price', 'opening_qty',
        ]);

    $parties = Party::orderBy('name')
        ->get(['id', 'name', 'phone']);

    $bankAccounts = BankAccount::active()->orderBy('display_name')->get();

    $paymentModes = collect(['Cash', 'Card', 'UPI', 'Credit'])
        ->merge($bankAccounts->pluck('display_with_account'))
        ->unique()
        ->values()
        ->all();

    return compact('items', 'parties', 'paymentModes', 'bankAccounts');
}

    /**
     * Primary POS route (used by the tab-based POS blade).
     */
    public function pos()
    {
        return view('dashboard.sales.pos', $this->posData());
    }

    /**
     * Alias / legacy POS route – identical data, same view.
     */
    public function pos1()
    {
        return view('dashboard.sales.pos', $this->posData());
    }
    // ────────────────────────────────────────────────────────────────────────────

    public function edit(Sale $sale)
    {
        if ($sale->type === 'invoice' && strtolower((string) $sale->status) === 'cancelled') {
            return redirect()
                ->route('sale.index')
                ->with('error', 'Cancelled invoice cannot be edited.');
        }

        $bankAccounts = BankAccount::active()->orderBy('display_name')->get();
        $brokers = Broker::orderBy('name')->get();

        // Product/Item items for sale
        $saleItemsSource = Item::active()->orderBy('name')->get();

        // Service items for sale
        $serviceItemsSource = Item::with('category')
            ->active()
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $parties = Party::orderBy('name')->get();
        $partyGroups = PartyGroup::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $saleCategoryOptions = $categories->pluck('name')->toArray();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $customerPoDetailsEnabled = \App\Models\AppSetting::getValue('transaction_customer_po_enabled', '0') === '1';
        $saleFormSettings = $this->getSaleFormSettings();
        $itemFormSettings = $this->getItemFormSettings();
        $termsConditionTemplates = $this->getTermsConditionTemplates();
        $type = $sale->type ?? 'invoice';

        $sale->load(['items', 'payments', 'party', 'details']);

        $editSaleData = $sale->toArray();
        $editSaleData['party_name'] = $sale->display_party_name;
        $editSaleData['party'] = $sale->party ? $sale->party->toArray() : null;
        $editSaleData['details'] = $sale->details ? $sale->details->toArray() : null;
        $editSaleData['items'] = $sale->items->map(function ($item) {
            return [
                'id' => $item->id,
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
                'tax_pct' => $item->tax_pct ?? 0,
                'tax_amount' => $item->tax_amount ?? 0,
                'free_qty' => $item->free_qty ?? 0,
                'amount' => $item->amount,
                'extra_fields' => $item->extra_fields ?? [],
            ];
        })->values()->all();
        $editSaleData['payments'] = $sale->payments->map(function ($payment) {
            return $payment->toArray();
        })->values()->all();

        $ledgerTransaction = Transaction::query()
            ->where('party_id', $sale->party_id)
            ->where('number', $sale->bill_number ?: (string) $sale->id)
            ->latest('id')
            ->first();

        if ($ledgerTransaction) {
            $sale->setAttribute('labour', $ledgerTransaction->labour);
            $sale->setAttribute('bardana', $ledgerTransaction->bardana);
            $sale->setAttribute('rehra_mazdori', $ledgerTransaction->rehra_mazdori);
            $sale->setAttribute('post_expense', $ledgerTransaction->post_expense);
            $sale->setAttribute('extra_expense', $ledgerTransaction->extra_expense);
        }

        // Provide full URLs for existing image/document if stored in the public disk
        if ($sale->image_path) {
            $sale->image_url = Storage::disk('public')->url($sale->image_path);
        }
        if ($sale->document_path) {
            $sale->document_url = Storage::disk('public')->url($sale->document_path);
        }
        return view('dashboard.sales.create', compact('bankAccounts', 'brokers', 'saleItemsSource', 'serviceItemsSource', 'saleCategoryOptions', 'parties', 'partyGroups', 'categories', 'warehouses', 'customerPoDetailsEnabled', 'saleFormSettings', 'itemFormSettings', 'termsConditionTemplates', 'editSaleData', 'sale', 'type'));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->normalizeSaleRequestPayload($request);

        // Validate incoming data (same as store)
        $data = $request->validate([
            'type' => 'nullable|in:invoice,estimate,sale_order,proforma,delivery_challan,sale_return,pos',
            'source_estimate_id' => 'nullable|exists:sales,id',
            'source_sale_order_id' => 'nullable|exists:sales,id',
            'source_challan_id' => 'nullable|exists:sales,id',
            'source_proforma_id' => 'nullable|exists:sales,id',
            'party_id' => 'nullable|exists:parties,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'brokerage_type' => 'nullable|in:broker_rate,full,half,custom_pct,fixed_rs,per_kg',
            'brokerage_rate' => 'nullable|numeric|min:0',
            'broker_amount' => 'nullable|numeric|min:0',
            'phone' => 'nullable|string|max:50',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'delivery_person' => 'nullable|string|max:255',
            'bilti_no' => 'nullable|string|max:255',
            'gate_no' => 'nullable|string|max:255',
            'po_no' => 'nullable|string|max:255',
            'po_date' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'party_no' => 'nullable|string|max:255',
            'goods_name' => 'nullable|string|max:255',
            'details_extra' => 'nullable|string|max:255',
            'bilti_gari_no' => 'nullable|string|max:255',
            'terms_condition_name' => 'nullable|string|max:255',
            'terms_condition_text' => 'nullable|string',
            'terms_condition_templates' => 'nullable|array',
            'terms_condition_templates.*.name' => 'nullable|string|max:255',
            'terms_condition_templates.*.description' => 'nullable|string',
            'terms_condition_templates.*.applicable_for' => 'nullable|array',
            'invoice_extra_fields' => 'nullable|array',
            'invoice_extra_fields.custom_field_1' => 'nullable|string|max:255',
            'invoice_extra_fields.date_field_2' => 'nullable|string|max:255',
            'payment_term_name' => 'nullable|string|max:255',
            'payment_term_days' => 'nullable|integer|min:0',
            'additional_charges' => 'nullable|array',
            'transportation_details' => 'nullable|array',
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
            'billing_address' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:1000',
            'bill_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'order_date' => 'nullable|date',
            'deal_days' => 'nullable|integer|min:0',
            'due_date' => 'nullable|date',
            'tadad' => 'nullable|integer|min:0',
            'total_wazan' => 'nullable|numeric|min:0',
            'safi_wazan' => 'nullable|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'deo' => 'nullable|numeric|min:0',
            'total_qty' => 'nullable|integer|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'labour' => 'nullable|numeric|min:0',
            'bardana' => 'nullable|numeric|min:0',
            'rehra_mazdori' => 'nullable|numeric|min:0',
            'post_expense' => 'nullable|numeric|min:0',
            'extra_expense' => 'nullable|numeric|min:0',
            'discount_pct' => 'nullable|numeric|min:0',
            'discount_rs' => 'nullable|numeric|min:0',
            'tax_pct' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'grand_total' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
            'document_path' => 'nullable|string|max:255',
            'image_paths' => 'nullable|array',
            'image_paths.*' => 'nullable|string|max:255',
            'document_paths' => 'nullable|array',
            'document_paths.*' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
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
            'items.*.tax_pct' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.free_qty' => 'nullable|numeric|min:0',
            'items.*.extra_fields' => 'nullable|array',
            'items.*.amount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.payment_type' => 'required|string|max:50',
            'payments.*.direction' => 'nullable|in:payment_in,payment_out',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string|max:255',
        ]);

        // When updating, treat payment entries as additional payments (keep previous received amount)
        $existingReceived = floatval($sale->received_amount ?? 0);
        $receivedAmount = $existingReceived;
        if (!empty($data['payments']) && is_array($data['payments'])) {
            foreach ($data['payments'] as $payment) {
                $receivedAmount += floatval($payment['amount'] ?? 0);
            }
        }

        $type = $data['type'] ?? $sale->type ?? 'invoice';
        $grandTotal = floatval($data['grand_total'] ?? 0);
        $balance = max(0, $grandTotal - $receivedAmount);
        $invoiceDate = !empty($data['invoice_date'])
            ? Carbon::parse($data['invoice_date'])
            : ($sale->invoice_date ? Carbon::parse($sale->invoice_date) : now());
        $orderDate = !empty($data['order_date'])
            ? Carbon::parse($data['order_date'])
            : $invoiceDate->copy();
        $dealDays = max(0, intval($data['deal_days'] ?? 0));
        $dueDate = !empty($data['due_date'])
            ? Carbon::parse($data['due_date'])
            : $orderDate->copy()->addDays($dealDays);
        $status = $this->resolveStatusForType(
            $type,
            $receivedAmount,
            $grandTotal,
            $data['status'] ?? null,
            $sale->status
        );
        $resolvedBroker = $this->resolveBrokerSnapshotFromAdjustments($data);
        $brokerId = $resolvedBroker['broker_id'] ?? ($data['broker_id'] ?? $sale->broker_id);
        $brokerAmount = $resolvedBroker['broker_amount'] ?? ($data['broker_amount'] ?? 0);

        $uploadedImagePaths = $this->storeSaleAttachmentFiles($request->file('images', []), 'sales/images');
        $uploadedDocumentPaths = $this->storeSaleAttachmentFiles($request->file('documents', []), 'sales/documents');
        $existingImagePaths = array_values(array_filter(array_merge($sale->image_paths ?? [], $uploadedImagePaths)));
        $existingDocumentPaths = array_values(array_filter(array_merge($sale->document_paths ?? [], $uploadedDocumentPaths)));

        $sale->update([
            'type' => $type,
            'party_id' => $data['party_id'] ?? $sale->party_id,
            'broker_id' => $brokerId,
            'brokerage_type' => $data['brokerage_type'] ?? null,
            'brokerage_rate' => $data['brokerage_rate'] ?? 0,
            'broker_amount' => $brokerAmount,
            'phone' => $data['phone'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'bill_number' => $data['bill_number'] ?? $sale->bill_number,
            'invoice_date' => $invoiceDate->toDateString(),
            'order_date' => $orderDate->toDateString(),
            'deal_days' => $dealDays,
            'due_date' => $dueDate->toDateString(),
            'tadad' => $data['tadad'] ?? 0,
            'total_wazan' => $data['total_wazan'] ?? 0,
            'safi_wazan' => $data['safi_wazan'] ?? 0,
            'rate' => $data['rate'] ?? 0,
            'deo' => $data['deo'] ?? 0,
            'total_qty' => $data['total_qty'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'discount_pct' => $data['discount_pct'] ?? 0,
            'discount_rs' => $data['discount_rs'] ?? 0,
            'tax_pct' => $data['tax_pct'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'round_off' => $data['round_off'] ?? 0,
            'grand_total' => $grandTotal,
            'received_amount' => $receivedAmount,
            'balance' => $balance,
            'status' => $status,
            'description' => $data['description'] ?? null,
            'image_path' => $existingImagePaths[0] ?? $sale->image_path,
            'document_path' => $existingDocumentPaths[0] ?? $sale->document_path,
            'image_paths' => $existingImagePaths ?: null,
            'document_paths' => $existingDocumentPaths ?: null,
        ]);

        $this->syncTermsConditionTemplates($data);
        $this->upsertSaleDetails($sale, $data);

        // Replace items (keep payment history intact for edit mode)
        $sale->items()->delete();
        foreach ($data['items'] as $item) {
    $itemRecord = $this->resolveSaleItemRecord($item);

    $sale->items()->create($this->buildSaleItemPayload($sale->id, $item, $itemRecord, true));
        }

        // Append new payments (maintain payment history; edit mode treats payments as additional amounts)
        if (!empty($data['payments']) && is_array($data['payments'])) {
            foreach ($data['payments'] as $payment) {
                $paymentAmount = floatval($payment['amount'] ?? 0);
                $rawPaymentType = (string) ($payment['payment_type'] ?? '');
                $normalizedPaymentType = strtolower($rawPaymentType);
                $isCash = $normalizedPaymentType === 'cash';
                $bankId = $payment['bank_account_id'] ?? null;
                $storePaymentType = $isCash ? 'cash' : $rawPaymentType;

               if ($paymentAmount <= 0) {
    continue;
}

$isCheque = in_array(strtolower($rawPaymentType), ['cheque', 'cheques']);
if (!$isCash && !$isCheque && empty($bankId)) {
    continue;
}

$direction = $this->normalizePaymentDirection($payment['direction'] ?? null);

                $cashAccount = null;
                if ($isCash) {
                    $cashAccount = BankAccount::cashAccount();
                    $bankId = $cashAccount->id;
                }

               $sale->payments()->create([
    'payment_type' => $storePaymentType,
    'direction' => $direction,
    'bank_account_id' => $bankId,
    'amount' => $paymentAmount,
    'reference' => $payment['reference'] ?? null,
]);

if ($isCheque) {
    $this->storeSaleCheque($sale, $payment, $paymentAmount, $bankId);
}

$bank = $isCash ? $cashAccount : BankAccount::find($bankId);

                if ($bank) {
                    $bank->opening_balance = ($bank->opening_balance ?? 0)
                        + ($direction === 'payment_out' ? -1 * $paymentAmount : $paymentAmount);
                    $bank->save();

                    $transactionType = $isCash
                        ? ($direction === 'payment_out' ? 'cash_out' : 'cash_in')
                        : ($direction === 'payment_out' ? 'sale_payment_out' : 'sale_payment');

                    $transactionDescription = $isCash
                        ? ($direction === 'payment_out'
                            ? 'Cash paid for invoice #' . ($sale->bill_number ?: $sale->id)
                            : 'Cash received for invoice #' . ($sale->bill_number ?: $sale->id))
                        : ($direction === 'payment_out'
                            ? 'Payment paid to party for invoice #' . ($sale->bill_number ?: $sale->id)
                            : 'Sale payment received for invoice #' . ($sale->bill_number ?: $sale->id));

                    BankTransaction::create([
                        'from_bank_account_id' => $bank->id,
                        'to_bank_account_id' => null,
                        'type' => $transactionType,
                        'amount' => $paymentAmount,
                        'transaction_date' => $sale->invoice_date ?? now()->toDateString(),
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'description' => $transactionDescription,
                        'meta' => [
                            'party_id' => $sale->party_id,
                            'payment_type' => $storePaymentType,
                            'reference' => $payment['reference'] ?? null,
                            'direction' => $direction,
                        ],
                    ]);
                }

            }
        }

        $sale->load('payments');
        $this->syncSaleLedgerEntries($sale, $data);
        $this->recalculatePartyLedgerBalances($sale->party_id);

        $requestType = (string) $request->input('type', $sale->type ?? 'invoice');
        $fromSaleOrder = $request->boolean('from_sale_order') || $requestType === 'sale_order';

        $redirectUrl = match (true) {
            $requestType === 'estimate' => route('sale.estimate'),
            $requestType === 'sale_return' => route('invoice', [
                'sale_id' => $sale->id,
                'type' => 'return-order',
            ]),
            $fromSaleOrder => route('sale-order'),
            default => route('sale.index'),
        };

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => $redirectUrl,
            'share_url' => $requestType === 'sale_return'
                ? route('invoice', [
                    'sale_id' => $sale->id,
                    'type' => 'return-order',
                ])
                : route('invoice', ['sale_id' => $sale->id]),
        ]);
    }

    public function store(Request $request)
    {   
        
        $this->normalizeSaleRequestPayload($request);

        $data = $request->validate([
            'type' => 'nullable|in:invoice,estimate,sale_order,proforma,delivery_challan,sale_return,pos',
            'source_estimate_id' => 'nullable|exists:sales,id',
            'source_sale_order_id' => 'nullable|exists:sales,id',
            'source_challan_id' => 'nullable|exists:sales,id',
            'source_proforma_id' => 'nullable|exists:sales,id',
            'party_id' => 'nullable|exists:parties,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'brokerage_type' => 'nullable|in:broker_rate,full,half,custom_pct,fixed_rs,per_kg',
            'brokerage_rate' => 'nullable|numeric|min:0',
            'broker_amount' => 'nullable|numeric|min:0',
            'phone' => 'nullable|string|max:50',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'delivery_person' => 'nullable|string|max:255',
            'bilti_no' => 'nullable|string|max:255',
            'gate_no' => 'nullable|string|max:255',
            'po_no' => 'nullable|string|max:255',
            'po_date' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'party_no' => 'nullable|string|max:255',
            'goods_name' => 'nullable|string|max:255',
            'details_extra' => 'nullable|string|max:255',
            'bilti_gari_no' => 'nullable|string|max:255',
            'terms_condition_name' => 'nullable|string|max:255',
            'terms_condition_text' => 'nullable|string',
            'terms_condition_templates' => 'nullable|array',
            'terms_condition_templates.*.name' => 'nullable|string|max:255',
            'terms_condition_templates.*.description' => 'nullable|string',
            'terms_condition_templates.*.applicable_for' => 'nullable|array',
            'invoice_extra_fields' => 'nullable|array',
            'invoice_extra_fields.custom_field_1' => 'nullable|string|max:255',
            'invoice_extra_fields.date_field_2' => 'nullable|string|max:255',
            'payment_term_name' => 'nullable|string|max:255',
            'payment_term_days' => 'nullable|integer|min:0',
            'additional_charges' => 'nullable|array',
            'transportation_details' => 'nullable|array',
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
            'billing_address' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:1000',
            'bill_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'order_date' => 'nullable|date',
            'deal_days' => 'nullable|integer|min:0',
            'due_date' => 'nullable|date',
            'tadad' => 'nullable|integer|min:0',
            'total_wazan' => 'nullable|numeric|min:0',
            'safi_wazan' => 'nullable|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'deo' => 'nullable|numeric|min:0',
            'total_qty' => 'nullable|integer|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'labour' => 'nullable|numeric|min:0',
            'bardana' => 'nullable|numeric|min:0',
            'rehra_mazdori' => 'nullable|numeric|min:0',
            'post_expense' => 'nullable|numeric|min:0',
            'extra_expense' => 'nullable|numeric|min:0',
            'discount_pct' => 'nullable|numeric|min:0',
            'discount_rs' => 'nullable|numeric|min:0',
            'tax_pct' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'grand_total' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
            'document_path' => 'nullable|string|max:255',
            'image_paths' => 'nullable|array',
            'image_paths.*' => 'nullable|string|max:255',
            'document_paths' => 'nullable|array',
            'document_paths.*' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
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
            'items.*.tax_pct' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.free_qty' => 'nullable|numeric|min:0',
            'items.*.extra_fields' => 'nullable|array',
            'items.*.amount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.payment_type' => 'required|string|max:50',
            'payments.*.direction' => 'nullable|in:payment_in,payment_out',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string|max:255',
        ]);

        $receivedAmount = 0;

        if (!empty($data['payments']) && is_array($data['payments'])) {
            foreach ($data['payments'] as $payment) {
                $receivedAmount += floatval($payment['amount'] ?? 0);
            }
        }

        $type = $data['type'] ?? 'invoice';
    
        $grandTotal = floatval($data['grand_total'] ?? 0);
        $balance = max(0, $grandTotal - $receivedAmount);
        $invoiceDate = !empty($data['invoice_date'])
            ? Carbon::parse($data['invoice_date'])
            : ($type === 'sale_order' ? now() : now());
        $orderDate = !empty($data['order_date'])
            ? Carbon::parse($data['order_date'])
            : $invoiceDate->copy();
        $dealDays = max(0, intval($data['deal_days'] ?? 0));
        $dueDate = !empty($data['due_date'])
            ? Carbon::parse($data['due_date'])
            : $orderDate->copy()->addDays($dealDays);
        $status = $this->resolveStatusForType(
            $type,
            $receivedAmount,
            $grandTotal,
            $data['status'] ?? null
        );
        $resolvedBroker = $this->resolveBrokerSnapshotFromAdjustments($data);
        $brokerId = $resolvedBroker['broker_id'] ?? ($data['broker_id'] ?? null);
        $brokerAmount = $resolvedBroker['broker_amount'] ?? ($data['broker_amount'] ?? 0);

        $sale = Sale::create([
            'type' => $type,
            'party_id' => $data['party_id'] ?? null,
            'broker_id' => $brokerId,
            'brokerage_type' => $data['brokerage_type'] ?? null,
            'brokerage_rate' => $data['brokerage_rate'] ?? 0,
            'broker_amount' => $brokerAmount,
            'phone' => $data['phone'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'bill_number' => $data['bill_number'] ?? null,
            'invoice_date' => $invoiceDate->toDateString(),
            'order_date' => $orderDate->toDateString(),
            'deal_days' => $dealDays,
            'due_date' => $dueDate->toDateString(),
            'tadad' => $data['tadad'] ?? 0,
            'total_wazan' => $data['total_wazan'] ?? 0,
            'safi_wazan' => $data['safi_wazan'] ?? 0,
            'rate' => $data['rate'] ?? 0,
            'deo' => $data['deo'] ?? 0,
            'total_qty' => $data['total_qty'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'discount_pct' => $data['discount_pct'] ?? 0,
            'discount_rs' => $data['discount_rs'] ?? 0,
            'tax_pct' => $data['tax_pct'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'round_off' => $data['round_off'] ?? 0,
            'grand_total' => $grandTotal,
            'received_amount' => $receivedAmount,
            'balance' => $balance,
            'status' => $status,
            'description' => $data['description'] ?? null,
            'image_path' => null,
            'document_path' => null,
            'image_paths' => null,
            'document_paths' => null,
        ]);

        $this->syncTermsConditionTemplates($data);
        $this->upsertSaleDetails($sale, $data);

        $uploadedImagePaths = $this->storeSaleAttachmentFiles($request->file('images', []), 'sales/images');
        $uploadedDocumentPaths = $this->storeSaleAttachmentFiles($request->file('documents', []), 'sales/documents');

        if (!empty($uploadedImagePaths)) {
            $sale->image_path = $uploadedImagePaths[0];
            $sale->image_paths = $uploadedImagePaths;
        }

        if (!empty($uploadedDocumentPaths)) {
            $sale->document_path = $uploadedDocumentPaths[0];
            $sale->document_paths = $uploadedDocumentPaths;
        }

        if ($sale->isDirty(['image_path', 'document_path', 'image_paths', 'document_paths'])) {
            $sale->save();
        }

        // Auto-generate invoice number based on the sale ID if not provided
        if (empty($sale->bill_number)) {
            $sale->bill_number = (string) $sale->id;
            $sale->save();
        }

        foreach ($data['items'] as $item) {
            $itemRecord = $this->resolveSaleItemRecord($item);
            $sale->items()->create($this->buildSaleItemPayload($sale->id, $item, $itemRecord, true));
        }

        if (!empty($data['payments']) && is_array($data['payments'])) {
            foreach ($data['payments'] as $payment) {
                $paymentAmount = floatval($payment['amount'] ?? 0);
                $rawPaymentType = (string) ($payment['payment_type'] ?? '');
                $normalizedPaymentType = strtolower($rawPaymentType);
                $isCash = $normalizedPaymentType === 'cash';
                $bankId = $payment['bank_account_id'] ?? null;
                $storePaymentType = $isCash ? 'cash' : $rawPaymentType;
if ($paymentAmount <= 0) {
    continue;
}

$isCheque = in_array(strtolower($rawPaymentType), ['cheque', 'cheques']);
if (!$isCash && !$isCheque && empty($bankId)) {
    continue;
}

$direction = $this->normalizePaymentDirection($payment['direction'] ?? null);

                $cashAccount = null;
                if ($isCash) {
                    $cashAccount = BankAccount::cashAccount();
                    $bankId = $cashAccount->id;
                }

                $sale->payments()->create([
                    'payment_type' => $storePaymentType,
                    'direction' => $direction,
                    'bank_account_id' => $bankId,
                    'amount' => $paymentAmount,
                    'reference' => $payment['reference'] ?? null,
                ]);
                if ($isCheque) {
                    $this->storeSaleCheque($sale, $payment, $paymentAmount, $bankId);
                }

$bank = $isCash ? $cashAccount : BankAccount::find($bankId);


                if ($bank) {
                    $bank->opening_balance = ($bank->opening_balance ?? 0)
                        + ($direction === 'payment_out' ? -1 * $paymentAmount : $paymentAmount);
                    $bank->save();

                    $transactionType = $isCash
                        ? ($direction === 'payment_out' ? 'cash_out' : 'cash_in')
                        : ($direction === 'payment_out' ? 'sale_payment_out' : 'sale_payment');

                    $transactionDescription = $isCash
                        ? ($direction === 'payment_out'
                            ? 'Cash paid for invoice #' . ($sale->bill_number ?: $sale->id)
                            : 'Cash received for invoice #' . ($sale->bill_number ?: $sale->id))
                        : ($direction === 'payment_out'
                            ? 'Payment paid to party for invoice #' . ($sale->bill_number ?: $sale->id)
                            : 'Additional payment received for invoice #' . ($sale->bill_number ?: $sale->id));

                    BankTransaction::create([
                        'from_bank_account_id' => $bank->id,
                        'to_bank_account_id' => null,
                        'type' => $transactionType,
                        'amount' => $paymentAmount,
                        'transaction_date' => $sale->invoice_date ?? now()->toDateString(),
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'description' => $transactionDescription,
                        'meta' => [
                            'party_id' => $sale->party_id,
                            'payment_type' => $storePaymentType,
                            'reference' => $payment['reference'] ?? null,
                            'direction' => $direction,
                        ],
                    ]);
                }

            }
        }

        $sale->load('payments');
        $this->syncSaleLedgerEntries($sale, $data);
        $this->recalculatePartyLedgerBalances($sale->party_id);

        if (!empty($data['source_estimate_id'])) {
            Sale::whereKey($data['source_estimate_id'])
                ->where('type', 'estimate')
                ->update(['status' => 'converted']);

            $sale->reference_id = $data['source_estimate_id'];
            $sale->save();
        }

        if (!empty($data['source_sale_order_id'])) {
            Sale::whereKey($data['source_sale_order_id'])
                ->where('type', 'sale_order')
                ->update(['status' => 'completed']);

            $sale->reference_id = $data['source_sale_order_id'];
            $sale->save();
        }

        if (!empty($data['source_challan_id'])) {
            Sale::whereKey($data['source_challan_id'])
                ->where('type', 'delivery_challan')
                ->update(['status' => 'closed']);

            $sale->reference_id = $data['source_challan_id'];
            $sale->save();
        }

        if (!empty($data['source_proforma_id'])) {
            Sale::whereKey($data['source_proforma_id'])
                ->where('type', 'proforma')
                ->update([
                    'status' => 'converted',
                ]);

            $sale->reference_id = $data['source_proforma_id'];
            $sale->save();
        }

        $requestType = (string) $request->input('type', $sale->type ?? 'invoice');
        $fromSaleOrder = $request->boolean('from_sale_order') || $requestType === 'sale_order';

        $redirectUrl = match (true) {
            $requestType === 'estimate' => route('invoice', ['sale_id' => $sale->id]),
            $requestType === 'sale_return' => route('invoice', [
                'sale_id' => $sale->id,
                'type' => 'return-order',
            ]),
            $fromSaleOrder => route('sale-order'),
            $requestType === 'proforma' => route('proforma-invoice'),
            default => route('sale.index'),
        };
        // return $redirectUrl;
        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => $redirectUrl,
            'share_url' => $requestType === 'sale_return'
                ? route('invoice', [
                    'sale_id' => $sale->id,
                    'type' => 'return-order',
                ])
                : route('invoice', ['sale_id' => $sale->id]),
        ]);
    }

    public function invoicePreview(Request $request, Sale $sale)
    {
        $sale->loadMissing(['items.item', 'party', 'payments.bankAccount']);
        $invoiceSource = $sale;

        if ($request->query('doc') === 'delivery_challan') {
            if ($sale->type === 'delivery_challan') {
                $invoiceSource = $sale;
            } elseif ($sale->reference_id) {
                $sourceChallan = Sale::with(['items.item', 'party', 'broker', 'challanDetail', 'details', 'payments.bankAccount'])
                    ->whereKey($sale->reference_id)
                    ->where('type', 'delivery_challan')
                    ->first();
                if ($sourceChallan) {
                    $invoiceSource = $sourceChallan;
                }
            }
        }

        $themeDefaults = $this->resolveStoredInvoiceThemeConfig($invoiceSource, $request);
        $themeConfig = $this->resolveInvoiceThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        return view('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $this->mapSaleToThemePreviewData($invoiceSource),
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function invoicePdf(Request $request, Sale $sale)
    {
        $sale->loadMissing(['items.item', 'party', 'payments.bankAccount']);
        $themeDefaults = $this->resolveStoredInvoiceThemeConfig($sale, $request);

        $themeConfig = $this->resolveInvoiceThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        if ($request->boolean('download')) {
            $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', [
                'invoicePreviewData' => $this->mapSaleToThemePreviewData($sale),
                'themeConfig' => $themeConfig,
                'accent' => $themeDefaults['accent'],
                'accent2' => $themeDefaults['accent2'],
            ]);

            if (($themeConfig['mode'] ?? 'regular') === 'thermal') {
                $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
            } else {
                $pdf->setPaper('a4', 'portrait');
            }

            return $pdf->download('invoice-' . ($sale->bill_number ?: $sale->id) . '.pdf');
        }

        $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $this->mapSaleToThemePreviewData($sale),
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
            'autoPrint' => $request->boolean('print'),
        ]);

        if (($themeConfig['mode'] ?? 'regular') === 'thermal') {
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        return $pdf->stream('invoice-' . ($sale->bill_number ?: $sale->id) . '.pdf');
    }

    public function print(Sale $sale)
    {
        $sale->loadMissing(['items.item', 'party', 'payments']);

        return view('dashboard.sales.sale_print', [
            'sale' => $sale,
        ]);
    }

    public function destroy(Sale $sale)
    {
        // Remove related items and payments first to avoid foreign key issues
        $sale->items()->delete();
        $sale->payments()->delete();

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully.',
        ]);
    }

    public function deliveryPreview(Sale $sale)
    {
        abort_unless($sale->type === 'invoice', 404);

        $sale->loadMissing(['items', 'party']);

        return view('dashboard.delivery.challan-preview', [
            'sale' => $sale,
            'previewTitle' => 'Delivery Challan',
            'documentNumberLabel' => 'Invoice No.',
            'documentDateLabel' => 'Date',
            'showRates' => false,
            'showAmount' => false,
        ]);
    }

    public function paymentHistory(Sale $sale)
    {
        abort_unless($sale->type === 'invoice', 404);

        $sale->loadMissing(['payments.bankAccount']);

        return response()->json([
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number ?: $sale->id,
            'grand_total' => (float) ($sale->grand_total ?? 0),
            'received_amount' => (float) ($sale->received_amount ?? 0),
            'balance' => (float) ($sale->balance ?? 0),
            'payments' => $sale->payments->map(function ($payment) use ($sale) {
                $paymentDateTime = $payment->created_at ?: $sale->created_at;
                return [
                    'payment_type' => $payment->payment_type ?: '-',
                    'bank_name' => $payment->bankAccount?->display_name ?: '-',
                    'amount' => (float) ($payment->amount ?? 0),
                    'reference' => $payment->reference ?: '-',
                    'date' => $this->formatPreviewDate($paymentDateTime ?: ($sale->invoice_date ?: $sale->created_at)),
                    'time' => $paymentDateTime ? Carbon::parse($paymentDateTime)->format('h:i A') : '-',
                ];
            })->values(),
        ]);
    }

    public function verifyTransactionPasscode(Request $request)
    {
        $data = $request->validate([
            'passcode' => ['required', 'digits:4'],
        ]);

        $settings = $this->getSaleFormSettings();
        $hash = (string) data_get($settings, 'more_transaction_features.transaction_passcode_hash', '');

        if ($hash === '' || !Hash::check($data['passcode'], $hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid passcode.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Passcode verified.',
        ]);
    }

    private function resolveStoredInvoiceThemeConfig(?Sale $sale, Request $request): array
    {
        $stored = $sale?->invoice_theme;

        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        if (!is_array($stored)) {
            $stored = [];
        }

        $mode = (string) ($request->query('mode', $stored['mode'] ?? 'regular'));
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';

        $regularThemeId = (int) $request->query(
            'theme_id',
            (int) ($stored['regularThemeId'] ?? ($stored['theme_id'] ?? 1))
        );
        $thermalThemeId = (int) ($stored['thermalThemeId'] ?? ($stored['theme_id'] ?? 1));
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

    public function bankHistory(Sale $sale)
    {
        abort_unless($sale->type === 'invoice', 404);

        $sale->loadMissing(['payments.bankAccount']);

        $transactions = BankTransaction::with(['fromBankAccount'])
            ->where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->orderByDesc('transaction_date')
            ->get()
            ->map(function ($transaction) {
                return [
                    'bank_name' => $transaction->fromBankAccount?->display_name ?: '-',
                    'amount' => (float) ($transaction->amount ?? 0),
                    'type' => (string) ($transaction->type ?: 'sale_payment'),
                    'reference' => (string) ($transaction->description ?: '-'),
                    'date' => $this->formatPreviewDate($transaction->transaction_date),
                ];
            });

        if ($transactions->isEmpty()) {
            $transactions = $sale->payments->map(function ($payment) use ($sale) {
                return [
                    'bank_name' => $payment->bankAccount?->display_name ?: '-',
                    'amount' => (float) ($payment->amount ?? 0),
                    'type' => 'sale_payment',
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

    public function reportPreview(Request $request)
    {
        $payload = $this->buildSaleReportPayload($request);

        return response()->view('dashboard.sales.partials.report-preview', $payload);
    }

    public function reportPdf(Request $request)
    {
        $payload = $this->buildSaleReportPayload($request);
        $pdf = Pdf::loadView('dashboard.sales.partials.report-preview', $payload)->setPaper('a4', 'portrait');

        return $pdf->download('sale-report-' . now()->format('Ymd-His') . '.pdf');
    }

    private function buildSaleReportPayload(Request $request): array
    {
        $saleIds = collect(explode(',', (string) $request->query('sale_ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        abort_if($saleIds->isEmpty(), 404, 'No sales selected.');

        $options = [
            'date' => $request->boolean('date', true),
            'invoice_no' => $request->boolean('invoice_no', true),
            'party_name' => $request->boolean('party_name', true),
            'total' => $request->boolean('total', true),
            'payment_type' => $request->boolean('payment_type', true),
            'received_paid' => $request->boolean('received_paid', true),
            'balance_due' => $request->boolean('balance_due', true),
            'item_details' => $request->boolean('item_details', true),
            'description' => $request->boolean('description', true),
            'payment_status' => $request->boolean('payment_status', true),
            'order_number' => $request->boolean('order_number', true),
            'party_phone' => $request->boolean('party_phone', true),
            'payment_breakup' => $request->boolean('payment_breakup', true),
        ];

        $sales = Sale::with(['items', 'party', 'payments.bankAccount', 'details'])
            ->whereIn('id', $saleIds->all())
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (Sale $sale) {
                $paymentTypes = $sale->payments->pluck('payment_type')->filter()->map(fn ($v) => ucfirst((string) $v))->unique()->values();

                return [
                    'id' => $sale->id,
                    'date' => $this->formatPreviewDate($sale->invoice_date ?: $sale->created_at),
                    'order_number' => $sale->reference_id ?: $sale->id,
                    'invoice_no' => $sale->bill_number ?: $sale->id,
                    'party_name' => $sale->party?->name ?: ($sale->display_party_name ?: '-'),
                    'party_phone' => $sale->phone ?: ($sale->party?->phone ?: '-'),
                    'total' => (float) ($sale->grand_total ?? 0),
                    'payment_type' => $paymentTypes->isNotEmpty() ? $paymentTypes->implode(', ') : ((string) ($sale->payment_type ?: '-')),
                    'received_paid' => (float) ($sale->received_amount ?? 0),
                    'balance_due' => (float) ($sale->balance ?? 0),
                    'due_date' => $sale->due_date ? $this->formatPreviewDate($sale->due_date) : '-',
                    'status' => (string) ($sale->status ?: 'Unpaid'),
                    'description' => (string) ($sale->description ?: '-'),
                    'payments' => $sale->payments->map(function ($payment) use ($sale) {
                        return [
                            'type' => ucfirst((string) ($payment->payment_type ?: '-')),
                            'bank' => $payment->bankAccount?->display_name ?: (($payment->payment_type ?: '') === 'cash' ? 'Cash in Hand' : '-'),
                            'amount' => (float) ($payment->amount ?? 0),
                            'reference' => $payment->reference ?: '-',
                            'date' => $this->formatPreviewDate($sale->invoice_date ?: $sale->created_at),
                        ];
                    })->values()->all(),
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'name' => $item->item_name ?: '-',
                            'quantity' => $item->quantity ?? 0,
                            'price' => (float) ($item->unit_price ?? 0),
                            'amount' => (float) ($item->amount ?? 0),
                        ];
                    })->values()->all(),
                ];
            });

        $dates = $sales->pluck('date')->filter()->values();
        $duration = $request->query('duration');
        if (!$duration) {
            $duration = $dates->isNotEmpty()
                ? ('From ' . $dates->last() . ' to ' . $dates->first())
                : '';
        }

        return [
            'sales' => $sales,
            'options' => $options,
            'duration' => $duration,
            'autoPrint' => $request->boolean('print'),
        ];
    }

    public function cancel(Sale $sale)
    {
        abort_unless($sale->type === 'invoice', 404);

        if (strtolower((string) $sale->status) === 'cancelled') {
            return response()->json([
                'success' => true,
                'message' => 'Invoice already cancelled.',
                'status' => 'Cancelled',
            ]);
        }

        $sale->update(['status' => 'Cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Invoice cancelled successfully.',
            'status' => 'Cancelled',
        ]);
    }

    public function previewEstimate(Sale $sale)
    {
        if ($sale->type !== 'estimate') {
            abort(404);
        }

        return redirect()->route('sale.invoice-preview', ['sale' => $sale->id]);
    }

    public function printEstimate(Sale $sale)
    {
        if ($sale->type !== 'estimate') {
            abort(404);
        }

        return redirect()->route('sale.invoice-preview', ['sale' => $sale->id, 'print' => 1]);
    }

    private function mapSaleToThemePreviewData(Sale $sale): array
    {
        $bankAccount = $sale->payments
            ->pluck('bankAccount')
            ->filter()
            ->first();

        if (!$bankAccount) {
            $bankAccount = BankAccount::where('print_on_invoice', true)
                ->orderBy('id')
                ->first();
        }

          $items = $sale->items->map(function ($item) use ($sale) {
              $taxPct = $this->formatPercentValue($sale->tax_pct);
              $amount = (float) ($item->amount ?? 0);
              $rate = (float) ($item->unit_price ?? 0);
              $quantity = (float) ($item->quantity ?? 0);
              $itemDefinitions = collect($item->item?->custom_fields ?? []);
              $customFieldsSource = $item->custom_fields ?? [];
              if (empty($customFieldsSource)) {
                  $extraFields = is_array($item->extra_fields ?? null) ? $item->extra_fields : [];
                  $customFieldsSource = collect(range(1, 6))
                      ->map(function ($index) use ($extraFields) {
                          $value = trim((string) ($extraFields['custom_field_' . $index] ?? ''));
                          if ($value === '') {
                              return null;
                          }

                          return [
                              'key' => 'custom_field_' . $index,
                              'enabled' => true,
                              'label' => 'Custom Field ' . $index,
                              'show_in_print' => true,
                              'value' => $value,
                          ];
                      })
                      ->filter()
                      ->values()
                      ->all();
              }

              $customFields = collect($customFieldsSource ?: $itemDefinitions->all())
                  ->values()
                  ->map(function ($field, $index) use ($itemDefinitions) {
                      $definition = $itemDefinitions->get($index, []);
                      if (is_array($field)) {
                          $label = trim((string) ($field['label'] ?? $field['name'] ?? ''));
                          $definitionLabel = is_array($definition) ? trim((string) ($definition['label'] ?? $definition['name'] ?? '')) : '';
                          if ($label === '' || preg_match('/^Custom Field\s*\d+$/i', $label)) {
                              $label = $definitionLabel;
                          }
                          return [
                              'key' => (string) ($field['key'] ?? ''),
                              'enabled' => (bool) ($field['enabled'] ?? true),
                              'label' => $label,
                              'show_in_print' => (bool) ($field['show_in_print'] ?? true),
                              'value' => trim((string) ($field['value'] ?? $field['text'] ?? '')),
                          ];
                      }

                      $definitionLabel = is_array($definition) ? trim((string) ($definition['label'] ?? $definition['name'] ?? '')) : '';
                      return [
                          'key' => '',
                          'enabled' => true,
                          'label' => $definitionLabel,
                          'show_in_print' => true,
                          'value' => trim((string) $field),
                      ];
                  })
                  ->filter(function (array $field) {
                      return $field['enabled'] && $field['show_in_print'] && ($field['label'] !== '' || $field['value'] !== '');
                  })
                  ->values()
                  ->all();

              if ($amount <= 0 && $quantity > 0 && $rate > 0) {
                  $amount = round($quantity * $rate, 2);
              }

            return [
                'name' => $item->item_name ?: ($item->item?->name ?: 'Item'),
                'hsn' => (string) ($item->item_code ?: ($item->item?->item_code ?: '')),
                'qty' => (string) ($item->quantity ?? 0),
                'tadaat' => (string) ($item->quantity ?? 0),
                'gross_w' => (float) ($item->gross_w ?? 0),
                'net_w' => (float) ($item->net_w ?? 0),
                'unit' => (string) ($item->unit ?: ($item->item?->unit ?: '')),
                'rate' => $rate,
                  'disc' => number_format((float) ($item->discount ?? 0), 2, '.', ''),
                  'gst' => $taxPct,
                  'amt' => $amount,
                  'customFields' => $customFields,
                  'customFieldSummary' => collect($customFields)
                      ->map(function ($field) {
                          if (!is_array($field)) {
                              return trim((string) $field);
                          }

                          $label = trim((string) ($field['label'] ?? ''));
                          $value = trim((string) ($field['value'] ?? ''));
                          if ($label === '' && $value === '') {
                              return '';
                          }

                          return $value !== '' ? ($label !== '' ? $label . ': ' . $value : $value) : $label;
                      })
                      ->filter()
                      ->implode(' | '),
                  'amount' => $amount,
              ];
          })->values()->all();

        if (empty($items)) {
            $items[] = [
                'name' => 'Item',
                'hsn' => '',
                'qty' => '0',
                'tadaat' => '0',
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

        $paymentsReceived = (float) $sale->payments
            ->sum('amount');

        $itemsTotal = (float) collect($items)->sum('amt');
        $subtotal = (float) ($sale->total_amount ?? 0);
        if ($subtotal <= 0) {
            $subtotal = $itemsTotal;
        }

        $totalAmount = (float) ($sale->grand_total ?? 0);
        if ($totalAmount <= 0) {
            $totalAmount = max($subtotal + (float) ($sale->tax_amount ?? 0) - (float) ($sale->discount_rs ?? 0), $itemsTotal);
        }
        $storedBalance = (float) ($sale->balance ?? 0);

        $receivedAmount = (float) ($sale->received_amount ?? 0);
        $receivedFromBalance = $totalAmount > 0 ? max($totalAmount - $storedBalance, 0) : 0;
        $receivedAmount = max($receivedAmount, $paymentsReceived, $receivedFromBalance);

        $partyExtraFields = [];
        foreach ([
            [AppSetting::getValue('party_additional_field_1_name', ''), AppSetting::getValue('party_additional_field_1_print', '0') === '1'],
            [AppSetting::getValue('party_additional_field_2_name', ''), AppSetting::getValue('party_additional_field_2_print', '0') === '1'],
        ] as [$label, $showInPrint]) {
            $label = trim((string) $label);
            if ($label !== '' && $showInPrint) {
                $partyExtraFields[] = $label;
            }
        }

        $partyCustomFields = collect($sale->party?->custom_fields ?? [])
            ->map(function ($field) {
                if (is_array($field)) {
                    $field = $field['label'] ?? $field['value'] ?? $field['name'] ?? '';
                }

                return trim((string) $field);
            })
            ->filter()
            ->values()
            ->all();

        return [
            'title' => $sale->type === 'invoice' ? 'Invoice' : ucwords(str_replace('_', ' ', (string) $sale->type)),
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
            'balance' => (float) ($sale->balance ?? max($totalAmount - $receivedAmount, 0)),
            'totalInWords' => $this->formatAmountInWords($totalAmount),
            'termsText' => trim((string) ($sale->details?->terms_condition_text ?: $sale->description ?: 'Thanks for doing business with us!')),
            'bankName' => (string) ($bankAccount?->bank_name ?: $bankAccount?->display_name ?: ''),
            'bankAccountNumber' => (string) ($bankAccount?->account_number ?: ''),
            'bankAccountHolder' => (string) ($bankAccount?->account_holder_name ?: ''),
            'partyExtraFields' => $partyExtraFields,
            'partyCustomFields' => $partyCustomFields,
        ];
    }

    private function resolveItemCustomFieldsSnapshot(?Item $itemRecord, array $item = []): array
    {
        $definitions = collect($itemRecord?->custom_fields ?? []);
        $extraFields = is_array($item['extra_fields'] ?? null) ? $item['extra_fields'] : [];

        $fields = collect(range(1, 6))->map(function ($index) use ($definitions, $extraFields) {
            $definition = $definitions->get($index - 1, []);
            $value = trim((string) ($extraFields['custom_field_' . $index] ?? ''));

            if (is_array($definition)) {
                return [
                    'key' => (string) ($definition['key'] ?? 'custom_field_' . $index),
                    'enabled' => (bool) ($definition['enabled'] ?? true),
                    'label' => trim((string) ($definition['label'] ?? $definition['name'] ?? 'Custom Field ' . $index)),
                    'show_in_print' => (bool) ($definition['show_in_print'] ?? true),
                    'value' => $value !== '' ? $value : trim((string) ($definition['value'] ?? $definition['text'] ?? '')),
                ];
            }

            return [
                'key' => 'custom_field_' . $index,
                'enabled' => $value !== '',
                'label' => 'Custom Field ' . $index,
                'show_in_print' => true,
                'value' => $value,
            ];
        });

        return $fields
            ->map(function ($field) {
                if (is_array($field)) {
                    return [
                        'key' => (string) ($field['key'] ?? ''),
                        'enabled' => (bool) ($field['enabled'] ?? true),
                        'label' => trim((string) ($field['label'] ?? $field['name'] ?? '')),
                        'show_in_print' => (bool) ($field['show_in_print'] ?? true),
                        'value' => trim((string) ($field['value'] ?? $field['text'] ?? '')),
                    ];
                }

                return [
                    'key' => '',
                    'enabled' => true,
                    'label' => '',
                    'show_in_print' => true,
                    'value' => trim((string) $field),
                ];
            })
            ->filter(function (array $field) {
                return ($field['label'] !== '' || $field['value'] !== '');
            })
            ->values()
            ->all();
    }

    private function resolveSaleItemRecord(array $item): ?Item
    {
        if (!empty($item['item_id'])) {
            $resolved = Item::find($item['item_id']);
            if ($resolved) {
                return $resolved;
            }
        }

        if (!empty($item['item_name'])) {
            return Item::whereRaw('LOWER(TRIM(name)) = ?', [
                strtolower(trim((string) $item['item_name']))
            ])->first();
        }

        return null;
    }

    private function buildSaleItemPayload(int $saleId, array $item, ?Item $itemRecord = null, bool $omitSaleId = false): array
    {
        $itemId = $itemRecord?->id ?? ($item['item_id'] ?? null);
        $itemName = trim((string) ($item['item_name'] ?? $itemRecord?->name ?? '')) ?: null;
        $itemCategory = $item['item_category'] ?? ($itemRecord?->category?->name ?? null);
        $itemCode = $item['item_code'] ?? ($itemRecord?->item_code ?? null);
        $itemDescription = $item['item_description'] ?? ($itemRecord?->description ?? null);

        $payload = [
            'item_id' => $itemId,
            'item_name' => $itemName,
            'item_category' => $itemCategory,
            'item_code' => $itemCode,
            'item_description' => $itemDescription,
            'tafseel' => $item['tafseel'] ?? null,
            'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 0,
            'gross_w' => isset($item['gross_w']) ? (float) $item['gross_w'] : 0,
            'net_w' => isset($item['net_w']) ? (float) $item['net_w'] : 0,
            'unit' => $item['unit'] ?? $itemRecord?->unit ?? null,
            'unit_price' => isset($item['unit_price']) ? (float) $item['unit_price'] : 0,
            'discount' => isset($item['discount']) ? (float) $item['discount'] : 0,
            'extra_fields' => $item['extra_fields'] ?? null,
            'amount' => isset($item['amount']) ? (float) $item['amount'] : 0,
        ];

        if (Schema::hasColumn('sale_items', 'custom_fields')) {
            $payload['custom_fields'] = $this->resolveItemCustomFieldsSnapshot($itemRecord, $item);
        }

        if (!$omitSaleId) {
            $payload['sale_id'] = $saleId;
        }

        return $payload;
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

    private function formatPercentValue($value): string
    {
        $numeric = (float) ($value ?? 0);
        $formatted = number_format($numeric, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return ($formatted === '' ? '0' : $formatted) . '%';
    }

    private function resolveSavedSaleThemeState(Sale $sale): ?array
    {
        $extraFields = $sale->details?->invoice_extra_fields;
        if (!is_array($extraFields)) {
            return null;
        }

        $mode = (string) ($extraFields['theme_mode'] ?? '');
        if (!in_array($mode, ['regular', 'thermal'], true)) {
            return null;
        }

        $regularThemeId = (int) ($extraFields['theme_regular_theme_id'] ?? 0);
        $thermalThemeId = (int) ($extraFields['theme_thermal_theme_id'] ?? 0);
        $accent = trim((string) ($extraFields['theme_accent'] ?? ''));
        $accent2 = trim((string) ($extraFields['theme_accent2'] ?? ''));

        return [
            'mode' => $mode,
            'regularThemeId' => $regularThemeId > 0 ? $regularThemeId : 1,
            'thermalThemeId' => $thermalThemeId > 0 ? $thermalThemeId : 1,
            'accent' => $accent !== '' ? $accent : '#1f4e79',
            'accent2' => $accent2 !== '' ? $accent2 : '#ff981f',
        ];
    }

    private function resolveSaleThemeContext(Sale $sale, Request $request): array
    {
        $savedTheme = $this->resolveSavedSaleThemeState($sale);
        $mode = (string) ($savedTheme['mode'] ?? $request->query('mode', 'regular'));
        $themeId = (int) (
            $mode === 'thermal'
                ? ($savedTheme['thermalThemeId'] ?? $request->query('theme_id', 1))
                : ($savedTheme['regularThemeId'] ?? $request->query('theme_id', 1))
        );

        return [
            'themeConfig' => $this->resolveInvoiceThemeConfig($mode, $themeId),
            'accent' => (string) ($savedTheme['accent'] ?? $request->query('accent', '#1f4e79')),
            'accent2' => (string) ($savedTheme['accent2'] ?? $request->query('accent2', '#ff981f')),
            'saleOrderThemeApplied' => $savedTheme !== null || $request->boolean('theme_applied'),
        ];
    }

    private function resolveInvoiceThemeConfig(string $mode, int $themeId): array
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

    public function pdfEstimate(Request $request, Sale $sale)
    {
        if ($sale->type !== 'estimate') {
            abort(404);
        }

        return redirect()->route('sale.invoice-pdf', ['sale' => $sale->id, 'download' => 1]);
    }

    public function previewSaleOrder(Sale $sale)
    {
        if ($sale->type !== 'sale_order') {
            abort(404);
        }

        $sale->loadMissing(['items.item', 'party', 'payments.bankAccount', 'details']);
        $themeContext = $this->resolveSaleThemeContext($sale, request());
        $themeConfig = $themeContext['themeConfig'];
        $saleOrderThemeApplied = $themeContext['saleOrderThemeApplied'];
        $signatureImage = (string) request()->query('signature_image', '');
        $previewData = $this->mapSaleToThemePreviewData($sale);
        $previewData['termsText'] = trim((string) ($sale->details?->terms_condition_text ?: $sale->description ?: 'Thanks for shopping with us!'));

        return view('themes.sales_invoice_pdf_document', [
            'sale' => $sale,
            'invoicePreviewData' => $previewData,
            'themeConfig' => $themeConfig,
            'saleOrderThemeApplied' => $saleOrderThemeApplied,
            'accent' => $themeContext['accent'],
            'accent2' => $themeContext['accent2'],
            'signatureImage' => $signatureImage,
            'pageTitle' => 'Sale Order Preview',
            'browserTabLabel' => 'Sale Order #' . ($sale->bill_number ?: $sale->id),
            'saveCloseUrl' => route('sale.index'),
        ]);
    }

    public function printSaleOrder(Sale $sale)
    {
        if ($sale->type !== 'sale_order') {
            abort(404);
        }

        $sale->loadMissing(['items.item', 'party', 'payments.bankAccount', 'details']);
        $themeContext = $this->resolveSaleThemeContext($sale, request());
        $themeConfig = $themeContext['themeConfig'];
        $saleOrderThemeApplied = $themeContext['saleOrderThemeApplied'];
        $signatureImage = (string) request()->query('signature_image', '');
        $previewData = $this->mapSaleToThemePreviewData($sale);
        $previewData['termsText'] = trim((string) ($sale->details?->terms_condition_text ?: $sale->description ?: 'Thanks for shopping with us!'));

        return view('themes.sales_invoice_pdf_document', [
            'sale' => $sale,
            'invoicePreviewData' => $previewData,
            'themeConfig' => $themeConfig,
            'saleOrderThemeApplied' => $saleOrderThemeApplied,
            'accent' => $themeContext['accent'],
            'accent2' => $themeContext['accent2'],
            'signatureImage' => $signatureImage,
            'pageTitle' => 'Sale Order Print',
            'browserTabLabel' => 'Sale Order #' . ($sale->bill_number ?: $sale->id),
            'saveCloseUrl' => route('sale.index'),
            'autoPrint' => true,
        ]);
    }

    public function pdfSaleOrder(Sale $sale)
    {
        if ($sale->type !== 'sale_order') {
            abort(404);
        }

        $sale->loadMissing(['items.item', 'party', 'payments.bankAccount', 'details']);
        $themeContext = $this->resolveSaleThemeContext($sale, request());
        $themeConfig = $themeContext['themeConfig'];
        $saleOrderThemeApplied = $themeContext['saleOrderThemeApplied'];
        $signatureImage = (string) request()->query('signature_image', '');
        $previewData = $this->mapSaleToThemePreviewData($sale);
        $previewData['termsText'] = trim((string) ($sale->details?->terms_condition_text ?: $sale->description ?: 'Thanks for shopping with us!'));

        $payload = [
            'sale' => $sale,
            'invoicePreviewData' => $previewData,
            'themeConfig' => $themeConfig,
            'saleOrderThemeApplied' => $saleOrderThemeApplied,
            'accent' => $themeContext['accent'],
            'accent2' => $themeContext['accent2'],
            'signatureImage' => $signatureImage,
            'pageTitle' => 'Sale Order PDF',
            'browserTabLabel' => 'Sale Order #' . ($sale->bill_number ?: $sale->id),
            'saveCloseUrl' => route('sale.index'),
        ];

        $fileName = 'sale-order-' . ($sale->bill_number ?: $sale->id) . '.pdf';
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

    private function mapEstimateToSaleDraft(Sale $estimate, string $nextInvoiceNumber): array
    {
        $details = $estimate->details;

        return [
            'source_type' => 'estimate',
            'source_estimate_id' => $estimate->id,
            'party_id' => $estimate->party_id,
            'party_name' => $estimate->display_party_name,
            'phone' => $estimate->phone,
            'billing_address' => $estimate->billing_address,
            'bill_number' => $nextInvoiceNumber,
            'invoice_date' => optional($estimate->invoice_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'order_date' => optional($estimate->order_date)->format('Y-m-d') ?? optional($estimate->invoice_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'deal_days' => $estimate->deal_days ?? 0,
            'due_date' => optional($estimate->due_date)->format('Y-m-d') ?? optional($estimate->invoice_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'tadad' => $estimate->tadad,
            'total_wazan' => $estimate->total_wazan,
            'safi_wazan' => $estimate->safi_wazan,
            'rate' => $estimate->rate,
            'deo' => $estimate->deo,
            'total_qty' => $estimate->total_qty,
            'total_amount' => $estimate->total_amount,
            'discount_pct' => $estimate->discount_pct,
            'discount_rs' => $estimate->discount_rs,
            'tax_pct' => $estimate->tax_pct,
            'tax_amount' => $estimate->tax_amount,
            'round_off' => $estimate->round_off,
            'grand_total' => $estimate->grand_total,
            'received_amount' => 0,
            'balance' => $estimate->grand_total,
            'status' => 'Unpaid',
            'description' => $estimate->description,
            'image_path' => $estimate->image_path,
            'details' => $details?->toArray(),
            'custom_expenses' => $details?->custom_expenses,
            'items' => $estimate->items->map(function ($item) {
                return [
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

    private function mapInvoiceToSaleDraft(Sale $sale, string $nextInvoiceNumber): array
    {
        $details = $sale->details;

        return [
            'source_type' => $sale->type,
            'party_id' => $sale->party_id,
            'party_name' => $sale->display_party_name,
            'phone' => $sale->phone,
            'billing_address' => $sale->billing_address,
            'shipping_address' => $sale->shipping_address,
            'bill_number' => $nextInvoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
            'order_date' => optional($sale->order_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'deal_days' => $sale->deal_days ?? 0,
            'due_date' => optional($sale->due_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'tadad' => $sale->tadad,
            'total_wazan' => $sale->total_wazan,
            'safi_wazan' => $sale->safi_wazan,
            'rate' => $sale->rate,
            'deo' => $sale->deo,
            'total_qty' => $sale->total_qty,
            'total_amount' => $sale->total_amount,
            'discount_pct' => $sale->discount_pct,
            'discount_rs' => $sale->discount_rs,
            'tax_pct' => $sale->tax_pct,
            'tax_amount' => $sale->tax_amount,
            'round_off' => $sale->round_off,
            'grand_total' => $sale->grand_total,
            'received_amount' => 0,
            'balance' => $sale->grand_total,
            'status' => 'Unpaid',
            'description' => $sale->description,
            'image_path' => $sale->image_path,
            'details' => $details?->toArray(),
            'custom_expenses' => $details?->custom_expenses,
            'items' => $sale->items->map(function ($item) {
                return [
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

    private function mapSaleOrderToSaleDraft(Sale $saleOrder, string $nextInvoiceNumber): array
    {
        $details = $saleOrder->details;

        return [
            'source_type' => 'sale_order',
            'source_sale_order_id' => $saleOrder->id,
            'party_id' => $saleOrder->party_id,
            'party_name' => $saleOrder->display_party_name,
            'phone' => $saleOrder->phone,
            'billing_address' => $saleOrder->billing_address,
            'shipping_address' => $saleOrder->shipping_address,
            'bill_number' => $nextInvoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
            'order_date' => optional($saleOrder->order_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'deal_days' => $saleOrder->deal_days ?? 0,
            'due_date' => optional($saleOrder->due_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'tadad' => $saleOrder->tadad,
            'total_wazan' => $saleOrder->total_wazan,
            'safi_wazan' => $saleOrder->safi_wazan,
            'rate' => $saleOrder->rate,
            'deo' => $saleOrder->deo,
            'total_qty' => $saleOrder->total_qty,
            'total_amount' => $saleOrder->total_amount,
            'discount_pct' => $saleOrder->discount_pct,
            'discount_rs' => $saleOrder->discount_rs,
            'tax_pct' => $saleOrder->tax_pct,
            'tax_amount' => $saleOrder->tax_amount,
            'round_off' => $saleOrder->round_off,
            'grand_total' => $saleOrder->grand_total,
            'received_amount' => 0,
            'balance' => $saleOrder->grand_total,
            'status' => 'Unpaid',
            'description' => $saleOrder->description,
            'image_path' => $saleOrder->image_path,
            'details' => $details?->toArray(),
            'custom_expenses' => $details?->custom_expenses,
            'items' => $saleOrder->items->map(function ($item) {
                return [
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

    private function mapDeliveryChallanToSaleDraft(Sale $challan, string $nextInvoiceNumber): array
    {
        $details = $challan->details;
        $challanDetails = $challan->challanDetail;
        $resolvedDetails = [
            'warehouse_id' => $details?->warehouse_id ?? $challanDetails?->warehouse_id,
            'delivery_person' => $details?->delivery_person ?? $challanDetails?->warehouse_handler_name,
            'po_no' => $details?->po_no,
            'po_date' => $details?->po_date,
            'city' => $details?->city ?? $challan->party?->city,
            'party_no' => $details?->party_no,
            'goods_name' => $details?->goods_name ?? $challanDetails?->destination,
            'details_extra' => $details?->details_extra,
            'bilti_gari_no' => $details?->bilti_gari_no ?? $challanDetails?->vehicle_number,
            'custom_expenses' => $details?->custom_expenses,
        ];

        return [
            'source_type' => 'delivery_challan',
            'source_challan_id' => $challan->id,
            'party_id' => $challan->party_id,
            'party_name' => $challan->display_party_name,
            'phone' => $challan->phone,
            'billing_address' => $challan->billing_address,
            'shipping_address' => $challan->shipping_address,
            'bill_number' => $nextInvoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
            'order_date' => optional($challan->order_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'deal_days' => $challan->deal_days ?? 0,
            'due_date' => optional($challan->due_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'tadad' => $challan->tadad,
            'total_wazan' => $challan->total_wazan,
            'safi_wazan' => $challan->safi_wazan,
            'rate' => $challan->rate,
            'deo' => $challan->deo,
            'total_qty' => $challan->total_qty,
            'total_amount' => $challan->total_amount,
            'discount_pct' => $challan->discount_pct,
            'discount_rs' => $challan->discount_rs,
            'tax_pct' => $challan->tax_pct,
            'tax_amount' => $challan->tax_amount,
            'round_off' => $challan->round_off,
            'grand_total' => $challan->grand_total,
            'received_amount' => 0,
            'balance' => $challan->grand_total,
            'status' => 'Unpaid',
            'description' => $challan->description,
            'image_path' => $challan->image_path,
            'details' => $resolvedDetails,
            'custom_expenses' => $resolvedDetails['custom_expenses'],
            'items' => $challan->items->map(function ($item) {
                return [
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

    private function mapProformaToSaleDraft(Sale $proforma, string $nextInvoiceNumber): array
    {
        $details = $proforma->details;

        return [
            'source_type' => 'proforma',
            'source_proforma_id' => $proforma->id,
            'party_id' => $proforma->party_id,
            'party_name' => $proforma->display_party_name,
            'phone' => $proforma->phone,
            'billing_address' => $proforma->billing_address,
            'bill_number' => $nextInvoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
            'order_date' => optional($proforma->order_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'deal_days' => $proforma->deal_days ?? 0,
            'due_date' => optional($proforma->due_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'tadad' => $proforma->tadad,
            'total_wazan' => $proforma->total_wazan,
            'safi_wazan' => $proforma->safi_wazan,
            'rate' => $proforma->rate,
            'deo' => $proforma->deo,
            'total_qty' => $proforma->total_qty,
            'total_amount' => $proforma->total_amount,
            'discount_pct' => $proforma->discount_pct,
            'discount_rs' => $proforma->discount_rs,
            'tax_pct' => $proforma->tax_pct,
            'tax_amount' => $proforma->tax_amount,
            'round_off' => $proforma->round_off,
            'grand_total' => $proforma->grand_total,
            'received_amount' => 0,
            'balance' => $proforma->grand_total,
            'status' => 'Unpaid',
            'description' => $proforma->description,
            'image_path' => $proforma->image_path,
            'details' => $details?->toArray(),
            'custom_expenses' => $details?->custom_expenses,
            'items' => $proforma->items->map(function ($item) {
                return [
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

    private function shouldCreateLedgerForSaleType(string $type): bool
    {
        return in_array($type, ['invoice', 'pos', 'sale_return', 'delivery_challan'], true);
    }

    private function resolveLedgerTypeFromSale(string $type): string
    {
        return match ($type) {
            'sale_return' => 'sale_return',
            default => 'sale',
        };
    }

    private function calculateLedgerExpenseTotal(array $data): float
    {
        $adjustmentTotal = collect($this->normalizeAdjustmentRows($data['custom_expenses'] ?? []))
            ->filter(fn (array $row) => $row['affects_invoice'])
            ->sum(function (array $row) {
                return $row['mode'] === '-' ? -1 * $row['amount'] : $row['amount'];
            });

        return floatval($data['broker_amount'] ?? 0)
            + floatval($data['labour'] ?? 0)
            + floatval($data['bardana'] ?? 0)
            + floatval($data['rehra_mazdori'] ?? 0)
            + floatval($data['parcel_expense'] ?? 0)
            + floatval($data['post_expense'] ?? 0)
            + floatval($data['extra_expense'] ?? 0)
            + floatval($adjustmentTotal);
    }

    private function calculateSilentBrokerLedgerDeduction(array $data): float
    {
        return collect($this->normalizeAdjustmentRows($data['custom_expenses'] ?? []))
            ->filter(function (array $row) {
                return $row['mode'] === '-'
                    && floatval($row['amount'] ?? 0) > 0;
            })
            ->sum(fn (array $row) => floatval($row['amount'] ?? 0));
    }

    private function calculatePartyTransferDeduction(array $data): float
    {
        return collect($this->normalizeAdjustmentRows($data['custom_expenses'] ?? []))
            ->filter(function (array $row) {
                return in_array($row['mode'] ?? null, ['-', 'S'], true)
                    && floatval($row['amount'] ?? 0) > 0;
            })
            ->sum(fn (array $row) => floatval($row['amount'] ?? 0));
    }

    private function calculateSameModeDeduction(array $data): float
    {
        return collect($this->normalizeAdjustmentRows($data['custom_expenses'] ?? []))
            ->filter(function (array $row) {
                return ($row['mode'] ?? null) === 'S'
                    && floatval($row['amount'] ?? 0) > 0;
            })
            ->sum(fn (array $row) => floatval($row['amount'] ?? 0));
    }

    private function calculateMinusModeDeduction(array $data): float
    {
        return collect($this->normalizeAdjustmentRows($data['custom_expenses'] ?? []))
            ->filter(function (array $row) {
                return ($row['mode'] ?? null) === '-'
                    && floatval($row['amount'] ?? 0) > 0;
            })
            ->sum(fn (array $row) => floatval($row['amount'] ?? 0));
    }

    private function saleLedgerTransferGroup(Sale $sale): string
    {
        return 'sale-ledger-' . ($sale->id ?: 'draft');
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

            $title = trim((string) ($row['heading'] ?? $row['title'] ?? ''));
            $details = trim((string) ($row['details'] ?? ''));
            $mode = strtoupper((string) ($row['mode'] ?? $row['operator'] ?? '+'));
            $mode = in_array($mode, ['+', '-', 'S'], true) ? $mode : '+';

            $percentage = isset($row['percentage'])
                ? (float) $row['percentage']
                : (isset($row['pct']) ? (float) $row['pct'] : null);
            $amount = isset($row['amount'])
                ? (float) $row['amount']
                : (isset($row['value']) ? (float) $row['value'] : 0);

            $accountType = strtolower(trim((string) ($row['account_type'] ?? '')));
            $accountId = !empty($row['account_id']) ? (int) $row['account_id'] : null;
            $accountName = trim((string) ($row['account_name'] ?? $row['brokerName'] ?? ''));
            $brokerId = !empty($row['brokerId']) ? (int) $row['brokerId'] : null;

            if ($accountType === 'broker' && !$accountId && $brokerId) {
                $accountId = $brokerId;
            }

            if ($accountType === '') {
                if ($accountId && $brokerId) {
                    $accountType = 'broker';
                } elseif (!empty($row['item_id'])) {
                    $accountType = 'item';
                    $accountId = (int) $row['item_id'];
                } elseif ($accountId) {
                    $accountType = 'party';
                }
            }

            if ($amount <= 0 && !$title && !$details && !$accountId && !$percentage) {
                continue;
            }

            $normalized[] = [
                'title' => $title !== '' ? $title : 'Adjustment',
                'details' => $details !== '' ? $details : null,
                'mode' => $mode,
                'percentage' => $percentage !== null ? round(max(0, $percentage), 4) : null,
                'rate' => $percentage !== null ? round(max(0, $percentage), 2) : null,
                'amount' => round(max(0, $amount), 2),
                'account_type' => in_array($accountType, ['party', 'broker', 'item'], true) ? $accountType : null,
                'account_id' => $accountId,
                'account_name' => $accountName !== '' ? $accountName : null,
                'affects_invoice' => $mode !== 'S',
                'sort_order' => $index,
            ];
        }

        return $normalized;
    }

    private function deleteSaleLedgerTransactions(Sale $sale): void
    {
        Transaction::query()
            ->where('transfer_group', $this->saleLedgerTransferGroup($sale))
            ->delete();

        if (!empty($sale->party_id) && !empty($sale->bill_number)) {
            Transaction::query()
                ->where('party_id', $sale->party_id)
                ->where(function ($query) use ($sale) {
                    $query->where(function ($subQuery) use ($sale) {
                        $subQuery->where('number', $sale->bill_number)
                            ->whereIn('type', ['sale', 'sale_return']);
                    })->orWhere(function ($subQuery) use ($sale) {
                        $subQuery->where('number', 'like', 'PAY-' . ($sale->bill_number ?: $sale->id) . '-%');
                    });
                })
                ->delete();
        }
    }

    private function buildLedgerItemAdjustments(Sale $sale, array $data): array
    {
        $items = $sale->items()->get()->values();
        $silentDeduction = $this->calculateSilentBrokerLedgerDeduction($data);

        if ($silentDeduction <= 0 || $items->isEmpty()) {
            return $items->mapWithKeys(function ($saleItem) {
                $amount = floatval($saleItem->amount ?? 0);
                $quantity = floatval($saleItem->quantity ?? 0);

                return [
                    $saleItem->id => [
                        'amount' => $amount,
                        'rate' => $quantity > 0 ? round($amount / $quantity, 2) : floatval($saleItem->unit_price ?? 0),
                    ],
                ];
            })->all();
        }

        $baseTotal = max(0, $items->sum(fn ($item) => floatval($item->amount ?? 0)));
        if ($baseTotal <= 0) {
            return [];
        }

        $remainingDeduction = min($silentDeduction, $baseTotal);
        $adjustments = [];
        $positiveItems = $items->filter(fn ($item) => floatval($item->amount ?? 0) > 0)->values();
        $lastPositiveIndex = max(0, $positiveItems->count() - 1);

        foreach ($positiveItems as $index => $saleItem) {
            $itemAmount = floatval($saleItem->amount ?? 0);
            $quantity = floatval($saleItem->quantity ?? 0);

            if ($remainingDeduction <= 0) {
                $adjustedAmount = $itemAmount;
            } elseif ($index === $lastPositiveIndex) {
                $cutAmount = min($itemAmount, $remainingDeduction);
                $adjustedAmount = max(0, $itemAmount - $cutAmount);
                $remainingDeduction -= $cutAmount;
            } else {
                $share = round(($itemAmount / $baseTotal) * $silentDeduction, 2);
                $cutAmount = min($itemAmount, min($share, $remainingDeduction));
                $adjustedAmount = max(0, $itemAmount - $cutAmount);
                $remainingDeduction -= $cutAmount;
            }

            $adjustments[$saleItem->id] = [
                'amount' => round($adjustedAmount, 2),
                'rate' => $quantity > 0 ? round($adjustedAmount / $quantity, 2) : floatval($saleItem->unit_price ?? 0),
            ];
        }

        foreach ($items as $saleItem) {
            if (isset($adjustments[$saleItem->id])) {
                continue;
            }

            $amount = floatval($saleItem->amount ?? 0);
            $quantity = floatval($saleItem->quantity ?? 0);

            $adjustments[$saleItem->id] = [
                'amount' => $amount,
                'rate' => $quantity > 0 ? round($amount / $quantity, 2) : floatval($saleItem->unit_price ?? 0),
            ];
        }

        return $adjustments;
    }

    private function syncTransactionItems(Transaction $transaction, Sale $sale, array $data = []): void
    {
        $transaction->items()->delete();
        $ledgerItemAdjustments = $this->buildLedgerItemAdjustments($sale, $data);

        foreach ($sale->items()->get() as $saleItem) {
            $resolvedItemId = $saleItem->item_id;

            if (empty($resolvedItemId) && !empty($saleItem->item_name)) {
                $resolvedItemId = Item::query()
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $saleItem->item_name))])
                    ->value('id');
            }

            $adjustedItem = $ledgerItemAdjustments[$saleItem->id] ?? null;
            $ledgerAmount = floatval($adjustedItem['amount'] ?? $saleItem->amount ?? 0);
            $ledgerRate = floatval($adjustedItem['rate'] ?? $saleItem->unit_price ?? 0);

            $transaction->items()->create([
                'item_id' => $resolvedItemId,
                'quantity' => floatval($saleItem->quantity ?? 0),
                'rate' => $ledgerRate,
                'amount' => $ledgerAmount,
            ]);
        }
    }

    private function syncTransactionAdjustments(Transaction $transaction, Sale $sale, array $data): array
    {
        $normalizedRows = $this->normalizeAdjustmentRows($data['custom_expenses'] ?? []);
        $transaction->adjustments()->delete();

        $affectedPartyIds = [$sale->party_id];

        foreach ($normalizedRows as $row) {
            $payload = [
                'mode' => $row['mode'],
                'title' => $row['title'],
                'details' => $row['details'],
                'percentage' => $row['percentage'],
                'rate' => $row['rate'],
                'amount' => $row['amount'],
                'affects_invoice' => $row['affects_invoice'],
                'sort_order' => $row['sort_order'],
            ];

            if ($row['account_type'] === 'party') {
                $payload['account_party_id'] = $row['account_id'];
            } elseif ($row['account_type'] === 'broker') {
                $payload['broker_id'] = $row['account_id'];
            } elseif ($row['account_type'] === 'item') {
                $payload['item_id'] = $row['account_id'];
            }

            $transaction->adjustments()->create($payload);

            if ($row['mode'] === '-' && $row['amount'] > 0 && $row['account_type'] === 'broker' && !empty($row['account_id'])) {
                $brokerLabel = $row['account_name'] ?: ('Broker #' . $row['account_id']);
                $detailsSuffix = $row['details'] ? ' - ' . $row['details'] : '';

                Transaction::create([
                    'party_id' => $sale->party_id,
                    'type' => 'party to party[paid]',
                    'number' => 'BR-DED-' . ($sale->bill_number ?: $sale->id) . '-' . str_pad((string) ($row['sort_order'] + 1), 2, '0', STR_PAD_LEFT),
                    'transfer_group' => $this->saleLedgerTransferGroup($sale),
                    'date' => $sale->invoice_date ?? now(),
                    'total' => $row['amount'],
                    'debit' => 0,
                    'credit' => 0,
                    'paid_amount' => 0,
                    'balance' => floatval($sale->balance ?? 0),
                    'running_balance' => 0,
                    'due_date' => $sale->due_date,
                    'status' => 'posted',
                    'broker_id' => $row['account_id'],
                    'description' => 'Brokerage deducted and paid to ' . $brokerLabel . ' against Invoice #' . ($sale->bill_number ?: $sale->id) . $detailsSuffix,
                ]);

                continue;
            }

            if (!in_array($row['mode'], ['S', '-'], true) || $row['amount'] <= 0) {
                continue;
            }

            if ($row['account_type'] === 'broker' && !empty($row['account_id'])) {
                $brokerLabel = $row['account_name'] ?: ('Broker #' . $row['account_id']);
                $detailsSuffix = $row['details'] ? ' - ' . $row['details'] : '';
                $numberPrefix = $row['mode'] === '-' ? 'M-BR-' : 'S-BR-';
                $descriptionPrefix = $row['mode'] === '-'
                    ? $row['title'] . ' deducted and transferred to '
                    : $row['title'] . ' transferred to ';

                Transaction::create([
                    'party_id' => $sale->party_id,
                    'type' => 'party to party[paid]',
                    'number' => $numberPrefix . ($sale->bill_number ?: $sale->id) . '-' . str_pad((string) ($row['sort_order'] + 1), 2, '0', STR_PAD_LEFT),
                    'transfer_group' => $this->saleLedgerTransferGroup($sale),
                    'date' => $sale->invoice_date ?? now(),
                    'total' => $row['amount'],
                    'debit' => 0,
                    'credit' => $row['amount'],
                    'paid_amount' => 0,
                    'balance' => floatval($sale->balance ?? 0),
                    'running_balance' => 0,
                    'due_date' => $sale->due_date,
                    'status' => 'posted',
                    'broker_id' => $row['account_id'],
                    'description' => $descriptionPrefix . $brokerLabel . ' against Invoice #' . ($sale->bill_number ?: $sale->id) . $detailsSuffix,
                ]);

                continue;
            }

            if ($row['account_type'] !== 'party' || empty($row['account_id'])) {
                continue;
            }

            $targetParty = Party::query()->find($row['account_id']);
            if (!$targetParty) {
                continue;
            }

            $baseNumber = ($row['mode'] === '-' ? 'DED-' : 'ADJ-') . ($sale->bill_number ?: $sale->id) . '-' . str_pad((string) ($row['sort_order'] + 1), 2, '0', STR_PAD_LEFT);
            $detailsSuffix = $row['details'] ? ' - ' . $row['details'] : '';
            $targetLabel = $targetParty->name ?: ($row['account_name'] ?? 'Selected Account');
            $titlePrefix = $row['mode'] === '-' ? ($row['title'] . ' deducted') : $row['title'];

            Transaction::create([
                'party_id' => $targetParty->id,
                'counter_party_id' => $sale->party_id,
                'type' => 'party to party[received]',
                'number' => $baseNumber . '-DR',
                'transfer_group' => $this->saleLedgerTransferGroup($sale),
                'date' => $sale->invoice_date ?? now(),
                'total' => $row['amount'],
                'debit' => $row['amount'],
                'credit' => 0,
                'paid_amount' => 0,
                'balance' => floatval($sale->balance ?? 0),
                'running_balance' => 0,
                'due_date' => $sale->due_date,
                'status' => 'posted',
                'description' => $titlePrefix . ' against Invoice #' . ($sale->bill_number ?: $sale->id) . ' for ' . ($sale->party?->name ?: 'Sale Party') . $detailsSuffix,
            ]);

            Transaction::create([
                'party_id' => $sale->party_id,
                'counter_party_id' => $targetParty->id,
                'type' => 'party to party[paid]',
                'number' => $baseNumber . '-CR',
                'transfer_group' => $this->saleLedgerTransferGroup($sale),
                'date' => $sale->invoice_date ?? now(),
                'total' => $row['amount'],
                'debit' => 0,
                'credit' => $row['amount'],
                'paid_amount' => 0,
                'balance' => 0,
                'running_balance' => 0,
                'due_date' => $sale->due_date,
                'status' => 'posted',
                'description' => $titlePrefix . ' linked from Invoice #' . ($sale->bill_number ?: $sale->id) . ' for ' . $targetLabel . $detailsSuffix,
            ]);

            $affectedPartyIds[] = $targetParty->id;
        }

        return array_values(array_unique(array_filter($affectedPartyIds)));
    }

    private function syncSaleLedgerEntries(Sale $sale, array $data): void
    {
        if (empty($sale->party_id) || !$this->shouldCreateLedgerForSaleType((string) $sale->type)) {
            return;
        }

        $this->deleteSaleLedgerTransactions($sale);

        $grossSaleAmount = floatval($sale->grand_total ?? 0);
        $saleAmount = $grossSaleAmount;
        $ledgerType = $this->resolveLedgerTypeFromSale((string) $sale->type);
        $transactionBalance = max(0, round($saleAmount - floatval($sale->received_amount ?? 0), 2));
        $saleLedgerAmount = max(0, round($saleAmount, 2));

        $transactionPayload = [
            'party_id' => $sale->party_id,
            'type' => $ledgerType,
            'number' => $sale->bill_number ?: (string) $sale->id,
            'transfer_group' => $this->saleLedgerTransferGroup($sale),
            'date' => $sale->invoice_date ?? now(),
            'total' => $saleAmount,
            'debit' => $ledgerType === 'sale_return' ? 0 : $saleLedgerAmount,
            'credit' => $ledgerType === 'sale_return' ? $saleLedgerAmount : 0,
            'paid_amount' => floatval($sale->received_amount ?? 0),
            'balance' => $transactionBalance,
            'running_balance' => 0,
            'due_date' => $sale->due_date,
            'status' => $sale->status,
            'broker_id' => $sale->broker_id,
            'broker_amount' => floatval($sale->broker_amount ?? ($data['broker_amount'] ?? 0)),
            'labour' => floatval($data['labour'] ?? 0),
            'bardana' => floatval($data['bardana'] ?? 0),
            'parcel_expense' => floatval($data['parcel_expense'] ?? 0),
            'post_expense' => floatval($data['post_expense'] ?? 0),
            'extra_expense' => floatval($data['extra_expense'] ?? 0),
            'description' => 'Invoice #' . ($sale->bill_number ?: $sale->id),
        ];

        if (Schema::hasColumn('transactions', 'rehra_mazdori')) {
            $transactionPayload['rehra_mazdori'] = floatval($data['rehra_mazdori'] ?? 0);
        }

        $masterTransaction = Transaction::create($transactionPayload);
        $this->syncTransactionItems($masterTransaction, $sale, $data);
        $affectedPartyIds = $this->syncTransactionAdjustments($masterTransaction, $sale, $data);

        foreach ($sale->payments()->orderBy('id')->get() as $paymentRecord) {
            $paymentAmount = floatval($paymentRecord->amount ?? 0);
            $paymentDirection = $this->normalizePaymentDirection($paymentRecord->direction ?? null);

            Transaction::create([
                'party_id' => $sale->party_id,
                'type' => $paymentDirection,
                'number' => 'PAY-' . ($sale->bill_number ?: $sale->id) . '-' . $paymentRecord->id,
                'transfer_group' => $this->saleLedgerTransferGroup($sale),
                'date' => $sale->invoice_date ?? now(),
                'total' => $paymentAmount,
                'debit' => 0,
                'credit' => $paymentAmount,
                'paid_amount' => $paymentAmount,
                'balance' => floatval($sale->balance ?? 0),
                'running_balance' => 0,
                'status' => 'paid',
                'description' => $paymentDirection === 'payment_out'
                    ? 'Payment paid to party for Invoice #' . ($sale->bill_number ?: $sale->id)
                    : 'Payment received for Invoice #' . ($sale->bill_number ?: $sale->id),
            ]);
        }

        foreach ($affectedPartyIds as $partyId) {
            $this->recalculatePartyLedgerBalances((int) $partyId);
        }
    }

    private function normalizePaymentDirection(?string $direction): string
    {
        return 'payment_in';
    }

    private function resolveBrokerSnapshotFromAdjustments(array $data): array
    {
        $fallbackBrokerId = !empty($data['broker_id']) ? (int) $data['broker_id'] : null;
        $fallbackBrokerAmount = round(max(0, floatval($data['broker_amount'] ?? 0)), 2);
        $rows = $this->normalizeAdjustmentRows($data['custom_expenses'] ?? []);

        $brokerRows = collect($rows)->filter(function (array $row) {
            return ($row['account_type'] ?? null) === 'broker'
                && !empty($row['account_id'])
                && floatval($row['amount'] ?? 0) > 0
                && strtoupper((string) ($row['mode'] ?? '+')) !== '-';
        })->values();

        if ($brokerRows->isEmpty()) {
            return [
                'broker_id' => $fallbackBrokerId,
                'broker_amount' => $fallbackBrokerAmount,
            ];
        }

        $selectedBrokerId = $fallbackBrokerId ?: (int) ($brokerRows->first()['account_id'] ?? 0);
        if (empty($selectedBrokerId)) {
            return [
                'broker_id' => $fallbackBrokerId,
                'broker_amount' => $fallbackBrokerAmount,
            ];
        }

        $selectedBrokerAmount = round((float) $brokerRows
            ->where('account_id', $selectedBrokerId)
            ->sum(fn (array $row) => floatval($row['amount'] ?? 0)), 2);

        return [
            'broker_id' => $selectedBrokerId,
            'broker_amount' => $selectedBrokerAmount > 0 ? $selectedBrokerAmount : $fallbackBrokerAmount,
        ];
    }

    private function normalizeSaleRequestPayload(Request $request): void
    {
        foreach (['items', 'payments', 'image_paths', 'document_paths', 'custom_expenses', 'terms_condition_templates'] as $field) {
            $value = $request->input($field);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request->merge([$field => $decoded]);
                }
            }
        }
    }

    private function syncTermsConditionTemplates(array $data): void
    {
        $templates = $this->sanitizeTermsConditionTemplates($data['terms_condition_templates'] ?? []);

        foreach ($templates as $template) {
            SaleTermsCondition::updateOrCreate(
                ['name' => $template['name']],
                [
                    'description' => $template['description'],
                    'applicable_for' => $template['applicable_for'] ?? [],
                    'is_active' => true,
                ]
            );
        }
    }

    private function sanitizeTermsConditionTemplates($templates): array
    {
        if (!is_array($templates)) {
            return [];
        }

        $normalized = [];

        foreach ($templates as $template) {
            if (!is_array($template)) {
                continue;
            }

            $name = trim((string) ($template['name'] ?? ''));
            $description = trim((string) ($template['description'] ?? ''));
            $applicableFor = array_values(array_filter(array_map(function ($value) {
                return trim((string) $value);
            }, (array) ($template['applicable_for'] ?? []))));

            if ($name === '' || $description === '') {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'description' => $description,
                'applicable_for' => $applicableFor,
            ];
        }

        return array_values(array_unique($normalized, SORT_REGULAR));
    }

    private function upsertSaleDetails(Sale $sale, array $data): void
    {
        $sale->details()->updateOrCreate(
            ['sale_id' => $sale->id],
            [
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'delivery_person' => $data['delivery_person'] ?? null,
                'bilti_no' => $data['bilti_no'] ?? null,
                'gate_no' => $data['gate_no'] ?? null,
                'po_no' => $data['po_no'] ?? null,
                'po_date' => $data['po_date'] ?? null,
                'city' => $data['city'] ?? null,
                'party_no' => $data['party_no'] ?? null,
                'goods_name' => $data['goods_name'] ?? null,
                'details_extra' => $data['details_extra'] ?? null,
                'bilti_gari_no' => $data['bilti_gari_no'] ?? null,
                'terms_condition_name' => $data['terms_condition_name'] ?? null,
                'terms_condition_text' => $data['terms_condition_text'] ?? null,
                'invoice_extra_fields' => $data['invoice_extra_fields'] ?? null,
                'payment_term_name' => $data['payment_term_name'] ?? null,
                'payment_term_days' => $data['payment_term_days'] ?? null,
                'additional_charges' => $data['additional_charges'] ?? null,
                'transportation_details' => $data['transportation_details'] ?? null,
                'custom_expenses' => $data['custom_expenses'] ?? null,
            ]
        );
    }

    private function getTermsConditionTemplates(): array
    {
        return SaleTermsCondition::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'description', 'applicable_for'])
            ->map(function (SaleTermsCondition $template) {
                return [
                    'name' => $template->name,
                    'description' => $template->description,
                    'applicable_for' => array_values(array_filter(array_map(
                        fn ($value) => trim((string) $value),
                        (array) ($template->applicable_for ?? [])
                    ))),
                ];
            })
            ->all();
    }

    private function defaultSaleFormSettings(): array
    {
        return [
            'transaction_header' => [
                'invoice_number_enabled' => true,
                'transaction_time_enabled' => false,
                'cash_sale_default' => false,
                'billing_name_enabled' => true,
                'customer_po_enabled' => \App\Models\AppSetting::getValue('transaction_customer_po_enabled', '0') === '1',
            ],
            'items_table' => [
                'free_item_qty_enabled' => false,
                'count_enabled' => \App\Models\AppSetting::getValue('transaction_items_count_enabled', '0') === '1',
                'count_label' => 'Count',
            ],
            'more_transaction_features' => [
                'terms_conditions_enabled' => true,
                'due_dates_payment_terms_enabled' => true,
                'quick_entry' => false,
                'link_payment_to_invoices' => true,
                'passcode_enabled' => false,
                'transaction_passcode_hash' => null,
                'do_not_show_invoice_preview' => false,
            ],
            'sale_prefix' => [
                'enabled' => true,
                'active' => 'INV',
                'options' => ['INV'],
            ],
            'transaction_prefixes' => [
                'sale' => ['active' => 'INV', 'options' => ['INV']],
                'credit_note' => ['active' => 'CN', 'options' => ['CN']],
                'sale_order' => ['active' => 'SO', 'options' => ['SO']],
                'purchase_order' => ['active' => 'PO', 'options' => ['PO']],
                'estimate' => ['active' => 'EST', 'options' => ['EST']],
                'proforma_invoice' => ['active' => 'PI', 'options' => ['PI']],
                'delivery_challan' => ['active' => 'DC', 'options' => ['DC']],
                'payment_in' => ['active' => 'PIN', 'options' => ['PIN']],
            ],
            'invoice_fields' => [
                'custom_field_1' => [
                    'enabled' => false,
                    'label' => 'Additional Field 1',
                ],
                'date_field_2' => [
                    'enabled' => false,
                    'label' => 'Date Field 2',
                    'format' => 'dd/mm/yyyy',
                ],
            ],
            'quick_entry' => false,
            'link_payment_to_invoices' => true,
            'payment_terms' => [
                'enabled' => true,
                'name' => 'Net 15',
                'days' => 15,
            ],
            'transportation_details' => [
                'enabled' => false,
                'fields' => [
                    ['key' => 'field_1', 'label' => 'Transport Name', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_2', 'label' => 'Vehicle Number', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_3', 'label' => 'Delivery Date', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_4', 'label' => 'Delivery Location', 'enabled' => false, 'show_in_print' => true],
                    ['key' => 'field_5', 'label' => 'Field 5', 'enabled' => false, 'show_in_print' => true],
                ],
            ],
            'additional_charges' => [
                'enabled' => true,
                'items' => [
                    ['key' => 'shipping', 'enabled' => true, 'label' => 'Shipping', 'tax_rate' => 'NONE', 'tax_enabled' => false],
                    ['key' => 'packaging', 'enabled' => true, 'label' => 'Packaging', 'tax_rate' => 'NONE', 'tax_enabled' => false],
                    ['key' => 'adjustment', 'enabled' => true, 'label' => 'Adjustment', 'tax_rate' => 'NONE', 'tax_enabled' => false],
                ],
            ],
        ];
    }

    private function getSaleFormSettings(): array
    {
        $stored = json_decode((string) AppSetting::getValue('sale_form_settings', '{}'), true);
        $defaults = $this->defaultSaleFormSettings();

        if (!is_array($stored)) {
            return $defaults;
        }

        $settings = array_replace_recursive($defaults, $stored);
        $prefixOptions = array_values(array_unique(array_filter(array_map(
            fn ($value) => strtoupper(trim((string) $value)),
            (array) ($settings['sale_prefix']['options'] ?? [])
        ))));

        if (empty($prefixOptions)) {
            $prefixOptions = ['INV'];
        }

        $activePrefix = strtoupper(trim((string) ($settings['sale_prefix']['active'] ?? 'INV')));
        if (!in_array($activePrefix, $prefixOptions, true)) {
            $prefixOptions[] = $activePrefix ?: 'INV';
        }

        $settings['sale_prefix']['options'] = $prefixOptions;
        $settings['sale_prefix']['active'] = $activePrefix ?: 'INV';

        $salePrefixFromTransaction = strtoupper(trim((string) data_get($settings, 'transaction_prefixes.sale.active', $settings['sale_prefix']['active'])));
        if ($salePrefixFromTransaction) {
            if (!in_array($salePrefixFromTransaction, $settings['sale_prefix']['options'], true)) {
                $settings['sale_prefix']['options'][] = $salePrefixFromTransaction;
            }
            $settings['sale_prefix']['active'] = $salePrefixFromTransaction;
            $settings['transaction_prefixes']['sale']['active'] = $salePrefixFromTransaction;
        }

        return $settings;
    }

    private function defaultItemFormSettings(): array
    {
        return [
            'enable_item' => true,
            'sell_type' => 'both',
            'barcode_scan_enabled' => false,
            'direct_barcode_scan_enabled' => false,
            'stock_maintenance_enabled' => false,
            'manufacturing_enabled' => false,
            'show_low_stock_dialog' => false,
            'items_unit_enabled' => true,
            'default_unit_enabled' => false,
            'item_category_enabled' => false,
            'party_wise_item_rate_enabled' => false,
            'description_enabled' => false,
            'description_label' => 'Description',
            'item_wise_tax_enabled' => false,
            'item_wise_discount_enabled' => false,
            'update_sale_price_from_transaction' => false,
            'quantity_decimals' => 2,
            'wholesale_price_enabled' => false,
            'free_item_qty_enabled' => false,
            'count_enabled' => false,
            'count_label' => 'Count',
            'mrp' => [
                'enabled' => false,
                'label' => 'MRP',
                'calculate_sale_price_from_mrp' => false,
                'use_mrp_for_batch_tracking' => false,
            ],
            'serial_tracking' => [
                'enabled' => false,
                'label' => 'Serial No.',
            ],
            'batch_tracking' => [
                'batch_no' => ['enabled' => false, 'label' => 'Batch No.'],
                'exp_date' => ['enabled' => false, 'label' => 'Exp. Date', 'format' => 'mm/yy'],
                'mfg_date' => ['enabled' => false, 'label' => 'Mfg. Date', 'format' => 'mm/yy'],
                'model_no' => ['enabled' => false, 'label' => 'Model No.'],
                'size' => ['enabled' => false, 'label' => 'Size'],
            ],
            'custom_fields' => collect(range(1, 6))->map(fn ($i) => [
                'key' => 'custom_field_' . $i,
                'enabled' => false,
                'label' => 'Custom Field ' . $i,
                'show_in_print' => false,
            ])->all(),
        ];
    }

    private function getItemFormSettings(): array
    {
        $stored = json_decode((string) AppSetting::getValue('item_form_settings', '{}'), true);
        $defaults = $this->defaultItemFormSettings();

        if (!is_array($stored)) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $stored);
    }

    public function updateFormSettings(Request $request)
    {
        $data = $request->validate([
            'transaction_header.invoice_number_enabled' => 'nullable|boolean',
            'transaction_header.transaction_time_enabled' => 'nullable|boolean',
            'transaction_header.cash_sale_default' => 'nullable|boolean',
            'transaction_header.billing_name_enabled' => 'nullable|boolean',
            'transaction_header.customer_po_enabled' => 'nullable|boolean',
            'items_table.free_item_qty_enabled' => 'nullable|boolean',
            'items_table.count_enabled' => 'nullable|boolean',
            'items_table.count_label' => 'nullable|string|max:100',
            'more_transaction_features.terms_conditions_enabled' => 'nullable|boolean',
            'more_transaction_features.due_dates_payment_terms_enabled' => 'nullable|boolean',
            'more_transaction_features.quick_entry' => 'nullable|boolean',
            'more_transaction_features.link_payment_to_invoices' => 'nullable|boolean',
            'more_transaction_features.passcode_enabled' => 'nullable|boolean',
            'more_transaction_features.do_not_show_invoice_preview' => 'nullable|boolean',
            'sale_prefix.enabled' => 'nullable|boolean',
            'sale_prefix.active' => 'nullable|string|max:20',
            'sale_prefix.options' => 'nullable|array',
            'sale_prefix.options.*' => 'nullable|string|max:20',
            'transaction_prefixes' => 'nullable|array',
            'transaction_prefixes.*.active' => 'nullable|string|max:20',
            'transaction_prefixes.*.options' => 'nullable|array',
            'transaction_prefixes.*.options.*' => 'nullable|string|max:20',
            'invoice_fields.custom_field_1.enabled' => 'nullable|boolean',
            'invoice_fields.custom_field_1.label' => 'nullable|string|max:100',
            'invoice_fields.date_field_2.enabled' => 'nullable|boolean',
            'invoice_fields.date_field_2.label' => 'nullable|string|max:100',
            'invoice_fields.date_field_2.format' => 'nullable|in:dd/mm/yyyy,yyyy/mm/dd,mm/yyyy,dd-mm-yyyy',
            'quick_entry' => 'nullable|boolean',
            'link_payment_to_invoices' => 'nullable|boolean',
            'payment_terms.enabled' => 'nullable|boolean',
            'payment_terms.name' => 'nullable|string|max:100',
            'payment_terms.days' => 'nullable|integer|min:0|max:365',
            'transportation_details.enabled' => 'nullable|boolean',
            'transportation_details.fields' => 'nullable|array',
            'transportation_details.fields.*.key' => 'nullable|string|max:30',
            'transportation_details.fields.*.label' => 'nullable|string|max:100',
            'transportation_details.fields.*.enabled' => 'nullable|boolean',
            'transportation_details.fields.*.show_in_print' => 'nullable|boolean',
            'additional_charges.enabled' => 'nullable|boolean',
            'additional_charges.items' => 'nullable|array',
            'additional_charges.items.*.key' => 'nullable|string|max:30',
            'additional_charges.items.*.enabled' => 'nullable|boolean',
            'additional_charges.items.*.label' => 'nullable|string|max:100',
            'additional_charges.items.*.tax_rate' => 'nullable|string|max:20',
            'additional_charges.items.*.tax_enabled' => 'nullable|boolean',
            'transaction_passcode' => 'nullable|string|digits:4',
            'transaction_passcode_confirmation' => 'nullable|string|digits:4|same:transaction_passcode',
        ]);

        $settings = array_replace_recursive($this->getSaleFormSettings(), $data);
        if (!empty($data['transaction_passcode'])) {
            $settings['more_transaction_features']['transaction_passcode_hash'] = bcrypt($data['transaction_passcode']);
            $settings['more_transaction_features']['passcode_enabled'] = true;
        }
        if (!empty(data_get($settings, 'items_table.count_enabled'))) {
            \App\Models\AppSetting::setValue('transaction_items_count_enabled', '1');
        } else {
            \App\Models\AppSetting::setValue('transaction_items_count_enabled', '0');
        }
        if (!empty(data_get($settings, 'transaction_header.customer_po_enabled'))) {
            \App\Models\AppSetting::setValue('transaction_customer_po_enabled', '1');
        } else {
            \App\Models\AppSetting::setValue('transaction_customer_po_enabled', '0');
        }
        AppSetting::setValue('sale_form_settings', json_encode($settings));

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    public function storeTermsTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'applicable_for' => 'nullable|array',
            'applicable_for.*' => 'nullable|string|max:50',
        ]);

        $template = SaleTermsCondition::updateOrCreate(
            ['name' => trim($data['name'])],
            [
                'description' => trim($data['description']),
                'applicable_for' => array_values(array_filter(array_map(
                    fn ($value) => trim((string) $value),
                    (array) ($data['applicable_for'] ?? [])
                ))),
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'template' => [
                'name' => $template->name,
                'description' => $template->description,
                'applicable_for' => $template->applicable_for ?? [],
            ],
        ]);
    }

    private function storeSaleAttachmentFiles(array $files = [], string $directory = ''): array
    {
        $storedPaths = [];

        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $storedPaths[] = $file->store($directory, 'public');
            }
        }

        return $storedPaths;
    }

    private function storeSaleCheque(Sale $sale, array $payment, float $paymentAmount, $bankId = null): void
    {
        if ($paymentAmount <= 0) {
            return;
        }

        $reference = trim((string) ($payment['reference'] ?? ''));
        $transactionDate = !empty($sale->invoice_date)
            ? Carbon::parse($sale->invoice_date)->toDateString()
            : now()->toDateString();

        $existingCheque = Cheque::query()
            ->where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->where('amount', $paymentAmount)
            ->where('ref_no', $reference !== '' ? $reference : null)
            ->first();

        if ($existingCheque) {
            return;
        }

        Cheque::create([
            'type' => 'sale',
            'name' => (string) ($sale->party?->name ?? ('Invoice #' . ($sale->bill_number ?: $sale->id))),
            'ref_no' => $reference !== '' ? $reference : null,
            'transaction_date' => $transactionDate,
            'cheque_date' => $transactionDate,
            'amount' => $paymentAmount,
            'status' => 'open',
            'bank_account_id' => $bankId ?: null,
            'reference_id' => $sale->id,
            'reference_type' => 'sale',
            'notes' => 'Cheque received against Invoice #' . ($sale->bill_number ?: $sale->id),
            'created_by' => auth()->id(),
        ]);
    }

    private function recalculatePartyLedgerBalances(?int $partyId): void
    {
        if (empty($partyId)) {
            return;
        }

        $runningBalance = 0.0;

        Transaction::where('party_id', $partyId)
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->each(function (Transaction $transaction) use (&$runningBalance) {
                $runningBalance += Transaction::normalizeLedgerAmount($transaction->debit ?? 0);
                $runningBalance -= Transaction::normalizeLedgerAmount($transaction->credit ?? 0);
                $runningBalance = Transaction::normalizeLedgerAmount($runningBalance);
                $transaction->running_balance = $runningBalance;
                $transaction->saveQuietly();
            });

        Transaction::syncPartyCurrentBalance($partyId);
    }

    private function resolveStatusForType(
        string $type,
        float $receivedAmount,
        float $grandTotal,
        ?string $requestedStatus = null,
        ?string $currentStatus = null
    ): string {
        $allowedStatuses = match ($type) {
            'estimate' => ['open', 'pending', 'converted'],
            'proforma' => ['open', 'pending', 'converted'],
            'sale_order' => ['pending', 'confirmed', 'completed'],
            'delivery_challan' => ['open', 'closed'],
            default => ['Unpaid', 'Partial', 'Paid'],
        };

        if ($requestedStatus && in_array($requestedStatus, $allowedStatuses, true)) {
            return $requestedStatus;
        }

        if (in_array($type, ['estimate', 'proforma', 'sale_order', 'delivery_challan'], true)
            && $currentStatus
            && in_array($currentStatus, $allowedStatuses, true)) {
            return $currentStatus;
        }

        if ($type === 'estimate') return 'open';
        if ($type === 'sale_order') return 'pending';
        if ($type === 'delivery_challan') return 'open';

if ($receivedAmount >= $grandTotal && $grandTotal > 0) return 'Paid';
        if ($receivedAmount > 0 && $receivedAmount < $grandTotal) return 'Partial';

        return 'Unpaid';
    }

 public function paymentIn()
{
    $parties = Party::all();
    $bankAccounts = BankAccount::active()->get();

    return view('dashboard.sales.payement-in', compact('parties', 'bankAccounts'));
}
public function getNextNumber(Request $request)
{
    $type = $request->query('type', 'invoice');
    $offset = max(0, (int) $request->query('offset', 0));
    $customPrefix = strtoupper(trim((string) $request->query('custom_prefix', '')));

    $prefixTypeMap = [
        'invoice'          => 'invoice',
        'estimate'         => 'estimate',
        'sale_order'       => 'sale_order',
        'proforma'         => 'proforma_invoice',
        'delivery_challan' => 'delivery_challan',
        'sale_return'      => 'credit_note',
        'pos'              => 'invoice',
    ];

    $nextSaleId = (Sale::max('id') ?? 0) + 1 + $offset;
    $nextNumber = TransactionNumberPrefix::format(
        $prefixTypeMap[$type] ?? 'invoice',
        $nextSaleId
    );

    if ($customPrefix !== '') {
        $nextNumber = $customPrefix . '-' . preg_replace('/^[A-Z]+-?/i', '', $nextNumber);
    }

    return response()->json(['bill_number' => $nextNumber]);
}

}
