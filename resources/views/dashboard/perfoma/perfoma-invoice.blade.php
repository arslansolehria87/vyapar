<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Vyapar - Proforma Invoice</title>
  <meta name="description" content="Create and manage proforma invoices in Vyapar.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="{{ asset('css/styles.css') }}" rel="stylesheet">
<link href="{{ asset('css/sale.css') }}" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
  .custom-table thead th {
    font-size: 13px; color: #6c757d; font-weight: 500;
    border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 5;
    background-color: #fafafa; white-space: nowrap;
  }
  .custom-table tbody td {
    font-size: 14px; padding: 14px 10px;
    border-bottom: 1px solid #f1f1f1; white-space: nowrap;
  }
  .custom-table tbody tr:hover { background-color: #fafafa; }
  .custom-table tbody tr.row-open-flash,
  .custom-table tbody tr.row-open-flash td {
    background-color: #e8f1ff !important;
  }
  .custom-table th, .custom-table td { border-right: 1px solid #f1f1f1; }
  .custom-table th:last-child, .custom-table td:last-child { border-right: none; }
  .table-wrapper {
    overflow-x: hidden; overflow-y: visible;
    max-height: none; border: 1px solid #eef2f7; border-radius: 12px;
    padding-bottom: 0;
  }
  .table-wrapper .dropdown-menu { z-index: 2000; max-height: 48vh; overflow-y: auto; }
  .proforma-header-cell { min-width: 0; position: relative; }
  .proforma-header-label { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
  .proforma-filter-trigger {
    border: 0; background: transparent; color: #adb5bd; padding: 0; line-height: 1; font-size: 12px; cursor: pointer;
  }
  .proforma-filter-trigger:hover,
  .proforma-filter-trigger.active { color: #0d6efd; }
  .proforma-filter-flyout {
    position: fixed; z-index: 1060; width: 210px; padding: 8px; background: #fff;
    border: 1px solid #dbe3ee; border-radius: 10px; box-shadow: 0 10px 24px rgba(15,23,42,0.12);
  }
  .proforma-filter-flyout input { width: 100%; }
  .proforma-filter-flyout .form-control { font-size: 13px; }
  .proforma-filter-flyout .filter-clear { margin-top: 6px; }
  .action-menu-btn {
    border: 0;
    background: transparent;
    color: #64748b;
    padding: 0.35rem 0.5rem;
  }
  .action-menu-btn::after { display: none; }
  .action-menu-cell {
    overflow: visible !important;
    position: relative;
    text-align: center;
    white-space: nowrap;
  }
  .proforma-action-menu .dropdown-menu {
    min-width: 180px;
    padding: 0.45rem 0;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    z-index: 1090;
  }
  .proforma-action-menu .dropdown-item {
    padding: 0.6rem 1rem;
    font-size: 14px;
    color: #1f2937;
    text-decoration: none;
  }
  .proforma-action-menu .dropdown-item:hover {
    background: #e0f2fe;
    color: #0f172a;
    font-weight: 700;
  }
  .proforma-action-menu .dropdown-divider {
    margin: 0.35rem 0;
  }
  .proforma-filter-row {
    border-top: 3px solid #d3dee8;
    border-bottom: 1px solid #d6e1eb;
    background: #fff;
    min-height: 102px;
    padding-left: 14px !important;
    padding-right: 14px !important;
  }
  .proforma-filter-label {
    color: #1f2937;
    font-size: 20px;
    font-weight: 600;
  }
  .proforma-filter-form {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
  }
  .proforma-filter-pill {
    display: inline-flex;
    align-items: center;
    min-height: 54px;
    padding: 0 20px;
    border: 0;
    border-radius: 999px;
    background: #e5f3ff;
    color: #17233c;
    font-size: 18px;
    font-weight: 600;
    white-space: nowrap;
  }
  .proforma-period-pill {
    width: 134px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: 1px solid #c4d4e1;
    padding-left: 19px;
    padding-right: 16px;
  }
  .proforma-date-pill {
    width: 312px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    gap: 12px;
    margin-left: -18px;
    padding-left: 23px;
    justify-content: flex-start;
  }
  .proforma-filter-select {
    appearance: none;
    -webkit-appearance: none;
    border: 0;
    outline: 0;
    background: transparent;
    color: inherit;
    font: inherit;
    cursor: pointer;
    padding-right: 24px;
  }
  .proforma-select-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .proforma-select-wrap::after {
    content: "\f107";
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 13px;
    pointer-events: none;
  }
  .proforma-date-picker {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .proforma-date-trigger {
    border: 0;
    background: #e5f3ff;
    color: inherit;
    font: inherit;
    cursor: pointer;
  }
  .proforma-firm-pill {
    width: 142px;
    justify-content: center;
  }
  .proforma-date-popover {
    position: fixed;
    z-index: 1080;
    width: 300px;
    background: #fff;
    border: 1px solid #d9dde7;
    border-radius: 9px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.24);
    color: #3e4254;
    overflow: hidden;
  }
  .proforma-date-popover.d-none {
    display: none;
  }
  .proforma-date-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    padding: 12px 16px 14px;
    border-bottom: 1px solid #e4e7ef;
  }
  .proforma-date-field label {
    display: block;
    margin-bottom: 5px;
    color: #737994;
    font-size: 14px;
    font-weight: 500;
  }
  .proforma-date-field input {
    width: 100%;
    height: 38px;
    border: 1px solid #dce2ec;
    border-radius: 8px;
    color: #202944;
    font-size: 15px;
    font-weight: 600;
    padding: 0 14px;
    outline: none;
  }
  .proforma-date-field input::placeholder {
    color: #959ab5;
    opacity: 1;
  }
  .proforma-calendar {
    padding: 13px 16px 15px;
  }
  .proforma-calendar-nav {
    display: grid;
    grid-template-columns: 32px 1fr 32px;
    align-items: center;
    margin-bottom: 10px;
  }
  .proforma-calendar-nav button {
    border: 0;
    background: transparent;
    color: #0876e6;
    font-size: 18px;
    line-height: 1;
    padding: 4px;
  }
  .proforma-calendar-month {
    justify-self: center;
    min-width: 128px;
    height: 30px;
    border: 1px solid #dce2ec;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #34384b;
    font-size: 18px;
    font-weight: 700;
  }
  .proforma-calendar-weekdays,
  .proforma-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
  }
  .proforma-calendar-weekdays span {
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3d4153;
    font-size: 16px;
    font-weight: 700;
  }
  .proforma-calendar-day {
    height: 31px;
    border: 0;
    background: transparent;
    color: #3e4254;
    font-size: 16px;
    font-weight: 500;
  }
  .proforma-calendar-day.muted {
    color: #b2b6c8;
  }
  .proforma-calendar-day.in-range {
    background: #e1f0ff;
  }
  .proforma-calendar-day.selected span,
  .proforma-calendar-day.today span {
    width: 29px;
    height: 29px;
    border-radius: 999px;
    background: #0876e6;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .proforma-calendar-day.today:not(.selected) span {
    border-radius: 0;
  }



  @media (max-width: 991px) {
    .table-wrapper { max-height: none; border-radius: 8px; }
    .custom-table thead th { font-size: 11px; padding: 8px 6px; }
    .custom-table tbody td { font-size: 12px; padding: 10px 6px; }
    .proforma-filter-row { min-height: auto; }
    .proforma-filter-label { font-size: 16px; }
    .proforma-filter-pill { min-height: 44px; font-size: 14px; }
  }

  @media (max-width: 575px) {
    .table-wrapper { border-radius: 6px; }
    .custom-table thead th { font-size: 10px; padding: 6px 4px; }
    .custom-table tbody td { font-size: 11px; padding: 8px 4px; }
  }

</style>
  <style>
    #proformaTable {
      width: 100% !important;
      table-layout: fixed;
    }

    #proformaTable th,
    #proformaTable td {
      white-space: normal;
      word-break: break-word;
    }

    #proformaTable th:last-child,
    #proformaTable td:last-child,
    #proformaTable td:nth-last-child(2) {
      white-space: nowrap;
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
  </script>
