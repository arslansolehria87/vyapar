<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar — Payment In</title>
  <meta name="description" content="Create professional estimates and quotations for your customers in Vyapar.">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
  <!-- Custom Styles -->
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
  .table-wrapper.dropdown-overflow-visible {
    overflow: visible !important;
  }
  #paymentInTable_wrapper .dataTables_length,
  #paymentInTable_wrapper .dataTables_filter {
    display: none !important;
  }
  .payment-filter-dropdown {
    min-width: 220px;
    padding: 12px;
  }
  .payment-filter-dropdown .form-control,
  .payment-filter-dropdown .form-select {
    min-width: 180px;
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

  @php
    $bankAccountsCollection = collect($bankAccounts ?? []);
    $defaultBankAccount = $bankAccountsCollection->firstWhere('is_active', 1) ?? $bankAccountsCollection->first();
    $defaultBankAccountId = $defaultBankAccount->id ?? null;
  @endphp

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
    window.paymentInDefaultBankAccountId = @json($defaultBankAccountId);
    console.log('App initialized:', window.App);
  </script>

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

    .link-payment-modal .modal-dialog {
      max-width: 1120px;
    }

    .link-payment-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      border-bottom: 1px solid #e5e7eb;
      padding-bottom: 16px;
      margin-bottom: 18px;
    }

    .link-payment-summary {
      display: flex;
      gap: 40px;
      align-items: flex-end;
      flex-wrap: wrap;
    }

    .link-payment-value {
      font-size: 28px;
      font-weight: 700;
      color: #111827;
    }

    .link-payment-label {
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 6px;
      display: block;
    }

    .link-payment-tools {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .link-payment-grid thead th {
      font-size: 13px;
      color: #6b7280;
      background: #f8fafc;
      border-bottom: 1px solid #e5e7eb;
      white-space: nowrap;
    }

    .link-payment-grid tbody td {
      vertical-align: middle;
      font-size: 14px;
    }

    .link-payment-grid-wrap {
      max-height: 360px;
      overflow: auto;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
    }

    .link-payment-empty {
      padding: 36px 12px;
      text-align: center;
      color: #94a3b8;
    }

  .unused-amount-negative {
      color: #dc2626;
    }

    .party-balance-display {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: #f8fbff;
      border: 1px solid #dbe6f1;
      color: #334155;
      font-size: 13px;
      font-weight: 600;
      width: fit-content;
      max-width: 100%;
    }

    .party-balance-display .balance-value {
      font-weight: 700;
      color: #0f172a;
    }

    .party-balance-display.balance-positive .balance-value {
      color: #16a34a;
    }

    .party-balance-display.balance-negative .balance-value {
      color: #dc2626;
    }

  .party-balance-display.balance-zero .balance-value {
      color: #475569;
    }

    .payment-in-modal .modal-dialog {
      width: calc(100vw - 96px);
      max-width: 1120px;
      margin: 18px auto;
    }

    .payment-in-modal .modal-content {
      border: 0;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
      min-height: auto;
      max-height: calc(100vh - 36px);
      display: flex;
      flex-direction: column;
    }

    .payment-in-modal .modal-header {
      border-bottom: 0;
      padding: 16px 22px 10px;
      flex: 0 0 auto;
    }

    .payment-in-modal .modal-body {
      padding: 6px 22px 8px;
      flex: 1 1 auto;
      overflow: auto;
    }

    .payment-in-sheet {
      min-height: 0;
    }

    .payment-in-party-bar {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .payment-in-party-bar .party-dropdown-wrapper {
      flex: 0 0 240px;
      max-width: 240px;
    }

    .payment-in-party-bar .party-search-input {
      height: 38px;
      border-radius: 4px;
      border: 1px solid #cfd6e0;
      background: #fff;
      box-shadow: none;
      font-size: 14px;
      padding-left: 12px;
      padding-right: 34px;
    }

    .payment-in-party-bar .party-search-input::placeholder {
      color: #9ca3af;
      font-weight: 500;
    }

    .payment-entry-card {
      width: 100%;
      max-width: 404px;
      border: 1px solid #d9dee7;
      border-radius: 4px;
      background: #f2f2f2;
      padding: 14px 14px 12px;
      box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
    }

    .payment-in-meta-card {
      padding-top: 4px;
    }

    .payment-in-meta-grid {
      display: grid;
      grid-template-columns: 92px minmax(160px, 190px);
      column-gap: 16px;
      row-gap: 14px;
      align-items: center;
      max-width: 320px;
      margin-left: auto;
    }

    .payment-in-meta-grid .meta-label {
      color: #a3aab7;
      font-size: 13px;
      text-align: right;
      font-weight: 500;
    }

    .payment-in-meta-grid .meta-value {
      height: 36px;
      border: 0;
      border-bottom: 1px solid #dde4ee;
      border-radius: 0;
      padding: 0 2px;
      box-shadow: none;
      background: transparent;
      font-size: 14px;
      color: #111827;
    }

    .payment-in-summary-grid {
      margin-top: 58px;
      display: grid;
      grid-template-columns: 110px minmax(0, 1fr);
      column-gap: 16px;
      row-gap: 16px;
      align-items: center;
      max-width: 340px;
      margin-left: auto;
    }

    .payment-in-summary-grid .summary-label {
      color: #a3aab7;
      font-size: 14px;
      text-align: right;
    }

    .payment-in-summary-grid .summary-input {
      height: 38px;
      border-radius: 4px;
      border: 1px solid #d7dde6;
      background: #fff;
      box-shadow: none;
    }

    .payment-in-summary-grid .summary-total {
      font-size: 15px;
      font-weight: 700;
      color: #111827;
    }

    .payment-in-summary-grid .summary-total-value {
      font-size: 18px;
      font-weight: 700;
      color: #1d4ed8;
      text-align: right;
    }

    .payment-extra-stack {
      margin-top: 14px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      align-items: flex-start;
      width: 100%;
      max-width: 404px;
    }

    .payment-extra-item {
      width: 100%;
      max-width: 404px;
    }

    .payment-extra-stack .btn-outline-light-like {
      border: 1px solid #d8dee7;
      background: #fff;
      color: #6b7280;
      padding: 8px 14px;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
    }

    .payment-extra-stack .image-upload-trigger {
      width: 24px;
      height: 24px;
      color: #9ca3af;
      cursor: pointer;
    }

    .payment-row-shell {
      background: #f2f2f2;
      border-radius: 4px;
    }

    .payment-row-shell .payment-row {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 12px !important;
    }

    .payment-row-shell .payment-row:last-child {
      margin-bottom: 0 !important;
    }

    .payment-row-shell .form-label {
      margin-bottom: 2px;
      font-size: 13px;
      color: #6b7280;
    }

    .payment-row-shell .form-select,
    .payment-row-shell .form-control {
      height: 34px;
      border-radius: 4px;
      border-color: #cfd6e0;
      box-shadow: none;
      font-size: 14px;
    }

    .payment-row-shell .payment-row-line {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      flex-wrap: nowrap;
    }

    .payment-row-shell .payment-type-block {
      width: 150px;
      flex: 0 0 150px;
    }

    .payment-row-shell .payment-type-line {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .payment-row-shell .payment-amount-block {
      width: 140px;
      flex: 0 0 140px;
    }

    .payment-row-shell .payment-reference-block {
      width: 100%;
      max-width: 220px;
      margin-top: 4px;
    }

    .payment-row-shell .payment-trash-block {
      width: 24px;
      flex: 0 0 24px;
      padding-top: 30px;
    }

    .payment-row-shell .payment-type-select {
      width: 100%;
    }

    .payment-row-shell .remove-row {
      margin-bottom: 0;
      padding: 0;
    }

    .payment-row-shell .btn-link {
      font-size: 13px;
    }

    .payment-row-shell .payment-add-link {
      margin-top: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      width: 100%;
    }

    .payment-row-shell .payment-add-total {
      margin-left: auto;
      color: #6b7280;
      font-size: 13px;
      font-weight: 600;
      white-space: nowrap;
    }

    .payment-in-modal .modal-footer {
      padding: 12px 22px 12px;
      border-top: 1px solid #eef2f7;
    }

    .payment-in-modal .modal-footer .btn {
      min-width: 96px;
    }

    .payment-in-modal .modal-footer .btn-secondary {
      background: #fff;
      border-color: #d7dde6;
      color: #475569;
    }

    .payment-in-modal .modal-footer .dropdown-toggle::after {
      margin-left: 0.45rem;
    }
  </style>
</head>

<body data-page="payment-in">

  <!-- Navbar & Sidebar injected by components.js -->

  <!-- ═══════════════════════════════════════
     MAIN CONTENT — ESTIMATE / QUOTATION
     ═══════════════════════════════════════ -->
  <main class="main-content" id="mainContent">

    <div class="d-flex justify-content-between align-items-center bg-light mb-2 p-4">
      <div>
        <!-- <h4 class="mb-0">Estimates / Quotations</h4> -->
        <div class="dropdown">
          <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="h4">Payment In</span>
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
      <div>
        <button class="btn rounded-pill" style="background-color: #D4112E;" data-bs-toggle="modal" data-bs-target="#addPaymentInModal">
    <span class="text-light">+ Add Payment-in</span>
</button>
   <div class="modal fade payment-in-modal" id="addPaymentInModal" tabindex="-1" aria-labelledby="addPaymentInModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl"> <!-- Keep centered, but smaller width is handled in CSS -->
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header d-flex justify-content-between align-items-center">
        <h5 class="modal-title" id="addPaymentInModalLabel">Payment-in</h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light" onclick="openCalculator()">
            <i class="fa-solid fa-calculator"></i>
          </button>
          <a href="{{ route('settings.transactions') }}" class="btn btn-light" title="Settings">
            <i class="fa-solid fa-gear"></i>
          </a>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        @php
          $nextReceiptNo = $nextEntryNo ?? ((int) $paymentIns->max(fn ($paymentIn) => (int) ($paymentIn->receipt_no ?? 0))) + 1;
          $todayDate = now()->format('Y-m-d');
          $todayTime = now()->format('h:i A');
        @endphp
        <form id="paymentInForm" action="{{ route('payments-in.store') }}" method="POST">
           @csrf
          <input type="hidden" id="paymentInId" value="">
          <input type="hidden" id="linkedRowsJson" value="[]">
          <input type="hidden" id="selectedEntityType" value="party">
          <input type="hidden" id="selectedEntityId" value="">
          <input type="hidden" id="selectedEntityName" value="">

          <div class="payment-in-sheet">
            <div class="row g-4 align-items-start">
              <div class="col-lg-7">
                <div class="payment-in-party-bar">
                  <div class="dropdown party-dropdown-wrapper" data-bs-auto-close="outside">
                    <input type="text" class="form-control party-search-input w-100" placeholder="Search by Name/Phone *" id="partyDropdownBtn" data-bs-toggle="dropdown" autocomplete="off">
                    <ul class="dropdown-menu w-100" aria-labelledby="partyDropdownBtn" id="partyDropdownMenu" style="max-height: 300px; overflow-y: auto;">
                      @foreach($brokers ?? [] as $broker)
                      <li class="entity-option dropdown-item d-flex justify-content-between align-items-center" style="cursor: default; opacity:.95;"
                          data-entity-type="broker"
                          data-id="{{ $broker->id }}"
                          data-name="{{ strtolower($broker->name ?? '') }}"
                          data-phone="{{ strtolower($broker->phone ?? '') }}"
                          data-billing="{{ strtolower(addslashes($broker->address ?? '')) }}">
                        <span class="party-name cursor-pointer">{{ $broker->name }}</span>
                        <span class="badge bg-secondary">Broker</span>
                      </li>
                      @endforeach
                      @foreach($items ?? [] as $item)
                      <li class="entity-option dropdown-item d-flex justify-content-between align-items-center" style="cursor: default; opacity:.95;"
                          data-entity-type="item"
                          data-id="{{ $item->id }}"
                          data-name="{{ strtolower($item->name ?? '') }}"
                          data-phone="{{ strtolower($item->item_code ?? '') }}"
                          data-billing="{{ strtolower(addslashes($item->description ?? '')) }}">
                        <span class="party-name cursor-pointer">{{ $item->name }}</span>
                        <span class="badge bg-info text-dark">Item</span>
                      </li>
                      @endforeach
                      @foreach($parties as $party)
                      <li class="entity-option party-option dropdown-item d-flex justify-content-between align-items-center" style="cursor: pointer;"
                          data-entity-type="party"
                          data-id="{{ $party->id }}"
                          data-current-balance="{{ $party->current_balance ?? $party->opening_balance ?? 0 }}"
                          data-phone="{{ strtolower($party->phone ?? '') }}"
                          data-billing="{{ strtolower(addslashes($party->billing_address ?? '')) }}">
                        <span class="party-name cursor-pointer">{{ $party->name }}</span>
                        <span class="party-balance small text-muted">
                          @if(($party->current_balance ?? $party->opening_balance ?? 0) < 0)
                            <i class="fa-solid fa-arrow-up text-danger me-1"></i>
                          @elseif(($party->current_balance ?? $party->opening_balance ?? 0) > 0)
                            <i class="fa-solid fa-arrow-down text-success me-1"></i>
                          @endif
                          Rs {{ number_format((float) ($party->current_balance ?? $party->opening_balance ?? 0), 2) }}
                        </span>
                      </li>
                      @endforeach
                      <li class="dropdown-item text-muted small d-none" id="partySearchNoResults">No matching records found.</li>
                      <li class="dropdown-item text-primary" id="addNewPartyBtn">+ Add New Party</li>
                    </ul>
                  </div>

                  <input type="hidden" class="party-id" name="party_id">
                  <input type="hidden" class="phone-input" name="phone-input">
                  <input type="hidden" class="billing-address" name="billing_address">
                  <div id="partyBalanceDisplay" class="party-balance-display balance-zero d-none">
                    <span>Current Balance:</span>
                    <span class="balance-value">Rs 0.00</span>
                  </div>
                </div>

                <div id="paymentContainer" class="payment-entry-card">
                  <div class="payment-row-shell">
                    <div class="payment-row payment-row--entry">
                      <div class="payment-row-line">
                        <div class="payment-type-block">
                          <label class="form-label">Payment Type</label>
                          <div class="payment-type-line">
                            <select class="form-select payment-type-select payment-type-entry" data-default-payment-type="cash"></select>

                          </div>
                          <input type="hidden" class="payment-bank" name="bank_account_id" value="">
                        </div>

                        <div class="payment-amount-block">
                          <label class="form-label">Amount</label>
                          <input type="number" class="form-control payment-amount" placeholder="0">
                        </div>

                        <div class="payment-trash-block d-flex align-items-center justify-content-center">
                          <button type="button" class="remove-row border-0 bg-transparent text-secondary" style="font-size:18px;">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                      </div>

                      <div class="payment-reference-block d-none">
                        <label class="form-label text-secondary">Reference No</label>
                        <input type="text" class="form-control payment-reference d-none" placeholder="Reference No.">
                      </div>
                    </div>
                  </div>

                  <div class="payment-add-link">
                    <button type="button" id="addPaymentRow" class="btn p-0 text-primary border-0 bg-transparent">
                      + Add Payment Type
                    </button>
                    <div class="payment-add-total">
                      Total payment: <span id="paymentRowTotalDisplay">0</span>
                    </div>
                  </div>
                  <input type="hidden" id="referenceNo" value="">
                </div>

                <div class="payment-extra-stack">
                  <div class="payment-extra-item">
                    <button type="button" id="toggleDescriptionBtn" class="btn-outline-light-like">
                      <i class="fa-solid fa-file text-secondary" style="font-size:18px;"></i>
                      <span>Add Description</span>
                    </button>

                    <div id="descriptionBox" class="d-none mt-1 w-100">
                      <label class="form-label text-secondary">Description</label>
                      <textarea class="form-control" id="paymentDescription" name="description" rows="4" placeholder="Enter description"></textarea>
                    </div>
                  </div>

                  <div class="payment-extra-item">
                    <button type="button" id="toggleImageBtn" class="btn-outline-light-like">
                      <i class="fa-solid fa-camera text-secondary" style="font-size:18px;"></i>
                      <span>Upload Image</span>
                    </button>

                    <input type="file" id="paymentImageInput" name="attachments[]" class="d-none" accept="image/*" multiple>
                    <div id="imageUploadBox" class="d-none mt-1 w-100">
                      <div class="border rounded p-3 text-center text-secondary" id="imagePlaceholder" style="cursor:pointer;">
                        Click to select an image
                      </div>
                      <div id="imagePreviewWrap" class="d-none mt-2 d-flex flex-wrap gap-2"></div>
                      <div id="imageSelectedName" class="small text-muted mt-2 d-none"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-5 payment-in-meta-card">
                <div class="payment-in-meta-grid">
                  <label class="meta-label">Receipt No</label>
                  <input type="text" class="form-control meta-value" id="receiptNo" name="receipt_no" placeholder="Receipt No" value="{{ old('receipt_no', $nextReceiptNo > 0 ? $nextReceiptNo : 1) }}">

                  <label class="meta-label">Date</label>
                  <input type="date" class="form-control meta-value" name="date" value="{{ old('date', $todayDate) }}">

                  <label class="meta-label">Time</label>
                  <input type="text" class="form-control meta-value" value="{{ $todayTime }}" readonly>
                </div>

                <div class="payment-in-summary-grid">
                  <div class="summary-label">Received</div>
                  <input type="text" class="form-control summary-input" id="receivedAmount" placeholder="Received" readonly>

                  <div class="summary-label">Discount</div>
                  <input type="text" class="form-control summary-input" id="paymentInDiscountDisplay" value="0" readonly>

                  <div class="summary-total">Total</div>
                  <div class="summary-total-value" id="paymentInTotalDisplay">0</div>
                </div>
              </div>
            </div>
          </div>

        </form>
      </div>

      <!-- Modal Footer -->
<!-- Modal Footer -->
  <!-- Modal Footer -->
<div class="modal-footer d-flex justify-content-between align-items-center">

  <!-- Top-left green button -->
  <button type="button" class="btn text-white" style="background-color: #28a745;" id="openLinkPaymentBtn">
    Link Payment
  </button>

  <!-- Right side buttons -->
  <div class="d-flex gap-2">

    <!-- Share Dropdown -->
    <!-- Share Dropdown -->
<div class="dropdown">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="shareDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
    Share
  </button>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="shareDropdownBtn" data-bs-display="static" style="margin-top:0;">
    <li><a class="dropdown-item" href="#" id="paymentInShareEmail">Share via Email</a></li>
    <li><a class="dropdown-item" href="#" id="paymentInShareWhatsApp">Share via WhatsApp</a></li>
    <li><a class="dropdown-item" href="#" id="paymentInShareLink">Share via Link</a></li>
  </ul>
</div>

    <!-- Save Payment-in button -->
    <button type="submit" class="btn btn-primary" form="paymentInForm">Save Payment-in</button>

  </div>

</div>
    </div>
  </div>
</div>

<div class="modal fade link-payment-modal" id="linkPaymentModal" tabindex="-1" aria-labelledby="linkPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="linkPaymentModalLabel">Link Payment to Txns</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="link-payment-header">
          <div class="link-payment-summary">
            <div>
              <span class="link-payment-label">Party</span>
              <div class="link-payment-value fs-4" id="linkPaymentPartyName">-</div>
            </div>
            <div>
              <span class="link-payment-label">Received</span>
              <div class="input-group">
                <input type="number" class="form-control" id="linkPaymentReceivedInput" min="0" step="0.01" />
                <span class="input-group-text"><i class="fa-solid fa-pen"></i></span>
              </div>
            </div>
          </div>

          <div class="link-payment-tools">
            <button type="button" class="btn btn-info text-white" id="linkPaymentAutoBtn">AUTO LINK</button>
            <button type="button" class="btn btn-light" id="linkPaymentResetBtn" title="Reset">
              <i class="fa-solid fa-rotate-right"></i>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
          <select class="form-select" id="linkPaymentTypeFilter" style="max-width: 280px;">
            <option value="all">All transactions</option>
            <option value="sale">Sale</option>
            <option value="pos">POS</option>
          </select>

          <input type="text" class="form-control" id="linkPaymentSearch" placeholder="Search transaction" style="max-width: 290px;">
        </div>

        <div class="link-payment-grid-wrap">
          <table class="table mb-0 link-payment-grid">
            <thead>
              <tr>
                <th style="width: 54px;"></th>
                <th>Date</th>
                <th>Type</th>
                <th>Ref/Inv No.</th>
                <th class="text-end">Total</th>
                <th class="text-end">Balance</th>
                <th style="width: 180px;">Linked Amount</th>
              </tr>
            </thead>
            <tbody id="linkPaymentRows">
              <tr>
                <td colspan="7" class="link-payment-empty">Select a party to load transactions.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <div class="fw-semibold">
          Unused Amount :
          <span id="linkPaymentUnusedAmount">0</span>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="linkPaymentDoneBtn">Done</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="paymentInPrintOptionsModal" tabindex="-1" aria-labelledby="paymentInPrintOptionsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header">
        <h4 class="modal-title fw-semibold" id="paymentInPrintOptionsLabel">Select Print Options</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-4">
        <div class="row g-3">
          <div class="col-6">
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="date" id="printOptionDate" checked>
              <label class="form-check-label" for="printOptionDate">Date</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="reference" id="printOptionReference" checked>
              <label class="form-check-label" for="printOptionReference">Invoice No.</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="party" id="printOptionParty" checked>
              <label class="form-check-label" for="printOptionParty">Party Name</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="amount" id="printOptionAmount" checked>
              <label class="form-check-label" for="printOptionAmount">Total</label>
            </div>
          </div>
          <div class="col-6">
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="bank" id="printOptionBank" checked>
              <label class="form-check-label" for="printOptionBank">Bank Account</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="payment_type" id="printOptionType" checked>
              <label class="form-check-label" for="printOptionType">Payment Type</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="description" id="printOptionDescription">
              <label class="form-check-label" for="printOptionDescription">Description</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input payment-print-option" type="checkbox" value="status" id="printOptionStatus">
              <label class="form-check-label" for="printOptionStatus">Payment Status</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn text-white px-4 rounded-pill" style="background:#f43f5e;" id="confirmPaymentInPrint" onclick="window.confirmPaymentInReport && window.confirmPaymentInReport(); return false;">Get Print</button>
      </div>
    </div>
  </div>
</div>
        <span class="mx-3 py-1" style="border-right: 1px solid rgb(45, 44, 44);"></span>
        <span class="text-secondary fs-5 pt-1"><i class="fas fa-gear"></i></span>
      </div>


    </div>
    <div class="d-flex justify-content-between align-items-center bg-light mb-2 px-3 py-2 rounded">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="small fw-semibold">Filter By:</span>

        <div class="d-flex rounded-pill filter-pill">
          <div class="filter-left">
            <select id="paymentInPeriodSelect" class="filter-select">
              <option value="all" selected>All Payment In</option>
              <option value="this_month">This Month</option>
              <option value="last_month">Last Month</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
              <option value="custom">Custom</option>
            </select>
          </div>

          <div class="filter-right">
            <span id="paymentInDateRangeDisplay"></span>
            <div id="paymentInCustomDateRange" class="d-none align-items-center gap-1">
              <input id="paymentInCustomFrom" type="date" class="date-input" />
              <span>to</span>
              <input id="paymentInCustomTo" type="date" class="date-input" />
            </div>
          </div>
        </div>

        <div class="filter-pill small-pill">
          <select id="paymentInFirmSelect" class="filter-select text-center">
            <option value="" selected>All Firms</option>
            @foreach($paymentIns->map(fn($paymentIn) => $paymentIn->entity_name ?: $paymentIn->party?->name)->filter()->unique()->values() as $firm)
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
            <p class="ps-3 text-secondary m-0">Total Amount</p>
            <p class="ps-3 h4">Rs {{ number_format($paymentIns->sum('amount'), 2) }}</p>
          </div>
          <div class="w-50 mt-2 d-flex align-items-end justify-content-center flex-column">
            <div class="col-5 h-50 rounded-pill d-flex justify-content-center align-item-center me-4"
              style="background-color: #DEF7EE;">
              <p class="text-success pt-1">{{ $paymentIns->count() > 0 ? $paymentIns->count() : 0 }} <i class="bi bi-arrow-up-right "></i></>
              </p>
            </div>
            <span class="me-4 pe-1 mt-1 text-secondary" style="font-size: 10px;">vs last month</span>
          </div>
        </div>
        <div class="w-100 d-flex mt-3">
          <p class="ps-3 pe-3 text-secondary">Received : <span class="fw-bold text-dark">Rs {{ number_format($paymentIns->sum('amount'), 2) }}</span></p>


        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="col-12 g-2 mb-3 d-flex flex-wrap justify-content-between">
          <p class="fw-bold">Transactions</p>

          <div class="d-flex align-items-center">
            <div class="search-container">
              <input type="text" class="search-input" id="paymentInSearch" placeholder="Search...">
              <span class="search-btn">
                <i class="fa fa-search"></i>
              </span>
            </div>
            <div class="mt-1 pt-1 ms-3 d-flex align-items-center">
              <button type="button" id="exportPaymentInExcel" class="btn p-0 mx-3 fs-4 text-secondary border-0 bg-transparent" title="Export Excel" onclick="window.openPaymentInExportModal && window.openPaymentInExportModal('excel')">
                <i class="fas fa-file-excel"></i>
              </button>
              <button type="button" id="printPaymentInTable" class="btn p-0 mx-3 fs-4 text-secondary border-0 bg-transparent" title="Print Table" onclick="window.openPaymentInExportModal && window.openPaymentInExportModal('print')">
                <i class="fas fa-print"></i>
              </button>
            </div>

          </div>

        </div>

        <div class="table-responsive" id="paymentInTableWrap">
          <table class="table table-hover mb-0 align-middle custom-table" id="paymentInTable">
            <thead class="table-light">
              <tr>
                <th style="width: 12%;">
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Date</span>
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                      <ul class="dropdown-menu payment-filter-dropdown">
                        <li class="dropdown-item">
                          <input type="text" class="form-control form-control-sm payment-column-filter" data-column="0" placeholder="Search date...">
                        </li>
                      </ul>
                    </div>
                  </div>
                </th>
                <th style="width: 14%;">
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Reference No.</span>
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                      <ul class="dropdown-menu payment-filter-dropdown">
                        <li class="dropdown-item">
                          <input type="text" class="form-control form-control-sm payment-column-filter" data-column="1" placeholder="Search reference...">
                        </li>
                      </ul>
                    </div>
                  </div>
                </th>
                <th style="width: 18%;">
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Party Name</span>
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                      <ul class="dropdown-menu payment-filter-dropdown">
                        <li class="dropdown-item">
                          <input type="text" class="form-control form-control-sm payment-column-filter" data-column="2" placeholder="Search party...">
                        </li>
                      </ul>
                    </div>
                  </div>
                </th>
                <th style="width: 14%;">
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Total Amount</span>
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                      <ul class="dropdown-menu payment-filter-dropdown">
                        <li class="dropdown-item">
                          <input type="text" class="form-control form-control-sm payment-column-filter" data-column="3" placeholder="Search amount...">
                        </li>
                      </ul>
                    </div>
                  </div>
                </th>
                <th style="width: 14%;">
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Bank Account</span>
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                      <ul class="dropdown-menu payment-filter-dropdown">
                        <li class="dropdown-item">
                          <input type="text" class="form-control form-control-sm payment-column-filter" data-column="4" placeholder="Search bank...">
                        </li>
                      </ul>
                    </div>
                  </div>
                </th>
                <th style="width: 14%;">
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Payment Type</span>
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                      <ul class="dropdown-menu payment-filter-dropdown">
                        <li class="dropdown-item">
                          <input type="text" class="form-control form-control-sm payment-column-filter" data-column="5" placeholder="Search type...">
                        </li>
                      </ul>
                    </div>
                  </div>
                </th>
                <th style="width: 14%; text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($paymentIns as $paymentIn)
                <tr class="payment-in-row" data-edit-url="{{ route('payments-in.edit', $paymentIn) }}">
                  <td>{{ $paymentIn->date ? \Carbon\Carbon::parse($paymentIn->date)->format('d-m-Y') : '-' }}</td>
                  <td><span class="badge bg-light text-dark">{{ $paymentIn->reference_no ?: '-' }}</span></td>
                  <td><strong>{{ $paymentIn->entity_name ?: $paymentIn->party?->name ?: '-' }}</strong></td>
                  <td><span class="text-success fw-bold">Rs {{ number_format((float) $paymentIn->amount, 2) }}</span></td>
                  <td><small>{{ $paymentIn->bankAccount?->display_name ?: '-' }}</small></td>
                  <td><span class="badge bg-info text-white">{{ ucfirst($paymentIn->payment_type) }}</span></td>
                  <td style="text-align: center;">
                    <div class="dropdown">
                      <button class="btn btn-sm btn-light px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('invoice', ['payment_in' => $paymentIn->id]) }}"><i class="fa-solid fa-eye me-2"></i>Open</a></li>
                        <li><a class="dropdown-item" href="{{ route('payments-in.edit', $paymentIn) }}"><i class="fa-solid fa-pen-to-square me-2"></i>Edit</a></li>
                        <li><a class="dropdown-item" href="{{ route('payments-in.duplicate', $paymentIn) }}"><i class="fa-solid fa-copy me-2"></i>Duplicate</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openPaymentInPdf('{{ route('payments-in.pdf', $paymentIn) }}'); return false;"><i class="fa-solid fa-file-pdf me-2"></i>Open PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('payments-in.print', $paymentIn) }}" target="_blank"><i class="fa-solid fa-print me-2"></i>Print</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="viewPaymentHistory({{ $paymentIn->id }})"><i class="fa-solid fa-history me-2"></i>View History</a></li>
                        <li>
                          <form action="{{ route('payments-in.destroy', $paymentIn) }}" method="POST" onsubmit="return confirm('Delete this payment in record?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-trash me-2"></i>Delete</button>
                          </form>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </main>

  <!-- ═══════════════════════════════════════ -->
  <!--        CALCULATOR MODAL               -->
  <!-- ═══════════════════════════════════════ -->
  <div class="modal fade" id="calculatorModal" tabindex="-1" aria-labelledby="calculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="calculatorModalLabel">Calculator</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-3">
          <style>
            .calculator-container {
              background: #f8f9fa;
              border-radius: 8px;
              padding: 15px;
            }

            .calc-display {
              background: #2c3e50;
              color: #fff;
              font-size: 24px;
              padding: 15px;
              border-radius: 6px;
              text-align: right;
              margin-bottom: 15px;
              min-height: 50px;
              word-wrap: break-word;
              word-break: break-all;
              font-weight: bold;
            }

            .calc-buttons {
              display: grid;
              grid-template-columns: repeat(4, 1fr);
              gap: 8px;
            }

            .calc-btn {
              padding: 15px;
              border: 1px solid #ddd;
              border-radius: 6px;
              font-size: 16px;
              font-weight: 600;
              cursor: pointer;
              background: #fff;
              transition: all 0.2s;
              border: none;
            }

            .calc-btn:hover {
              background: #e9ecef;
              transform: translateY(-2px);
            }

            .calc-btn:active {
              transform: translateY(0);
            }

            .calc-btn.operator {
              background: #2563eb;
              color: white;
            }

            .calc-btn.operator:hover {
              background: #1d4ed8;
            }

            .calc-btn.equals {
              background: #10b981;
              color: white;
              grid-column: span 2;
            }

            .calc-btn.equals:hover {
              background: #059669;
            }

            .calc-btn.clear {
              background: #ef4444;
              color: white;
              grid-column: span 2;
            }

            .calc-btn.clear:hover {
              background: #dc2626;
            }

            .calc-btn.use-amount {
              background: #f59e0b;
              color: white;
              grid-column: span 4;
              margin-top: 10px;
            }

            .calc-btn.use-amount:hover {
              background: #d97706;
            }
          </style>

          <div class="calculator-container">
            <div class="calc-display" id="calcDisplay">0</div>
            <div class="calc-buttons">
              <button type="button" class="calc-btn" onclick="appendToCalc('7')">7</button>
              <button type="button" class="calc-btn" onclick="appendToCalc('8')">8</button>
              <button type="button" class="calc-btn" onclick="appendToCalc('9')">9</button>
              <button type="button" class="calc-btn operator" onclick="appendToCalc('/')">/</button>

              <button type="button" class="calc-btn" onclick="appendToCalc('4')">4</button>
              <button type="button" class="calc-btn" onclick="appendToCalc('5')">5</button>
              <button type="button" class="calc-btn" onclick="appendToCalc('6')">6</button>
              <button type="button" class="calc-btn operator" onclick="appendToCalc('*')">×</button>

              <button type="button" class="calc-btn" onclick="appendToCalc('1')">1</button>
              <button type="button" class="calc-btn" onclick="appendToCalc('2')">2</button>
              <button type="button" class="calc-btn" onclick="appendToCalc('3')">3</button>
              <button type="button" class="calc-btn operator" onclick="appendToCalc('-')">−</button>

              <button type="button" class="calc-btn" onclick="appendToCalc('0')">0</button>
              <button type="button" class="calc-btn" onclick="appendToCalc('.')">.</button>
              <button type="button" class="calc-btn operator" onclick="appendToCalc('+')">+</button>
              <button type="button" class="calc-btn clear" onclick="clearCalc()">C</button>

              <button type="button" class="calc-btn equals" onclick="calculateResult()">=</button>
            </div>
            <button type="button" class="calc-btn use-amount" onclick="useCalculatorResult()">Use Amount</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ADD PARTY MODAL -->
<div class="modal fade" id="addPartyModal" tabindex="-1" >
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-solid fa-user-plus me-2"></i> Add Party
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="addPartyForm">
          @csrf
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-600">Party Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Enter party name" id="partyNameInput" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                <input type="tel" name="phone" class="form-control" placeholder="Enter phone number" id="partyPhoneInput">
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs" id="partyModalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="party-address-tab" data-bs-toggle="tab" data-bs-target="#partyAddressPane" type="button" role="tab">
                <i class="fa-solid fa-location-dot me-1"></i> Address
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-credit-tab" data-bs-toggle="tab" data-bs-target="#partyCreditPane" type="button" role="tab">
                <i class="fa-solid fa-credit-card me-1"></i> Credit & Balance
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-additional-tab" data-bs-toggle="tab" data-bs-target="#partyAdditionalPane" type="button" role="tab">
                <i class="fa-solid fa-sliders me-1"></i> Additional Fields
              </button>
            </li>
          </ul>

          <div class="tab-content pt-3" id="partyModalTabContent">
            <!-- Address Tab -->
            <div class="tab-pane fade show active" id="partyAddressPane" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Email ID</label>
                  <input type="email" name="email" class="form-control" placeholder="example@email.com">
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                  <label class="form-label">Billing Address</label>
                  <textarea id="billingAddress" class="form-control" name="billing_address" rows="3" placeholder="Enter billing address"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Shipping Address</label>
                  <textarea  id="shippingAddress" class="form-control" name="shipping_address" rows="3" placeholder="Enter shipping address"></textarea>
                </div>
              </div>
            </div>

            <!-- Credit & Balance Tab -->
          <div class="tab-pane fade" id="partyCreditPane" role="tabpanel">
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Opening Balance <span class="text-danger">*</span></label>
      <div class="input-group">
        <span class="input-group-text">₹</span>
        <input type="number" name="opening_balance" class="form-control" placeholder="0.00" min="0" required>
      </div>
    </div>
    <div class="col-md-4">
      <label class="form-label">As Of Date</label>
      <input type="date" name="as_of_date" class="form-control" value="{{ date('Y-m-d') }}">
    </div>
    <div class="col-md-4">
      <label class="form-label d-block">Credit Limit</label>
      <div class="form-check form-switch mt-2">
        <input class="form-check-input" name="credit_limit_enabled" type="checkbox" id="creditLimitSwitch">
        <label class="form-check-label" for="creditLimitSwitch">Enable</label>
      </div>
    </div>
  </div>

  <!-- To Receive / To Pay Options at the bottom -->
  <div class="mt-4">
    <label class="form-label d-block">Transaction Type</label>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" id="toReceive" value="receive">
      <label class="form-check-label" for="toReceive">To Receive</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" id="toPay" value="pay">
      <label class="form-check-label" for="toPay">To Pay</label>
    </div>
  </div>
</div>
<div class="col-md-6">
  <label class="form-label fw-600">Party Type</label>

  <div class="form-check">
    <input class="form-check-input" type="radio" name="party_type" id="customerParty" value="customer" checked>
    <label class="form-check-label" for="customerParty">Customer Party</label>
  </div>

  <div class="form-check">
    <input class="form-check-input" type="radio" name="party_type" id="supplierParty" value="supplier">
    <label class="form-check-label" for="supplierParty">Supplier Party</label>
  </div>
</div>

            <!-- Additional Fields Tab -->
            <div class="tab-pane fade" id="partyAdditionalPane" role="tabpanel">
              <p class="text-muted mb-3" style="font-size:13px;">Add custom fields to track additional information.</p>
              <div class="row g-3">
                @for($i=1; $i<=4; $i++)
                <div class="col-md-6">
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="customField{{$i}}Check">
                    <label class="form-check-label" for="customField{{$i}}Check">Custom Field {{$i}}</label>
                  </div>
                  <input type="text" name="custom_fields[]" class="form-control form-control-sm" placeholder="Field name">
                </div>

                <input type="hidden" id="transactionTypeValue" name="transaction_type">
                @endfor

              </div>
            </div>
          </div>


          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" id="btnSaveNewParty">
              <i class="fa-solid fa-plus me-1"></i> Save & New
            </button>
            <button type="button" class="btn btn-primary" id="btnSaveParty">
              <i class="fa-solid fa-check me-1"></i> Save
            </button>

          </div>
        </form>
      </div>


    </div>
  </div>
</div>

  <!-- ═══════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════ -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script>
    window.bankAccounts = @json($bankAccounts ?? []);
    window.bankAccountRoutes = {
      store: "{{ route('bank-accounts.store') }}"
    };
  </script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/payment_in.js') }}"></script>
  <script>
    $(document).ready(function () {
      // Add New Party button - Open modal
      $('#addNewPartyBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const addPartyModal = new bootstrap.Modal(document.getElementById('addPartyModal'));
        addPartyModal.show();
      });

      const $searchInput = $("#paymentInSearch");
      const $periodSelect = $("#paymentInPeriodSelect");
      const $firmSelect = $("#paymentInFirmSelect");
      const $dateRangeDisplay = $("#paymentInDateRangeDisplay");
      const $customDateRange = $("#paymentInCustomDateRange");
      const $customFrom = $("#paymentInCustomFrom");
      const $customTo = $("#paymentInCustomTo");

      let globalSearch = "";
      let periodFilter = $periodSelect.val() || "all";
      let firmFilter = $firmSelect.val() || "";
      let customFrom = "";
      let customTo = "";

      function formatDisplayDate(date) {
        const dd = String(date.getDate()).padStart(2, "0");
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const yyyy = date.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
      }

      function formatIsoDate(date) {
        const dd = String(date.getDate()).padStart(2, "0");
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const yyyy = date.getFullYear();
        return `${yyyy}-${mm}-${dd}`;
      }

      function parseRowDate(value) {
        const parts = (value || "").trim().split(/[-\/]/);
        if (parts.length !== 3) return null;

        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const year = parseInt(parts[2], 10);

        if ([day, month, year].some(Number.isNaN)) return null;
        return new Date(year, month, day);
      }

      function updateRangeDisplay(from, to) {
        if (!from || !to) {
          $dateRangeDisplay.text("");
          return;
        }

        $dateRangeDisplay.text(`${formatDisplayDate(from)} To ${formatDisplayDate(to)}`);
      }

      function getPeriodRange(period) {
        const now = new Date();
        let start = null;
        let end = null;

        if (period === "this_month") {
          start = new Date(now.getFullYear(), now.getMonth(), 1);
          end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        } else if (period === "last_month") {
          start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
          end = new Date(now.getFullYear(), now.getMonth(), 0);
        } else if (period === "this_quarter") {
          const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3;
          start = new Date(now.getFullYear(), quarterStartMonth, 1);
          end = new Date(now.getFullYear(), quarterStartMonth + 3, 0);
        } else if (period === "this_year") {
          start = new Date(now.getFullYear(), 0, 1);
          end = new Date(now.getFullYear(), 11, 31);
        }

        return { start, end };
      }

      function setCustomMode(isCustom) {
        $dateRangeDisplay.toggleClass("d-none", isCustom);
        $customDateRange.toggleClass("d-none", !isCustom).toggleClass("d-flex", isCustom);
      }

      function rowMatchesPaymentInFilters(rowNode) {
        if (!rowNode) return true;

        const $row = $(rowNode);
        const rowText = $row.text().toLowerCase().replace(/\s+/g, " ").trim();
        const partyName = $row.find("td").eq(2).text().trim().toLowerCase();
        const rowDateText = $row.find("td").eq(0).text().trim();
        const rowDate = parseRowDate(rowDateText);

        let visible = true;

        if (paymentInFilterState.globalSearch && !rowText.includes(paymentInFilterState.globalSearch)) {
          visible = false;
        }

        if (visible && paymentInFilterState.firmFilter && partyName !== paymentInFilterState.firmFilter.toLowerCase()) {
          visible = false;
        }

        if (visible && paymentInFilterState.periodFilter !== "all") {
          let rangeStart = null;
          let rangeEnd = null;

          if (paymentInFilterState.periodFilter === "custom") {
            rangeStart = paymentInFilterState.customFrom ? new Date(paymentInFilterState.customFrom) : null;
            rangeEnd = paymentInFilterState.customTo ? new Date(paymentInFilterState.customTo) : null;
          } else {
            const range = getPeriodRange(paymentInFilterState.periodFilter);
            rangeStart = range.start;
            rangeEnd = range.end;
          }

          if (!rowDate || !rangeStart || !rangeEnd) {
            visible = false;
          } else {
            rangeStart.setHours(0, 0, 0, 0);
            rangeEnd.setHours(23, 59, 59, 999);
            rowDate.setHours(12, 0, 0, 0);

            if (rowDate < rangeStart || rowDate > rangeEnd) {
              visible = false;
            }
          }
        }

        return visible;
      }

      if ($.fn.dataTable && $.fn.dataTable.ext) {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
          if (!paymentInTable || settings.nTable.id !== "paymentInTable") {
            return true;
          }

          const rowNode = paymentInTable.row(dataIndex).node();
          return rowMatchesPaymentInFilters(rowNode);
        });
      }

      function applyPaymentInFilters() {
        if (paymentInTable) {
          paymentInTable.draw();
        }
      }

      function initializePeriodFilter() {
        if (paymentInFilterState.periodFilter === "custom") {
          const today = new Date();
          const todayIso = formatIsoDate(today);
          $customFrom.val(todayIso);
          $customTo.val(todayIso);
          paymentInFilterState.customFrom = todayIso;
          paymentInFilterState.customTo = todayIso;
          setCustomMode(true);
          return;
        }

        const range = getPeriodRange(paymentInFilterState.periodFilter);
        setCustomMode(false);
        updateRangeDisplay(range.start, range.end);
      }

      function initializePaymentInTable() {
        if (!$.fn.DataTable || paymentInTable || !$("#paymentInTable").length) {
          return;
        }

        paymentInTable = $("#paymentInTable").DataTable({
          paging: true,
          pageLength: 10,
          lengthChange: false,
          searching: true,
          ordering: true,
          autoWidth: false,
          order: [[0, "desc"]],
          columnDefs: [
            { targets: 6, orderable: false, searchable: false },
          ],
          language: {
            emptyTable: 'No payment in records yet. Click "Add Payment-in" to create one.',
          },
        });
      }

      $(".search-btn").click(function () {
        $(".search-container").toggleClass("active");
        $(".search-input").focus();
      });

      initializePeriodFilter();
      initializePaymentInTable();
      $(document).on("dblclick", "#paymentInTable tbody tr.payment-in-row", function (event) {
        if ($(event.target).closest(".dropdown, a, button, input, select, textarea, label").length) {
          return;
        }

        const editUrl = this.dataset.editUrl;
        if (editUrl) {
          window.location.href = editUrl;
        }
      });
      document.querySelectorAll("#paymentInTable .dropdown").forEach(function(dropdown) {
        dropdown.addEventListener("show.bs.dropdown", function() {
          document.getElementById("paymentInTableWrap")?.classList.add("dropdown-overflow-visible");
        });
        dropdown.addEventListener("hide.bs.dropdown", function() {
          document.getElementById("paymentInTableWrap")?.classList.remove("dropdown-overflow-visible");
        });
      });
      applyPaymentInFilters();

      $searchInput.on("input", function () {
        paymentInFilterState.globalSearch = $(this).val().toLowerCase().trim();
        applyPaymentInFilters();
      });

      $periodSelect.on("change", function () {
        paymentInFilterState.periodFilter = $(this).val() || "all";

        if (paymentInFilterState.periodFilter === "custom") {
          const today = new Date();
          const todayIso = formatIsoDate(today);
          $customFrom.val(todayIso);
          $customTo.val(todayIso);
          paymentInFilterState.customFrom = todayIso;
          paymentInFilterState.customTo = todayIso;
          setCustomMode(true);
        } else {
          const range = getPeriodRange(paymentInFilterState.periodFilter);
          setCustomMode(false);
          updateRangeDisplay(range.start, range.end);
        }

        applyPaymentInFilters();
      });

      $firmSelect.on("change", function () {
        paymentInFilterState.firmFilter = $(this).val() || "";
        applyPaymentInFilters();
      });

      $customFrom.on("change", function () {
        paymentInFilterState.customFrom = $(this).val() || "";
        applyPaymentInFilters();
      });

      $customTo.on("change", function () {
        paymentInFilterState.customTo = $(this).val() || "";
        applyPaymentInFilters();
      });

      $(document).on("input", ".payment-column-filter", function() {
        if (!paymentInTable) return;
        const columnIndex = parseInt(this.dataset.column || "0", 10);
        paymentInTable.column(columnIndex).search(this.value || "").draw();
      });

      function downloadPaymentInExcel(selectedColumns) {
        const visibleRows = Array.from(document.querySelectorAll("#paymentInTable tbody tr.payment-in-row"))
          .filter((row) => $(row).is(":visible"));

        let excelHtml = `
          <table border="1">
            <tr>
              ${selectedColumns.map((key) => `<th>${paymentPrintColumns[key].label}</th>`).join("")}
            </tr>
        `;

        visibleRows.forEach((row) => {
          const cells = row.querySelectorAll("td");
          const rowHtml = selectedColumns.map((key) => {
            const config = paymentPrintColumns[key];
            const value = typeof config.getValue === "function"
              ? config.getValue(row, cells)
              : (cells[config.index]?.textContent.trim() || "-");
            return `<td>${value}</td>`;
          }).join("");
          excelHtml += `<tr>${rowHtml}</tr>`;
        });

        excelHtml += "</table>";

        const blob = new Blob([`\ufeff${excelHtml}`], { type: "application/vnd.ms-excel;charset=utf-8;" });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        const now = new Date();
        const filename = `payment-in-${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}.xls`;

        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = "hidden";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
      }

      const paymentPrintModalEl = document.getElementById("paymentInPrintOptionsModal");
      const paymentPrintModal = paymentPrintModalEl ? new bootstrap.Modal(paymentPrintModalEl) : null;
      const paymentPrintColumns = {
        date: { label: "DATE", index: 0 },
        reference: { label: "Ref No.", index: 1 },
        party: { label: "Party Name", index: 2 },
        amount: { label: "TOTAL", index: 3 },
        bank: { label: "BANK ACCOUNT", index: 4 },
        payment_type: { label: "PAYMENT TYPE", index: 5 },
        description: { label: "DESCRIPTION", index: null, getValue: () => "-" },
        status: { label: "PAYMENT STATUS", index: null, getValue: () => "Used" },
      };

      function getSelectedPaymentPrintColumns() {
        return Array.from(document.querySelectorAll(".payment-print-option:checked"))
          .map((checkbox) => checkbox.value)
          .filter((key) => paymentPrintColumns[key]);
      }

      function openPaymentInExportModal(mode) {
        paymentInExportMode = mode === "excel" ? "excel" : "print";
        const title = paymentInExportMode === "excel" ? "Select Excel Options" : "Select Print Options";
        $("#paymentInPrintOptionsLabel").text(title);
        $("#confirmPaymentInPrint").text(paymentInExportMode === "excel" ? "Get Excel" : "Get Print");
        paymentPrintModal?.show();
      }

      window.openPaymentInExportModal = openPaymentInExportModal;

      function confirmPaymentInReport() {
        const visibleRows = Array.from(document.querySelectorAll("#paymentInTable tbody tr.payment-in-row"))
          .filter((row) => $(row).is(":visible"));

        if (!visibleRows.length) {
          alert("No visible records found to print.");
          return;
        }

        const selectedColumns = getSelectedPaymentPrintColumns();
        if (!selectedColumns.length) {
          alert("Print ke liye kam az kam aik option select karein.");
          return;
        }

        paymentPrintModal?.hide();

        if (paymentInExportMode === "excel") {
          downloadPaymentInExcel(selectedColumns);
          return;
        }

        const partyName = $("#paymentInFirmSelect").val() || "All Parties";
        const rangeDisplay = ($("#paymentInDateRangeDisplay").text() || "All Time").trim();
        const reportDate = new Date().toLocaleDateString("en-GB");
        const companyName = @json(config('app.name', 'My Company'));

        const headerHtml = selectedColumns
          .map((key) => `<th>${paymentPrintColumns[key].label}</th>`)
          .join("");

        const previewRowsHtml = visibleRows.map((row) => {
          const cells = row.querySelectorAll("td");
          const colsHtml = selectedColumns.map((key) => {
            const config = paymentPrintColumns[key];
            const value = typeof config.getValue === "function"
              ? config.getValue(row, cells)
              : (cells[config.index]?.textContent.trim() || "-");
            return `<td>${value}</td>`;
          }).join("");

          return `<tr>${colsHtml}</tr>`;
        }).join("");

        const totalAmount = visibleRows.reduce((sum, row) => {
          const amountText = row.querySelectorAll("td")[3]?.textContent || "0";
          const numeric = parseFloat(amountText.replace(/[^\d.-]/g, "")) || 0;
          return sum + numeric;
        }, 0);

        const previewHtml = `
          <!DOCTYPE html>
          <html lang="en">
            <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <title>Payment-In Preview</title>
              <style>
                * { box-sizing: border-box; }
                body {
                  margin: 0;
                  font-family: Arial, sans-serif;
                  background: #f3f4f6;
                  color: #111827;
                }
                .preview-shell {
                  max-width: 1180px;
                  margin: 24px auto;
                  background: #ffffff;
                  border: 1px solid #e5e7eb;
                  border-radius: 18px;
                  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
                  overflow: hidden;
                }
                .preview-header {
                  display: flex;
                  align-items: center;
                  justify-content: space-between;
                  padding: 18px 28px;
                  border-bottom: 1px solid #e5e7eb;
                }
                .preview-title {
                  font-size: 18px;
                  font-weight: 700;
                }
                .close-icon {
                  border: none;
                  background: transparent;
                  font-size: 28px;
                  line-height: 1;
                  color: #6b7280;
                  cursor: pointer;
                }
                .report-area {
                  padding: 40px 38px 28px;
                }
                .company-block {
                  text-align: center;
                  margin-bottom: 28px;
                }
                .company-block h2 {
                  margin: 0;
                  font-size: 20px;
                  font-weight: 700;
                }
                .company-block p {
                  margin: 6px 0 0;
                  color: #4b5563;
                  font-size: 13px;
                }
                .report-heading {
                  text-align: center;
                  font-size: 20px;
                  font-weight: 700;
                  text-decoration: underline;
                  margin: 0 0 28px;
                }
                .report-meta {
                  margin-bottom: 24px;
                }
                .report-meta p {
                  margin: 0 0 10px;
                  font-size: 15px;
                  font-weight: 700;
                }
                table {
                  width: 100%;
                  border-collapse: collapse;
                }
                thead th {
                  background: #e5e7eb;
                  font-size: 13px;
                  font-weight: 700;
                  padding: 12px 10px;
                  text-align: left;
                  border-bottom: 1px solid #d1d5db;
                }
                tbody td {
                  padding: 12px 10px;
                  border-bottom: 1px solid #e5e7eb;
                  font-size: 14px;
                }
                .total-row {
                  text-align: right;
                  font-size: 18px;
                  font-weight: 700;
                  margin-top: 20px;
                }
                .preview-actions {
                  display: flex;
                  justify-content: flex-end;
                  gap: 12px;
                  padding: 16px 28px 22px;
                  border-top: 1px solid #eef2f7;
                  flex-wrap: wrap;
                }
                .preview-actions button,
                .preview-actions a {
                  appearance: none;
                  border: 1px solid #f43f5e;
                  background: #fff;
                  color: #e11d48;
                  border-radius: 999px;
                  padding: 10px 18px;
                  font-size: 15px;
                  line-height: 1;
                  text-decoration: none;
                  cursor: pointer;
                }
                .preview-actions .primary-close {
                  background: #f43f5e;
                  color: #fff;
                }
                @media print {
                  body {
                    background: #fff;
                  }
                  .preview-shell {
                    margin: 0;
                    max-width: none;
                    border: none;
                    border-radius: 0;
                    box-shadow: none;
                  }
                  .preview-header,
                  .preview-actions {
                    display: none !important;
                  }
                  .report-area {
                    padding: 0;
                  }
                }
              </style>
            </head>
            <body>
              <div class="preview-shell">
                <div class="preview-header">
                  <div class="preview-title">Preview</div>
                  <button type="button" class="close-icon" onclick="window.close()">&times;</button>
                </div>

                <div class="report-area" id="paymentInPrintableArea">
                  <div class="company-block">
                    <h2>${companyName}</h2>
                    <p>Generated on: ${reportDate}</p>
                  </div>

                  <h1 class="report-heading">All Transactions Report</h1>

                  <div class="report-meta">
                    <p>Party name: ${partyName}</p>
                    <p>Transaction type: Payment-In</p>
                    <p>Duration: ${rangeDisplay}</p>
                  </div>

                  <table>
                    <thead>
                      <tr>
                        ${headerHtml}
                      </tr>
                    </thead>
                    <tbody>
                      ${previewRowsHtml}
                    </tbody>
                  </table>

                  <div class="total-row">Total: Rs ${totalAmount.toFixed(2)}</div>
                </div>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>
                <script>
                  function buildPaymentInPdfBlob() {
                    const element = document.getElementById('paymentInPrintableArea');
                    if (!window.html2pdf || !element) return Promise.resolve(null);

                    return window.html2pdf()
                      .set({
                        margin: 0.2,
                        filename: 'payment-in-report-${Date.now()}.pdf',
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true },
                        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
                      })
                      .from(element)
                      .outputPdf('blob');
                  }

                  function openPaymentInPreviewPdf() {
                    const element = document.getElementById('paymentInPrintableArea');
                    if (!window.html2pdf || !element) {
                      window.print();
                      return;
                    }

                    buildPaymentInPdfBlob()
                      .then(function(blob) {
                        if (!blob) {
                          window.print();
                          return;
                        }

                        const url = URL.createObjectURL(blob);
                        const popup = window.open(url, '_blank');
                        if (!popup) {
                          window.print();
                          return;
                        }

                        setTimeout(function() {
                          URL.revokeObjectURL(url);
                        }, 60000);
                      })
                      .catch(function() {
                        window.print();
                      });
                  }

                  function savePaymentInPreviewPdf() {
                    const element = document.getElementById('paymentInPrintableArea');
                    if (!window.html2pdf || !element) {
                      window.print();
                      return;
                    }

                    window.html2pdf().set({
                      margin: 0.2,
                      filename: 'payment-in-report-${Date.now()}.pdf',
                      image: { type: 'jpeg', quality: 0.98 },
                      html2canvas: { scale: 2, useCORS: true },
                      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
                    }).from(element).save();
                  }

                  function emailPaymentInPreview() {
                    const subject = encodeURIComponent('Payment-In Report');
                    const body = encodeURIComponent(
                      'Please find the Payment-In report attached after saving it as PDF from preview.'
                    );
                    const mailUrl = 'mailto:?subject=' + subject + '&body=' + body;
                    const opener = window.opener && !window.opener.closed ? window.opener : window;
                    try {
                      opener.location.href = mailUrl;
                    } catch (e) {
                      window.location.href = mailUrl;
                    }
                  }
                <\/script>
                <div class="preview-actions">
                  <button type="button" onclick="openPaymentInPreviewPdf()">Open PDF</button>
                  <button type="button" onclick="window.print()">Print</button>
                  <button type="button" onclick="savePaymentInPreviewPdf()">Save PDF</button>
                  <button type="button" onclick="emailPaymentInPreview()">Email PDF</button>
                  <button type="button" class="primary-close" onclick="window.close()">Close</button>
                </div>
              </div>
            </body>
          </html>
        `;

        const printWindow = window.open("", "_blank", "width=1280,height=900");
        if (!printWindow) {
          alert("Please allow popups to preview the report.");
          return;
        }

        printWindow.document.open();
        printWindow.document.write(previewHtml);
        printWindow.document.close();
      }

      window.confirmPaymentInReport = confirmPaymentInReport;
    });
  </script>

  @include('components.bank-account-modal')
  <script src="{{ asset('js/bank-account-modal.js') }}"></script>

  <script>

  document.addEventListener("DOMContentLoaded", function () {
    // Elements
    const dropdownBtn = document.getElementById("partyDropdownBtn");
    const dropdownMenu = document.getElementById("partyDropdownMenu");
    const RAW_BROKERS = @json(($brokers ?? collect())->values());
    const RAW_ITEMS = @json(($items ?? collect())->values());

    const partyIdInput = document.querySelector(".party-id");
    const phoneInput = document.querySelector(".phone-input");
    const billingInput = document.querySelector(".billing-address");
    const balanceDisplay = document.getElementById("partyBalanceDisplay");
    const selectedEntityTypeInput = document.getElementById("selectedEntityType");
    const selectedEntityIdInput = document.getElementById("selectedEntityId");
    const selectedEntityNameInput = document.getElementById("selectedEntityName");
    const partySearchInput = dropdownBtn;
    const partySearchNoResults = document.getElementById("partySearchNoResults");
    const partyDropdown = dropdownBtn?.closest('.dropdown');

    const addModalEl = document.getElementById('addPartyModal');
    const addModal = addModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(addModalEl) : null;

    const saveBtn = document.getElementById("btnSaveParty");
    const saveNewBtn = document.getElementById("btnSaveNewParty");

    function filterPartyOptions(query) {
        const normalizedQuery = String(query || '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('#partyDropdownMenu .entity-option').forEach(function(option) {
            const name = String(option.dataset.name || option.querySelector('.party-name')?.textContent || '').trim().toLowerCase();
            const phone = String(option.dataset.phone || '').toLowerCase();
            const billing = String(option.dataset.billing || '').toLowerCase();
            const matches = !normalizedQuery || name.includes(normalizedQuery) || phone.includes(normalizedQuery) || billing.includes(normalizedQuery);

            option.classList.toggle('d-none', !matches);
            if (matches) {
                visibleCount += 1;
            }
        });

        if (partySearchNoResults) {
            partySearchNoResults.classList.toggle('d-none', visibleCount > 0 || !normalizedQuery);
        }
    }

    function showPartyDropdown() {
        if (!dropdownBtn || !dropdownMenu) return;
        dropdownMenu.classList.add('show');
        dropdownBtn.setAttribute('aria-expanded', 'true');
    }

    function hidePartyDropdown() {
        if (!dropdownBtn || !dropdownMenu) return;
        dropdownMenu.classList.remove('show');
        dropdownBtn.setAttribute('aria-expanded', 'false');
    }

    function setSelectedEntity(entityType, entityId, entityName) {
        if (selectedEntityTypeInput) selectedEntityTypeInput.value = entityType || 'party';
        if (selectedEntityIdInput) selectedEntityIdInput.value = entityId || '';
        if (selectedEntityNameInput) selectedEntityNameInput.value = entityName || '';
    }

    partySearchInput?.addEventListener('focus', function() {
        showPartyDropdown();
        filterPartyOptions(this.value || '');
    });

    ['click', 'mousedown', 'keydown', 'keyup'].forEach(function(eventName) {
        partySearchInput?.addEventListener(eventName, function(event) {
            event.stopPropagation();
        });
    });

    partySearchInput?.addEventListener('click', function() {
        showPartyDropdown();
        filterPartyOptions(this.value || '');
    });

    partySearchInput?.addEventListener('input', function(event) {
        showPartyDropdown();
        filterPartyOptions(event.target.value);
    });

    partySearchInput?.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        event.stopPropagation();

        const searchTerm = String(this.value || '').trim();
        if (!searchTerm) return;

        const options = Array.from(dropdownMenu.querySelectorAll('.entity-option'));
        const exactOption = options.find((opt) => {
            const name = String(opt.dataset.name || opt.querySelector('.party-name')?.textContent || '').trim().toLowerCase();
            return name === searchTerm.toLowerCase();
        });

        if (exactOption) {
            exactOption.click();
            return;
        }

        addModal?.show();
        const nameInput = document.getElementById('partyNameInput');
        if (nameInput) {
            nameInput.value = searchTerm;
            nameInput.focus();
        }
    });

    document.addEventListener('click', function(event) {
        if (partyDropdown?.contains(event.target)) {
            return;
        }

        hidePartyDropdown();
        if (partySearchInput && !partyIdInput?.value) {
            partySearchInput.value = '';
            filterPartyOptions('');
        }
    });

    document.addEventListener('click', function(e) {
        const option = e.target.closest('.entity-option');
        const addNew = e.target.closest('#addNewPartyBtn');
        if (!option && !addNew) return;

        if (option) {
            const entityType = String(option.dataset.entityType || 'party').toLowerCase();
            const entityName = option.querySelector('.party-name')?.innerText || '';
            const partyId = option.dataset.id || '';
            const phone = option.dataset.phone || '';
            const billing = option.dataset.billing || '';
            const currentBalance = parseFloat(option.dataset.currentBalance || 0) || 0;

            dropdownBtn.value = entityName;
            partyIdInput.value = entityType === 'party' ? partyId : '';
            phoneInput.value = phone;
            billingInput.value = billing;
            setSelectedEntity(entityType, partyId, entityName);
            resetLinkPaymentState();
            $('#linkPaymentBtn').toggle(entityType === 'party');

            if (balanceDisplay) {
                if (entityType !== 'party') {
                    balanceDisplay.classList.add('d-none');
                } else {
                balanceDisplay.classList.remove('d-none', 'balance-positive', 'balance-negative', 'balance-zero');
                balanceDisplay.classList.add(
                    currentBalance > 0 ? 'balance-positive' : currentBalance < 0 ? 'balance-negative' : 'balance-zero'
                );
                const valueNode = balanceDisplay.querySelector('.balance-value');
                if (valueNode) {
                    valueNode.textContent = `Rs ${Math.abs(currentBalance).toFixed(2)}`;
                }
                }
            }

            filterPartyOptions('');
            hidePartyDropdown();
            return;
        }

        if (addNew) {
            window.location.href = '/dashboard/parties';
        }
    });

    // SAVE PARTY FUNCTION
    function saveParty(closeAfterSave = true) {
        const form = document.getElementById("addPartyForm");
        if(!form) return;

        const data = new FormData(form);

        // Transaction type
        const toReceive = document.getElementById("toReceive")?.checked;
        const toPay = document.getElementById("toPay")?.checked;
        if(toReceive) data.set("transaction_type", "receive");
        else if(toPay) data.set("transaction_type", "pay");

        // Credit limit
        const creditSwitch = document.getElementById("creditLimitSwitch");
        data.set("credit_limit_enabled", creditSwitch?.checked ? 1 : 0);

        fetch("{{ route('parties.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json"
            },
            body: data
        })
        .then(res => res.json())
        .then(res => {
            if(res.success && res.party) {
                // Add the new party to the dropdown
                const party = res.party;
                const newOption = document.createElement('li');
                newOption.className = 'entity-option party-option dropdown-item d-flex justify-content-between align-items-center';
                newOption.style.cursor = 'pointer';
                newOption.dataset.id = party.id;
                newOption.dataset.entityType = 'party';
                newOption.dataset.currentBalance = party.current_balance ?? party.opening_balance ?? 0;
                newOption.dataset.type = party.transaction_type || '';
                newOption.dataset.phone = party.phone || '';
                newOption.dataset.billing = party.billing_address || '';
                newOption.dataset.name = String(party.name || '').trim().toLowerCase();
                newOption.innerHTML = `
                    <span class="party-name cursor-pointer">${party.name}</span>
                    <span class="party-balance small text-muted">Rs ${parseFloat(party.current_balance ?? party.opening_balance ?? 0).toFixed(2)}</span>
                `;

                // Insert before the "Add New Party" button
                const addNewBtn = document.getElementById('addNewPartyBtn');
                addNewBtn.parentNode.insertBefore(newOption, addNewBtn);

                // Auto-select the newly created party
                const partyName = party.name;
                dropdownBtn.value = partyName || '';
                partyIdInput.value = party.id;
                phoneInput.value = party.phone || '';
                billingInput.value = party.billing_address || '';
                setSelectedEntity('party', party.id, partyName || '');
                if (balanceDisplay) {
                    const newBalance = parseFloat(party.current_balance ?? party.opening_balance ?? 0) || 0;
                    balanceDisplay.classList.remove('d-none', 'balance-positive', 'balance-negative', 'balance-zero');
                    balanceDisplay.classList.add(
                        newBalance > 0 ? 'balance-positive' : newBalance < 0 ? 'balance-negative' : 'balance-zero'
                    );
                    const valueNode = balanceDisplay.querySelector('.balance-value');
                    if (valueNode) {
                        valueNode.textContent = `Rs ${Math.abs(newBalance).toFixed(2)}`;
                    }
                }
                resetLinkPaymentState();

                // Close the modal
                addModal?.hide();

                // Show success message (simple notification)
                const successMsg = document.createElement('div');
                successMsg.style.cssText = 'position:fixed; top:20px; right:20px; background:#10b981; color:white; padding:12px 20px; border-radius:6px; z-index:9999; font-weight:600;';
                successMsg.textContent = 'Party created successfully!';
                document.body.appendChild(successMsg);
                setTimeout(() => successMsg.remove(), 3000);

                // Reset form for next use
                form.reset();

                // Close party dropdown
                document.body.click();
            } else {
                alert(res.message || "Error saving party");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Something went wrong! Check console.");
        });
    }

    // BUTTON LISTENERS
    saveBtn?.addEventListener('click', () => saveParty(true));
    saveNewBtn?.addEventListener('click', () => saveParty(false));
});
</script>

  <script>
