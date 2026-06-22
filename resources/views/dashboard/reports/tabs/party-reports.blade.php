{{-- resources/views/dashboard/reports/tabs/party-reports.blade.php --}}
{{-- Include this file in your main reports.blade.php with: @include('dashboard.reports.tabs.party-reports') --}}

<style>
    .party-report-shell {
        background: #f7faff;
    }

    .party-report-toolbar {
        border-bottom: 1px solid #dde5f0;
        gap: 14px;
    }

    .party-report-period {
        font-size: 18px;
        font-weight: 700;
        color: #243145;
        min-width: 170px;
    }

    .party-report-range {
        display: flex;
        align-items: center;
        border: 1px solid #d6dde8;
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
        min-height: 42px;
    }

    .party-report-range-label {
        background: #a39f99;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        padding: 11px 14px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .party-report-range input {
        border: 0;
        padding: 8px 12px;
        min-width: 130px;
        outline: none;
    }

    .party-report-range-sep {
        color: #7b8795;
        font-size: 13px;
    }

    .party-report-party {
        min-width: 240px;
        max-width: 240px;
    }

    .party-report-view {
        font-size: 14px;
        color: #4b5563;
    }

    .party-report-view label {
        cursor: pointer;
    }

    .party-report-table-wrap {
        padding: 0 14px 14px;
    }

    .party-report-table-box {
        background: #fff;
        border: 1px solid #e3eaf3;
        border-radius: 8px;
        overflow: hidden;
    }

    .party-report-table-box .table {
        margin-bottom: 0;
    }

    .party-report-table-box thead th {
        background: #fff;
        color: #7a8797;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #dce5f1;
    }

    .party-report-table-box tbody td,
    .party-report-table-box tfoot td {
        font-size: 14px;
        vertical-align: middle;
    }

    .party-report-highlight {
        background: #d9eefc;
    }

    .party-report-summary-card {
        background: #fff;
        border: 1px solid #dce5f1;
        border-radius: 10px 10px 0 0;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.04);
    }

    .party-report-summary-header {
        font-size: 18px;
        font-weight: 600;
        color: #4b5563;
    }

    .party-report-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(160px, 1fr));
        gap: 18px;
        font-size: 14px;
    }

    .party-report-summary-grid small {
        display: block;
        color: #9aa4b2;
        line-height: 1.3;
    }

    .party-report-total-receivable {
        border-left: 1px solid #e5e7eb;
        min-width: 220px;
        text-align: right;
    }

    .party-report-money {
        white-space: nowrap;
    }

    .report-resizable-table th {
        position: relative;
    }

    .report-col-resizer {
        position: absolute;
        top: 0;
        right: -2px;
        width: 8px;
        height: 100%;
        cursor: col-resize;
        user-select: none;
        z-index: 2;
    }

    .party-report-positive {
        color: #16a34a;
        font-weight: 700;
    }

    .party-report-negative {
        color: #dc2626;
        font-weight: 700;
    }

    @media (max-width: 1200px) {
        .party-report-summary-grid {
            grid-template-columns: repeat(2, minmax(180px, 1fr));
        }
    }
</style>

