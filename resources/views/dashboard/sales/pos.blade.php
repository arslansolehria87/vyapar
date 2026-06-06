{{-- resources/views/dashboard/sales/pos.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>POS – Sale</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
  .custom-table thead th {
    font-size: 13px; color: #6c757d; font-weight: 500;
    border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 5;
    background-color: #fafafa; white-space: nowrap; position: relative;
  }
  .custom-table tbody td {
    font-size: 14px; padding: 14px 10px;
    border-bottom: 1px solid #f1f1f1; white-space: nowrap;
  }
  .custom-table tbody tr:hover { background-color: #fafafa; }
  .custom-table th, .custom-table td { border-right: 1px solid #f1f1f1; }
  .custom-table th:last-child, .custom-table td:last-child { border-right: none; }
  .table-wrapper {
    overflow-x: auto; overflow-y: auto;
    max-height: 68vh; border: 1px solid #eef2f7; border-radius: 12px;
  }
  @media (max-width: 991px) {
    .table-wrapper { max-height: none; border-radius: 8px; }
    .custom-table thead th { font-size: 11px; padding: 8px 6px; }
    .custom-table tbody td { font-size: 12px; padding: 10px 6px; }
  }
  @media (max-width: 575px) {
    .custom-table thead th { font-size: 10px; padding: 6px 4px; }
    .custom-table tbody td { font-size: 11px; padding: 8px 4px; }
  }
</style>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:#1976d2;--blue-light:#e3f2fd;--blue-dark:#1565c0;
  --green:#a5d6a7;--green-dark:#1b5e20;--green-btn:#4caf50;
  --red:#e53935;--border:#e0e0e0;--bg:#f0f2f5;--white:#fff;
  --text:#222;--muted:#555;--light-muted:#888;
  --row-hover:#f7fbff;--row-sel:#e3f2fd;
}
html,body{height:100%;overflow:hidden;font-family:'Inter',sans-serif;font-size:13px;background:var(--bg);color:var(--text)}

/* ── TOP BAR ── */
.app-bar{height:34px;background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 10px;flex-shrink:0;user-select:none}
.brand{display:flex;align-items:center;gap:6px;font-weight:700;font-size:15px;color:var(--red)}
.brand svg{width:20px;height:20px}
.menu-btns{display:flex;gap:2px;margin-left:8px}
.menu-btns button{background:none;border:none;cursor:pointer;font-size:12px;color:#444;padding:3px 9px;border-radius:3px;font-family:inherit}
.menu-btns button:hover{background:var(--bg)}
.support{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:6px}
.support a{color:var(--blue);text-decoration:none}
.support strong{color:#111}
.win-btns{display:flex}
.win-btns button{background:none;border:none;cursor:pointer;width:38px;height:34px;font-size:14px;color:#555}
.win-btns button:hover{background:#e0e0e0}
.win-btns .close:hover{background:var(--red);color:#fff}

/* ── TAB BAR ── */
.tab-bar{height:34px;background:#f5f5f5;border-bottom:1px solid #ddd;display:flex;align-items:flex-end;padding:0 4px 0 0;flex-shrink:0}
.tab{display:flex;align-items:center;gap:5px;background:#e8e8e8;border:1px solid #ccc;border-bottom:none;border-radius:5px 5px 0 0;padding:5px 10px;font-size:12px;color:#444;cursor:pointer;height:30px;min-width:80px}
.tab.active{background:#fff;border-color:#ddd;color:#111;font-weight:600}
.tab .close-x{background:none;border:none;cursor:pointer;font-size:11px;color:#999;padding:0 0 0 2px;line-height:1}
.tab .close-x:hover{color:var(--red)}
.new-tab-btn{background:none;border:none;cursor:pointer;font-size:12px;color:var(--muted);padding:4px 10px;height:30px;display:flex;align-items:center;gap:4px}
.new-tab-btn:hover{color:var(--blue)}

/* ── MAIN LAYOUT ── */
.pos-wrap{flex:1;display:flex;flex-direction:column;min-height:0;overflow:hidden;height:calc(100vh - 68px)}

/* Search */
.search-bar{background:#fff;border-bottom:1px solid var(--border);padding:7px 10px;flex-shrink:0}
.search-inner{position:relative}
.search-inner input{width:100%;height:34px;border:1.5px solid var(--blue);border-radius:4px;padding:0 34px 0 10px;font-size:13px;outline:none;font-family:inherit}
.search-inner input:focus{border-color:var(--blue-dark)}
.search-inner .s-icon{position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#888;font-size:14px;pointer-events:none}

/* Search Dropdown */
.search-dd{display:none;position:absolute;top:calc(100% + 3px);left:0;right:0;background:#fff;border:1px solid #ddd;border-radius:4px;box-shadow:0 4px 16px rgba(0,0,0,.1);z-index:900}
.search-dd.open{display:block}
.search-dd table{width:100%;border-collapse:collapse}
.search-dd thead tr{background:#f5f5f5}
.search-dd th,.search-dd td{padding:6px 10px;font-size:12px;text-align:left;border-bottom:1px solid #f0f0f0}
.search-dd tbody tr{cursor:pointer}
.search-dd tbody tr:hover{background:var(--blue-light)}

/* ── SPLIT ── */
.split{flex:1;display:flex;min-height:0;overflow:hidden}

/* LEFT */
.left{flex:1;display:flex;flex-direction:column;min-height:0;border-right:1px solid var(--border);background:#fff}
.table-wrap{flex:1;overflow-y:auto;min-height:0}
.bill-table{width:100%;border-collapse:collapse;font-size:13px}
.bill-table thead th{background:#f5f5f5;border-bottom:1px solid var(--border);padding:7px 10px;font-weight:600;font-size:12px;color:var(--muted);position:sticky;top:0;z-index:1}
.bill-table tbody td{padding:7px 10px;border-bottom:1px solid #f5f5f5;color:#222}
.bill-table tbody tr{cursor:pointer}
.bill-table tbody tr:hover{background:var(--row-hover)}
.bill-table tbody tr.sel{background:var(--row-sel)}
.empty-msg td{text-align:center;color:#bbb;padding:40px!important}

/* Shortcuts */
.shortcuts{flex-shrink:0;background:#fff;border-top:1px solid var(--border);padding:6px 8px}
.sc-row{display:flex;gap:5px;margin-bottom:5px}
.sc-row:last-child{margin-bottom:0}
.sc-btn{flex:1;background:#f5f5f5;border:1px solid #ddd;border-radius:4px;padding:6px 4px;font-size:12px;text-align:center;cursor:pointer;color:#333;font-family:inherit;line-height:1.3}
.sc-btn:hover{background:var(--blue-light);border-color:#90caf9}
.sc-btn small{display:block;font-size:11px;color:#999;margin-top:1px}

/* RIGHT */
.right{width:350px;flex-shrink:0;background:#fff;display:flex;flex-direction:column;overflow-y:auto;padding:10px 12px;gap:9px}
.right label{font-size:12px;color:var(--muted);display:block;margin-bottom:3px}
.right input[type=text],.right input[type=number],.right select{width:100%;height:32px;border:1px solid #ccc;border-radius:4px;padding:0 8px;font-size:13px;outline:none;font-family:inherit}
.right input:focus,.right select:focus{border-color:var(--blue)}

/* Date row */
.date-row{display:flex;align-items:center;gap:6px}
.date-display{flex:1;height:32px;border:1px solid #ccc;border-radius:4px;padding:0 8px;font-size:13px;display:flex;align-items:center;background:#fff;cursor:pointer}
.date-display:hover{border-color:var(--blue)}
.cal-btn{width:32px;height:32px;border:1px solid #ccc;border-radius:4px;background:#f5f5f5;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cal-btn:hover{border-color:var(--blue);background:var(--blue-light)}

/* Summary */
.summary{background:#f7fbff;border:1px solid #dce8f5;border-radius:6px;padding:9px 11px}
.summary-top{display:flex;align-items:center;gap:7px;margin-bottom:3px}
.sum-icon{background:var(--blue-light);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;color:var(--blue);font-size:14px;flex-shrink:0}
.sum-total{font-weight:700;font-size:15px;color:#111;flex:1}
.sum-breakup{color:var(--blue);font-size:12px;cursor:pointer;text-align:right;line-height:1.2}
.sum-meta{font-size:12px;color:#777}

/* Payment */
.pay-row{display:flex;gap:8px}
.pay-row>div{flex:1}
.amt-wrap{display:flex;align-items:center;border:1px solid #ccc;border-radius:4px;overflow:hidden;height:32px}
.amt-wrap:focus-within{border-color:var(--blue)}
.amt-wrap .prefix{padding:0 7px;background:#f5f5f5;border-right:1px solid #ccc;color:#555;font-size:13px;white-space:nowrap}
.amt-wrap input{border:none;outline:none;width:100%;padding:0 7px;font-size:13px;height:100%;font-family:inherit}

.change-row{display:flex;justify-content:space-between;align-items:center;padding:4px 0}
.change-row .lbl{font-size:13px;color:var(--muted)}
.change-row .val{font-weight:700;font-size:15px;color:#111}

.btn-save{width:100%;height:40px;background:var(--green);border:none;border-radius:4px;font-weight:600;font-size:13px;color:var(--green-dark);cursor:pointer;font-family:inherit}
.btn-save:hover{background:#81c784}
.btn-credit{width:100%;height:38px;background:#f5f5f5;border:1px solid #ccc;border-radius:4px;font-weight:600;font-size:13px;color:#333;cursor:pointer;font-family:inherit}
.btn-credit:hover{background:#e0e0e0}

.bot-links{display:flex;justify-content:space-between}
.bot-links button{background:none;border:none;cursor:pointer;color:var(--blue);font-size:12px;padding:0;font-family:inherit}
.bot-links button:hover{text-decoration:underline}

/* Customer */
.cust-wrap{position:relative}
.cust-dd{display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:#fff;border:1px solid #ddd;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.1);z-index:300;max-height:200px;overflow-y:auto}
.cust-dd.open{display:block}
.cust-dd .add-new{padding:7px 10px;color:var(--blue);cursor:pointer;font-weight:600;font-size:12px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:4px}
.cust-dd .add-new:hover{background:#f5f5f5}
.cust-dd .c-item{padding:6px 10px;font-size:12px;cursor:pointer;border-bottom:1px solid #fafafa}
.cust-dd .c-item:hover{background:var(--blue-light)}
.cust-dd .c-item .c-name{font-weight:600;color:#222}
.cust-dd .c-item .c-phone{color:#888;font-size:11px}
.cust-dd .no-results{padding:10px;text-align:center;color:#bbb;font-size:12px}

/* ── CALENDAR ── */
.cal-pop{display:none;position:absolute;top:calc(100% + 4px);right:0;background:#fff;border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:400;min-width:220px;padding:8px}
.cal-pop.open{display:block}
.cal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;font-weight:600;font-size:13px}
.cal-nav{background:none;border:none;cursor:pointer;font-size:16px;color:#555;padding:2px 6px;border-radius:3px}
.cal-nav:hover{background:var(--bg)}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:1px;text-align:center}
.cal-dow{font-size:11px;font-weight:600;color:var(--muted);padding:2px 0}
.cal-day{font-size:12px;padding:4px 0;cursor:pointer;border-radius:50%;aspect-ratio:1;display:flex;align-items:center;justify-content:center}
.cal-day:hover{background:var(--blue-light)}
.cal-day.today{background:var(--blue);color:#fff}
.cal-day.other{color:#bbb}

/* ── MODALS ── */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.32);z-index:800;align-items:center;justify-content:center}
.overlay.open{display:flex}
.mbox{background:#fff;border-radius:10px;padding:24px;min-width:340px;max-width:500px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,.18);position:relative}
.mbox h2{font-size:17px;font-weight:700;margin-bottom:14px;color:#111}
.m-close{position:absolute;top:14px;right:14px;background:none;border:none;cursor:pointer;font-size:13px;color:#555;font-family:inherit}
.m-close:hover{color:var(--red)}
.mfield{margin-bottom:12px}
.mfield label{font-size:12px;color:var(--muted);margin-bottom:4px;display:block}
.mfield input,.mfield select,.mfield textarea{width:100%;padding:7px 10px;border:1.5px solid var(--blue);border-radius:5px;font-size:13px;outline:none;font-family:inherit}
.mfield textarea{resize:none;height:66px}
.m-item-name{font-size:13px;color:#444;margin-bottom:10px}
.m-item-name strong{color:#111}
.m-total{font-size:13px;margin-bottom:12px;color:#333}
.m-total strong{font-size:16px;color:#111;font-weight:700}
.m-row{display:flex;align-items:center;gap:10px}
.m-row .mfield{flex:1}
.m-sep{color:#555;font-size:12px;font-weight:600;padding-top:16px}
.d-input-wrap{display:flex;align-items:center;border:1.5px solid var(--blue);border-radius:5px;overflow:hidden;height:38px}
.d-input-wrap .pfx{padding:0 7px;background:#f5f5f5;border-right:1px solid #ccc;color:#555;font-size:13px}
.d-input-wrap input{border:none;outline:none;width:100%;padding:0 7px;font-size:13px;font-family:inherit}
.d-input-wrap.grey{border-color:#ccc}
.m-actions{display:flex;gap:8px;margin-top:4px}
.btn-ms{flex:1;height:38px;background:var(--green);border:none;border-radius:5px;font-weight:600;font-size:13px;color:var(--green-dark);cursor:pointer;font-family:inherit}
.btn-ms:hover{background:#81c784}
.btn-mc{flex:1;height:38px;background:#f5f5f5;border:1px solid #ccc;border-radius:5px;font-weight:600;font-size:13px;color:#333;cursor:pointer;font-family:inherit}
.btn-mc:hover{background:#e0e0e0}
.weigh-link{display:inline-block;margin:4px 0 12px;color:var(--blue);font-size:12px;cursor:pointer}

/* Add Customer grid */
.cust-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.span2{grid-column:1/-1}
.same-addr{display:flex;align-items:center;gap:6px;font-size:12px;color:#444;cursor:pointer}

/* No Items / Info modal */
.info-mbox{background:#fff;border-radius:8px;padding:22px 24px;min-width:320px;max-width:420px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,.18);position:relative}
.info-mbox h3{font-size:15px;font-weight:700;margin-bottom:10px;color:#111}
.info-mbox p{font-size:13px;color:#444;margin-bottom:14px}
.info-mbox .note{font-size:12px;color:var(--muted);margin-bottom:14px}
.btn-okay{width:100%;height:36px;background:var(--green);border:none;border-radius:5px;font-weight:600;font-size:13px;color:var(--green-dark);cursor:pointer;font-family:inherit}
.btn-okay:hover{background:#81c784}

/* Multi Pay */
.mpay-item{margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #f0f0f0}
.mpay-item:last-of-type{border-bottom:none}
.mpay-num{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:var(--blue);color:#fff;border-radius:50%;font-size:12px;font-weight:700;margin-bottom:6px}
.mpay-label{font-weight:600;font-size:13px;color:#111;margin-left:5px}
.mpay-fields{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px}
.mpay-summary{background:#f7fbff;border-radius:5px;padding:10px 12px;margin:8px 0 14px}
.mpay-summary-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px}
.mpay-summary-row:last-child{font-weight:700;font-size:14px;margin-bottom:0}
.mpay-warn{font-size:11px;color:var(--red);display:flex;align-items:center;gap:3px;margin-top:4px}
.mpay-actions{display:flex;gap:8px}
.btn-mpay{flex:1;height:38px;background:var(--green);border:none;border-radius:5px;font-weight:600;font-size:13px;color:var(--green-dark);cursor:pointer;font-family:inherit;opacity:.6}
.btn-mpay.active{opacity:1}
.btn-mpay:hover.active{background:#81c784}

/* Modify Item */
.mi-info{font-size:13px;color:#444;margin-bottom:12px}
.mi-info strong{color:#111}
.mi-row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-bottom:10px}
.mi-row2{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px}
.mi-field label{font-size:11px;color:var(--muted);margin-bottom:3px;display:block}
.mi-field input,.mi-field select{width:100%;height:34px;border:1.5px solid var(--blue);border-radius:5px;padding:0 7px;font-size:13px;outline:none;font-family:inherit}
.mi-check{display:flex;align-items:center;gap:6px;font-size:12px;color:#444;margin-bottom:14px}

/* Remarks */
.rem-mbox{background:#fff;border-radius:8px;padding:22px 24px;min-width:340px;box-shadow:0 8px 32px rgba(0,0,0,.18);position:relative}
.rem-mbox h3{font-size:15px;font-weight:700;margin-bottom:12px}

/* ── PRINT MODAL ── */
.print-mbox{background:#fff;border-radius:10px;min-width:420px;max-width:560px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,.22);overflow:hidden}
.print-header{background:var(--blue);color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between}
.print-header h2{font-size:16px;font-weight:700;margin:0}
.print-header .ph-close{background:rgba(255,255,255,.2);border:none;cursor:pointer;color:#fff;width:28px;height:28px;border-radius:50%;font-size:14px;display:flex;align-items:center;justify-content:center}
.print-header .ph-close:hover{background:rgba(255,255,255,.4)}
.print-body{padding:18px 20px}
.print-success{display:flex;flex-direction:column;align-items:center;padding:10px 0 14px;gap:6px}
.print-tick{width:52px;height:52px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px}
.print-bill-no{font-size:18px;font-weight:700;color:#111}
.print-saved{font-size:13px;color:#4caf50;font-weight:600}
.print-divider{border:none;border-top:1px dashed #ddd;margin:12px 0}
.print-summary{display:grid;grid-template-columns:1fr 1fr;gap:6px 14px;margin-bottom:14px}
.ps-item{font-size:12px}
.ps-label{color:#888;margin-bottom:2px}
.ps-val{font-weight:600;color:#222}
.print-actions{display:flex;gap:8px}
.btn-print-now{flex:1;height:40px;background:var(--blue);border:none;border-radius:5px;font-weight:600;font-size:13px;color:#fff;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px}
.btn-print-now:hover{background:var(--blue-dark)}
.btn-print-skip{flex:1;height:40px;background:#f5f5f5;border:1px solid #ccc;border-radius:5px;font-weight:600;font-size:13px;color:#333;cursor:pointer;font-family:inherit}
.btn-print-skip:hover{background:#e0e0e0}

/* ── THERMAL RECEIPT (hidden, for window.print) ── */
#receipt-frame{display:none}
@media print{
  body > *:not(#receipt-overlay){display:none!important}
  #receipt-overlay{display:flex!important;background:transparent!important;position:static!important;padding:0!important}
  .print-mbox{box-shadow:none!important;border-radius:0!important;width:80mm!important;min-width:0!important}
  .print-header{background:#fff!important;color:#000!important;border-bottom:1px solid #000}
  .print-header .ph-close{display:none}
  .print-actions{display:none!important}
}

/* Feedback toast */
#toast{position:fixed;left:14px;bottom:14px;z-index:2000;background:rgba(15,23,42,.9);color:#fff;padding:7px 13px;border-radius:7px;font-size:12px;opacity:0;transform:translateY(6px);pointer-events:none;transition:opacity 140ms,transform 140ms}
#toast.show{opacity:1;transform:translateY(0)}

/* Scrollbars */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:#f0f0f0}
::-webkit-scrollbar-thumb{background:#ccc;border-radius:3px}
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="app-bar">
  <div style="display:flex;align-items:center;gap:2px">
    <div class="brand">
      <svg viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5z" fill="#e53935"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="#e53935" stroke-width="2" stroke-linecap="round"/></svg>
    </div>
    <div class="menu-btns">
      <button>Company</button>
      <button>Help</button>
      <button>Versions</button>
      <button>Shortcuts</button>
      <button title="Refresh">↻</button>
    </div>
  </div>
  <div class="support">
    <span>Customer Support: 📞 <strong>(+92) 300 000 0000</strong></span>
    <span>|</span>
    <a href="#">Get Instant Online Support</a>
  </div>
  <div class="win-btns">
    <button title="Minimize">─</button>
    <button title="Restore">☐</button>
    <button class="close" title="Close" onclick="window.location.href='{{ route('sale.index') }}'">✕</button>
  </div>
</div>

<!-- TAB BAR -->
<div class="tab-bar" id="tab-strip">
  <div class="tab active" id="tab-1" onclick="activateTab(1)">
    <span>#1</span>
    <span style="font-size:11px;color:#999">Ctrl+W</span>
    <button class="close-x" onclick="closeTab(1,event)">✕</button>
  </div>
  <button class="new-tab-btn" onclick="addTab()">＋ New Bill [Ctrl+T]</button>
</div>

<!-- POS WRAP -->
<div class="pos-wrap">
  <!-- SEARCH BAR -->
  <div class="search-bar">
    <div class="search-inner">
      <input type="text" id="search-in"
        placeholder="Search by item name, item code, hsn code, mrp, sale price, purchase price... [F1]"
        oninput="doSearch(this.value)"
        onkeydown="searchKey(event)"
        autocomplete="off"/>
      <span class="s-icon">🔍</span>
      <div class="search-dd" id="search-dd">
        <table>
          <thead>
            <tr>
              <th>ITEM CODE</th>
              <th>ITEM NAME</th>
              <th>STOCK</th>
              <th>SALE PRICE (Rs)</th>
              <th>PURCHASE PRICE (Rs)</th>
            </tr>
          </thead>
          <tbody id="search-results"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- SPLIT -->
  <div class="split">
    <!-- LEFT -->
    <div class="left">
     <div class="table-wrapper">
  <table class="table align-middle custom-table mb-0">
          <thead>
            <tr>
              <th style="width:36px">#</th>
              <th>ITEM CODE</th>
              <th>ITEM NAME</th>
              <th style="width:70px">QTY</th>
              <th style="width:60px">UNIT</th>
              <th style="width:110px">PRICE/UNIT (Rs)</th>
              <th style="width:80px">DISCOUNT (Rs)</th>
              <th style="width:100px">TOTAL (Rs)</th>
              <th style="width:90px">Date</th>
            </tr>
          </thead>
          <tbody id="bill-body">
            <tr class="empty-msg">
              <td colspan="9">Start scanning items to add them to the bill.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- SHORTCUTS -->
      <div class="shortcuts">
        <div class="sc-row">
          <button class="sc-btn" onclick="doChangeQty()">Change Quantity<small>[F2]</small></button>
          <button class="sc-btn" onclick="doItemDiscount()">Item Discount<small>[F3]</small></button>
          <button class="sc-btn" onclick="doRemoveItem()">Remove Item<small>[F4]</small></button>
          <button class="sc-btn" onclick="doChangeUnit()">Change Unit<small>[F6]</small></button>
        </div>
        <div class="sc-row">
          <button class="sc-btn" onclick="doAdditionalCharges()">Additional Charges<small>[F8]</small></button>
          <button class="sc-btn" onclick="doBillDiscount()">Bill Discount<small>[F9]</small></button>
          <button class="sc-btn" onclick="toast('Loyalty Points [F10]')">Loyalty Points<small>[F10]</small></button>
          <button class="sc-btn" onclick="openRemarks()">Remarks<small>[F12]</small></button>
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="right">
      <!-- Date -->
      <div>
        <label>Date</label>
        <div style="position:relative">
          <div class="date-row">
            <div class="date-display" id="date-display" onclick="toggleCal()">
              {{ now()->format('d/m/Y') }}
            </div>
            <button class="cal-btn" onclick="toggleCal()">📅</button>
          </div>
          <!-- Calendar popup -->
          <div class="cal-pop" id="cal-pop">
            <div class="cal-header">
              <button class="cal-nav" onclick="calPrev()">‹</button>
              <span id="cal-label"></span>
              <button class="cal-nav" onclick="calNext()">›</button>
            </div>
            <div class="cal-grid" id="cal-grid"></div>
          </div>
        </div>
      </div>

      <!-- ── FIX 3: Customer search shows parties from DB ── -->
      <div>
        <label>Customer [F11]</label>
        <div class="cust-wrap">
          <input type="text" id="cust-in"
            placeholder="Search for a customer by name, phone number [F11]"
            oninput="filterCust(this.value)"

            autocomplete="off"/>
          <div class="cust-dd" id="cust-dd">
            <div class="add-new" onclick="openAddCustomer()">➕ Add New Customer</div>
            <div class="c-item" onclick="selectCust('Walk-in Customer','',null)">
              <div class="c-name">Walk-in Customer</div>
            </div>
            @foreach($parties ?? [] as $party)
              <div class="c-item"
                onclick="selectCust('{{ addslashes($party->name) }}','{{ $party->phone ?? '' }}',{{ $party->id }})"
                data-name="{{ strtolower($party->name) }}"
                data-phone="{{ $party->phone ?? '' }}"
                data-phone2="{{ $party->phone_number_2 ?? '' }}"
                data-ptcl="{{ $party->ptcl_number ?? '' }}">
                <div class="c-name">{{ $party->name }}</div>
                @if($party->phone)
                  <div class="c-phone">📞 {{ $party->phone }}</div>
                @endif
                @if($party->phone_number_2)
                  <div class="c-phone">Alt: {{ $party->phone_number_2 }}</div>
                @endif
                @if($party->ptcl_number)
                  <div class="c-phone">PTCL: {{ $party->ptcl_number }}</div>
                @endif
              </div>
            @endforeach
            <div class="no-results" id="cust-no-results" style="display:none">No customers found</div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="summary">
        <div class="summary-top">
          <div class="sum-icon">🧾</div>
          <div class="sum-total" id="sum-total">Total Rs 0.00</div>
          <div class="sum-breakup" onclick="toast('Full Breakup [Ctrl+F]')">Full Breakup<br>[Ctrl+F]</div>
        </div>
        <div class="sum-meta" id="sum-meta">Items: 0 , Quantity: 0</div>
      </div>

      <!-- ── FIX 4: Payment Mode with all fetched modes ── -->
      <div class="pay-row">
        <div>
          <label>Payment Mode</label>
          <select id="pay-mode" onchange="onPayModeChange()">
            @foreach($paymentModes ?? ['Cash','Card','UPI','HBL','Credit'] as $mode)
              <option value="{{ $mode }}">{{ $mode }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label>Amount Received</label>
          <div class="amt-wrap">
            <span class="prefix">Rs</span>
            <input type="number" id="amt-recv" value="0.00" oninput="calcChange()"/>
          </div>
        </div>
      </div>

      <div class="change-row">
        <span class="lbl">Change to Return:</span>
        <span class="val" id="change-val">Rs 0.00</span>
      </div>

      <button class="btn-save" onclick="doSaveBill()">Save &amp; Print Bill [Ctrl+P]</button>
      <button class="btn-credit" onclick="openMultiPay()">Other/Credit Payments [Ctrl+M]</button>

      <div class="bot-links">
        <button onclick="addTab()">New Bill [Ctrl+T]</button>
        <button onclick="window.location.href='{{ route('items') }}'">Items Master</button>
      </div>
    </div>
  </div>
</div>

<!-- ════════════ MODALS ════════════ -->

<!-- No Items Added -->
<div class="overlay" id="modal-noitems">
  <div class="info-mbox">
    <div class="m-close" onclick="closeModal('modal-noitems')" style="position:absolute;top:12px;right:12px">✕ [Esc]</div>
    <h3>No Items Added</h3>
    <p>Please add at-least one item to perform this action.</p>
    <button class="btn-okay" onclick="closeModal('modal-noitems')">Okay</button>
  </div>
</div>

<!-- Change Quantity -->
<div class="overlay" id="modal-qty">
  <div class="mbox" style="max-width:380px">
    <h2>Change Quantity</h2>
    <button class="m-close" onclick="closeModal('modal-qty')">✕ [Esc]</button>
    <p class="m-item-name">Item Name: <strong id="qty-name">—</strong></p>
    <div class="mfield">
      <label>Enter New Quantity</label>
      <input type="number" id="qty-input" value="1" min="0.001" step="any"/>
    </div>
    <a class="weigh-link">Connect Weighing Scale ›</a>
    <div class="m-actions">
      <button class="btn-ms" onclick="saveQty()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-qty')">Cancel</button>
    </div>
  </div>
</div>

<!-- ── FIX 2: Item Discount (fully working) ── -->
<div class="overlay" id="modal-itemdisc">
  <div class="mbox" style="max-width:420px">
    <h2>Item Discount</h2>
    <button class="m-close" onclick="closeModal('modal-itemdisc')">✕ [Esc]</button>
    <p class="m-item-name">Item Name: <strong id="id-name">—</strong></p>
    <div class="m-total">Item Value: <strong id="id-total">Rs 0.00</strong></div>
    <div class="m-row">
      <div class="mfield">
        <label>Discount in %</label>
        <div class="d-input-wrap">
          <span class="pfx">%</span>
          <input type="number" id="id-pct" value="0" min="0" max="100" step="any" oninput="idSyncPct()"/>
        </div>
      </div>
      <div class="m-sep">OR</div>
      <div class="mfield">
        <label>Discount in Rs</label>
        <div class="d-input-wrap grey">
          <span class="pfx">Rs</span>
          <input type="number" id="id-rs" value="0" min="0" step="any" oninput="idSyncRs()"/>
        </div>
      </div>
    </div>
    <div class="m-actions">
      <button class="btn-ms" onclick="saveItemDisc()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-itemdisc')">Cancel</button>
    </div>
  </div>
</div>

<!-- Bill Discount -->
<div class="overlay" id="modal-billdisc">
  <div class="mbox" style="max-width:420px">
    <h2>Bill Discount</h2>
    <button class="m-close" onclick="closeModal('modal-billdisc')">✕ [Esc]</button>
    <div class="m-total">Total: <strong id="bd-total">Rs 0.00</strong></div>
    <div class="m-row">
      <div class="mfield">
        <label>Discount in %</label>
        <div class="d-input-wrap">
          <span class="pfx">%</span>
          <input type="number" id="bd-pct" value="0" oninput="bdSyncPct()"/>
        </div>
      </div>
      <div class="m-sep">OR</div>
      <div class="mfield">
        <label>Discount in Rs</label>
        <div class="d-input-wrap grey">
          <span class="pfx">Rs</span>
          <input type="number" id="bd-rs" value="0" oninput="bdSyncRs()"/>
        </div>
      </div>
    </div>
    <div class="m-actions">
      <button class="btn-ms" onclick="saveBillDisc()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-billdisc')">Cancel</button>
    </div>
  </div>
</div>
<!-- Additional Charges -->
<div class="overlay" id="modal-addcharges">
  <div class="mbox" style="max-width:420px">
    <h2>Additional Charges</h2>
    <button class="m-close" onclick="closeModal('modal-addcharges')">✕ [Esc]</button>
    <div class="m-total" style="margin-bottom:14px">Total: <strong id="ac-total">Rs 0.00</strong></div>
    <div class="mfield">
      <label>Shipping</label>
      <div class="d-input-wrap">
        <span class="pfx">Rs</span>
        <input type="number" id="ac-shipping" value="0" min="0" step="any"/>
      </div>
    </div>
    <div class="m-actions">
      <button class="btn-ms" onclick="saveAdditionalCharges()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-addcharges')">Cancel</button>
    </div>
  </div>
</div>

<!-- Change Unit -->
<div class="overlay" id="modal-unit">
  <div class="mbox" style="max-width:360px">
    <h2>Change Unit</h2>
    <button class="m-close" onclick="closeModal('modal-unit')">✕ [Esc]</button>
    <p class="m-item-name">Item Name: <strong id="unit-name">—</strong></p>
    <div class="mfield">
      <label>Select Unit</label>
      <select id="unit-select">
        <option value="Kg">KILOGRAMS (Kg)</option>
        <option value="g">GRAMS (g)</option>
        <option value="Pcs">PIECES (Pcs)</option>
        <option value="Box">BOX (Box)</option>
        <option value="L">LITERS (L)</option>
        <option value="m">METERS (m)</option>
        <option value="Nos">NOS (Nos)</option>
      </select>
    </div>
    <div class="m-actions">
      <button class="btn-ms" onclick="saveUnit()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-unit')">Cancel</button>
    </div>
  </div>
</div>

<!-- Modify Item (double click) -->
<div class="overlay" id="modal-modify">
  <div class="mbox" style="max-width:520px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
      <h2 style="margin:0">Modify Item</h2>
      <div style="display:flex;align-items:center;gap:10px">
        <a class="weigh-link" style="margin:0;font-size:12px">Connect Weighing Scale ›</a>
        <button class="m-close" style="position:static" onclick="closeModal('modal-modify')">✕ [Esc]</button>
      </div>
    </div>
    <div class="mi-info">Item Name: <strong id="mi-name">—</strong></div>
    <div class="mi-info">Price/Unit: <strong id="mi-price">Rs 0.00</strong></div>
    <div class="mi-row">
      <div class="mi-field">
        <label>Quantity</label>
        <input type="number" id="mi-qty" value="1" step="any" oninput="miCalc()"/>
      </div>
      <div class="mi-field">
        <label>Unit</label>
        <select id="mi-unit">
          <option value="Kg">Kg</option>
          <option value="Box">BOX (Box)</option>
          <option value="Pcs">Pcs</option>
          <option value="g">g</option>
          <option value="L">L</option>
          <option value="Nos">Nos</option>
        </select>
      </div>
      <div class="mi-field">
        <label>Discount in %</label>
        <div class="d-input-wrap" style="height:34px">
          <span class="pfx" style="font-size:12px">%</span>
          <input type="number" id="mi-dpct" value="0" oninput="miSyncPct()"/>
        </div>
      </div>
      <div class="mi-field" style="display:flex;flex-direction:column;gap:0">
        <label>Discount in Rs</label>
        <div style="display:flex;align-items:center;gap:4px">
          <span style="font-size:12px;color:#555">OR</span>
          <div class="d-input-wrap grey" style="height:34px;flex:1">
            <span class="pfx" style="font-size:12px">Rs</span>
            <input type="number" id="mi-drs" value="0" oninput="miSyncRs()"/>
          </div>
        </div>
      </div>
    </div>
    <div class="mi-row2">
      <div class="mi-field">
        <label>New Price/Unit</label>
        <div class="d-input-wrap" style="height:34px">
          <span class="pfx">Rs</span>
          <input type="number" id="mi-newprice" oninput="miCalc()"/>
        </div>
      </div>
      <div class="mi-field">
        <label>Free Quantity</label>
        <input type="number" id="mi-free" value="" placeholder=""/>
      </div>
    </div>
    <div class="mi-check">
      <input type="checkbox" id="mi-perm"/>
      <label for="mi-perm" style="cursor:pointer">Update Item Price Permanently</label>
    </div>
    <div class="m-actions">
      <button class="btn-ms" onclick="saveModify()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-modify')">Cancel</button>
    </div>
  </div>
</div>

<!-- Remarks -->
<div class="overlay" id="modal-remarks">
  <div class="rem-mbox">
    <button class="m-close" onclick="closeModal('modal-remarks')">✕ [Esc]</button>
    <h3>Remarks</h3>
    <div class="mfield">
      <label>Remarks</label>
      <textarea id="rem-text" rows="5" style="width:100%;border:1.5px solid #ccc;border-radius:5px;padding:7px 10px;font-size:13px;outline:none;font-family:inherit;resize:none;height:100px"></textarea>
    </div>
    <div class="m-actions">
      <button class="btn-ms" onclick="saveRemarks()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-remarks')">Cancel</button>
    </div>
  </div>
</div>
<!-- Add Customer -->
<div class="overlay" id="modal-addcust">
  <div class="mbox" style="max-width:520px">
    <h2>Add Customer</h2>
    <button class="m-close" onclick="closeModal('modal-addcust')">✕ [Esc]</button>
    <div class="cust-grid">
      <div class="mfield">
        <label>Customer Name <span style="color:red">*</span></label>
        <input type="text" id="nc-name" placeholder="Customer Name"/>
      </div>
      <div class="mfield">
        <label>Phone Number</label>
        <input type="text" id="nc-phone" placeholder="Phone Number"/>
      </div>
      <div class="mfield">
        <label>Phone Number 2</label>
        <input type="text" id="nc-phone-2" placeholder="Second Phone Number"/>
      </div>
      <div class="mfield">
        <label>PTCL Number</label>
        <input type="text" id="nc-ptcl" placeholder="PTCL Number"/>
      </div>
      <div class="mfield span2">
        <label>Billing Address</label>
        <textarea id="nc-billing" placeholder="Billing Address"></textarea>
      </div>
      <div class="mfield span2">

        <label>Shipping Address</label>
        <textarea id="nc-shipping" placeholder="Shipping Address"></textarea>
      </div>
      <div></div>
      <label class="same-addr">
        <input type="checkbox" id="same-addr" onchange="syncShip()"/>
        Same as billing address
      </label>
    </div>
    <div class="m-actions" style="margin-top:14px">
      <button class="btn-ms" style="background:#4caf50;color:#fff" onclick="saveCustomer()">Save</button>
      <button class="btn-mc" onclick="closeModal('modal-addcust')">Cancel</button>
    </div>
  </div>
</div>

<!-- Multi Pay -->
<div class="overlay" id="modal-multipay">
  <div class="mbox" style="max-width:520px;max-height:90vh;overflow-y:auto">
    <h2>Multi Pay</h2>
    <button class="m-close" onclick="closeModal('modal-multipay')">✕ [Esc]</button>
    <div id="mpay-methods"></div>
    <div class="mpay-summary">
      <div class="mpay-summary-row"><span>Total:</span><span id="mp-total">Rs 0.00</span></div>
      <div class="mpay-summary-row"><span><strong>Balance</strong></span><span id="mp-balance">Rs 0.00</span></div>
    </div>
    <div class="mpay-warn" id="mp-warn">⚠ Party should be selected if amount is less than bill total</div>
    <div class="mpay-actions">
      <button class="btn-mpay" id="mp-save-print" onclick="mpSave('print')">Save &amp; Print Bill [Ctrl+P]</button>
      <button class="btn-mpay" id="mp-save-new" onclick="mpSave('new')">Save &amp; New Bill [Ctrl+N]</button>
    </div>
  </div>
</div>

<!-- ── FIX 1: Print / Save Success Modal ── -->
<div class="overlay" id="receipt-overlay">
  <div class="print-mbox">
    <div class="print-header">
      <h2>🖨️ Bill Saved Successfully</h2>
      <button class="ph-close" onclick="closePrintModal()">✕</button>
    </div>
    <div class="print-body">
      <div class="print-success">
        <div class="print-tick">✅</div>
        <div class="print-bill-no" id="pr-bill-no">Bill #—</div>
        <div class="print-saved">Saved Successfully</div>
      </div>
      <hr class="print-divider"/>
      <div class="print-summary">
        <div class="ps-item">
          <div class="ps-label">Customer</div>
          <div class="ps-val" id="pr-customer">Walk-in Customer</div>
        </div>
        <div class="ps-item">
          <div class="ps-label">Date</div>
          <div class="ps-val" id="pr-date">—</div>
        </div>
        <div class="ps-item">
          <div class="ps-label">Items</div>
          <div class="ps-val" id="pr-items">—</div>
        </div>
        <div class="ps-item">
          <div class="ps-label">Payment Mode</div>
          <div class="ps-val" id="pr-paymode">—</div>
        </div>
        <div class="ps-item" style="grid-column:1/-1">
          <div class="ps-label">Grand Total</div>
          <div class="ps-val" id="pr-total" style="font-size:18px;color:var(--blue)">Rs 0.00</div>
        </div>
      </div>

      <!-- Inline receipt for printing -->
      <div id="receipt-content" style="display:none;font-family:monospace;font-size:12px;line-height:1.6;border:1px dashed #ccc;padding:10px;margin-bottom:12px;border-radius:4px;max-height:200px;overflow-y:auto">
      </div>

      <div class="print-actions">
        <button class="btn-print-now" onclick="doPrint()">🖨️ Print Receipt</button>
        <button class="btn-print-skip" onclick="closePrintModal()">Skip &amp; New Bill</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast"></div>

<script>
    let additionalCharges = 0; // shipping / extra charges
// ── CONFIG FROM LARAVEL ──
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const ROUTES = {
  storeSale:    '{{ route('sale.store') }}',
  storeParty:   '{{ route('parties.store') }}',
  searchItems:  '{{ route('items') }}?json=1',
};

// ── PRODUCTS DATA (from Laravel) ──
let PRODUCTS = @json($items ?? []);

// ── PARTIES DATA (from Laravel) ── FIX 3: full party objects with id
let PARTIES = @json($parties ?? []);

// ── PAYMENT MODES from Laravel controller ──
// FIX 4: SaleController::pos() should pass $paymentModes = PaymentMode::pluck('name') or similar
const BANK_ACCOUNTS = @json($bankAccounts ?? []);
const PAYMENT_MODES = @json($paymentModes ?? []);

// ── BOOT ──
(async function bootData() {
  if (PRODUCTS.length === 0) {
    try {
      const r = await fetch('{{ route("items") }}?json=1', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
      });
      if (r.ok) PRODUCTS = await r.json();
    } catch(e) { console.error('[POS] Failed to load items:', e); }
  }
})();

// ── STATE ──
let billItems    = [];
let selRow       = -1;
let billDiscount = 0;
let remarks      = '';
let tabCount     = 1;
let activeTabId = 1;
const TAB_STATES = {};

function saveTabState(tabId) {
  TAB_STATES[tabId] = {
    billItems:         JSON.parse(JSON.stringify(billItems)),
    selRow:            selRow,
    billDiscount:      billDiscount,
    additionalCharges: additionalCharges,
    remarks:           remarks,
    selectedPartyId:   selectedPartyId,
    selectedPartyName: selectedPartyName,
    selectedDate:      new Date(selectedDate.getTime()),
    custInValue:       document.getElementById('cust-in').value,
    amtRecvValue:      document.getElementById('amt-recv').value,
    payMode:           document.getElementById('pay-mode').value,
  };
}

function loadTabState(tabId) {
  const s = TAB_STATES[tabId];
  if (!s) {
    billItems         = [];
    selRow            = -1;
    billDiscount      = 0;
    additionalCharges = 0;
    remarks           = '';
    selectedPartyId   = null;
    selectedPartyName = 'Walk-in Customer';
    selectedDate      = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    document.getElementById('cust-in').value  = '';
    document.getElementById('amt-recv').value = '0.00';
    document.getElementById('pay-mode').value = document.getElementById('pay-mode').options[0]?.value || '';
  } else {
    billItems         = JSON.parse(JSON.stringify(s.billItems));
    selRow            = s.selRow;
    billDiscount      = s.billDiscount;
    additionalCharges = s.additionalCharges;
    remarks           = s.remarks;
    selectedPartyId   = s.selectedPartyId;
    selectedPartyName = s.selectedPartyName;
    selectedDate      = new Date(s.selectedDate.getTime());
    document.getElementById('cust-in').value  = s.custInValue;
    document.getElementById('amt-recv').value = s.amtRecvValue;
    document.getElementById('pay-mode').value = s.payMode;
  }
  renderBill();
  updateDateDisplay();
  renderCal();
}
let selectedPartyId   = null;
let selectedPartyName = 'Walk-in Customer';

const today = new Date();
let calDate      = new Date(today.getFullYear(), today.getMonth(), today.getDate());
let selectedDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

// ── INIT ──
renderCal();
updateDateDisplay();
renderBill();
document.getElementById('cust-in').addEventListener('click', function(e) {
  e.stopPropagation();
  filterCust(this.value);
  document.getElementById('cust-dd').classList.add('open');
});

document.addEventListener('click', function(e) {
  if (!e.target.closest('.cust-wrap')) {
    document.getElementById('cust-dd').classList.remove('open');
  }
});

// ── SEARCH ──
function doSearch(q) {
  const dd = document.getElementById('search-dd');
  const tb = document.getElementById('search-results');
  if (!q.trim()) { dd.classList.remove('open'); return; }
  const res = PRODUCTS.filter(p =>
    (p.name && p.name.toLowerCase().includes(q.toLowerCase())) ||
    (p.item_code && p.item_code.includes(q))
  );
  if (!res.length) { dd.classList.remove('open'); return; }
  tb.innerHTML = res.map(p => `
    <tr onclick="addItem(${p.id})">
      <td>${p.item_code || ''}</td>
      <td style="font-weight:600">${p.name}</td>
      <td>${p.opening_qty ?? 0} ${p.unit || ''}</td>
      <td>${parseFloat(p.sale_price || 0).toFixed(2)}</td>
      <td>${parseFloat(p.purchase_price || 0).toFixed(2)}</td>
    </tr>`).join('');
  dd.classList.add('open');
}

function searchKey(e) {
  if (e.key === 'Escape') document.getElementById('search-dd').classList.remove('open');
  if (e.key === 'Enter') {
    const q = e.target.value.trim();
    const p = PRODUCTS.find(p =>
      p.item_code === q || (p.name && p.name.toLowerCase() === q.toLowerCase())
    );
    if (p) { addItem(p.id); document.getElementById('search-dd').classList.remove('open'); }
  }
}

document.addEventListener('click', e => {
  if (!e.target.closest('.search-inner')) document.getElementById('search-dd').classList.remove('open');
  if (!e.target.closest('.cust-wrap') && !e.target.closest('#cust-dd'))
    document.getElementById('cust-dd').classList.remove('open');
  if (!e.target.closest('.date-row') && !e.target.closest('.cal-pop'))
    document.getElementById('cal-pop').classList.remove('open');
});

// ── BILL ──
function addItem(id) {
  const p = PRODUCTS.find(x => x.id === id);
  if (!p) return;
  const ex = billItems.find(x => x.id === id);
  if (ex) { ex.qty += 1; }
  else {
    billItems.push({
      id:             p.id,
      item_code:      p.item_code || '',
      name:           p.name,
      qty:            1,
      unit:           p.unit || 'Pcs',
      sale_price:     parseFloat(p.sale_price || 0),
      purchase_price: parseFloat(p.purchase_price || 0),
      discount:       0
    });
  }
  document.getElementById('search-in').value = '';
  document.getElementById('search-dd').classList.remove('open');
  selRow = billItems.length - 1;
  renderBill();
}
function doAdditionalCharges() {
  if (!billItems.length) { openModal('modal-noitems'); return; }
  document.getElementById('ac-total').textContent = `Rs ${getTotal().toFixed(2)}`;
  document.getElementById('ac-shipping').value = additionalCharges.toFixed(2);
  openModal('modal-addcharges');
  setTimeout(() => document.getElementById('ac-shipping').focus(), 80);
}

function saveAdditionalCharges() {
  additionalCharges = parseFloat(document.getElementById('ac-shipping').value) || 0;
  updateSummary();
  closeModal('modal-addcharges');
  if (additionalCharges > 0) toast(`Shipping added: Rs ${additionalCharges.toFixed(2)}`);
}

function renderBill() {
  const tb = document.getElementById('bill-body');
  const billDate = document.getElementById('date-display').textContent || '{{ now()->format("d/m/Y") }}';
  if (!billItems.length) {
    tb.innerHTML = '<tr class="empty-msg"><td colspan="9">Start scanning items to add them to the bill.</td></tr>';
  } else {
    tb.innerHTML = billItems.map((it, i) => `
      <tr class="${i === selRow ? 'sel' : ''}"
          onclick="selBillRow(${i})"
          ondblclick="openModify(${i})">
        <td>${i + 1}</td>
        <td>${it.item_code}</td>
        <td>${it.name}</td>
        <td>${it.qty.toFixed(2)}</td>
        <td>${it.unit}</td>
        <td>${parseFloat(it.sale_price).toFixed(2)}</td>
        <td style="color:${it.discount > 0 ? '#e53935' : '#222'}">${it.discount > 0 ? '-' + it.discount.toFixed(2) : '0.00'}</td>
        <td><strong>${rowTotal(it).toFixed(2)}</strong></td>
        <td>${billDate}</td>
      </tr>`).join('');
  }
  updateSummary();
}

function rowTotal(it) {
  return Math.max(0, it.qty * parseFloat(it.sale_price) - it.discount);
}

function selBillRow(i) { selRow = i; renderBill(); }

function getSubtotal() { return billItems.reduce((s, it) => s + rowTotal(it), 0); }
function getTotal()    { return Math.max(0, getSubtotal() - billDiscount + additionalCharges); }

function updateSummary() {
  const total = getTotal();
  const items = billItems.length;
  const qty   = billItems.reduce((s, i) => s + i.qty, 0);
  document.getElementById('sum-total').textContent = `Total Rs ${total.toFixed(2)}`;
  document.getElementById('sum-meta').textContent  = `Items: ${items} , Quantity: ${qty}`;
  calcChange();
    onPayModeChange();
}

function calcChange() {
  const recv = parseFloat(document.getElementById('amt-recv').value) || 0;
  document.getElementById('change-val').textContent = `Rs ${Math.max(0, recv - getTotal()).toFixed(2)}`;
}

// ── FIX 4: Auto-fill amount on payment mode change ──
function onPayModeChange() {
  const mode = document.getElementById('pay-mode').value;
  if (mode === 'Credit') {
    document.getElementById('amt-recv').value = '0.00';
  } else {
    const total = getTotal();
    document.getElementById('amt-recv').value = total > 0 ? total.toFixed(2) : '0.00';
  }
  calcChange();
}

// ── FIX 1: SAVE BILL → show print popup ──
function doSaveBill() {
  if (!billItems.length) { openModal('modal-noitems'); return; }

  const payMode  = document.getElementById('pay-mode').value;
  const recv     = parseFloat(document.getElementById('amt-recv').value) || 0;
  const total    = getTotal();
  const subTotal = getSubtotal();

  const itemsPayload = billItems.map(it => ({
    item_name:    it.name,
    item_code:    it.item_code,
    quantity:     it.qty,
    unit:         it.unit,
    unit_price:   it.sale_price,
    discount:     it.discount,
    amount:       rowTotal(it),
  }));

const bankAcc = BANK_ACCOUNTS.find(b => (b.display_with_account || b.display_name) === payMode);

const paymentsPayload = recv > 0 ? [{
    payment_type:    payMode,
    bank_account_id: bankAcc ? bankAcc.id : null,
    amount:          recv,
    reference:       '',
}] : [];
  const payload = {
    type:         'pos',
    party_id:     selectedPartyId || null,
    invoice_date: formatDateForServer(selectedDate),
    total_qty:    billItems.reduce((s, i) => s + i.qty, 0),
    total_amount: subTotal,
    discount_rs:  billDiscount,
    discount_pct: subTotal > 0 ? ((billDiscount / subTotal) * 100).toFixed(2) : 0,
    grand_total:  total,
    description:  remarks,
    items:        itemsPayload,
    payments:     paymentsPayload,
  };

  fetch(ROUTES.storeSale, {
    method:  'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
      'Accept':       'application/json',
    },
    body: JSON.stringify(payload),
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showPrintModal(data.bill_number || data.id, payMode, total, itemsPayload);
    } else {
      toast('Error saving bill: ' + (data.message || ''));
    }
  })
  .catch(() => toast('Network error. Please try again.'));
}

// ── FIX 1: Show Print Modal ──
function showPrintModal(billNo, payMode, total, items) {
  document.getElementById('pr-bill-no').textContent    = `Bill #${billNo}`;
  document.getElementById('pr-customer').textContent   = selectedPartyName || 'Walk-in Customer';
  document.getElementById('pr-date').textContent       = formatDateDisplay(selectedDate);
  document.getElementById('pr-items').textContent      = `${items.length} item(s), Qty: ${items.reduce((s,i)=>s+i.quantity,0)}`;
  document.getElementById('pr-paymode').textContent    = payMode;
  document.getElementById('pr-total').textContent      = `Rs ${total.toFixed(2)}`;

  // Build thermal-style receipt
  const line = '─'.repeat(32);
  let rec = `${line}\n`;
  rec += `         SALE RECEIPT\n`;
  rec += `Bill #${billNo}   Date: ${formatDateDisplay(selectedDate)}\n`;
  rec += `Customer: ${selectedPartyName || 'Walk-in'}\n`;
  rec += `${line}\n`;
  items.forEach(it => {
    rec += `${it.item_name}\n`;
    rec += `  ${it.quantity} x Rs${it.unit_price.toFixed(2)}`;
    if (it.discount > 0) rec += ` -Rs${it.discount.toFixed(2)}`;
    rec += `   = Rs${it.amount.toFixed(2)}\n`;
  });
  rec += `${line}\n`;
  if (billDiscount > 0) rec += `Bill Discount:   -Rs${billDiscount.toFixed(2)}\n`;
  rec += `GRAND TOTAL:     Rs${total.toFixed(2)}\n`;
  rec += `Payment:         ${payMode}\n`;
  rec += `${line}\n`;
  rec += `     Thank you for your purchase!\n`;

  const rc = document.getElementById('receipt-content');
  rc.textContent = rec;
  rc.style.display = 'block';

  openModal('receipt-overlay');
}

function doPrint() {
  window.print();
}

function closePrintModal() {
  closeModal('receipt-overlay');
  resetBill();
}

function resetBill() {
  billItems          = [];
  selRow             = -1;
  billDiscount       = 0;
  additionalCharges = 0;
  remarks            = '';
  selectedPartyId    = null;
  selectedPartyName  = 'Walk-in Customer';
  document.getElementById('amt-recv').value = '0.00';
  document.getElementById('cust-in').value  = '';
  renderBill();
}

function formatDateForServer(d) {
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}
function formatDateDisplay(d) {
  return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
}

// ── SHORTCUTS ──
function doChangeQty() {
  if (!billItems.length) { openModal('modal-noitems'); return; }
  if (selRow < 0) selRow = 0;
  document.getElementById('qty-name').textContent = billItems[selRow].name;
  document.getElementById('qty-input').value = billItems[selRow].qty;
  openModal('modal-qty');
  setTimeout(() => document.getElementById('qty-input').focus(), 80);
}

function saveQty() {
  const v = parseFloat(document.getElementById('qty-input').value);
  if (!isNaN(v) && v > 0) { billItems[selRow].qty = v; renderBill(); }
  closeModal('modal-qty');
}

// ── FIX 2: Item Discount – fully working ──
function doItemDiscount() {
  if (!billItems.length) { openModal('modal-noitems'); return; }
  if (selRow < 0) selRow = 0;
  const it   = billItems[selRow];
  const base = it.qty * parseFloat(it.sale_price);
  document.getElementById('id-name').textContent  = it.name;
  document.getElementById('id-total').textContent = `Rs ${base.toFixed(2)}`;
  // Pre-fill existing discount
  document.getElementById('id-rs').value  = it.discount.toFixed(2);
  document.getElementById('id-pct').value = base > 0 ? ((it.discount / base) * 100).toFixed(2) : '0';
  openModal('modal-itemdisc');
  setTimeout(() => document.getElementById('id-pct').focus(), 80);
}

function idSyncPct() {
  const it   = billItems[selRow]; if (!it) return;
  const pct  = parseFloat(document.getElementById('id-pct').value) || 0;
  const base = it.qty * parseFloat(it.sale_price);
  document.getElementById('id-rs').value = ((base * pct) / 100).toFixed(2);
}

function idSyncRs() {
  const it   = billItems[selRow]; if (!it) return;
  const rs   = parseFloat(document.getElementById('id-rs').value) || 0;
  const base = it.qty * parseFloat(it.sale_price);
  document.getElementById('id-pct').value = base > 0 ? ((rs / base) * 100).toFixed(2) : '0';
}

function saveItemDisc() {
  const it   = billItems[selRow]; if (!it) return;
  const rs   = parseFloat(document.getElementById('id-rs').value) || 0;
  const base = it.qty * parseFloat(it.sale_price);
  it.discount = Math.min(rs, base); // can't exceed item value
  renderBill();
  closeModal('modal-itemdisc');
  toast(`Discount applied: Rs ${it.discount.toFixed(2)}`);
}

function doRemoveItem() {
  if (!billItems.length) { openModal('modal-noitems'); return; }
  if (selRow < 0) selRow = 0;
  billItems.splice(selRow, 1);
  selRow = Math.min(selRow, billItems.length - 1);
  renderBill();
}

function doChangeUnit() {
  if (!billItems.length) { openModal('modal-noitems'); return; }
  if (selRow < 0) selRow = 0;
  document.getElementById('unit-name').textContent = billItems[selRow].name;
  openModal('modal-unit');
}
function saveUnit() {
  const v = document.getElementById('unit-select').value;
  billItems[selRow].unit = v;
  renderBill();
  closeModal('modal-unit');
}

function doBillDiscount() {
  if (!billItems.length) { openModal('modal-noitems'); return; }
  const sub = getSubtotal();
  document.getElementById('bd-total').textContent = `Rs ${sub.toFixed(2)}`;
  document.getElementById('bd-pct').value = sub > 0 ? ((billDiscount / sub) * 100).toFixed(2) : 0;
  document.getElementById('bd-rs').value  = billDiscount.toFixed(2);
  openModal('modal-billdisc');
}
function bdSyncPct() {
  const pct = parseFloat(document.getElementById('bd-pct').value) || 0;
  document.getElementById('bd-rs').value = ((getSubtotal() * pct) / 100).toFixed(2);
}
function bdSyncRs() {
  const rs   = parseFloat(document.getElementById('bd-rs').value) || 0;
  const base = getSubtotal();
  document.getElementById('bd-pct').value = base > 0 ? ((rs / base) * 100).toFixed(2) : 0;
}
function saveBillDisc() {
  billDiscount = parseFloat(document.getElementById('bd-rs').value) || 0;
  updateSummary();
  closeModal('modal-billdisc');
  toast(`Bill discount: Rs ${billDiscount.toFixed(2)}`);
}

// ── MODIFY ITEM ──
function openModify(i) {
  selRow = i;
  const it = billItems[i];
  document.getElementById('mi-name').textContent     = it.name;
  document.getElementById('mi-price').textContent    = `Rs ${parseFloat(it.sale_price).toFixed(2)}`;
  document.getElementById('mi-qty').value            = it.qty;
  document.getElementById('mi-unit').value           = it.unit;
  document.getElementById('mi-dpct').value           = 0;
  document.getElementById('mi-drs').value            = it.discount.toFixed(2);
  document.getElementById('mi-newprice').value       = it.sale_price;
  openModal('modal-modify');
  setTimeout(() => document.getElementById('mi-qty').focus(), 80);
}
function miCalc() {}
function miSyncPct() {
  const it = billItems[selRow]; if (!it) return;
  const pct  = parseFloat(document.getElementById('mi-dpct').value) || 0;
  const base = parseFloat(document.getElementById('mi-newprice').value || it.sale_price) *
               (parseFloat(document.getElementById('mi-qty').value) || 1);
  document.getElementById('mi-drs').value = ((base * pct) / 100).toFixed(2);
}
function miSyncRs() {
  const it = billItems[selRow]; if (!it) return;
  const rs   = parseFloat(document.getElementById('mi-drs').value) || 0;
  const base = parseFloat(document.getElementById('mi-newprice').value || it.sale_price) *
               (parseFloat(document.getElementById('mi-qty').value) || 1);
  document.getElementById('mi-dpct').value = base > 0 ? ((rs / base) * 100).toFixed(2) : 0;
}
function saveModify() {
  const it = billItems[selRow]; if (!it) return;
  it.qty      = parseFloat(document.getElementById('mi-qty').value) || it.qty;
  it.unit     = document.getElementById('mi-unit').value;
  it.discount = parseFloat(document.getElementById('mi-drs').value) || 0;
  const np    = parseFloat(document.getElementById('mi-newprice').value);
  if (!isNaN(np) && np > 0) it.sale_price = np;
  renderBill();
  closeModal('modal-modify');
}

// ── REMARKS ──
function openRemarks() {
  document.getElementById('rem-text').value = remarks;
  openModal('modal-remarks');
}
function saveRemarks() {
  remarks = document.getElementById('rem-text').value;
  closeModal('modal-remarks');
  if (remarks) toast('Remarks saved');
}

// ── MULTI PAY ──
function openMultiPay() {
  if (!billItems.length) { openModal('modal-noitems'); return; }
  const total = getTotal();
  document.getElementById('mp-total').textContent   = `Rs ${total.toFixed(2)}`;
  document.getElementById('mp-balance').textContent = `Rs ${total.toFixed(2)}`;

  let html = '';
  PAYMENT_MODES.forEach((m, i) => {
    html += `<div class="mpay-item">
      <span class="mpay-num">${i + 1}</span>
      <span class="mpay-label">${m}</span>
      <div class="mpay-fields">
        <div>
          <div style="font-size:11px;color:#777;margin-bottom:3px">Amount</div>
          <div class="d-input-wrap grey" style="height:34px">
            <span class="pfx">Rs</span>
            <input type="number" value="0" class="mp-amt" data-mode="${m}" oninput="mpCalc()"/>
          </div>
        </div>
        <div>
          <div style="font-size:11px;color:#777;margin-bottom:3px">Reference Number</div>
          <input type="text" placeholder="Reference Number"
            style="width:100%;height:34px;border:1px solid #ccc;border-radius:4px;padding:0 8px;font-size:13px;outline:none;font-family:inherit"/>
        </div>
      </div>
    </div>`;
  });
  document.getElementById('mpay-methods').innerHTML = html;
  openModal('modal-multipay');
}

function mpCalc() {
  const total = getTotal();
  const paid  = Array.from(document.querySelectorAll('.mp-amt'))
                     .reduce((s, el) => s + (parseFloat(el.value) || 0), 0);
  const bal   = total - paid;
  document.getElementById('mp-balance').textContent = `Rs ${bal.toFixed(2)}`;
  const btns = document.querySelectorAll('.btn-mpay');
  if (paid >= total) btns.forEach(b => b.classList.add('active'));
  else               btns.forEach(b => b.classList.remove('active'));
}

function mpSave(type) {
  if (!billItems.length) return;
  const paymentInputs    = document.querySelectorAll('.mpay-item');
  const paymentsPayload  = [];
  paymentInputs.forEach(el => {
    const amtInput = el.querySelector('.mp-amt');
    const refInput = el.querySelector('input[type=text]');
    const modeName = amtInput?.dataset.mode || '';
    const amt      = parseFloat(amtInput?.value || 0);
    if (amt > 0) {
      paymentsPayload.push({ payment_type: modeName, bank_account_id: null, amount: amt, reference: refInput?.value || '' });
    }
  });
  const subTotal = getSubtotal();
  const total    = getTotal();
  const payload  = {
    type:         'pos',
    party_id:     selectedPartyId || null,
    invoice_date: formatDateForServer(selectedDate),
    total_qty:    billItems.reduce((s, i) => s + i.qty, 0),
    total_amount: subTotal,
    discount_rs:  billDiscount,
    discount_pct: subTotal > 0 ? ((billDiscount / subTotal) * 100).toFixed(2) : 0,
    grand_total:  total,
    description:  remarks,
    items:        billItems.map(it => ({ item_name: it.name, item_code: it.item_code, quantity: it.qty, unit: it.unit, unit_price: it.sale_price, discount: it.discount, amount: rowTotal(it) })),
    payments:     paymentsPayload,
  };

  fetch(ROUTES.storeSale, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    body:   JSON.stringify(payload),
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeModal('modal-multipay');
      const payMode = paymentsPayload.length ? paymentsPayload.map(p=>p.payment_type).join('+') : 'Mixed';
      showPrintModal(data.bill_number || data.id, payMode, total, payload.items);
      if (type === 'new') addTab();
    } else {
      toast('Error saving bill.');
    }
  })
  .catch(() => toast('Network error.'));
}

// ── FIX 3: CUSTOMER – shows parties, tracks ID & name ──
function openCustDd()  { document.getElementById('cust-dd').classList.add('open'); }

function filterCust(q) {
  document.getElementById('cust-dd').classList.add('open');
  const items   = document.querySelectorAll('#cust-dd .c-item');
  const noRes   = document.getElementById('cust-no-results');
  let   visible = 0;
  items.forEach(el => {
    const name  = (el.dataset.name  || '').toLowerCase();
    const phone = (el.dataset.phone || '');
    const phone2 = (el.dataset.phone2 || '');
    const ptcl = (el.dataset.ptcl || '');
    const match = !q || name.includes(q.toLowerCase()) || phone.includes(q) || phone2.includes(q) || ptcl.includes(q);
    el.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  noRes.style.display = (visible === 0 && q) ? '' : 'none';
}

function selectCust(name, phone, partyId) {
  selectedPartyId   = partyId;
  selectedPartyName = name;
  document.getElementById('cust-in').value = phone ? `${name} (${phone})` : name;
  document.getElementById('cust-dd').classList.remove('open');
  // Auto-fill amount received when customer selected
  const total = getTotal();
  if (total > 0) {
    document.getElementById('amt-recv').value = total.toFixed(2);
    calcChange();
  }
}

function openAddCustomer() {
  document.getElementById('cust-dd').classList.remove('open');
  ['nc-name','nc-phone','nc-phone-2','nc-ptcl','nc-billing','nc-shipping'].forEach(id => {
    const el = document.getElementById(id);
    el.value = '';
    if (id === 'nc-shipping') el.disabled = false;
  });
  document.getElementById('same-addr').checked = false;
  openModal('modal-addcust');
}

function syncShip() {
  const cb = document.getElementById('same-addr');
  const sh = document.getElementById('nc-shipping');
  if (cb.checked) { sh.value = document.getElementById('nc-billing').value; sh.disabled = true; }
  else            { sh.value = ''; sh.disabled = false; }
}

document.addEventListener('input', e => {
  if (e.target.id === 'nc-billing' && document.getElementById('same-addr').checked)
    document.getElementById('nc-shipping').value = e.target.value;
});

function saveCustomer() {
  const name = document.getElementById('nc-name').value.trim();
  if (!name) { alert('Customer name is required.'); return; }
  const phone = document.getElementById('nc-phone').value.trim();
  const phone2 = document.getElementById('nc-phone-2').value.trim();
  const ptcl = document.getElementById('nc-ptcl').value.trim();
  const billing = document.getElementById('nc-billing').value.trim();
  const shipping = document.getElementById('nc-shipping').value.trim();

  fetch(ROUTES.storeParty, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    body:    JSON.stringify({
      name,
      phone,
      phone_number_2: phone2,
      ptcl_number: ptcl,
      billing_address: billing,
      shipping_address: shipping,
      party_type: ['customer'],
    }),
  })
  .then(r => {
    const ct = r.headers.get('content-type') || '';
    if (!ct.includes('application/json')) throw new Error('PartyController::store() must return JSON.');
    return r.json();
  })
  .then(data => {
    const party = (data && data.party) ? data.party : data;
    if (!party || !party.id) throw new Error('No party id: ' + JSON.stringify(data));

    selectCust(party.name || name, party.phone || phone, party.id);
    closeModal('modal-addcust');
    toast('Customer saved!');

    // Inject into dropdown for this session
    const dd  = document.getElementById('cust-dd');
    const div = document.createElement('div');
    div.className      = 'c-item';
    div.dataset.name   = (party.name || name).toLowerCase();
    div.dataset.phone  = party.phone || phone;
    div.dataset.phone2 = party.phone_number_2 || phone2;
    div.dataset.ptcl   = party.ptcl_number || ptcl;
    div.innerHTML      = `<div class="c-name">${party.name || name}</div>`
      + `${(party.phone || phone) ? `<div class="c-phone">📞 ${party.phone || phone}</div>` : ''}`
      + `${(party.phone_number_2 || phone2) ? `<div class="c-phone">Alt: ${party.phone_number_2 || phone2}</div>` : ''}`
      + `${(party.ptcl_number || ptcl) ? `<div class="c-phone">PTCL: ${party.ptcl_number || ptcl}</div>` : ''}`;
    div.onclick        = () => selectCust(party.name || name, party.phone || phone, party.id);
    dd.appendChild(div);
  })
  .catch(err => {
    console.error('[POS] saveCustomer error:', err.message);
    toast('Error saving customer. Check console (F12).');
  });
}

// ── CALENDAR ──


function toggleCal() {
  const pop = document.getElementById('cal-pop');
  pop.classList.toggle('open');
  if (pop.classList.contains('open')) renderCal();
}
function calPrev() { calDate = new Date(calDate.getFullYear(), calDate.getMonth() - 1, 1); renderCal(); }
function calNext() { calDate = new Date(calDate.getFullYear(), calDate.getMonth() + 1, 1); renderCal(); }

function renderCal() {
  const y = calDate.getFullYear(), m = calDate.getMonth();
  document.getElementById('cal-label').textContent = `${MONTHS[m]} ${y}`;
  const grid = document.getElementById('cal-grid');
  const dows = ['Su','Mo','Tu','We','Th','Fr','Sa'];
  let html = dows.map(d => `<div class="cal-dow">${d}</div>`).join('');
  const first    = new Date(y, m, 1).getDay();
  const days     = new Date(y, m + 1, 0).getDate();
  const prevDays = new Date(y, m, 0).getDate();
  for (let i = 0; i < first; i++) html += `<div class="cal-day other">${prevDays - first + 1 + i}</div>`;
  for (let d = 1; d <= days; d++) {
    const isSel = y === selectedDate.getFullYear() && m === selectedDate.getMonth() && d === selectedDate.getDate();
    html += `<div class="cal-day${isSel ? ' today' : ''}" onclick="pickDay(${y},${m},${d})">${d}</div>`;
  }
  const remaining = 42 - first - days;
  for (let d = 1; d <= remaining; d++) html += `<div class="cal-day other">${d}</div>`;
  grid.innerHTML = html;
}

function pickDay(y, m, d) {
  selectedDate = new Date(y, m, d);
  updateDateDisplay();
  document.getElementById('cal-pop').classList.remove('open');
  renderCal();
}

function updateDateDisplay() {
  document.getElementById('date-display').textContent = formatDateDisplay(selectedDate);
}

// ── TABS ──
function addTab() {
  saveTabState(activeTabId);
  tabCount++;
  const strip  = document.getElementById('tab-strip');
  const newBtn = strip.querySelector('.new-tab-btn');
  const t      = document.createElement('div');
  t.className  = 'tab';
  t.id         = `tab-${tabCount}`;
  t.innerHTML  = `<span>#${tabCount}</span>
    <span style="font-size:11px;color:#999">Ctrl+W</span>
    <button class="close-x" onclick="closeTab(${tabCount},event)">✕</button>`;
 t.onclick = (function(id){ return function(){ activateTab(id); }; })(tabCount);
  strip.insertBefore(t, newBtn);
  activeTabId = tabCount;
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.getElementById(`tab-${tabCount}`).classList.add('active');
  resetBill();
  toast('New Bill opened');
}
function activateTab(n) {
  saveTabState(activeTabId);
  activeTabId = n;
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  const t = document.getElementById(`tab-${n}`);
  if (t) t.classList.add('active');
  loadTabState(n);
}
function closeTab(n, e) {
  if (e) e.stopPropagation();
  const t = document.getElementById(`tab-${n}`);
  if (t) t.remove();
}

// ── MODAL HELPERS ──
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ── TOAST ──
let toastTimer;
function toast(msg) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 1800);
}

// ── KEYBOARD SHORTCUTS ──
document.addEventListener('keydown', e => {
  if (e.defaultPrevented) return;
  const tag = document.activeElement.tagName;
  if (['INPUT','TEXTAREA','SELECT'].includes(tag) && !e.ctrlKey && !e.metaKey &&
      e.key !== 'Escape' && e.key !== 'F1') return;

  const map = {
    F1:  () => document.getElementById('search-in').focus(),
    F2:  doChangeQty,
    F3:  doItemDiscount,
    F4:  doRemoveItem,
    F6:  doChangeUnit,
    F9:  doBillDiscount,
    F11: () => { document.getElementById('cust-in').focus(); openCustDd(); },
    F12: openRemarks,
  };

  if (map[e.key]) { e.preventDefault(); map[e.key](); return; }
  if (e.key === 'Escape') {
    document.querySelectorAll('.overlay.open').forEach(m => m.classList.remove('open'));
    document.getElementById('cal-pop').classList.remove('open');
    return;
  }
  if (e.ctrlKey || e.metaKey) {
    const k  = (e.key || '').toLowerCase();
    const cm = {
      p: doSaveBill,
      m: openMultiPay,
      t: addTab,
      f: () => toast('Full Breakup [Ctrl+F]'),
    };
    if (cm[k]) { e.preventDefault(); cm[k](); }
  }
});
</script>
<script>
  (function () {
    var isResizing = false, startX = 0, startW = 0, thEl = null;
    function init() {
      document.querySelectorAll('.custom-table thead th').forEach(function (th) {
        if (th.querySelector('.col-rh')) return;
        th.style.position = 'relative';
        var h = document.createElement('div');
        h.className = 'col-rh';
        h.style.cssText = 'position:absolute;right:0;top:0;bottom:0;width:5px;cursor:col-resize;z-index:10;';
        th.appendChild(h);
      });
    }
    document.addEventListener('mousedown', function (e) {
      if (!e.target.classList.contains('col-rh')) return;
      e.preventDefault();
      thEl = e.target.closest('th'); isResizing = true;
      startX = e.clientX; startW = thEl.getBoundingClientRect().width;
      document.body.style.cursor = 'col-resize';
      document.body.style.userSelect = 'none';
    });
    document.addEventListener('mousemove', function (e) {
      if (!isResizing || !thEl) return;
      var w = Math.max(60, startW + (e.clientX - startX));
      thEl.style.minWidth = w + 'px'; thEl.style.width = w + 'px';
    });
    document.addEventListener('mouseup', function () {
      if (!isResizing) return;
      isResizing = false; thEl = null;
      document.body.style.cursor = ''; document.body.style.userSelect = '';
    });
    document.addEventListener('DOMContentLoaded', init);
  })();
</script>
</body>
</html>
