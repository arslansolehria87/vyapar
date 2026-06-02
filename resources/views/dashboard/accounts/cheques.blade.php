@extends('layouts.app')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* ── Global ── */
    body { background-color: #ffffff; color: #000000; }
    .main-content {
      padding: 20px 24px !important;
      min-width: 0;
      overflow: visible;
    }
    .cheques-page {
      width: 100%;
      max-width: 100%;
      min-width: 0;
      min-height: calc(100vh - var(--navbar-height, 50px) - 40px);
      display: flex;
      flex-direction: column;
      background: #fff;
      box-sizing: border-box;
      overflow: hidden;
    }

    /* ── Header ── */
    .cheque-header {
      background-color: #fff !important;
      border-bottom: 4px solid #e2e8f0;
      padding: 1rem 1.5rem !important;
    }
    .cheque-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; }

    /* ── Search icon top-right ── */
    .cheque-search-icon-btn {
      background: none; border: none; cursor: pointer;
      color: #64748b; font-size: 18px; padding: 4px 6px;
      border-radius: 6px;
    }
    .cheque-search-icon-btn:hover { background: #f1f5f9; color: #1e293b; }

    /* ── Transactions bar ── */
    .transactions-bar { padding: 0.75rem 1.5rem !important; background-color: #fff; border-bottom: 1px solid #f3f4f6; }
    .transactions-title { font-weight: 600; color: #475569; font-size: 0.95rem; margin-bottom: 0; }

    /* Search input (hidden by default, shown via toggle) */
    .txn-search-wrap { position: relative; }
    .txn-search-input {
      border: 1px solid #e5e7eb; border-radius: 6px;
      padding: 7px 10px 7px 34px; font-size: 13px; outline: none; width: 240px; color: #374151;
      background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' viewBox='0 0 24 24' stroke='%23b0b8c4' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath stroke-linecap='round' d='M21 21l-4.35-4.35'/%3E%3C/svg%3E") no-repeat 11px center;
      transition: border-color .2s;
    }
    .txn-search-input::placeholder { color: #b0b8c4; }
    .txn-search-input:focus { border-color: #2563eb; }
    .txn-search-input.has-value { border-color: #e53e3e !important; background-color: #fff5f5; }

    /* ── Table Wrapper ── */
    .cheque-tbl-wrap { overflow-y: auto; overflow-x: auto; position: relative; border: 1px solid #ebebeb; }
    .cheque-tbl-wrap::-webkit-scrollbar { width: 4px; height: 4px; }
    .cheque-tbl-wrap::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

    .cheque-tbl { width: 100%; border-collapse: collapse; table-layout: fixed; }

    /* ── Header Cells ── */
    .cheque-tbl th {
      padding: 8px 12px; font-size: 12px; font-weight: 500;
      text-transform: uppercase; color: #7a828e;
      background: #f4f5f7; border-bottom: 1px solid #ebebeb;
      border-right: 1px solid #ebebeb;
      text-align: left; white-space: nowrap;
      position: relative; overflow: visible; user-select: none;
    }
    .cheque-tbl th:last-child { border-right: none; }

    .cheque-tbl th .th-inner {
      display: flex; align-items: center; justify-content: space-between;
      width: calc(100% - 6px); cursor: pointer; overflow: hidden;
    }
    .th-sort-arrow {
      display: inline-flex; align-items: center;
      color: #4a4a4a; flex-shrink: 0; font-size: 10px; font-style: normal;
      opacity: 0; transition: opacity .1s; line-height: 1; margin-left: auto; margin-right: 6px;
    }
    .cheque-tbl th.sort-asc  .th-sort-arrow,
    .cheque-tbl th.sort-desc .th-sort-arrow { opacity: 1; }
    .th-sort-arrow::after               { content: '↑'; }
    .cheque-tbl th.sort-desc .th-sort-arrow::after { content: '↓'; }

    /* ── Transaction Date column always shows ↕ ── */
    .cheque-tbl th[data-col="transaction_date"] .th-sort-arrow {
      opacity: 1; color: #6b7280;
    }
    .cheque-tbl th[data-col="transaction_date"]:not(.sort-asc):not(.sort-desc) .th-sort-arrow::after {
      content: '↕';
    }

    .cheque-tbl th .th-filter-icon {
      color: #a0aec0; flex-shrink: 0; cursor: pointer;
      transition: color .15s, background .15s;
      font-size: 11px; padding: 3px 4px; border-radius: 3px;
    }
    .cheque-tbl th .th-filter-icon:hover { color: #718096; background: #e9ecef; }
    .cheque-tbl th .th-filter-icon.active { color: #fff !important; background-color: #e53e3e !important; border-radius: 3px; }
    .cheque-tbl th.filter-active { background: #fff5f5 !important; }

    /* ── Resize Handle ── */
    .col-resize-handle {
      position: absolute; right: 0; top: 0; bottom: 0;
      width: 6px; cursor: col-resize; z-index: 20; background: transparent;
    }
    .col-resize-handle::after {
      content: ''; position: absolute; right: 1px; top: 20%; bottom: 20%;
      width: 2px; background: transparent; border-radius: 2px; transition: background 0.15s;
    }
    .col-resize-handle:hover::after, .col-resize-handle.resizing::after { background: #2563eb; }

    /* ── Table Cells ── */
    .cheque-tbl td {
      padding: 18px 10px; font-size: 13px; color: #000000; font-weight: 400;
      border-bottom: 1px solid #f0f0f0; border-right: 1px solid #f0f0f0;
      vertical-align: middle;
      overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
      background-color: #ffffff;
    }
    .cheque-tbl td:last-child { border-right: none; }

    .cheque-tbl td.td-price { text-align: right; color: #10b981; font-weight: 500; }
    .cheque-tbl th.th-price-right { text-align: right; }
    .cheque-tbl td.td-actions { padding: 2px 4px; width: 40px; text-align: center; background-color: #ffffff; }

    .cheque-tbl tbody tr:not(.tr-highlight):hover td { background-color: #f5fbff; }
    .cheque-tbl tbody tr.tr-highlight td { background-color: #dceefa !important; }
    .cheque-tbl tbody tr.tr-highlight:hover td { background-color: #cce5f5 !important; }

    /* ── Type Label ── */
    .type-label { font-size: 13px; font-weight: 400; color: #000000; text-transform: capitalize; display: inline-block; }

   

    /* ── Deposit Action Link ── */
    .deposit-link {
      color: #2563eb; font-size: 13px; font-weight: 500; cursor: pointer;
      text-decoration: none; background: none; border: none; padding: 0;
    }
    .deposit-link:hover { text-decoration: underline; color: #1d4ed8; }

    /* ── Row action menu ── */
    .il-row-menu-wrap { position: relative; }
    .il-row-menu-btn { background: none; border: none; cursor: pointer; color: #9ca3af; padding: 4px 6px; border-radius: 4px; font-size: 18px; line-height: 1; }
    .il-row-menu-btn:hover { color: #374151; background: #f3f4f6; }
    .il-row-menu {
      position: fixed; background: #fff; border: 1px solid #e5e7eb;
      border-radius: 8px; box-shadow: 0 6px 24px rgba(0,0,0,.13);
      z-index: 9000; min-width: 160px; display: none; padding: 4px 0;
    }
    .il-row-menu.open { display: block; }
    .il-row-menu-item { padding: 10px 16px; cursor: pointer; font-size: 13px; color: #374151; display: flex; align-items: center; gap: 8px; }
    .il-row-menu-item:hover { background: #f9fafb; }
    .il-row-menu-item.danger { color: #ef4444; }
    .il-row-menu-item.danger:hover { background: #fef2f2; }
    .il-row-menu-item i { font-size: 13px; width: 16px; }

    /* ══ COLUMN FILTER DROPDOWNS ══ */
    .col-filter-dd {
      display: none; position: fixed;
      background: #fff; border: 1px solid #cbd5e1;
      border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,.15);
      z-index: 9999; min-width: 220px; max-width: 260px; padding: 14px;
      text-transform: none; font-weight: normal;
    }
    .col-filter-dd.open { display: block; }
    .cfd-title { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 12px; }
    .cfd-cb-row { display: flex; align-items: center; gap: 10px; padding: 7px 2px; font-size: 13px; color: #374151; cursor: pointer; }
    .cfd-cb-row input[type=checkbox] { width: 15px; height: 15px; accent-color: #2563eb; flex-shrink: 0; }
    .cfd-select {
      width: 100%; border: 1.5px solid #e5e7eb; border-radius: 6px;
      padding: 9px 10px; font-size: 13px; color: #374151;
      background: #fff; outline: none; cursor: pointer; margin-bottom: 10px;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
    }
    .cfd-input {
      width: 100%; border: 1.5px solid #e5e7eb; border-radius: 6px;
      padding: 9px 10px; font-size: 13px; color: #374151;
      outline: none; box-sizing: border-box;
    }
    .cfd-input:focus { border-color: #2563eb; }
    .cfd-input::placeholder { color: #9ca3af; }
    .cfd-date-lbl { font-size: 11px; color: #9ca3af; margin-bottom: 6px; }
    .cfd-actions { display: flex; gap: 8px; margin-top: 14px; }
    .cfd-clear { flex: 1; border: 1.5px solid #e5e7eb; background: #fff; border-radius: 20px; padding: 8px 0; font-size: 12px; color: #6b7280; cursor: pointer; font-weight: 500; }
    .cfd-apply { flex: 1; border: none; background: #e53e3e; border-radius: 20px; padding: 8px 0; font-size: 12px; color: #fff; cursor: pointer; font-weight: 600; }
    .cfd-clear:hover { background: #f3f4f6; }
    .cfd-apply:hover { background: #c53030; }

    /* ══ ADD / EDIT CHEQUE MODAL ══ */
    .cheque-modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.45); z-index: 10100;
      align-items: center; justify-content: center;
    }
    .cheque-modal-overlay.open { display: flex; }
    .cheque-modal {
      background: #fff; border-radius: 10px;
      width: 92%; max-width: 520px;
      box-shadow: 0 20px 60px rgba(0,0,0,.22);
      display: flex; flex-direction: column;
      max-height: 90vh; overflow-y: auto;
    }
    .cheque-modal-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 22px 14px; border-bottom: 1px solid #f0f0f0;
      position: sticky; top: 0; background: #fff; z-index: 1;
    }
    .cheque-modal-title { font-size: 16px; font-weight: 700; color: #1e293b; }
    .cheque-modal-close { background: none; border: none; cursor: pointer; font-size: 22px; color: #9ca3af; line-height: 1; padding: 0 4px; }
    .cheque-modal-close:hover { color: #374151; }
    .cheque-modal-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 14px; }
    .cheque-modal-footer {
      display: flex; justify-content: flex-end; gap: 10px;
      padding: 14px 22px 18px; border-top: 1px solid #f0f0f0;
      position: sticky; bottom: 0; background: #fff;
    }

    .cm-field { display: flex; flex-direction: column; gap: 5px; }
    .cm-label { font-size: 13px; font-weight: 500; color: #374151; }
    .cm-input {
      border: 1.5px solid #e2e8f0; border-radius: 6px;
      padding: 10px 12px; font-size: 14px; color: #1e293b;
      outline: none; width: 100%; box-sizing: border-box;
      transition: border-color .15s;
    }
    .cm-input:focus { border-color: #2563eb; }
    .cm-input::placeholder { color: #94a3b8; }
    .cm-input:disabled {
      background: #f8fafc;
      color: #64748b;
      cursor: not-allowed;
    }
    .cm-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .cm-btn-cancel { border: 1.5px solid #cbd5e1; background: #fff; border-radius: 20px; padding: 9px 22px; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; transition: background .15s; }
    .cm-btn-cancel:hover { background: #f8fafc; }
    .cm-btn-save { border: none; background: #e11d48; border-radius: 20px; padding: 9px 26px; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; transition: background .15s; }
    .cm-btn-save:hover { background: #be123c; }

    /* ══ DEPOSIT MODAL ══ */
    .deposit-modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.45); z-index: 10200;
      align-items: center; justify-content: center;
    }
    .deposit-modal-overlay.open { display: flex; }
    .deposit-modal {
      background: #fff; border-radius: 10px;
      width: 92%; max-width: 420px;
      box-shadow: 0 20px 60px rgba(0,0,0,.22);
    }
    .deposit-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px 14px; border-bottom: 1px solid #f0f0f0; }
    .deposit-modal-title { font-size: 16px; font-weight: 700; color: #1e293b; }
    .deposit-modal-close { background: none; border: none; cursor: pointer; font-size: 22px; color: #9ca3af; line-height: 1; }
    .deposit-modal-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 14px; }
    .deposit-modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px 18px; border-top: 1px solid #f0f0f0; }
    .deposit-info-row { display: flex; justify-content: space-between; font-size: 13px; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
    .deposit-info-row:last-child { border-bottom: none; }
    .deposit-info-label { color: #64748b; }
    .deposit-info-value { font-weight: 600; color: #1e293b; }

    /* ══ VIEW HISTORY MODAL ══ */
    .history-modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.45); z-index: 10300;
      align-items: center; justify-content: center;
    }
    .history-modal-overlay.open { display: flex; }
    .history-modal { background: #fff; border-radius: 12px; width: 90%; max-width: 560px; max-height: 80vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
    .history-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #f3f4f6; }
    .history-modal-title { font-size: 16px; font-weight: 700; color: #1e293b; }
    .history-modal-close { background: none; border: none; cursor: pointer; font-size: 20px; color: #9ca3af; line-height: 1; padding: 0 4px; }
    .history-modal-close:hover { color: #374151; }
    .history-modal-body { padding: 20px; overflow-y: auto; flex: 1; }
    .history-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f3f4f6; }
    .history-item:last-child { border-bottom: none; }
    .history-dot { width: 10px; height: 10px; border-radius: 50%; background: #2563eb; flex-shrink: 0; margin-top: 4px; }
    .history-action { font-size: 13px; font-weight: 600; color: #374151; }
    .history-meta { font-size: 12px; color: #9ca3af; margin-top: 2px; }

    /* Empty state */
    .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; }
    .empty-state p { font-size: 14px; }

    /* Print */
    @media print {
      body * { visibility: hidden; }
      #print-area, #print-area * { visibility: visible; }
      #print-area { position: fixed; left: 0; top: 0; width: 100%; }
    }

    /* Resize cursor */
    body.col-resizing, body.col-resizing * { cursor: col-resize !important; user-select: none !important; }
  </style>
@section('title', 'Vyapar — Cheques')
@section('description', 'Manage cheque transactions in Vyapar accounting software.')
@section('page', 'cheques')

@section('content')

  <div class="cheques-page" id="chequesPage">

    <!-- ── Header ── -->
    <div class="d-flex justify-content-between align-items-center cheque-header">
      <span class="cheque-title">Cheque Details</span>
      <button type="button" class="cheque-search-icon-btn" onclick="toggleSearchBar()" title="Search">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
    </div>

    <!-- ── Transactions Bar ── -->
    <div class="d-flex justify-content-between align-items-center transactions-bar">
      <p class="transactions-title">Transactions</p>
      <div class="d-flex align-items-center gap-2">
        <div id="searchBarWrap" style="display:none;">
          <input
            type="text"
            class="txn-search-input"
            id="chequeSearchInput"
            placeholder="Search transactions..."
            oninput="applyAllChequeFilters(); syncChequeFilterIcons()"
          />
        </div>
      </div>
    </div>

    <!-- ── Table ── -->
    <div class="cheque-tbl-wrap">
      <table class="cheque-tbl" id="cheque-table">
        <colgroup>
          <col style="width:160px; min-width:100px;">
          <col style="width:auto;  min-width:160px;">
          <col style="width:130px; min-width:90px;">
          <col style="width:160px; min-width:110px;">
          <col style="width:160px; min-width:110px;">
          <col style="width:160px; min-width:100px;">
          <col style="width:120px; min-width:90px;">
          <col style="width:40px;  min-width:40px;">
        </colgroup>
        <thead>
          <tr id="cheque-thead-row">

            {{-- TYPE --}}
            <th data-col="type">
              <span class="th-inner" onclick="sortChequeCol('type')">
                TYPE <i class="th-sort-arrow"></i>
                <i class="fa-solid fa-filter th-filter-icon" id="cfi-type" onclick="toggleChequeColFilter(event,'ccf-type')"></i>
              </span>
              <div class="col-resize-handle"></div>
              <div class="col-filter-dd" id="ccf-type" onclick="event.stopPropagation()">
                <div class="cfd-title">Filter by Type</div>
                <label class="cfd-cb-row"><input type="checkbox" value="sale"        onchange="syncChequeFilterIcons()"> Sale</label>
                <label class="cfd-cb-row"><input type="checkbox" value="purchase"    onchange="syncChequeFilterIcons()"> Purchase</label>
                <label class="cfd-cb-row"><input type="checkbox" value="payment_in"  onchange="syncChequeFilterIcons()"> Payment In</label>
                <label class="cfd-cb-row"><input type="checkbox" value="payment_out" onchange="syncChequeFilterIcons()"> Payment Out</label>
                <label class="cfd-cb-row"><input type="checkbox" value="other"       onchange="syncChequeFilterIcons()"> Other</label>
                <div class="cfd-actions">
                  <button class="cfd-clear" onclick="clearChequeColFilter('ccf-type')">Clear</button>
                  <button class="cfd-apply" onclick="applyAllChequeFilters(); closeAllChequeColFilters()">Apply</button>
                </div>
              </div>
            </th>

            {{-- NAME --}}
            <th data-col="name">
              <span class="th-inner" onclick="sortChequeCol('name')">
                NAME <i class="th-sort-arrow"></i>
                <i class="fa-solid fa-filter th-filter-icon" id="cfi-name" onclick="toggleChequeColFilter(event,'ccf-name')"></i>
              </span>
              <div class="col-resize-handle"></div>
              <div class="col-filter-dd" id="ccf-name" onclick="event.stopPropagation()">
                <div class="cfd-title">Filter by Name</div>
                <select class="cfd-select" id="ccf-name-op">
                  <option value="contains">Contains</option>
                  <option value="exact">Exact match</option>
                  <option value="starts">Starts with</option>
                </select>
                <input type="text" class="cfd-input" id="ccf-name-val" placeholder="Name" oninput="syncChequeFilterIcons()"/>
                <div class="cfd-actions">
                  <button class="cfd-clear" onclick="clearChequeColFilter('ccf-name')">Clear</button>
                  <button class="cfd-apply" onclick="applyAllChequeFilters(); closeAllChequeColFilters()">Apply</button>
                </div>
              </div>
            </th>

            {{-- REF NO --}}
            <th data-col="ref_no">
              <span class="th-inner" onclick="sortChequeCol('ref_no')">
                REF NO. <i class="th-sort-arrow"></i>
                <i class="fa-solid fa-filter th-filter-icon" id="cfi-ref_no" onclick="toggleChequeColFilter(event,'ccf-refno')"></i>
              </span>
              <div class="col-resize-handle"></div>
              <div class="col-filter-dd" id="ccf-refno" onclick="event.stopPropagation()">
                <div class="cfd-title">Filter by Ref No.</div>
                <input type="text" class="cfd-input" id="ccf-refno-val" placeholder="Ref No." oninput="syncChequeFilterIcons()"/>
                <div class="cfd-actions">
                  <button class="cfd-clear" onclick="clearChequeColFilter('ccf-refno')">Clear</button>
                  <button class="cfd-apply" onclick="applyAllChequeFilters(); closeAllChequeColFilters()">Apply</button>
                </div>
              </div>
            </th>

            {{-- TRANSACTION DATE --}}
            <th data-col="transaction_date">
              <span class="th-inner" onclick="sortChequeCol('transaction_date')">
                TRANSACTION DATE <i class="th-sort-arrow"></i>
                <i class="fa-solid fa-filter th-filter-icon" id="cfi-transaction_date" onclick="toggleChequeColFilter(event,'ccf-txdate')"></i>
              </span>
              <div class="col-resize-handle"></div>
              <div class="col-filter-dd" id="ccf-txdate" onclick="event.stopPropagation()">
                <div class="cfd-title">Filter by Transaction Date</div>
                <select class="cfd-select" id="ccf-txdate-op" onchange="toggleTxDateRange()">
                  <option value="equal">Equal To</option>
                  <option value="before">Before</option>
                  <option value="after">After</option>
                  <option value="range">Date Range</option>
                </select>
                <div class="cfd-date-lbl">Select Date</div>
                <input type="date" class="cfd-input" id="ccf-txdate-val" oninput="syncChequeFilterIcons()"/>
                <div id="ccf-txdate-range-wrap" style="display:none; margin-top:8px;">
                  <div class="cfd-date-lbl">To Date</div>
                  <input type="date" class="cfd-input" id="ccf-txdate-val2" oninput="syncChequeFilterIcons()"/>
                </div>
                <div class="cfd-actions">
                  <button class="cfd-clear" onclick="clearChequeColFilter('ccf-txdate')">Clear</button>
                  <button class="cfd-apply" onclick="applyAllChequeFilters(); closeAllChequeColFilters()">Apply</button>
                </div>
              </div>
            </th>

            {{-- CHEQUE DATE --}}
            <th data-col="cheque_date">
              <span class="th-inner" onclick="sortChequeCol('cheque_date')">
                CHEQUE DATE <i class="th-sort-arrow"></i>
                <i class="fa-solid fa-filter th-filter-icon" id="cfi-cheque_date" onclick="toggleChequeColFilter(event,'ccf-chqdate')"></i>
              </span>
              <div class="col-resize-handle"></div>
              <div class="col-filter-dd" id="ccf-chqdate" onclick="event.stopPropagation()">
                <div class="cfd-title">Filter by Cheque Date</div>
                <select class="cfd-select" id="ccf-chqdate-op" onchange="toggleChqDateRange()">
                  <option value="equal">Equal To</option>
                  <option value="before">Before</option>
                  <option value="after">After</option>
                  <option value="range">Date Range</option>
                </select>
                <div class="cfd-date-lbl">Select Date</div>
                <input type="date" class="cfd-input" id="ccf-chqdate-val" oninput="syncChequeFilterIcons()"/>
                <div id="ccf-chqdate-range-wrap" style="display:none; margin-top:8px;">
                  <div class="cfd-date-lbl">To Date</div>
                  <input type="date" class="cfd-input" id="ccf-chqdate-val2" oninput="syncChequeFilterIcons()"/>
                </div>
                <div class="cfd-actions">
                  <button class="cfd-clear" onclick="clearChequeColFilter('ccf-chqdate')">Clear</button>
                  <button class="cfd-apply" onclick="applyAllChequeFilters(); closeAllChequeColFilters()">Apply</button>
                </div>
              </div>
            </th>

            {{-- AMOUNT --}}
            <th data-col="amount" class="th-price-right">
              <span class="th-inner" onclick="sortChequeCol('amount')">
                AMOUNT <i class="th-sort-arrow"></i>
                <i class="fa-solid fa-filter th-filter-icon" id="cfi-amount" onclick="toggleChequeColFilter(event,'ccf-amount')"></i>
              </span>
              <div class="col-resize-handle"></div>
              <div class="col-filter-dd" id="ccf-amount" onclick="event.stopPropagation()">
                <div class="cfd-title">Filter by Amount</div>
                <select class="cfd-select" id="ccf-amount-op" onchange="toggleAmtRange()">
                  <option value="equal">Equal to</option>
                  <option value="lt">Less Than</option>
                  <option value="gt">Greater Than</option>
                  <option value="between">Between</option>
                </select>
                <input type="number" class="cfd-input" id="ccf-amount-val" placeholder="Amount" step="1" oninput="syncChequeFilterIcons()"/>
                <div id="ccf-amount-range-wrap" style="display:none; margin-top:8px;">
                  <input type="number" class="cfd-input" id="ccf-amount-val2" placeholder="Max Amount" step="1" oninput="syncChequeFilterIcons()"/>
                </div>
                <div class="cfd-actions">
                  <button class="cfd-clear" onclick="clearChequeColFilter('ccf-amount')">Clear</button>
                  <button class="cfd-apply" onclick="applyAllChequeFilters(); closeAllChequeColFilters()">Apply</button>
                </div>
              </div>
            </th>

            {{-- STATUS --}}
            <th data-col="status">
              <span class="th-inner" onclick="sortChequeCol('status')">
                STATUS <i class="th-sort-arrow"></i>
                <i class="fa-solid fa-filter th-filter-icon" id="cfi-status" onclick="toggleChequeColFilter(event,'ccf-status')"></i>
              </span>
              <div class="col-resize-handle"></div>
              <div class="col-filter-dd" id="ccf-status" onclick="event.stopPropagation()">
                <div class="cfd-title">Filter by Status</div>
                <label class="cfd-cb-row"><input type="checkbox" value="open"       onchange="syncChequeFilterIcons()"> Open</label>
                <label class="cfd-cb-row"><input type="checkbox" value="deposited"  onchange="syncChequeFilterIcons()"> Deposited</label>
                <label class="cfd-cb-row"><input type="checkbox" value="bounced"    onchange="syncChequeFilterIcons()"> Bounced</label>
                <label class="cfd-cb-row"><input type="checkbox" value="cancelled"  onchange="syncChequeFilterIcons()"> Cancelled</label>
                <div class="cfd-actions">
                  <button class="cfd-clear" onclick="clearChequeColFilter('ccf-status')">Clear</button>
                  <button class="cfd-apply" onclick="applyAllChequeFilters(); closeAllChequeColFilters()">Apply</button>
                </div>
              </div>
            </th>

            {{-- DOTS --}}
            <th data-col="actions"></th>

          </tr>
        </thead>
        <tbody id="cheque-tbody">
          @forelse($cheques as $cheque)
            <tr
              data-id="{{ $cheque->id }}"
              data-type="{{ strtolower($cheque->type) }}"
              data-name="{{ strtolower($cheque->name) }}"
              data-ref_no="{{ strtolower($cheque->ref_no ?? '') }}"
              data-transaction_date="{{ $cheque->transaction_date ? $cheque->transaction_date->format('d/m/Y') : '' }}"
              data-cheque_date="{{ $cheque->cheque_date ? $cheque->cheque_date->format('d/m/Y') : '' }}"
              data-amount="{{ $cheque->amount }}"
              data-status="{{ $cheque->status }}"
              onclick="setRowHighlight(this, event)"
            >
              <td><span class="type-label">{{ ucwords(str_replace('_', ' ', $cheque->type)) }}</span></td>
              <td title="{{ $cheque->name }}">{{ $cheque->name }}</td>
              <td>{{ $cheque->ref_no ?? '—' }}</td>
              <td>{{ $cheque->transaction_date ? $cheque->transaction_date->format('d/m/Y') : '—' }}</td>
              <td>{{ $cheque->cheque_date ? $cheque->cheque_date->format('d/m/Y') : '—' }}</td>
              <td class="td-price">Rs {{ number_format($cheque->amount, 2) }}</td>
              <td>
                @if($cheque->status === 'open')
                  <button class="deposit-link" onclick="markChequeDeposited({{ $cheque->id }}, event)">Open/Deposit</button>
                @else
                  {{ $cheque->statusBadge() }}
                @endif
              </td>
              <td style="display:none;">
                @if($cheque->status === 'open')
                  <button class="deposit-link" onclick="openDepositModal({{ $cheque->id }}, '{{ $cheque->name }}', '{{ number_format($cheque->amount,2) }}', event)">Deposit</button>
                @else
                  <span style="font-size:13px; color:#94a3b8;">—</span>
                @endif
              </td>
              <td class="td-actions">
                <div class="il-row-menu-wrap">
                  <button class="il-row-menu-btn" onclick="toggleChequeRowMenu(event,'chq-menu-{{ $loop->index }}')" aria-label="Row actions">⋮</button>
                  <div class="il-row-menu" id="chq-menu-{{ $loop->index }}">
                    <div class="il-row-menu-item" onclick="openEditModal({{ $cheque->id }})">
                      <i class="fa-regular fa-pen-to-square"></i> View/Edit
                    </div>
                    <div class="il-row-menu-item danger" onclick="deleteCheque({{ $cheque->id }})">
                      <i class="fa-regular fa-trash-can"></i> Delete
                    </div>
                    <div class="il-row-menu-item" onclick="printCheque({{ $cheque->id }})">
                      <i class="fa-solid fa-print"></i> Print
                    </div>
                    <div class="il-row-menu-item" onclick="viewHistory({{ $cheque->id }}, '{{ $cheque->name }}')">
                      <i class="fa-solid fa-clock-rotate-left"></i> View History
                    </div>
                    @if($cheque->status === 'open')
                    <div class="il-row-menu-item" onclick="markChequeDeposited({{ $cheque->id }}, event)">
                      <i class="fa-solid fa-building-columns"></i> Deposit
                    </div>
                    <div class="il-row-menu-item" onclick="updateStatus({{ $cheque->id }}, 'bounced')">
                      <i class="fa-solid fa-ban"></i> Mark Bounced
                    </div>
                    <div class="il-row-menu-item" onclick="updateStatus({{ $cheque->id }}, 'cancelled')">
                      <i class="fa-solid fa-xmark"></i> Cancel
                    </div>
                    @endif
                  </div>
                </div>
              </td>
            </tr>
          @empty
          @endforelse
        </tbody>
      </table>

      <div id="cheque-empty-state" class="empty-state" style="display:none;">
        <i class="fa-solid fa-inbox"></i>
        <p>No cheques match your filters.</p>
      </div>
    </div>

  </div>

  <!-- ══ ADD / EDIT CHEQUE MODAL ══ -->
  <div class="cheque-modal-overlay" id="chequeModalOverlay" onclick="closeChequeModal()">
    <div class="cheque-modal" onclick="event.stopPropagation()">
      <div class="cheque-modal-header">
        <span class="cheque-modal-title" id="chequeModalTitle">Add Cheque</span>
        <button class="cheque-modal-close" onclick="closeChequeModal()">×</button>
      </div>
      <div class="cheque-modal-body">
        <input type="hidden" id="cm_id">

        <div class="cm-row-2">
          <div class="cm-field">
            <label class="cm-label">Payment Type <span style="color:#e53e3e">*</span></label>
            <select class="cm-input" id="cm_type" disabled>
              <option value="cheque">Cheque</option>
            </select>
          </div>
          <div class="cm-field">
            <label class="cm-label">Name / Party <span style="color:#e53e3e">*</span></label>
            <input type="text" class="cm-input" id="cm_name" placeholder="Party name">
          </div>
        </div>

        <div class="cm-row-2">
          <div class="cm-field">
            <label class="cm-label">Ref / Cheque No.</label>
            <input type="text" class="cm-input" id="cm_ref_no" placeholder="Cheque number">
          </div>
          <div class="cm-field">
            <label class="cm-label">Amount <span style="color:#e53e3e">*</span></label>
            <input type="number" class="cm-input" id="cm_amount" min="0" step="1" placeholder="0">
          </div>
        </div>

        <div class="cm-row-2">
          <div class="cm-field">
            <label class="cm-label">Transaction Date <span style="color:#e53e3e">*</span></label>
            <input type="date" class="cm-input" id="cm_transaction_date" value="{{ date('Y-m-d') }}">
          </div>
          <div class="cm-field">
            <label class="cm-label">Cheque Date</label>
            <input type="date" class="cm-input" id="cm_cheque_date">
          </div>
        </div>

        <div class="cm-field" style="display:none;">
          <label class="cm-label">Bank Account</label>
          <select class="cm-input" id="cm_bank_account_id">
            <option value="">— Select Bank Account —</option>
            @foreach($bankAccounts as $ba)
              <option value="{{ $ba->id }}">{{ $ba->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="cm-field">
          <label class="cm-label">Description</label>
          <input type="text" class="cm-input" id="cm_notes" placeholder="Write description">
        </div>
      </div>
      <div class="cheque-modal-footer">
        <button class="cm-btn-cancel" onclick="closeChequeModal()">Cancel</button>
        <button class="cm-btn-save" onclick="saveCheque()">Save</button>
      </div>
    </div>
  </div>

  <!-- ══ DEPOSIT MODAL ══ -->
  <div class="deposit-modal-overlay" id="depositModalOverlay" onclick="closeDepositModal()">
    <div class="deposit-modal" onclick="event.stopPropagation()">
      <div class="deposit-modal-header">
        <span class="deposit-modal-title">Deposit Cheque</span>
        <button class="deposit-modal-close" onclick="closeDepositModal()">×</button>
      </div>
      <div class="deposit-modal-body">
        <input type="hidden" id="deposit_cheque_id">
        <div class="deposit-info-row"><span class="deposit-info-label">Party</span><span class="deposit-info-value" id="deposit_party_name">—</span></div>
        <div class="deposit-info-row"><span class="deposit-info-label">Amount</span><span class="deposit-info-value" id="deposit_amount">—</span></div>
        <div class="cm-field" style="margin-top:14px;">
          <label class="cm-label">Bank Account</label>
          <select class="cm-input" id="deposit_bank_account_id">
            <option value="">— Select Bank Account —</option>
            @foreach($bankAccounts as $ba)
              <option value="{{ $ba->id }}">{{ $ba->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="deposit-modal-footer">
        <button class="cm-btn-cancel" onclick="closeDepositModal()">Cancel</button>
        <button class="cm-btn-save" onclick="confirmDeposit()">Confirm Deposit</button>
      </div>
    </div>
  </div>

  <!-- ══ VIEW HISTORY MODAL ══ -->
  <div class="history-modal-overlay" id="historyModalOverlay" onclick="closeHistoryModal()">
    <div class="history-modal" onclick="event.stopPropagation()">
      <div class="history-modal-header">
        <span class="history-modal-title" id="historyModalTitle">Edit History</span>
        <button class="history-modal-close" onclick="closeHistoryModal()">×</button>
      </div>
      <div class="history-modal-body" id="historyModalBody"></div>
    </div>
  </div>

  <div id="print-area" style="display:none;"></div>

@endsection

@push('scripts')

  <script>
    /* ═══════════════════════════════════════════
        COLUMN RESIZE ENGINE
       ═══════════════════════════════════════════ */
    (function () {
      var isResizing = false, startX = 0, startW = 0, thEl = null, handleEl = null;
      var colIndex = -1, colEls = [];

      function lockColWidths(table) {
        var ths = table.querySelectorAll('thead th');
        var cols = table.querySelectorAll('colgroup col');
        ths.forEach(function (th, i) {
          var w = th.getBoundingClientRect().width;
          if (cols[i]) cols[i].style.width = w + 'px';
          th.style.width = th.style.minWidth = th.style.maxWidth = w + 'px';
        });
        table.style.tableLayout = 'fixed';
        table.style.width = 'auto';
        table.style.minWidth = '100%';
      }

      document.addEventListener('mousedown', function (e) {
        if (!e.target.classList.contains('col-resize-handle')) return;
        e.preventDefault(); e.stopPropagation();
        handleEl = e.target; thEl = handleEl.closest('th');
        var table = thEl.closest('table');
        lockColWidths(table);
        var ths = Array.from(table.querySelectorAll('thead th'));
        colIndex = ths.indexOf(thEl);
        colEls = Array.from(table.querySelectorAll('colgroup col'));
        isResizing = true; startX = e.clientX; startW = thEl.getBoundingClientRect().width;
        handleEl.classList.add('resizing'); document.body.classList.add('col-resizing');
      });

      document.addEventListener('mousemove', function (e) {
        if (!isResizing || !thEl) return;
        var newW = Math.max(50, startW + (e.clientX - startX));
        thEl.style.width = thEl.style.minWidth = thEl.style.maxWidth = newW + 'px';
        if (colEls[colIndex]) colEls[colIndex].style.width = newW + 'px';
      });

      document.addEventListener('mouseup', function () {
        if (!isResizing) return;
        isResizing = false;
        if (handleEl) handleEl.classList.remove('resizing');
        document.body.classList.remove('col-resizing');
        handleEl = null; thEl = null; colIndex = -1; colEls = [];
      });
    })();

    /* ═══════════════════════════════════════════
        SORT
       ═══════════════════════════════════════════ */
    var chqSortCol = null, chqSortAsc = true;
    function sortChequeCol(col) {
      if (chqSortCol === col) { chqSortAsc = !chqSortAsc; } else { chqSortCol = col; chqSortAsc = true; }
      document.querySelectorAll('#cheque-thead-row th').forEach(function(th){ th.classList.remove('sort-asc','sort-desc'); });
      var th = document.querySelector('#cheque-thead-row th[data-col="'+col+'"]');
      if (th) th.classList.add(chqSortAsc ? 'sort-asc' : 'sort-desc');
      var tbody = document.getElementById('cheque-tbody');
      var rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort(function(a, b) {
        var av = a.dataset[col] || ''; var bv = b.dataset[col] || '';
        if (col === 'amount') return chqSortAsc ? parseFloat(av)-parseFloat(bv) : parseFloat(bv)-parseFloat(av);
        if (col === 'transaction_date' || col === 'cheque_date') {
          var ap = av.split('/'); var bp = bv.split('/');
          var ad = ap.length===3 ? new Date(ap[2],ap[1]-1,ap[0]) : new Date(av||0);
          var bd = bp.length===3 ? new Date(bp[2],bp[1]-1,bp[0]) : new Date(bv||0);
          return chqSortAsc ? ad-bd : bd-ad;
        }
        return chqSortAsc ? av.localeCompare(bv) : bv.localeCompare(av);
      });
      rows.forEach(function(r){ tbody.appendChild(r); });
      highlightFirstVisible();
    }

    /* ═══════════════════════════════════════════
        ROW ACTION MENU
       ═══════════════════════════════════════════ */
    function toggleChequeRowMenu(e, id) {
      e.stopPropagation();
      var btn = e.currentTarget; var rect = btn.getBoundingClientRect();
      document.querySelectorAll('.il-row-menu.open').forEach(function(m){ if(m.id!==id) m.classList.remove('open'); });
      var menu = document.getElementById(id);
      var isOpen = menu.classList.contains('open');
      menu.classList.remove('open');
      if (!isOpen) {
        menu.style.top = (rect.bottom + window.scrollY + 2)+'px';
        menu.style.left = rect.left+'px';
        menu.classList.add('open');
        requestAnimationFrame(function(){
          var mRect = menu.getBoundingClientRect();
          menu.style.left = (rect.right - mRect.width)+'px';
          if (parseFloat(menu.style.left) < 0) menu.style.left = '4px';
        });
      }
    }

    /* ═══════════════════════════════════════════
        FILTER ENGINE
       ═══════════════════════════════════════════ */
    function parseDMY(str) {
      var p = (str||'').split('/');
      if (p.length===3) return new Date(p[2],p[1]-1,p[0]);
      return str ? new Date(str) : null;
    }

    function applyAllChequeFilters() {
      var q = (document.getElementById('chequeSearchInput').value||'').toLowerCase().trim();
      var typeChecked   = Array.from(document.querySelectorAll('#ccf-type input:checked')).map(function(c){ return c.value.toLowerCase(); });
      var statusChecked = Array.from(document.querySelectorAll('#ccf-status input:checked')).map(function(c){ return c.value.toLowerCase(); });
      var nameOp  = document.getElementById('ccf-name-op').value;
      var nameVal = document.getElementById('ccf-name-val').value.toLowerCase().trim();
      var refNoVal = (document.getElementById('ccf-refno-val').value||'').toLowerCase().trim();

      var txdOp   = document.getElementById('ccf-txdate-op').value;
      var txdVal  = document.getElementById('ccf-txdate-val').value;
      var txdVal2 = document.getElementById('ccf-txdate-val2').value;
      var chqdOp  = document.getElementById('ccf-chqdate-op').value;
      var chqdVal = document.getElementById('ccf-chqdate-val').value;
      var chqdVal2= document.getElementById('ccf-chqdate-val2').value;

      var amtOp   = document.getElementById('ccf-amount-op').value;
      var amtVal  = parseFloat(document.getElementById('ccf-amount-val').value);
      var amtVal2 = parseFloat(document.getElementById('ccf-amount-val2').value);

      var rows = document.querySelectorAll('#cheque-tbody tr');
      var visibleCount = 0;

      function matchDate(rowDateStr, op, v1, v2) {
        if (!v1) return true;
        var rd = parseDMY(rowDateStr); if (!rd) return true;
        var fd = new Date(v1); fd.setHours(0,0,0,0); rd.setHours(0,0,0,0);
        if (op==='equal')  return rd.toDateString()===fd.toDateString();
        if (op==='before') return rd < fd;
        if (op==='after')  return rd > fd;
        if (op==='range' && v2) { var fd2=new Date(v2); fd2.setHours(0,0,0,0); return rd>=fd && rd<=fd2; }
        return true;
      }

      rows.forEach(function(row){
        var show = true;
        var rowType   = (row.dataset.type||'').toLowerCase();
        var rowName   = (row.dataset.name||'').toLowerCase();
        var rowRefNo  = (row.dataset.ref_no||'').toLowerCase();
        var rowStatus = (row.dataset.status||'').toLowerCase();
        var rowTxDate = row.dataset.transaction_date||'';
        var rowChqDate= row.dataset.cheque_date||'';
        var rowAmt    = parseFloat(row.dataset.amount||0);

        if (q && !row.textContent.toLowerCase().includes(q)) show = false;
        if (show && typeChecked.length>0 && !typeChecked.includes(rowType)) show = false;
        if (show && statusChecked.length>0 && !statusChecked.includes(rowStatus)) show = false;
        if (show && nameVal) {
          if (nameOp==='contains' && !rowName.includes(nameVal)) show=false;
          else if (nameOp==='exact' && rowName!==nameVal) show=false;
          else if (nameOp==='starts' && !rowName.startsWith(nameVal)) show=false;
        }
        if (show && refNoVal && !rowRefNo.includes(refNoVal)) show=false;
        if (show && !matchDate(rowTxDate,  txdOp,  txdVal,  txdVal2))  show=false;
        if (show && !matchDate(rowChqDate, chqdOp, chqdVal, chqdVal2)) show=false;
        if (show && !isNaN(amtVal)) {
          if (amtOp==='equal'   && rowAmt!==amtVal) show=false;
          else if (amtOp==='lt' && rowAmt>=amtVal) show=false;
          else if (amtOp==='gt' && rowAmt<=amtVal) show=false;
          else if (amtOp==='between' && !isNaN(amtVal2) && (rowAmt<amtVal||rowAmt>amtVal2)) show=false;
        }
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
      });

      document.getElementById('cheque-empty-state').style.display =
        (visibleCount===0 && rows.length>0) ? 'block' : 'none';
      syncChequeFilterIcons();
      var currentHL = document.querySelector('#cheque-tbody tr.tr-highlight');
      if (!currentHL || currentHL.style.display==='none') highlightFirstVisible();
    }

    /* ═══════════════════════════════════════════
        DROPDOWN CONTROLS
       ═══════════════════════════════════════════ */
    function toggleChequeColFilter(e, id) {
      e.stopPropagation();
      var th = e.currentTarget.closest('th');
      var dd = document.getElementById(id);
      var wasOpen = dd.classList.contains('open');
      closeAllChequeColFilters();
      if (!wasOpen) {
        var rect = th.getBoundingClientRect();
        document.body.appendChild(dd);
        dd.style.top = (rect.bottom + 2)+'px';
        dd.classList.add('open');
        requestAnimationFrame(function(){
          var ddW = dd.offsetWidth;
          var left = rect.right - ddW;
          if (left < 4) left = 4;
          if (left + ddW > window.innerWidth - 4) left = window.innerWidth - ddW - 4;
          dd.style.left = left+'px';
        });
      }
    }

    function closeAllChequeColFilters() {
      document.querySelectorAll('.col-filter-dd.open').forEach(function(d){ d.classList.remove('open'); });
    }

    function clearChequeColFilter(id) {
      var dd = document.getElementById(id);
      dd.querySelectorAll('input[type=checkbox]').forEach(function(c){ c.checked=false; });
      dd.querySelectorAll('input[type=text],input[type=number],input[type=date]').forEach(function(i){ i.value=''; });
      dd.querySelectorAll('select').forEach(function(s){ s.selectedIndex=0; });
      ['ccf-txdate-range-wrap','ccf-chqdate-range-wrap','ccf-amount-range-wrap'].forEach(function(wid){
        var el = document.getElementById(wid); if(el) el.style.display='none';
      });
      applyAllChequeFilters();
    }

    function syncChequeFilterIcons() {
      var checks = {
        'type':             function(){ return document.querySelectorAll('#ccf-type input:checked').length > 0; },
        'name':             function(){ return document.getElementById('ccf-name-val').value.trim()!==''; },
        'ref_no':           function(){ return document.getElementById('ccf-refno-val').value.trim()!==''; },
        'transaction_date': function(){ return document.getElementById('ccf-txdate-val').value!==''; },
        'cheque_date':      function(){ return document.getElementById('ccf-chqdate-val').value!==''; },
        'amount':           function(){ return document.getElementById('ccf-amount-val').value!==''; },
        'status':           function(){ return document.querySelectorAll('#ccf-status input:checked').length > 0; },
      };
      Object.entries(checks).forEach(function(entry){
        var isActive = entry[1]();
        var icon = document.getElementById('cfi-'+entry[0]);
        if (icon) icon.classList.toggle('active', isActive);
        var th = document.querySelector('#cheque-thead-row th[data-col="'+entry[0]+'"]');
        if (th) th.classList.toggle('filter-active', isActive);
      });
      var si = document.getElementById('chequeSearchInput');
      if (si) si.classList.toggle('has-value', si.value.trim()!=='');
    }

    function toggleTxDateRange()  { document.getElementById('ccf-txdate-range-wrap').style.display  = document.getElementById('ccf-txdate-op').value==='range'   ? 'block':'none'; }
    function toggleChqDateRange() { document.getElementById('ccf-chqdate-range-wrap').style.display = document.getElementById('ccf-chqdate-op').value==='range'  ? 'block':'none'; }
    function toggleAmtRange()     { document.getElementById('ccf-amount-range-wrap').style.display  = document.getElementById('ccf-amount-op').value==='between' ? 'block':'none'; }

    /* Search bar toggle */
    function toggleSearchBar() {
      var wrap = document.getElementById('searchBarWrap');
      var visible = wrap.style.display !== 'none';
      wrap.style.display = visible ? 'none' : '';
      if (!visible) document.getElementById('chequeSearchInput').focus();
    }

    /* ═══════════════════════════════════════════
        ADD / EDIT MODAL
       ═══════════════════════════════════════════ */
    function setChequeAmountDateLocked(locked) {
      document.getElementById('cm_amount').disabled = locked;
      document.getElementById('cm_transaction_date').disabled = locked;
    }

    function openAddModal() {
      document.getElementById('chequeModalTitle').textContent = 'Add Cheque';
      setChequeAmountDateLocked(false);
      document.getElementById('cm_id').value = '';
      document.getElementById('cm_type').value = 'cheque';
      document.getElementById('cm_name').value = '';
      document.getElementById('cm_ref_no').value = '';
      document.getElementById('cm_amount').value = '';
      document.getElementById('cm_transaction_date').value = '{{ date("Y-m-d") }}';
      document.getElementById('cm_cheque_date').value = '';
      document.getElementById('cm_bank_account_id').value = '';
      document.getElementById('cm_notes').value = '';
      document.getElementById('chequeModalOverlay').classList.add('open');
    }

    function openEditModal(id) {
      closeAllMenus();
      setChequeAmountDateLocked(true);
      fetch('/dashboard/cheques/'+id, { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': window.App.csrfToken } })
        .then(function(r){ return r.json(); }).then(function(d){
          if (!d.success) { alert('Could not load cheque.'); return; }
          var c = d.cheque;
          document.getElementById('chequeModalTitle').textContent = 'Edit Cheque';
          document.getElementById('cm_id').value = c.id;
          document.getElementById('cm_type').value = 'cheque';
          document.getElementById('cm_name').value = c.name;
          document.getElementById('cm_ref_no').value = c.ref_no||'';
          document.getElementById('cm_amount').value = c.amount;
          /* convert dd/mm/yyyy → yyyy-mm-dd for <input type=date> */
          function dmyToYmd(s){ if(!s||!s.includes('/')) return s||''; var p=s.split('/'); return p[2]+'-'+p[1]+'-'+p[0]; }
          document.getElementById('cm_transaction_date').value = dmyToYmd(c.transaction_date);
          document.getElementById('cm_cheque_date').value = dmyToYmd(c.cheque_date);
          document.getElementById('cm_bank_account_id').value = c.bank_account_id||'';
          document.getElementById('cm_notes').value = c.notes||'';
          document.getElementById('chequeModalOverlay').classList.add('open');
        }).catch(function(){ alert('Network error.'); });
    }

    function closeChequeModal() { document.getElementById('chequeModalOverlay').classList.remove('open'); }

    function saveCheque() {
      var id     = document.getElementById('cm_id').value;
      var method = id ? 'PUT' : 'POST';
      var url    = id ? '/dashboard/cheques/'+id : '/dashboard/cheques';

      var body = {
        type:             document.getElementById('cm_type').value,
        name:             document.getElementById('cm_name').value.trim(),
        ref_no:           document.getElementById('cm_ref_no').value.trim(),
        amount:           parseFloat(document.getElementById('cm_amount').value)||0,
        transaction_date: document.getElementById('cm_transaction_date').value,
        cheque_date:      document.getElementById('cm_cheque_date').value,
        bank_account_id:  document.getElementById('cm_bank_account_id').value||null,
        notes:            document.getElementById('cm_notes').value.trim(),
      };

      if (!body.name)             { alert('Party name is required.'); return; }
      if (!body.amount)           { alert('Amount is required.'); return; }
      if (!body.transaction_date) { alert('Transaction date is required.'); return; }

      fetch(url, {
        method: method,
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': window.App.csrfToken },
        body: JSON.stringify(body)
      })
      .then(function(r){ return r.json(); }).then(function(d){
        if (d.success) { closeChequeModal(); window.location.reload(); }
        else { alert(d.message||'Error saving cheque.'); }
      }).catch(function(){ alert('Network error.'); });
    }

    /* ═══════════════════════════════════════════
        DELETE
       ═══════════════════════════════════════════ */
    function deleteCheque(id) {
      closeAllMenus();
      if (!confirm('Delete this cheque?')) return;
      fetch('/dashboard/cheques/'+id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': window.App.csrfToken, 'Accept':'application/json' }
      }).then(function(){ window.location.reload(); }).catch(function(){ window.location.reload(); });
    }

    /* ═══════════════════════════════════════════
        DEPOSIT MODAL
       ═══════════════════════════════════════════ */
    function markChequeDeposited(id, e) {
      if (e) e.stopPropagation();
      closeAllMenus();
      fetch('/dashboard/cheques/'+id+'/deposit', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': window.App.csrfToken },
        body: JSON.stringify({ bank_account_id: null })
      })
      .then(function(r){ return r.json(); }).then(function(d){
        if (d.success) window.location.reload();
        else alert(d.message||'Error depositing cheque.');
      }).catch(function(){ alert('Network error.'); });
    }

    function openDepositModal(id, name, amount, e) {
      if (e) e.stopPropagation();
      document.getElementById('deposit_cheque_id').value = id;
      document.getElementById('deposit_party_name').textContent = name;
      document.getElementById('deposit_amount').textContent = 'Rs '+amount;
      document.getElementById('depositModalOverlay').classList.add('open');
    }
    function closeDepositModal() { document.getElementById('depositModalOverlay').classList.remove('open'); }

    function confirmDeposit() {
      var id = document.getElementById('deposit_cheque_id').value;
      if (!id || id.toString().startsWith('demo')) { alert('This is a demo row.'); return; }
      var bankId = document.getElementById('deposit_bank_account_id').value;
      fetch('/dashboard/cheques/'+id+'/deposit', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': window.App.csrfToken },
        body: JSON.stringify({ bank_account_id: bankId||null })
      })
      .then(function(r){ return r.json(); }).then(function(d){
        if (d.success) { closeDepositModal(); window.location.reload(); }
        else { alert(d.message||'Error depositing cheque.'); }
      }).catch(function(){ alert('Network error.'); });
    }

    /* ═══════════════════════════════════════════
        STATUS UPDATE (Bounce / Cancel)
       ═══════════════════════════════════════════ */
    function updateStatus(id, status) {
      closeAllMenus();
      var label = status==='bounced' ? 'mark as bounced' : 'cancel';
      if (!confirm('Are you sure you want to '+label+' this cheque?')) return;
      fetch('/dashboard/cheques/'+id+'/status', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': window.App.csrfToken },
        body: JSON.stringify({ status: status })
      })
      .then(function(r){ return r.json(); }).then(function(d){
        if (d.success) window.location.reload();
        else alert(d.message||'Error updating status.');
      }).catch(function(){ alert('Network error.'); });
    }

    /* ═══════════════════════════════════════════
        PRINT
       ═══════════════════════════════════════════ */
    function printCheque(id) {
      closeAllMenus();
      var row = document.querySelector('#cheque-tbody tr[data-id="'+id+'"]');
      if (!row) return;
      var printArea = document.getElementById('print-area'); printArea.style.display='block';
      printArea.innerHTML = `
        <div style="font-family:Arial,sans-serif;padding:30px;max-width:480px;margin:auto;border:1px solid #e5e7eb;border-radius:8px;">
          <div style="text-align:center;margin-bottom:20px;">
            <h2 style="font-size:18px;font-weight:700;margin:0;">Cheque Receipt</h2>
          </div>
          <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <tr><td style="padding:6px 0;color:#64748b;">Type</td><td style="padding:6px 0;font-weight:600;">${row.dataset.type}</td></tr>
            <tr><td style="padding:6px 0;color:#64748b;">Party</td><td style="padding:6px 0;font-weight:600;">${row.querySelector('td:nth-child(2)').textContent}</td></tr>
            <tr><td style="padding:6px 0;color:#64748b;">Ref No.</td><td style="padding:6px 0;font-weight:600;">${row.dataset.ref_no||'—'}</td></tr>
            <tr><td style="padding:6px 0;color:#64748b;">Txn Date</td><td style="padding:6px 0;font-weight:600;">${row.dataset.transaction_date||'—'}</td></tr>
            <tr><td style="padding:6px 0;color:#64748b;">Cheque Date</td><td style="padding:6px 0;font-weight:600;">${row.dataset.cheque_date||'—'}</td></tr>
            <tr><td style="padding:6px 0;color:#64748b;">Amount</td><td style="padding:6px 0;font-weight:600;">Rs ${parseFloat(row.dataset.amount).toLocaleString()}</td></tr>
            <tr><td style="padding:6px 0;color:#64748b;">Status</td><td style="padding:6px 0;font-weight:600;">${row.dataset.status}</td></tr>
          </table>
        </div>`;
      window.print();
      setTimeout(function(){ printArea.style.display='none'; }, 1000);
    }

    /* ═══════════════════════════════════════════
        VIEW HISTORY
       ═══════════════════════════════════════════ */
    function viewHistory(id, name) {
      closeAllMenus();
      document.getElementById('historyModalTitle').textContent = 'History — '+name;
      var body = document.getElementById('historyModalBody');
      body.innerHTML = '<div style="text-align:center;padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i></div>';
      document.getElementById('historyModalOverlay').classList.add('open');

      if (!id || id.toString().startsWith('demo')) {
        body.innerHTML = '<p class="text-center p-4" style="color:#94a3b8;">No history available.</p>';
        return;
      }
      fetch('/dashboard/cheques/'+id+'/history', { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': window.App.csrfToken } })
        .then(function(r){ return r.json(); }).then(function(d){
          if (d.success && d.history && d.history.length>0) {
            var html = '';
            d.history.forEach(function(item){
              html += '<div class="history-item"><div class="history-dot"></div><div><div class="history-action">'+item.action+'</div><div class="history-meta">'+item.created_at+(item.amount ? ' &nbsp;·&nbsp; Rs '+parseFloat(item.amount).toLocaleString() : '')+'</div></div></div>';
            });
            body.innerHTML = html;
          } else {
            body.innerHTML = '<p class="text-center p-4" style="color:#94a3b8;">No history found.</p>';
          }
        }).catch(function(){ body.innerHTML = '<p class="text-center p-4" style="color:#94a3b8;">No history found.</p>'; });
    }
    function closeHistoryModal() { document.getElementById('historyModalOverlay').classList.remove('open'); }

    /* ═══════════════════════════════════════════
        ROW HIGHLIGHT
       ═══════════════════════════════════════════ */
    function setRowHighlight(row, event) {
      if (event && event.target && event.target.closest('.il-row-menu-wrap')) return;
      document.querySelectorAll('#cheque-tbody tr.tr-highlight').forEach(function(r){ r.classList.remove('tr-highlight'); });
      row.classList.add('tr-highlight');
    }
    function highlightFirstVisible() {
      document.querySelectorAll('#cheque-tbody tr.tr-highlight').forEach(function(r){ r.classList.remove('tr-highlight'); });
      var first = document.querySelector('#cheque-tbody tr:not([style*="display: none"]):not([style*="display:none"])');
      if (first) first.classList.add('tr-highlight');
    }

    /* ═══════════════════════════════════════════
        CLOSE ALL
       ═══════════════════════════════════════════ */
    function closeAllMenus() {
      closeAllChequeColFilters();
      document.querySelectorAll('.il-row-menu.open').forEach(function(m){ m.classList.remove('open'); });
    }

    document.addEventListener('click', closeAllMenus);
    document.addEventListener('keydown', function(e){ if (e.key==='Escape') { closeAllMenus(); closeHistoryModal(); closeChequeModal(); closeDepositModal(); } });
    document.addEventListener('DOMContentLoaded', function(){ highlightFirstVisible(); });
  </script>
@endpush
