<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Vyapar - Sale Return / Credit Notes</title>
  <meta name="description" content="Manage sale return and credit notes in Vyapar.">
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

  .custom-table thead th {
    font-size: 13px; color: #6c757d; font-weight: 500;
    border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 5;
    background-color: #fafafa; white-space: nowrap; position: relative;
  }
  .column-filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    position: relative;
    min-width: 0;
  }
  .column-filter-header > span {
    min-width: 0;
    flex: 1 1 auto;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .sale-return-sort-btn {
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    font: inherit;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
  }
  .sale-return-sort-btn:hover {
    color: #334155;
  }
  .sale-return-sort-btn > span {
    min-width: 0;
    flex: 1 1 auto;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .sale-return-sort-btn .sort-indicator {
    font-size: 11px;
    color: #94a3b8;
    flex: 0 0 auto;
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
    border-radius: 999px;
  }
  .filter-icon-btn:hover {
    color: #64748b;
    background: #f8fafc;
  }
  .column-filter-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    min-width: 280px;
    width: 320px;
    max-width: 360px;
    padding: 14px 16px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.16);
    z-index: 20;
  }
  .column-filter-dropdown.align-end {
    left: auto;
    right: 0;
  }
  .column-filter-dropdown.show { display: block; }
  .column-filter-dropdown .form-control,
  .column-filter-dropdown .form-select { font-size: 12px; }
  .column-filter-dropdown-options {
    min-width: 320px;
  }
  .column-filter-label {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 6px;
  }
  .column-filter-option-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding-right: 4px;
  }
  .column-filter-option-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #1f2937;
    user-select: none;
  }
  .column-filter-option-item input {
    width: 16px;
    height: 16px;
    margin: 0;
    flex: 0 0 auto;
  }
  .column-filter-actions {
    margin-top: 10px;
  }
  .column-filter-actions .btn {
    font-size: 12px;
    line-height: 1.1;
    border-radius: 999px;
    padding: 0.45rem 1rem;
    min-width: 64px;
  }
  .column-filter-actions .column-filter-clear {
    background: #f3f4f6;
    border-color: #f3f4f6;
    color: #6b7280;
  }
  .column-filter-actions .column-filter-clear:hover,
  .column-filter-actions .column-filter-clear:focus {
    background: #e5e7eb;
    border-color: #e5e7eb;
    color: #4b5563;
  }
  .column-filter-actions .column-filter-apply {
    background: #f43f5e;
    border-color: #f43f5e;
    color: #fff;
  }
  .column-filter-actions .column-filter-apply:hover,
  .column-filter-actions .column-filter-apply:focus {
    background: #e11d48;
    border-color: #e11d48;
    color: #fff;
    box-shadow: none;
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
  .sale-return-main-wrapper {
    overflow: visible !important;
    max-height: none !important;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    position: relative;
  }
  @media (max-width: 991px) {
    .table-wrapper { max-height: none; border-radius: 8px; }
    .custom-table thead th { font-size: 11px; padding: 8px 6px; }
    .custom-table tbody td { font-size: 12px; padding: 10px 6px; }
    .sale-return-table thead th { font-size: 11px; padding: 8px 6px !important; }
    .sale-return-table tbody td { font-size: 12px; padding: 10px 6px !important; }
  }
  @media (max-width: 575px) {
    .custom-table thead th { font-size: 10px; padding: 6px 4px; }
    .custom-table tbody td { font-size: 11px; padding: 8px 4px; }
    .sale-return-table thead th { font-size: 10px; padding: 6px 4px !important; }
    .sale-return-table tbody td { font-size: 11px; padding: 8px 4px !important; }
  }
</style>
  <style>
    .sale-return-page {
      padding: 1.25rem;
    }

    .sale-return-card {
      border: 0;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .sale-return-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.25rem;
      flex-wrap: wrap;
    }

    .sale-return-search {
      position: relative;
      min-width: 280px;
      max-width: 360px;
      width: 100%;
    }

    .sale-return-search i {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
    }

    .sale-return-search input {
      border-radius: 999px;
      border: 1px solid #d7deea;
      padding: 0.85rem 1rem 0.85rem 2.75rem;
      width: 100%;
      background: #fff;
    }

    .sale-return-add-btn {
      border-radius: 999px;
      background: #1d8cf8;
      border: 0;
      color: #fff;
      padding: 0.8rem 1.35rem;
      font-weight: 600;
      box-shadow: 0 10px 20px rgba(29, 140, 248, 0.18);
    }

    .sale-return-table {
      table-layout: fixed;
      min-width: 100%;
      border-collapse: collapse;
      width: 100%;
    }
    .sale-return-table thead th {
      position: relative;
      overflow: visible;
      background: #fafafa; color: #6c757d;
      font-size: 13px; font-weight: 500;
      border-bottom: 1px solid #eee;
      padding: 12px 10px !important;
      vertical-align: middle; white-space: nowrap;
    }
    .sale-return-table tbody td {
      padding: 14px 10px !important;
      border-bottom: 1px solid #f1f1f1;
      vertical-align: middle; color: #0f172a;
      white-space: nowrap;
      overflow: hidden; text-overflow: ellipsis;
    }
    .sale-return-table tbody td.action-cell {
      overflow: visible !important;
      position: relative;
    }
    .sale-return-table tbody td.action-menu-cell {
      overflow: visible !important;
      position: relative;
      text-align: center;
      white-space: nowrap;
    }
    .sale-return-table tbody tr:hover { background: #fafafa; }
    .sale-return-table th, .sale-return-table td { border-right: 1px solid #e9ecef !important; }
    .sale-return-table th:last-child, .sale-return-table td:last-child { border-right: none !important; }

    .status-pill {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: 0.38rem 0.8rem;
      font-size: 0.83rem;
      font-weight: 600;
    }

    .status-pill.paid {
      background: #e9f9ef;
      color: #16a34a;
    }

    .status-pill.partial {
      background: #eef4ff;
      color: #2563eb;
    }

    .status-pill.unpaid {
      background: #fff4e8;
      color: #f97316;
    }

    .icon-action {
      border: 0;
      background: transparent;
      color: #64748b;
      padding: 0.2rem 0.35rem;
      font-size: 1.1rem;
    }

    .action-menu-btn {
      border: 0;
      background: transparent;
      color: #64748b;
      padding: 0.2rem 0.35rem;
    }

    .action-menu-btn::after {
      display: none;
    }

    .sale-return-table tbody td.action-cell .dropdown-menu {
      min-width: 180px;
      padding: 0.45rem 0;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
      z-index: 1090;
    }

    .sale-return-table tbody td.action-cell .dropdown-item {
      padding: 0.6rem 1rem;
      font-size: 14px;
      color: #1f2937;
      text-decoration: none;
    }

    .sale-return-table tbody td.action-cell .dropdown-item:hover {
      background: #e0f2fe;
      color: #0f172a;
      font-weight: 700;
    }

    .sale-return-table tbody td.action-cell .dropdown-divider {
      margin: 0.35rem 0;
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

<body data-page="sales-return">
  <main class="main-content sale-return-page" id="mainContent">
    <div class="card sale-return-card">
      <div class="card-body">
        <div class="row g-2 mb-1">
          <p class="fw-bold mb-0">Transactions</p>
        </div>

        <div class="sale-return-toolbar">
          <form method="GET" action="{{ route('sale-return') }}" class="sale-return-search">
            <i class="bi bi-search"></i>
            <input type="text" name="search" placeholder="Search Transactions" value="{{ $search ?? '' }}">
          </form>

          <button class="btn sale-return-add-btn" onclick="window.location='{{ route('sale-return.create') }}'">
            <i class="fa-solid fa-plus me-2"></i>Add Credit Note
          </button>
        </div>

        <div class="table-responsive small-table table-wrapper sale-return-main-wrapper">
  @php
    $saleReturnTypeFilterOptions = ['Credit Note', 'Debit Note', 'Sale', 'Purchase', 'Payment-In', 'Payment-Out'];
    $saleReturnStatusFilterOptions = ['Paid', 'Partial', 'Unpaid'];
  @endphp
  <table class="table sale-return-table align-middle mb-0 txn-table"
         id="saleReturnTransactionsTable"
         data-column-drag="native"
         data-column-drag-storage="vyapar.sale-return.transactions.column-order.v1">
            <thead>
              <tr>
                <th data-column-key="row_number">#</th>
                <th data-column-key="date">
                  <div class="column-filter-header">
                    <span>Date</span>
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
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="1">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="1">Apply</button>
                    </div>
                  </div>
                </th>
                <th data-column-key="reference">
                  <div class="column-filter-header">
                    <span>Ref No.</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Ref No.">
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="2">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="2">Apply</button>
                    </div>
                  </div>
                </th>
                <th data-column-key="party">
                  <div class="column-filter-header">
                    <span>Party Name</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Party Name">
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="3">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="3">Apply</button>
                    </div>
                  </div>
                </th>
                <th data-column-key="type">
                  <div class="column-filter-header">
                    <span>Type</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown column-filter-dropdown-options">
                    <div class="column-filter-option-list">
                      @foreach($saleReturnTypeFilterOptions as $option)
                        <label class="column-filter-option-item">
                          <input type="checkbox" class="column-filter-checkbox" value="{{ $option }}">
                          <span>{{ $option }}</span>
                        </label>
                      @endforeach
                    </div>
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="4">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="4">Apply</button>
                    </div>
                  </div>
                </th>
                <th class="text-end" data-column-key="total">
                  <div class="column-filter-header justify-content-end">
                    <button type="button" class="sale-return-sort-btn" data-sort-column="5">
                      <span>Total</span>
                      <i class="fa-solid fa-sort sort-indicator"></i>
                    </button>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown align-end text-start">
                    <label class="column-filter-label">Select Category</label>
                    <select class="form-select form-select-sm column-filter-operator">
                      <option value="contains">Contains</option>
                      <option value="eq">Equal To</option>
                      <option value="gt">Greater Than</option>
                      <option value="lt">Less Than</option>
                    </select>
                    <label class="column-filter-label mt-2">Select Amount</label>
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Total" inputmode="decimal">
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="5">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="5">Apply</button>
                    </div>
                  </div>
                </th>
                <th class="text-end" data-column-key="received">
                  <div class="column-filter-header justify-content-end">
                    <span class="me-2">Received/Paid</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown align-end text-start">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Received/Paid">
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="6">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="6">Apply</button>
                    </div>
                  </div>
                </th>
                <th class="text-end" data-column-key="balance">
                  <div class="column-filter-header justify-content-end">
                    <span class="me-2">Balance</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown align-end text-start">
                    <input type="text" class="form-control form-control-sm column-filter-input" placeholder="Filter Balance">
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="7">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="7">Apply</button>
                    </div>
                  </div>
                </th>
                <th data-column-key="status">
                  <div class="column-filter-header">
                    <span>Status</span>
                    <button class="filter-icon-btn" type="button"><i class="fa-solid fa-filter"></i></button>
                  </div>
                  <div class="column-filter-dropdown column-filter-dropdown-options">
                    <div class="column-filter-option-list">
                      @foreach($saleReturnStatusFilterOptions as $option)
                        <label class="column-filter-option-item">
                          <input type="checkbox" class="column-filter-checkbox" value="{{ $option }}">
                          <span>{{ $option }}</span>
                        </label>
                      @endforeach
                    </div>
                    <div class="d-flex justify-content-end gap-2 column-filter-actions">
                      <button class="btn btn-sm btn-outline-secondary column-filter-clear" data-column-index="8">Clear</button>
                      <button class="btn btn-sm btn-primary column-filter-apply" data-column-index="8">Apply</button>
                    </div>
                  </div>
                </th>
                <th style="width: 110px;" data-column-key="print_share">Print / Share</th>
                <th style="width: 56px;" data-column-key="action">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($saleReturns as $index => $saleReturn)
                @php
                  $status = strtolower((string) ($saleReturn->status ?? 'unpaid'));
                  $statusClass = match ($status) {
                      'paid' => 'paid',
                      'partial' => 'partial',
                      default => 'unpaid',
                  };
                @endphp
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ optional($saleReturn->order_date ?? $saleReturn->invoice_date)->format('d/m/Y') ?? '-' }}</td>
                  <td>{{ $saleReturn->bill_number ?? '-' }}</td>
                  <td>{{ $saleReturn->display_party_name }}</td>
                  <td>Credit Note</td>
                  <td class="text-end">Rs {{ number_format($saleReturn->grand_total ?? 0, 2) }}</td>
                  <td class="text-end">Rs {{ number_format($saleReturn->received_amount ?? 0, 2) }}</td>
                  <td class="text-end">Rs {{ number_format($saleReturn->balance ?? 0, 2) }}</td>
                  <td>
                    <span class="status-pill {{ $statusClass }}">{{ ucfirst($status) }}</span>
                  </td>
                  <td class="action-cell" style="width:110px;">
                    <a href="#" onclick="openSaleReturnPrint('{{ route('invoice', ['sale_id' => $saleReturn->id, 'type' => 'return-order', 'print' => 1]) }}'); return false;" class="icon-action" title="Print">
                      <i class="fa-solid fa-print"></i>
                    </a>
                    <div class="dropdown d-inline">
                      <button class="icon-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Share">
                        <i class="fa-solid fa-share-nodes"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="shareSaleReturn('whatsapp', '{{ route('invoice', ['sale_id' => $saleReturn->id, 'type' => 'return-order']) }}'); return false;"><i class="fa-brands fa-whatsapp me-2"></i>WhatsApp</a></li>
                        <li><a class="dropdown-item" href="#" onclick="shareSaleReturn('gmail', '{{ route('invoice', ['sale_id' => $saleReturn->id, 'type' => 'return-order']) }}'); return false;"><i class="fa-solid fa-envelope me-2"></i>Gmail</a></li>
                        <li><a class="dropdown-item" href="#" onclick="shareSaleReturn('copy', '{{ route('invoice', ['sale_id' => $saleReturn->id, 'type' => 'return-order']) }}'); return false;"><i class="fa-regular fa-copy me-2"></i>Copy Link</a></li>
                      </ul>
                    </div>
                  </td>
                  <td class="action-menu-cell" style="width:56px;">
                    <div class="dropdown">
                      <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#" onclick="return transactionPasscodeNavigate('{{ route('sale-return.edit', $saleReturn->id) }}');"><i class="fas fa-edit me-2"></i>View/Edit</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openSaleReturnPdf('{{ route('sale-return.pdf', $saleReturn->id) }}'); return false;"><i class="fas fa-file-pdf me-2"></i>Open PDF</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openSaleReturnPrint('{{ route('invoice', ['sale_id' => $saleReturn->id, 'type' => 'return-order', 'print' => 1]) }}'); return false;"><i class="fas fa-print me-2"></i>Print</a></li>
                        <li><a class="dropdown-item" href="#" onclick="duplicateSaleReturn('{{ route('sale-return.duplicate', $saleReturn->id) }}'); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                        <li><a class="dropdown-item" href="#" onclick="viewSaleReturnHistory('{{ route('sale-return.bank-history', $saleReturn->id) }}'); return false;"><i class="fas fa-clock-rotate-left me-2"></i>View History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="return transactionPasscodeExecute('deleteSaleReturn','{{ route('sale-return.destroy', $saleReturn->id) }}');"><i class="fas fa-trash me-2"></i>Delete</a></li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="12" class="text-center text-muted py-5">No credit notes found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="saleReturnHistoryModal" tabindex="-1" aria-labelledby="saleReturnHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="saleReturnHistoryLabel">View History</h5>
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
              <tbody id="saleReturnHistoryBody">
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

  @include('dashboard.partials.transaction-passcode-guard')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script>
    const saleReturnCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function openSaleReturnPdf(url) {
      window.open(url, '_blank');
    }

    function openSaleReturnPreview(url) {
      window.open(url, '_blank');
    }

    function openSaleReturnPrint(url) {
      window.open(url, '_blank');
    }

    function duplicateSaleReturn(url) {
      window.open(url, '_blank');
    }

    function shareSaleReturn(channel, url) {
      const encoded = encodeURIComponent(url);
      if (channel === 'whatsapp') {
        window.open(`https://wa.me/?text=${encoded}`, '_blank');
        return;
      }

      if (channel === 'gmail') {
        window.open(`https://mail.google.com/mail/?view=cm&fs=1&su=Sale%20Return&body=${encoded}`, '_blank');
        return;
      }

      if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => alert('Link copied to clipboard.'));
      } else {
        window.prompt('Copy this link:', url);
      }
    }

    function viewSaleReturnHistory(historyUrl) {
      const modalEl = document.getElementById('saleReturnHistoryModal');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      const tbody = document.getElementById('saleReturnHistoryBody');
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>`;

      fetch(historyUrl, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': saleReturnCsrf,
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

    function deleteSaleReturn(url) {
      if (!confirm('Are you sure you want to delete this credit note?')) {
        return;
      }

      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': saleReturnCsrf,
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
          alert(error.message || 'Unable to delete credit note.');
        });
    }
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.querySelector('.sale-return-search input');
      const columnFilters = {};
      const dateFilterColumns = new Set(['date']);
      const multiSelectFilterColumns = new Set(['type', 'status']);
      const numericFilterColumns = new Set(['total', 'received', 'balance']);
      const totalSortButton = document.querySelector('.sale-return-sort-btn[data-sort-column="5"]');
      let totalSortDirection = null;

      function normalizeText(text) {
        return String(text || '').toLowerCase().trim().replace(/\s+/g, ' ');
      }

      function parseDateText(text) {
        const match = String(text || '').match(/(\d{2})\/(\d{2})\/(\d{4})/);
        if (!match) return null;

        const [, dd, mm, yyyy] = match;
        return new Date(Number(yyyy), Number(mm) - 1, Number(dd));
      }

      function matchesDateFilter(cellText, filter) {
        const cellDate = parseDateText(cellText);
        const filterDate = parseDateText(filter.value);

        if (!cellDate || !filterDate) return false;

        const cellTime = cellDate.setHours(0, 0, 0, 0);
        const filterTime = filterDate.setHours(0, 0, 0, 0);

        if (filter.operator === 'before') return cellTime < filterTime;
        if (filter.operator === 'after') return cellTime > filterTime;
        return cellTime === filterTime;
      }

      function parseNumericText(text) {
        const normalized = normalizeText(text).replace(/[^0-9.-]/g, '');
        const value = Number.parseFloat(normalized);
        return Number.isNaN(value) ? null : value;
      }

      function normalizeOptionValue(value) {
        return normalizeText(value).replace(/\s+/g, ' ');
      }

      function getCheckedValues(dropdown) {
        return Array.from(dropdown?.querySelectorAll('.column-filter-checkbox:checked') || [])
          .map((checkbox) => checkbox.value)
          .filter(Boolean);
      }

      function setDropdownCheckboxes(dropdown, values) {
        const normalized = new Set((values || []).map(normalizeOptionValue));
        dropdown?.querySelectorAll('.column-filter-checkbox').forEach((checkbox) => {
          checkbox.checked = normalized.has(normalizeOptionValue(checkbox.value));
        });
      }

      function setColumnFilter(columnKey, dropdown) {
        const normalizedValue = normalizeText(dropdown?.querySelector('.column-filter-input')?.value || '');
        const operator = dropdown?.querySelector('.column-filter-operator')?.value || 'eq';

        if (dateFilterColumns.has(columnKey)) {
          if (normalizedValue) {
            columnFilters[columnKey] = { type: 'date', operator, value: normalizedValue };
          } else {
            delete columnFilters[columnKey];
          }
          return;
        }

        if (multiSelectFilterColumns.has(columnKey)) {
          const values = getCheckedValues(dropdown);
          if (values.length) {
            columnFilters[columnKey] = { type: 'multi', values };
          } else {
            delete columnFilters[columnKey];
          }
          return;
        }

        if (numericFilterColumns.has(columnKey)) {
          if (normalizedValue) {
            columnFilters[columnKey] = {
              type: 'number',
              operator,
              value: normalizedValue,
            };
          } else {
            delete columnFilters[columnKey];
          }
          return;
        }

        if (normalizedValue) {
          columnFilters[columnKey] = normalizedValue;
        } else {
          delete columnFilters[columnKey];
        }
      }

      function applySaleReturnTableFilters() {
        const rows = document.querySelectorAll('.txn-table tbody tr');
        const universalSearchQuery = searchInput ? normalizeText(searchInput.value) : '';

        rows.forEach((row) => {
          if (row.cells.length === 1) return;

          const matchesUniversal = !universalSearchQuery
            || normalizeText(row.textContent || '').includes(universalSearchQuery);
          const matchesColumnFilters = Object.entries(columnFilters).every(([columnKey, filterValue]) => {
            const header = document.querySelector(`.sale-return-table thead th[data-column-key="${columnKey}"]`);
            const cell = header ? row.cells[header.cellIndex] : null;
            const cellText = normalizeText(cell?.textContent || '');

            if (!cell) return true;

            if (dateFilterColumns.has(columnKey)) {
              return matchesDateFilter(cell.textContent || '', filterValue);
            }

            if (filterValue && typeof filterValue === 'object' && filterValue.type === 'number') {
                const cellNumber = parseNumericText(cell.textContent || '');
                const filterNumber = parseNumericText(filterValue.value);
                if (cellNumber === null || filterNumber === null) return false;
                if (filterValue.operator === 'contains') {
                  return cellText.includes(normalizeText(filterValue.value));
                }
                if (filterValue.operator === 'gt') return cellNumber > filterNumber;
                if (filterValue.operator === 'lt') return cellNumber < filterNumber;
                return cellNumber === filterNumber;
            }

            if (filterValue && typeof filterValue === 'object' && filterValue.type === 'multi') {
                const normalizedCell = normalizeOptionValue(cell.textContent || '');
                const selectedValues = (filterValue.values || []).map(normalizeOptionValue);
                return selectedValues.some((value) => normalizedCell.includes(value));
            }

            return cellText.includes(filterValue);
          });

          row.style.display = (matchesUniversal && matchesColumnFilters) ? '' : 'none';
        });
      }

      function applyTotalSort() {
        if (!totalSortDirection) return;

        const tbody = document.querySelector('.txn-table tbody');
        if (!tbody) return;

        const totalHeader = document.querySelector('.sale-return-table thead th[data-column-key="total"]');
        const totalColumnIndex = totalHeader?.cellIndex ?? -1;
        const rows = Array.from(tbody.querySelectorAll('tr')).filter((row) => row.cells.length > 1);
        rows.sort((a, b) => {
          const aValue = parseNumericText(a.cells[totalColumnIndex]?.textContent || '');
          const bValue = parseNumericText(b.cells[totalColumnIndex]?.textContent || '');
          const aNum = aValue === null ? 0 : aValue;
          const bNum = bValue === null ? 0 : bValue;
          return totalSortDirection === 'asc' ? aNum - bNum : bNum - aNum;
        });

        rows.forEach((row) => tbody.appendChild(row));

        if (totalSortButton) {
          const icon = totalSortButton.querySelector('.sort-indicator');
          if (icon) {
            icon.classList.remove('fa-sort', 'fa-sort-up', 'fa-sort-down');
            icon.classList.add(totalSortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
          }
        }
      }

      document.querySelectorAll('.filter-icon-btn').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          const th = this.closest('th');
          const dropdown = this.closest('.column-filter-header')?.nextElementSibling;
          if (!dropdown) return;

          document.querySelectorAll('.column-filter-dropdown.show').forEach((openDropdown) => {
            if (openDropdown !== dropdown) {
              openDropdown.classList.remove('show');
              openDropdown.removeAttribute('style');
            }
          });

          const rect = th ? th.getBoundingClientRect() : null;
          if (rect) {
            const availableRight = window.innerWidth - rect.left;
            const needsRightAlign = availableRight < 340;
            dropdown.style.left = needsRightAlign ? 'auto' : '0';
            dropdown.style.right = needsRightAlign ? '0' : 'auto';
          }

          const currentFilterButton = dropdown.querySelector('.column-filter-apply');
          const columnKey = currentFilterButton?.closest('th')?.dataset.columnKey;
          const currentValue = columnFilters[columnKey];
          const input = dropdown.querySelector('.column-filter-input');
          const operatorSelect = dropdown.querySelector('.column-filter-operator');

          if (input && typeof currentValue === 'object' && (currentValue?.type === 'date' || currentValue?.type === 'number')) {
            input.value = currentValue.value || '';
            if (operatorSelect) operatorSelect.value = currentValue.operator || 'eq';
          } else if (input) {
            input.value = typeof currentValue === 'string' ? currentValue : '';
            if (operatorSelect) operatorSelect.value = 'eq';
          }

          if (currentValue && typeof currentValue === 'object' && currentValue.type === 'multi') {
            setDropdownCheckboxes(dropdown, currentValue.values || []);
          } else if (dropdown.querySelector('.column-filter-checkbox')) {
            setDropdownCheckboxes(dropdown, []);
          }

          dropdown.classList.add('show');
        });
      });

      totalSortButton?.addEventListener('click', function () {
        totalSortDirection = totalSortDirection === 'asc' ? 'desc' : 'asc';
        applyTotalSort();
        applySaleReturnTableFilters();
      });

      document.querySelectorAll('.column-filter-apply').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          const columnKey = this.closest('th')?.dataset.columnKey;
          const dropdown = this.closest('.column-filter-dropdown');
          if (!columnKey) return;
          setColumnFilter(columnKey, dropdown);

          dropdown?.classList.remove('show');
          applySaleReturnTableFilters();
        });
      });

      document.querySelectorAll('.column-filter-input').forEach((input) => {
        input.addEventListener('input', function () {
          const dropdown = this.closest('.column-filter-dropdown');
          const applyButton = dropdown?.querySelector('.column-filter-apply');
          const columnKey = applyButton?.closest('th')?.dataset.columnKey;

          if (!columnKey) return;
          setColumnFilter(columnKey, dropdown);

          applySaleReturnTableFilters();
        });
      });

      document.querySelectorAll('.column-filter-operator').forEach((select) => {
        select.addEventListener('change', function () {
          const dropdown = this.closest('.column-filter-dropdown');
          const applyButton = dropdown?.querySelector('.column-filter-apply');
          const columnKey = applyButton?.closest('th')?.dataset.columnKey;
          const input = dropdown?.querySelector('.column-filter-input');

          if (!dateFilterColumns.has(columnKey) && !numericFilterColumns.has(columnKey)) return;

          const normalizedValue = normalizeText(input?.value || '');
          const type = dateFilterColumns.has(columnKey) ? 'date' : 'number';
          if (normalizedValue) {
            columnFilters[columnKey] = { type, operator: this.value || 'eq', value: normalizedValue };
          } else {
            delete columnFilters[columnKey];
          }
          applySaleReturnTableFilters();
        });
      });

      document.querySelectorAll('.column-filter-clear').forEach((button) => {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          const columnKey = this.closest('th')?.dataset.columnKey;
          const dropdown = this.closest('.column-filter-dropdown');
          const input = dropdown?.querySelector('.column-filter-input');
          const operator = dropdown?.querySelector('.column-filter-operator');

          if (input) input.value = '';
          if (operator) operator.value = 'eq';
          dropdown?.querySelectorAll('.column-filter-checkbox').forEach((checkbox) => {
            checkbox.checked = false;
          });
          if (columnKey) delete columnFilters[columnKey];
          dropdown?.classList.remove('show');
          applySaleReturnTableFilters();
        });
      });

      searchInput?.addEventListener('input', applySaleReturnTableFilters);

      document.addEventListener('click', function (event) {
        if (!event.target.closest('.column-filter-dropdown') && !event.target.closest('.filter-icon-btn')) {
          document.querySelectorAll('.column-filter-dropdown.show').forEach((dropdown) => {
            dropdown.classList.remove('show');
            dropdown.removeAttribute('style');
          });
        }
      });

      applySaleReturnTableFilters();
      applyTotalSort();
    });
  </script>
  <script>
  (function () {
    var isResizing = false, startX = 0, startW = 0, thEl = null;
    function initResizeHandles() {
      document.querySelectorAll('.custom-table thead th, .sale-return-table thead th').forEach(function (th) {
        if (th.querySelector('.col-rh')) return;
        th.style.position = 'relative';
        th.style.overflow = 'visible';
        th.style.width = th.getBoundingClientRect().width + 'px';

        var handle = document.createElement('div');
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
      var widthCalc = Math.max(60, startW + (e.clientX - startX));
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
  })();
  </script>
  <script src="{{ asset('js/transaction-column-drag.js') }}"></script>
</body>

</html>
