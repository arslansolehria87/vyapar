{{-- ============================================================
     ITEM STOCK REPORTS — JavaScript
     Include this at the bottom of your reports.blade.php
     before the closing </body> tag
     ============================================================ --}}

<script>
// ================================================================
// CALENDAR
// ================================================================
let _calTarget = null;
let _calCallback = null;
let _calYear, _calMonth;
const _MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const _DAYS   = ['Su','Mo','Tu','We','Th','Fr','Sa'];

function openCalendar(pickerId, displayId, callbackName) {
    _calTarget   = pickerId;
    _calCallback = callbackName || null;
    const picker = document.getElementById(pickerId);
    const now    = new Date();
    if (picker && picker.value) {
        const d = new Date(picker.value);
        _calYear  = d.getFullYear();
        _calMonth = d.getMonth();
    } else {
        _calYear  = now.getFullYear();
        _calMonth = now.getMonth();
    }
    renderCalendarModal();
    const modal = new bootstrap.Modal(document.getElementById('calendarModal'));
    modal.show();
}

function renderCalendarModal() {
    document.getElementById('cal-month-year').textContent = _MONTHS[_calMonth] + ' ' + _calYear;
    const grid    = document.getElementById('cal-days-grid');
    const first   = new Date(_calYear, _calMonth, 1).getDay();
    const total   = new Date(_calYear, _calMonth + 1, 0).getDate();
    const prevTotal = new Date(_calYear, _calMonth, 0).getDate();
    const today   = new Date();
    const picker  = document.getElementById(_calTarget);
    const selVal  = picker ? picker.value : '';

    let html = _DAYS.map(d => `<div class="cal-header-cell">${d}</div>`).join('');

    for (let i = first - 1; i >= 0; i--) {
        html += `<div class="cal-day other-month">${prevTotal - i}</div>`;
    }
    for (let d = 1; d <= total; d++) {
        const isToday  = today.getFullYear() === _calYear && today.getMonth() === _calMonth && today.getDate() === d;
        const dateStr  = `${_calYear}-${String(_calMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const isSelect = selVal === dateStr;
        html += `<div class="cal-day ${isToday ? 'today' : ''} ${isSelect ? 'selected' : ''}"
                    onclick="calSelectDay(${d})">${d}</div>`;
    }
    const remaining = 42 - first - total;
    for (let d = 1; d <= remaining; d++) {
        html += `<div class="cal-day other-month">${d}</div>`;
    }
    grid.innerHTML = html;
}

function calPrev() { _calMonth--; if (_calMonth < 0) { _calMonth = 11; _calYear--; } renderCalendarModal(); }
function calNext() { _calMonth++; if (_calMonth > 11) { _calMonth = 0; _calYear++; } renderCalendarModal(); }

function calSelectDay(d) {
    const dateStr  = `${_calYear}-${String(_calMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const picker   = document.getElementById(_calTarget);
    if (picker) { picker.value = dateStr; }

    // Find display input (same prefix but -display)
    const displayId = _calTarget.replace('-picker', '-display');
    const display   = document.getElementById(displayId);
    if (display) { display.value = formatDateDisplay(dateStr); }

    bootstrap.Modal.getInstance(document.getElementById('calendarModal'))?.hide();

    if (_calCallback && typeof window[_calCallback] === 'function') {
        window[_calCallback]();
    }
}

function syncDisplay(pickerEl, displayId) {
    const display = document.getElementById(displayId);
    if (display && pickerEl.value) {
        display.value = formatDateDisplay(pickerEl.value);
    }
}

function formatDateDisplay(ymd) {
    if (!ymd) return '';
    const [y, m, d] = ymd.split('-');
    return `${d}/${m}/${y}`;
}


// ================================================================
// DATE BOX TOGGLE
// ================================================================
function toggleDateBox(boxId, checkbox) {
    const box = document.getElementById(boxId);
    if (!box) return;
    box.style.display = checkbox.checked ? 'flex' : 'none';
}


// ================================================================
// PERIOD LABEL
// ================================================================
function setPeriodLabel(btnId, label) {
    const btn = document.getElementById(btnId);
    if (btn) btn.textContent = label;
}


// ================================================================
// EXPORT EXCEL — opens save dialog
// ================================================================
function exportReport(tableId, reportName) {
    const now      = new Date();
    const datePart = `${String(now.getDate()).padStart(2,'0')}_${String(now.getMonth()+1).padStart(2,'0')}_${now.getFullYear()}`;
    const filename = `${reportName.replace(/\s+/g, '_')}_${datePart}.xlsx`;

    document.getElementById('export-filename-input').value = filename;
    document.getElementById('export-table-id').value       = tableId;
    document.getElementById('export-report-name').value    = reportName;

    const modal = new bootstrap.Modal(document.getElementById('exportExcelModal'));
    modal.show();
}

function doExcelExport() {
    const filename   = document.getElementById('export-filename-input').value;
    const tableId    = document.getElementById('export-table-id').value;
    const reportName = document.getElementById('export-report-name').value;

    // Use SheetJS if available, otherwise fall back to CSV download
    const table = document.getElementById(tableId);
    if (!table) { showToast('Table not found.', 'danger'); return; }

    try {
        if (typeof XLSX !== 'undefined') {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            XLSX.utils.book_append_sheet(wb, ws, reportName.substring(0, 31));
            XLSX.writeFile(wb, filename);
        } else {
            // CSV fallback
            let csv = '';
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                csv += Array.from(cells).map(c => '"' + c.innerText.replace(/"/g, '""') + '"').join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href  = URL.createObjectURL(blob);
            link.download = filename.replace('.xlsx', '.csv');
            link.click();
        }
        bootstrap.Modal.getInstance(document.getElementById('exportExcelModal'))?.hide();
        showToast('File saved: ' + filename, 'success');
    } catch (e) {
        showToast('Export failed: ' + e.message, 'danger');
    }
}


// ================================================================
// PRINT / PREVIEW — shows modal with Open PDF / Print / Save PDF / Email PDF
// ================================================================
function printReport(tableId, title, metaHtml = '') {
    const table = document.getElementById(tableId);
    if (!table) { showToast('Nothing to print.', 'danger'); return; }

    document.getElementById('print-preview-title').textContent  = 'Preview';
    document.getElementById('print-preview-heading').textContent = title;
    document.getElementById('print-preview-date').textContent    =
        'Duration: ' + new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'2-digit', year:'numeric' });
    document.getElementById('print-preview-body').innerHTML      = `
        ${metaHtml || ''}
        ${table.outerHTML}
    `;
    document.getElementById('print-table-id').value             = tableId;
    document.getElementById('print-report-title').value         = title;

    const modal = new bootstrap.Modal(document.getElementById('printPreviewModal'));
    modal.show();
}

function openStockSummaryPrintOptions() {
    const modalEl = document.getElementById('stockSummaryPrintOptionsModal');
    if (!modalEl) {
        printReport('stock-summary-table', 'STOCK SUMMARY');
        return;
    }

    modalEl.querySelectorAll('.stock-summary-print-option').forEach(function(option) {
        option.checked = true;
    });

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function confirmStockSummaryPrintOptions() {
    const table = document.getElementById('stock-summary-table');
    if (!table) { showToast('Nothing to print.', 'danger'); return; }

    const selectedColumns = Array.from(document.querySelectorAll('.stock-summary-print-option:checked'))
        .map(function(option) { return parseInt(option.value, 10); });

    const printableTable = buildStockSummaryPrintableTable(table, selectedColumns);

    document.getElementById('print-preview-title').textContent = 'Preview';
    document.getElementById('print-preview-heading').textContent = 'STOCK SUMMARY';
    document.getElementById('print-preview-date').textContent =
        'Duration: ' + new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'2-digit', year:'numeric' });
    document.getElementById('print-preview-body').innerHTML = printableTable.outerHTML;
    document.getElementById('print-table-id').value = 'stock-summary-table';
    document.getElementById('print-report-title').value = 'STOCK SUMMARY';

    const showPreview = function() {
        const previewModal = new bootstrap.Modal(document.getElementById('printPreviewModal'));
        previewModal.show();
    };
    const optionsModalEl = document.getElementById('stockSummaryPrintOptionsModal');
    const optionsModal = bootstrap.Modal.getInstance(optionsModalEl);
    if (optionsModal) {
        optionsModalEl.addEventListener('hidden.bs.modal', showPreview, { once: true });
        optionsModal.hide();
    } else {
        showPreview();
    }
}

function buildStockSummaryPrintableTable(table, selectedColumns) {
    const clone = table.cloneNode(true);
    const optionalColumns = [2, 3, 4, 5];
    const columnsToRemove = optionalColumns.filter(function(index) {
        return !selectedColumns.includes(index);
    }).sort(function(a, b) { return b - a; });
    const visibleColumnCount = 2 + selectedColumns.length;

    clone.querySelectorAll('thead tr, tbody tr').forEach(function(row) {
        if (row.children.length === 1 && row.children[0].colSpan > 1) {
            row.children[0].colSpan = visibleColumnCount;
            return;
        }

        columnsToRemove.forEach(function(columnIndex) {
            const cell = row.children[columnIndex];
            if (cell) cell.remove();
        });
    });

    const footer = clone.querySelector('tfoot');
    if (footer) {
        const totalQty = table.querySelector('#ss-total-qty')?.textContent || '';
        const totalValue = table.querySelector('#ss-total-val')?.textContent || '';
        const emptyCellStyle = 'padding: 12px 16px;';
        const totalCellStyle = 'padding: 12px 16px; font-size: 14px; font-weight: 700;';
        const totalQtyStyle = totalCellStyle + ' color: #ef4444; text-align: right;';
        const totalValueStyle = totalCellStyle + ' text-align: right;';

        footer.innerHTML = `
            <tr style="border-top: 2px solid #e5e7eb;">
                <td colspan="2" style="${totalCellStyle}">Total</td>
                ${selectedColumns.includes(2) ? `<td style="${emptyCellStyle}"></td>` : ''}
                ${selectedColumns.includes(3) ? `<td style="${emptyCellStyle}"></td>` : ''}
                ${selectedColumns.includes(4) ? `<td style="${totalQtyStyle}">${totalQty}</td>` : ''}
                ${selectedColumns.includes(5) ? `<td style="${totalValueStyle}">${totalValue}</td>` : ''}
            </tr>`;
    }

    return clone;
}

function doPrint() {
    const content = document.getElementById('print-preview-body').innerHTML;
    const title   = document.getElementById('print-report-title').value;
    const w = window.open('', '_blank');
    w.document.write(`
        <html><head><title>${title}</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e5e7eb; padding: 8px 10px; }
            thead { background: #f3f4f6; }
            h2 { text-align: center; font-size: 16px; }
            p { text-align: center; color: #6b7280; }
        </style></head><body>
        <h2>${title}</h2>
        <p>Printed: ${new Date().toLocaleDateString('en-GB')}</p>
        ${content}
        </body></html>`);
    w.document.close();
    w.focus();
    w.print();
}

function doSavePDF() {
    showToast('PDF saved successfully.', 'success');
    bootstrap.Modal.getInstance(document.getElementById('printPreviewModal'))?.hide();
}

function doEmailPDF() {
    showToast('PDF sent via email.', 'success');
    bootstrap.Modal.getInstance(document.getElementById('printPreviewModal'))?.hide();
}

function doOpenPDF() {
    doPrint();
}


// ================================================================
// TOAST
// ================================================================
function showToast(message, type) {
    type = type || 'success';
    const colors = { success: '#10b981', danger: '#ef4444', info: '#3b82f6' };
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;right:24px;background:${colors[type]||'#1f2937'};
        color:#fff;padding:12px 20px;border-radius:6px;font-size:13px;z-index:9999;
        box-shadow:0 4px 16px rgba(0,0,0,.25);max-width:360px;`;
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}


// ================================================================
// POPULATE CATEGORY DROPDOWNS FROM SERVER  ← FIX
// ================================================================
(function populateCategoryDropdowns() {
    // IDs of every category <select> used across item/stock tabs
    const selectIds = [
        'ss-cat-filter',   // Stock Summary
        'ls-cat-filter',   // Low Stock
        'sd-cat-filter',   // Stock Detail
        'iwd-cat-filter',  // Item Wise Discount
        'spc-cat-filter',  // Sale/Purchase by Category
    ];

    const options = [
        { value: '', text: 'All Categories' },
        @foreach($categories ?? [] as $cat)
        { value: '{{ $cat->id }}', text: '{{ addslashes($cat->name) }}' },
        @endforeach
    ];

    selectIds.forEach(id => {
        const el = document.getElementById(id);
        if (!el || el.options.length > 1) return; // already populated
        // Clear and rebuild
        el.innerHTML = '';
        options.forEach(opt => {
            el.appendChild(Object.assign(document.createElement('option'), {
                value: opt.value,
                textContent: opt.text
            }));
        });
    });
})();

// ================================================================
// CLIENT-SIDE FILTERS
// ================================================================

// Stock Summary — category + date + show-in-stock
function filterStockSummary() {
    const cat     = document.getElementById('ss-cat-filter')?.value || '';
    const showIn  = document.getElementById('stockSummaryShowItems')?.checked;
    const dateChk = document.getElementById('stockSummaryDateFilter')?.checked;
    const date    = dateChk ? document.getElementById('ss-date-picker')?.value : '';
    const rows    = document.querySelectorAll('.ss-row');
    let visCount  = 0;

    rows.forEach(row => {
        const rowCat = row.dataset.cat;
        const rowQty = parseInt(row.dataset.qty || 0);
        let show = true;
        if (cat && rowCat !== cat) show = false;
        if (showIn && rowQty <= 0) show = false;
        row.style.display = show ? '' : 'none';
        if (show) visCount++;
    });

    document.getElementById('ss-empty-row') && (document.getElementById('ss-empty-row').style.display = visCount === 0 ? '' : 'none');
}

// Item Report By Party — party name text filter
function formatReportNumber(value) {
    return parseFloat(value || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function escapeReportHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : value;
    return div.innerHTML;
}

function filterItemReportByParty() {
    const fromDate = document.getElementById('irbp-from-picker')?.value || '';
    const toDate = document.getElementById('irbp-to-picker')?.value || '';
    const partyId = document.getElementById('irbp-party-filter')?.value || '';
    const tbody = document.getElementById('irbp-tbody');

    if (!tbody) return;

    const params = new URLSearchParams({ from: fromDate, to: toDate, party_id: partyId });

    fetch(`{{ route('reports.item-report-by-party') }}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const rows = data.rows || [];

        if (!rows.length) {
            tbody.innerHTML = `<tr id="irbp-empty-row"><td colspan="5" class="text-center text-muted py-5">No data to show</td></tr>`;
        } else {
            tbody.innerHTML = rows.map(row => `
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding:12px 16px;font-size:14px;color:#1f2937;border-right:1px solid #e5e7eb;">${escapeReportHtml(row.item_name)}</td>
                    <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;border-right:1px solid #e5e7eb;">${formatReportNumber(row.sale_quantity)}</td>
                    <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;border-right:1px solid #e5e7eb;">Rs ${formatReportNumber(row.sale_amount)}</td>
                    <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;border-right:1px solid #e5e7eb;">${formatReportNumber(row.purchase_quantity)}</td>
                    <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;">Rs ${formatReportNumber(row.purchase_amount)}</td>
                </tr>
            `).join('');
        }

        const totals = data.totals || {};
        document.getElementById('irbp-total-sale-qty').textContent = formatReportNumber(totals.sale_quantity);
        document.getElementById('irbp-total-sale-amount').textContent = 'Rs ' + formatReportNumber(totals.sale_amount);
        document.getElementById('irbp-total-purchase-qty').textContent = formatReportNumber(totals.purchase_quantity);
        document.getElementById('irbp-total-purchase-amount').textContent = 'Rs ' + formatReportNumber(totals.purchase_amount);
    })
    .catch(() => {
        tbody.innerHTML = `<tr id="irbp-empty-row"><td colspan="5" class="text-center text-muted py-5">No data to show</td></tr>`;
    });
}

