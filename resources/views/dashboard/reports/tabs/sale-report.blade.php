{{-- resources/views/dashboard/reports/tabs/sale-report.blade.php --}}
{{-- Include this in your main report.blade.php: @include('dashboard.reports.tabs.sale-report') --}}

<!-- ═══════════════════════════════════════════════
     SALE INVOICES TAB
═══════════════════════════════════════════════ -->
<div id="tab-Sale" class="report-tab-content">

  <!-- ── Header ── -->
  <div class="sale-report-header d-flex justify-content-between align-items-center mb-3 px-1">
    <h3 class="sale-report-title mb-0">Sale Invoices</h3>
    <div class="d-flex gap-2 align-items-center">
      <button class="sale-add-btn" id="saleAddSaleBtn" type="button">
        <i class="fa-solid fa-plus me-1"></i> Add Sale
      </button>
      <a class="sale-icon-btn" id="saleSettingsBtn" href="{{ route('settings.transactions') }}"
        title="Transaction Settings" aria-label="Open transaction settings">
        <i class="fa-solid fa-gear"></i>
      </a>
    </div>
  </div>

  <!-- ── Filter Bar ── -->
  <div class="sale-filter-bar d-flex align-items-center gap-3 mb-3">
    <span class="sale-filter-label">Filter by :</span>

    <!-- Period Selector -->
    <div class="sale-period-pill d-flex align-items-center">
      <div class="sale-period-select-wrap">
        <select id="salePeriodSelect" class="sale-period-select">
          <option value="all">All Sale Invoices</option>
          <option value="this_month">This Month</option>
          <option value="last_month" selected>Last Month</option>
          <option value="this_quarter">This Quarter</option>
          <option value="this_year">This Year</option>
          <option value="custom">Custom</option>
        </select>
        <i class="fa-solid fa-chevron-down sale-period-arrow"></i>
      </div>
      <div class="sale-period-divider"></div>
      <div class="sale-date-range-display" id="saleDateRangeDisplay" role="button" tabindex="0" title="Click to set custom date range">
        <i class="fa-regular fa-calendar me-1" style="color:#6b7280;font-size:13px;"></i>
        <span id="saleDateFrom">01/03/2026</span>
        <span class="mx-1" style="color:#6b7280;">To</span>
        <span id="saleDateTo">31/03/2026</span>
      </div>
    </div>

    <!-- Custom Date Inputs (hidden unless custom selected) -->
    <div class="sale-custom-dates d-none" id="saleCustomDates">
      <input type="date" id="saleFromDate" class="sale-date-input">
      <span class="mx-1 text-muted">To</span>
      <input type="date" id="saleToDate" class="sale-date-input">
      <button class="sale-apply-dates-btn" id="saleApplyDates">Apply</button>
    </div>

    <!-- Firms Filter -->
    <div class="sale-firm-select-wrap">
      <select id="saleFirmSelect" class="sale-firm-select">
        <option value="all">All Firms</option>
      </select>
      <i class="fa-solid fa-chevron-down sale-period-arrow"></i>
    </div>

    <!-- Store Filter -->
    <div class="sale-firm-select-wrap">
      <select id="saleStoreSelect" class="sale-firm-select">
        <option value="all">All Store</option>
      </select>
      <i class="fa-solid fa-chevron-down sale-period-arrow"></i>
    </div>
  </div>

  <!-- ── Summary Card ── -->
  <div class="sale-summary-card mb-4" id="saleSummaryCard">
    <div class="sale-summary-inner">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="sale-summary-label">Total Sales Amount</span>
        <div class="text-end">
          <div class="sale-growth-badge" id="saleGrowthBadge">
            <span id="saleGrowthPct">0%</span>
            <i class="fa-solid fa-arrow-up-right-dots ms-1" style="font-size:10px;"></i>
          </div>
          <div class="sale-vs-label" id="saleVsLabel">vs last month</div>
        </div>
      </div>
      <div class="sale-summary-amount" id="saleTotalAmount">Rs 0</div>
      <div class="sale-summary-foot">
        <span>Received: <strong id="saleTotalReceived">Rs 0</strong></span>
        <span class="sale-summary-divider"></span>
        <span>Balance: <strong id="saleTotalBalance">Rs 0</strong></span>
      </div>
    </div>
  </div>

  <!-- ── Transactions Card ── -->
  <div class="sale-txn-card">

    <!-- Card Header -->
    <div class="sale-txn-card-header">
      <h5 class="sale-txn-title">Transactions</h5>
      <div class="sale-txn-actions">
        <button class="sale-txn-action-btn" id="saleTxnSearchBtn" title="Search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <button class="sale-txn-action-btn" id="saleTxnChartBtn" title="Chart">
          <i class="fa-solid fa-chart-simple"></i>
        </button>
        <button class="sale-txn-action-btn text-success" id="saleTxnExcelBtn" title="Export to Excel">
          <i class="fa-regular fa-file-excel"></i>
        </button>
        <button class="sale-txn-action-btn" id="saleTxnPrintBtn" title="Print">
          <i class="fa-solid fa-print"></i>
        </button>
      </div>
    </div>

    <!-- Search Bar (hidden by default) -->
    <div class="sale-search-bar d-none" id="saleTxnSearchBar">
      <div class="sale-search-inner">
        <i class="fa-solid fa-search" style="color:#9ca3af;"></i>
        <input type="text" id="saleTxnSearchInput" class="sale-search-input" placeholder="Search transactions...">
        <button id="saleTxnSearchClose" class="sale-search-close"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <!-- Chart Panel (hidden by default) -->
    <div class="sale-chart-panel d-none" id="saleTxnChartPanel">
      <div class="sale-chart-header">
        <div class="d-flex align-items-center gap-3">
          <div class="sale-chart-date-wrap">
            <span>From</span>
            <input type="date" id="saleChartFrom" class="sale-chart-date-input">
            <span>To</span>
            <input type="date" id="saleChartTo" class="sale-chart-date-input">
          </div>
        </div>
        <div class="sale-chart-periods">
          <button class="sale-chart-period active" data-period="daily">Daily</button>
          <button class="sale-chart-period" data-period="weekly">Weekly</button>
          <button class="sale-chart-period" data-period="monthly">Monthly</button>
          <button class="sale-chart-period" data-period="yearly">Yearly</button>
        </div>
      </div>
      <div class="sale-chart-title">Sales Graph</div>
      <div class="sale-chart-container">
        <canvas id="saleChart"></canvas>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="sale-table" id="saleTransactionsTable" data-column-drag="native"
        data-column-drag-storage="vyapar.reports.sale.transactions.v1">
        <thead>
          <tr>
            <!-- Date -->
            <th data-column-key="date">
              <div class="sale-th-inner">
                <span>Date</span>
                <button class="sale-th-sort" onclick="saleSortBy('date', this)" title="Sort">
                  <i class="fa-solid fa-sort"></i>
                </button>
                <div class="sale-th-filter-wrap">
                  <button class="sale-th-filter-btn" onclick="saleToggleColFilter(this)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                  </button>
                  <div class="sale-col-filter-dropdown">
                    <div class="sale-col-filter-body">
                      <label class="sale-col-filter-label">Select Category:</label>
                      <select class="sale-col-filter-select" id="saleFilterDateType">
                        <option value="equal">Equal to</option>
                        <option value="lt">Less than</option>
                        <option value="gt">Greater than</option>
                        <option value="range">Range</option>
                      </select>
                      <label class="sale-col-filter-label mt-2">Select Date:</label>
                      <input type="date" class="sale-col-filter-input" id="saleFilterDateVal">
                      <div id="saleFilterDateRange" class="d-none">
                        <input type="date" class="sale-col-filter-input mt-1" id="saleFilterDateRangeFrom">
                        <input type="date" class="sale-col-filter-input mt-1" id="saleFilterDateRangeTo">
                      </div>
                    </div>
                    <div class="sale-col-filter-actions">
                      <button class="sale-col-clear-btn" onclick="saleClearColFilter('date', this)">Clear</button>
                      <button class="sale-col-apply-btn" onclick="saleApplyColFilter('date', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Invoice No -->
            <th data-column-key="invoice_no">
              <div class="sale-th-inner">
                <span>Invoice No.</span>
                <button class="sale-th-sort" onclick="saleSortBy('invoice_no', this)" title="Sort">
                  <i class="fa-solid fa-sort"></i>
                </button>
                <div class="sale-th-filter-wrap">
                  <button class="sale-th-filter-btn" onclick="saleToggleColFilter(this)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                  </button>
                  <div class="sale-col-filter-dropdown">
                    <div class="sale-col-filter-body">
                      <label class="sale-col-filter-label">Select Category:</label>
                      <select class="sale-col-filter-select" id="saleFilterInvoiceType">
                        <option value="contains">Contains</option>
                        <option value="exact">Exact Match</option>
                      </select>
                      <label class="sale-col-filter-label mt-2">Invoice No.</label>
                      <input type="text" class="sale-col-filter-input" id="saleFilterInvoiceVal" placeholder="e.g. INV-001">
                    </div>
                    <div class="sale-col-filter-actions">
                      <button class="sale-col-clear-btn" onclick="saleClearColFilter('invoice_no', this)">Clear</button>
                      <button class="sale-col-apply-btn" onclick="saleApplyColFilter('invoice_no', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Party Name -->
            <th data-column-key="party_name">
              <div class="sale-th-inner">
                <span>Party Name</span>
                <button class="sale-th-sort" onclick="saleSortBy('party_name', this)" title="Sort">
                  <i class="fa-solid fa-sort"></i>
                </button>
                <div class="sale-th-filter-wrap">
                  <button class="sale-th-filter-btn" onclick="saleToggleColFilter(this)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                  </button>
                  <div class="sale-col-filter-dropdown">
                    <div class="sale-col-filter-body">
                      <label class="sale-col-filter-label">Select Category:</label>
                      <select class="sale-col-filter-select" id="saleFilterPartyType">
                        <option value="contains">Contains</option>
                        <option value="exact">Exact Match</option>
                      </select>
                      <label class="sale-col-filter-label mt-2">Party Name</label>
                      <input type="text" class="sale-col-filter-input" id="saleFilterPartyVal" placeholder="Party name...">
                    </div>
                    <div class="sale-col-filter-actions">
                      <button class="sale-col-clear-btn" onclick="saleClearColFilter('party_name', this)">Clear</button>
                      <button class="sale-col-apply-btn" onclick="saleApplyColFilter('party_name', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Transaction -->
            <th data-column-key="transaction">
              <div class="sale-th-inner">
                <span>Transaction</span>
                <div class="sale-th-filter-wrap">
                  <button class="sale-th-filter-btn" onclick="saleToggleColFilter(this)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                  </button>
                  <div class="sale-col-filter-dropdown" style="min-width:200px;">
                    <div class="sale-col-filter-body">
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Sale" class="sale-txn-type-check"> Sale</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Lite Sale" class="sale-txn-type-check"> Lite Sale</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Credit Note" class="sale-txn-type-check"> Credit Note</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Sale (Invoice)" class="sale-txn-type-check"> Sale (Invoice)</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Credit Note (Invoice)" class="sale-txn-type-check"> Credit Note (Invoice)</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="POS Sale" class="sale-txn-type-check"> POS Sale</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Sale [Cancelled]" class="sale-txn-type-check"> Sale [Cancelled]</label>
                    </div>
                    <div class="sale-col-filter-actions">
                      <button class="sale-col-clear-btn" onclick="saleClearColFilter('transaction', this)">Clear</button>
                      <button class="sale-col-apply-btn" onclick="saleApplyColFilter('transaction', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Payment Type -->
            <th data-column-key="payment_type">
              <div class="sale-th-inner">
                <span>Payment Type</span>
                <div class="sale-th-filter-wrap">
                  <button class="sale-th-filter-btn" onclick="saleToggleColFilter(this)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                  </button>
                  <div class="sale-col-filter-dropdown">
                    <div class="sale-col-filter-body">
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Cash" class="sale-pay-type-check"> Cash</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Cheque" class="sale-pay-type-check"> Cheque</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Online" class="sale-pay-type-check"> Online</label>
                      <label class="sale-col-filter-checkbox"><input type="checkbox" value="Card" class="sale-pay-type-check"> Card</label>
                    </div>
                    <div class="sale-col-filter-actions">
                      <button class="sale-col-clear-btn" onclick="saleClearColFilter('payment_type', this)">Clear</button>
                      <button class="sale-col-apply-btn" onclick="saleApplyColFilter('payment_type', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Amount -->
            <th data-column-key="amount">
              <div class="sale-th-inner">
                <span>Amount</span>
                <button class="sale-th-sort" onclick="saleSortBy('amount', this)" title="Sort">
                  <i class="fa-solid fa-sort"></i>
                </button>
                <div class="sale-th-filter-wrap">
                  <button class="sale-th-filter-btn" onclick="saleToggleColFilter(this)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                  </button>
                  <div class="sale-col-filter-dropdown" style="min-width:220px;">
                    <div class="sale-col-filter-body">
                      <label class="sale-col-filter-label">Amount</label>
                      <input type="range" class="sale-range-slider" id="saleFilterAmountRange" min="0" max="1000000" step="100" value="1000000">
                      <div class="d-flex gap-2 mt-2">
                        <div class="flex-1">
                          <label class="sale-col-filter-label">Min</label>
                          <input type="number" class="sale-col-filter-input" id="saleFilterAmountMin" placeholder="0">
                        </div>
                        <div class="flex-1">
                          <label class="sale-col-filter-label">Max</label>
                          <input type="number" class="sale-col-filter-input" id="saleFilterAmountMax" placeholder="+500000">
                        </div>
                      </div>
                    </div>
                    <div class="sale-col-filter-actions">
                      <button class="sale-col-clear-btn" onclick="saleClearColFilter('amount', this)">Clear</button>
                      <button class="sale-col-apply-btn" onclick="saleApplyColFilter('amount', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Balance -->
            <th data-column-key="balance">
              <div class="sale-th-inner">
                <span>Balance</span>
                <button class="sale-th-sort" onclick="saleSortBy('balance', this)" title="Sort">
                  <i class="fa-solid fa-sort"></i>
                </button>
                <div class="sale-th-filter-wrap">
                  <button class="sale-th-filter-btn" onclick="saleToggleColFilter(this)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                  </button>
                  <div class="sale-col-filter-dropdown" style="min-width:220px;">
                    <div class="sale-col-filter-body">
                      <label class="sale-col-filter-label">Price Range</label>
                      <input type="range" class="sale-range-slider" min="0" max="1000000" step="100" value="1000000">
                      <div class="d-flex gap-2 mt-2">
                        <div class="flex-1">
                          <label class="sale-col-filter-label">Min</label>
                          <input type="number" class="sale-col-filter-input" id="saleFilterBalanceMin" placeholder="0">
                        </div>
                        <div class="flex-1">
                          <label class="sale-col-filter-label">Max</label>
                          <input type="number" class="sale-col-filter-input" id="saleFilterBalanceMax" placeholder="+500000">
                        </div>
                      </div>
                    </div>
                    <div class="sale-col-filter-actions">
                      <button class="sale-col-clear-btn" onclick="saleClearColFilter('balance', this)">Clear</button>
                      <button class="sale-col-apply-btn" onclick="saleApplyColFilter('balance', this)">Apply</button>
                    </div>
                  </div>
                </div>
              </div>
            </th>

            <!-- Actions -->
            <th data-column-key="actions"><div class="sale-th-inner"><span>Actions</span></div></th>
          </tr>
        </thead>
        <tbody id="saleTxnTableBody">
          <tr id="saleNoDataRow">
            <td colspan="8" class="sale-empty-state">
              <i class="fa-solid fa-receipt" style="font-size:36px;color:#d1d5db;display:block;margin-bottom:8px;"></i>
              No sale invoices found. Click "Add Sale" to create one.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="sale-pagination" id="salePagination" style="display:none;">
      <span class="sale-pagination-info" id="salePaginationInfo"></span>
      <div class="sale-pagination-btns">
        <button class="sale-page-btn" id="salePrevPage"><i class="fa-solid fa-chevron-left"></i></button>
        <span id="salePageNumbers"></span>
        <button class="sale-page-btn" id="saleNextPage"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

  </div><!-- /sale-txn-card -->

