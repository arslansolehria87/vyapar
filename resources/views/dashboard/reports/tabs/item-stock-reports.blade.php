{{-- ============================================================
     1. STOCK SUMMARY TAB
     ============================================================ --}}
<div id="tab-stock-summary" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 16px;">
                <span style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Filters</span>

                <select class="form-select form-select-sm" id="ss-cat-filter" style="width: 150px; border: 1px solid #e5e7eb;" onchange="filterStockSummary()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <div class="form-check mb-0 d-flex align-items-center" style="gap: 8px;">
                    <input class="form-check-input mt-0" type="checkbox" id="stockSummaryDateFilter"
                        onchange="toggleDateBox('stockSummaryDateBox', this)">
                    <label class="form-check-label mb-0" for="stockSummaryDateFilter" style="color: #6b7280; font-size: 14px;">Date filter</label>
                </div>

                <div id="stockSummaryDateBox" class="d-flex align-items-center px-2 py-1" style="border: 1px solid #d1d5db; border-radius: 4px; display: none !important;">
                    <span style="font-size: 12px; color: #9ca3af; margin-right: 8px;">Date</span>
                    <input type="text" id="ss-date-display" readonly
                        style="font-size: 14px; outline: none; border: none; background: transparent; width: 90px; cursor: pointer;"
                        placeholder="Select date"
                        onclick="openCalendar('ss-date-picker', 'ss-date-display', 'filterStockSummary')">
                    <input type="date" id="ss-date-picker" style="position: absolute; opacity: 0; pointer-events: none;"
                        onchange="syncDisplay(this, 'ss-date-display'); filterStockSummary()">
                    <i class="fa-regular fa-calendar ms-1" style="color: #9ca3af; cursor: pointer;"
                        onclick="openCalendar('ss-date-picker', 'ss-date-display', 'filterStockSummary')"></i>
                </div>

                <div class="form-check mb-0 d-flex align-items-center" style="gap: 8px;">
                    <input class="form-check-input mt-0" type="checkbox" id="stockSummaryShowItems"
                        onchange="filterStockSummary()">
                    <label class="form-check-label mb-0" for="stockSummaryShowItems" style="color: #374151; font-size: 14px;">Show items in stock</label>
                </div>
            </div>

            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('stock-summary', 'Stock Summary')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="openStockSummaryPrintOptions()">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 24px; font-size: 22px;">STOCK SUMMARY</h2>

        <div class="table-responsive">
            <table class="w-100" id="stock-summary-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; width: 40px; font-size: 13px; font-weight: 600; color: #6b7280;">#</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Name</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale Price</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Purchase Price</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Stock Qty</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Stock Value</th>
                    </tr>
                </thead>
                <tbody id="ss-tbody">
                    @forelse($stockSummary ?? [] as $index => $item)
                    <tr class="ss-row"
                        data-cat="{{ $item->category_id }}"
                        data-qty="{{ $item->stock_qty }}"
                        style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #9ca3af;">{{ $index + 1 }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->name }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->sale_price, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->purchase_price, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; text-align: right; border-right: 1px solid #e5e7eb; color: {{ $item->stock_qty < 0 ? '#ef4444' : '#1f2937' }};">{{ $item->stock_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">Rs {{ number_format($item->stock_value, 2) }}</td>
                    </tr>
                    @empty
                    <tr id="ss-empty-row">
                        <td colspan="6" class="text-center text-muted py-5">No items to show</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td colspan="2" style="padding: 12px 16px; font-size: 14px; font-weight: 700;">Total</td>
                        <td></td><td></td>
                        <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #ef4444; text-align: right;" id="ss-total-qty">{{ $stockSummaryTotals['qty'] ?? 0 }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: right;" id="ss-total-val">Rs {{ number_format($stockSummaryTotals['value'] ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ============================================================
     2. ITEM REPORT BY PARTY
     ============================================================ --}}
