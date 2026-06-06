<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar — Delivery Challan</title>
  <meta name="description" content="Record supplier purchase bills with live preview in Vyapar.">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <!-- Custom Styles -->
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
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

  <style>

    .search-container {
      position: relative;
      width: 50px;
      transition: all 0.3s ease;
    }

    .search-container.active {
      width: 250px;
    }

    .search-input {
      width: 100%;
      height: 40px;
      border: none;
      outline: none;
      padding: 0 40px 0 10px;
      border-radius: 20px;
      opacity: 0;
      transition: 0.3s;
    }

    .search-container.active .search-input {
      opacity: 1;
    }

    .search-btn {
      position: absolute;
      right: 5px;
      top: 5px;
      width: 30px;
      height: 30px;
      background: #6C757D;
      color: white;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
    }

    .filter-pill {
      background-color: #E4F2FF;
      border-radius: 999px;
      display: flex;
      align-items: center;
      height: 38px;
      padding: 0 8px;
    }

    .filter-left {
      border-right: 1px solid #ccc;
      padding: 0 10px;
    }

    .filter-right {
      padding: 0 10px;
      min-width: 210px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      white-space: nowrap;
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
      min-width: 120px;
    }

    .date-input {
      border: none;
      background: transparent;
      font-size: 12px;
      width: 110px;
      outline: none;
    }

    .challan-header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-left: auto;
      flex-shrink: 0;
    }

    .challan-action-btn {
      border: 0;
      background: transparent;
      padding: 0;
      line-height: 1;
      cursor: pointer;
    }

    .challan-action-btn.print-btn {
      color: #6c757d;
    }

  .challan-action-btn.excel-btn {
    color: #198754;
  }
  .challan-header-cell {
    min-width: 120px;
  }
  .challan-header-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .challan-filter-trigger {
    border: 0;
    background: transparent;
    color: #adb5bd;
    padding: 0;
    line-height: 1;
    font-size: 12px;
    cursor: pointer;
  }
  .challan-filter-trigger:hover,
  .challan-filter-trigger.active {
    color: #0d6efd;
  }
  .challan-filter-flyout {
    position: fixed;
    z-index: 1060;
    width: 210px;
    padding: 8px;
    background: #fff;
    border: 1px solid #dbe3ee;
    border-radius: 10px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
  }
  .challan-filter-flyout input {
    width: 100%;
  }
  .challan-filter-flyout .form-control {
    font-size: 13px;
  }
  .challan-filter-flyout .filter-clear {
    margin-top: 6px;
  }
  </style>
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


</head>