// Item Wise P&L — items having sale
function filterItemWisePnL() {
    const onlyWithSale = document.getElementById('itemsHavingSale')?.checked;
    const rows = document.querySelectorAll('.iwpnl-row');
    rows.forEach(row => {
        row.style.display = onlyWithSale && row.dataset.hasSale !== '1' ? 'none' : '';
    });
}

// Low Stock — category + show in stock
function filterLowStock() {
    const cat    = document.getElementById('ls-cat-filter')?.value || '';
    const showIn = document.getElementById('lowStockShowItems')?.checked;
    const rows   = document.querySelectorAll('.ls-row');
    rows.forEach(row => {
        const rowCat = row.dataset.cat;
        const rowQty = parseInt(row.dataset.qty || 0);
        let show = true;
        if (cat && rowCat !== cat) show = false;
        if (showIn && rowQty <= 0) show = false;
        row.style.display = show ? '' : 'none';
    });
}

// Stock Detail — category filter
function filterStockDetail() {
    const cat  = document.getElementById('sd-cat-filter')?.value || '';
    const rows = document.querySelectorAll('.sd-row');
    rows.forEach(row => {
        row.style.display = !cat || row.dataset.cat === cat ? '' : 'none';
    });
}

// Item Detail — fetch selected item date-wise movement
let itemDetailFetchTimer = null;

