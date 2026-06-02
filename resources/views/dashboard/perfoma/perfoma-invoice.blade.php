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
    overflow-x: auto; overflow-y: auto;
    max-height: 68vh; border: 1px solid #eef2f7; border-radius: 12px;
    padding-bottom: 72px;
  }
  .table-wrapper .dropdown-menu { z-index: 2000; max-height: 48vh; overflow-y: auto; }
  .proforma-header-cell { min-width: 120px; position: relative; }
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
 


  @media (max-width: 991px) {
    .table-wrapper { max-height: none; border-radius: 8px; }
    .custom-table thead th { font-size: 11px; padding: 8px 6px; }
    .custom-table tbody td { font-size: 12px; padding: 10px 6px; }
  }

  @media (max-width: 575px) {
    .table-wrapper { border-radius: 6px; }
    .custom-table thead th { font-size: 10px; padding: 6px 4px; }
    .custom-table tbody td { font-size: 11px; padding: 8px 4px; }
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

      <div class="d-flex justify-content-between align-items-center bg-light mb-2 px-4 py-2 rounded">
        <div class="d-flex">
          <div class="d-flex justify-content-center align-items-center me-2">Filter By:</div>
          <form method="GET" action="{{ route('proforma-invoice') }}" class="d-flex rounded-pill" style="background-color:#E4F2FF;">
            <input type="hidden" name="search" value="{{ $search ?? '' }}">
            <div class="d-flex justify-content-center align-items-center text-center" style="width: 8rem; height:40px; border-right: 1px solid rgb(45, 44, 44); font-size:12px;">
              <select name="date_range" class="bg-transparent border-0" style="outline:none;" onchange="this.form.submit()">
                <option value="all" {{ ($dateRange ?? 'all') === 'all' ? 'selected' : '' }}>All Proformas</option>
                <option value="this_month" {{ ($dateRange ?? 'all') === 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ ($dateRange ?? 'all') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="this_quarter" {{ ($dateRange ?? 'all') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="this_year" {{ ($dateRange ?? 'all') === 'this_year' ? 'selected' : '' }}>This Year</option>
              </select>
            </div>
            <div class="d-flex justify-content-center align-items-center text-center" style="width: 10rem; height:40px; border-right: 1px solid rgb(45, 44, 44); font-size:12px;">
              <select name="party_id" class="bg-transparent border-0" style="outline:none;" onchange="this.form.submit()">
                <option value="all" {{ ($partyId ?? 'all') === 'all' ? 'selected' : '' }}>All Firms</option>
                @foreach($partyOptions ?? [] as $party)
                  <option value="{{ $party->id }}" {{ ($partyId ?? 'all') == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="d-flex justify-content-center align-items-center" style="width: 14rem; height: 40px;">{{ $dateRangeLabel ?? 'All dates' }}</div>
          </form>
        </div>
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
                  <th>Actions</th>
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
                    <td>
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
                           data-preview-url="{{ route('sale.invoice-preview', $proforma) }}"
                           data-pdf-url="{{ route('sale.invoice-pdf', $proforma) }}"
                           data-print-url="{{ route('sale.invoice-preview', ['sale' => $proforma->id, 'print' => 1]) }}"
                           data-duplicate-url="{{ route('proforma-invoice.duplicate', $proforma->id) }}">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                          <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="#" onclick="return transactionPasscodeNavigate('{{ route('proforma-invoice.edit', $proforma->id) }}');"><i class="fas fa-edit me-2"></i>View/Edit</a></li>
                          <li><a class="dropdown-item" href="#" onclick="previewProforma(this); return false;"><i class="fas fa-file-alt me-2"></i>Preview</a></li>
                          <li><a class="dropdown-item" href="#" onclick="openProformaPdf(this); return false;"><i class="fas fa-file-pdf me-2"></i>Open PDF</a></li>
                          <li><a class="dropdown-item" href="#" onclick="printProforma(this); return false;"><i class="fas fa-print me-2"></i>Print</a></li>
                          <li><a class="dropdown-item" href="#" onclick="duplicateProforma(this); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item text-danger" href="#" onclick="return transactionPasscodeExecute('deleteProforma','{{ route('proforma-invoice.destroy', $proforma->id) }}');"><i class="fas fa-trash me-2"></i>Delete</a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td class="text-center text-muted py-4">No proforma invoices yet.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
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
            <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="proformaPreviewEmailPdf">Email PDF</button>
            <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Close</button>
          </div>
      </div>
    </div>
  </div>

  @include('dashboard.partials.transaction-passcode-guard')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="{{ asset('js/components.js') }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
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
      const proformaPreviewEmailPdf = document.getElementById('proformaPreviewEmailPdf');
      const proformaSearchToggle = document.getElementById('proformaSearchToggle');
      const proformaSearchForm = document.getElementById('proformaSearchForm');
      const proformaSearchInput = document.getElementById('proformaSearchInput');

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
          duplicateUrl: menu?.dataset?.duplicateUrl || '',
        };
      }

      window.previewProforma = function (trigger) {
        const { previewUrl, pdfUrl, printUrl } = resolveAction(trigger);
        if (!proformaPreviewModal || !proformaPreviewFrame) {
          window.open(previewUrl || pdfUrl || printUrl, '_blank');
          return;
        }
        proformaPreviewFrame.src = previewUrl || pdfUrl || printUrl;
        proformaPreviewFrame.dataset.previewUrl = previewUrl || '';
        proformaPreviewFrame.dataset.pdfUrl = pdfUrl || '';
        proformaPreviewFrame.dataset.printUrl = printUrl || '';
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
          const url = proformaPreviewFrame?.dataset?.downloadUrl || proformaPreviewFrame?.dataset?.pdfUrl || proformaPreviewFrame?.dataset?.previewUrl || proformaPreviewFrame?.src || '';
          if (!url) return;
          const subject = 'Proforma Invoice';
          const body = `Please find the proforma invoice here: ${url}`;
          openMailClient(subject, body);
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
          scrollX: true,
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
