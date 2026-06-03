@php
    $itemFormSettings = $itemFormSettings ?? json_decode(\App\Models\AppSetting::getValue('item_form_settings', '{}'), true) ?: [];
    $itemEnable = (bool) data_get($itemFormSettings, 'enable_item', true);
    $sellType = (string) data_get($itemFormSettings, 'sell_type', 'both');
    $showProducts = $itemEnable && in_array($sellType, ['product', 'both'], true);
    $showServices = $itemEnable && in_array($sellType, ['service', 'both'], true);
    $itemTableProducts = $showProducts ? collect($saleItemsSource ?? $items ?? collect()) : collect();
    $itemTableServices = $showServices ? collect($serviceItemsSource ?? collect()) : collect();
    $itemTableCategories = $saleCategoryOptions ?? collect($itemTableProducts)
        ->map(function ($item) {
            return $item->category->name ?? $item->category_name ?? $item->category_id ?? null;
        })
        ->filter()
        ->unique()
        ->values();
@endphp

<div class="table-container">
    <table class="item-table">
        <thead>
            <tr>
                <th class="row-num">#</th>
                <th class="col-barcode-scan d-none"><i class="fa-solid fa-qrcode"></i></th>
                <th class="col-item-name">ITEM</th>
                <th class="col-serial-no d-none">SERIAL NO.</th>
                <th class="col-description d-none">DESCRIPTION</th>
                <th class="col-count d-none">COUNT</th>
                <th class="col-batch-no d-none">BATCH NO.</th>
                <th class="col-model-no d-none">MODEL NO.</th>
                <th class="col-exp-date d-none">EXP. DATE</th>
                <th class="col-mfg-date d-none">MFG. DATE</th>
                <th class="col-mrp d-none">MRP</th>
                <th class="col-size d-none">SIZE</th>
                <th class="col-tafseel">DETAIL</th>
                <th class="col-tadaat">QUANTITY</th>
                <th class="col-free-qty d-none">FREE QTY</th>
                <th class="col-gross-w">GROSS W</th>
                <th class="col-net-w">NET W</th>
                <th class="custom-size-th">UNIT</th>
                <th class="col-rate">RATE</th>
                <th class="col-amount">AMOUNT</th>
                <th class="col-category d-none">CATEGORY</th>
                <th class="col-item-code d-none">ITEM CODE</th>
                <th class="col-discount d-none">
                    <div class="compound-col-head">
                        <span class="header-main-label">DISCOUNT</span>
                        <div class="header-sub-labels">
                            <span>%</span>
                            <span>AMOUNT</span>
                        </div>
                    </div>
                </th>
                <th class="col-item-tax d-none">
                    <div class="compound-col-head">
                        <span class="header-main-label">TAX</span>
                        <div class="header-sub-labels">
                            <span>%</span>
                            <span>AMOUNT</span>
                        </div>
                    </div>
                </th>
                <th class="custom-item-field col-custom-field-1 d-none">CUSTOM FIELD 1</th>
                <th class="custom-item-field col-custom-field-2 d-none">CUSTOM FIELD 2</th>
                <th class="custom-item-field col-custom-field-3 d-none">CUSTOM FIELD 3</th>
                <th class="custom-item-field col-custom-field-4 d-none">CUSTOM FIELD 4</th>
                <th class="custom-item-field col-custom-field-5 d-none">CUSTOM FIELD 5</th>
                <th class="custom-item-field col-custom-field-6 d-none">CUSTOM FIELD 6</th>
                <th class="add-col" style="position: relative;">
                    <button type="button" class="btn-add-circle table-settings-btn" data-bs-toggle="modal" data-bs-target="#itemColumnModal">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </th>
            </tr>
        </thead>
        <tbody class="item-rows">
            <tr class="item-row">
                <td class="row-num">
                    <span class="row-index-text">1</span>
                    <div class="delete-row-icon"><i class="fa-solid fa-trash-can"></i></div>
                </td>
                <td class="col-barcode-scan d-none">
                    <button type="button" class="btn btn-sm btn-outline-primary open-scan-serial-modal" title="Scan code/serial"><i class="fa-solid fa-qrcode"></i></button>
                </td>
                <td class="col-item-name">
                    <div class="item-picker">
                        <input type="text" class="item-picker-input" placeholder="Search Item" style="position: relative; z-index: 10;">
                        <div class="item-picker-panel">
                            <div class="item-picker-add" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; color: #2563eb; font-weight: 600; cursor: pointer; border-bottom: 1px solid #e1e8ed;"><i class="fa-regular fa-square-plus"></i> Add Item</div>
                            <div class="item-picker-head" style="display: grid; grid-template-columns: minmax(0, 2fr) 100px 110px 80px 80px; gap: 12px; padding: 10px 18px; font-size: 12px; font-weight: 700; color: #97a3b6; text-transform: uppercase; background: #f8fbff; border-bottom: 1px solid #e1e8ed;">
                                <span>Item</span>
                                <span>Sale Price</span>
                                <span>Purchase Price</span>
                                <span>Stock</span>
                                <span>Weight</span>
                            </div>
                            <div class="item-picker-list" style="max-height: 280px; overflow-y: auto;">
                                @forelse($itemTableProducts as $item)
                                    <div class="item-picker-row item-picker-option" data-id="{{ $item->id }}" data-type="product">
                                        <div class="item-picker-name">
                                            {{ $item->name }}
                                            @if(!empty($item->item_code))
                                                <small>({{ $item->item_code }})</small>
                                            @endif
                                        </div>
                                        <div>{{ number_format((float) ($item->sale_price ?? $item->price ?? 0), 2, '.', '') }}</div>
                                        <div>{{ number_format((float) ($item->purchase_price ?? 0), 2, '.', '') }}</div>
                                        <div class="item-picker-stock {{ (float) ($item->opening_qty ?? 0) < 0 ? 'neg' : '' }}">{{ (float) ($item->opening_qty ?? 0) }}</div>
                                    </div>
                                @empty
                                    <div class="item-picker-empty">No items found</div>
                                @endforelse
                                @foreach($itemTableServices as $serviceItem)
                                    <div class="item-picker-row item-picker-option" data-id="{{ $serviceItem->id }}" data-type="service">
                                        <div class="item-picker-name">
                                            {{ $serviceItem->name }}
                                            @if(!empty($serviceItem->item_code))
                                                <small>({{ $serviceItem->item_code }})</small>
                                            @endif
                                            <small style="color: #f59e0b;">[Service]</small>
                                        </div>
                                        <div>{{ number_format((float) ($serviceItem->sale_price ?? $serviceItem->price ?? 0), 2, '.', '') }}</div>
                                        <div>{{ number_format((float) ($serviceItem->purchase_price ?? 0), 2, '.', '') }}</div>
                                        <div class="item-picker-stock">—</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <select class="form-select item-name d-none">
                            <option value="" selected disabled>Select Item</option>
                            @foreach($itemTableProducts as $item)
                                <option value="{{ $item->id }}"
                                    data-price="{{ $item->price }}"
                                    data-sale-price="{{ $item->sale_price }}"
                                    data-purchase-price="{{ $item->purchase_price }}"
                                    data-stock="{{ $item->opening_qty }}"
                                    data-location="{{ $item->location }}"
                                    data-label="{{ $item->name }}"
                                    data-rich-label="{{ $item->name }} | Sale: {{ $item->sale_price ?? $item->price ?? 0 }} | Stock: {{ $item->opening_qty ?? 0 }} | Location: {{ $item->location ?? '' }}"
                                    data-unit="{{ $item->unit }}"
                                    data-weight="{{ $item->bag_weight ?? 0 }}"
                                    data-category="{{ $item->category->name ?? $item->category_name ?? $item->category_id ?? '' }}"
                                    data-item-code="{{ $item->item_code ?? '' }}"
                                    data-description="{{ $item->description ?? $item->item_description ?? '' }}"
                                    data-discount="{{ $item->discount ?? 0 }}"
                                    data-type="product">
                                    {{ $item->name }} | Sale: {{ $item->sale_price ?? $item->price ?? 0 }} | Stock: {{ $item->opening_qty ?? 0 }} | Location: {{ $item->location ?? '' }}
                                </option>
                            @endforeach
                            @foreach($itemTableServices as $serviceItem)
                                <option value="{{ $serviceItem->id }}"
                                    data-price="{{ $serviceItem->price }}"
                                    data-sale-price="{{ $serviceItem->sale_price }}"
                                    data-purchase-price="{{ $serviceItem->purchase_price }}"
                                    data-stock="0"
                                    data-location=""
                                    data-label="{{ $serviceItem->name }}"
                                    data-rich-label="{{ $serviceItem->name }} (Service) | Sale: {{ $serviceItem->sale_price ?? $serviceItem->price ?? 0 }}"
                                    data-unit="{{ $serviceItem->unit }}"
                                    data-weight="0"
                                    data-category="{{ $serviceItem->category->name ?? $serviceItem->category_name ?? $serviceItem->category_id ?? '' }}"
                                    data-item-code="{{ $serviceItem->item_code ?? '' }}"
                                    data-description="{{ $serviceItem->description ?? $serviceItem->item_description ?? '' }}"
                                    data-discount="{{ $serviceItem->discount ?? 0 }}"
                                    data-type="service">
                                    {{ $serviceItem->name }} (Service) | Sale: {{ $serviceItem->sale_price ?? $serviceItem->price ?? 0 }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="col-serial-no d-none"><input type="text" class="item-serial-input" placeholder="Serial No."></td>
                <td class="col-description d-none"><input type="text" class="item-desc" placeholder="Description" readonly></td>
                <td class="col-count d-none"><input type="number" class="item-count-input" value="0" min="0" step="1"></td>
                <td class="col-batch-no d-none"><input type="text" class="item-batch-no-input" placeholder="Batch No."></td>
                <td class="col-model-no d-none"><input type="text" class="item-model-no-input" placeholder="Model No."></td>
                <td class="col-exp-date d-none"><input type="date" class="item-exp-date-input"></td>
                <td class="col-mfg-date d-none"><input type="date" class="item-mfg-date-input"></td>
                <td class="col-mrp d-none"><input type="number" class="item-mrp-input" value="0" min="0" step="0.01"></td>
                <td class="col-size d-none"><input type="text" class="item-size-input" placeholder="Size"></td>
                <td class="col-tafseel"><input type="text" class="item-tafseel" placeholder="Detail"></td>
                <td class="col-tadaat"><input type="number" class="item-qty tadaat-input" value="1"></td>
                <td class="col-free-qty d-none"><input type="number" class="item-free-qty" value="0" min="0" step="1"></td>
                <td class="col-gross-w"><input type="number" class="gross-w-input" value="0" min="0" step="0.01"></td>
                <td class="col-net-w"><input type="number" class="net-w-input" value="0" min="0" step="0.01"></td>
                <td class="custom-size-td">
                    <div class="item-unit-wrapper d-flex align-items-center gap-1">
                        <select class="item-unit">
                            <option value="">Select Unit</option>
                            <option value="PCS">PCS (Pieces)</option>
                            <option value="BOX">BOX</option>
                            <option value="PACK">PACK</option>
                            <option value="SET">SET</option>
                            <option value="KG">KG (Kilogram)</option>
                            <option value="G">Gram</option>
                            <option value="M">Meter</option>
                            <option value="FT">Feet</option>
                            <option value="L">Liter</option>
                            <option value="ML">Milliliter</option>
                            <option value="__add_unit__">+ Add Unit</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-primary open-add-unit-from-selector" title="Add Unit"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </td>
                <td class="col-rate"><input type="number" class="item-rate" value="0" min="0" step="0.01"></td>
                <td class="col-amount"><input type="number" class="item-amount" value="0" min="0" step="0.01" readonly></td>
                <td class="col-category d-none">
                    <select class="item-category">
                        <option value="">Select Category</option>
                        @foreach($itemTableCategories as $categoryOption)
                            <option value="{{ $categoryOption }}">{{ $categoryOption }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="col-item-code d-none"><input type="text" class="item-code" placeholder="Item Code" readonly></td>
                <td class="col-discount d-none">
                    <div class="item-discount-fields">
                        <input type="number" class="item-discount-pct" value="" min="0" step="0.01" placeholder="%">
                        <input type="number" class="item-discount" value="0" min="0" step="0.01" placeholder="Amount">
                    </div>
                </td>
                <td class="col-item-tax d-none">
                    <div class="item-tax-fields">
                        <input type="number" class="item-tax-pct" value="" min="0" step="0.01" placeholder="%">
                        <input type="number" class="item-tax-amount" value="0" min="0" step="0.01" placeholder="Amount">
                    </div>
                </td>
                <td class="custom-item-field col-custom-field-1 d-none"><input type="text" class="item-custom-field-input item-custom-field-1-input" placeholder="Custom Field 1"></td>
                <td class="custom-item-field col-custom-field-2 d-none"><input type="text" class="item-custom-field-input item-custom-field-2-input" placeholder="Custom Field 2"></td>
                <td class="custom-item-field col-custom-field-3 d-none"><input type="text" class="item-custom-field-input item-custom-field-3-input" placeholder="Custom Field 3"></td>
                <td class="custom-item-field col-custom-field-4 d-none"><input type="text" class="item-custom-field-input item-custom-field-4-input" placeholder="Custom Field 4"></td>
                <td class="custom-item-field col-custom-field-5 d-none"><input type="text" class="item-custom-field-input item-custom-field-5-input" placeholder="Custom Field 5"></td>
                <td class="custom-item-field col-custom-field-6 d-none"><input type="text" class="item-custom-field-input item-custom-field-6-input" placeholder="Custom Field 6"></td>
                <td class="add-col"></td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="item-totals-row">
                <td class="tfoot-add-row-cell">
                    <span class="column-total-label">#</span>
                </td>
                <td class="col-barcode-scan d-none"></td>
                <td class="tfoot-add-row-cell">
                    <button type="button" class="btn-add-row add-row-btn">ADD ROW</button>
                </td>
                <td class="col-serial-no d-none"></td>
                <td class="col-description d-none"></td>
                <td class="col-count d-none"></td>
                <td class="col-batch-no d-none"></td>
                <td class="col-model-no d-none"></td>
                <td class="col-exp-date d-none"></td>
                <td class="col-mfg-date d-none"></td>
                <td class="col-mrp d-none"></td>
                <td class="col-size d-none"></td>
                <td class="col-tafseel"></td>
                <td class="col-tadaat">
                    <span class="column-total-label">Total Quantity</span>
                    <span class="column-total-value total-qty">0</span>
                </td>
                <td class="col-free-qty d-none">
                    <span class="column-total-label">Free Qty</span>
                    <span class="column-total-value total-free-qty">0</span>
                </td>
                <td class="col-gross-w">
                    <span class="column-total-label">Total Gross W</span>
                    <span class="column-total-value total-gross-w">0.00</span>
                </td>
                <td class="col-net-w">
                    <span class="column-total-label">Total Net W</span>
                    <span class="column-total-value total-net-w">0.00</span>
                </td>
                <td class="custom-size-td"></td>
                <td class="col-rate"></td>
                <td class="col-amount">
                    <span class="column-total-label">Total</span>
                    <span class="column-total-value total-base-amount">0.00</span>
                </td>
                <td class="col-category d-none"></td>
                <td class="col-item-code d-none"></td>
                <td class="col-discount d-none"></td>
                <td class="col-item-tax d-none"></td>
                <td class="custom-item-field col-custom-field-1 d-none"></td>
                <td class="custom-item-field col-custom-field-2 d-none"></td>
                <td class="custom-item-field col-custom-field-3 d-none"></td>
                <td class="custom-item-field col-custom-field-4 d-none"></td>
                <td class="custom-item-field col-custom-field-5 d-none"></td>
                <td class="custom-item-field col-custom-field-6 d-none"></td>
                <td class="add-col"></td>
            </tr>
        </tfoot>
    </table>
</div>