</div><!-- /tab-Sale -->


<!-- ═══════════════════════════════════════════════
     EXCEL EXPORT MODAL  (matches Vyapar "Select Report Options")
═══════════════════════════════════════════════ -->
<div class="sale-modal-overlay" id="saleExcelModal" style="display:none;">
  <div class="sale-modal-box">
    <div class="sale-modal-header">
      <h5 class="sale-modal-title">Select Report Options</h5>
      <button class="sale-modal-close" id="saleExcelModalClose"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="sale-modal-body">
      <div class="sale-modal-columns">
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="date" checked> Date</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="item_details"> Item Details</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="invoice_no" checked> Invoice No.</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="description"> Description</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="party_name" checked> Party Name</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="payment_status"> Payment Status</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="total" checked> Total</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="order_number"> Order Number</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="payment_type" checked> Payment Type</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="party_phone"> Party's Phone No.</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="received_paid" checked> Received/Paid</label>
        <label class="sale-modal-col-item"></label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-export-col" value="balance_due" checked> Balance Due</label>
      </div>
    </div>
    <div class="sale-modal-footer">
      <button class="sale-modal-generate-btn" id="saleExcelGenerateBtn">
        Generate Report
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     PRINT OPTIONS MODAL
═══════════════════════════════════════════════ -->
<div class="sale-modal-overlay" id="salePrintModal" style="display:none;">
  <div class="sale-modal-box">
    <div class="sale-modal-header">
      <h5 class="sale-modal-title">Select Print Options</h5>
      <button class="sale-modal-close" id="salePrintModalClose"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="sale-modal-body">
      <div class="sale-modal-columns">
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="date" checked> Date</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="item_details"> Item Details</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="invoice_no" checked> Invoice No.</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="description"> Description</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="party_name" checked> Party Name</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="payment_status"> Payment Status</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="total" checked> Total</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="order_number"> Order Number</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="payment_type" checked> Payment Type</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="party_phone"> Party's Phone No.</label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="received_paid" checked> Received/Paid</label>
        <label class="sale-modal-col-item"></label>
        <label class="sale-modal-col-item"><input type="checkbox" class="sale-print-col" value="balance_due" checked> Balance Due</label>
      </div>
    </div>
    <div class="sale-modal-footer">
      <button class="sale-modal-generate-btn sale-modal-print-btn" id="salePrintGenerateBtn">
        Get Print
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     PRINT PREVIEW MODAL  (matches Vyapar "Preview")
═══════════════════════════════════════════════ -->
<div class="sale-modal-overlay" id="salePrintPreviewModal" style="display:none;">
  <div class="sale-modal-box sale-preview-box">
    <div class="sale-modal-header">
      <h5 class="sale-modal-title">Preview</h5>
      <button class="sale-modal-close" id="salePreviewModalClose"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="sale-preview-body" id="salePrintPreviewBody">
      <!-- dynamically filled -->
    </div>
    <div class="sale-preview-footer">
      <button class="sale-preview-btn" id="salePreviewOpenPdf">Open PDF</button>
      <button class="sale-preview-btn" id="salePreviewPrint">Print</button>
      <button class="sale-preview-btn" id="salePreviewSavePdf">Save PDF</button>
      <button class="sale-preview-btn" id="salePreviewEmailPdf">Email PDF</button>
      <button class="sale-preview-btn sale-preview-close-btn" id="salePreviewClose">Close</button>
    </div>
  </div>
