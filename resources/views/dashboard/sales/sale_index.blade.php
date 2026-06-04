<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar — Sales Invoices</title>
  <meta name="description" content="Create professional estimates and quotations for your customers in Vyapar.">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <!-- Custom Styles -->
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
  <link href="{{ asset('css/sale.css') }}" rel="stylesheet">

  <script>
    // Ensure window.App is always initialized, even if Auth is null
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

  <style>
    .custom-table thead th {
  font-size: 13px;
  color: #6c757d;
  font-weight: 500;
  border-bottom: 1px solid #eee;
}

.custom-table tbody td {
  font-size: 14px;
  padding: 14px 10px;
  border-bottom: 1px solid #f1f1f1;
}

.custom-table tbody tr:hover {
  background-color: #fafafa;
}

.filter-icon {
  font-size: 11px;
  margin-left: 6px;
  color: #adb5bd;
  cursor: pointer;
}

.status-text {
  font-weight: 500;
}

.txn-table tbody tr.sale-cancelled {
  opacity: .72;
}

.txn-table tbody tr.sale-cancelled td {
  text-decoration: line-through;
}

.history-table td,
.history-table th {
  vertical-align: middle;
}

.text-success {
  color: #22c55e !important;
}

.text-warning {
  color: #f59e0b !important;
}

.text-danger {
  color: #ef4444 !important;
}

.action-icon {
  font-size: 14px;
  margin-right: 12px;
  cursor: pointer;
  color: #6c757d;
}

.action-icon:hover {
  color: #000;
}

.custom-table {
  border-collapse: collapse;
}

.custom-table th,
.custom-table td {
  border-right: 1px solid #e9ecef; /* vertical lines */
}

.custom-table th:last-child,
.custom-table td:last-child {
  border-right: none; /* last column pe line nahi */
}

.custom-table th,
.custom-table td {
  border-right: 1px solid #f1f1f1;
}

.custom-table thead th {
  background-color: #fafafa;
}