document.getElementById("addPaymentRow").addEventListener("click", function () {
    const container = document.getElementById("paymentContainer");

    const newRow = document.createElement("div");
    newRow.classList.add("payment-row", "payment-row--entry");

    newRow.innerHTML = `
        <div class="payment-row-line">
            <div class="payment-type-block">
              <label class="form-label">Payment Type</label>
              <div class="payment-type-line">
                <select class="form-select payment-type-select payment-type-entry" data-default-payment-type="cash"></select>

              </div>
              <input type="hidden" class="payment-bank" name="bank_account_id" value="">
            </div>

            <div class="payment-amount-block">
                <label class="form-label">Amount</label>
                <input type="number" class="form-control payment-amount" placeholder="0">
            </div>

            <div class="payment-trash-block d-flex align-items-center justify-content-center">
                <button type="button" class="remove-row border-0 bg-transparent text-secondary" style="font-size:18px;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>

        <div class="payment-reference-block">
            <label class="form-label text-secondary">Reference No</label>
            <input type="text" class="form-control payment-reference" placeholder="Reference No.">
        </div>
    `;
  const shell = container.querySelector('.payment-row-shell');
  const addLink = shell?.querySelector('.payment-add-link');
  if (shell && addLink) {
    shell.insertBefore(newRow, addLink);
  } else {
    shell?.appendChild(newRow);
  }
  const $newRow = $(newRow);
  populatePaymentTypeSelect($newRow.find('.payment-type-select'));
  togglePaymentBankRow($newRow);
  updateReceivedTotal();
});

