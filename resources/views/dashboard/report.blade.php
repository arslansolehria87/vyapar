<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar — Reports</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <!-- Custom Styles -->
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

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
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .reports-container {
      font-family: 'Inter', sans-serif;
      height: calc(100vh - 60px);
      /* Adjust based on topbar height to fit screen */
    }

    .reports-nav .nav-link {
      font-size: 14px;
      transition: all 0.2s ease-in-out;
      color: #495057;
    }

    .reports-nav .nav-link:hover {
      background-color: #f1f3f5;
      color: #212529;
    }

    .reports-nav .nav-link.active {
      background-color: #e2e8f0;
      font-weight: 500;
      color: #111827 !important;
      border-right: 4px solid #6366f1;
    }

    .cursor-pointer {
      cursor: pointer;
    }

    /* Minor overrides for tables and custom badges equivalent to Tailwind */
    .bg-success-subtle-custom {
      background-color: #d1fae5;
    }

    .text-success-custom {
      color: #047857;
    }

    .border-indigo-100 {
      border: 1px solid #e0e7ff;
    }

    .table-custom-header th {
      font-size: 12px;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      padding-top: 1rem;
      padding-bottom: 1rem;
      background-color: #f9fafb;
      border-bottom: 1px solid #e5e7eb;
    }

    .table-custom-body td {
      font-size: 14px;
      color: #374151;
      padding-top: 1rem;
      padding-bottom: 1rem;
      vertical-align: middle;
    }

    .reports-filter-box {
      background-color: white;
      border: 1px solid #e5e7eb;
      transition: background-color 0.2s;
    }

    .reports-filter-box:hover {
      background-color: #f9fafb;
    }

    /* PnL Custom Styles */
    .pnl-container::-webkit-scrollbar {
      width: 6px;
    }

    .pnl-container::-webkit-scrollbar-track {
      background: transparent;
    }

    .pnl-container::-webkit-scrollbar-thumb {
      background-color: #cbd5e1;
      border-radius: 10px;
    }

    .pnl-row-hover:hover {
      background-color: #f8fafc !important;
    }

    .pnl-chevron {
      width: 24px;
      text-align: center;
      cursor: pointer;
      color: #3b82f6;
      display: inline-block;
      transition: transform 0.2s;
    }

    .pnl-bullet {
      width: 24px;
      text-align: center;
      color: #9ca3af;
      display: inline-block;
      font-size: 20px;
      line-height: 14px;
    }

    /* Party Statement Styles */
    .ps-radio {
      appearance: none;
      width: 18px;
      height: 18px;
      border: 2px solid #ccc;
      border-radius: 50%;
      margin: 0;
      display: grid;
      place-content: center;
      cursor: pointer;
    }

    .ps-radio::before {
      content: "";
      width: 10px;
      height: 10px;
      border-radius: 50%;
      transform: scale(0);
      background-color: #4b5563;
      transition: transform 120ms ease-in-out;
    }

    .ps-radio:checked {
      border: 2px solid #4b5563;
    }

    .ps-radio:checked::before {
      transform: scale(1);
    }
  </style>
</head>

<body data-page="reports">

  <!-- Navbar & Sidebar injected by components.js -->

  <main class="main-content" id="mainContent" style="padding: 0; overflow: hidden;">

    <div class="d-flex w-100 reports-container" style="background-color: #f3f4f6;">
      <!-- Internal Reports Sidebar -->
      <aside class="reports-sidebar border-end flex-shrink-0"
        style="width: 200px; min-width: 200px; background-color: #f9fafb; overflow-y: auto;">
        <div class="pt-4">
          <h6 class="text-secondary text-uppercase fw-bold px-4 mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
            Transaction report</h6>
          <ul class="nav flex-column mb-4 reports-nav">
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4 active" data-target="Sale"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i> Sale</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Purchase"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i> Purchase</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Daybook"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i> Day book</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Alltransactions"><i
                  class="fa-regular fa-file-lines me-2 text-secondary "></i>All Transactions</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="ProfitAndLoss"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i> Profit and Loss</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="cashFlow"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Cash Flow</a></li>
            <!-- <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Bill Wise Profit"><i
                  class="fa-solid fa-crown me-2 text-primary"></i> Bill Wise Profit</a></li> -->

          </ul>

          <h6 class="text-secondary text-uppercase fw-bold px-4 mb-3 mt-4"
            style="font-size: 11px; letter-spacing: 0.5px;">Party report</h6>
          <ul class="nav flex-column mb-4 reports-nav">

            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Party Statement"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i> Party Statement</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="All Parties"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i> All Parties</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Party Report by Items"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i> Party Report by Items</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Partysalepurchase"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Sale Purchase by Party</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Partysalepurchasegroup"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Sale Purchase by Party Group</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4 report-nav-link" data-tab="unreceived-invoices"
                onclick="showTab('unreceived-invoices'); return false;"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Agarri List / Unreceived Invoice</a></li>
            <!-- <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Trial Balance Report"><i
                  class="fa-solid fa-crown me-2 text-primary"></i> Trial Balance Report</a></li> -->
            <!-- <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Balance Sheet"><i
                  class="fa-solid fa-crown me-2 text-primary"></i> Balance Sheet</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="Party wise Profit & Loss"><i
                  class="fa-solid fa-crown me-2 text-primary"></i> Party wise Profit & Loss</a></li> -->
          </ul>
          <h6 class="text-secondary text-uppercase fw-bold px-4 mb-3 mt-4"
            style="font-size: 11px; letter-spacing: 0.5px;">Item/Stock Report</h6>
          <ul class="nav flex-column mb-4 reports-nav">

            <li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="stock-summary"
     onclick="showTab('stock-summary'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Stock Summary
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="party-report-summary"
     onclick="showTab('party-report-summary'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Party report by items
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="item-wise-profit-and-loss"
     onclick="showTab('item-wise-profit-and-loss'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Item wise profit and loss
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="item-category-wise-profit-and-loss"
     onclick="showTab('item-category-wise-profit-and-loss'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Item Category wise profit and loss
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="low-stock-summary"
     onclick="showTab('low-stock-summary'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Low stock summary
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="stock-details"
     onclick="showTab('stock-details'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Stock Details
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="item-details"
     onclick="showTab('item-details'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Item Details
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="sale-purchase-report-by-item-category"
     onclick="showTab('sale-purchase-report-by-item-category'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Sale/Purchase report by item category
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="stock-summary-report-by-item-category"
     onclick="showTab('stock-summary-report-by-item-category'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Stock Summary report by item category
  </a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link py-2 px-4 report-nav-link" 
     data-tab="item-wise-discount"
     onclick="showTab('item-wise-discount'); return false;">
    <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Item wise discount
  </a>