{{-- ===================== PARTY STATEMENT TAB ===================== --}}
<div id="tab-Party Statement" class="report-tab-content d-none party-report-shell">

    <div class="party-report-toolbar d-flex align-items-center px-4 py-3 flex-wrap">
        <select id="ps-period" class="party-report-period bg-transparent border-0" style="outline:none;">
            <option value="this_month" selected>This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_quarter">This Quarter</option>
            <option value="this_year">This Year</option>
            <option value="custom">Custom</option>
        </select>

        <div class="party-report-range">
            <span class="party-report-range-label">Between</span>
            <input type="date" id="ps-date-from">
            <span class="party-report-range-sep">To</span>
            <input type="date" id="ps-date-to">
        </div>

        <select id="ps-party-select" class="form-select party-report-party">
            <option value="">-- Select Party --</option>
            @foreach($parties as $party)
                <option value="{{ $party->id }}">{{ $party->name }}</option>
            @endforeach
        </select>

        <div class="party-report-view d-flex align-items-center gap-3">
            <span>View :</span>
            <label class="d-flex align-items-center gap-2 mb-0">
                <input type="radio" name="psView" value="vyapar" checked class="ps-radio"> <span>Vyapar</span>
            </label>
            <label class="d-flex align-items-center gap-2 mb-0">
                <input type="radio" name="psView" value="accounting" class="ps-radio"> <span>Accounting</span>
            </label>
        </div>

        <div class="ms-auto d-flex gap-4 text-secondary align-items-center">
            <div class="text-center small">
                <i class="fa-solid fa-file-excel fs-5 cursor-pointer d-block mb-1" id="ps-excel-btn" title="Excel Report"></i>
                <span>Excel Report</span>
            </div>
            <div class="text-center small">
                <i class="fa-solid fa-print fs-5 cursor-pointer d-block mb-1" id="ps-print-btn" title="Print"></i>
                <span>Print</span>
            </div>
        </div>
    </div>

    <div class="party-report-table-wrap h-100 d-flex flex-column">
        <div class="party-report-table-box flex-grow-1 overflow-auto">
            <table class="table table-hover w-100 report-resizable-table" id="ps-table"
                data-column-drag="native" data-column-drag-storage="vyapar.reports.party-statement.transactions.v1">
            <thead style="position:sticky; top:0; z-index:1;">
                <tr>
                    <th id="ps-col-date" data-column-key="date">Date</th>
                    <th id="ps-col-type" data-column-key="transaction">TXN</th>
                    <th id="ps-col-ref" data-column-key="reference">Ref. No</th>
                    <th id="ps-col-paytype" data-column-key="payment_type">Payment</th>
                    <th class="text-end" id="ps-col-total" data-column-key="total">Total</th>
                    <th class="text-end" id="ps-col-received" data-column-key="received_paid">Received/Paid</th>
                    <th class="text-end" id="ps-col-txnbalance" data-column-key="txn_balance">TXN Balance</th>
                    <th class="text-end" id="ps-col-receivable" data-column-key="receivable">Receivable Balance</th>
                    <th class="text-end" id="ps-col-payable" data-column-key="payable">Payable Balance</th>
                    <th class="text-end d-none" id="ps-col-debit" data-column-key="debit">Debit</th>
                    <th class="text-end d-none" id="ps-col-credit" data-column-key="credit">Credit</th>
                    <th class="text-end d-none" id="ps-col-running" data-column-key="running_balance">Running Balance</th>
                    <th class="text-center" data-column-key="actions">Actions</th>
                </tr>
            </thead>
            <tbody id="ps-tbody">
                <tr><td colspan="13" class="text-center text-muted py-5">Select a party and date range to view statement.</td></tr>
            </tbody>
            </table>
        </div>

        <div id="ps-summary-bar" class="party-report-summary-card mt-3 p-3 d-none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="party-report-summary-header">Party Statement Summary</div>
                <i class="fa-solid fa-angle-down text-secondary"></i>
            </div>
            <div class="d-flex justify-content-between gap-4 flex-wrap align-items-start">
                <div class="party-report-summary-grid flex-grow-1">
                    <div>
                        <div>Total Sale: <strong id="ps-foot-sale">Rs 0.00</strong></div>
                        <small>(Sale - Sale Return)</small>
                    </div>
                    <div>
                        <div>Total Purchase: <strong id="ps-foot-purchase">Rs 0.00</strong></div>
                        <small>(Purchase - Purchase Return)</small>
                    </div>
                    <div>
                        <div>Total Expense: <strong id="ps-total-credit">Rs 0.00</strong></div>
                        <small>Credit / Money Out</small>
                    </div>
                    <div>
                        <div>Total Money-In: <strong id="ps-foot-moneyin">Rs 0.00</strong></div>
                        <small>Payments received</small>
                    </div>
                    <div>
                        <div>Total Money-out: <strong id="ps-foot-moneyout">Rs 0.00</strong></div>
                        <small>Payments paid</small>
                    </div>
                    <div>
                        <div>Opening Balance: <strong id="ps-opening-bal">Rs 0.00</strong></div>
                        <small>Initial ledger balance</small>
                    </div>
                    <div>
                        <div>Closing Balance: <strong id="ps-closing-bal">Rs 0.00</strong></div>
                        <small>End of selected period</small>
                    </div>
                    <div>
                        <div>Total Debit: <strong id="ps-total-debit">Rs 0.00</strong></div>
                        <small>Accounting debit total</small>
                    </div>
                </div>
                <div class="party-report-total-receivable ps-4">
                    <div class="fw-bold text-dark">Total Receivable</div>
                    <div class="party-report-positive fs-4" id="ps-foot-receivable">Rs 0.00</div>
                    <div class="text-muted mt-2">Total Payable: <strong id="ps-foot-payable">Rs 0.00</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ===================== ALL PARTIES TAB ===================== --}}
