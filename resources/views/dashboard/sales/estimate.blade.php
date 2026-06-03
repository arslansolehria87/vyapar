@extends('layouts.app')

@section('title', 'Vyapar — Estimate / Quotation')
@section('description', 'Create professional estimates and quotations for your customers in Vyapar.')
@section('page', 'sale-estimate')

@section('content')
<style>
  .custom-table thead th {
    font-size: 13px; color: #6c757d; font-weight: 500;
    border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 5;
    background-color: #fafafa; white-space: nowrap; position: relative;
  }
  .custom-table {
    width: 100% !important;
    table-layout: auto;
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
    overflow-x: auto; overflow-y: auto;
    max-height: 68vh; border: 1px solid #eef2f7; border-radius: 12px;
    padding-bottom: 72px;
  }
  .table-wrapper .dropdown-menu {
    z-index: 2000;
    max-height: 48vh;
    overflow-y: auto;
  }
  .dataTables_scrollBody {
    overflow: auto !important;
  }
  .dataTables_scrollBody.dropdown-overflow-visible {
    overflow: visible !important;
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
    <div class="container-fluid col-12">
      @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
      @endif

      <div class="d-flex justify-content-between align-items-center bg-light mb-2 p-4">
        <div>
         <div class="dropdown">
          <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="h4"> Estimates / Quotations</span>
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
        <button class="btn rounded-pill" style="background-color: #D4112E;" onclick="window.location='{{ route('estimates.create') }}'"><span class="text-light">+ Add
            Estimate</span></button>
      </div>
      <div class="d-flex justify-content-between align-items-center bg-light mb-2 px-3 py-2 rounded">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="small fw-semibold">Filter By:</span>

          <div class="d-flex rounded-pill filter-pill">
            <div class="filter-left">
              <select id="estimatePeriodSelect" class="filter-select">
                <option value="all">All Estimates</option>
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="this_quarter">This Quarter</option>
                <option value="this_year">This Year</option>
                <option value="custom">Custom</option>
              </select>
            </div>

            <div class="filter-right">
              <div id="estimateDateRangeDisplay" class="small text-nowrap"></div>
              <div id="estimateCustomDateRange" class="d-flex align-items-center gap-1" style="display:none;">
                <input id="estimateCustomFrom" type="date" class="date-input" />
                <span>to</span>
                <input id="estimateCustomTo" type="date" class="date-input" />
              </div>
            </div>
          </div>

          <div class="filter-pill small-pill">
            <select id="estimateFirmSelect" class="filter-select text-center">
              <option value="">All Firms</option>
              @foreach((($allEstimates ?? $estimates)->map(fn($estimate) => $estimate->party?->name)->filter()->unique()->values()) as $firm)
                <option value="{{ $firm }}">{{ $firm }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="bg-light mb-2 px-4 py-3 rounded">
        <div class="border rounded p-1" style="width: 25rem; height: 8rem; background-color: #FCF8FF;">
          <div class="w-100 d-flex">
            <div class="w-50 mt-2">
              <p class="ps-3 text-secondary m-0">Total Quotations</p>
              <p class="ps-3 h4">Rs {{ number_format(($allEstimates ?? $estimates)->sum('grand_total'), 2) }}</p>
            </div>
            <div class="w-50 mt-2 d-flex align-items-end justify-content-center flex-column">
              <div class="col-5 h-50 rounded-pill d-flex justify-content-center align-item-center me-4"
                style="background-color: #DEF7EE;">
                <p class="text-success pt-1">{{ ($allEstimates ?? $estimates)->count() > 0 ? round((($allEstimates ?? $estimates)->where('status', 'converted')->count() / ($allEstimates ?? $estimates)->count()) * 100) : 0 }}% <i class="bi bi-arrow-up-right "></i></p>
              </div>
              <span class="me-4 pe-1 mt-1 text-secondary" style="font-size: 10px;">conversion rate</span>
            </div>
          </div>
          <div class="w-100 d-flex mt-3">
            <p class="ps-3 pe-3 text-secondary" style="border-right:1px solid rgb(45, 44, 44);">Converted : <span
                class="fw-bold text-dark">Rs {{ number_format(($allEstimates ?? $estimates)->where('status', 'converted')->sum('grand_total'), 2) }}</span></p>
            <p class="ps-3 text-secondary">Open : <span class="fw-bold text-dark">Rs {{ number_format(($allEstimates ?? $estimates)->where('status', 'open')->sum('grand_total'), 2) }}</span></p>

          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <p class="fw-bold mb-2">Transactions</p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
              <div class="estimate-table-tools">
                <button type="button" class="estimate-search-toggle" id="estimateSearchToggle" title="Search">
                  <i class="fas fa-search"></i>
                </button>
                <div class="estimate-search-box" id="estimateSearchBox">
                  <i class="fas fa-search text-muted"></i>
                  <input type="text" id="estimateSearchInput" placeholder="Search bill no. or party name">
                </div>
              </div>
            </div>
          </div>

         <div class="table-wrapper">
  <table id="estimatesTable" class="table align-middle custom-table mb-0">
              <thead>
                <tr class="text-uppercase small text-secondary">
                  <th class="py-3 estimate-header-cell">
                    <div class="estimate-header-label">
                      <span>Date</span>
                      <button type="button" class="estimate-filter-trigger" data-column="0" aria-label="Filter Date" onmousedown="event.stopPropagation();" onclick="openEstimateFilter(event, 0)">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                    </div>
                  </th>
                  <th class="py-3 estimate-header-cell">
                    <div class="estimate-header-label">
                      <span>Reference No.</span>
                      <button type="button" class="estimate-filter-trigger" data-column="1" aria-label="Filter Reference No." onmousedown="event.stopPropagation();" onclick="openEstimateFilter(event, 1)">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                    </div>
                  </th>
                  <th class="py-3 estimate-header-cell">
                    <div class="estimate-header-label">
                      <span>Party Name</span>
                      <button type="button" class="estimate-filter-trigger" data-column="2" aria-label="Filter Party Name" onmousedown="event.stopPropagation();" onclick="openEstimateFilter(event, 2)">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                    </div>
                  </th>
                  <th class="py-3 estimate-header-cell text-end">
                    <div class="estimate-header-label">
                      <span>Amount</span>
                      <button type="button" class="estimate-filter-trigger" data-column="3" aria-label="Filter Amount" onmousedown="event.stopPropagation();" onclick="openEstimateFilter(event, 3)">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                    </div>
                  </th>
                  <th class="py-3 estimate-header-cell text-end">
                    <div class="estimate-header-label">
                      <span>Balance</span>
                      <button type="button" class="estimate-filter-trigger" data-column="4" aria-label="Filter Balance" onmousedown="event.stopPropagation();" onclick="openEstimateFilter(event, 4)">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                    </div>
                  </th>
                  <th class="py-3 estimate-header-cell">
                    <div class="estimate-header-label">
                      <span>Status</span>
                      <button type="button" class="estimate-filter-trigger" data-column="5" aria-label="Filter Status" onmousedown="event.stopPropagation();" onclick="openEstimateFilter(event, 5)">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                    </div>
                  </th>
                  <th class="py-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($estimates ?? [] as $estimate)
                @php
                  $isConverted = $estimate->status === 'converted';
                  $convertedInvoiceNumber = $convertedInvoices[$estimate->id] ?? null;
                  $statusLabel = $isConverted
                      ? 'Converted' . ($convertedInvoiceNumber ? ' (Invoice #' . $convertedInvoiceNumber . ')' : '')
                      : ucfirst($estimate->status);
                @endphp
                <tr data-estimate-id="{{ $estimate->id }}"
                    data-edit-url="{{ route('estimates.edit', $estimate->id) }}"
                    data-date="{{ $estimate->invoice_date ? $estimate->invoice_date->format('d/m/Y') : '-' }}"
                    data-party="{{ $estimate->display_party_name }}"
                    data-ref="{{ $estimate->bill_number ?? '-' }}"
                    data-amount="{{ number_format($estimate->items->sum('amount'), 2, '.', '') }}"
                    data-balance="{{ number_format($estimate->balance ?? $estimate->grand_total ?? 0, 2, '.', '') }}"
                    data-status="{{ $statusLabel }}">
                  <td data-order="{{ $estimate->invoice_date ? $estimate->invoice_date->format('Y-m-d') : '' }}">{{ $estimate->invoice_date ? $estimate->invoice_date->format('d/m/Y') : '-' }}</td>
                  <td data-order="{{ $estimate->bill_number ?? '' }}">{{ $estimate->bill_number ?? '-' }}</td>
                  <td>{{ $estimate->display_party_name }}</td>
                  <td class="text-end" data-order="{{ number_format($estimate->items->sum('amount'), 2, '.', '') }}">Rs {{ number_format($estimate->items->sum('amount'), 2) }}</td>
                  <td class="text-end" data-order="{{ number_format($estimate->balance ?? $estimate->grand_total ?? 0, 2, '.', '') }}">Rs {{ number_format($estimate->balance ?? $estimate->grand_total ?? 0, 2) }}</td>
                  <td>
                    <span class="badge {{ $isConverted ? 'text-primary bg-primary-subtle border border-primary-subtle' : ($estimate->status === 'open' ? 'bg-success' : 'bg-warning text-dark') }}">
                      {{ $statusLabel }}
                    </span>
                  </td>
                  <td>
                    <div class="dropdown d-inline me-2">
                      <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="white-space: nowrap;" {{ $isConverted ? 'disabled' : '' }}>
                        Convert
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-item {{ $isConverted ? 'disabled' : '' }}" href="{{ $isConverted ? '#' : route('estimates.convert-to-sale', $estimate->id) }}">
                            <i class="fas fa-file-invoice me-2"></i>Estimate to Sale
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item {{ $isConverted ? 'disabled' : '' }}" href="{{ $isConverted ? '#' : route('estimates.convert-to-sale-order', $estimate->id) }}">
                            <i class="fas fa-clipboard-list me-2"></i>Estimate to Sale Order
                          </a>
                        </li>
                      </ul>
                    </div>
                    <div class="dropdown d-inline estimate-action-menu"
                         data-estimate-id="{{ $estimate->id }}"
                         data-preview-url="{{ route('sale.invoice-preview', $estimate) }}"
                         data-pdf-url="{{ route('sale.invoice-pdf', $estimate) }}"
                         data-print-url="{{ route('sale.invoice-preview', ['sale' => $estimate->id, 'print' => 1]) }}"
                         data-duplicate-url="{{ route('estimates.create', ['duplicate_sale_id' => $estimate->id]) }}">
                      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="return transactionPasscodeNavigate('{{ route('estimates.edit', $estimate->id) }}');"><i class="fas fa-edit me-2"></i>View/Edit</a></li>
                        <li><a class="dropdown-item" href="#" onclick="duplicateEstimate(this); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                        <li><a class="dropdown-item" href="#" onclick="printEstimate(this); return false;"><i class="fas fa-print me-2"></i>Print</a></li>
                        <li><a class="dropdown-item" href="#" onclick="previewEstimate(this); return false;"><i class="fas fa-file-alt me-2"></i>Preview</a></li>
                        <li><a class="dropdown-item" href="{{ route('estimates.pdf', $estimate->id) }}" target="_blank" rel="noopener"><i class="fas fa-file-pdf me-2"></i>Open PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="return transactionPasscodeExecute('deleteEstimate','{{ route('estimates.destroy', $estimate->id) }}');"><i class="fas fa-trash me-2"></i>Delete</a></li>
                      </ul>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    No estimates yet. Click "New Estimate" to create one.
                  </td>
                </tr>
                @endforelse
              </tbody>
          </table>
          </div>

          <div id="estimateFilterFlyout" class="estimate-filter-flyout d-none" aria-hidden="true">
            <input id="estimateFilterInput" type="text" class="form-control form-control-sm" placeholder="Filter">
            <button type="button" class="btn btn-link btn-sm p-0 filter-clear" id="estimateFilterClear">Clear</button>
          </div>

        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="estimatePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="estimatePreviewTitle">Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0" style="min-height:72vh;">
          <iframe id="estimatePreviewFrame" title="Estimate Preview" style="width:100%; min-height:72vh; border:0;"></iframe>
        </div>
        <div class="modal-footer justify-content-center gap-2 flex-wrap">
          <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="estimatePreviewOpenPdf">Open PDF</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="estimatePreviewPrint">Print</button>
          <button type="button" class="btn btn-outline-success rounded-pill px-4" id="estimatePreviewSavePdf">Save PDF</button>
          <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="estimatePreviewEmailPdf">Email PDF</button>
          <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  @include('dashboard.partials.transaction-passcode-guard')

@endsection

@push('scripts')
<style>
.filter-pill {
  background-color: #e4f2ff;
  border-radius: 999px;
  min-height: 40px;
  display: flex;
  align-items: center;
}

.filter-left {
  border-right: 1px solid #ccc;
  padding: 0 10px;
  min-height: 40px;
  display: flex;
  align-items: center;
}

.filter-right {
  padding: 0 12px;
  min-height: 40px;
  display: flex;
  align-items: center;
}

.filter-select {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  padding: 0;
  margin: 0;
}

.small-pill {
  padding: 0 12px;
  min-width: 130px;
}

  .date-input {
    border: none;
    background: transparent;
    font-size: 12px;
    width: 120px;
    outline: none;
  }
  .estimate-table-tools {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .estimate-search-toggle {
    width: 40px;
    height: 40px;
    border: 1px solid #dbe3ee;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #6c757d;
    cursor: pointer;
  }
  .estimate-search-box {
    display: none;
    align-items: center;
    gap: 8px;
    min-width: 280px;
    border: 1px solid #dbe3ee;
    border-radius: 12px;
    background: #fff;
    padding: 8px 12px;
  }
  .estimate-search-box.open {
    display: inline-flex;
  }
  .estimate-search-box input {
    border: 0;
    outline: 0;
    width: 100%;
    font-size: 14px;
  }
  .estimate-header-cell {
    min-width: 120px;
  }
  .estimate-header-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .estimate-filter-trigger {
    border: 0;
    background: transparent;
    color: #adb5bd;
    padding: 0;
    line-height: 1;
    font-size: 12px;
    cursor: pointer;
  }
  .estimate-filter-trigger.active,
  .estimate-filter-trigger:hover {
    color: #0d6efd;
  }
  .estimate-filter-flyout {
    position: fixed;
    z-index: 1060;
    width: 210px;
    padding: 8px;
    background: #fff;
    border: 1px solid #dbe3ee;
    border-radius: 10px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
  }
  .estimate-filter-flyout .form-control {
    font-size: 13px;
  }
  .estimate-filter-flyout .filter-clear {
    margin-top: 6px;
  }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
  const estimateState = {
    table: null,
    periodFilter: $('#estimatePeriodSelect').val() || 'all',
    firmFilter: $('#estimateFirmSelect').val() || '',
    customFrom: $('#estimateCustomFrom').val() || '',
    customTo: $('#estimateCustomTo').val() || '',
    searchValue: '',
    activeFilterColumn: null,
  };

  const estimateColumnFilters = { 0: '', 1: '', 2: '', 3: '', 4: '', 5: '' };
  const estimateFilterFlyout = document.getElementById('estimateFilterFlyout');
  const estimateFilterInput = document.getElementById('estimateFilterInput');
  const estimateFilterClear = document.getElementById('estimateFilterClear');
  const estimateSearchToggle = document.getElementById('estimateSearchToggle');
  const estimateSearchBox = document.getElementById('estimateSearchBox');
  const estimateSearchInput = document.getElementById('estimateSearchInput');
  const estimatePreviewModalEl = document.getElementById('estimatePreviewModal');
  const estimatePreviewModal = estimatePreviewModalEl ? bootstrap.Modal.getOrCreateInstance(estimatePreviewModalEl) : null;
  const estimatePreviewFrame = document.getElementById('estimatePreviewFrame');
  const estimatePreviewTitle = document.getElementById('estimatePreviewTitle');
  const estimatePreviewOpenPdf = document.getElementById('estimatePreviewOpenPdf');
  const estimatePreviewPrint = document.getElementById('estimatePreviewPrint');
  const estimatePreviewSavePdf = document.getElementById('estimatePreviewSavePdf');
  const estimatePreviewEmailPdf = document.getElementById('estimatePreviewEmailPdf');
  let estimatePreviewUrls = { preview: '', pdf: '', print: '' };

  function parseDateDMY(value) {
    const parts = String(value || '').split('/');
    if (parts.length !== 3) return null;
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const year = parseInt(parts[2], 10);
    if ([day, month, year].some(Number.isNaN)) return null;
    return new Date(year, month, day);
  }

  function getPeriodRange(period) {
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

  function formatDisplayDate(date) {
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const yyyy = date.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
  }

  function updateEstimateRangeDisplay(from, to) {
    const display = $('#estimateDateRangeDisplay');
    if (!display.length) return;
    if (!from || !to) {
      display.text('');
      return;
    }
    display.text(`${formatDisplayDate(from)} To ${formatDisplayDate(to)}`);
  }

  function closeEstimateFlyout() {
    estimateState.activeFilterColumn = null;
    estimateFilterFlyout?.classList.add('d-none');
    if (estimateFilterFlyout) estimateFilterFlyout.setAttribute('aria-hidden', 'true');
    document.querySelectorAll('.estimate-filter-trigger.active').forEach((btn) => btn.classList.remove('active'));
  }

  function openEstimateFilter(event, columnIndex) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
      if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
    }

    const trigger = event?.currentTarget || document.querySelector(`.estimate-filter-trigger[data-column="${columnIndex}"]`);
    if (!trigger || !estimateFilterFlyout || !estimateFilterInput) return;

    const rect = trigger.getBoundingClientRect();
    closeEstimateFlyout();

    estimateState.activeFilterColumn = Number(columnIndex);
    estimateFilterInput.value = estimateColumnFilters[estimateState.activeFilterColumn] || '';
    estimateFilterFlyout.style.left = `${Math.max(12, Math.min(rect.left, window.innerWidth - 234))}px`;
    estimateFilterFlyout.style.top = `${rect.bottom + 8}px`;
    estimateFilterFlyout.classList.remove('d-none');
    estimateFilterFlyout.setAttribute('aria-hidden', 'false');
    trigger.classList.add('active');
    estimateFilterInput.focus();
    estimateFilterInput.select();
  }

  function syncEstimateFilterUi() {
    if (estimateState.periodFilter === 'custom') {
      $('#estimateDateRangeDisplay').hide();
      $('#estimateCustomDateRange').show();
    } else {
      $('#estimateCustomDateRange').hide();
      $('#estimateDateRangeDisplay').show();
      const range = getPeriodRange(estimateState.periodFilter);
      updateEstimateRangeDisplay(range.start, range.end);
    }

    if (estimateState.periodFilter === 'all') {
      $('#estimateDateRangeDisplay').text('');
    }
  }

  function openEstimatePreviewModal(previewUrl, pdfUrl, printUrl) {
    if (!estimatePreviewModal || !estimatePreviewFrame) {
      window.open(previewUrl || pdfUrl || printUrl, '_blank');
      return;
    }

    estimatePreviewTitle.textContent = 'Preview';
    estimatePreviewFrame.src = previewUrl || pdfUrl || printUrl;
    estimatePreviewFrame.dataset.previewUrl = previewUrl || '';
    estimatePreviewFrame.dataset.pdfUrl = pdfUrl || '';
    estimatePreviewFrame.dataset.printUrl = printUrl || '';
    estimatePreviewFrame.dataset.downloadUrl = (() => {
      if (!pdfUrl) return previewUrl || '';
      try {
        const download = new URL(pdfUrl, window.location.origin);
        download.searchParams.set('download', '1');
        return download.toString();
      } catch (error) {
        return pdfUrl + (String(pdfUrl).includes('?') ? '&' : '?') + 'download=1';
      }
    })();
    estimatePreviewModal.show();
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

  function resolveEstimateAction(trigger) {
    const menu = trigger?.closest('.estimate-action-menu');
    return {
      estimateId: menu?.dataset?.estimateId,
      previewUrl: menu?.dataset?.previewUrl || '',
      pdfUrl: menu?.dataset?.pdfUrl || '',
      printUrl: menu?.dataset?.printUrl || '',
      duplicateUrl: menu?.dataset?.duplicateUrl || '',
    };
  }

  window.previewEstimate = function (trigger) {
    const { previewUrl, pdfUrl, printUrl } = resolveEstimateAction(trigger);
    openEstimatePreviewModal(previewUrl, pdfUrl, printUrl);
  };

  window.printEstimate = function (trigger) {
    const { printUrl, pdfUrl, previewUrl } = resolveEstimateAction(trigger);
    window.open(printUrl || pdfUrl || previewUrl, '_blank');
  };

  window.openPdf = function (trigger) {
    const { pdfUrl, previewUrl } = resolveEstimateAction(trigger);
    window.open(pdfUrl || previewUrl, '_blank');
  };

  window.duplicateEstimate = function (trigger) {
    const { duplicateUrl } = resolveEstimateAction(trigger);
    if (duplicateUrl) {
      window.location.href = duplicateUrl;
    }
  };

  $.fn.dataTable.ext.search.push(function(settings, data) {
    if (!settings.nTable || settings.nTable.id !== 'estimatesTable') {
      return true;
    }

    const rowDate = parseDateDMY(data[0] || '');
    const partyName = String(data[2] || '').trim().toLowerCase();
    const matchesColumns = Object.entries(estimateColumnFilters).every(([index, value]) => {
      const normalized = String(value || '').trim().toLowerCase();
      if (!normalized) return true;
      return String(data[Number(index)] || '').trim().toLowerCase().includes(normalized);
    });

    if (!matchesColumns) {
      return false;
    }

    if (estimateState.firmFilter && partyName !== estimateState.firmFilter.toLowerCase()) {
      return false;
    }

    if (!estimateState.periodFilter || estimateState.periodFilter === 'all') {
      return true;
    }

    if (!rowDate) {
      return false;
    }

    let rangeStart = null;
    let rangeEnd = null;
    if (estimateState.periodFilter === 'custom') {
      rangeStart = estimateState.customFrom ? new Date(estimateState.customFrom) : null;
      rangeEnd = estimateState.customTo ? new Date(estimateState.customTo) : null;
    } else {
      const range = getPeriodRange(estimateState.periodFilter);
      rangeStart = range.start;
      rangeEnd = range.end;
    }

    if (!rangeStart || !rangeEnd) {
      return true;
    }

    rangeStart.setHours(0, 0, 0, 0);
    rangeEnd.setHours(23, 59, 59, 999);
    rowDate.setHours(12, 0, 0, 0);
    return rowDate >= rangeStart && rowDate <= rangeEnd;
  });

  $(document).ready(function() {
    document.querySelectorAll('.table-wrapper .dropdown').forEach(function (dropdown) {
      dropdown.addEventListener('show.bs.dropdown', function () {
        const wrapper = dropdown.closest('.table-wrapper');
        const scrollBody = dropdown.closest('.dataTables_scrollBody');
        if (wrapper) {
          wrapper.dataset.prevOverflowY = wrapper.style.overflowY || '';
          wrapper.style.overflowY = 'visible';
        }
        if (scrollBody) {
          scrollBody.classList.add('dropdown-overflow-visible');
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
          scrollBody.classList.remove('dropdown-overflow-visible');
        }
      });
    });

    estimateState.table = $('#estimatesTable').DataTable({
      pageLength: 10,
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
      order: [[0, 'desc']],
      autoWidth: false,
      scrollX: true,
      responsive: false,
      columnDefs: [
        { orderable: false, searchable: false, targets: [6] },
      ],
      dom: '<"row mb-2 align-items-center"<"col-md-6"l><"col-md-6 text-end">>rt<"row mt-3 align-items-center"<"col-md-6"i><"col-md-6"p>>',
      language: {
        lengthMenu: 'Show _MENU_ estimates',
        info: 'Showing _START_ to _END_ of _TOTAL_ estimates',
        emptyTable: 'No estimates found'
      }
    });

    syncEstimateFilterUi();
    estimateState.table.draw();

    $(document).on('dblclick', '#estimatesTable tbody tr[data-edit-url]', function (event) {
      if ($(event.target).closest('.dropdown, a, button, input, select, textarea, label').length) {
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

    estimateSearchToggle?.addEventListener('click', function () {
      estimateSearchBox?.classList.toggle('open');
      if (estimateSearchBox?.classList.contains('open')) {
        estimateSearchInput?.focus();
      } else if (estimateSearchInput) {
        estimateSearchInput.value = '';
        estimateState.searchValue = '';
        estimateState.table.search('').draw();
      }
    });

    estimateSearchInput?.addEventListener('input', function () {
      estimateState.searchValue = this.value || '';
      estimateState.table.search(estimateState.searchValue).draw();
    });

    estimateFilterInput?.addEventListener('input', function () {
      if (estimateState.activeFilterColumn === null || estimateState.activeFilterColumn === undefined) return;
      estimateColumnFilters[estimateState.activeFilterColumn] = String(this.value || '');
      estimateState.table.draw();
    });

    estimateFilterClear?.addEventListener('click', function () {
      if (estimateState.activeFilterColumn === null || estimateState.activeFilterColumn === undefined) return;
      estimateColumnFilters[estimateState.activeFilterColumn] = '';
      estimateFilterInput.value = '';
      estimateState.table.draw();
      estimateFilterInput.focus();
    });

    $(document).on('click', function (event) {
      if ($(event.target).closest('.estimate-filter-flyout').length || $(event.target).closest('.estimate-filter-trigger').length) {
        return;
      }
      closeEstimateFlyout();
    });

    $('#estimatePeriodSelect').on('change', function() {
      estimateState.periodFilter = $(this).val() || 'all';

      if (estimateState.periodFilter === 'custom') {
        const today = new Date();
        const iso = today.toISOString().split('T')[0];

        if (!$('#estimateCustomFrom').val()) {
          $('#estimateCustomFrom').val(iso);
        }

        if (!$('#estimateCustomTo').val()) {
          $('#estimateCustomTo').val(iso);
        }

        estimateState.customFrom = $('#estimateCustomFrom').val();
        estimateState.customTo = $('#estimateCustomTo').val();
      }

      syncEstimateFilterUi();
      estimateState.table.draw();
    });

    $('#estimateFirmSelect').on('change', function() {
      estimateState.firmFilter = $(this).val() || '';
      estimateState.table.draw();
    });

    $('#estimateCustomFrom').on('change', function() {
      estimateState.customFrom = $(this).val() || '';
      if (estimateState.periodFilter === 'custom') {
        estimateState.table.draw();
      }
    });

    $('#estimateCustomTo').on('change', function() {
      estimateState.customTo = $(this).val() || '';
      if (estimateState.periodFilter === 'custom') {
        estimateState.table.draw();
      }
    });

    estimatePreviewOpenPdf?.addEventListener('click', function () {
      const url = estimatePreviewFrame?.dataset?.pdfUrl || estimatePreviewFrame?.dataset?.previewUrl;
      if (url) window.open(url, '_blank');
    });

    estimatePreviewPrint?.addEventListener('click', function () {
      const url = estimatePreviewFrame?.dataset?.printUrl || estimatePreviewFrame?.dataset?.pdfUrl || estimatePreviewFrame?.dataset?.previewUrl;
      if (url) {
        window.open(url, '_blank');
      }
    });

  estimatePreviewSavePdf?.addEventListener('click', function () {
      const url = estimatePreviewFrame?.dataset?.downloadUrl || estimatePreviewFrame?.dataset?.pdfUrl || estimatePreviewFrame?.dataset?.previewUrl;
      if (!url) return;
      const a = document.createElement('a');
      a.href = url;
      a.target = '_blank';
      a.rel = 'noopener';
      document.body.appendChild(a);
      a.click();
      a.remove();
    });

  estimatePreviewEmailPdf?.addEventListener('click', function () {
    const subject = 'Estimate Preview';
    const downloadUrl = estimatePreviewFrame?.dataset?.downloadUrl || estimatePreviewFrame?.dataset?.pdfUrl || estimatePreviewFrame?.dataset?.previewUrl || window.location.href;
    const body = `Please review the estimate: ${downloadUrl}`;
    openMailClient(subject, body);
  });

    estimatePreviewModalEl?.addEventListener('hidden.bs.modal', function () {
      if (estimatePreviewFrame) {
        estimatePreviewFrame.src = 'about:blank';
        estimatePreviewFrame.removeAttribute('data-preview-url');
        estimatePreviewFrame.removeAttribute('data-pdf-url');
        estimatePreviewFrame.removeAttribute('data-print-url');
        estimatePreviewFrame.removeAttribute('data-download-url');
      }
    });

    estimateState.table.on('draw', function () {
      const visibleCount = estimateState.table.rows({ filter: 'applied' }).count();
      const emptyRow = document.querySelector('#estimatesTable tbody tr td[colspan="7"]');
      if (emptyRow && visibleCount > 0) {
        emptyRow.closest('tr')?.remove();
      }
    });
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

@endpush