</div>


{{-- ══════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════ --}}
<style>
/* ── Base ── */
.sale-report-title {
  font-size: 22px;
  font-weight: 700;
  color: #111827;
}

/* ── Add Sale Button ── */
.sale-add-btn {
  background: #ef4444;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 8px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.sale-add-btn:hover { background: #dc2626; }

.sale-icon-btn {
  width: 36px; height: 36px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #6b7280;
  font-size: 15px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s;
}
.sale-icon-btn:hover { background: #f3f4f6; }

/* ── Filter Bar ── */
.sale-filter-bar { flex-wrap: wrap; }
.sale-filter-label {
  font-size: 14px; color: #6b7280; white-space: nowrap;
}

.sale-period-pill {
  background: #E4F2FF;
  border-radius: 999px;
  overflow: hidden;
  height: 40px;
}
.sale-period-select-wrap {
  display: flex; align-items: center;
  padding: 0 14px;
  border-right: 1px solid rgba(0,0,0,0.15);
  position: relative;
}
.sale-period-select {
  background: transparent;
  border: none; outline: none;
  font-size: 13px; font-weight: 500;
  color: #374151;
  cursor: pointer;
  padding-right: 18px;
  appearance: none;
}
.sale-period-arrow {
  position: absolute; right: 14px;
  font-size: 10px; color: #6b7280;
  pointer-events: none;
}
.sale-date-range-display {
  padding: 0 16px;
  font-size: 13px; color: #374151;
  white-space: nowrap;
  display: flex; align-items: center;
  cursor: pointer;
  user-select: none;
}
.sale-date-range-display:hover { background: rgba(37, 99, 235, 0.07); }
.sale-date-range-display:focus-visible {
  outline: 2px solid #2563eb;
  outline-offset: -2px;
}

.sale-firm-select-wrap {
  position: relative;
  display: flex; align-items: center;
  background: #E4F2FF;
  border-radius: 999px;
  padding: 0 14px;
  height: 40px;
}
.sale-firm-select {
  background: transparent; border: none; outline: none;
  font-size: 13px; color: #374151;
  cursor: pointer; appearance: none; padding-right: 18px;
}

.sale-custom-dates {
  display: flex; align-items: center; gap: 8px;
}
.sale-date-input {
  border: 1px solid #e5e7eb; border-radius: 8px;
  padding: 6px 10px; font-size: 13px;
  outline: none; color: #374151;
}
.sale-apply-dates-btn {
  background: #ef4444; color: #fff;
  border: none; border-radius: 8px;
  padding: 6px 14px; font-size: 13px;
  cursor: pointer;
}

/* ── Summary Card ── */
.sale-summary-card {
  max-width: 340px;
  background: #fff;
  border: 1px solid #e0e7ff;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(99,102,241,0.06);
}
.sale-summary-inner { padding: 20px 24px; }
.sale-summary-label { font-size: 14px; color: #6b7280; font-weight: 500; }
.sale-growth-badge {
  display: inline-flex; align-items: center;
  background: #d1fae5; color: #047857;
  border-radius: 999px; padding: 3px 10px;
  font-size: 12px; font-weight: 700;
}
.sale-vs-label { font-size: 10px; color: #9ca3af; margin-top: 2px; }
.sale-summary-amount { font-size: 28px; font-weight: 700; color: #111827; margin: 6px 0 14px; }
.sale-summary-foot {
  display: flex; align-items: center; gap: 16px;
  border-top: 1px solid #f3f4f6; padding-top: 12px;
  font-size: 13px; color: #6b7280;
}
.sale-summary-foot strong { color: #111827; }
.sale-summary-divider { width: 1px; height: 16px; background: #e5e7eb; }

/* ── Transactions Card ── */
.sale-txn-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.sale-txn-card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #f3f4f6;
}
.sale-txn-title { font-size: 16px; font-weight: 700; color: #111827; margin: 0; }
.sale-txn-actions { display: flex; gap: 4px; }
.sale-txn-action-btn {
  width: 34px; height: 34px;
  border: none; background: transparent;
  color: #6b7280; font-size: 15px;
  cursor: pointer; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s, color 0.15s;
}
.sale-txn-action-btn:hover { background: #f3f4f6; color: #374151; }
.sale-txn-action-btn.text-success { color: #10b981 !important; }

/* ── Search Bar ── */
.sale-search-bar { padding: 12px 20px; border-bottom: 1px solid #f3f4f6; }
.sale-search-inner {
  display: flex; align-items: center; gap: 8px;
  border: 1px solid #e5e7eb; border-radius: 10px; padding: 8px 12px;
  max-width: 360px;
}
.sale-search-input { flex:1; border:none; outline:none; font-size:14px; color:#374151; }
.sale-search-close { border:none; background:transparent; color:#9ca3af; cursor:pointer; }

/* ── Chart Panel ── */
.sale-chart-panel { padding: 16px 20px; border-bottom: 1px solid #f3f4f6; }
.sale-chart-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 12px;
}
.sale-chart-date-wrap { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; }
.sale-chart-date-input {
  border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px 8px;
  font-size: 13px; color: #374151; outline: none;
}
.sale-chart-periods { display: flex; gap: 4px; }
.sale-chart-period {
  border: none; background: transparent; font-size: 13px; font-weight: 600;
  color: #6b7280; padding: 6px 12px; cursor: pointer; border-bottom: 2px solid transparent;
  transition: color 0.15s, border-color 0.15s;
}
.sale-chart-period.active { color: #2563eb; border-color: #2563eb; }
.sale-chart-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.sale-chart-container { height: 220px; }

/* ── Table ── */
.sale-table {
  width: 100%; border-collapse: collapse;
  font-size: 13px;
}
.sale-table thead tr { background: #fff; }
.sale-table th {
  padding: 0;
  border-bottom: 1px solid #f3f4f6;
  white-space: nowrap;
}
.sale-th-inner {
  display: flex; align-items: center; gap: 4px;
  padding: 12px 14px;
  color: #6b7280; font-size: 12px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.03em;
}
.sale-th-sort {
  border: none; background: transparent; color: #c4c9d4;
  font-size: 11px; cursor: pointer; padding: 0;
  transition: color 0.15s;
}
.sale-th-sort:hover, .sale-th-sort.active { color: #2563eb; }

/* Column filter */
.sale-th-filter-wrap { position: relative; display: inline-flex; }
.sale-th-filter-btn {
  border: none; background: transparent;
  color: #c4c9d4; font-size: 11px;
  cursor: pointer; padding: 2px 4px;
  border-radius: 4px; transition: color 0.15s, background 0.15s;
}
.sale-th-filter-btn:hover, .sale-th-filter-btn.active { color: #2563eb; background: #eff6ff; }

.sale-col-filter-dropdown {
  display: none;
  position: absolute; top: calc(100% + 6px); left: 0;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 12px 30px rgba(0,0,0,0.12);
  z-index: 200;
  min-width: 190px;
  overflow: hidden;
}
.sale-col-filter-dropdown.open { display: block; }
.sale-col-filter-body { padding: 12px 14px; }
.sale-col-filter-label { display: block; font-size: 11px; color: #9ca3af; margin-bottom: 4px; }
.sale-col-filter-select, .sale-col-filter-input {
  width: 100%; border: 1px solid #e5e7eb; border-radius: 8px;
  padding: 6px 10px; font-size: 13px; outline: none; color: #374151;
  background: #fff;
}
.sale-col-filter-checkbox {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; color: #374151; padding: 5px 0; cursor: pointer;
}
.sale-col-filter-actions {
  display: flex; justify-content: flex-end; gap: 8px;
  padding: 10px 14px;
  border-top: 1px solid #f3f4f6; background: #fafafa;
}
.sale-col-clear-btn {
  background: #EBEAEA; color: #71748E;
  border: none; border-radius: 999px;
  padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer;
}
.sale-col-apply-btn {
  background: #ef4444; color: #fff;
  border: none; border-radius: 999px;
  padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer;
}
.sale-range-slider { width: 100%; accent-color: #2563eb; }

/* Table body */
.sale-table tbody tr { border-bottom: 1px solid #f9fafb; }
.sale-table tbody tr:hover { background: #fafafa; }
.sale-table td { padding: 13px 14px; color: #374151; font-size: 13px; vertical-align: middle; }

.sale-empty-state {
  text-align: center; padding: 48px; color: #9ca3af; font-size: 14px;
}

/* Row action buttons */
.sale-row-print-btn, .sale-row-share-btn {
  border: none; background: transparent;
  color: #9ca3af; font-size: 14px;
  cursor: pointer; padding: 4px 6px; border-radius: 6px;
  transition: color 0.15s, background 0.15s;
}
.sale-row-print-btn:hover { color: #374151; background: #f3f4f6; }
.sale-row-share-btn:hover { color: #2563eb; background: #eff6ff; }
.sale-row-more-btn {
  border: none; background: transparent;
  color: #9ca3af; font-size: 14px;
  cursor: pointer; padding: 4px 6px; border-radius: 6px;
}
.sale-row-more-btn:hover { color: #374151; background: #f3f4f6; }

/* Status badges */
.sale-badge-paid   { background:#d1fae5; color:#047857; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; }
.sale-badge-unpaid { background:#fef3c7; color:#d97706; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; }
.sale-badge-partial{ background:#dbeafe; color:#2563eb; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; }

/* Pagination */
.sale-pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 20px; border-top: 1px solid #f3f4f6; font-size: 13px; color: #6b7280;
}
.sale-pagination-btns { display: flex; align-items: center; gap: 4px; }
.sale-page-btn {
  width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e5e7eb;
  background: #fff; color: #6b7280; cursor: pointer; font-size: 11px;
  display: flex; align-items: center; justify-content: center;
}
.sale-page-btn:hover { background: #f3f4f6; }

/* ── Modals ── */
.sale-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4);
  z-index: 1100; display: flex; align-items: center; justify-content: center;
}
.sale-modal-box {
  background: #fff; border-radius: 16px;
  width: min(520px, calc(100vw - 32px));
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  overflow: hidden;
}
.sale-modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 24px 16px;
}
.sale-modal-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; }
.sale-modal-close {
  border: none; background: transparent; color: #9ca3af; font-size: 18px; cursor: pointer;
}
.sale-modal-body { padding: 0 24px 16px; }
.sale-modal-columns {
  display: grid; grid-template-columns: 1fr 1fr; gap: 6px 24px;
}
.sale-modal-col-item {
  display: flex; align-items: center; gap: 10px;
  font-size: 14px; color: #374151; padding: 6px 0; cursor: pointer;
}
.sale-modal-col-item input[type="checkbox"] {
  width: 18px; height: 18px;
  accent-color: #2563eb; cursor: pointer;
}
.sale-modal-footer { padding: 16px 24px 20px; text-align: center; }
.sale-modal-generate-btn {
  background: #ef4444; color: #fff;
  border: none; border-radius: 999px;
  padding: 12px 36px; font-size: 15px; font-weight: 600;
  cursor: pointer; transition: background 0.15s;
}
.sale-modal-generate-btn:hover { background: #dc2626; }
.sale-modal-print-btn { background: #ef4444 !important; }

/* Preview Modal */
.sale-preview-box { width: min(860px, calc(100vw - 32px)); }
.sale-preview-body {
  padding: 20px 28px;
  min-height: 340px; max-height: 60vh; overflow-y: auto;
  border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
  background: #fff;
}
.sale-preview-footer {
  display: flex; gap: 10px; justify-content: flex-end;
  padding: 16px 24px;
}
.sale-preview-btn {
  border: 1px solid #e5e7eb; background: #fff;
  border-radius: 999px; padding: 8px 20px;
  font-size: 13px; font-weight: 600; color: #374151; cursor: pointer;
  transition: background 0.15s;
}
.sale-preview-btn:hover { background: #f3f4f6; }
.sale-preview-close-btn { background: #ef4444 !important; color: #fff !important; border-color: #ef4444 !important; }
.sale-preview-close-btn:hover { background: #dc2626 !important; }

/* Print preview HTML */
.sale-print-preview { font-family: Arial, sans-serif; color: #1f2937; padding: 10px; }
.sale-print-preview h2 { text-align: center; font-size: 20px; margin-bottom: 2px; }
.sale-print-preview .sp-sub { text-align: center; font-size: 12px; color: #6b7280; margin-bottom: 4px; }
.sale-print-preview .sp-report-title { text-align: center; font-size: 18px; font-weight: 700; text-decoration: underline; margin-bottom: 8px; }
.sale-print-preview .sp-duration { font-size: 13px; font-weight: 700; margin-bottom: 12px; }
.sale-print-preview table { width: 100%; border-collapse: collapse; font-size: 12px; }
.sale-print-preview th { background: #f3f4f6; font-weight: 700; padding: 8px 10px; text-align: left; border: 1px solid #e5e7eb; }
.sale-print-preview td { padding: 7px 10px; border: 1px solid #e5e7eb; }
.sale-print-preview .sp-total-row { font-weight: 700; text-align: right; padding-top: 12px; }
.sale-print-preview .sp-generated { font-size: 11px; color: #9ca3af; margin-top: 12px; }
</style>


{{-- ══════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════ --}}
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
<script>
(function () {
  'use strict';

  /* ── State ── */
  let saleAllRows   = [];  // raw data from server
  let saleFiltered  = [];  // after all filters applied
  let saleSortState = { key: null, dir: 1 };
  let saleColFilters = {};
  let saleCurrentPage = 1;
  let saleActiveFrom = '';
  let saleActiveTo = '';
  const SALE_PAGE_SIZE = 25;

  /* ── Helpers ── */
  function fmt(n) { return 'Rs ' + parseFloat(n || 0).toFixed(2); }
  function today() { return new Date().toISOString().split('T')[0]; }
  function fmtDate(d) {
    if (!d) return '-';
    const dt = new Date(d);
    return dt.toLocaleDateString('en-GB');
  }

  /* ── Date Range Logic ── */
  function getDateRange(period) {
    const now = new Date();
    const y = now.getFullYear(), m = now.getMonth();
    switch (period) {
      case 'this_month':
        return [new Date(y, m, 1), new Date(y, m + 1, 0)];
      case 'last_month':
        return [new Date(y, m - 1, 1), new Date(y, m, 0)];
      case 'this_quarter': {
        const q = Math.floor(m / 3);
        return [new Date(y, q * 3, 1), new Date(y, q * 3 + 3, 0)];
      }
      case 'this_year':
        return [new Date(y, 0, 1), new Date(y, 11, 31)];
      case 'all':
        return [new Date(2000, 0, 1), new Date(2099, 11, 31)];
      default:
        return [new Date(y, m - 1, 1), new Date(y, m, 0)];
    }
  }

  function toISODate(d) { return d.toISOString().split('T')[0]; }
  function toDisplay(d) { return d.toLocaleDateString('en-GB').replace(/\//g, '/'); }

  function updateDateDisplay(period) {
    const [from, to] = getDateRange(period);
    document.getElementById('saleDateFrom').textContent = toDisplay(from);
    document.getElementById('saleDateTo').textContent   = toDisplay(to);
    return [toISODate(from), toISODate(to)];
  }

  /* ── Fetch data from server ── */
  function saleLoadData(fromDate, toDate) {
    saleActiveFrom = fromDate;
    saleActiveTo = toDate;

    const tbody = document.getElementById('saleTxnTableBody');
    tbody.innerHTML = `<tr><td colspan="8" class="sale-empty-state">
      <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;color:#d1d5db;display:block;margin-bottom:6px;"></i>
      Loading transactions…
    </td></tr>`;

    const params = new URLSearchParams({ from: fromDate, to: toDate });
    const partyId = document.getElementById('saleFirmSelect')?.value;
    const warehouseId = document.getElementById('saleStoreSelect')?.value;
    if (partyId && partyId !== 'all') params.set('party', partyId);
    if (warehouseId && warehouseId !== 'all') params.set('warehouse', warehouseId);

    fetch(`{{ route('reports.sale') }}?${params.toString()}`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        saleSyncFilterOptions(data);
        saleAllRows  = data.transactions || [];
        saleFiltered = [...saleAllRows];
        saleCurrentPage = 1;
        updateSaleSummary(data);
        saleRenderTable();
      } else {
        tbody.innerHTML = `<tr><td colspan="8" class="sale-empty-state">No data found.</td></tr>`;
      }
    })
    .catch(() => {
      tbody.innerHTML = `<tr><td colspan="8" class="sale-empty-state text-danger">Error loading data.</td></tr>`;
    });
  }

  function salePopulateFilter(selectId, items, defaultLabel) {
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

  function saleSyncFilterOptions(data) {
    salePopulateFilter('saleFirmSelect', data.firms, 'All Firms');
    salePopulateFilter('saleStoreSelect', data.stores, 'All Store');
  }

  function saleReloadWithFilters() {
    if (saleActiveFrom && saleActiveTo) saleLoadData(saleActiveFrom, saleActiveTo);
  }

  function updateSaleSummary(data) {
    document.getElementById('saleTotalAmount').textContent   = fmt(data.total_amount   || 0);
    document.getElementById('saleTotalReceived').textContent = fmt(data.total_received || 0);
    document.getElementById('saleTotalBalance').textContent  = fmt(data.total_balance  || 0);
    const pct = data.growth_pct != null ? data.growth_pct + '%' : '0%';
    document.getElementById('saleGrowthPct').textContent     = pct;
  }

  /* ── Render Table ── */
  function saleRenderTable() {
    const tbody  = document.getElementById('saleTxnTableBody');
    const total  = saleFiltered.length;
    const start  = (saleCurrentPage - 1) * SALE_PAGE_SIZE;
    const pageRows = saleFiltered.slice(start, start + SALE_PAGE_SIZE);

    if (!total) {
      tbody.innerHTML = `<tr id="saleNoDataRow"><td colspan="8" class="sale-empty-state">
        <i class="fa-solid fa-receipt" style="font-size:36px;color:#d1d5db;display:block;margin-bottom:8px;"></i>
        No sale invoices found for this period.
      </td></tr>`;
      document.getElementById('salePagination').style.display = 'none';
      return;
    }

    tbody.innerHTML = '';
    pageRows.forEach(row => {
      const tr = document.createElement('tr');
      const balDue = parseFloat(row.balance_due || row.balance || 0);
      let badgeHtml = '';
      if (balDue <= 0) {
        badgeHtml = `<span class="sale-badge-paid">Paid</span>`;
      } else if ((row.received_paid || row.received || 0) > 0) {
        badgeHtml = `<span class="sale-badge-partial">Partial</span>`;
      } else {
        badgeHtml = `<span class="sale-badge-unpaid">Unpaid</span>`;
      }

      tr.innerHTML = `
        <td data-column-key="date">${fmtDate(row.invoice_date || row.date)}</td>
        <td data-column-key="invoice_no">${row.bill_number || row.invoice_no || '-'}</td>
        <td data-column-key="party_name">${row.party_name || row.customer_name || '-'}</td>
        <td data-column-key="transaction">Sale</td>
        <td data-column-key="payment_type">${row.payment_type || 'Cash'}</td>
        <td data-column-key="amount">${fmt(row.total_amount || row.amount || 0)}</td>
        <td data-column-key="balance">${fmt(balDue)} ${badgeHtml}</td>
        <td data-column-key="actions">
          <button class="sale-row-print-btn" title="Print" onclick="salePrintRow(${JSON.stringify(row).replace(/"/g,'&quot;')})">
            <i class="fa-solid fa-print"></i>
          </button>
          <button class="sale-row-share-btn" title="Share">
            <i class="fa-solid fa-arrow-up-from-bracket"></i>
          </button>
          <button class="sale-row-more-btn" title="More">
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    /* Pagination */
    const totalPages = Math.ceil(total / SALE_PAGE_SIZE);
    const pagination = document.getElementById('salePagination');
    if (totalPages > 1) {
      pagination.style.display = 'flex';
      document.getElementById('salePaginationInfo').textContent =
        `Showing ${start + 1}–${Math.min(start + SALE_PAGE_SIZE, total)} of ${total}`;
      let pagesHtml = '';
      for (let i = 1; i <= totalPages; i++) {
        pagesHtml += `<button class="sale-page-btn${i === saleCurrentPage ? ' active' : ''}"
          style="${i === saleCurrentPage ? 'background:#eff6ff;color:#2563eb;border-color:#bfdbfe;font-weight:700;' : ''}"
          onclick="saleGoToPage(${i})">${i}</button>`;
      }
      document.getElementById('salePageNumbers').innerHTML = pagesHtml;
    } else {
      pagination.style.display = 'none';
    }
  }

  window.saleGoToPage = function(page) {
    saleCurrentPage = page;
    saleRenderTable();
  };

  /* ── Apply all filters & search ── */
  function saleApplyAllFilters() {
    const keyword = (document.getElementById('saleTxnSearchInput')?.value || '').toLowerCase();

    saleFiltered = saleAllRows.filter(row => {
      // Search
      if (keyword) {
        const haystack = [
          row.invoice_date, row.bill_number, row.party_name,
          row.payment_type, row.total_amount, row.balance_due
        ].join(' ').toLowerCase();
        if (!haystack.includes(keyword)) return false;
      }

      // Date filter
      if (saleColFilters.date) {
        const rowDate = new Date(row.invoice_date || row.date);
        const { type, val, from, to } = saleColFilters.date;
        const filterDate = new Date(val);
        if (type === 'equal' && rowDate.toDateString() !== filterDate.toDateString()) return false;
        if (type === 'lt' && rowDate >= filterDate) return false;
        if (type === 'gt' && rowDate <= filterDate) return false;
        if (type === 'range') {
          if (from && rowDate < new Date(from)) return false;
          if (to   && rowDate > new Date(to))   return false;
        }
      }

      // Invoice filter
      if (saleColFilters.invoice_no) {
        const inv = (row.bill_number || row.invoice_no || '').toLowerCase();
        const { type, val } = saleColFilters.invoice_no;
        if (type === 'exact' && inv !== val.toLowerCase()) return false;
        if (type === 'contains' && !inv.includes(val.toLowerCase())) return false;
      }

      // Party filter
      if (saleColFilters.party_name) {
        const name = (row.party_name || '').toLowerCase();
        const { type, val } = saleColFilters.party_name;
        if (type === 'exact' && name !== val.toLowerCase()) return false;
        if (type === 'contains' && !name.includes(val.toLowerCase())) return false;
      }

      // Amount filter
      if (saleColFilters.amount) {
        const amt = parseFloat(row.total_amount || 0);
        const { min, max } = saleColFilters.amount;
        if (min != null && amt < min) return false;
        if (max != null && amt > max) return false;
      }

      return true;
    });

    // Sort
    if (saleSortState.key) {
      saleFiltered.sort((a, b) => {
        let av = a[saleSortState.key] || 0;
        let bv = b[saleSortState.key] || 0;
        if (typeof av === 'string') av = av.toLowerCase();
        if (typeof bv === 'string') bv = bv.toLowerCase();
        return av < bv ? -saleSortState.dir : av > bv ? saleSortState.dir : 0;
      });
    }

    saleCurrentPage = 1;
    saleRenderTable();
  }

  /* ── Sort ── */
  window.saleSortBy = function(key, btn) {
    if (saleSortState.key === key) {
      saleSortState.dir *= -1;
    } else {
      saleSortState.key = key;
      saleSortState.dir = 1;
    }
    document.querySelectorAll('.sale-th-sort').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    saleApplyAllFilters();
  };

  /* ── Column Filter open/close ── */
  window.saleToggleColFilter = function(btn) {
    const dropdown = btn.nextElementSibling;
    const isOpen = dropdown.classList.contains('open');
    // Close all
    document.querySelectorAll('.sale-col-filter-dropdown.open').forEach(d => {
      d.classList.remove('open');
      d.previousElementSibling?.classList.remove('active');
    });
    if (!isOpen) {
      dropdown.classList.add('open');
      btn.classList.add('active');
    }
  };

  window.saleClearColFilter = function(key, btn) {
    delete saleColFilters[key];
    const dropdown = btn.closest('.sale-col-filter-dropdown');
    dropdown.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]').forEach(i => i.value = '');
    dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    saleApplyAllFilters();
    dropdown.classList.remove('open');
    dropdown.previousElementSibling?.classList.remove('active');
  };

  window.saleApplyColFilter = function(key, btn) {
    const dropdown = btn.closest('.sale-col-filter-dropdown');

    if (key === 'date') {
      const type = document.getElementById('saleFilterDateType')?.value || 'equal';
      const val  = document.getElementById('saleFilterDateVal')?.value;
      const from = document.getElementById('saleFilterDateRangeFrom')?.value;
      const to   = document.getElementById('saleFilterDateRangeTo')?.value;
      if (val || from || to) saleColFilters[key] = { type, val, from, to };
    } else if (key === 'invoice_no') {
      const type = document.getElementById('saleFilterInvoiceType')?.value;
      const val  = document.getElementById('saleFilterInvoiceVal')?.value.trim();
      if (val) saleColFilters[key] = { type, val };
    } else if (key === 'party_name') {
      const type = document.getElementById('saleFilterPartyType')?.value;
      const val  = document.getElementById('saleFilterPartyVal')?.value.trim();
      if (val) saleColFilters[key] = { type, val };
    } else if (key === 'amount') {
      const min = parseFloat(document.getElementById('saleFilterAmountMin')?.value);
      const max = parseFloat(document.getElementById('saleFilterAmountMax')?.value);
      if (!isNaN(min) || !isNaN(max)) {
        saleColFilters[key] = {
          min: isNaN(min) ? null : min,
          max: isNaN(max) ? null : max
        };
      }
    } else if (key === 'balance') {
      const min = parseFloat(document.getElementById('saleFilterBalanceMin')?.value);
      const max = parseFloat(document.getElementById('saleFilterBalanceMax')?.value);
      if (!isNaN(min) || !isNaN(max)) {
        saleColFilters[key] = {
          min: isNaN(min) ? null : min,
          max: isNaN(max) ? null : max
        };
      }
    }

    saleApplyAllFilters();
    dropdown.classList.remove('open');
    dropdown.previousElementSibling?.classList.remove('active');
  };

  /* Show/hide date range input */
  document.getElementById('saleFilterDateType')?.addEventListener('change', function() {
    document.getElementById('saleFilterDateRange').classList.toggle('d-none', this.value !== 'range');
    document.getElementById('saleFilterDateVal').closest('.mb-0, div')?.classList.toggle('d-none', this.value === 'range');
  });

  /* ── Period Selector ── */
  function saleOpenCustomDates() {
    const periodSelect = document.getElementById('salePeriodSelect');
    const customDates  = document.getElementById('saleCustomDates');
    const fromInput    = document.getElementById('saleFromDate');
    const toInput      = document.getElementById('saleToDate');

    if (periodSelect) periodSelect.value = 'custom';
    customDates?.classList.remove('d-none');

    const displayToISO = value => {
      const parts = value.trim().split('/');
      return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : '';
    };

    if (fromInput) {
      fromInput.value = displayToISO(document.getElementById('saleDateFrom')?.textContent || '');
    }
    if (toInput) {
      toInput.value = displayToISO(document.getElementById('saleDateTo')?.textContent || '');
    }

    fromInput?.focus();
  }

  document.getElementById('salePeriodSelect')?.addEventListener('change', function() {
    const val = this.value;
    if (val === 'custom') {
      saleOpenCustomDates();
      return;
    }
    document.getElementById('saleCustomDates').classList.add('d-none');
    const [from, to] = updateDateDisplay(val);
    saleLoadData(from, to);
  });

  document.getElementById('saleDateRangeDisplay')?.addEventListener('click', saleOpenCustomDates);
  document.getElementById('saleDateRangeDisplay')?.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      saleOpenCustomDates();
    }
  });

  document.getElementById('saleApplyDates')?.addEventListener('click', function() {
    const from = document.getElementById('saleFromDate')?.value;
    const to   = document.getElementById('saleToDate')?.value;
    if (!from || !to) return;
    const toDisplayDate = value => value.split('-').reverse().join('/');
    document.getElementById('saleDateFrom').textContent = toDisplayDate(from);
    document.getElementById('saleDateTo').textContent   = toDisplayDate(to);
    document.getElementById('saleCustomDates')?.classList.add('d-none');
    saleLoadData(from, to);
  });

  document.getElementById('saleFirmSelect')?.addEventListener('change', saleReloadWithFilters);
  document.getElementById('saleStoreSelect')?.addEventListener('change', saleReloadWithFilters);

  /* ── Search ── */
  document.getElementById('saleTxnSearchBtn')?.addEventListener('click', function() {
    const bar = document.getElementById('saleTxnSearchBar');
    const isHidden = bar.classList.contains('d-none');
    bar.classList.toggle('d-none', !isHidden);
    if (isHidden) document.getElementById('saleTxnSearchInput')?.focus();
    else {
      if (document.getElementById('saleTxnSearchInput')) {
        document.getElementById('saleTxnSearchInput').value = '';
      }
      saleApplyAllFilters();
    }
  });

  document.getElementById('saleTxnSearchClose')?.addEventListener('click', function() {
    document.getElementById('saleTxnSearchBar').classList.add('d-none');
    document.getElementById('saleTxnSearchInput').value = '';
    saleApplyAllFilters();
  });

  document.getElementById('saleTxnSearchInput')?.addEventListener('input', saleApplyAllFilters);

  /* ── Chart Toggle ── */
  let saleChart = null;
  document.getElementById('saleTxnChartBtn')?.addEventListener('click', function() {
    const panel = document.getElementById('saleTxnChartPanel');
    const isHidden = panel.classList.contains('d-none');
    panel.classList.toggle('d-none', !isHidden);
    if (isHidden) saleInitChart('daily');
  });

  document.querySelectorAll('.sale-chart-period').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.sale-chart-period').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      saleInitChart(this.dataset.period);
    });
  });

  function saleInitChart(period) {
    const canvas = document.getElementById('saleChart');
    if (!canvas) return;

    // Group data by period
    const grouped = {};
    saleFiltered.forEach(row => {
      const d = new Date(row.invoice_date || row.date);
      let key;
      if (period === 'daily')   key = d.toISOString().split('T')[0];
      else if (period === 'weekly') {
        const wn = Math.ceil(d.getDate() / 7);
        key = `Week ${wn} ${d.toLocaleString('default', { month: 'short' })}-${d.getFullYear().toString().slice(-2)}`;
      }
      else if (period === 'monthly') key = d.toLocaleString('default', { month: 'short', year: '2-digit' });
      else key = d.getFullYear().toString();

      grouped[key] = (grouped[key] || 0) + parseFloat(row.total_amount || 0);
    });

    const labels = Object.keys(grouped);
    const values = Object.values(grouped);

    if (saleChart) saleChart.destroy();

    if (typeof Chart === 'undefined') {
      // Load Chart.js dynamically
      const script = document.createElement('script');
      script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js';
      script.onload = () => saleDrawChart(canvas, labels, values);
      document.head.appendChild(script);
    } else {
      saleDrawChart(canvas, labels, values);
    }
  }

  function saleDrawChart(canvas, labels, values) {
    saleChart = new Chart(canvas, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Sales',
          data: values,
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37,99,235,0.06)',
          borderWidth: 2,
          pointBackgroundColor: '#2563eb',
          pointRadius: 4,
          tension: 0.3,
          fill: true,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, maxRotation: 45 } },
          y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } } }
        }
      }
    });
  }

  /* ── Excel Export Modal ── */
  document.getElementById('saleTxnExcelBtn')?.addEventListener('click', () => {
    document.getElementById('saleExcelModal').style.display = 'flex';
  });
  document.getElementById('saleExcelModalClose')?.addEventListener('click', () => {
    document.getElementById('saleExcelModal').style.display = 'none';
  });
  document.getElementById('saleExcelModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });

  document.getElementById('saleExcelGenerateBtn')?.addEventListener('click', function() {
    const selectedCols = Array.from(document.querySelectorAll('.sale-export-col:checked')).map(c => c.value);
    saleExportCSV(selectedCols);
    document.getElementById('saleExcelModal').style.display = 'none';
  });

  function saleExportCSV(cols) {
    const colMap = {
      date: 'Date', invoice_no: 'Invoice No', party_name: 'Party Name',
      total: 'Total', payment_type: 'Payment Type', received_paid: 'Received/Paid',
      balance_due: 'Balance Due', payment_status: 'Payment Status',
      order_number: 'Order Number', party_phone: "Party's Phone No.",
      description: 'Description', item_details: 'Item Details'
    };
    const headers = cols.map(c => colMap[c] || c);
    const lines = [headers.join(',')];

    saleFiltered.forEach(row => {
      const vals = cols.map(c => {
        if (c === 'date') return fmtDate(row.invoice_date || row.date);
        if (c === 'invoice_no') return row.bill_number || row.invoice_no || '-';
        if (c === 'party_name') return row.party_name || '-';
        if (c === 'total') return parseFloat(row.total_amount || 0).toFixed(2);
        if (c === 'payment_type') return row.payment_type || 'Cash';
        if (c === 'received_paid') return parseFloat(row.received_paid || row.received || 0).toFixed(2);
        if (c === 'balance_due') return parseFloat(row.balance_due || row.balance || 0).toFixed(2);
        if (c === 'party_phone') return row.party_phone || '-';
        return '-';
      });
      lines.push(vals.map(v => `"${String(v).replace(/"/g,'""')}"`).join(','));
    });

    const blob = new Blob(['\uFEFF' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    const period = document.getElementById('salePeriodSelect').value;
    const [from, to] = getDateRange(period);
    const fromStr = toISODate(from).replace(/-/g,'');
    const toStr   = toISODate(to).replace(/-/g,'');
    a.download = `SaleReport_${fromStr}_to_${toStr}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  /* ── Print Modal ── */
  document.getElementById('saleTxnPrintBtn')?.addEventListener('click', () => {
    document.getElementById('salePrintModal').style.display = 'flex';
  });
  document.getElementById('salePrintModalClose')?.addEventListener('click', () => {
    document.getElementById('salePrintModal').style.display = 'none';
  });
  document.getElementById('salePrintModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });

  document.getElementById('salePrintGenerateBtn')?.addEventListener('click', function() {
    const cols = Array.from(document.querySelectorAll('.sale-print-col:checked')).map(c => c.value);
    document.getElementById('salePrintModal').style.display = 'none';
    saleBuildPrintPreview(cols);
  });

  function saleBuildPrintPreview(cols) {
    const colMap = {
      date: { label: 'DATE', fn: r => fmtDate(r.invoice_date || r.date) },
      invoice_no: { label: 'INVOICE NO.', fn: r => r.bill_number || r.invoice_no || '-' },
      party_name: { label: 'PARTY NAME', fn: r => r.party_name || '-' },
      total: { label: 'TOTAL', fn: r => fmt(r.total_amount || 0) },
      payment_type: { label: 'PAYMENT TYPE', fn: r => r.payment_type || 'Cash' },
      transaction: { label: 'TRANSACTION TYPE', fn: () => 'Sale' },
      received_paid: { label: 'RECEIVED / PAID', fn: r => fmt(r.received_paid || r.received || 0) },
      balance_due: { label: 'BALANCE DUE', fn: r => fmt(r.balance_due || r.balance || 0) },
    };
    const activeCols = cols.filter(c => colMap[c]);
    const thHtml = activeCols.map(c => `<th>${colMap[c].label}</th>`).join('');
    const rowsHtml = saleFiltered.map(row =>
      `<tr>${activeCols.map(c => `<td>${colMap[c].fn(row)}</td>`).join('')}</tr>`
    ).join('');

    const totalSale = saleFiltered.reduce((s, r) => s + parseFloat(r.total_amount || 0), 0);
    const fromDisp = document.getElementById('saleDateFrom').textContent;
    const toDisp   = document.getElementById('saleDateTo').textContent;

    /* Company info from page if available */
    const companyName = document.querySelector('.company-name')?.textContent?.trim() || 'Company';
    const companyPhone = '';

    document.getElementById('salePrintPreviewBody').innerHTML = `
      <div class="sale-print-preview">
        <h2>${companyName}</h2>
        ${companyPhone ? `<p class="sp-sub">Phone no.: ${companyPhone}</p>` : ''}
        <p class="sp-report-title">Sale Report</p>
        <p class="sp-duration"><strong>Duration: From ${fromDisp} to ${toDisp}</strong></p>
        <table>
          <thead><tr>${thHtml}</tr></thead>
          <tbody>${rowsHtml}</tbody>
        </table>
        <p class="sp-total-row">Total Sale: ${fmt(totalSale)}</p>
        <p class="sp-generated">Generated on ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
      </div>
    `;
    document.getElementById('salePrintPreviewModal').style.display = 'flex';
  }

  /* Preview actions */
  document.getElementById('salePreviewModalClose')?.addEventListener('click', () => {
    document.getElementById('salePrintPreviewModal').style.display = 'none';
  });
  document.getElementById('salePreviewClose')?.addEventListener('click', () => {
    document.getElementById('salePrintPreviewModal').style.display = 'none';
  });
  document.getElementById('salePrintPreviewModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });

  document.getElementById('salePreviewPrint')?.addEventListener('click', function() {
    const w = window.open('', '_blank', 'width=900,height=700');
    if (!w) return;
    w.document.write(`<html><head><title>Sale Report</title>
      <style>
        body{font-family:Arial,sans-serif;padding:20px;color:#1f2937;}
        h2{text-align:center;margin-bottom:2px;}
        .sub{text-align:center;font-size:12px;color:#6b7280;}
        .title{text-align:center;font-size:18px;font-weight:700;text-decoration:underline;margin:8px 0;}
        .dur{font-weight:700;margin-bottom:12px;}
        table{width:100%;border-collapse:collapse;font-size:12px;}
        th,td{border:1px solid #e5e7eb;padding:8px 10px;text-align:left;}
        th{background:#f3f4f6;font-weight:700;}
        .tot{text-align:right;font-weight:700;padding-top:12px;}
        .gen{font-size:11px;color:#9ca3af;margin-top:10px;}
      </style></head><body>
      ${document.getElementById('salePrintPreviewBody').innerHTML}
      </body></html>`);
    w.document.close();
    w.focus();
    w.print();
  });

  /* Row print */
  window.salePrintRow = function(row) {
    alert('Row print for invoice: ' + (row.bill_number || row.invoice_no || '-'));
  };

  /* Add Sale redirect */
  document.getElementById('saleAddSaleBtn')?.addEventListener('click', function() {
    window.location.href = '/dashboard/sale/create';
  });

  /* Close dropdowns on outside click */
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.sale-th-filter-wrap')) {
      document.querySelectorAll('.sale-col-filter-dropdown.open').forEach(d => {
        d.classList.remove('open');
        d.previousElementSibling?.classList.remove('active');
      });
    }
    if (!e.target.closest('.sale-period-pill') && !e.target.closest('#saleCustomDates')) {
      // keep custom dates visible if custom is selected
    }
  });

  /* ── INIT ── */
  document.addEventListener('DOMContentLoaded', function() {
    const [from, to] = updateDateDisplay('last_month');
    saleLoadData(from, to);
  });

  // Also init if DOMContentLoaded already fired
  if (document.readyState !== 'loading') {
    const [from, to] = updateDateDisplay('last_month');
    saleLoadData(from, to);
  }

})();
</script>
