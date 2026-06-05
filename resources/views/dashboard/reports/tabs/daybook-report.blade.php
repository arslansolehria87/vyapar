{{-- Day Book Tab --}}
<div id="tab-Daybook" class="report-tab-content d-none" style="background:#f9fafb; min-height:100%;">

    {{-- ══════════════════════════════════════════════
         TOP FILTER BAR
    ══════════════════════════════════════════════ --}}
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
                <option value="firm1">Firm 1</option>
                <option value="firm2">Firm 2</option>
                <option value="firm3">Firm 3</option>
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
        {{-- <div class="d-flex align-items-center border rounded px-2 py-1" style="background:#fff;">
            <select id="db-payment-type" class="border-0 bg-transparent text-dark" style="font-size:13px; outline:none;">
                <option value="">All Payment Types</option>
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
                <option value="Online">Online</option>
                <option value="HBL">HBL</option>
                <option value="hasnain">hasnain</option>
            </select>
        </div> --}}

        {{-- Spacer --}}
        <div class="flex-grow-1"></div>

        {{-- Search bar --}}
        <div class="d-flex align-items-center border rounded px-2 py-1" style="background:#fff; width:200px;">
            <i class="fa-solid fa-magnifying-glass text-secondary me-2" style="font-size:12px;"></i>
            <input type="text" id="db-search" placeholder="Search transactions..."
                   class="border-0 bg-transparent text-dark w-100" style="font-size:13px; outline:none;">
        </div>

        {{-- Excel --}}
        <button id="db-excel-btn" class="btn d-flex align-items-center justify-content-center p-0"
                title="Export to Excel"
                style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;">
            <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:17px;"></i>
        </button>

        {{-- Print --}}
        <button id="db-print-btn" class="btn d-flex align-items-center justify-content-center p-0"
                title="Print"
                style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;">
            <i class="fa-solid fa-print" style="color:#4b5563;font-size:17px;"></i>
        </button>

        {{-- Expand / Full screen --}}
        <button id="db-fullscreen-btn" class="btn d-flex align-items-center justify-content-center p-0"
                title="Full screen"
                style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;">
            <i class="fa-solid fa-expand" style="color:#4b5563;font-size:15px;"></i>
        </button>
    </div>

    {{-- ══════════════════════════════════════════════
         SUMMARY CARDS
    ══════════════════════════════════════════════ --}}
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

    {{-- ══════════════════════════════════════════════
         MAIN TABLE CARD
    ══════════════════════════════════════════════ --}}
    <div class="mx-4 mb-4 rounded-3 overflow-hidden" style="background:#fff;border:1px solid #e5e7eb;">

        {{-- Table header title --}}
        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
            <span class="fw-bold text-dark" style="font-size:15px;">Day Book Transactions</span>
            <span id="db-row-count" class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:12px;">0 records</span>
        </div>

        <div class="table-responsive" style="max-height:calc(100vh - 370px); overflow-y:auto;">
            <table class="table table-hover mb-0 align-middle" style="border-collapse:collapse; font-size:13px;">

                {{-- ── THEAD ── --}}
                <thead style="position:sticky;top:0;z-index:5;">
                    <tr style="background:#f3f4f6; border-bottom:2px solid #e5e7eb;">

                        {{-- NAME --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
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
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbDaybookClearColFilter('name')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbDaybookApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- REF NO --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
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
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbDaybookClearColFilter('ref')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbDaybookApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- TYPE --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
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
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbDaybookClearColFilter('type')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbDaybookApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- PAYMENT TYPE --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap;">
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
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbDaybookClearColFilter('pay')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbDaybookApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- TOTAL --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:right;">
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
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbDaybookClearColFilter('total')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbDaybookApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- MONEY IN --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:right;">
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
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbDaybookClearColFilter('moneyin')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbDaybookApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- MONEY OUT --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:right;">
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
                                            <button class="btn btn-sm rounded-pill px-3" style="background:#ebeaea;color:#71748e;" onclick="dbDaybookClearColFilter('moneyout')">Clear</button>
                                            <button class="btn btn-sm rounded-pill px-3 text-white" style="background:#d4112e;" onclick="dbDaybookApplyFilters()">Apply</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </th>

                        {{-- PRINT / SHARE --}}
                        <th style="padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; text-align:center;">
                            PRINT / SHARE
                        </th>
                    </tr>
                </thead>

                {{-- ── TBODY ── --}}
                <tbody id="db-tbody">
                    {{-- Populated by JS --}}
                    <tr id="db-empty-row">
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fa-regular fa-folder-open fs-3 d-block mb-2 text-secondary opacity-50"></i>
                            No transactions found for the selected filters.
                        </td>
                    </tr>
                </tbody>

                {{-- ── TFOOT ── --}}
                <tfoot id="db-tfoot" style="display:none; background:#f9fafb; border-top:2px solid #e5e7eb;">
                    <tr>
                        <td colspan="4" class="px-4 py-3 fw-bold text-dark" style="font-size:13px;">Totals</td>
                        <td class="px-4 py-3 fw-bold text-dark text-end" id="db-foot-total" style="font-size:13px;"></td>
                        <td class="px-4 py-3 fw-bold text-success text-end" id="db-foot-moneyin" style="font-size:13px;"></td>
                        <td class="px-4 py-3 fw-bold text-danger text-end" id="db-foot-moneyout" style="font-size:13px;"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>{{-- end table-responsive --}}
    </div>{{-- end card --}}

</div>{{-- end tab-Daybook --}}


{{-- ══════════════════════════════════════════════════════
     PRINT MODAL
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="dbPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Day Book — Print Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dbPrintArea" style="font-size:13px;">
                {{-- filled by JS --}}
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-dark btn-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ROW ACTION MODAL  (Print / Share per row)
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="dbRowActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:340px;">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="dbRowActionTitle">Transaction Actions</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-secondary btn-sm text-start" onclick="dbDaybookRowPrint()">
                        <i class="fa-solid fa-print me-2 text-secondary"></i> Print Voucher
                    </button>
                    <button class="btn btn-outline-success btn-sm text-start" onclick="dbDaybookRowShare('whatsapp')">
                        <i class="fa-brands fa-whatsapp me-2 text-success"></i> Share via WhatsApp
                    </button>
                    <button class="btn btn-outline-primary btn-sm text-start" onclick="dbDaybookRowShare('email')">
                        <i class="fa-regular fa-envelope me-2 text-primary"></i> Share via Email
                    </button>
                    <button class="btn btn-outline-secondary btn-sm text-start" onclick="dbRowDownloadPdf()">
                        <i class="fa-regular fa-file-pdf me-2 text-danger"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════
     DAY BOOK JAVASCRIPT
══════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';
    const DB_MOCK = [];

    //yahn syyy agyyy,,,
//     window.fetchCashFlow = function fetchCashFlow() {
//     if (CF.loading) return;
//     CF.loading = true;
//     hideError();

//     const { from, to } = getDateRange();
//     const tbody = document.getElementById('cfTbody');
//     const tfoot = document.getElementById('cfTfoot');

//     tbody.innerHTML = `
//       <tr id="cfLoadingRow">
//         <td colspan="12" class="text-center py-5">
//           <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
//           <span class="text-muted" style="font-size:13px;">Loading…</span>
//         </td>
//       </tr>`;
//     tfoot.style.display = 'none';

//     const csrfToken = window.App?.csrfToken
//       || document.querySelector('meta[name=csrf-token]')?.content
//       || '';

//    fetch(`/dashboard/reports/cash-flow?from=${from}&to=${to}`, {
//       headers: {
//         'X-Requested-With': 'XMLHttpRequest',
//         'X-CSRF-TOKEN': csrfToken,
//         'Accept': 'application/json',
//       }
//     })
//     .then(async r => {
//       CF.loading = false;

//       /* ── Handle non-200 HTTP status ── */
//       if (!r.ok) {
//         const text = await r.text().catch(() => '');
//         let detail = `HTTP ${r.status} ${r.statusText}`;
//         try {
//           const json = JSON.parse(text);
//           detail = json.message || json.error || detail;
//         } catch (_) { /* non-JSON body */ }
//         showError(detail);
//         return;
//       }

//       let data;
//       try {
//         data = await r.json();
//       } catch (_) {
//         showError('Server returned invalid JSON. Check the /reports/cash-flow controller.');
//         return;
//       }

//       if (!data.success) {
//         showError(data.message || data.error || 'Server returned success:false');
//         return;
//       }

//       CF.raw = data.transactions || [];
//       CF.activeFilters = {};

//       document.getElementById('cfOpeningCash').textContent = fmtN(data.opening_balance || 0);
//       document.getElementById('cfPrintOpening').textContent = fmt(data.opening_balance || 0);

//       applyFiltersAndRender();
//     })
//     .catch(err => {
//       CF.loading = false;
//       showError('Network error — could not reach server. (' + err.message + ')');
//       console.error('CashFlow fetch error:', err);
//     });
//   };

//     fetchCashFlow();
// window.fetchDayBook = function fetchDayBook() {
//  fetch('/dashboard/reports/day-book', {
//         headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
//     })
//     .then(response => response.json())
//     .then(data => {

//         DB_MOCK = data;
        
//         console.log("this is DBB Moch:",DB_MOCK);

//         // yahan apna kaam karo (table render etc)
//     })
//     .catch(error => {
//         console.error('Error:', error);
//     });
// };

// fetchDayBook();

    // fromDate, toDate
//      function saleLoadData() {
//     const tbody = document.getElementById('db-tbody');
//     tbody.innerHTML = `<tr><td colspan="8" class="sale-empty-state">
//       <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;color:#d1d5db;display:block;margin-bottom:6px;"></i>
//       Loading transactions…
//     </td></tr>`;
// // from=${fromDate}&to=${toDate}
//     fetch(`{{ route('reports.daybook') }}?`, {
//       headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
//     })
//     .then(r => r.json())
//     .then(data => {
//       if (data.success) {
//         // saleAllRows  = data.transactions || [];
//         // saleFiltered = [...saleAllRows];
//         DB_MOCK = data.transactions || [];
//         updateSaleSummary(data);
//         saleRenderTable();
//       } else {
//         DB_MOCK = [name:"hello",ref:"",type:"Sale",pay_type:"Cash",total:100,money_in:100,money_out:0];
//         // tbody.innerHTML = `<tr><td colspan="8" class="sale-empty-state">No data found.</td></tr>`;
//       }
//     })
//     .catch(() => {
//       tbody.innerHTML = `<tr><td colspan="8" class="sale-empty-state text-danger">Error loading data.</td></tr>`;
//     });
//   }

    /* ─── sample / mock data (replace with real API call) ─── */
    // const DB_MOCK = [
    //     { id:1,  name:'talha',           ref:'',  type:'Cash Withdraw',   pay_type:'',      total:100,   money_in:0,    money_out:0 },
    //     { id:2,  name:'hasnain',           ref:'',  type:'Cash Withdraw',   pay_type:'',      total:100,   money_in:0,    money_out:0 },
    //     { id:3,  name:'hasnain',           ref:'',  type:'Cash Deposit',    pay_type:'',      total:100,   money_in:0,    money_out:0 },
    //     { id:4,  name:'hasnain TO HBL',    ref:'',  type:'Bank to Bank',    pay_type:'',      total:1000,  money_in:0,    money_out:0 },
    //     { id:5,  name:'[Loan Acc] ok ha',  ref:'',  type:'Opening Loan',    pay_type:'HBL',   total:5000,  money_in:5000, money_out:0 },
    //     { id:6,  name:'[Loan Acc] ok ha',  ref:'',  type:'Processing Fee',  pay_type:'hasnain',total:100,  money_in:0,    money_out:100 },
    //     { id:7,  name:'[Loan Acc] maleeq azhar',ref:'',type:'Loan Adjustment',pay_type:'hasnain',total:100,money_in:100, money_out:0 },
    //     { id:8,  name:'Party 1',            ref:'16',type:'Sale',            pay_type:'hasnain',total:700,  money_in:700,  money_out:0 },
    //     { id:9,  name:'Party 1',            ref:'1', type:'Credit Note',     pay_type:'hasnain',total:700,  money_in:0,    money_out:0 },
    //     { id:10, name:'Party 1',            ref:'17',type:'Sale',            pay_type:'hasnain',total:600,  money_in:600,  money_out:0 },
    //     { id:11, name:'Party 1',            ref:'18',type:'Sale',            pay_type:'Cash',   total:700,  money_in:600,  money_out:0 },
    //     { id:12, name:'maleeq',             ref:'1', type:'Payment-In',      pay_type:'Cash',   total:100,  money_in:100,  money_out:0 },
    //     { id:13, name:'dodh patya',         ref:'2', type:'Delivery Challan',pay_type:'',       total:600,  money_in:0,    money_out:0 },
    //     { id:14, name:'dodh patya',         ref:'19',type:'Sale',            pay_type:'hasnain',total:700,  money_in:500,  money_out:0 },
    // ];

    let dbDaybookAllRows   = [...DB_MOCK];
    let dbDaybookFiltered  = [...DB_MOCK];
    let dbDaybookActiveRow = null;   // for print/share modal

    /* ─── helpers ─── */
    const fmt = v => 'Rs ' + Number(v || 0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});

    const typeColor = t => {
        const map = {
            'Sale':'#10b981','Purchase':'#ef4444','Payment-In':'#3b82f6',
            'Payment-Out':'#f59e0b','Credit Note':'#8b5cf6','Debit Note':'#ec4899',
            'Cash Withdraw':'#64748b','Cash Deposit':'#22c55e','Bank to Bank':'#0ea5e9',
            'Opening Loan':'#6366f1','Processing Fee':'#f97316','Loan Adjustment':'#14b8a6',
            'Delivery Challan':'#84cc16','Expense':'#ef4444'
        };
        return map[t] || '#6b7280';
    };

    /* ─── init on tab show ─── */
    function dbDaybookInit() {
        dbDaybookBindEvents();
        dbDaybookSetTodayDateAndFilter();
    }

    /* ─── helper to set today's date and apply Day Book filters ─── */
    window.dbDaybookSetTodayDateAndFilter = function () {
        const today = new Date().toISOString().slice(0,10);
        const dateInput = document.getElementById('db-date');
        if (dateInput) {
            dateInput.value = today;
        }
        dbDaybookFetchData();
    };

    /* ─── fetch (replace with real AJAX) ─── */
    function dbDaybookFetchData() {
        const date     = document.getElementById('db-date').value;
        const firm     = document.getElementById('db-firm').value;
        const typeTop  = document.getElementById('db-type').value;
        const payTop   = document.getElementById('db-payment-type').value;

        // In real app: $.get('/reports/daybook', {date, firm, type:typeTop, payment_type:payTop}, ...)
        // For now: filter mock data
        dbDaybookAllRows = DB_MOCK.filter(r => {
            if (typeTop && r.type !== typeTop) return false;
            if (payTop  && r.pay_type !== payTop) return false;
            if (firm)   return true; // would filter by firm
            return true;
        });

        dbDaybookApplyFilters();
    }

    /* ─── apply column-level filters ─── */
    window.dbDaybookApplyFilters = function () {
        let rows = [...dbDaybookAllRows];

        // search bar
        const q = (document.getElementById('db-search').value || '').toLowerCase();
        if (q) rows = rows.filter(r =>
            r.name.toLowerCase().includes(q) ||
            r.type.toLowerCase().includes(q) ||
            String(r.ref).includes(q)
        );

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

        // type checkboxes
        const typeChecked = [...document.querySelectorAll('.db-type-chk:checked')].map(c => String(c.value).toLowerCase());
        if (typeChecked.length) rows = rows.filter(r => typeChecked.includes(String(r.type || '').toLowerCase()));

        // pay type checkboxes
        const payChecked = [...document.querySelectorAll('.db-pay-chk:checked')].map(c => String(c.value).toLowerCase());
        if (payChecked.length) rows = rows.filter(r => payChecked.includes(String(r.pay_type || '').toLowerCase()));

        // total filter
        const totalVal  = parseFloat(document.getElementById('db-total-val').value);
        const totalCond = document.getElementById('db-total-cond').value;
        if (!isNaN(totalVal)) {
            const totalVal2 = parseFloat(document.getElementById('db-total-val2').value);
            rows = rows.filter(r => {
                if (totalCond === 'gte')   return r.total >= totalVal;
                if (totalCond === 'lte')   return r.total <= totalVal;
                if (totalCond === 'eq')    return r.total === totalVal;
                if (totalCond === 'range') return r.total >= totalVal && r.total <= (isNaN(totalVal2)?Infinity:totalVal2);
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

        dbDaybookFiltered = rows;
        dbDaybookRender();
    };

    /* ─── render table ─── */
    function dbDaybookRender() {
        const tbody    = document.getElementById('db-tbody');
        const emptyRow = document.getElementById('db-empty-row');
        const tfoot    = document.getElementById('db-tfoot');

        // update summary cards
        const totalMoneyIn  = dbDaybookFiltered.reduce((s,r) => s + r.money_in,  0);
        const totalMoneyOut = dbDaybookFiltered.reduce((s,r) => s + r.money_out, 0);
        const totalAmount   = dbDaybookFiltered.reduce((s,r) => s + r.total,     0);

        document.getElementById('db-total-money-in').textContent  = fmt(totalMoneyIn);
        document.getElementById('db-total-money-out').textContent = fmt(totalMoneyOut);
        document.getElementById('db-total-amount').textContent    = fmt(totalAmount);
        document.getElementById('db-total-count').textContent     = dbDaybookFiltered.length;
        document.getElementById('db-row-count').textContent       = dbDaybookFiltered.length + ' records';

        // remove old dynamic rows
        tbody.querySelectorAll('.db-data-row').forEach(el => el.remove());

        if (!dbDaybookFiltered.length) {
            emptyRow.style.display = '';
            tfoot.style.display = 'none';
            return;
        }
        emptyRow.style.display = 'none';
        tfoot.style.display = '';

        // footer totals
        document.getElementById('db-foot-total').textContent    = fmt(totalAmount);
        document.getElementById('db-foot-moneyin').textContent  = fmt(totalMoneyIn);
        document.getElementById('db-foot-moneyout').textContent = fmt(totalMoneyOut);

        // rows
        dbDaybookFiltered.forEach((r, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'db-data-row';
            tr.style.cssText = 'border-bottom:1px solid #f3f4f6; cursor:pointer;';
            tr.setAttribute('data-id', r.id);

            // highlight first row (like screenshot)
            const rowBg = idx === 0 ? '#eff6ff' : '#fff';

            tr.innerHTML = `
                <td style="padding:12px 14px; color:#1f2937; background:${rowBg};">
                    <span class="fw-medium">${r.name}</span>
                </td>
                <td style="padding:12px 14px; color:#6b7280; background:${rowBg};">${r.ref || ''}</td>
                <td style="padding:12px 14px; background:${rowBg};">
                    <span class="badge rounded-pill px-2 py-1 fw-medium"
                          style="font-size:11px; background:${typeColor(r.type)}20; color:${typeColor(r.type)};">
                        ${r.type}
                    </span>
                </td>
                <td style="padding:12px 14px; color:#6b7280; background:${rowBg};">${r.pay_type || '—'}</td>
                <td style="padding:12px 14px; color:#1f2937; text-align:right; font-weight:500; background:${rowBg};">${fmt(r.total)}</td>
                <td style="padding:12px 14px; color:${r.money_in>0?'#10b981':'#9ca3af'}; text-align:right; font-weight:${r.money_in>0?'600':'400'}; background:${rowBg};">${fmt(r.money_in)}</td>
                <td style="padding:12px 14px; color:${r.money_out>0?'#ef4444':'#9ca3af'}; text-align:right; font-weight:${r.money_out>0?'600':'400'}; background:${rowBg};">${fmt(r.money_out)}</td>
                <td style="padding:12px 14px; text-align:center; background:${rowBg};">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <button class="btn btn-sm p-0 border-0 bg-transparent" title="Print"
                                onclick="dbDaybookOpenRowAction(event, ${r.id})">
                            <i class="fa-solid fa-print" style="color:#6b7280;font-size:14px;"></i>
                        </button>
                        <button class="btn btn-sm p-0 border-0 bg-transparent" title="Share"
                                onclick="dbDaybookOpenRowAction(event, ${r.id})">
                            <i class="fa-solid fa-share-nodes" style="color:#6b7280;font-size:14px;"></i>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm p-0 border-0 bg-transparent" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical" style="color:#9ca3af;font-size:14px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px;min-width:160px;">
                                <li><a class="dropdown-item" href="#" onclick="dbDaybookOpenRowAction(event,${r.id})">
                                    <i class="fa-solid fa-print me-2 text-secondary"></i>Print</a></li>
                                <li><a class="dropdown-item" href="#" onclick="dbDaybookOpenRowAction(event,${r.id})">
                                    <i class="fa-brands fa-whatsapp me-2 text-success"></i>WhatsApp</a></li>
                                <li><a class="dropdown-item" href="#" onclick="dbDaybookOpenRowAction(event,${r.id})">
                                    <i class="fa-regular fa-envelope me-2 text-primary"></i>Email</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#">
                                    <i class="fa-solid fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    /* ─── clear individual column filter ─── */
    window.dbDaybookClearColFilter = function (col) {
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
        dbDaybookApplyFilters();
    };

    /* ─── row action modal ─── */
    window.dbDaybookOpenRowAction = function (e, id) {
        e.preventDefault();
        e.stopPropagation();
        dbDaybookActiveRow = dbDaybookAllRows.find(r => r.id === id);
        if (!dbDaybookActiveRow) return;
        document.getElementById('dbRowActionTitle').textContent =
            dbDaybookActiveRow.type + ' — ' + dbDaybookActiveRow.name;
        new bootstrap.Modal(document.getElementById('dbRowActionModal')).show();
    };

    window.dbDaybookRowPrint = function () {
        if (!dbDaybookActiveRow) return;
        const w = window.open('', '_blank');
        w.document.write(`<html><body style="font-family:sans-serif;padding:32px;">
            <h3>Day Book Voucher</h3>
            <p><b>Name:</b> ${dbDaybookActiveRow.name}</p>
            <p><b>Type:</b> ${dbDaybookActiveRow.type}</p>
            <p><b>Ref:</b> ${dbDaybookActiveRow.ref || '—'}</p>
            <p><b>Payment Type:</b> ${dbDaybookActiveRow.pay_type || '—'}</p>
            <p><b>Total:</b> Rs ${dbDaybookActiveRow.total}</p>
            <p><b>Money In:</b> Rs ${dbDaybookActiveRow.money_in}</p>
            <p><b>Money Out:</b> Rs ${dbDaybookActiveRow.money_out}</p>
        </body></html>`);
        w.document.close();
        w.print();
    };

    window.dbDaybookRowShare = function (method) {
        if (!dbDaybookActiveRow) return;
        const text = `Day Book Entry\nName: ${dbDaybookActiveRow.name}\nType: ${dbDaybookActiveRow.type}\nTotal: Rs ${dbDaybookActiveRow.total}`;
        if (method === 'whatsapp') {
            window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
        } else {
            window.location.href = 'mailto:?subject=Day Book Entry&body=' + encodeURIComponent(text);
        }
    };

    window.dbRowDownloadPdf = function () {
        alert('PDF download: connect your server-side PDF route here.');
    };

    /* ─── excel export ─── */
    function dbDaybookExportExcel() {
        let csv = 'Name,Ref No,Type,Payment Type,Total,Money In,Money Out\n';
        dbDaybookFiltered.forEach(r => {
            csv += `"${r.name}","${r.ref}","${r.type}","${r.pay_type}",${r.total},${r.money_in},${r.money_out}\n`;
        });
        const blob = new Blob([csv], {type:'text/csv'});
        const url  = URL.createObjectURL(blob);
        const a    = Object.assign(document.createElement('a'), {href:url, download:'daybook.csv'});
        document.body.appendChild(a); a.click(); a.remove();
        URL.revokeObjectURL(url);
    }

    /* ─── print all ─── */
    function dbDaybookPrintAll() {
        const date = document.getElementById('db-date').value;
        let html = `<html><head><title>Day Book</title>
        <style>body{font-family:sans-serif;font-size:12px;padding:24px}
        table{width:100%;border-collapse:collapse}
        th{background:#f3f4f6;padding:8px;text-align:left;border:1px solid #e5e7eb;font-size:11px;}
        td{padding:8px;border:1px solid #e5e7eb;}
        h2{margin-bottom:4px} p{margin:0 0 16px;color:#666}
        tfoot td{font-weight:700;background:#f9fafb}</style></head>
        <body>
        <h2>Day Book Report</h2>
        <p>Date: ${date}</p>
        <table>
        <thead><tr><th>Name</th><th>Ref</th><th>Type</th><th>Payment Type</th><th style="text-align:right">Total</th><th style="text-align:right">Money In</th><th style="text-align:right">Money Out</th></tr></thead>
        <tbody>`;

        dbDaybookFiltered.forEach(r => {
            html += `<tr>
                <td>${r.name}</td><td>${r.ref||''}</td><td>${r.type}</td><td>${r.pay_type||'—'}</td>
                <td style="text-align:right">Rs ${r.total.toLocaleString()}</td>
                <td style="text-align:right">Rs ${r.money_in.toLocaleString()}</td>
                <td style="text-align:right">Rs ${r.money_out.toLocaleString()}</td>
            </tr>`;
        });

        const mi  = dbDaybookFiltered.reduce((s,r) => s+r.money_in,  0);
        const mo  = dbDaybookFiltered.reduce((s,r) => s+r.money_out, 0);
        const tot = dbDaybookFiltered.reduce((s,r) => s+r.total,     0);

        html += `</tbody>
        <tfoot><tr><td colspan="4">Totals</td>
        <td style="text-align:right">Rs ${tot.toLocaleString()}</td>
        <td style="text-align:right">Rs ${mi.toLocaleString()}</td>
        <td style="text-align:right">Rs ${mo.toLocaleString()}</td>
        </tr></tfoot>
        </table></body></html>`;

        const w = window.open('', '_blank');
        w.document.write(html);
        w.document.close();
        w.print();
    }

    /* ─── bind events ─── */
    function dbDaybookBindEvents() {
        // top-bar filters trigger fetch
        ['db-date','db-firm','db-type','db-payment-type'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', dbDaybookFetchData);
        });

        // search bar — live filter
        document.getElementById('db-search').addEventListener('input', dbDaybookApplyFilters);

        // total range toggle
        document.getElementById('db-total-cond').addEventListener('change', function () {
            document.getElementById('db-total-range-row').classList.toggle('d-none', this.value !== 'range');
        });

        // excel
        document.getElementById('db-excel-btn').addEventListener('click', dbDaybookExportExcel);

        // print
        document.getElementById('db-print-btn').addEventListener('click', dbDaybookPrintAll);

        // fullscreen
        document.getElementById('db-fullscreen-btn').addEventListener('click', function () {
            const tab = document.getElementById('tab-Daybook');
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
    // Works with your existing showTab() / nav-link click system
    document.addEventListener('DOMContentLoaded', function () {
        // If this tab is active by default
        if (!document.getElementById('tab-Daybook').classList.contains('d-none')) {
            dbDaybookInit();
        }

        // Listen for nav-link clicks that activate Daybook
        document.querySelectorAll('.reports-nav .nav-link[data-target="Daybook"]').forEach(link => {
            link.addEventListener('click', function () {
                // small delay to allow DOM to show tab
                setTimeout(dbDaybookInit, 50);
            });
        });
    });

})();
</script>