</li>

          </ul>
          <h6 class="text-secondary text-uppercase fw-bold px-4 mb-3 mt-4"
            style="font-size: 11px; letter-spacing: 0.5px;">Business Status</h6>
          <ul class="nav flex-column mb-4 reports-nav">

            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="bank statement"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Bank Statement</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="discount report"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Discount report</a></li>

          </ul>

          <h6 class="text-secondary text-uppercase fw-bold px-4 mb-3 mt-4"
            style="font-size: 11px; letter-spacing: 0.5px;">Taxes</h6>
          <ul class="nav flex-column mb-4 reports-nav">

            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="tax report"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Tax Report</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="tax rate report"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Tax Rate Report</a></li>

          </ul>
          <h6 class="text-secondary text-uppercase fw-bold px-4 mb-3 mt-4"
            style="font-size: 11px; letter-spacing: 0.5px;">Expense Report</h6>
          <ul class="nav flex-column mb-4 reports-nav">

            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="expense"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Expense</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="expense category report"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Expense Category Report</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-2 px-4" data-target="expense item report"><i
                  class="fa-regular fa-file-lines me-2 text-secondary"></i>Expense item Report</a></li>

          </ul>
<h6 class="text-secondary text-uppercase fw-bold px-4 mb-3 mt-4"
              style="font-size: 11px; letter-spacing: 0.5px;">Sale Order Report</h6>
          <ul class="nav flex-column mb-4 reports-nav">
              <li class="nav-item">
                  <a href="#" class="nav-link py-2 px-4 report-nav-link"
                     data-tab="sale-order"
                     onclick="showTab('sale-order'); return false;">
                      <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Sale Order
                  </a>
              </li>
              <li class="nav-item">
                  <a href="#" class="nav-link py-2 px-4 report-nav-link"
                     data-tab="sale-order-item"
                     onclick="showTab('sale-order-item'); return false;">
                      <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Sale Order Item
                  </a>
              </li>
          </ul>

          <h6 class="text-secondary text-uppercase fw-bold px-4 mb-3 mt-4"
              style="font-size: 11px; letter-spacing: 0.5px;">Loan Accounts</h6>
          <ul class="nav flex-column mb-4 reports-nav">
              <li class="nav-item">
                  <a href="#" class="nav-link py-2 px-4 report-nav-link"
                     data-tab="loan-statement"
                     onclick="showTab('loan-statement'); return false;">
                      <i class="fa-regular fa-file-lines me-2 text-secondary"></i>Loan Statement
                  </a>
              </li>
          </ul>

        </div>
      </aside>

     
       
       <!-- Main Reports Content Area -->

<div class="flex-grow-1 overflow-auto p-4" id="reportsContentArea">
    @include('dashboard.reports.tabs.sale-report')
    @include('dashboard.reports.tabs.purchase-report')
    @include('dashboard.reports.tabs.daybook-report')
    @include('dashboard.reports.tabs.all-transactions-report')
    @include('dashboard.reports.tabs.profit-and-loss-report')
    @include('dashboard.reports.tabs.cash-flow-report')
    @include('dashboard.reports.tabs.party-reports')
    @include('dashboard.reports.tabs.item-stock-reports')
    @include('dashboard.reports.tabs.bank-statement')
    @include('dashboard.reports.tabs.discount-report')
    @include('dashboard.reports.tabs.expense-report')
    @include('dashboard.reports.tabs.sale-order-report')
    @include('dashboard.reports.tabs.loan-statement-report')
    </div>  {{-- ← THIS closes reportsContentArea --}}

</div>

</div>

  </main>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>

  @include('dashboard.reports.partials._party-report-scripts')
   @include('dashboard.reports.partials._reports-scripts')
 <script>
document.addEventListener('DOMContentLoaded', function () {

    // Make item/stock tab links work independently
    document.querySelectorAll('[data-tab]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // stops components.js from intercepting
            window.showTab(this.getAttribute('data-tab'));
        });
    });

});
</script>
</body>


</html>

