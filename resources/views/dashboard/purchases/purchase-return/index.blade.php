<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Vyapar - Purchase Return / Debit Notes</title>
  <meta name="description" content="Manage purchase return and debit notes in Vyapar.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
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
    overflow-x: hidden; overflow-y: auto;
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
    .purchase-return-page { padding: 1.25rem; }
    .purchase-return-card { border: 0; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); }
    .purchase-return-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
    .purchase-return-search { position: relative; min-width: 280px; max-width: 360px; width: 100%; }
    .purchase-return-search i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; }
    .purchase-return-search input { border-radius: 999px; border: 1px solid #d7deea; padding: 0.85rem 1rem 0.85rem 2.75rem; width: 100%; background: #fff; }
    .purchase-return-add-btn { border-radius: 999px; background: #1d8cf8; border: 0; color: #fff; padding: 0.8rem 1.35rem; font-weight: 600; box-shadow: 0 10px 20px rgba(29, 140, 248, 0.18); }
    .purchase-return-table { width: 100%; min-width: 0; table-layout: fixed; }
    .purchase-return-table th:nth-child(1),
    .purchase-return-table td:nth-child(1) { width: 5%; }
    .purchase-return-table th:nth-child(2),
    .purchase-return-table td:nth-child(2) { width: 10%; }
    .purchase-return-table th:nth-child(3),
    .purchase-return-table td:nth-child(3) { width: 17%; }
    .purchase-return-table th:nth-child(4),
    .purchase-return-table td:nth-child(4) { width: 13%; }
    .purchase-return-table th:nth-child(5),
    .purchase-return-table td:nth-child(5),
    .purchase-return-table th:nth-child(6),
    .purchase-return-table td:nth-child(6),
    .purchase-return-table th:nth-child(7),
    .purchase-return-table td:nth-child(7) { width: 12%; }
    .purchase-return-table th:nth-child(8),
    .purchase-return-table td:nth-child(8) { width: 12%; }
    .purchase-return-table th:nth-child(9),
    .purchase-return-table td:nth-child(9) { width: 5%; }
    .purchase-return-table th,
    .purchase-return-table td {
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .purchase-return-table thead th { background: #f8fbff; color: #334155; font-size: 0.92rem; font-weight: 700; border-bottom: 1px solid #dbe4f0; padding: 1rem 0.85rem; vertical-align: middle; white-space: nowrap; }
    .purchase-return-table tbody td { padding: 1rem 0.85rem; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: #0f172a; white-space: nowrap; }
    .purchase-return-table tbody tr:hover { background: #f8fbff; }
    .status-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: 0.38rem 0.8rem; font-size: 0.83rem; font-weight: 600; }
    .status-pill.paid { background: #e9f9ef; color: #16a34a; }
    .status-pill.partial { background: #eef4ff; color: #2563eb; }
    .status-pill.unpaid { background: #fff4e8; color: #f97316; }
    .icon-action, .action-menu-btn { border: 0; background: transparent; color: #64748b; padding: 0.2rem 0.35rem; font-size: 1.1rem; }
    .action-menu-btn::after { display: none; }
    .purchase-return-table tbody td.action-cell,
    .purchase-return-table tbody td.action-menu-cell {
      overflow: visible !important;
      position: relative;
    }
    .purchase-return-table .dropdown-menu {
      min-width: 188px;
      padding: 0.45rem 0;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 14px 30px rgba(15, 23, 42, 0.16);
      z-index: 1090;
    }
    .purchase-return-table .dropdown-item {
      padding: 0.6rem 1rem;
      font-size: 14px;
      color: #1f2937;
      text-decoration: none;
    }
    .purchase-return-table .dropdown-item:hover {
      background: #e0f2fe;
      color: #0f172a;
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

<body data-page="purchase-return">
  <main class="main-content purchase-return-page" id="mainContent">
    <div class="card purchase-return-card">
      <div class="card-body">
        <div class="row g-2 mb-1">
          <p class="fw-bold mb-0">Transactions</p>
        </div>

        <div class="purchase-return-toolbar">
          <form method="GET" action="{{ route('purchase-return') }}" class="purchase-return-search">
            <i class="bi bi-search"></i>
            <input type="text" name="search" placeholder="Search Transactions" value="{{ $search ?? '' }}">
          </form>

          <button class="btn purchase-return-add-btn" onclick="window.location='{{ route('purchase-return.create') }}'">
            <i class="fa-solid fa-plus me-2"></i>Add Debit Note
          </button>
        </div>

       <div class="table-wrapper">
  <table class="table align-middle custom-table purchase-return-table mb-0">
            <thead>
              <tr>
                <th>DATE</th>
                <th>REF NO.</th>
                <th>PARTY NAME</th>
                <th>TYPE</th>
                <th class="text-end">TOTAL</th>
                <th class="text-end">RECEIVE...</th>
                <th class="text-end">BALANCE</th>
                <th>PRINT / ...</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($purchaseReturns as $index => $purchaseReturn)
                @php
                  $status = strtolower((string) ($purchaseReturn->status ?? 'unpaid'));
                  $statusClass = match ($status) {
                      'paid' => 'paid',
                      'partial' => 'partial',
                      default => 'unpaid',
                  };
                @endphp
                <tr>
                  <td>{{ optional($purchaseReturn->bill_date)->format('d/m/Y') ?? '-' }}</td>
                  <td>{{ $purchaseReturn->bill_number ?? '-' }}</td>
                  <td>{{ $purchaseReturn->party_name ?: ($purchaseReturn->party?->name ?? '-') }}</td>
                  <td>Debit Note</td>
                  <td class="text-end">Rs {{ number_format($purchaseReturn->grand_total ?? 0, 2) }}</td>
                  <td class="text-end">Rs {{ number_format($purchaseReturn->paid_amount ?? 0, 2) }}</td>
                  <td class="text-end">Rs {{ number_format($purchaseReturn->grand_total ?? 0, 2) }}</td>
                  <td class="action-cell">
                    <a href="#" onclick="openPurchaseReturnPrint('{{ route('invoice', ['purchase_id' => $purchaseReturn->id, 'type' => 'purchase-return', 'print' => 1]) }}', {{ \Illuminate\Support\Js::from($purchaseReturn->invoice_theme) }}); return false;" class="icon-action" title="Print"><i class="fa-solid fa-print"></i></a>
                    <a href="#" onclick="openPurchaseReturnPreview('{{ route('invoice', ['purchase_id' => $purchaseReturn->id, 'type' => 'purchase-return']) }}', {{ \Illuminate\Support\Js::from($purchaseReturn->invoice_theme) }}); return false;" class="icon-action" title="Preview"><i class="fa-solid fa-share-nodes"></i></a>
                  </td>
                  <td class="text-center action-menu-cell">
                    <div class="dropdown">
                      <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#" onclick="return transactionPasscodeNavigate('{{ route('purchase-return.edit', $purchaseReturn->id) }}');"><i class="fas fa-edit me-2"></i>View/Edit</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openPurchaseReturnPdf('{{ route('purchase-return.pdf', $purchaseReturn->id) }}', {{ \Illuminate\Support\Js::from($purchaseReturn->invoice_theme) }}); return false;"><i class="fas fa-file-pdf me-2"></i>Open PDF</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openPurchaseReturnPreview('{{ route('invoice', ['purchase_id' => $purchaseReturn->id, 'type' => 'purchase-return']) }}', {{ \Illuminate\Support\Js::from($purchaseReturn->invoice_theme) }}); return false;"><i class="fas fa-file-alt me-2"></i>Preview</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openPurchaseReturnPrint('{{ route('invoice', ['purchase_id' => $purchaseReturn->id, 'type' => 'purchase-return', 'print' => 1]) }}', {{ \Illuminate\Support\Js::from($purchaseReturn->invoice_theme) }}); return false;"><i class="fas fa-print me-2"></i>Print</a></li>
                        <li><a class="dropdown-item" href="#" onclick="duplicatePurchaseReturn('{{ route('purchase-return.duplicate', $purchaseReturn->id) }}'); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="return transactionPasscodeExecute('deletePurchaseReturn','{{ route('purchase-return.destroy', $purchaseReturn->id) }}');"><i class="fas fa-trash me-2"></i>Delete</a></li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-5">No debit notes found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  @include('dashboard.partials.transaction-passcode-guard')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script>
    function applyPurchaseReturnThemeToUrl(resolvedUrl, savedTheme) {
      if (!savedTheme) {
        return;
      }

      resolvedUrl.searchParams.set('mode', savedTheme.mode || 'regular');
      if (savedTheme.theme) {
        resolvedUrl.searchParams.set('theme', savedTheme.theme);
      }
      if (savedTheme.mode === 'thermal' && savedTheme.thermalThemeId) {
        resolvedUrl.searchParams.set('theme_id', savedTheme.thermalThemeId);
      } else if (savedTheme.regularThemeId) {
        resolvedUrl.searchParams.set('theme_id', savedTheme.regularThemeId);
      }
      if (savedTheme.accent) {
        resolvedUrl.searchParams.set('accent', savedTheme.accent);
        resolvedUrl.searchParams.set('color', savedTheme.accent);
      }
      if (savedTheme.accent2) {
        resolvedUrl.searchParams.set('accent2', savedTheme.accent2);
        resolvedUrl.searchParams.set('color2', savedTheme.accent2);
      }
    }

    function buildPurchaseReturnThemedUrl(url, rowTheme) {
      var resolvedUrl = new URL(url, window.location.origin);
      var purchaseId = resolvedUrl.searchParams.get('purchase_id');

      if (!purchaseId) {
        var match = resolvedUrl.pathname.match(/\/purchase-return\/(\d+)(?:\/|$)/);
        purchaseId = match ? match[1] : '';
      }

      if (!purchaseId) {
        return resolvedUrl.toString();
      }

      try {
        var savedTheme = rowTheme || JSON.parse(
          window.localStorage.getItem('purchaseInvoiceTheme:' + purchaseId)
          || 'null'
        );
        applyPurchaseReturnThemeToUrl(resolvedUrl, savedTheme);
      } catch (error) {}

      return resolvedUrl.toString();
    }

    function openPurchaseReturnPdf(url, rowTheme) {
      window.open(buildPurchaseReturnThemedUrl(url, rowTheme), '_blank');
    }

    function openPurchaseReturnPreview(url, rowTheme) {
      window.open(buildPurchaseReturnThemedUrl(url, rowTheme), '_blank');
    }

    function openPurchaseReturnPrint(url, rowTheme) {
      window.open(buildPurchaseReturnThemedUrl(url, rowTheme), '_blank');
    }

    function duplicatePurchaseReturn(url) {
      window.open(url, '_blank');
    }

    function deletePurchaseReturn(url) {
      if (!confirm('Are you sure you want to delete this debit note?')) {
        return;
      }

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
          alert(error.message || 'Unable to delete debit note.');
        });
    }
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