<div id="tab-All Parties" class="report-tab-content d-none bg-white">

    {{-- Filter Bar --}}
    <div class="d-flex align-items-center bg-light px-4 py-2 gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" id="ap-date-filter-check">
            <label for="ap-date-filter-check" class="mb-0" style="font-size:13px;">Date Filter</label>
            <input type="date" id="ap-date-input" class="form-control form-control-sm d-none" style="width:150px;">
        </div>
        <select id="ap-type-filter" class="form-select form-select-sm" style="width:140px;">
            <option value="">All Parties</option>
            <option value="receivable">Receivable</option>
            <option value="payable">Payable</option>
        </select>
        <select id="ap-group-filter" class="form-select form-select-sm" style="width:180px;">
            <option value="">All Groups</option>
            <option value="Ungrouped">Ungrouped</option>
            @foreach($partyGroups ?? [] as $group)
                <option value="{{ $group->name }}">{{ $group->name }}</option>
            @endforeach
        </select>
        <div class="ms-2 position-relative" style="width:220px;">
            <i class="fa-solid fa-magnifying-glass position-absolute text-secondary" style="left:10px;top:50%;transform:translateY(-50%);"></i>
            <input type="text" id="ap-search" class="form-control form-control-sm" placeholder="Search party..." style="padding-left:32px;">
        </div>
        <div class="ms-auto d-flex gap-3 text-secondary">
            <i class="fa-solid fa-file-excel fs-5 cursor-pointer" id="ap-excel-btn" title="Export Excel"></i>
            <i class="fa-solid fa-print fs-5 cursor-pointer" id="ap-print-btn" title="Print"></i>
        </div>
    </div>

    {{-- Table --}}
    <div class="h-100 overflow-auto">
        <table class="table table-hover mb-0 w-100 report-resizable-table" id="ap-table"
            data-column-drag="native" data-column-drag-storage="vyapar.reports.all-parties.columns.v1">
            <thead class="table-light" style="font-size:12px; text-transform:uppercase; position:sticky; top:0; z-index:1;">
                <tr>
                    <th data-column-key="selection"><input type="checkbox" id="ap-select-all"></th>
                    <th data-column-key="party_name">Party Name</th>
                    <th data-column-key="party_group">Party Group</th>
                    <th data-column-key="email">Email</th>
                    <th data-column-key="phone">Phone No.</th>
                    <th data-column-key="receivable_balance" class="text-end">Receivable Balance</th>
                    <th data-column-key="payable_balance" class="text-end">Payable Balance</th>
                    <th data-column-key="credit_limit" class="text-end">Credit Limit</th>
                </tr>
            </thead>
            <tbody id="ap-tbody">
                <tr><td colspan="8" class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="bg-white px-4 py-3 border-top d-flex justify-content-between align-items-center" style="font-size:13px; box-shadow:0 -4px 6px -1px rgba(0,0,0,0.02);">
        <div>
            <span class="text-dark fw-bold">Total Receivable: </span>
            <span class="fw-bold" style="color:#10b981;" id="ap-total-receivable">Rs 0.00</span>
        </div>
        <div>
            <span class="text-dark fw-bold">Total Payable: </span>
            <span class="fw-bold" style="color:#ef4444;" id="ap-total-payable">Rs 0.00</span>
        </div>
    </div>