// Remove row
document.addEventListener("click", function (e) {
    if (e.target.closest(".remove-row")) {
        e.target.closest(".payment-row").remove();
        updateReceivedTotal();
    }
});

function togglePaymentBankRow($row) {
  const type = $row.find('.payment-type-select').val() || '';
  const $bankInput = $row.find('.payment-bank');

  if ($bankInput.length) {
    if (type === 'bank' || type === 'cheque' || String(type).startsWith('bank:')) {
      const currentValue = String($bankInput.val() || '').trim();
      const extractedBankId = extractBankAccountId(type);
      if (extractedBankId) {
        $bankInput.val(extractedBankId);
      } else
      if (!currentValue && window.paymentInDefaultBankAccountId) {
        $bankInput.val(window.paymentInDefaultBankAccountId);
      }
    } else {
      $bankInput.val('');
    }
  }

  return type;
}

document.addEventListener("change", function (e) {
    const select = e.target.closest('.payment-type-select');
    if (!select) return;
    togglePaymentBankRow($(select).closest('.payment-row'));
});

document.addEventListener("DOMContentLoaded", function () {
  populateAllPaymentTypeSelects();
  const firstRow = $('#paymentContainer .payment-row').first();
  if (firstRow.length) {
    togglePaymentBankRow(firstRow);
  }
});