</head>

<body data-page="proforma-invoice">
  <main class="main-content" id="mainContent">
    <div class="container-fluid col-12">
      <div class="d-flex justify-content-between align-items-center bg-light mb-2 p-4">
        <div class="dropdown">
          <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="h4">Proforma Invoice</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('sale.index') }}">Sale Invoice</a></li>
            <li><a class="dropdown-item" href="{{ route('sale.estimate') }}">Estimate / Quotation</a></li>
            <li><a class="dropdown-item" href="{{ route('sale-return') }}">Sale Return / Cr. Note</a></li>
          </ul>
        </div>
        <button class="btn rounded-pill" style="background-color: #D4112E;" onclick="window.location='{{ route('proforma-invoice.create') }}'">
          <span class="text-light">+ Add Proforma</span>
        </button>
      </div>

      @php
        $selectedDateRange = $dateRange ?? 'custom';
        $customFromDate = $fromDate ?: now()->startOfMonth()->toDateString();
        $customToDate = $toDate ?: now()->endOfMonth()->toDateString();
      @endphp
      <div class="proforma-filter-row d-flex align-items-center mb-2 px-4 py-3">
        <form method="GET" action="{{ route('proforma-invoice') }}" class="proforma-filter-form">
          <input type="hidden" name="search" value="{{ $search ?? '' }}">
          <input type="hidden" name="from_date" id="proformaFromDate" value="{{ $customFromDate }}">
          <input type="hidden" name="to_date" id="proformaToDate" value="{{ $customToDate }}">

          <span class="proforma-filter-label">Filter by :</span>

          <div class="proforma-filter-pill proforma-period-pill">
            <span class="proforma-select-wrap">
              <select name="date_range" class="proforma-filter-select" onchange="this.form.submit()">
                <option value="all" {{ $selectedDateRange === 'all' ? 'selected' : '' }}>All Proformas</option>
                <option value="this_month" {{ $selectedDateRange === 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ $selectedDateRange === 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="this_quarter" {{ $selectedDateRange === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="this_year" {{ $selectedDateRange === 'this_year' ? 'selected' : '' }}>This Year</option>
                <option value="custom" {{ $selectedDateRange === 'custom' ? 'selected' : '' }}>Custom</option>
              </select>
            </span>
          </div>

          <button type="button" class="proforma-filter-pill proforma-date-pill proforma-date-trigger" id="proformaDateTrigger">
            <i class="fa-regular fa-calendar-days"></i>
            <span id="proformaDateRangeText">{{ $dateRangeLabel ?? 'All dates' }}</span>
          </button>

          <div class="proforma-date-popover d-none" id="proformaDatePopover" aria-hidden="true">
            <div class="proforma-date-fields">
              <div class="proforma-date-field">
                <label for="proformaFromDisplay">From</label>
                <input type="text" id="proformaFromDisplay" placeholder="From Date" readonly>
              </div>
              <div class="proforma-date-field">
                <label for="proformaToDisplay">To</label>
                <input type="text" id="proformaToDisplay" placeholder="To Date" readonly>
              </div>
            </div>
            <div class="proforma-calendar">
              <div class="proforma-calendar-nav">
                <button type="button" id="proformaCalendarPrev" aria-label="Previous month"><i class="fa-solid fa-caret-left"></i></button>
                <div class="proforma-calendar-month" id="proformaCalendarMonth"></div>
                <button type="button" id="proformaCalendarNext" aria-label="Next month"><i class="fa-solid fa-caret-right"></i></button>
              </div>
              <div class="proforma-calendar-weekdays">
                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
              </div>
              <div class="proforma-calendar-grid" id="proformaCalendarGrid"></div>
            </div>
          </div>

          <div class="proforma-filter-pill proforma-firm-pill">
            <span class="proforma-select-wrap">
              <select name="party_id" class="proforma-filter-select" onchange="this.form.submit()">
                <option value="all" {{ ($partyId ?? 'all') === 'all' ? 'selected' : '' }}>All Firms</option>
                @foreach($partyOptions ?? [] as $party)
                  <option value="{{ $party->id }}" {{ ($partyId ?? 'all') == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                @endforeach
              </select>
            </span>
          </div>
        </form>
      </div>

      <div class="bg-light mb-2 px-4 py-3 rounded">
        <div class="border rounded p-1" style="width: 25rem; height: 8rem; background-color: #FCF8FF;">
          <div class="w-100 d-flex">
            <div class="w-50 mt-2">
              <p class="ps-3 text-secondary m-0">Total Quotations</p>
              <p class="ps-3 h4">Rs {{ number_format(($allProformas ?? $proformas)->sum('grand_total'), 2) }}</p>
            </div>
            <div class="w-50 mt-2 d-flex align-items-end justify-content-center flex-column">
              <div class="col-5 h-50 rounded-pill d-flex justify-content-center align-item-center me-4" style="background-color: #DEF7EE;">
                <p class="text-success pt-1">{{ ($allProformas ?? $proformas)->count() > 0 ? round((($allProformas ?? $proformas)->where('status', 'converted')->count() / ($allProformas ?? $proformas)->count()) * 100) : 0 }}% <i class="bi bi-arrow-up-right"></i></p>
              </div>
              <span class="me-4 pe-1 mt-1 text-secondary" style="font-size: 10px;">conversion rate</span>
            </div>
          </div>
          <div class="w-100 d-flex mt-3">
            <p class="ps-3 pe-3 text-secondary" style="border-right:1px solid rgb(45, 44, 44);">Converted : <span class="fw-bold text-dark">Rs {{ number_format(($allProformas ?? $proformas)->where('status', 'converted')->sum('grand_total'), 2) }}</span></p>
            <p class="ps-3 text-secondary">Open : <span class="fw-bold text-dark">Rs {{ number_format(($allProformas ?? $proformas)->where('status', 'open')->sum('grand_total'), 2) }}</span></p>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <p class="fw-bold mb-2">Transactions</p>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-end align-items-start gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle search-toggle-btn" id="proformaSearchToggle" title="Search">
                  <i class="fas fa-search"></i>
                </button>
                <div class="{{ !empty($search) ? 'd-flex' : 'd-none' }} gap-2 align-items-center" id="proformaSearchForm">
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    placeholder="Search by Ref No. or Party Name..."
                    id="proformaSearchInput"
                    value="{{ $search ?? '' }}"
                    style="border-radius: 20px; min-width: 260px;"
                  >
                </div>
              </div>
            </div>
          </div>

          <div class="table-wrapper">
  <table id="proformaTable" class="table align-middle custom-table mb-0">
              <thead>
                <tr>
                  <th class="proforma-header-cell">
                    <div class="proforma-header-label"><span>Date</span><button type="button" class="proforma-filter-trigger" data-column="0" onclick="openProformaFilter(event, 0)"><i class="fa-solid fa-filter"></i></button></div>
                  </th>
                  <th class="proforma-header-cell">
                    <div class="proforma-header-label"><span>Reference no</span><button type="button" class="proforma-filter-trigger" data-column="1" onclick="openProformaFilter(event, 1)"><i class="fa-solid fa-filter"></i></button></div>
                  </th>
                  <th class="proforma-header-cell">
                    <div class="proforma-header-label"><span>Party Name</span><button type="button" class="proforma-filter-trigger" data-column="2" onclick="openProformaFilter(event, 2)"><i class="fa-solid fa-filter"></i></button></div>
                  </th>
                  <th class="proforma-header-cell text-end">
                    <div class="proforma-header-label"><span>Amount</span><button type="button" class="proforma-filter-trigger" data-column="3" onclick="openProformaFilter(event, 3)"><i class="fa-solid fa-filter"></i></button></div>
                  </th>
                  <th class="proforma-header-cell text-end">
                    <div class="proforma-header-label"><span>Balance</span><button type="button" class="proforma-filter-trigger" data-column="4" onclick="openProformaFilter(event, 4)"><i class="fa-solid fa-filter"></i></button></div>
                  </th>
                  <th class="proforma-header-cell">
                    <div class="proforma-header-label"><span>Status</span><button type="button" class="proforma-filter-trigger" data-column="5" onclick="openProformaFilter(event, 5)"><i class="fa-solid fa-filter"></i></button></div>
                  </th>
                  <th class="text-center" style="width: 220px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($proformas as $proforma)
                  @php
                    $isConverted = $proforma->status === 'converted';
                    $convertedSaleNumber = $convertedSales[$proforma->id] ?? null;
                    $convertedSaleOrderNumber = $convertedSaleOrders[$proforma->id] ?? null;
                  @endphp
                  <tr data-edit-url="{{ route('proforma-invoice.edit', $proforma->id) }}">
                    <td>{{ optional($proforma->invoice_date)->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $proforma->bill_number ?? '-' }}</td>
                    <td>{{ $proforma->display_party_name }}</td>
                    <td data-order="{{ number_format($proforma->items->sum('amount'), 2, '.', '') }}">Rs {{ number_format($proforma->items->sum('amount'), 2) }}</td>
                    <td data-order="{{ number_format($proforma->balance ?? $proforma->grand_total ?? 0, 2, '.', '') }}">Rs {{ number_format($proforma->balance ?? $proforma->grand_total ?? 0, 2) }}</td>
                    <td>
                      <span class="badge {{ $isConverted ? 'text-primary bg-primary-subtle border border-primary-subtle' : 'bg-warning text-dark' }}">
                        @if($isConverted)
                          Converted
                        @else
                          {{ ucfirst($proforma->status ?? 'open') }}
                        @endif
                      </span>
                    </td>
                    <td class="action-menu-cell text-center">
                      <div class="dropdown d-inline me-2">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" {{ $isConverted ? 'disabled' : '' }}>
                          Convert
                        </button>
                        <ul class="dropdown-menu">
                          <li>
                            <a class="dropdown-item {{ $isConverted ? 'disabled' : '' }}" href="{{ $isConverted ? '#' : route('proforma-invoice.convert-to-sale', $proforma->id) }}">
                              <i class="fas fa-file-invoice me-2"></i>Convert to Sale
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item {{ $isConverted ? 'disabled' : '' }}" href="{{ $isConverted ? '#' : route('proforma-invoice.convert-to-sale-order', $proforma->id) }}">
                              <i class="fas fa-clipboard-list me-2"></i>Convert to Sale Order
                            </a>
                          </li>
                        </ul>
                      </div>

                      <div class="dropdown d-inline proforma-action-menu"
                           data-preview-url="{{ route('proforma-invoice.preview', $proforma->id) }}"
                           data-pdf-url="{{ route('proforma-invoice.pdf', $proforma->id) }}"
                           data-print-url="{{ route('proforma-invoice.print', $proforma->id) }}"
                           data-party-email="{{ $proforma->party?->email ?? '' }}"
                           data-party-name="{{ $proforma->party?->name ?? '' }}"
                           data-sale-number="{{ $proforma->bill_number ?? $proforma->id }}"
                           data-email-url="{{ route('sale.invoice-email', $proforma) }}"
                           data-duplicate-url="{{ route('proforma-invoice.duplicate', $proforma->id) }}">
                        <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                          <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li><a class="dropdown-item" href="#" onclick="transactionPasscodeNavigate('{{ route('proforma-invoice.edit', $proforma->id) }}'); return false;">View/Edit</a></li>
                          <li><a class="dropdown-item" href="#" onclick="previewProforma(this); return false;">Preview</a></li>
                          <li><a class="dropdown-item" href="#" onclick="printProforma(this); return false;">Print</a></li>
                          <li><a class="dropdown-item" href="#" onclick="duplicateProforma(this); return false;">Duplicate</a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">No proforma invoices yet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="proformaPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1180px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0" style="min-height:72vh;">
          <iframe id="proformaPreviewFrame" title="Proforma Preview" style="width:100%; min-height:72vh; border:0;"></iframe>
        </div>
          <div class="modal-footer justify-content-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="proformaPreviewOpenPdf">Open PDF</button>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="proformaPreviewPrint">Print</button>
            <button type="button" class="btn btn-outline-success rounded-pill px-4" id="proformaPreviewSavePdf">Save PDF</button>
            <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="proformaPreviewEmailPdf">Send Email</button>
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
    'title' => 'Send Email',
    'subjectValue' => 'Your Vyapar PDF',
    'messageValue' => "Dear Sir,\nPlease find the attached document below.\nThank you for doing business with us.\nThanks and regards.",
    'helperText' => 'The invoice PDF will be attached automatically.',
  ])

  @include('dashboard.partials.transaction-passcode-guard')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/document-email-preview.js') }}"></script>
  <script>
    function deleteProforma(url) {
      if (!confirm('Are you sure you want to delete this proforma invoice?')) {
        return;
      }

      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
          'Accept': 'application/json',
        },
      })
        .then(async (response) => {
          const data = await response.json();
          if (!response.ok) {
            throw new Error(data.message || 'Delete failed');
          }
          window.location.reload();
        })
        .catch((error) => {
          alert(error.message || 'Unable to delete proforma invoice.');
        });
    }
  </script>
  <script>
    (function () {
      let proformaState = {
        table: null,
        activeFilterColumn: null,
      };
      const proformaColumnFilters = {};
      const proformaFilterFlyout = document.createElement('div');
      proformaFilterFlyout.id = 'proformaFilterFlyout';
      proformaFilterFlyout.className = 'proforma-filter-flyout d-none';
      proformaFilterFlyout.setAttribute('aria-hidden', 'true');
      proformaFilterFlyout.innerHTML = '<input id="proformaFilterInput" type="text" class="form-control form-control-sm" placeholder="Filter"><button type="button" class="btn btn-link btn-sm p-0 filter-clear" id="proformaFilterClear">Clear</button>';
      document.body.appendChild(proformaFilterFlyout);

      const proformaFilterInput = proformaFilterFlyout.querySelector('#proformaFilterInput');
      const proformaFilterClear = proformaFilterFlyout.querySelector('#proformaFilterClear');
      const proformaPreviewModalEl = document.getElementById('proformaPreviewModal');
      const proformaPreviewModal = proformaPreviewModalEl ? bootstrap.Modal.getOrCreateInstance(proformaPreviewModalEl) : null;
      const proformaPreviewFrame = document.getElementById('proformaPreviewFrame');
      const proformaPreviewOpenPdf = document.getElementById('proformaPreviewOpenPdf');
      const proformaPreviewPrint = document.getElementById('proformaPreviewPrint');
      const proformaPreviewSavePdf = document.getElementById('proformaPreviewSavePdf');
      const proformaSearchToggle = document.getElementById('proformaSearchToggle');
      const proformaSearchForm = document.getElementById('proformaSearchForm');
      const proformaSearchInput = document.getElementById('proformaSearchInput');
      const proformaFilterForm = document.querySelector('.proforma-filter-form');
      const proformaDateRangeSelect = proformaFilterForm?.querySelector('select[name="date_range"]');
      const proformaFromDate = document.getElementById('proformaFromDate');
      const proformaToDate = document.getElementById('proformaToDate');
      const proformaDateTrigger = document.getElementById('proformaDateTrigger');
      const proformaDatePopover = document.getElementById('proformaDatePopover');
      const proformaFromDisplay = document.getElementById('proformaFromDisplay');
      const proformaToDisplay = document.getElementById('proformaToDisplay');
      const proformaCalendarMonth = document.getElementById('proformaCalendarMonth');
      const proformaCalendarGrid = document.getElementById('proformaCalendarGrid');
      const proformaCalendarPrev = document.getElementById('proformaCalendarPrev');
      const proformaCalendarNext = document.getElementById('proformaCalendarNext');
      const proformaMonthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
      let proformaSelectedFrom = parseProformaDate(proformaFromDate?.value);
      let proformaSelectedTo = parseProformaDate(proformaToDate?.value);
      let proformaCalendarView = proformaSelectedFrom
        ? new Date(proformaSelectedFrom.getFullYear(), proformaSelectedFrom.getMonth(), 1)
        : new Date();

      function parseProformaDate(value) {
        const parts = String(value || '').split('-').map(Number);
        if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
        return new Date(parts[0], parts[1] - 1, parts[2]);
      }

      function formatProformaDateInput(date) {
        if (!date) return '';
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
      }

      function formatProformaDateDisplay(date) {
        if (!date) return '';
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        return `${dd}/${mm}/${date.getFullYear()}`;
      }

      function sameProformaDay(a, b) {
        return !!a && !!b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
      }

      function isProformaInRange(date) {
        if (!proformaSelectedFrom || !proformaSelectedTo) return false;
        const time = date.getTime();
        return time >= proformaSelectedFrom.getTime() && time <= proformaSelectedTo.getTime();
      }

      function syncProformaDateFields(showValues = false) {
        if (proformaFromDate) proformaFromDate.value = formatProformaDateInput(proformaSelectedFrom);
        if (proformaToDate) proformaToDate.value = formatProformaDateInput(proformaSelectedTo);
        if (proformaFromDisplay) proformaFromDisplay.value = showValues ? formatProformaDateDisplay(proformaSelectedFrom) : '';
        if (proformaToDisplay) proformaToDisplay.value = showValues ? formatProformaDateDisplay(proformaSelectedTo) : '';
      }

      function renderProformaCalendar() {
        if (!proformaCalendarMonth || !proformaCalendarGrid) return;
        const year = proformaCalendarView.getFullYear();
        const month = proformaCalendarView.getMonth();
        const today = new Date();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const gridStart = new Date(year, month, 1 - firstDay.getDay());
        const totalCells = firstDay.getDay() + lastDay.getDate() > 35 ? 42 : 35;

        proformaCalendarMonth.textContent = `${proformaMonthNames[month]} ${year}`;
        proformaCalendarGrid.innerHTML = '';

        for (let index = 0; index < totalCells; index += 1) {
          const date = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + index);
          const button = document.createElement('button');
          const day = document.createElement('span');
          button.type = 'button';
          button.className = 'proforma-calendar-day';
          if (date.getMonth() !== month) button.classList.add('muted');
          if (isProformaInRange(date)) button.classList.add('in-range');
          if (sameProformaDay(date, proformaSelectedFrom) || sameProformaDay(date, proformaSelectedTo)) button.classList.add('selected');
          if (sameProformaDay(date, today)) button.classList.add('today');
          day.textContent = date.getDate();
          button.appendChild(day);
          button.addEventListener('click', function () {
            const picked = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            if (!proformaSelectedFrom || proformaSelectedTo) {
              proformaSelectedFrom = picked;
              proformaSelectedTo = null;
              syncProformaDateFields(true);
              renderProformaCalendar();
              return;
            }
            if (picked.getTime() < proformaSelectedFrom.getTime()) {
              proformaSelectedFrom = picked;
              proformaSelectedTo = null;
              syncProformaDateFields(true);
              renderProformaCalendar();
              return;
            }
            proformaSelectedTo = picked;
            if (proformaDateRangeSelect) proformaDateRangeSelect.value = 'custom';
            syncProformaDateFields(true);
            proformaFilterForm?.submit();
          });
          proformaCalendarGrid.appendChild(button);
        }
      }

      function positionProformaDatePopover() {
        if (!proformaDateTrigger || !proformaDatePopover) return;
        const rect = proformaDateTrigger.getBoundingClientRect();
        const left = Math.max(8, Math.min(rect.left - 84, window.innerWidth - proformaDatePopover.offsetWidth - 8));
        const belowTop = rect.bottom + 4;
        const aboveTop = rect.top - proformaDatePopover.offsetHeight - 4;
        const top = belowTop + proformaDatePopover.offsetHeight > window.innerHeight - 8 && aboveTop > 8
          ? aboveTop
          : belowTop;
        proformaDatePopover.style.left = `${left}px`;
        proformaDatePopover.style.top = `${top}px`;
      }

      function openProformaDatePopover() {
        if (!proformaDatePopover) return;
        renderProformaCalendar();
        proformaDatePopover.classList.remove('d-none');
        proformaDatePopover.setAttribute('aria-hidden', 'false');
        positionProformaDatePopover();
      }

      function closeProformaDatePopover() {
        proformaDatePopover?.classList.add('d-none');
        proformaDatePopover?.setAttribute('aria-hidden', 'true');
      }

      syncProformaDateFields(false);

      proformaDateTrigger?.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (proformaDatePopover?.classList.contains('d-none')) {
          openProformaDatePopover();
        } else {
          closeProformaDatePopover();
        }
      });

      proformaCalendarPrev?.addEventListener('click', function () {
        proformaCalendarView = new Date(proformaCalendarView.getFullYear(), proformaCalendarView.getMonth() - 1, 1);
        renderProformaCalendar();
      });

      proformaCalendarNext?.addEventListener('click', function () {
        proformaCalendarView = new Date(proformaCalendarView.getFullYear(), proformaCalendarView.getMonth() + 1, 1);
        renderProformaCalendar();
      });

      window.addEventListener('resize', positionProformaDatePopover);
      window.addEventListener('scroll', positionProformaDatePopover, true);

      function closeProformaFilters() {
        document.querySelectorAll('.proforma-filter-trigger.active').forEach((btn) => btn.classList.remove('active'));
        proformaState.activeFilterColumn = null;
        proformaFilterFlyout.classList.add('d-none');
        proformaFilterFlyout.setAttribute('aria-hidden', 'true');
      }

      window.openProformaFilter = function (event, columnIndex) {
        if (event) {
          event.preventDefault();
          event.stopPropagation();
          if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
        }
        const trigger = event?.currentTarget || document.querySelector(`.proforma-filter-trigger[data-column="${columnIndex}"]`);
        if (!trigger) return;
        const rect = trigger.getBoundingClientRect();
        closeProformaFilters();
        trigger.classList.add('active');
        proformaState.activeFilterColumn = Number(columnIndex);
        proformaFilterInput.value = proformaColumnFilters[proformaState.activeFilterColumn] || '';
        proformaFilterFlyout.style.left = `${Math.max(12, Math.min(rect.left, window.innerWidth - 234))}px`;
        proformaFilterFlyout.style.top = `${rect.bottom + 8}px`;
        proformaFilterFlyout.classList.remove('d-none');
        proformaFilterFlyout.setAttribute('aria-hidden', 'false');
        proformaFilterInput.focus();
        proformaFilterInput.select();
      };

      proformaFilterInput?.addEventListener('input', function () {
        if (proformaState.activeFilterColumn === null || proformaState.activeFilterColumn === undefined) return;
        proformaColumnFilters[proformaState.activeFilterColumn] = String(this.value || '');
        proformaState.table?.draw();
      });

      proformaFilterClear?.addEventListener('click', function () {
        if (proformaState.activeFilterColumn === null || proformaState.activeFilterColumn === undefined) return;
        proformaColumnFilters[proformaState.activeFilterColumn] = '';
        proformaFilterInput.value = '';
        proformaState.table?.draw();
      });

      function resolveAction(trigger) {
        const menu = trigger?.closest('.proforma-action-menu');
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

      window.previewProforma = function (trigger) {
        const { previewUrl, pdfUrl, printUrl, partyEmail, partyName, saleNumber, emailUrl } = resolveAction(trigger);
        if (!proformaPreviewModal || !proformaPreviewFrame) {
          window.open(previewUrl || pdfUrl || printUrl, '_blank');
          return;
        }
        proformaPreviewFrame.src = previewUrl || pdfUrl || printUrl;
        proformaPreviewFrame.dataset.previewUrl = previewUrl || '';
        proformaPreviewFrame.dataset.pdfUrl = pdfUrl || '';
        proformaPreviewFrame.dataset.printUrl = printUrl || '';
        proformaPreviewFrame.dataset.partyEmail = partyEmail || '';
        proformaPreviewFrame.dataset.partyName = partyName || '';
        proformaPreviewFrame.dataset.saleNumber = saleNumber || '';
        proformaPreviewFrame.dataset.emailUrl = emailUrl || '';
        proformaPreviewFrame.dataset.documentLabel = 'Proforma Invoice';
        proformaPreviewFrame.dataset.downloadUrl = (() => {
          if (!pdfUrl) return previewUrl || '';
          try {
            const download = new URL(pdfUrl, window.location.origin);
            download.searchParams.set('download', '1');
            return download.toString();
          } catch (error) {
            return pdfUrl + (String(pdfUrl).includes('?') ? '&' : '?') + 'download=1';
          }
        })();
        proformaPreviewModal.show();
      };

      window.openProformaPdf = function (trigger) {
        const { pdfUrl, previewUrl } = resolveAction(trigger);
        window.open(pdfUrl || previewUrl, '_blank');
      };

      window.printProforma = function (trigger) {
        const { printUrl, pdfUrl, previewUrl } = resolveAction(trigger);
        window.open(printUrl || pdfUrl || previewUrl, '_blank');
      };

      window.duplicateProforma = function (trigger) {
        const { duplicateUrl } = resolveAction(trigger);
        if (duplicateUrl) {
          window.location.href = duplicateUrl;
        }
      };

      const proformaEmailComposer = window.DocumentEmailPreview?.init({
        name: 'proforma-email-preview',
        previewModalId: 'proformaPreviewModal',
        previewFrameId: 'proformaPreviewFrame',
        emailModalId: 'documentEmailModal',
        emailToId: 'documentEmailTo',
        emailSubjectId: 'documentEmailSubject',
        emailMessageId: 'documentEmailMessage',
        viewPdfBtnId: 'documentEmailViewPdfBtn',
        sendBtnId: 'documentEmailSendBtn',
        openButtonId: 'proformaPreviewEmailPdf',
        toastId: 'documentEmailToast',
        defaultSubject: (context) => `Proforma Invoice${context.saleNumber ? ' - ' + context.saleNumber : ''}`,
        defaultMessage: (context) => {
          const pdfLink = context.pdfUrl || context.previewUrl || '';
          return `Dear ${context.partyName || 'Sir'},\n\nPlease find the proforma invoice attached below.\n${pdfLink ? 'PDF Link: ' + pdfLink + '\n' : ''}\nThank you for doing business with us.\nThanks and regards.`;
        },
      });

      window.openProformaEmail = function (trigger) {
        const composer = proformaEmailComposer || window.DocumentEmailPreview?.get('proforma-email-preview');
        composer?.open(trigger);
      };

      if (proformaPreviewOpenPdf) {
        proformaPreviewOpenPdf.addEventListener('click', function () {
          const url = proformaPreviewFrame?.dataset?.pdfUrl || proformaPreviewFrame?.src || '';
          if (url) window.open(url, '_blank');
        });
      }

      if (proformaPreviewPrint) {
        proformaPreviewPrint.addEventListener('click', function () {
          const url = proformaPreviewFrame?.dataset?.printUrl || proformaPreviewFrame?.dataset?.pdfUrl || proformaPreviewFrame?.src || '';
          if (url) window.open(url, '_blank');
        });
      }

      if (proformaPreviewSavePdf) {
        proformaPreviewSavePdf.addEventListener('click', function () {
          const url = proformaPreviewFrame?.dataset?.downloadUrl || proformaPreviewFrame?.dataset?.pdfUrl || proformaPreviewFrame?.dataset?.previewUrl || proformaPreviewFrame?.src || '';
          if (!url) return;
          const a = document.createElement('a');
          a.href = url;
          a.download = '';
          a.target = '_blank';
          a.rel = 'noopener';
          document.body.appendChild(a);
          a.click();
          a.remove();
        });
      }

      function openMailClient(subject, body) {
        const mailtoUrl = 'mailto:?subject=' + encodeURIComponent(subject || 'Invoice Preview') +
          '&body=' + encodeURIComponent(body || '');
        try {
          const opened = window.open(mailtoUrl, '_self');
          if (opened !== null) {
            return;
          }
        } catch (error) {
          // fall through to anchor fallback
        }
        const link = document.createElement('a');
        link.href = mailtoUrl;
        link.target = '_self';
        link.rel = 'noopener';
        document.body.appendChild(link);
        link.click();
        link.remove();
      }

      if (proformaPreviewEmailPdf) {
        proformaPreviewEmailPdf.addEventListener('click', function () {
          const composer = proformaEmailComposer || window.DocumentEmailPreview?.get('proforma-email-preview');
          composer?.open();
        });
      }

      proformaSearchToggle?.addEventListener('click', function () {
        if (!proformaSearchForm) return;
        const isHidden = proformaSearchForm.classList.contains('d-none');
        if (isHidden) {
          proformaSearchForm.classList.remove('d-none');
          proformaSearchInput?.focus();
          proformaSearchInput?.select();
        } else {
          proformaSearchForm.classList.add('d-none');
        }
      });

      proformaSearchInput?.addEventListener('input', function () {
        proformaState.table?.search(this.value || '').draw();
      });

      $.fn.dataTable.ext.search.push(function (settings, data) {
        if (!settings.nTable || settings.nTable.id !== 'proformaTable') {
          return true;
        }

        const matchesColumns = Object.entries(proformaColumnFilters).every(([index, value]) => {
          const normalized = String(value || '').trim().toLowerCase();
          if (!normalized) return true;
          return String(data[Number(index)] || '').trim().toLowerCase().includes(normalized);
        });

        return matchesColumns;
      });

      $(document).ready(function () {
      proformaState.table = $('#proformaTable').DataTable({
          pageLength: 10,
          lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
          order: [[0, 'desc']],
          autoWidth: false,
          scrollX: false,
          responsive: false,
          columnDefs: [{ orderable: false, searchable: false, targets: [6] }],
          dom: '<"row mb-2 align-items-center"<"col-md-6"l><"col-md-6 text-end">>rt<"row mt-3 align-items-center"<"col-md-6"i><"col-md-6"p>>',
          language: {
            lengthMenu: 'Show _MENU_ proformas',
            info: 'Showing _START_ to _END_ of _TOTAL_ proformas',
            emptyTable: 'No proforma invoices found'
          }
        });

      if (proformaSearchInput?.value) {
        proformaState.table.search(proformaSearchInput.value).draw();
      }

      $(document).on('dblclick', '#proformaTable tbody tr[data-edit-url]', function (event) {
        if ($(event.target).closest('.dropdown, a, button, input, select, textarea, label, .proforma-filter-flyout, .proforma-filter-trigger').length) {
          return;
        }

        const editUrl = this.dataset.editUrl;
        if (editUrl) {
          const row = this;
          row.classList.add('row-open-flash');
          setTimeout(() => {
            row.classList.remove('row-open-flash');
            if (window.transactionPasscodeNavigate) {
              window.transactionPasscodeNavigate(editUrl);
            } else {
              window.location.href = editUrl;
            }
          }, 120);
        }
      });

      proformaState.table.draw();

        document.querySelectorAll('.table-wrapper .dropdown').forEach(function (dropdown) {
          dropdown.addEventListener('show.bs.dropdown', function () {
            const wrapper = dropdown.closest('.table-wrapper');
            const scrollBody = dropdown.closest('.dataTables_scrollBody');
            if (wrapper) {
              wrapper.dataset.prevOverflowY = wrapper.style.overflowY || '';
              wrapper.style.overflowY = 'visible';
            }
            if (scrollBody) {
              scrollBody.style.overflow = 'visible';
            }
          });

          dropdown.addEventListener('hidden.bs.dropdown', function () {
            const wrapper = dropdown.closest('.table-wrapper');
            const scrollBody = dropdown.closest('.dataTables_scrollBody');
            if (wrapper) {
              wrapper.style.overflowY = wrapper.dataset.prevOverflowY || 'auto';
              delete wrapper.dataset.prevOverflowY;
            }
            if (scrollBody) {
              scrollBody.style.overflow = 'auto';
            }
          });
        });

        document.addEventListener('click', function (event) {
          if (!event.target.closest('.proforma-filter-flyout') && !event.target.closest('.proforma-filter-trigger')) {
            closeProformaFilters();
          }
          if (!event.target.closest('.proforma-date-popover') && !event.target.closest('.proforma-date-trigger')) {
            closeProformaDatePopover();
          }
        });
      });
    })();
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