</div>


{{-- ===================== PARTY REPORT BY ITEMS TAB ===================== --}}
<div id="tab-Party Report by Items" class="report-tab-content d-none bg-white">

    {{-- Filter Bar --}}
    <div class="d-flex align-items-center bg-light px-4 py-2 gap-3 flex-wrap">
        <select id="pri-period" class="bg-transparent border-0 fw-bold" style="outline:none;">
            <option value="this_month" selected>This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_quarter">This Quarter</option>
            <option value="this_year">This Year</option>
            <option value="custom">Custom</option>
        </select>
        <div class="d-flex align-items-center" style="border:1px solid #AAAAAA; border-radius:5px; height:32px; padding:0 10px; font-size:13px;">
            <input type="date" id="pri-date-from" class="bg-transparent border-0" style="outline:none; font-size:13px;">
            <span class="mx-2 text-muted">To</span>
            <input type="date" id="pri-date-to" class="bg-transparent border-0" style="outline:none; font-size:13px;">
        </div>
        <select id="pri-category" class="form-select form-select-sm" style="width:160px;">
            <option value="">All Categories</option>
        </select>
        <select id="pri-item" class="form-select form-select-sm" style="width:160px;">
            <option value="">All Items</option>
        </select>
        <div class="ms-2 position-relative" style="width:200px;">
            <i class="fa-solid fa-magnifying-glass position-absolute text-secondary" style="left:10px;top:50%;transform:translateY(-50%);"></i>
            <input type="text" id="pri-search" class="form-control form-control-sm" placeholder="Search..." style="padding-left:32px;">
        </div>
        <div class="ms-auto d-flex gap-3 text-secondary">
            <i class="fa-solid fa-file-excel fs-5 cursor-pointer" id="pri-excel-btn"></i>
            <i class="fa-solid fa-print fs-5 cursor-pointer" id="pri-print-btn"></i>
        </div>
    </div>

    {{-- Table --}}
        <div class="flex-grow-1 overflow-auto">
        <table class="table table-hover mb-0 w-100" id="pri-table">
            <thead class="table-light" style="font-size:12px; text-transform:uppercase; position:sticky; top:0; z-index:1;">
                <tr>
                    <th>Party Name</th>
                    <th>Category</th>
                    <th>Item</th>
                    <th class="text-end">Sale Qty</th>
                    <th class="text-end">Sale Amount</th>
                    <th class="text-end">Purchase Qty</th>
                    <th class="text-end">Purchase Amount</th>
                </tr>
            </thead>
            <tbody id="pri-tbody">
                <tr><td colspan="7" class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
            <tfoot class="table-light fw-bold" id="pri-tfoot" style="position:sticky; bottom:0;">
                <tr>
                    <td>Total:</td>
                    <td></td>
                    <td></td>
                    <td class="text-end" id="pri-total-sale-qty">0</td>
                    <td class="text-end" id="pri-total-sale-amt">Rs 0.00</td>
                    <td class="text-end" id="pri-total-pur-qty">0</td>
                    <td class="text-end" id="pri-total-pur-amt">Rs 0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