function extractBankAccountId(type) {
  const raw = String(type || '').trim();
  if (!raw.startsWith('bank:')) return '';
  if (raw.startsWith('bank:')) return raw.slice(5).trim();
  if (raw.startsWith('bank-')) return raw.slice(5).trim();
  return '';
}

function paymentTypeOptionsHtml(currentValue) {
  const accounts = Array.isArray(window.bankAccounts) ? window.bankAccounts : [];
  const options = [];
  const selected = String(currentValue || 'cash').trim();
  const escapeHtml = (text) => String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  options.push(`<option value="cash"${selected === 'cash' ? ' selected' : ''}>Cash</option>`);
  options.push(`<option value="bank"${selected === 'bank' ? ' selected' : ''}>Bank</option>`);
  options.push(`<option value="cheque"${selected === 'cheque' ? ' selected' : ''}>Cheque</option>`);
  if (accounts.length) {
    options.push('<option value="" disabled>──────── Bank Accounts ────────</option>');
    accounts.forEach(function(acc) {
      const label = acc.display_name || acc.bank_name || acc.account_holder_name || ('Account ' + (acc.id || ''));
      const suffix = acc.account_number ? ' - ' + String(acc.account_number).replace(/\s+/g, '') : '';
      const value = `bank:${acc.id}`;
      options.push(`<option value="${value}"${selected === value ? ' selected' : ''}>${escapeHtml(label + suffix)}</option>`);
    });
    // special option to add a new bank account (handled by bank-account-modal.js)
    options.push('<option value="add_new_bank">+ Add Bank Account</option>');
  }
  return options.join('');
}

