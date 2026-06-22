{{-- TAB 1: EXPENSE --}}
<div id="tab-expense" class="report-tab-content d-none">

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div class="d-flex rounded-pill overflow-hidden border" style="background:#E4F2FF;">
        <select id="exp-period-select" class="border-0 bg-transparent px-3 py-2 fw-medium"
                style="outline:none;font-size:13px;min-width:130px;border-right:1px solid #cce0f5 !important;">
          <option value="this_month" selected>This Month</option>
          <option value="last_month">Last Month</option>
          <option value="this_quarter">This Quarter</option>
          <option value="this_year">This Year</option>
          <option value="custom">Custom</option>
        </select>
        <div class="d-flex align-items-center px-3 gap-1" style="font-size:13px;min-width:200px;">
          <input type="date" id="exp-from-date" class="border-0 bg-transparent" style="outline:none;font-size:13px;width:105px;">
          <span class="text-muted">To</span>
          <input type="date" id="exp-to-date" class="border-0 bg-transparent" style="outline:none;font-size:13px;width:105px;">
        </div>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button class="btn p-0 d-flex align-items-center justify-content-center border"
              style="width:38px;height:38px;border-radius:50%;background:#fff;" title="Graph" onclick="toggleExpenseChart()">
        <i class="fa-solid fa-chart-bar" style="color:#6366f1;font-size:16px;"></i>
      </button>
      <button class="btn p-0 d-flex align-items-center justify-content-center border"
              style="width:38px;height:38px;border-radius:50%;background:#fff;" title="Excel" onclick="exportExpenseExcel()">
        <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:16px;"></i>
      </button>
      <button class="btn p-0 d-flex align-items-center justify-content-center border"
              style="width:38px;height:38px;border-radius:50%;background:#fff;" title="Print" onclick="printExpenseReport()">
        <i class="fa-solid fa-print" style="color:#4b5563;font-size:16px;"></i>
      </button>
      <a href="{{ route('expense') }}" class="btn text-white px-4 py-2 fw-semibold d-flex align-items-center gap-2"
         style="background:#ef4444;border:none;border-radius:8px;font-size:14px;">
        <i class="fa-solid fa-plus"></i> Add Expense
      </a>
    </div>
  </div>

  <div id="expenseChartArea" class="mb-3 d-none">
    <div class="bg-white border rounded-3 p-3">
      <canvas id="expenseBarChart" height="80"></canvas>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-auto">
      <div class="rounded-3 px-4 py-3 border text-center" style="background:#fef2f2;min-width:160px;">
        <div class="text-muted" style="font-size:12px;">Total Expenses</div>
        <div class="fw-bold text-danger fs-5 mt-1" id="exp-total-amount">Rs 0.00</div>
      </div>
    </div>
    <div class="col-auto">
      <div class="rounded-3 px-4 py-3 border text-center" style="background:#f0fdf4;min-width:160px;">
        <div class="text-muted" style="font-size:12px;">Paid</div>
        <div class="fw-bold text-success fs-5 mt-1" id="exp-total-paid">Rs 0.00</div>
      </div>
    </div>
    <div class="col-auto">
      <div class="rounded-3 px-4 py-3 border text-center" style="background:#fffbeb;min-width:160px;">
        <div class="text-muted" style="font-size:12px;">Balance Due</div>
        <div class="fw-bold text-warning fs-5 mt-1" id="exp-total-balance">Rs 0.00</div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0" style="font-size:15px;">TRANSACTIONS</h6>
      <div class="position-relative">
        <input type="text" id="expSearchInput" placeholder="Search…"
               class="form-control form-control-sm ps-5"
               style="border-radius:20px;min-width:180px;font-size:13px;" oninput="filterExpenseTable()">
        <i class="fa-solid fa-magnifying-glass position-absolute"
           style="left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:13px;"></i>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle" id="expenseTable"
             data-column-drag="native" data-column-drag-storage="vyapar.reports.expense.transactions.v1">
        <thead style="background:#f9fafb;">
          <tr>
            <th data-column-key="date" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;">DATE</th>
            <th data-column-key="expense_no" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;">EXP NO.</th>
            <th data-column-key="party" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;">PARTY</th>
            <th data-column-key="category_name" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;">CATEGORY NAME</th>
            <th data-column-key="payment_type" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;">PAYMENT TYPE</th>
            <th data-column-key="amount" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;text-align:right;">AMOUNT</th>
            <th data-column-key="balance_due" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;text-align:right;">BALANCE DUE</th>
            <th data-column-key="status" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;text-align:center;">STATUS</th>
            <th data-column-key="actions" style="width:40px;"></th>
          </tr>
        </thead>
        <tbody id="expenseTableBody">
          <tr><td colspan="9" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading…
          </td></tr>
        </tbody>
      </table>
    </div>
    <div class="border-top px-4 py-3 d-flex gap-4" style="background:#fafafa;">
      <span class="fw-semibold text-muted" style="font-size:13px;">Total: <span class="text-dark ms-1" id="expFooterTotal">Rs 0.00</span></span>
      <span class="fw-semibold text-muted" style="font-size:13px;">Paid: <span class="text-success ms-1" id="expFooterPaid">Rs 0.00</span></span>
      <span class="fw-semibold text-muted" style="font-size:13px;">Balance Due: <span class="text-danger ms-1" id="expFooterBalance">Rs 0.00</span></span>
    </div>
  </div>

  {{-- Context Menu --}}
  <div id="expContextMenu" class="dropdown-menu shadow border-0 py-1"
       style="display:none;position:fixed;z-index:9999;min-width:160px;border-radius:10px;">
    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#" id="expMenuViewEdit">
      <i class="fa-regular fa-pen-to-square text-primary" style="width:16px;"></i> View/Edit
    </a>
    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#" id="expMenuDelete">
      <i class="fa-regular fa-trash-can text-danger" style="width:16px;"></i> Delete
    </a>
    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#" id="expMenuDuplicate">
      <i class="fa-regular fa-copy text-secondary" style="width:16px;"></i> Duplicate
    </a>
    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#" id="expMenuPrint">
      <i class="fa-solid fa-print text-secondary" style="width:16px;"></i> Print
    </a>
  </div>

  {{-- Delete Modal --}}
  <div class="modal fade" id="expDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
      <div class="modal-content border-0 shadow-lg rounded-4 p-2">
        <div class="modal-body text-center py-4 px-4">
          <div class="mb-3"><i class="fa-solid fa-triangle-exclamation text-danger" style="font-size:40px;"></i></div>
          <h5 class="fw-bold mb-2">Delete Expense?</h5>
          <p class="text-muted mb-0" style="font-size:14px;">This action cannot be undone.</p>
        </div>
        <div class="modal-footer border-0 justify-content-center pb-4 gap-3">
          <button class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-danger rounded-pill px-4" id="expConfirmDeleteBtn">Yes, Delete</button>
        </div>
      </div>
    </div>
  </div>