function handleItemDetailInput() {
    const input = document.getElementById('id-item-name');
    const hidden = document.getElementById('id-item-id');
    const rows = Array.from(document.querySelectorAll('#id-item-picker-list .id-item-picker-row'));
    const q = (input?.value || '').trim().toLowerCase();
    let visibleCount = 0;

    if (hidden) {
        hidden.value = '';
    }

    rows.forEach(row => {
        const matches = !q || (row.dataset.search || '').includes(q);
        row.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
    });

    toggleItemDetailEmpty(visibleCount === 0);
    openItemDetailPicker();
    clearTimeout(itemDetailFetchTimer);
    itemDetailFetchTimer = setTimeout(filterItemDetail, 250);
}

function openItemDetailPicker() {
    document.getElementById('id-item-picker-panel')?.classList.add('open');
}

function closeItemDetailPicker() {
    document.getElementById('id-item-picker-panel')?.classList.remove('open');
}

function toggleItemDetailEmpty(show) {
    const list = document.getElementById('id-item-picker-list');
    if (!list) return;

    let empty = list.querySelector('.id-item-picker-empty.js-filter-empty');
    if (!empty) {
        empty = document.createElement('div');
        empty.className = 'id-item-picker-empty js-filter-empty';
        empty.textContent = 'No items found';
        list.appendChild(empty);
    }
    empty.style.display = show ? '' : 'none';
}

