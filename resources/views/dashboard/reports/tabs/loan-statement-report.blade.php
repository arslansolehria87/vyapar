{{-- ============================================================
     Loan Statement Report Tab
     resources/views/dashboard/reports/tabs/loan-statement-report.blade.php
     ============================================================ --}}

<div id="tab-loan statement" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height:100vh; padding:24px; background:#fff; border:1px solid #e5e7eb;">

        {{-- ── Top Filter & Action Row ───────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            {{-- Left: Account + Date filters --}}
            <div class="d-flex align-items-center flex-wrap gap-3">

                {{-- ACCOUNT dropdown --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase;
                                letter-spacing:.5px; margin-bottom:4px;">Account:</div>
                    <select id="loan-acct-select"
                        style="border:1px solid #d1d5db; border-radius:4px; padding:7px 12px;
                               font-size:13px; color:#374151; background:#fff; outline:none; min-width:200px;">
                        <option value="">— All Accounts —</option>
                        @foreach(\App\Models\LoanAccount::orderBy('display_name')->get() as $la)
                            <option value="{{ $la->id }}">{{ $la->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date filter checkbox --}}
                <div style="display:flex; align-items:center; gap:6px; align-self:flex-end; padding-bottom:8px;">
                    <input type="checkbox" id="loan-date-filter-enabled" checked
                           onchange="toggleLoanDateFilter()"
                           style="width:14px; height:14px; cursor:pointer;">
                    <label for="loan-date-filter-enabled"
                           style="font-size:13px; color:#374151; font-weight:600; cursor:pointer; margin:0;">
                        Date filter
                    </label>
                </div>

                {{-- From date --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase;
                                letter-spacing:.5px; margin-bottom:4px;">From</div>
                    <input type="date" id="loan-from"
                        value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        style="border:1px solid #d1d5db; border-radius:4px; padding:7px 10px;
                               font-size:13px; color:#374151; outline:none;">
                </div>

                {{-- To date --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase;
                                letter-spacing:.5px; margin-bottom:4px;">To</div>
                    <input type="date" id="loan-to"
                        value="{{ now()->format('Y-m-d') }}"
                        style="border:1px solid #d1d5db; border-radius:4px; padding:7px 10px;
                               font-size:13px; color:#374151; outline:none;">
                </div>

                {{-- Apply button --}}
                <div style="align-self:flex-end;">
                    <button onclick="loadLoanStatement()"
                        style="background:#4f46e5; color:#fff; border:none; border-radius:6px;
                               padding:8px 20px; font-size:13px; font-weight:600; cursor:pointer;">
                        Apply
                    </button>
                </div>
            </div>

            {{-- Right: Export, Print, Add Loan A/C --}}
            <div class="d-flex align-items-center gap-2">
                <button onclick="exportLoanCSV()" title="Export Excel"
                    style="width:38px; height:38px; border-radius:50%; border:1px solid #e5e7eb;
                           background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-file-excel" style="color:#10b981; font-size:17px;"></i>
                </button>
                <button onclick="printLoanStatement()" title="Print"
                    style="width:38px; height:38px; border-radius:50%; border:1px solid #e5e7eb;
                           background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-print" style="color:#4b5563; font-size:17px;"></i>
                </button>
                <button onclick="openAddLoanModal()"
                    style="background:#2563eb; color:#fff; border:none; border-radius:6px;
                           padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer;
                           display:flex; align-items:center; gap:6px; white-space:nowrap;">
                    <i class="fa-solid fa-plus"></i> Add Loan A/C
                </button>
            </div>
        </div>

        {{-- ── Data Table ───────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="w-100" id="loan-stmt-table" style="border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:left; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            #
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:left; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            DATE
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:left; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            Account
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:left; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            TYPE
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:left; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            Details
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:right; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            Principal
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:right; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            Charges
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:right; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            Amount
                        </th>
                        <th style="padding:11px 16px; font-size:12px; font-weight:600; color:#6b7280;
                                   text-align:right; background:#fff; text-transform:uppercase; letter-spacing:.4px;">
                            Ending Balance
                        </th>
                    </tr>
                </thead>
                <tbody id="loan-stmt-body">
                    <tr>
                        <td colspan="9"
                            style="padding:60px 16px; text-align:center; color:#9ca3af; font-size:13px;">
                            Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ── Loan Account Summary ─────────────────────────── --}}
        <div id="loan-summary-section" style="display:none; margin-top:32px;">
            <h3 style="font-size:15px; font-weight:700; color:#1f2937; margin-bottom:16px;">
                Loan Account Summary
            </h3>
            <table style="border-collapse:collapse; min-width:400px;">
                <tbody>
                    <tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:11px 16px; font-size:13px; color:#374151;">Opening Balance</td>
                        <td style="padding:11px 16px; font-size:13px; color:#374151; text-align:right; font-weight:600;"
                            id="ls-opening">Rs 0.00</td>
                    </tr>
                    <tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:11px 16px; font-size:13px; color:#374151;">Balance Due</td>
                        <td style="padding:11px 16px; font-size:13px; color:#dc2626; text-align:right; font-weight:600;"
                            id="ls-due">Rs 0.00</td>
                    </tr>
                    <tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:11px 16px; font-size:13px; color:#374151;">Total Principal Paid</td>
                        <td style="padding:11px 16px; font-size:13px; color:#16a34a; text-align:right; font-weight:600;"
                            id="ls-paid">Rs 0.00</td>
                    </tr>
                    <tr style="border-top:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb;">
                        <td style="padding:11px 16px; font-size:13px; color:#374151;">Total Principal Due</td>
                        <td style="padding:11px 16px; font-size:13px; color:#374151; text-align:right; font-weight:600;"
                            id="ls-pdue">Rs 0.00</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>


{{-- ============================================================
     ADD LOAN ACCOUNT MODAL
     ============================================================ --}}
<div id="add-loan-modal-backdrop"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45);
           z-index:9990; align-items:center; justify-content:center;">

    <div style="background:#fff; border-radius:8px; width:100%; max-width:620px;
                margin:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); overflow:hidden;">

        {{-- Modal Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center;
                    padding:18px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="margin:0; font-size:16px; font-weight:700; color:#1f2937;">
                Add Loan Account
            </h3>
            <button onclick="closeAddLoanModal()"
                style="background:none; border:none; width:28px; height:28px; border-radius:50%;
                       background:#f3f4f6; cursor:pointer; font-size:16px; color:#6b7280;
                       display:flex; align-items:center; justify-content:center; line-height:1;">
                &times;
            </button>
        </div>

        {{-- Modal Body --}}
        <div style="padding:24px;">
            <form id="add-loan-form" onsubmit="saveLoanAccount(event)">
                @csrf

                {{-- Row 1: Account Name + Lender Bank --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <input type="text" name="account_name" placeholder="Account Name *" required
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                    <div>
                        <input type="text" name="lender_bank" placeholder="Lender Bank"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                </div>

                {{-- Row 2: Account Number + Description --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <input type="text" name="account_number" placeholder="Account Number"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                    <div>
                        <input type="text" name="description" placeholder="Description"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                </div>

                {{-- Row 3: Current Balance + Balance as of date --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <input type="number" name="current_balance" placeholder="Current Balance *"
                            step="0.01" required
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                    <div style="position:relative;">
                        <label style="position:absolute; top:-9px; left:10px; font-size:11px;
                                      color:#6b7280; background:#fff; padding:0 4px;">
                            Balance as of
                        </label>
                        <input type="date" name="balance_as_of"
                            value="{{ now()->format('Y-m-d') }}"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                </div>

                {{-- Row 4: Loan received In --}}
                <div style="margin-bottom:16px;">
                    <div style="position:relative;">
                        <label style="position:absolute; top:-9px; left:10px; font-size:11px;
                                      color:#6b7280; background:#fff; padding:0 4px;">
                            Loan received In
                        </label>
                        <select name="loan_received_in"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; background:#fff;
                                   outline:none; box-sizing:border-box;">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                </div>

                {{-- Row 5: Interest Rate + Term Duration --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div style="position:relative;">
                        <input type="number" name="interest_rate" placeholder="Interest Rate"
                            step="0.01"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 48px 10px 14px; font-size:13px; color:#374151;
                                   outline:none; box-sizing:border-box;">
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%);
                                     font-size:12px; color:#9ca3af;">% per annum</span>
                    </div>
                    <div>
                        <input type="number" name="term_duration" placeholder="Term Duration (in Months)"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                </div>

                {{-- Row 6: Processing Fee + Processing Fee Paid from --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                    <div>
                        <input type="number" name="processing_fee" placeholder="Processing Fee"
                            step="0.01"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; outline:none;
                                   box-sizing:border-box;">
                    </div>
                    <div style="position:relative;">
                        <label style="position:absolute; top:-9px; left:10px; font-size:11px;
                                      color:#6b7280; background:#fff; padding:0 4px;">
                            Processing Fee Paid from
                        </label>
                        <select name="processing_fee_paid_from"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px;
                                   padding:10px 14px; font-size:13px; color:#374151; background:#fff;
                                   outline:none; box-sizing:border-box;">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                </div>

                {{-- Save button --}}
                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit"
                        style="background:#2563eb; color:#fff; border:none; border-radius:6px;
                               padding:10px 32px; font-size:14px; font-weight:600; cursor:pointer;
                               min-width:100px;">
                        SAVE
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


{{-- ============================================================
     SCRIPTS
     ============================================================ --}}
<script>

/* ── Helpers ────────────────────────────────────────────────── */
function lsFmt(val) {
    return 'Rs ' + parseFloat(val || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
function lsRowBold(type) {
    return ['Opening Loan','EMI Paid','Loan Adjustment','Processing Fee'].includes(type);
}

/* ── Toggle date inputs ─────────────────────────────────────── */
function toggleLoanDateFilter() {
    const enabled = document.getElementById('loan-date-filter-enabled').checked;
    document.getElementById('loan-from').disabled = !enabled;
    document.getElementById('loan-to').disabled   = !enabled;
}

/* ── Load Loan Statement ────────────────────────────────────── */
function loadLoanStatement() {
    const accountId   = document.getElementById('loan-acct-select').value;
    const dateEnabled = document.getElementById('loan-date-filter-enabled').checked;
    const from        = dateEnabled ? document.getElementById('loan-from').value : '';
    const to          = dateEnabled ? document.getElementById('loan-to').value   : '';
    const tbody       = document.getElementById('loan-stmt-body');
    const summary     = document.getElementById('loan-summary-section');

    tbody.innerHTML = `<tr><td colspan="5" style="padding:60px;text-align:center;color:#9ca3af;font-size:13px;">
        <i class="fa-solid fa-spinner fa-spin me-2"></i>Loading…</td></tr>`;
    summary.style.display = 'none';

    // If account selected → /dashboard/loan-accounts/{id}
    // If NO account selected → /dashboard/loan-accounts  (returns ALL)
   let url = accountId
    ? `/dashboard/loan-accounts/${accountId}`
    : `/dashboard/loan-accounts-json`;   // ✅ THIS returns JSON

    const params = [];
    if (from) params.push(`from=${from}`);
    if (to)   params.push(`to=${to}`);
    if (params.length) url += '?' + params.join('&');

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        // Support multiple response shapes your API might return
        const transactions = data.transactions || data.entries || data.data || [];
        const opening = parseFloat(data.opening_balance ?? data.loan_amount ?? data.current_balance ?? 0);
        const due     = parseFloat(data.balance_due     ?? data.current_balance ?? 0);
        const paid    = parseFloat(data.principal_paid  ?? 0);
        const pdue    = parseFloat(data.principal_due   ?? due);

        document.getElementById('ls-opening').textContent = lsFmt(opening);
        document.getElementById('ls-due').textContent     = lsFmt(due);
        document.getElementById('ls-paid').textContent    = lsFmt(paid);
        document.getElementById('ls-pdue').textContent    = lsFmt(pdue);
        summary.style.display = '';

        if (!transactions.length) {
            tbody.innerHTML = `<tr><td colspan="5" style="padding:60px;text-align:center;color:#9ca3af;font-size:13px;">
                No transactions found for the selected period.</td></tr>`;
            return;
        }

        tbody.innerHTML = transactions.map((t, i) => {
            const amt    = parseFloat(t.amount ?? t.debit ?? t.credit ?? 0);
            const ending = parseFloat(t.ending_balance ?? t.balance ?? 0);
            const type   = t.type ?? t.transaction_type ?? '—';
            const bold   = lsRowBold(type) ? 'font-weight:700;' : '';
            const bg     = bold ? '#f9fafb' : '';

            return `
            <tr style="border-bottom:1px solid #e5e7eb; background:${bg}"
                onmouseover="this.style.background='#f9fafb'"
                onmouseout="this.style.background='${bg}'">
                <td style="padding:13px 16px; font-size:13px; color:#374151; ${bold}">${i + 1}</td>
                <td style="padding:13px 16px; font-size:13px; color:#374151; ${bold}">${t.date ?? '—'}</td>
                <td style="padding:13px 16px; font-size:13px; color:#374151; ${bold}">${type}</td>
                <td style="padding:13px 16px; font-size:13px; color:#374151; text-align:right; ${bold}">${lsFmt(amt)}</td>
                <td style="padding:13px 16px; font-size:13px; color:#374151; text-align:right; ${bold}">${lsFmt(ending)}</td>
            </tr>`;
        }).join('');
    })
    .catch(err => {
        console.error('Loan statement error:', err);
        tbody.innerHTML = `<tr><td colspan="5" style="padding:60px;text-align:center;color:#ef4444;font-size:13px;">
            Failed to load. Check console for details. (${err.message})</td></tr>`;
    });
}

/* ── Add Loan Modal ─────────────────────────────────────────── */
function openAddLoanModal() {
    const backdrop = document.getElementById('add-loan-modal-backdrop');
    backdrop.style.display = 'flex';
    document.getElementById('add-loan-form').reset();
    document.querySelector('#add-loan-form [name="balance_as_of"]').value =
        new Date().toISOString().split('T')[0];
}
function closeAddLoanModal() {
    document.getElementById('add-loan-modal-backdrop').style.display = 'none';
}
document.getElementById('add-loan-modal-backdrop').addEventListener('click', function(e) {
    if (e.target === this) closeAddLoanModal();
});

/* ── Save Loan Account ──────────────────────────────────────── */
function saveLoanAccount(e) {
    e.preventDefault();
    const form     = document.getElementById('add-loan-form');
    const saveBtn  = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    saveBtn.disabled    = true;
    saveBtn.textContent = 'Saving…';

    fetch('{{ route("loan-accounts.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                         || '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.id || data.loan_account) {
            const acct = data.loan_account || data;
            const sel  = document.getElementById('loan-acct-select');
            const opt  = document.createElement('option');
            opt.value       = acct.id;
            opt.textContent = acct.account_name;
            opt.selected    = true;
            sel.appendChild(opt);

            closeAddLoanModal();
            loadLoanStatement();
            showLoanToast('Loan account added successfully!', 'success');
        } else {
            const errors = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Failed to save. Please try again.');
            alert(errors);
        }
    })
    .catch(() => alert('Network error. Please try again.'))
    .finally(() => {
        saveBtn.disabled    = false;
        saveBtn.textContent = 'SAVE';
    });
}

/* ── Toast ──────────────────────────────────────────────────── */
function showLoanToast(message, type) {
    const colors = { success:'#16a34a', error:'#dc2626' };
    const toast  = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:99999;
        background:${colors[type] || '#374151'}; color:#fff;
        padding:12px 20px; border-radius:6px; font-size:13px;
        font-weight:600; box-shadow:0 4px 12px rgba(0,0,0,.2);
        transition:opacity .3s;`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity='0'; setTimeout(()=>toast.remove(),300); }, 3000);
}

/* ── Export CSV ─────────────────────────────────────────────── */
function exportLoanCSV() {
    const table = document.getElementById('loan-stmt-table');
    if (!table) return;
    let csv = '#,Date,Type,Amount,Ending Balance\n';
    table.querySelectorAll('tbody tr').forEach(tr => {
        const cells = [...tr.querySelectorAll('td')];
        if (cells.length >= 5)
            csv += cells.map(td => '"' + td.innerText.trim().replace(/"/g,'""') + '"').join(',') + '\n';
    });
    csv += '\nLoan Account Summary\n';
    csv += '"Opening Balance","' + document.getElementById('ls-opening').textContent + '"\n';
    csv += '"Balance Due","'     + document.getElementById('ls-due').textContent     + '"\n';
    csv += '"Principal Paid","'  + document.getElementById('ls-paid').textContent    + '"\n';
    csv += '"Principal Due","'   + document.getElementById('ls-pdue').textContent    + '"\n';

    const a = document.createElement('a');
    a.href     = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'loan_statement.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

/* ── Print ──────────────────────────────────────────────────── */
function printLoanStatement() {
    const tableHTML = document.getElementById('loan-stmt-table')?.outerHTML || '';
    const summaryHTML = `
        <div style="margin-top:24px;">
          <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Loan Account Summary</h3>
          <table style="border-collapse:collapse;min-width:360px;">
            <tr style="border-top:1px solid #e5e7eb;">
              <td style="padding:9px 14px;font-size:13px;">Opening Balance</td>
              <td style="padding:9px 14px;font-size:13px;text-align:right;font-weight:700;">${document.getElementById('ls-opening').textContent}</td>
            </tr>
            <tr style="border-top:1px solid #e5e7eb;">
              <td style="padding:9px 14px;font-size:13px;color:#dc2626;">Balance Due</td>
              <td style="padding:9px 14px;font-size:13px;text-align:right;font-weight:700;color:#dc2626;">${document.getElementById('ls-due').textContent}</td>
            </tr>
            <tr style="border-top:1px solid #e5e7eb;">
              <td style="padding:9px 14px;font-size:13px;color:#16a34a;">Total Principal Paid</td>
              <td style="padding:9px 14px;font-size:13px;text-align:right;font-weight:700;color:#16a34a;">${document.getElementById('ls-paid').textContent}</td>
            </tr>
            <tr style="border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;">
              <td style="padding:9px 14px;font-size:13px;">Total Principal Due</td>
              <td style="padding:9px 14px;font-size:13px;text-align:right;font-weight:700;">${document.getElementById('ls-pdue').textContent}</td>
            </tr>
          </table>
        </div>`;

    const sel      = document.getElementById('loan-acct-select');
    const acctName = sel.options[sel.selectedIndex]?.text || 'All Accounts';
    const from     = document.getElementById('loan-from').value;
    const to       = document.getElementById('loan-to').value;

    const w = window.open('', '_blank');
    w.document.write(`
        <!DOCTYPE html><html>
        <head>
          <meta charset="UTF-8">
          <title>Loan Statement — ${acctName}</title>
          <style>
            body  { font-family:Arial,sans-serif; padding:32px; color:#1f2937; }
            h2    { font-size:18px; font-weight:700; margin-bottom:4px; }
            p     { font-size:12px; color:#6b7280; margin:0 0 20px; }
            table { width:100%; border-collapse:collapse; font-size:13px; }
            th    { padding:10px 14px; font-size:11px; font-weight:700; color:#6b7280;
                    text-transform:uppercase; border-bottom:2px solid #e5e7eb; text-align:left; }
            td    { padding:11px 14px; border-bottom:1px solid #f3f4f6; }
            @media print { button { display:none !important; } }
          </style>
        </head>
        <body>
          <h2>Loan Statement — ${acctName}</h2>
          <p>Period: ${from} to ${to}</p>
          ${tableHTML}
          ${summaryHTML}
          <script>window.onload=function(){window.print();}<\/script>
        </body></html>`);
    w.document.close();
}

/* ── Auto-load ALL accounts on page ready ───────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    loadLoanStatement(); // runs with no account selected = loads all
});
</script>