<body data-page="delivery-challan">

  <!-- Navbar & Sidebar injected by components.js -->

  <!-- ═══════════════════════════════════════
     MAIN CONTENT — PURCHASE BILL
     ═══════════════════════════════════════ -->
  <main class="main-content" id="mainContent">


    <div class="d-flex justify-content-between align-items-center bg-light p-4 border-bottom mb-2">
      <div class="col-12 text-center">
        <h4 class="mb-0 text-secondary">Delivery Challan</h4>
      </div>

    </div>
    <div class="d-flex justify-content-between align-items-center bg-light mb-2 px-3 py-2 rounded gap-3 flex-wrap">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="small fw-semibold">Filter By:</span>

        <div class="d-flex rounded-pill filter-pill">
          <div class="filter-left">
            <select id="challanPeriodSelect" class="filter-select">
              <option value="all" selected>All Delivery Challans</option>
              <option value="this_month">This Month</option>
              <option value="last_month">Last Month</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
              <option value="custom">Custom</option>
            </select>
          </div>

          <div class="filter-right">
            <span id="challanDateRangeDisplay"></span>
            <div id="challanCustomDateRange" class="d-none align-items-center gap-1">
              <input id="challanCustomFrom" type="date" class="date-input" />
              <span>to</span>
              <input id="challanCustomTo" type="date" class="date-input" />
            </div>
          </div>
        </div>

        <div class="filter-pill small-pill">
          <select id="challanFirmSelect" class="filter-select text-center">
            <option value="" selected>All Firms</option>
            @foreach($challans->map(fn($challan) => $challan->display_party_name)->filter()->unique()->values() as $firm)
              <option value="{{ $firm }}">{{ $firm }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="challan-header-actions">
        <button type="button" class="challan-action-btn print-btn" id="challanPrintBtn" title="Print" onclick="openChallanOptions('print')">
          <i class="fas fa-print fs-5"></i>
        </button>
        <button type="button" class="challan-action-btn excel-btn" id="challanExcelBtn" title="Export Excel" onclick="openChallanOptions('excel')">
          <i class="fas fa-file-excel fs-5"></i>
        </button>
      </div>
    </div>



    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="row g-2 mb-3">
          <p class="fw-bold">Transactions</p>
        </div>
        <div class="col-12 d-flex justify-content-between">
          <div class="topbar-search ms-3">
            <span class="search-icon"><i class="bi bi-search"></i></span>
            <input type="text" placeholder="Search...">
          </div>

          <button onclick="window.location.href='{{ route('create-challan') }}'"
        class="btn btn-primary rounded">
    <span class="text-primary bg-light rounded-circle" style="padding: 0px 4px;">+</span>
    Add Delivery Challan
</button>
        </div>

        <div class="table-wrapper">
  <table id="challanTable" class="table align-middle custom-table mb-0">
            <thead>
              <tr class="text-uppercase small text-secondary">
                <th class="py-3 challan-header-cell">
                  <div class="challan-header-label">
                    <span>Date</span>
                    <button type="button" class="challan-filter-trigger" data-column="0" aria-label="Filter Date" onmousedown="event.stopPropagation();" onclick="openChallanFilter(event, 0)">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                  </div>
                </th>
                <th class="py-3 challan-header-cell">
                  <div class="challan-header-label">
                    <span>Party</span>
                    <button type="button" class="challan-filter-trigger" data-column="1" aria-label="Filter Party" onmousedown="event.stopPropagation();" onclick="openChallanFilter(event, 1)">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                  </div>
                </th>
                <th class="py-3 challan-header-cell">
                  <div class="challan-header-label">
                    <span>Challan No.</span>
                    <button type="button" class="challan-filter-trigger" data-column="2" aria-label="Filter Challan No." onmousedown="event.stopPropagation();" onclick="openChallanFilter(event, 2)">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                  </div>
                </th>
                <th class="py-3 challan-header-cell">
                  <div class="challan-header-label">
                    <span>Due Date</span>
                    <button type="button" class="challan-filter-trigger" data-column="3" aria-label="Filter Due Date" onmousedown="event.stopPropagation();" onclick="openChallanFilter(event, 3)">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                  </div>
                </th>
                <th class="py-3 text-end challan-header-cell">
                  <div class="challan-header-label">
                    <span>Total Amount</span>
                    <button type="button" class="challan-filter-trigger" data-column="4" aria-label="Filter Total Amount" onmousedown="event.stopPropagation();" onclick="openChallanFilter(event, 4)">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                  </div>
                </th>
                <th class="py-3 challan-header-cell">
                  <div class="challan-header-label">
                    <span>Status</span>
                    <button type="button" class="challan-filter-trigger" data-column="5" aria-label="Filter Status" onmousedown="event.stopPropagation();" onclick="openChallanFilter(event, 5)">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                  </div>
                </th>
                <th class="py-3">Action</th>
                <th class="py-3 text-center" style="width:56px;"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($challans as $challan)
                @php
                  $isClosed = $challan->status === 'closed';
                  $isOverdue = !$isClosed && $challan->due_date && $challan->due_date->isPast();
                  $convertedInvoice = $convertedInvoices[$challan->id] ?? null;
                  $convertedInvoiceNumber = $convertedInvoice->bill_number ?? null;
                  $overdueDays = $isOverdue ? max(1, $challan->due_date->copy()->startOfDay()->diffInDays(now()->copy()->startOfDay())) : 0;
                @endphp
                @php
                  $challanItemsJson = $challan->items->map(function ($item) {
                    return [
                      'name' => $item->item_name ?? '-',
                      'quantity' => (float) ($item->quantity ?? 0),
                      'unit' => $item->unit ?? '-',
                      'price' => (float) ($item->unit_price ?? 0),
                      'amount' => (float) ($item->amount ?? 0),
                    ];
                  })->values();

                  $challanDescriptionJson = $challan->description ?? ($challan->challanDetail?->description ?? '');
                @endphp
                <tr class="challan-row"
                    data-edit-url="{{ route('delivery-challan.edit', $challan->id) }}"
                    data-date="{{ optional($challan->invoice_date)->format('d/m/Y') ?? '-' }}"
                    data-party-name="{{ $challan->display_party_name }}"
                    data-challan-no="{{ $challan->bill_number ?? '-' }}"
                    data-due-date="{{ optional($challan->due_date)->format('d/m/Y') ?? '-' }}"
                    data-total="{{ number_format($challan->grand_total ?? 0, 2) }}"
                    data-status="{{ $isClosed ? 'Closed' : 'Open' }}"
                    data-category-name="{{ $challan->challanDetail?->warehouse_name ?: ($challan->challanDetail?->broker_name ?: '-') }}"
                    data-payment-type="{{ $challan->payment_type ?? '-' }}"
                    data-received-paid="{{ number_format((float) ($challan->received_amount ?? 0), 2) }}"
                    data-balance="{{ number_format((float) ($challan->balance ?? 0), 2) }}"
                    data-description='@json($challanDescriptionJson)'
                    data-items='@json($challanItemsJson)'>
                  <td data-order="{{ optional($challan->invoice_date)->format('Y-m-d') ?? '' }}">{{ optional($challan->invoice_date)->format('d/m/Y') ?? '-' }}</td>
                  <td>{{ $challan->display_party_name }}</td>
                  <td data-order="{{ $challan->bill_number ?? '' }}">{{ $challan->bill_number ?? '-' }}</td>
                  <td data-order="{{ optional($challan->due_date)->format('Y-m-d') ?? '' }}">
                    <div>{{ optional($challan->due_date)->format('d/m/Y') ?? '-' }}</div>
                    @if($isOverdue)
                      <span class="badge text-bg-light text-secondary mt-1">Overdue: {{ $overdueDays }} {{ $overdueDays === 1 ? 'day' : 'days' }}</span>
                    @endif
                  </td>
                  <td class="text-end fw-semibold" data-order="{{ number_format($challan->grand_total ?? 0, 2, '.', '') }}">Rs {{ number_format($challan->grand_total ?? 0, 2) }}</td>
                  <td>
                    @if($isClosed)
                      <span class="text-primary fw-semibold">Closed</span>
                      <span class="badge text-bg-light text-secondary">{{ optional($challan->updated_at)->format('d/m/Y') }}</span>
                    @else
                      <span class="text-primary fw-semibold">Open</span>
                    @endif
                  </td>
                  <td>
                    @if($isClosed)
                      <a href="{{ $convertedInvoice ? route('sale.edit', $convertedInvoice->id) : '#' }}" class="text-decoration-underline text-primary">
                        Converted To Invoice No.{{ $convertedInvoiceNumber ?? '-' }}
                      </a>
                    @else
                      <a href="{{ route('delivery-challans.convert-to-sale', $challan->id) }}" class="btn btn-sm btn-light border text-uppercase text-primary px-3">
                        Convert To Sale
                      </a>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="dropdown">
                      <button class="btn btn-sm border-0 text-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#" onclick="return transactionPasscodeNavigate('{{ route('delivery-challan.edit', $challan->id) }}');">View/Edit</a></li>
                        <li><a class="dropdown-item" href="#" onclick="return transactionPasscodeExecute('deleteChallan','{{ route('delivery-challan.destroy', $challan->id) }}');">Delete</a></li>
                        <li><a class="dropdown-item" href="{{ route('delivery-challan.duplicate', $challan->id) }}">Duplicate</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openChallanRecordPreview('{{ route('sale.invoice-preview', $challan) }}', '{{ route('sale.invoice-pdf', ['sale' => $challan->id, 'download' => 1, 'doc' => 'delivery_challan']) }}', '{{ route('sale.invoice-preview', ['sale' => $challan->id, 'print' => 1, 'doc' => 'delivery_challan']) }}'); return false;">Preview</a></li>
                        <li><a class="dropdown-item" href="{{ route('sale.invoice-pdf', ['sale' => $challan->id, 'download' => 1, 'doc' => 'delivery_challan']) }}" target="_blank" rel="noopener">Open PDF</a></li>
                        <li><a class="dropdown-item" href="#" onclick="printChallanPdf('{{ route('sale.invoice-preview', ['sale' => $challan->id, 'print' => 1]) }}'); return false;">Print</a></li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">No delivery challans found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div id="challanFilterFlyout" class="challan-filter-flyout d-none" aria-hidden="true">
          <input id="challanFilterInput" type="text" class="form-control form-control-sm" placeholder="Filter">
          <button type="button" class="btn btn-link btn-sm p-0 filter-clear" id="challanFilterClear">Clear</button>
        </div>

      </div>
    </div>

  </main>

  <!-- ═══════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════ -->
  <div class="modal fade" id="challanOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="challanOptionsTitle">Print Options</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-grid gap-2">
            <label class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span>Item Details</span>
              <input type="checkbox" id="challanOptionItemDetails" checked>
            </label>
            <label class="d-flex justify-content-between align-items-center py-2">
              <span>Description</span>
              <input type="checkbox" id="challanOptionDescription" checked>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="challanOptionsApply">Apply</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="challanPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="challanPreviewTitle">Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0" style="min-height:72vh;">
          <iframe id="challanPreviewFrame" title="Delivery Challan Preview" style="width:100%; min-height:72vh; border:0;"></iframe>
        </div>
        <div class="modal-footer justify-content-center gap-2 flex-wrap">
          <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="challanPreviewOpenPdf">Open PDF</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="challanPreviewPrint">Print</button>
          <button type="button" class="btn btn-outline-success rounded-pill px-4" id="challanPreviewSavePdf">Save PDF</button>
          <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="challanPreviewEmailPdf">Email PDF</button>
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
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script>
    const challanState = {
      table: null,
      periodFilter: 'all',
      firmFilter: '',
      customFrom: '',
      customTo: '',
      pendingAction: null,
      challans: []
    };

    const challanOptionsModalEl = document.getElementById('challanOptionsModal');
    const challanOptionsModal = challanOptionsModalEl ? bootstrap.Modal.getOrCreateInstance(challanOptionsModalEl) : null;
    const challanPreviewModalEl = document.getElementById('challanPreviewModal');
    const challanPreviewModal = challanPreviewModalEl ? bootstrap.Modal.getOrCreateInstance(challanPreviewModalEl) : null;
    const challanPreviewFrame = document.getElementById('challanPreviewFrame');
    const challanPreviewTitle = document.getElementById('challanPreviewTitle');
    const challanPreviewOpenPdf = document.getElementById('challanPreviewOpenPdf');
    const challanPreviewPrint = document.getElementById('challanPreviewPrint');
    const challanPreviewSavePdf = document.getElementById('challanPreviewSavePdf');
    const challanPreviewEmailPdf = document.getElementById('challanPreviewEmailPdf');
    const challanOptionsTitle = document.getElementById('challanOptionsTitle');
    const challanOptionsApply = document.getElementById('challanOptionsApply');
    const challanOptionItemDetails = document.getElementById('challanOptionItemDetails');
    const challanOptionDescription = document.getElementById('challanOptionDescription');
    const deliveryCompanyName = window.App?.user?.name || 'My Company';
    let challanPreviewObjectUrl = null;
    const challanColumnFilters = { 0: '', 1: '', 2: '', 3: '', 4: '', 5: '' };
    const challanFilterFlyout = document.getElementById('challanFilterFlyout');
    const challanFilterInput = document.getElementById('challanFilterInput');
    const challanFilterClear = document.getElementById('challanFilterClear');
    let challanActiveFilterColumn = null;

    function escapeHtml(value) {
      return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[char]));
    }

    function money(value) {
      return `Rs ${Number(value || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function parseRowDate(value) {
      const raw = String(value || '').trim();
      if (!raw) return null;

      const parts = raw.split('/');
      if (parts.length !== 3) return null;

      const day = parseInt(parts[0], 10);
      const month = parseInt(parts[1], 10) - 1;
      const year = parseInt(parts[2], 10);
      if ([day, month, year].some(Number.isNaN)) return null;
      return new Date(year, month, day);
    }

    function formatDisplayDate(date) {
      if (!date) return '';
      const dd = String(date.getDate()).padStart(2, '0');
      const mm = String(date.getMonth() + 1).padStart(2, '0');
      const yyyy = date.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    }

    function formatIsoDate(date) {
      const dd = String(date.getDate()).padStart(2, '0');
      const mm = String(date.getMonth() + 1).padStart(2, '0');
      const yyyy = date.getFullYear();
      return `${yyyy}-${mm}-${dd}`;
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

    function updateRangeDisplay(from, to) {
      const $dateRangeDisplay = $("#challanDateRangeDisplay");
      if (!from || !to) {
        $dateRangeDisplay.text('');
        return;
      }

      $dateRangeDisplay.text(`${formatDisplayDate(from)} To ${formatDisplayDate(to)}`);
    }

    function setCustomMode(isCustom) {
      const $dateRangeDisplay = $("#challanDateRangeDisplay");
      const $customDateRange = $("#challanCustomDateRange");
      $dateRangeDisplay.toggleClass('d-none', isCustom);
      $customDateRange.toggleClass('d-none', !isCustom).toggleClass('d-flex', isCustom);
    }

    function buildChallanRowDataFromDom(row) {
      return {
        date: row.dataset.date || '',
        party_name: row.dataset.partyName || '',
        challan_no: row.dataset.challanNo || '',
        due_date: row.dataset.dueDate || '',
        total: row.dataset.total || '0.00',
        status: row.dataset.status || '',
        category_name: row.dataset.categoryName || '',
        payment_type: row.dataset.paymentType || '',
        received_paid: row.dataset.receivedPaid || '0.00',
        balance: row.dataset.balance || '0.00',
        description: row.dataset.description || '',
        items: (() => {
          try {
            return JSON.parse(row.dataset.items || '[]');
          } catch (error) {
            return [];
          }
        })()
      };
    }

    function collectChallanRows() {
      challanState.challans = Array.from(document.querySelectorAll('#challanTable tbody tr.challan-row'))
        .map(buildChallanRowDataFromDom);
    }

    function getFilteredChallanRows() {
      if (challanState.table) {
        const indexes = challanState.table.rows({ search: 'applied', order: 'applied' }).indexes().toArray();
        return indexes.map((index) => challanState.challans[index]).filter(Boolean);
      }

      return challanState.challans.filter((row) => {
        if (challanState.firmFilter && String(row.party_name || '').toLowerCase() !== String(challanState.firmFilter || '').toLowerCase()) {
          return false;
        }

        if (challanState.periodFilter === 'all') return true;

        let rangeStart = null;
        let rangeEnd = null;
        if (challanState.periodFilter === 'custom') {
          rangeStart = challanState.customFrom ? new Date(challanState.customFrom) : null;
          rangeEnd = challanState.customTo ? new Date(challanState.customTo) : null;
        } else {
          const range = getPeriodRange(challanState.periodFilter);
          rangeStart = range.start;
          rangeEnd = range.end;
        }

        const rowDate = parseRowDate(row.date);
        if (!rowDate || !rangeStart || !rangeEnd) return false;
        rangeStart.setHours(0, 0, 0, 0);
        rangeEnd.setHours(23, 59, 59, 999);
        rowDate.setHours(12, 0, 0, 0);
        return rowDate >= rangeStart && rowDate <= rangeEnd;
      });
    }

    function formatExtraSectionHtml(label, bodyHtml) {
      return `
        <tr>
          <td colspan="10">
            <div class="bg-white border rounded p-2 mt-1">
              <div class="fw-bold text-secondary mb-2" style="font-size:12px;">${escapeHtml(label)}</div>
              ${bodyHtml}
            </div>
          </td>
        </tr>
      `;
    }

    function buildChallanPreviewHtml(options = {}) {
      const rows = getFilteredChallanRows();
      const fromLabel = challanState.periodFilter === 'all'
        ? 'All Delivery Challans'
        : challanState.periodFilter === 'custom'
          ? `${challanState.customFrom || 'Start'} to ${challanState.customTo || 'Today'}`
          : {
              this_month: 'This Month',
              last_month: 'Last Month',
              this_quarter: 'This Quarter',
              this_year: 'This Year',
            }[challanState.periodFilter] || 'All Delivery Challans';

      const showItemDetails = Boolean(options.item_details);
      const showDescription = Boolean(options.description);

      const tableRows = rows.map((row, index) => {
        const itemTable = showItemDetails && Array.isArray(row.items) && row.items.length
          ? `
            <table style="width:100%; border-collapse:collapse; margin-top:6px;">
              <thead>
                <tr>
                  <th style="border:1px solid #d1d5db; background:#e5e7eb; padding:6px; font-size:11px; width:42px;">#</th>
                  <th style="border:1px solid #d1d5db; background:#e5e7eb; padding:6px; font-size:11px;">Item name</th>
                  <th style="border:1px solid #d1d5db; background:#e5e7eb; padding:6px; font-size:11px; width:90px;">Quantity</th>
                  <th style="border:1px solid #d1d5db; background:#e5e7eb; padding:6px; font-size:11px; width:110px;">Price / Unit</th>
                  <th style="border:1px solid #d1d5db; background:#e5e7eb; padding:6px; font-size:11px; width:120px;">Amount</th>
                </tr>
              </thead>
              <tbody>
                ${row.items.map((item, itemIndex) => `
                  <tr>
                    <td style="border:1px solid #d1d5db; padding:6px; font-size:11px;">${itemIndex + 1}</td>
                    <td style="border:1px solid #d1d5db; padding:6px; font-size:11px;">${escapeHtml(item.name || '-')}</td>
                    <td style="border:1px solid #d1d5db; padding:6px; font-size:11px; text-align:right;">${escapeHtml(item.quantity ?? 0)}</td>
                    <td style="border:1px solid #d1d5db; padding:6px; font-size:11px; text-align:right;">${money(item.price || 0)}</td>
                    <td style="border:1px solid #d1d5db; padding:6px; font-size:11px; text-align:right;">${money(item.amount || 0)}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          `
          : '';

        const descriptionHtml = showDescription && row.description
          ? `<div class="mt-2" style="font-size:12px;"><strong>Description:</strong> ${escapeHtml(row.description)}</div>`
          : '';

        return `
          <tr>
            <td>${row.date || '-'}</td>
            <td>${escapeHtml(row.challan_no || '-')}</td>
            <td><strong>${escapeHtml(row.party_name || '-')}</strong></td>
            <td>${escapeHtml(row.category_name || '-')}</td>
            <td>${escapeHtml('Delivery Challan')}</td>
            <td>${escapeHtml(row.status || 'Open')}</td>
            <td class="num">${money(row.total || 0)}</td>
            <td>${escapeHtml(row.payment_type || '-')}</td>
            <td class="num">${money(row.received_paid || 0)}</td>
            <td class="num">${money(row.balance || 0)}</td>
          </tr>
          ${itemTable ? formatExtraSectionHtml('Item Details', itemTable) : ''}
          ${descriptionHtml ? formatExtraSectionHtml('Description', descriptionHtml) : ''}
        `;
      }).join('');

      return `
        <!DOCTYPE html>
        <html>
          <head>
            <meta charset="utf-8">
            <style>
              html, body { margin:0; padding:0; background:#f4f5f7; color:#111827; }
              body { font-family: Arial, sans-serif; }
              .sheet {
                width: min(1120px, calc(100vw - 40px));
                margin: 18px auto;
                background: #fff;
                border: 1px solid #e5e7eb;
                box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
                padding: 18px 20px 24px;
              }
              .company { text-align:center; font-size:22px; font-weight:700; }
              .company-meta { text-align:center; font-size:12px; color:#6b7280; margin-top:2px; }
              .title { text-align:center; font-size:26px; font-weight:700; text-decoration: underline; margin:20px 0 14px; }
              .duration { font-size:18px; font-weight:700; margin: 0 0 18px; }
              table { width:100%; border-collapse:collapse; }
              th, td { border:1px solid #d1d5db; padding:8px 10px; font-size:12px; vertical-align: top; }
              th { background:#d9d9d9; font-weight:700; text-transform: uppercase; }
              .num { text-align:right; white-space:nowrap; }
              .totals {
                display:flex;
                justify-content:flex-end;
                margin-top:24px;
                font-size:22px;
                font-weight:700;
              }
              .footer-note { margin-top:22px; font-size:10px; color:#64748b; }
            </style>
          </head>
          <body>
            <div class="sheet">
              <div class="company">${escapeHtml(deliveryCompanyName)}</div>
              <div class="company-meta">Phone no.: -</div>
              <div class="title">Delivery Challan Report</div>
              <div class="duration">Duration: ${escapeHtml(fromLabel)}</div>
              <table>
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Ref No.</th>
                    <th>Party Name</th>
                    <th>Category Name</th>
                    <th>Type</th>
                    <th>Txn Status</th>
                    <th class="num">Total</th>
                    <th>Payment Type</th>
                    <th class="num">Received / Paid</th>
                    <th class="num">Balance</th>
                  </tr>
                </thead>
                <tbody>
                  ${tableRows || `<tr><td colspan="10" style="text-align:center;color:#64748b;padding:18px;">No delivery challans found.</td></tr>`}
                </tbody>
              </table>
              <div class="totals">Total: ${money(rows.reduce((sum, row) => sum + Number(row.total || 0), 0))}</div>
              <div class="footer-note">Generated on ${new Date().toLocaleString()}</div>
            </div>
          </body>
        </html>
      `;
    }

    function openChallanPreviewModal(options = {}) {
      if (challanPreviewObjectUrl) {
        URL.revokeObjectURL(challanPreviewObjectUrl);
        challanPreviewObjectUrl = null;
      }

      const html = buildChallanPreviewHtml(options);
      const blob = new Blob([html], { type: 'text/html;charset=utf-8' });
      const url = URL.createObjectURL(blob);
      challanPreviewObjectUrl = url;

      if (!challanPreviewModal || !challanPreviewFrame) {
        window.open(url, '_blank');
        return;
      }

      challanPreviewTitle.textContent = 'Preview';
      challanPreviewFrame.src = url;
      challanPreviewFrame.dataset.reportUrl = url;
      challanPreviewFrame.dataset.reportMode = 'delivery-challan';
      challanPreviewFrame.dataset.reportHtml = html;
      challanPreviewFrame.dataset.reportName = 'delivery-challan-report';
      challanPreviewModal.show();
    }

    function openDeliveryChallanPdf(url) {
      if (!url) return;
      const a = document.createElement('a');
      a.href = url;
      a.download = '';
      a.rel = 'noopener';
      document.body.appendChild(a);
      a.click();
      a.remove();
    }

    function printDeliveryChallan(url) {
      window.open(url, '_blank');
    }

    function openChallanRecordPreview(url, pdfUrl = null, printUrl = null) {
      if (!challanPreviewFrame || !challanPreviewModal) {
        window.open(url, '_blank');
        return;
      }

      if (challanPreviewObjectUrl) {
        URL.revokeObjectURL(challanPreviewObjectUrl);
        challanPreviewObjectUrl = null;
      }

      challanPreviewTitle.textContent = 'Preview';
      challanPreviewFrame.src = url;
      challanPreviewFrame.dataset.reportUrl = url;
      challanPreviewFrame.dataset.reportPdfUrl = pdfUrl || url;
      challanPreviewFrame.dataset.reportPrintUrl = printUrl || pdfUrl || url;
      challanPreviewFrame.dataset.reportDownloadUrl = (() => {
        const target = pdfUrl || url;
        try {
          const download = new URL(target, window.location.origin);
          download.searchParams.set('download', '1');
          return download.toString();
        } catch (error) {
          return target + (String(target).includes('?') ? '&' : '?') + 'download=1';
        }
      })();
      challanPreviewModal.show();
    }

    function openChallanPdf(url) {
      if (!url) return;
      const a = document.createElement('a');
      a.href = url;
      a.download = '';
      a.rel = 'noopener';
      document.body.appendChild(a);
      a.click();
      a.remove();
    }

    function printChallanPdf(url) {
      window.open(url, '_blank');
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

    function getVisibleChallanRows() {
      return getFilteredChallanRows();
    }

    function exportVisibleChallansToExcel(extraOptions = {}) {
      const rows = getFilteredChallanRows();

      if (!rows.length) {
        alert('Export ke liye koi delivery challan available nahi hai.');
        return;
      }

      const columns = [
        { key: 'date', label: 'Date' },
        { key: 'challan_no', label: 'Ref No.' },
        { key: 'party_name', label: 'Party Name' },
        { key: 'category_name', label: 'Category Name' },
        { key: 'type', label: 'Type' },
        { key: 'status', label: 'Txn Status' },
        { key: 'total', label: 'Total' },
        { key: 'payment_type', label: 'Payment Type' },
        { key: 'received_paid', label: 'Received / Paid' },
        { key: 'balance', label: 'Balance' },
      ];

      if (extraOptions.item_details) {
        columns.push({ key: 'item_details', label: 'Item Details' });
      }
      if (extraOptions.description) {
        columns.push({ key: 'description', label: 'Description' });
      }

      const csvLines = [columns.map((column) => `"${column.label.replace(/"/g, '""')}"`).join(',')];

      rows.forEach((row) => {
        const exportRow = {
          ...row,
          type: 'Delivery Challan',
          item_details: Array.isArray(row.items) && row.items.length
            ? row.items.map((item, index) => `${index + 1}. ${item.name || '-'} | Qty: ${item.quantity ?? 0} | Amount: ${money(item.amount || 0)}`).join('\n')
            : '',
        };

        csvLines.push(
          columns.map((column) => `"${String(exportRow[column.key] ?? '').replace(/"/g, '""')}"`).join(',')
        );
      });

      const now = new Date();
      const filename = `delivery-challans-${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}.csv`;
      const blob = new Blob(["\uFEFF" + csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');

      link.href = URL.createObjectURL(blob);
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(link.href);
    }

    function printVisibleChallans(extraOptions = {}) {
      openChallanPreviewModal(extraOptions);
    }

    function deleteChallan(url) {
      if (!confirm('Are you sure you want to delete this delivery challan?')) {
        return;
      }

      const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || window.App?.csrfToken
        || '';

      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
      })
        .then(async (response) => {
          const data = await response.json();

          if (!response.ok) {
            throw new Error(data?.message || 'Delete failed');
          }

          window.location.reload();
        })
        .catch((error) => {
          alert(error.message || 'Unable to delete delivery challan.');
        });
    }

    function closeChallanFilterPopovers() {
      document.querySelectorAll('.challan-filter-trigger.active').forEach((btn) => {
        btn.classList.remove('active');
      });
      challanActiveFilterColumn = null;
      challanFilterFlyout?.classList.add('d-none');
      if (challanFilterFlyout) {
        challanFilterFlyout.setAttribute('aria-hidden', 'true');
      }
    }

    function openChallanFilter(event, columnIndex) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
          event.stopImmediatePropagation();
        }
      }

      const trigger = event?.currentTarget || document.querySelector(`.challan-filter-trigger[data-column="${columnIndex}"]`);
      if (!trigger) {
        return;
      }

      const rect = trigger.getBoundingClientRect();
      closeChallanFilterPopovers();
      trigger.classList.add('active');
      challanActiveFilterColumn = Number(columnIndex);
      challanFilterInput.value = challanColumnFilters[challanActiveFilterColumn] || '';
      challanFilterFlyout.style.left = `${Math.max(12, Math.min(rect.left, window.innerWidth - 234))}px`;
      challanFilterFlyout.style.top = `${rect.bottom + 8}px`;
      challanFilterFlyout.classList.remove('d-none');
      challanFilterFlyout.setAttribute('aria-hidden', 'false');
      challanFilterInput.focus();
      challanFilterInput.select();
    }

    function applyChallanLiveFilter(value) {
      if (challanActiveFilterColumn === null || challanActiveFilterColumn === undefined) {
        return;
      }
      challanColumnFilters[challanActiveFilterColumn] = String(value || '');
      challanState.table.draw();
    }

    function openChallanOptions(action) {
      challanState.pendingAction = action;
      if (challanOptionsTitle) {
        challanOptionsTitle.textContent = action === 'excel' ? 'Excel Options' : 'Print Options';
      }
      challanOptionsModal?.show();
    }

    // Filter / DataTable functionality
    $(document).ready(function () {
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

      collectChallanRows();

      const $periodSelect = $("#challanPeriodSelect");
      const $firmSelect = $("#challanFirmSelect");
      const $customFrom = $("#challanCustomFrom");
      const $customTo = $("#challanCustomTo");
      const $printBtn = $("#challanPrintBtn");
      const $excelBtn = $("#challanExcelBtn");
      const $topSearch = $(".topbar-search input");
      const $optionsModal = $("#challanOptionsModal");
      const $previewModal = $("#challanPreviewModal");

      challanState.periodFilter = $periodSelect.val() || "all";
      challanState.firmFilter = $firmSelect.val() || "";

      $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'challanTable') {
          return true;
        }

        const row = challanState.challans[dataIndex];
        if (!row) return true;

        const columnMatches = Object.entries(challanColumnFilters).every(([index, value]) => {
          const normalized = String(value || '').trim().toLowerCase();
          if (!normalized) return true;
          return String(data[Number(index)] || '').trim().toLowerCase().includes(normalized);
        });

        if (!columnMatches) {
          return false;
        }

        if (challanState.firmFilter && String(row.party_name || '').toLowerCase() !== String(challanState.firmFilter || '').toLowerCase()) {
          return false;
        }

        if (challanState.periodFilter === 'all') {
          return true;
        }

        let rangeStart = null;
        let rangeEnd = null;

        if (challanState.periodFilter === 'custom') {
          rangeStart = challanState.customFrom ? new Date(challanState.customFrom) : null;
          rangeEnd = challanState.customTo ? new Date(challanState.customTo) : null;
        } else {
          const range = getPeriodRange(challanState.periodFilter);
          rangeStart = range.start;
          rangeEnd = range.end;
        }

        const rowDate = parseRowDate(row.date);
        if (!rowDate || !rangeStart || !rangeEnd) {
          return false;
        }

        rangeStart.setHours(0, 0, 0, 0);
        rangeEnd.setHours(23, 59, 59, 999);
        rowDate.setHours(12, 0, 0, 0);
        return rowDate >= rangeStart && rowDate <= rangeEnd;
      });

      challanState.table = $('#challanTable').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        order: [[0, 'desc']],
        autoWidth: false,
        scrollX: true,
        responsive: false,
        columnDefs: [
          { orderable: false, searchable: false, targets: [6, 7] },
        ],
        dom: '<"row mb-2 align-items-center"<"col-md-6"l><"col-md-6 text-end">>rt<"row mt-3 align-items-center"<"col-md-6"i><"col-md-6"p>>',
        language: {
          lengthMenu: 'Show _MENU_ delivery challans',
          info: 'Showing _START_ to _END_ of _TOTAL_ delivery challans',
          emptyTable: 'No delivery challans found'
        }
      });

      $(document).on('dblclick', '#challanTable tbody tr.challan-row', function (event) {
        if ($(event.target).closest('.dropdown, a, button, input, select, textarea, label, .challan-filter-flyout, .challan-filter-trigger').length) {
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

      function syncPeriodDisplay() {
        if (challanState.periodFilter === 'custom') {
          setCustomMode(true);
          if (!challanState.customFrom || !challanState.customTo) {
            const today = new Date();
            const todayIso = formatIsoDate(today);
            challanState.customFrom = challanState.customFrom || todayIso;
            challanState.customTo = challanState.customTo || todayIso;
            $customFrom.val(challanState.customFrom);
            $customTo.val(challanState.customTo);
          }
          updateRangeDisplay(new Date(challanState.customFrom), new Date(challanState.customTo));
          return;
        }

        setCustomMode(false);
        const range = getPeriodRange(challanState.periodFilter);
        updateRangeDisplay(range.start, range.end);
      }

      syncPeriodDisplay();
      challanState.table.draw();

      challanFilterInput?.addEventListener('input', function () {
        applyChallanLiveFilter(this.value);
      });

      challanFilterClear?.addEventListener('click', function () {
        if (challanActiveFilterColumn === null || challanActiveFilterColumn === undefined) {
          return;
        }
        challanFilterInput.value = '';
        challanColumnFilters[challanActiveFilterColumn] = '';
        challanState.table.draw();
        challanFilterInput.focus();
      });

      $(document).on('click', function (event) {
        if ($(event.target).closest('.challan-filter-flyout').length || $(event.target).closest('.challan-filter-trigger').length) {
          return;
        }
        closeChallanFilterPopovers();
      });

      $topSearch.on('input', function () {
        challanState.table.search(this.value || '').draw();
      });

      $periodSelect.on('change', function () {
        challanState.periodFilter = $(this).val() || 'all';
        syncPeriodDisplay();
        challanState.table.draw();
      });

      $firmSelect.on('change', function () {
        challanState.firmFilter = $(this).val() || '';
        challanState.table.draw();
      });

      $customFrom.on('change', function () {
        challanState.customFrom = $(this).val() || '';
        if (challanState.periodFilter === 'custom') {
          challanState.table.draw();
        }
      });

      $customTo.on('change', function () {
        challanState.customTo = $(this).val() || '';
        if (challanState.periodFilter === 'custom') {
          challanState.table.draw();
        }
      });

      $printBtn.on('click', function () {
        openChallanOptions('print');
      });

      $excelBtn.on('click', function () {
        openChallanOptions('excel');
      });

      challanOptionsApply?.addEventListener('click', function () {
        const options = {
          item_details: Boolean(challanOptionItemDetails?.checked),
          description: Boolean(challanOptionDescription?.checked),
        };

        challanOptionsModal?.hide();

        if (challanState.pendingAction === 'print') {
          openChallanPreviewModal(options);
        } else if (challanState.pendingAction === 'excel') {
          exportVisibleChallansToExcel(options);
        }

        challanState.pendingAction = null;
      });

      challanPreviewOpenPdf?.addEventListener('click', function () {
      const url = challanPreviewFrame?.dataset?.reportPdfUrl || challanPreviewFrame?.dataset?.reportUrl;
      if (!url) return;
      openChallanPdf(url);
    });

      challanPreviewPrint?.addEventListener('click', function () {
        const printUrl = challanPreviewFrame?.dataset?.reportPrintUrl;
        if (printUrl) {
          window.open(printUrl, '_blank');
          return;
        }

        try {
          challanPreviewFrame?.contentWindow?.focus();
          challanPreviewFrame?.contentWindow?.print();
        } catch (error) {
          const fallbackUrl = challanPreviewFrame?.dataset?.reportPdfUrl || challanPreviewFrame?.dataset?.reportUrl;
          if (fallbackUrl) window.open(fallbackUrl, '_blank');
        }
      });

      challanPreviewSavePdf?.addEventListener('click', function () {
        const url = challanPreviewFrame?.dataset?.reportDownloadUrl || challanPreviewFrame?.dataset?.reportPdfUrl || challanPreviewFrame?.dataset?.reportUrl;
        if (!url) return;
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.rel = 'noopener';
        document.body.appendChild(a);
        a.click();
        a.remove();
      });

      challanPreviewEmailPdf?.addEventListener('click', function () {
        const rows = getFilteredChallanRows();
        const subject = `Delivery Challan Report - ${rows.length} entries`;
        const body = `Please find the delivery challan report attached/opened here: ${challanPreviewFrame?.dataset?.reportDownloadUrl || challanPreviewFrame?.dataset?.reportPdfUrl || window.location.href}`;
        openMailClient(subject, body);
      });

      challanPreviewModalEl?.addEventListener('hidden.bs.modal', function () {
        if (challanPreviewFrame) {
          challanPreviewFrame.src = 'about:blank';
          challanPreviewFrame.removeAttribute('data-report-url');
          challanPreviewFrame.removeAttribute('data-report-html');
          challanPreviewFrame.removeAttribute('data-report-pdf-url');
          challanPreviewFrame.removeAttribute('data-report-print-url');
          challanPreviewFrame.removeAttribute('data-report-download-url');
        }
        if (challanPreviewObjectUrl) {
          URL.revokeObjectURL(challanPreviewObjectUrl);
          challanPreviewObjectUrl = null;
        }
      });

      challanState.table.on('draw', function () {
        const visibleCount = challanState.table.rows({ filter: 'applied' }).count();
        const emptyRow = document.querySelector('#challanTable tbody tr td[colspan="8"]');
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
</body>

</html>

