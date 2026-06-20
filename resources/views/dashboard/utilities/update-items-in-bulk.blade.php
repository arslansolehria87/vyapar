@extends('layouts.app')

@section('title', 'Utilities - Update Items In Bulk')
@section('description', 'Update multiple items at once using bulk edit tools.')
@section('page', 'update-items-in-bulk')

@push('styles')
  <style>
    .bulk-update-page {
      padding: 20px 12px;
      background: #f4f4f7;
      min-height: calc(100vh - 20px);
    }

    .bulk-update-shell {
      background: #fff;
      border: 1px solid #e8edf6;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 18px 34px rgba(46, 74, 121, 0.08);
    }

    .bulk-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      padding: 18px 22px 14px;
      border-bottom: 1px solid #edf1f7;
      background: #fff;
    }

    .bulk-topbar h1 {
      margin: 0;
      color: #1f2942;
      font-size: 2rem;
      font-weight: 700;
    }

    .bulk-controls {
      display: flex;
      align-items: center;
      gap: 18px;
      flex-wrap: wrap;
      margin-left: auto;
    }

    .bulk-search {
      position: relative;
      min-width: 320px;
    }

    .bulk-search i {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #9aa6ba;
    }

    .bulk-search input {
      width: 100%;
      height: 48px;
      padding: 10px 16px 10px 44px;
      border: 1px solid #dde3ee;
      border-radius: 999px;
      background: #fff;
      color: #25314c;
      outline: none;
    }

    .mode-switch {
      display: flex;
      align-items: center;
      gap: 22px;
      flex-wrap: wrap;
    }

    .mode-option {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      color: #30384a;
      font-size: 1.05rem;
      cursor: pointer;
    }

    .mode-option input {
      width: 24px;
      height: 24px;
      accent-color: #1583ef;
    }

    .bulk-selected-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 14px 22px;
      background: #dff0ff;
      border-bottom: 1px solid #d4e5f6;
    }

    .bulk-selected-bar.is-hidden {
      display: none;
    }

    .bulk-selected-text {
      color: #2f3a4f;
      font-size: 1rem;
      font-weight: 500;
    }

    .tax-button {
      border: 0;
      border-radius: 999px;
      padding: 12px 22px;
      background: linear-gradient(180deg, #ff2a62 0%, #f3124e 100%);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      box-shadow: 0 12px 22px rgba(243, 18, 78, 0.24);
      cursor: pointer;
    }

    .tax-action {
      position: relative;
      flex-shrink: 0;
    }

    .tax-button i {
      margin-left: 5px;
      transition: transform 0.18s ease;
    }

    .tax-action.is-open .tax-button i {
      transform: rotate(180deg);
    }

    .tax-slab-menu {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      z-index: 1100;
      display: none;
      width: 270px;
      max-height: 300px;
      overflow-y: auto;
      padding: 8px;
      border: 1px solid #dce3ee;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 16px 34px rgba(36, 52, 86, 0.2);
    }

    .tax-action.is-open .tax-slab-menu {
      display: block;
    }

    .tax-slab-option {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      width: 100%;
      border: 0;
      border-radius: 9px;
      padding: 10px 12px;
      background: transparent;
      color: #29344a;
      text-align: left;
      cursor: pointer;
    }

    .tax-slab-option:hover,
    .tax-slab-option:focus {
      background: #eef6ff;
      outline: none;
    }

    .tax-slab-rate {
      color: #718096;
      font-size: 0.86rem;
      white-space: nowrap;
    }

    .tax-slab-empty {
      padding: 14px 12px;
      color: #7a8497;
      text-align: center;
    }

    .bulk-table-wrap {
      overflow: auto;
      min-height: 510px;
      background: #fff;
    }

    .bulk-table {
      width: 100%;
      min-width: 1280px;
      border-collapse: separate;
      border-spacing: 0;
    }

    .bulk-table th,
    .bulk-table td {
      padding: 12px 10px;
      border-right: 1px solid #e9edf5;
      border-bottom: 1px solid #e9edf5;
      white-space: nowrap;
      vertical-align: middle;
    }

    .bulk-table th {
      background: #fff;
      color: #5c6372;
      font-weight: 700;
      font-size: 0.96rem;
      text-transform: uppercase;
    }

    .bulk-table tbody tr:nth-child(odd) td {
      background: #fff;
    }

    .bulk-table tbody tr:nth-child(even) td {
      background: #fbfcff;
    }

    .bulk-check {
      width: 24px;
      height: 24px;
      accent-color: #1786ef;
    }

    .bulk-input,
    .bulk-select {
      width: 100%;
      min-width: 150px;
      border: 1px solid #d5ddea;
      border-radius: 10px;
      background: #fff;
      color: #1f2942;
      padding: 9px 12px;
      outline: none;
      transition: 0.18s ease;
    }

    .bulk-input:focus,
    .bulk-select:focus {
      border-color: #65aefa;
      box-shadow: 0 0 0 3px rgba(101, 174, 250, 0.15);
    }

    .bulk-input.currency {
      min-width: 130px;
    }

    .bulk-empty {
      min-height: 280px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6b7488;
      font-size: 1.15rem;
    }

    .bulk-video-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding: 16px 20px;
      border-top: 1px solid #edf1f7;
      background: #fff;
    }

    .bulk-video-hint {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      color: #485368;
      font-size: 0.98rem;
    }

    .bulk-video-hint i {
      color: #ff445f;
      font-size: 1.5rem;
    }

    .watch-button {
      border: 0;
      border-radius: 999px;
      padding: 12px 24px;
      background: linear-gradient(180deg, #ff2a62 0%, #f3124e 100%);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      box-shadow: 0 10px 18px rgba(243, 18, 78, 0.22);
    }

    .bulk-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 16px 20px;
      background: #f7f7fb;
      border-top: 1px solid #edf1f7;
    }

    .bulk-summary {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 14px;
      background: #fbf1de;
      border-radius: 10px;
      color: #4d4d56;
      font-weight: 600;
    }

    .bulk-update-btn {
      min-width: 164px;
      border: 0;
      border-radius: 10px;
      background: #bec1d8;
      color: #fff;
      padding: 14px 24px;
      font-size: 1rem;
      font-weight: 700;
    }

    .bulk-update-btn.is-ready {
      background: linear-gradient(180deg, #268ff5 0%, #146ed7 100%);
      box-shadow: 0 10px 18px rgba(20, 110, 215, 0.24);
    }

    .bulk-toast {
      position: fixed;
      top: 22px;
      right: 22px;
      z-index: 1080;
      max-width: 420px;
      border-radius: 10px;
      padding: 15px 18px;
      color: #fff;
      background: #22a755;
      box-shadow: 0 14px 24px rgba(34, 52, 92, 0.18);
      display: none;
    }

    .bulk-toast.is-visible {
      display: block;
    }

    .bulk-toast.error {
      background: #e85a5a;
    }

    @media (max-width: 1199.98px) {
      .bulk-topbar,
      .bulk-controls {
        flex-direction: column;
        align-items: stretch;
      }

      .bulk-search {
        min-width: 100%;
      }

      .bulk-selected-bar,
      .bulk-video-row,
      .bulk-footer {
        flex-direction: column;
        align-items: stretch;
      }

      .bulk-update-btn,
      .tax-button,
      .watch-button {
        width: 100%;
      }

      .tax-action {
        width: 100%;
      }

      .tax-slab-menu {
        left: 0;
        right: auto;
        width: 100%;
      }
    }

    @media (max-width: 767.98px) {
      .bulk-update-page {
        padding: 12px 8px;
      }

      .bulk-topbar h1 {
        font-size: 1.55rem;
      }
    }
  </style>
@endpush

@section('content')
  <div class="bulk-update-page">
    <div class="bulk-update-shell">
      <div class="bulk-topbar">
        <h1>Bulk Update Items</h1>

        <div class="bulk-controls">
          <div class="bulk-search">
            <i class="bi bi-search"></i>
            <input type="text" id="bulkSearchInput" placeholder="Search by item name">
          </div>

          <div class="mode-switch">
            <label class="mode-option">
              <input type="radio" name="bulkMode" value="pricing" checked>
              <span>Pricing</span>
            </label>
            <label class="mode-option">
              <input type="radio" name="bulkMode" value="stock">
              <span>Stock</span>
            </label>
            <label class="mode-option">
              <input type="radio" name="bulkMode" value="information">
              <span>Item Information</span>
            </label>
          </div>
        </div>
      </div>

      <div class="bulk-selected-bar is-hidden" id="bulkSelectedBar">
        <div class="bulk-selected-text" id="bulkSelectedText">0 items selected</div>
        <div class="tax-action" id="taxSlabAction">
          <button class="tax-button" id="updateTaxSlabButton" type="button"
            aria-expanded="false" aria-controls="taxSlabMenu">
            Update Tax Slab <i class="bi bi-chevron-down"></i>
          </button>
          <div class="tax-slab-menu" id="taxSlabMenu" role="menu"></div>
        </div>
      </div>

      <div class="bulk-table-wrap">
        <table class="bulk-table">
          <thead id="bulkTableHead"></thead>
          <tbody id="bulkTableBody"></tbody>
        </table>
        <div class="bulk-empty" id="bulkEmptyState" style="display:none;">No items found.</div>
      </div>

      <div class="bulk-video-row">
        <div class="bulk-video-hint">
          <i class="bi bi-youtube"></i>
          <span>Watch Youtube tutorial to learn more</span>
        </div>
        <button class="watch-button" type="button">Watch Video</button>
      </div>

      <div class="bulk-footer">
        <div class="bulk-summary" id="bulkSummaryText">Pricing - 0 Updates, Stock - 0 Updates, Item Information - 0 Updates</div>
        <button class="bulk-update-btn" id="bulkUpdateButton" type="button">Update</button>
      </div>
    </div>
  </div>

  <div class="bulk-toast" id="bulkToast"></div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const head = document.getElementById('bulkTableHead');
      const body = document.getElementById('bulkTableBody');
      const emptyState = document.getElementById('bulkEmptyState');
      const searchInput = document.getElementById('bulkSearchInput');
      const selectedBar = document.getElementById('bulkSelectedBar');
      const selectedText = document.getElementById('bulkSelectedText');
      const summaryText = document.getElementById('bulkSummaryText');
      const updateButton = document.getElementById('bulkUpdateButton');
      const taxSlabAction = document.getElementById('taxSlabAction');
      const taxSlabButton = document.getElementById('updateTaxSlabButton');
      const taxSlabMenu = document.getElementById('taxSlabMenu');
      const toast = document.getElementById('bulkToast');
      const dataUrl = "{{ route('utilities.update-items-in-bulk.data') }}";
      const categoriesUrl = "{{ route('items.category.list') }}";
      const updateUrl = "{{ route('items.bulk-update') }}";
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      let items = [];
      let categories = [];
      let taxRates = [];
      let selectedIds = [];
      let mode = 'pricing';
      let search = '';
      let pendingUpdates = {};

      const modeColumns = {
        pricing: [
          { key: 'name', label: 'Item Name*', type: 'text' },
          { key: 'category_id', label: 'Category', type: 'select' },
          { key: 'purchase_price', label: 'Purchase Price', type: 'number' },
          { key: 'sale_price', label: 'Sale Price', type: 'number' }
        ],
        stock: [
          { key: 'name', label: 'Item Name*', type: 'text' },
          { key: 'opening_qty', label: 'Opening Quant...', type: 'number' },
          { key: 'at_price', label: 'At Price', type: 'number' },
          { key: 'as_of_date', label: 'As Of Date', type: 'date' },
          { key: 'min_stock', label: 'Min. Stock To ...', type: 'number' },
          { key: 'location', label: 'Location', type: 'text' }
        ],
        information: [
          { key: 'name', label: 'Item Name*', type: 'text' },
          { key: 'category_id', label: 'Category', type: 'select' },
          { key: 'item_code', label: 'Item Code', type: 'text' },
          { key: 'description', label: 'Description', type: 'text' }
        ]
      };

      function showToast(message, isError) {
        toast.textContent = message;
        toast.classList.toggle('error', !!isError);
        toast.classList.add('is-visible');
        clearTimeout(showToast.timeoutId);
        showToast.timeoutId = setTimeout(function () {
          toast.classList.remove('is-visible');
        }, 3500);
      }

      function getCurrentValue(item, key) {
        if (pendingUpdates[item.id] && Object.prototype.hasOwnProperty.call(pendingUpdates[item.id], key)) {
          return pendingUpdates[item.id][key];
        }
        return item[key] ?? '';
      }

      function getVisibleItems() {
        return items.filter(function (item) {
          return String(item.name || '').toLowerCase().includes(search.toLowerCase());
        });
      }

      function getCategoryOptions(selectedValue) {
        const options = ['<option value="">---</option>'];
        categories.forEach(function (category) {
          const selected = String(selectedValue || '') === String(category.id) ? 'selected' : '';
          options.push(`<option value="${category.id}" ${selected}>${category.name}</option>`);
        });
        return options.join('');
      }

      function escapeHtml(value) {
        return String(value ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function renderTaxSlabMenu() {
        const options = [
          '<button class="tax-slab-option" type="button" role="menuitem" data-tax-rate-id="" data-tax-rate-name="" data-tax-rate-value="0"><span>No Tax</span><span class="tax-slab-rate">0%</span></button>'
        ];

        taxRates.forEach(function (taxRate) {
          options.push(
            `<button class="tax-slab-option" type="button" role="menuitem" data-tax-rate-id="${taxRate.id}" data-tax-rate-name="${escapeHtml(taxRate.name)}" data-tax-rate-value="${taxRate.rate}">` +
              `<span>${escapeHtml(taxRate.name)}</span>` +
              `<span class="tax-slab-rate">${Number(taxRate.rate || 0).toFixed(2)}%</span>` +
            '</button>'
          );
        });

        taxSlabMenu.innerHTML = options.join('');

        taxSlabMenu.querySelectorAll('.tax-slab-option').forEach(function (option) {
          option.addEventListener('click', function () {
            applyTaxSlabToSelection({
              id: option.dataset.taxRateId,
              name: option.dataset.taxRateName,
              rate: option.dataset.taxRateValue
            });
          });
        });
      }

      function closeTaxSlabMenu() {
        taxSlabAction.classList.remove('is-open');
        taxSlabButton.setAttribute('aria-expanded', 'false');
      }

      function applyTaxSlabToSelection(taxRate) {
        if (!selectedIds.length) {
          closeTaxSlabMenu();
          showToast('Please select at least one item.', true);
          return;
        }

        selectedIds.forEach(function (itemId) {
          if (!pendingUpdates[itemId]) {
            pendingUpdates[itemId] = {};
          }

          pendingUpdates[itemId].tax_rate_id = taxRate.id || null;
          pendingUpdates[itemId].tax_rate_name = taxRate.name || null;
          pendingUpdates[itemId].tax_rate_value = Number(taxRate.rate || 0);
        });

        closeTaxSlabMenu();
        renderSummary();
        showToast(
          (taxRate.name ? taxRate.name + ' tax slab' : 'No Tax') +
          ' applied to ' + selectedIds.length + ' selected item(s). Click Update to save.',
          false
        );
      }

      function renderHead() {
        const columns = modeColumns[mode];
        head.innerHTML = `
          <tr>
            <th><input class="bulk-check" type="checkbox" id="bulkSelectAll"></th>
            <th>#</th>
            ${columns.map(function (column) { return `<th>${column.label}</th>`; }).join('')}
          </tr>
        `;

        const selectAll = document.getElementById('bulkSelectAll');
        if (selectAll) {
          const visibleIds = getVisibleItems().map(function (item) { return item.id; });
          selectAll.checked = visibleIds.length > 0 && visibleIds.every(function (id) { return selectedIds.includes(id); });
          selectAll.addEventListener('change', function () {
            if (selectAll.checked) {
              visibleIds.forEach(function (id) {
                if (!selectedIds.includes(id)) {
                  selectedIds.push(id);
                }
              });
            } else {
              selectedIds = selectedIds.filter(function (id) {
                return !visibleIds.includes(id);
              });
            }
            renderAll();
          });
        }
      }

      function renderBody() {
        const columns = modeColumns[mode];
        const visibleItems = getVisibleItems();

        body.innerHTML = '';
        emptyState.style.display = visibleItems.length ? 'none' : 'flex';

        visibleItems.forEach(function (item, index) {
          const row = document.createElement('tr');
          const isSelected = selectedIds.includes(item.id);

          row.innerHTML = `
            <td><input class="bulk-check bulk-row-check" type="checkbox" data-item-id="${item.id}" ${isSelected ? 'checked' : ''}></td>
            <td>${index + 1}</td>
            ${columns.map(function (column) {
              const value = getCurrentValue(item, column.key);
              if (column.type === 'select') {
                return `<td><select class="bulk-select bulk-field" data-item-id="${item.id}" data-field="${column.key}">${getCategoryOptions(value)}</select></td>`;
              }
              const inputType = column.type === 'date' ? 'date' : (column.type === 'number' ? 'number' : 'text');
              return `<td><input class="bulk-input bulk-field ${column.type === 'number' ? 'currency' : ''}" type="${inputType}" step="${column.type === 'number' ? '0.01' : ''}" data-item-id="${item.id}" data-field="${column.key}" value="${String(value ?? '').replace(/"/g, '&quot;')}"></td>`;
            }).join('')}
          `;

          body.appendChild(row);
        });

        document.querySelectorAll('.bulk-row-check').forEach(function (checkbox) {
          checkbox.addEventListener('change', function () {
            const itemId = Number(checkbox.dataset.itemId);
            if (checkbox.checked) {
              if (!selectedIds.includes(itemId)) {
                selectedIds.push(itemId);
              }
            } else {
              selectedIds = selectedIds.filter(function (id) { return id !== itemId; });
            }
            renderSummary();
            renderHead();
          });
        });

        document.querySelectorAll('.bulk-field').forEach(function (field) {
          field.addEventListener('input', handleFieldChange);
          field.addEventListener('change', handleFieldChange);
        });
      }

      function handleFieldChange(event) {
        const itemId = Number(event.target.dataset.itemId);
        const field = event.target.dataset.field;
        const value = event.target.value;

        if (!pendingUpdates[itemId]) {
          pendingUpdates[itemId] = {};
        }

        pendingUpdates[itemId][field] = value;

        if (!selectedIds.includes(itemId)) {
          selectedIds.push(itemId);
        }

        renderSummary();
        renderHead();
      }

      function countUpdatesFor(section) {
        return Object.keys(pendingUpdates).reduce(function (count, itemId) {
          const fields = Object.keys(pendingUpdates[itemId] || {});
          const sectionKeys = modeColumns[section].map(function (column) { return column.key; });
          return count + (fields.some(function (field) { return sectionKeys.includes(field); }) ? 1 : 0);
        }, 0);
      }

      function renderSummary() {
        const selectedCount = selectedIds.length;
        const noTaxCount = selectedIds.filter(function (itemId) {
          const item = items.find(function (row) { return row.id === itemId; }) || {};
          const taxRateId = pendingUpdates[itemId]
            && Object.prototype.hasOwnProperty.call(pendingUpdates[itemId], 'tax_rate_id')
              ? pendingUpdates[itemId].tax_rate_id
              : item.tax_rate_id;
          return taxRateId === null || taxRateId === '' || taxRateId === undefined;
        }).length;

        selectedText.textContent = selectedCount + ' items selected ( No Tax : ' + noTaxCount + ' )';
        selectedBar.classList.toggle('is-hidden', selectedCount === 0);
        if (selectedCount === 0) {
          closeTaxSlabMenu();
        }

        summaryText.textContent = 'Pricing - ' + countUpdatesFor('pricing') + ' Updates, Stock - ' + countUpdatesFor('stock') + ' Updates, Item Information - ' + countUpdatesFor('information') + ' Updates';
        updateButton.classList.toggle('is-ready', Object.keys(pendingUpdates).length > 0);
      }

      function renderAll() {
        renderHead();
        renderBody();
        renderSummary();
      }

      async function loadData() {
        try {
          const [itemsResponse, categoriesResponse] = await Promise.all([
            fetch(dataUrl, { credentials: 'same-origin' }),
            fetch(categoriesUrl, { credentials: 'same-origin' })
          ]);

          const itemsJson = await itemsResponse.json();
          const categoriesJson = await categoriesResponse.json();

          if (!itemsResponse.ok || !itemsJson.success) {
            throw new Error(itemsJson.message || 'Unable to load items.');
          }

          items = itemsJson.items || [];
          taxRates = itemsJson.tax_rates || [];
          categories = Array.isArray(categoriesJson) ? categoriesJson : [];
          renderTaxSlabMenu();
          renderAll();
        } catch (error) {
          showToast(error.message, true);
        }
      }

      searchInput.addEventListener('input', function () {
        search = searchInput.value || '';
        renderAll();
      });

      document.querySelectorAll('input[name="bulkMode"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
          mode = radio.value;
          renderAll();
        });
      });

      taxSlabButton.addEventListener('click', function (event) {
        event.stopPropagation();
        const willOpen = !taxSlabAction.classList.contains('is-open');
        taxSlabAction.classList.toggle('is-open', willOpen);
        taxSlabButton.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });

      taxSlabMenu.addEventListener('click', function (event) {
        event.stopPropagation();
      });

      document.addEventListener('click', closeTaxSlabMenu);
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          closeTaxSlabMenu();
        }
      });

      updateButton.addEventListener('click', async function () {
        if (!Object.keys(pendingUpdates).length) {
          return;
        }

        const originalText = updateButton.textContent;
        updateButton.disabled = true;
        updateButton.textContent = 'Updating...';

        try {
          const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ updates: pendingUpdates })
          });

          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to update items.');
          }

          items = items.map(function (item) {
            if (!pendingUpdates[item.id]) {
              return item;
            }

            return Object.assign({}, item, pendingUpdates[item.id], {
              category_name: categories.find(function (category) {
                return String(category.id) === String(pendingUpdates[item.id].category_id || item.category_id);
              })?.name || item.category_name
            });
          });

          pendingUpdates = {};
          selectedIds = [];
          renderAll();
          showToast(result.message || 'Items updated successfully.', false);
        } catch (error) {
          showToast(error.message, true);
        } finally {
          updateButton.disabled = false;
          updateButton.textContent = originalText;
        }
      });

      loadData();
    });
  </script>
@endpush