<div id="tab-item-report-by-party" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 12px; flex-wrap: wrap;">
                <span style="font-size: 13px; color: #6b7280;">From</span>
                <input type="text" id="irbp-from-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    value="{{ now()->startOfMonth()->format('d/m/Y') }}"
                    onclick="openCalendar('irbp-from-picker','irbp-from-display','filterItemReportByParty')">
                <input type="date" id="irbp-from-picker"
                    value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                    style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'irbp-from-display'); filterItemReportByParty()">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('irbp-from-picker','irbp-from-display','filterItemReportByParty')"></i>

                <span style="font-size: 13px; color: #6b7280;">To</span>
                <input type="text" id="irbp-to-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    value="{{ now()->endOfMonth()->format('d/m/Y') }}"
                    onclick="openCalendar('irbp-to-picker','irbp-to-display','filterItemReportByParty')">
                <input type="date" id="irbp-to-picker"
                    value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                    style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'irbp-to-display'); filterItemReportByParty()">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('irbp-to-picker','irbp-to-display','filterItemReportByParty')"></i>

                <select class="form-select form-select-sm" id="irbp-party-filter" style="width: 220px;" onchange="filterItemReportByParty()">
                    <option value="">All Parties</option>
                    @foreach($parties ?? [] as $party)
                        <option value="{{ $party->id }}">{{ $party->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('irbp-table', 'Item Report By Party')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="printReport('irbp-table', 'ITEM REPORT BY PARTY')">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 24px; font-size: 22px;">ITEM REPORT BY PARTY</h2>

        <div class="table-responsive">
            <table class="w-100" id="irbp-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Name</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale Amount</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Purchase Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Purchase Amount</th>
                    </tr>
                </thead>
                <tbody id="irbp-tbody">
                    @forelse($itemReportByParty ?? [] as $row)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $row['item_name'] }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ number_format($row['sale_quantity'], 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($row['sale_amount'], 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ number_format($row['purchase_quantity'], 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">Rs {{ number_format($row['purchase_amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr id="irbp-empty-row"><td colspan="5" class="text-center text-muted py-5">No data to show</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td style="padding: 12px 16px; font-size: 14px; font-weight: 700;">Total</td>
                        <td id="irbp-total-sale-qty" style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: right; border-right: 1px solid #e5e7eb;">{{ number_format($itemReportByPartyTotals['sale_quantity'] ?? 0, 2) }}</td>
                        <td id="irbp-total-sale-amount" style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($itemReportByPartyTotals['sale_amount'] ?? 0, 2) }}</td>
                        <td id="irbp-total-purchase-qty" style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: right; border-right: 1px solid #e5e7eb;">{{ number_format($itemReportByPartyTotals['purchase_quantity'] ?? 0, 2) }}</td>
                        <td id="irbp-total-purchase-amount" style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: right;">Rs {{ number_format($itemReportByPartyTotals['purchase_amount'] ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>




{{-- ============================================================
     3. ITEM WISE PROFIT AND LOSS TAB
     ============================================================ --}}
 <div id="tab-item-wise-profit-and-loss" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 16px;">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span style="font-size: 13px; color: #6b7280;">From</span>
                    <input type="text" id="iwpnl-from-display" readonly
                        style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                        placeholder="From date"
                        onclick="openCalendar('iwpnl-from-picker','iwpnl-from-display','filterItemWisePnL')">
                    <input type="date" id="iwpnl-from-picker" style="position:absolute;opacity:0;pointer-events:none;"
                        onchange="syncDisplay(this,'iwpnl-from-display'); filterItemWisePnL()">
                    <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('iwpnl-from-picker','iwpnl-from-display','filterItemWisePnL')"></i>

                    <span style="font-size: 13px; color: #6b7280;">To</span>
                    <input type="text" id="iwpnl-to-display" readonly
                        style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                        placeholder="To date"
                        onclick="openCalendar('iwpnl-to-picker','iwpnl-to-display','filterItemWisePnL')">
                    <input type="date" id="iwpnl-to-picker" style="position:absolute;opacity:0;pointer-events:none;"
                        onchange="syncDisplay(this,'iwpnl-to-display'); filterItemWisePnL()">
                    <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('iwpnl-to-picker','iwpnl-to-display','filterItemWisePnL')"></i>
                </div>
                <div class="form-check mb-0 d-flex align-items-center" style="gap: 8px;">
                    <input class="form-check-input mt-0" type="checkbox" id="itemsHavingSale" onchange="filterItemWisePnL()">
                    <label class="form-check-label mb-0" for="itemsHavingSale" style="font-size: 14px; color: #374151;">Items Having Sale</label>
                </div>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('iwpnl-table', 'Item Wise Profit And Loss')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="printReport('iwpnl-table', 'ITEM WISE PROFIT AND LOSS')">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 24px; font-size: 22px;">DETAILS</h2>

        <div class="table-responsive">
            <table class="w-100" id="iwpnl-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Name</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Cr. Note / Sale Return</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Purchase</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Dr. Note / Purchase Return</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Opening Stock</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Closing Stock</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Tax Receivable</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Tax Payable</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Mfg. Cost</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Consumption Cost</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Net Profit / Loss</th>
                    </tr>
                </thead>
                <tbody id="iwpnl-tbody">
                    @forelse($itemWisePnL ?? [] as $item)
                    <tr class="iwpnl-row" data-has-sale="{{ $item->sale > 0 ? '1' : '0' }}"
                        style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->name }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->sale, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->cr_note, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->purchase, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->dr_note, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->opening_stock, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->closing_stock, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->tax_receivable, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->tax_payable, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->mfg_cost, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->consumption_cost, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; text-align: right; color: {{ $item->net_profit >= 0 ? '#10b981' : '#ef4444' }};">Rs {{ number_format($item->net_profit, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center text-muted py-5">No data to show</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td colspan="11" style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: right;">Total Amount:</td>
                        <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #10b981; text-align: right;">Rs {{ number_format($itemWisePnLTotal ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     4. ITEM CATEGORY WISE PROFIT AND LOSS
     ============================================================ --}}
 <div id="tab-item-category-wise-profit-and-loss" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 12px;">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="icp-period-label">
                        This Month
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="setItemCategoryPeriod('This Month'); return false;">This Month</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setItemCategoryPeriod('Last Month'); return false;">Last Month</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setItemCategoryPeriod('This Quarter'); return false;">This Quarter</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setItemCategoryPeriod('This Year'); return false;">This Year</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setItemCategoryPeriod('Custom'); return false;">Custom</a></li>
                    </ul>
                </div>
                <div class="d-flex align-items-center border rounded px-2 py-1" style="gap: 6px; font-size: 13px;">
                    <i class="fa-regular fa-calendar text-secondary" style="cursor:pointer;" onclick="openCalendar('icp-from-picker','icp-from-display','filterItemCategoryPnL')"></i>
                    <input type="text" id="icp-from-display" readonly
                        style="border:none;background:transparent;font-size:13px;outline:none;width:80px;cursor:pointer;"
                        value="{{ now()->startOfMonth()->format('d/m/Y') }}"
                        onclick="openCalendar('icp-from-picker','icp-from-display','filterItemCategoryPnL')">
                    <input type="date" id="icp-from-picker" style="position:absolute;opacity:0;pointer-events:none;"
                        value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        onchange="syncDisplay(this,'icp-from-display'); filterItemCategoryPnL()">
                    <span class="text-secondary">To</span>
                    <input type="text" id="icp-to-display" readonly
                        style="border:none;background:transparent;font-size:13px;outline:none;width:80px;cursor:pointer;"
                        value="{{ now()->endOfMonth()->format('d/m/Y') }}"
                        onclick="openCalendar('icp-to-picker','icp-to-display','filterItemCategoryPnL')">
                    <input type="date" id="icp-to-picker" style="position:absolute;opacity:0;pointer-events:none;"
                        value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                        onchange="syncDisplay(this,'icp-to-display'); filterItemCategoryPnL()">
                </div>
                <select class="form-select form-select-sm" id="icp-item-status"
                    style="width:140px;" onchange="filterItemCategoryPnL()">
                    <option value="active">Active Items</option>
                    <option value="all">All Items</option>
                    <option value="inactive">Inactive Items</option>
                </select>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('icp-table', 'Item Category Wise Profit And Loss')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="printReport('icp-table', 'ITEM CATEGORY WISE PROFIT AND LOSS')">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight:700;color:#1f2937;margin-bottom:24px;font-size:22px;">DETAILS</h2>
        <div class="table-responsive">
            <table class="w-100" id="icp-table" style="border-collapse:collapse;min-width:1500px;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Category Name</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Cr. Note / Sale Return</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Purchase</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Dr. Note / Purchase Return</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Opening Stock</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Closing Stock</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Tax Receivable</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Tax Payable</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Mfg. Cost</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Consumption Cost</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Net Profit / Loss</th>
                    </tr>
                </thead>
                <tbody id="icp-tbody">
                    <tr>
                        <td colspan="12" class="text-center text-muted py-5">
                            <div class="spinner-border spinner-border-sm me-2"></div>Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     5. LOW STOCK SUMMARY
     ============================================================ --}}
