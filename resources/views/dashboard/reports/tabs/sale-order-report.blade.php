{{-- Sale Order Report Tab --}}
<div id="tab-sale-order" class="report-tab-content d-none">
  <div class="d-flex flex-column" style="min-height:100vh;padding:24px;background:#fff;border:1px solid #e5e7eb;">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <input type="text" id="so-party-filter" placeholder="Party filter"
          style="border:1px solid #d1d5db;border-radius:4px;padding:6px 10px;font-size:13px;width:170px;outline:none;color:#374151;">

        <select id="so-status-filter"
          style="border:1px solid #d1d5db;border-radius:4px;padding:6px 10px;font-size:13px;background:#fff;color:#374151;outline:none;">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <div class="d-flex align-items-center gap-1">
          <span style="font-size:12px;color:#9ca3af;">From</span>
          <input type="date" id="so-from-date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
            style="border:1px solid #d1d5db;border-radius:4px;padding:5px 8px;font-size:13px;color:#374151;outline:none;">
          <span style="font-size:12px;color:#9ca3af;">To</span>
          <input type="date" id="so-to-date" value="{{ now()->format('Y-m-d') }}"
            style="border:1px solid #d1d5db;border-radius:4px;padding:5px 8px;font-size:13px;color:#374151;outline:none;">
        </div>

        <button id="so-apply-btn"
          style="background:#4f46e5;color:#fff;border:none;border-radius:4px;padding:6px 14px;font-size:13px;cursor:pointer;">
          Apply
        </button>
      </div>

      <div class="d-flex gap-2">
        <button id="so-excel-btn" title="Export Excel"
          style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
          <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:17px;"></i>
        </button>
        <button id="so-print-btn" title="Print"
          style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
          <i class="fa-solid fa-print" style="color:#4b5563;font-size:17px;"></i>
        </button>
      </div>
    </div>

    <h2 style="font-weight:700;color:#1f2937;margin:8px 0 16px;font-size:22px;">Sale Order Report</h2>

    <div class="d-flex gap-3 mb-3 flex-wrap">
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;min-width:150px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Orders</div>
        <div id="so-total-count" style="font-size:18px;font-weight:700;color:#2563eb;margin-top:4px;">0</div>
      </div>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;min-width:170px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Amount</div>
        <div id="so-total-amount" style="font-size:18px;font-weight:700;color:#16a34a;margin-top:4px;">Rs 0.00</div>
      </div>
      <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 16px;min-width:150px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Open Orders</div>
        <div id="so-open-count" style="font-size:18px;font-weight:700;color:#c2410c;margin-top:4px;">0</div>
      </div>
    </div>

    <div id="so-loading" class="d-none text-center py-5">
      <div class="spinner-border text-primary"><span class="visually-hidden">Loading...</span></div>
    </div>

    <div id="so-table-wrap" class="table-responsive">
      <table class="w-100" id="so-main-table" data-column-drag="native"
        data-column-drag-storage="vyapar.reports.sale-order.transactions.v1"
        style="border-collapse:collapse;">
        <thead style="background:#f9fafb;">
          <tr style="border-bottom:2px solid #e5e7eb;">
            <th data-column-key="index" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">#</th>
            <th data-column-key="date" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Date</th>
            <th data-column-key="order_number" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Order No.</th>
            <th data-column-key="party_name" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Party Name</th>
            <th data-column-key="status" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Status</th>
            <th data-column-key="amount" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;">Amount</th>
            <th data-column-key="balance" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;">Balance</th>
          </tr>
        </thead>
        <tbody id="so-table-body">
          <tr><td colspan="7" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px;">Click Apply to load sale orders</td></tr>
        </tbody>
        <tfoot id="so-table-foot" style="display:none;">
          <tr style="background:#f9fafb;border-top:2px solid #e5e7eb;">
            <td data-column-key="index" style="padding:11px 14px;font-size:14px;font-weight:700;color:#1f2937;">Total</td>
            <td data-column-key="date"></td>
            <td data-column-key="order_number"></td>
            <td data-column-key="party_name"></td>
            <td data-column-key="status"></td>
            <td data-column-key="amount" id="so-grand-total" style="padding:11px 14px;font-size:14px;font-weight:700;color:#1f2937;text-align:right;">Rs 0.00</td>
            <td data-column-key="balance" id="so-balance-total" style="padding:11px 14px;font-size:14px;font-weight:700;color:#1f2937;text-align:right;">Rs 0.00</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