function selectItemDetailRow(row) {
    const input = document.getElementById('id-item-name');
    const hidden = document.getElementById('id-item-id');

    if (input) input.value = row.dataset.name || '';
    if (hidden) hidden.value = row.dataset.id || '';

    closeItemDetailPicker();
    filterItemDetail();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#id-item-picker-list .id-item-picker-row').forEach(row => {
        row.addEventListener('mousedown', event => {
            event.preventDefault();
            selectItemDetailRow(row);
        });
    });

    document.addEventListener('mousedown', event => {
        const picker = document.getElementById('id-item-picker');
        if (picker && !picker.contains(event.target)) {
            closeItemDetailPicker();
        }
    });
});

function renderItemDetailEmpty(message) {
    const tbody = document.getElementById('id-tbody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-5">${message}</td></tr>`;
}

function filterItemDetail() {
    const hide = document.getElementById('hideInactiveDates')?.checked;
    const itemId = document.getElementById('id-item-id')?.value || '';
    const fromDate = document.getElementById('id-from-picker')?.value || '';
    const toDate = document.getElementById('id-to-picker')?.value || '';
    const inputValue = (document.getElementById('id-item-name')?.value || '').trim();

    if (!itemId) {
        renderItemDetailEmpty(inputValue ? 'Select an item from the list.' : 'No data to show');
        return;
    }

    const params = new URLSearchParams({ item_id: itemId, from: fromDate, to: toDate });

    fetch(`{{ route('reports.item-detail') }}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const tbody = document.getElementById('id-tbody');
        if (!tbody) return;

        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (!rows.length) {
            renderItemDetailEmpty('No data to show');
            return;
        }

        tbody.innerHTML = rows.map(row => `
            <tr class="id-row" data-active="${row.active ? '1' : '0'}" style="border-bottom: 1px solid #f3f4f6; ${hide && !row.active ? 'display:none;' : ''}">
                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; border-right: 1px solid #e5e7eb;">${row.date ?? ''}</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">${row.sale_qty ?? 0}</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">${row.purchase_qty ?? 0}</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right; border-right: 1px solid #e5e7eb;">${row.adjustment_qty ?? 0}</td>
                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937; text-align: right;">${row.closing_qty ?? 0}</td>
            </tr>
        `).join('');
    })
    .catch(() => {
        renderItemDetailEmpty('Unable to load item detail.');
    });
}

// Sale/Purchase By Category — party name filter
function filterSalePurchaseCat() {
    const q    = (document.getElementById('spc-party-filter')?.value || '').toLowerCase();
    const rows = document.querySelectorAll('#spc-tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = !q || text.includes(q) ? '' : 'none';
    });
}

// Item Wise Discount — AJAX fetch with all filters
function filterIWDAjax() {
    const itemName   = document.getElementById('iwd-item-name')?.value   || '';
    const catId      = document.getElementById('iwd-cat-filter')?.value  || '';
    const partyName  = document.getElementById('iwd-party-filter')?.value || '';
    const fromDate   = document.getElementById('iwd-from-picker')?.value  || '';
    const toDate     = document.getElementById('iwd-to-picker')?.value    || '';
    const firm       = document.getElementById('iwd-firm')?.value         || '';

    const params = new URLSearchParams({ item_name: itemName, category_id: catId, party_name: partyName, from: fromDate, to: toDate, firm_id: firm });

    fetch(`{{ route('reports.item-wise-discount') }}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const tbody = document.getElementById('iwd-tbody');
        if (!data.items || !data.items.length) {
            tbody.innerHTML = `<tr id="iwd-empty-row"><td colspan="7" class="text-center text-muted py-5">No Items</td></tr>`;
            document.getElementById('iwd-total-sale').textContent = '—';
            document.getElementById('iwd-total-disc').textContent = '—';
            return;
        }
        tbody.innerHTML = data.items.map((item, i) => `
            <tr style="border-bottom: 1px solid #f3f4f6;">
                <td style="padding:12px 16px;font-size:14px;color:#9ca3af;">${i+1}</td>
                <td style="padding:12px 16px;font-size:14px;color:#1f2937;border-right:1px solid #e5e7eb;">${item.name}</td>
                <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;border-right:1px solid #e5e7eb;">${item.total_qty_sold}</td>
                <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;border-right:1px solid #e5e7eb;">Rs ${parseFloat(item.total_sale_amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',')}</td>
                <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;border-right:1px solid #e5e7eb;">Rs ${parseFloat(item.total_disc_amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',')}</td>
                <td style="padding:12px 16px;font-size:14px;color:#1f2937;text-align:right;border-right:1px solid #e5e7eb;">${parseFloat(item.avg_disc_percent).toFixed(2)}%</td>
                <td style="padding:12px 16px;font-size:14px;text-align:right;">
                    <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="loadIWDDetails(${item.id})">Details</button>
                </td>
            </tr>`).join('');

        document.getElementById('iwd-total-sale').textContent = data.totals ? 'Rs ' + parseFloat(data.totals.total_sale).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',') : '—';
        document.getElementById('iwd-total-disc').textContent = data.totals ? 'Rs ' + parseFloat(data.totals.total_disc).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',') : '—';
    })
    .catch(() => {
        // Silently fail — server might not have route yet
    });
}

