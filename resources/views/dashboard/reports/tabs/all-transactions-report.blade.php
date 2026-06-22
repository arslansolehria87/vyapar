{{-- Day Book / All Transactions Tab --}}
<div id="tab-Alltransactions" class="report-tab-content d-none" style="background:#f3f4f6; height:calc(100vh - 60px); overflow-y:auto;">

    {{-- TOP FILTER BAR --}}
    <div class="d-flex flex-wrap align-items-center gap-2 px-4 py-2 bg-white border-bottom" style="position:sticky;top:0;z-index:10;">

        {{-- Date picker --}}
        <div class="d-flex align-items-center border rounded px-2 py-1" style="background:#fff; gap:6px;">
            <label class="mb-0 fw-semibold text-white rounded px-2 py-1 me-1"
                   style="font-size:12px; background:#6c757d; white-space:nowrap;">Date</label>
            <input type="date" id="db-date" class="border-0 bg-transparent fw-medium text-dark"
                   style="font-size:13px; outline:none; width:130px;">
        </div>

        {{-- Firms dropdown --}}
        <div class="d-flex align-items-center border rounded px-2 py-1" style="background:#fff;">
            <select id="db-firm" class="border-0 bg-transparent text-dark" style="font-size:13px; outline:none;">
                <option value="">All Firms</option>
            </select>
        </div>

        {{-- Type filter --}}
        <div class="d-flex align-items-center border rounded px-2 py-1" style="background:#fff;">
            <select id="db-type" class="border-0 bg-transparent text-dark" style="font-size:13px; outline:none;">
                <option value="">All Transactions</option>
                <option value="Sale">Sale</option>
                <option value="Purchase">Purchase</option>
                <option value="Payment-In">Payment In</option>
                <option value="Payment-Out">Payment Out</option>
                <option value="Credit Note">Credit Note</option>
                <option value="Debit Note">Debit Note</option>
                <option value="Cash Withdraw">Cash Withdraw</option>
                <option value="Cash Deposit">Cash Deposit</option>
                <option value="Bank to Bank">Bank to Bank</option>
                <option value="Opening Loan">Opening Loan</option>
                <option value="Processing Fee">Processing Fee</option>
                <option value="Loan Adjustment">Loan Adjustment</option>
                <option value="Delivery Challan">Delivery Challan</option>
                <option value="Expense">Expense</option>
            </select>
        </div>

        {{-- Payment type filter --}}
        <div class="d-flex align-items-center border rounded px-2 py-1" style="background:#fff;">
            <select id="db-payment-type" class="border-0 bg-transparent text-dark" style="font-size:13px; outline:none;">
                <option value="">All Payment Types</option>
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
                <option value="Online">Online</option>
                <option value="HBL">HBL</option>
            </select>
        </div>

        <div class="flex-grow-1"></div>

        {{-- Search bar --}}
        <div class="d-flex align-items-center border rounded px-2 py-1" style="background:#fff; width:200px;">
            <i class="fa-solid fa-magnifying-glass text-secondary me-2" style="font-size:12px;"></i>
            <input type="text" id="db-search" placeholder="Search transactions..."
                   class="border-0 bg-transparent text-dark w-100" style="font-size:13px; outline:none;">
        </div>

        {{-- Excel --}}
        <button id="db-excel-btn" class="btn d-flex align-items-center justify-content-center p-0" title="Export to Excel"
                style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;">
            <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:17px;"></i>
        </button>

        {{-- Print --}}
        <button id="db-print-btn" class="btn d-flex align-items-center justify-content-center p-0" title="Print"
                style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;">
            <i class="fa-solid fa-print" style="color:#4b5563;font-size:17px;"></i>
        </button>

        {{-- Fullscreen --}}
        <button id="db-fullscreen-btn" class="btn d-flex align-items-center justify-content-center p-0" title="Full screen"
                style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;">
            <i class="fa-solid fa-expand" style="color:#4b5563;font-size:15px;"></i>
        </button>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="d-flex flex-wrap gap-3 px-4 pt-3 pb-2">
        <div class="rounded-3 p-3 flex-fill" style="background:#fff;border:1px solid #e5e7eb;min-width:160px;">
            <p class="mb-1 text-secondary" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Money In</p>
            <h5 class="mb-0 fw-bold text-success" id="db-total-money-in">Rs 0.00</h5>
        </div>
        <div class="rounded-3 p-3 flex-fill" style="background:#fff;border:1px solid #e5e7eb;min-width:160px;">
            <p class="mb-1 text-secondary" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Money Out</p>
            <h5 class="mb-0 fw-bold text-danger" id="db-total-money-out">Rs 0.00</h5>
        </div>
        <div class="rounded-3 p-3 flex-fill" style="background:#fff;border:1px solid #e5e7eb;min-width:160px;">
            <p class="mb-1 text-secondary" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Total Amount</p>
            <h5 class="mb-0 fw-bold text-dark" id="db-total-amount">Rs 0.00</h5>
        </div>
        <div class="rounded-3 p-3 flex-fill" style="background:#fff;border:1px solid #e5e7eb;min-width:160px;">
            <p class="mb-1 text-secondary" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Transactions</p>
            <h5 class="mb-0 fw-bold text-primary" id="db-total-count">0</h5>
        </div>
    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="mx-4 mb-4 rounded-3 overflow-hidden" style="background:#fff;border:1px solid #e5e7eb;">

        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
            <span class="fw-bold text-dark" style="font-size:15px;">All Transactions</span>
            <span id="db-row-count" class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:12px;">0 records</span>
        </div>

        <div class="table-responsive" style="height:calc(100vh - 320px); overflow-y:auto;">
            <table class="table table-hover mb-0 align-middle" id="allTransactionsTable"
                data-column-drag="native" data-column-drag-storage="vyapar.reports.all-transactions.v1"
                style="border-collapse:collapse; font-size:13px;">
                <thead style="position:sticky;top:0;z-index:5;">
                    <tr style="background:#f3f4f6; border-bottom:2px solid #e5e7eb;">

                        {{-- NAME --}}
                        <th data-column-key="name" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
                            <div class="d-flex align-items-center gap-1">
                                NAME
                                <div class="dropdown">
                                    <button class="btn btn-sm p-0 ms-1 border-0 bg-transparent" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm" style="min-width:220px;font-size:13px;">
                                        <li class="px-3 pt-2">
                                            <p class="mb-1 text-muted" style="font-size:11px;">Match type:</p>
                                            <select id="db-name-match" class="form-select form-select-sm mb-2">
                                                <option value="contains">Contains</option>
                                                <option value="exact">Exact Match</option>
                                                <option value="starts">Starts With</option>
                                            </select>
                                            <input type="text" id="db-name-val" class="form-control form-control-sm mb-2" placeholder="Party / Account name">
                                        </li>
                                        <li class="px-3 pb-2 d-flex gap-2">
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbClearColFilter('name')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- REF NO --}}
                        <th data-column-key="reference" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
                            <div class="d-flex align-items-center gap-1">
                                REF NO.
                                <div class="dropdown">
                                    <button class="btn btn-sm p-0 ms-1 border-0 bg-transparent" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm" style="min-width:220px;font-size:13px;">
                                        <li class="px-3 pt-2">
                                            <p class="mb-1 text-muted" style="font-size:11px;">Match type:</p>
                                            <select id="db-ref-match" class="form-select form-select-sm mb-2">
                                                <option value="contains">Contains</option>
                                                <option value="exact">Exact Match</option>
                                            </select>
                                            <input type="number" id="db-ref-val" class="form-control form-control-sm mb-2" placeholder="Reference number">
                                        </li>
                                        <li class="px-3 pb-2 d-flex gap-2">
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbClearColFilter('ref')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- TYPE --}}
                        <th data-column-key="type" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
                            <div class="d-flex align-items-center gap-1">
                                TYPE
                                <div class="dropdown">
                                    <button class="btn btn-sm p-0 ms-1 border-0 bg-transparent" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm p-2" style="min-width:200px;font-size:13px;">
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Sale">Sale</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Purchase">Purchase</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Payment-In">Payment In</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Payment-Out">Payment Out</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Credit Note">Credit Note</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Debit Note">Debit Note</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Cash Withdraw">Cash Withdraw</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Cash Deposit">Cash Deposit</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-type-chk me-2" value="Expense">Expense</label></li>
                                        <li class="px-2 pt-2 d-flex gap-2">
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbClearColFilter('type')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- PAYMENT TYPE --}}
                        <th data-column-key="payment_type" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
                            <div class="d-flex align-items-center gap-1">
                                PAYMENT TYPE
                                <div class="dropdown">
                                    <button class="btn btn-sm p-0 ms-1 border-0 bg-transparent" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm p-2" style="min-width:180px;font-size:13px;">
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-pay-chk me-2" value="Cash">Cash</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-pay-chk me-2" value="Cheque">Cheque</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-pay-chk me-2" value="Online">Online</label></li>
                                        <li><label class="dropdown-item rounded"><input type="checkbox" class="db-pay-chk me-2" value="HBL">HBL</label></li>
                                        <li class="px-2 pt-2 d-flex gap-2">
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbClearColFilter('pay')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- TOTAL --}}
                        <th data-column-key="total" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:right;">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                TOTAL
                                <div class="dropdown">
                                    <button class="btn btn-sm p-0 ms-1 border-0 bg-transparent" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:220px;font-size:13px;">
                                        <li class="px-3 pt-2">
                                            <p class="mb-1 text-muted" style="font-size:11px;">Condition:</p>
                                            <select id="db-total-cond" class="form-select form-select-sm mb-2">
                                                <option value="gte">Greater than or equal</option>
                                                <option value="lte">Less than or equal</option>
                                                <option value="eq">Equal to</option>
                                                <option value="range">Range</option>
                                            </select>
                                            <input type="number" id="db-total-val" class="form-control form-control-sm mb-2" placeholder="Amount">
                                            <div id="db-total-range-row" class="d-none">
                                                <input type="number" id="db-total-val2" class="form-control form-control-sm mb-2" placeholder="Max amount">
                                            </div>
                                        </li>
                                        <li class="px-3 pb-2 d-flex gap-2">
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbClearColFilter('total')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- MONEY IN --}}
                        <th data-column-key="money_in" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:right;">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                MONEY IN
                                <div class="dropdown">
                                    <button class="btn btn-sm p-0 ms-1 border-0 bg-transparent" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:220px;font-size:13px;">
                                        <li class="px-3 pt-2">
                                            <p class="mb-1 text-muted" style="font-size:11px;">Condition:</p>
                                            <select id="db-moneyin-cond" class="form-select form-select-sm mb-2">
                                                <option value="gte">Greater than or equal</option>
                                                <option value="lte">Less than or equal</option>
                                                <option value="eq">Equal to</option>
                                            </select>
                                            <input type="number" id="db-moneyin-val" class="form-control form-control-sm mb-2" placeholder="Amount">
                                        </li>
                                        <li class="px-3 pb-2 d-flex gap-2">
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbClearColFilter('moneyin')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- MONEY OUT --}}
                        <th data-column-key="money_out" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:right;">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                MONEY OUT
                                <div class="dropdown">
                                    <button class="btn btn-sm p-0 ms-1 border-0 bg-transparent" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:220px;font-size:13px;">
                                        <li class="px-3 pt-2">
                                            <p class="mb-1 text-muted" style="font-size:11px;">Condition:</p>
                                            <select id="db-moneyout-cond" class="form-select form-select-sm mb-2">
                                                <option value="gte">Greater than or equal</option>
                                                <option value="lte">Less than or equal</option>
                                                <option value="eq">Equal to</option>
                                            </select>
                                            <input type="number" id="db-moneyout-val" class="form-control form-control-sm mb-2" placeholder="Amount">
                                        </li>
                                        <li class="px-3 pb-2 d-flex gap-2">
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbClearColFilter('moneyout')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        <th data-column-key="actions" style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:center;">
                            PRINT / SHARE
                        </th>
                    </tr>
                </thead>
                <tbody id="db-tbody">
                    <tr id="db-empty-row">
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fa-regular fa-folder-open fs-3 d-block mb-2 text-secondary opacity-50"></i>
                            No transactions found for the selected filters.
                        </td>
                    </tr>
                </tbody>
                <tfoot id="db-tfoot" style="display:none; background:#f9fafb; border-top:2px solid #e5e7eb;">
                    <tr>
                        <td data-column-key="name" class="px-4 py-3 fw-bold text-dark" style="font-size:13px;">Totals</td>
                        <td data-column-key="reference"></td>
                        <td data-column-key="type"></td>
                        <td data-column-key="payment_type"></td>
                        <td data-column-key="total" class="px-4 py-3 fw-bold text-dark text-end" id="db-foot-total" style="font-size:13px;"></td>
                        <td data-column-key="money_in" class="px-4 py-3 fw-bold text-success text-end" id="db-foot-moneyin" style="font-size:13px;"></td>
                        <td data-column-key="money_out" class="px-4 py-3 fw-bold text-danger text-end" id="db-foot-moneyout" style="font-size:13px;"></td>
                        <td data-column-key="actions"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ROW ACTION MODAL --}}