{{-- ===================== SALE PURCHASE BY PARTY TAB ===================== --}}
<div id="tab-Partysalepurchase" class="report-tab-content d-none d-flex flex-column h-100 bg-white">

    {{-- Filter Bar --}}
    <div class="d-flex align-items-center bg-light px-4 py-2 gap-3 flex-wrap">
        <select id="spp-period" class="bg-transparent border-0 fw-bold" style="outline:none;">
            <option value="this_month" selected>This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_quarter">This Quarter</option>
            <option value="this_year">This Year</option>
            <option value="custom">Custom</option>
        </select>
        <div class="d-flex align-items-center" style="border:1px solid #AAAAAA; border-radius:5px; height:32px; padding:0 10px; font-size:13px;">
            <input type="date" id="spp-date-from" class="bg-transparent border-0" style="outline:none; font-size:13px;">
            <span class="mx-2 text-muted">To</span>
            <input type="date" id="spp-date-to" class="bg-transparent border-0" style="outline:none; font-size:13px;">
        </div>
        <div class="ms-2 position-relative" style="width:200px;">
            <i class="fa-solid fa-magnifying-glass position-absolute text-secondary" style="left:10px;top:50%;transform:translateY(-50%);"></i>
            <input type="text" id="spp-search" class="form-control form-control-sm" placeholder="Search party..." style="padding-left:32px;">
        </div>
        <div class="ms-auto d-flex gap-3 text-secondary">
            <i class="fa-solid fa-file-excel fs-5 cursor-pointer" id="spp-excel-btn"></i>
            <i class="fa-solid fa-print fs-5 cursor-pointer" id="spp-print-btn"></i>
        </div>
    </div>

    {{-- Table --}}
    <div class="flex-grow-1 overflow-auto">
        <table class="table table-hover mb-0 w-100" id="spp-table">
            <thead class="table-light" style="font-size:12px; text-transform:uppercase; position:sticky; top:0; z-index:1;">
                <tr>
                    <th>Party Name</th>
                    <th class="text-end">Sale Amount</th>
                    <th class="text-end">Purchase Amount</th>
                </tr>
            </thead>
            <tbody id="spp-tbody">
                <tr><td colspan="3" class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
            <tfoot class="table-light fw-bold" style="position:sticky; bottom:0;">
                <tr>
                    <td>Total:</td>
                    <td class="text-end" id="spp-total-sale">Rs 0.00</td>
                    <td class="text-end" id="spp-total-purchase">Rs 0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