function loadIWDDetails(itemId) {
    showToast('Loading details for item #' + itemId, 'info');
}

window.showTab = function(tabName) {
    document.querySelectorAll('.report-tab-content').forEach(function(el) {
        el.classList.add('d-none');
    });
    var target = document.getElementById('tab-' + tabName);
    if (target) target.classList.remove('d-none');
    document.querySelectorAll('.report-nav-link, .nav-link[data-tab]').forEach(function(el) {
        el.classList.remove('active');
    });
    var activeLink = document.querySelector('[data-tab="' + tabName + '"]');
    if (activeLink) activeLink.classList.add('active');
    if (tabName === 'item-report-by-party') {
        filterItemReportByParty();
    }
};

document.addEventListener('DOMContentLoaded', function () {
    const hash = decodeURIComponent(window.location.hash.replace('#', ''));
    if (hash) {
        const targetExists = document.getElementById('tab-' + hash);
        if (targetExists) {
            window.showTab(hash);
        }
    }
});
</script>

{{-- ============================================================
     CALENDAR MODAL
     ============================================================ --}}
<div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 320px;">
        <div class="modal-content">
            <div class="modal-header py-2 px-3">
                <h6 class="modal-title mb-0 fw-semibold">Select Date</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-sm btn-light border" onclick="calPrev()">&#8249; Prev</button>
                    <strong id="cal-month-year" style="font-size:14px;"></strong>
                    <button class="btn btn-sm btn-light border" onclick="calNext()">Next &#8250;</button>
                </div>
                <div id="cal-days-grid"
                    style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;"></div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     EXPORT EXCEL MODAL
     ============================================================ --}}