</div>{{-- /tab-expense --}}


{{-- TAB 2: EXPENSE CATEGORY REPORT --}}
<div id="tab-expense category report" class="report-tab-content d-none">

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <div class="d-flex align-items-center border rounded px-3 py-2 bg-white gap-2">
        <label class="mb-0 text-muted" style="font-size:13px;">From</label>
        <input type="date" id="cat-from-date" class="border-0 bg-transparent" style="outline:none;font-size:13px;">
        <label class="mb-0 text-muted ms-2" style="font-size:13px;">To</label>
        <input type="date" id="cat-to-date" class="border-0 bg-transparent" style="outline:none;font-size:13px;">
        <button class="btn btn-sm btn-primary ms-2 rounded-pill px-3" onclick="loadExpCategoryReport()">Apply</button>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn p-0 d-flex align-items-center justify-content-center border"
              style="width:38px;height:38px;border-radius:50%;background:#fff;" onclick="exportCatExcel()">
        <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:16px;"></i>
      </button>
      <button class="btn p-0 d-flex align-items-center justify-content-center border"
              style="width:38px;height:38px;border-radius:50%;background:#fff;" onclick="printCatReport()">
        <i class="fa-solid fa-print" style="color:#4b5563;font-size:16px;"></i>
      </button>
      <a href="{{ route('expense') }}" class="btn text-white px-4 py-2 fw-semibold d-flex align-items-center gap-2"
         style="background:#ef4444;border:none;border-radius:8px;font-size:14px;">
        <i class="fa-solid fa-plus"></i> Add Expense
      </a>
    </div>
  </div>

  <h5 class="fw-bold mb-3" style="color:#1f2937;">EXPENSE</h5>

  <div class="bg-white rounded-3 border overflow-hidden">
    <div class="table-responsive">
      <table class="table mb-0 align-middle" id="expCategoryTable">
        <thead style="background:#f9fafb;">
          <tr>
            <th style="font-size:12px;font-weight:600;color:#6b7280;padding:14px 20px;">#</th>
            <th style="font-size:12px;font-weight:600;color:#6b7280;padding:14px 20px;">EXPENSE CATEGORY</th>
            <th style="font-size:12px;font-weight:600;color:#6b7280;padding:14px 20px;">CATEGORY TYPE</th>
            <th style="font-size:12px;font-weight:600;color:#6b7280;padding:14px 20px;text-align:right;">AMOUNT</th>
          </tr>
        </thead>
        <tbody id="expCategoryBody">
          <tr><td colspan="4" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading…
          </td></tr>
        </tbody>
        <tfoot style="background:#fafafa;">
          <tr class="border-top">
            <td colspan="3" class="fw-bold px-4 py-3" style="font-size:14px;">Total Expense</td>
            <td class="fw-bold px-4 py-3 text-end" style="font-size:14px;" id="expCatTotal">Rs 0.00</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  <div class="mt-2 px-2 text-end text-muted" style="font-size:13px;">
    Total Expense: <strong class="text-dark ms-1" id="expCatTotalBar">Rs 0.00</strong>
  </div>