{{-- ===================== SALE PURCHASE BY PARTY GROUP TAB ===================== --}}
<div id="tab-Partysalepurchasegroup" class="report-tab-content d-none d-flex flex-column h-100 bg-white">

    {{-- Filter Bar --}}
    <div class="d-flex align-items-center bg-light px-4 py-2 gap-3 flex-wrap">
        <select id="spg-period" class="bg-transparent border-0 fw-bold" style="outline:none;">
            <option value="this_month" selected>This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_quarter">This Quarter</option>
            <option value="this_year">This Year</option>
            <option value="custom">Custom</option>
        </select>
        <div class="d-flex align-items-center" style="border:1px solid #AAAAAA; border-radius:5px; height:32px; padding:0 10px; font-size:13px;">
            <input type="date" id="spg-date-from" class="bg-transparent border-0" style="outline:none; font-size:13px;">
            <span class="mx-2 text-muted">To</span>
            <input type="date" id="spg-date-to" class="bg-transparent border-0" style="outline:none; font-size:13px;">
        </div>
        <div class="ms-2 position-relative" style="width:200px;">
            <i class="fa-solid fa-magnifying-glass position-absolute text-secondary" style="left:10px;top:50%;transform:translateY(-50%);"></i>
            <input type="text" id="spg-search" class="form-control form-control-sm" placeholder="Search group..." style="padding-left:32px;">
        </div>
        <div class="ms-auto d-flex gap-3 text-secondary">
            <i class="fa-solid fa-file-excel fs-5 cursor-pointer" id="spg-excel-btn"></i>
            <i class="fa-solid fa-print fs-5 cursor-pointer" id="spg-print-btn"></i>
        </div>
    </div>

    {{-- Table --}}
    <div class="flex-grow-1 overflow-auto">
        <table class="table table-hover mb-0 w-100" id="spg-table">
            <thead class="table-light" style="font-size:12px; text-transform:uppercase; position:sticky; top:0; z-index:1;">
                <tr>
                    <th>Party Group</th>
                    <th class="text-end">Sale Amount</th>
                    <th class="text-end">Purchase Amount</th>
                </tr>
            </thead>
            <tbody id="spg-tbody">
                <tr><td colspan="3" class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
            <tfoot class="table-light fw-bold" style="position:sticky; bottom:0;">
                <tr>
                    <td>Total:</td>
                    <td class="text-end" id="spg-total-sale">Rs 0.00</td>
                    <td class="text-end" id="spg-total-purchase">Rs 0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="tab-unreceived-invoices" class="report-tab-content d-none d-flex flex-column h-100 bg-white">
    <div class="d-flex align-items-center bg-light px-4 py-3 gap-3 flex-wrap">
        <div class="fw-bold text-dark">Agarri List / Unreceived Invoice PDF</div>
        <input type="date" id="ui-date-from" class="form-control form-control-sm" style="width:160px;">
        <input type="date" id="ui-date-to" class="form-control form-control-sm" style="width:160px;">
        <select id="ui-party" class="form-select form-select-sm" style="width:200px;">
            <option value="">All Parties</option>
            @foreach($parties as $party)
                <option value="{{ $party->id }}">{{ $party->name }}</option>
            @endforeach
        </select>
        <select id="ui-broker" class="form-select form-select-sm" style="width:200px;">
            <option value="">All Brokers</option>
            @foreach(($brokers ?? collect()) as $broker)
                <option value="{{ $broker->id }}">{{ $broker->name }}</option>
            @endforeach
        </select>
        <input type="text" id="ui-city" class="form-control form-control-sm" style="width:180px;" placeholder="City">
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" id="ui-reset-btn">Reset</button>
            <button class="btn btn-danger btn-sm" id="ui-generate-pdf-btn">
                <i class="fa-solid fa-file-pdf me-1"></i>Generate PDF
            </button>
        </div>
    </div>

    <div class="p-4 flex-grow-1 overflow-auto">
        <div class="border rounded-4 p-4 bg-white shadow-sm" style="max-width: 980px;">
            <h4 class="fw-bold mb-3">Urdu Khata Format PDF</h4>
            <p class="text-muted mb-3">
                Ye report sirf un sales ko include karegi jahan <strong>balance &gt; 0</strong> ho.
                PDF me party, broker, mobile numbers, WhatsApp, PTCL, item names, soda date, due date,
                deal days aur late days sab show honge.
            </p>
            <ul class="mb-0 text-secondary" style="line-height: 1.9;">
                <li>Sort by due date ascending</li>
                <li>Group by city and party</li>
                <li>Urdu style RTL PDF layout</li>
                <li>Direct PDF download</li>
            </ul>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
@endonce
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fromInput = document.getElementById('ui-date-from');
    const toInput = document.getElementById('ui-date-to');
    const partyInput = document.getElementById('ui-party');
    const brokerInput = document.getElementById('ui-broker');
    const cityInput = document.getElementById('ui-city');
    const generateButton = document.getElementById('ui-generate-pdf-btn');
    const resetButton = document.getElementById('ui-reset-btn');

    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const formatIso = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

    if (fromInput && !fromInput.value) fromInput.value = formatIso(firstDay);
    if (toInput && !toInput.value) toInput.value = formatIso(today);

    generateButton?.addEventListener('click', function () {
        const params = new URLSearchParams();
        if (fromInput?.value) params.set('from', fromInput.value);
        if (toInput?.value) params.set('to', toInput.value);
        if (partyInput?.value) params.set('party_id', partyInput.value);
        if (brokerInput?.value) params.set('broker_id', brokerInput.value);
        if (cityInput?.value.trim()) params.set('city', cityInput.value.trim());

        window.open(`{{ route('reports.unreceived-invoices.pdf') }}?${params.toString()}`, '_blank');
    });

    resetButton?.addEventListener('click', function () {
        if (fromInput) fromInput.value = formatIso(firstDay);
        if (toInput) toInput.value = formatIso(today);
        if (partyInput) partyInput.value = '';
        if (brokerInput) brokerInput.value = '';
        if (cityInput) cityInput.value = '';
    });
});
</script>