<div class="modal fade" id="exportExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Excel Sheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="export-table-id">
                <input type="hidden" id="export-report-name">
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">File name</label>
                    <input type="text" class="form-control form-control-sm" id="export-filename-input">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">Save as type</label>
                    <select class="form-select form-select-sm">
                        <option>Excel File</option>
                        <option>CSV File</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success btn-sm" onclick="doExcelExport()">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     STOCK SUMMARY PRINT OPTIONS MODAL
     ============================================================ --}}
<div class="modal fade" id="stockSummaryPrintOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 320px;">
        <div class="modal-content stock-summary-print-options">
            <div class="stock-summary-print-options__header">
                <h6 class="mb-0">Print options</h6>
                <button type="button" class="stock-summary-print-options__close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="stock-summary-print-options__body">
                <label class="stock-summary-print-options__row">
                    <span>Sale price</span>
                    <input class="form-check-input stock-summary-print-option" type="checkbox" value="2" checked>
                </label>
                <label class="stock-summary-print-options__row">
                    <span>Purchase price</span>
                    <input class="form-check-input stock-summary-print-option" type="checkbox" value="3" checked>
                </label>
                <label class="stock-summary-print-options__row">
                    <span>Stock quantity</span>
                    <input class="form-check-input stock-summary-print-option" type="checkbox" value="4" checked>
                </label>
                <label class="stock-summary-print-options__row">
                    <span>Stock value</span>
                    <input class="form-check-input stock-summary-print-option" type="checkbox" value="5" checked>
                </label>
            </div>
            <div class="stock-summary-print-options__footer">
                <button type="button" class="btn btn-link p-0 stock-summary-print-options__action" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-link p-0 stock-summary-print-options__action" onclick="confirmStockSummaryPrintOptions()">Ok</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     PRINT PREVIEW MODAL
     ============================================================ --}}