.add-sale-btn {
  background: linear-gradient(135deg, #ff4d4d, #ff4b4b);
  color: #fff;
  border: none;
  border-radius: 50px;
  padding: 10px 22px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(255, 77, 77, 0.3);
  transition: all 0.25s ease;
  display: inline-flex;
  align-items: center;
}

.add-sale-btn i {
  font-size: 13px;
}

.add-sale-btn:hover {
  transform: translateY(-2px) scale(1.03);
  box-shadow: 0 6px 16px rgba(255, 77, 77, 0.45);
  background: linear-gradient(135deg, #ff3b3b, #ff3b3b);
}

.add-sale-btn:active {
  transform: scale(0.97);
  box-shadow: 0 3px 8px rgba(255, 77, 77, 0.3);
}

.agarri-btn {
  background: linear-gradient(135deg, #17365d, #295b93);
  color: #fff;
  border: none;
  border-radius: 50px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(23, 54, 93, 0.28);
  transition: all 0.25s ease;
  display: inline-flex;
  align-items: center;
}

.agarri-btn:hover {
  transform: translateY(-2px) scale(1.02);
  color: #fff;
  box-shadow: 0 6px 16px rgba(23, 54, 93, 0.35);
}

/* common pill */
.filter-pill {
  background-color: #E4F2FF;
  border-radius: 999px;
  display: flex;
  align-items: center;
  height: 38px;
  padding: 0 8px;
}

/* left part */
.filter-left {
  border-right: 1px solid #ccc;
  padding: 0 10px;
}

/* right part */
.filter-right {
  padding: 0 10px;
}

/* select clean */
.filter-select {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  padding: 0;
  margin: 0;
}

/* small pill (All Firms) */
.small-pill {
  padding: 0 12px;
  min-width: 120px;
}

/* date input */
.date-input {
  border: none;
  background: transparent;
  font-size: 12px;
  width: 110px;
  outline: none;
}

.table-wrapper {
  overflow-x: hidden;
  overflow-y: auto;
  margin-bottom: 20px;
  max-height: 68vh;
  border: 1px solid #eef2f7;
  border-radius: 12px;
}

.card-body {
  overflow: hidden;
}

.pagination {
  margin: 0;
}

.d-flex.justify-content-between.align-items-center.mt-3 {
  padding-top: 10px;
  border-top: 1px solid #eee;
}

.table-responsive {
  overflow-x: hidden;
  overflow-y: auto !important;
  max-height: 68vh;
}
.pagination-wrapper {
  display: flex;
  justify-content: flex-end;
}

.custom-table {
  width: 100%;
  min-width: 0;
  table-layout: fixed;
}

.custom-table thead th {
  position: sticky;
  top: 0;
  z-index: 5;
  background-color: #fafafa;
  vertical-align: top;
  white-space: normal;
  word-break: break-word;
}

.custom-table tbody td {
  white-space: normal;
  word-break: break-word;
}

.custom-table th:last-child,
.custom-table td:last-child {
  width: 90px;
}

.txn-table tbody tr[data-edit-url] {
  cursor: pointer;
}

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
  width: 18px;
  height: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.filter-icon-btn:hover {
  color: #334155;
}

.column-filter-dropdown {
  display: none;
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 220px;
  padding: 10px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
  z-index: 20;
}

.column-filter-dropdown.show {
  display: block;
}

.column-filter-dropdown .form-control {
  font-size: 12px;
}

  </style>
</head>

<body data-page="sale"
      data-transaction-passcode-enabled="{{ $transactionPasscodeEnabled ? 1 : 0 }}"
      data-transaction-passcode-verify-url="{{ route('sale.verify-passcode') }}">

  <!-- Navbar & Sidebar injected by components.js -->

  <!-- ═══════════════════════════════════════
     MAIN CONTENT — ESTIMATE / QUOTATION
     ═══════════════════════════════════════ -->
  <main id="mainContent" style="padding: 0px 0px; margin-left:17rem; margin-top: 3.6rem;">
    <div class="container-fluid col-12">
      <div class="d-flex justify-content-between align-items-center bg-white mb-2 p-4">
        <div>
         <div class="dropdown">
            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="h4"> Sales Invoice</span>
            </button>
            <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('sale.index') }}">Sale Invoice</a></li>
            <li><a class="dropdown-item" href="{{ route('sale.estimate') }}">Estimate / Quotation</a></li>
            <li><a class="dropdown-item" href="{{ route('sale-return') }}">Sale Return / Cr. Note</a></li>
            <li><a class="dropdown-item" href="{{ route('payment-in') }}">Payment In</a></li>
            <li><a class="dropdown-item" href="{{ route('payment-out') }}">Payment out</a></li>
            <li><a class="dropdown-item" href="{{ route('purchase-expenses') }}">Purchase Bill</a></li>
            <li><a class="dropdown-item" href="{{ route('purchase-return') }}">Purchase Return / Dr. Note</a></li>
            <li><a class="dropdown-item" href="{{ route('expense') }}">Expenses</a></li>

            </ul>
          </div>
        </div>
       <div class="d-flex align-items-center gap-2">
        <button id="agarriListBtn" class="btn agarri-btn" type="button">
          <i class="fa-solid fa-file-pdf me-2"></i> Agarri List
        </button>
        <button class="btn add-sale-btn" onclick="window.location='{{ route('sale.create') }}'">
          <i class="fa-solid fa-plus me-2"></i> Add Sale
        </button>
      </div>
      </div>
    <div class="d-flex justify-content-between align-items-center bg-white mb-2 px-3 py-2 rounded">

  <div class="d-flex align-items-center gap-2">

    <span class="small fw-semibold">Filter By:</span>

    <!-- Period Filter -->
    <div class="d-flex rounded-pill filter-pill">

      <div class="filter-left">
        <select id="salesPeriodSelect" class="filter-select">
          <option value="all">All Sales Invoices</option>
          <option value="this_month" {{ ($period ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
          <option value="last_month" {{ ($period ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
          <option value="this_quarter" {{ ($period ?? '') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
          <option value="this_year" {{ ($period ?? '') === 'this_year' ? 'selected' : '' }}>This Year</option>
          <option value="custom" {{ ($period ?? '') === 'custom' ? 'selected' : '' }}>Custom</option>
        </select>
      </div>

      <div class="filter-right">
        <div id="customDateRange" class="d-flex align-items-center gap-1" style="display:none;">
          <input id="salesCustomFrom" type="date" class="date-input" value="{{ $from ?? '' }}" />
          <span>to</span>
          <input id="salesCustomTo" type="date" class="date-input" value="{{ $to ?? '' }}" />
        </div>
      </div>

    </div>

    <!-- Firm Filter -->
    <div class="filter-pill small-pill">
      <select id="salesFirmSelect" class="filter-select text-center">
        <option value="">All Firms</option>
        @foreach(\App\Models\Party::orderBy('name')->get(['name']) as $party)
          @if(!empty($party->name))
            <option value="{{ $party->name }}" {{ ($firm ?? '') === $party->name ? 'selected' : '' }}>{{ $party->name }}</option>
          @endif
        @endforeach
      </select>
    </div>

    <a href="{{ route('sale.index', ['overdue' => $showOverdueOnly ? null : 1]) }}"
       class="btn btn-sm {{ $showOverdueOnly ? 'btn-danger' : 'btn-outline-danger' }}">
      {{ $showOverdueOnly ? 'Showing Overdue Only' : 'Overdue List' }}
    </a>

  </div>

</div>
      <div class="bg-white mb-2 px-4 py-3 rounded">
        @php
          $salesCollection = $sales->getCollection();
          $totalSalesAmount = $salesCollection->sum(fn($sale) => (float) ($sale->grand_total ?? $sale->total_amount ?? 0));
          $convertedAmount = $salesCollection->sum(fn($sale) => (float) ($sale->received_amount ?? 0));
          $openAmount = $salesCollection->sum(fn($sale) => (float) ($sale->balance ?? 0));
        @endphp
        <div class="border rounded p-1" style="width: 25rem; height: 8rem; background-color: #FCF8FF;">
          <div class="w-100 d-flex">
            <div class="w-50 mt-2">
              <p class="ps-3 text-secondary m-0">Total Sales Amount</p>
              <p class="ps-3 h4">Rs {{ number_format($totalSalesAmount, 2) }}</p>
            </div>
            <div class="w-50 mt-2 d-flex align-items-end justify-content-center flex-column">
              <div class="col-5 h-50 rounded-pill d-flex justify-content-center align-item-center me-4"
                style="background-color: #DEF7EE;">
                <p class="text-success pt-1">100% <i class="bi bi-arrow-up-right"></i></p>
              </div>
              <span class="me-4 pe-1 mt-1 text-secondary" style="font-size: 10px;">vs last month</span>
            </div>
          </div>
          <div class="w-100 d-flex mt-3">
            <p class="ps-3 pe-3 text-secondary" style="border-right:1px solid rgb(45, 44, 44);">Converted : <span
                class="fw-bold text-dark">Rs {{ number_format($convertedAmount, 2) }}</span></p>
            <p class="ps-3 text-secondary">Open : <span class="fw-bold text-dark">Rs {{ number_format($openAmount, 2) }}</span></p>

          </div>
        </div>
      </div>

 <div class="card border-0 shadow-sm">
  <div class="card-body p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="fw-semibold mb-0">Transactions</h6>
      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search"></i></span>
          <input id="searchTransactionsInput"
                 type="search"
                 class="form-control form-control-sm border-start-0"
                 placeholder="Search..."
                 name="sale_transactions_search"
                 autocomplete="off"
                 autocapitalize="off"
                 autocorrect="off"
                 spellcheck="false">
        </div>
        <button id="exportExcel" class="btn btn-sm btn-outline-secondary" type="button" title="Export to Excel"><i class="fa-solid fa-file-excel"></i></button>
        <button id="printTable"
                class="btn btn-sm btn-outline-secondary"
                type="button"
                title="Print"
                data-report-preview-url="{{ route('sale.report-preview') }}"
                data-report-pdf-url="{{ route('sale.report-pdf') }}"
                data-report-email-url="{{ route('sale.report-email') }}">
          <i class="fa-solid fa-print"></i>
        </button>
        <button id="signalBtn" class="btn btn-sm btn-outline-secondary" type="button" title="Signal"><i class="fa-solid fa-signal"></i></button>
      </div>
    </div>

    <div class="table-responsive table-wrapper">
      <table class="table align-middle custom-table mb-0 txn-table">
        <thead>
          <tr>
            <th>
              <div class="column-filter-header">
                <span>Date</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Date">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="0">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="0">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Invoice no</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Invoice">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="1">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="1">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Party Name</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Party">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="2">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="2">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Transaction</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Transaction">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="3">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="3">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Payment Type</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Payment">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="4">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="4">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Amount</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Amount">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="5">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="5">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Received Amount</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Received">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="6">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="6">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Balance</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Balance">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="7">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="7">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Due Date</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Due Date">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="8">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="8">Apply</button>
                </div>
              </div>
            </th>
            <th>
              <div class="column-filter-header">
                <span>Status</span>
                <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
              </div>
              <div class="column-filter-dropdown">
                <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Status">
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="9">Clear</button>
                  <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="9">Apply</button>
                </div>
              </div>
            </th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          @forelse($sales as $sale)
          <tr data-edit-url="{{ route('sale.edit', $sale) }}">
            <td>{{ \Carbon\Carbon::parse($sale->invoice_date ?? $sale->created_at)->format('d/m/Y') }}</td>
            <td>{{ $sale->bill_number ?? $sale->id }}</td>
            <td>{{ $sale->party?->name ?? 'No Party Selected' }}</td>
            <td>Sale</td>

            <td>
              {{ $sale->payments->pluck('payment_type')->filter()->unique()->join(', ') ?: '-' }}
            </td>

            <td>Rs {{ number_format(($sale->grand_total ?? $sale->total_amount ?? 0), 2) }}</td>
            <td>Rs {{ number_format($sale->received_amount ?? 0) }}</td>
            <td>Rs {{ number_format($sale->balance ?? 0) }}</td>
            <td>
              @if($sale->due_date)
                @php $isOverdue = ($sale->balance ?? 0) > 0 && \Carbon\Carbon::parse($sale->due_date)->isPast(); @endphp
                <span class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                  {{ \Carbon\Carbon::parse($sale->due_date)->format('d/m/Y') }}
                </span>
              @else
                -
              @endif
            </td>

            <td>
              @php
                $status = strtolower($sale->status ?? 'unpaid');
              @endphp

              <span class="status-text
                {{ $status == 'paid' ? 'text-success' : '' }}
                {{ $status == 'partial' ? 'text-warning' : '' }}
                {{ $status == 'unpaid' ? 'text-danger' : '' }}">

                {{ ucfirst($status) }}
              </span>
            </td>

            <td class="text-muted">
              <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-print row-action-print" title="Print" style="cursor:pointer;"></i>
                <i class="fa-solid fa-share row-action-share" title="Share" style="cursor:pointer;"></i>
                <div class="dropdown sale-action-menu"
                     data-sale-id="{{ $sale->id }}"
                     data-party-name="{{ $sale->party?->name ?? 'No Party Selected' }}"
                     data-party-email="{{ $sale->party?->email ?? '' }}"
                     data-balance="{{ (float) ($sale->balance ?? 0) }}"
                     data-edit-url="{{ route('sale.edit', $sale) }}"
                     data-preview-url="{{ route('sale.invoice-preview', $sale) }}"
                     data-pdf-url="{{ route('sale.invoice-pdf', ['sale' => $sale->id, 'download' => 1]) }}"
                     data-print-url="{{ route('sale.invoice-preview', ['sale' => $sale->id, 'print' => 1]) }}"
                     data-delivery-preview-url="{{ route('sale.invoice-preview', ['sale' => $sale->id, 'doc' => 'delivery_challan']) }}"
                     data-payment-history-url="{{ route('sale.payment-history', $sale) }}"
                     data-bank-history-url="{{ route('sale.bank-history', $sale) }}"
                     data-convert-return-url="{{ route('sale-return.create', ['sale_id' => $sale->id]) }}"
                     data-cancel-url="{{ route('sale.cancel', $sale) }}"
                     data-is-cancelled="{{ strtolower((string) ($sale->status ?? '')) === 'cancelled' ? '1' : '0' }}"
                     data-email-url="{{ route('sale.invoice-email', $sale) }}"
                     data-sale-number="{{ $sale->bill_number ?? $sale->id }}">
                  <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" data-action="view">View / Edit</a></li>
                    <li><a class="dropdown-item" href="#" data-action="convert-return">Convert to Return</a></li>
                    <li><a class="dropdown-item" href="#" data-action="preview-delivery">Preview Delivery Challan</a></li>
                    <li><a class="dropdown-item" href="#" data-action="payment-history">Payment History</a></li>
                    <li><a class="dropdown-item" href="#" data-action="cancel">Cancel Invoice</a></li>
                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                    <li><a class="dropdown-item" href="#" data-action="duplicate">Duplicate</a></li>
                    <li><a class="dropdown-item" href="#" data-action="preview">Preview</a></li>
                    <li><a class="dropdown-item" href="#" data-action="pdf">Open PDF</a></li>
                    <li><a class="dropdown-item" href="#" data-action="print">Print</a></li>
                    <li><a class="dropdown-item" href="#" data-action="history">View History</a></li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="11" class="text-center text-muted py-4">
              No sales yet.
            </td>
          </tr>
          @endforelse
        </tbody>

      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Showing {{ $sales->firstItem() ?: 0 }} to {{ $sales->lastItem() ?: 0 }} of {{ $sales->total() }} results
      </div>
      <div>
        {{ $sales->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>
    </div>
  </main>

  <!-- ═══════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════ -->
  <div class="modal fade" id="salePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="salePreviewModalTitle">Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0" style="min-height:70vh;">
          <iframe id="salePreviewFrame" title="Preview" style="width:100%; min-height:70vh; border:0;"></iframe>
        </div>
        <div class="modal-footer justify-content-center gap-2 flex-wrap">
          <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="salePreviewOpenPdf">Open PDF</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="salePreviewPrint">Print</button>
          <button type="button" class="btn btn-outline-success rounded-pill px-4" id="salePreviewSavePdf">Save PDF</button>
          <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="salePreviewEmailPdf">Email PDF</button>
          <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Close</button>
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
  ])

  <div class="modal fade" id="saleHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="saleHistoryModalTitle">History</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="saleHistoryModalBody">
          <div class="text-muted">Loading...</div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="salePaymentHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="salePaymentHistoryModalTitle">Payment History</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="salePaymentHistoryModalBody">
          <div class="text-muted">Loading...</div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="salePasscodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Enter Passcode</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Enter the 4-digit transaction passcode to continue.</p>
          <form autocomplete="off" onsubmit="return false;">
            <input type="text" autocomplete="username" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;opacity:0;">
            <input type="password" autocomplete="current-password" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;opacity:0;">
            <input type="password"
                   inputmode="numeric"
                   maxlength="4"
                   class="form-control text-center fs-5"
                   id="salePasscodeInput"
                   name="sale_transaction_passcode"
                   placeholder="••••"
                   autocomplete="one-time-code"
                   autocapitalize="off"
                   autocorrect="off"
                   spellcheck="false">
          </form>
          <div class="text-danger small mt-2 d-none" id="salePasscodeError"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="salePasscodeConfirm">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="salePrintOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Select Print Options</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="date" checked> <span class="form-check-label">Date</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="item_details" checked> <span class="form-check-label">Item Details</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="invoice_no" checked> <span class="form-check-label">Invoice No.</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="description" checked> <span class="form-check-label">Description</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="party_name" checked> <span class="form-check-label">Party Name</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="payment_status" checked> <span class="form-check-label">Payment Status</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="total" checked> <span class="form-check-label">Total</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="order_number" checked> <span class="form-check-label">Order Number</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="payment_type" checked> <span class="form-check-label">Payment Type</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="party_phone" checked> <span class="form-check-label">Party's Phone No.</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="received_paid" checked> <span class="form-check-label">Received/Paid</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="payment_breakup" checked> <span class="form-check-label">Show Payment Breakup</span></label></div>
            <div class="col-6"><label class="form-check"><input class="form-check-input sale-print-opt" type="checkbox" value="balance_due" checked> <span class="form-check-label">Balance Due</span></label></div>
          </div>
        </div>
        <div class="modal-footer justify-content-end">
          <button type="button" class="btn btn-danger rounded-pill px-4" id="salePrintOptionsApply">Get Print</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/sale.js') }}"></script>
  <script src="{{ asset('js/document-email-preview.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const agarriButton = document.getElementById('agarriListBtn');
      const periodSelect = document.getElementById('salesPeriodSelect');
      const firmSelect = document.getElementById('salesFirmSelect');
      const customFrom = document.getElementById('salesCustomFrom');
      const customTo = document.getElementById('salesCustomTo');
      const searchInput = document.getElementById('searchTransactionsInput');
      const table = document.querySelector('.txn-table');
      const tbody = table?.querySelector('tbody');
      const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
      const columnFilters = {};
      const saleInvoiceEmailComposer = window.DocumentEmailPreview?.init({
        name: 'sale-invoice-email-preview',
        previewModalId: 'salePreviewModal',
        previewFrameId: 'salePreviewFrame',
        emailModalId: 'documentEmailModal',
        emailToId: 'documentEmailTo',
        emailSubjectId: 'documentEmailSubject',
        emailMessageId: 'documentEmailMessage',
        viewPdfBtnId: 'documentEmailViewPdfBtn',
        sendBtnId: 'documentEmailSendBtn',
        openButtonId: 'salePreviewEmailPdf',
        toastId: 'documentEmailToast',
        defaultSubject: (context) => `${context.documentLabel || 'Sale Invoice'} PDF${context.saleNumber ? ' - ' + context.saleNumber : ''}`,
        defaultMessage: (context) => {
          const pdfLink = context.pdfUrl || context.previewUrl || '';
          const documentName = (context.documentLabel || 'sale invoice').toString().toLowerCase();
          return `Dear ${context.partyName || 'Sir'},\n\nPlease find the ${documentName} PDF attached below.\n${pdfLink ? 'PDF Link: ' + pdfLink + '\n' : ''}\nThank you for doing business with us.\nThanks and regards.`;
        },
      });

      function normalizeText(value) {
        return String(value || '').trim().toLowerCase();
      }

      function applySalesTableFilters() {
        if (!tbody) {
          return;
        }

        const keyword = normalizeText(searchInput?.value || '');
        let visibleCount = 0;

        rows.forEach((row) => {
          const cells = Array.from(row.children);
          const rowText = normalizeText(row.textContent);

          const matchesSearch = !keyword || rowText.includes(keyword);
          const matchesColumns = Object.entries(columnFilters).every(([index, value]) => {
            if (!value) {
              return true;
            }

            const cell = cells[Number(index)];
            return normalizeText(cell?.textContent || '').includes(value);
          });

          const shouldShow = matchesSearch && matchesColumns;
          row.style.display = shouldShow ? '' : 'none';

          if (shouldShow) {
            visibleCount += 1;
          }
        });

        let emptyStateRow = tbody.querySelector('.sales-table-empty-state');
        if (!visibleCount) {
          if (!emptyStateRow) {
            emptyStateRow = document.createElement('tr');
            emptyStateRow.className = 'sales-table-empty-state';
            emptyStateRow.innerHTML = '<td colspan="11" class="text-center text-muted py-4">No matching sales found.</td>';
            tbody.appendChild(emptyStateRow);
          }
        } else if (emptyStateRow) {
          emptyStateRow.remove();
        }
      }

      document.querySelectorAll('.filter-icon-btn').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          const dropdown = this.closest('.column-filter-header')?.nextElementSibling;
          if (!dropdown) {
            return;
          }

          document.querySelectorAll('.column-filter-dropdown.show').forEach((openDropdown) => {
            if (openDropdown !== dropdown) {
              openDropdown.classList.remove('show');
            }
          });

          dropdown.classList.toggle('show');
        });
      });

      document.querySelectorAll('.column-filter-apply').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          const columnIndex = this.dataset.columnIndex;
          const dropdown = this.closest('.column-filter-dropdown');
          const input = dropdown?.querySelector('.column-filter-input');

          columnFilters[columnIndex] = normalizeText(input?.value || '');
          dropdown?.classList.remove('show');
          applySalesTableFilters();
        });
      });

      document.querySelectorAll('.column-filter-input').forEach((input) => {
        input.addEventListener('input', function () {
          const dropdown = this.closest('.column-filter-dropdown');
          const applyButton = dropdown?.querySelector('.column-filter-apply');
          const columnIndex = applyButton?.dataset.columnIndex;

          if (columnIndex === undefined) {
            return;
          }

          const normalizedValue = normalizeText(this.value || '');

          if (normalizedValue) {
            columnFilters[columnIndex] = normalizedValue;
          } else {
            delete columnFilters[columnIndex];
          }

          applySalesTableFilters();
        });
      });

      document.querySelectorAll('.column-filter-clear').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          const columnIndex = this.dataset.columnIndex;
          const dropdown = this.closest('.column-filter-dropdown');
          const input = dropdown?.querySelector('.column-filter-input');

          if (input) {
            input.value = '';
          }

          delete columnFilters[columnIndex];
          dropdown?.classList.remove('show');
          applySalesTableFilters();
        });
      });

      searchInput?.addEventListener('input', applySalesTableFilters);

      tbody?.addEventListener('dblclick', function (event) {
        const actionMenu = event.target.closest('.sale-action-menu');
        if (actionMenu || event.target.closest('.dropdown-menu') || event.target.closest('button') || event.target.closest('a')) {
          return;
        }

        const row = event.target.closest('tr[data-edit-url]');
        if (!row) {
          return;
        }

        const editUrl = row.dataset.editUrl;
        if (editUrl) {
          if (window.guardSaleEditAction) {
            window.guardSaleEditAction(editUrl);
          } else {
            window.location.href = editUrl;
          }
        }
      });

      document.addEventListener('click', function (event) {
        if (!event.target.closest('.column-filter-dropdown') && !event.target.closest('.filter-icon-btn')) {
          document.querySelectorAll('.column-filter-dropdown.show').forEach((dropdown) => {
            dropdown.classList.remove('show');
          });
        }
      });

      function buildDateRangeFromPeriod(period) {
        const now = new Date();
        let start = null;
        let end = null;

        if (period === 'this_month') {
          start = new Date(now.getFullYear(), now.getMonth(), 1);
          end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        } else if (period === 'last_month') {
          start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
          end = new Date(now.getFullYear(), now.getMonth(), 0);
        } else if (period === 'this_quarter') {
          const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3;
          start = new Date(now.getFullYear(), quarterStartMonth, 1);
          end = new Date(now.getFullYear(), quarterStartMonth + 3, 0);
        } else if (period === 'this_year') {
          start = new Date(now.getFullYear(), 0, 1);
          end = new Date(now.getFullYear(), 11, 31);
        }

        return { start, end };
      }

      function toIsoDate(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
      }

      agarriButton?.addEventListener('click', function () {
        const params = new URLSearchParams();
        const selectedPeriod = periodSelect?.value || 'all';
        const selectedFirm = (firmSelect?.value || '').trim();

        if (selectedPeriod === 'custom') {
          if (customFrom?.value) params.set('from', customFrom.value);
          if (customTo?.value) params.set('to', customTo.value);
        } else {
          const range = buildDateRangeFromPeriod(selectedPeriod);
          if (range.start && range.end) {
            params.set('from', toIsoDate(range.start));
            params.set('to', toIsoDate(range.end));
          }
        }

        if (selectedFirm) {
          params.set('party_name', selectedFirm);
        }

        window.open(`{{ route('reports.unreceived-invoices.pdf') }}?${params.toString()}`, '_blank');
      });
    });
  </script>
