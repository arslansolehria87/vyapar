<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BankAccount;
use App\Models\Broker;
use App\Models\Item;
use App\Models\Party;
use App\Models\PartyGroup;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Warehouse;
use App\Support\TransactionNumberPrefix;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleOrderController extends Controller
{
    public function saleOrder(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $query = Sale::with(['items', 'payments', 'party'])
            ->where('type', 'sale_order')
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->whereHas('party', function ($partyQuery) use ($search) {
                    $partyQuery->where('name', 'like', "%{$search}%");
                })->orWhere('bill_number', 'like', "%{$search}%");
            });
        }

        $saleOrders = $query->get();
        $convertedInvoiceNumbers = Sale::where('type', 'invoice')
            ->whereNotNull('reference_id')
            ->whereIn('reference_id', $saleOrders->pluck('id'))
            ->pluck('bill_number', 'reference_id');

        $convertedInvoiceIds = Sale::where('type', 'invoice')
            ->whereNotNull('reference_id')
            ->whereIn('reference_id', $saleOrders->pluck('id'))
            ->pluck('id', 'reference_id');

        return view('dashboard.saleorder.sale-order', compact('saleOrders', 'search', 'convertedInvoiceNumbers', 'convertedInvoiceIds'));
    }

    public function create(Request $request)
    {
        [$bankAccounts, $items, $parties, $brokers, $partyGroups, $warehouses, $customerPoDetailsEnabled] = $this->getFormDependencies();
        $nextInvoiceNumber = TransactionNumberPrefix::format('sale_order', (Sale::max('id') ?? 0) + 1);

        $sale = null;
        $convertedSaleData = null;
        $editSaleData = null;

        if ($request->filled('edit_sale_id')) {
            $sale = Sale::with(['items', 'payments', 'party', 'details'])
                ->where('type', 'sale_order')
                ->findOrFail($request->integer('edit_sale_id'));
            $editSaleData = $this->mapSaleOrderToEditDraft($sale);
        }

        if ($request->filled('duplicate_sale_id')) {
            $sourceSaleOrder = Sale::with(['items', 'payments', 'party', 'details'])
                ->where('type', 'sale_order')
                ->findOrFail($request->integer('duplicate_sale_id'));

            $convertedSaleData = $this->mapSaleOrderToDraft($sourceSaleOrder, $nextInvoiceNumber);
        }

        return view('dashboard.saleorder.create-sale-order', compact(
            'bankAccounts',
            'items',
            'parties',
            'nextInvoiceNumber',
            'convertedSaleData',
            'editSaleData',
            'sale',
            'brokers',
            'partyGroups',
            'warehouses',
            'customerPoDetailsEnabled'
        ));
    }

    public function edit(Sale $sale)
    {
        if ($sale->type !== 'sale_order') {
            abort(404);
        }

        return redirect()->route('sale-order.create', ['edit_sale_id' => $sale->id]);
    }

    public function store(Request $request)
    {
        $isConversionToInvoice = $request->filled('source_sale_order_id');

        $request->merge([
            'type' => $isConversionToInvoice ? 'invoice' : 'sale_order',
            'from_sale_order' => $isConversionToInvoice ? 1 : (int) $request->boolean('from_sale_order'),
        ]);

        return $this->forceInvoiceRedirect(app(SaleController::class)->store($request));
    }

    public function update(Request $request, Sale $sale)
    {
        if ($sale->type !== 'sale_order') {
            abort(404);
        }

        if ($sale->status === 'completed') {
            $message = 'This Transaction already been converted. Please Close the window.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('sale-order')
                ->with('error', $message);
        }

        $request->merge(['type' => 'sale_order']);

        return $this->forceInvoiceRedirect(app(SaleController::class)->update($request, $sale));
    }

    public function createFromEstimate(Sale $sale)
    {
        if ($sale->type !== 'estimate') {
            abort(404);
        }

        if ($sale->status === 'converted') {
            return redirect()
                ->route('sale.estimate')
                ->with('error', 'This estimate is already converted.');
        }

        [$bankAccounts, $items, $parties, $brokers, $partyGroups, $warehouses, $customerPoDetailsEnabled] = $this->getFormDependencies();
        $nextInvoiceNumber = TransactionNumberPrefix::format('sale_order', (Sale::max('id') ?? 0) + 1);

        $sale->load(['items', 'details']);

        $convertedSaleData = $this->mapSourceSaleToOrderDraft($sale, $nextInvoiceNumber, [
            'source_type' => 'estimate',
            'source_estimate_id' => $sale->id,
        ]);

        return view('dashboard.saleorder.create-sale-order', compact(
            'bankAccounts',
            'items',
            'parties',
            'nextInvoiceNumber',
            'convertedSaleData',
            'brokers',
            'partyGroups',
            'warehouses',
            'customerPoDetailsEnabled'
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
                ->with('error', 'This proforma is already converted.');
        }

        [$bankAccounts, $items, $parties, $brokers, $partyGroups, $warehouses, $customerPoDetailsEnabled] = $this->getFormDependencies();
        $nextInvoiceNumber = TransactionNumberPrefix::format('sale_order', (Sale::max('id') ?? 0) + 1);

        $sale->load(['items', 'details']);

        $convertedSaleData = $this->mapSourceSaleToOrderDraft($sale, $nextInvoiceNumber, [
            'source_type' => 'proforma',
            'source_proforma_id' => $sale->id,
        ]);

        return view('dashboard.saleorder.create-sale-order', compact(
            'bankAccounts',
            'items',
            'parties',
            'nextInvoiceNumber',
            'convertedSaleData',
            'brokers',
            'partyGroups',
            'warehouses',
            'customerPoDetailsEnabled'
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
                ->with('error', 'This sale order is already converted.');
        }

        [$bankAccounts, $items, $parties, $brokers, $partyGroups, $warehouses, $customerPoDetailsEnabled] = $this->getFormDependencies();
        $nextInvoiceNumber = TransactionNumberPrefix::format('invoice', (Sale::max('id') ?? 0) + 1);

        $sale->load(['items', 'details']);

        $convertedSaleData = $this->mapSaleOrderToDraft($sale, $nextInvoiceNumber);

        return view('dashboard.saleorder.create-sale-order', compact(
            'bankAccounts',
            'items',
            'parties',
            'nextInvoiceNumber',
            'convertedSaleData',
            'brokers',
            'partyGroups',
            'warehouses',
            'customerPoDetailsEnabled'
        ));
    }

    private function getFormDependencies(): array
    {
        return [
            BankAccount::active()->orderBy('display_name')->get(),
            Item::active()->with('category')->orderBy('name')->get(),
            Party::orderBy('name')->get(),
            Broker::orderBy('name')->get(),
            PartyGroup::orderBy('name')->get(),
            Warehouse::where('is_active', true)->orderBy('name')->get(),
            AppSetting::getValue('customer_po_details_enabled', '0') === '1',
        ];
    }

    private function mapSaleOrderToDraft(Sale $saleOrder, string $nextInvoiceNumber): array
    {
        return $this->mapSourceSaleToOrderDraft($saleOrder, $nextInvoiceNumber, [
            'source_type' => 'sale_order',
            'source_sale_order_id' => $saleOrder->id,
        ]);
    }

    private function mapSaleOrderToEditDraft(Sale $saleOrder): array
    {
        $saleOrder->loadMissing(['items', 'payments', 'party', 'details']);
        $details = $saleOrder->details;
        $party = $saleOrder->party ? $saleOrder->party->toArray() : [];
        if (!empty($party)) {
            $party['billing_address'] = $party['billing_address'] ?: ($saleOrder->billing_address ?: '');
            $party['shipping_address'] = $party['shipping_address'] ?: ($saleOrder->shipping_address ?: '');
            $party['phone'] = $party['phone'] ?: ($saleOrder->phone ?: '');
        }

        return [
            'id' => $saleOrder->id,
            'type' => $saleOrder->type,
            'party_id' => $saleOrder->party_id,
            'party_name' => $saleOrder->display_party_name,
            'phone' => $saleOrder->phone,
            'billing_address' => $saleOrder->billing_address,
            'shipping_address' => $saleOrder->shipping_address,
            'bill_number' => $saleOrder->bill_number,
            'invoice_date' => optional($saleOrder->invoice_date)->format('Y-m-d'),
            'order_date' => optional($saleOrder->order_date)->format('Y-m-d'),
            'due_date' => optional($saleOrder->due_date)->format('Y-m-d'),
            'details' => $details?->toArray(),
            'custom_expenses' => $details?->custom_expenses,
            'warehouse_id' => $details?->warehouse_id,
            'delivery_person' => $details?->delivery_person,
            'po_no' => $details?->po_no,
            'po_date' => $details?->po_date,
            'city' => $details?->city,
            'party_no' => $details?->party_no,
            'goods_name' => $details?->goods_name,
            'details_extra' => $details?->details_extra,
            'bilti_gari_no' => $details?->bilti_gari_no,
            'terms_condition_name' => $details?->terms_condition_name,
            'terms_condition_text' => $details?->terms_condition_text,
            'invoice_extra_fields' => $details?->invoice_extra_fields,
            'payment_term_name' => $details?->payment_term_name,
            'payment_term_days' => $details?->payment_term_days,
            'additional_charges' => $details?->additional_charges,
            'transportation_details' => $details?->transportation_details,
            'broker_id' => $saleOrder->broker_id,
            'brokerage_type' => $saleOrder->brokerage_type,
            'brokerage_rate' => $saleOrder->brokerage_rate,
            'broker_amount' => $saleOrder->broker_amount,
            'received_amount' => $saleOrder->received_amount,
            'balance' => $saleOrder->balance,
            'discount_pct' => $saleOrder->discount_pct,
            'discount_rs' => $saleOrder->discount_rs,
            'tax_pct' => $saleOrder->tax_pct,
            'tax_amount' => $saleOrder->tax_amount,
            'round_off' => $saleOrder->round_off,
            'grand_total' => $saleOrder->grand_total,
            'total_qty' => $saleOrder->total_qty,
            'total_amount' => $saleOrder->total_amount,
            'labour' => $saleOrder->labour,
            'bardana' => $saleOrder->bardana,
            'rehra_mazdori' => $saleOrder->rehra_mazdori,
            'post_expense' => $saleOrder->post_expense,
            'extra_expense' => $saleOrder->extra_expense,
            'description' => $saleOrder->description,
            'image_path' => $saleOrder->image_path,
            'document_path' => $saleOrder->document_path,
            'created_at' => $saleOrder->created_at?->toISOString(),
            'updated_at' => $saleOrder->updated_at?->toISOString(),
            'party' => $party,
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
                    'tax_pct' => $item->tax_pct,
                    'tax_amount' => $item->tax_amount,
                    'free_qty' => $item->free_qty,
                    'free_quantity' => $item->free_qty,
                    'amount' => $item->amount,
                    'extra_fields' => $item->extra_fields ?? [],
                ];
            })->values()->all(),
            'payments' => $saleOrder->payments->map(function ($payment) {
                return [
                    'direction' => $payment->direction,
                    'payment_type' => $payment->payment_type,
                    'bank_account_id' => $payment->bank_account_id,
                    'amount' => $payment->amount,
                    'reference' => $payment->reference,
                ];
            })->values()->all(),
        ];
    }

    private function mapSourceSaleToOrderDraft(Sale $sale, string $nextInvoiceNumber, array $meta = []): array
    {
        $details = $sale->details;

        return array_merge([
            'party_id' => $sale->party_id,
            'party_name' => $sale->display_party_name,
            'phone' => $sale->phone,
            'billing_address' => $sale->billing_address,
            'shipping_address' => $sale->shipping_address,
            'bill_number' => $nextInvoiceNumber,
            'order_date' => now()->format('Y-m-d'),
            'due_date' => optional($sale->due_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'details' => $details?->toArray(),
            'custom_expenses' => $details?->custom_expenses,
            'total_qty' => $sale->total_qty,
            'total_amount' => $sale->total_amount,
            'discount_pct' => $sale->discount_pct,
            'discount_rs' => $sale->discount_rs,
            'tax_pct' => $sale->tax_pct,
            'tax_amount' => $sale->tax_amount,
            'round_off' => $sale->round_off,
            'grand_total' => $sale->grand_total,
            'received_amount' => 0,
            'balance' => $sale->grand_total ?? $sale->total_amount ?? 0,
            'payments' => [],
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
        ], $meta);
    }

    private function forceInvoiceRedirect($response)
    {
        if (!$response instanceof JsonResponse) {
            return $response;
        }

        $payload = $response->getData(true);
        $saleId = $payload['sale_id'] ?? null;

        if (!empty($payload['success']) && $saleId) {
            $invoiceUrl = route('invoice', [
                'sale_id' => $saleId,
                'type' => 'sale_order',
                'from_sale_order' => 1,
            ]);

            $payload['redirect_url'] = $invoiceUrl;
            $payload['share_url'] = $invoiceUrl;
        }

        return response()->json($payload, $response->getStatusCode());
    }
}