<div id="tab-low-stock-summary" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 16px;">
                <span style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Filters</span>
                <select class="form-select form-select-sm" id="ls-cat-filter" style="width: 150px; border: 1px solid #e5e7eb;" onchange="filterLowStock()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <div class="form-check mb-0 d-flex align-items-center" style="gap: 8px;">
                    <input class="form-check-input mt-0" type="checkbox" id="lowStockShowItems" onchange="filterLowStock()">
                    <label class="form-check-label mb-0" for="lowStockShowItems" style="color: #374151; font-size: 14px;">Show items in stock</label>
                </div>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('ls-table', 'Low Stock Summary')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="printReport('ls-table', 'LOW STOCK SUMMARY')">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="w-100" id="ls-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; width: 40px; font-size: 13px; color: #6b7280;">#</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Name</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Minimum Stock Qty</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Stock Qty</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Stock Value</th>
                    </tr>
                </thead>
                <tbody id="ls-tbody">
                    @forelse($lowStock ?? [] as $index => $item)
                    <tr class="ls-row" data-cat="{{ $item->category_id }}" data-qty="{{ $item->stock_qty }}"
                        style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #9ca3af;">{{ $index + 1 }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->name }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->min_stock_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; text-align: right; border-right: 1px solid #e5e7eb; color: {{ $item->stock_qty < $item->min_stock_qty ? '#ef4444' : '#1f2937' }};">{{ $item->stock_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">Rs {{ number_format($item->stock_value, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No items to show</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     6. STOCK DETAIL
     ============================================================ --}}
