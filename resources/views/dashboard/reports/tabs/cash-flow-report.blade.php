{{-- ============================================================
     Cash Flow Report Tab  –  FIXED VERSION
     @include('dashboard.reports.tabs.cash-flow-report')
     ============================================================ --}}

<div id="tab-cashFlow" class="report-tab-content d-none">

  {{-- ── Top Filter Bar ── --}}
  <div class="d-flex align-items-center justify-content-between bg-white border-bottom px-4 py-2 flex-wrap gap-2">

    <div class="d-flex align-items-center gap-2 flex-wrap">

      <select id="cfPeriod" class="form-select form-select-sm"
        style="width:150px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;box-shadow:none;">
        <option value="this_month" selected>This Month</option>
        <option value="last_month">Last Month</option>
        <option value="this_quarter">This Quarter</option>
        <option value="this_year">This Year</option>
        <option value="custom">Custom</option>
      </select>

      <div class="d-flex align-items-center" style="border:1px solid #d1d5db;border-radius:6px;overflow:hidden;height:32px;">
        <span class="px-2 py-1 text-white fw-semibold"
          style="background:#9ca3af;font-size:12px;height:100%;display:flex;align-items:center;">Between</span>
        <div class="d-flex align-items-center gap-1 px-2" style="font-size:13px;color:#374151;">
          <input type="date" id="cfFrom" class="border-0 bg-transparent p-0"
            style="font-size:13px;outline:none;width:115px;" />
          <span class="text-muted">To</span>
          <input type="date" id="cfTo" class="border-0 bg-transparent p-0"
            style="font-size:13px;outline:none;width:115px;" />
        </div>
      </div>

      <select class="form-select form-select-sm"
        style="width:130px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;box-shadow:none;">
        <option>All Firms</option>
      </select>

      <div class="form-check mb-0 d-flex align-items-center gap-1">
        <input class="form-check-input mt-0" type="checkbox" id="cfShowZero" style="box-shadow:none;">
        <label class="form-check-label mb-0" for="cfShowZero" style="font-size:13px;color:#6b7280;">
          Show zero amount transactions
        </label>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button id="cfExcelBtn" class="btn d-flex align-items-center justify-content-center p-0"
        style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;" title="Export Excel">
        <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:16px;"></i>
      </button>
      <button id="cfPrintBtn" class="btn d-flex align-items-center justify-content-center p-0"
        style="width:36px;height:36px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;" title="Print">
        <i class="fa-solid fa-print" style="color:#4b5563;font-size:16px;"></i>
      </button>
    </div>
  </div>

  {{-- ── Opening Cash Banner ── --}}
  <div class="d-flex align-items-center gap-4 px-4 py-2 bg-white border-bottom" style="font-size:13px;">
    <span class="text-success fw-semibold">
      Opening Cash in Hand: Rs <span id="cfOpeningCash">0.00</span>
    </span>
  </div>

  {{-- ── Summary Cards ── --}}
  <div class="d-flex gap-3 px-4 py-3 flex-wrap">
    <div class="rounded-3 px-4 py-3 d-flex flex-column" style="background:#f0fdf4;border:1px solid #bbf7d0;min-width:160px;">
      <span style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;">Total Cash In</span>
      <span id="cfTotalIn" class="fw-bold mt-1" style="font-size:18px;color:#16a34a;">Rs 0.00</span>
    </div>
    <div class="rounded-3 px-4 py-3 d-flex flex-column" style="background:#fef2f2;border:1px solid #fecaca;min-width:160px;">
      <span style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;">Total Cash Out</span>
      <span id="cfTotalOut" class="fw-bold mt-1" style="font-size:18px;color:#dc2626;">Rs 0.00</span>
    </div>
    <div class="rounded-3 px-4 py-3 d-flex flex-column" style="background:#eff6ff;border:1px solid #bfdbfe;min-width:160px;">
      <span style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;">Closing Cash</span>
      <span id="cfClosingCash" class="fw-bold mt-1" style="font-size:18px;color:#2563eb;">Rs 0.00</span>
    </div>
  </div>

  {{-- ── Server Error Alert (hidden by default) ── --}}
  <div id="cfServerErrorAlert" class="mx-4 mb-3 d-none">
    <div class="alert alert-danger d-flex align-items-start gap-3 mb-0" style="border-radius:10px;">
      <i class="fa-solid fa-circle-exclamation mt-1" style="font-size:18px;color:#dc2626;flex-shrink:0;"></i>
      <div>
        <div class="fw-bold mb-1" style="font-size:14px;">Could not load Cash Flow data</div>
        <div id="cfServerErrorMsg" style="font-size:13px;color:#6b7280;">Server error</div>
        <div class="mt-2 d-flex gap-2 flex-wrap">
          <button class="btn btn-sm btn-danger rounded-pill px-3" onclick="fetchCashFlow()" style="font-size:12px;">
            <i class="fa-solid fa-rotate-right me-1"></i> Retry
          </button>
          <span style="font-size:12px;color:#6b7280;line-height:28px;">
            Check that <code>/reports/cash-flow</code> route exists and returns JSON with <code>success</code> key.
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Transactions Table Card ── --}}
 <div class="mx-4 mb-4 rounded-3 border overflow-hidden bg-white" style="box-shadow:0 1px 4px rgba(0,0,0,.06);min-height:calc(100vh - 350px);">

    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom bg-white">
      <span class="fw-bold" style="font-size:15px;color:#111827;">Transactions</span>
      <div class="d-flex align-items-center gap-2">
        <div class="position-relative">
          <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:13px;"></i>
          <input type="text" id="cfSearch" placeholder="Search…"
            class="form-control form-control-sm ps-4"
            style="width:220px;border:1px solid #e5e7eb;border-radius:20px;font-size:13px;box-shadow:none;">
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="w-100" id="cfTable" data-column-drag="native"
        data-column-drag-storage="vyapar.reports.cash-flow.transactions.v1"
        style="border-collapse:collapse;table-layout:fixed;">
        <colgroup>
          <col style="width:44px;">    {{-- # --}}
          <col style="width:100px;">   {{-- Date --}}
          <col style="width:160px;">   {{-- Name --}}
          <col style="width:120px;">   {{-- Ref --}}
          <col style="width:110px;">   {{-- Category --}}
          <col style="width:110px;">   {{-- Type --}}
          <col style="width:110px;">   {{-- Payment Type --}}
          <col style="width:110px;">   {{-- Total --}}
          <col style="width:110px;">   {{-- Cash In --}}
          <col style="width:110px;">   {{-- Cash Out --}}
          <col style="width:120px;">   {{-- Running Cash --}}
          <col style="width:110px;">   {{-- Print/Share --}}
        </colgroup>
        <thead style="background:#f9fafb;">
          <tr style="border-bottom:2px solid #e5e7eb;">

            <th data-column-key="index" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;">#</th>

            <th data-column-key="date" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center gap-1">Date
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:200px;font-size:13px;">
                    <li class="mb-1"><label class="text-muted mb-1" style="font-size:11px;">From</label><input type="date" id="cfFilterDateFrom" class="form-control form-control-sm"></li>
                    <li class="mb-2"><label class="text-muted mb-1" style="font-size:11px;">To</label><input type="date" id="cfFilterDateTo" class="form-control form-control-sm"></li>
                    <li class="d-flex gap-1">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('date')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('date')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="name" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center gap-1">Name
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:200px;font-size:13px;">
                    <li class="mb-1"><select id="cfFilterNameMode" class="form-select form-select-sm mb-1"><option value="contains">Contains</option><option value="exact">Exact match</option></select><input type="text" id="cfFilterName" class="form-control form-control-sm" placeholder="Name…"></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('name')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('name')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="reference" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center gap-1">Reference No.
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:200px;font-size:13px;">
                    <li class="mb-1"><select id="cfFilterRefMode" class="form-select form-select-sm mb-1"><option value="contains">Contains</option><option value="exact">Exact match</option></select><input type="text" id="cfFilterRef" class="form-control form-control-sm" placeholder="Reference…"></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('ref')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('ref')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="category" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;border-right:1px solid #f3f4f6;">Category</th>

            <th data-column-key="type" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center gap-1">Type
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:160px;font-size:13px;">
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-type-chk" value="Sale"> Sale</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-type-chk" value="Purchase"> Purchase</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-type-chk" value="Payment-In"> Payment In</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-type-chk" value="Payment-Out"> Payment Out</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-type-chk" value="Expense"> Expense</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-type-chk" value="Credit Note"> Credit Note</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-type-chk" value="Debit Note"> Debit Note</label></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('type')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('type')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="payment_type" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center gap-1">Payment Type
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:150px;font-size:13px;">
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-pay-chk" value="Cash"> Cash</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-pay-chk" value="Cheque"> Cheque</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-pay-chk" value="Online"> Online</label></li>
                    <li><label class="d-flex align-items-center gap-2 py-1"><input type="checkbox" class="cf-pay-chk" value="UPI"> UPI</label></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('pay')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('pay')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="total" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center justify-content-end gap-1">Total
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:200px;font-size:13px;">
                    <li class="mb-1"><select id="cfFilterTotalMode" class="form-select form-select-sm mb-1"><option value="gte">Greater than or equal</option><option value="lte">Less than or equal</option><option value="eq">Equal to</option></select><input type="number" id="cfFilterTotal" class="form-control form-control-sm" placeholder="Amount…" min="0"></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('total')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('total')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="cash_in" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center justify-content-end gap-1">Cash In
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:200px;font-size:13px;">
                    <li class="mb-1"><select id="cfFilterCashInMode" class="form-select form-select-sm mb-1"><option value="gte">Greater than or equal</option><option value="lte">Less than or equal</option><option value="eq">Equal to</option></select><input type="number" id="cfFilterCashIn" class="form-control form-control-sm" placeholder="Amount…" min="0"></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('cashIn')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('cashIn')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="cash_out" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center justify-content-end gap-1">Cash Out
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:200px;font-size:13px;">
                    <li class="mb-1"><select id="cfFilterCashOutMode" class="form-select form-select-sm mb-1"><option value="gte">Greater than or equal</option><option value="lte">Less than or equal</option><option value="eq">Equal to</option></select><input type="number" id="cfFilterCashOut" class="form-control form-control-sm" placeholder="Amount…" min="0"></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('cashOut')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('cashOut')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="running_cash" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border-right:1px solid #f3f4f6;">
              <div class="d-flex align-items-center justify-content-end gap-1">Running Cash
                <div class="dropdown">
                  <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown"><i class="fa-solid fa-filter" style="font-size:10px;color:#9ca3af;"></i></button>
                  <ul class="dropdown-menu p-2" style="min-width:200px;font-size:13px;">
                    <li class="mb-1"><select id="cfFilterRunningMode" class="form-select form-select-sm mb-1"><option value="gte">Greater than or equal</option><option value="lte">Less than or equal</option><option value="eq">Equal to</option></select><input type="number" id="cfFilterRunning" class="form-control form-control-sm" placeholder="Amount…"></li>
                    <li class="d-flex gap-1 mt-2">
                      <button class="btn btn-sm rounded-pill flex-fill" style="background:#f3f4f6;color:#374151;font-size:12px;" onclick="cfClearFilter('running')">Clear</button>
                      <button class="btn btn-sm rounded-pill flex-fill text-white" style="background:#ef4444;font-size:12px;" onclick="cfApplyFilter('running')">Apply</button>
                    </li>
                  </ul>
                </div>
              </div>
            </th>

            <th data-column-key="actions" style="padding:10px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:center;">Print/Share</th>

          </tr>
        </thead>
        <tbody id="cfTbody" style="background:#fff;">
          <tr id="cfLoadingRow">
            <td colspan="12" class="text-center py-5">
              <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
              <span class="text-muted" style="font-size:13px;">Loading…</span>
            </td>
          </tr>
        </tbody>
        <tfoot id="cfTfoot" style="background:#f9fafb;display:none;">
          <tr style="border-top:2px solid #e5e7eb;">
            <td data-column-key="index" style="padding:12px 16px;font-size:13px;font-weight:700;color:#1f2937;">Totals</td>
            <td data-column-key="date"></td>
            <td data-column-key="name"></td>
            <td data-column-key="reference"></td>
            <td data-column-key="category"></td>
            <td data-column-key="type"></td>
            <td data-column-key="payment_type"></td>
            <td data-column-key="total" style="padding:12px 16px;font-size:13px;font-weight:700;color:#1f2937;text-align:right;" id="cfFootTotal">Rs 0.00</td>
            <td data-column-key="cash_in" style="padding:12px 16px;font-size:13px;font-weight:700;color:#16a34a;text-align:right;" id="cfFootIn">Rs 0.00</td>
            <td data-column-key="cash_out" style="padding:12px 16px;font-size:13px;font-weight:700;color:#dc2626;text-align:right;" id="cfFootOut">Rs 0.00</td>
            <td data-column-key="running_cash" style="padding:12px 16px;font-size:13px;font-weight:700;color:#2563eb;text-align:right;" id="cfFootRunning">Rs 0.00</td>
            <td data-column-key="actions"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>{{-- /card --}}

</div>{{-- /tab-cashFlow --}}


{{-- ============================================================
     PRINT STYLES  (only visible when window.print() is called)
     ============================================================ --}}
<style>
/* Print is handled via popup window — no @media print needed here */
#cf-print-header { display: none; }

/* ── Three-dots row action menu styles ── */
.cf-row-menu {
  position: relative;
  display: inline-block;
}
.cf-row-menu-dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 100%;
  z-index: 1055;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,.12);
  min-width: 160px;
  padding: 4px 0;
  white-space: nowrap;
}
.cf-row-menu-dropdown.show {
  display: block;
}
.cf-row-menu-dropdown a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 16px;
  font-size: 13px;
  color: #374151;
  text-decoration: none;
  cursor: pointer;
  transition: background .12s;
}
.cf-row-menu-dropdown a:hover {
  background: #f9fafb;
  color: #111827;
}
.cf-row-menu-dropdown a.text-danger:hover {
  background: #fef2f2;
}
.cf-row-menu-dropdown .cf-menu-divider {
  border-top: 1px solid #f3f4f6;
  margin: 4px 0;
}
</style>

