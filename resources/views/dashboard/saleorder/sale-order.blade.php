<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar — Sale Orders</title>
  <meta name="description" content="Record supplier purchase bills with live preview in Vyapar.">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
  
  <style>
    /* ── Resizable column handle ── */
    .col-rh {
      position: absolute;
      right: 0; top: 0; bottom: 0;
      width: 6px;
      cursor: col-resize;
      z-index: 10;
      background: transparent;
    }
    .col-rh:hover, .col-rh:active {
      background: rgba(29, 140, 248, 0.35);
      border-radius: 3px;
    }

    /* ── Custom table (modals) ── */
    .custom-table thead th {
      font-size: 13px; color: #6c757d; font-weight: 500;
      border-bottom: 1px solid #eee;
      position: sticky; top: 0; z-index: 5;
      background-color: #fafafa;
      white-space: nowrap;
      position: relative;
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

    /* ── Sale order page ── */
    .sale-order-page { padding: 1.25rem; }

    .sale-order-card {
      border: 0; border-radius: 16px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .sale-order-toolbar {
      display: flex; justify-content: space-between;
      align-items: center; gap: 1rem;
      margin-bottom: 1.25rem; flex-wrap: wrap;
    }

    .sale-order-search {
      position: relative; min-width: 280px;
      max-width: 360px; width: 100%;
    }
    .sale-order-search i {
      position: absolute; left: 16px; top: 50%;
      transform: translateY(-50%); color: #64748b;
    }
    .sale-order-search input {
      border-radius: 999px; border: 1px solid #d7deea;
      padding: 0.85rem 1rem 0.85rem 2.75rem;
      width: 100%; background: #fff;
    }

    .sale-order-add-btn {
      border-radius: 999px; background: #1d8cf8;
      border: 0; color: #fff; padding: 0.8rem 1.35rem;
      font-weight: 600; box-shadow: 0 10px 20px rgba(29, 140, 248, 0.18);
    }

    /* ── Sale order table with fixed layout for resize ── */
    .sale-order-table {
      table-layout: fixed;
      min-width: 100%;
      border-collapse: collapse;
      width: 100%;
    }
    .sale-order-table thead th {
      position: relative;
      overflow: visible;
      background: #fafafa; color: #6c757d;
      font-size: 11.5px; font-weight: 600;
      border-bottom: 1px solid #eee;
      padding: 8px 6px !important;
      vertical-align: middle; white-space: nowrap;
    }
    .sale-order-table tbody td {
      padding: 8px 6px !important;
      border-bottom: 1px solid #f1f1f1;
      vertical-align: middle; color: #0f172a;
      white-space: nowrap;
      overflow: hidden; text-overflow: ellipsis;
      font-size: 12px;
    }
    .sale-order-table tbody td.action-cell {
      overflow: visible !important;
      position: relative;
    }
    .sale-order-table tbody td.action-menu-cell {
      overflow: visible !important;
      position: relative;
      text-align: center;
      white-space: nowrap;
    }
    .sale-order-table thead th.col-checkbox,
    .sale-order-table tbody td.col-checkbox {
      width: 34px;
      text-align: center;
    }
    .sale-order-table thead th.col-party,
    .sale-order-table tbody td.col-party { width: 130px; }
    .sale-order-table thead th.col-number,
    .sale-order-table tbody td.col-number { width: 75px; }
    .sale-order-table thead th.col-date,
    .sale-order-table tbody td.col-date { width: 85px; }
    .sale-order-table thead th.col-due,
    .sale-order-table tbody td.col-due { width: 85px; }
    .sale-order-table thead th.col-total,
    .sale-order-table tbody td.col-total { width: 100px; text-align: right; }
    .sale-order-table thead th.col-balance,
    .sale-order-table tbody td.col-balance { width: 95px; text-align: right; }
    .sale-order-table thead th.col-type,
    .sale-order-table tbody td.col-type { width: 75px; }
    .sale-order-table thead th.col-status,
    .sale-order-table tbody td.col-status { width: 110px; }
    .sale-order-table thead th.col-action,
    .sale-order-table tbody td.col-action { width: 100px; }
    .sale-order-table thead th.col-menu,
    .sale-order-table tbody td.col-menu { width: 36px; }
    .sale-order-table tbody tr:hover { background: #fafafa; }
    .sale-order-table th, .sale-order-table td { border-right: 1px solid #e9ecef !important; }
    .sale-order-table th:last-child, .sale-order-table td:last-child { border-right: none !important; }

    /* ── Column Header Dropdown Filters UI Styles ── */
    .column-filter-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      position: relative;
    }
    .filter-icon-btn {
      border: none;
      background: transparent;
      color: #94a3b8;
      padding: 0;
      width: 22px;
      height: 22px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
    }
    .filter-icon-btn:hover { color: #64748b; background: #f8fafc; }
    .column-filter-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 10px);
      left: 0;
      width: 220px;
      padding: 10px;
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
      z-index: 20;
    }
    .column-filter-dropdown.align-end {
      left: auto;
      right: 0;
    }
    .column-filter-dropdown.show { display: block; }
    .column-filter-dropdown .form-control { font-size: 12px; }
    .column-filter-dropdown .form-select { font-size: 12px; }
    .column-filter-label {
      font-size: 12px;
      color: #64748b;
      margin-bottom: 6px;
      display: block;
    }
    .column-filter-actions .btn {
      border-radius: 999px;
      min-width: 62px;
      padding: 0.35rem 0.9rem;
      font-weight: 700;
      box-shadow: none;
    }
    .column-filter-clear {
      background: #f3f4f6;
      border: 1px solid #e5e7eb;
      color: #6b7280;
    }
    .column-filter-clear:hover {
      background: #e5e7eb;
      color: #4b5563;
    }
    .column-filter-apply {
      background: #f43f5e;
      border: 1px solid #f43f5e;
      color: #fff;
    }
    .column-filter-apply:hover {
      background: #e11d48;
      border-color: #e11d48;
      color: #fff;
    }

    /* ── Status styles ── */
    .status-text { font-weight: 500; }
    .text-success { color: #16a34a !important; }
    .text-warning { color: #f97316 !important; }
    .text-danger { color: #ef4444 !important; }

    .convert-btn {
      border-radius: 8px; border: 1px solid #d1d5db;
      background: #f8fafc;
      color: #6b7280; font-weight: 600;
      padding: 0.55rem 0.95rem; white-space: nowrap;
      box-shadow: none;
    }
    .convert-btn:hover {
      color: #4b5563;
      background: #eef2f7;
      border-color: #cbd5e1;
    }

    .converted-link {
      display: inline-block;
      max-width: 140px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      vertical-align: middle;
      color: #6366f1; font-weight: 500;
      text-decoration: underline; text-underline-offset: 2px;
    }

    .action-menu-btn {
      border: 0; background: transparent;
      color: #64748b; padding: 0.35rem 0.5rem;
    }
    .action-menu-btn::after { display: none; }
    .sale-order-table tbody td.action-cell .dropdown-menu {
      z-index: 1090;
    }
    .sale-order-table tbody td.action-cell .dropdown-menu {
      min-width: 180px;
      padding: 0.45rem 0;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .sale-order-table tbody td.action-cell .dropdown-item {
      padding: 0.6rem 1rem;
      font-size: 14px;
      color: #1f2937;
      text-decoration: none;
    }
    .sale-order-table tbody td.action-cell .dropdown-item:hover {
      background: #e0f2fe;
      color: #0f172a;
      font-weight: 700;
    }
    .sale-order-table tbody td.action-cell .dropdown-divider {
      margin: 0.35rem 0;
    }
    .sale-order-table tbody td.action-cell {
      white-space: nowrap;
    }

    /* Main sale-order table wrapper should not clip menus or force an inner scroll */
    .sale-order-main-wrapper {
      overflow: visible !important;
      max-height: none !important;
      position: relative;
    }
    .invoice-preview-frame {
      width: 100%;
      height: calc(100vh - 170px);
      border: 0;
      background: #fff;
    }
    .sale-order-preview-footer {
      gap: 10px;
      justify-content: flex-end;
    }
    .sale-order-preview-action {
      appearance: none;
      border: 1.5px solid #ef233c;
      background: #fff;
      color: #ef233c;
      border-radius: 999px;
      padding: 0.58rem 1.15rem;
      font-weight: 700;
      line-height: 1;
      transition: background-color .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .sale-order-preview-action:hover,
    .sale-order-preview-action:focus {
      background: #ef233c;
      color: #fff;
      border-color: #ef233c;
      box-shadow: 0 8px 18px rgba(239, 35, 60, 0.22);
      transform: translateY(-1px);
    }
    .sale-order-preview-action:focus {
      outline: none;
    }
  </style>

  <script>
    const authUser = @json(Auth::user());
    window.App = window.App || {
      isAuthenticated: @json(Auth::check()),
      user: authUser ? {
        id: authUser.id,
        name: authUser.name,
        roles: @json(Auth::user()?->roles()->pluck('name')->toArray() ?? []),
        permissions: @json(Auth::user()?->getAllPermissions() ?? []),
      } : { id: null, name: null, roles: [], permissions: [] },
      logoutUrl: "{{ route('logout') }}",
      csrfToken: "{{ csrf_token() }}",
    };
    console.log('App initialized:', window.App);
  </script>
</head>

<body data-page="sale-orders">

  <main class="main-content sale-order-page" id="mainContent">

    <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom mb-2">
      <div class="text-center col-12">
        <h4 class="text-secondary">Sales Orders</h4>
      </div>
    </div>

    <div class="card sale-order-card">
      <div class="card-body">
        <div class="row g-2 mb-1">
          <p class="fw-bold">Transactions</p>
        </div>
        
        <div class="sale-order-toolbar">
          <form method="GET" action="{{ route('sale-order') }}" class="sale-order-search">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search..." name="search" value="{{ $search ?? '' }}">
          </form>
          <div>
            <button class="btn convert-btn me-2" id="bulkConvertTrigger" type="button">
              Convert to Sale
            </button>
            <button class="btn sale-order-add-btn" onclick="window.location='{{ route('sale-order.create') }}'">
              <i class="fa-solid fa-plus me-2"></i>Add Sale Order
            </button>
          </div>
        </div>

        <div class="table-responsive small-table table-wrapper sale-order-main-wrapper">
          <table class="table sale-order-table align-middle mb-0 txn-table"
                 id="saleOrderTransactionsTable"
                 data-column-drag="native"
                 data-column-drag-storage="vyapar.sale-order.transactions.column-order.v1">
            <thead>
              <tr>
                <th class="col-checkbox" style="vertical-align: middle;" data-column-key="select"><input type="checkbox" id="selectAllOrders"></th>
                
                <th class="col-party" data-column-key="party">
                  <div class="column-filter-header">
                    <span>PARTY</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Party">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="1">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="1">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="col-number" data-column-key="number">
                  <div class="column-filter-header">
                    <span>NO.</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter No.">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="2">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="2">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="col-date" data-column-key="date">
                  <div class="column-filter-header">
                    <span>DATE</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <label class="column-filter-label">Select Category</label>
                    <select class="form-select form-select-sm column-filter-operator">
                      <option value="eq">Equal To</option>
                      <option value="before">Before</option>
                      <option value="after">After</option>
                    </select>
                    <label class="column-filter-label mt-2">Select Date</label>
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="DD/MM/YYYY" inputmode="numeric">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="3">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="3">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="col-due" data-column-key="due_date">
                  <div class="column-filter-header">
                    <span>DUE DATE</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <label class="column-filter-label">Select Category</label>
                    <select class="form-select form-select-sm column-filter-operator">
                      <option value="eq">Equal To</option>
                      <option value="before">Before</option>
                      <option value="after">After</option>
                    </select>
                    <label class="column-filter-label mt-2">Select Date</label>
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="DD/MM/YYYY" inputmode="numeric">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="4">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="4">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="text-end col-total" data-column-key="amount">
                  <div class="column-filter-header justify-content-end">
                    <span class="me-3">AMOUNT</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown text-start">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Total">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="5">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="5">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="text-end col-balance" data-column-key="balance">
                  <div class="column-filter-header justify-content-end">
                    <span class="me-2">BALANCE</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown text-start">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Balance">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="6">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="6">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="col-type" data-column-key="type">
                  <div class="column-filter-header">
                    <span>TYPE</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Type">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="7">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="7">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="col-status" data-column-key="status">
                  <div class="column-filter-header">
                    <span>STATUS</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Status">
                    <div class="d-flex justify-content-end gap-2 mt-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="8">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="8">Apply</button>
                    </div>
                  </div>
                </th>

                <th class="col-action" data-column-key="action">ACTION</th>
                <th class="col-menu" data-column-key="menu"></th>
              </tr>
            </thead>
            <tbody>
              @forelse(($saleOrders ?? collect()) as $saleOrder)
                @php
                  $isCompleted = $saleOrder->status === 'completed';
                  $isOverdue = !$isCompleted && $saleOrder->due_date && $saleOrder->due_date->isPast();
                  $statusLabel = $isCompleted ? 'Order Completed' : ($isOverdue ? 'Order Overdue' : ucfirst($saleOrder->status ?? 'pending'));
                  $convertedInvoiceNumber = $convertedInvoiceNumbers[$saleOrder->id] ?? null;
                  $convertedInvoiceId = $convertedInvoiceIds[$saleOrder->id] ?? null;
                @endphp
                <tr data-edit-url="{{ route('sale-order.edit', $saleOrder->id) }}">
                  <td class="col-checkbox">
                    <input type="checkbox"
                           class="sale-order-select"
                           value="{{ $saleOrder->id }}"
                           data-party="{{ $saleOrder->display_party_name }}"
                           data-number="{{ $saleOrder->bill_number ?? '-' }}"
                           data-date="{{ optional($saleOrder->order_date)->format('d/m/Y') ?? '-' }}"
                           data-due="{{ optional($saleOrder->due_date)->format('d/m/Y') ?? '-' }}"
                           data-total="{{ number_format($saleOrder->grand_total ?? 0, 2) }}"
                           data-status="{{ $statusLabel }}"
                           @if($isCompleted) disabled @endif>
                  </td>
                  <td class="col-party">{{ $saleOrder->display_party_name }}</td>
                  <td class="col-number">{{ $saleOrder->bill_number ?? '-' }}</td>
                  <td class="col-date">{{ optional($saleOrder->order_date)->format('d/m/Y') ?? '-' }}</td>
                  <td class="col-due">{{ optional($saleOrder->due_date)->format('d/m/Y') ?? '-' }}</td>
                  <td class="text-end col-total">Rs {{ number_format($saleOrder->grand_total ?? 0, 2) }}</td>
                  <td class="text-end col-balance">Rs {{ number_format($saleOrder->balance ?? 0, 2) }}</td>
                  <td class="col-type">Sale Order</td>
                  <td class="col-status">
                    <span class="status-text {{ $isCompleted ? 'text-success' : ($isOverdue ? 'text-danger' : 'text-warning') }}">
                      {{ $statusLabel }}
                    </span>
                  </td>
                  <td class="action-cell col-action">
                    @if($isCompleted)
                      @php
                        $invoiceLabel = $convertedInvoiceNumber ?: ($convertedInvoiceId ? 'ID '.$convertedInvoiceId : null);
                        $invoiceRouteId = $convertedInvoiceId ?: $saleOrder->id;
                      @endphp
                      <a href="{{ route('invoice', ['sale_id' => $invoiceRouteId]) }}" class="converted-link" title="View Invoice No.{{ $invoiceLabel }}">
                        Invoice No.{{ $invoiceLabel }}
                      </a>
                    @else
                      <a href="{{ route('sale-orders.convert-to-sale', $saleOrder->id) }}" class="btn convert-btn btn-sm">
                        CONVERT TO SALE
                      </a>
                    @endif
                  </td>
                  <td class="action-menu-cell col-menu">
                    <div class="dropdown d-inline action-menu sale-order-action-menu"
                         data-preview-url="{{ route('sale-orders.preview', $saleOrder->id) }}"
                         data-pdf-url="{{ route('sale-orders.pdf', $saleOrder->id) }}"
                         data-print-url="{{ route('sale-orders.print', $saleOrder->id) }}"
                         data-party-email="{{ $saleOrder->party?->email ?? '' }}"
                         data-party-name="{{ $saleOrder->display_party_name }}"
                         data-sale-number="{{ $saleOrder->bill_number ?? $saleOrder->id }}"
                         data-email-url="{{ route('sale.invoice-email', $saleOrder) }}"
                         data-duplicate-url="{{ route('sale-order.create', ['duplicate_sale_id' => $saleOrder->id]) }}">
                      <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="return transactionPasscodeNavigate('{{ route('sale-order.edit', $saleOrder->id) }}');">View/Edit</a></li>
                        <li><a class="dropdown-item" href="#" onclick="previewSaleOrder(this); return false;">Preview</a></li>
                        <li><a class="dropdown-item" href="#" onclick="printSaleOrder(this); return false;">Print</a></li>
                        <li><a class="dropdown-item" href="#" onclick="duplicateSaleOrder(this); return false;">Duplicate</a></li>
                        <li><a class="dropdown-item" href="#" onclick="viewSaleOrderHistory('{{ $convertedInvoiceId ? route('sale.bank-history', $convertedInvoiceId) : '' }}'); return false;">View History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="return transactionPasscodeExecute('deleteSaleOrder','{{ route('sale.destroy', $saleOrder->id) }}');">Delete</a></li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center text-muted py-4">No sale orders found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </main>

  <div class="modal fade" id="bulkConvertModal" tabindex="-1" aria-labelledby="bulkConvertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bulkConvertModalLabel">Select orders to attach</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <div class="text-muted small">Selected orders</div>
          </div>
          <div class="table-wrapper">
            <table class="table align-middle custom-table mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Party</th>
                  <th>No.</th>
                  <th>Date</th>
                  <th>Due Date</th>
                  <th class="text-end">Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="bulkConvertTableBody">
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">Select sale orders to convert.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="bulkConvertConfirm">Convert to Sale</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="saleOrderHistoryModal" tabindex="-1" aria-labelledby="saleOrderHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="saleOrderHistoryLabel">View History</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-wrapper">
            <table class="table align-middle custom-table mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Bank</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Reference</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody id="saleOrderHistoryBody">
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">No history to show.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
  </div>
</div>

  @include('dashboard.partials.document-email-modal', [
    'modalId' => 'documentEmailModal',
    'toId' => 'documentEmailTo',
    'subjectId' => 'documentEmailSubject',
    'messageId' => 'documentEmailMessage',
    'viewPdfBtnId' => 'documentEmailViewPdfBtn',
    'sendBtnId' => 'documentEmailSendBtn',
    'title' => 'Send Email',
    'subjectValue' => 'Your Vyapar PDF',
    'messageValue' => "Dear Sir,\nPlease find the attached document below.\nThank you for doing business with us.\nThanks and regards.",
    'helperText' => 'The invoice PDF will be attached automatically.',
  ])

  <div class="modal fade" id="saleOrderPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 96vw;">
      <div class="modal-content border-0 shadow">
        <div class="modal-header">
          <h5 class="modal-title">Sale Order Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          <iframe id="saleOrderPreviewFrame" class="invoice-preview-frame" title="Invoice Preview"></iframe>
        </div>
        <div class="modal-footer sale-order-preview-footer">
          <button type="button" class="sale-order-preview-action" id="saleOrderOpenPdfBtn">Open PDF</button>
          <button type="button" class="sale-order-preview-action" id="saleOrderPrintBtn">Print</button>
          <button type="button" class="sale-order-preview-action" id="saleOrderSavePdfBtn">Save PDF</button>
          <button type="button" class="sale-order-preview-action" id="saleOrderEmailPdfBtn">Email PDF</button>
          <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  @include('dashboard.partials.transaction-passcode-guard')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/document-email-preview.js') }}"></script>
  
  <script>
    const bulkConvertUrl = "{{ route('sale-orders.bulk-convert') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function appendSaleOrderThemeParams(url) {
      try {
        const match = String(url || '').match(/\/sale-orders\/(\d+)\//);
        const saleId = match ? match[1] : null;
        if (!saleId || !window.localStorage) {
          return url;
        }

        const raw = window.localStorage.getItem(`saleInvoiceTheme:${saleId}`) || window.localStorage.getItem('saleInvoiceTheme:draft');
        if (!raw) {
          return url;
        }

        const theme = JSON.parse(raw);
        const params = new URLSearchParams();
        const mode = String(theme?.mode || '').trim();
        const regularThemeId = Number(theme?.regularThemeId || 0);
        const thermalThemeId = Number(theme?.thermalThemeId || 0);
        const activeThemeId = mode === 'thermal' ? thermalThemeId : regularThemeId;

        if (mode) params.set('mode', mode);
        if (activeThemeId > 0) params.set('theme_id', String(activeThemeId));
        if (theme?.accent) params.set('accent', String(theme.accent));
        if (theme?.accent2) params.set('accent2', String(theme.accent2));
        params.set('theme_applied', '1');
        const signatureImage = window.localStorage.getItem('vyapar_signature_image');
        if (signatureImage) params.set('signature_image', signatureImage);

        if (!params.toString()) {
          return url;
        }

        return url + (String(url).includes('?') ? '&' : '?') + params.toString();
      } catch (error) {
        return url;
      }
    }

    function getSaleOrderPreviewData(modalEl) {
      const frame = document.getElementById('saleOrderPreviewFrame');
      const previewUrl = String(modalEl?.dataset?.previewUrl || frame?.src || '');
      const pdfUrl = String(modalEl?.dataset?.pdfUrl || previewUrl.replace('/preview', '/pdf'));
      return {
        previewUrl,
        pdfUrl,
        partyEmail: String(modalEl?.dataset?.partyEmail || frame?.dataset?.partyEmail || ''),
        partyName: String(modalEl?.dataset?.partyName || frame?.dataset?.partyName || ''),
        saleNumber: String(modalEl?.dataset?.saleNumber || frame?.dataset?.saleNumber || ''),
        emailUrl: String(modalEl?.dataset?.emailUrl || frame?.dataset?.emailUrl || ''),
      };
    }

    function resolveSaleOrderAction(trigger) {
      const menu = trigger?.closest('.sale-order-action-menu') || trigger?.closest('.action-menu');
      return {
        previewUrl: menu?.dataset?.previewUrl || '',
        pdfUrl: menu?.dataset?.pdfUrl || '',
        printUrl: menu?.dataset?.printUrl || '',
        partyEmail: menu?.dataset?.partyEmail || '',
        partyName: menu?.dataset?.partyName || '',
        saleNumber: menu?.dataset?.saleNumber || '',
        emailUrl: menu?.dataset?.emailUrl || '',
        duplicateUrl: menu?.dataset?.duplicateUrl || '',
      };
    }

    function previewSaleOrder(trigger) {
      const { previewUrl, pdfUrl, partyEmail, partyName, saleNumber, emailUrl } = resolveSaleOrderAction(trigger);
      const modalEl = document.getElementById('saleOrderPreviewModal');
      const frame = document.getElementById('saleOrderPreviewFrame');
      const themedUrl = appendSaleOrderThemeParams(previewUrl);
      const themedPdfUrl = appendSaleOrderThemeParams(pdfUrl || String(previewUrl).replace('/preview', '/pdf'));

      if (!modalEl || !frame) {
        window.open(themedUrl, '_blank');
        return;
      }

      frame.src = themedUrl;
      modalEl.dataset.previewUrl = themedUrl;
      modalEl.dataset.pdfUrl = themedPdfUrl;
      modalEl.dataset.partyEmail = partyEmail || '';
      modalEl.dataset.partyName = partyName || '';
      modalEl.dataset.saleNumber = saleNumber || '';
      modalEl.dataset.emailUrl = emailUrl || '';
      frame.dataset.previewUrl = themedUrl;
      frame.dataset.pdfUrl = themedPdfUrl;
      frame.dataset.partyEmail = partyEmail || '';
      frame.dataset.partyName = partyName || '';
      frame.dataset.saleNumber = saleNumber || '';
      frame.dataset.emailUrl = emailUrl || '';
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
    function printSaleOrder(trigger) {
      const { printUrl } = resolveSaleOrderAction(trigger);
      window.open(appendSaleOrderThemeParams(printUrl), '_blank');
    }
    function openSaleOrderPdf(url) {
      window.open(appendSaleOrderThemeParams(url), '_blank');
    }
    function duplicateSaleOrder(trigger) {
      const { duplicateUrl } = resolveSaleOrderAction(trigger);
      window.open(duplicateUrl, '_blank');
    }

    function openSaleOrderPreviewPdf() {
      const modalEl = document.getElementById('saleOrderPreviewModal');
      const { pdfUrl } = getSaleOrderPreviewData(modalEl);
      if (!pdfUrl) return;
      window.open(pdfUrl, '_blank', 'noopener');
    }

    function saveSaleOrderPreviewPdf() {
      const modalEl = document.getElementById('saleOrderPreviewModal');
      const { pdfUrl } = getSaleOrderPreviewData(modalEl);
      if (!pdfUrl) return;
      const downloadUrl = appendSaleOrderThemeParams(pdfUrl) + (String(pdfUrl).includes('?') ? '&' : '?') + 'download=1';
      window.open(downloadUrl, '_blank', 'noopener');
    }

    function printSaleOrderPreview() {
      const frame = document.getElementById('saleOrderPreviewFrame');
      if (!frame || !frame.contentWindow) return;
      frame.contentWindow.focus();
      frame.contentWindow.print();
    }

    const saleOrderEmailComposer = window.DocumentEmailPreview?.init({
      name: 'sale-order-email-preview',
      previewModalId: 'saleOrderPreviewModal',
      previewFrameId: 'saleOrderPreviewFrame',
      emailModalId: 'documentEmailModal',
      emailToId: 'documentEmailTo',
      emailSubjectId: 'documentEmailSubject',
      emailMessageId: 'documentEmailMessage',
      viewPdfBtnId: 'documentEmailViewPdfBtn',
      sendBtnId: 'documentEmailSendBtn',
      openButtonId: 'saleOrderEmailPdfBtn',
      toastId: 'documentEmailToast',
      defaultSubject: (context) => `Sale Order PDF${context.saleNumber ? ' - ' + context.saleNumber : ''}`,
      defaultMessage: (context) => {
        const pdfLink = context.pdfUrl || context.previewUrl || '';
        return `Dear ${context.partyName || 'Sir'},\n\nPlease find the sale order PDF attached below.\n${pdfLink ? 'PDF Link: ' + pdfLink + '\n' : ''}\nThank you for doing business with us.\nThanks and regards.`;
      },
    });

    function bindSaleOrderPreviewButtons() {
      const openPdfBtn = document.getElementById('saleOrderOpenPdfBtn');
      const printBtn = document.getElementById('saleOrderPrintBtn');
      const savePdfBtn = document.getElementById('saleOrderSavePdfBtn');

      if (openPdfBtn) openPdfBtn.addEventListener('click', openSaleOrderPreviewPdf);
      if (printBtn) printBtn.addEventListener('click', printSaleOrderPreview);
      if (savePdfBtn) savePdfBtn.addEventListener('click', saveSaleOrderPreviewPdf);
      document.getElementById('saleOrderEmailPdfBtn')?.addEventListener('click', function () {
        const composer = saleOrderEmailComposer || window.DocumentEmailPreview?.get('sale-order-email-preview');
        composer?.open();
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', bindSaleOrderPreviewButtons, { once: true });
    } else {
      bindSaleOrderPreviewButtons();
    }

    function viewSaleOrderHistory(historyUrl) {
      if (!historyUrl) {
        alert('No bank history available until the sale order is converted.');
        return;
      }

      const modalEl = document.getElementById('saleOrderHistoryModal');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      const tbody = document.getElementById('saleOrderHistoryBody');
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>`;

      fetch(historyUrl, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      })
        .then(res => res.json())
        .then(data => {
          const rows = (data.entries || []).map((entry, index) => `
            <tr>
              <td>${index + 1}</td>
              <td>${entry.bank_name || '-'}</td>
              <td>${entry.type || '-'}</td>
              <td>Rs ${Number(entry.amount || 0).toFixed(2)}</td>
              <td>${entry.reference || '-'}</td>
              <td>${entry.date || '-'}</td>
            </tr>
          `).join('');

          tbody.innerHTML = rows || `<tr><td colspan="6" class="text-center text-muted py-4">No history found.</td></tr>`;
          modal.show();
        })
        .catch(() => {
          tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Unable to load history.</td></tr>`;
          modal.show();
        });
    }

    function deleteSaleOrder(url) {
      if (!confirm('Are you sure you want to delete this sale order?')) return;

      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
      })
        .then(async (response) => {
          const data = await response.json();
          if (!response.ok) throw new Error(data.message || 'Delete failed');
          window.location.reload();
        })
        .catch((error) => {
          alert(error.message || 'Unable to delete sale order.');
        });
    }

    function getSelectedOrderRows() {
      return Array.from(document.querySelectorAll('.sale-order-select:checked')).filter(input => !input.disabled);
    }

    function populateBulkConvertModal() {
      const tbody = document.getElementById('bulkConvertTableBody');
      const rows = getSelectedOrderRows();

      if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Select sale orders to convert.</td></tr>`;
        return;
      }

      tbody.innerHTML = rows.map((input, index) => `
        <tr>
          <td>${index + 1}</td>
          <td>${input.dataset.party || '-'}</td>
          <td>${input.dataset.number || '-'}</td>
          <td>${input.dataset.date || '-'}</td>
          <td>${input.dataset.due || '-'}</td>
          <td class="text-end">Rs ${input.dataset.total || '0.00'}</td>
          <td>${input.dataset.status || '-'}</td>
        </tr>
      `).join('');
    }

    document.getElementById('selectAllOrders')?.addEventListener('change', function () {
      const checked = this.checked;
      document.querySelectorAll('.sale-order-select').forEach(input => {
        if (!input.disabled) input.checked = checked;
      });
    });

    document.querySelectorAll('.sale-order-select').forEach(input => {
      input.addEventListener('change', function () {
        const allInputs = Array.from(document.querySelectorAll('.sale-order-select')).filter(i => !i.disabled);
        const allChecked = allInputs.length && allInputs.every(i => i.checked);
        const selectAll = document.getElementById('selectAllOrders');
        if (selectAll) selectAll.checked = allChecked;
      });
    });

    document.getElementById('bulkConvertTrigger')?.addEventListener('click', function () {
      populateBulkConvertModal();
      const modalEl = document.getElementById('bulkConvertModal');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    });

    document.getElementById('bulkConvertConfirm')?.addEventListener('click', function () {
      const rows = getSelectedOrderRows();
      if (!rows.length) {
        alert('Please select at least one sale order.');
        return;
      }

      const ids = rows.map(input => Number(input.value));
      fetch(bulkConvertUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ sale_order_ids: ids }),
      })
        .then(async res => {
          const data = await res.json();
          if (!res.ok) throw new Error(data.message || 'Bulk conversion failed.');
          window.location.reload();
        })
        .catch(err => {
          alert(err.message || 'Bulk conversion failed.');
        });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.querySelector('.sale-order-search input');
      const columnFilters = {};
      const dateFilterColumns = new Set(['date', 'due_date']);

      function normalizeText(text) {
        return text.toString().toLowerCase().trim().replace(/\s+/g, ' ');
      }

      function parseDateText(text) {
        const match = String(text || '').match(/(\d{2})\/(\d{2})\/(\d{4})/);
        if (!match) return null;

        const [, dd, mm, yyyy] = match;
        const parsed = new Date(Number(yyyy), Number(mm) - 1, Number(dd));
        return Number.isNaN(parsed.getTime()) ? null : parsed;
      }

      function matchesDateFilter(cellText, filterValue) {
        const cellDate = parseDateText(cellText);
        if (!cellDate || !filterValue) return false;

        if (String(filterValue.value || '').length < 10) return true;

        const targetDate = parseDateText(filterValue.value);
        if (!targetDate) return false;

        const cellTime = cellDate.setHours(0, 0, 0, 0);
        const targetTime = targetDate.setHours(0, 0, 0, 0);

        switch (filterValue.operator) {
          case 'before':
            return cellTime < targetTime;
          case 'after':
            return cellTime > targetTime;
          default:
            return cellTime === targetTime;
        }
      }

      function applySalesTableFilters() {
        const rows = document.querySelectorAll('.txn-table tbody tr');
        const universalSearchQuery = searchInput ? normalizeText(searchInput.value) : '';

        rows.forEach((row) => {
          if (row.cells.length === 1) return;

          const matchesUniversal = !universalSearchQuery
            || normalizeText(row.textContent || '').includes(universalSearchQuery);
          const matchesColumnFilters = Object.entries(columnFilters).every(([columnKey, filterValue]) => {
            const header = document.querySelector(`.sale-order-table thead th[data-column-key="${columnKey}"]`);
            const cell = header ? row.cells[header.cellIndex] : null;
            if (!cell) return true;

            const cellText = normalizeText(cell.textContent || cell.innerText);
            if (dateFilterColumns.has(columnKey)) {
              return matchesDateFilter(cell.textContent || cell.innerText, filterValue);
            }

            return cellText.includes(filterValue);
          });

          row.style.display = (matchesUniversal && matchesColumnFilters) ? '' : 'none';
        });
      }

      // Open Dropdown Action Toggles
      document.querySelectorAll('.filter-icon-btn').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          const th = this.closest('th');
          const dropdown = this.closest('.column-filter-header')?.nextElementSibling;
          if (!dropdown) return;

          document.querySelectorAll('.column-filter-dropdown.show').forEach((openDropdown) => {
            if (openDropdown !== dropdown) openDropdown.classList.remove('show');
          });

          const rect = th ? th.getBoundingClientRect() : null;
          if (rect) {
            dropdown.style.left = 'auto';
            dropdown.style.right = 'auto';
            dropdown.style.top = `${th.offsetHeight + 10}px`;
            dropdown.style.position = 'fixed';
            dropdown.style.zIndex = '1080';
            dropdown.style.minWidth = '220px';

            const left = Math.min(Math.max(8, rect.left), window.innerWidth - 232);
            const rightAligned = rect.right > window.innerWidth - 232;
            dropdown.style.left = rightAligned ? 'auto' : `${left}px`;
            dropdown.style.right = rightAligned ? `${Math.max(8, window.innerWidth - rect.right)}px` : 'auto';
            dropdown.style.top = `${Math.min(rect.bottom + 8, window.innerHeight - 16)}px`;
          }

          dropdown.classList.add('show');
        });
      });

      // Filter Column Context Confirmation Handler
      document.querySelectorAll('.column-filter-apply').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          const columnKey = this.closest('th')?.dataset.columnKey;
          const dropdown = this.closest('.column-filter-dropdown');
          const input = dropdown?.querySelector('.column-filter-input');
          const operator = dropdown?.querySelector('.column-filter-operator')?.value || 'eq';
          const normalizedValue = normalizeText(input?.value || '');

          if (!columnKey) return;

          if (dateFilterColumns.has(columnKey)) {
            if (normalizedValue) {
              columnFilters[columnKey] = { type: 'date', operator, value: normalizedValue };
            } else {
              delete columnFilters[columnKey];
            }
          } else {
            if (normalizedValue) {
              columnFilters[columnKey] = normalizedValue;
            } else {
              delete columnFilters[columnKey];
            }
          }
          dropdown?.classList.remove('show');
          applySalesTableFilters();
        });
      });

      // Sync character keystrokes inside inline search boxes instantly
      document.querySelectorAll('.column-filter-input').forEach((input) => {
        input.addEventListener('input', function () {
          const dropdown = this.closest('.column-filter-dropdown');
          const applyButton = dropdown?.querySelector('.column-filter-apply');
          const columnKey = applyButton?.closest('th')?.dataset.columnKey;
          const operator = dropdown?.querySelector('.column-filter-operator')?.value || 'eq';

          if (!columnKey) return;
          const normalizedValue = normalizeText(this.value || '');

          if (dateFilterColumns.has(columnKey)) {
            if (normalizedValue) {
              columnFilters[columnKey] = { type: 'date', operator, value: normalizedValue };
            } else {
              delete columnFilters[columnKey];
            }
          } else {
            if (normalizedValue) {
              columnFilters[columnKey] = normalizedValue;
            } else {
              delete columnFilters[columnKey];
            }
          }
          applySalesTableFilters();
        });
      });

      document.querySelectorAll('.column-filter-operator').forEach((select) => {
        select.addEventListener('change', function () {
          const dropdown = this.closest('.column-filter-dropdown');
          const applyButton = dropdown?.querySelector('.column-filter-apply');
          const columnKey = applyButton?.closest('th')?.dataset.columnKey;
          const input = dropdown?.querySelector('.column-filter-input');

          if (!dateFilterColumns.has(columnKey)) return;

          const normalizedValue = normalizeText(input?.value || '');
          if (normalizedValue) {
            columnFilters[columnKey] = { type: 'date', operator: this.value || 'eq', value: normalizedValue };
          } else {
            delete columnFilters[columnKey];
          }
          applySalesTableFilters();
        });
      });

      // Clear Context Triggers
      document.querySelectorAll('.column-filter-clear').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          const columnKey = this.closest('th')?.dataset.columnKey;
          const dropdown = this.closest('.column-filter-dropdown');
          const input = dropdown?.querySelector('.column-filter-input');
          const operator = dropdown?.querySelector('.column-filter-operator');

          if (input) input.value = '';
          if (operator) operator.value = 'eq';
          if (columnKey) delete columnFilters[columnKey];
          dropdown?.classList.remove('show');
          applySalesTableFilters();
        });
      });

      searchInput?.addEventListener('input', applySalesTableFilters);

      // Dismiss menu windows clicking outside of target components
      document.addEventListener('click', function (event) {
        if (!event.target.closest('.column-filter-dropdown') && !event.target.closest('.filter-icon-btn')) {
          document.querySelectorAll('.column-filter-dropdown.show').forEach((dropdown) => {
            dropdown.classList.remove('show');
            dropdown.removeAttribute('style');
          });
        }
      });

      /* ─── COLUMN DRAG & RESIZE WORKFLOW ─── */
      document.querySelectorAll('.sale-order-table tbody tr[data-edit-url]').forEach((row) => {
        row.addEventListener('dblclick', function (event) {
          const target = event.target;
          if (!target) return;

          if (target.closest('a, button, input, .dropdown, .dropdown-menu, .action-cell, .action-menu-cell')) {
            return;
          }

          const editUrl = row.dataset.editUrl;
          if (editUrl) {
            window.location.href = editUrl;
          }
        });
      });

      let isResizing = false, startX = 0, startW = 0, thEl = null;

      function initResizeHandles() {
        document.querySelectorAll('.custom-table thead th, .sale-order-table thead th').forEach(function (th) {
          if (th.querySelector('.col-rh')) return;
          th.style.position = 'relative';
          th.style.overflow = 'visible';
          th.style.width = th.getBoundingClientRect().width + 'px';

          const handle = document.createElement('div');
          handle.className = 'col-rh';
          th.appendChild(handle);
        });
      }

      document.addEventListener('mousedown', function (e) {
        if (!e.target.classList.contains('col-rh')) return;
        e.preventDefault();
        thEl = e.target.closest('th');
        isResizing = true;
        startX = e.clientX;
        startW = thEl.getBoundingClientRect().width;
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
      });

      document.addEventListener('mousemove', function (e) {
        if (!isResizing || !thEl) return;
        const widthCalc = Math.max(60, startW + (e.clientX - startX));
        thEl.style.width = widthCalc + 'px';
        thEl.style.minWidth = widthCalc + 'px';
      });

      document.addEventListener('mouseup', function () {
        if (!isResizing) return;
        isResizing = false; 
        thEl = null;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
      });

      initResizeHandles();
    });
  </script>
  <script src="{{ asset('js/transaction-column-drag.js') }}"></script>
</body>

</html>
