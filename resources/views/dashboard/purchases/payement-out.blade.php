<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar - Payment Out</title>
  <meta name="description" content="Track supplier payments and payment out transactions in Vyapar.">

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

  <style>
    .filter-pill {
      background-color: #e4f2ff;
      border-radius: 999px;
      display: flex;
      align-items: center;
      min-height: 38px;
      padding: 0 10px;
      gap: 10px;
    }

    .filter-pill select,
    .filter-pill input {
      border: none;
      background: transparent;
      outline: none;
      font-size: 13px;
    }

    .payment-out-row td {
      vertical-align: middle;
    }

    .summary-card {
      width: 25rem;
      min-height: 8rem;
      background: #fcf8ff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 14px 16px;
    }

    .payment-out-empty {
      padding: 36px 12px;
      text-align: center;
      color: #6b7280;
    }

    .table-clean > :not(caption) > * > * {
      white-space: nowrap;
    }
  </style>
</head>

<body data-page="payment-out">
  <main class="main-content" id="mainContent">
    @if (session('success'))
      <div class="alert alert-success mb-2">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger mb-2">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center bg-light mb-2 p-4">
      <div class="dropdown">
        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="h4 mb-0">Payment Out</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('sale.index') }}">Sale Invoice</a></li>
          <li><a class="dropdown-item" href="{{ route('sale.estimate') }}">Estimate / Quotation</a></li>
          <li><a class="dropdown-item" href="{{ route('sale-return') }}">Sale Return / Cr. Note</a></li>
          <li><a class="dropdown-item" href="{{ route('payment-in') }}">Payment In</a></li>
          <li><a class="dropdown-item" href="{{ route('payment-out') }}">Payment Out</a></li>
          <li><a class="dropdown-item" href="{{ route('purchase-expenses') }}">Purchase Bill</a></li>
          <li><a class="dropdown-item" href="{{ route('purchase-return') }}">Purchase Return / Dr. Note</a></li>
          <li><a class="dropdown-item" href="{{ route('expense') }}">Expenses</a></li>
        </ul>
      </div>

      <button type="button" class="btn rounded-pill" style="background-color:#D4112E;" data-bs-toggle="modal" data-bs-target="#addPaymentOutModal">
        <span class="text-light">+ Add Payment-out</span>
      </button>
    </div>

    <div class="d-flex justify-content-between align-items-center bg-light mb-2 px-3 py-2 rounded">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="small fw-semibold">Filter By:</span>

        <div class="filter-pill">
          <select id="paymentOutPeriodFilter">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="this_month" selected>This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_year">This Year</option>
          </select>
          <span id="paymentOutDateRangeLabel" class="small text-secondary"></span>
        </div>

        <div class="filter-pill">
          <i class="fa-solid fa-building text-secondary"></i>
          <select id="paymentOutFirmFilter">
            <option value="">All Parties</option>
            @foreach($paymentOuts->map(fn ($payment) => $payment->purchase?->party?->name ?: $payment->purchase?->party_name)->filter()->unique()->values() as $partyName)
              <option value="{{ $partyName }}">{{ $partyName }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <input type="text" id="paymentOutSearchInput" class="form-control rounded-pill" placeholder="Search payment out..." style="width: 260px;">
      </div>
    </div>

    <div class="bg-light mb-2 px-4 py-3 rounded">
      <div class="summary-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary m-0">Total Amount</p>
            <p class="h4 mb-0" id="paymentOutTotalAmount">Rs {{ number_format($paymentOuts->sum('amount'), 2) }}</p>
          </div>
          <div class="text-end">
            <div class="rounded-pill px-3 py-1 text-primary bg-primary-subtle small fw-semibold">
              <span id="paymentOutTotalCount">{{ $paymentOuts->count() }}</span> Entries
            </div>
            <span class="d-block mt-2 text-secondary" style="font-size: 10px;">visible payment-out transactions</span>
          </div>
        </div>

        <div class="mt-3 d-flex gap-4 flex-wrap">
          <p class="mb-0 text-secondary">Paid Out:
            <span class="fw-bold text-dark" id="paymentOutPaidAmount">Rs {{ number_format($paymentOuts->sum('amount'), 2) }}</span>
          </p>
          <p class="mb-0 text-secondary">Linked Bills:
            <span class="fw-bold text-dark" id="paymentOutLinkedBills">{{ $paymentOuts->filter(fn ($payment) => $payment->purchase)->count() }}</span>
          </p>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="row g-2 mb-3">
          <p class="fw-bold mb-0">Transactions</p>
        </div>

        <div class="table-wrapper">
  <table class="table align-middle custom-table mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Reference No.</th>
                <th>Party Name</th>
                <th>Amount</th>
                <th>Bank</th>
                <th>Payment Type</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($paymentOuts as $paymentOut)
                @php
                  $purchase = $paymentOut->purchase;
                  $partyName = $purchase?->party?->name ?: ($purchase?->party_name ?: '-');
                  $dateValue = optional($purchase?->bill_date)->format('Y-m-d')
                      ?: optional($paymentOut->created_at)->format('Y-m-d')
                      ?: now()->format('Y-m-d');
                  $bankName = $paymentOut->bankAccount?->display_name
                      ?? $paymentOut->bankAccount?->bank_name
                      ?? strtoupper((string) $paymentOut->payment_type);
                  $linkedTransaction = $paymentOut->receipt_no ? ($transactionMap[$paymentOut->receipt_no] ?? null) : null;
                  $historyPayload = [
                    'entry_no' => $linkedTransaction?->id ?? $paymentOut->id,
                    'payment_id' => $paymentOut->id,
                    'purchase_id' => $purchase?->id,
                    'bill_number' => $purchase?->bill_number ?? '-',
                    'party_name' => $partyName,
                    'amount' => (float) ($paymentOut->amount ?? 0),
                    'reference' => $paymentOut->reference ?? '-',
                    'receipt' => $paymentOut->receipt_no ?? '-',
                    'payment_type' => ucfirst((string) ($paymentOut->payment_type ?? '-')),
                    'bank_name' => $bankName ?: '-',
                    'created_at' => optional($paymentOut->created_at)->format('Y-m-d H:i:s') ?? '-',
                    'updated_at' => optional($paymentOut->updated_at)->format('Y-m-d H:i:s') ?? '-',
                    'user_name' => auth()->user()->name ?? 'System User',
                    'action' => 'Payment Out Recorded',
                    'description' => 'Supplier payment linked with purchase bill ' . ($purchase?->bill_number ?? '-'),
                  ];
                @endphp
                <tr class="payment-out-row"
                    data-date="{{ $dateValue }}"
                    data-party="{{ strtolower($partyName) }}"
                    data-search="{{ strtolower(trim(($paymentOut->reference ?? '') . ' ' . $partyName . ' ' . $bankName . ' ' . ($purchase?->bill_number ?? ''))) }}"
                    data-amount="{{ (float) ($paymentOut->amount ?? 0) }}">
                  <td>{{ \Carbon\Carbon::parse($dateValue)->format('d/m/Y') }}</td>
                  <td><span class="badge bg-light text-dark">{{ $paymentOut->reference ?: '-' }}</span></td>
                  <td><strong>{{ $partyName }}</strong></td>
                  <td><span class="text-danger fw-bold">Rs {{ number_format((float) $paymentOut->amount, 2) }}</span></td>
                  <td><small>{{ $bankName ?: '-' }}</small></td>
                  <td><span class="badge bg-warning text-dark">{{ ucfirst($paymentOut->payment_type ?: 'Bank') }}</span></td>
                  <td class="text-center">
                    <div class="dropdown">
                      <button class="btn btn-sm btn-light px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        @if($purchase)
                          <li><a class="dropdown-item" href="{{ route('purchase-bills.preview', $purchase) }}" target="_blank"><i class="fa-solid fa-eye me-2"></i>Open</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-bills.edit', $purchase) }}"><i class="fa-solid fa-pen-to-square me-2"></i>Edit</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-bills.pdf', $purchase) }}" target="_blank"><i class="fa-solid fa-file-pdf me-2"></i>Open PDF</a></li>
                          <li><a class="dropdown-item" href="{{ route('purchase-bills.print', $purchase) }}" target="_blank"><i class="fa-solid fa-print me-2"></i>Print</a></li>
                          <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                          <a class="dropdown-item" href="#" onclick='viewPaymentOutHistory(@json($historyPayload)); return false;'>
                            <i class="fa-solid fa-history me-2"></i>View History
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="payment-out-empty">
                    <i class="fa-solid fa-inbox fa-2x mb-3 d-block opacity-50"></i>
                    <strong>No payment out records yet.</strong> Add supplier payments from purchase bill workflow.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="addPaymentOutModal" tabindex="-1" aria-labelledby="addPaymentOutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center">
          <h5 class="modal-title" id="addPaymentOutModalLabel">Payment-out</h5>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>

        <form method="POST" action="{{ route('payment-out.store') }}" id="paymentOutForm">
          @csrf
          <div class="modal-body">
            <div class="row">
              <div class="col-lg-7">
                <div class="mb-3">
                  <label class="form-label">Select Party</label>
                  <select class="form-select" id="paymentOutPartyFilter">
                    <option value="">All Parties</option>
                    @foreach($parties as $party)
                      <option value="{{ $party->id }}">{{ $party->name }}</option>
                    @endforeach
                  </select>
                </div>

                <div style="padding:22px; border:1px solid #ced4da; box-shadow:0 2px 6px rgba(0,0,0,0.12);">
                  <div class="row align-items-end g-3 mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Payment Type</label>
                      <select class="form-select" name="payment_type" id="paymentOutPaymentType">
                        <option value="cash">Cash</option>
                        <option value="bank" selected>Bank</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Bank</label>
                      <select class="form-select" name="bank_account_id" id="paymentOutBankSelect">
                        <option value="">-- Select Bank --</option>
                        @foreach($bankAccounts as $bank)
                          @php
                            $accountNumber = preg_replace('/\s+/', '', (string) ($bank->account_number ?? ''));
                            $bankLabel = trim($bank->display_name . ($accountNumber !== '' ? ' - ' . $accountNumber : ''));
                          @endphp
                          <option value="{{ $bank->id }}">{{ $bankLabel }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Amount</label>
                      <input type="number" step="0.01" min="0.01" class="form-control" name="amount" id="paymentOutAmount" placeholder="Enter amount" required>
                    </div>
                  </div>

                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Receipt No</label>
                      <input type="text" class="form-control" name="receipt_no" id="paymentOutReceiptNo" value="{{ old('receipt_no', $nextEntryNo ?? 1) }}" placeholder="Receipt No">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Reference No</label>
                      <input type="text" class="form-control" name="reference" id="paymentOutReference" placeholder="Reference No">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Date</label>
                      <input type="date" class="form-control" name="payment_date" value="{{ now()->format('Y-m-d') }}">
                    </div>
                  </div>
                </div>

                <div class="mt-4">
                  <h6 class="fw-semibold mb-2">Link Purchase Bill</h6>
                  <div class="table-responsive border rounded" style="max-height: 300px; overflow:auto;">
                    <table class="table table-sm align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 8%;">Select</th>
                          <th>Date</th>
                          <th>Bill No.</th>
                          <th>Party</th>
                          <th class="text-end">Grand Total</th>
                          <th class="text-end">Balance</th>
                        </tr>
                      </thead>
                      <tbody id="paymentOutPurchaseRows">
                        @forelse($pendingPurchases as $purchase)
                          @php
                            $purchasePartyName = $purchase->party?->name ?: ($purchase->party_name ?: '-');
                          @endphp
                          <tr class="payment-out-purchase-row"
                              data-party-id="{{ $purchase->party_id }}"
                              data-balance="{{ (float) ($purchase->balance ?? 0) }}"
                              data-bill="{{ $purchase->bill_number ?? '-' }}"
                              data-party-name="{{ $purchasePartyName }}">
                            <td>
                              <input class="form-check-input payment-out-purchase-check" type="radio" name="purchase_id" value="{{ $purchase->id }}" required>
                            </td>
                            <td>{{ optional($purchase->bill_date)->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $purchase->bill_number ?? '-' }}</td>
                            <td>{{ $purchasePartyName }}</td>
                            <td class="text-end">{{ number_format((float) ($purchase->grand_total ?? 0), 2) }}</td>
                            <td class="text-end text-danger fw-semibold">{{ number_format((float) ($purchase->balance ?? 0), 2) }}</td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="6" class="text-center text-muted py-4">No pending purchase bills found.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="col-lg-5">
                <div class="border rounded-3 p-3 h-100 bg-light">
                  <h6 class="fw-semibold mb-3">Payment Summary</h6>
                  <div class="mb-3">
                    <label class="form-label text-secondary">Selected Bill</label>
                    <input type="text" class="form-control" id="paymentOutSelectedBill" value="No bill selected" readonly>
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-secondary">Selected Party</label>
                    <input type="text" class="form-control" id="paymentOutSelectedParty" value="No party selected" readonly>
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-secondary">Balance Due</label>
                    <input type="text" class="form-control" id="paymentOutSelectedBalance" value="Rs 0.00" readonly>
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-secondary">Payment Note</label>
                    <textarea class="form-control" rows="4" readonly>Payment-out purchase bill ke against save hogi, same Payment In modal flow ki tarah.</textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-between align-items-center">
            <div class="text-muted small">Select a pending purchase bill, then save payment-out.</div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn text-white" style="background-color:#D4112E;">Save Payment-out</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>

  <script>
    function viewPaymentOutHistory(entry) {
      $('#paymentOutHistoryModal').remove();

      const amount = entry.amount ? `Rs ${parseFloat(entry.amount).toFixed(2)}` : '-';
      const paymentType = entry.payment_type || '-';
      const reference = entry.reference || '-';
      const receipt = entry.receipt || '-';
      const bankName = entry.bank_name || '-';
      const timestamp = entry.created_at || entry.updated_at || '-';

      const historyHtml = `
        <div class="modal fade" id="paymentOutHistoryModal" tabindex="-1">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  <i class="fa-solid fa-history me-2"></i>Payment Out History (1 Record)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="alert alert-info mb-3">
                  <h6 class="mb-2"><strong>Payment Details Summary:</strong></h6>
                  <div class="row">
                    <div class="col-md-6">
                      <small><strong>Entry No:</strong> ${entry.entry_no || '-'}</small><br>
                      <small><strong>Receipt No:</strong> ${receipt}</small><br>
                      <small><strong>Reference No:</strong> ${reference}</small><br>
                      <small><strong>Amount:</strong> <span class="text-danger fw-bold">${amount}</span></small>
                    </div>
                    <div class="col-md-6">
                      <small><strong>Payment Type:</strong> <span class="badge bg-warning text-dark">${paymentType}</span></small><br>
                      <small><strong>Party:</strong> ${entry.party_name || '-'}</small><br>
                      <small><strong>Bank:</strong> ${bankName}</small>
                    </div>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                      <tr>
                        <th style="width:10%;">Entry No</th>
                        <th style="width:12%;">Date & Time</th>
                        <th style="width:18%;">Action</th>
                        <th style="width:12%;">Amount</th>
                        <th style="width:14%;">Reference</th>
                        <th style="width:14%;">Receipt</th>
                        <th style="width:10%;">Type</th>
                        <th style="width:10%;">User</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><span class="badge bg-dark-subtle text-dark border">${entry.entry_no || '-'}</span></td>
                        <td><small class="text-muted">${timestamp}</small></td>
                        <td>
                          <strong>${entry.action || 'Payment Out Recorded'}</strong>
                          ${entry.description ? `<br><small class="text-muted">${entry.description}</small>` : ''}
                        </td>
                        <td><span class="text-danger fw-bold">${amount}</span></td>
                        <td><span class="badge bg-light text-dark">${reference}</span></td>
                        <td><span class="badge bg-light text-dark">${receipt}</span></td>
                        <td><span class="badge bg-warning text-dark text-uppercase" style="font-size:0.7rem;">${paymentType.substring(0, 3)}</span></td>
                        <td><small><i class="fa-solid fa-user me-1 text-secondary"></i>${entry.user_name || 'System'}</small></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                  <i class="fa-solid fa-xmark me-1"></i>Close
                </button>
              </div>
            </div>
          </div>
        </div>
      `;

      $('body').append(historyHtml);
      const modal = new bootstrap.Modal(document.getElementById('paymentOutHistoryModal'));
      modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
      const rows = Array.from(document.querySelectorAll('.payment-out-row'));
      const searchInput = document.getElementById('paymentOutSearchInput');
      const periodFilter = document.getElementById('paymentOutPeriodFilter');
      const firmFilter = document.getElementById('paymentOutFirmFilter');
      const totalAmountEl = document.getElementById('paymentOutTotalAmount');
      const totalCountEl = document.getElementById('paymentOutTotalCount');
      const paidAmountEl = document.getElementById('paymentOutPaidAmount');
      const linkedBillsEl = document.getElementById('paymentOutLinkedBills');
      const dateRangeLabel = document.getElementById('paymentOutDateRangeLabel');
      const modalPartyFilter = document.getElementById('paymentOutPartyFilter');
      const modalAmountInput = document.getElementById('paymentOutAmount');
      const selectedBillInput = document.getElementById('paymentOutSelectedBill');
      const selectedPartyInput = document.getElementById('paymentOutSelectedParty');
      const selectedBalanceInput = document.getElementById('paymentOutSelectedBalance');
      const purchaseOptionRows = Array.from(document.querySelectorAll('.payment-out-purchase-row'));
      const purchaseChecks = Array.from(document.querySelectorAll('.payment-out-purchase-check'));

      function getDateRange(period) {
        const today = new Date();
        let from = null;
        let to = null;

        if (period === 'today') {
          from = new Date(today.getFullYear(), today.getMonth(), today.getDate());
          to = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        } else if (period === 'this_month') {
          from = new Date(today.getFullYear(), today.getMonth(), 1);
          to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        } else if (period === 'last_month') {
          from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
          to = new Date(today.getFullYear(), today.getMonth(), 0);
        } else if (period === 'this_year') {
          from = new Date(today.getFullYear(), 0, 1);
          to = new Date(today.getFullYear(), 11, 31);
        }

        return { from, to };
      }

      function formatDate(date) {
        return date ? date.toLocaleDateString('en-GB') : '';
      }

      function updateDateLabel(period) {
        const range = getDateRange(period);
        if (!range.from || !range.to) {
          dateRangeLabel.textContent = 'All dates';
          return;
        }
        dateRangeLabel.textContent = `${formatDate(range.from)} To ${formatDate(range.to)}`;
      }

      function rowMatchesPeriod(row, period) {
        if (period === 'all') {
          return true;
        }

        const value = row.getAttribute('data-date');
        if (!value) {
          return false;
        }

        const rowDate = new Date(value + 'T00:00:00');
        const range = getDateRange(period);
        if (!range.from || !range.to) {
          return true;
        }

        return rowDate >= range.from && rowDate <= range.to;
      }

      function applyFilters() {
        const term = (searchInput?.value || '').toLowerCase().trim();
        const period = periodFilter?.value || 'all';
        const firm = (firmFilter?.value || '').toLowerCase().trim();

        let total = 0;
        let count = 0;
        let linked = 0;

        rows.forEach((row) => {
          const matchesSearch = !term || (row.getAttribute('data-search') || '').includes(term);
          const matchesFirm = !firm || (row.getAttribute('data-party') || '') === firm;
          const matchesPeriod = rowMatchesPeriod(row, period);
          const visible = matchesSearch && matchesFirm && matchesPeriod;

          row.style.display = visible ? '' : 'none';

          if (visible) {
            total += parseFloat(row.getAttribute('data-amount') || '0');
            count += 1;
            linked += 1;
          }
        });

        totalAmountEl.textContent = `Rs ${total.toFixed(2)}`;
        paidAmountEl.textContent = `Rs ${total.toFixed(2)}`;
        totalCountEl.textContent = count;
        linkedBillsEl.textContent = linked;
        updateDateLabel(period);
      }

      function updateSelectedPurchaseSummary() {
        const selected = document.querySelector('.payment-out-purchase-check:checked');
        if (!selected) {
          selectedBillInput.value = 'No bill selected';
          selectedPartyInput.value = 'No party selected';
          selectedBalanceInput.value = 'Rs 0.00';
          return;
        }

        const row = selected.closest('.payment-out-purchase-row');
        const balance = parseFloat(row?.getAttribute('data-balance') || '0');
        selectedBillInput.value = row?.getAttribute('data-bill') || '-';
        selectedPartyInput.value = row?.getAttribute('data-party-name') || '-';
        selectedBalanceInput.value = `Rs ${balance.toFixed(2)}`;

        if (!modalAmountInput.value || parseFloat(modalAmountInput.value || '0') > balance) {
          modalAmountInput.value = balance > 0 ? balance.toFixed(2) : '';
        }
      }

      function filterPendingPurchasesByParty() {
        const partyId = modalPartyFilter?.value || '';

        purchaseOptionRows.forEach((row) => {
          const match = !partyId || row.getAttribute('data-party-id') === partyId;
          row.style.display = match ? '' : 'none';

          const radio = row.querySelector('.payment-out-purchase-check');
          if (!match && radio?.checked) {
            radio.checked = false;
          }
        });

        updateSelectedPurchaseSummary();
      }

      searchInput?.addEventListener('input', applyFilters);
      periodFilter?.addEventListener('change', applyFilters);
      firmFilter?.addEventListener('change', applyFilters);
      modalPartyFilter?.addEventListener('change', filterPendingPurchasesByParty);
      purchaseChecks.forEach((radio) => {
        radio.addEventListener('change', updateSelectedPurchaseSummary);
      });

      updateDateLabel(periodFilter?.value || 'this_month');
      applyFilters();
      updateSelectedPurchaseSummary();
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

