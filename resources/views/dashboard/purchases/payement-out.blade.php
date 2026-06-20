<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Payment Out — Vyapar</title>
  @php
    $authUser = Auth::user();
    $authUserRoles = $authUser?->roles()->pluck('name')->toArray() ?? [];
    $authUserPermissions = $authUser?->getAllPermissions() ?? [];
    $authUserPayload = $authUser ? [
      'id' => $authUser->id,
      'name' => $authUser->name,
      'roles' => $authUserRoles,
      'permissions' => $authUserPermissions,
    ] : null;
  @endphp
  <script>
    window.App = window.App || {
      isAuthenticated: {{ Auth::check() ? 'true' : 'false' }},
      user: @json($authUserPayload),
      csrfToken: '{{ csrf_token() }}',
      logoutUrl: '{{ route('logout') }}'
    };
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('css/payment.css') }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
  <style>
    .report-options-panel{
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:14px;
      padding:14px;
      box-shadow:0 10px 28px rgba(15,23,42,.08);
    }
    .report-options-title{
      font-size:14px;
      font-weight:700;
      margin-bottom:10px;
      color:#111827;
    }
    .report-option-check{
      display:flex;
      align-items:center;
      gap:8px;
      font-size:13px;
      color:#374151;
      margin-bottom:8px;
      user-select:none;
    }
    .report-preview-card{
      background:#fff;
      border:0;
      border-radius:0;
      overflow:hidden;
      min-height:0;
      height:100%;
      display:flex;
      flex-direction:column;
    }
    .report-preview-head{
      display:flex;
      justify-content:center;
      align-items:flex-start;
      padding:18px 18px 6px;
      background:#fff;
      position:relative;
    }
    .report-preview-head .report-range{position:absolute; right:18px; top:18px;}
    .report-company-name{font-size:18px;font-weight:700;color:#111827;}
    .report-company-sub{font-size:12px;color:#6b7280;margin-top:3px;}
    .report-range{font-size:13px;color:#111827;font-weight:600;}
    .report-preview-summary{
      padding:0 18px 10px;
      display:grid;
      gap:10px;
      color:#111827;
      font-size:15px;
      font-weight:600;
    }
    .report-preview-summary-line{
      line-height:1.35;
    }
    .report-preview-table-wrap{
      padding:10px 18px 0;
      overflow:auto;
      flex:1;
    }
    .report-preview-table{
      width:100%;
      border-collapse:collapse;
      font-size:12px;
      background:#fff;
    }
    .report-preview-table th,
    .report-preview-table td{
      border:1px solid #d1d5db;
      padding:8px 10px;
      vertical-align:top;
    }
    .report-preview-table th{
      background:#f3f4f6;
      font-weight:700;
      color:#111827;
    }
    .report-options-modal .modal-dialog{
      max-width:420px;
    }
    .report-options-modal .modal-content{
      border-radius:18px;
      overflow:hidden;
      border:1px solid #e5e7eb;
      box-shadow:0 18px 40px rgba(15,23,42,.18);
    }
    .report-options-modal .modal-body{
      background:#f8fafc;
      padding:18px;
    }
    .report-options-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px 18px;
    }
    .report-options-grid .report-option-check{
      margin-bottom:0;
    }
    .report-options-footer{
      display:flex;
      justify-content:flex-end;
      align-items:center;
      gap:10px;
      padding:0 18px 18px;
    }
    .report-preview-modal .modal-content{
      border-radius:14px;
      overflow:hidden;
      border:1px solid #d1d5db;
      box-shadow:0 20px 45px rgba(15,23,42,.20);
      height:calc(100vh - 40px);
      max-height:880px;
    }
    .report-preview-modal .modal-dialog{
      max-width:1240px;
      width:min(1240px, calc(100vw - 32px));
    }
    .report-preview-modal .modal-body{
      background:#fff;
      padding:0;
      display:flex;
      flex-direction:column;
    }
    .report-preview-modal .modal-header{
      border-bottom:1px solid #e5e7eb;
      background:#fff;
      padding:14px 18px;
    }
    .report-preview-modal .modal-footer{
      border-top:1px solid #e5e7eb;
      background:#fff;
      padding:14px 18px 16px;
    }
    .link-payment-modal .modal-dialog{
      max-width:800px;
    }
    .link-payment-modal .modal-content{
      border:0;
      border-radius:16px;
      overflow:hidden;
      box-shadow:0 24px 60px rgba(15,23,42,.22);
    }
    .link-payment-header{
      display:flex;
      justify-content:space-between;
      gap:20px;
      align-items:flex-start;
      padding:12px 0 16px;
      border-bottom:1px solid #e5e7eb;
      margin-bottom:14px;
    }
    .link-payment-summary{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:16px 28px;
      align-items:end;
    }
    .link-payment-label{
      font-size:12px;
      color:#0ea5e9;
      font-weight:700;
      display:block;
      margin-bottom:5px;
    }
    .link-payment-value{
      font-size:16px;
      font-weight:700;
      color:#111827;
    }
    .link-payment-tools{
      display:flex;
      align-items:center;
      gap:10px;
      margin-left:auto;
    }
    .link-payment-grid-wrap{
      max-height:430px;
      overflow:auto;
      border:1px solid #e5e7eb;
      border-radius:12px;
    }
    .link-payment-grid thead th{
      background:#fafafa;
      color:#6b7280;
      font-size:13px;
      font-weight:600;
      border-bottom:1px solid #e5e7eb;
      position:sticky;
      top:0;
      z-index:2;
      white-space:nowrap;
    }
    .link-payment-grid tbody td{
      font-size:13px;
      white-space:nowrap;
      vertical-align:middle;
    }
    .link-payment-empty{
      text-align:center;
      color:#9ca3af;
      padding:22px !important;
    }
    .unused-amount-negative{
      color:#dc2626;
    }
    .link-payment-modal .modal-footer{
      border-top:1px solid #e5e7eb;
      background:#fff;
    }
    .link-payment-total{
      font-weight:700;
      color:#111827;
    }
    .link-payment-mini-btn{
      min-width:72px;
    }
    .report-action-btn{
      border-radius:999px;
      padding:8px 18px;
      border:1px solid #e36a7c;
      color:#e36a7c;
      background:#fff;
      font-weight:500;
    }
    .report-action-btn:hover{
      background:#fff5f7;
      border-color:#d9485e;
      color:#d9485e;
    }
    .report-action-btn.primary{
      background:#ef4444;
      border-color:#ef4444;
      color:#fff;
    }
    .report-action-btn.primary:hover{
      background:#dc2626;
      border-color:#dc2626;
      color:#fff;
    }
    .report-preview-sheet{
      display:flex;
      flex-direction:column;
      height:100%;
      min-height:0;
    }
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body data-page="payment-out">

  <!-- MAIN -->
  <main class="main-content" id="mainContent">

    <div class="page-header">
      <div class="page-title-wrap" id="titleWrap">
        <span class="page-title">Payment Out</span>
        <i class="fa-solid fa-chevron-down page-title-chevron"></i>
        <div class="title-dropdown" id="titleDropdown">
          <a href="#">Sale Invoice</a>
          <a href="#">Estimate / Quotation</a>
          <a href="#">Sale Return / Cr. Note</a>
          <a href="#">Payment In</a>
          <a href="#" class="active">Payment Out</a>
          <a href="#">Purchase Bill</a>
          <a href="#">Purchase Return / Dr. Note</a>
          <a href="#">Expenses</a>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <button class="btn-add" onclick="openModal()"><i class="fa-solid fa-plus"></i> Add Payment-out</button>
        <button class="btn-icon" title="Settings"><i class="fa-solid fa-gear"></i></button>
      </div>
    </div>

    <div class="filter-bar">
      <div class="filter-left">
        <span class="filter-label">Filter By:</span>
        <div class="filter-pill">
          <select id="periodFilter" onchange="toggleCustomDateControls(); applyFilters()">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_year">This Year</option>
            <option value="custom">Custom Date</option>
          </select>
          <div class="pill-divider"></div>
          <span class="date-range-text" id="dateRangeLabel">All dates</span>
        </div>
        <div id="customDateRangeWrap" class="filter-pill" style="display:none; gap:8px; align-items:center;">
          <input type="date" id="customStartDate" onchange="applyFilters()" style="border:1px solid #d1d5db; border-radius:8px; padding:4px 8px; font-size:12px;">
          <span style="font-size:12px; color:#6b7280;">to</span>
          <input type="date" id="customEndDate" onchange="applyFilters()" style="border:1px solid #d1d5db; border-radius:8px; padding:4px 8px; font-size:12px;">
        </div>
        <div class="filter-pill">
          <i class="fa-solid fa-building pill-icon"></i>
          <select id="firmFilter" onchange="applyFilters()">
            <option value="">All Firms</option>
          </select>
        </div>
      </div>
    </div>

    <div class="summary-section">
      <div class="summary-card">
        <div class="summary-top">
          <div>
            <div class="summary-label">Total Amount</div>
            <div class="summary-value" id="totalAmount">Rs 0.00</div>
          </div>
          <div>
            <div class="summary-badge">100% <i class="fa-solid fa-arrow-up-right"></i></div>
            <div class="summary-vs">vs last month</div>
          </div>
        </div>
        <div class="summary-bottom">
          <div class="summary-stat">Paid: <span id="paidAmount">Rs 0.00</span></div>
          <div class="summary-stat">Linked Bills: <span id="linkedBills">0</span></div>
        </div>
      </div>
    </div>

    <div class="transactions-card">
      <div class="transactions-header">
        <div class="transactions-title">Transactions</div>
        <div class="transactions-tools">
          <div class="search-wrap" id="tableSearchWrap" hidden>
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="tableSearchInput" placeholder="Search..." aria-label="Search table">
          </div>
          <button class="tool-btn" id="searchToggleBtn" type="button" title="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
          <button class="tool-btn" type="button" title="Export to Excel" onclick="openReportOptions('excel')"><i class="fa-solid fa-file-excel" style="color:#217346;"></i></button>
          <button class="tool-btn" type="button" title="Print" onclick="openReportOptions('print')"><i class="fa-solid fa-print"></i></button>
        </div>
      </div>
      <div class="table-wrapper">
        <table class="custom-table"
               id="mainTable"
               data-column-drag="native"
               data-column-drag-storage="vyapar.payment-out.transactions.column-order.v1">
          <colgroup>
            <col id="col-date"    style="width:120px">
            <col id="col-ref"     style="width:90px">
            <col id="col-party"   style="width:160px">
            <col id="col-total"   style="width:130px">
            <col id="col-paid"    style="width:130px">
            <col id="col-type"    style="width:120px">
            <col id="col-status"  style="width:100px">
            <col id="col-actions" style="width:110px">
          </colgroup>
          <thead>
            <tr>
              <th data-column-key="date">
                <div class="col-header">Date <button class="col-filter-btn" data-col="date" onclick="toggleColFilter(this,'date',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-date"></div>
              </th>
              <th data-column-key="reference">
                <div class="col-header">Ref. No. <button class="col-filter-btn" data-col="refNo" onclick="toggleColFilter(this,'refNo',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-ref"></div>
              </th>
              <th data-column-key="party">
                <div class="col-header">Party Name <button class="col-filter-btn" data-col="party" onclick="toggleColFilter(this,'party',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-party"></div>
              </th>
              <th data-column-key="amount">
                <div class="col-header">Total Amount <button class="col-filter-btn" data-col="amount" onclick="toggleColFilter(this,'amount',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-total"></div>
              </th>
              <th data-column-key="paid">
                <div class="col-header">Paid <button class="col-filter-btn" data-col="paid" onclick="toggleColFilter(this,'paid',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-paid"></div>
              </th>
              <th data-column-key="payment_type">
                <div class="col-header">Payment Type <button class="col-filter-btn" data-col="payType" onclick="toggleColFilter(this,'payType',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-type"></div>
              </th>
              <th data-column-key="status">
                <div class="col-header">Status <button class="col-filter-btn" data-col="status" onclick="toggleColFilter(this,'status',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-status"></div>
              </th>
              <th style="text-align:center;" data-column-key="actions">Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody">
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- ── COLUMN FILTER POPUP (one shared, repositioned on open) ── -->
  <div class="col-filter-popup" id="colFilterPopup">
    <input type="text" id="colFilterInput" placeholder="Filter…" oninput="liveColFilter()">
    <div class="col-filter-popup-footer">
      <button class="col-filter-clear-btn" onclick="clearColFilter()">Clear</button>
      <button class="col-filter-apply-btn" onclick="applyColFilter()">Apply</button>
    </div>
  </div>

  <div class="modal fade report-options-modal" id="paymentOutPrintOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="paymentOutOptionsTitle">Select Print Options</h5>
          <button type="button" class="btn-close" aria-label="Close" onclick="closeReportOptions()"></button>
        </div>
        <div class="modal-body">
          <div class="report-options-panel" style="box-shadow:none;border:0;padding:0;background:transparent;">
            <div class="report-options-title">Select Columns</div>
            <div class="report-options-grid">
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="date" checked> Date</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="receiptNo" checked> Invoice No.</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="party" checked> Party Name</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="total" checked> Total</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="paid" checked> Payment Total</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="received" checked> Received / Paid</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="balance" checked> Balance Due</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="items" checked> Items Details</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="description" checked> Description</label>
              <label class="report-option-check"><input type="checkbox" class="report-col-check" value="status" checked> Payment Status</label>
            </div>
          </div>
        </div>
        <div class="report-options-footer">
          <button type="button" class="btn btn-outline-secondary" onclick="closeReportOptions()">Close</button>
          <button type="button" class="btn btn-primary" id="paymentOutOptionsActionBtn" onclick="applyReportOptions()">Get Print</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade report-preview-modal" id="paymentOutReportPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Preview</h5>
          <button type="button" class="btn-close" aria-label="Close" onclick="closeReportPreview()"></button>
        </div>
        <div class="modal-body">
          <div class="report-preview-card report-preview-sheet">
            <div class="report-preview-head">
              <div class="report-company">
                <div class="report-company-name">{{ config('app.name', 'Vyapar') }}</div>
                <div class="report-company-sub">Phone no.: {{ config('app.phone', '3362500666') }}</div>
              </div>
              <div class="report-range" id="paymentOutReportRange">All dates</div>
            </div>
            <div class="report-preview-summary">
              <div class="report-preview-summary-line">Party name: <span id="paymentOutReportPartyLabel">All Parties</span></div>
              <div class="report-preview-summary-line">Transaction type: Payment-Out</div>
              <div class="report-preview-summary-line">Duration: <span id="paymentOutReportDurationLabel">All dates</span></div>
            </div>
            <div class="report-preview-table-wrap">
              <table class="report-preview-table" id="paymentOutReportTable">
                <thead id="paymentOutReportThead"></thead>
                <tbody id="paymentOutReportTbody"></tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="justify-content:flex-end; gap:10px;">
          <button type="button" class="report-action-btn" onclick="openReportPdf()">Open PDF</button>
          <button type="button" class="report-action-btn" onclick="printReport()">Print</button>
          <button type="button" class="report-action-btn" onclick="saveReportPdf()">Save PDF</button>
          <button type="button" class="report-action-btn" onclick="emailReportPdf()">Email PDF</button>
          <button type="button" class="report-action-btn primary" onclick="closeReportPreview()">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════
       PAYMENT-OUT MODAL
  ═══════════════════════════════════════ -->
  <div class="modal-overlay" id="addModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title">Payment-Out</span>
        <div class="modal-header-actions">
          <button class="modal-header-btn" title="Calculator"><i class="fa-solid fa-calculator"></i></button>
          <button class="modal-header-btn" title="Settings"><i class="fa-solid fa-gear"></i></button>
          <button class="modal-header-btn" onclick="closeModal()" title="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>
      </div>

      <div class="modal-body">
        <div class="modal-columns">

          <!-- LEFT -->
          <div class="modal-col-left">

            <div class="po-field-wrap" id="partyFieldWrap">
              <span class="po-field-label-static">Party <span class="po-required">*</span></span>
              <div style="position:relative; width: 200px;">
                <div id="partyDisplayBox" onclick="togglePartyDropdown()">
                  <span id="partyDisplayText" style="color:#bbb;font-size:13.5px;"></span>
                </div>
                <i class="fa-solid fa-chevron-down po-chevron" id="partyChevron"></i>
                <div class="party-dropdown-list" id="partyDropdownList">
                  <div class="party-add-btn" onclick="addNewParty()">
                    <i class="fa-solid fa-circle-plus"></i> Add Party
                  </div>
                  <div class="party-list-header">
                    <span></span><span>Party Balance</span>
                  </div>
                  <div id="partyListItems"></div>
                </div>
              </div>
            </div>

            <div class="party-balance-line" id="partyBalanceLine">
              BAL: <span id="partyBalanceVal">0</span>
            </div>

            <div id="simplePaymentRow" class="simple-payment-row">
              <div class="simple-pay-type-wrap">
                <label class="simple-pay-label">Payment Type</label>
                <select class="po-select-sm" id="simplePayType">
                  <option value="Cash">Cash</option>
                  <option value="Bank">Bank</option>
                  <option value="Cheque">Cheque</option>
                  <option value="UPI">UPI</option>
                </select>
              </div>
            </div>

            <div id="addPayTypeLinkWrap" style="margin-bottom:12px;">
              <button class="add-payment-type-btn" onclick="activatePaymentBox()">
                <i class="fa-solid fa-plus" style="font-size:10px;"></i>
                Add Payment type
              </button>
            </div>

            <div class="payment-section-box" id="paymentSection" style="display:none;">
              <div id="paymentRows"></div>
              <input type="text" class="po-ref-input" id="refNoInput" placeholder="dddd No." style="display:none;">
              <input type="hidden" id="linkedRowsJson" value="[]">
              <div class="payment-section-footer">
                <button class="add-payment-type-btn" onclick="addPaymentRow()">
                  <i class="fa-solid fa-plus" style="font-size:10px;"></i>
                  Add Payment type
                </button>
                <span class="total-payment-line" id="totalPaymentLine">Total payment: 0</span>
              </div>
            </div>

            <div id="descriptionWrap" style="margin-bottom:10px;">
              <button class="po-desc-btn" id="descriptionBtn" onclick="toggleDescription()" type="button">
                <i class="fa-solid fa-file-lines"></i> ADD DESCRIPTION
              </button>
              <textarea id="descriptionArea" rows="3" placeholder="Add description..."
                style="display:none;width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px;font-size:13px;outline:none;resize:vertical;margin-bottom:10px;"></textarea>
            </div>

            <button class="po-camera-btn" title="Add photo" type="button" onclick="triggerPaymentOutImagePicker()">
              <i class="fa-solid fa-camera"></i>
            </button>
            <input type="file" id="paymentOutImageInput" accept="image/*" multiple hidden>
            <div id="paymentOutImagePreview" class="mt-2 d-flex flex-wrap gap-2"></div>

          </div><!-- /left -->

          <!-- RIGHT -->
          <div class="modal-col-right">
            <div class="po-right-row">
              <span class="po-right-label">Receipt No</span>
              <input type="text" class="po-right-input" id="modalReceiptNo" value="1">
            </div>
            <div class="po-right-row">
              <span class="po-right-label">Date</span>
              <div style="display:flex;align-items:center;gap:6px;">
                <input type="date" class="po-right-input" id="modalDate" style="width:130px;">
              </div>
            </div>
            <div class="po-paid-section">
              <div class="po-paid-row">
                <span class="po-paid-label">Paid</span>
                <div class="po-paid-box" id="paidDisplay">0</div>
              </div>
            </div>

            <div class="po-paid-section" style="margin-top:8px;">
              <div class="po-paid-row">
                <span class="po-paid-label">Discount</span>
                <input type="number" class="po-right-input" id="paymentDiscountInput" min="0" step="0.01" value="0" style="width:140px;" oninput="updatePaymentSummary()">
              </div>
              <div class="po-paid-row" style="margin-top:10px;">
                <span class="po-paid-label">Total</span>
                <input type="text" class="po-right-input" id="paymentTotalInput" value="0" readonly style="width:140px;background:#f8fafc;">
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="link-payment-btn" id="linkPaymentBtn" style="display:none;" type="button" onclick="openLinkPaymentModal()">
          LINK PAYMENT <span class="lp-question">?</span>
        </button>
        <div style="flex:1;"></div>
        <div class="modal-footer-right">
          <button class="btn-share">Share <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i></button>
          <button class="btn-save" onclick="savePaymentOut()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade link-payment-modal" id="linkPaymentModal" tabindex="-1" aria-labelledby="linkPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content border-0">
        <div class="modal-header">
          <h5 class="modal-title" id="linkPaymentModalLabel">Link Payment to Txns</h5>
          <button type="button" class="btn-close" aria-label="Close" onclick="closeLinkPaymentModal()"></button>
        </div>
        <div class="modal-body">
          <div class="link-payment-header">
            <div class="link-payment-summary">
              <div>
                <span class="link-payment-label">Party</span>
                <div class="link-payment-value" id="linkPaymentPartyName">-</div>
              </div>
              <div>
                <span class="link-payment-label">Paid Amount</span>
                <div class="input-group">
                  <input type="number" class="form-control" id="linkPaymentReceivedInput" min="0" step="0.01">
                  <span class="input-group-text"><i class="fa-solid fa-pen"></i></span>
                </div>
              </div>
            </div>

            <div class="link-payment-tools">
              <button type="button" class="btn btn-info text-white link-payment-mini-btn" id="linkPaymentAutoBtn">AUTO LINK</button>
              <button type="button" class="btn btn-light" id="linkPaymentResetBtn" title="Reset">
                <i class="fa-solid fa-rotate-right"></i>
              </button>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
            <select class="form-select" id="linkPaymentTypeFilter" style="max-width:280px;">
              <option value="all">All transactions</option>
            </select>
            <input type="text" class="form-control" id="linkPaymentSearch" placeholder="Search transaction" style="max-width:290px;">
          </div>

          <div class="link-payment-grid-wrap">
            <table class="table mb-0 link-payment-grid">
              <thead>
                <tr>
                  <th style="width:54px;"></th>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Ref/Inv No.</th>
                  <th class="text-end">Total</th>
                  <th class="text-end">Balance</th>
                  <th style="width:180px;">Linked Amount</th>
                </tr>
              </thead>
              <tbody id="linkPaymentRows">
                <tr>
                  <td colspan="7" class="link-payment-empty">Select a party to load transactions.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <div class="fw-semibold">
            Unused Amount :
            <span id="linkPaymentUnusedAmount" class="link-payment-total">0</span>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" onclick="closeLinkPaymentModal()">Cancel</button>
            <button type="button" class="btn btn-primary" id="linkPaymentDoneBtn">Done</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- HISTORY MODAL -->
  <div class="modal-overlay" id="historyModal">
    <div class="history-modal-box">
      <div class="modal-header">
        <span class="modal-title"><i class="fa-solid fa-clock-rotate-left" style="margin-right:8px;"></i>Payment Out History</span>
        <button class="modal-header-btn" onclick="closeHistoryModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body" id="historyModalBody"></div>
      <div class="modal-footer" style="justify-content:flex-end;">
        <button class="btn btn-secondary" onclick="closeHistoryModal()"><i class="fa-solid fa-xmark" style="margin-right:6px;"></i>Close</button>
      </div>
    </div>
  </div>

  @include('components.modals.party-modal')
  @include('components.bank-account-modal')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}"></script>
  <script src="{{ asset('js/bank-account-modal.js') }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>


  <script>
    /* ═════════════════════
       DATA
    ═════════════════════ */
    const RAW_PARTIES = @json(($parties ?? collect())->values());
    const RAW_BROKERS = @json(($brokers ?? collect())->values());
    const RAW_ITEMS = @json(($items ?? collect())->values());
    const BANK_ACCOUNTS = @json(($bankAccounts ?? collect())->values());
    let payments = @json(($paymentOutRows ?? collect())->values());
    let nextId = 1;
    let nextReceiptNo = 1;
    let editingId = null;
    let paymentBoxActive = false;
    let tableSearchQuery = '';
    let selectedPartyId = null;
    let selectedBrokerId = null;
    let selectedItemId = null;
    let selectedEntityType = 'party';
    let selectedEntityName = '';
    let selectedBankAccountId = null;
    let paymentImageFiles = [];
    let linkPaymentRows = [];
    let appliedLinkPaymentRows = [];
    let paymentOutReportMode = 'print';
    let paymentOutReportRows = [];
    let paymentOutReportScopeId = null;

    function getNextReceiptNumber() {
      const numbers = payments.map(p => parseInt(String(p.receiptNo || '').replace(/[^0-9]/g, ''), 10)).filter(n => !Number.isNaN(n));
      const maxNumber = numbers.length ? Math.max(...numbers) : 0;
      return maxNumber + 1;
    }

    function formatBalanceValue(value) {
      const amount = parseFloat(value || 0) || 0;
      return amount.toFixed(2);
    }

    function getPartyBalanceClass(value) {
      const amount = parseFloat(value || 0) || 0;
      return amount >= 0 ? 'green' : 'red';
    }

    function renderPaymentTypeOptions(selectedValue = '') {
      const bankOptions = BANK_ACCOUNTS.map(bank => {
        const label = bank.display_with_account || bank.display_name || 'Bank Account';
        return `<option value="bank:${bank.id}"${selectedValue === `bank:${bank.id}` ? ' selected' : ''}>${label}</option>`;
      }).join('');

      return `
        <option value="Cash"${selectedValue === 'Cash' ? ' selected' : ''}>Cash</option>
        <option value="Cheque"${selectedValue === 'Cheque' ? ' selected' : ''}>Cheque</option>
        ${bankOptions}
        <option value="add_bank">+ Add Bank A/C</option>
      `;
    }

    function renderDropdownSection(title, items, renderer) {
      if (!items.length) return '';
      return `
        <div class="party-list-header" style="padding:8px 12px 6px;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.04em;">
          <span>${title}</span><span></span>
        </div>
        ${items.map(renderer).join('')}
      `;
    }

    function getPaymentOutPaidAmount() {
      return parseFloat(document.getElementById('paidDisplay')?.textContent || 0) || 0;
    }

    function formatLinkPaymentCurrency(value) {
      return Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getLinkPaymentModalInstance() {
      const modalElement = document.getElementById('linkPaymentModal');
      return modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
    }

    function resetLinkPaymentState() {
      linkPaymentRows = [];
      appliedLinkPaymentRows = [];
      const hiddenField = document.getElementById('linkedRowsJson');
      if (hiddenField) hiddenField.value = '[]';
      const tbody = document.getElementById('linkPaymentRows');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">Select a party to load transactions.</td></tr>';
      }
      const unusedEl = document.getElementById('linkPaymentUnusedAmount');
      if (unusedEl) {
        unusedEl.textContent = formatLinkPaymentCurrency(0);
        unusedEl.classList.remove('unused-amount-negative');
      }
    }

    function calculateLinkedTotal() {
      return linkPaymentRows.reduce((sum, row) => sum + (parseFloat(row.selected_amount || 0) || 0), 0);
    }

    function refreshLinkPaymentSummary() {
      const receivedAmount = getPaymentOutPaidAmount();
      const linkedTotal = calculateLinkedTotal();
      const unusedAmount = receivedAmount - linkedTotal;
      const unusedEl = document.getElementById('linkPaymentUnusedAmount');

      if (unusedEl) {
        unusedEl.textContent = formatLinkPaymentCurrency(unusedAmount);
        unusedEl.classList.toggle('unused-amount-negative', unusedAmount < 0);
      }
    }

    function persistAppliedLinkRows() {
      const cleaned = linkPaymentRows
        .filter(row => (parseFloat(row.selected_amount || 0) || 0) > 0)
        .map(row => ({
          purchase_id: row.purchase_id,
          amount: Number(parseFloat(row.selected_amount).toFixed(2)),
        }));

      appliedLinkPaymentRows = cleaned;
      const hiddenField = document.getElementById('linkedRowsJson');
      if (hiddenField) hiddenField.value = JSON.stringify(cleaned);
    }

    function applyLinkPaymentTypeFilter() {
      const filter = (document.getElementById('linkPaymentTypeFilter')?.value || 'all').toLowerCase();
      const rows = linkPaymentRows.filter((row) => {
        if (filter === 'all') return true;
        return String(row.type || '').toLowerCase() === filter;
      });
      const tbody = document.getElementById('linkPaymentRows');
      if (!tbody) return;

      const search = (document.getElementById('linkPaymentSearch')?.value || '').trim().toLowerCase();
      const filteredRows = rows.filter((row) => {
        const haystack = `${row.date} ${row.type} ${row.ref_no}`.toLowerCase();
        return !search || haystack.includes(search);
      });

      if (!filteredRows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">No transactions found.</td></tr>';
        refreshLinkPaymentSummary();
        return;
      }

      tbody.innerHTML = filteredRows.map((row) => {
        const selectedAmount = parseFloat(row.selected_amount || 0) || 0;
        const maxAmount = parseFloat(row.balance || 0) || 0;
        const checked = selectedAmount > 0 ? 'checked' : '';
        const disabled = selectedAmount > 0 ? '' : 'disabled';
        return `
        
          <tr data-purchase-id="${row.purchase_id}">
            <td>
              <input type="checkbox" class="form-check-input link-payment-check" data-purchase-id="${row.purchase_id}" ${checked}>
            </td>
            <td>${row.date}</td>
            <td>${row.type}</td>
            <td>${row.ref_no}</td>
            <td class="text-end">${formatLinkPaymentCurrency(row.total)}</td>
            <td class="text-end">${formatLinkPaymentCurrency(row.balance)}</td>
            <td>
              <input type="number" class="form-control form-control-sm link-payment-amount" data-purchase-id="${row.purchase_id}" min="0" max="${maxAmount}" step="0.01" value="${selectedAmount > 0 ? selectedAmount.toFixed(2) : ''}" ${disabled}>
            </td>
          </tr>
        `;
      }).join('');

      refreshLinkPaymentSummary();
    }

    function renderLinkPaymentRows() {
      applyLinkPaymentTypeFilter();
    }

    function syncLinkPaymentSelectionState(target) {
      const purchaseId = target?.dataset?.purchaseId;
      if (!purchaseId) return;

      const row = linkPaymentRows.find((entry) => String(entry.purchase_id) === String(purchaseId));
      if (!row) return;

      const checkbox = document.querySelector(`.link-payment-check[data-purchase-id="${purchaseId}"]`);
      const amountInput = document.querySelector(`.link-payment-amount[data-purchase-id="${purchaseId}"]`);
      const isChecked = checkbox ? checkbox.checked : false;
      const amountValue = parseFloat(amountInput?.value || 0) || 0;
      const maxAmount = parseFloat(row.balance || 0) || 0;

      if (!isChecked) {
        row.selected_amount = 0;
      } else {
        const safeAmount = Math.max(0, Math.min(amountValue, maxAmount));
        row.selected_amount = Number(safeAmount.toFixed(2));
      }

      if (amountInput) {
        amountInput.disabled = !isChecked;
        if (!isChecked) {
          amountInput.value = '';
        } else if (!amountInput.value) {
          amountInput.value = Math.min(maxAmount, getPaymentOutPaidAmount()).toFixed(2);
        }
      }

      persistAppliedLinkRows();
      refreshLinkPaymentSummary();
    }

    function loadLinkablePurchases(partyId, options = {}) {
      const tbody = document.getElementById('linkPaymentRows');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">Loading transactions...</td></tr>';
      }

      const query = new URLSearchParams();
      if (options.paymentOutId) {
        query.set('payment_out_id', options.paymentOutId);
      }

      fetch(`/dashboard/payment-out/linkable-purchases/${partyId}?${query.toString()}`, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        }
      })
      .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
          throw new Error(data.message || 'Transactions load nahi ho sakin.');
        }
        return data;
      })
      .then((data) => {
        document.getElementById('linkPaymentPartyName').textContent = data.party?.name || '-';
        linkPaymentRows = (data.rows || []).map((row) => {
          const applied = appliedLinkPaymentRows.find((item) => String(item.purchase_id) === String(row.purchase_id));
          return {
            ...row,
            selected_amount: applied ? applied.amount : (parseFloat(row.linked_amount || 0) || 0),
          };
        });

        const typeFilter = document.getElementById('linkPaymentTypeFilter');
        if (typeFilter) {
          const types = [...new Set(linkPaymentRows.map(row => String(row.type || '').trim()).filter(Boolean))].sort();
          const currentValue = typeFilter.value || 'all';
          typeFilter.innerHTML = '<option value="all">All transactions</option>' + types.map(type => `<option value="${type}">${type}</option>`).join('');
          if (types.includes(currentValue)) {
            typeFilter.value = currentValue;
          } else {
            typeFilter.value = 'all';
          }
        }

        renderLinkPaymentRows();
      })
      .catch((error) => {
        if (tbody) {
          tbody.innerHTML = `<tr><td colspan="7" class="link-payment-empty">${error.message || 'Transactions load nahi ho sakin.'}</td></tr>`;
        }
      });
    }

    function openLinkPaymentModal() {
      const partyId = selectedPartyId;
      updatePaymentSummary();
      document.getElementById('linkPaymentPartyName').textContent = selectedEntityName || document.getElementById('partyDisplayText').textContent || '-';
      document.getElementById('linkPaymentReceivedInput').value = getPaymentOutPaidAmount().toFixed(2);
      document.getElementById('linkPaymentSearch').value = '';
      document.getElementById('linkPaymentTypeFilter').value = 'all';
      if (partyId) {
        loadLinkablePurchases(partyId, {});
      } else {
        resetLinkPaymentState();
      }
      getLinkPaymentModalInstance()?.show();
    }

    function closeLinkPaymentModal() {
      getLinkPaymentModalInstance()?.hide();
    }

    function autoAllocateLinkPayments() {
      let remaining = getPaymentOutPaidAmount();
      linkPaymentRows = linkPaymentRows.map((row) => {
        const available = parseFloat(row.balance || 0) || 0;
        const allocate = Math.max(0, Math.min(remaining, available));
        remaining -= allocate;
        return {
          ...row,
          selected_amount: Number(allocate.toFixed(2)),
        };
      });
      persistAppliedLinkRows();
      renderLinkPaymentRows();
    }

    /* ═════════════════════
       COLUMN FILTERS STATE
    ═════════════════════ */
    // Stores active filter per column key
    const colFilters = {};
    // Tracks which column the popup is currently open for
    let activeColKey = null;
    let activeColBtn = null;

    /* ── Col filter popup ─────────────────────────────────── */
    function toggleColFilter(btn, colKey, e) {
      e.stopPropagation();
      const popup = document.getElementById('colFilterPopup');

      // If clicking the same open one — close it
      if (activeColKey === colKey && popup.classList.contains('open')) {
        closeColFilter();
        return;
      }

      // Position popup below the button
      const rect = btn.getBoundingClientRect();
      popup.style.top  = (rect.bottom + 6) + 'px';
      popup.style.left = rect.left + 'px';

      // Populate with current filter value
      const input = document.getElementById('colFilterInput');
      input.placeholder = 'Filter ' + colKey.replace(/([A-Z])/g,' $1').replace(/^./,s=>s.toUpperCase()) + '…';
      input.value = colFilters[colKey] || '';

      activeColKey = colKey;
      activeColBtn = btn;

      popup.classList.add('open');
      input.focus();

      // Mark button as active
      document.querySelectorAll('.col-filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    function closeColFilter() {
      document.getElementById('colFilterPopup').classList.remove('open');
      if (activeColBtn) activeColBtn.classList.remove('active');
      // Keep active class if a filter is set for that column
      if (activeColKey && colFilters[activeColKey]) {
        if (activeColBtn) activeColBtn.classList.add('active');
      }
      activeColKey = null;
      activeColBtn = null;
    }

    function liveColFilter() {
      // Optional: live filtering as user types
      // Uncomment next line to enable live filtering
      // applyFilters();
    }

    function applyColFilter() {
      const val = document.getElementById('colFilterInput').value.trim();
      if (activeColKey) {
        if (val) colFilters[activeColKey] = val.toLowerCase();
        else delete colFilters[activeColKey];
      }
      applyFilters();
      // Keep button highlighted if filter active
      if (activeColBtn) {
        if (val) activeColBtn.classList.add('active');
        else activeColBtn.classList.remove('active');
      }
      closeColFilter();
    }

    function clearColFilter() {
      document.getElementById('colFilterInput').value = '';
      if (activeColKey) delete colFilters[activeColKey];
      if (activeColBtn) activeColBtn.classList.remove('active');
      applyFilters();
      closeColFilter();
    }

    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
      const popup = document.getElementById('colFilterPopup');
      if (popup.classList.contains('open') && !e.target.closest('#colFilterPopup') && !e.target.closest('.col-filter-btn')) {
        closeColFilter();
      }
    });

    // Apply col filter on Enter key
    document.getElementById('colFilterInput').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') applyColFilter();
      if (e.key === 'Escape') closeColFilter();
    });

    /* ═════════════════════
       TABLE
    ═════════════════════ */
    function renderTable(rows) {
      const tbody = document.getElementById('tableBody');
      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:48px;color:#9ca3af;font-size:13.5px;">No records found.</td></tr>';
        return;
      }
      tbody.innerHTML = rows.map(p => {
        const dateStr = new Date(p.date + 'T00:00:00').toLocaleDateString('en-GB');
        const sc = p.status === 'Unused' ? 'badge-status-unused' : 'badge-status-used';
        return `
          <tr data-id="${p.id}">
            <td data-column-key="date">${dateStr}</td>
            <td data-column-key="reference"><span class="badge-ref">${p.receiptNo}</span></td>
            <td data-column-key="party"><span class="party-name">${p.party}</span></td>
            <td data-column-key="amount"><span class="amount-danger">Rs ${parseFloat(p.amount).toFixed(2)}</span></td>
            <td data-column-key="paid"><span class="amount-danger">Rs ${parseFloat(p.amount).toFixed(2)}</span></td>
            <td data-column-key="payment_type"><span class="badge-payment">${p.payType}</span></td>
            <td data-column-key="status"><span class="${sc}">${p.status}</span></td>
            <td data-column-key="actions">
              <div class="row-actions">
            <i class="fa-solid fa-print row-action-icon" title="Print" onclick="openReportOptions('print', ${p.id})"></i>
            <i class="fa-solid fa-share-nodes row-action-icon" title="Share"></i>
                <div class="dropdown">
                  <button class="dropdown-toggle-btn" onclick="toggleDropdown(this,event)">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                  <div class="row-dropdown-menu">
                    <a href="#" onclick="editPayment(${p.id});closeAllDropdowns();return false;"><i class="fa-solid fa-eye menu-icon"></i> View/Edit</a>
                    <a href="#" onclick="openReportOptions('print', ${p.id});closeAllDropdowns();return false;"><i class="fa-solid fa-file-pdf menu-icon"></i> Open PDF</a>
                    <a href="#" onclick="openReportOptions('print', ${p.id});closeAllDropdowns();return false;"><i class="fa-solid fa-print menu-icon"></i> Print</a>
                    <hr>
                    <a href="#" onclick="deletePayment(${p.id});return false;"><i class="fa-solid fa-trash menu-icon"></i> Delete</a>
                    <a href="#" onclick="duplicatePayment(${p.id});return false;"><i class="fa-solid fa-copy menu-icon"></i> Duplicate</a>
                    <a href="#" onclick="openHistoryModal(${p.id});closeAllDropdowns();return false;"><i class="fa-solid fa-clock-rotate-left menu-icon"></i> View History</a>
                  </div>
                </div>
              </div>
            </td>
          </tr>`;
      }).join('');
    }

    /* ═════════════════════
       FILTERS
    ═════════════════════ */
    function todayDate() { return new Date(); }
    function dateFromStr(s) { return s ? new Date(s + 'T00:00:00') : null; }
    function formatDate(d) { return d ? d.toLocaleDateString('en-GB') : ''; }

    function populateFirmFilterOptions() {
      const select = document.getElementById('firmFilter');
      if (!select) return;

      const names = [...new Set([
        ...payments.map(p => String(p.party || '').trim()),
        ...RAW_PARTIES.map(p => String(p.name || '').trim())
      ].filter(Boolean))].sort((a, b) => a.localeCompare(b));

      const currentValue = select.value || '';
      select.innerHTML = '<option value="">All Firms</option>' + names.map(name => {
        const safeValue = String(name).replace(/"/g, '&quot;');
        return `<option value="${safeValue}">${name}</option>`;
      }).join('');

      if (currentValue && names.includes(currentValue)) {
        select.value = currentValue;
      }
    }

    function toggleCustomDateControls() {
      const wrap = document.getElementById('customDateRangeWrap');
      const period = document.getElementById('periodFilter').value;
      if (wrap) {
        wrap.style.display = period === 'custom' ? 'inline-flex' : 'none';
      }
    }

    function getRange(period) {
      const t = todayDate();
      if (period === 'today') { const d = new Date(t.getFullYear(),t.getMonth(),t.getDate()); return {from:d,to:d}; }
      if (period === 'this_month') return {from:new Date(t.getFullYear(),t.getMonth(),1),to:new Date(t.getFullYear(),t.getMonth()+1,0)};
      if (period === 'last_month') return {from:new Date(t.getFullYear(),t.getMonth()-1,1),to:new Date(t.getFullYear(),t.getMonth(),0)};
      if (period === 'this_year')  return {from:new Date(t.getFullYear(),0,1),to:new Date(t.getFullYear(),11,31)};
      if (period === 'custom') {
        const startValue = document.getElementById('customStartDate').value;
        const endValue = document.getElementById('customEndDate').value;
        return {
          from: startValue ? dateFromStr(startValue) : null,
          to: endValue ? dateFromStr(endValue) : null
        };
      }
      return {from:null,to:null};
    }

    function applyFilters() {
      const period = document.getElementById('periodFilter').value;
      const firm   = (document.getElementById('firmFilter').value||'').trim().toLowerCase();
      const search = tableSearchQuery.trim().toLowerCase();
      const range  = getRange(period);

      const filtered = payments.filter(p => {
        // Period filter
        let mp = true;
        if (period !== 'all' && (range.from || range.to)) {
          const d = dateFromStr(p.date);
          if (range.from && range.to) {
            mp = d && d >= range.from && d <= range.to;
          } else if (range.from) {
            mp = d && d >= range.from;
          } else if (range.to) {
            mp = d && d <= range.to;
          }
        }

        // Firm filter
        const mf = !firm || String(p.party || '').trim().toLowerCase() === firm;

        // Column filters
        let mc = true;
        for (const [key, val] of Object.entries(colFilters)) {
          let fieldVal = '';
          if (key === 'date') {
            fieldVal = new Date(p.date + 'T00:00:00').toLocaleDateString('en-GB').toLowerCase();
          } else if (key === 'refNo') {
            fieldVal = String(p.receiptNo).toLowerCase();
          } else if (key === 'party') {
            fieldVal = p.party.toLowerCase();
          } else if (key === 'amount' || key === 'paid') {
            fieldVal = parseFloat(p.amount).toFixed(2);
          } else if (key === 'payType') {
            fieldVal = p.payType.toLowerCase();
          } else if (key === 'status') {
            fieldVal = p.status.toLowerCase();
          }
          if (!fieldVal.includes(val)) { mc = false; break; }
        }

        const searchTarget = [
          new Date(p.date + 'T00:00:00').toLocaleDateString('en-GB'),
          p.receiptNo,
          p.party,
          p.amount,
          p.payType,
          p.status
        ].join(' ').toLowerCase();
        const ms = !search || searchTarget.includes(search);

        return mp && mf && mc && ms;
      });

      renderTable(filtered);
      window.__paymentOutVisibleRows = filtered;
      const total = filtered.reduce((s,p)=>s+parseFloat(p.amount),0);
      document.getElementById('totalAmount').textContent = 'Rs '+total.toFixed(2);
      document.getElementById('paidAmount').textContent  = 'Rs '+total.toFixed(2);
      document.getElementById('linkedBills').textContent = filtered.length;
      const label = document.getElementById('dateRangeLabel');
      if (period === 'custom') {
        const startValue = document.getElementById('customStartDate').value;
        const endValue = document.getElementById('customEndDate').value;
        if (startValue && endValue) {
          label.textContent = formatDate(dateFromStr(startValue)) + ' To ' + formatDate(dateFromStr(endValue));
        } else if (startValue) {
          label.textContent = 'From ' + formatDate(dateFromStr(startValue));
        } else if (endValue) {
          label.textContent = 'Until ' + formatDate(dateFromStr(endValue));
        } else {
          label.textContent = 'Custom date';
        }
      } else {
        label.textContent = (range.from && range.to) ? formatDate(range.from)+' To '+formatDate(range.to) : 'All dates';
      }
    }

    /* ═════════════════════
       TITLE DROPDOWN
    ═════════════════════ */
    document.getElementById('titleWrap').addEventListener('click', function(e) {
      e.stopPropagation();
      document.getElementById('titleDropdown').classList.toggle('open');
    });
    document.addEventListener('click', () => document.getElementById('titleDropdown').classList.remove('open'));

    populateFirmFilterOptions();
    toggleCustomDateControls();
    applyFilters();

    const tableSearchWrap = document.getElementById('tableSearchWrap');
    const tableSearchInput = document.getElementById('tableSearchInput');
    document.getElementById('searchToggleBtn').addEventListener('click', () => {
      const opening = tableSearchWrap.hidden;
      tableSearchWrap.hidden = !opening;
      if (opening) {
        tableSearchInput.focus();
      } else if (!tableSearchInput.value.trim()) {
        tableSearchQuery = '';
        applyFilters();
      }
    });
    tableSearchInput.addEventListener('input', () => {
      tableSearchQuery = tableSearchInput.value;
      applyFilters();
    });
    tableSearchInput.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        tableSearchInput.value = '';
        tableSearchQuery = '';
        tableSearchWrap.hidden = true;
        applyFilters();
      }
    });

    /* ═════════════════════
       ROW DROPDOWN — opens UPWARD
    ═════════════════════ */
    function toggleDropdown(btn, e) {
      e.stopPropagation();
      const menu = btn.nextElementSibling;
      const isOpen = menu.classList.contains('open');
      closeAllDropdowns();
      if (!isOpen) {
        // Temporarily show to measure height
        menu.style.visibility = 'hidden';
        menu.style.display = 'block';
        const menuH = menu.offsetHeight;
        menu.style.display = '';
        menu.style.visibility = '';

        const rect = btn.getBoundingClientRect();
        // Position above the button
        menu.style.bottom = 'auto';
        menu.style.top    = (rect.top - menuH - 4) + 'px';
        menu.style.right  = (window.innerWidth - rect.right) + 'px';
        menu.style.left   = 'auto';

        menu.classList.add('open');
        const row = btn.closest('tr');
        if (row) row.classList.add('row-active');
      }
    }

    function closeAllDropdowns() {
      document.querySelectorAll('.row-dropdown-menu.open').forEach(m => {
        m.classList.remove('open');
        const row = m.closest('tr');
        if (row) row.classList.remove('row-active');
      });
    }

    document.addEventListener('click', e => { if (!e.target.closest('.dropdown')) closeAllDropdowns(); });

    /* ═════════════════════
       PARTY DROPDOWN
    ═════════════════════ */
    function buildPartyList() {
      document.getElementById('partyListItems').innerHTML = PARTIES.map(p => `
        <div class="party-item" onclick="selectParty(${p.id})">
          <div class="party-item-info">
            <div class="party-item-name">${p.name}</div>
            <div class="party-item-phone">${p.phone}</div>
          </div>
          <span class="party-balance-badge ${p.dir}">
            ${p.balance} <i class="fa-solid fa-${p.dir==='green'?'arrow-up':'arrow-down'}" style="font-size:9px;"></i>
          </span>
        </div>`).join('');
    }

    function togglePartyDropdown() {
      document.getElementById('partyDropdownList').classList.toggle('open');
    }

    function selectParty(id) {
      const party = PARTIES.find(p => p.id === id);
      if (!party) return;
      selectedPartyId = id;
      const txt = document.getElementById('partyDisplayText');
      txt.textContent = party.name;
      txt.style.color = '#374151';
      document.getElementById('partyDropdownList').classList.remove('open');
      const bl = document.getElementById('partyBalanceLine');
      bl.style.display = 'block';
      document.getElementById('partyBalanceVal').textContent = party.balance;
      document.getElementById('linkPaymentBtn').style.display = 'flex';
    }

    function addNewParty() {
      alert('Add New Party — connect to your backend.');
      document.getElementById('partyDropdownList').classList.remove('open');
    }

    document.addEventListener('click', function(e) {
      if (!e.target.closest('#partyFieldWrap') && !e.target.closest('#partyDisplayBox'))
        document.getElementById('partyDropdownList').classList.remove('open');
    });

    /* ═════════════════════
       PAYMENT BOX LOGIC
    ═════════════════════ */
    let paymentRowCount = 0;

    function activatePaymentBox() {
      const initialType = document.getElementById('simplePayType').value;
      document.getElementById('simplePaymentRow').style.display = 'none';
      document.getElementById('addPayTypeLinkWrap').style.display = 'none';
      const section = document.getElementById('paymentSection');
      section.style.display = 'block';
      paymentBoxActive = true;
      addPaymentRow(initialType, '', true);
      addPaymentRow('', '', false);
    }

    function addPaymentRow(type='', amount='', isFirst=false) {
      paymentRowCount++;
      const id = paymentRowCount;
      const div = document.createElement('div');
      div.className = 'payment-row';
      div.id = 'payRow_' + id;

      const typeOptions = [
        { value:'Cash',   label:'Cash'   },
        { value:'Bank',   label:'Bank'   },
        { value:'Cheque', label:'Cheque' },
        { value:'UPI',    label:'UPI'    },
      ];

      const hasType = type !== '';
      const placeholder = hasType ? '' : '<option value="" disabled selected>Select Type</option>';
      const opts = typeOptions.map(o =>
        `<option value="${o.value}"${type===o.value?' selected':''}>${o.label}</option>`
      ).join('');

      div.innerHTML = `
        <div class="po-pay-type-col">
          <label class="po-row-label">Payment Type</label>
          <select class="po-select-sm" id="payType_${id}" onchange="updateTotal()">
            ${placeholder}${opts}
          </select>
        </div>
        <div class="po-amount-col">
          <label class="po-row-label">Amount</label>
          <input type="number" class="po-amount-input${isFirst?' active-input':''}" id="payAmount_${id}"
            placeholder="Amount" value="${amount}" min="0" oninput="updateTotal()">
        </div>
        <button class="po-delete-btn" onclick="removePaymentRow(${id})" style="${isFirst?'visibility:hidden;':''}">
          <i class="fa-solid fa-trash-can"></i>
        </button>`;

      document.getElementById('paymentRows').appendChild(div);
      updateTotal();
      if (isFirst) setTimeout(() => document.getElementById('payAmount_' + id)?.focus(), 50);
    }

    function removePaymentRow(id) {
      const row = document.getElementById('payRow_' + id);
      if (row) { row.remove(); updateTotal(); }
    }

    function updateTotal() {
      let total = 0;
      document.querySelectorAll('[id^="payAmount_"]').forEach(inp => { total += parseFloat(inp.value)||0; });
      document.getElementById('totalPaymentLine').textContent = 'Total payment: ' + total.toFixed(0);
      document.getElementById('paidDisplay').textContent = total.toFixed(0);
    }

    function toggleDescription() {
      const a = document.getElementById('descriptionArea');
      a.style.display = a.style.display === 'none' ? 'block' : 'none';
    }

    /* ═════════════════════
       MODAL OPEN / CLOSE
    ═════════════════════ */
    function openModal(editData=null) {
      editingId = editData ? editData.id : null;
      resetModal();
      if (editData) {
        const party = PARTIES.find(p => p.name === editData.party);
        if (party) selectParty(party.id);
        activatePaymentBox();
        const amt = document.querySelector('[id^="payAmount_"]');
        if (amt) amt.value = editData.amount;
        const sel = document.querySelector('[id^="payType_"]');
        if (sel) sel.value = editData.payType;
        const rows = document.querySelectorAll('[id^="payRow_"]');
        if (rows.length > 1) rows[rows.length-1].remove();
        document.getElementById('refNoInput').value     = editData.refNo || '';
        document.getElementById('modalReceiptNo').value = editData.receiptNo;
        document.getElementById('modalDate').value      = editData.date;
        updateTotal();
      }
      document.getElementById('addModal').classList.add('open');
    }

    function closeModal() {
      document.getElementById('addModal').classList.remove('open');
      editingId = null;
    }

    document.getElementById('addModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    function resetModal() {
      selectedPartyId = null;
      paymentBoxActive = false;
      paymentRowCount = 0;

      const txt = document.getElementById('partyDisplayText');
      txt.textContent = '';
      txt.style.color = '#bbb';
      document.getElementById('partyBalanceLine').style.display = 'none';
      document.getElementById('partyDropdownList').classList.remove('open');
      document.getElementById('linkPaymentBtn').style.display = 'none';

      document.getElementById('simplePaymentRow').style.display = 'flex';
      document.getElementById('addPayTypeLinkWrap').style.display = 'block';
      document.getElementById('simplePayType').value = 'Cash';

      document.getElementById('paymentSection').style.display = 'none';
      document.getElementById('paymentRows').innerHTML = '';
      document.getElementById('refNoInput').value = '';
      document.getElementById('totalPaymentLine').textContent = 'Total payment: 0';
      document.getElementById('paidDisplay').textContent = '0';

      document.getElementById('descriptionArea').style.display = 'none';
      document.getElementById('descriptionArea').value = '';

      const d = new Date();
      document.getElementById('modalDate').value = d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
      document.getElementById('modalReceiptNo').value = nextReceiptNo;
    }

    /* ═════════════════════
       SAVE
    ═════════════════════ */
    function savePaymentOut() {
      if (!selectedPartyId) { alert('Please select a party.'); return; }

      let total = 0;
      let payType = 'Cash';

      if (paymentBoxActive) {
        document.querySelectorAll('[id^="payAmount_"]').forEach(inp => { total += parseFloat(inp.value)||0; });
        const firstSel = document.querySelector('[id^="payType_"]');
        if (firstSel) payType = firstSel.value || 'Cash';
        if (total <= 0) { alert('Please enter a valid amount.'); return; }
      } else {
        alert('Please enter an amount by clicking "Add Payment type".');
        return;
      }

      const party     = PARTIES.find(p => p.id === selectedPartyId);
      const date      = document.getElementById('modalDate').value;
      const refNo     = document.getElementById('refNoInput').value;
      const receiptNo = document.getElementById('modalReceiptNo').value;

      if (editingId !== null) {
        const idx = payments.findIndex(p => p.id === editingId);
        if (idx !== -1) payments[idx] = {...payments[idx], party:party.name, amount:total, payType, date, refNo, receiptNo};
      } else {
        payments.push({id:nextId++, party:party.name, amount:total, payType, date, refNo, receiptNo, status:'Unused'});
        nextReceiptNo++;
      }
      applyFilters();
      closeModal();
    }

    /* ═════════════════════
       EDIT / DELETE / DUP
    ═════════════════════ */
    function editPayment(id)      { const p = payments.find(x=>x.id===id); if (p) openModal(p); }
    function deletePayment(id)    { if (!confirm('Delete this payment?')) return; payments = payments.filter(p=>p.id!==id); applyFilters(); }
    function duplicatePayment(id) { const p = payments.find(x=>x.id===id); if (!p) return; payments.push({...p,id:nextId++,receiptNo:nextReceiptNo++}); applyFilters(); }

    /* ═════════════════════
       HISTORY MODAL
    ═════════════════════ */
    function openHistoryModal(id) {
      const p = payments.find(x=>x.id===id);
      if (!p) return;
      const dateStr = new Date(p.date+'T00:00:00').toLocaleDateString('en-GB');
      document.getElementById('historyModalBody').innerHTML = `
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px;margin-bottom:20px;">
          <p style="font-weight:700;margin-bottom:10px;">Payment Details Summary:</p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 24px;font-size:13px;">
            <div><strong>Receipt No:</strong> ${p.receiptNo}</div>
            <div><strong>Payment Type:</strong> <span class="badge-payment">${p.payType}</span></div>
            <div><strong>Amount:</strong> <span class="amount-danger">Rs ${parseFloat(p.amount).toFixed(2)}</span></div>
            <div><strong>Party:</strong> ${p.party}</div>
            <div><strong>Status:</strong> ${p.status}</div>
          </div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
          <thead><tr style="background:#f3f4f6;">
            <th style="padding:9px 12px;font-weight:500;color:#6c757d;border-bottom:1px solid #e5e7eb;">Date</th>
            <th style="padding:9px 12px;font-weight:500;color:#6c757d;border-bottom:1px solid #e5e7eb;">Action</th>
            <th style="padding:9px 12px;font-weight:500;color:#6c757d;border-bottom:1px solid #e5e7eb;">Amount</th>
            <th style="padding:9px 12px;font-weight:500;color:#6c757d;border-bottom:1px solid #e5e7eb;">Type</th>
            <th style="padding:9px 12px;font-weight:500;color:#6c757d;border-bottom:1px solid #e5e7eb;">User</th>
          </tr></thead>
          <tbody><tr>
            <td style="padding:10px 12px;border-bottom:1px solid #f1f1f1;color:#6b7280;">${dateStr}</td>
            <td style="padding:10px 12px;border-bottom:1px solid #f1f1f1;"><strong>Payment Out Recorded</strong><br><small style="color:#9ca3af;">Supplier payment for ${p.party}</small></td>
            <td style="padding:10px 12px;border-bottom:1px solid #f1f1f1;color:#D4112E;font-weight:700;">Rs ${parseFloat(p.amount).toFixed(2)}</td>
            <td style="padding:10px 12px;border-bottom:1px solid #f1f1f1;"><span class="badge-payment">${p.payType}</span></td>
            <td style="padding:10px 12px;border-bottom:1px solid #f1f1f1;"><i class="fa-solid fa-user" style="color:#9ca3af;margin-right:4px;"></i>Admin</td>
          </tr></tbody>
        </table>`;
      document.getElementById('historyModal').classList.add('open');
    }

    function closeHistoryModal() { document.getElementById('historyModal').classList.remove('open'); }
    document.getElementById('historyModal').addEventListener('click', function(e) { if (e.target===this) closeHistoryModal(); });

    function getPaymentOutReportRows() {
      const baseRows = Array.isArray(window.__paymentOutVisibleRows) && window.__paymentOutVisibleRows.length
        ? window.__paymentOutVisibleRows
        : payments;
      if (paymentOutReportScopeId) {
        return baseRows.filter(row => Number(row.id) === Number(paymentOutReportScopeId));
      }
      return baseRows;
    }

    function getSelectedReportColumns() {
      return Array.from(document.querySelectorAll('.report-col-check:checked')).map(cb => cb.value);
    }

    function getPaymentOutReportRangeText() {
      const rangeLabel = document.getElementById('dateRangeLabel');
      return rangeLabel ? rangeLabel.textContent : 'All dates';
    }

    function formatMoney(value) {
      return 'Rs ' + (parseFloat(value || 0) || 0).toFixed(2);
    }

    function getRowEntityLabel(row) {
      return row.party || row.item || row.broker || '';
    }

    function buildPaymentOutReportPreview() {
      const rows = getPaymentOutReportRows();
      const cols = getSelectedReportColumns();
      paymentOutReportRows = rows;
      const thead = document.getElementById('paymentOutReportThead');
      const tbody = document.getElementById('paymentOutReportTbody');
      const range = document.getElementById('paymentOutReportRange');
      const durationLabel = getPaymentOutReportRangeText();
      if (range) range.textContent = durationLabel;
      const durationNode = document.getElementById('paymentOutReportDurationLabel');
      if (durationNode) durationNode.textContent = durationLabel;
      const partyNode = document.getElementById('paymentOutReportPartyLabel');
      if (partyNode) {
        if (paymentOutReportScopeId) {
          const scoped = rows[0];
          partyNode.textContent = scoped ? getRowEntityLabel(scoped) || 'Selected Party' : 'Selected Party';
        } else {
          partyNode.textContent = 'All Parties';
        }
      }

      const headers = {
        date: 'Date',
        receiptNo: 'Invoice No.',
        party: 'Party Name',
        total: 'Total',
        paid: 'Payment Total',
        received: 'Received / Paid',
        balance: 'Balance Due',
        items: 'Items Details',
        description: 'Description',
        status: 'Payment Status',
      };

      thead.innerHTML = `<tr>${cols.map(key => `<th>${headers[key] || key}</th>`).join('')}</tr>`;

      if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="${Math.max(cols.length,1)}" style="text-align:center;padding:24px;color:#9ca3af;">No transactions to show</td></tr>`;
        return;
      }

      tbody.innerHTML = rows.map(row => {
        const dateText = new Date(row.date + 'T00:00:00').toLocaleDateString('en-GB');
        const itemText = row.item || row.item_name || row.itemName || '';
        return `<tr>${cols.map(key => {
          if (key === 'date') return `<td>${dateText}</td>`;
          if (key === 'receiptNo') return `<td>${row.receiptNo || ''}</td>`;
          if (key === 'party') return `<td>${getRowEntityLabel(row)}</td>`;
          if (key === 'total') return `<td>${formatMoney(row.total)}</td>`;
          if (key === 'paid') return `<td>${formatMoney(row.amount)}</td>`;
          if (key === 'received') return `<td>${formatMoney(row.amount)}</td>`;
          if (key === 'balance') return `<td>${formatMoney(row.balance)}</td>`;
          if (key === 'items') return `<td>${itemText || '-'}</td>`;
          if (key === 'description') return `<td>${row.description || '-'}</td>`;
          if (key === 'status') return `<td>${row.status || ''}</td>`;
          return `<td></td>`;
        }).join('')}</tr>`;
      }).join('');
    }

    function updateReportOptionsUI() {
      const btn = document.getElementById('paymentOutOptionsActionBtn');
      const title = document.getElementById('paymentOutOptionsTitle');
      if (btn) btn.textContent = paymentOutReportMode === 'excel' ? 'Export Excel' : 'Get Print';
      if (title) title.textContent = paymentOutReportMode === 'excel' ? 'Select Export Options' : 'Select Print Options';
    }

    function openReportOptions(mode = 'print', scopeId = null) {
      paymentOutReportMode = mode;
      paymentOutReportScopeId = scopeId;
      updateReportOptionsUI();
      const modalEl = document.getElementById('paymentOutPrintOptionsModal');
      if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      } else {
        alert('Report options modal is not available right now.');
      }
    }

    function closeReportOptions() {
      paymentOutReportScopeId = null;
      const optionsModal = document.getElementById('paymentOutPrintOptionsModal');
      const previewModal = document.getElementById('paymentOutReportPreviewModal');
      if (optionsModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(optionsModal).hide();
      if (previewModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(previewModal).hide();
    }

    function closeReportPreview() {
      const modalEl = document.getElementById('paymentOutReportPreviewModal');
      if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      }
    }

    function applyReportOptions() {
      if (paymentOutReportMode === 'excel') {
        exportReportExcel();
        closeReportOptions();
        return;
      }
      buildPaymentOutReportPreview();
      const optionsModal = document.getElementById('paymentOutPrintOptionsModal');
      const previewModal = document.getElementById('paymentOutReportPreviewModal');
      if (optionsModal && window.bootstrap) {
        const instance = bootstrap.Modal.getOrCreateInstance(optionsModal);
        optionsModal.addEventListener('hidden.bs.modal', function openPreviewOnce() {
          optionsModal.removeEventListener('hidden.bs.modal', openPreviewOnce);
          if (previewModal && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(previewModal).show();
          }
        });
        instance.hide();
      }
      else if (previewModal && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(previewModal).show();
      } else {
        alert('Preview modal is not available right now.');
      }
    }

    function buildReportHtmlForPrint() {
      const rows = getPaymentOutReportRows();
      const cols = getSelectedReportColumns();
      const headers = {
        date: 'Date',
        receiptNo: 'Invoice No.',
        party: 'Party Name',
        total: 'Total',
        paid: 'Payment Total',
        received: 'Received / Paid',
        balance: 'Balance Due',
        items: 'Items Details',
        description: 'Description',
        status: 'Payment Status',
      };
      const tableHead = `<tr>${cols.map(key => `<th style="border:1px solid #999;padding:8px;text-align:left;">${headers[key] || key}</th>`).join('')}</tr>`;
      const tableBody = rows.map(row => {
        const dateText = new Date(row.date + 'T00:00:00').toLocaleDateString('en-GB');
        const itemText = row.item || row.item_name || row.itemName || '';
        return `<tr>${cols.map(key => {
          let val = '';
          if (key === 'date') val = dateText;
          else if (key === 'receiptNo') val = row.receiptNo || '';
          else if (key === 'party') val = getRowEntityLabel(row);
          else if (key === 'total') val = (parseFloat(row.total || 0) || 0).toFixed(2);
          else if (key === 'paid' || key === 'received') val = (parseFloat(row.amount || 0) || 0).toFixed(2);
          else if (key === 'balance') val = (parseFloat(row.balance || 0) || 0).toFixed(2);
          else if (key === 'items') val = itemText || '-';
          else if (key === 'description') val = row.description || '-';
          else if (key === 'status') val = row.status || '';
          return `<td style="border:1px solid #999;padding:8px;">${val}</td>`;
        }).join('')}</tr>`;
      }).join('');

      return `
        <html>
          <head>
            <title>Payment Out Report</title>
            <style>
              body{font-family:Arial,sans-serif;padding:24px;color:#111;}
              h1{font-size:20px;margin:0 0 8px;}
              .sub{color:#666;margin-bottom:16px;font-size:13px;}
              table{width:100%;border-collapse:collapse;font-size:12px;}
              th{background:#f3f4f6;}
            </style>
          </head>
          <body>
            <h1>Payment Out Report</h1>
            <div class="sub">${getPaymentOutReportRangeText()}</div>
            <table>
              <thead>${tableHead}</thead>
              <tbody>${tableBody}</tbody>
            </table>
          </body>
        </html>
      `;
    }

    function openPrintWindowAndMaybePrint(action = 'preview') {
      buildPaymentOutReportPreview();
      const html = buildReportHtmlForPrint();
      const win = window.open('', '_blank', 'width=1200,height=800');
      if (!win) {
        alert('Popup blocked. Please allow popups for this action.');
        return;
      }

      win.document.open();
      win.document.write(html);
      win.document.close();

      if (action === 'print') {
        win.focus();
        setTimeout(() => win.print(), 500);
        return;
      }

      if (action === 'download') {
        if (window.html2pdf) {
          const element = win.document.body;
          const filename = `payment-out-report-${new Date().toISOString().slice(0, 10)}.pdf`;
          window.html2pdf()
            .set({
              margin: [0.25, 0.25, 0.25, 0.25],
              filename,
              image: { type: 'jpeg', quality: 0.98 },
              html2canvas: { scale: 2, useCORS: true },
              jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
            })
            .from(element)
            .save();
          return;
        }
        win.focus();
        setTimeout(() => win.print(), 500);
      }
    }

    function openReportPdf() {
      openPrintWindowAndMaybePrint('preview');
    }

    function printReport() {
      openPrintWindowAndMaybePrint('print');
    }

    function saveReportPdf() {
      openPrintWindowAndMaybePrint('download');
    }

    function emailReportPdf() {
      buildPaymentOutReportPreview();
      const subject = encodeURIComponent('Payment Out Report');
      const body = encodeURIComponent('Please review the payment out report. It has been prepared for you in the application.');
      window.open(`mailto:?subject=${subject}&body=${body}`, '_blank', 'noopener');
    }

    function exportReportExcel() {
      const rows = getPaymentOutReportRows();
      const cols = getSelectedReportColumns();
      const headers = {
        date: 'Date',
        receiptNo: 'Invoice No.',
        party: 'Party Name',
        total: 'Total',
        paid: 'Payment Total',
        received: 'Received / Paid',
        balance: 'Balance Due',
        items: 'Items Details',
        description: 'Description',
        status: 'Payment Status',
      };
      const lines = [];
      lines.push(cols.map(key => headers[key] || key).join('\t'));
      rows.forEach(row => {
        const dateText = new Date(row.date + 'T00:00:00').toLocaleDateString('en-GB');
        const itemText = row.item || row.item_name || row.itemName || '';
        const values = cols.map(key => {
          if (key === 'date') return dateText;
          if (key === 'receiptNo') return row.receiptNo || '';
          if (key === 'party') return getRowEntityLabel(row);
          if (key === 'total') return (parseFloat(row.total || 0) || 0).toFixed(2);
          if (key === 'paid' || key === 'received') return (parseFloat(row.amount || 0) || 0).toFixed(2);
          if (key === 'balance') return (parseFloat(row.balance || 0) || 0).toFixed(2);
          if (key === 'items') return itemText || '-';
          if (key === 'description') return row.description || '-';
          if (key === 'status') return row.status || '';
          return '';
        });
        lines.push(values.join('\t'));
      });
      const blob = new Blob([lines.join('\n')], { type: 'application/vnd.ms-excel;charset=utf-8;' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'payment-out-report.xls';
      document.body.appendChild(a);
      a.click();
      setTimeout(() => {
        URL.revokeObjectURL(a.href);
        a.remove();
      }, 0);
    }

    function buildPartyList() {
      const parties = RAW_PARTIES.map(p => ({
        id: p.id,
        name: p.name || '',
        phone: p.phone || '',
        balance: formatBalanceValue(p.current_balance ?? p.balance ?? 0),
        dir: getPartyBalanceClass(p.current_balance ?? p.balance ?? 0),
      }));
      const brokers = RAW_BROKERS.map(b => ({ id: b.id, name: b.name || '', phone: b.phone || '' }));
      const items = RAW_ITEMS.map(i => ({ id: i.id, name: i.name || '', phone: i.item_code || '' }));

      document.getElementById('partyListItems').innerHTML = `
        ${renderDropdownSection('Parties', parties, p => `
          <div class="party-item" onclick="selectParty(${p.id}, '${(p.name || '').replace(/'/g, "\\'")}')">
            <div class="party-item-info">
              <div class="party-item-name">${p.name}</div>
              <div class="party-item-phone">${p.phone}</div>
            </div>
            <span class="party-balance-badge ${p.dir}">
              ${p.balance} <i class="fa-solid fa-${p.dir==='green'?'arrow-up':'arrow-down'}" style="font-size:9px;"></i>
            </span>
          </div>
        `)}
        ${renderDropdownSection('Brokers', brokers, b => `
          <div class="party-item" onclick="selectBroker(${b.id}, '${(b.name || '').replace(/'/g, "\\'")}')">
            <div class="party-item-info">
              <div class="party-item-name">${b.name}</div>
              <div class="party-item-phone">${b.phone}</div>
            </div>
            <span class="party-balance-badge green">Broker</span>
          </div>
        `)}
        ${renderDropdownSection('Items', items, i => `
          <div class="party-item" onclick="selectItem(${i.id}, '${(i.name || '').replace(/'/g, "\\'")}')">
            <div class="party-item-info">
              <div class="party-item-name">${i.name}</div>
              <div class="party-item-phone">${i.phone}</div>
            </div>
            <span class="party-balance-badge green">Item</span>
          </div>
        `)}
      `;
    }

    function togglePartyDropdown() {
      document.getElementById('partyDropdownList').classList.toggle('open');
    }

    function selectParty(id, label = '') {
      const party = RAW_PARTIES.find(p => Number(p.id) === Number(id));
      if (!party) return;
      selectedPartyId = id;
      selectedBrokerId = null;
      selectedItemId = null;
      selectedEntityType = 'party';
      selectedEntityName = label || party.name || '';
      document.getElementById('partyDisplayText').textContent = selectedEntityName;
      document.getElementById('partyDisplayText').style.color = '#374151';
      document.getElementById('partyDropdownList').classList.remove('open');
      document.getElementById('partyBalanceLine').style.display = 'block';
      document.getElementById('partyBalanceVal').textContent = formatBalanceValue(party.current_balance ?? party.balance ?? 0);
      document.getElementById('linkPaymentBtn').style.display = 'flex';
    }

    function selectBroker(id, label = '') {
      const broker = RAW_BROKERS.find(b => Number(b.id) === Number(id));
      if (!broker) return;
      selectedBrokerId = id;
      selectedPartyId = null;
      selectedItemId = null;
      selectedEntityType = 'broker';
      selectedEntityName = label || broker.name || '';
      document.getElementById('partyDisplayText').textContent = selectedEntityName;
      document.getElementById('partyDisplayText').style.color = '#374151';
      document.getElementById('partyDropdownList').classList.remove('open');
      document.getElementById('partyBalanceLine').style.display = 'none';
      document.getElementById('linkPaymentBtn').style.display = 'flex';
    }

    function selectItem(id = null, label = '') {
      selectedPartyId = null;
      selectedBrokerId = null;
      selectedItemId = id;
      selectedEntityType = 'item';
      selectedEntityName = label || '';
      document.getElementById('partyDisplayText').textContent = selectedEntityName || 'Selected item';
      document.getElementById('partyDisplayText').style.color = '#374151';
      document.getElementById('partyDropdownList').classList.remove('open');
      document.getElementById('partyBalanceLine').style.display = 'none';
      document.getElementById('linkPaymentBtn').style.display = 'flex';
    }

    function addNewParty() {
      const modalEl = document.getElementById('addPartyModal');
      if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      } else {
        alert('Party modal is not available right now.');
      }
      document.getElementById('partyDropdownList').classList.remove('open');
    }

    function activatePaymentBox() {
      const section = document.getElementById('paymentSection');
      const initialType = document.getElementById('simplePayType').value || 'Cash';
      document.getElementById('simplePaymentRow').style.display = 'none';
      document.getElementById('addPayTypeLinkWrap').style.display = 'none';
      section.style.display = 'block';
      paymentBoxActive = true;
      if (!document.getElementById('payRow_1')) {
        addPaymentRow(initialType, 0, '', true);
      }
    }

    function handlePaymentTypeChange(selectEl) {
      if (!selectEl) return;
      if (selectEl.value === 'add_bank') {
        selectEl.value = selectEl.dataset.previousValue || 'Cash';
        const bankModal = document.getElementById('bankAccountModal');
        if (bankModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(bankModal).show();
        return;
      }

      selectEl.dataset.previousValue = selectEl.value;
      const bankMatch = (selectEl.value || '').startsWith('bank:');
      selectedBankAccountId = bankMatch ? parseInt(selectEl.value.replace('bank:', ''), 10) : null;
      updatePaymentSummary();
    }

    function addPaymentRow(type = 'Cash', amount = 0, refNo = '', isFirst = false) {
      paymentRowCount += 1;
      const id = paymentRowCount;
      const row = document.createElement('div');
      row.className = 'payment-row';
      row.id = `payRow_${id}`;

      row.style.cssText = 'display:flex; flex-direction:column; gap:8px; width:100%; margin-bottom:8px;';
      row.innerHTML = `
        <div style="display:flex; align-items:flex-end; gap:8px; flex-wrap:nowrap; width:100%;">
          <div class="po-pay-type-col" style="flex: 1 1 180px; min-width: 0; display:flex; flex-direction:column;">
            <label class="po-row-label">Payment Type</label>
            <select class="po-select-sm payment-type-entry" id="payType_${id}" onchange="handlePaymentTypeChange(this)" style="width:90%;">
              ${renderPaymentTypeOptions(type)}
            </select>
          </div>
          <div class="po-amount-col" style="flex: 1 1 140px; min-width: 0; display:flex; flex-direction:column;">
            <label class="po-row-label">Amount</label>
            <input type="number" class="po-amount-input${isFirst ? ' active-input' : ''}" id="payAmount_${id}"
              placeholder="Amount" value="${amount || ''}" min="0" step="0.01" oninput="updatePaymentSummary()" style="width:100%;">
          </div>
          <button class="po-delete-btn" onclick="removePaymentRow(${id})" style="flex-shrink:0; align-self:flex-end;${isFirst ? 'visibility:hidden;' : ''}">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </div>
        <div class="po-ref-col" style="width:100%;${isFirst ? 'display:none;' : ''}">
          <label class="po-row-label">Reference No.</label>
          <input type="text" class="po-ref-input" id="payRef_${id}" placeholder="Reference No." value="${refNo || ''}" oninput="updatePaymentSummary()" style="width:30%;">
        </div>`;

      document.getElementById('paymentRows').appendChild(row);
      updatePaymentSummary();
      if (isFirst) {
        setTimeout(() => document.getElementById(`payAmount_${id}`)?.focus(), 50);
      }
    }

    function removePaymentRow(id) {
      const row = document.getElementById(`payRow_${id}`);
      if (row) {
        row.remove();
        updatePaymentSummary();
      }
    }

    function updatePaymentSummary() {
      let paid = 0;
      document.querySelectorAll('[id^="payAmount_"]').forEach(inp => {
        paid += parseFloat(inp.value) || 0;
      });
      const discount = parseFloat(document.getElementById('paymentDiscountInput')?.value || 0) || 0;
      const total = Math.max(paid - discount, 0);
      const totalPaymentLine = document.getElementById('totalPaymentLine');
      if (totalPaymentLine) totalPaymentLine.textContent = 'Total payment: ' + paid.toFixed(0);
      const paidDisplay = document.getElementById('paidDisplay');
      if (paidDisplay) paidDisplay.textContent = paid.toFixed(0);
      const totalInput = document.getElementById('paymentTotalInput');
      if (totalInput) totalInput.value = total.toFixed(0);
      const amountInput = document.getElementById('modalPaidAmount');
      if (amountInput) amountInput.value = paid.toFixed(2);
      const refInputs = Array.from(document.querySelectorAll('[id^="payRef_"]'));
      if (refInputs.length && !document.getElementById('refNoInput').value) {
        document.getElementById('refNoInput').value = refInputs[0].value || '';
      }
    }

    function toggleDescription() {
      const btn = document.getElementById('descriptionBtn');
      const area = document.getElementById('descriptionArea');
      if (!btn || !area) return;
      btn.style.display = 'none';
      area.style.display = 'block';
      area.focus();
    }

    function triggerPaymentOutImagePicker() {
      document.getElementById('paymentOutImageInput')?.click();
    }

    function handlePaymentOutImageSelection(input) {
      paymentImageFiles = Array.from(input?.files || []);
      const preview = document.getElementById('paymentOutImagePreview');
      if (!preview) return;
      preview.innerHTML = paymentImageFiles.map(file => `
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;background:#fff;font-size:12px;color:#374151;">
          <i class="fa-solid fa-image" style="margin-right:6px;color:#64748b;"></i>${file.name}
        </div>
      `).join('');
    }

    function openModal(editData = null) {
      editingId = editData ? editData.id : null;
      resetModal();
      if (editData) {
        const party = RAW_PARTIES.find(p => Number(p.id) === Number(editData.party_id)) || RAW_PARTIES.find(p => (p.name || '') === (editData.party || ''));
        if (party) selectParty(party.id, party.name || editData.party || '');
        if (editData.broker) {
          const broker = RAW_BROKERS.find(b => (b.name || '') === editData.broker);
          if (broker) selectBroker(broker.id, broker.name);
        }
        if (editData.item_id) {
          const item = RAW_ITEMS.find(i => Number(i.id) === Number(editData.item_id)) || RAW_ITEMS.find(i => (i.name || '') === (editData.item || ''));
          if (item) selectItem(item.id, item.name || editData.item || '');
        }
        document.getElementById('descriptionArea').value = editData.description || '';
        document.getElementById('paymentDiscountInput').value = editData.discount || 0;
        document.getElementById('paymentTotalInput').value = editData.total || editData.amount || 0;
        activatePaymentBox();
        const amountInput = document.querySelector('[id^="payAmount_"]');
        if (amountInput) amountInput.value = editData.amount || 0;
        const payTypeSelect = document.querySelector('[id^="payType_"]');
        if (payTypeSelect) {
          payTypeSelect.value = editData.bankAccountId ? `bank:${editData.bankAccountId}` : (editData.payType || 'Cash');
          payTypeSelect.dataset.previousValue = payTypeSelect.value;
        }
        const refInput = document.querySelector('[id^="payRef_"]');
        if (refInput) refInput.value = editData.reference || editData.refNo || '';
        document.getElementById('refNoInput').value = editData.reference || editData.refNo || '';
        document.getElementById('modalReceiptNo').value = editData.receiptNo || nextReceiptNo;
        document.getElementById('modalDate').value = editData.date || new Date().toISOString().slice(0, 10);
        updatePaymentSummary();
      } else {
        document.getElementById('modalReceiptNo').value = getNextReceiptNumber();
      }
      document.getElementById('addModal').classList.add('open');
    }

    function resetModal() {
      selectedPartyId = null;
      selectedBrokerId = null;
      selectedItemId = null;
      selectedEntityType = 'party';
      selectedEntityName = '';
      selectedBankAccountId = null;
      paymentBoxActive = false;
      paymentRowCount = 0;
      paymentImageFiles = [];

      document.getElementById('partyDisplayText').textContent = '';
      document.getElementById('partyDisplayText').style.color = '#bbb';
      document.getElementById('partyBalanceLine').style.display = 'none';
      document.getElementById('partyDropdownList').classList.remove('open');
      document.getElementById('linkPaymentBtn').style.display = 'none';

      document.getElementById('simplePaymentRow').style.display = 'flex';
      document.getElementById('addPayTypeLinkWrap').style.display = 'block';
      document.getElementById('simplePayType').value = 'Cash';

      document.getElementById('paymentSection').style.display = 'none';
      document.getElementById('paymentRows').innerHTML = '';
      document.getElementById('refNoInput').value = '';
      document.getElementById('totalPaymentLine').textContent = 'Total payment: 0';
      document.getElementById('paidDisplay').textContent = '0';
      document.getElementById('paymentDiscountInput').value = '0';
      document.getElementById('paymentTotalInput').value = '0';

      document.getElementById('descriptionBtn').style.display = 'inline-flex';
      document.getElementById('descriptionArea').style.display = 'none';
      document.getElementById('descriptionArea').value = '';
      document.getElementById('paymentOutImageInput').value = '';
      document.getElementById('paymentOutImagePreview').innerHTML = '';
      document.getElementById('linkedRowsJson').value = '[]';
      resetLinkPaymentState();

      const d = new Date();
      document.getElementById('modalDate').value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      document.getElementById('modalReceiptNo').value = getNextReceiptNumber();
    }

    function savePaymentOut() {
      if (!selectedPartyId && !selectedBrokerId && !selectedItemId && !selectedEntityName) {
        alert('Please select a party, broker, or item.');
        return;
      }

      let paid = 0;
      document.querySelectorAll('[id^="payAmount_"]').forEach(inp => { paid += parseFloat(inp.value) || 0; });
      if (paid <= 0) {
        alert('Please enter a valid amount.');
        return;
      }

      const firstSel = document.querySelector('[id^="payType_"]');
      const paymentType = firstSel ? firstSel.value : 'Cash';
      const bankAccountId = paymentType.startsWith('bank:') ? parseInt(paymentType.replace('bank:', ''), 10) : null;
      const discount = parseFloat(document.getElementById('paymentDiscountInput')?.value || 0) || 0;
      const total = Math.max(paid - discount, 0);

      const linkedRows = linkPaymentRows
        .filter(row => (parseFloat(row.selected_amount || 0) || 0) > 0)
        .map(row => ({
          purchase_id: row.purchase_id || null,
          sale_id: row.sale_id || null,
          transaction_id: row.transaction_id || null,
          amount: Number(parseFloat(row.selected_amount).toFixed(2))
        }));

      const payload = {
        party_id: selectedPartyId || null,
        broker_id: selectedBrokerId || null,
        item_id: selectedItemId || null,
        payment_type: paymentType,
        bank_account_id: bankAccountId,
        amount: paid.toFixed(2),
        discount: discount.toFixed(2),
        total: total.toFixed(2),
        reference: document.getElementById('refNoInput').value || '',
        receipt_no: document.getElementById('modalReceiptNo').value || '',
        payment_date: document.getElementById('modalDate').value || '',
        description: document.getElementById('descriptionArea').value || '',
        entity_type: selectedEntityType,
        entity_name: selectedEntityName || document.getElementById('partyDisplayText').textContent || '',
        linked_rows: linkedRows
      };

      fetch(@json(route('payment-out.store')), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify(payload),
      })
      .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) throw new Error(data.message || 'Save failed');
        return data;
      })
      .then(data => {
        const row = data.payment || null;
        if (row) {
          payments.unshift({
            id: row.id,
            date: row.date,
            receiptNo: row.receiptNo,
            party: row.party,
            party_id: row.party_id,
            broker: row.broker || '',
            item_id: row.item_id || null,
            item: row.item || '',
            payType: row.payType || row.paymentType || 'Cash',
            paymentType: row.paymentType || row.payType || 'Cash',
            bankAccountId: row.bankAccountId || null,
            amount: row.amount,
            discount: row.discount || 0,
            total: row.total || row.amount,
            status: row.status || 'paid',
            reference: row.reference || '',
            description: row.description || '',
            dueDate: row.dueDate || '',
          });
          nextReceiptNo = getNextReceiptNumber();
        } else {
          window.location.reload();
          return;
        }
        applyFilters();
        document.getElementById('linkedRowsJson').value = '[]';
        resetLinkPaymentState();
        closeModal();
      })
      .catch(err => {
        alert(err.message || 'Save failed.');
      });
    }

    function editPayment(id) {
      const p = payments.find(x => x.id === id);
      if (p) openModal(p);
    }

    function deletePayment(id) {
      if (!confirm('Delete this payment?')) return;
      payments = payments.filter(p => p.id !== id);
      applyFilters();
    }

    function duplicatePayment(id) {
      const p = payments.find(x => x.id === id);
      if (!p) return;
      const clone = {
        ...p,
        id: null,
        receiptNo: getNextReceiptNumber(),
        date: p.date || new Date().toISOString().slice(0, 10),
      };
      openModal(clone);
    }

    function updatePaymentSummary() {
      let paid = 0;
      document.querySelectorAll('[id^="payAmount_"]').forEach(inp => {
        paid += parseFloat(inp.value) || 0;
      });
      const discount = parseFloat(document.getElementById('paymentDiscountInput')?.value || 0) || 0;
      const total = Math.max(paid - discount, 0);
      document.getElementById('totalPaymentLine').textContent = 'Total payment: ' + paid.toFixed(0);
      document.getElementById('paidDisplay').textContent = paid.toFixed(0);
      document.getElementById('paymentTotalInput').value = total.toFixed(0);
    }

    /* ═════════════════════
       COLUMN RESIZING
    ═════════════════════ */
    function populatePrimaryPaymentTypeSelect() {
      const select = document.getElementById('simplePayType');
      if (!select) return;
      select.innerHTML = renderPaymentTypeOptions('Cash');
      select.value = 'Cash';
      select.dataset.previousValue = 'Cash';
      select.classList.add('default-payment-type');
      select.onchange = function() {
        handlePaymentTypeChange(this);
      };
    }

    document.querySelectorAll('.resizer').forEach(resizer => {
      let startX, startW, col;
      resizer.addEventListener('mousedown', function(e) {
        e.preventDefault();
        e.stopPropagation();
        col = document.getElementById(this.dataset.col);
        startX = e.clientX; startW = col.offsetWidth;
        resizer.classList.add('active');
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
      }, true);
      function onMove(e) { col.style.width = Math.max(60, startW+(e.clientX-startX))+'px'; }
      function onUp()    { resizer.classList.remove('active'); document.removeEventListener('mousemove',onMove); document.removeEventListener('mouseup',onUp); }
    });

    /* ═════════════════════
       INIT
    ═════════════════════ */
    document.addEventListener('DOMContentLoaded', () => {
      populatePrimaryPaymentTypeSelect();
      buildPartyList();
      applyFilters();
      resetLinkPaymentState();

      const urlParams = new URLSearchParams(window.location.search);
      const editPaymentOutId = urlParams.get('edit_payment_out') || urlParams.get('payment_out_id');
      if (editPaymentOutId) {
        const openTarget = payments.find((p) => String(p.id) === String(editPaymentOutId));
        if (openTarget) {
          setTimeout(() => openModal(openTarget), 50);
        }
      }

      document.getElementById('paymentOutImageInput')?.addEventListener('change', function() {
        handlePaymentOutImageSelection(this);
      });
      document.getElementById('paymentDiscountInput')?.addEventListener('input', updatePaymentSummary);
      document.getElementById('simplePayType')?.addEventListener('change', function() {
        handlePaymentTypeChange(this);
      });
      document.getElementById('linkPaymentAutoBtn')?.addEventListener('click', autoAllocateLinkPayments);
      document.getElementById('linkPaymentResetBtn')?.addEventListener('click', () => {
        linkPaymentRows = linkPaymentRows.map(row => ({ ...row, selected_amount: 0 }));
        renderLinkPaymentRows();
        persistAppliedLinkRows();
      });
      document.getElementById('linkPaymentDoneBtn')?.addEventListener('click', () => {
        const received = getPaymentOutPaidAmount();
        const linked = calculateLinkedTotal();
        if (linked - received > 0.001) {
          alert('Linked amount paid amount se zyada nahi ho sakta.');
          return;
        }
        persistAppliedLinkRows();
        closeLinkPaymentModal();
      });
      document.getElementById('linkPaymentSearch')?.addEventListener('input', renderLinkPaymentRows);
      document.getElementById('linkPaymentTypeFilter')?.addEventListener('change', renderLinkPaymentRows);
      document.getElementById('linkPaymentRows')?.addEventListener('change', (event) => {
        const target = event.target;
        if (target.classList.contains('link-payment-check') || target.classList.contains('link-payment-amount')) {
          syncLinkPaymentSelectionState(target);
        }
      });
      document.getElementById('linkPaymentRows')?.addEventListener('click', (event) => {
        const target = event.target;
        if (target.classList.contains('link-payment-check')) {
          syncLinkPaymentSelectionState(target);
        }
      });
    });
  </script>
  <script src="{{ asset('js/transaction-column-drag.js') }}"></script>
</body>
</html>
