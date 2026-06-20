<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar — Purchase Return and Credit Notes</title>
  <meta name="description" content="Record supplier purchase bills with live preview in Vyapar.">

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

</head>

<body data-page="sales-return">

  <!-- Navbar & Sidebar injected by components.js -->

  <!-- ═══════════════════════════════════════
     MAIN CONTENT — PURCHASE BILL
     ═══════════════════════════════════════ -->
  <main class="main-content" id="mainContent">




    <div class="d-flex align-items-center bg-light px-4 py-2">
      <div class="d-flex">
        <!-- <div class="d-flex justify-content-center align-items-center me-2">Filter By: </div> -->
        <select name="" id="" class="bg-transparent border-0 fs-5 fw-bold" style="outline:none;">
          <option value="">All Purchase Invoices</option>
          <option value="" selected>This Month</option>
          <option value="">Last Month</option>
          <option value="">This Quarter</option>
          <option value="">This Year</option>
          <option value="">Custom</option>
        </select>
      </div>
      <div class="d-flex pt-3 ps-2">
        <p class="text-center pt-2 text-white fw-bold"
          style="width: 6rem; height: 40px; background-color: #AAAAAA; border-radius: 5px 0px 0px 5px">Between</p>
        <div class="d-flex justify-content-center pt-2 gap-3"
          style="width: 20rem; height: 40px; border-radius: 0px 5px 5px 0px; border: 1px solid #AAAAAA">
          <p>01/03/2026</p>
          <p>To</p>
          <p>31/03/2026</p>
        </div>

      </div>
      <div class="d-flex justify-content-center align-items-center ms-4"
        style="border: 1px solid #AAAAAA;border-radius: 5px; width: 8rem; height: 40px;"><select name="" id=""
          class="bg-transparent border-0" style="outline:none;">
          <option value="" selected>All Firms</option>
          <option value=""><a href="">Firm 1</a></option>
          <option value=""><a href="">Firm 2</a></option>
          <option value=""><a href="">Firm 3</a></option>

        </select></div>
      <div class="px-5 mx-5"></div>
      <div class="d-flex gap-5 text-secondary">
        <i class="fa-solid fa-file-excel fs-5"></i>
        <i class="fa-solid fa-print fs-5"></i>
      </div>

    </div>
    <div class="bg-light px-4 mb-3 pb-3">
      <div class="d-flex justify-content-center align-items-center"
        style="border: 1px solid #AAAAAA;border-radius: 5px; width: 13rem; height: 40px;"><select name="" id=""
          class="bg-transparent border-0" style="outline:none;">
          <option value="" selected>All Transactions</option>
          <option value=""><a href="">Sale</a></option>
          <option value=""><a href="">Purchase</a></option>
          <option value=""><a href="">Payment In</a></option>
          <option value=""><a href="">Payment Out</a></option>
          <option value=""><a href="">Credit Note</a></option>
          <option value=""><a href="">Debit Note</a></option>
          <option value=""><a href="">Sale Order</a></option>
          <option value=""><a href="">Purchase Order</a></option>
          <option value=""><a href="">Estimate</a></option>
          <option value=""><a href="">Proforma Invoice</a></option>
          <option value=""><a href="">Delivery Challan</a></option>
          <option value=""><a href="">Expense</a></option>
          <option value=""><a href="">Party to Party [Received]</a></option>
          <option value=""><a href="">Party to Party [Paid]</a></option>
          <option value=""><a href="">Manufacture</a></option>
          <option value=""><a href="">Sale FA</a></option>
          <option value=""><a href="">Purchase FA</a></option>
          <option value=""><a href="">Sale [Canceled]</a></option>
          <option value=""><a href="">Journel Entry</a></option>
          <option value=""><a href="">Purchase (Job Work)</a></option>

        </select></div>
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
          <button class="btn btn-primary rounded"><span class="text-primary bg-light rounded-circle"
              style="padding: 0px 4px;">+</span> Add Debit Note</button>
        </div>

        <div class="table-responsive small-table">
          <table class="table table-hover mb-0 align-middle table-clean"
                 id="purchaseReturnTransactionsTable"
                 data-column-drag="native"
                 data-column-drag-storage="vyapar.purchase-return.transactions.column-order.v1">
            <thead>
              <tr class="d-flex gap-3">
                <th class="d-flex" data-column-key="date">
                  <p class="pt-1">Date</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Category:</p>
                        <select name="" id="" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                          <option value="" selected>Equal to</option>
                          <option value=""><a href="">Less than</a></option>
                          <option value=""><a href="">Greater than</a></option>
                          <option value=""><a href="">Range</a></option>
                        </select>
                      </li>
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Date:</p>
                        <input type="date" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                      </li>
                      <div class="mt-2 ms-4">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>
                <th class="d-flex" data-column-key="reference">
                  <p class="pt-1">Refernece No</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Category:</p>
                        <select name="" id="" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                          <option value="" selected>Equal to</option>
                          <option value=""><a href="">Less than</a></option>
                          <option value=""><a href="">Greater than</a></option>
                          <option value=""><a href="">Range</a></option>
                        </select>
                      </li>
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Reference No.</p>
                        <input type="text" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                      </li>
                      <div class="mt-2 ms-3">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>
                <th class="d-flex" data-column-key="party">
                  <p class="pt-1">Party Name</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Category:</p>
                        <select name="" id="" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                          <option value="" selected>Contains</option>
                          <option value=""><a href="">Exact Match</a></option>
                        </select>
                      </li>
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Party Name</p>
                        <input type="text" class="bg-transparent border py-2 rounded w-100" style="outline:none;">

                      </li>
                      <div class="mt-2 ms-3">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>
                <th class="d-flex" data-column-key="category">
                  <p class="pt-1">Category Name</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Category:</p>
                        <select name="" id="" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                          <option value="" selected>Contains</option>
                          <option value=""><a href="">Exact Match</a></option>

                        </select>
                      </li>
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Category Name</p>
                        <input type="text" class="bg-transparent border py-2 rounded w-100" style="outline:none;">

                      </li>
                      <div class="mt-2 ms-4">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>
                <th class="d-flex" data-column-key="type">
                  <p class="pt-1">Type</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <input type="checkbox"><span class="ms-1">Sale</span>
                      </li>
                      <li class="dropdown-item">
                        <input type="checkbox"><span class="ms-1">Purchase</span>
                      </li>
                      <li class="dropdown-item">
                        <input type="checkbox"><span class="ms-1">Payment In</span>
                      </li>
                      <li class="dropdown-item">
                        <input type="checkbox"><span class="ms-1">Payment Out</span>
                      </li>
                      <li class="dropdown-item">
                        <input type="checkbox"><span class="ms-1">Credit Note</span>
                      </li>
                      <li class="dropdown-item">
                        <input type="checkbox"><span class="ms-1">Debit Note</span>
                      </li>
                      <div class="mt-2 ms-4">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>
                <th class="d-flex" data-column-key="total">
                  <p class="pt-1">Total</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Category:</p>
                        <select name="" id="" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                          <option value="" selected>Equal to</option>
                          <option value=""><a href="">Less than</a></option>
                          <option value=""><a href="">Greater than</a></option>
                          <option value=""><a href="">Range</a></option>
                        </select>
                      </li>
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Total</p>
                        <input type="text" class="bg-transparent border py-2 rounded w-100" style="outline:none;">

                      </li>
                      <div class="mt-2 ms-3">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>
                <th class="d-flex" data-column-key="received">
                  <p class="pt-1">Received</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Category:</p>
                        <select name="" id="" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                          <option value="" selected>Equal to</option>
                          <option value=""><a href="">Less than</a></option>
                          <option value=""><a href="">Greater than</a></option>
                          <option value=""><a href="">Range</a></option>
                        </select>
                      </li>
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Received/Paid</p>
                        <input type="text" class="bg-transparent border py-2 rounded w-100" style="outline:none;">

                      </li>
                      <div class="mt-2 ms-3">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>
                <th class="d-flex" data-column-key="balance">
                  <p class="pt-1">Balance</p>
                  <div class="dropdown ms-3">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-filter"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Select Category:</p>
                        <select name="" id="" class="bg-transparent border py-2 rounded w-100" style="outline:none;">
                          <option value="" selected>Equal to</option>
                          <option value=""><a href="">Less than</a></option>
                          <option value=""><a href="">Greater than</a></option>
                          <option value=""><a href="">Range</a></option>
                        </select>
                      </li>
                      <li class="dropdown-item">
                        <p class="mb-0" style="font-size: 11px;">Balance</p>
                        <input type="text" class="bg-transparent border py-2 rounded w-100" style="outline:none;">

                      </li>
                      <div class="mt-2 ms-3">
                        <button class="btn rounded-pill" style="background-color: #EBEAEA;"><span
                            style="color: #71748E;">Clear</span></button>
                        <button class="btn rounded-pill" style="background-color: #D4112E;"><span
                            class="text-light">Apply</span></button>
                      </div>

                    </ul>
                  </div>
                </th>


                <th class="d-flex col-1" data-column-key="print_share">
                  <p class="pt-1">Print / Share</p>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  No estimates yet. Click "New Estimate" to create one.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </main>

  <!-- ═══════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════ -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/purchase_return.js') }}"></script>
  <script src="{{ asset('js/transaction-column-drag.js') }}"></script>

</body>

</html>