<div  id="tab-stock-details" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 12px;">
                <span style="font-size: 13px; color: #6b7280;">From</span>
                <input type="text" id="sd-from-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    placeholder="From date"
                    onclick="openCalendar('sd-from-picker','sd-from-display','')">
                <input type="date" id="sd-from-picker" style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'sd-from-display')">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('sd-from-picker','sd-from-display','')"></i>

                <span style="font-size: 13px; color: #6b7280;">To</span>
                <input type="text" id="sd-to-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    placeholder="To date"
                    onclick="openCalendar('sd-to-picker','sd-to-display','')">
                <input type="date" id="sd-to-picker" style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'sd-to-display')">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('sd-to-picker','sd-to-display','')"></i>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('sd-table', 'Stock Detail')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="printReport('sd-table', 'STOCK DETAIL')">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 16px; font-size: 22px;">DETAILS</h2>

        <div class="mb-3 d-flex align-items-center" style="gap: 12px;">
            <span style="font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Filters</span>
            <label style="font-size: 13px; color: #374151;">Filter by Item Category</label>
            <select class="form-select form-select-sm" id="sd-cat-filter" style="width: 180px; border: 1px solid #e5e7eb;" onchange="filterStockDetail()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="table-responsive">
            <table class="w-100" id="sd-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Name</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Beginning Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Quantity In</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Purchase Amount</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Quantity Out</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale Amount</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Closing Quantity</th>
                    </tr>
                </thead>
                <tbody id="sd-tbody">
                    @forelse($stockDetail ?? [] as $item)
                    <tr class="sd-row" data-cat="{{ $item->category_id }}" style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->name }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->beginning_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->qty_in }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->purchase_amount, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->qty_out }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->sale_amount, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">{{ $item->closing_qty }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">No data to show</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td style="padding: 12px 16px; font-size: 14px; font-weight: 700;">Total</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700;">{{ $stockDetailTotals['beginning_qty'] ?? 0 }}</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700;">{{ $stockDetailTotals['qty_in'] ?? 0 }}</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700;">Rs {{ number_format($stockDetailTotals['purchase_amount'] ?? 0, 2) }}</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700;">{{ $stockDetailTotals['qty_out'] ?? 0 }}</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700;">Rs {{ number_format($stockDetailTotals['sale_amount'] ?? 0, 2) }}</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700;">{{ $stockDetailTotals['closing_qty'] ?? 0 }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     7. ITEM DETAIL
     ============================================================ --}}