function populatePaymentTypeSelect($select) {
  if (!$select || !$select.length) return;
  const currentValue = String($select.val() || $select.data('default-payment-type') || 'cash').trim();
  $select.html(paymentTypeOptionsHtml(currentValue));
}

function populateAllPaymentTypeSelects() {
  document.querySelectorAll('.payment-type-select').forEach(function(el) {
    populatePaymentTypeSelect($(el));
  });
}

function updateReceivedTotal() {
    let total = 0;
    document.querySelectorAll('.payment-amount').forEach(input => {
        total += parseFloat(input.value || 0) || 0;
    });

    const receivedInput = document.getElementById('receivedAmount');
    if (receivedInput) {
        receivedInput.value = total.toFixed(2).replace(/\.00$/, '');
    }

    const linkReceivedInput = document.getElementById('linkPaymentReceivedInput');
    if (linkReceivedInput) {
        linkReceivedInput.value = total.toFixed(2);
    }

    const totalDisplay = document.getElementById('paymentInTotalDisplay');
    if (totalDisplay) {
        totalDisplay.textContent = total.toFixed(2).replace(/\.00$/, '');
    }

    const rowTotalDisplay = document.getElementById('paymentRowTotalDisplay');
    if (rowTotalDisplay) {
        rowTotalDisplay.textContent = total.toFixed(2).replace(/\.00$/, '');
    }

    const discountDisplay = document.getElementById('paymentInDiscountDisplay');
    if (discountDisplay && !discountDisplay.value) {
        discountDisplay.value = '0';
    }

    refreshLinkPaymentSummary();
}

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('payment-amount')) {
        updateReceivedTotal();
    }
});

