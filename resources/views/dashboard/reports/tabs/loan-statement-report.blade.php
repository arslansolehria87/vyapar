{{-- Loan Statement Report Tab --}}
<div id="tab-loan statement" class="report-tab-content d-none">
  <div class="d-flex flex-column" style="min-height:100vh;padding:24px;background:#fff;border:1px solid #e5e7eb;">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div class="d-flex align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">Loan Account</span>
          <select id="ls-account-select" style="font-size:13px;border:1px solid #d1d5db;border-radius:4px;padding:5px 10px;color:#374151;outline:none;background:#fff;min-width:220px;">
            <option value="">-- Select Account --</option>
            @foreach(\App\Models\LoanAccount::orderBy('display_name')->get() as $loanAccount)
              <option value="{{ $loanAccount->id }}">{{ $loanAccount->display_name }}</option>
            @endforeach
          </select>
        </div>

        <label class="d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
          <input type="checkbox" id="ls-date-toggle" style="width:15px;height:15px;cursor:pointer;">
          <span style="font-size:14px;color:#6b7280;">Date filter</span>
        </label>

        <div id="ls-date-range" class="d-flex align-items-center gap-2 d-none">
          <div style="border:1px solid #d1d5db;border-radius:4px;padding:4px 10px;background:#fff;display:flex;align-items:center;gap:6px;">
            <span style="font-size:11px;color:#9ca3af;">From</span>
            <input type="date" id="ls-from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" style="border:none;outline:none;font-size:13px;color:#374151;">
          </div>
          <div style="border:1px solid #d1d5db;border-radius:4px;padding:4px 10px;background:#fff;display:flex;align-items:center;gap:6px;">
            <span style="font-size:11px;color:#9ca3af;">To</span>
            <input type="date" id="ls-to" value="{{ now()->format('Y-m-d') }}" style="border:none;outline:none;font-size:13px;color:#374151;">
          </div>
        </div>

        <button id="ls-apply-btn" style="font-size:12px;padding:6px 16px;background:#6366f1;color:#fff;border:none;border-radius:4px;cursor:pointer;">Apply</button>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <button id="ls-excel-btn" title="Export Excel" style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;">
          <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:17px;"></i>
        </button>
        <button id="ls-print-btn" title="Print" style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;">
          <i class="fa-solid fa-print" style="color:#4b5563;font-size:17px;"></i>
        </button>
        <a href="{{ route('loan-accounts') }}" class="btn text-white d-flex align-items-center gap-2" style="background:#2563eb;border:none;border-radius:6px;padding:8px 14px;font-size:13px;font-weight:600;white-space:nowrap;">
          <i class="fa-solid fa-plus"></i> Add Loan Account
        </a>
      </div>
    </div>

    <h2 style="font-weight:700;color:#1f2937;font-size:22px;margin:8px 0 16px;">Loan Statement Report</h2>

    <div id="ls-summary" class="d-flex gap-3 flex-wrap mb-3 d-none">
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 16px;min-width:170px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Opening Balance</div>
        <div id="ls-opening" style="font-size:17px;color:#111827;font-weight:700;margin-top:4px;">Rs 0.00</div>
      </div>
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;min-width:170px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Loan Amount</div>
        <div id="ls-total-loan" style="font-size:17px;color:#2563eb;font-weight:700;margin-top:4px;">Rs 0.00</div>
      </div>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;min-width:190px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Payments Received</div>
        <div id="ls-total-payments" style="font-size:17px;color:#16a34a;font-weight:700;margin-top:4px;">Rs 0.00</div>
      </div>
      <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 16px;min-width:180px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Interest Charged</div>
        <div id="ls-total-interest" style="font-size:17px;color:#c2410c;font-weight:700;margin-top:4px;">Rs 0.00</div>
      </div>
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;min-width:170px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Closing Balance</div>
        <div id="ls-closing" style="font-size:17px;color:#dc2626;font-weight:700;margin-top:4px;">Rs 0.00</div>
      </div>
    </div>

    <div id="ls-loading" class="d-none text-center py-5">
      <div class="spinner-border text-primary"><span class="visually-hidden">Loading...</span></div>
    </div>

    <div id="ls-table-wrap" class="table-responsive">
      <table id="ls-table" style="width:100%;border-collapse:collapse;">
        <thead style="background:#f3f4f6;">
          <tr style="border-bottom:2px solid #e5e7eb;">
            <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;border-right:1px solid #e5e7eb;width:70px;">#</th>
            <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;border-right:1px solid #e5e7eb;width:140px;">Date</th>
            <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;border-right:1px solid #e5e7eb;">Type</th>
            <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border-right:1px solid #e5e7eb;width:170px;">Amount</th>
            <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;width:180px;">Ending Balance</th>
          </tr>
        </thead>
        <tbody id="ls-body">
          <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:42px;font-size:14px;">Select an account and click Apply</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function(){
  var rows = [];
  var summary = {};
  var appliedFilters = { account: '', accountName: '', dateEnabled: false, from: '', to: '' };

  function money(value) {
    var n = parseFloat(value || 0);
    return 'Rs ' + (isNaN(n) ? 0 : n).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function dateText(value) {
    if (!value) return '';
    var d = new Date(value);
    return isNaN(d) ? value : d.toLocaleDateString('en-GB');
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function(ch) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch];
    });
  }

  function empty(message) {
    rows = [];
    document.getElementById('ls-body').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:42px;font-size:14px;">' + escapeHtml(message) + '</td></tr>';
    document.getElementById('ls-summary').classList.add('d-none');
  }

  function setSummary(data) {
    summary = data || {};
    document.getElementById('ls-opening').textContent = money(summary.opening_balance);
    document.getElementById('ls-total-loan').textContent = money(summary.total_loan_amount);
    document.getElementById('ls-total-payments').textContent = money(summary.total_payments_received);
    document.getElementById('ls-total-interest').textContent = money(summary.total_interest_charged);
    document.getElementById('ls-closing').textContent = money(summary.closing_balance);
    document.getElementById('ls-summary').classList.remove('d-none');
  }

  function render(data) {
    rows = data.transactions || [];
    setSummary(data);

    if (!rows.length) {
      document.getElementById('ls-body').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:42px;font-size:14px;">No transactions found for the selected filters.</td></tr>';
      return;
    }

    document.getElementById('ls-body').innerHTML = rows.map(function(row, index) {
      return '<tr style="border-bottom:1px solid #f3f4f6;">'
        + '<td style="padding:12px 16px;font-size:13px;color:#374151;border-right:1px solid #e5e7eb;">' + (index + 1) + '</td>'
        + '<td style="padding:12px 16px;font-size:13px;color:#374151;border-right:1px solid #e5e7eb;">' + escapeHtml(row.date_display || dateText(row.date)) + '</td>'
        + '<td style="padding:12px 16px;font-size:13px;color:#374151;border-right:1px solid #e5e7eb;">' + escapeHtml(row.type || '') + '</td>'
        + '<td style="padding:12px 16px;font-size:13px;color:#374151;text-align:right;border-right:1px solid #e5e7eb;">' + money(row.amount) + '</td>'
        + '<td style="padding:12px 16px;font-size:13px;color:#111827;text-align:right;font-weight:600;">' + money(row.ending_balance) + '</td>'
        + '</tr>';
    }).join('');
  }

  function load() {
    var select = document.getElementById('ls-account-select');
    var accountId = select.value;
    var dateEnabled = document.getElementById('ls-date-toggle').checked;

    if (!accountId) {
      empty('Select an account and click Apply');
      return;
    }

    var params = new URLSearchParams({ account_id: accountId });
    if (dateEnabled) {
      var from = document.getElementById('ls-from').value;
      var to = document.getElementById('ls-to').value;
      if (from) params.append('from', from);
      if (to) params.append('to', to);
    }

    appliedFilters = {
      account: accountId,
      accountName: select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : '',
      dateEnabled: dateEnabled,
      from: dateEnabled ? document.getElementById('ls-from').value : '',
      to: dateEnabled ? document.getElementById('ls-to').value : ''
    };

    document.getElementById('ls-loading').classList.remove('d-none');
    document.getElementById('ls-table-wrap').classList.add('d-none');

    fetch('{{ route('loan-accounts.json') }}?' + params.toString(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function(response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
      })
      .then(render)
      .catch(function(error) {
        console.error(error);
        empty('Failed to load loan statement. Please try again.');
      })
      .finally(function() {
        document.getElementById('ls-loading').classList.add('d-none');
        document.getElementById('ls-table-wrap').classList.remove('d-none');
      });
  }

  function filterText() {
    if (!appliedFilters.account) return '';
    var period = appliedFilters.dateEnabled
      ? ((appliedFilters.from ? dateText(appliedFilters.from) : 'Start') + ' to ' + (appliedFilters.to ? dateText(appliedFilters.to) : 'Today'))
      : 'All dates';
    return 'Account: ' + appliedFilters.accountName + ' | Period: ' + period;
  }

  function exportExcel() {
    if (!rows.length) {
      alert('No visible data to export.');
      return;
    }

    var table = '<table><thead><tr><th>#</th><th>Date</th><th>Type</th><th>Amount</th><th>Ending Balance</th></tr></thead><tbody>';
    rows.forEach(function(row, index) {
      table += '<tr><td>' + (index + 1) + '</td><td>' + escapeHtml(row.date_display || dateText(row.date)) + '</td><td>' + escapeHtml(row.type || '') + '</td><td>' + money(row.amount) + '</td><td>' + money(row.ending_balance) + '</td></tr>';
    });
    table += '</tbody></table><br><table>'
      + '<tr><td>Opening Balance</td><td>' + money(summary.opening_balance) + '</td></tr>'
      + '<tr><td>Total Loan Amount</td><td>' + money(summary.total_loan_amount) + '</td></tr>'
      + '<tr><td>Total Payments Received</td><td>' + money(summary.total_payments_received) + '</td></tr>'
      + '<tr><td>Total Interest Charged</td><td>' + money(summary.total_interest_charged) + '</td></tr>'
      + '<tr><td>Closing Balance</td><td>' + money(summary.closing_balance) + '</td></tr>'
      + '</table>';

    var blob = new Blob(['<html><head><meta charset="UTF-8"></head><body><h3>Loan Statement Report</h3><p>' + escapeHtml(filterText()) + '</p>' + table + '</body></html>'], { type: 'application/vnd.ms-excel' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'loan-statement-report.xls';
    document.body.appendChild(link);
    link.click();
    URL.revokeObjectURL(link.href);
    link.remove();
  }

  function printReport() {
    if (!rows.length) {
      alert('No visible data to print.');
      return;
    }

    var bodyRows = rows.map(function(row, index) {
      return '<tr><td>' + (index + 1) + '</td><td>' + escapeHtml(row.date_display || dateText(row.date)) + '</td><td>' + escapeHtml(row.type || '') + '</td><td class="num">' + money(row.amount) + '</td><td class="num">' + money(row.ending_balance) + '</td></tr>';
    }).join('');

    var printHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Loan Statement Report</title>'
      + '<style>body{font-family:Arial,sans-serif;color:#111827;padding:32px}h2{font-size:20px;margin:0 0 4px}p{font-size:12px;color:#6b7280;margin:0 0 18px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #e5e7eb;padding:8px 10px;font-size:12px}th{background:#f3f4f6;color:#6b7280;text-align:left}.num{text-align:right}.summary{width:420px;margin-top:22px;margin-left:auto}.summary td:last-child{text-align:right;font-weight:700}@media print{@page{margin:14mm}body{padding:0}}</style>'
      + '</head><body><h2>Loan Statement Report</h2><p>' + escapeHtml(filterText()) + '</p>'
      + '<table><thead><tr><th>#</th><th>Date</th><th>Type</th><th class="num">Amount</th><th class="num">Ending Balance</th></tr></thead><tbody>' + bodyRows + '</tbody></table>'
      + '<table class="summary"><tr><td>Opening Balance</td><td>' + money(summary.opening_balance) + '</td></tr><tr><td>Total Loan Amount</td><td>' + money(summary.total_loan_amount) + '</td></tr><tr><td>Total Payments Received</td><td>' + money(summary.total_payments_received) + '</td></tr><tr><td>Total Interest Charged</td><td>' + money(summary.total_interest_charged) + '</td></tr><tr><td>Closing Balance</td><td>' + money(summary.closing_balance) + '</td></tr></table>'
      + '<script>window.onload=function(){window.print();}<\/script></body></html>';

    var popup = window.open('', '_blank', 'width=960,height=720');
    popup.document.write(printHtml);
    popup.document.close();
  }

  document.getElementById('ls-date-toggle').addEventListener('change', function() {
    document.getElementById('ls-date-range').classList.toggle('d-none', !this.checked);
  });
  document.getElementById('ls-apply-btn').addEventListener('click', load);
  document.getElementById('ls-excel-btn').addEventListener('click', exportExcel);
  document.getElementById('ls-print-btn').addEventListener('click', printReport);
})();
</script>