<div  id="tab-item-details" class="report-tab-content d-none">
    <style>
        #tab-item-details .id-item-picker {
            position: relative;
            width: 360px;
        }
        #tab-item-details .id-item-picker-input {
            width: 100%;
            height: 48px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            background: #fff;
        }
        #tab-item-details .id-item-picker-input:focus {
            border-color: #2684ff;
            box-shadow: 0 0 0 3px rgba(38, 132, 255, 0.16);
            outline: 0;
        }
        #tab-item-details .id-item-picker-panel {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            z-index: 1050;
            width: 620px;
            max-width: calc(100vw - 360px);
            overflow: hidden;
            background: #fff;
            border: 1px solid #d8e1ee;
            border-radius: 8px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.14);
        }
        #tab-item-details .id-item-picker-panel.open {
            display: block;
        }
        #tab-item-details .id-item-picker-head,
        #tab-item-details .id-item-picker-row {
            display: grid;
            grid-template-columns: minmax(0, 2fr) 95px 110px 80px 95px;
            gap: 12px;
            align-items: center;
        }
        #tab-item-details .id-item-picker-head {
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 700;
            color: #7b8798;
            text-transform: uppercase;
            background: #f8fbff;
            border-bottom: 1px solid #e5edf6;
        }
        #tab-item-details .id-item-picker-list {
            max-height: 290px;
            overflow-y: auto;
        }
        #tab-item-details .id-item-picker-row {
            padding: 12px 16px;
            font-size: 13px;
            color: #1f2937;
            cursor: pointer;
            border-bottom: 1px solid #edf2f7;
        }
        #tab-item-details .id-item-picker-row:hover {
            background: #f3f8ff;
        }
        #tab-item-details .id-item-picker-name {
            min-width: 0;
            font-weight: 600;
            color: #1672f3;
        }
        #tab-item-details .id-item-picker-name small {
            display: block;
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }
        #tab-item-details .id-item-picker-empty {
            padding: 18px;
            color: #6b7280;
            text-align: center;
        }
        @media (max-width: 900px) {
            #tab-item-details .id-item-picker {
                width: min(360px, 100%);
            }
            #tab-item-details .id-item-picker-panel {
                width: min(620px, calc(100vw - 48px));
                max-width: min(620px, calc(100vw - 48px));
            }
            #tab-item-details .id-item-picker-head,
            #tab-item-details .id-item-picker-row {
                grid-template-columns: minmax(0, 1fr) 80px 70px;
            }
            #tab-item-details .id-item-picker-head span:nth-child(3),
            #tab-item-details .id-item-picker-head span:nth-child(5),
            #tab-item-details .id-item-picker-row > div:nth-child(3),
            #tab-item-details .id-item-picker-row > div:nth-child(5) {
                display: none;
            }
        }
    </style>
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 12px;">
                <span style="font-size: 13px; color: #6b7280;">From</span>
                <input type="text" id="id-from-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    placeholder="From date"
                    onclick="openCalendar('id-from-picker','id-from-display','filterItemDetail')">
                <input type="date" id="id-from-picker" style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'id-from-display'); filterItemDetail()">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('id-from-picker','id-from-display','filterItemDetail')"></i>

                <span style="font-size: 13px; color: #6b7280;">To</span>
                <input type="text" id="id-to-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    placeholder="To date"
                    onclick="openCalendar('id-to-picker','id-to-display','filterItemDetail')">
                <input type="date" id="id-to-picker" style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'id-to-display'); filterItemDetail()">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('id-to-picker','id-to-display','filterItemDetail')"></i>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('id-table', 'Item Detail')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="printReport('id-table', 'ITEM DETAIL')">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 16px; font-size: 22px;">DETAILS</h2>

        <div class="mb-3 d-flex align-items-center" style="gap: 16px;">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <label style="font-size: 13px; color: #374151;">Item name</label>
                <div class="id-item-picker" id="id-item-picker">
                    <input type="text" class="id-item-picker-input" id="id-item-name"
                        placeholder="" autocomplete="off" onfocus="openItemDetailPicker()" oninput="handleItemDetailInput()">
                    <input type="hidden" id="id-item-id">
                    <div class="id-item-picker-panel" id="id-item-picker-panel">
                        <div class="id-item-picker-head">
                            <span>Item</span>
                            <span>Sale Price</span>
                            <span>Purchase Price</span>
                            <span>Stock</span>
                            <span>Location</span>
                        </div>
                        <div class="id-item-picker-list" id="id-item-picker-list">
                            @forelse($items ?? [] as $itemOption)
                                <div class="id-item-picker-row"
                                    data-id="{{ $itemOption->id }}"
                                    data-name="{{ $itemOption->name }}"
                                    data-search="{{ strtolower(($itemOption->name ?? '') . ' ' . ($itemOption->item_code ?? '') . ' ' . ($itemOption->location ?? '')) }}">
                                    <div class="id-item-picker-name">
                                        {{ $itemOption->name }}
                                        @if(!empty($itemOption->item_code))
                                            <small>{{ $itemOption->item_code }}</small>
                                        @endif
                                    </div>
                                    <div>{{ number_format((float) ($itemOption->sale_price ?? $itemOption->price ?? 0), 2, '.', '') }}</div>
                                    <div>{{ number_format((float) ($itemOption->purchase_price ?? 0), 2, '.', '') }}</div>
                                    <div>{{ (float) ($itemOption->opening_qty ?? 0) }}</div>
                                    <div>{{ $itemOption->location ?? '' }}</div>
                                </div>
                            @empty
                                <div class="id-item-picker-empty">No items found</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-check mb-0 d-flex align-items-center" style="gap: 8px;">
                <input class="form-check-input mt-0" type="checkbox" id="hideInactiveDates" onchange="filterItemDetail()">
                <label class="form-check-label mb-0" for="hideInactiveDates" style="font-size: 14px; color: #374151;">Hide inactive dates</label>
            </div>
        </div>

        <div class="table-responsive">
            <table class="w-100" id="id-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Date</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Purchase Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Adjustment Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Closing Quantity</th>
                    </tr>
                </thead>
                <tbody id="id-tbody">
                    @forelse($itemDetail ?? [] as $item)
                    <tr class="id-row" data-active="{{ ($item->sale_qty + $item->purchase_qty + $item->adjustment_qty) > 0 ? '1' : '0' }}"
                        style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->date }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->sale_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->purchase_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->adjustment_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">{{ $item->closing_qty }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No data to show</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     8. SALE/PURCHASE REPORT BY ITEM CATEGORY
     ============================================================ --}}
 <div id="tab-sale-purchase-report-by-item-category" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center" style="gap: 12px;">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <label style="font-size: 13px; color: #6b7280;">Party name</label>
                    <select class="form-select form-select-sm" id="spc-party-filter" style="width: 220px;" onchange="filterSalePurchaseCat()">
                        <option value="">All Parties</option>
                        @foreach($parties ?? [] as $party)
                            <option value="{{ $party->id }}">{{ $party->name }}</option>
                        @endforeach
                    </select>
                </div>
                <span style="font-size: 13px; color: #6b7280;">From</span>
                <input type="text" id="spc-from-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    placeholder="From date"
                    onclick="openCalendar('spc-from-picker','spc-from-display','filterSalePurchaseCat')">
                <input type="date" id="spc-from-picker" style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'spc-from-display'); filterSalePurchaseCat()">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('spc-from-picker','spc-from-display','filterSalePurchaseCat')"></i>
                <span style="font-size: 13px; color: #6b7280;">To</span>
                <input type="text" id="spc-to-display" readonly
                    style="width: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 5px 8px; font-size: 13px; cursor: pointer; background: #fff;"
                    placeholder="To date"
                    onclick="openCalendar('spc-to-picker','spc-to-display','filterSalePurchaseCat')">
                <input type="date" id="spc-to-picker" style="position:absolute;opacity:0;pointer-events:none;"
                    onchange="syncDisplay(this,'spc-to-display'); filterSalePurchaseCat()">
                <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;" onclick="openCalendar('spc-to-picker','spc-to-display','filterSalePurchaseCat')"></i>
            </div>
            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="exportReport('spc-table', 'Sale Purchase Report By Item Category')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
                </button>
                <button class="btn d-flex align-items-center justify-content-center p-0"
                    style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                    onclick="printReport('spc-table', 'SALE/PURCHASE REPORT BY ITEM CATEGORY')">
                    <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 24px; font-size: 22px;">SALE/PURCHASE REPORT BY ITEM CATEGORY</h2>

        <div class="table-responsive">
            <table class="w-100" id="spc-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Category</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Sale Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Total Sale Amount</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Purchase Quantity</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Total Purchase Amount</th>
                    </tr>
                </thead>
                <tbody id="spc-tbody">
                    @forelse($salePurchaseByCat ?? [] as $item)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->category_name }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->sale_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->total_sale_amount, 2) }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->purchase_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">Rs {{ number_format($item->total_purchase_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No data to show</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     9. ITEM WISE DISCOUNT — matches screenshot 5 & 6
     ============================================================ --}}
