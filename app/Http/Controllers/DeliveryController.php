<?php

namespace App\Http\Controllers;

use App\Models\ChallanDetail;
use App\Models\Broker;
use App\Models\BankAccount;
use App\Models\Item;
use App\Models\Party;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Support\TransactionNumberPrefix;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\DeliveryChallanAssignedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    public function deliveryChallan()
    {
        $challans = Sale::where('type', 'delivery_challan')
            ->with(['items', 'challanDetail', 'party'])
            ->orderByDesc('created_at')
            ->get();

        $convertedInvoices = Sale::where('type', 'invoice')
            ->whereNotNull('reference_id')
            ->get()
            ->keyBy('reference_id');

        return view('dashboard.delivery.delivery-challan', compact('challans', 'convertedInvoices'));
    }

    public function createChallan()
    {
        return $this->renderChallanForm();
    }

    public function edit(Sale $sale)
    {
        abort_unless($sale->type === 'delivery_challan', 404);

        $sale->load(['items', 'challanDetail', 'details', 'party', 'payments']);
        $this->hydrateDeliverySaleDetails($sale);

        return $this->renderChallanForm($sale);
    }

    public function duplicate(Sale $sale)
    {
        abort_unless($sale->type === 'delivery_challan', 404);

        $sale->load(['items', 'challanDetail', 'details', 'party', 'payments']);
        $this->hydrateDeliverySaleDetails($sale);

        return $this->renderChallanForm(null, $sale);
    }

    private function renderChallanForm(?Sale $challan = null, ?Sale $duplicateChallan = null)
{
    if ($challan) {
        $challan->loadMissing(['items', 'challanDetail', 'details', 'party', 'payments']);
        $this->hydrateDeliverySaleDetails($challan);
    }

    if ($duplicateChallan) {
        $duplicateChallan->loadMissing(['items', 'challanDetail', 'details', 'party', 'payments']);
        $this->hydrateDeliverySaleDetails($duplicateChallan);
    }

    $items = Item::active()->orderBy('name')->get();
    $parties = Party::orderBy('name')->get();
    $brokers = Broker::orderBy('name')->get();
    $bankAccounts = BankAccount::orderBy('bank_name')->get();
    $users = User::orderBy('name')->get(['id', 'name', 'email']);
    $warehouses = Warehouse::with('responsibleUser:id,name,email')
        ->where('is_active', true)
        ->orderBy('name')
        ->get();
    $partyGroups = \App\Models\PartyGroup::orderBy('name')->get();
    $nextSaleId = (Sale::max('id') ?? 0) + 1;
    $nextInvoiceNumber = TransactionNumberPrefix::format('delivery_challan', $nextSaleId);

    return view('dashboard.delivery.create-challan', compact(
        'items', 'parties', 'brokers', 'bankAccounts', 'users',
        'warehouses', 'nextInvoiceNumber', 'challan', 'duplicateChallan',
        'partyGroups'
    ));
}

    public function store(Request $request)
    {
        $data = $this->validateChallanRequest($request);

        $sale = DB::transaction(function () use ($request, $data) {
            [$imagePaths, $primaryImagePath] = $this->storeChallanImages($request);

            $sale = Sale::create($this->buildSalePayload($data, $primaryImagePath, $imagePaths));

            foreach ($data['items'] as $item) {
                $sale->items()->create($this->buildItemPayload($item));
            }

            $challanDetail = ChallanDetail::create($this->buildChallanDetailPayload($data, $sale));
            $this->upsertSaleDetails($sale, $data);

            $this->notifyResponsibleUser($challanDetail, $sale);

            return $sale;
        });

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => route('invoice', ['sale_id' => $sale->id, 'doc' => 'delivery_challan']),
            'share_url' => route('invoice', ['sale_id' => $sale->id, 'doc' => 'delivery_challan']),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        abort_unless($sale->type === 'delivery_challan', 404);

        $data = $this->validateChallanRequest($request);

        DB::transaction(function () use ($request, $data, $sale) {
            $existingImagePaths = collect($sale->image_paths ?? [])
                ->filter()
                ->values()
                ->all();

            if (empty($existingImagePaths) && $sale->image_path) {
                $existingImagePaths = [$sale->image_path];
            }

            [$imagePaths, $primaryImagePath] = $this->storeChallanImages($request, $data['existing_image_paths'] ?? [], $existingImagePaths);

            $sale->update($this->buildSalePayload($data, $primaryImagePath, $imagePaths, $sale));
            $sale->items()->delete();

            foreach ($data['items'] as $item) {
                $sale->items()->create($this->buildItemPayload($item));
            }

            $challanDetail = $sale->challanDetail()->updateOrCreate(
                ['sale_id' => $sale->id],
                $this->buildChallanDetailPayload($data, $sale)
            );
            $this->upsertSaleDetails($sale, $data);

            $this->notifyResponsibleUser($challanDetail, $sale);
        });

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'redirect_url' => route('delivery-challan'),
            'share_url' => route('invoice', ['sale_id' => $sale->id, 'doc' => 'delivery_challan']),
        ]);
    }

    public function destroy(Sale $sale)
    {
        abort_unless($sale->type === 'delivery_challan', 404);

        foreach (array_filter($sale->image_paths ?? [$sale->image_path]) as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $sale->challanDetail()?->delete();
        $sale->items()->delete();
        $sale->payments()->delete();
        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery challan deleted successfully.',
        ]);
    }

    public function preview(Sale $sale)
    {
        abort_unless($sale->type === 'delivery_challan', 404);
        return redirect()->route('sale.invoice-preview', ['sale' => $sale->id, 'doc' => 'delivery_challan']);
    }

    public function print(Sale $sale)
    {
        abort_unless($sale->type === 'delivery_challan', 404);
        return redirect()->route('sale.invoice-preview', ['sale' => $sale->id, 'doc' => 'delivery_challan', 'print' => 1]);
    }

    public function pdf(Request $request, Sale $sale)
    {
        abort_unless($sale->type === 'delivery_challan', 404);
        return redirect()->route('sale.invoice-pdf', ['sale' => $sale->id, 'doc' => 'delivery_challan', 'download' => 1]);
    }

    private function validateChallanRequest(Request $request): array
    {
        if (is_string($request->input('items'))) {
            $decodedItems = json_decode($request->input('items'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge([
                    'items' => $decodedItems,
                ]);
            }
        }

        return $request->validate([
            'party_id' => 'nullable|exists:parties,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'broker_name' => 'nullable|string|max:255',
            'broker_phone' => 'nullable|string|max:50',
            'brokerage_type' => 'nullable|string|max:50',
            'brokerage_rate' => 'nullable|numeric|min:0',
            'broker_amount' => 'nullable|numeric|min:0',
            'phone' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:1000',
            'bill_number' => 'required|string|max:100',
            'invoice_date' => 'nullable|date',
            'order_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'deal_days' => 'nullable|integer|min:0',
            'tadad' => 'nullable|integer|min:0',
            'total_wazan' => 'nullable|numeric|min:0',
            'safi_wazan' => 'nullable|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'deo' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'warehouse_name' => 'nullable|string|max:255',
            'warehouse_phone' => 'nullable|string|max:50',
            'warehouse_handler_name' => 'nullable|string|max:255',
            'warehouse_handler_phone' => 'nullable|string|max:50',
            'responsible_user_id' => 'nullable|exists:users,id',
            'vehicle_number' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:255',
            'delivery_expenses' => 'nullable|numeric|min:0',
            'delivery_person' => 'nullable|string|max:255',
            'po_no' => 'nullable|string|max:255',
            'po_date' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'party_no' => 'nullable|string|max:255',
            'goods_name' => 'nullable|string|max:255',
            'details_extra' => 'nullable|string|max:255',
            'bilti_gari_no' => 'nullable|string|max:255',
            'custom_expenses' => 'nullable|array',
            'total_qty' => 'nullable|integer|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'discount_pct' => 'nullable|numeric|min:0',
            'discount_rs' => 'nullable|numeric|min:0',
            'tax_pct' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'grand_total' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
            'existing_image_paths' => 'nullable|array',
            'existing_image_paths.*' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|image',
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
        ]);
    }

    private function buildSalePayload(array $data, ?string $primaryImagePath = null, array $imagePaths = [], ?Sale $existingSale = null): array
    {
        return [
            'type' => 'delivery_challan',
            'party_id' => $data['party_id'] ?? null,
            'broker_id' => $data['broker_id'] ?? null,
            'brokerage_type' => $data['brokerage_type'] ?? null,
            'brokerage_rate' => $data['brokerage_rate'] ?? 0,
            'broker_amount' => $data['broker_amount'] ?? 0,
            'phone' => $data['phone'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'bill_number' => $data['bill_number'],
            'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
            'order_date' => $data['order_date'] ?? ($data['invoice_date'] ?? now()->toDateString()),
            'due_date' => $data['due_date'] ?? ($data['invoice_date'] ?? now()->toDateString()),
            'deal_days' => $data['deal_days'] ?? 0,
            'tadad' => $data['tadad'] ?? ($data['total_qty'] ?? 0),
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
            'grand_total' => $data['grand_total'] ?? 0,
            'received_amount' => 0,
            'balance' => $data['grand_total'] ?? 0,
            'status' => $data['status'] ?? 'open',
            'description' => $data['description'] ?? null,
            'image_path' => $primaryImagePath ?? $data['image_path'] ?? null,
            'image_paths' => !empty($imagePaths) ? array_values($imagePaths) : null,
            'document_path' => $data['document_path'] ?? null,
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

    private function buildChallanDetailPayload(array $data, Sale $sale): array
    {
        return [
            'sale_id' => $sale->id,
            'challan_number' => $sale->bill_number,
            'invoice_date' => $sale->invoice_date,
            'due_date' => $sale->due_date,
            'broker_name' => $data['broker_name'] ?? null,
            'broker_phone' => $data['broker_phone'] ?? null,
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'warehouse_name' => $data['warehouse_name'] ?? null,
            'warehouse_phone' => $data['warehouse_phone'] ?? null,
            'warehouse_handler_name' => $data['warehouse_handler_name'] ?? null,
            'warehouse_handler_phone' => $data['warehouse_handler_phone'] ?? null,
            'responsible_user_id' => $data['responsible_user_id'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'destination' => $data['destination'] ?? null,
            'delivery_expenses' => $data['delivery_expenses'] ?? 0,
        ];
    }

    private function buildItemPayload(array $item): array
    {
        return [
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
        ];
    }

    private function upsertSaleDetails(Sale $sale, array $data): void
    {
        $sale->details()->updateOrCreate(
            ['sale_id' => $sale->id],
            [
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'delivery_person' => $data['delivery_person'] ?? null,
                'po_no' => $data['po_no'] ?? null,
                'po_date' => $data['po_date'] ?? null,
                'city' => $data['city'] ?? null,
                'party_no' => $data['party_no'] ?? null,
                'goods_name' => $data['goods_name'] ?? null,
                'details_extra' => $data['details_extra'] ?? null,
                'bilti_gari_no' => $data['bilti_gari_no'] ?? null,
                'custom_expenses' => $data['custom_expenses'] ?? null,
            ]
        );
    }

    private function hydrateDeliverySaleDetails(Sale $sale): void
    {
        $challanDetail = $sale->challanDetail;
        $saleDetail = $sale->details;

        if ($saleDetail) {
            if ($challanDetail && empty($saleDetail->warehouse_id) && !empty($challanDetail->warehouse_id)) {
                $saleDetail->warehouse_id = $challanDetail->warehouse_id;
            }
            $sale->setRelation('details', $saleDetail);
            return;
        }

        $sale->setRelation('details', new SaleDetail([
            'sale_id' => $sale->id,
            'warehouse_id' => $challanDetail?->warehouse_id,
            'delivery_person' => $challanDetail?->warehouse_handler_name,
            'city' => $sale->party?->city,
            'goods_name' => $challanDetail?->destination,
            'bilti_gari_no' => $challanDetail?->vehicle_number,
        ]));
    }

    private function storeChallanImages(Request $request, array $existingPaths = [], array $originalPaths = []): array
    {
        $paths = collect($existingPaths)->filter()->values()->all();

        if ($request->hasFile('images')) {
            foreach ($request->file('images', []) as $image) {
                if ($image instanceof UploadedFile) {
                    $paths[] = $image->store('delivery-challans', 'public');
                }
            }
        }

        $paths = array_values(array_unique(array_filter($paths)));

        $deletedPaths = array_diff(array_filter($originalPaths), $paths);
        foreach ($deletedPaths as $deletedPath) {
            Storage::disk('public')->delete($deletedPath);
        }

        return [$paths, $paths[0] ?? null];
    }

    private function notifyResponsibleUser(ChallanDetail $challanDetail, Sale $sale): void
    {
        $responsibleUser = $challanDetail->responsibleUser;

        if (!$responsibleUser) {
            return;
        }

        $sale->loadMissing(['party', 'challanDetail']);
        $responsibleUser->notify(new DeliveryChallanAssignedNotification($sale));
        $challanDetail->forceFill([
            'notification_sent_at' => now(),
        ])->save();
    }
    public function getNextNumber()
{
    $nextSaleId = (Sale::max('id') ?? 0) + 1;
    $nextInvoiceNumber = TransactionNumberPrefix::format('delivery_challan', $nextSaleId);

    return response()->json([
        'bill_number' => $nextInvoiceNumber
    ]);
}
}
