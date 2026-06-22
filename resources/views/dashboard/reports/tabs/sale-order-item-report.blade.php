{{-- Sale Order Item Report Tab --}}
<div id="tab-sale-order-item" class="report-tab-content d-none">
  <div class="d-flex flex-column" style="min-height:100vh;padding:24px;background:#fff;border:1px solid #e5e7eb;">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <select id="soi-party-filter"
          style="border:1px solid #d1d5db;border-radius:4px;padding:6px 10px;font-size:13px;width:180px;background:#fff;outline:none;color:#374151;">
          <option value="">All Parties</option>
          @foreach(($parties ?? collect())->sortBy('name') as $party)
            @if(!empty($party->name))
              <option value="{{ $party->id }}">{{ $party->name }}</option>
            @endif
          @endforeach
        </select>

        <select id="soi-order-type"
          style="border:1px solid #d1d5db;border-radius:4px;padding:6px 10px;font-size:13px;width:155px;background:#fff;color:#2563eb;font-weight:600;outline:none;">
          <option value="sale_order">Sale Order</option>
          <option value="purchase_order">Purchase Order</option>
        </select>

        <input type="text" id="soi-item-filter" placeholder="Item filter"
          style="border:1px solid #d1d5db;border-radius:4px;padding:6px 10px;font-size:13px;width:160px;outline:none;color:#374151;">

        <select id="soi-status-filter"
          style="border:1px solid #d1d5db;border-radius:4px;padding:6px 10px;font-size:13px;background:#fff;color:#374151;outline:none;">
          <option value="">All Status</option>
          <option value="open">Open</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <div class="d-flex align-items-center gap-1">
          <span style="font-size:12px;color:#9ca3af;">From</span>
          <input type="date" id="soi-from-date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
            style="border:1px solid #d1d5db;border-radius:4px;padding:5px 8px;font-size:13px;color:#374151;outline:none;">
          <span style="font-size:12px;color:#9ca3af;">To</span>
          <input type="date" id="soi-to-date" value="{{ now()->format('Y-m-d') }}"
            style="border:1px solid #d1d5db;border-radius:4px;padding:5px 8px;font-size:13px;color:#374151;outline:none;">
        </div>

        <button id="soi-apply-btn"
          style="background:#4f46e5;color:#fff;border:none;border-radius:4px;padding:6px 14px;font-size:13px;cursor:pointer;">
          Apply
        </button>
      </div>

      <div class="d-flex gap-2">
        <button id="soi-excel-btn" title="Export Excel"
          style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
          <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:17px;"></i>
        </button>
        <button id="soi-print-btn" title="Print"
          style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
          <i class="fa-solid fa-print" style="color:#4b5563;font-size:17px;"></i>
        </button>
      </div>
    </div>

    <h2 id="soi-report-title" style="font-weight:700;color:#1f2937;margin:8px 0 16px;font-size:22px;">Sale Order Item Report</h2>

    <div class="d-flex gap-3 mb-3 flex-wrap">
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;min-width:150px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Items</div>
        <div id="soi-total-count" style="font-size:18px;font-weight:700;color:#2563eb;margin-top:4px;">0</div>
      </div>
      <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 16px;min-width:150px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Qty</div>
        <div id="soi-card-qty" style="font-size:18px;font-weight:700;color:#c2410c;margin-top:4px;">0</div>
      </div>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;min-width:170px;">
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Amount</div>
        <div id="soi-card-amount" style="font-size:18px;font-weight:700;color:#16a34a;margin-top:4px;">Rs 0.00</div>
      </div>
    </div>

    <div id="soi-loading" class="d-none text-center py-5">
      <div class="spinner-border text-primary"><span class="visually-hidden">Loading...</span></div>
    </div>

    <div id="soi-table-wrap" class="table-responsive">
      <table class="w-100" id="soi-main-table" data-column-drag="native"
        data-column-drag-storage="vyapar.reports.sale-order-items.transactions.v1"
        style="border-collapse:collapse;">
        <thead style="background:#f9fafb;">
          <tr style="border-bottom:2px solid #e5e7eb;">
            <th data-column-key="index" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">#</th>
            <th data-column-key="date" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Date</th>
            <th data-column-key="order_number" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Order No.</th>
            <th data-column-key="party_name" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Party</th>
            <th data-column-key="item_name" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Item Name</th>
            <th data-column-key="quantity" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;">Qty</th>
            <th data-column-key="unit" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Unit</th>
            <th data-column-key="rate" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;">Rate</th>
            <th data-column-key="amount" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;">Amount</th>
            <th data-column-key="status" style="padding:11px 14px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;">Status</th>
          </tr>
        </thead>
        <tbody id="soi-table-body">
          <tr><td colspan="10" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px;">Click Apply to load sale order items</td></tr>
        </tbody>
        <tfoot id="soi-table-foot" style="display:none;">
          <tr style="background:#f9fafb;border-top:2px solid #e5e7eb;">
            <td data-column-key="index" style="padding:11px 14px;font-size:14px;font-weight:700;color:#1f2937;">Total</td>
            <td data-column-key="date"></td>
            <td data-column-key="order_number"></td>
            <td data-column-key="party_name"></td>
            <td data-column-key="item_name"></td>
            <td data-column-key="quantity" id="soi-total-qty" style="padding:11px 14px;font-size:14px;font-weight:700;color:#1f2937;text-align:right;">0</td>
            <td data-column-key="unit"></td>
            <td data-column-key="rate"></td>
            <td data-column-key="amount" id="soi-total-amt" style="padding:11px 14px;font-size:14px;font-weight:700;color:#1f2937;text-align:right;">Rs 0.00</td>
            <td data-column-key="status"></td>
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

  function qty(value) {
    var n = parseFloat(value || 0);
    if (isNaN(n)) return '0';
    return n % 1 === 0 ? String(n) : n.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

  function selectedOrderLabel() {
    var select = document.getElementById('soi-order-type');
    return select?.options[select.selectedIndex]?.text || 'Sale Order';
  }

  function setEmpty(message, error) {
    rows = [];
    document.getElementById('soi-table-body').innerHTML = '<tr><td colspan="10" style="padding:48px;text-align:center;color:' + (error ? '#ef4444' : '#9ca3af') + ';font-size:13px;">' + esc(message) + '</td></tr>';
    document.getElementById('soi-table-foot').style.display = 'none';
    document.getElementById('soi-total-count').textContent = '0';
    document.getElementById('soi-card-qty').textContent = '0';
    document.getElementById('soi-card-amount').textContent = money(0);
  }

  function render(data) {
    rows = data.rows || [];
    var totalQty = rows.reduce(function(sum, row) { return sum + parseFloat(row.quantity || 0); }, 0);
    var totalAmount = rows.reduce(function(sum, row) { return sum + parseFloat(row.amount || 0); }, 0);

    document.getElementById('soi-total-count').textContent = rows.length;
    document.getElementById('soi-card-qty').textContent = qty(totalQty);
    document.getElementById('soi-card-amount').textContent = money(totalAmount);
    document.getElementById('soi-total-qty').textContent = qty(totalQty);
    document.getElementById('soi-total-amt').textContent = money(totalAmount);

    if (!rows.length) {
      setEmpty('No ' + selectedOrderLabel().toLowerCase() + ' items found for the selected filters.', false);
      return;
    }

    document.getElementById('soi-table-body').innerHTML = rows.map(function(row, index) {
      var status = String(row.status || '').toLowerCase();
      var color = status === 'completed' ? '#16a34a' : (status === 'cancelled' ? '#dc2626' : '#ca8a04');
      return '<tr style="border-bottom:1px solid #e5e7eb;">'
        + '<td data-column-key="index" style="padding:11px 14px;font-size:13px;color:#6b7280;">' + (index + 1) + '</td>'
        + '<td data-column-key="date" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(dateText(row.date)) + '</td>'
        + '<td data-column-key="order_number" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(row.bill_number || '-') + '</td>'
        + '<td data-column-key="party_name" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(row.party_name || 'Walk-in') + '</td>'
        + '<td data-column-key="item_name" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(row.item_name || 'Item') + '</td>'
        + '<td data-column-key="quantity" style="padding:11px 14px;font-size:13px;color:#374151;text-align:right;">' + qty(row.quantity) + '</td>'
        + '<td data-column-key="unit" style="padding:11px 14px;font-size:13px;color:#374151;">' + esc(row.unit || '-') + '</td>'
        + '<td data-column-key="rate" style="padding:11px 14px;font-size:13px;color:#374151;text-align:right;">' + money(row.unit_price) + '</td>'
        + '<td data-column-key="amount" style="padding:11px 14px;font-size:13px;color:#374151;text-align:right;">' + money(row.amount) + '</td>'
        + '<td data-column-key="status" style="padding:11px 14px;font-size:13px;"><span style="color:' + color + ';font-weight:600;">' + esc(statusText(row.status)) + '</span></td>'
        + '</tr>';
    }).join('');
    document.getElementById('soi-table-foot').style.display = '';
  }

  function loadSaleOrderItemReport() {
    var params = new URLSearchParams({
      from: document.getElementById('soi-from-date').value,
      to: document.getElementById('soi-to-date').value,
      order_type: document.getElementById('soi-order-type').value
    });
    var party = document.getElementById('soi-party-filter').value;
    var item = document.getElementById('soi-item-filter').value.trim();
    var status = document.getElementById('soi-status-filter').value;
    if (party) params.append('party', party);
    if (item) params.append('item', item);
    if (status) params.append('status', status);

    document.getElementById('soi-report-title').textContent = selectedOrderLabel() + ' Item Report';
    document.getElementById('soi-loading').classList.remove('d-none');
    document.getElementById('soi-table-wrap').classList.add('d-none');

    fetch('{{ route('reports.sale-order-items') }}?' + params.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(function(response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
      })
      .then(render)
      .catch(function(error) {
        console.error(error);
        setEmpty('Failed to load ' + selectedOrderLabel().toLowerCase() + ' item data. Please try again.', true);
      })
      .finally(function() {
        document.getElementById('soi-loading').classList.add('d-none');
        document.getElementById('soi-table-wrap').classList.remove('d-none');
      });
  }

  function exportSaleOrderItemCSV() {
    if (!rows.length) return alert('No data to export.');
    var orderLabel = selectedOrderLabel();
    var csv = orderLabel + ' Item Report\\n';
    csv += 'From,' + document.getElementById('soi-from-date').value + ',To,' + document.getElementById('soi-to-date').value + '\\n\\n';
    csv += '#,Date,Order No.,Party,Item Name,Qty,Unit,Rate,Amount,Status\\n';
    rows.forEach(function(row, index) {
      csv += [
        index + 1,
        dateText(row.date),
        row.bill_number || '',
        row.party_name || 'Walk-in',
        row.item_name || 'Item',
        qty(row.quantity),
        row.unit || '',
        money(row.unit_price),
        money(row.amount),
        statusText(row.status)
      ].map(function(cell) { return '"' + String(cell).replace(/"/g, '""') + '"'; }).join(',') + '\\n';
    });
    csv += '\\nTotal Qty,' + document.getElementById('soi-total-qty').textContent + '\\n';
    csv += 'Total Amount,' + document.getElementById('soi-total-amt').textContent + '\\n';

    var link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    link.download = orderLabel.toLowerCase().replace(/\s+/g, '_') + '_item_report.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
  }

  function printSaleOrderItemReport() {
    if (!rows.length) return alert('No data to print.');
    var orderLabel = selectedOrderLabel();
    var popup = window.open('', '_blank', 'width=1100,height=760');
    popup.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + esc(orderLabel) + ' Item Report</title><style>body{font-family:Arial,sans-serif;padding:32px;color:#1f2937}h2{font-size:20px;margin:0 0 4px}p{font-size:12px;color:#6b7280;margin:0 0 18px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #e5e7eb;padding:8px 10px;font-size:12px}th{background:#f3f4f6;text-align:left}tfoot td{font-weight:700}@media print{@page{margin:10mm landscape}body{padding:0}}</style></head><body><h2>' + esc(orderLabel) + ' Item Report</h2><p>From ' + esc(document.getElementById('soi-from-date').value) + ' To ' + esc(document.getElementById('soi-to-date').value) + '</p>' + document.getElementById('soi-main-table').outerHTML + '<script>window.onload=function(){window.print();}<\/script></body></html>');
    popup.document.close();
  }

  window.loadSaleOrderItemReport = loadSaleOrderItemReport;
  window.exportSaleOrderItemCSV = exportSaleOrderItemCSV;
  window.printSaleOrderItemReport = printSaleOrderItemReport;

  document.getElementById('soi-apply-btn')?.addEventListener('click', loadSaleOrderItemReport);
  document.getElementById('soi-party-filter')?.addEventListener('change', loadSaleOrderItemReport);
  document.getElementById('soi-order-type')?.addEventListener('change', loadSaleOrderItemReport);
  document.getElementById('soi-status-filter')?.addEventListener('change', loadSaleOrderItemReport);
  document.getElementById('soi-excel-btn')?.addEventListener('click', exportSaleOrderItemCSV);
  document.getElementById('soi-print-btn')?.addEventListener('click', printSaleOrderItemReport);
  loadSaleOrderItemReport();
})();
</script>
