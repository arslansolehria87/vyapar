<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\ItemUnit;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ItemController extends Controller
{
    private function defaultUnits(): array
    {
        return [
            ['id' => 'pcs', 'name' => 'PIECES', 'short_name' => 'PCS'],
            ['id' => 'box', 'name' => 'BOX', 'short_name' => 'BOX'],
            ['id' => 'pack', 'name' => 'PACK', 'short_name' => 'PACK'],
            ['id' => 'set', 'name' => 'SET', 'short_name' => 'SET'],
            ['id' => 'kg', 'name' => 'KILOGRAMS', 'short_name' => 'KG'],
            ['id' => 'g', 'name' => 'GRAM', 'short_name' => 'G'],
            ['id' => 'm', 'name' => 'METER', 'short_name' => 'M'],
            ['id' => 'ft', 'name' => 'FEET', 'short_name' => 'FT'],
            ['id' => 'l', 'name' => 'LITER', 'short_name' => 'L'],
            ['id' => 'ml', 'name' => 'MILLILITER', 'short_name' => 'ML'],
        ];
    }

    private function getStoredUnits(): array
    {
        if (!Schema::hasTable('item_units')) {
            return array_values(array_map(function ($unit) {
                $name = strtoupper(trim((string) ($unit['name'] ?? '')));
                $shortName = strtoupper(trim((string) ($unit['short_name'] ?? $unit['short'] ?? '')));

                return [
                    'id' => (string) ($unit['id'] ?? Str::slug($shortName ?: $name ?: uniqid('unit_'))),
                    'name' => $name,
                    'short_name' => $shortName ?: $name,
                ];
            }, $this->defaultUnits()));
        }

        if (ItemUnit::count() === 0) {
            $legacyStored = AppSetting::getValue('item_units', null);
            $legacyUnits = is_string($legacyStored) ? json_decode($legacyStored, true) : $legacyStored;
            $seedUnits = is_array($legacyUnits) && !empty($legacyUnits) ? $legacyUnits : $this->defaultUnits();

            foreach ($seedUnits as $unit) {
                $name = strtoupper(trim((string) ($unit['name'] ?? '')));
                $shortName = strtoupper(trim((string) ($unit['short_name'] ?? $unit['short'] ?? $unit['name'] ?? '')));

                if ($name === '' || $shortName === '') {
                    continue;
                }

                ItemUnit::firstOrCreate(
                    ['short_name' => $shortName],
                    [
                        'name' => $name,
                        'is_active' => true,
                    ]
                );
            }
        }

        return ItemUnit::query()
            ->where('is_active', true)
            ->orderBy('short_name')
            ->get()
            ->map(fn (ItemUnit $unit) => [
                'id' => (string) $unit->id,
                'name' => strtoupper(trim((string) $unit->name)),
                'short_name' => strtoupper(trim((string) $unit->short_name)),
            ])
            ->values()
            ->all();
    }

    private function itemListQuery(string $type, bool $includeInactive = false)
    {
        $query = Item::with('category');

        if ($type === 'product') {
            $query->where(function ($typeQuery) {
                $typeQuery->where('type', 'product')
                    ->orWhereNull('type');
            });
        } else {
            $query->where('type', $type);
        }

        if (!$includeInactive) {
            $query->active();
        }

        return $query;
    }

    private function normalizeDecimal(mixed $value, float $default = 0): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return $default;
    }

    private function normalizeCustomFieldsFromRequest(array $data): array
    {
        $fields = [];

        for ($i = 1; $i <= 6; $i++) {
            $value = trim((string) ($data["custom_field_{$i}_value"] ?? ''));
            $label = trim((string) ($data["custom_field_{$i}_name"] ?? 'Custom Field ' . $i));
            $enabled = filter_var($data["custom_field_{$i}_enabled"] ?? false, FILTER_VALIDATE_BOOLEAN);
            $showInPrint = filter_var($data["custom_field_{$i}_print"] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($label === '' && $value === '' && !$enabled && !$showInPrint) {
                continue;
            }

            $fields[] = [
                'key' => 'custom_field_' . $i,
                'enabled' => $enabled,
                'label' => $label !== '' ? $label : 'Custom Field ' . $i,
                'show_in_print' => $showInPrint,
                'value' => $value,
            ];
        }

        return $fields;
    }

    private function storeItemImages(Request $request): array
    {
        $imagePaths = [];

        // Handle the main item image first (from the new item image field)
        if ($request->hasFile('item_image')) {
            $image = $request->file('item_image');
            if ($image) {
                $imagePaths[] = $image->store('items', 'public');
            }
        } elseif ($request->hasFile('images')) {
            foreach ((array) $request->file('images') as $image) {
                if ($image) {
                    $imagePaths[] = $image->store('items', 'public');
                }
            }
        } elseif ($request->hasFile('image')) {
            $imagePaths[] = $request->file('image')->store('items', 'public');
        }

        if ($request->hasFile('item_images')) {
            foreach ((array) $request->file('item_images') as $image) {
                if ($image) {
                    $imagePaths[] = $image->store('items', 'public');
                }
            }
        }

        return $imagePaths;
    }

    private function normalizeItemImagePaths(?array $paths, ?string $fallbackPath = null): array
    {
        $normalized = array_values(array_filter($paths ?? []));

        if (empty($normalized) && $fallbackPath) {
            $normalized[] = $fallbackPath;
        }

        return $normalized;
    }

    private function deleteStoredImages(array $paths): void
    {
        foreach (array_filter($paths) as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function index(Request $request)
    {
        if ($request->has('json')) {
            $includeInactive = $request->boolean('include_inactive');
            $query = $this->itemListQuery('product', $includeInactive);
            if ($request->has('category_id'))
                $query->where('category_id', $request->category_id);
            if ($request->has('uncategorized'))
                $query->whereNull('category_id');
            return response()->json($query->get());
        }

        $products = $this->itemListQuery('product', true)->get();
        $units = $this->getStoredUnits();

        if ($products->isEmpty()) {
            return view('items.products', compact('products'));
        }

        return view('items.index', compact('products', 'units'));
    }

    public function services(Request $request)
    {
        if ($request->has('json')) {
            return response()->json(
                $this->itemListQuery('service', $request->boolean('include_inactive'))->get()
            );
        }

        $services = $this->itemListQuery('service', true)->get();
        $units = $this->getStoredUnits();

        return view('items.services', compact('services', 'units'));
    }

    public function create(Request $request)
    {
        $categories = Category::all();
        $units      = $this->getStoredUnits();
        $taxes      = [];
        
        // Check if this is a modal request
        if ($request->get('modal') == '1') {
            return view('items.create-modal', compact('categories', 'units', 'taxes'));
        }
        
        return view('items.create', compact('categories', 'units', 'taxes'));
    }

    public function store(Request $request)
    {
        $data = $request->isJson() ? $request->json()->all() : $request->all();
        $type = $data['type'] ?? 'product';
        $hasCustomFieldsColumn = Schema::hasColumn('items', 'custom_fields');

        $categoryId = null;
        if (!empty($data['category_id'])) {
            $categoryId = $data['category_id'];
        } elseif (!empty($data['category'])) {
            $cat = Category::where('name', $data['category'])->first();
            $categoryId = $cat?->id;
        }

        $imagePaths = $this->storeItemImages($request);
        $salePrice = $this->normalizeDecimal($data['sale_price'] ?? $data['price'] ?? 0);
        $purchasePrice = $this->normalizeDecimal($data['purchase_price'] ?? $data['cost_price'] ?? 0);
        $openingQty = $this->normalizeDecimal($data['opening_qty'] ?? 0);
        $minStock = $this->normalizeDecimal($data['min_stock'] ?? 0);
        $customFields = $this->normalizeCustomFieldsFromRequest($data);
        $itemStocksTableExists = Schema::hasTable('item_stocks');
        $itemStocksColumns = $itemStocksTableExists ? array_flip(Schema::getColumnListing('item_stocks')) : [];

        $item = DB::transaction(function () use (
            $data,
            $type,
            $categoryId,
            $imagePaths,
            $salePrice,
            $purchasePrice,
            $openingQty,
            $minStock,
            $hasCustomFieldsColumn,
            $customFields,
            $itemStocksTableExists,
            $itemStocksColumns
        ) {
            $item = Item::create([
                'type'            => $type,
                'name'            => $data['name'] ?? '',
                'category_id'     => $categoryId,
                'unit'            => $data['unit'] ?? '',
                'secondary_unit'  => Schema::hasColumn('items', 'secondary_unit') ? ($data['secondary_unit'] ?? null) : null,
                'unit_conversion_rate' => Schema::hasColumn('items', 'unit_conversion_rate')
                    ? $this->normalizeDecimal($data['unit_conversion_rate'] ?? null, 0)
                    : null,
                'bag_weight'      => Schema::hasColumn('items', 'bag_weight')
                    ? $this->normalizeDecimal($data['bag_weight'] ?? null, 0)
                    : null,
                'price'           => $salePrice,
                'sale_price'      => $salePrice,
                'wholesale_price' => $this->normalizeDecimal($data['wholesale_price'] ?? 0),
                'purchase_price'  => $purchasePrice,
                'opening_qty'     => $openingQty,
                'item_code'       => $data['item_code'] ?? null,
                'location'        => $data['location'] ?? null,
                'description'     => $data['description'] ?? null,
                'image_path'      => $imagePaths[0] ?? null,
                'image_paths'     => $imagePaths ?: null,
                'min_stock'       => $minStock,
                'is_active'       => array_key_exists('is_active', $data) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            ]);
            if ($hasCustomFieldsColumn) {
                $item->custom_fields = $customFields ?: null;
                $item->save();
            }

            if ($itemStocksTableExists) {
                $stockData = [
                    'item_id' => $item->id,
                    'opening_stock' => $openingQty,
                    'current_stock' => $openingQty,
                    'at_price' => $this->normalizeDecimal($data['at_price'] ?? $purchasePrice),
                    'as_of_date' => $data['as_of_date'] ?? now()->toDateString(),
                    'min_stock_level' => $minStock,
                    'location' => $data['location'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $stockData = array_filter(
                    $stockData,
                    fn ($value, $key) => isset($itemStocksColumns[$key]),
                    ARRAY_FILTER_USE_BOTH
                );

                DB::table('item_stocks')->insert($stockData);
            }

            return $item->load('category');
        });

        return response()->json([
            'redirect' => $type === 'service' ? route('items.services') : route('items'),
            'item'     => $item,
        ]);
    }

    public function edit(string $id)
    {
        $item       = Item::with('category')->findOrFail($id);
        $categories = Category::all();
        $units      = $this->getStoredUnits();
        $taxes      = [];
        return view('items.edit', compact('item', 'categories', 'units', 'taxes'));
    }

    public function update(Request $request, string $id)
    {
        $item = Item::findOrFail($id);
        $data = $request->isJson() ? $request->json()->all() : $request->all();

        $categoryId = $item->category_id;
        $hasCustomFieldsColumn = Schema::hasColumn('items', 'custom_fields');
        if (!empty($data['category_id'])) {
            $categoryId = $data['category_id'];
        } elseif (!empty($data['category'])) {
            $cat = Category::where('name', $data['category'])->first();
            $categoryId = $cat?->id;
        }

        $existingImagePaths = $this->normalizeItemImagePaths($item->image_paths, $item->image_path);
        $imagePaths = $existingImagePaths;

        if ($request->hasFile('images') || $request->hasFile('image')) {
            $this->deleteStoredImages($existingImagePaths);
            $imagePaths = $this->storeItemImages($request);
        }
        $customFields = $this->normalizeCustomFieldsFromRequest($data);

        $item->update([
            'name'            => $data['name']            ?? $item->name,
            'category_id'     => $categoryId,
            'unit'            => $data['unit']             ?? $item->unit,
            'secondary_unit'  => Schema::hasColumn('items', 'secondary_unit') ? ($data['secondary_unit'] ?? $item->secondary_unit) : null,
            'unit_conversion_rate' => Schema::hasColumn('items', 'unit_conversion_rate')
                ? $this->normalizeDecimal($data['unit_conversion_rate'] ?? $item->unit_conversion_rate, (float) ($item->unit_conversion_rate ?? 0))
                : null,
            'bag_weight'      => Schema::hasColumn('items', 'bag_weight')
                ? $this->normalizeDecimal($data['bag_weight'] ?? $item->bag_weight, (float) ($item->bag_weight ?? 0))
                : null,
            'price'           => $this->normalizeDecimal($data['sale_price'] ?? $data['price'] ?? $item->price, (float) ($item->price ?? $item->sale_price ?? 0)),
            'sale_price'      => $this->normalizeDecimal($data['sale_price'] ?? $item->sale_price, (float) $item->sale_price),
            'wholesale_price' => $this->normalizeDecimal($data['wholesale_price'] ?? $item->wholesale_price, (float) $item->wholesale_price),
            'purchase_price'  => $this->normalizeDecimal($data['purchase_price'] ?? $data['cost_price'] ?? $item->purchase_price, (float) $item->purchase_price),
            'opening_qty'     => $this->normalizeDecimal($data['opening_qty'] ?? $item->opening_qty, (float) $item->opening_qty),
            'item_code'       => $data['item_code']        ?? $item->item_code,
            'location'        => $data['location']         ?? $item->location,
            'description'     => $data['description']      ?? $item->description,
            'image_path'      => $imagePaths[0] ?? null,
            'image_paths'     => $imagePaths ?: null,
            'min_stock'       => $this->normalizeDecimal($data['min_stock'] ?? $item->min_stock, (float) $item->min_stock),
            'is_active'       => array_key_exists('is_active', $data) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : $item->is_active,
        ]);

        if ($hasCustomFieldsColumn) {
            $item->custom_fields = $customFields ?: null;
            $item->save();
        }

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function destroy(string $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        $item->delete();
        return response()->json(['message' => 'Item deleted successfully']);
    }

    // ── Category ─────────────────────────────────────────────────

    public function importValidItems(Request $request)
    {
        $payload = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.itemName' => ['required', 'string', 'max:255'],
            'items.*.itemCode' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.category' => ['nullable', 'string', 'max:255'],
            'items.*.baseUnit' => ['nullable', 'string', 'max:255'],
            'items.*.itemLocation' => ['nullable', 'string', 'max:255'],
            'items.*.salePrice' => ['nullable'],
            'items.*.purchasePrice' => ['nullable'],
            'items.*.wholesalePrice' => ['nullable'],
            'items.*.openingStock' => ['nullable'],
            'items.*.minStock' => ['nullable'],
        ]);

        $rows = $payload['items'];
        $nameCounts = [];
        $codeCounts = [];
        $created = [];
        $errors = [];
        $itemsTableColumns = array_flip(Schema::getColumnListing('items'));
        $itemStocksTableExists = Schema::hasTable('item_stocks');
        $itemStocksColumns = $itemStocksTableExists ? array_flip(Schema::getColumnListing('item_stocks')) : [];

        foreach ($rows as $row) {
            $nameKey = strtolower(trim((string) ($row['itemName'] ?? '')));
            $codeKey = strtolower(trim((string) ($row['itemCode'] ?? '')));

            if ($nameKey !== '') {
                $nameCounts[$nameKey] = ($nameCounts[$nameKey] ?? 0) + 1;
            }

            if ($codeKey !== '') {
                $codeCounts[$codeKey] = ($codeCounts[$codeKey] ?? 0) + 1;
            }
        }

        foreach ($rows as $index => $row) {
            $name = trim((string) ($row['itemName'] ?? ''));
            $itemCode = trim((string) ($row['itemCode'] ?? ''));
            $nameKey = strtolower($name);
            $codeKey = strtolower($itemCode);
            $rowErrors = [];

            if ($name === '') {
                $rowErrors[] = 'Item name is required.';
            }

            if ($nameKey !== '' && ($nameCounts[$nameKey] ?? 0) > 1) {
                $rowErrors[] = 'Duplicate item name found in import file.';
            }

            if ($codeKey !== '' && ($codeCounts[$codeKey] ?? 0) > 1) {
                $rowErrors[] = 'Duplicate item code found in import file.';
            }

            if ($nameKey !== '' && Item::whereRaw('LOWER(TRIM(name)) = ?', [$nameKey])->exists()) {
                $rowErrors[] = 'Item name already exists.';
            }

            if ($codeKey !== '' && Item::whereRaw('LOWER(TRIM(item_code)) = ?', [$codeKey])->exists()) {
                $rowErrors[] = 'Item code already exists.';
            }

            $validator = Validator::make($row, [
                'salePrice' => ['nullable', 'numeric'],
                'purchasePrice' => ['nullable', 'numeric'],
                'wholesalePrice' => ['nullable', 'numeric'],
                'openingStock' => ['nullable', 'numeric'],
                'minStock' => ['nullable', 'numeric'],
            ]);

            if ($validator->fails()) {
                $rowErrors = array_merge($rowErrors, $validator->errors()->all());
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $index + 1,
                    'itemName' => $name,
                    'itemCode' => $itemCode,
                    'error' => implode(' ', array_unique($rowErrors)),
                ];
                continue;
            }

            $categoryId = null;
            $categoryName = trim((string) ($row['category'] ?? ''));
            if ($categoryName !== '') {
                $category = Category::firstOrCreate(['name' => $categoryName]);
                $categoryId = $category->id;
            }

            $salePrice = $this->normalizeDecimal($row['salePrice'] ?? 0);
            $purchasePrice = $this->normalizeDecimal($row['purchasePrice'] ?? 0);
            $wholesalePrice = $this->normalizeDecimal($row['wholesalePrice'] ?? 0);
            $openingStock = $this->normalizeDecimal($row['openingStock'] ?? 0);
            $minStock = $this->normalizeDecimal($row['minStock'] ?? 0);

            $itemData = [
                'type' => 'product',
                'name' => $name,
                'category_id' => $categoryId,
                'unit' => trim((string) ($row['baseUnit'] ?? '')) ?: null,
                'price' => $salePrice,
                'sale_price' => $salePrice,
                'wholesale_price' => $wholesalePrice,
                'purchase_price' => $purchasePrice,
                'opening_qty' => $openingStock,
                'item_code' => $itemCode !== '' ? $itemCode : null,
                'location' => trim((string) ($row['itemLocation'] ?? '')) ?: null,
                'description' => trim((string) ($row['description'] ?? '')) ?: null,
                'min_stock' => $minStock,
                'is_active' => true,
            ];

            $itemData = array_filter(
                $itemData,
                fn ($value, $key) => isset($itemsTableColumns[$key]),
                ARRAY_FILTER_USE_BOTH
            );

            $item = Item::create($itemData);

            if ($itemStocksTableExists) {
                $stockData = [
                    'item_id' => $item->id,
                    'opening_stock' => $openingStock,
                    'current_stock' => $openingStock,
                    'at_price' => $purchasePrice,
                    'as_of_date' => now()->toDateString(),
                    'min_stock_level' => $minStock,
                    'location' => trim((string) ($row['itemLocation'] ?? '')) ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $stockData = array_filter(
                    $stockData,
                    fn ($value, $key) => isset($itemStocksColumns[$key]),
                    ARRAY_FILTER_USE_BOTH
                );

                DB::table('item_stocks')->insert($stockData);
            }

            $created[] = $item;
        }

        return response()->json([
            'success' => empty($errors),
            'message' => count($created) . ' valid items imported successfully.',
            'imported_count' => count($created),
            'error_count' => count($errors),
            'errors' => $errors,
            'items' => $created,
            'redirect' => route('items'),
        ], empty($errors) ? 200 : 422);
    }

    public function category(Request $request)
    {
        $categories = Category::withCount('items')->get();
        $uncategorizedCount = Item::whereNull('category_id')->count();
        return view('items.category', compact('categories', 'uncategorizedCount'));
    }

    public function categoryList()
    {
        return response()->json(Category::all());
    }

    public function exportItemsData()
    {
        $items = Item::with('category')
            ->where('type', 'product')
            ->orderBy('name')
            ->get();

        $rows = $items->map(function (Item $item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'description' => $item->description,
                'category' => $item->category?->name,
                'unit' => $item->unit,
                'sale_price' => $item->sale_price,
                'purchase_price' => $item->purchase_price,
                'opening_qty' => $item->opening_qty,
                'stock_qty' => $item->stock_qty,
                'min_stock' => $item->min_stock,
                'location' => $item->location,
                'bag_weight' => $item->bag_weight,
                'created_at' => optional($item->created_at)->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $rows,
            'count' => $rows->count(),
        ]);
    }

    public function exportItemsDownload()
    {
        $items = Item::with('category')
            ->where('type', 'product')
            ->orderBy('name')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Item Name',
            'Item Code',
            'Description',
            'Category',
            'Unit',
            'Sale Price',
            'Purchase Price',
            'Opening Qty',
            'Stock Qty',
            'Min Stock',
            'Location',
            'Created At',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        $rowNumber = 2;
        foreach ($items as $item) {
            $sheet->setCellValueByColumnAndRow(1, $rowNumber, $item->name);
            $sheet->setCellValueByColumnAndRow(2, $rowNumber, $item->item_code);
            $sheet->setCellValueByColumnAndRow(3, $rowNumber, $item->description);
            $sheet->setCellValueByColumnAndRow(4, $rowNumber, $item->category?->name);
            $sheet->setCellValueByColumnAndRow(5, $rowNumber, $item->unit);
            $sheet->setCellValueByColumnAndRow(6, $rowNumber, $item->sale_price);
            $sheet->setCellValueByColumnAndRow(7, $rowNumber, $item->purchase_price);
            $sheet->setCellValueByColumnAndRow(8, $rowNumber, $item->opening_qty);
            $sheet->setCellValueByColumnAndRow(9, $rowNumber, $item->stock_qty);
            $sheet->setCellValueByColumnAndRow(10, $rowNumber, $item->min_stock);
            $sheet->setCellValueByColumnAndRow(11, $rowNumber, $item->location);
            $sheet->setCellValueByColumnAndRow(12, $rowNumber, optional($item->created_at)->format('Y-m-d H:i:s'));
            $rowNumber++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'export-items-' . now()->format('YmdHis') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function bulkUpdateData()
    {
        $items = Item::with('category')
            ->where('type', 'product')
            ->orderBy('name')
            ->get();

        $stockMap = [];
        if (Schema::hasTable('item_stocks')) {
            $stockMap = DB::table('item_stocks')
                ->select('item_id', 'opening_stock', 'current_stock', 'at_price', 'as_of_date', 'min_stock_level', 'location')
                ->orderByDesc('id')
                ->get()
                ->unique('item_id')
                ->keyBy('item_id');
        }

        $rows = $items->map(function (Item $item) use ($stockMap) {
            $stock = $stockMap[$item->id] ?? null;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'category_id' => $item->category_id,
                'category_name' => $item->category?->name,
                'item_code' => $item->item_code,
                'description' => $item->description,
                'purchase_price' => $item->purchase_price,
                'sale_price' => $item->sale_price,
                'opening_qty' => $stock->opening_stock ?? $item->opening_qty,
                'current_stock' => $stock->current_stock ?? $item->stock_qty,
                'at_price' => $stock->at_price ?? $item->purchase_price,
                'as_of_date' => isset($stock->as_of_date) ? (string) $stock->as_of_date : optional($item->updated_at)->format('Y-m-d'),
                'min_stock' => $stock->min_stock_level ?? $item->min_stock,
                'location' => $stock->location ?? $item->location,
                'bag_weight' => $item->bag_weight,
                'tax_rate_id' => data_get($item->custom_fields, 'tax_rate_id'),
                'tax_rate_name' => data_get($item->custom_fields, 'tax_rate_name'),
                'tax_rate_value' => data_get($item->custom_fields, 'tax_rate_value', 0),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'items' => $rows,
            'tax_rates' => TaxRate::query()
                ->orderBy('rate')
                ->orderBy('name')
                ->get(['id', 'name', 'rate'])
                ->values(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $cat = Category::create(['name' => $request->name]);
        return response()->json(['category' => $cat]);
    }

    public function updateCategory(Request $request, $id)
    {
        $cat = Category::findOrFail($id);
        $cat->update(['name' => $request->name]);
        return response()->json(['success' => true, 'category' => $cat]);
    }

    public function destroyCategory($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Units ──────────────────────────────────────────────────────

    public function units(Request $request)
    {
        $units = $this->getStoredUnits();

        if ($request->has('json')) {
            return response()->json(['units' => $units]);
        }

        return view('items.units', compact('units'));
    }

    public function storeUnit(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:50'],
        ]);

        $name = strtoupper(trim($payload['name']));
        $shortName = strtoupper(trim($payload['short_name']));

        if (!Schema::hasTable('item_units')) {
            return response()->json(['message' => 'Item units table does not exist. Please run migration first.'], 500);
        }

        $exists = ItemUnit::query()
            ->whereRaw('UPPER(name) = ?', [$name])
            ->orWhereRaw('UPPER(short_name) = ?', [$shortName])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Unit name or short name already exists.',
            ], 422);
        }

        $unit = ItemUnit::create([
            'name' => $name,
            'short_name' => $shortName,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'unit' => [
                'id' => (string) $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
            ],
            'units' => $this->getStoredUnits(),
        ]);
    }

    public function updateUnit(Request $request, $id)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:50'],
        ]);

        if (!Schema::hasTable('item_units')) {
            return response()->json(['message' => 'Item units table does not exist. Please run migration first.'], 500);
        }

        $unit = ItemUnit::find($id);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found.'], 404);
        }

        $name = strtoupper(trim($payload['name']));
        $shortName = strtoupper(trim($payload['short_name']));

        $exists = ItemUnit::query()
            ->where('id', '!=', $unit->id)
            ->where(function ($query) use ($name, $shortName) {
                $query->whereRaw('UPPER(name) = ?', [$name])
                    ->orWhereRaw('UPPER(short_name) = ?', [$shortName]);
            })
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Unit name or short name already exists.'], 422);
        }

        $unit->update([
            'name' => $name,
            'short_name' => $shortName,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'unit' => [
                'id' => (string) $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
            ],
            'units' => $this->getStoredUnits(),
        ]);
    }

    public function destroyUnit($id)
    {
        if (!Schema::hasTable('item_units')) {
            return response()->json(['message' => 'Item units table does not exist. Please run migration first.'], 500);
        }

        $unit = ItemUnit::find($id);
        if ($unit) {
            $unit->delete();
        }

        return response()->json(['success' => true, 'units' => $this->getStoredUnits()]);
    }

    public function show(string $id)
    {
        $item = Item::with('category')->findOrFail($id);

        // If details are requested, return JSON
        if (request()->boolean('details')) {
            return response()->json($item);
        }

        return view('items.show', compact('item'));
    }

    public function transactions(string $id)
    {
        $item = Item::findOrFail($id);

        SaleItem::whereNull('item_id')
            ->where('item_name', $item->name)
            ->update(['item_id' => $item->id]);

        $saleItems = SaleItem::with(['sale.party', 'sale.details'])
            ->where(function ($q) use ($id, $item) {
                $q->where('item_id', $id)
                  ->orWhere(function ($q2) use ($item) {
                      $q2->whereNull('item_id')
                         ->where('item_name', $item->name);
                  });
            })
            ->get();

        $typeMap = [
            'invoice'          => 'Sale',
            'sale_return'      => 'Credit Note',
            'proforma'         => 'Proforma Invoice',
            'sale_order'       => 'Sale Order',
            'delivery_challan' => 'Delivery Challan',
            'estimate'         => 'Estimate',
            'pos'              => 'Sale',
        ];

        $transactions = $saleItems->map(function ($si) use ($typeMap) {
            $sale = $si->sale;
            if (!$sale) return null;

            $brokerAdjustmentRows = $this->resolveMinusBrokerRowsForSale($sale);
            $minusBrokerAmount = collect($brokerAdjustmentRows)->sum(fn (array $row) => (float) ($row['amount'] ?? 0));

            $ledgerTransaction = Transaction::query()
                ->where('transfer_group', 'sale-ledger-' . $sale->id)
                ->where('number', $sale->bill_number ?: (string) $sale->id)
                ->first();
            $transactionItemAmount = null;

            if ($ledgerTransaction && !empty($si->item_id)) {
                $transactionItemAmount = TransactionItem::query()
                    ->where('transaction_id', $ledgerTransaction->id)
                    ->where('item_id', $si->item_id)
                    ->value('amount');
            }

            $resolvedAmount = $transactionItemAmount;

            if ($minusBrokerAmount > 0) {
                $resolvedAmount = $this->resolveAdjustedSaleItemAmount($sale, $si, $minusBrokerAmount);
            } elseif ($resolvedAmount === null) {
                $resolvedAmount = (float) ($si->amount ?? 0);
            }

            return [
                'id'       => $sale->id,
                'type'     => $typeMap[$sale->type] ?? ucfirst($sale->type),
                'raw_type' => $sale->type,
                'invoice'  => $sale->bill_number ?? $sale->id,
                'name'     => $sale->party?->name ?? 'Walk-in Customer',
                'date'     => $sale->invoice_date
                                ? \Carbon\Carbon::parse($sale->invoice_date)->format('d/m/Y')
                                : \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y'),
                'qty'      => $si->quantity ?? 0,
                'net_w'    => $si->net_w ?? 0,
                'unit'     => $si->unit ?? '',
                'price'    => $si->unit_price ?? 0,
                'broker'   => collect($brokerAdjustmentRows)
                    ->pluck('name')
                    ->filter()
                    ->unique()
                    ->implode(', '),
                'status'   => $sale->status ?? 'Unpaid',
                'amount'   => $resolvedAmount ?? $si->amount ?? 0,
                'isAdd'    => !in_array($sale->type, ['sale_return']),
            ];
        })->filter()->values();

        return response()->json($transactions);
    }

    private function resolveMinusBrokerRowsForSale(Sale $sale): array
    {
        $rows = $this->normalizeCustomExpenseRows($sale->details?->custom_expenses);

        return collect($rows)
            ->filter(function ($row) {
                if (!is_array($row)) {
                    return false;
                }

                $mode = strtoupper((string) ($row['mode'] ?? $row['operator'] ?? ''));
                $accountType = strtolower(trim((string) ($row['account_type'] ?? '')));
                $amount = (float) ($row['amount'] ?? $row['value'] ?? 0);

                return $mode === '-'
                    && in_array($accountType, ['broker', 'party'], true)
                    && $amount > 0;
            })
            ->map(function (array $row) {
                $accountType = strtolower(trim((string) ($row['account_type'] ?? '')));
                $name = trim((string) ($row['account_name'] ?? $row['brokerName'] ?? ''));

                if ($name === '') {
                    $name = $accountType === 'party' ? 'Selected Party' : 'Selected Broker';
                }

                return [
                    'name' => $name,
                    'account_type' => $accountType,
                    'amount' => round((float) ($row['amount'] ?? $row['value'] ?? 0), 2),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeCustomExpenseRows($rawRows): array
    {
        if (is_array($rawRows)) {
            return $rawRows;
        }

        if (is_string($rawRows) && trim($rawRows) !== '') {
            $decoded = json_decode($rawRows, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function resolveAdjustedSaleItemAmount(Sale $sale, SaleItem $saleItem, float $minusBrokerAmount): float
    {
        $itemAmount = (float) ($saleItem->amount ?? 0);

        if ($minusBrokerAmount <= 0) {
            return round($itemAmount, 2);
        }

        $saleItems = $sale->relationLoaded('items')
            ? $sale->items
            : $sale->items()->get();

        $baseTotal = (float) $saleItems->sum(function ($row) {
            return (float) ($row->amount ?? 0);
        });

        if ($baseTotal <= 0) {
            return round($itemAmount, 2);
        }

        $remainingDeduction = min($minusBrokerAmount, $baseTotal);
        $positiveItems = $saleItems->filter(fn ($row) => (float) ($row->amount ?? 0) > 0)->values();
        $lastPositiveIndex = max(0, $positiveItems->count() - 1);

        foreach ($positiveItems as $index => $row) {
            $rowAmount = (float) ($row->amount ?? 0);

            if ($remainingDeduction <= 0) {
                $adjustedAmount = $rowAmount;
            } elseif ($index === $lastPositiveIndex) {
                $cutAmount = min($rowAmount, $remainingDeduction);
                $adjustedAmount = max(0, $rowAmount - $cutAmount);
                $remainingDeduction -= $cutAmount;
            } else {
                $share = round(($rowAmount / $baseTotal) * $minusBrokerAmount, 2);
                $cutAmount = min($rowAmount, min($share, $remainingDeduction));
                $adjustedAmount = max(0, $rowAmount - $cutAmount);
                $remainingDeduction -= $cutAmount;
            }

            if ((int) $row->id === (int) $saleItem->id) {
                return round($adjustedAmount, 2);
            }
        }

        return round($itemAmount, 2);
    }

    public function adjust(Request $request, string $id)
    {
        $item = Item::findOrFail($id);

        $qty   = floatval($request->input('qty', 0));
        $isAdd = filter_var($request->input('is_add', true), FILTER_VALIDATE_BOOLEAN);

        if ($qty <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid quantity'], 422);
        }

        if (!$isAdd) {
            $currentStock = floatval($item->stock_qty ?? $item->opening_qty ?? 0);
            if ($qty > $currentStock) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot reduce. Current stock is only {$currentStock}."
                ], 422);
            }
        }

        if ($isAdd) {
            $item->opening_qty = floatval($item->opening_qty) + $qty;
        } else {
            $item->opening_qty = floatval($item->opening_qty) - $qty;
        }

        $item->save();

        return response()->json([
            'success'     => true,
            'opening_qty' => $item->opening_qty,
            'stock_qty'   => $item->stock_qty,
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $updates = $request->input('updates', []);

        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'No updates provided'], 422);
        }

        try {
            foreach ($updates as $itemId => $fields) {
                $item = Item::find($itemId);
                if (!$item) continue;

                $stockColumns = Schema::hasTable('item_stocks')
                    ? array_flip(Schema::getColumnListing('item_stocks'))
                    : [];

                if (isset($fields['name']) && $fields['name'] !== null && $fields['name'] !== '') {
                    $item->name = $fields['name'];
                }
                if (array_key_exists('category_id', $fields)) {
                    $item->category_id = $fields['category_id'] ?: null;
                }
                if (array_key_exists('item_code', $fields)) {
                    $item->item_code = $fields['item_code'] !== '' ? $fields['item_code'] : null;
                }
                if (array_key_exists('unit', $fields)) {
                    $item->unit = $fields['unit'] !== '' ? $fields['unit'] : null;
                }
                if (Schema::hasColumn('items', 'secondary_unit') && array_key_exists('secondary_unit', $fields)) {
                    $item->secondary_unit = $fields['secondary_unit'] !== '' ? $fields['secondary_unit'] : null;
                }
                if (Schema::hasColumn('items', 'unit_conversion_rate') && array_key_exists('unit_conversion_rate', $fields)) {
                    $item->unit_conversion_rate = $fields['unit_conversion_rate'] !== '' ? floatval($fields['unit_conversion_rate']) : 0;
                }
                if (array_key_exists('description', $fields) && Schema::hasColumn('items', 'description')) {
                    $item->description = $fields['description'] !== '' ? $fields['description'] : null;
                }
                if (Schema::hasColumn('items', 'bag_weight') && array_key_exists('bag_weight', $fields) && $fields['bag_weight'] !== '') {
                    $item->bag_weight = floatval($fields['bag_weight']);
                }
                if (isset($fields['sale_price']) && $fields['sale_price'] !== null && $fields['sale_price'] !== '') {
                    $item->sale_price = floatval($fields['sale_price']);
                }
                if (isset($fields['purchase_price']) && $fields['purchase_price'] !== null && $fields['purchase_price'] !== '') {
                    $item->purchase_price = floatval($fields['purchase_price']);
                }
                if (isset($fields['opening_qty']) && $fields['opening_qty'] !== null && $fields['opening_qty'] !== '') {
                    $item->opening_qty = floatval($fields['opening_qty']);
                }
                if (isset($fields['min_stock']) && $fields['min_stock'] !== null && $fields['min_stock'] !== '') {
                    $item->min_stock = floatval($fields['min_stock']);
                }
                if (array_key_exists('location', $fields)) {
                    $item->location = $fields['location'] !== '' ? $fields['location'] : null;
                }
                if (
                    array_key_exists('tax_rate_id', $fields)
                    || array_key_exists('tax_rate_name', $fields)
                    || array_key_exists('tax_rate_value', $fields)
                ) {
                    $customFields = is_array($item->custom_fields) ? $item->custom_fields : [];
                    $taxRateId = $fields['tax_rate_id'] ?? null;

                    if ($taxRateId === null || $taxRateId === '') {
                        unset(
                            $customFields['tax_rate_id'],
                            $customFields['tax_rate_name'],
                            $customFields['tax_rate_value']
                        );
                    } else {
                        $taxRate = TaxRate::query()->find($taxRateId);
                        if (!$taxRate) {
                            throw new \InvalidArgumentException('Selected tax slab is not available.');
                        }

                        $customFields['tax_rate_id'] = $taxRate->id;
                        $customFields['tax_rate_name'] = $taxRate->name;
                        $customFields['tax_rate_value'] = (float) $taxRate->rate;
                    }

                    $item->custom_fields = $customFields;
                }

                $item->save();

                if (!empty($stockColumns)) {
                    $payload = [];

                    if (isset($stockColumns['opening_stock']) && isset($fields['opening_qty']) && $fields['opening_qty'] !== '') {
                        $payload['opening_stock'] = floatval($fields['opening_qty']);
                    }
                    if (isset($stockColumns['current_stock']) && isset($fields['opening_qty']) && $fields['opening_qty'] !== '') {
                        $payload['current_stock'] = floatval($fields['opening_qty']);
                    }
                    if (isset($stockColumns['at_price']) && isset($fields['at_price']) && $fields['at_price'] !== '') {
                        $payload['at_price'] = floatval($fields['at_price']);
                    }
                    if (isset($stockColumns['as_of_date']) && isset($fields['as_of_date']) && $fields['as_of_date'] !== '') {
                        $payload['as_of_date'] = $fields['as_of_date'];
                    }
                    if (isset($stockColumns['min_stock_level']) && isset($fields['min_stock']) && $fields['min_stock'] !== '') {
                        $payload['min_stock_level'] = floatval($fields['min_stock']);
                    }
                    if (isset($stockColumns['location']) && array_key_exists('location', $fields)) {
                        $payload['location'] = $fields['location'] !== '' ? $fields['location'] : null;
                    }

                    if (!empty($payload)) {
                        $stockRecord = DB::table('item_stocks')->where('item_id', $item->id)->orderByDesc('id')->first();

                        if ($stockRecord) {
                            $payload['updated_at'] = now();
                            DB::table('item_stocks')->where('id', $stockRecord->id)->update($payload);
                        } else {
                            DB::table('item_stocks')->insert(array_merge([
                                'item_id' => $item->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ], $payload));
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Items updated successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer', 'exists:items,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $updated = Item::whereIn('id', $data['item_ids'])->update([
            'is_active' => $data['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => $data['is_active']
                ? 'Selected items marked active.'
                : 'Selected items marked inactive.',
        ]);
    }
}