{{-- ── Hidden Print Header ── --}}
<div id="cf-print-header" class="mb-3">
  <div class="text-center mb-2">
    <div style="font-size:18px;font-weight:700;color:#111827;">Cash Flow Report</div>
    <div style="font-size:13px;color:#6b7280;" id="cfPrintDateRange"></div>
  </div>
  <div class="d-flex gap-4 justify-content-center" style="font-size:13px;">
    <span>Opening Cash: <strong id="cfPrintOpening">Rs 0.00</strong></span>
    <span style="color:#16a34a;">Total Cash In: <strong id="cfPrintIn">Rs 0.00</strong></span>
    <span style="color:#dc2626;">Total Cash Out: <strong id="cfPrintOut">Rs 0.00</strong></span>
    <span style="color:#2563eb;">Closing Cash: <strong id="cfPrintClosing">Rs 0.00</strong></span>
  </div>
</div>


{{-- ============================================================
     CASH FLOW JAVASCRIPT
     ============================================================ --}}
@once
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
@endonce
<script>
(function () {
  'use strict';

  /* ── State ─────────────────────────────────────────── */
  let CF = {
    raw: [],
    filtered: [],
    activeFilters: {},
    loading: false,
  };

  /* ── Helpers ────────────────────────────────────────── */
  const fmt  = v => 'Rs ' + parseFloat(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmtN = v => parseFloat(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function getDateRange() {
    return {
      from: document.getElementById('cfFrom').value,
      to:   document.getElementById('cfTo').value,
    };
  }

  function setDefaultDates() {
    const now = new Date();
    const y = now.getFullYear(), m = now.getMonth();
    document.getElementById('cfFrom').value = new Date(y, m, 1).toISOString().slice(0,10);
    document.getElementById('cfTo').value   = new Date(y, m + 1, 0).toISOString().slice(0,10);
  }

  function showError(msg) {
    document.getElementById('cfServerErrorAlert').classList.remove('d-none');
    document.getElementById('cfServerErrorMsg').textContent = msg;
    document.getElementById('cfTbody').innerHTML = '';
    document.getElementById('cfTfoot').style.display = 'none';
  }

  function hideError() {
    document.getElementById('cfServerErrorAlert').classList.add('d-none');
  }

  /* ── Close all open row menus ───────────────────────── */
  function closeAllRowMenus(except) {
    document.querySelectorAll('.cf-row-menu-dropdown.show').forEach(function(m) {
      if (m !== except) m.classList.remove('show');
    });
  }

  /* Close menus when clicking outside */
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.cf-row-menu')) {
      closeAllRowMenus(null);
    }
  });

  /* ── Fetch from server ──────────────────────────────── */
  window.fetchCashFlow = function fetchCashFlow() {
    if (CF.loading) return;
    CF.loading = true;
    hideError();

    const { from, to } = getDateRange();
    const tbody = document.getElementById('cfTbody');
    const tfoot = document.getElementById('cfTfoot');

    tbody.innerHTML = `
      <tr id="cfLoadingRow">
        <td colspan="12" class="text-center py-5">
          <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
          <span class="text-muted" style="font-size:13px;">Loading…</span>
        </td>
      </tr>`;
    tfoot.style.display = 'none';

    const csrfToken = window.App?.csrfToken
      || document.querySelector('meta[name=csrf-token]')?.content
      || '';

   fetch(`/dashboard/reports/cash-flow?from=${from}&to=${to}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      }
    })
    .then(async r => {
      CF.loading = false;

      /* ── Handle non-200 HTTP status ── */
      if (!r.ok) {
        const text = await r.text().catch(() => '');
        let detail = `HTTP ${r.status} ${r.statusText}`;
        try {
          const json = JSON.parse(text);
          detail = json.message || json.error || detail;
        } catch (_) { /* non-JSON body */ }
        showError(detail);
        return;
      }

      let data;
      try {
        data = await r.json();
      } catch (_) {
        showError('Server returned invalid JSON. Check the /reports/cash-flow controller.');
        return;
      }

      if (!data.success) {
        showError(data.message || data.error || 'Server returned success:false');
        return;
      }

      CF.raw = data.transactions || [];
      CF.activeFilters = {};

      document.getElementById('cfOpeningCash').textContent = fmtN(data.opening_balance || 0);
      document.getElementById('cfPrintOpening').textContent = fmt(data.opening_balance || 0);

      applyFiltersAndRender();
    })
    .catch(err => {
      CF.loading = false;
      showError('Network error — could not reach server. (' + err.message + ')');
      console.error('CashFlow fetch error:', err);
    });
  };

  /* ── Client-side filter + render ────────────────────── */
  function applyFiltersAndRender() {
    let rows = [...CF.raw];
    const f  = CF.activeFilters;
    const showZero = document.getElementById('cfShowZero').checked;

    const q = (document.getElementById('cfSearch').value || '').toLowerCase().trim();
    if (q) {
      rows = rows.filter(r =>
        (r.party_name  || '').toLowerCase().includes(q) ||
        (r.bill_number || '').toString().toLowerCase().includes(q) ||
        (r.type        || '').toLowerCase().includes(q) ||
        (r.category    || '').toLowerCase().includes(q)
      );
    }

    if (!showZero) rows = rows.filter(r => parseFloat(r.total_amount || 0) !== 0);
    if (f.dateFrom) rows = rows.filter(r => r.date >= f.dateFrom);
    if (f.dateTo)   rows = rows.filter(r => r.date <= f.dateTo);

    if (f.name) {
      const nm = f.name.toLowerCase();
      rows = rows.filter(r =>
        f.nameMode === 'exact'
          ? (r.party_name || '').toLowerCase() === nm
          : (r.party_name || '').toLowerCase().includes(nm)
      );
    }

    if (f.ref) {
      const rf = f.ref.toLowerCase();
      rows = rows.filter(r =>
        f.refMode === 'exact'
          ? (r.bill_number || '').toString().toLowerCase() === rf
          : (r.bill_number || '').toString().toLowerCase().includes(rf)
      );
    }

    if (f.types    && f.types.length)    rows = rows.filter(r => f.types.includes(r.type));
    if (f.payTypes && f.payTypes.length) rows = rows.filter(r => f.payTypes.includes(r.payment_type));

    function numFilter(arr, field, mode, val) {
      if (val === null || val === undefined || val === '') return arr;
      const v = parseFloat(val);
      return arr.filter(r => {
        const rv = parseFloat(r[field] || 0);
        return mode === 'gte' ? rv >= v : mode === 'lte' ? rv <= v : rv === v;
      });
    }
    if (f.total   !== undefined) rows = numFilter(rows, 'total_amount', f.totalMode,   f.total);
    if (f.cashIn  !== undefined) rows = numFilter(rows, 'cash_in',      f.cashInMode,  f.cashIn);
    if (f.cashOut !== undefined) rows = numFilter(rows, 'cash_out',     f.cashOutMode, f.cashOut);
    if (f.running !== undefined) rows = numFilter(rows, 'running_cash', f.runningMode, f.running);

    CF.filtered = rows;
    renderTable(rows);
  }

  /* ── Render table rows ──────────────────────────────── */
  function renderTable(rows) {
    const tbody = document.getElementById('cfTbody');
    const tfoot = document.getElementById('cfTfoot');

    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center py-5 text-muted" style="font-size:13px;">
            <i class="fa-regular fa-folder-open fs-3 d-block mb-2 opacity-50"></i>
            No transactions found for this period.
          </td>
        </tr>`;
      tfoot.style.display = 'none';
      updateSummaryCards(0, 0, 0);
      return;
    }

    let totalAmt = 0, totalIn = 0, totalOut = 0;

    const typeColors = {
      'Sale':        '#16a34a',
      'Purchase':    '#dc2626',
      'Expense':     '#d97706',
      'Payment-In':  '#2563eb',
      'Payment-Out': '#7c3aed',
    };

    const html = rows.map((r, idx) => {
      const cashIn  = parseFloat(r.cash_in      || 0);
      const cashOut = parseFloat(r.cash_out     || 0);
      const total   = parseFloat(r.total_amount || 0);
      const running = parseFloat(r.running_cash || 0);
      totalAmt += total;
      totalIn  += cashIn;
      totalOut += cashOut;

      const tc = typeColors[r.type] || '#374151';
      const rc = running >= 0 ? '#16a34a' : '#dc2626';
      // Escape for onclick attribute safety
      const safeType = (r.type || '').replace(/'/g, "\\'");
      const menuId = `cfMenu_${r.id}_${idx}`;

      return `
        <tr style="border-bottom:1px solid #f3f4f6;transition:background .15s;"
            onmouseover="this.style.background='#f9fafb'"
            onmouseout="this.style.background='#fff'">
          <td data-column-key="index" style="padding:10px 12px;font-size:13px;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${idx + 1}</td>
          <td data-column-key="date" style="padding:10px 12px;font-size:13px;color:#374151;border-right:1px solid #f3f4f6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${r.date || '—'}</td>
          <td data-column-key="name" style="padding:10px 12px;font-size:13px;color:#111827;font-weight:500;border-right:1px solid #f3f4f6;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${(r.party_name||'').replace(/"/g,'&quot;')}">${r.party_name || '—'}</td>
          <td data-column-key="reference" style="padding:10px 12px;font-size:13px;color:#374151;border-right:1px solid #f3f4f6;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.bill_number || '—'}</td>
          <td data-column-key="category" style="padding:10px 12px;font-size:13px;color:#374151;border-right:1px solid #f3f4f6;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.category || '—'}</td>
          <td data-column-key="type" style="padding:10px 12px;border-right:1px solid #f3f4f6;white-space:nowrap;">
            <span class="px-2 py-1 rounded-pill" style="font-size:11px;font-weight:600;background:${tc}18;color:${tc};">${r.type || '—'}</span>
          </td>
          <td data-column-key="payment_type" style="padding:10px 12px;font-size:13px;color:#374151;border-right:1px solid #f3f4f6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${r.payment_type || '—'}</td>
          <td data-column-key="total" style="padding:10px 12px;font-size:13px;color:#374151;text-align:right;border-right:1px solid #f3f4f6;white-space:nowrap;">${total ? fmt(total) : '—'}</td>
          <td data-column-key="cash_in" style="padding:10px 12px;font-size:13px;font-weight:600;color:#16a34a;text-align:right;border-right:1px solid #f3f4f6;white-space:nowrap;">${cashIn ? fmt(cashIn) : '—'}</td>
          <td data-column-key="cash_out" style="padding:10px 12px;font-size:13px;font-weight:600;color:#dc2626;text-align:right;border-right:1px solid #f3f4f6;white-space:nowrap;">${cashOut ? fmt(cashOut) : '—'}</td>
          <td data-column-key="running_cash" style="padding:10px 12px;font-size:13px;font-weight:700;color:${rc};text-align:right;border-right:1px solid #f3f4f6;white-space:nowrap;">${fmt(running)}</td>
          <td data-column-key="actions" style="padding:10px 12px;text-align:center;white-space:nowrap;">
            <div class="d-flex justify-content-center align-items-center gap-1">
              <button class="btn p-0 border-0 bg-transparent" title="Print" onclick="cfPrintRow(${r.id},'${safeType}')">
                <i class="fa-solid fa-print" style="font-size:14px;color:#6b7280;"></i>
              </button>
              <button class="btn p-0 border-0 bg-transparent" title="Share" onclick="cfShareRow(${r.id},'${safeType}')">
                <i class="fa-solid fa-share-nodes" style="font-size:14px;color:#6b7280;"></i>
              </button>
              <div class="cf-row-menu">
                <button class="btn p-0 border-0 bg-transparent" title="More actions"
                  onclick="cfToggleRowMenu(event,'${menuId}')">
                  <i class="fa-solid fa-ellipsis-vertical" style="font-size:15px;color:#6b7280;padding:0 4px;"></i>
                </button>
                <div class="cf-row-menu-dropdown" id="${menuId}">
                  <a onclick="cfViewEdit(${r.id},'${safeType}');closeAllCfMenus()">
                    <i class="fa-solid fa-pen-to-square" style="color:#6b7280;font-size:13px;width:16px;text-align:center;"></i>
                    View/Edit
                  </a>
                  <a onclick="cfOpenPDF(${r.id},'${safeType}');closeAllCfMenus()">
                    <i class="fa-solid fa-file-pdf" style="color:#ef4444;font-size:13px;width:16px;text-align:center;"></i>
                    Open PDF
                  </a>
                  <a onclick="cfPrintRow(${r.id},'${safeType}');closeAllCfMenus()">
                    <i class="fa-solid fa-print" style="color:#6b7280;font-size:13px;width:16px;text-align:center;"></i>
                    Print
                  </a>
                  <div class="cf-menu-divider"></div>
                  <a onclick="cfDuplicate(${r.id},'${safeType}');closeAllCfMenus()">
                    <i class="fa-solid fa-copy" style="color:#6b7280;font-size:13px;width:16px;text-align:center;"></i>
                    Duplicate
                  </a>
                  <a onclick="cfViewHistory(${r.id},'${safeType}');closeAllCfMenus()">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#6b7280;font-size:13px;width:16px;text-align:center;"></i>
                    View History
                  </a>
                  <div class="cf-menu-divider"></div>
                  <a class="text-danger" onclick="cfDelete(${r.id},'${safeType}');closeAllCfMenus()">
                    <i class="fa-solid fa-trash" style="color:#ef4444;font-size:13px;width:16px;text-align:center;"></i>
                    Delete
                  </a>
                </div>
              </div>
            </div>
          </td>
        </tr>`;
    }).join('');

    tbody.innerHTML = html;

    const lastRunning = rows.length ? parseFloat(rows[rows.length - 1].running_cash || 0) : 0;
    document.getElementById('cfFootTotal').textContent   = fmt(totalAmt);
    document.getElementById('cfFootIn').textContent      = fmt(totalIn);
    document.getElementById('cfFootOut').textContent     = fmt(totalOut);
    document.getElementById('cfFootRunning').textContent = fmt(lastRunning);
    tfoot.style.display = '';

    updateSummaryCards(totalIn, totalOut, lastRunning);
  }

  function updateSummaryCards(cashIn, cashOut, closing) {
    document.getElementById('cfTotalIn').textContent     = fmt(cashIn);
    document.getElementById('cfTotalOut').textContent    = fmt(cashOut);
    document.getElementById('cfClosingCash').textContent = fmt(closing);
    /* sync print header */
    document.getElementById('cfPrintIn').textContent      = fmt(cashIn);
    document.getElementById('cfPrintOut').textContent     = fmt(cashOut);
    document.getElementById('cfPrintClosing').textContent = fmt(closing);
  }

  /* ── Three-dots menu toggle ─────────────────────────── */
  window.cfToggleRowMenu = function(event, menuId) {
    event.stopPropagation();
    const menu = document.getElementById(menuId);
    if (!menu) return;
    const isOpen = menu.classList.contains('show');
    closeAllRowMenus(null);
    if (!isOpen) menu.classList.add('show');
  };

  /* Expose close helper for inline onclick handlers */
  window.closeAllCfMenus = function() {
    closeAllRowMenus(null);
  };

  /* ── Row action: View / Edit ────────────────────────── */
  window.cfViewEdit = function(id, type) {
    const routes = {
      'Sale':        `/dashboard/sales/${id}/edit`,
      'Purchase':    `/dashboard/purchase-bills/${id}/edit`,
      'Payment-In':  `/dashboard/payment-in`,
      'Payment-Out': `/dashboard/payment-out`,
      'Expense':     `/dashboard/expense`,
      'Credit Note': `/dashboard/sale-return/${id}/edit`,
      'Debit Note':  `/dashboard/purchase-return/${id}/edit`,
    };
    const route = routes[type];
    if (route) {
      window.location.href = route;
    } else {
      alert('View/Edit not available for type: ' + type);
    }
  };

  /* ── Row action: Open PDF ───────────────────────────── */
  window.cfOpenPDF = function(id, type) {
    const routes = {
      'Sale':        `/dashboard/sales/${id}/invoice-pdf`,
      'Purchase':    `/dashboard/purchase-bills/${id}/pdf`,
      'Payment-In':  null,
      'Payment-Out': null,
      'Expense':     null,
      'Credit Note': `/dashboard/sale-return/${id}/pdf`,
      'Debit Note':  `/dashboard/purchase-return/${id}/pdf`,
    };
    const route = routes[type];
    if (route) {
      window.open(route, '_blank');
    } else {
      alert('PDF not available for type: ' + type);
    }
  };

  /* ── Row action: Duplicate ──────────────────────────── */
  window.cfDuplicate = function(id, type) {
    const routes = {
      'Sale':        `/sales/${id}/duplicate`,
      'Purchase':    null,
      'Payment-In':  null,
      'Payment-Out': null,
      'Expense':     null,
      'Credit Note': `/dashboard/sale-return/${id}/duplicate`,
      'Debit Note':  `/dashboard/purchase-return/${id}/duplicate`,
    };
    const route = routes[type];
    if (route) {
      if (confirm('Duplicate this ' + type + '?')) {
        const csrfToken = window.App?.csrfToken
          || document.querySelector('meta[name=csrf-token]')?.content || '';
        fetch(route, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          }
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            fetchCashFlow();
          } else {
            alert(data.message || 'Could not duplicate.');
          }
        })
        .catch(() => alert('Network error while duplicating.'));
      }
    } else {
      alert('Duplicate not available for type: ' + type);
    }
  };

  /* ── Row action: View History ───────────────────────── */
  window.cfViewHistory = function(id, type) {
    const routes = {
      'Sale':        `/dashboard/sales/${id}/payment-history`,
      'Purchase':    `/dashboard/purchase-orders/${id}/history`,
      'Payment-In':  null,
      'Payment-Out': null,
      'Expense':     null,
      'Credit Note': null,
      'Debit Note':  null,
    };
    const route = routes[type];
    if (route) {
      window.open(route, '_blank');
    } else {
      alert('View History not available for type: ' + type);
    }
  };

  /* ── Row action: Delete ─────────────────────────────── */
  window.cfDelete = function(id, type) {
    if (!confirm('Are you sure you want to delete this ' + type + '? This action cannot be undone.')) return;

    const routes = {
      'Sale':        `/dashboard/sales/${id}`,
      'Purchase':    `/dashboard/purchase-bills/${id}`,
      'Payment-In':  null,
      'Payment-Out': null,
      'Expense':     `/dashboard/expense/${id}`,
      'Credit Note': `/dashboard/sale-return/${id}`,
      'Debit Note':  `/dashboard/purchase-return/${id}`,
    };
    const route = routes[type];
    if (!route) { alert('Delete not available for type: ' + type); return; }

    const csrfToken = window.App?.csrfToken
      || document.querySelector('meta[name=csrf-token]')?.content || '';

    fetch(route, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      }
    })
    .then(async r => {
      const data = await r.json().catch(() => ({}));
      if (r.ok && data.success !== false) {
        fetchCashFlow();
      } else {
        alert(data.message || 'Could not delete. Please try again.');
      }
    })
    .catch(() => alert('Network error while deleting.'));
  };

  /* ── Filter apply / clear ───────────────────────────── */
  window.cfApplyFilter = function(key) {
    const f = CF.activeFilters;
    switch(key) {
      case 'date':
        f.dateFrom = document.getElementById('cfFilterDateFrom').value || undefined;
        f.dateTo   = document.getElementById('cfFilterDateTo').value   || undefined;
        break;
      case 'name':
        f.name     = document.getElementById('cfFilterName').value.trim() || undefined;
        f.nameMode = document.getElementById('cfFilterNameMode').value;
        break;
      case 'ref':
        f.ref     = document.getElementById('cfFilterRef').value.trim() || undefined;
        f.refMode = document.getElementById('cfFilterRefMode').value;
        break;
      case 'type':
        f.types = [...document.querySelectorAll('.cf-type-chk:checked')].map(c => c.value);
        if (!f.types.length) delete f.types;
        break;
      case 'pay':
        f.payTypes = [...document.querySelectorAll('.cf-pay-chk:checked')].map(c => c.value);
        if (!f.payTypes.length) delete f.payTypes;
        break;
      case 'total':
        f.total     = document.getElementById('cfFilterTotal').value;
        f.totalMode = document.getElementById('cfFilterTotalMode').value;
        break;
      case 'cashIn':
        f.cashIn     = document.getElementById('cfFilterCashIn').value;
        f.cashInMode = document.getElementById('cfFilterCashInMode').value;
        break;
      case 'cashOut':
        f.cashOut     = document.getElementById('cfFilterCashOut').value;
        f.cashOutMode = document.getElementById('cfFilterCashOutMode').value;
        break;
      case 'running':
        f.running     = document.getElementById('cfFilterRunning').value;
        f.runningMode = document.getElementById('cfFilterRunningMode').value;
        break;
    }
    document.querySelectorAll('.dropdown-menu.show').forEach(d => d.classList.remove('show'));
    applyFiltersAndRender();
  };

  window.cfClearFilter = function(key) {
    const f = CF.activeFilters;
    const idMap = {
      date:    ['cfFilterDateFrom','cfFilterDateTo'],
      name:    ['cfFilterName'],
      ref:     ['cfFilterRef'],
      total:   ['cfFilterTotal'],
      cashIn:  ['cfFilterCashIn'],
      cashOut: ['cfFilterCashOut'],
      running: ['cfFilterRunning'],
    };
    const stateKeys = {
      date:    ['dateFrom','dateTo'],
      name:    ['name','nameMode'],
      ref:     ['ref','refMode'],
      type:    ['types'],
      pay:     ['payTypes'],
      total:   ['total','totalMode'],
      cashIn:  ['cashIn','cashInMode'],
      cashOut: ['cashOut','cashOutMode'],
      running: ['running','runningMode'],
    };
    (stateKeys[key] || []).forEach(k => delete f[k]);
    (idMap[key] || []).forEach(id => { const el = document.getElementById(id); if(el) el.value = ''; });
    if (key === 'type') document.querySelectorAll('.cf-type-chk').forEach(c => c.checked = false);
    if (key === 'pay')  document.querySelectorAll('.cf-pay-chk').forEach(c => c.checked = false);
    applyFiltersAndRender();
  };

  /* ── Period dropdown ────────────────────────────────── */
  document.getElementById('cfPeriod').addEventListener('change', function () {
    const now = new Date();
    const y = now.getFullYear(), m = now.getMonth();
    let from, to;
    switch (this.value) {
      case 'this_month':   from = new Date(y,m,1);     to = new Date(y,m+1,0);   break;
      case 'last_month':   from = new Date(y,m-1,1);   to = new Date(y,m,0);     break;
      case 'this_quarter': { const q=Math.floor(m/3); from=new Date(y,q*3,1); to=new Date(y,q*3+3,0); break; }
      case 'this_year':    from = new Date(y,0,1);     to = new Date(y,11,31);   break;
      default: return;
    }
    document.getElementById('cfFrom').value = from.toISOString().slice(0,10);
    document.getElementById('cfTo').value   = to.toISOString().slice(0,10);
    fetchCashFlow();
  });

  ['cfFrom','cfTo'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
      document.getElementById('cfPeriod').value = 'custom';
      fetchCashFlow();
    });
  });

  document.getElementById('cfShowZero').addEventListener('change', applyFiltersAndRender);

  let cfSearchTimer;
  document.getElementById('cfSearch').addEventListener('input', function () {
    clearTimeout(cfSearchTimer);
    cfSearchTimer = setTimeout(applyFiltersAndRender, 280);
  });

  /* ── Excel export ──────────────────────────────────── */
  document.getElementById('cfExcelBtn').addEventListener('click', function () {
    if (!CF.filtered.length) { alert('No data to export.'); return; }
    const { from, to } = getDateRange();
   window.location.href = `/dashboard/reports/cash-flow/export?from=${from}&to=${to}`;
  });

  /* ── Print (full report) ────────────────────────────── */
  document.getElementById('cfPrintBtn').addEventListener('click', function () {
    if (!CF.filtered.length) { alert('No data to print.'); return; }

    const { from, to } = getDateRange();
    const opening  = document.getElementById('cfOpeningCash').textContent;
    const totalIn  = document.getElementById('cfTotalIn').textContent;
    const totalOut = document.getElementById('cfTotalOut').textContent;
    const closing  = document.getElementById('cfClosingCash').textContent;

    /* Build table rows from filtered data */
    const typeColors = {
      'Sale':'#16a34a','Purchase':'#dc2626','Expense':'#d97706',
      'Payment-In':'#2563eb','Payment-Out':'#7c3aed',
    };
    const rowsHtml = CF.filtered.map((r, idx) => {
      const cashIn  = parseFloat(r.cash_in      || 0);
      const cashOut = parseFloat(r.cash_out     || 0);
      const total   = parseFloat(r.total_amount || 0);
      const running = parseFloat(r.running_cash || 0);
      const tc  = typeColors[r.type] || '#374151';
      const rc  = running >= 0 ? '#16a34a' : '#dc2626';
      const fmtP = v => 'Rs ' + parseFloat(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
      return `<tr>
        <td>${idx+1}</td>
        <td>${r.date||'—'}</td>
        <td>${r.party_name||'—'}</td>
        <td>${r.bill_number||'—'}</td>
        <td>${r.category||'—'}</td>
        <td><span style="background:${tc}18;color:${tc};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">${r.type||'—'}</span></td>
        <td>${r.payment_type||'—'}</td>
        <td style="text-align:right;">${total ? fmtP(total) : '—'}</td>
        <td style="text-align:right;color:#16a34a;font-weight:600;">${cashIn ? fmtP(cashIn) : '—'}</td>
        <td style="text-align:right;color:#dc2626;font-weight:600;">${cashOut ? fmtP(cashOut) : '—'}</td>
        <td style="text-align:right;color:${rc};font-weight:700;">${fmtP(running)}</td>
      </tr>`;
    }).join('');

    const win = window.open('', '_blank', 'width=1100,height=700');
    win.document.write(`<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cash Flow Report — ${from} to ${to}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; padding: 20px; }
    .header { text-align: center; margin-bottom: 16px; }
    .header h2 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .header p  { font-size: 12px; color: #6b7280; }
    .summary { display: flex; gap: 24px; justify-content: center; margin-bottom: 18px; font-size: 12px; flex-wrap: wrap; }
    .summary span { padding: 6px 14px; border-radius: 6px; font-weight: 600; }
    .s-open  { background:#f0fdf4; color:#15803d; }
    .s-in    { background:#f0fdf4; color:#16a34a; }
    .s-out   { background:#fef2f2; color:#dc2626; }
    .s-close { background:#eff6ff; color:#2563eb; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th { background: #f3f4f6; padding: 8px 10px; text-align: left; font-weight: 600;
         color: #374151; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
    td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    tr:nth-child(even) td { background: #fafafa; }
    tfoot td { background: #f9fafb !important; font-weight: 700; border-top: 2px solid #e5e7eb; }
    @media print {
      body { padding: 10mm; }
      @page { size: A4 landscape; margin: 10mm; }
      button { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="header">
    <h2>Cash Flow Report</h2>
    <p>Period: ${from} &nbsp;to&nbsp; ${to}</p>
  </div>
  <div class="summary">
    <span class="s-open">Opening Cash: Rs ${opening}</span>
    <span class="s-in">Total Cash In: ${totalIn}</span>
    <span class="s-out">Total Cash Out: ${totalOut}</span>
    <span class="s-close">Closing Cash: ${closing}</span>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th><th>Date</th><th>Name</th><th>Ref No.</th>
        <th>Category</th><th>Type</th><th>Payment Type</th>
        <th style="text-align:right;">Total</th>
        <th style="text-align:right;">Cash In</th>
        <th style="text-align:right;">Cash Out</th>
        <th style="text-align:right;">Running Cash</th>
      </tr>
    </thead>
    <tbody>${rowsHtml}</tbody>
  </table>
  <script>window.onload = function(){ window.print(); }<\/script>
</body>
</html>`);
    win.document.close();
  });

  /* ── Row-level print ────────────────────────────────── */
  window.cfPrintRow = function(id, type) {
    const routes = {
      'Sale':        `/dashboard/sales/${id}/invoice-preview`,
      'Purchase':    `/dashboard/purchase-bills/${id}/print`,
      'Payment-In':  null,
      'Payment-Out': null,
      'Expense':     null,
      'Credit Note': `/dashboard/sale-return/${id}/print`,
      'Debit Note':  `/dashboard/purchase-return/${id}/print`,
    };
    const route = routes[type];
    if (route) {
      window.open(route, '_blank');
    } else {
      alert('Print not available for type: ' + type);
    }
  };

  /* ── Row-level share ────────────────────────────────── */
  window.cfShareRow = function(id, type) {
    const url = window.location.origin + (
      type === 'Sale'     ? `/dashboard/sales/${id}/invoice-preview` :
      type === 'Purchase' ? `/dashboard/purchase-bills/${id}/preview` :
      type === 'Credit Note' ? `/dashboard/sale-return/${id}/preview` :
      type === 'Debit Note'  ? `/dashboard/purchase-return/${id}/preview` :
      window.location.pathname
    );
    if (navigator.share) {
      navigator.share({ title: `${type} #${id}`, url });
    } else {
      navigator.clipboard?.writeText(url)
        .then(() => alert('Link copied to clipboard!'))
        .catch(() => alert('Share URL: ' + url));
    }
  };

  /* ── Init ───────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    setDefaultDates();

    const tabEl = document.getElementById('tab-cashFlow');
    if (!tabEl) return;

    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(m) {
        if (m.target.id === 'tab-cashFlow' && !m.target.classList.contains('d-none')) {
          if (!CF.raw.length && !CF.loading) fetchCashFlow();
        }
      });
    });
    observer.observe(tabEl, { attributes: true, attributeFilter: ['class'] });

    /* Also load immediately if tab is already visible on page load */
    if (!tabEl.classList.contains('d-none')) fetchCashFlow();
  });

})();
</script>
