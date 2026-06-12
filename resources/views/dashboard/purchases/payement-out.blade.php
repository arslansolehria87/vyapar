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
          <select id="periodFilter" onchange="applyFilters()">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_year">This Year</option>
          </select>
          <div class="pill-divider"></div>
          <span class="date-range-text" id="dateRangeLabel">All dates</span>
        </div>
        <div class="filter-pill">
          <i class="fa-solid fa-building pill-icon"></i>
          <select id="firmFilter" onchange="applyFilters()">
            <option value="">All Firms</option>
            <option value="dodh patya">Dodh Patya</option>
            <option value="hasnain">Hasnain</option>
            <option value="hello">Hello</option>
            <option value="maleeq">Maleeq</option>
            <option value="party 1">Party 1</option>
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
          <button class="tool-btn" title="Export to Excel"><i class="fa-solid fa-file-excel" style="color:#217346;"></i></button>
          <button class="tool-btn" title="Print"><i class="fa-solid fa-print"></i></button>
        </div>
      </div>
      <div class="table-wrapper">
        <table class="custom-table" id="mainTable">
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
              <th>
                <div class="col-header">Date <button class="col-filter-btn" data-col="date" onclick="toggleColFilter(this,'date',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-date"></div>
              </th>
              <th>
                <div class="col-header">Ref. No. <button class="col-filter-btn" data-col="refNo" onclick="toggleColFilter(this,'refNo',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-ref"></div>
              </th>
              <th>
                <div class="col-header">Party Name <button class="col-filter-btn" data-col="party" onclick="toggleColFilter(this,'party',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-party"></div>
              </th>
              <th>
                <div class="col-header">Total Amount <button class="col-filter-btn" data-col="amount" onclick="toggleColFilter(this,'amount',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-total"></div>
              </th>
              <th>
                <div class="col-header">Paid <button class="col-filter-btn" data-col="paid" onclick="toggleColFilter(this,'paid',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-paid"></div>
              </th>
              <th>
                <div class="col-header">Payment Type <button class="col-filter-btn" data-col="payType" onclick="toggleColFilter(this,'payType',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-type"></div>
              </th>
              <th>
                <div class="col-header">Status <button class="col-filter-btn" data-col="status" onclick="toggleColFilter(this,'status',event)"><i class="fa-solid fa-filter"></i></button></div>
                <div class="resizer" data-col="col-status"></div>
              </th>
              <th style="text-align:center;">Actions</th>
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
              <input type="text" class="po-ref-input" id="refNoInput" placeholder="Reference No.">
              <div class="payment-section-footer">
                <button class="add-payment-type-btn" onclick="addPaymentRow()">
                  <i class="fa-solid fa-plus" style="font-size:10px;"></i>
                  Add Payment type
                </button>
                <span class="total-payment-line" id="totalPaymentLine">Total payment: 0</span>
              </div>
            </div>

            <button class="po-desc-btn" onclick="toggleDescription()">
              <i class="fa-solid fa-file-lines"></i> ADD DESCRIPTION
            </button>
            <textarea id="descriptionArea" rows="3" placeholder="Add description..."
              style="display:none;width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px;font-size:13px;outline:none;resize:vertical;margin-bottom:10px;"></textarea>

            <button class="po-camera-btn" title="Add photo">
              <i class="fa-solid fa-camera"></i>
            </button>

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
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="link-payment-btn" id="linkPaymentBtn" style="display:none;">
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

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>


  <script>
    /* ═════════════════════
       DATA
    ═════════════════════ */
    const PARTIES = [
      { id:1, name:'dodh patya', phone:'465436436', balance:2310, dir:'green' },
      { id:2, name:'hasnain',    phone:'45645645',  balance:1500, dir:'green' },
      { id:3, name:'hello',      phone:'453435534', balance:4500, dir:'red'   },
      { id:4, name:'maleeq',     phone:'034878687', balance:1500, dir:'green' },
      { id:5, name:'Party 1',    phone:'034967063', balance:4600, dir:'green' },
    ];

    let payments = [];
    let nextId = 1;
    let nextReceiptNo = 1;
    let editingId = null;
    let paymentBoxActive = false;
    let tableSearchQuery = '';

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
            <td>${dateStr}</td>
            <td><span class="badge-ref">${p.receiptNo}</span></td>
            <td><span class="party-name">${p.party}</span></td>
            <td><span class="amount-danger">Rs ${parseFloat(p.amount).toFixed(2)}</span></td>
            <td><span class="amount-danger">Rs ${parseFloat(p.amount).toFixed(2)}</span></td>
            <td><span class="badge-payment">${p.payType}</span></td>
            <td><span class="${sc}">${p.status}</span></td>
            <td>
              <div class="row-actions">
                <i class="fa-solid fa-print row-action-icon" title="Print"></i>
                <i class="fa-solid fa-share-nodes row-action-icon" title="Share"></i>
                <div class="dropdown">
                  <button class="dropdown-toggle-btn" onclick="toggleDropdown(this,event)">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                  <div class="row-dropdown-menu">
                    <a href="#" onclick="editPayment(${p.id});closeAllDropdowns();return false;"><i class="fa-solid fa-eye menu-icon"></i> View/Edit</a>
                    <a href="#"><i class="fa-solid fa-file-pdf menu-icon"></i> Open PDF</a>
                    <a href="#"><i class="fa-solid fa-print menu-icon"></i> Print</a>
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
    function getRange(period) {
      const t = todayDate();
      if (period === 'today') { const d = new Date(t.getFullYear(),t.getMonth(),t.getDate()); return {from:d,to:d}; }
      if (period === 'this_month') return {from:new Date(t.getFullYear(),t.getMonth(),1),to:new Date(t.getFullYear(),t.getMonth()+1,0)};
      if (period === 'last_month') return {from:new Date(t.getFullYear(),t.getMonth()-1,1),to:new Date(t.getFullYear(),t.getMonth(),0)};
      if (period === 'this_year')  return {from:new Date(t.getFullYear(),0,1),to:new Date(t.getFullYear(),11,31)};
      return {from:null,to:null};
    }

    function applyFilters() {
      const period = document.getElementById('periodFilter').value;
      const firm   = (document.getElementById('firmFilter').value||'').toLowerCase().trim();
      const search = tableSearchQuery.trim().toLowerCase();
      const range  = getRange(period);

      const filtered = payments.filter(p => {
        // Period filter
        let mp = true;
        if (period !== 'all' && range.from && range.to) {
          const d = dateFromStr(p.date);
          mp = d && d >= range.from && d <= range.to;
        }

        // Firm filter
        const mf = !firm || p.party.toLowerCase() === firm;

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
      const total = filtered.reduce((s,p)=>s+parseFloat(p.amount),0);
      document.getElementById('totalAmount').textContent = 'Rs '+total.toFixed(2);
      document.getElementById('paidAmount').textContent  = 'Rs '+total.toFixed(2);
      document.getElementById('linkedBills').textContent = filtered.length;
      const label = document.getElementById('dateRangeLabel');
      label.textContent = (range.from && range.to) ? formatDate(range.from)+' To '+formatDate(range.to) : 'All dates';
    }

    /* ═════════════════════
       TITLE DROPDOWN
    ═════════════════════ */
    document.getElementById('titleWrap').addEventListener('click', function(e) {
      e.stopPropagation();
      document.getElementById('titleDropdown').classList.toggle('open');
    });
    document.addEventListener('click', () => document.getElementById('titleDropdown').classList.remove('open'));

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
    let selectedPartyId = null;

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

    /* ═════════════════════
       COLUMN RESIZING
    ═════════════════════ */
    document.querySelectorAll('.resizer').forEach(resizer => {
      let startX, startW, col;
      resizer.addEventListener('mousedown', function(e) {
        e.preventDefault();
        col = document.getElementById(this.dataset.col);
        startX = e.clientX; startW = col.offsetWidth;
        resizer.classList.add('active');
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
      });
      function onMove(e) { col.style.width = Math.max(60, startW+(e.clientX-startX))+'px'; }
      function onUp()    { resizer.classList.remove('active'); document.removeEventListener('mousemove',onMove); document.removeEventListener('mouseup',onUp); }
    });

    /* ═════════════════════
       INIT
    ═════════════════════ */
    document.addEventListener('DOMContentLoaded', () => { buildPartyList(); applyFilters(); });
  </script>
</body>
</html>