@once
<script src="{{ asset('js/transaction-column-drag.js') }}"></script>
@endonce
<script>
(function(){
  var rows = [];

  function money(value) {
    var n = parseFloat(value || 0);
    return 'Rs ' + (isNaN(n) ? 0 : n).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function dateText(value) {
    if (!value) return '';
    var d = new Date(value);
    return isNaN(d) ? value : d.toLocaleDateString('en-GB');
  }

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function(ch) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch];
    });
  }

  function statusText(value) {
    var s = String(value || '').toLowerCase();
    if (!s) return '-';
    return s.charAt(0).toUpperCase() + s.slice(1);
  }

  function setEmpty(message, error) {
    rows = [];
    document.getElementById('so-table-body').innerHTML = '<tr><td colspan="7" style="padding:48px;text-align:center;color:' + (error ? '#ef4444' : '#9ca3af') + ';font-size:13px;">' + esc(message) + '</td></tr>';
    document.getElementById('so-table-foot').style.display = 'none';
    document.getElementById('so-total-count').textContent = '0';
    document.getElementById('so-total-amount').textContent = money(0);
    document.getElementById('so-open-count').textContent = '0';
  }

  function render(data) {
    rows = data.rows || [];
    var totalAmount = rows.reduce(function(sum, row) { return sum + parseFloat(row.grand_total || row.total_amount || 0); }, 0);
    var totalBalance = rows.reduce(function(sum, row) { return sum + parseFloat(row.balance || 0); }, 0);
    var openOrders = rows.filter(function(row) {
      return ['pending', 'confirmed', 'open'].indexOf(String(row.status || '').toLowerCase()) !== -1;
    }).length;

    document.getElementById('so-total-count').textContent = rows.length;
    document.getElementById('so-total-amount').textContent = money(totalAmount);
    document.getElementById('so-open-count').textContent = openOrders;
    document.getElementById('so-grand-total').textContent = money(totalAmount);
    document.getElementById('so-balance-total').textContent = money(totalBalance);

    if (!rows.length) {
      setEmpty('No sale orders found for the selected filters.', false);
      return;
    }

    document.getElementById('so-table-body').innerHTML = rows.map(function(row, index) {
      var amount = parseFloat(row.grand_total || row.total_amount || 0);
      var status = String(row.status || '').toLowerCase();
      var color = status === 'completed' ? '#16a34a' : (status === 'cancelled' ? '#dc2626' : '#ca8a04');
      return '<tr style="border-bottom:1px solid #e5e7eb;">'
        + '<td data-column-key="index" style="padding:11px 14px;font-size:13px;color:#6b7280;">' + (index + 1) + '</td>'
        + '<td data-column-key="date" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(dateText(row.date)) + '</td>'
        + '<td data-column-key="order_number" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(row.bill_number || row.reference_bill_number || '-') + '</td>'
        + '<td data-column-key="party_name" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(row.party_name || 'Walk-in') + '</td>'
        + '<td data-column-key="status" style="padding:11px 14px;font-size:13px;"><span style="color:' + color + ';font-weight:600;">' + esc(statusText(row.status)) + '</span></td>'
        + '<td data-column-key="amount" style="padding:11px 14px;font-size:13px;color:#374151;text-align:right;">' + money(amount) + '</td>'
        + '<td data-column-key="balance" style="padding:11px 14px;font-size:13px;color:#374151;text-align:right;">' + money(row.balance) + '</td>'
        + '</tr>';
    }).join('');
    document.getElementById('so-table-foot').style.display = '';
  }

  function loadSaleOrderReport() {
    var params = new URLSearchParams({
      from: document.getElementById('so-from-date').value,
      to: document.getElementById('so-to-date').value
    });
    var party = document.getElementById('so-party-filter').value.trim();
    var status = document.getElementById('so-status-filter').value;
    if (party) params.append('party', party);
    if (status) params.append('status', status);

    document.getElementById('so-loading').classList.remove('d-none');
    document.getElementById('so-table-wrap').classList.add('d-none');

    fetch('{{ route('reports.sale-order') }}?' + params.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(function(response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
      })
      .then(render)
      .catch(function(error) {
        console.error(error);
        setEmpty('Failed to load sale order data. Please try again.', true);
      })
      .finally(function() {
        document.getElementById('so-loading').classList.add('d-none');
        document.getElementById('so-table-wrap').classList.remove('d-none');
      });
  }

  function exportSaleOrderCSV() {
    if (!rows.length) return alert('No data to export.');
    var csv = 'Sale Order Report\\n';
    csv += 'From,' + document.getElementById('so-from-date').value + ',To,' + document.getElementById('so-to-date').value + '\\n\\n';
    csv += '#,Date,Order No.,Party Name,Status,Amount,Balance\\n';
    rows.forEach(function(row, index) {
      csv += [
        index + 1,
        dateText(row.date),
        row.bill_number || row.reference_bill_number || '',
        row.party_name || 'Walk-in',
        statusText(row.status),
        money(row.grand_total || row.total_amount || 0),
        money(row.balance)
      ].map(function(cell) { return '"' + String(cell).replace(/"/g, '""') + '"'; }).join(',') + '\\n';
    });
    csv += '\\nTotal Amount,' + document.getElementById('so-grand-total').textContent + '\\n';

    var link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    link.download = 'sale_order_report.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
  }

  function printSaleOrderReport() {
    if (!rows.length) return alert('No data to print.');
    var popup = window.open('', '_blank', 'width=960,height=720');
    popup.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Sale Order Report</title><style>body{font-family:Arial,sans-serif;padding:32px;color:#1f2937}h2{font-size:20px;margin:0 0 4px}p{font-size:12px;color:#6b7280;margin:0 0 18px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #e5e7eb;padding:8px 10px;font-size:12px}th{background:#f3f4f6;text-align:left}.num{text-align:right}tfoot td{font-weight:700}@media print{@page{margin:14mm}body{padding:0}}</style></head><body><h2>Sale Order Report</h2><p>From ' + esc(document.getElementById('so-from-date').value) + ' To ' + esc(document.getElementById('so-to-date').value) + '</p>' + document.getElementById('so-main-table').outerHTML + '<script>window.onload=function(){window.print();}<\/script></body></html>');
    popup.document.close();
  }

  window.loadSaleOrderReport = loadSaleOrderReport;
  window.exportSaleOrderCSV = exportSaleOrderCSV;
  window.printSaleOrderReport = printSaleOrderReport;

  document.getElementById('so-apply-btn')?.addEventListener('click', loadSaleOrderReport);
  document.getElementById('so-excel-btn')?.addEventListener('click', exportSaleOrderCSV);
  document.getElementById('so-print-btn')?.addEventListener('click', printSaleOrderReport);
})();
</script>
