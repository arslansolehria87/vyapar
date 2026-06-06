<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vyapar — Purchase Return / Debit Note</title>
  <meta name="description" content="Record purchase returns and generate debit notes in Vyapar.">

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
    #searchBox {
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
      padding: 12px 16px;
      background-color: #fff;
      border-bottom: 1px solid var(--border-color, #e0e0e0);
    }

    .search-left {
      display: flex;
      align-items: center;
    }

    .search-icon {
      font-size: 20px;
      cursor: pointer;
      color: #5f6368;
    }

    .search-input {
      width: 0;
      opacity: 0;
      padding: 0;
      margin-left: 0;
      border: 1px solid transparent;
      border-radius: 4px;
      outline: none;
      transition: all 0.4s ease;
      background-color: transparent;
    }

    .search-input.active {
      width: 200px;
      opacity: 1;
      padding: 8px;
      margin-left: 8px;
      border: 1px solid #ccc;
      background-color: #fff;
    }

    .other-btn {
      padding: 8px 12px;
      background-color: teal;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      white-space: nowrap;
      transition: background-color 0.2s ease;
    }

    .other-btn:hover {
      background-color: #006666;
    }

    .hidden {
      display: none !important;
    }
  </style>
</head>

<body data-page="expenses">

  <!-- Navbar & Sidebar injected by components.js -->

  <main class="main-content" id="mainContent">
    <div class="split-pane">
      <!-- Left: Debit Notes -->
      <div class="split-left">
        <div class="list-panel-header" id="searchBox">
          <div class="search-left">
            <span class="search-icon"><i class="bi bi-search"></i></span>
            <input type="text" class="search-input" placeholder="Search...">
          </div>
          <button class="other-btn btn rounded-pill" style="background-color:#ED1A3B;"><span
              class="bg-white text-danger rounded-circle" style="padding: 0px 4px;">+</span> Add Expense</button>
        </div>
        <ul class="entity-list" id="purchaseReturnList">
          <li data-note="dn-001">
            <span class="entity-name text-secondary fw-bold">Category </span>
            <span class="ms-5 ps-5 text-secondary fw-bold">Amount</span>
          </li>
          <li class="active" data-note="dn-001">
            <span class="entity-name">Charges on Loan </span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>
          <li class="" data-note="dn-001">
            <span class="entity-name">Interest Payment For [ABC]</span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>
          <li class="" data-note="dn-001">
            <span class="entity-name">Petrol</span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>
          <li class="" data-note="dn-001">
            <span class="entity-name">Processing fee for Loans</span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>
          <li class="" data-note="dn-001">
            <span class="entity-name">Rent</span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>
          <li class="" data-note="dn-001">
            <span class="entity-name">Salary</span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>
          <li class="" data-note="dn-001">
            <span class="entity-name">Tea</span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>
          <li class="" data-note="dn-001">
            <span class="entity-name">Transport</span>


            <div class="d-flex">
              <div>
                <span class="me-3">0</span>
              </div>
              <div class="dropdown">
                <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li class="dropdown-item">
                    View/Edit
                  </li>
                  <li class="dropdown-item">
                    Delete
                  </li>


                </ul>
              </div>
            </div>

          </li>




        </ul>
      </div>

      <!-- Right: Debit Note details -->
      <div class="split-right">
        <div class="detail-panel-header">
          <div>
            <div class="entity-detail-name" id="purchaseReturnDetailTitle">Charges on Laon</div>

          </div>
          <div class="">
            <p class="mb-1 ms-3 ps-1">Total : <span class="text-danger">Rs 0.00</span></p>
            <p class="mb-1">Balance : <span class="text-danger">Rs 0.00</span></p>
          </div>
        </div>
        <div class="detail-panel-body">
          <div class="table-responsive small-table">
            <table class="table table-hover mb-0 align-middle table-clean">
              <thead>
                <tr class="d-flex gap-3">
                  <th class="d-flex">
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
                  <th class="d-flex">
                    <p class="pt-1">Exp No.</p>
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
                          <p class="mb-0" style="font-size: 11px;">Exp No.</p>
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
                  <th class="d-flex">
                    <p class="pt-1">Party</p>
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
                          <p class="mb-0" style="font-size: 11px;">Party</p>
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
                    <th class="d-flex">
                    <p class="pt-1">Payment Type</p>
                    <div class="dropdown ms-3">
                      <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-filter"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li class="dropdown-item">
                          <input type="checkbox"><span class="ms-1">Cheque</span>
                        </li>
                        <li class="dropdown-item">
                          <input type="checkbox"><span class="ms-1">Cash</span>
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

                  <th class="d-flex">
                    <p class="pt-1">Amount</p>
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
                          <p class="mb-0" style="font-size: 11px;">Amount</p>
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
                  <th class="d-flex">
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
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
  <script src="{{ asset('js/common.js') }}"></script>
  <script src="{{ asset('js/expenses.js') }}"></script>


  <script>
    document.addEventListener("DOMContentLoaded", function () {

      const container = document.getElementById("searchBox");
      const icon = container.querySelector(".search-icon");
      const input = container.querySelector(".search-input");
      const otherBtn = container.querySelector(".other-btn");

      // Open search
      icon.addEventListener("click", function (e) {
        e.stopPropagation();
        input.classList.add("active");
        otherBtn.classList.add("hidden");
        input.focus();
      });

      // Prevent closing when clicking inside container
      container.addEventListener("click", function (e) {
        e.stopPropagation();
      });

      // Close when clicking outside
      document.addEventListener("click", function () {
        input.classList.remove("active");
        otherBtn.classList.remove("hidden");
      });

    });
  </script>


</body>

</html>