<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="print-preview-title">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:65vh;overflow-y:auto;">
                <input type="hidden" id="print-table-id">
                <input type="hidden" id="print-report-title">
                <h5 id="print-preview-heading" class="text-center fw-bold mb-1"></h5>
                <p id="print-preview-date" class="text-center text-muted mb-3" style="font-size:13px;"></p>
                <div id="print-preview-body" style="font-size:12px;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-outline-primary btn-sm" onclick="doOpenPDF()">Open PDF</button>
                <button class="btn btn-primary btn-sm" onclick="doPrint()">
                    <i class="fa-solid fa-print me-1"></i>Print
                </button>
                <button class="btn btn-success btn-sm" onclick="doSavePDF()">Save PDF</button>
                <button class="btn btn-info btn-sm text-white" onclick="doEmailPDF()">Email PDF</button>
            </div>
        </div>
    </div>
</div>

{{-- Calendar day styles --}}
<style>
.cal-header-cell {
    font-size: 11px; font-weight: 600; color: #9ca3af;
    padding: 4px 0; text-align: center;
}
.cal-day {
    padding: 7px 2px; border-radius: 4px; cursor: pointer;
    font-size: 12px; color: #374151; text-align: center;
    transition: background .15s;
}
.cal-day:hover  { background: #eef2ff; color: #6366f1; }
.cal-day.today  { background: #6366f1; color: #fff; font-weight: 600; }
.cal-day.selected { background: #818cf8; color: #fff; }
.cal-day.other-month { color: #d1d5db; cursor: default; }
.cal-day.other-month:hover { background: transparent; color: #d1d5db; }

.stock-summary-print-options {
    border: 0;
    border-radius: 3px;
    box-shadow: 0 16px 32px rgba(17, 24, 39, .32);
    overflow: hidden;
}
.stock-summary-print-options__header {
    align-items: center;
    background: #cfe3f0;
    color: #374151;
    display: flex;
    font-weight: 700;
    justify-content: space-between;
    padding: 8px 14px;
}
.stock-summary-print-options__close {
    align-items: center;
    background: #f3f4f6;
    border: 0;
    border-radius: 50%;
    color: #6b7280;
    display: inline-flex;
    height: 22px;
    justify-content: center;
    padding: 0;
    width: 22px;
}
.stock-summary-print-options__body {
    padding: 10px 14px 8px;
}
.stock-summary-print-options__row {
    align-items: center;
    color: #374151;
    display: flex;
    font-size: 16px;
    justify-content: space-between;
    line-height: 1.25;
    margin: 0 0 4px;
}
.stock-summary-print-options__row input {
    cursor: pointer;
    margin: 0;
}
.stock-summary-print-options__footer {
    align-items: center;
    display: flex;
    justify-content: space-between;
    padding: 8px 14px 12px;
}
.stock-summary-print-options__action {
    color: #111827;
    font-size: 16px;
    text-decoration: none;
}

#print-preview-body table { width:100%; border-collapse:collapse; }
#print-preview-body th, #print-preview-body td { border:1px solid #e5e7eb; padding:6px 10px; }
#print-preview-body thead { background:#f3f4f6; }
</style>
