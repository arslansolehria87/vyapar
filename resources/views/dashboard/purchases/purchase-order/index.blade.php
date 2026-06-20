<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar - Purchase Orders</title>
  <meta name="description" content="Manage purchase orders in Vyapar.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

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
<body data-page="purchase-orders">
  <main class="main-content" id="mainContent">
    <div class="d-flex justify-content-between align-items-center bg-light mb-2 p-4">
      <div>
        <h4 class="mb-0">Purchase Orders</h4>
      </div>
      <button class="btn btn-primary rounded-pill px-4" onclick="window.location.href='{{ route('purchase-order.create') }}'">
        <i class="fa-solid fa-circle-plus me-2"></i>Add Purchase Order
      </button>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <p class="fw-bold mb-0">Transactions</p>
          <form method="GET" action="{{ route('purchase-order') }}" class="d-flex align-items-center gap-2">
            <div class="input-group" style="max-width: 320px;">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search party / no."
                value="{{ $search }}"
              >
            </div>
            <button type="submit" class="btn btn-outline-secondary">Search</button>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0"
                 id="purchaseOrderTransactionsTable"
                 data-column-drag="native"
                 data-column-drag-storage="vyapar.purchase-order.transactions.column-order.v1">
            <thead>
              <tr>
                <th data-column-key="party">Party</th>
                <th data-column-key="number">No.</th>
                <th data-column-key="date">Date</th>
                <th data-column-key="due_date">Due Date</th>
                <th class="text-end" data-column-key="total">Total Amount</th>
                <th class="text-end" data-column-key="balance">Balance</th>
                <th data-column-key="type">Type</th>
                <th data-column-key="status">Status</th>
                <th data-column-key="action">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($purchaseOrders as $order)
                @php
                  $isCompleted = (float) $order->balance <= 0;
                  $isOverdue = !$isCompleted && $order->due_date && $order->due_date->isPast();
                  $statusLabel = $isCompleted ? 'Order Completed' : ($isOverdue ? 'Order Overdue' : 'Open');
                  $statusClass = $isCompleted ? 'success' : ($isOverdue ? 'warning' : 'primary');
                  $convertedPurchaseNumber = $convertedPurchaseBills[$order->id] ?? null;
                @endphp
                <tr>
                  <td>{{ $order->party?->name ?? $order->party_name ?? '-' }}</td>
                  <td>{{ $order->bill_number ?? $order->id }}</td>
                  <td>{{ optional($order->bill_date)->format('d/m/Y') }}</td>
                  <td>{{ optional($order->due_date)->format('d/m/Y') ?? '-' }}</td>
                  <td class="text-end">Rs {{ number_format((float) $order->grand_total, 2) }}</td>
                  <td class="text-end">Rs {{ number_format((float) $order->balance, 2) }}</td>
                  <td>Purchase Order</td>
                  <td><span class="text-{{ $statusClass }}">{{ $statusLabel }}</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      @if($convertedPurchaseNumber)
                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                          Converted to Purchase #{{ $convertedPurchaseNumber }}
                        </button>
                      @else
                        <a
                          href="{{ route('purchase-bill.create', ['source_purchase_order_id' => $order->id]) }}"
                          class="btn btn-sm btn-outline-primary"
                        >
                          Convert to Purchase
                        </a>
                      @endif
                      <div class="dropdown">
                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li><a class="dropdown-item" href="{{ route('purchase-orders.show', $order) }}">View</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-orders.edit', $order) }}">Edit</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-orders.preview', $order) }}" target="_blank">Preview</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-orders.pdf', $order) }}" target="_blank">Open PDF</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-orders.print', $order) }}" target="_blank">Print</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-orders.history', $order) }}">History</a></li>
                          <li>
                            <form method="POST" action="{{ route('purchase-orders.destroy', $order) }}" onsubmit="return confirm('Delete this purchase order?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="dropdown-item text-danger">Delete</button>
                            </form>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">
                    No purchase orders found yet.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/transaction-column-drag.js') }}"></script>
</body>
</html>
