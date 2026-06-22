{{-- resources/views/dashboard/reports/tabs/purchase-report.blade.php --}}

<div id="tab-Purchase" class="report-tab-content d-none">

  <!-- ── Header ── -->
  <div class="pur-report-header d-flex justify-content-between align-items-center mb-3 px-1">
    <h3 class="pur-report-title mb-0">Purchase Bills</h3>
    <div class="d-flex gap-2 align-items-center">
      <button class="pur-add-btn" id="purAddPurchaseBtn" type="button">
        <i class="fa-solid fa-plus me-1"></i> Add Purchase
      </button>
      <a class="pur-icon-btn" id="purSettingsBtn" href="{{ route('settings.transactions') }}"
        title="Transaction Settings" aria-label="Open transaction settings">
        <i class="fa-solid fa-gear"></i>
      </a>
    </div>
  </div>

  <!-- ── Filter Bar ── -->
  <div class="pur-filter-bar d-flex align-items-center gap-3 mb-3" style="position:relative;">
    <span class="pur-filter-label">Filter by :</span>

    <!-- Period Selector pill (wraps select + date display) -->
    <div class="pur-period-pill d-flex align-items-center" style="position:relative;">
      <div class="pur-period-select-wrap">
        <select id="purPeriodSelect" class="pur-period-select">
          <option value="all" selected>All Purchase Bills</option>
          <option value="this_month">This Month</option>
          <option value="last_month">Last Month</option>
          <option value="this_quarter">This Quarter</option>
          <option value="this_year">This Year</option>
          <option value="custom">Custom</option>
        </select>
        <i class="fa-solid fa-chevron-down pur-period-arrow"></i>
      </div>
      <div class="pur-period-divider"></div>
      <div class="pur-date-range-display" id="purDateRangeDisplay" style="cursor:pointer;" title="Click to set custom date range">
        <span class="pur-between-label">Between</span>
        <span id="purDateFrom">01/01/2000</span>
        <span class="mx-1" style="color:#6b7280;">To</span>
        <span id="purDateTo">31/12/2099</span>
        <i class="fa-regular fa-calendar-days ms-2" style="color:#2563eb;font-size:14px;"></i>
      </div>

      <!-- Calendar popup anchored to the pill -->
      <div class="pur-calendar-popup d-none" id="purCalendarPopup">
        <div class="pur-calendar-popup-inner">
          <div class="d-flex align-items-center gap-2 mb-2">
            <label style="font-size:12px;color:#6b7280;white-space:nowrap;">From</label>
            <input type="date" id="purCalFrom" class="pur-date-input">
          </div>
          <div class="d-flex align-items-center gap-2 mb-3">
            <label style="font-size:12px;color:#6b7280;white-space:nowrap;">To &nbsp;&nbsp;&nbsp;</label>
            <input type="date" id="purCalTo" class="pur-date-input">
          </div>
          <div class="d-flex gap-2 justify-content-end">
            <button class="pur-col-clear-btn" id="purCalCancel">Cancel</button>
            <button class="pur-col-apply-btn" id="purCalApply">Apply</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Firms Filter -->
    <div class="pur-firm-select-wrap">
      <select id="purFirmSelect" class="pur-firm-select">
        <option value="all">All Firms</option>
      </select>
      <i class="fa-solid fa-chevron-down pur-period-arrow"></i>
    </div>

    <!-- Spacer + top action buttons -->
    <div class="ms-auto d-flex gap-2">
      <button class="pur-icon-btn" id="purExcelTopBtn" title="Export to Excel">
        <i class="fa-regular fa-file-excel text-success"></i>
      </button>
      <button class="pur-icon-btn" id="purPrintTopBtn" title="Print">
        <i class="fa-solid fa-print"></i>
      </button>
    </div>
  </div>

  <!-- ── Summary Cards ── -->
  <div class="pur-summary-row mb-4 d-flex align-items-center gap-3 flex-wrap">
    <div class="pur-stat-card pur-stat-paid">
      <div class="pur-stat-label">Paid</div>
      <div class="pur-stat-amount" id="purTotalPaid">Rs 0.00</div>
    </div>
    <div class="pur-stat-operator">+</div>
    <div class="pur-stat-card pur-stat-unpaid">
      <div class="pur-stat-label">Unpaid</div>
      <div class="pur-stat-amount" id="purTotalUnpaid">Rs 0.00</div>
    </div>
    <div class="pur-stat-operator">=</div>
    <div class="pur-stat-card pur-stat-total">
      <div class="pur-stat-label">Total</div>
      <div class="pur-stat-amount" id="purTotalAmount">Rs 0.00</div>
    </div>
  </div>

  <!-- ── Transactions Card ── -->
  <div class="pur-txn-card pur-txn-card-fullheight">

    <div class="pur-txn-card-header">
      <h5 class="pur-txn-title">Transactions</h5>
      <div class="pur-txn-actions">
        <button class="pur-txn-action-btn" id="purTxnSearchBtn" title="Search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <button class="pur-txn-action-btn" id="purTxnChartBtn" title="Chart">
          <i class="fa-solid fa-chart-simple"></i>
        </button>
        <button class="pur-txn-action-btn text-success" id="purTxnExcelBtn" title="Export to Excel">
          <i class="fa-regular fa-file-excel"></i>
        </button>
        <button class="pur-txn-action-btn" id="purTxnPrintBtn" title="Print">
          <i class="fa-solid fa-print"></i>
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="pur-search-bar d-none" id="purTxnSearchBar">
      <div class="pur-search-inner">
        <i class="fa-solid fa-search" style="color:#9ca3af;"></i>
        <input type="text" id="purTxnSearchInput" class="pur-search-input" placeholder="Search transactions...">
        <button id="purTxnSearchClose" class="pur-search-close"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <!-- Chart Panel -->
    <div class="pur-chart-panel d-none" id="purTxnChartPanel">
      <div class="pur-chart-header">
        <div class="pur-chart-periods">
          <button class="pur-chart-period active" data-period="daily">Daily</button>
          <button class="pur-chart-period" data-period="weekly">Weekly</button>
          <button class="pur-chart-period" data-period="monthly">Monthly</button>
          <button class="pur-chart-period" data-period="yearly">Yearly</button>
        </div>
      </div>
      <div class="pur-chart-title">Purchase Graph</div>
      <div class="pur-chart-container">
        <canvas id="purChart"></canvas>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive pur-table-scroll">
      <table class="pur-table" id="purTransactionsTable" data-column-drag="native"
        data-column-drag-storage="vyapar.reports.purchase.transactions.v1">
        <thead>
          <tr>
            <!-- Date -->
            <th data-column-key="date">
              <div class="pur-th-inner">
                <span>Date</span>
                <button class="pur-th-sort" onclick="purSortBy('date', this)"><i class="fa-solid fa-sort"></i></button>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-label">Condition:</label>
                      <select class="pur-col-filter-select" id="purFilterDateType">
                        <option value="equal">Equal to</option>
                        <option value="lt">Less than</option>
                        <option value="gt">Greater than</option>
                        <option value="range">Range</option>
                      </select>
                      <label class="pur-col-filter-label mt-2">Date:</label>
                      <input type="date" class="pur-col-filter-input" id="purFilterDateVal">
                      <div id="purFilterDateRange" class="d-none mt-1">
                        <input type="date" class="pur-col-filter-input" id="purFilterDateRangeFrom">
                        <input type="date" class="pur-col-filter-input mt-1" id="purFilterDateRangeTo">
                      </div>
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('date', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('date', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Invoice No -->
            <th data-column-key="invoice_no">
              <div class="pur-th-inner">
                <span>Invoice No.</span>
                <button class="pur-th-sort" onclick="purSortBy('invoice_no', this)"><i class="fa-solid fa-sort"></i></button>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-label">Match type:</label>
                      <select class="pur-col-filter-select" id="purFilterInvoiceType">
                        <option value="contains">Contains</option>
                        <option value="exact">Exact Match</option>
                      </select>
                      <label class="pur-col-filter-label mt-2">Invoice No.</label>
                      <input type="text" class="pur-col-filter-input" id="purFilterInvoiceVal" placeholder="e.g. PUR-001">
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('invoice_no', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('invoice_no', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Party Name -->
            <th data-column-key="party_name">
              <div class="pur-th-inner">
                <span>Party Name</span>
                <button class="pur-th-sort" onclick="purSortBy('party_name', this)"><i class="fa-solid fa-sort"></i></button>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-label">Match type:</label>
                      <select class="pur-col-filter-select" id="purFilterPartyType">
                        <option value="contains">Contains</option>
                        <option value="exact">Exact Match</option>
                      </select>
                      <label class="pur-col-filter-label mt-2">Party Name</label>
                      <input type="text" class="pur-col-filter-input" id="purFilterPartyVal" placeholder="Party name...">
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('party_name', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('party_name', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Transaction -->
            <th data-column-key="transaction">
              <div class="pur-th-inner">
                <span>Transaction</span>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown" style="min-width:200px;">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Purchase" class="pur-txn-type-check"> Purchase</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Debit Note" class="pur-txn-type-check"> Debit Note</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Purchase (Invoice)" class="pur-txn-type-check"> Purchase (Invoice)</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Purchase [Cancelled]" class="pur-txn-type-check"> Purchase [Cancelled]</label>
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('transaction', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('transaction', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Payment Type -->
            <th data-column-key="payment_type">
              <div class="pur-th-inner">
                <span>Payment Type</span>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Cash" class="pur-pay-type-check"> Cash</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Cheque" class="pur-pay-type-check"> Cheque</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Online" class="pur-pay-type-check"> Online</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Card" class="pur-pay-type-check"> Card</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="HBL" class="pur-pay-type-check"> HBL</label>
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('payment_type', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('payment_type', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Amount -->
            <th data-column-key="amount">
              <div class="pur-th-inner">
                <span>Amount</span>
                <button class="pur-th-sort" onclick="purSortBy('amount', this)"><i class="fa-solid fa-sort"></i></button>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown" style="min-width:220px;">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-label">Amount Range</label>
                      <div class="d-flex gap-2 mt-2">
                        <div style="flex:1">
                          <label class="pur-col-filter-label">Min</label>
                          <input type="number" class="pur-col-filter-input" id="purFilterAmountMin" placeholder="0">
                        </div>
                        <div style="flex:1">
                          <label class="pur-col-filter-label">Max</label>
                          <input type="number" class="pur-col-filter-input" id="purFilterAmountMax" placeholder="+500000">
                        </div>
                      </div>
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('amount', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('amount', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Balance Due -->
            <th data-column-key="balance">
              <div class="pur-th-inner">
                <span>Balance Due</span>
                <button class="pur-th-sort" onclick="purSortBy('balance', this)"><i class="fa-solid fa-sort"></i></button>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown" style="min-width:220px;">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-label">Balance Range</label>
                      <div class="d-flex gap-2 mt-2">
                        <div style="flex:1">
                          <label class="pur-col-filter-label">Min</label>
                          <input type="number" class="pur-col-filter-input" id="purFilterBalanceMin" placeholder="0">
                        </div>
                        <div style="flex:1">
                          <label class="pur-col-filter-label">Max</label>
                          <input type="number" class="pur-col-filter-input" id="purFilterBalanceMax" placeholder="+500000">
                        </div>
                      </div>
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('balance', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('balance', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Status -->
            <th data-column-key="status">
              <div class="pur-th-inner">
                <span>Status</span>
                <div class="pur-th-filter-wrap">
                  <button class="pur-th-filter-btn" onclick="purToggleColFilter(this)"><i class="fa-solid fa-filter"></i></button>
                  <div class="pur-col-filter-dropdown">
                    <div class="pur-col-filter-body">
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Paid" class="pur-status-check"> Paid</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Partial" class="pur-status-check"> Partial</label>
                      <label class="pur-col-filter-checkbox"><input type="checkbox" value="Unpaid" class="pur-status-check"> Unpaid</label>
                    </div>
                    <div class="pur-col-filter-actions">
                      <button class="pur-col-clear-btn" onclick="purClearColFilter('status', this)">Clear</button>
                      <button class="pur-col-apply-btn" onclick="purApplyColFilter('status', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Actions -->
            <th data-column-key="actions"><div class="pur-th-inner"><span>Actions</span></div></th>
          </tr>
        </thead>
        <tbody id="purTxnTableBody">
          <tr id="purNoDataRow">
            <td colspan="9" class="pur-empty-state">
              <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;color:#d1d5db;display:block;margin-bottom:6px;"></i>
              Loading transactions…
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pur-pagination" id="purPagination" style="display:none;">
      <span class="pur-pagination-info" id="purPaginationInfo"></span>
      <div class="pur-pagination-btns">
        <button class="pur-page-btn" id="purPrevPage"><i class="fa-solid fa-chevron-left"></i></button>
        <span id="purPageNumbers"></span>
        <button class="pur-page-btn" id="purNextPage"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

  </div><!-- /pur-txn-card -->
</div><!-- /tab-Purchase -->


<!-- Excel Export Modal -->
<div class="pur-modal-overlay" id="purExcelModal" style="display:none;">
  <div class="pur-modal-box">
    <div class="pur-modal-header">
      <h5 class="pur-modal-title">Select Report Options</h5>
      <button class="pur-modal-close" id="purExcelModalClose"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="pur-modal-body">
      <div class="pur-modal-columns">
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="date" checked> Date</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="invoice_no" checked> Invoice No.</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="party_name" checked> Party Name</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="total" checked> Total</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="payment_type" checked> Payment Type</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="received_paid" checked> Paid Amount</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="balance_due" checked> Balance Due</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="status" checked> Status</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="party_phone"> Party's Phone No.</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-export-col" value="description"> Description</label>
      </div>
    </div>
    <div class="pur-modal-footer">
      <button class="pur-modal-generate-btn" id="purExcelGenerateBtn">Generate Report</button>
    </div>
  </div>
</div>

<!-- Print Options Modal -->
<div class="pur-modal-overlay" id="purPrintModal" style="display:none;">
  <div class="pur-modal-box">
    <div class="pur-modal-header">
      <h5 class="pur-modal-title">Select Print Options</h5>
      <button class="pur-modal-close" id="purPrintModalClose"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="pur-modal-body">
      <div class="pur-modal-columns">
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="date" checked> Date</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="invoice_no" checked> Invoice No.</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="party_name" checked> Party Name</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="total" checked> Total</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="payment_type" checked> Payment Type</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="received_paid" checked> Paid Amount</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="balance_due" checked> Balance Due</label>
        <label class="pur-modal-col-item"><input type="checkbox" class="pur-print-col" value="status" checked> Status</label>
      </div>
    </div>
    <div class="pur-modal-footer">
      <button class="pur-modal-generate-btn" id="purPrintGenerateBtn">Get Print</button>
    </div>
  </div>
</div>


<style>
/* ── Base ── */
.pur-report-title { font-size:22px; font-weight:700; color:#111827; }

/* ── Buttons ── */
.pur-add-btn {
  background:#ef4444; color:#fff; border:none; border-radius:999px;
  padding:8px 20px; font-size:14px; font-weight:600; cursor:pointer; transition:background .15s;
}
.pur-add-btn:hover { background:#dc2626; }
.pur-icon-btn {
  width:36px; height:36px; border-radius:8px; border:1px solid #e5e7eb; background:#fff;
  color:#6b7280; font-size:15px; cursor:pointer; display:flex; align-items:center; justify-content:center;
  text-decoration:none; transition:background .15s;
}
.pur-icon-btn:hover { background:#f3f4f6; }

/* ── Filter Bar ── */
.pur-filter-bar { flex-wrap:wrap; }
.pur-filter-label { font-size:14px; color:#6b7280; white-space:nowrap; }
.pur-period-pill { background:#E4F2FF; border-radius:999px; height:40px; position:relative; }
.pur-period-select-wrap {
  display:flex; align-items:center; padding:0 14px;
  border-right:1px solid rgba(0,0,0,.15); position:relative;
}
.pur-period-select { background:transparent; border:none; outline:none; font-size:13px; font-weight:500; color:#374151; cursor:pointer; padding-right:18px; appearance:none; }
.pur-period-arrow { position:absolute; right:14px; font-size:10px; color:#6b7280; pointer-events:none; }
.pur-date-range-display {
  padding:0 16px; font-size:13px; color:#374151; white-space:nowrap;
  display:flex; align-items:center; gap:4px; border-radius:0 999px 999px 0; transition:background .15s;
}
.pur-date-range-display:hover { background:rgba(37,99,235,.07); }
.pur-between-label { background:#aaa; color:#fff; font-weight:600; padding:4px 10px; border-radius:4px; font-size:12px; }

/* ── Calendar Popup ── */
.pur-calendar-popup {
  position:absolute; top:calc(100% + 8px); left:0; z-index:500;
  background:#fff; border:1px solid #e5e7eb; border-radius:14px;
  box-shadow:0 16px 40px rgba(0,0,0,.14); min-width:260px;
}
.pur-calendar-popup-inner { padding:16px 18px; }

.pur-firm-select-wrap {
  position:relative; display:flex; align-items:center;
  background:#E4F2FF; border-radius:999px; padding:0 14px; height:40px;
}
.pur-firm-select { background:transparent; border:none; outline:none; font-size:13px; color:#374151; cursor:pointer; appearance:none; padding-right:18px; }
.pur-date-input { border:1px solid #e5e7eb; border-radius:8px; padding:6px 10px; font-size:13px; outline:none; color:#374151; }
.pur-date-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.1); }

/* ── Summary Cards ── */
.pur-stat-card { border-radius:12px; padding:16px 24px; min-width:160px; }
.pur-stat-paid   { background:#B9F3E7; }
.pur-stat-unpaid { background:#CFE6FE; }
.pur-stat-total  { background:#F8C889; }
.pur-stat-label  { font-size:14px; font-weight:600; color:#374151; margin-bottom:4px; }
.pur-stat-amount { font-size:22px; font-weight:700; color:#111827; }
.pur-stat-operator { font-size:28px; font-weight:300; color:#9ca3af; line-height:1; }

/* ── Transactions Card ── */
.pur-txn-card {
  background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;
  box-shadow:0 2px 10px rgba(0,0,0,.05);
}
.pur-txn-card-fullheight { display:flex; flex-direction:column; min-height:calc(100vh - 340px); }
.pur-table-scroll { flex:1; overflow-y:auto; }
.pur-txn-card-header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #f3f4f6; flex-shrink:0; }
.pur-txn-title { font-size:16px; font-weight:700; color:#111827; margin:0; }
.pur-txn-actions { display:flex; gap:4px; }
.pur-txn-action-btn { width:34px; height:34px; border:none; background:transparent; color:#6b7280; font-size:15px; cursor:pointer; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:background .15s; }
.pur-txn-action-btn:hover { background:#f3f4f6; color:#374151; }
.pur-txn-action-btn.text-success { color:#10b981 !important; }

/* Search */
.pur-search-bar { padding:12px 20px; border-bottom:1px solid #f3f4f6; flex-shrink:0; }
.pur-search-inner { display:flex; align-items:center; gap:8px; border:1px solid #e5e7eb; border-radius:10px; padding:8px 12px; max-width:360px; }
.pur-search-input { flex:1; border:none; outline:none; font-size:14px; color:#374151; }
.pur-search-close { border:none; background:transparent; color:#9ca3af; cursor:pointer; }

/* Chart */
.pur-chart-panel { padding:16px 20px; border-bottom:1px solid #f3f4f6; flex-shrink:0; }
.pur-chart-header { display:flex; justify-content:flex-end; margin-bottom:12px; }
.pur-chart-periods { display:flex; gap:4px; }
.pur-chart-period { border:none; background:transparent; font-size:13px; font-weight:600; color:#6b7280; padding:6px 12px; cursor:pointer; border-bottom:2px solid transparent; transition:color .15s, border-color .15s; }
.pur-chart-period.active { color:#2563eb; border-color:#2563eb; }
.pur-chart-title { font-size:15px; font-weight:700; color:#111827; margin-bottom:8px; }
.pur-chart-container { height:220px; }

/* Table */
.pur-table { width:100%; border-collapse:collapse; font-size:13px; }
.pur-table thead tr { background:#fff; }
.pur-table th { padding:0; border-bottom:1px solid #f3f4f6; white-space:nowrap; position:sticky; top:0; z-index:10; background:#fff; }
.pur-th-inner { display:flex; align-items:center; gap:4px; padding:12px 14px; color:#6b7280; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; }
.pur-th-sort { border:none; background:transparent; color:#c4c9d4; font-size:11px; cursor:pointer; padding:0; transition:color .15s; }
.pur-th-sort:hover, .pur-th-sort.active { color:#2563eb; }

/* Column filter */
.pur-th-filter-wrap { position:relative; display:inline-flex; }
.pur-th-filter-btn { border:none; background:transparent; color:#c4c9d4; font-size:11px; cursor:pointer; padding:2px 4px; border-radius:4px; transition:color .15s, background .15s; }
.pur-th-filter-btn:hover, .pur-th-filter-btn.active { color:#2563eb; background:#eff6ff; }
.pur-col-filter-dropdown { display:none; position:absolute; top:calc(100% + 6px); left:0; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,.12); z-index:200; min-width:190px; overflow:hidden; }
.pur-col-filter-dropdown.open { display:block; }
.pur-col-filter-body { padding:12px 14px; }
.pur-col-filter-label { display:block; font-size:11px; color:#9ca3af; margin-bottom:4px; }
.pur-col-filter-select, .pur-col-filter-input { width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:6px 10px; font-size:13px; outline:none; color:#374151; background:#fff; }
.pur-col-filter-checkbox { display:flex; align-items:center; gap:8px; font-size:13px; color:#374151; padding:5px 0; cursor:pointer; }
.pur-col-filter-actions { display:flex; justify-content:flex-end; gap:8px; padding:10px 14px; border-top:1px solid #f3f4f6; background:#fafafa; }
.pur-col-clear-btn { background:#EBEAEA; color:#71748E; border:none; border-radius:999px; padding:6px 14px; font-size:12px; font-weight:600; cursor:pointer; }
.pur-col-apply-btn { background:#ef4444; color:#fff; border:none; border-radius:999px; padding:6px 14px; font-size:12px; font-weight:600; cursor:pointer; }

/* Table body */
.pur-table tbody tr { border-bottom:1px solid #f9fafb; }
.pur-table tbody tr:hover { background:#fafafa; }
.pur-table td { padding:13px 14px; color:#374151; font-size:13px; vertical-align:middle; }
.pur-empty-state { text-align:center; padding:48px; color:#9ca3af; font-size:14px; }

/* Row buttons */
.pur-row-print-btn, .pur-row-share-btn { border:none; background:transparent; color:#9ca3af; font-size:14px; cursor:pointer; padding:4px 6px; border-radius:6px; transition:color .15s, background .15s; }
.pur-row-print-btn:hover { color:#374151; background:#f3f4f6; }
.pur-row-share-btn:hover { color:#2563eb; background:#eff6ff; }
.pur-row-more-btn { border:none; background:transparent; color:#9ca3af; font-size:14px; cursor:pointer; padding:4px 6px; border-radius:6px; }
.pur-row-more-btn:hover { color:#374151; background:#f3f4f6; }

/* Badges */
.pur-badge-paid    { background:#d1fae5; color:#047857; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; }
.pur-badge-unpaid  { background:#fef3c7; color:#d97706; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; }
.pur-badge-partial { background:#dbeafe; color:#2563eb; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; }

/* Pagination */
.pur-pagination { display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-top:1px solid #f3f4f6; font-size:13px; color:#6b7280; flex-shrink:0; }
.pur-pagination-btns { display:flex; align-items:center; gap:4px; }
.pur-page-btn { width:30px; height:30px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; color:#6b7280; cursor:pointer; font-size:11px; display:flex; align-items:center; justify-content:center; }
.pur-page-btn:hover { background:#f3f4f6; }

/* Modals */
.pur-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1100; display:flex; align-items:center; justify-content:center; }
.pur-modal-box { background:#fff; border-radius:16px; width:min(520px,calc(100vw - 32px)); box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; }
.pur-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px; }
.pur-modal-title { font-size:18px; font-weight:700; color:#111827; margin:0; }
.pur-modal-close { border:none; background:transparent; color:#9ca3af; font-size:18px; cursor:pointer; }
.pur-modal-body { padding:0 24px 16px; }
.pur-modal-columns { display:grid; grid-template-columns:1fr 1fr; gap:6px 24px; }
.pur-modal-col-item { display:flex; align-items:center; gap:10px; font-size:14px; color:#374151; padding:6px 0; cursor:pointer; }
.pur-modal-col-item input[type="checkbox"] { width:18px; height:18px; accent-color:#2563eb; cursor:pointer; }
.pur-modal-footer { padding:16px 24px 20px; text-align:center; }
.pur-modal-generate-btn { background:#ef4444; color:#fff; border:none; border-radius:999px; padding:12px 36px; font-size:15px; font-weight:600; cursor:pointer; transition:background .15s; }
.pur-modal-generate-btn:hover { background:#dc2626; }
</style>


@once
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
@endonce
<script>
(function () {
  'use strict';

  /* ── State ── */
  let purAllRows    = [];
  let purFiltered   = [];
  let purSortState  = { key: null, dir: 1 };
  let purColFilters = {};
  let purCurrentPage = 1;
  let purActiveFrom = null;
  let purActiveTo = null;
  const PUR_PAGE_SIZE = 25;

  /* ── Helpers ── */
  const fmt    = n => 'Rs ' + parseFloat(n || 0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});
  const toISO  = d => d instanceof Date ? d.toISOString().split('T')[0] : d;
  const fmtDate = d => { if (!d) return '-'; return new Date(d).toLocaleDateString('en-GB'); };

  function getStatus(row) {
    const bal = parseFloat(row.balance_due || 0);
    const paid = parseFloat(row.paid_amount || row.received_paid || 0);
    if (bal <= 0) return 'Paid';
    if (paid > 0) return 'Partial';
    return 'Unpaid';
  }

  /* ── Date Range presets ── */
  function getDateRange(period) {
    const now = new Date(), y = now.getFullYear(), m = now.getMonth();
    switch (period) {
      case 'this_month':   return [new Date(y, m, 1),     new Date(y, m + 1, 0)];
      case 'last_month':   return [new Date(y, m - 1, 1), new Date(y, m, 0)];
      case 'this_quarter': { const q = Math.floor(m / 3); return [new Date(y, q*3, 1), new Date(y, q*3+3, 0)]; }
      case 'this_year':    return [new Date(y, 0, 1), new Date(y, 11, 31)];
      default:             return [new Date(2000, 0, 1), new Date(2099, 11, 31)];
    }
  }

  function updateDateDisplay(from, to) {
    const fStr = typeof from === 'string' ? from : toISO(from);
    const tStr = typeof to   === 'string' ? to   : toISO(to);
    document.getElementById('purDateFrom').textContent = new Date(fStr).toLocaleDateString('en-GB');
    document.getElementById('purDateTo').textContent   = new Date(tStr).toLocaleDateString('en-GB');
    return [fStr, tStr];
  }

  /* ── Fetch Data ── */
  function purLoadData(fromDate, toDate) {
    purActiveFrom = fromDate;
    purActiveTo = toDate;

    const tbody = document.getElementById('purTxnTableBody');
    tbody.innerHTML = `<tr><td colspan="9" class="pur-empty-state">
      <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;color:#d1d5db;display:block;margin-bottom:6px;"></i>
      Loading transactions…
    </td></tr>`;
    document.getElementById('purPagination').style.display = 'none';

    const params = new URLSearchParams({ from: fromDate, to: toDate });
    const partyId = document.getElementById('purFirmSelect')?.value;
    if (partyId && partyId !== 'all') params.set('party', partyId);

    fetch(`{{ route('reports.purchase') }}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        purSyncFilterOptions(data);
        purAllRows  = data.transactions || [];
        purFiltered = [...purAllRows];
        updatePurSummary(data);
        purColFilters = {};   // reset column filters when new data loads
        purApplyAllFilters();
      } else {
        tbody.innerHTML = `<tr><td colspan="9" class="pur-empty-state">No data found.</td></tr>`;
      }
    })
    .catch(err => {
      console.error('Purchase fetch error:', err);
      tbody.innerHTML = `<tr><td colspan="9" class="pur-empty-state text-danger">Error loading data. Please try again.</td></tr>`;
    });
  }

  function updatePurSummary(data) {
    // Calculate paid/unpaid from the transactions array if server doesn't provide
    const rows = data.transactions || [];
    const totalAmount = parseFloat(data.total_amount || rows.reduce((s,r)=>s+parseFloat(r.grand_total||r.total_amount||0),0));
    const totalPaid   = parseFloat(data.total_paid   || rows.reduce((s,r)=>s+parseFloat(r.paid_amount||r.received_paid||0),0));
    const totalBalance= parseFloat(data.total_balance|| data.total_unpaid || rows.reduce((s,r)=>s+parseFloat(r.balance_due||0),0));

    document.getElementById('purTotalPaid').textContent   = fmt(totalPaid);
    document.getElementById('purTotalUnpaid').textContent = fmt(totalBalance);
    document.getElementById('purTotalAmount').textContent = fmt(totalAmount);
  }

  /* ── Render Table ── */
  function purPopulateFilter(selectId, items, defaultLabel) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const selectedValue = select.value || 'all';
    select.replaceChildren(new Option(defaultLabel, 'all'));

    (items || []).forEach(item => {
      if (item?.id == null || !item?.name) return;
      select.add(new Option(item.name, String(item.id)));
    });

    const selectedStillExists = Array.from(select.options).some(option => option.value === selectedValue);
    select.value = selectedStillExists ? selectedValue : 'all';
  }

  function purSyncFilterOptions(data) {
    purPopulateFilter('purFirmSelect', data.firms, 'All Firms');
  }

  function purReloadWithFilters() {
    if (purActiveFrom && purActiveTo) purLoadData(purActiveFrom, purActiveTo);
  }

  function purRenderTable() {
    const tbody = document.getElementById('purTxnTableBody');
    const total = purFiltered.length;
    const start = (purCurrentPage - 1) * PUR_PAGE_SIZE;
    const pageRows = purFiltered.slice(start, start + PUR_PAGE_SIZE);

    if (!total) {
      tbody.innerHTML = `<tr><td colspan="9" class="pur-empty-state">
        <i class="fa-solid fa-receipt" style="font-size:36px;color:#d1d5db;display:block;margin-bottom:8px;"></i>
        No purchase bills found for the selected filters.
      </td></tr>`;
      document.getElementById('purPagination').style.display = 'none';
      return;
    }

    tbody.innerHTML = '';
    pageRows.forEach(row => {
      const tr  = document.createElement('tr');
      const status = getStatus(row);
      const badge = status === 'Paid'
        ? `<span class="pur-badge-paid">Paid</span>`
        : status === 'Partial'
          ? `<span class="pur-badge-partial">Partial</span>`
          : `<span class="pur-badge-unpaid">Unpaid</span>`;

      tr.innerHTML = `
        <td data-column-key="date">${fmtDate(row.bill_date || row.invoice_date || row.date)}</td>
        <td data-column-key="invoice_no">${row.bill_number || row.invoice_no || '-'}</td>
        <td data-column-key="party_name">${row.party_name || '-'}</td>
        <td data-column-key="transaction">Purchase</td>
        <td data-column-key="payment_type">${row.payment_type || 'Cash'}</td>
        <td data-column-key="amount" style="text-align:right;font-weight:500;">${fmt(row.grand_total || row.total_amount || row.amount || 0)}</td>
        <td data-column-key="balance" style="text-align:right;">${fmt(row.balance_due || 0)}</td>
        <td data-column-key="status">${badge}</td>
        <td data-column-key="actions">
          <button class="pur-row-print-btn" title="Print" onclick="purPrintRow(${row.id})">
            <i class="fa-solid fa-print"></i>
          </button>
          <button class="pur-row-share-btn" title="Share">
            <i class="fa-solid fa-arrow-up-from-bracket"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    /* Pagination */
    const totalPages = Math.ceil(total / PUR_PAGE_SIZE);
    const pag = document.getElementById('purPagination');
    if (totalPages > 1) {
      pag.style.display = 'flex';
      document.getElementById('purPaginationInfo').textContent =
        `Showing ${start + 1}–${Math.min(start + PUR_PAGE_SIZE, total)} of ${total}`;
      let html = '';
      for (let i = 1; i <= totalPages; i++) {
        html += `<button class="pur-page-btn${i === purCurrentPage ? ' active' : ''}"
          style="${i === purCurrentPage ? 'background:#eff6ff;color:#2563eb;border-color:#bfdbfe;font-weight:700;' : ''}"
          onclick="purGoToPage(${i})">${i}</button>`;
      }
      document.getElementById('purPageNumbers').innerHTML = html;
    } else {
      pag.style.display = 'none';
    }
  }

  window.purGoToPage = function(page) { purCurrentPage = page; purRenderTable(); };

  /* ── Apply ALL Filters (search + column filters + sort) ── */
  function purApplyAllFilters() {
    const keyword = (document.getElementById('purTxnSearchInput')?.value || '').toLowerCase();

    purFiltered = purAllRows.filter(row => {
      // ─ Search ─
      if (keyword) {
        const hay = [
          row.bill_date, row.bill_number, row.party_name,
          row.payment_type, row.grand_total, row.total_amount, row.balance_due
        ].join(' ').toLowerCase();
        if (!hay.includes(keyword)) return false;
      }

      // ─ Date filter ─
      if (purColFilters.date) {
        const rowDate = new Date(row.bill_date || row.invoice_date || row.date);
        const { type, val, from, to } = purColFilters.date;
        if (type === 'equal' && val) {
          const fd = new Date(val); fd.setHours(0,0,0,0); rowDate.setHours(0,0,0,0);
          if (rowDate.getTime() !== fd.getTime()) return false;
        }
        if (type === 'lt' && val && rowDate >= new Date(val)) return false;
        if (type === 'gt' && val && rowDate <= new Date(val)) return false;
        if (type === 'range') {
          if (from && rowDate < new Date(from)) return false;
          if (to   && rowDate > new Date(to))   return false;
        }
      }

      // ─ Invoice No filter ─
      if (purColFilters.invoice_no) {
        const inv = (row.bill_number || row.invoice_no || '').toLowerCase();
        const { type, val } = purColFilters.invoice_no;
        if (type === 'exact'    && inv !== val.toLowerCase()) return false;
        if (type === 'contains' && !inv.includes(val.toLowerCase())) return false;
      }

      // ─ Party Name filter ─
      if (purColFilters.party_name) {
        const name = (row.party_name || '').toLowerCase();
        const { type, val } = purColFilters.party_name;
        if (type === 'exact'    && name !== val.toLowerCase()) return false;
        if (type === 'contains' && !name.includes(val.toLowerCase())) return false;
      }

      // ─ Transaction type checkboxes ─
      if (purColFilters.transaction && purColFilters.transaction.length) {
        // In this data everything is 'Purchase'; extend if you have more types
        const rowType = row.type || 'Purchase';
        if (!purColFilters.transaction.includes(rowType)) return false;
      }

      // ─ Payment type checkboxes ─
      if (purColFilters.payment_type && purColFilters.payment_type.length) {
        if (!purColFilters.payment_type.includes(row.payment_type || '')) return false;
      }

      // ─ Amount range ─
      if (purColFilters.amount) {
        const amt = parseFloat(row.grand_total || row.total_amount || row.amount || 0);
        const { min, max } = purColFilters.amount;
        if (min != null && amt < min) return false;
        if (max != null && amt > max) return false;
      }

      // ─ Balance range ─
      if (purColFilters.balance) {
        const bal = parseFloat(row.balance_due || 0);
        const { min, max } = purColFilters.balance;
        if (min != null && bal < min) return false;
        if (max != null && bal > max) return false;
      }

      // ─ Status checkboxes ─
      if (purColFilters.status && purColFilters.status.length) {
        if (!purColFilters.status.includes(getStatus(row))) return false;
      }

      return true;
    });

    /* Sort */
    if (purSortState.key) {
      purFiltered.sort((a, b) => {
        const keyMap = {
          date:       r => new Date(r.bill_date || r.invoice_date || r.date),
          invoice_no: r => (r.bill_number || r.invoice_no || '').toLowerCase(),
          party_name: r => (r.party_name || '').toLowerCase(),
          amount:     r => parseFloat(r.grand_total || r.total_amount || 0),
          balance:    r => parseFloat(r.balance_due  || 0),
        };
        const fn = keyMap[purSortState.key] || (r => r[purSortState.key] || '');
        const av = fn(a), bv = fn(b);
        return av < bv ? -purSortState.dir : av > bv ? purSortState.dir : 0;
      });
    }

    purCurrentPage = 1;
    purRenderTable();
  }

  /* ── Sort ── */
  window.purSortBy = function(key, btn) {
    purSortState.dir = purSortState.key === key ? purSortState.dir * -1 : 1;
    purSortState.key = key;
    document.querySelectorAll('.pur-th-sort').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    purApplyAllFilters();
  };

  /* ── Column Filter open/close ── */
  window.purToggleColFilter = function(btn) {
    const dropdown = btn.nextElementSibling;
    const isOpen = dropdown.classList.contains('open');
    document.querySelectorAll('.pur-col-filter-dropdown.open').forEach(d => {
      d.classList.remove('open');
      d.previousElementSibling?.classList.remove('active');
    });
    if (!isOpen) { dropdown.classList.add('open'); btn.classList.add('active'); }
  };

  window.purClearColFilter = function(key, btn) {
    delete purColFilters[key];
    const dd = btn.closest('.pur-col-filter-dropdown');
    dd.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]').forEach(i => i.value = '');
    dd.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    purApplyAllFilters();
    dd.classList.remove('open');
    dd.previousElementSibling?.classList.remove('active');
  };

  window.purApplyColFilter = function(key, btn) {
    const dd = btn.closest('.pur-col-filter-dropdown');

    if (key === 'date') {
      const type = document.getElementById('purFilterDateType')?.value || 'equal';
      const val  = document.getElementById('purFilterDateVal')?.value;
      const from = document.getElementById('purFilterDateRangeFrom')?.value;
      const to   = document.getElementById('purFilterDateRangeTo')?.value;
      if (val || from || to) purColFilters[key] = { type, val, from, to };
      else delete purColFilters[key];

    } else if (key === 'invoice_no') {
      const type = document.getElementById('purFilterInvoiceType')?.value;
      const val  = document.getElementById('purFilterInvoiceVal')?.value.trim();
      if (val) purColFilters[key] = { type, val };
      else delete purColFilters[key];

    } else if (key === 'party_name') {
      const type = document.getElementById('purFilterPartyType')?.value;
      const val  = document.getElementById('purFilterPartyVal')?.value.trim();
      if (val) purColFilters[key] = { type, val };
      else delete purColFilters[key];

    } else if (key === 'transaction') {
      const checked = [...dd.querySelectorAll('.pur-txn-type-check:checked')].map(c => c.value);
      if (checked.length) purColFilters[key] = checked;
      else delete purColFilters[key];

    } else if (key === 'payment_type') {
      const checked = [...dd.querySelectorAll('.pur-pay-type-check:checked')].map(c => c.value);
      if (checked.length) purColFilters[key] = checked;
      else delete purColFilters[key];

    } else if (key === 'amount') {
      const min = parseFloat(document.getElementById('purFilterAmountMin')?.value);
      const max = parseFloat(document.getElementById('purFilterAmountMax')?.value);
      if (!isNaN(min) || !isNaN(max)) purColFilters[key] = { min: isNaN(min) ? null : min, max: isNaN(max) ? null : max };
      else delete purColFilters[key];

    } else if (key === 'balance') {
      const min = parseFloat(document.getElementById('purFilterBalanceMin')?.value);
      const max = parseFloat(document.getElementById('purFilterBalanceMax')?.value);
      if (!isNaN(min) || !isNaN(max)) purColFilters[key] = { min: isNaN(min) ? null : min, max: isNaN(max) ? null : max };
      else delete purColFilters[key];

    } else if (key === 'status') {
      const checked = [...dd.querySelectorAll('.pur-status-check:checked')].map(c => c.value);
      if (checked.length) purColFilters[key] = checked;
      else delete purColFilters[key];
    }

    purApplyAllFilters();
    dd.classList.remove('open');
    dd.previousElementSibling?.classList.remove('active');
  };

  /* Show/hide range inputs inside date filter */
  document.getElementById('purFilterDateType')?.addEventListener('change', function () {
    document.getElementById('purFilterDateRange').classList.toggle('d-none', this.value !== 'range');
    document.getElementById('purFilterDateVal').closest('div')?.previousElementSibling?.classList.toggle('d-none', this.value === 'range');
  });

  /* ── Period Selector ── */
  document.getElementById('purPeriodSelect')?.addEventListener('change', function () {
    if (this.value === 'custom') {
      document.getElementById('purCalendarPopup').classList.remove('d-none');
      return;
    }
    document.getElementById('purCalendarPopup').classList.add('d-none');
    const [from, to] = getDateRange(this.value);
    const [fStr, tStr] = updateDateDisplay(from, to);
    purLoadData(fStr, tStr);
  });

  /* ── Calendar icon click (date range display) ── */
  document.getElementById('purDateRangeDisplay')?.addEventListener('click', function () {
    const popup = document.getElementById('purCalendarPopup');
    popup.classList.toggle('d-none');
    if (!popup.classList.contains('d-none')) {
      // Pre-fill from the displayed text (dd/mm/yyyy → yyyy-mm-dd)
      function toISOfromDisplay(str) {
        const p = str.split('/'); return p.length === 3 ? `${p[2]}-${p[1]}-${p[0]}` : '';
      }
      document.getElementById('purCalFrom').value = toISOfromDisplay(document.getElementById('purDateFrom').textContent);
      document.getElementById('purCalTo').value   = toISOfromDisplay(document.getElementById('purDateTo').textContent);
    }
  });

  document.getElementById('purCalApply')?.addEventListener('click', function () {
    const from = document.getElementById('purCalFrom').value;
    const to   = document.getElementById('purCalTo').value;
    if (!from || !to) { alert('Please select both From and To dates.'); return; }
    updateDateDisplay(from, to);
    document.getElementById('purCalendarPopup').classList.add('d-none');
    document.getElementById('purPeriodSelect').value = 'custom';
    purLoadData(from, to);
  });

  document.getElementById('purCalCancel')?.addEventListener('click', function () {
    document.getElementById('purCalendarPopup').classList.add('d-none');
  });

  /* ── Search ── */
  document.getElementById('purTxnSearchBtn')?.addEventListener('click', function () {
    const bar    = document.getElementById('purTxnSearchBar');
    const hidden = bar.classList.contains('d-none');
    bar.classList.toggle('d-none', !hidden);
    if (hidden) document.getElementById('purTxnSearchInput')?.focus();
    else { document.getElementById('purTxnSearchInput').value = ''; purApplyAllFilters(); }
  });
  document.getElementById('purTxnSearchClose')?.addEventListener('click', function () {
    document.getElementById('purTxnSearchBar').classList.add('d-none');
    document.getElementById('purTxnSearchInput').value = '';
    purApplyAllFilters();
  });
  document.getElementById('purTxnSearchInput')?.addEventListener('input', purApplyAllFilters);
  document.getElementById('purFirmSelect')?.addEventListener('change', purReloadWithFilters);

  /* ── Chart ── */
  let purChart = null;
  document.getElementById('purTxnChartBtn')?.addEventListener('click', function () {
    const panel = document.getElementById('purTxnChartPanel');
    panel.classList.toggle('d-none');
    if (!panel.classList.contains('d-none')) purInitChart('daily');
  });
  document.querySelectorAll('.pur-chart-period').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.pur-chart-period').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      purInitChart(this.dataset.period);
    });
  });

  function purInitChart(period) {
    const canvas = document.getElementById('purChart');
    if (!canvas) return;
    const grouped = {};
    purFiltered.forEach(row => {
      const d = new Date(row.bill_date || row.invoice_date || row.date);
      const key = period === 'daily'   ? d.toISOString().split('T')[0]
                : period === 'monthly' ? d.toLocaleString('default', {month:'short', year:'2-digit'})
                : period === 'yearly'  ? d.getFullYear().toString()
                : `W${Math.ceil(d.getDate()/7)} ${d.toLocaleString('default',{month:'short'})}`;
      grouped[key] = (grouped[key] || 0) + parseFloat(row.grand_total || row.total_amount || 0);
    });
    if (purChart) purChart.destroy();
    const drawFn = () => {
      purChart = new Chart(canvas, {
        type: 'bar',
        data: {
          labels: Object.keys(grouped),
          datasets: [{ label:'Purchases', data:Object.values(grouped), backgroundColor:'rgba(239,68,68,.7)', borderColor:'#ef4444', borderWidth:1 }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
          scales: { x:{grid:{color:'#f3f4f6'}}, y:{grid:{color:'#f3f4f6'}} }
        }
      });
    };
    if (typeof Chart === 'undefined') {
      const s = document.createElement('script');
      s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js';
      s.onload = drawFn; document.head.appendChild(s);
    } else { drawFn(); }
  }

  /* ── Excel Export ── */
  ['purTxnExcelBtn', 'purExcelTopBtn'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', () => {
      document.getElementById('purExcelModal').style.display = 'flex';
    });
  });
  document.getElementById('purExcelModalClose')?.addEventListener('click', () => {
    document.getElementById('purExcelModal').style.display = 'none';
  });
  document.getElementById('purExcelModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
  document.getElementById('purExcelGenerateBtn')?.addEventListener('click', function () {
    const cols = [...document.querySelectorAll('.pur-export-col:checked')].map(c => c.value);
    purExportCSV(cols);
    document.getElementById('purExcelModal').style.display = 'none';
  });

  function purExportCSV(cols) {
    const colMap = {
      date:         { label:'Date',          fn: r => fmtDate(r.bill_date || r.invoice_date) },
      invoice_no:   { label:'Invoice No',    fn: r => r.bill_number || '-' },
      party_name:   { label:'Party Name',    fn: r => r.party_name || '-' },
      total:        { label:'Total',         fn: r => parseFloat(r.grand_total || r.total_amount || 0).toFixed(2) },
      payment_type: { label:'Payment Type',  fn: r => r.payment_type || 'Cash' },
      received_paid:{ label:'Paid Amount',   fn: r => parseFloat(r.paid_amount || r.received_paid || 0).toFixed(2) },
      balance_due:  { label:'Balance Due',   fn: r => parseFloat(r.balance_due || 0).toFixed(2) },
      status:       { label:'Status',        fn: r => getStatus(r) },
      party_phone:  { label:"Party's Phone", fn: r => r.party_phone || '-' },
      description:  { label:'Description',   fn: r => r.description || '-' },
    };
    const headers = cols.map(c => colMap[c]?.label || c);
    const lines = [headers.join(',')];
    purFiltered.forEach(row => {
      const vals = cols.map(c => colMap[c] ? `"${String(colMap[c].fn(row)).replace(/"/g,'""')}"` : '""');
      lines.push(vals.join(','));
    });
    const blob = new Blob(['\uFEFF' + lines.join('\n')], { type:'text/csv;charset=utf-8;' });
    const a = Object.assign(document.createElement('a'), {
      href: URL.createObjectURL(blob),
      download: `PurchaseReport_${new Date().toISOString().split('T')[0]}.csv`
    });
    document.body.appendChild(a); a.click(); a.remove();
  }

  /* ── Print ── */
  ['purTxnPrintBtn', 'purPrintTopBtn'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', () => {
      document.getElementById('purPrintModal').style.display = 'flex';
    });
  });
  document.getElementById('purPrintModalClose')?.addEventListener('click', () => {
    document.getElementById('purPrintModal').style.display = 'none';
  });
  document.getElementById('purPrintModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
  document.getElementById('purPrintGenerateBtn')?.addEventListener('click', function () {
    const cols = [...document.querySelectorAll('.pur-print-col:checked')].map(c => c.value);
    document.getElementById('purPrintModal').style.display = 'none';
    purDoPrint(cols);
  });

  function purDoPrint(cols) {
    const colMap = {
      date:         { label:'DATE',         fn: r => fmtDate(r.bill_date || r.invoice_date) },
      invoice_no:   { label:'INVOICE NO.',  fn: r => r.bill_number || '-' },
      party_name:   { label:'PARTY NAME',   fn: r => r.party_name || '-' },
      total:        { label:'TOTAL',        fn: r => fmt(r.grand_total || r.total_amount || 0) },
      payment_type: { label:'PAYMENT TYPE', fn: r => r.payment_type || 'Cash' },
      received_paid:{ label:'PAID',         fn: r => fmt(r.paid_amount || r.received_paid || 0) },
      balance_due:  { label:'BALANCE DUE',  fn: r => fmt(r.balance_due || 0) },
      status:       { label:'STATUS',       fn: r => getStatus(r) },
    };
    const active = cols.filter(c => colMap[c]);
    const ths = active.map(c => `<th style="background:#f3f4f6;padding:8px 10px;border:1px solid #e5e7eb;font-size:11px;">${colMap[c].label}</th>`).join('');
    const trs = purFiltered.map(row =>
      `<tr>${active.map(c => `<td style="padding:7px 10px;border:1px solid #e5e7eb;font-size:12px;">${colMap[c].fn(row)}</td>`).join('')}</tr>`
    ).join('');
    const w = window.open('', '_blank', 'width=900,height=700');
    if (!w) return;
    w.document.write(`<html><head><title>Purchase Report</title>
      <style>body{font-family:Arial,sans-serif;padding:20px;color:#1f2937;}
      h2{text-align:center;} table{width:100%;border-collapse:collapse;}</style>
    </head><body>
      <h2>Purchase Bills Report</h2>
      <p><strong>${document.getElementById('purDateFrom').textContent} to ${document.getElementById('purDateTo').textContent}</strong></p>
      <table><thead><tr>${ths}</tr></thead><tbody>${trs}</tbody></table>
    </body></html>`);
    w.document.close(); w.focus(); w.print();
  }

  /* Row print */
  window.purPrintRow = function(id) {
    const row = purAllRows.find(r => r.id == id);
    if (!row) return;
    const w = window.open('', '_blank');
    w.document.write(`<html><body style="font-family:sans-serif;padding:32px;">
      <h3>Purchase Voucher</h3>
      <p><b>Invoice No:</b> ${row.bill_number || '-'}</p>
      <p><b>Date:</b> ${fmtDate(row.bill_date || row.invoice_date)}</p>
      <p><b>Party:</b> ${row.party_name || '-'}</p>
      <p><b>Payment Type:</b> ${row.payment_type || '-'}</p>
      <p><b>Total:</b> ${fmt(row.grand_total || row.total_amount || 0)}</p>
      <p><b>Paid:</b> ${fmt(row.paid_amount || row.received_paid || 0)}</p>
      <p><b>Balance Due:</b> ${fmt(row.balance_due || 0)}</p>
      <p><b>Status:</b> ${getStatus(row)}</p>
    </body></html>`);
    w.document.close(); w.print();
  };

  /* ── Add Purchase ── */
  document.getElementById('purAddPurchaseBtn')?.addEventListener('click', function () {
    window.location.href = '{{ route("purchase-bill.create") }}';
  });

  /* ── Close dropdowns / calendar on outside click ── */
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.pur-th-filter-wrap')) {
      document.querySelectorAll('.pur-col-filter-dropdown.open').forEach(d => {
        d.classList.remove('open');
        d.previousElementSibling?.classList.remove('active');
      });
    }
    if (!e.target.closest('.pur-period-pill') && !e.target.closest('#purCalendarPopup')) {
      document.getElementById('purCalendarPopup')?.classList.add('d-none');
    }
  });

  /* ── INIT ── */
  (function init() {
    const allFrom = '2000-01-01', allTo = '2099-12-31';
    updateDateDisplay(allFrom, allTo);
    purLoadData(allFrom, allTo);
  })();

})();
</script>
