(function () {
    window.__sharedPartyItemCreateLoaded = true;
    window.itemRoutes = Object.assign({
        index: '/dashboard/items',
        store: '/dashboard/items',
        categoryStore: '/dashboard/items/category',
        unitsIndex: '/dashboard/items/units',
        unitsStore: '/dashboard/items/units'
    }, window.itemRoutes || {});

    let activePartyContext = null;
    let activeItemContext = null;
    let cachedUnits = [];
    let currentItemImageObjectUrl = null;
    let currentStockImageObjectUrls = [];
    let reopenUnitSelectorAfterQuickAdd = false;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function generateItemCode(seed = '') {
        const normalizedSeed = String(seed || '')
            .trim()
            .toUpperCase()
            .replace(/\s+/g, '-')
            .replace(/[^A-Z0-9-_]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '')
            .substring(0, 24);
        const suffix = String(Math.floor(1000 + Math.random() * 9000));
        return normalizedSeed ? `${normalizedSeed}-${suffix}`.substring(0, 50) : `ITEM-${suffix}`;
    }

    function clearItemImagePreview() {
        if (currentItemImageObjectUrl) {
            URL.revokeObjectURL(currentItemImageObjectUrl);
            currentItemImageObjectUrl = null;
        }
    }

    function clearStockImagePreviews() {
        currentStockImageObjectUrls.forEach((url) => URL.revokeObjectURL(url));
        currentStockImageObjectUrls = [];
        $('#newItemStockImages').val('');
        $('#newItemStockImagesList').empty();
    }

    function renderStockImagePreviews(files) {
        clearStockImagePreviews();
        const html = (files || []).map((file) => {
            const objectUrl = URL.createObjectURL(file);
            currentStockImageObjectUrls.push(objectUrl);
            return `<div class="item-stock-image-card"><img src="${objectUrl}" alt="${escapeHtml(file.name)}"><div class="name">${escapeHtml(file.name)}</div></div>`;
        }).join('');
        $('#newItemStockImagesList').html(html);
    }

    function updateUnitButtonLabel() {
        const baseUnit = String($('#newItemUnit').val() || '').trim().toUpperCase();
        const secondaryUnit = String($('#newItemSecondaryUnit').val() || '').trim().toUpperCase();
        let label = 'Select Unit';
        if (baseUnit && secondaryUnit && secondaryUnit !== baseUnit) {
            label = `${baseUnit} / ${secondaryUnit}`;
        } else if (baseUnit) {
            label = baseUnit;
        }
        $('#newItemUnitBtn').text(label);
        $('.base-unit-preview').text(baseUnit ? `1 ${baseUnit}` : '1 Base Unit');
        $('.secondary-unit-preview').text(secondaryUnit || 'Secondary Unit');
    }

    function populateUnitSelectorOptions() {
        const selects = ['#newItemBaseUnitSelect', '#newItemSecondaryUnitSelect'];
        const options = ['<option value="">Select Unit</option>'];
        const seen = new Set();
        (Array.isArray(cachedUnits) ? cachedUnits : []).forEach((unit) => {
            const shortName = String(unit.short_name || unit.short || unit.name || '').trim().toUpperCase();
            const name = String(unit.name || shortName).trim().toUpperCase();
            if (!shortName || seen.has(shortName)) {
                return;
            }
            seen.add(shortName);
            const label = name && name !== shortName ? `${name} (${shortName})` : shortName;
            options.push(`<option value="${escapeHtml(shortName)}">${escapeHtml(label)}</option>`);
        });
        selects.forEach((selector) => {
            const current = $(selector).val();
            $(selector).html(options.join(''));
            if (current) {
                $(selector).val(current);
            }
        });
    }

    function parseJsonSafely(text) {
        try {
            return JSON.parse(text);
        } catch (_) {
            return null;
        }
    }

    function fetchJson(url, options = {}) {
        const headers = Object.assign({
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }, options.headers || {});

        return fetch(url, Object.assign({}, options, { headers }))
            .then(async (response) => {
                const text = await response.text();
                const data = parseJsonSafely(text);
                if (!response.ok) {
                    throw new Error(data && data.message ? data.message : 'Request failed.');
                }
                if (data === null) {
                    throw new Error('Server response was not valid JSON.');
                }
                return data;
            });
    }

    function getBodyContext($target) {
        const $context = $target.closest('.invoice-container, .tab-pane, .invoice-form, body');
        return $context.length ? $context : $(document.body);
    }

    function getItemMeta(item) {
        return {
            plainLabel: item.name || '',
            richLabel: `${item.name || ''} | Sale: ${item.sale_price ?? item.price ?? 0} | Stock: ${item.opening_qty ?? 0} | Location: ${item.location ?? ''}`,
            categoryLabel: item.category_name || item.category?.name || item.category || item.category_id || '',
            itemCode: item.item_code || '',
            description: item.description || item.item_description || '',
            discount: item.discount ?? item.sale_discount ?? 0,
            purchasePrice: item.purchase_price ?? 0
        };
    }

    function buildItemOptionsHtml(items = []) {
        return items.map((item) => {
            const meta = getItemMeta(item);
            return `<option value="${item.id}" data-price="${item.price ?? ''}" data-sale-price="${item.sale_price ?? ''}" data-purchase-price="${meta.purchasePrice}" data-stock="${item.opening_qty ?? ''}" data-location="${item.location ?? ''}" data-label="${meta.plainLabel}" data-rich-label="${meta.richLabel}" data-unit="${item.unit || ''}" data-category="${meta.categoryLabel}" data-item-code="${meta.itemCode}" data-description="${meta.description}" data-discount="${meta.discount}" data-bag-weight="${item.bag_weight ?? ''}">${meta.richLabel}</option>`;
        }).join('');
    }

    function buildPickerRowsHtml(items = []) {
        if (!items.length) {
            return '<div class="item-picker-empty">No items found</div>';
        }

        return items.map((item) => {
            const stock = parseFloat(item.opening_qty ?? 0) || 0;
            const stockClass = stock > 0 ? 'pos' : 'zero';
            return `
                <div class="item-picker-row item-picker-option" data-id="${item.id}">
                    <div class="item-picker-name">${item.name || ''}${item.item_code ? `<small>(${item.item_code})</small>` : ''}</div>
                    <div>${(parseFloat(item.sale_price ?? item.price ?? 0) || 0).toFixed(2)}</div>
                    <div>${(parseFloat(item.purchase_price ?? 0) || 0).toFixed(2)}</div>
                    <div class="item-picker-stock ${stockClass}">${stock}</div>
                </div>
            `;
        }).join('');
    }

    function buildPartyOptionHtml(party) {
        const amount = Number(party.opening_balance || 0).toFixed(2);
        const type = party.transaction_type || '';
        const colorClass = type === 'pay' ? 'text-danger' : (type === 'receive' ? 'text-success' : '');
        const arrowIcon = type === 'pay'
            ? '<i class="fa-solid fa-arrow-up me-1"></i>'
            : (type === 'receive' ? '<i class="fa-solid fa-arrow-down me-1"></i>' : '');

        return `
            <li>
                <a class="dropdown-item d-flex justify-content-between party-option" href="#"
                   data-id="${party.id || ''}"
                   data-name="${party.name || ''}"
                   data-phone="${party.phone || ''}"
                   data-phone-number-2="${party.phone_number_2 || ''}"
                   data-city="${party.city || ''}"
                   data-ptcl="${party.ptcl_number || ''}"
                   data-email="${party.email || ''}"
                   data-address="${party.address || ''}"
                   data-billing="${party.billing_address || ''}"
                   data-shipping="${party.shipping_address || ''}"
                   data-party-group="${party.party_group || ''}"
                   data-due-days="${party.due_days || ''}"
                   data-opening="${party.opening_balance || 0}"
                   data-type="${type}"
                   data-party-type="${Array.isArray(party.party_type) ? party.party_type.join(',') : (party.party_type || '')}">
                    <span>${party.name || ''}</span>
                    <span class="${colorClass}">${arrowIcon}Rs ${amount}</span>
                </a>
            </li>
        `;
    }

    function buildUnitMenuHtml(units = []) {
        const normalized = (Array.isArray(units) ? units : [])
            .map((unit) => ({
                name: String(unit.name || unit.short_name || unit.short || '').trim(),
                short_name: String(unit.short_name || unit.short || unit.name || '').trim().toUpperCase()
            }))
            .filter((unit) => unit.short_name);

        if (!normalized.length) {
            return '<li><span class="dropdown-item-text text-muted">No units found</span></li>';
        }

        return normalized.map((unit) => {
            const label = unit.name && unit.name.toUpperCase() !== unit.short_name
                ? `${unit.short_name} (${unit.name})`
                : unit.short_name;
            return `<li><button class="dropdown-item unit-option" type="button" data-unit="${escapeHtml(unit.short_name)}">${escapeHtml(label)}</button></li>`;
        }).join('');
    }

    function populateUnitsMenu(units = []) {
        cachedUnits = Array.isArray(units) ? units.slice() : [];
        const baseHtml = buildUnitMenuHtml(cachedUnits);
        $('#newItemUnitMenu').html(`${baseHtml}<li><hr class="dropdown-divider unit-menu-divider"></li><li class="unit-add-action"><button class="dropdown-item" type="button" id="openAddUnitModalBtn"><i class="fa-regular fa-square-plus me-2"></i>Add Unit</button></li>`);
        syncAllRowUnitSelects();
    }

    function buildRowUnitOptionsHtml(selectedUnit = '') {
        const normalizedSelected = String(selectedUnit || '').trim().toUpperCase();
        const units = (Array.isArray(cachedUnits) ? cachedUnits : [])
            .map((unit) => ({
                short_name: String(unit.short_name || unit.short || unit.name || '').trim().toUpperCase()
            }))
            .filter((unit) => unit.short_name);

        if (!units.length) {
            return '<option value="">Select Unit</option>';
        }

        const seen = new Set();
        const options = ['<option value="">Select Unit</option>'];
        units.forEach((unit) => {
            if (seen.has(unit.short_name)) {
                return;
            }
            seen.add(unit.short_name);
            options.push(`<option value="${escapeHtml(unit.short_name)}" ${normalizedSelected === unit.short_name ? 'selected' : ''}>${escapeHtml(unit.short_name)}</option>`);
        });

        if (normalizedSelected && !seen.has(normalizedSelected)) {
            options.push(`<option value="${escapeHtml(normalizedSelected)}" selected>${escapeHtml(normalizedSelected)}</option>`);
        }

        return options.join('');
    }

    function syncAllRowUnitSelects() {
        $('select.item-unit').each(function () {
            const $select = $(this);
            const currentValue = String($select.val() || '').trim().toUpperCase();
            $select.html(buildRowUnitOptionsHtml(currentValue));
            if (currentValue) {
                $select.val(currentValue);
            }
        });
    }

    function fetchUnits() {
        const unitsIndex = window.itemRoutes && window.itemRoutes.unitsIndex;
        if (!unitsIndex) {
            populateUnitsMenu([]);
            return Promise.resolve([]);
        }

        const unitsUrl = String(unitsIndex).includes('?')
            ? `${unitsIndex}&json=1`
            : `${unitsIndex}?json=1`;

        return fetchJson(unitsUrl)
            .then((data) => {
                const units = data.units || data.data || [];
                populateUnitsMenu(units);
                return units;
            })
            .catch(() => {
                populateUnitsMenu([]);
                return [];
            });
    }

    function resetPartyTabs() {
        const tabEl = document.getElementById('party-address-tab')
            || document.querySelector('#addPartyModal [data-bs-target="#partyAddressPane"]');
        if (tabEl && window.bootstrap && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(tabEl).show();
        }
        $('#partyAddressPane').addClass('show active');
        $('#partyCreditPane, #partyAdditionalPane').removeClass('show active');
        $('#party-address-tab, #addPartyModal [data-bs-target="#partyAddressPane"]').addClass('active').attr('aria-selected', 'true');
        $('#party-credit-tab, #party-additional-tab, #addPartyModal [data-bs-target="#partyCreditPane"], #addPartyModal [data-bs-target="#partyAdditionalPane"]').removeClass('active').attr('aria-selected', 'false');
    }

    function resetItemTabs() {
        const pricingTab = document.getElementById('pricing-tab');
        if (pricingTab && window.bootstrap && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(pricingTab).show();
        }
        $('#pricing-tab-pane').addClass('show active');
        $('#stock-tab-pane').removeClass('show active');
        $('#pricing-tab').addClass('active').attr('aria-selected', 'true');
        $('#stock-tab').removeClass('active').attr('aria-selected', 'false');
    }

    function resetPartyModal() {
        const form = document.getElementById('addPartyForm');
        if (form) {
            form.reset();
        }

        $('#transactionTypeValue').val('');
        $('#partyGroupInput').val('');
        $('#partyGroupText').text('Select group');
        $('#partyGroupMenu').addClass('d-none');
        $('#creditLimitAmountWrap').addClass('is-hidden');
        $('#btnUpdateParty, #btnDeleteParty').hide();
        $('#btnSaveParty, #btnSaveNewParty').show();
        resetPartyTabs();
    }

    function resetItemModal() {
        const form = document.getElementById('addItemForm');
        if (form) {
            form.reset();
        }

        $('#newItemCategory').val('');
        $('#newItemType').val('product');
        $('#newItemTypeToggle').prop('checked', false);
        $('#newItemProductLabel').text('Product');
        $('#newItemNameLabel').text('Item Name *');
        $('#purchase-sec').show();
        $('.wholesale-pricing').addClass('d-none');
        $('#toggleWholesalePricing').text('+ Add Wholesale Price');
        $('#stock-tab').show();
        resetItemTabs();

        $('#newItemUnitBtn').text('Select Unit');
        $('#newItemUnit').val('');
        $('#newItemSecondaryUnit').val('');
        $('#newItemUnitConversionRate').val('');
        $('#newItemBaseUnitSelect, #newItemSecondaryUnitSelect').val('');
        $('#newItemUnitConversionInput').val('0');
        $('#quickCategoryName, #quickUnitName, #quickUnitShortName').val('');
        $('#newItemImage').val('');
        clearItemImagePreview();
        clearStockImagePreviews();
        updateUnitButtonLabel();

        const thumb = document.getElementById('newItemImageThumb');
        const label = document.getElementById('newItemImageLabel');
        if (thumb) {
            thumb.innerHTML = '<i class="fa-regular fa-image fa-2x text-secondary"></i>';
            thumb.style.border = '1.5px solid #93c5fd';
        }
        if (label) {
            label.textContent = 'Click to choose image';
        }
    }

    function showPartyModal($trigger, prefillName = '') {
        activePartyContext = getBodyContext($trigger);
        resetPartyModal();
        if (prefillName) {
            $('#partyNameInput').val(prefillName);
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addPartyModal')).show();
        if (prefillName) {
            setTimeout(() => {
                const input = document.getElementById('partyNameInput');
                if (input) {
                    input.focus();
                }
            }, 10);
        }
    }

    function showItemModal($trigger, rowIndex = null) {
        const effectiveRowIndex = rowIndex !== null && rowIndex !== undefined ? rowIndex : $trigger.closest('tr.item-row').index();
        activeItemContext = {
            $context: getBodyContext($trigger),
            rowIndex: effectiveRowIndex >= 0 ? effectiveRowIndex : null
        };
        window.activeCreateRow = activeItemContext.rowIndex;
        resetItemModal();

        const itemNameInput = document.getElementById('newItemName');
        if (itemNameInput && activeItemContext.rowIndex !== null && activeItemContext.rowIndex !== undefined) {
            const $row = activeItemContext.$context.find('.item-row').eq(activeItemContext.rowIndex);
            const rowPickerValue = String($row.find('.item-picker-input').first().val() || '').trim();
            const selectedOptionLabel = String($row.find('select.item-name option:selected').data('label') || $row.find('select.item-name option:selected').text() || '').trim();
            const initialValue = rowPickerValue || selectedOptionLabel;
            if (initialValue) {
                itemNameInput.value = initialValue;
            }
        }

        fetchUnits();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addItemModal')).show();
    }

    function refreshPartyDropdowns() {
        const parties = Array.isArray(window.parties) ? window.parties : [];
        $('.party-dropdown-wrapper #partyDropdownMenu').each(function () {
            const $menu = $(this);
            $menu.find('.party-option').closest('li').remove();
            const $divider = $menu.find('.dropdown-divider').first().closest('li');
            const html = parties.map(buildPartyOptionHtml).join('');
            if ($divider.length) {
                $divider.before(html);
            } else {
                $menu.append(html);
            }
        });
    }

    function applyPartySelection($context, party) {
        if (!$context || !$context.length || !party) {
            return;
        }

        $context.find('.party-id').first().val(party.id || '');
        const $dropdownBtn = $context.find('#partyDropdownBtn').first();
        if ($dropdownBtn.is('input, textarea')) {
            $dropdownBtn.val(party.name || 'Select Party');
        } else {
            $dropdownBtn.text(party.name || 'Select Party');
        }
        $context.find('.phone-input').first().val(party.phone || '');
        $context.find('.billing-address').first().val(party.billing_address || '');
        $context.find('.shipping-address').first().val(party.shipping_address || '');
        $context.find('.party-group-input').first().val(party.party_group || '');

        const $balance = $context.find('#partyBalanceDisplay').first();
        if ($balance.length) {
            const amount = Number(party.opening_balance || 0).toFixed(2);
            if (party.transaction_type === 'pay') {
                $balance.html(`<i class="fa-solid fa-arrow-up text-danger me-1"></i>Rs ${amount}`);
            } else if (party.transaction_type === 'receive') {
                $balance.html(`<i class="fa-solid fa-arrow-down text-success me-1"></i>Rs ${amount}`);
            } else {
                $balance.text(`Rs ${amount}`);
            }
        }

        const $summary = $context.find('#selectedPartySummary').first();
        if ($summary.length) {
            $summary.removeClass('d-none');
            $summary.find('.party-summary-name').text(party.name || '-');
            $summary.find('.party-summary-phone').text(party.phone || '-');
            $summary.find('.party-summary-billing').text(party.billing_address || '-');
            $summary.find('.party-summary-shipping').text(party.shipping_address || '-');
        }

        $context.find('.party-details').removeClass('d-none');
        $context.find('.billing-address-field, .shipping-address-field, .phone-field').each(function () {
            this.style.display = '';
        });
    }

    function updateWindowParties(party) {
        window.parties = Array.isArray(window.parties) ? window.parties : [];
        window.parties = window.parties.filter((entry) => String(entry.id) !== String(party.id));
        window.parties.unshift(party);
    }

    function updateWindowItems(item) {
        window.items = Array.isArray(window.items) ? window.items : [];
        window.items = window.items.filter((entry) => String(entry.id) !== String(item.id));
        window.items.push(item);
    }

    function getItemsFromSelect($select) {
        return $select.find('option').map(function () {
            const value = $(this).attr('value');
            if (!value) {
                return null;
            }
            return {
                id: value,
                name: $(this).attr('data-label') || $(this).text().trim(),
                item_code: $(this).attr('data-item-code') || '',
                description: $(this).attr('data-description') || '',
                sale_price: parseFloat($(this).attr('data-sale-price') || $(this).attr('data-price') || 0) || 0,
                purchase_price: parseFloat($(this).attr('data-purchase-price') || 0) || 0,
                opening_qty: parseFloat($(this).attr('data-stock') || 0) || 0,
                location: $(this).attr('data-location') || '',
                unit: $(this).attr('data-unit') || '',
                category_name: $(this).attr('data-category') || '',
                discount: parseFloat($(this).attr('data-discount') || 0) || 0,
                bag_weight: parseFloat($(this).attr('data-bag-weight') || 0) || 0
            };
        }).get().filter(Boolean);
    }

    function positionPanel($picker) {
        const $panel = $picker.find('.item-picker-panel');
        if (!$panel.hasClass('open')) {
            return;
        }

        const input = $picker.find('.item-picker-input').get(0);
        if (!input) {
            return;
        }

        const rect = input.getBoundingClientRect();
        const width = Math.max(rect.width, 560);
        const left = Math.max(12, Math.min(rect.left, window.innerWidth - width - 12));

        $panel.css({
            position: 'fixed',
            top: `${rect.bottom + 4}px`,
            left: `${left}px`,
            width: `${width}px`,
            minWidth: `${width}px`,
            zIndex: 1055,
            display: 'block'
        });
    }

    function enhanceItemPicker($ctx, $select) {
        if ($select.data('enhanced-picker')) {
            return;
        }

        const existingPicker = $select.siblings('.item-picker');
        if (existingPicker.length) {
            existingPicker.remove();
        }

        $select.data('enhanced-picker', true)
            .addClass('d-none enhanced-hidden')
            .attr('aria-hidden', 'true')
            .attr('style', 'display:none!important;visibility:hidden!important;position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;');

        const $picker = $(`
            <div class="item-picker">
                <input type="text" class="item-picker-input" placeholder="Search Item">
                <div class="item-picker-panel">
                    <div class="item-picker-add open-item-modal"><i class="fa-regular fa-square-plus"></i> Add Item</div>
                    <div class="item-picker-head">
                        <span>Item</span>
                        <span>Sale Price</span>
                        <span>Purchase Price</span>
                        <span>Stock</span>
                    </div>
                    <div class="item-picker-list"></div>
                </div>
            </div>
        `);

        $select.before($picker);
        $select.data('all-options-html', $select.html() || '');

        function getRowCategoryValue() {
            return String($select.closest('tr').find('.item-category').val() || '').trim().toLowerCase();
        }

        function filterPoolByCategory(pool) {
            const selectedCategory = getRowCategoryValue();
            if (!selectedCategory) {
                return pool;
            }
            return (pool || []).filter((item) => {
                const categoryValue = String(item.category_name || item.category?.name || item.category || item.category_id || '').trim().toLowerCase();
                return categoryValue === selectedCategory;
            });
        }

        function rebuildSelectOptionsByCategory() {
            const currentValue = String($select.val() || '').trim();
            const allOptionsHtml = String($select.data('all-options-html') || '');
            if (!allOptionsHtml) {
                return;
            }

            const selectedCategory = getRowCategoryValue();
            const $temp = $('<select>' + allOptionsHtml + '</select>');
            const $options = $temp.find('option').filter(function () {
                const value = String($(this).attr('value') || '').trim();
                if (!value || !selectedCategory) {
                    return true;
                }
                const optionCategory = String($(this).data('category') || '').trim().toLowerCase();
                return optionCategory === selectedCategory;
            });

            $select.html($options);

            if (currentValue && $select.find(`option[value="${currentValue.replace(/"/g, '\\"')}"]`).length) {
                $select.val(currentValue);
            } else {
                $select.val('');
            }
        }

        function syncInput() {
            const $selected = $select.find('option:selected');
            const value = String($selected.val() || '').trim();
            $picker.find('.item-picker-input').val(value ? (($selected.data('label') || $selected.text() || '').trim()) : '');
        }

        function getPickerPool() {
            const selectItems = getItemsFromSelect($select);
            if (selectItems.length) {
                return selectItems;
            }
            const globalItems = Array.isArray(window.items) ? window.items : [];
            return filterPoolByCategory(globalItems);
        }

        function renderPicker(query) {
            const normalized = String(query || '').trim().toLowerCase();
            rebuildSelectOptionsByCategory();
            const pool = getPickerPool();
            const items = pool.filter((item) => {
                const label = String(item.name || '').toLowerCase();
                const code = String(item.item_code || '').toLowerCase();
                const desc = String(item.description || '').toLowerCase();
                return !normalized || label.includes(normalized) || code.includes(normalized) || desc.includes(normalized);
            });

            $picker.find('.item-picker-list').html(buildPickerRowsHtml(items));
            $picker.find('.item-picker-panel').addClass('open');
            positionPanel($picker);
        }

        syncInput();
        rebuildSelectOptionsByCategory();

        $picker.on('focus click', '.item-picker-input', function () {
            const raw = String($(this).val() || '').trim();
            rebuildSelectOptionsByCategory();
            renderPicker(raw.toLowerCase() === 'select item' ? '' : raw);
        });

        $picker.on('input', '.item-picker-input', function () {
            renderPicker($(this).val());
        });

        $picker.on('click', '.item-picker-option', function (event) {
            event.preventDefault();
            const id = String($(this).data('id') || '');
            $select.val(id).trigger('change');
            syncInput();
            $picker.find('.item-picker-panel').removeClass('open').hide();
        });

        $ctx.on('change.sharedCategoryPicker', '.item-category', function () {
            if ($(this).closest('tr').find('select.item-name').get(0) !== $select.get(0)) {
                return;
            }
            rebuildSelectOptionsByCategory();
            syncInput();
            if ($picker.find('.item-picker-panel').hasClass('open')) {
                renderPicker($picker.find('.item-picker-input').val());
            }
        });

        $picker.on('click', '.item-picker-add', function (event) {
            event.preventDefault();
            const rowIndex = $ctx.find('.item-row').index($select.closest('tr'));
            showItemModal($(this), rowIndex);
        });

        $select.on('change.sharedPicker', function () {
            syncInput();
        });
    }

    function enhanceAllItemPickers() {
        $('select.item-name').each(function () {
            const $select = $(this);
            if ($select.closest('.item-picker').length && $select.closest('.item-picker').find('.item-picker-input').length) {
                return;
            }
            enhanceItemPicker(getBodyContext($(this)), $(this));
        });
    }

    function refreshAllItemOptions(item) {
        if (item) {
            updateWindowItems(item);
        }

        const items = Array.isArray(window.items) ? window.items : [];
        const optionsHtml = buildItemOptionsHtml(items);
        $('select.item-name').each(function () {
            const current = $(this).val();
            $(this).html('<option value="" selected disabled>Select Item</option>' + optionsHtml);
            $(this).data('all-options-html', $(this).html() || '');
            if (current) {
                $(this).val(current);
            }
            $(this).trigger('change.sharedPicker');
        });
    }

    function saveParty(closeAfterSave) {
        const form = document.getElementById('addPartyForm');
        if (!form || !window.partyStoreUrl) {
            return;
        }

        const formData = new FormData(form);
        const transactionType = $('#toReceive').is(':checked') ? 'receive' : ($('#toPay').is(':checked') ? 'pay' : '');
        formData.set('transaction_type', transactionType);
        formData.set('credit_limit_enabled', $('#creditLimitSwitch').is(':checked') ? 1 : 0);
        $('#transactionTypeValue').val(transactionType);

        fetchJson(window.partyStoreUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
        }).then((data) => {
            const party = data.party || data.data || null;
            if (!party) {
                throw new Error('Party was not returned from server.');
            }

            updateWindowParties(party);
            refreshPartyDropdowns();
            applyPartySelection(activePartyContext || $(document.body), party);
            $(window).trigger('partiesUpdated');

            if (closeAfterSave) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('addPartyModal')).hide();
            }
            resetPartyModal();
        }).catch((error) => {
            alert(error.message || 'Unable to save party.');
        });
    }

    function savePartyGroup() {
        const name = $('#newGroupName').val().trim();
        if (!name) {
            alert('Enter group name');
            return;
        }

        fetchJson(window.partyGroupStoreUrl || '/dashboard/party-groups', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ name: name })
        }).then((data) => {
            const groupName = data.partyGroup && data.partyGroup.name ? data.partyGroup.name : name;
            const exists = $('#partyGroupList [data-group]').filter(function () {
                return $(this).data('group') === groupName;
            }).length > 0;

            if (!exists) {
                $('#partyGroupList').append(`<button type="button" class="dropdown-item" data-group="${groupName}">${groupName}</button>`);
            }

            $('#partyGroupInput').val(groupName);
            $('#partyGroupText').text(groupName);
            $('#newGroupName').val('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('partyGroupModal')).hide();
        }).catch((error) => {
            alert(error.message || 'Could not save party group.');
        });
    }

    function saveQuickCategory() {
        const name = $('#quickCategoryName').val().trim();
        if (!name) {
            alert('Please enter a category name');
            return;
        }

        fetchJson(window.itemRoutes.categoryStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ name: name })
        }).then((data) => {
            const category = data.category || data.data || null;
            if (!category) {
                throw new Error('Category was not returned from server.');
            }

            const $select = $('#newItemCategory');
            $select.find('option[value="__add_new__"]').before(`<option value="${category.id}">${category.name}</option>`);
            $select.val(String(category.id));
            $('#quickCategoryName').val('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addCategoryModal')).hide();
        }).catch((error) => {
            alert(error.message || 'Unable to save category.');
        });
    }

    function saveQuickUnit() {
        const name = $('#quickUnitName').val().trim();
        const shortName = $('#quickUnitShortName').val().trim().toUpperCase();

        if (!name || !shortName) {
            alert('Please enter both unit name and short name');
            return;
        }

        fetchJson(window.itemRoutes.unitsStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ name: name, short_name: shortName })
        }).then((data) => {
            const unit = data.unit || data.data || { name: name, short_name: shortName };
            cachedUnits.push(unit);
            populateUnitsMenu(cachedUnits);
            const selectedUnit = unit.short_name || shortName;
            $('#newItemUnitBtn').text(selectedUnit);
            $('#newItemUnit').val(selectedUnit);
            $('select.item-unit').each(function () {
                const $select = $(this);
                const exists = $select.find('option').filter(function () {
                    return String($(this).val() || $(this).text()).trim().toUpperCase() === selectedUnit;
                }).length > 0;
                if (!exists) {
                    $select.append(`<option value="${selectedUnit}">${selectedUnit}</option>`);
                }
            });
            populateUnitSelectorOptions();
            if (!$('#newItemUnit').val()) {
                $('#newItemUnit').val(selectedUnit);
            }
            $('#newItemBaseUnitSelect').val($('#newItemUnit').val() || selectedUnit);
            updateUnitButtonLabel();
            $('#quickUnitName, #quickUnitShortName').val('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addUnitModal')).hide();
            if (reopenUnitSelectorAfterQuickAdd) {
                reopenUnitSelectorAfterQuickAdd = false;
                $('#newItemBaseUnitSelect').val($('#newItemUnit').val() || selectedUnit);
                if (!$('#newItemSecondaryUnit').val()) {
                    $('#newItemSecondaryUnitSelect').val(selectedUnit);
                }
                setTimeout(() => {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).show();
                }, 180);
            }
        }).catch((error) => {
            alert(error.message || 'Unable to save unit.');
        });
    }

    function saveItem() {
        if (!window.itemRoutes || !window.itemRoutes.store) {
            alert('Item store route is missing.');
            return;
        }

        const itemName = $('#newItemName').val().trim();
        if (!itemName) {
            alert('Please enter an item name');
            return;
        }

        const formData = new FormData();
        const itemType = $('#newItemType').val() || 'product';
        formData.append('name', itemName);
        formData.append('category_id', $('#newItemCategory').val() || '');
        formData.append('unit', $('#newItemUnit').val() || '');
        formData.append('secondary_unit', $('#newItemSecondaryUnit').val() || '');
        formData.append('unit_conversion_rate', $('#newItemUnitConversionRate').val() || 0);
        formData.append('item_type', itemType);
        formData.append('type', itemType);
        formData.append('sale_price', $('#newItemSalePrice').val() || 0);
        formData.append('purchase_price', $('#newItemPurchasePrice').val() || 0);
        formData.append('wholesale_price', $('#newItemWholesalePrice').val() || 0);
        formData.append('wholesale_min_qty', $('#newItemWholesaleMinQty').val() || 0);
        formData.append('item_code', $('#newItemCode').val() || '');
        formData.append('opening_qty', $('#newItemStock').val() || 0);
        formData.append('at_price', $('#newItemAtPrice').val() || 0);
        formData.append('as_of_date', $('#newItemAsOfDate').val() || '');
        formData.append('bag_weight', $('#newItemBagWeight').val() || 0);
        formData.append('min_stock', $('#newItemMinStock').val() || 0);
        formData.append('location', $('#newItemLocation').val() || '');
        formData.append('description', $('#newItemDescription').val() || '');

        const imageInput = document.getElementById('newItemImage');
        if (imageInput && imageInput.files.length > 0) {
            formData.append('item_image', imageInput.files[0]);
        }
        const stockImagesInput = document.getElementById('newItemStockImages');
        if (stockImagesInput && stockImagesInput.files.length > 0) {
            Array.from(stockImagesInput.files).forEach((file) => {
                formData.append('item_images[]', file);
            });
        }

        fetchJson(window.itemRoutes.store, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
        }).then((data) => {
            const item = data.item || data.data || null;
            if (!item) {
                throw new Error('Item was not returned from server.');
            }

            refreshAllItemOptions(item);

            if (activeItemContext && activeItemContext.rowIndex !== null && activeItemContext.rowIndex !== undefined) {
                const $row = activeItemContext.$context.find('.item-row').eq(activeItemContext.rowIndex);
                const $select = $row.find('select.item-name').first();
                if ($select.length) {
                    $select.val(String(item.id)).trigger('change');
                }
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('addItemModal')).hide();
            resetItemModal();
        }).catch((error) => {
            alert(error.message || 'Unable to save item.');
        });
    }

    function syncItemType() {
        const isService = $('#newItemTypeToggle').is(':checked');
        $('#newItemType').val(isService ? 'service' : 'product');
        $('#newItemProductLabel').text(isService ? 'Service' : 'Product');
        $('#newItemNameLabel').text(isService ? 'Service Name *' : 'Item Name *');
        $('#purchase-sec').toggle(!isService);
        $('#stock-tab').toggle(!isService);
        if (isService && window.bootstrap && bootstrap.Tab) {
            const pricingTab = document.getElementById('pricing-tab');
            if (pricingTab) {
                bootstrap.Tab.getOrCreateInstance(pricingTab).show();
            }
            $('#pricing-tab-pane').addClass('show active');
            $('#stock-tab-pane').removeClass('show active');
        }
    }

    function bindPartySearch() {
        $(document).on('click.sharedPartySearch focus.sharedPartySearch keydown.sharedPartySearch keyup.sharedPartySearch', '.party-search-input, .dropdown-header-search', function (event) {
            event.stopPropagation();
        });

        $(document).on('input.sharedPartySearch', '.party-search-input', function () {
            const searchTerm = String($(this).val() || '').trim().toLowerCase();
            const $menu = $(this).closest('.party-dropdown-wrapper').find('#partyDropdownMenu');
            $menu.find('.party-option').each(function () {
                const $option = $(this);
                const partyName = String($.trim($option.find('span').first().text() || '')).toLowerCase();
                const partyPhone = String($option.data('phone') || '').toLowerCase();
                const visible = !searchTerm || partyName.includes(searchTerm) || partyPhone.includes(searchTerm);
                $option.closest('li').toggleClass('d-none', !visible);
            });
        });

        $(document).on('keydown.sharedPartySearch', '.party-search-input', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            const searchTerm = String($(this).val() || '').trim();
            if (!searchTerm) {
                return;
            }

            const $menu = $(this).closest('.party-dropdown-wrapper').find('#partyDropdownMenu');
            const $visibleOptions = $menu.find('.party-option').filter(function () {
                return !$(this).closest('li').hasClass('d-none');
            });

            const $exactMatch = $visibleOptions.filter(function () {
                return String($.trim($(this).find('span').first().text() || '')).toLowerCase() === searchTerm.toLowerCase();
            });

            if ($exactMatch.length) {
                $exactMatch.first().trigger('click');
                return;
            }

            showPartyModal($(this), searchTerm);
        });

        $(document).on('hide.bs.dropdown.sharedPartySearch', '.party-dropdown-wrapper', function () {
            const $wrapper = $(this);
            const $context = getBodyContext($wrapper);
            const hasSelectedParty = String($wrapper.find('.party-id').first().val() || $context.find('.party-id').first().val() || '').trim() !== '';
            if (!hasSelectedParty) {
                $wrapper.find('.party-search-input').val('');
                $wrapper.find('.party-option').closest('li').removeClass('d-none');
            }
        });
    }

    function bindPartySelection() {
        $(document).on('click.sharedPartySelect', '.party-option', function (event) {
            event.preventDefault();
            const $option = $(this);
            const party = {
                id: $option.data('id') || '',
                name: $.trim($option.find('span').first().text()),
                phone: $option.data('phone') || '',
                billing_address: $option.data('billing') || '',
                shipping_address: $option.data('shipping') || '',
                opening_balance: $option.data('opening') || 0,
                transaction_type: $option.data('type') || '',
                party_group: $option.data('party-group') || ''
            };
            applyPartySelection(getBodyContext($option), party);
            const button = $option.closest('.party-dropdown-wrapper').find('#partyDropdownBtn').get(0);
            if (button && window.bootstrap && bootstrap.Dropdown) {
                bootstrap.Dropdown.getOrCreateInstance(button).hide();
            }
        });
    }

    function bindEvents() {
        bindPartySearch();
        bindPartySelection();

        $(document).on('mousedown.sharedPartyPrefill touchstart.sharedPartyPrefill', '#addNewPartyBtn', function () {
            const $trigger = $(this);
            const $dropdownBtn = $trigger.closest('.party-dropdown-wrapper').find('#partyDropdownBtn').first();
            const prefillName = $dropdownBtn.is('input, textarea')
                ? String($dropdownBtn.val() || '').trim()
                : String($dropdownBtn.text() || '').trim();
            $trigger.data('prefill-name', prefillName);
        });

        $(document).on('click.sharedPartyModalOpen', '.open-party-modal, #addNewPartyBtn', function (event) {
            event.preventDefault();
            const $trigger = $(this);
            let prefillName = '';
            if ($trigger.is('#addNewPartyBtn')) {
                prefillName = String($trigger.data('prefill-name') || '').trim();
                if (!prefillName) {
                    const $dropdownBtn = $trigger.closest('.party-dropdown-wrapper').find('#partyDropdownBtn').first();
                    prefillName = $dropdownBtn.is('input, textarea')
                        ? String($dropdownBtn.val() || '').trim()
                        : String($dropdownBtn.text() || '').trim();
                }
            }
            showPartyModal($trigger, prefillName);
        });

        $(document).on('click.sharedItemModalOpen', '.open-item-modal', function (event) {
            event.preventDefault();
            const rowIndex = $(this).closest('tr.item-row').index();
            showItemModal($(this), rowIndex >= 0 ? rowIndex : null);
        });

        $(document).on('click.sharedPartySave', '#btnSaveParty', function (event) {
            event.preventDefault();
            saveParty(true);
        });

        $(document).on('click.sharedPartySaveNew', '#btnSaveNewParty', function (event) {
            event.preventDefault();
            saveParty(false);
        });

        $(document).on('click.sharedPartyGroupOpen', '#addNewGroupBtn', function (event) {
            event.preventDefault();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('partyGroupModal')).show();
        });

        $(document).on('click.sharedPartyGroupSave', '#saveGroupBtn', function (event) {
            event.preventDefault();
            savePartyGroup();
        });

        $(document).on('click.sharedPartyGroupToggle', '#partyGroupTrigger', function (event) {
            event.preventDefault();
            $('#partyGroupMenu').toggleClass('d-none');
        });

        $(document).on('click.sharedPartyGroupSelect', '#partyGroupList [data-group]', function (event) {
            event.preventDefault();
            const groupName = $(this).data('group') || $(this).text().trim();
            $('#partyGroupInput').val(groupName);
            $('#partyGroupText').text(groupName);
            $('#partyGroupMenu').addClass('d-none');
        });

        $(document).on('click.sharedPartyGroupOutside', function (event) {
            if (!$(event.target).closest('#partyGroupTrigger, #partyGroupMenu').length) {
                $('#partyGroupMenu').addClass('d-none');
            }
        });

        $(document).on('change.sharedCreditToggle', '#creditLimitSwitch', function () {
            $('#creditLimitAmountWrap').toggleClass('is-hidden', !this.checked);
        });

        $(document).on('change.sharedTransactionType', '#toReceive, #toPay', function () {
            if (this.checked) {
                $('#toReceive, #toPay').not(this).prop('checked', false);
            }
            const value = $('#toReceive').is(':checked') ? 'receive' : ($('#toPay').is(':checked') ? 'pay' : '');
            $('#transactionTypeValue').val(value);
        });

        $(document).on('change.sharedItemType', '#newItemTypeToggle', syncItemType);

        $(document).on('click.sharedWholesaleToggle', '#toggleWholesalePricing', function (event) {
            event.preventDefault();
            const $pricing = $('.wholesale-pricing');
            const willShow = $pricing.hasClass('d-none');
            $pricing.toggleClass('d-none', !willShow);
            $(this).text(willShow ? 'Hide Wholesale Price' : '+ Add Wholesale Price');
        });

        $(document).on('click.sharedCategoryTrigger', '#newItemCategory', function () {
            if (!cachedUnits.length) {
                fetchUnits();
            }
        });

        $(document).on('change.sharedCategoryAdd', '#newItemCategory', function () {
            if ($(this).val() === '__add_new__') {
                $(this).val('');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('addCategoryModal')).show();
            }
        });

        $(document).on('click.sharedCategorySave', '#saveQuickCategoryBtn', function (event) {
            event.preventDefault();
            saveQuickCategory();
        });

        $(document).on('click.sharedUnitModalOpen', '#openAddUnitModalBtn', function (event) {
            event.preventDefault();
            $('#quickUnitName, #quickUnitShortName').val('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addUnitModal')).show();
            setTimeout(() => $('#quickUnitName').trigger('focus'), 150);
        });

        $(document).on('click.sharedUnitSave', '#saveQuickUnitBtn', function (event) {
            event.preventDefault();
            saveQuickUnit();
        });

        $(document).on('click.sharedUnitButton', '#newItemUnitBtn', function (event) {
            event.preventDefault();
            populateUnitSelectorOptions();
            $('#newItemBaseUnitSelect').val($('#newItemUnit').val() || '');
            $('#newItemSecondaryUnitSelect').val($('#newItemSecondaryUnit').val() || '');
            $('#newItemUnitConversionInput').val($('#newItemUnitConversionRate').val() || '0');
            updateUnitButtonLabel();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).show();
        });

        $(document).on('change.sharedUnitSelectionPreview', '#newItemBaseUnitSelect, #newItemSecondaryUnitSelect', function () {
            const baseUnit = String($('#newItemBaseUnitSelect').val() || '').trim().toUpperCase();
            const secondaryUnit = String($('#newItemSecondaryUnitSelect').val() || '').trim().toUpperCase();
            $('.base-unit-preview').text(baseUnit ? `1 ${baseUnit}` : '1 Base Unit');
            $('.secondary-unit-preview').text(secondaryUnit || 'Secondary Unit');
        });

        $(document).on('click.sharedUnitSelectionSave', '#saveSelectedUnitsBtn', function (event) {
            event.preventDefault();
            const baseUnit = String($('#newItemBaseUnitSelect').val() || '').trim().toUpperCase();
            const secondaryUnit = String($('#newItemSecondaryUnitSelect').val() || '').trim().toUpperCase();
            $('#newItemUnit').val(baseUnit);
            $('#newItemSecondaryUnit').val(secondaryUnit);
            $('#newItemUnitConversionRate').val($('#newItemUnitConversionInput').val() || 0);
            updateUnitButtonLabel();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).hide();
        });

        $(document).on('click.sharedOpenAddUnitFromSelector', '.open-add-unit-from-selector', function (event) {
            event.preventDefault();
            reopenUnitSelectorAfterQuickAdd = true;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).hide();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addUnitModal')).show();
        });

        $(document).on('click.sharedAssignCode', '#assignItemCodeBtn', function (event) {
            event.preventDefault();
            $('#newItemCode').val(generateItemCode($('#newItemName').val()));
        });

        $(document).on('click.sharedItemSave', '#saveNewItemBtn', function (event) {
            event.preventDefault();
            saveItem();
        });

        $(document).on('click.sharedItemImageOpen', '.open-item-image-picker', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const input = document.getElementById('newItemImage');
            if (input) {
                input.click();
            }
        });

        $(document).on('click.sharedItemImageInput', '#newItemImage', function (event) {
            event.stopPropagation();
        });

        $(document).on('change.sharedItemImagePreview', '#newItemImage', function () {
            const file = this.files && this.files[0];
            const thumb = document.getElementById('newItemImageThumb');
            const label = document.getElementById('newItemImageLabel');
            clearItemImagePreview();

            if (!thumb || !label) {
                return;
            }

            if (!file) {
                thumb.innerHTML = '<i class="fa-regular fa-image fa-2x text-secondary"></i>';
                thumb.style.border = '1.5px solid #93c5fd';
                label.textContent = 'Click to choose image';
                return;
            }

            label.textContent = file.name;
            currentItemImageObjectUrl = URL.createObjectURL(file);
            thumb.innerHTML = `<img src="${currentItemImageObjectUrl}" alt="${escapeHtml(file.name)}" style="width:100%;height:100%;object-fit:cover;">`;
            thumb.style.border = '1.5px solid #2563eb';
        });

        $(document).on('click.sharedStockImagesOpen', '.open-item-stock-images-picker', function (event) {
            event.preventDefault();
            const input = document.getElementById('newItemStockImages');
            if (input) {
                input.click();
            }
        });

        $(document).on('change.sharedStockImagesPreview', '#newItemStockImages', function () {
            renderStockImagePreviews(Array.from(this.files || []));
        });

        $(document).on('shown.bs.modal.sharedPartyModal', '#addPartyModal', function () {
            resetPartyTabs();
            setTimeout(resetPartyTabs, 20);
        });

        $(document).on('shown.bs.modal.sharedItemModal', '#addItemModal', function () {
            resetItemTabs();
            setTimeout(resetItemTabs, 20);
        });

        $(document).on('click.sharedPickerClose', function (event) {
            if (!$(event.target).closest('.item-picker').length) {
                $('.item-picker-panel').removeClass('open').hide();
            }
        });

        $(window).on('resize.sharedPicker scroll.sharedPicker', function () {
            $('.item-picker').each(function () {
                positionPanel($(this));
            });
        });

        $(window).on('partiesUpdated.sharedPartyRefresh', refreshPartyDropdowns);
    }

    function initialize() {
        window.partyGroupStoreUrl = window.partyGroupStoreUrl || '/dashboard/party-groups';
        window.items = Array.isArray(window.items) ? window.items : [];
        window.parties = Array.isArray(window.parties) ? window.parties : [];
        refreshPartyDropdowns();
        enhanceAllItemPickers();
        fetchUnits();
        resetPartyTabs();
        resetItemTabs();

        const observer = new MutationObserver(function () {
            enhanceAllItemPickers();
            syncAllRowUnitSelects();
        });
        const rowsRoot = document.querySelector('.item-rows');
        if (rowsRoot) {
            observer.observe(rowsRoot, { childList: true });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindEvents();
        initialize();
    });
})();