@once
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
@endonce

<div id="tab-item-wise-discount" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        {{-- Top filter bar --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center" style="gap: 12px; flex-wrap: wrap;">

                {{-- Period Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center"
                        style="gap: 6px;" type="button" data-bs-toggle="dropdown" id="iwd-period-label">
                        This Month
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="setPeriodLabel('iwd-period-label','This Month')">This Month</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setPeriodLabel('iwd-period-label','Last Month')">Last Month</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setPeriodLabel('iwd-period-label','This Quarter')">This Quarter</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setPeriodLabel('iwd-period-label','This Year')">This Year</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setPeriodLabel('iwd-period-label','Custom')">Custom</a></li>
                    </ul>
                </div>

                {{-- Between + Date Range --}}
                <div class="d-flex align-items-center">
                    <span class="px-2 py-1 text-white fw-bold"
                        style="background-color: #9ca3af; border-radius: 4px 0 0 4px; font-size: 13px;">Between</span>
                    <div class="d-flex align-items-center px-3 py-1 border"
                        style="border-radius: 0 4px 4px 0; gap: 8px; font-size: 13px;">
                        <input type="text" id="iwd-from-display" readonly
                            style="font-size: 13px; outline: none; border: none; width: 80px; cursor: pointer; background: transparent;"
                            value="{{ now()->startOfMonth()->format('d/m/Y') }}"
                            onclick="openCalendar('iwd-from-picker','iwd-from-display','filterIWDAjax')">
                        <input type="date" id="iwd-from-picker"
                            value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                            style="position:absolute;opacity:0;pointer-events:none;"
                            onchange="syncDisplay(this,'iwd-from-display'); filterIWDAjax()">
                        <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;"
                            onclick="openCalendar('iwd-from-picker','iwd-from-display','filterIWDAjax')"></i>

                        <span class="text-secondary">To</span>

                        <input type="text" id="iwd-to-display" readonly
                            style="font-size: 13px; outline: none; border: none; width: 80px; cursor: pointer; background: transparent;"
                            value="{{ now()->endOfMonth()->format('d/m/Y') }}"
                            onclick="openCalendar('iwd-to-picker','iwd-to-display','filterIWDAjax')">
                        <input type="date" id="iwd-to-picker"
                            value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                            style="position:absolute;opacity:0;pointer-events:none;"
                            onchange="syncDisplay(this,'iwd-to-display'); filterIWDAjax()">
                        <i class="fa-regular fa-calendar" style="color:#9ca3af;cursor:pointer;"
                            onclick="openCalendar('iwd-to-picker','iwd-to-display','filterIWDAjax')"></i>
                    </div>
                </div>

                {{-- All Firms --}}
                <select class="form-select form-select-sm" id="iwd-firm" style="width: 130px;" onchange="filterIWDAjax()">
                    <option value="">ALL FIRMS</option>
                    @foreach($firms ?? [] as $firm)
                        <option value="{{ $firm->id }}">{{ $firm->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex" style="gap: 8px;">
                <button class="btn d-flex align-items-center justify-content-center"
                    style="gap: 4px; font-size: 13px; border: 1px solid #e5e7eb;"
                    onclick="exportReport('iwd-table', 'Item Wise Discount')">
                    <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Excel Report
                </button>
                <button class="btn d-flex align-items-center justify-content-center"
                    style="gap: 4px; font-size: 13px; border: 1px solid #e5e7eb;"
                    onclick="printReport('iwd-table', 'Item Wise Discount')">
                    <i class="fa-solid fa-print" style="color: #4b5563;"></i> Print
                </button>
            </div>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 16px; font-size: 22px;">Item Wise Discount</h2>

        {{-- Second filter row --}}
        <div class="mb-3 d-flex align-items-center" style="gap: 12px; flex-wrap: wrap;">
            <div class="d-flex align-items-center" style="gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Item Name</label>
                <select class="form-select form-select-sm" id="iwd-item-filter" style="width: 220px;" onchange="filterIWDAjax()">
                    <option value="">All Items</option>
                    @foreach($items ?? [] as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <select class="form-select form-select-sm" id="iwd-cat-filter" style="width: 160px;" onchange="filterIWDAjax()">
                <option value="">All Categories</option>
                @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm" id="iwd-party-filter" style="width: 220px;" onchange="filterIWDAjax()">
                <option value="">All Parties</option>
                @foreach($parties ?? [] as $party)
                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="table-responsive">
            <table class="w-100" id="iwd-table" data-column-drag="native"
                data-column-drag-storage="vyapar.reports.item-wise-discount.transactions.v1"
                style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th data-column-key="index" style="padding: 12px 16px; width: 40px; font-size: 13px; font-weight: 600; color: #6b7280;">#</th>
                        <th data-column-key="item_name" style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Name</th>
                        <th data-column-key="total_qty_sold" style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Total Qty Sold</th>
                        <th data-column-key="total_sale_amount" style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Total Sale Amount</th>
                        <th data-column-key="total_discount_amount" style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Total Disc. Amount</th>
                        <th data-column-key="average_discount" style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Avg. Disc. (%)</th>
                        <th data-column-key="actions" style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Details</th>
                    </tr>
                </thead>
                <tbody id="iwd-tbody">
                    @forelse($itemWiseDiscount ?? [] as $index => $item)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td data-column-key="index" style="padding: 12px 16px; font-size: 14px; color: #9ca3af;">{{ $index + 1 }}</td>
                        <td data-column-key="item_name" style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->name }}</td>
                        <td data-column-key="total_qty_sold" style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->total_qty_sold }}</td>
                        <td data-column-key="total_sale_amount" style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->total_sale_amount, 2) }}</td>
                        <td data-column-key="total_discount_amount" style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">Rs {{ number_format($item->total_disc_amount, 2) }}</td>
                        <td data-column-key="average_discount" style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ number_format($item->avg_disc_percent, 2) }}%</td>
                        <td data-column-key="actions" style="padding: 12px 16px; font-size: 14px; text-align: right;">
                            <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                onclick="loadIWDDetails({{ $item->id }})">Details</button>
                        </td>
                    </tr>
                    @empty
                    <tr id="iwd-empty-row">
                        <td colspan="7" class="text-center text-muted py-5">No Items</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top: 1px solid #e5e7eb;">
                        <td data-column-key="index" style="padding: 12px 16px; font-size: 14px;"><strong>Summary</strong></td>
                        <td data-column-key="item_name"></td>
                        <td data-column-key="total_qty_sold"></td>
                        <td data-column-key="total_sale_amount" style="padding: 12px 16px; font-size: 13px; color: #374151; text-align:right;">
                            Total: <span id="iwd-total-sale">{{ isset($iwdTotals) ? 'Rs '.number_format($iwdTotals['total_sale'], 2) : '—' }}</span>
                        </td>
                        <td data-column-key="total_discount_amount" style="padding: 12px 16px; font-size: 13px; color: #374151; text-align:right;">
                            Total: <span id="iwd-total-disc">{{ isset($iwdTotals) ? 'Rs '.number_format($iwdTotals['total_disc'], 2) : '—' }}</span>
                        </td>
                        <td data-column-key="average_discount"></td>
                        <td data-column-key="actions"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     10. STOCK SUMMARY REPORT BY ITEM CATEGORY
     ============================================================ --}}