</div>{{-- /tab-expense category report --}}


{{-- TAB 3: EXPENSE ITEM REPORT --}}
<div id="tab-expense item report" class="report-tab-content d-none">

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div class="d-flex rounded-pill overflow-hidden border" style="background:#E4F2FF;">
        <select id="ei-period-select" class="border-0 bg-transparent px-3 py-2 fw-medium"
                style="outline:none;font-size:13px;min-width:130px;border-right:1px solid #cce0f5 !important;">
          <option value="this_month" selected>This Month</option>
          <option value="last_month">Last Month</option>
          <option value="this_quarter">This Quarter</option>
          <option value="this_year">This Year</option>
          <option value="custom">Custom</option>
        </select>
        <div class="d-flex align-items-center px-3 gap-1" style="font-size:13px;min-width:200px;">
          <input type="date" id="ei-from-date" class="border-0 bg-transparent" style="outline:none;font-size:13px;width:105px;">
          <span class="text-muted">To</span>
          <input type="date" id="ei-to-date" class="border-0 bg-transparent" style="outline:none;font-size:13px;width:105px;">
        </div>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn p-0 d-flex align-items-center justify-content-center border"
              style="width:38px;height:38px;border-radius:50%;background:#fff;" onclick="exportItemExcel()">
        <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:16px;"></i>
      </button>
      <button class="btn p-0 d-flex align-items-center justify-content-center border"
              style="width:38px;height:38px;border-radius:50%;background:#fff;" onclick="printItemReport()">
        <i class="fa-solid fa-print" style="color:#4b5563;font-size:16px;"></i>
      </button>
      <a href="{{ route('expense') }}" class="btn text-white px-4 py-2 fw-semibold d-flex align-items-center gap-2"
         style="background:#ef4444;border:none;border-radius:8px;font-size:14px;">
        <i class="fa-solid fa-plus"></i> Add Expense
      </a>
    </div>
  </div>

  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="position-relative" style="max-width:280px;">
      <input type="text" id="eiSearchInput" placeholder="Search expense items…"
             class="form-control form-control-sm ps-5"
             style="border-radius:20px;font-size:13px;" oninput="filterItemTable()">
      <i class="fa-solid fa-magnifying-glass position-absolute"
         style="left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:13px;"></i>
    </div>
  </div>

  <div class="bg-white rounded-3 border overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle" id="expItemTable"
             data-column-drag="native" data-column-drag-storage="vyapar.reports.expense.items.v1">
        <thead style="background:#f9fafb;">
          <tr>
            <th data-column-key="item_name" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;">EXPENSE ITEM</th>
            <th data-column-key="party" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;">PARTY</th>
            <th data-column-key="unit_price" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;text-align:right;">UNIT PRICE</th>
            <th data-column-key="quantity" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;text-align:right;">QUANTITY</th>
            <th data-column-key="amount" style="font-size:12px;font-weight:600;color:#6b7280;padding:12px 16px;text-align:right;">AMOUNT</th>
          </tr>
        </thead>
        <tbody id="expItemBody">
          <tr><td colspan="5" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading…
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>{{-- /tab-expense item report --}}


