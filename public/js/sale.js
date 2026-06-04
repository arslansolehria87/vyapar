/**
 * ═══════════════════════════════════════════
 *  VYAPAR — Sale Page Logic
 * ═══════════════════════════════════════════
 */

$(document).ready(function () {
  const $input = $('#searchTransactionsInput');
  if ($input.length) {
    const rawSearchValue = String($input.val() || '').trim();
    if (rawSearchValue.includes('@')) {
      $input.val('');
    }
  }
  document.querySelectorAll('input[type="search"], .search-input, [placeholder*="Search"], [placeholder*="search"]').forEach((input) => {
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('autocapitalize', 'off');
    input.setAttribute('autocorrect', 'off');
    input.setAttribute('spellcheck', 'false');
  });
  const $dropdowns = $('.sale-dropdown');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const salePreviewModalEl = document.getElementById('salePreviewModal');
  const salePreviewModal = salePreviewModalEl ? bootstrap.Modal.getOrCreateInstance(salePreviewModalEl) : null;
  const salePreviewFrame = document.getElementById('salePreviewFrame');
  const salePreviewModalTitle = document.getElementById('salePreviewModalTitle');
  const salePreviewOpenPdfBtn = document.getElementById('salePreviewOpenPdf');
  const salePreviewPrintBtn = document.getElementById('salePreviewPrint');
  const saleHistoryModalEl = document.getElementById('saleHistoryModal');
  const saleHistoryModal = saleHistoryModalEl ? bootstrap.Modal.getOrCreateInstance(saleHistoryModalEl) : null;
  const saleHistoryModalTitle = document.getElementById('saleHistoryModalTitle');
  const saleHistoryModalBody = document.getElementById('saleHistoryModalBody');
  const salePaymentHistoryModalEl = document.getElementById('salePaymentHistoryModal');
  const salePaymentHistoryModal = salePaymentHistoryModalEl ? bootstrap.Modal.getOrCreateInstance(salePaymentHistoryModalEl) : null;
  const salePaymentHistoryModalTitle = document.getElementById('salePaymentHistoryModalTitle');
  const salePaymentHistoryModalBody = document.getElementById('salePaymentHistoryModalBody');
  const salePrintOptionsModalEl = document.getElementById('salePrintOptionsModal');
  const salePrintOptionsModal = salePrintOptionsModalEl ? bootstrap.Modal.getOrCreateInstance(salePrintOptionsModalEl) : null;
  const salePrintOptionsApplyBtn = document.getElementById('salePrintOptionsApply');
  const printTableBtn = document.getElementById('printTable');
  const salePasscodeModalEl = document.getElementById('salePasscodeModal');
  const salePasscodeModal = salePasscodeModalEl ? bootstrap.Modal.getOrCreateInstance(salePasscodeModalEl) : null;
  const salePasscodeInput = document.getElementById('salePasscodeInput');
  const salePasscodeConfirmBtn = document.getElementById('salePasscodeConfirm');
  const salePasscodeError = document.getElementById('salePasscodeError');
  const passcodeEnabled = String(document.body?.dataset?.transactionPasscodeEnabled || '0') === '1';
  const passcodeVerifyUrl = document.body?.dataset?.transactionPasscodeVerifyUrl || '';
  const reportPreviewBaseUrl = printTableBtn?.dataset?.reportPreviewUrl || `${window.location.origin}/dashboard/sale-report-preview`;
  const reportPdfBaseUrl = printTableBtn?.dataset?.reportPdfUrl || `${window.location.origin}/dashboard/sale-report-pdf`;
  let pendingProtectedAction = null;

  // Filter variables
  const $periodSelect = $('#salesPeriodSelect');
  const $firmSelect = $('#salesFirmSelect');
  const $dateRangeDisplay = $('#salesDateRangeDisplay');
  const $customDateRange = $('#customDateRange');
  const $customFrom = $('#salesCustomFrom');
  const $customTo = $('#salesCustomTo');

  let periodFilter = $periodSelect.val() || 'all';
  let firmFilter = $firmSelect.val() || '';
  let customFrom = null;
  let customTo = null;

  // Global search term and column-specific filters
  let globalSearch = '';
  const columnFilters = {};

  function getInvoiceThemeState(saleId) {
    if (!saleId) return null;

    try {
      const raw = window.localStorage.getItem(`saleInvoiceTheme:${saleId}`) || window.localStorage.getItem('saleInvoiceTheme:draft');
      return raw ? JSON.parse(raw) : null;
    } catch (error) {
      return null;
    }
  }

  function buildUrlWithTheme(baseUrl, saleId, extraParams = {}) {
    if (!baseUrl) return '';

    const url = new URL(baseUrl, window.location.origin);
    const savedTheme = getInvoiceThemeState(saleId);

    if (savedTheme) {
      if (savedTheme.mode) url.searchParams.set('mode', savedTheme.mode);
      if (savedTheme.mode === 'thermal' && savedTheme.thermalThemeId) {
        url.searchParams.set('theme_id', savedTheme.thermalThemeId);
      } else if (savedTheme.regularThemeId) {
        url.searchParams.set('theme_id', savedTheme.regularThemeId);
      }
      if (savedTheme.accent) url.searchParams.set('accent', savedTheme.accent);
      if (savedTheme.accent2) url.searchParams.set('accent2', savedTheme.accent2);
    }

    Object.entries(extraParams).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        url.searchParams.set(key, value);
      }
    });

    return url.toString();
  }

  function openPreviewModal(url, title, options = {}) {
    if (!salePreviewModal || !salePreviewFrame || !url) {
      if (url) window.open(url, '_blank');
      return;
    }

    salePreviewModalTitle.textContent = title || 'Preview';
    salePreviewFrame.src = url;
    salePreviewFrame.dataset.pdfUrl = options.pdfUrl || '';
    salePreviewFrame.dataset.printUrl = options.printUrl || '';
    salePreviewFrame.dataset.previewUrl = options.previewUrl || url || '';
    salePreviewFrame.dataset.partyEmail = options.partyEmail || '';
    salePreviewFrame.dataset.partyName = options.partyName || '';
    salePreviewFrame.dataset.saleNumber = options.saleNumber || '';
    salePreviewFrame.dataset.emailUrl = options.emailUrl || '';
    salePreviewFrame.dataset.documentLabel = options.documentLabel || 'Sale Invoice';
    salePreviewModal.show();
  }

  function clearSalePasscodeError() {
    if (salePasscodeError) {
      salePasscodeError.textContent = '';
      salePasscodeError.classList.add('d-none');
    }
  }

  function setSalePasscodeError(message) {
    if (salePasscodeError) {
      salePasscodeError.textContent = message || 'Invalid passcode.';
      salePasscodeError.classList.remove('d-none');
    }
  }

  function runProtectedAction(actionFn) {
    if (typeof actionFn === 'function') {
      actionFn();
    }
  }

  function requestSalePasscode(actionFn) {
    if (!passcodeEnabled || !passcodeVerifyUrl) {
      runProtectedAction(actionFn);
      return;
    }

    pendingProtectedAction = actionFn;
    clearSalePasscodeError();
    if (salePasscodeInput) {
      salePasscodeInput.value = '';
    }
    salePasscodeModal?.show();
    setTimeout(() => salePasscodeInput?.focus(), 150);
  }

  function verifySalePasscodeAndContinue() {
    const passcode = String(salePasscodeInput?.value || '').trim();
    clearSalePasscodeError();

    if (!/^\d{4}$/.test(passcode)) {
      setSalePasscodeError('Enter a valid 4-digit passcode.');
      salePasscodeInput?.focus();
      return;
    }

    const formPayload = new URLSearchParams();
    formPayload.set('_token', csrfToken);
    formPayload.set('passcode', passcode);

    fetchJson(passcodeVerifyUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      credentials: 'same-origin',
      body: formPayload.toString(),
    })
      .then(() => {
        const actionFn = pendingProtectedAction;
        pendingProtectedAction = null;
        salePasscodeModal?.hide();
        runProtectedAction(actionFn);
      })
      .catch((error) => {
        setSalePasscodeError(error.message || 'Invalid passcode.');
        salePasscodeInput?.focus();
      });
  }

  salePasscodeConfirmBtn?.addEventListener('click', function () {
    verifySalePasscodeAndContinue();
  });

  salePasscodeInput?.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      verifySalePasscodeAndContinue();
    }
  });

  salePasscodeModalEl?.addEventListener('hidden.bs.modal', function () {
    pendingProtectedAction = null;
    clearSalePasscodeError();
    if (salePasscodeInput) {
      salePasscodeInput.value = '';
    }
  });

  salePasscodeModalEl?.addEventListener('shown.bs.modal', function () {
    setTimeout(() => {
      document.querySelectorAll('input[type="search"], .search-input, [placeholder*="Search"], [placeholder*="search"]').forEach((input) => {
        if (input instanceof HTMLInputElement && input !== document.activeElement && input.value && input.value.includes('@')) {
          input.value = '';
        }
      });
    }, 120);
  });

  window.guardSaleEditAction = function(editUrl) {
    requestSalePasscode(() => {
      window.location.href = editUrl;
    });
  };

  salePreviewOpenPdfBtn?.addEventListener('click', function () {
    const pdfUrl = salePreviewFrame?.dataset?.pdfUrl || salePreviewFrame?.src;
    if (pdfUrl) {
      window.open(pdfUrl, '_blank');
    }
  });

  salePreviewPrintBtn?.addEventListener('click', function () {
    const printUrl = salePreviewFrame?.dataset?.printUrl || salePreviewFrame?.src;
    if (printUrl) {
      window.open(printUrl, '_blank');
    }
  });

  salePreviewModalEl?.addEventListener('hidden.bs.modal', function () {
    if (salePreviewFrame) {
      salePreviewFrame.src = 'about:blank';
      delete salePreviewFrame.dataset.pdfUrl;
      delete salePreviewFrame.dataset.printUrl;
      delete salePreviewFrame.dataset.previewUrl;
      delete salePreviewFrame.dataset.partyEmail;
      delete salePreviewFrame.dataset.partyName;
      delete salePreviewFrame.dataset.saleNumber;
      delete salePreviewFrame.dataset.emailUrl;
      delete salePreviewFrame.dataset.documentLabel;
    }
  });

  function buildPrintOptionParams() {
    const params = {};
    document.querySelectorAll('.sale-print-opt').forEach((input) => {
      params[input.value] = input.checked ? 1 : 0;
    });
    return params;
  }

  function showPrintOptions(previewUrl, pdfUrl, saleLabel) {
    if (!salePrintOptionsModal || !salePrintOptionsApplyBtn || !previewUrl) {
      if (previewUrl) window.open(previewUrl, '_blank');
      return;
    }
    salePrintOptionsApplyBtn.dataset.previewUrl = previewUrl;
    salePrintOptionsApplyBtn.dataset.printUrl = pdfUrl || '';
    salePrintOptionsApplyBtn.dataset.saleLabel = saleLabel || 'Invoice';
    salePrintOptionsModal.show();
  }

  salePrintOptionsApplyBtn?.addEventListener('click', function () {
    const previewUrl = this.dataset.previewUrl;
    if (!previewUrl) return;
    const preview = new URL(previewUrl, window.location.origin);
    const options = buildPrintOptionParams();
    Object.entries(options).forEach(([key, value]) => preview.searchParams.set(key, value));
    let pdf = '';
    if (this.dataset.printUrl) {
      const pdfUrl = new URL(this.dataset.printUrl, window.location.origin);
      Object.entries(options).forEach(([key, value]) => pdfUrl.searchParams.set(key, value));
      pdf = pdfUrl.toString();
    }
    salePrintOptionsModal.hide();
    openPreviewModal(preview.toString(), this.dataset.saleLabel || 'Sale Report', {
      pdfUrl: pdf,
      printUrl: preview.toString() + (preview.search ? '&print=1' : '?print=1'),
    });
  });

  function renderHistoryTable(title, headers, rows, summaryHtml = '') {
    if (!saleHistoryModal || !saleHistoryModalBody) return;

    saleHistoryModalTitle.textContent = title;

    if (!rows.length) {
      saleHistoryModalBody.innerHTML = `<div class="text-muted">No records found.</div>`;
      saleHistoryModal.show();
      return;
    }

    const thead = headers.map(header => `<th>${header}</th>`).join('');
    const tbody = rows.map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('');

    saleHistoryModalBody.innerHTML = `
      ${summaryHtml}
      <div class="table-responsive">
        <table class="table table-bordered table-sm history-table mb-0">
          <thead class="table-light"><tr>${thead}</tr></thead>
          <tbody>${tbody}</tbody>
        </table>
      </div>
    `;

    saleHistoryModal.show();
  }

  function renderPaymentHistoryModal(data) {
    if (!salePaymentHistoryModal || !salePaymentHistoryModalBody) return;

    salePaymentHistoryModalTitle.textContent = `Payment History - ${data.bill_number || ''}`.trim();

    const payments = Array.isArray(data.payments) ? data.payments : [];

    if (!payments.length) {
      salePaymentHistoryModalBody.innerHTML = `
        <div class="mb-3 small text-muted">
          <div><strong>Invoice:</strong> ${data.bill_number || '-'}</div>
          <div><strong>Received:</strong> Rs ${Number(data.received_amount || 0).toFixed(2)}</div>
          <div><strong>Balance:</strong> Rs ${Number(data.balance || 0).toFixed(2)}</div>
        </div>
        <div class="text-muted">No payment history found.</div>
      `;
      salePaymentHistoryModal.show();
      return;
    }

    const cards = payments.map((payment, index) => `
      <div class="border rounded-3 p-3 mb-2">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
          <div>
            <div class="fw-semibold">${payment.payment_type || '-'}</div>
            <div class="small text-muted">${payment.bank_name || '-'}</div>
          </div>
          <div class="text-end">
            <div class="fw-semibold text-success">Rs ${Number(payment.amount || 0).toFixed(2)}</div>
            <div class="small text-muted">${payment.date || '-'}</div>
            <div class="small text-muted">${payment.time || '-'}</div>
          </div>
        </div>
        <div class="small mt-2"><strong>Reference:</strong> ${payment.reference || '-'}</div>
      </div>
    `).join('');

    salePaymentHistoryModalBody.innerHTML = `
      <div class="mb-3 small text-muted">
        <div><strong>Invoice:</strong> ${data.bill_number || '-'}</div>
        <div><strong>Total:</strong> Rs ${Number(data.grand_total || 0).toFixed(2)}</div>
        <div><strong>Received:</strong> Rs ${Number(data.received_amount || 0).toFixed(2)}</div>
        <div><strong>Balance:</strong> Rs ${Number(data.balance || 0).toFixed(2)}</div>
      </div>
      <div>${cards}</div>
    `;

    salePaymentHistoryModal.show();
  }

  async function fetchJson(url, options = {}) {
    const optionHeaders = options.headers || {};
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
        ...optionHeaders,
      },
      ...options,
    });

    const rawText = await response.text();
    let data = null;
    try {
      data = rawText ? JSON.parse(rawText) : {};
    } catch (error) {
      data = null;
    }

    if (!response.ok) {
      throw new Error(data?.message || `Request failed with status ${response.status}.`);
    }

    return data || {};
  }

  function parseDateDMY(value) {
    const parts = (value || '').split('/');
    if (parts.length !== 3) return null;
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const year = parseInt(parts[2], 10);
    if (isNaN(day) || isNaN(month) || isNaN(year)) return null;
    return new Date(year, month, day);
  }

  function updateRangeDisplay(from, to) {
    if (!from || !to) return;
    const fmt = (d) => {
      const dd = String(d.getDate()).padStart(2, '0');
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    };
    $dateRangeDisplay.text(`${fmt(from)} To ${fmt(to)}`);
  }

  function getPeriodRange(period) {
    const now = new Date();
    let start = null;
    let end = null;

    if (period === 'this_month') {
      start = new Date(now.getFullYear(), now.getMonth(), 1);
      end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    } else if (period === 'last_month') {
      start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
      end = new Date(now.getFullYear(), now.getMonth(), 0);
    } else if (period === 'this_quarter') {
      const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3;
      start = new Date(now.getFullYear(), quarterStartMonth, 1);
      end = new Date(now.getFullYear(), quarterStartMonth + 3, 0);
    } else if (period === 'this_year') {
      start = new Date(now.getFullYear(), 0, 1);
      end = new Date(now.getFullYear(), 11, 31);
    }

    return { start, end };
  }

  function applyFilters() {
    const normalizedSearch = (globalSearch || '').toString().toLowerCase().trim();

    $('table.txn-table tbody tr').each(function () {
      const $row = $(this);
      if ($row.find('td[colspan]').length) {
        $row.show();
        return;
      }
      const rowText = $row.text().toLowerCase();

      let visible = true;

      if (normalizedSearch && rowText.indexOf(normalizedSearch) === -1) {
        visible = false;
      }

      if (visible) {
        for (const colIndex in columnFilters) {
          const filterVal = (columnFilters[colIndex] || '').toString().toLowerCase().trim();
          if (!filterVal) continue;

          const cellText = $row.find('td').eq(parseInt(colIndex, 10)).text().toLowerCase();
          if (cellText.indexOf(filterVal) === -1) {
            visible = false;
            break;
          }
        }
      }

      $row.toggle(visible);
    });

    const hasActiveFilter = Boolean(normalizedSearch)
      || Object.values(columnFilters).some(val => (val || '').toString().trim() !== '');

    if (hasActiveFilter) {
      $('.pagination').hide();
      $('.pagination-wrapper').hide();
    } else {
      $('.pagination').show();
      $('.pagination-wrapper').show();
    }
  }

  function filterTransactions(term) {
    globalSearch = (term || '').toString();
    applyFilters();
  }

  function closeAllDropdowns() {
    $('.sale-dropdown').removeClass('open');
  }

  function closeAllColumnFilters() {
    $('.column-filter-dropdown').removeClass('open');
  }

  function closeAllPopups() {
    closeAllDropdowns();
    closeAllColumnFilters();
  }

  // Initialize
  globalSearch = '';
  $input.val('');

  // Helper to toggle between the display span and the custom date inputs
  function setCustomMode(isCustom) {
    if (isCustom) {
      $dateRangeDisplay.hide();
      $customDateRange.show();
    } else {
      $dateRangeDisplay.show();
      $customDateRange.hide();
    }
  }

  // Initialize period filter display
  const initRange = getPeriodRange(periodFilter);

  if (periodFilter === 'custom') {
    // Default custom to today
    const today = new Date();
    const iso = (d) => d.toISOString().split('T')[0];
    $customFrom.val(iso(today));
    $customTo.val(iso(today));
    customFrom = $customFrom.val();
    customTo = $customTo.val();
    updateRangeDisplay(today, today);
    setCustomMode(true);
  } else if (initRange.start && initRange.end) {
    updateRangeDisplay(initRange.start, initRange.end);
    setCustomMode(false);
  } else {
    setCustomMode(false);
  }

  applyFilters();

  $input.on('input', function () {
    const val = $(this).val();
    filterTransactions(val);
  });

  function goWithFilters(nextPeriod, nextFirm, nextFrom, nextTo) {
    const url = new URL(window.location.href);
    url.searchParams.delete('page');
    if (nextPeriod && nextPeriod !== 'all') {
      url.searchParams.set('period', nextPeriod);
    } else {
      url.searchParams.delete('period');
    }
    if (nextFirm) {
      url.searchParams.set('firm', nextFirm);
    } else {
      url.searchParams.delete('firm');
    }
    if (nextPeriod === 'custom') {
      if (nextFrom) url.searchParams.set('from', nextFrom); else url.searchParams.delete('from');
      if (nextTo) url.searchParams.set('to', nextTo); else url.searchParams.delete('to');
    } else {
      url.searchParams.delete('from');
      url.searchParams.delete('to');
    }
    window.location.href = url.toString();
  }

  $periodSelect.on('change', function () {
    periodFilter = $(this).val();
    const iso = (d) => d.toISOString().split('T')[0];

    if (periodFilter === 'custom') {
      $customDateRange.show();
      const today = new Date();
      $customFrom.val($customFrom.val() || iso(today));
      $customTo.val($customTo.val() || iso(today));
      customFrom = $customFrom.val();
      customTo = $customTo.val();
      goWithFilters(periodFilter, firmFilter, customFrom, customTo);
      return;
    }
    $customDateRange.hide();
    goWithFilters(periodFilter, firmFilter, null, null);
  });

  $firmSelect.on('change', function () {
    firmFilter = $(this).val() || '';
    goWithFilters(periodFilter, firmFilter, customFrom, customTo);
  });

  $customFrom.on('change', function () {
    customFrom = $(this).val();
    if (periodFilter === 'custom') {
      goWithFilters(periodFilter, firmFilter, customFrom, customTo);
    }
  });

  $customTo.on('change', function () {
    customTo = $(this).val();
    if (periodFilter === 'custom') {
      goWithFilters(periodFilter, firmFilter, customFrom, customTo);
    }
  });

  // Make the search icon clickable/usable
  $('.sale-search-icon').on('click', function () {
    $input.focus();
  });

  // Action buttons
  $('#exportExcel').on('click', function () {
    const headers = $('table.txn-table thead th').not(':last').map(function () {
      const $th = $(this);
      const headerText = $th.find('.column-filter-header span').first().text().trim()
        || $th.clone().children().remove().end().text().trim();
      return headerText || '';
    }).get();

    const rows = [];
    rows.push(headers.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','));

    $('table.txn-table tbody tr:visible').each(function () {
      const cols = $(this).find('td').not(':last').map(function () {
        return $(this).text().replace(/\s+/g, ' ').trim();
      }).get();
      if (!cols.length) return;
      const normalized = headers.map((_, idx) => cols[idx] ?? '');
      rows.push(normalized.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','));
    });
    const csvContent = rows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', 'transactions.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });

  function getVisibleSaleRows() {
    return $('table.txn-table tbody tr:visible').filter(function () {
      return $(this).find('.sale-action-menu').length > 0;
    });
  }

  function buildReportQueryFromRows($rows) {
    const saleIds = [];
    const dates = [];
    $rows.each(function () {
      const $row = $(this);
      const saleId = $row.find('.sale-action-menu').data('sale-id');
      if (saleId) saleIds.push(saleId);
      const dateText = ($row.children().eq(0).text() || '').trim();
      if (dateText) dates.push(dateText);
    });
    return {
      sale_ids: [...new Set(saleIds)].join(','),
      duration: dates.length ? `From ${dates[dates.length - 1]} to ${dates[0]}` : ''
    };
  }

  $('#printTable').on('click', function () {
    const $rows = getVisibleSaleRows();
    if (!$rows.length) {
      alert('No visible sales found for print preview.');
      return;
    }
    const query = buildReportQueryFromRows($rows);
    const previewUrl = new URL(reportPreviewBaseUrl, window.location.origin);
    previewUrl.searchParams.set('sale_ids', query.sale_ids);
    if (query.duration) previewUrl.searchParams.set('duration', query.duration);
    const pdfUrl = new URL(reportPdfBaseUrl, window.location.origin);
    pdfUrl.searchParams.set('sale_ids', query.sale_ids);
    if (query.duration) pdfUrl.searchParams.set('duration', query.duration);
    showPrintOptions(previewUrl.toString(), pdfUrl.toString(), 'Sale Report');
  });

  $('#signalBtn').on('click', function () {
    const total = $('table.txn-table tbody tr:visible').length;
    alert('Showing ' + total + ' transaction(s) (signal action placeholder).');
  });

  // Row action dropdown items
  $(document).on('click', '.sale-action-menu .dropdown-item', function (e) {
    e.preventDefault();
    const action = $(this).data('action');
    const $menu = $(this).closest('.sale-action-menu');
    const saleId = $menu.data('sale-id');
    const isCancelled = String($menu.data('is-cancelled')) === '1';
    const editUrl = $menu.data('edit-url');
    const previewUrl = buildUrlWithTheme($menu.data('preview-url'), saleId);
    const pdfUrl = buildUrlWithTheme($menu.data('pdf-url'), saleId);
    const printUrl = buildUrlWithTheme($menu.data('print-url'), saleId, { print: 1 });
    const deliveryPreviewUrl = buildUrlWithTheme($menu.data('delivery-preview-url'), saleId);
    const paymentHistoryUrl = $menu.data('payment-history-url');
    const bankHistoryUrl = $menu.data('bank-history-url');
    const convertReturnUrl = $menu.data('convert-return-url');
    const cancelUrl = $menu.data('cancel-url');
    const saleNumber = $menu.data('sale-number');
    const partyName = $menu.data('party-name');
    const partyEmail = $menu.data('party-email');
    const emailUrl = $menu.data('email-url');

    if (action === 'view') {
      if (isCancelled) {
        alert('Cancelled invoice cannot be edited.');
        return;
      }

      if (editUrl) {
        requestSalePasscode(() => {
          window.location.href = editUrl;
        });
      }
    } else if (action === 'convert-return') {
      if (convertReturnUrl) {
        window.location.href = convertReturnUrl;
      }
    } else if (action === 'preview-delivery') {
      openPreviewModal(deliveryPreviewUrl, `Delivery Challan - ${saleNumber}`, {
        pdfUrl: pdfUrl,
        printUrl: printUrl,
        previewUrl: deliveryPreviewUrl,
        partyEmail: partyEmail,
        partyName: partyName,
        saleNumber: saleNumber,
        emailUrl: emailUrl,
        documentLabel: 'Delivery Challan',
      });
    } else if (action === 'payment-history') {
      if (!paymentHistoryUrl) return;

      fetchJson(paymentHistoryUrl)
        .then((data) => {
          renderPaymentHistoryModal(data);
        })
        .catch((error) => {
          alert(error.message || 'Unable to load payment history.');
        });
    } else if (action === 'cancel') {
      if (isCancelled) {
        alert('Invoice already cancelled.');
        return;
      }

      if (!cancelUrl || !confirm('Are you sure you want to cancel this invoice?')) {
        return;
      }

      fetchJson(cancelUrl, {
        method: 'POST',
      })
        .then((data) => {
          const $row = $menu.closest('tr');
          $row.addClass('sale-cancelled');
          $row.find('.status-text').removeClass('text-success text-warning text-danger').text(data.status || 'Cancelled');
          $menu.attr('data-is-cancelled', '1').data('is-cancelled', 1);
          alert(data.message || 'Invoice cancelled successfully.');
        })
        .catch((error) => {
          alert(error.message || 'Unable to cancel invoice.');
        });
    } else if (action === 'delete') {
      if (!saleId) return;
      const $row = $menu.closest('tr');

      requestSalePasscode(() => {
        if (!confirm('Are you sure you want to delete this sale?')) {
          return;
        }

        fetch(`/dashboard/sales/${saleId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
        })
          .then(res => res.json())
          .then(data => {
            if (data && data.success) {
              $row.remove();
              alert(data.message || 'Sale deleted successfully');
            } else {
              throw new Error((data && data.message) ? data.message : 'Unable to delete sale');
            }
          })
          .catch(err => {
            console.error(err);
            alert('Error deleting sale. See console for details.');
          });
      });
    } else if (action === 'duplicate') {
      const duplicateUrl = `${window.location.origin}/dashboard/sales/${saleId}/duplicate`;
      window.location.href = duplicateUrl;
    } else if (action === 'pdf') {
      if (pdfUrl) {
        window.open(pdfUrl, '_blank');
      }
    } else if (action === 'preview') {
      if (previewUrl) {
        openPreviewModal(previewUrl, `Invoice Preview - ${saleNumber}`, {
          pdfUrl: pdfUrl,
          printUrl: printUrl,
          previewUrl: previewUrl,
          partyEmail: partyEmail,
          partyName: partyName,
          saleNumber: saleNumber,
          emailUrl: emailUrl,
          documentLabel: 'Sale Invoice',
        });
      }
    } else if (action === 'print') {
      const previewReportUrl = new URL(reportPreviewBaseUrl, window.location.origin);
      previewReportUrl.searchParams.set('sale_ids', String(saleId));
      previewReportUrl.searchParams.set('duration', `For invoice ${saleNumber}`);
      const pdfReportUrl = new URL(reportPdfBaseUrl, window.location.origin);
      pdfReportUrl.searchParams.set('sale_ids', String(saleId));
      pdfReportUrl.searchParams.set('duration', `For invoice ${saleNumber}`);
      showPrintOptions(previewReportUrl.toString(), pdfReportUrl.toString(), `Sale Report - ${saleNumber}`);
    } else if (action === 'history') {
      if (!bankHistoryUrl) return;

      fetchJson(bankHistoryUrl)
        .then((data) => {
          const rows = (data.entries || []).map((entry, index) => ([
            index + 1,
            entry.bank_name,
            entry.type,
            `Rs ${Number(entry.amount || 0).toFixed(2)}`,
            entry.reference,
            entry.date,
          ]));

          renderHistoryTable('Bank History', ['#', 'Bank', 'Type', 'Amount', 'Reference', 'Date'], rows, `<div class="mb-3"><strong>Invoice:</strong> ${data.bill_number}</div>`);
        })
        .catch((error) => {
          alert(error.message || 'Unable to load bank history.');
        });
    }
  });

  $dropdowns.each(function () {
    const $dropdown = $(this);
    const $toggle = $dropdown.find('.sale-dropdown-toggle');
    const $menu = $dropdown.find('.sale-dropdown-menu');

    $toggle.on('click', function (e) {
      e.stopPropagation();
      const isOpen = $dropdown.hasClass('open');
      closeAllPopups();
      if (!isOpen) {
        $dropdown.addClass('open');
      }
    });

    $menu.on('click', 'button', function (e) {
      e.stopPropagation();
      const action = $(this).data('action');
      closeAllPopups();

      if (action === 'notifications') {
        alert('No notifications yet.');
      } else if (action === 'settings') {
        alert('Settings coming soon.');
      } else if (action === 'all') {
        alert('Showing all invoices.');
      } else if (action === 'paid') {
        alert('Showing paid invoices.');
      } else if (action === 'unpaid') {
        alert('Showing unpaid invoices.');
      } else if (action === 'view') {
        alert('View/Edit invoice (placeholder action).');
      } else if (action === 'receive-payment') {
        alert('Receive payment (placeholder action).');
      } else if (action === 'convert-return') {
        alert('Convert to return (placeholder action).');
      } else if (action === 'preview-delivery') {
        alert('Preview delivery challan (placeholder action).');
      } else if (action === 'cancel') {
        alert('Cancel invoice (placeholder action).');
      } else if (action === 'delete') {
        alert('Delete invoice (placeholder action).');
      } else if (action === 'duplicate') {
        alert('Duplicate invoice (placeholder action).');
      } else if (action === 'pdf') {
        alert('Open PDF (placeholder action).');
      } else if (action === 'preview') {
        alert('Preview (placeholder action).');
      } else if (action === 'print') {
        alert('Print (placeholder action).');
      } else if (action === 'history') {
        alert('View history (placeholder action).');
      }
    });
  });

  // Column filter dropdown toggles
  $(document).on('click', '.filter-icon-btn', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = $(this);
    const $dropdown = $btn.closest('th').find('.column-filter-dropdown');
    const isOpen = $dropdown.hasClass('open');
    closeAllPopups();
    if (!isOpen) {
      $dropdown.addClass('open');
    }
  });

  // Column filter apply / clear actions
  $(document).on('click', '.column-filter-apply', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $btn = $(this);
    const colIndex = $btn.data('column-index');
    const $dropdown = $btn.closest('.column-filter-dropdown');
    const filterValue = $dropdown.find('.column-filter-input').val() || '';

    if (filterValue.trim() === '') {
      delete columnFilters[colIndex];
    } else {
      columnFilters[colIndex] = filterValue;
    }

    applyFilters();
    $dropdown.removeClass('open');
  });

  $(document).on('click', '.column-filter-clear', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $btn = $(this);
    const colIndex = $btn.data('column-index');
    const $dropdown = $btn.closest('.column-filter-dropdown');

    delete columnFilters[colIndex];
    $dropdown.find('.column-filter-input').val('');

    applyFilters();
    $dropdown.removeClass('open');
  });

  // Row-level action buttons
  $(document).on('click', '.row-action-print', function () {
    const $menu = $(this).closest('td').find('.sale-action-menu');
    const saleId = $menu.data('sale-id');
    if (saleId) {
      window.open(`/dashboard/sales/${saleId}/print`, '_blank');
    }
  });

  $(document).on('click', '.row-action-share', function () {
    const $menu = $(this).closest('td').find('.sale-action-menu');
    const saleId = $menu.data('sale-id');
    const previewUrl = buildUrlWithTheme($menu.data('preview-url'), saleId);
    const rowText = $(this).closest('tr').find('td').map(function () {
      return $(this).text().trim();
    }).get().join(' | ');

    if (navigator.share) {
      navigator.share({
        title: 'Invoice details',
        text: rowText,
        url: previewUrl || window.location.href,
      }).catch(() => {
        // ignore
      });
    } else {
      alert('Share is not supported in this browser.');
    }
  });

  $(document).on('click', function (e) {
    // Keep dropdowns open while interacting with their content (inputs/buttons)
    if ($(e.target).closest('.sale-dropdown, .column-filter-header, .column-filter-dropdown').length === 0) {
      closeAllPopups();
    }
  });
});