document.getElementById('toggleDescriptionBtn')?.addEventListener('click', function() {
    this.classList.add('d-none');
    const box = document.getElementById('descriptionBox');
    box?.classList.remove('d-none');
    box?.querySelector('textarea')?.focus();
});

document.getElementById('toggleImageBtn')?.addEventListener('click', function() {
    const box = document.getElementById('imageUploadBox');
    this.classList.add('d-none');
    box?.classList.remove('d-none');
    document.getElementById('paymentImageInput')?.click();
});

document.getElementById('imagePlaceholder')?.addEventListener('click', function() {
    document.getElementById('paymentImageInput')?.click();
});

document.getElementById('paymentImageInput')?.addEventListener('change', function(e) {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;
    const selectedName = document.getElementById('imageSelectedName');
    const previewWrap = document.getElementById('imagePreviewWrap');
    const placeholder = document.getElementById('imagePlaceholder');
    if (selectedName) {
      selectedName.textContent = `Selected: ${files.map((file) => file.name).join(', ')}`;
      selectedName.classList.remove('d-none');
    }
    if (previewWrap) {
      previewWrap.innerHTML = '';
      files.forEach((file) => {
        const img = document.createElement('img');
        img.alt = file.name;
        img.src = URL.createObjectURL(file);
        img.style.maxWidth = '100%';
        img.style.maxHeight = '120px';
        img.style.borderRadius = '8px';
        img.style.border = '1px solid #e5e7eb';
        img.style.objectFit = 'cover';
        previewWrap.appendChild(img);
      });
      previewWrap.classList.remove('d-none');
    }
    if (placeholder) {
      placeholder.classList.add('d-none');
    }
});