<div id="tab-stock-summary-report-by-item-category" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height: 100vh; padding: 24px; background-color: #ffffff;">

        <div class="d-flex justify-content-end mb-4" style="gap: 8px;">
            <button class="btn d-flex align-items-center justify-content-center p-0"
                style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                onclick="exportReport('ssc-table', 'Stock Summary Report By Item Category')">
                <i class="fa-solid fa-file-excel" style="color: #10b981; font-size: 18px;"></i>
            </button>
            <button class="btn d-flex align-items-center justify-content-center p-0"
                style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #e5e7eb;"
                onclick="printReport('ssc-table', 'STOCK SUMMARY REPORT BY ITEM CATEGORY')">
                <i class="fa-solid fa-print" style="color: #4b5563; font-size: 18px;"></i>
            </button>
        </div>

        <h2 style="font-weight: 700; color: #1f2937; margin-bottom: 24px; font-size: 22px;">Stock Summary Report By Item Category</h2>

        <div class="table-responsive">
            <table class="w-100" id="ssc-table" style="border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: left; border-right: 1px solid #e5e7eb;">Item Category</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right; border-right: 1px solid #e5e7eb;">Stock Qty</th>
                        <th style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #6b7280; text-align: right;">Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockSummaryByCat ?? [] as $item)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">{{ $item->category_name }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">{{ $item->stock_qty }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">Rs {{ number_format($item->stock_value, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-5">No data to show</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