<div class="modal fade" id="dbRowActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:340px;">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="dbRowActionTitle">Transaction Actions</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-secondary btn-sm text-start" onclick="dbRowPrint()">
                        <i class="fa-solid fa-print me-2 text-secondary"></i> Print Voucher
                    </button>
                    <button class="btn btn-outline-success btn-sm text-start" onclick="dbRowShare('whatsapp')">
                        <i class="fa-brands fa-whatsapp me-2 text-success"></i> Share via WhatsApp
                    </button>
                    <button class="btn btn-outline-primary btn-sm text-start" onclick="dbRowShare('email')">
                        <i class="fa-regular fa-envelope me-2 text-primary"></i> Share via Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@once
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
@endonce
<script>
(function () {
    'use strict';

    /* ─── state ─── */
    let dbAllRows  = [];
    let dbFiltered = [];
    let dbActiveRow = null;
    let dbInitialized = false;

    /* ─── helpers ─── */
    const fmt = v => 'Rs ' + Number(v || 0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});

    /**
     * Determine money_in / money_out from transaction type.
     * Adjust the arrays below to match your business logic.
     */
    const MONEY_IN_TYPES  = ['Sale', 'Payment In', 'Payment-In', 'Cash Deposit',
                              'Opening Loan', 'Loan Adjustment', 'Credit Note'];
    const MONEY_OUT_TYPES = ['Purchase', 'Payment Out', 'Payment-Out', 'Cash Withdraw',
                              'Bank to Bank', 'Processing Fee', 'Debit Note', 'Expense'];

    function moneyIn(r) {
        if (r.money_in != null) return parseFloat(r.money_in);
        return MONEY_IN_TYPES.includes(r.type) ? parseFloat(r.amount || r.total || 0) : 0;
    }
    function moneyOut(r) {
        if (r.money_out != null) return parseFloat(r.money_out);
        return MONEY_OUT_TYPES.includes(r.type) ? parseFloat(r.amount || r.total || 0) : 0;
    }

    const typeColor = t => {
        const map = {
            'Sale':'#10b981','Purchase':'#ef4444','Payment-In':'#3b82f6','Payment In':'#3b82f6',
            'Payment-Out':'#f59e0b','Payment Out':'#f59e0b','Credit Note':'#8b5cf6',
            'Debit Note':'#ec4899','Cash Withdraw':'#64748b','Cash Deposit':'#22c55e',
            'Bank to Bank':'#0ea5e9','Opening Loan':'#6366f1','Processing Fee':'#f97316',
            'Loan Adjustment':'#14b8a6','Delivery Challan':'#84cc16','Expense':'#ef4444'
        };
        return map[t] || '#6b7280';
    };

    /* ─── INIT ─── */
    function dbInit() {
        if (!dbInitialized) {
            dbBindEvents();
            dbInitialized = true;
        }
        dbFetchData();
    }

    /* ─── FETCH from real API ─── */
    function dbFetchData() {
        const dateVal = document.getElementById('db-date').value;
        const typeVal = document.getElementById('db-type').value;
        const payVal  = document.getElementById('db-payment-type').value;

        // Show loading state
        const tbody = document.getElementById('db-tbody');
        tbody.querySelectorAll('.db-data-row').forEach(el => el.remove());
        document.getElementById('db-empty-row').innerHTML =
            '<td colspan="8" class="text-center text-muted py-5">' +
            '<i class="fa-solid fa-spinner fa-spin fs-4 d-block mb-2"></i>Loading transactions…</td>';
        document.getElementById('db-empty-row').style.display = '';

        const params = new URLSearchParams();
        if (dateVal) {
            params.append('from', dateVal);
            params.append('to',   dateVal);
        } else {
            params.append('from', '2000-01-01');
            params.append('to',   new Date().toISOString().slice(0, 10));
        }
        if (typeVal) params.append('type', typeVal);
        if (payVal)  params.append('payment_type', payVal);

        fetch(`{{ route('reports.all-transactions') }}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            dbAllRows = (data.transactions || []).map(r => ({
                id:        r.id,
                name:      r.party_name || r.reference || '—',
                ref:       r.bill_number || r.reference || '',
                type:      r.type || '',
                pay_type:  r.payment_type || '',
                total:     parseFloat(r.total_amount || r.amount || 0),
                money_in:  moneyIn(r),
                money_out: moneyOut(r),
            }));
            // Reset the empty row text for when there's genuinely no data
            document.getElementById('db-empty-row').innerHTML =
                '<td colspan="8" class="text-center text-muted py-5">' +
                '<i class="fa-regular fa-folder-open fs-3 d-block mb-2 text-secondary opacity-50"></i>' +
                'No transactions found for the selected filters.</td>';
            dbApplyFilters();
        })
        .catch(err => {
            console.error('All Transactions fetch error:', err);
            document.getElementById('db-empty-row').innerHTML =
                '<td colspan="8" class="text-center text-danger py-5">Error loading transactions. Please try again.</td>';
            document.getElementById('db-empty-row').style.display = '';
            dbAllRows = [];
            dbFiltered = [];
        });
    }

    /* ─── apply all column filters ─── */
    window.dbApplyFilters = function () {
        let rows = [...dbAllRows];

        // search bar
        const q = (document.getElementById('db-search').value || '').toLowerCase();
        if (q) rows = rows.filter(r =>
            r.name.toLowerCase().includes(q) ||
            r.type.toLowerCase().includes(q) ||
            String(r.ref).toLowerCase().includes(q)
        );

        // top-bar type dropdown (if set)
        const topType = document.getElementById('db-type').value;
        if (topType) rows = rows.filter(r => r.type === topType);

        // top-bar payment type dropdown
        const topPay = document.getElementById('db-payment-type').value;
        if (topPay) rows = rows.filter(r => r.pay_type === topPay);

        // name column filter
        const nameVal   = (document.getElementById('db-name-val').value || '').toLowerCase();
        const nameMatch = document.getElementById('db-name-match').value;
        if (nameVal) rows = rows.filter(r => {
            const n = r.name.toLowerCase();
            if (nameMatch === 'exact')  return n === nameVal;
            if (nameMatch === 'starts') return n.startsWith(nameVal);
            return n.includes(nameVal);
        });

        // ref column filter
        const refVal = (document.getElementById('db-ref-val').value || '').toLowerCase();
        if (refVal) rows = rows.filter(r => String(r.ref).toLowerCase().includes(refVal));

        // type checkboxes (column header)
        const typeChecked = [...document.querySelectorAll('.db-type-chk:checked')].map(c => c.value);
        if (typeChecked.length) rows = rows.filter(r => typeChecked.includes(r.type));

        // pay type checkboxes
        const payChecked = [...document.querySelectorAll('.db-pay-chk:checked')].map(c => c.value);
        if (payChecked.length) rows = rows.filter(r => payChecked.includes(r.pay_type));

        // total filter
        const totalVal  = parseFloat(document.getElementById('db-total-val').value);
        const totalCond = document.getElementById('db-total-cond').value;
        if (!isNaN(totalVal)) {
            const totalVal2 = parseFloat(document.getElementById('db-total-val2').value);
            rows = rows.filter(r => {
                if (totalCond === 'gte')   return r.total >= totalVal;
                if (totalCond === 'lte')   return r.total <= totalVal;
                if (totalCond === 'eq')    return r.total === totalVal;
                if (totalCond === 'range') return r.total >= totalVal && r.total <= (isNaN(totalVal2) ? Infinity : totalVal2);
                return true;
            });
        }

        // money in filter
        const miVal  = parseFloat(document.getElementById('db-moneyin-val').value);
        const miCond = document.getElementById('db-moneyin-cond').value;
        if (!isNaN(miVal)) rows = rows.filter(r => {
            if (miCond === 'gte') return r.money_in >= miVal;
            if (miCond === 'lte') return r.money_in <= miVal;
            return r.money_in === miVal;
        });

        // money out filter
        const moVal  = parseFloat(document.getElementById('db-moneyout-val').value);
        const moCond = document.getElementById('db-moneyout-cond').value;
        if (!isNaN(moVal)) rows = rows.filter(r => {
            if (moCond === 'gte') return r.money_out >= moVal;
            if (moCond === 'lte') return r.money_out <= moVal;
            return r.money_out === moVal;
        });

        dbFiltered = rows;
        dbRender();
    };

    /* ─── render table ─── */
    function dbRender() {
        const tbody    = document.getElementById('db-tbody');
        const emptyRow = document.getElementById('db-empty-row');
        const tfoot    = document.getElementById('db-tfoot');

        const totalMoneyIn  = dbFiltered.reduce((s, r) => s + r.money_in,  0);
        const totalMoneyOut = dbFiltered.reduce((s, r) => s + r.money_out, 0);
        const totalAmount   = dbFiltered.reduce((s, r) => s + r.total,     0);

        document.getElementById('db-total-money-in').textContent  = fmt(totalMoneyIn);
        document.getElementById('db-total-money-out').textContent = fmt(totalMoneyOut);
        document.getElementById('db-total-amount').textContent    = fmt(totalAmount);
        document.getElementById('db-total-count').textContent     = dbFiltered.length;
        document.getElementById('db-row-count').textContent       = dbFiltered.length + ' records';

        tbody.querySelectorAll('.db-data-row').forEach(el => el.remove());

        if (!dbFiltered.length) {
            emptyRow.style.display = '';
            tfoot.style.display = 'none';
            return;
        }
        emptyRow.style.display = 'none';
        tfoot.style.display = '';

        document.getElementById('db-foot-total').textContent    = fmt(totalAmount);
        document.getElementById('db-foot-moneyin').textContent  = fmt(totalMoneyIn);
        document.getElementById('db-foot-moneyout').textContent = fmt(totalMoneyOut);

        dbFiltered.forEach((r, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'db-data-row';
            tr.style.cssText = 'border-bottom:1px solid #f3f4f6;';
            tr.setAttribute('data-id', r.id);
            const rowBg = idx === 0 ? '#eff6ff' : '#fff';

            tr.innerHTML = `
                <td data-column-key="name" style="padding:12px 14px; color:#1f2937; background:${rowBg};">
                    <span class="fw-medium">${r.name}</span>
                </td>
                <td data-column-key="reference" style="padding:12px 14px; color:#6b7280; background:${rowBg};">${r.ref || ''}</td>
                <td data-column-key="type" style="padding:12px 14px; background:${rowBg};">
                    <span class="badge rounded-pill px-2 py-1 fw-medium"
                          style="font-size:11px; background:${typeColor(r.type)}20; color:${typeColor(r.type)};">
                        ${r.type}
                    </span>
                </td>
                <td data-column-key="payment_type" style="padding:12px 14px; color:#6b7280; background:${rowBg};">${r.pay_type || '—'}</td>
                <td data-column-key="total" style="padding:12px 14px; color:#1f2937; text-align:right; font-weight:500; background:${rowBg};">${fmt(r.total)}</td>
                <td data-column-key="money_in" style="padding:12px 14px; color:${r.money_in > 0 ? '#10b981' : '#9ca3af'}; text-align:right; font-weight:${r.money_in > 0 ? '600' : '400'}; background:${rowBg};">${fmt(r.money_in)}</td>
                <td data-column-key="money_out" style="padding:12px 14px; color:${r.money_out > 0 ? '#ef4444' : '#9ca3af'}; text-align:right; font-weight:${r.money_out > 0 ? '600' : '400'}; background:${rowBg};">${fmt(r.money_out)}</td>
                <td data-column-key="actions" style="padding:12px 14px; text-align:center; background:${rowBg};">
                    <button class="btn btn-sm p-0 border-0 bg-transparent" title="Print / Share"
                            onclick="dbOpenRowAction(event, ${r.id})">
                        <i class="fa-solid fa-print" style="color:#6b7280;font-size:14px;"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    /* ─── clear column filter ─── */
    window.dbClearColFilter = function (col) {
        const map = {
            name:     () => { document.getElementById('db-name-val').value = ''; document.getElementById('db-name-match').value = 'contains'; },
            ref:      () => { document.getElementById('db-ref-val').value  = ''; },
            type:     () => { document.querySelectorAll('.db-type-chk').forEach(c => c.checked = false); },
            pay:      () => { document.querySelectorAll('.db-pay-chk').forEach(c => c.checked = false); },
            total:    () => { document.getElementById('db-total-val').value = ''; document.getElementById('db-total-val2').value = ''; },
            moneyin:  () => { document.getElementById('db-moneyin-val').value  = ''; },
            moneyout: () => { document.getElementById('db-moneyout-val').value = ''; },
        };
        if (map[col]) map[col]();
        dbApplyFilters();
    };

    /* ─── row action modal ─── */
    window.dbOpenRowAction = function (e, id) {
        e.preventDefault(); e.stopPropagation();
        dbActiveRow = dbAllRows.find(r => r.id === id);
        if (!dbActiveRow) return;
        document.getElementById('dbRowActionTitle').textContent = dbActiveRow.type + ' — ' + dbActiveRow.name;
        new bootstrap.Modal(document.getElementById('dbRowActionModal')).show();
    };

    window.dbRowPrint = function () {
        if (!dbActiveRow) return;
        const w = window.open('', '_blank');
        w.document.write(`<html><body style="font-family:sans-serif;padding:32px;">
            <h3>Day Book Voucher</h3>
            <p><b>Name:</b> ${dbActiveRow.name}</p>
            <p><b>Type:</b> ${dbActiveRow.type}</p>
            <p><b>Ref:</b> ${dbActiveRow.ref || '—'}</p>
            <p><b>Payment Type:</b> ${dbActiveRow.pay_type || '—'}</p>
            <p><b>Total:</b> Rs ${dbActiveRow.total}</p>
            <p><b>Money In:</b> Rs ${dbActiveRow.money_in}</p>
            <p><b>Money Out:</b> Rs ${dbActiveRow.money_out}</p>
        </body></html>`);
        w.document.close(); w.print();
    };

    window.dbRowShare = function (method) {
        if (!dbActiveRow) return;
        const text = `Day Book Entry\nName: ${dbActiveRow.name}\nType: ${dbActiveRow.type}\nTotal: Rs ${dbActiveRow.total}`;
        if (method === 'whatsapp') window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
        else window.location.href = 'mailto:?subject=Day Book Entry&body=' + encodeURIComponent(text);
    };

    /* ─── excel export ─── */
    function dbExportExcel() {
        let csv = 'Name,Ref No,Type,Payment Type,Total,Money In,Money Out\n';
        dbFiltered.forEach(r => {
            csv += `"${r.name}","${r.ref}","${r.type}","${r.pay_type}",${r.total},${r.money_in},${r.money_out}\n`;
        });
        const blob = new Blob([csv], {type:'text/csv'});
        const a = Object.assign(document.createElement('a'), {href: URL.createObjectURL(blob), download:'all-transactions.csv'});
        document.body.appendChild(a); a.click(); a.remove();
    }

    /* ─── print all ─── */
    function dbPrintAll() {
        const date = document.getElementById('db-date').value || 'All dates';
        let html = `<html><head><title>All Transactions</title>
        <style>body{font-family:sans-serif;font-size:12px;padding:24px}
        table{width:100%;border-collapse:collapse}
        th{background:#f3f4f6;padding:8px;text-align:left;border:1px solid #e5e7eb;font-size:11px;}
        td{padding:8px;border:1px solid #e5e7eb;}
        tfoot td{font-weight:700;background:#f9fafb}</style></head>
        <body><h2>All Transactions</h2><p>Date: ${date}</p>
        <table><thead><tr><th>Name</th><th>Ref</th><th>Type</th><th>Payment Type</th>
        <th style="text-align:right">Total</th><th style="text-align:right">Money In</th>
        <th style="text-align:right">Money Out</th></tr></thead><tbody>`;

        dbFiltered.forEach(r => {
            html += `<tr><td>${r.name}</td><td>${r.ref||''}</td><td>${r.type}</td><td>${r.pay_type||'—'}</td>
                <td style="text-align:right">Rs ${r.total.toLocaleString()}</td>
                <td style="text-align:right">Rs ${r.money_in.toLocaleString()}</td>
                <td style="text-align:right">Rs ${r.money_out.toLocaleString()}</td></tr>`;
        });

        const mi  = dbFiltered.reduce((s, r) => s + r.money_in,  0);
        const mo  = dbFiltered.reduce((s, r) => s + r.money_out, 0);
        const tot = dbFiltered.reduce((s, r) => s + r.total,     0);

        html += `</tbody><tfoot><tr><td colspan="4">Totals</td>
            <td style="text-align:right">Rs ${tot.toLocaleString()}</td>
            <td style="text-align:right">Rs ${mi.toLocaleString()}</td>
            <td style="text-align:right">Rs ${mo.toLocaleString()}</td>
        </tr></tfoot></table></body></html>`;

        const w = window.open('', '_blank'); w.document.write(html); w.document.close(); w.print();
    }

    /* ─── bind events (called ONCE) ─── */
    function dbBindEvents() {
        // Top-bar filters re-fetch from server
        ['db-date', 'db-firm'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', dbFetchData);
        });

        // Type & payment type dropdowns just re-filter client side
        // (data is already fetched; re-fetch only if you want server-side filtering)
        ['db-type', 'db-payment-type'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', dbApplyFilters);
        });

        document.getElementById('db-search')?.addEventListener('input', dbApplyFilters);

        document.getElementById('db-total-cond')?.addEventListener('change', function () {
            document.getElementById('db-total-range-row').classList.toggle('d-none', this.value !== 'range');
        });

        document.getElementById('db-excel-btn')?.addEventListener('click', dbExportExcel);
        document.getElementById('db-print-btn')?.addEventListener('click', dbPrintAll);

        document.getElementById('db-fullscreen-btn')?.addEventListener('click', function () {
            const tab = document.getElementById('tab-Alltransactions');
            if (!document.fullscreenElement) {
                tab.requestFullscreen && tab.requestFullscreen();
                this.querySelector('i').className = 'fa-solid fa-compress';
            } else {
                document.exitFullscreen && document.exitFullscreen();
                this.querySelector('i').className = 'fa-solid fa-expand';
            }
        });
    }

    /* ─── hook into tab activation ─── */
    document.addEventListener('click', function (e) {
        // Adjust selector to match your nav tab links
        const link = e.target.closest('[data-target="Alltransactions"], [href="#tab-Alltransactions"]');
        if (link) setTimeout(dbInit, 150);
    });

    // Auto-init if this tab is already visible on page load
    document.addEventListener('DOMContentLoaded', function () {
        const tab = document.getElementById('tab-Alltransactions');
        if (tab && !tab.classList.contains('d-none')) dbInit();
    });

    if (document.readyState !== 'loading') {
        const tab = document.getElementById('tab-Alltransactions');
        if (tab && !tab.classList.contains('d-none')) dbInit();
    }

})();
</script>