function buildPaymentInShareText() {
    const receiptNo = document.getElementById('receiptNo')?.value || '';
    const partyName = document.querySelector('.party-dropdown-input')?.value || document.getElementById('selectedEntityName')?.value || '';
    const amount = document.getElementById('receivedAmount')?.value || '0';
    const date = document.querySelector('input[name="date"]')?.value || '';
    return `Payment In${receiptNo ? ' #' + receiptNo : ''}\nParty: ${partyName}\nDate: ${date}\nAmount: ${amount}`;
}

document.getElementById('paymentInShareEmail')?.addEventListener('click', function(e) {
    e.preventDefault();
    const subject = encodeURIComponent('Payment In');
    const body = encodeURIComponent(buildPaymentInShareText() + '\n\nOpen: ' + window.location.href);
    window.open(`mailto:?subject=${subject}&body=${body}`, '_blank', 'noopener');
});

document.getElementById('paymentInShareWhatsApp')?.addEventListener('click', function(e) {
    e.preventDefault();
    const text = encodeURIComponent(buildPaymentInShareText() + '\n' + window.location.href);
    window.open(`https://wa.me/?text=${text}`, '_blank', 'noopener');
});

document.getElementById('paymentInShareLink')?.addEventListener('click', async function(e) {
    e.preventDefault();
    try {
        await navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard');
    } catch (error) {
        window.prompt('Copy this link:', window.location.href);
    }
});

function openPaymentInPdf(url) {
    window.open(url, '_blank');
}

const editPaymentIn = @json($editPaymentIn ?? null);
const duplicatePaymentIn = @json($duplicatePaymentIn ?? null);
const paymentInDefaultReceiptNo = @json($nextReceiptNo > 0 ? $nextReceiptNo : 1);
let linkPaymentRows = [];
let appliedLinkPaymentRows = [];
let paymentInTable = null;
let paymentInExportMode = 'print';
const paymentInFilterState = {
    globalSearch: '',
    firmFilter: '',
    periodFilter: 'all',
    customFrom: '',
    customTo: '',
};

function resetLinkPaymentState() {
    linkPaymentRows = [];
    appliedLinkPaymentRows = [];

    const hiddenField = document.getElementById('linkedRowsJson');
    if (hiddenField) {
        hiddenField.value = '[]';
    }

    const tbody = document.getElementById('linkPaymentRows');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">Select a party to load transactions.</td></tr>';
    }

    refreshLinkPaymentSummary();
}

function getLinkPaymentModalInstance() {
    const modalElement = document.getElementById('linkPaymentModal');
    return modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
}

function formatLinkPaymentCurrency(value) {
    return Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getCurrentReceivedAmount() {
    return parseFloat(document.getElementById('receivedAmount')?.value || 0) || 0;
}

function getLinkReceivedAmount() {
    return parseFloat(document.getElementById('linkPaymentReceivedInput')?.value || 0) || 0;
}

function calculateLinkedTotal() {
    return linkPaymentRows.reduce((sum, row) => sum + (parseFloat(row.selected_amount || 0) || 0), 0);
}

function refreshLinkPaymentSummary() {
    const receivedAmount = getLinkReceivedAmount() || getCurrentReceivedAmount();
    const linkedTotal = calculateLinkedTotal();
    const unusedAmount = receivedAmount - linkedTotal;
    const unusedEl = document.getElementById('linkPaymentUnusedAmount');

    if (unusedEl) {
        unusedEl.textContent = formatLinkPaymentCurrency(unusedAmount);
        unusedEl.classList.toggle('unused-amount-negative', unusedAmount < 0);
    }
}

function persistAppliedLinkRows() {
    const cleaned = linkPaymentRows
        .filter(row => (parseFloat(row.selected_amount || 0) || 0) > 0)
        .map(row => ({
            sale_id: row.sale_id,
            amount: Number(parseFloat(row.selected_amount).toFixed(2)),
        }));

    appliedLinkPaymentRows = cleaned;
    const hiddenField = document.getElementById('linkedRowsJson');
    if (hiddenField) {
        hiddenField.value = JSON.stringify(cleaned);
    }
}

function renderLinkPaymentRows() {
    const tbody = document.getElementById('linkPaymentRows');
    if (!tbody) return;

    const typeFilter = (document.getElementById('linkPaymentTypeFilter')?.value || 'all').toLowerCase();
    const search = (document.getElementById('linkPaymentSearch')?.value || '').trim().toLowerCase();

    const filteredRows = linkPaymentRows.filter((row) => {
        const matchesType = typeFilter === 'all' || row.type.toLowerCase() === typeFilter;
        const haystack = `${row.date} ${row.type} ${row.ref_no}`.toLowerCase();
        const matchesSearch = !search || haystack.includes(search);
        return matchesType && matchesSearch;
    });

    if (!filteredRows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">No transactions found.</td></tr>';
        refreshLinkPaymentSummary();
        return;
    }

    tbody.innerHTML = filteredRows.map((row) => {
        const selectedAmount = parseFloat(row.selected_amount || 0) || 0;
        const maxAmount = parseFloat(row.balance || 0) || 0;

        return `
            <tr data-sale-id="${row.sale_id}">
                <td>
                    <input type="checkbox" class="form-check-input link-payment-check" data-sale-id="${row.sale_id}" ${selectedAmount > 0 ? 'checked' : ''}>
                </td>
                <td>${row.date}</td>
                <td>${row.type}</td>
                <td>${row.ref_no}</td>
                <td class="text-end">${formatLinkPaymentCurrency(row.total)}</td>
                <td class="text-end">${formatLinkPaymentCurrency(row.balance)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm link-payment-amount" data-sale-id="${row.sale_id}" min="0" max="${maxAmount}" step="0.01" value="${selectedAmount > 0 ? selectedAmount.toFixed(2) : ''}" ${selectedAmount > 0 ? '' : 'disabled'}>
                </td>
            </tr>
        `;
    }).join('');

    refreshLinkPaymentSummary();
}

function loadLinkableSales(partyId, options = {}) {
    const tbody = document.getElementById('linkPaymentRows');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">Loading transactions...</td></tr>';
    }

    const query = new URLSearchParams();
    if (options.paymentInId) {
        query.set('payment_in_id', options.paymentInId);
    }

    fetch(`/dashboard/payments-in/linkable-sales/${partyId}?${query.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        credentials: 'same-origin'
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Transactions load nahi ho sakin.');
        }
        return data;
    })
    .then((data) => {
        document.getElementById('linkPaymentPartyName').textContent = data.party?.name || '-';

        linkPaymentRows = (data.rows || []).map((row) => {
            const applied = appliedLinkPaymentRows.find((item) => String(item.sale_id) === String(row.sale_id));
            return {
                ...row,
                selected_amount: applied ? applied.amount : (parseFloat(row.linked_amount || 0) || 0),
            };
        });

        renderLinkPaymentRows();
    })
    .catch((error) => {
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" class="link-payment-empty">${error.message || 'Transactions load nahi ho sakin.'}</td></tr>`;
        }
    });
}

function openLinkPaymentModal() {
    const partyId = $('.party-id').val();
    const partyName = $('#partyDropdownBtn').val().trim();

    updateReceivedTotal();
    document.getElementById('linkPaymentPartyName').textContent = partyName || '-';
    document.getElementById('linkPaymentReceivedInput').value = (getCurrentReceivedAmount() || 0).toFixed(2);
    document.getElementById('linkPaymentTypeFilter').value = 'all';
    document.getElementById('linkPaymentSearch').value = '';

    if (partyId) {
        loadLinkableSales(partyId, {
            paymentInId: $('#paymentInId').val() || '',
        });
    } else {
        resetLinkPaymentState();
    }

    getLinkPaymentModalInstance()?.show();
}

function autoAllocateLinkPayments() {
    let remaining = getLinkReceivedAmount();

    linkPaymentRows = linkPaymentRows.map((row) => {
        const available = parseFloat(row.balance || 0) || 0;
        const allocate = Math.max(0, Math.min(remaining, available));
        remaining -= allocate;

        return {
            ...row,
            selected_amount: Number(allocate.toFixed(2)),
        };
    });

    renderLinkPaymentRows();
}

function populateEditPaymentIn(paymentIn) {
    if (!paymentIn) return;

    const modalElement = document.getElementById('addPaymentInModal');
    const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
    const firstRow = $('#paymentContainer .payment-row').first();

    $('#paymentInId').val(paymentIn.id || '');
    $('.party-id').val(paymentIn.party_id || '');
    $('#selectedEntityType').val(paymentIn.entity_type || (paymentIn.party_id ? 'party' : 'party'));
    $('#selectedEntityId').val(paymentIn.entity_id || paymentIn.party_id || '');
    $('#selectedEntityName').val(paymentIn.entity_name || paymentIn.party?.name || '');
    $('#partyDropdownBtn').val(paymentIn.entity_name || paymentIn.party?.name || '');
    $('#receiptNo').val(paymentIn.receipt_no || '');
    $('input[name="date"]').val(paymentIn.date || '');
    $('#paymentDescription').val(paymentIn.description || '');
    $('#referenceNo').val(paymentIn.reference_no || '');
    appliedLinkPaymentRows = (paymentIn.links || []).map((link) => ({
        sale_id: link.sale_id,
        amount: Number(parseFloat(link.linked_amount || 0).toFixed(2)),
    }));
    $('#linkedRowsJson').val(JSON.stringify(appliedLinkPaymentRows));

    $('#paymentContainer .payment-row').not(':first').remove();
    firstRow.find('.payment-bank').val(paymentIn.bank_account_id || '');
    firstRow.find('.payment-amount').val(paymentIn.amount || '');
    firstRow.find('.payment-reference').val(paymentIn.reference_no || '');
    firstRow.find('.payment-type-select').val((paymentIn.payment_type || 'bank').toLowerCase());
    togglePaymentBankRow(firstRow);
    if ((paymentIn.entity_type || 'party') !== 'party') {
        $('#partyBalanceDisplay').addClass('d-none');
        $('#linkPaymentBtn').hide();
    } else {
        $('#linkPaymentBtn').show();
    }

    updateReceivedTotal();
    modal?.show();
}

function populateDuplicatePaymentIn(paymentIn) {
  if (!paymentIn) return;

  populateEditPaymentIn(paymentIn);
  $('#paymentInId').val('');
  $('#receiptNo').val(paymentInDefaultReceiptNo);
  $('#addPaymentInModalLabel').text('Duplicate Payment-in');
  $('#selectedEntityType').val(paymentIn.entity_type || 'party');
  $('#selectedEntityId').val(paymentIn.entity_id || paymentIn.party_id || '');
  $('#selectedEntityName').val(paymentIn.entity_name || paymentIn.party?.name || '');
}