{{-- SCRIPTS --}}
@once
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
@endonce
<script>
const expFmt  = v => 'Rs ' + parseFloat(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
const expCsrf = () => window.App?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

// Init dates
(function(){
  const now=new Date(), y=now.getFullYear(), m=now.getMonth();
  const pad=n=>String(n).padStart(2,'0');
  const from=`${y}-${pad(m+1)}-01`;
  const to=`${y}-${pad(m+1)}-${pad(new Date(y,m+1,0).getDate())}`;
  ['exp-from-date','cat-from-date','ei-from-date'].forEach(id=>{const el=document.getElementById(id);if(el)el.value=from;});
  ['exp-to-date',  'cat-to-date',  'ei-to-date'  ].forEach(id=>{const el=document.getElementById(id);if(el)el.value=to;});
})();

function expPeriodDates(p){
  const now=new Date(),y=now.getFullYear(),m=now.getMonth();
  const pad=n=>String(n).padStart(2,'0');
  const ymd=d=>`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  if(p==='this_month') return[`${y}-${pad(m+1)}-01`,ymd(new Date(y,m+1,0))];
  if(p==='last_month') return[`${y}-${pad(m)}-01`,ymd(new Date(y,m,0))];
  if(p==='this_quarter'){const q=Math.floor(m/3);return[ymd(new Date(y,q*3,1)),ymd(new Date(y,q*3+3,0))];}
  if(p==='this_year')  return[`${y}-01-01`,`${y}-12-31`];
  return[`${y}-${pad(m+1)}-01`,ymd(now)];
}

document.getElementById('exp-period-select')?.addEventListener('change',function(){
  if(this.value==='custom')return;
  const[f,t]=expPeriodDates(this.value);
  document.getElementById('exp-from-date').value=f;
  document.getElementById('exp-to-date').value=t;
  loadExpenseReport();
});
document.getElementById('exp-from-date')?.addEventListener('change',loadExpenseReport);
document.getElementById('exp-to-date')  ?.addEventListener('change',loadExpenseReport);
document.getElementById('ei-period-select')?.addEventListener('change',function(){
  if(this.value==='custom')return;
  const[f,t]=expPeriodDates(this.value);
  document.getElementById('ei-from-date').value=f;
  document.getElementById('ei-to-date').value=t;
  loadExpItemReport();
});
document.getElementById('ei-from-date')?.addEventListener('change',loadExpItemReport);
document.getElementById('ei-to-date')  ?.addEventListener('change',loadExpItemReport);
document.getElementById('cat-from-date')?.addEventListener('change',loadExpCategoryReport);
document.getElementById('cat-to-date')  ?.addEventListener('change',loadExpCategoryReport);

// ── TAB 1: EXPENSE LIST ─────────────────────────────────────
let expAllRows=[],expCurrentId=null;

async function loadExpenseReport(){
  const from=document.getElementById('exp-from-date')?.value;
  const to  =document.getElementById('exp-to-date')?.value;
  if(!from||!to)return;
  document.getElementById('expenseTableBody').innerHTML=`<tr><td colspan="9" class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary me-2"></div><span class="text-muted">Loading…</span></td></tr>`;
  try{
    const r=await fetch(`/dashboard/reports/expense?from=${from}&to=${to}`,{headers:{'Accept':'application/json','X-CSRF-TOKEN':expCsrf()}});
    const d=await r.json();
    expAllRows=d.expenses||[];
    renderExpenseTable(expAllRows);
    updateExpSummary(expAllRows);
  }catch(e){
    document.getElementById('expenseTableBody').innerHTML=`<tr><td colspan="9" class="text-center py-4 text-danger"><i class="fa-solid fa-circle-exclamation me-2"></i>Failed to load expenses.</td></tr>`;
  }
}

function renderExpenseTable(rows){
  const tbody=document.getElementById('expenseTableBody');
  if(!rows.length){
    tbody.innerHTML=`<tr><td colspan="9" class="text-center py-5 text-muted"><i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>No expenses found.</td></tr>`;
    return;
  }
  tbody.innerHTML=rows.map(r=>`
    <tr style="cursor:pointer;">
      <td data-column-key="date" style="padding:14px 16px;font-size:14px;">${r.expense_date||'-'}</td>
      <td data-column-key="expense_no" style="padding:14px 16px;font-size:14px;">${r.expense_no||'-'}</td>
      <td data-column-key="party" style="padding:14px 16px;font-size:14px;">${r.party||'-'}</td>
      <td data-column-key="category_name" style="padding:14px 16px;font-size:14px;">${r.category_name||'-'}</td>
      <td data-column-key="payment_type" style="padding:14px 16px;font-size:14px;">${r.payment_type||'-'}</td>
      <td data-column-key="amount" style="padding:14px 16px;font-size:14px;text-align:right;">${expFmt(r.total_amount)}</td>
      <td data-column-key="balance_due" style="padding:14px 16px;font-size:14px;text-align:right;">${expFmt(r.balance_due||0)}</td>
      <td data-column-key="status" style="padding:14px 16px;font-size:14px;text-align:center;">
        <span class="badge rounded-pill px-3 py-1"
          style="background:${r.status==='Paid'?'#d1fae5':'#fef3c7'};color:${r.status==='Paid'?'#065f46':'#92400e'};font-size:12px;">
          ${r.status||'Paid'}
        </span>
      </td>
      <td data-column-key="actions" style="padding:14px 8px;text-align:center;">
        <button class="btn btn-sm p-1 border-0 bg-transparent" onclick="showExpMenu(event,${r.id})">
          <i class="fa-solid fa-ellipsis-vertical text-secondary"></i>
        </button>
      </td>
    </tr>`).join('');
}

function updateExpSummary(rows){
  const total  =rows.reduce((s,r)=>s+parseFloat(r.total_amount||0),0);
  const balance=rows.reduce((s,r)=>s+parseFloat(r.balance_due||0),0);
  const paid   =total-balance;
  document.getElementById('exp-total-amount').textContent =expFmt(total);
  document.getElementById('exp-total-paid').textContent   =expFmt(paid);
  document.getElementById('exp-total-balance').textContent=expFmt(balance);
  document.getElementById('expFooterTotal').textContent   =expFmt(total);
  document.getElementById('expFooterPaid').textContent    =expFmt(paid);
  document.getElementById('expFooterBalance').textContent =expFmt(balance);
}

function filterExpenseTable(){
  const q=(document.getElementById('expSearchInput')?.value||'').toLowerCase();
  renderExpenseTable(expAllRows.filter(r=>
    (r.expense_date||'').includes(q)||
    (r.expense_no||'').toLowerCase().includes(q)||
    (r.party||'').toLowerCase().includes(q)||
    (r.category_name||'').toLowerCase().includes(q)||
    (r.payment_type||'').toLowerCase().includes(q)
  ));
}

function showExpMenu(e,id){
  e.stopPropagation();
  expCurrentId=id;
  const menu=document.getElementById('expContextMenu');
  menu.style.display='block';
  menu.style.top=(e.clientY+4)+'px';
  menu.style.left=(e.clientX-160)+'px';
  document.getElementById('expMenuViewEdit').href =`/dashboard/expense/${id}/edit`;
  document.getElementById('expMenuDuplicate').href=`/dashboard/expense/${id}/duplicate`;
  document.getElementById('expMenuPrint').href    =`/dashboard/expense/${id}/print`;
}
document.addEventListener('click',()=>{document.getElementById('expContextMenu').style.display='none';});

document.getElementById('expMenuDelete')?.addEventListener('click',function(e){
  e.preventDefault();
  new bootstrap.Modal(document.getElementById('expDeleteModal')).show();
});

document.getElementById('expConfirmDeleteBtn')?.addEventListener('click',async function(){
  if(!expCurrentId)return;
  this.disabled=true;
  this.innerHTML='<span class="spinner-border spinner-border-sm"></span> Deleting…';
  try{
    await fetch(`/dashboard/expense/${expCurrentId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':expCsrf(),'Accept':'application/json'}});
    bootstrap.Modal.getInstance(document.getElementById('expDeleteModal'))?.hide();
    loadExpenseReport();
  }catch(e){alert('Delete failed.');}
  finally{this.disabled=false;this.textContent='Yes, Delete';}
});

let expChart=null;
function toggleExpenseChart(){
  const area=document.getElementById('expenseChartArea');
  area.classList.toggle('d-none');
  if(!area.classList.contains('d-none'))buildExpenseChart(expAllRows);
}
function buildExpenseChart(rows){
  if(typeof Chart==='undefined')return;
  if(expChart)expChart.destroy();
  const grouped={};
  rows.forEach(r=>{grouped[r.category_name||'Other']=(grouped[r.category_name||'Other']||0)+parseFloat(r.total_amount||0);});
  expChart=new Chart(document.getElementById('expenseBarChart'),{
    type:'bar',
    data:{labels:Object.keys(grouped),datasets:[{label:'Amount',data:Object.values(grouped),backgroundColor:'rgba(239,68,68,0.7)',borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
  });
}

function exportExpenseExcel(){
  const from=document.getElementById('exp-from-date')?.value;
  const to  =document.getElementById('exp-to-date')?.value;
  window.open(`/dashboard/reports/expense/export?from=${from}&to=${to}`,'_blank');
}
function printExpenseReport(){
  const win=window.open('','_blank');
  win.document.write(`<!DOCTYPE html><html><head><title>Expense Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{padding:20px}</style></head><body>
    <h4 class="fw-bold mb-3">Expense Report</h4>
    ${document.getElementById('expenseTable')?.outerHTML||''}
    <script>window.onload=()=>window.print();<\/script></body></html>`);
  win.document.close();
}

// ── TAB 2: CATEGORY REPORT ──────────────────────────────────
async function loadExpCategoryReport(){
  const from=document.getElementById('cat-from-date')?.value;
  const to  =document.getElementById('cat-to-date')?.value;
  if(!from||!to)return;
  document.getElementById('expCategoryBody').innerHTML=`<tr><td colspan="4" class="text-center py-5"><div class="spinner-border spinner-border-sm me-2"></div>Loading…</td></tr>`;
  try{
    const r=await fetch(`/dashboard/reports/expense-category-report?from=${from}&to=${to}`,{headers:{'Accept':'application/json'}});
    const d=await r.json();
    const rows=d.rows||[];
    if(!rows.length){
      document.getElementById('expCategoryBody').innerHTML=`<tr><td colspan="4" class="text-center py-5 text-muted">No data for this period.</td></tr>`;
      document.getElementById('expCatTotal').textContent='Rs 0.00';
      document.getElementById('expCatTotalBar').textContent='Rs 0.00';
      return;
    }
    document.getElementById('expCategoryBody').innerHTML=rows.map((r,i)=>`
      <tr>
        <td style="padding:14px 20px;font-size:14px;">${i+1}</td>
        <td style="padding:14px 20px;font-size:14px;">${r.category_name||'-'}</td>
        <td style="padding:14px 20px;font-size:14px;">
          <span class="badge rounded-pill px-3"
            style="background:${r.category_type==='Direct Expense'?'#dbeafe':'#fce7f3'};
                   color:${r.category_type==='Direct Expense'?'#1d4ed8':'#9d174d'};font-size:12px;">
            ${r.category_type||'-'}
          </span>
        </td>
        <td style="padding:14px 20px;font-size:14px;text-align:right;">${expFmt(r.amount)}</td>
      </tr>`).join('');
    const total=rows.reduce((s,r)=>s+parseFloat(r.amount||0),0);
    document.getElementById('expCatTotal').textContent   =expFmt(total);
    document.getElementById('expCatTotalBar').textContent=expFmt(total);
  }catch(e){
    document.getElementById('expCategoryBody').innerHTML=`<tr><td colspan="4" class="text-center py-4 text-danger">Failed to load.</td></tr>`;
  }
}

function exportCatExcel(){
  const from=document.getElementById('cat-from-date')?.value;
  const to  =document.getElementById('cat-to-date')?.value;
  window.open(`/dashboard/reports/expense-category-report/export?from=${from}&to=${to}`,'_blank');
}
function printCatReport(){
  const win=window.open('','_blank');
  win.document.write(`<!DOCTYPE html><html><head><title>Expense Category Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{padding:20px}</style></head><body>
    <h4 class="fw-bold mb-3">Expense Category Report</h4>
    ${document.getElementById('expCategoryTable')?.outerHTML||''}
    <script>window.onload=()=>window.print();<\/script></body></html>`);
  win.document.close();
}

// ── TAB 3: ITEM REPORT ─────────────────────────────────────
let eiAllRows=[];

async function loadExpItemReport(){
  const from=document.getElementById('ei-from-date')?.value;
  const to  =document.getElementById('ei-to-date')?.value;
  if(!from||!to)return;
  document.getElementById('expItemBody').innerHTML=`<tr><td colspan="5" class="text-center py-5"><div class="spinner-border spinner-border-sm me-2"></div>Loading…</td></tr>`;
  try{
    const r=await fetch(`/dashboard/reports/expense-item-report?from=${from}&to=${to}`,{headers:{'Accept':'application/json'}});
    const d=await r.json();
    eiAllRows=d.rows||[];
    renderEiTable(eiAllRows);
  }catch(e){
    document.getElementById('expItemBody').innerHTML=`<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load.</td></tr>`;
  }
}

function renderEiTable(rows){
  const tbody=document.getElementById('expItemBody');
  if(!rows.length){
    tbody.innerHTML=`<tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>No items found.</td></tr>`;
    return;
  }
  tbody.innerHTML=rows.map(r=>`
    <tr>
      <td data-column-key="item_name" style="padding:14px 16px;font-size:14px;">${r.item_name||'-'}</td>
      <td data-column-key="party" style="padding:14px 16px;font-size:14px;">${r.party||'-'}</td>
      <td data-column-key="unit_price" style="padding:14px 16px;font-size:14px;text-align:right;">${expFmt(r.price_per_unit)}</td>
      <td data-column-key="quantity" style="padding:14px 16px;font-size:14px;text-align:right;">${parseFloat(r.quantity||0).toFixed(2)}</td>
      <td data-column-key="amount" style="padding:14px 16px;font-size:14px;text-align:right;">${expFmt(r.amount)}</td>
    </tr>`).join('');
}

function filterItemTable(){
  const q=(document.getElementById('eiSearchInput')?.value||'').toLowerCase();
  renderEiTable(eiAllRows.filter(r=>(r.item_name||'').toLowerCase().includes(q)||(r.party||'').toLowerCase().includes(q)));
}

function exportItemExcel(){
  const from=document.getElementById('ei-from-date')?.value;
  const to  =document.getElementById('ei-to-date')?.value;
  window.open(`/dashboard/reports/expense-item-report/export?from=${from}&to=${to}`,'_blank');
}
function printItemReport(){
  const win=window.open('','_blank');
  win.document.write(`<!DOCTYPE html><html><head><title>Expense Item Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{padding:20px}</style></head><body>
    <h4 class="fw-bold mb-3">Expense Item Report</h4>
    ${document.getElementById('expItemTable')?.outerHTML||''}
    <script>window.onload=()=>window.print();<\/script></body></html>`);
  win.document.close();
}

// ── Auto-load when sidebar tab clicked ─────────────────────
document.querySelectorAll('.reports-nav .nav-link[data-target]').forEach(function(el){
  el.addEventListener('click',function(){
    const t=this.getAttribute('data-target');
    if(t==='expense')                 setTimeout(loadExpenseReport,    100);
    if(t==='expense category report') setTimeout(loadExpCategoryReport,100);
    if(t==='expense item report')     setTimeout(loadExpItemReport,    100);
  });
});
</script>