<script>
  /* ═══════════════════════════════════════
      COLUMN RESIZE — Sales Table
     ═══════════════════════════════════════ */
  (function () {
    var isResizing = false, startX = 0, startW = 0, thEl = null;

    function initResizeHandles() {
      var table = document.querySelector('.txn-table');
      if (!table) return;

      var ths = table.querySelectorAll('thead th');
      ths.forEach(function (th) {
        if (th.querySelector('.col-resize-handle-sales')) return;
        th.style.position = 'relative';
        var handle = document.createElement('div');
        handle.className = 'col-resize-handle-sales';
        handle.style.cssText = 'position:absolute;right:0;top:0;bottom:0;width:5px;cursor:col-resize;z-index:10;';
        th.appendChild(handle);
      });
    }

    document.addEventListener('mousedown', function (e) {
      if (!e.target.classList.contains('col-resize-handle-sales')) return;
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
      var newW = Math.max(60, startW + (e.clientX - startX));
      thEl.style.minWidth = newW + 'px';
      thEl.style.width = newW + 'px';
    });

    document.addEventListener('mouseup', function () {
      if (!isResizing) return;
      isResizing = false;
      thEl = null;
      document.body.style.cursor = '';
      document.body.style.userSelect = '';
    });

    document.addEventListener('DOMContentLoaded', initResizeHandles);
  })();
</script>



</body>

</html>