if (editPaymentIn) {
    populateEditPaymentIn(editPaymentIn);
}

if (duplicatePaymentIn) {
    populateDuplicatePaymentIn(duplicatePaymentIn);
}

document.getElementById('openLinkPaymentBtn')?.addEventListener('click', openLinkPaymentModal);
document.getElementById('linkPaymentAutoBtn')?.addEventListener('click', autoAllocateLinkPayments);
document.getElementById('linkPaymentResetBtn')?.addEventListener('click', function() {
    linkPaymentRows = linkPaymentRows.map((row) => ({ ...row, selected_amount: 0 }));
    renderLinkPaymentRows();
});
document.getElementById('linkPaymentTypeFilter')?.addEventListener('change', renderLinkPaymentRows);
document.getElementById('linkPaymentSearch')?.addEventListener('input', renderLinkPaymentRows);
document.getElementById('linkPaymentReceivedInput')?.addEventListener('input', refreshLinkPaymentSummary);
document.getElementById('linkPaymentDoneBtn')?.addEventListener('click', function() {
    const received = getLinkReceivedAmount();
    const linked = calculateLinkedTotal();

    if (linked - received > 0.001) {
        alert('Linked amount received amount se zyada nahi ho sakta.');
        return;
    }

    persistAppliedLinkRows();
    getLinkPaymentModalInstance()?.hide();
});

document.addEventListener('change', function(event) {
    if (event.target.classList.contains('link-payment-check')) {
        const saleId = event.target.dataset.saleId;
        const row = linkPaymentRows.find((item) => String(item.sale_id) === String(saleId));
        if (!row) return;

        if (event.target.checked) {
            const currentSelected = parseFloat(row.selected_amount || 0) || 0;
            if (currentSelected <= 0) {
                const availableAmount = Math.min(parseFloat(row.balance || 0) || 0, Math.max(getLinkReceivedAmount() - calculateLinkedTotal(), 0));
                row.selected_amount = Number(Math.max(availableAmount, 0).toFixed(2));
                if (row.selected_amount === 0) {
                    row.selected_amount = Number((parseFloat(row.balance || 0) || 0).toFixed(2));
                }
            }
        } else {
            row.selected_amount = 0;
        }

        renderLinkPaymentRows();
        refreshLinkPaymentSummary();
    }
});

document.addEventListener('input', function(event) {
    if (event.target.classList.contains('link-payment-amount')) {
        const saleId = event.target.dataset.saleId;
        const row = linkPaymentRows.find((item) => String(item.sale_id) === String(saleId));
        if (!row) return;

        const max = parseFloat(row.balance || 0) || 0;
        let value = parseFloat(event.target.value || 0) || 0;
        value = Math.max(0, Math.min(value, max));
        row.selected_amount = Number(value.toFixed(2));
        const checkbox = document.querySelector(`.link-payment-check[data-sale-id="${saleId}"]`);
        if (checkbox) {
            checkbox.checked = value > 0;
        }
        refreshLinkPaymentSummary();
    }
});


$('#paymentInForm').on('submit', function(e) {
    e.preventDefault();

    const payments = [];
    $('#paymentContainer .payment-row').each(function() {
        const rawType = String($(this).find('.payment-type-select').val() || '').trim();
        let type = rawType;
        const amount = $(this).find('.payment-amount').val();
        let bank_account_id = $(this).find('.payment-bank').val();
        const reference = $(this).find('.payment-reference').val();

        if (rawType.startsWith('bank:')) {
            bank_account_id = extractBankAccountId(rawType);
            type = 'bank';
        }

        if ((rawType === 'bank' || rawType === 'cheque') && !bank_account_id && window.paymentInDefaultBankAccountId) {
            bank_account_id = window.paymentInDefaultBankAccountId;
        }

        if(type && amount) {
            payments.push({ type, amount, bank_account_id, reference });
        }
    });

    $('#referenceNo').val($('.payment-reference').first().val() || '');
    updateReceivedTotal();

    let linkedRows = [];
    try {
        linkedRows = JSON.parse($('#linkedRowsJson').val() || '[]');
    } catch (error) {
        linkedRows = [];
    }

    const paymentInId = $('#paymentInId').val();
    const requestUrl = paymentInId ? `/dashboard/payments-in/${paymentInId}` : '/dashboard/payments-in';
    const spoofMethod = paymentInId ? 'PUT' : 'POST';
    const formData = new FormData();

    formData.append('party_id', $('.party-id').val() || '');
    formData.append('entity_type', $('#selectedEntityType').val() || 'party');
    formData.append('entity_id', $('#selectedEntityId').val() || '');
    formData.append('entity_name', $('#selectedEntityName').val() || $('#partyDropdownBtn').val().trim() || '');
    formData.append('reference_no', $('#referenceNo').val() || '');
    formData.append('receipt_no', $('#receiptNo').val() || '');
    formData.append('date', $('input[name="date"]').val() || '');
    formData.append('received', $('#receivedAmount').val() || '');
    formData.append('description', $('#paymentDescription').val() || '');
    formData.append('_method', spoofMethod);

    payments.forEach((payment, index) => {
        formData.append(`payments[${index}][type]`, payment.type);
        formData.append(`payments[${index}][amount]`, payment.amount);
        formData.append(`payments[${index}][bank_account_id]`, payment.bank_account_id || '');
        formData.append(`payments[${index}][reference]`, payment.reference || '');
    });

    linkedRows.forEach((row, index) => {
        formData.append(`linked_rows[${index}][sale_id]`, row.sale_id);
        formData.append(`linked_rows[${index}][amount]`, row.amount);
    });

    Array.from(document.getElementById('paymentImageInput')?.files || []).forEach((file) => {
        formData.append('attachments[]', file);
    });

    $.ajax({
        url: requestUrl,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.redirect_url) {
                window.location.href = res.redirect_url;
                return;
            }

            window.location.reload();
        },
        error: function(xhr) {
            console.log(xhr.responseJSON);
            if(xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let msg = '';
                for(const field in errors) {
                    msg += field + ': ' + errors[field].join(', ') + '\n';
                }
                alert('Validation Error:\n' + msg);
            } else {
                const serverMessage = xhr.responseJSON?.message || xhr.responseText || 'Something went wrong. Please try again.';
                alert(serverMessage);
            }
        }
    });
});

// View Payment History Function - Display in Table Format
function viewPaymentHistory(paymentInId) {
    const historyUrl = `/dashboard/payments-in/${paymentInId}/history`;

    $.ajax({
        url: historyUrl,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        success: function(res) {
            if(res.success && res.history && res.history.length > 0) {
                // Remove old modal if exists
                $('#paymentHistoryModal').remove();

                let historyHtml = `
                    <div class="modal fade" id="paymentHistoryModal" tabindex="-1">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fa-solid fa-history me-2"></i>Payment History (${res.total_records} Records)
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                `;

                // Add payment details summary
                if(res.payment_details) {
                    historyHtml += `
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2"><strong>📋 Payment Details Summary:</strong></h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small><strong>Entry No:</strong> ${res.payment_details.entry_no || '-'}</small><br>
                                    <small><strong>Receipt No:</strong> ${res.payment_details.receipt_no || '-'}</small><br>
                                    <small><strong>Reference No:</strong> ${res.payment_details.reference_no || '-'}</small><br>
                                    <small><strong>Amount:</strong> <span class="text-success fw-bold">₹${res.payment_details.amount}</span></small>
                                </div>
                                <div class="col-md-6">
                                    <small><strong>Payment Type:</strong> <span class="badge bg-info">${res.payment_details.payment_type || '-'}</span></small><br>
                                    <small><strong>Date:</strong> ${res.payment_details.date || '-'}</small>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Add table format history
                historyHtml += `
                       <div class="table-wrapper">
  <table class="table align-middle custom-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 10%;">Entry No</th>
                                        <th style="width: 12%;">Date & Time</th>
                                        <th style="width: 16%;">Action</th>
                                        <th style="width: 12%;">Amount</th>
                                        <th style="width: 14%;">Reference</th>
                                        <th style="width: 14%;">Receipt</th>
                                        <th style="width: 12%;">Type</th>
                                        <th style="width: 10%;">User</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                  res.history.forEach((entry, index) => {
                      const timestamp = entry.created_at || entry.updated_at || '-';
                      const user = entry.user_name || 'System';
                      const action = entry.action || 'Action Recorded';
                      const amount = entry.amount ? `₹${parseFloat(entry.amount).toFixed(2)}` : '-';
                      const reference = entry.reference || '-';
                      const receipt = entry.receipt || '-';
                      const paymentType = entry.payment_type ? `<span class="badge bg-info text-white text-uppercase" style="font-size: 0.7rem;">${entry.payment_type.substring(0, 3)}</span>` : '-';
                      const entryNo = entry.entry_no || '-';

                    historyHtml += `
                        <tr>
                            <td>
                                <span class="badge bg-dark-subtle text-dark border">${entryNo}</span>
                            </td>
                            <td>
                                <small class="text-muted">${timestamp}</small>
                            </td>
                            <td>
                                <strong>${action}</strong>
                                ${entry.description ? `<br><small class="text-muted">${entry.description}</small>` : ''}
                            </td>
                            <td>
                                <span class="text-success fw-bold">${amount}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">${reference}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">${receipt}</span>
                            </td>
                            <td>${paymentType}</td>
                            <td>
                                <small><i class="fa-solid fa-user me-1 text-secondary"></i>${user}</small>
                            </td>
                        </tr>
                    `;
                });

                historyHtml += `
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

                const modal = new bootstrap.Modal(document.getElementById('paymentHistoryModal'));
                modal.show();
            } else {
                alert('No history found for this payment.');
            }
        },
        error: function(xhr) {
            alert('Could not load history. Please try again.');
            console.error(xhr);
        }
    });
}

</script>

<!-- CALCULATOR FUNCTIONS -->
<script>
let calcExpression = '';

function openCalculator() {
    window.open('https://www.google.com/search?q=calculator', 'googleCalculator', 'width=400,height=600,resizable=yes');
}

function appendToCalc(value) {
    const display = document.getElementById('calcDisplay');

    if(value === '.') {
        // Prevent multiple decimal points in a number
        const lastOperator = Math.max(
            calcExpression.lastIndexOf('+'),
            calcExpression.lastIndexOf('-'),
            calcExpression.lastIndexOf('*'),
            calcExpression.lastIndexOf('/')
        );
        const lastNumber = calcExpression.substring(lastOperator + 1);
        if(lastNumber.includes('.')) return;
    }

    calcExpression += value;
    // Replace * and / with × and − for display
    display.textContent = calcExpression.replace(/\*/g, '×').replace(/\//g, '÷');
}

function clearCalc() {
    calcExpression = '';
    document.getElementById('calcDisplay').textContent = '0';
}

function calculateResult() {
    try {
        if(calcExpression.trim() === '') return;

        // Replace × and ÷ with * and / for calculation
        let expression = calcExpression.replace(/×/g, '*').replace(/÷/g, '/');

        // Evaluate the expression safely (basic validation)
        const result = Function('"use strict"; return (' + expression + ')')();

        calcExpression = String(result);
        document.getElementById('calcDisplay').textContent = result.toFixed(2);
    } catch(e) {
        document.getElementById('calcDisplay').textContent = 'Error';
        calcExpression = '';
    }
}

function useCalculatorResult() {
    try {
        // Get the display value and remove any formatting
        const displayValue = document.getElementById('calcDisplay').textContent;

        if(displayValue === '0' || displayValue === 'Error' || displayValue === '') {
            alert('Please calculate a value first');
            return;
        }

        // Parse the value
        const amount = parseFloat(displayValue);
        if(isNaN(amount)) {
            alert('Invalid amount');
            return;
        }

        // Find the first payment amount field and set it
        const paymentAmountField = document.querySelector('.payment-amount');
        if(paymentAmountField) {
            paymentAmountField.value = amount.toFixed(2);
            paymentAmountField.dispatchEvent(new Event('input', { bubbles: true }));
            updateReceivedTotal();
        }

        // Close calculator modal
        const calculatorModal = bootstrap.Modal.getInstance(document.getElementById('calculatorModal'));
        if(calculatorModal) calculatorModal.hide();

        clearCalc();
    } catch(e) {
        alert('Error setting amount: ' + e.message);
    }
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
