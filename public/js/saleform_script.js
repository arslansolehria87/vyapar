function initializeForm(context) {
    const $ctx = $(context);
    let lastBrokerRateValue = 0;
    let previousBrokerageType = ($ctx.find('.brokerage-type').val() || '').toString();
    const hasCustomPartyDropdown = $ctx.find('.party-id').length > 0;
    const $paidInput = $ctx.find('.received-amount, .advance-amount').first();
    const defaultPaymentDirection = 'payment_in';

    const baseItems = Array.isArray(window.items) ? window.items : [];
    const itemFormSettingsSnapshot = (() => {
        const raw = window.itemFormSettings;
        if (raw && typeof raw === 'object') {
            return raw;
        }
        return {};
    })();
    const itemEnable = !(
        itemFormSettingsSnapshot.enable_item === false ||
        String(itemFormSettingsSnapshot.enable_item || '1') === '0'
    );
    const sellType = String(itemFormSettingsSnapshot.sell_type || 'both').toLowerCase();
    const normalizeItemType = (item = {}) => String(item.type || item.item_type || item.kind || 'product').toLowerCase();
    const isItemAllowedBySettings = (item = {}) => {
        if (!itemEnable) return false;
        const type = normalizeItemType(item);
        if (sellType === 'product') {
            return type !== 'service';
        }
        if (sellType === 'service') {
            return type === 'service';
        }
        return true;
    };
    const filterItemsBySettings = (items = []) => (Array.isArray(items) ? items : []).filter(isItemAllowedBySettings);
    const getUrlDocType = () => new URLSearchParams(window.location.search).get('type') || '';
    const getActiveDocType = () => String($ctx.find('.doc-type').val() || window.docType || getUrlDocType() || 'invoice');
    const currentDocType = getActiveDocType();
    window.docType = currentDocType;
    const isDuplicateSaleMode = Boolean(window.isDuplicateSaleMode);
    let termsConditionTemplates = Array.isArray(window.transactionTermsTemplates) ? window.transactionTermsTemplates : [];
    const termsConditionTypeLabels = {
        invoice: 'Sale Invoice',
        sale_order: 'Sale Order',
        delivery_challan: 'Delivery Challan',
        estimate: 'Estimation/Quotation',
        purchase_bill: 'Purchase Bill',
        purchase_order: 'Purchase Order',
        proforma: 'Proforma Invoice',
        sale_return: 'Sale Return',
        pos: 'Sale Invoice',
    };
    window.saleUnits = Array.isArray(window.saleUnits) ? window.saleUnits : [];
    const getItemMeta = (item = {}) => {
        const plainLabel = item.name || "";
        const richLabel = `${plainLabel} | Sale: ${item.sale_price ?? item.price ?? 0} | Stock: ${item.opening_qty ?? 0} | Location: ${item.location ?? ""}`;
        const categoryLabel = item.category_name || (item.category && item.category.name) || item.category || item.category_id || '';
        const itemCode = item.item_code || item.code || '';
        const description = item.description || item.item_description || '';
        const discount = item.discount ?? item.sale_discount ?? 0;
        return { plainLabel, richLabel, categoryLabel, itemCode, description, discount };
    };

    const buildItemOptionsHtml = (items = []) => {
        return filterItemsBySettings(items).map(item => {
            const { plainLabel, richLabel, categoryLabel, itemCode, description, discount } = getItemMeta(item);
            const itemType = normalizeItemType(item);
            return `<option value="${item.id}" data-price="${item.price ?? ""}" data-sale-price="${item.sale_price ?? ""}" data-stock="${item.opening_qty ?? ""}" data-location="${item.location ?? ""}" data-label="${plainLabel}" data-rich-label="${richLabel}" data-unit="${item.unit || ''}" data-weight="${item.weight ?? 0}" data-category="${categoryLabel}" data-item-code="${itemCode}" data-description="${description}" data-discount="${discount}" data-bag-weight="${item.bag_weight ?? ''}" data-type="${itemType}">${richLabel}</option>`;
        }).join('');
    };

    const getDomItems = () => {
        const domItems = [];
        $ctx.find('.item-name option').each(function () {
            const value = $(this).attr('value');
            if (!value) return;
            domItems.push({
                id: value,
                name: $(this).attr('data-label') || $(this).text().trim(),
                type: $(this).attr('data-type') || 'product',
                item_code: $(this).attr('data-item-code') || '',
                description: $(this).attr('data-description') || '',
                sale_price: parseFloat($(this).attr('data-sale-price') || $(this).attr('data-price') || 0) || 0,
                purchase_price: parseFloat($(this).attr('data-purchase-price') || 0) || 0,
                opening_qty: parseFloat($(this).attr('data-stock') || 0) || 0,
                location: $(this).attr('data-location') || '',
                unit: $(this).attr('data-unit') || '',
                weight: parseFloat($(this).attr('data-weight') || 0) || 0,
                category_name: $(this).attr('data-category') || '',
                bag_weight: parseFloat($(this).attr('data-bag-weight') || 0) || 0
            });
        });
        return domItems;
    };

    const dedupeItemsById = (items = []) => {
        const seen = new Set();
        return (items || []).reduce((acc, item) => {
            const id = String(item?.id ?? '');
            if (!id || seen.has(id)) {
                return acc;
            }
            seen.add(id);
            acc.push(item);
            return acc;
        }, []);
    };

    const getSourceItems = () => {
        const latestItems = Array.isArray(window.items) ? window.items : baseItems;
        if (latestItems !== baseItems) {
            const filteredLatestItems = filterItemsBySettings(latestItems);
            baseItems.splice(0, baseItems.length, ...filteredLatestItems);
            return filteredLatestItems;
        }

        if (latestItems.length) {
            return filterItemsBySettings(latestItems);
        }

        const domItems = getDomItems();
        if (domItems.length) {
            const filteredDomItems = filterItemsBySettings(domItems);
            baseItems.splice(0, baseItems.length, ...filteredDomItems);
            window.items = filteredDomItems;
            return filteredDomItems;
        }

        return [];
    };

    const getAvailableCategories = () => {
        return Array.from(new Set(
            getSourceItems()
                .map(item => getItemMeta(item).categoryLabel)
                .filter(Boolean)
                .map(value => String(value).trim())
                .filter(Boolean)
        )).sort((a, b) => a.localeCompare(b));
    };

    const buildCategoryOptionsHtml = (selectedValue = '') => {
        const normalizedSelected = String(selectedValue || '').trim();
        const options = ['<option value="">Select Category</option>'];
        getAvailableCategories().forEach(category => {
            const isSelected = category === normalizedSelected ? ' selected' : '';
            options.push(`<option value="${category}"${isSelected}>${category}</option>`);
        });
        return options.join('');
    };

    const syncRowCategoryOptions = ($row, selectedValue = '') => {
        const $category = $row.find('.item-category');
        if (!$category.length) {
            return;
        }
        $category.html(buildCategoryOptionsHtml(selectedValue));
    };

    const updateRowDiscountFromPercent = ($row) => {
        const qty = parseFloat($row.find('.item-qty').val() || 0) || 0;
        const price = parseFloat($row.find('.item-rate').val() || 0) || 0;
        const netW = parseFloat($row.find('.net-w-input').val() || 0) || 0;
        const discountPct = parseFloat($row.find('.item-discount-pct').val() || 0) || 0;
        const baseAmount = (netW > 0 ? netW : qty) * price;
        const discountAmount = baseAmount > 0 ? (baseAmount * discountPct) / 100 : 0;
        $row.find('.item-discount').val(discountAmount.toFixed(2));
    };

    const updateRowDiscountPercentFromAmount = ($row) => {
        const qty = parseFloat($row.find('.item-qty').val() || 0) || 0;
        const price = parseFloat($row.find('.item-rate').val() || 0) || 0;
        const netW = parseFloat($row.find('.net-w-input').val() || 0) || 0;
        const discountAmount = parseFloat($row.find('.item-discount').val() || 0) || 0;
        const baseAmount = (netW > 0 ? netW : qty) * price;
        const discountPct = baseAmount > 0 ? (discountAmount / baseAmount) * 100 : 0;
        $row.find('.item-discount-pct').val(discountAmount > 0 ? discountPct.toFixed(2) : '');
    };

    const updateRowTaxFromPercent = ($row) => {
        const qty = parseFloat($row.find('.item-qty').val() || 0) || 0;
        const rate = parseFloat($row.find('.item-rate').val() || 0) || 0;
        const netW = parseFloat($row.find('.net-w-input').val() || 0) || 0;
        const discountAmount = parseFloat($row.find('.item-discount').val() || 0) || 0;
        const taxPct = parseFloat($row.find('.item-tax-pct').val() || 0) || 0;
        const taxableAmount = Math.max(((netW > 0 ? netW : qty) * rate) - discountAmount, 0);
        const taxAmount = taxableAmount > 0 ? (taxableAmount * taxPct) / 100 : 0;
        $row.find('.item-tax-amount').val(taxAmount.toFixed(2));
    };

    const updateRowTaxPercentFromAmount = ($row) => {
        const qty = parseFloat($row.find('.item-qty').val() || 0) || 0;
        const rate = parseFloat($row.find('.item-rate').val() || 0) || 0;
        const netW = parseFloat($row.find('.net-w-input').val() || 0) || 0;
        const discountAmount = parseFloat($row.find('.item-discount').val() || 0) || 0;
        const taxAmount = parseFloat($row.find('.item-tax-amount').val() || 0) || 0;
        const taxableAmount = Math.max(((netW > 0 ? netW : qty) * rate) - discountAmount, 0);
        const taxPct = taxableAmount > 0 ? (taxAmount / taxableAmount) * 100 : 0;
        $row.find('.item-tax-pct').val(taxAmount > 0 ? taxPct.toFixed(2) : '');
    };

    const setPartyDropdownDisplay = (value = '') => {
        const $partyField = $ctx.find('#partyDropdownBtn').first();
        const displayValue = value || 'Select Party';
        if (!$partyField.length) {
            return;
        }
        if ($partyField.is('input, textarea, select')) {
            $partyField.val(displayValue);
            return;
        }
        $partyField.text(displayValue);
    };

    const getPartyDropdownDisplay = () => {
        const $partyField = $ctx.find('#partyDropdownBtn').first();
        if (!$partyField.length) {
            return '';
        }
        const rawValue = $partyField.is('input, textarea, select')
            ? ($partyField.val() || '')
            : ($partyField.text() || '');
        const displayValue = String(rawValue).trim();
        return displayValue === 'Select Party' ? '' : displayValue;
    };

    const buildItemPickerRowsHtml = (items = []) => {
        if (!items.length) {
            return '<div class="item-picker-empty">No items found</div>';
        }

        return items.map(item => {
            const plainLabel = item.name || '';
            const itemCode = item.item_code || '';
            const salePrice = parseFloat(item.sale_price ?? item.price ?? 0) || 0;
            const purchasePrice = parseFloat(item.purchase_price ?? 0) || 0;
            const stock = parseFloat(item.opening_qty ?? 0) || 0;
            const stockClass = stock > 0 ? 'pos' : 'zero';
            const bagWeight = parseFloat(item.bag_weight ?? 0) || 0;
            return `
                <div class="item-picker-row item-picker-option" data-id="${item.id}">
                    <div class="item-picker-name">${plainLabel}${itemCode ? `<small>(${itemCode})</small>` : ''}</div>
                    <div>${salePrice.toFixed(2)}</div>
                    <div>${purchasePrice.toFixed(2)}</div>
                    <div class="item-picker-stock ${stockClass}">${stock}</div>
                    <div>${bagWeight.toFixed(2)}</div>
                </div>
            `;
        }).join('');
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const $closeIcon = $('.close-app-icon');
    const selectedImages = [];
    const selectedDocuments = [];
    const existingImages = [];
    const existingDocuments = [];
    const $imageFilesList = $ctx.find('.image-files-list');
    const $documentFilesList = $ctx.find('.document-files-list');
    const termsConditionModalEl = document.getElementById('termsConditionModal');
    const termsConditionModal = termsConditionModalEl ? bootstrap.Modal.getOrCreateInstance(termsConditionModalEl) : null;
    const uiFind = (selector) => {
        const $local = $ctx.find(selector);
        return $local.length ? $local : $(selector);
    };

    const normalizeTermsConditionTemplates = (templates = []) => {
        if (!Array.isArray(templates)) {
            return [];
        }

        const seen = new Set();
        return templates.reduce((acc, template) => {
            if (!template || typeof template !== 'object') {
                return acc;
            }

            const name = String(template.name || '').trim();
            const description = String(template.description || '').trim();
            const applicableFor = Array.isArray(template.applicable_for)
                ? template.applicable_for.map(value => String(value || '').trim()).filter(Boolean)
                : [];

            if (!name || !description) {
                return acc;
            }

            const key = `${name}__${description}`;
            if (seen.has(key)) {
                return acc;
            }
            seen.add(key);

            acc.push({ name, description, applicable_for: applicableFor });
            return acc;
        }, []);
    };

    const getTermsTemplateMatchesCurrentDoc = (template) => {
        const applicable = Array.isArray(template?.applicable_for) ? template.applicable_for : [];
        return !applicable.length || applicable.includes(currentDocType);
    };

    const renderTermsConditionOptions = (selectedName = '') => {
        const $select = $ctx.find('.terms-condition-select');
        if (!$select.length) return;

        const options = ['<option value="">Select Terms</option>'];
        normalizeTermsConditionTemplates(termsConditionTemplates)
            .filter(getTermsTemplateMatchesCurrentDoc)
            .forEach(template => {
                const selected = template.name === selectedName ? ' selected' : '';
                options.push(`<option value="${template.name.replace(/"/g, '&quot;')}"${selected}>${template.name}</option>`);
            });
        options.push('<option value="__add_new__">+ Add Terms & Conditions</option>');
        $select.html(options.join(''));
    };

    const setTermsConditionSelection = (name = '', description = '') => {
        const normalizedName = String(name || '').trim();
        const normalizedDescription = String(description || '').trim();
        if (normalizedName && normalizedDescription) {
            const exists = normalizeTermsConditionTemplates(termsConditionTemplates).some(template => (
                template.name === normalizedName && template.description === normalizedDescription
            ));
            if (!exists) {
                termsConditionTemplates = normalizeTermsConditionTemplates([
                    ...termsConditionTemplates,
                    { name: normalizedName, description: normalizedDescription, applicable_for: [currentDocType] }
                ]);
            }
        }

        renderTermsConditionOptions(normalizedName);
        $ctx.find('.terms-condition-select').val(normalizedName).trigger('change');
        $ctx.find('.terms-condition-text').val(normalizedDescription);
    };

    termsConditionTemplates = normalizeTermsConditionTemplates(termsConditionTemplates);

    const itemRoutes = Object.assign({
        index: '/dashboard/items',
        store: '/dashboard/items',
        categoryStore: '/dashboard/items/category',
        unitsIndex: '/dashboard/items/units',
        unitsStore: '/dashboard/items/units'
    }, window.itemRoutes || {});
    const $categoryModal = $('#addCategoryModal');
    const $unitModal = $('#addUnitModal');

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
            .then(async response => {
                const text = await response.text();
                const data = parseJsonSafely(text);

                if (!response.ok) {
                    const message = data?.message
                        || data?.error
                        || (text && text.trim().startsWith('<!DOCTYPE') ? `Request failed with status ${response.status}. Server returned HTML instead of JSON.` : `Request failed with status ${response.status}.`);
                    throw new Error(message);
                }

                if (data === null) {
                    throw new Error('Server response was not valid JSON.');
                }

                return data;
            });
    }

    const defaultSaleFormSettings = {
        transaction_header: {
            invoice_number_enabled: true,
            transaction_time_enabled: false,
            cash_sale_default: false,
            billing_name_enabled: true,
            customer_po_enabled: false
        },
        items_table: {
            free_item_qty_enabled: false,
            count_enabled: false,
            count_label: 'Count'
        },
        more_transaction_features: {
            terms_conditions_enabled: true,
            due_dates_payment_terms_enabled: true,
            quick_entry: false,
            link_payment_to_invoices: true,
            passcode_enabled: false,
            transaction_passcode_hash: null,
            do_not_show_invoice_preview: false
        },
        transaction_totals: {
            discount_enabled: true,
            tax_enabled: true,
            round_total_enabled: true,
            round_total_mode: 'down-to',
            round_total_precision: 100
        },
        sale_prefix: {
            enabled: true,
            active: 'INV',
            options: ['INV']
        },
        transaction_prefixes: {
            sale: { active: 'INV', options: ['INV'] },
            credit_note: { active: 'CN', options: ['CN'] },
            sale_order: { active: 'SO', options: ['SO'] },
            purchase_order: { active: 'PO', options: ['PO'] },
            estimate: { active: 'EST', options: ['EST'] },
            proforma_invoice: { active: 'PI', options: ['PI'] },
            delivery_challan: { active: 'DC', options: ['DC'] },
            payment_in: { active: 'PIN', options: ['PIN'] }
        },
        invoice_fields: {
            custom_field_1: {
                enabled: false,
                label: 'Additional Field 1'
            },
            date_field_2: {
                enabled: false,
                label: 'Date Field 2',
                format: 'dd/mm/yyyy'
            }
        },
        quick_entry: false,
        link_payment_to_invoices: true,
        payment_terms: {
            enabled: true,
            name: 'Net 15',
            days: 15
        },
        transportation_details: {
            enabled: false,
            fields: [
                { key: 'field_1', label: 'Transport Name', enabled: false, show_in_print: true },
                { key: 'field_2', label: 'Vehicle Number', enabled: false, show_in_print: true },
                { key: 'field_3', label: 'Delivery Date', enabled: false, show_in_print: true },
                { key: 'field_4', label: 'Delivery Location', enabled: false, show_in_print: true },
                { key: 'field_5', label: 'Field 5', enabled: false, show_in_print: true }
            ]
        },
        additional_charges: {
            enabled: true,
            items: [
                { key: 'shipping', enabled: true, label: 'Shipping', tax_rate: 'NONE', tax_enabled: false },
                { key: 'packaging', enabled: true, label: 'Packaging', tax_rate: 'NONE', tax_enabled: false },
                { key: 'adjustment', enabled: true, label: 'Adjustment', tax_rate: 'NONE', tax_enabled: false }
            ]
        }
    };

    const defaultItemFormSettings = {
        enable_item: true,
        sell_type: 'both',
        barcode_scan_enabled: false,
        direct_barcode_scan_enabled: false,
        stock_maintenance_enabled: false,
        manufacturing_enabled: false,
        show_low_stock_dialog: false,
        items_unit_enabled: true,
        default_unit_enabled: false,
        item_category_enabled: false,
        party_wise_item_rate_enabled: false,
        description_enabled: false,
        description_label: 'Description',
        item_wise_tax_enabled: false,
        item_wise_discount_enabled: false,
        update_sale_price_from_transaction: false,
        quantity_decimals: 2,
        wholesale_price_enabled: false,
        free_item_qty_enabled: false,
        count_enabled: false,
        count_label: 'Count',
        mrp: {
            enabled: false,
            label: 'MRP',
            calculate_sale_price_from_mrp: false,
            use_mrp_for_batch_tracking: false
        },
        serial_tracking: {
            enabled: false,
            label: 'Serial No.'
        },
        batch_tracking: {
            batch_no: { enabled: false, label: 'Batch No.' },
            exp_date: { enabled: false, label: 'Exp. Date', format: 'mm/yy' },
            mfg_date: { enabled: false, label: 'Mfg. Date', format: 'mm/yy' },
            model_no: { enabled: false, label: 'Model No.' },
            size: { enabled: false, label: 'Size' }
        },
        custom_fields: Array.from({ length: 6 }, (_, index) => ({
            key: `custom_field_${index + 1}`,
            enabled: false,
            label: `Custom Field ${index + 1}`,
            show_in_print: false
        }))
    };

    const mergeDeep = (target, source) => {
        const output = Array.isArray(target) ? [...target] : { ...target };
        if (!source || typeof source !== 'object') {
            return output;
        }
        Object.keys(source).forEach(key => {
            const sourceValue = source[key];
            if (Array.isArray(sourceValue)) {
                output[key] = sourceValue.map(item => (item && typeof item === 'object' ? { ...item } : item));
                return;
            }
            if (sourceValue && typeof sourceValue === 'object') {
                output[key] = mergeDeep(output[key] && typeof output[key] === 'object' ? output[key] : {}, sourceValue);
                return;
            }
            output[key] = sourceValue;
        });
        return output;
    };

    const toBooleanish = (value) => {
        if (typeof value === 'boolean') return value;
        if (typeof value === 'number') return value === 1;
        const normalized = String(value ?? '').trim().toLowerCase();
        if (['1', 'true', 'yes', 'on'].includes(normalized)) return true;
        if (['0', 'false', 'no', 'off', '', 'null', 'undefined'].includes(normalized)) return false;
        return !!value;
    };

    const normalizeSaleFormSettings = (settings = {}) => {
        const merged = mergeDeep(defaultSaleFormSettings, settings || {});
        const prefixOptions = Array.from(new Set(
            (merged.sale_prefix?.options || [])
                .map(value => String(value || '').trim().toUpperCase())
                .filter(Boolean)
        ));
        if (!prefixOptions.length) {
            prefixOptions.push('INV');
        }
        const activePrefix = String(merged.sale_prefix?.active || prefixOptions[0] || 'INV').trim().toUpperCase() || 'INV';
        if (!prefixOptions.includes(activePrefix)) {
            prefixOptions.push(activePrefix);
        }
        merged.sale_prefix.options = prefixOptions;
        merged.sale_prefix.active = activePrefix;
        const saleTxnPrefix = String(merged.transaction_prefixes?.sale?.active || activePrefix).trim().toUpperCase();
        if (saleTxnPrefix) {
            merged.sale_prefix.active = saleTxnPrefix;
            if (!merged.sale_prefix.options.includes(saleTxnPrefix)) {
                merged.sale_prefix.options.push(saleTxnPrefix);
            }
            merged.transaction_prefixes.sale.active = saleTxnPrefix;
        }
        merged.invoice_fields.date_field_2.format = String(merged.invoice_fields?.date_field_2?.format || 'dd/mm/yyyy');
        merged.additional_charges.items = Array.isArray(merged.additional_charges?.items) ? merged.additional_charges.items : [];
        merged.transportation_details.fields = Array.isArray(merged.transportation_details?.fields) ? merged.transportation_details.fields : [];
        merged.quick_entry = toBooleanish(merged.more_transaction_features?.quick_entry ?? merged.quick_entry);
        merged.link_payment_to_invoices = toBooleanish(merged.more_transaction_features?.link_payment_to_invoices ?? merged.link_payment_to_invoices);
        merged.more_transaction_features.passcode_enabled = toBooleanish(merged.more_transaction_features?.passcode_enabled);
        merged.more_transaction_features.quick_entry = merged.quick_entry;
        merged.more_transaction_features.link_payment_to_invoices = merged.link_payment_to_invoices;
        merged.transaction_totals.discount_enabled = toBooleanish(merged.transaction_totals?.discount_enabled);
        merged.transaction_totals.tax_enabled = toBooleanish(merged.transaction_totals?.tax_enabled);
        merged.transaction_totals.round_total_enabled = toBooleanish(merged.transaction_totals?.round_total_enabled);
        merged.transaction_totals.round_total_mode = String(merged.transaction_totals?.round_total_mode || 'down-to');
        merged.transaction_totals.round_total_precision = parseInt(merged.transaction_totals?.round_total_precision || 100, 10) || 100;
        return merged;
    };

    const normalizeItemFormSettings = (settings = {}) => {
        const merged = mergeDeep(defaultItemFormSettings, settings || {});
        merged.custom_fields = Array.isArray(merged.custom_fields) ? merged.custom_fields : [];
        while (merged.custom_fields.length < 6) {
            const index = merged.custom_fields.length + 1;
            merged.custom_fields.push({
                key: `custom_field_${index}`,
                enabled: false,
                label: `Custom Field ${index}`,
                show_in_print: false
            });
        }
        return merged;
    };

    let saleFormSettings = normalizeSaleFormSettings(window.saleFormSettings || {});
    let itemFormSettings = normalizeItemFormSettings(window.itemFormSettings || {});
    let currentSaleAdditionalCharges = {};
    let currentTransportationDetails = {};

    // Define isQuickEntryEnabled early so it's available for addRow()
    const isQuickEntryEnabled = () => !!(saleFormSettings.quick_entry || saleFormSettings.more_transaction_features?.quick_entry);

    const getAdditionalChargeFieldKey = (key = '') => `charge_${String(key || '').trim().toLowerCase()}`;

    const parseTaxRateValue = (label = '') => {
        const match = String(label || '').match(/(\d+(?:\.\d+)?)\s*%/);
        return match ? (parseFloat(match[1]) || 0) : 0;
    };

    const formatDateByPattern = (value = '', pattern = 'dd/mm/yyyy') => {
        const raw = String(value || '').trim();
        if (!raw) return '';
        const parsed = normalizeDateForApi(raw);
        if (!parsed) return raw;
        const parts = parsed.split('-');
        if (parts.length !== 3) return raw;
        const [year, month, day] = parts;
        switch (pattern) {
            case 'yyyy/mm/dd':
                return `${year}/${month}/${day}`;
            case 'mm/yyyy':
                return `${month}/${year}`;
            case 'dd-mm-yyyy':
                return `${day}-${month}-${year}`;
            case 'dd/mm/yyyy':
            default:
                return `${day}/${month}/${year}`;
        }
    };

    const getSelectedPrefixOption = () => {
        const selected = String($ctx.find('.sale-prefix-select').val() || saleFormSettings.sale_prefix.active || 'INV').trim().toUpperCase();
        return selected || 'INV';
    };

    const updatePrefixPreview = () => {
        const prefix = getSelectedPrefixOption();
        const currentNumber = String(uiFind('.bill-number').val() || '').trim();
        const numericPart = currentNumber.replace(/^[A-Z]+-?/i, '') || '1';
        uiFind('.settings-prefix-preview').text(`${prefix}-${numericPart}`);
    };

    const renderTransactionTimeDisplay = () => {
        const enabled = !!saleFormSettings.transaction_header?.transaction_time_enabled;
        const $group = uiFind('.transaction-time-group');
        const $display = uiFind('.transaction-time-display');
        if (!$group.length || !$display.length) return;
        if (!enabled) {
            $group.addClass('d-none');
            return;
        }
        const rawTime = window.editSaleData?.created_at || window.editSaleData?.updated_at || new Date();
        const dt = new Date(rawTime);
        const text = Number.isNaN(dt.getTime())
            ? new Date().toLocaleTimeString('en-PK', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'Asia/Karachi' })
            : dt.toLocaleTimeString('en-PK', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'Asia/Karachi' });
        $display.val(text);
        $group.removeClass('d-none');
    };

    const renderTransactionTotalsControls = () => {
        const totalsSettings = saleFormSettings.transaction_totals || {};
        const discountEnabled = !!totalsSettings.discount_enabled;
        const taxEnabled = !!totalsSettings.tax_enabled;
        const roundTotalEnabled = !!totalsSettings.round_total_enabled;

        const $discountRow = uiFind('.transaction-discount-row, .calc-row.transaction-discount-row').first();
        const $taxRow = uiFind('.transaction-tax-row, .calc-row.transaction-tax-row').first();
        const $roundRow = uiFind('.transaction-round-total-row, .calc-row.transaction-round-total-row').first();
        const $roundCheck = uiFind('.round-off-check');
        const $roundOptions = uiFind('#roundTotalOptions');
        const $roundMode = uiFind('#roundTotalModeSelect');
        const $roundPrecision = uiFind('#roundTotalPrecisionSelect');

        $discountRow.toggleClass('d-none', !discountEnabled);
        $taxRow.toggleClass('d-none', !taxEnabled);
        $roundRow.toggleClass('d-none', !roundTotalEnabled);
        $roundCheck.prop('checked', roundTotalEnabled);
        $roundOptions.toggleClass('d-none', !roundTotalEnabled);
        $roundMode.prop('disabled', !roundTotalEnabled);
        $roundPrecision.prop('disabled', !roundTotalEnabled);

        if (!discountEnabled) {
            $ctx.find('.discount-pct, .discount-rs').val('');
        }
        if (!taxEnabled) {
            $ctx.find('.tax-select').val('0');
            $ctx.find('.tax-amount-display').text('0');
        }
        if (!roundTotalEnabled) {
            $ctx.find('.round-off-val').val('0');
        }
    };

    const renderTransportationFields = () => {
        const $section = $ctx.find('.transportation-details-live-section');
        if (!$section.length) return;
        const settings = saleFormSettings.transportation_details || {};
        const fields = Array.isArray(settings.fields) ? settings.fields.filter(field => settings.enabled && field.enabled) : [];
        if (!fields.length) {
            $section.addClass('d-none').empty();
            return;
        }
        const html = fields.map(field => `
            <div class="party-meta-field header-mini-field transportation-live-field" data-transport-key="${field.key}">
                <div class="floating-input-wrapper">
                    <input type="text" class="meta-control transportation-live-input" data-transport-key="${field.key}" placeholder=" " value="${String(currentTransportationDetails[field.key] || '').replace(/"/g, '&quot;')}">
                    <label>${String(field.label || field.key)}</label>
                </div>
            </div>
        `).join('');
        $section.removeClass('d-none').html(html);
    };

    const serializeTransportationDetails = () => {
        const payload = {};
        $ctx.find('.transportation-live-input').each(function() {
            const key = String($(this).data('transportKey') || '').trim();
            if (!key) return;
            payload[key] = String($(this).val() || '').trim();
        });
        return payload;
    };

    const setDueDaysSelectionValue = (daysValue) => {
        const days = parseInt(daysValue || 0, 10) || 0;
        const $select = uiFind('.due-days-select');
        const $custom = uiFind('.due-days-custom');
        if (!$select.length) return;
        const matchingOption = $select.find('option').filter(function() {
            return String($(this).attr('value') || '') === String(days);
        }).first();

        if (matchingOption.length) {
            $select.val(String(days));
            $custom.addClass('d-none').val('');
        } else {
            $select.val('custom');
            $custom.removeClass('d-none').val(days || '');
        }
        $select.trigger('change');
    };

    const collectAdditionalChargeSettingsFromModal = () => {
        const items = [];
        $('#additionalChargesModal .additional-charge-block').each(function() {
            const $block = $(this);
            items.push({
                key: String($block.data('chargeKey') || '').trim().toLowerCase(),
                enabled: $block.find('.additional-charge-check').is(':checked'),
                label: String($block.find('.additional-charge-input').val() || '').trim(),
                tax_rate: String($block.find('.additional-charge-tax').val() || 'NONE').trim(),
                tax_enabled: $block.find('.additional-charge-tax-check').is(':checked')
            });
        });
        return {
            enabled: $('#additionalChargesToggle').is(':checked'),
            items
        };
    };

    const populateAdditionalChargesModal = () => {
        const settings = normalizeSaleFormSettings(saleFormSettings);
        const additionalCharges = settings.additional_charges || {};
        $('#additionalChargesToggle').prop('checked', !!additionalCharges.enabled);
        $('#additionalChargesModal .additional-charge-block').each(function() {
            const $block = $(this);
            const key = String($block.data('chargeKey') || '').trim().toLowerCase();
            const item = (additionalCharges.items || []).find(entry => String(entry.key || '').trim().toLowerCase() === key) || {};
            const label = item.label || key.charAt(0).toUpperCase() + key.slice(1);
            $block.find('.additional-charge-check').prop('checked', !!item.enabled);
            $block.find('.additional-charge-input').val(label);
            $block.find('.additional-charge-tax').val(item.tax_rate || 'NONE');
            $block.find('.additional-charge-tax-check').prop('checked', !!item.tax_enabled);
            $block.find('.form-check-label.small').text(`Enable tax for ${label}`);
        });
        setAdditionalChargesEditable($('#additionalChargesToggle').is(':checked'));
    };

    const renderAdditionalChargeLiveRows = () => {
        const $section = $ctx.find('.additional-charge-live-section');
        if (!$section.length) return;

        const additionalCharges = saleFormSettings.additional_charges || {};
        const enabledItems = (additionalCharges.items || []).filter(item => additionalCharges.enabled && item.enabled);

        if (!enabledItems.length) {
            $section.addClass('d-none').empty();
            $ctx.find('.additional-charges-summary-text').text('No fields are enabled');
            return;
        }

        $ctx.find('.additional-charges-summary-text').text(`${enabledItems.length} fields are enabled`);

        const rowsHtml = enabledItems.map(item => {
            const fieldKey = getAdditionalChargeFieldKey(item.key);
            const currentValue = currentSaleAdditionalCharges[fieldKey] || {};
            const amount = parseFloat(currentValue.amount || 0) || 0;
            const taxRate = String(currentValue.tax_rate || item.tax_rate || 'NONE');
            return `
                <div class="additional-charge-live-row" data-charge-key="${item.key}">
                    <div class="additional-charge-live-label">${item.label || item.key}</div>
                    <input type="number" class="additional-charge-live-input" data-charge-key="${item.key}" min="0" step="0.01" value="${amount.toFixed(2)}">
                    <select class="additional-charge-live-tax" data-charge-tax-key="${item.key}" ${item.tax_enabled ? '' : 'disabled'}>
                        <option value="NONE" ${taxRate === 'NONE' ? 'selected' : ''}>NONE</option>
                        <option value="GST 5%" ${taxRate === 'GST 5%' ? 'selected' : ''}>GST 5%</option>
                        <option value="GST 12%" ${taxRate === 'GST 12%' ? 'selected' : ''}>GST 12%</option>
                        <option value="GST 18%" ${taxRate === 'GST 18%' ? 'selected' : ''}>GST 18%</option>
                    </select>
                    <span class="fw-semibold additional-charge-live-total" data-charge-total-key="${item.key}">0.00</span>
                </div>
            `;
        }).join('');

        $section.removeClass('d-none').html(rowsHtml);
    };

    const serializeAdditionalCharges = () => {
        const additionalCharges = saleFormSettings.additional_charges || {};
        const items = (additionalCharges.items || []).filter(item => additionalCharges.enabled && item.enabled).map(item => {
            const $row = $ctx.find(`.additional-charge-live-row[data-charge-key="${item.key}"]`);
            const amount = parseFloat($row.find('.additional-charge-live-input').val() || 0) || 0;
            const selectedTaxRate = String($row.find('.additional-charge-live-tax').val() || item.tax_rate || 'NONE');
            return {
                key: item.key,
                label: item.label || item.key,
                enabled: true,
                amount,
                tax_rate: selectedTaxRate,
                tax_enabled: !!item.tax_enabled
            };
        });
        return items;
    };

    const updateAdditionalChargeRowTotals = () => {
        serializeAdditionalCharges().forEach(item => {
            const total = item.tax_enabled ? item.amount + ((item.amount * parseTaxRateValue(item.tax_rate)) / 100) : item.amount;
            $ctx.find(`[data-charge-total-key="${item.key}"]`).text(total.toFixed(2));
        });
    };

    const getAdditionalChargesTotal = () => {
        return serializeAdditionalCharges().reduce((sum, item) => {
            const taxAmount = item.tax_enabled ? ((item.amount * parseTaxRateValue(item.tax_rate)) / 100) : 0;
            return sum + item.amount + taxAmount;
        }, 0);
    };

    const populateDynamicInvoiceFields = () => {
        const $target = $ctx.find('.dynamic-invoice-fields-row');
        if (!$target.length) return;

        const savedFieldValues = window.editSaleData?.details?.invoice_extra_fields || window.editSaleData?.invoice_extra_fields || {};
        const rows = [];
        const customField = saleFormSettings.invoice_fields?.custom_field_1 || {};
        const dateField = saleFormSettings.invoice_fields?.date_field_2 || {};

        if (customField.enabled) {
            rows.push(`
                <div class="party-meta-field header-mini-field settings-custom-field-shell" style="display:block;">
                    <div class="floating-input-wrapper">
                        <input type="text" class="meta-control dynamic-invoice-extra-field" data-field-key="custom_field_1" placeholder=" " value="${String(savedFieldValues.custom_field_1 || '').replace(/"/g, '&quot;')}">
                        <label>${String(customField.label || 'Additional Field 1')}</label>
                    </div>
                </div>
            `);
        }

        if (dateField.enabled) {
            rows.push(`
                <div class="party-meta-field header-mini-field settings-date-field-shell" style="display:block;">
                    <div class="floating-input-wrapper">
                        <input type="text" class="meta-control dynamic-invoice-extra-field dynamic-invoice-date-field" data-field-key="date_field_2" data-date-format="${String(dateField.format || 'dd/mm/yyyy')}" placeholder=" " value="${String(formatDateByPattern(savedFieldValues.date_field_2 || '', dateField.format || 'dd/mm/yyyy')).replace(/"/g, '&quot;')}">
                        <label>${String(dateField.label || 'Date Field 2')}</label>
                    </div>
                </div>
            `);
        }

        $target.html(rows.join(''));
    };

    const serializeInvoiceExtraFields = () => {
        const payload = {};
        $ctx.find('.dynamic-invoice-extra-field').each(function() {
            const key = String($(this).data('fieldKey') || '').trim();
            if (!key) return;
            let value = String($(this).val() || '').trim();
            if (key === 'date_field_2') {
                value = normalizeDateForApi(value);
            }
            payload[key] = value;
        });
        return payload;
    };

    const getPersistedInvoiceThemeState = () => {
        const keys = [];
        const editSaleId = !isDuplicateSaleMode ? window.editSaleData?.id : null;

        if (editSaleId) {
            keys.push(`saleInvoiceTheme:${editSaleId}`);
        }

        keys.push('saleInvoiceTheme:draft');

        for (const key of keys) {
            try {
                const raw = window.localStorage.getItem(key);
                if (!raw) continue;
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') {
                    return parsed;
                }
            } catch (error) {
                // ignore malformed theme payloads
            }
        }

        return null;
    };

    const renderItemSettingsColumns = () => {
        const settings = itemFormSettings || defaultItemFormSettings;
        const showFreeQty = !!(settings.free_item_qty_enabled || saleFormSettings.items_table?.free_item_qty_enabled);
        const extraColumnsEnabled = !!(
            settings.barcode_scan_enabled ||
            settings.direct_barcode_scan_enabled ||
            settings.serial_tracking?.enabled ||
            settings.description_enabled ||
            settings.count_enabled ||
            settings.batch_tracking?.batch_no?.enabled ||
            settings.batch_tracking?.model_no?.enabled ||
            settings.batch_tracking?.exp_date?.enabled ||
            settings.batch_tracking?.mfg_date?.enabled ||
            settings.mrp?.enabled ||
            settings.batch_tracking?.size?.enabled ||
            showFreeQty ||
            (settings.custom_fields || []).some(field => field && field.enabled)
        );
        const mappings = [
            { selector: '.col-barcode-scan', enabled: !!(settings.barcode_scan_enabled || settings.direct_barcode_scan_enabled) },
            { selector: '.col-serial-no', enabled: !!settings.serial_tracking?.enabled, label: settings.serial_tracking?.label || 'Serial No.' },
            { selector: '.col-description', enabled: !!settings.description_enabled, label: settings.description_label || 'Description' },
            { selector: '.col-count', enabled: !!settings.count_enabled, label: settings.count_label || 'Count' },
            { selector: '.col-batch-no', enabled: !!settings.batch_tracking?.batch_no?.enabled, label: settings.batch_tracking?.batch_no?.label || 'Batch No.' },
            { selector: '.col-model-no', enabled: !!settings.batch_tracking?.model_no?.enabled, label: settings.batch_tracking?.model_no?.label || 'Model No.' },
            { selector: '.col-exp-date', enabled: !!settings.batch_tracking?.exp_date?.enabled, label: settings.batch_tracking?.exp_date?.label || 'Exp. Date' },
            { selector: '.col-mfg-date', enabled: !!settings.batch_tracking?.mfg_date?.enabled, label: settings.batch_tracking?.mfg_date?.label || 'Mfg. Date' },
            { selector: '.col-mrp', enabled: !!settings.mrp?.enabled, label: settings.mrp?.label || 'MRP' },
            { selector: '.col-size', enabled: !!settings.batch_tracking?.size?.enabled, label: settings.batch_tracking?.size?.label || 'Size' },
            { selector: '.col-free-qty', enabled: showFreeQty, label: 'Free Qty' },
            { selector: '.col-discount', enabled: !!settings.item_wise_discount_enabled, label: 'DISCOUNT' },
            { selector: '.col-item-tax', enabled: !!settings.item_wise_tax_enabled, label: 'TAX' }
        ];

        mappings.forEach(({ selector, enabled, label }) => {
            const $cells = $ctx.find(selector);
            $cells.toggleClass('d-none', !enabled);
            if (label) {
                $cells.filter('th').each(function() {
                    const $th = $(this);
                    const $mainLabel = $th.find('.header-main-label');
                    if ($mainLabel.length) {
                        $mainLabel.text(label);
                    } else {
                        $th.text(label);
                    }
                });
                $cells.find('input').attr('placeholder', label);
            }
        });

        (settings.custom_fields || []).slice(0, 6).forEach((field, index) => {
            const selector = `.col-custom-field-${index + 1}`;
            const enabled = !!field.enabled;
            const label = String(field.label || `Custom Field ${index + 1}`);
            const $cells = $ctx.find(selector);
            $cells.toggleClass('d-none', !enabled);
            $cells.filter('th').text(label.toUpperCase());
            $cells.find('input').attr('placeholder', label);
        });

        const $itemTable = $ctx.find('.item-table');
        $itemTable.toggleClass('default-item-layout', !extraColumnsEnabled);
    };

    const collectSaleFormSettingsFromUi = () => {
        const existingOptions = Array.from(new Set(
            (uiFind('.settings-prefix-select option').map(function() {
                return String($(this).val() || '').trim().toUpperCase();
            }).get()).filter(Boolean)
        ));
        const typedPrefix = String(uiFind('.settings-prefix-input').val() || '').trim().toUpperCase();
        if (typedPrefix) {
            existingOptions.push(typedPrefix);
        }
        const activePrefix = typedPrefix || String(uiFind('.settings-prefix-select').val() || saleFormSettings.sale_prefix.active || 'INV').trim().toUpperCase();

        return normalizeSaleFormSettings({
            sale_prefix: {
                enabled: uiFind('.settings-prefix-enabled').is(':checked'),
                active: activePrefix,
                options: Array.from(new Set(existingOptions.length ? existingOptions : ['INV']))
            },
            invoice_fields: {
                custom_field_1: {
                    enabled: uiFind('.settings-custom-field-enabled').is(':checked'),
                    label: String(uiFind('.settings-custom-field-label').val() || 'Additional Field 1').trim()
                },
                date_field_2: {
                    enabled: uiFind('.settings-date-field-enabled').is(':checked'),
                    label: String(uiFind('.settings-date-field-label').val() || 'Date Field 2').trim(),
                    format: String(uiFind('.settings-date-field-format').val() || 'dd/mm/yyyy').trim()
                }
            },
            quick_entry: uiFind('.settings-quick-entry').is(':checked'),
            link_payment_to_invoices: uiFind('.settings-link-payments').is(':checked'),
            payment_terms: {
                enabled: uiFind('.settings-payment-terms-enabled').is(':checked'),
                name: String(uiFind('.settings-payment-term-name').val() || '').trim(),
                days: parseInt(uiFind('.settings-payment-term-days').val() || 0, 10) || 0
            },
            transportation_details: saleFormSettings.transportation_details,
            additional_charges: collectAdditionalChargeSettingsFromModal()
        });
    };

    const syncSaleSettingsControls = () => {
        const settings = normalizeSaleFormSettings(saleFormSettings);
        $ctx.find('.invoice-number-group').toggle(!!settings.transaction_header.invoice_number_enabled);
        $ctx.find('.billing-name-group').toggle(!!settings.transaction_header.billing_name_enabled);
        const termsEnabled = !!settings.more_transaction_features.terms_conditions_enabled;
        const $termsGroup = $ctx.find('.terms-condition-group');
        const $actionLayout = $ctx.find('.action-fields-layout.meta-stack-layout');
        const $metaRightStack = $actionLayout.find('.meta-right-stack').first();

        $termsGroup.toggle(termsEnabled);
        if ($actionLayout.length) {
            if (termsEnabled) {
                $actionLayout.removeClass('no-terms-layout').css({
                    gridTemplateColumns: '',
                    gap: ''
                });
                $metaRightStack.css({
                    gridColumn: '',
                    width: '',
                    maxWidth: ''
                });
            } else {
                $actionLayout.addClass('no-terms-layout').css({
                    gridTemplateColumns: 'minmax(0, 1fr)',
                    gap: '0'
                });
                $metaRightStack.css({
                    gridColumn: '1 / -1',
                    width: '100%',
                    maxWidth: '100%'
                });
            }
        }
        $ctx.find('.col-free-qty').toggleClass('d-none', !settings.items_table.free_item_qty_enabled);
        uiFind('.sale-payment-terms-item').toggle(!!settings.more_transaction_features.due_dates_payment_terms_enabled);
        $ctx.find('.deal-days-group').toggle(!!settings.more_transaction_features.due_dates_payment_terms_enabled);
        $ctx.find('.final-due-date-group').show();
        uiFind('.settings-prefix-enabled').prop('checked', !!settings.sale_prefix.enabled);
        uiFind('.settings-prefix-select').html(
            settings.sale_prefix.options.map(option => `<option value="${option}" ${option === settings.sale_prefix.active ? 'selected' : ''}>${option}</option>`).join('')
        );
        uiFind('.settings-prefix-input').val('');
        uiFind('.sale-prefix-select').html(
            settings.sale_prefix.options.map(option => `<option value="${option}" ${option === settings.sale_prefix.active ? 'selected' : ''}>${option}</option>`).join('')
        );
        uiFind('.sale-prefix-select').val(settings.sale_prefix.active);
        uiFind('.settings-custom-field-label').val(settings.invoice_fields.custom_field_1.label || 'Additional Field 1');
        uiFind('.settings-custom-field-enabled').prop('checked', !!settings.invoice_fields.custom_field_1.enabled);
        uiFind('.settings-date-field-label').val(settings.invoice_fields.date_field_2.label || 'Date Field 2');
        uiFind('.settings-date-field-format').val(settings.invoice_fields.date_field_2.format || 'dd/mm/yyyy');
        uiFind('.settings-date-field-enabled').prop('checked', !!settings.invoice_fields.date_field_2.enabled);
        uiFind('.settings-quick-entry').prop('checked', !!settings.quick_entry);
        uiFind('.settings-link-payments').prop('checked', !!settings.link_payment_to_invoices);
        uiFind('.settings-payment-terms-enabled').prop('checked', !!settings.payment_terms.enabled);
        uiFind('.settings-payment-term-name').val(settings.payment_terms.name || '');
        uiFind('.settings-payment-term-days').val(settings.payment_terms.days || '');
        const paymentTermsSummary = settings.payment_terms.enabled
            ? `Default - ${settings.payment_terms.name || 'Net'} ${parseInt(settings.payment_terms.days || 0, 10) || 0}`
            : 'Set payment terms';
        uiFind('.payment-terms-summary-text').text(paymentTermsSummary);
        populateAdditionalChargesModal();
        populateDynamicInvoiceFields();
        renderAdditionalChargeLiveRows();
        renderTransportationFields();
        renderTransactionTotalsControls();
        updateAdditionalChargeRowTotals();
        renderItemSettingsColumns();
        updatePrefixPreview();
        renderTransactionTimeDisplay();
        if (!window.editSaleData && settings.transaction_header.cash_sale_default) {
            const toggle = document.getElementById('saleToggleSwitch');
            if (toggle && !toggle.checked) {
                toggle.checked = true;
                toggle.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    };

    const applySelectedPrefixToBillNumber = (prefix = '', options = {}) => {
        const normalizedPrefix = String(prefix || '').trim().toUpperCase() || 'INV';
        const currentBillNumber = String(uiFind('.bill-number').val() || '').trim();
        const fallbackApply = () => {
            const numericPart = currentBillNumber.replace(/^[A-Z]+-?/i, '')
                || (isDuplicateSaleMode
                    ? String(window.nextInvoiceNumber || '').replace(/^[A-Z]+-?/i, '')
                    : String(window.editSaleData?.id || '').trim())
                || '1';
            uiFind('.bill-number').val(`${normalizedPrefix}-${numericPart}`);
            updatePrefixPreview();
        };

        if ((window.editSaleData?.id && !isDuplicateSaleMode) || !window.saleNextNumberUrl) {
            fallbackApply();
            return;
        }

        const query = new URLSearchParams({
            type: String(window.docType || 'invoice'),
            custom_prefix: normalizedPrefix
        });

        fetchJson(`${window.saleNextNumberUrl}?${query.toString()}`)
            .then(data => {
                const billNumber = String(data?.bill_number || '').trim();
                if (billNumber) {
                    uiFind('.bill-number').val(billNumber);
                    updatePrefixPreview();
                    return;
                }
                fallbackApply();
            })
            .catch(() => fallbackApply())
            .finally(() => {
                if (options.syncSettingsSelect !== false) {
                    uiFind('.settings-prefix-select').val(normalizedPrefix);
                }
            });
    };

    const persistSaleFormSettings = (options = {}) => {
        const payload = collectSaleFormSettingsFromUi();
        return fetchJson(window.saleSettingsUpdateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(data => {
            saleFormSettings = normalizeSaleFormSettings(data?.settings || payload);
            window.saleFormSettings = saleFormSettings;
            syncSaleSettingsControls();
            if (saleFormSettings.payment_terms.enabled) {
                setDueDaysSelectionValue(saleFormSettings.payment_terms.days || 0);
            }
            applySelectedPrefixToBillNumber(saleFormSettings.sale_prefix.active, { syncSettingsSelect: true });
            if (!options.silent) {
                showToast('Sale settings saved successfully.', false);
            }
            return saleFormSettings;
        }).catch(error => {
            if (!options.silent) {
                alert(error.message || 'Unable to save sale settings.');
            }
            throw error;
        });
    };

    const getNormalizedSaleUnits = () => {
        const sourceUnits = Array.isArray(window.saleUnits) ? window.saleUnits : [];
        return sourceUnits.map(unit => {
            const shortName = String(unit.short_name || unit.short || unit.name || '').trim().toUpperCase();
            const name = String(unit.name || shortName || '').trim().toUpperCase();
            return {
                id: unit.id || shortName.toLowerCase(),
                name,
                short_name: shortName || name
            };
        }).filter(unit => unit.short_name);
    };

    const buildUnitOptionsHtml = (selectedUnit = '') => {
        const normalizedSelected = String(selectedUnit || '').trim().toUpperCase();
        const seen = new Set();
        const options = ['<option value="">Select Unit</option>'];

        getNormalizedSaleUnits().forEach(unit => {
            const shortName = unit.short_name;
            if (!shortName || seen.has(shortName)) {
                return;
            }
            seen.add(shortName);
            options.push(`<option value="${shortName}" ${normalizedSelected === shortName ? 'selected' : ''}>${shortName}</option>`);
        });

        if (normalizedSelected && !seen.has(normalizedSelected)) {
            options.push(`<option value="${normalizedSelected}" selected>${normalizedSelected}</option>`);
        }

        options.push('<option value="__add_unit__">+ Add Unit</option>');

        return options.join('');
    };

    function syncItemUnitSelects() {
        $ctx.find('.item-unit').each(function() {
            const $select = $(this);
            const currentValue = String($select.val() || '').trim().toUpperCase();
            $select.html(buildUnitOptionsHtml(currentValue));
            if (currentValue) {
                $select.val(currentValue);
            }
            $select.attr('data-last-value', currentValue);
        });
    }

    function renderNewItemUnitMenu(selectedUnit = '') {
        const normalizedSelected = String(selectedUnit || '').trim().toUpperCase();
        const units = getNormalizedSaleUnits();
        if (!units.length) {
            $('#newItemUnitMenu').html(`
                <li><span class="dropdown-item-text text-muted">No units found</span></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item text-primary fw-semibold" type="button" id="openAddUnitModalBtn">+ Add Unit</button></li>
            `);
            return;
        }
        const itemsHtml = units.map(unit => {
            const label = unit.name && unit.name !== unit.short_name
                ? `${unit.short_name} (${unit.name})`
                : unit.short_name;
            return `
            <li><button class="dropdown-item unit-option ${normalizedSelected === unit.short_name ? 'active' : ''}" type="button" data-unit="${unit.short_name}">${label}</button></li>
        `;
        }).join('');

        $('#newItemUnitMenu').html(`
            ${itemsHtml}
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item text-primary fw-semibold" type="button" id="openAddUnitModalBtn">+ Add Unit</button></li>
        `);
    }

    function updateNewItemUnitButton() {
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

    function populateUnitSelectionModal() {
        const options = ['<option value="">Select Unit</option>'];
        getNormalizedSaleUnits().forEach(unit => {
            const label = unit.name && unit.name !== unit.short_name
                ? `${unit.name} (${unit.short_name})`
                : unit.short_name;
            options.push(`<option value="${unit.short_name}">${label}</option>`);
        });
        const currentBase = $('#newItemBaseUnitSelect').val();
        const currentSecondary = $('#newItemSecondaryUnitSelect').val();
        $('#newItemBaseUnitSelect, #newItemSecondaryUnitSelect').html(options.join(''));
        if (currentBase) $('#newItemBaseUnitSelect').val(currentBase);
        if (currentSecondary) $('#newItemSecondaryUnitSelect').val(currentSecondary);
    }

    function positionItemPickerPanel($row) {
        const $input = $row.find('.item-picker-input');
        const $panel = $row.find('.item-picker-panel');

        if (!$input.length || !$panel.length || !$panel.hasClass('open')) {
            return;
        }

        const inputRect = $input[0].getBoundingClientRect();
        const viewportPadding = 12;
        const maxWidth = Math.min(window.innerWidth - viewportPadding * 2, 760);
        const width = Math.min(maxWidth, Math.max(inputRect.width + 220, 520));
        let left = inputRect.left;

        if (left + width > window.innerWidth - viewportPadding) {
            left = Math.max(viewportPadding, window.innerWidth - width - viewportPadding);
        }

        const top = inputRect.bottom + 4;
        const availableHeight = Math.max(180, window.innerHeight - top - viewportPadding);
        const listMaxHeight = Math.max(120, Math.min(280, availableHeight - 88));

        $panel.css({
            position: 'fixed',
            top: `${top}px`,
            left: `${left}px`,
            right: '',
            width: `${width}px`,
            minWidth: `${width}px`,
            zIndex: 1055,
            display: 'block'
        });

        $panel.find('.item-picker-list').css('max-height', `${listMaxHeight}px`);
    }

    function hideItemPickerPanels() {
        $ctx.find('.item-picker-panel').removeClass('open').css({
            display: 'none',
            top: '',
            left: '',
            right: '',
            width: '',
            minWidth: ''
        });
    }

    function renderItemPicker($row, query = '') {
        const $panel = $row.find('.item-picker-panel');
        const $list = $row.find('.item-picker-list');
        const normalizedQuery = String(query || '').trim().toLowerCase();
        let itemsToUse = getFilteredItems();
        const selectedCategory = String($row.find('.item-category').val() || '').trim().toLowerCase();

        if (!itemsToUse.length) {
            const fallbackItems = [];
            $row.find('.item-name option').each(function () {
                const value = $(this).attr('value');
                if (!value) return;
                fallbackItems.push({
                    id: value,
                    name: $(this).data('label') || $(this).text().trim(),
                    item_code: $(this).data('item-code') || '',
                    description: $(this).data('description') || '',
                    sale_price: $(this).data('sale-price') || $(this).data('price') || 0,
                    purchase_price: $(this).data('purchase-price') || 0,
                    opening_qty: $(this).data('stock') || 0,
                    location: $(this).data('location') || '',
                });
            });
            itemsToUse = fallbackItems;
        }

        if (selectedCategory) {
            itemsToUse = itemsToUse.filter(item => {
                const categoryValue = String(getItemMeta(item).categoryLabel || '').trim().toLowerCase();
                return categoryValue === selectedCategory;
            });
        }

        const filtered = itemsToUse.filter(item => {
            const label = String(item.name || '').toLowerCase();
            const code = String(item.item_code || '').toLowerCase();
            const description = String(item.description || item.item_description || '').toLowerCase();
            return !normalizedQuery ||
                   label.includes(normalizedQuery) ||
                   code.includes(normalizedQuery) ||
                   description.includes(normalizedQuery);
        });

        $list.html(buildItemPickerRowsHtml(filtered));
        $panel.addClass('open').css('display', 'block');
        positionItemPickerPanel($row);
    }

    function syncItemPickerInput($row) {
        const $selected = $row.find('.item-name option:selected');
        const selectedValue = String($selected.val() || '').trim();
        const plainLabel = $selected.data('label') || $selected.text() || '';
        $row.find('.item-picker-input').val(selectedValue ? plainLabel.trim() : '');
    }

    function clearSelectedItemRow($row) {
        if (!$row || !$row.length) {
            return;
        }

        $row.find('.item-name').val('');
        $row.removeAttr('data-bag-weight');
        $row.find('.item-category').val('');
        $row.find('.item-code').val('');
        $row.find('.item-desc').val('');
        $row.find('.item-tafseel').val('');
        $row.find('.gross-w-input').val('0');
        $row.find('.net-w-input').val('0');
        $row.find('.item-rate').val('0');
        $row.find('.item-unit').val('');
        $row.find('.item-discount').val('0');
        $row.find('.item-discount-pct').val('');
        $row.find('.item-amount').val('0.00');
        $row.find('.item-picker-input').val('');
    }

    function setupPartyDropdownSearch() {
        const $partySearchInput = $ctx.find('.party-search-input').first();
        const $partyDropdown = $ctx.find('.party-dropdown-wrapper').first();

        if (!$partySearchInput.length) {
            return;
        }

        $partySearchInput.on('click focus', function(e) {
            e.stopPropagation();
        });

        $partySearchInput.on('input', function() {
            const searchTerm = String($(this).val() || '').trim().toLowerCase();
            $ctx.find('.party-option').each(function() {
                const $option = $(this);
                const partyName = String($.trim($option.data('name') || $option.find('.party-option-name').text() || '')).toLowerCase();
                const partyPhone = String($option.data('phone') || '').toLowerCase();
                const isVisible = !searchTerm || partyName.includes(searchTerm) || partyPhone.includes(searchTerm);
                $option.closest('li').toggleClass('d-none', !isVisible);
            });
        });

        if ($partyDropdown.length) {
            $partyDropdown.on('show.bs.dropdown', function() {
                setTimeout(() => {
                    $partySearchInput.trigger('focus').trigger('select');
                }, 100);
            });
            $partyDropdown.on('hide.bs.dropdown', function() {
                $partySearchInput.val('');
                $ctx.find('.party-option').closest('li').removeClass('d-none');
            });
        }

        $ctx.on('click', '.dropdown-header-search', function(e) {
            e.stopPropagation();
        });

        $ctx.on('click', '.party-search-input', function(e) {
            e.stopPropagation();
        });

        $ctx.on('keydown keyup', '.party-search-input', function(e) {
            e.stopPropagation();
        });
    }

    function setupBrokerDropdownSearch() {
        const ensureNoResultsItem = ($menu) => {
            let $noResults = $menu.find('.broker-no-results').first();
            if (!$noResults.length) {
                $noResults = $('<li class="broker-no-results d-none"><span class="dropdown-item text-muted">No brokers found</span></li>');
                const $addNewBrokerItem = $menu.find('#addNewBrokerBtn').closest('li').first();
                if ($addNewBrokerItem.length) {
                    $addNewBrokerItem.before($noResults);
                } else {
                    $menu.append($noResults);
                }
            }
            return $noResults;
        };

        const filterBrokerOptions = ($wrapper, searchTerm = '') => {
            const $menu = $wrapper.find('.broker-dropdown-menu, #brokerDropdownMenu').first();
            if (!$menu.length) return;
            const $noResults = ensureNoResultsItem($menu);
            const query = String(searchTerm || '').trim().toLowerCase();
            let anyVisible = false;

            $menu.find('.broker-option').each(function() {
                const $option = $(this);
                const brokerName = String($.trim($option.data('name') || $option.find('.broker-option-name').text() || '')).toLowerCase();
                const brokerCity = String($.trim($option.data('city') || $option.find('.broker-option-city').text() || '')).toLowerCase();
                const brokerPhone = String($.trim($option.data('phone') || '')).toLowerCase();
                const optionText = [brokerName, brokerCity, brokerPhone].filter(Boolean).join(' ');
                const isVisible = !query || optionText.includes(query);
                $option.closest('li').toggleClass('d-none', !isVisible);
                if (isVisible) anyVisible = true;
            });

            $noResults.toggleClass('d-none', anyVisible);
        };

        $ctx.on('click focus', '.broker-search-input', function(e) {
            e.stopPropagation();
        });

        $ctx.on('input', '.broker-search-input', function() {
            filterBrokerOptions($(this).closest('.broker-dropdown-wrapper'), $(this).val());
        });

        $ctx.on('show.bs.dropdown', '.broker-dropdown-wrapper', function() {
            const $wrapper = $(this);
            const $input = $wrapper.find('.broker-search-input').first();
            setTimeout(() => {
                $input.trigger('focus').trigger('select');
                filterBrokerOptions($wrapper, $input.val());
            }, 100);
        });

        $ctx.on('hide.bs.dropdown', '.broker-dropdown-wrapper', function() {
            const $wrapper = $(this);
            const $input = $wrapper.find('.broker-search-input').first();
            const $menu = $wrapper.find('.broker-dropdown-menu, #brokerDropdownMenu').first();
            $input.val('');
            $menu.find('.broker-option').closest('li').removeClass('d-none');
            $menu.find('.broker-no-results').addClass('d-none');
        });

        $ctx.on('keydown keyup', '.broker-search-input', function(e) {
            e.stopPropagation();
        });
    }

    function updateBrokerRemaining() {
        const total = parseFloat($ctx.find('#brokerTotalBrokerage').val() || 0) || 0;
        const paid = parseFloat($ctx.find('#brokerPaidBrokerage').val() || 0) || 0;
        const remaining = Math.max(0, total - paid);
        $ctx.find('#brokerRemainingBrokerage').val(remaining.toFixed(2));
    }

    function resetBrokerModal() {
        const $modal = $ctx.find('#brokerModal');
        $modal.find('#brokerForm')[0].reset();
        $modal.find('#brokerStatus').prop('checked', true);
        $modal.find('#brokerTotalBrokerage').val('0');
        $modal.find('#brokerPaidBrokerage').val('0');
        $modal.find('#brokerRemainingBrokerage').val('0.00');
    }

    function refreshItemsList(selectedItem = null) {
        const productUrl = `${itemRoutes.index}?json=1&include_inactive=1`;
        const serviceBaseUrl = itemRoutes.servicesIndex || `${itemRoutes.index}/services`;
        const serviceUrl = `${serviceBaseUrl}?json=1&include_inactive=1`;

        Promise.all([
            fetchJson(productUrl).catch(() => []),
            fetchJson(serviceUrl).catch(() => [])
        ])
        .then(([productItems, serviceItems]) => {
            const mergedItems = [
                ...(Array.isArray(productItems) ? productItems : []),
                ...(Array.isArray(serviceItems) ? serviceItems : [])
            ];
            const uniqueItems = dedupeItemsById(mergedItems);
            const filteredItems = filterItemsBySettings(uniqueItems);
            baseItems.splice(0, baseItems.length, ...filteredItems);
            window.items = filteredItems;
            updateItemSelectOptions();

            if (selectedItem && selectedItem.id) {
                const activeRowIndex = Number(window.activeSaleItemRowIndex || 0);
                const $targetRow = $ctx.find('.item-row').eq(activeRowIndex >= 0 ? activeRowIndex : 0);
                if ($targetRow.length) {
                    $targetRow.find('.item-name').val(String(selectedItem.id)).trigger('change');
                }
            }
        })
        .catch(() => {});
    }

    function refreshUnitsList(selectedUnit = '') {
        return fetchJson(`${itemRoutes.unitsIndex}?json=1`)
        .then(data => {
            const units = Array.isArray(data.units) ? data.units : [];
            window.saleUnits = units;
            renderNewItemUnitMenu(selectedUnit);
            syncItemUnitSelects();
            if (selectedUnit) {
                $('#newItemUnit').val(selectedUnit);
                updateNewItemUnitButton();
            }
            populateUnitSelectionModal();
            return units;
        })
        .catch(() => {
            window.saleUnits = [];
            renderNewItemUnitMenu(selectedUnit);
            syncItemUnitSelects();
            populateUnitSelectionModal();
            return [];
        });
    }

    if (!window.__saleItemModalMessageBound) {
        window.__saleItemModalMessageBound = true;
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'item-saved') {
                $(document).trigger('sale:item-created', [event.data.item || null]);
            }
        });
    }

    $(document).off('sale:item-created.saleform').on('sale:item-created.saleform', function(_, item) {
        refreshItemsList(item);
        const modalEl = document.getElementById('addItemModal');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }
    });

    // IMPORTANT: Set the doc-type field from window.docType
    // This ensures the correct type is captured when form is saved
    $ctx.find('.doc-type').val(currentDocType);
    $ctx.on('change', '.doc-type', function() {
        window.docType = String($(this).val() || 'invoice');
    });

    // Auto-fill invoice/order dates
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayValue = `${yyyy}-${mm}-${dd}`;
    const todayDisplayValue = `${dd}/${mm}/${yyyy}`;
    $ctx.find('.invoice-date').val(todayValue);
    $ctx.find('.order-date').val(todayValue);
    $ctx.find('.due-date').val(todayValue);

    function parseFlexibleDate(value) {
        const raw = String(value || '').trim();
        if (!raw) return null;

        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
            const [year, month, day] = raw.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            return Number.isNaN(date.getTime()) ? null : date;
        }

        if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) {
            const [day, month, year] = raw.split('/').map(Number);
            const date = new Date(year, month - 1, day);
            return Number.isNaN(date.getTime()) ? null : date;
        }

        const fallback = new Date(raw);
        return Number.isNaN(fallback.getTime()) ? null : fallback;
    }

    function formatDisplayDate(value) {
        const parsed = parseFlexibleDate(value);
        if (!parsed) return '';
        const displayDay = String(parsed.getDate()).padStart(2, '0');
        const displayMonth = String(parsed.getMonth() + 1).padStart(2, '0');
        const displayYear = parsed.getFullYear();
        return `${displayDay}/${displayMonth}/${displayYear}`;
    }

    function formatInputDate(value) {
        const parsed = parseFlexibleDate(value);
        if (!parsed) return '';

        const year = parsed.getFullYear();
        const month = String(parsed.getMonth() + 1).padStart(2, '0');
        const day = String(parsed.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
  $ctx.find('.due-days-select').val('0');

    // If editing an existing sale, populate the form with saved values
    if (window.editSaleData) {
        populateFormFromSale(window.editSaleData);
    }

// Auto-fetch next invoice number for new tabs
if (!window.editSaleData) {
    const currentDocType = window.docType || 'invoice';
    window._tabOpenCount = (window._tabOpenCount || 0) + 1;
    const tabOffset = window._tabOpenCount - 1;

    fetch(`/dashboard/sale/next-number?type=${currentDocType}&offset=${tabOffset}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.bill_number) {
            $ctx.find('.bill-number').val(data.bill_number);
        }
    })
    .catch(() => {});
}
    // ========== TOGGLE FIELDS BASED ON DOCUMENT TYPE ==========
    const docType = window.docType || 'invoice';
    const typeLabels = {
        'invoice': 'Sales Invoice',
        'estimate': 'Estimate / Quotation',
        'sale_order': 'Sale Order',
        'proforma': 'Proforma Invoice',
        'delivery_challan': 'Delivery Challan',
        'sale_return': 'Sale Return',
        'pos': 'POS'
    };

    // Set the form title
    const formTitle = $ctx.find('.form-title');
    if (formTitle.length) {
        formTitle.text(typeLabels[docType] || 'Sale');
    }

    const shippingGroup = $ctx.find('.shipping-address-group');
    const orderDateGroup = $ctx.find('.order-date-group');
    const dueDateGroup = $ctx.find('.due-date-group');
    const invoiceDateGroup = $ctx.find('.invoice-date-group');
    const docNumberLabel = $ctx.find('.doc-number-label');
    const docDateLabel = $ctx.find('.doc-date-label');
    const paymentSection = $ctx.find('.payment-section');
    const receivedInput = $paidInput;
    const receivedLabelDiv = $ctx.find('.received-label-text');
    const receivedRow = $ctx.find('.received-row');
    const balanceRow = $ctx.find('.balance-row');

    $ctx.find('.default-payment-direction').val(defaultPaymentDirection);

    function getDueDateBaseValue() {
        return $ctx.find('.invoice-date').val() || $ctx.find('.order-date').val() || '';
    }

    function updateDueDateFromSelection() {
        const $dueSelect = $ctx.find('.due-days-select');
        const $customInput = $ctx.find('.due-days-custom');
        const $dueDate = $ctx.find('.due-date');

        if (!$dueSelect.length || !$dueDate.length) return;

        const selectedValue = $dueSelect.val();
        const baseDateValue = getDueDateBaseValue();

        if (selectedValue === 'custom') {
            $customInput.removeClass('d-none').focus();
        } else {
            $customInput.addClass('d-none');
        }

        const days = selectedValue === 'custom'
            ? parseInt($customInput.val() || 0, 10)
            : parseInt(selectedValue || 0, 10);

        if (!baseDateValue) return;

        const baseDate = parseFlexibleDate(baseDateValue);
        if (Number.isNaN(baseDate.getTime())) return;

        const dueDate = new Date(baseDate);
        if (days > 0) {
            dueDate.setDate(dueDate.getDate() + days);
        }

        const displayYear = dueDate.getFullYear();
        const displayMonth = String(dueDate.getMonth() + 1).padStart(2, '0');
        const displayDay = String(dueDate.getDate()).padStart(2, '0');
        $dueDate.val(`${displayYear}-${displayMonth}-${displayDay}`);
    }

    function syncDealDaysFromSale(sale) {
        const $dueSelect = $ctx.find('.due-days-select');
        const $customInput = $ctx.find('.due-days-custom');
        if (!$dueSelect.length) return;

        const dealDays = parseInt(sale.deal_days || 0, 10) || 0;
        const predefined = ['0', '5', '10', '15', '30', '45'];

        if (predefined.includes(String(dealDays))) {
            $dueSelect.val(String(dealDays));
            $customInput.addClass('d-none').val('');
        } else {
            $dueSelect.val('custom');
            $customInput.removeClass('d-none').val(dealDays > 0 ? dealDays : '');
        }
    }

    function setupAdjustmentControls() {
        const $roundOffInput = $ctx.find('.round-off-val');
        const $roundOffCheck = $ctx.find('.round-off-check');
        if ($roundOffInput.length && $roundOffCheck.length) {
            $roundOffInput.prop('readonly', !$roundOffCheck.is(':checked'));
            if (!$roundOffCheck.is(':checked')) {
                $roundOffInput.val('0');
            }
        }

        if ($paidInput.length && !$ctx.find('.fill-balance-check').length) {
            const checkboxText = $paidInput.hasClass('advance-amount') ? 'Full Advance' : 'Full Receive';
            $paidInput.closest('.calc-inputs').prepend(
                `<label class="d-flex align-items-center gap-1 me-2 mb-0 text-nowrap" style="font-size:12px;">
                    <input type="checkbox" class="fill-balance-check">
                    <span>${checkboxText}</span>
                </label>`
            );
        }
    }

    function getExpenseLabelStorageKey() {
        const type = String(window.docType || $ctx.find('.doc-type').val() || 'invoice');
        return `saleExpenseLabels:${type}`;
    }

    function getCustomExpenseStorageKey() {
        const type = String(window.docType || $ctx.find('.doc-type').val() || 'invoice');
        return `saleCustomExpenseRows:v2:${type}`;
    }

    function getDefaultCustomExpenseRows() {
        return [
            { heading: '', mode: '+', percentage: '', amount: 0, details: '', account_type: '', account_id: '', account_name: '' },
        ];
    }

    function getLedgerAccountOptions() {
        const parties = Array.isArray(window.parties) ? window.parties : [];
        const brokers = Array.isArray(window.brokers) ? window.brokers : [];
        const items = Array.isArray(window.items) ? window.items : [];

        return {
            parties: parties.map((party) => ({
                type: 'party',
                id: party.id,
                name: party.name || '',
                meta: party.phone || party.phone_number || party.city || '',
                phone: party.phone || '',
            })),
            brokers: brokers.map((broker) => ({
                type: 'broker',
                id: broker.id,
                name: broker.name || '',
                meta: broker.city || broker.phone || '',
                phone: broker.phone || '',
                commission_rate: parseFloat(broker.commission_rate ?? 0) || 0,
            })),
            items: items.map((item) => ({
                type: 'item',
                id: item.id,
                name: item.name || item.item_name || '',
                meta: item.category || item.item_category || item.unit || '',
                phone: '',
            })),
        };
    }

    function buildLedgerAccountOptionMarkup(account) {
        return `
            <li>
                <a class="dropdown-item ledger-account-option" href="#"
                   data-account-type="${account.type}"
                   data-account-id="${account.id}"
                   data-account-name="${account.name || ''}"
                   data-account-meta="${account.meta || ''}"
                   data-account-phone="${account.phone || ''}"
                   data-commission-rate="${account.commission_rate ?? 0}">
                    <span>${account.name || '-'}</span>
                    <small>${account.type}</small>
                </a>
            </li>
        `;
    }

    function buildLedgerAccountActionMarkup() {
        return `
            <li><a class="dropdown-item text-primary ledger-account-action" href="#" data-action="party">+ Add New Party</a></li>
            <li><a class="dropdown-item text-primary ledger-account-action" href="#" data-action="broker">+ Add New Broker</a></li>
            <li><a class="dropdown-item text-primary ledger-account-action" href="#" data-action="item">+ Add New Item</a></li>
            <li><hr class="dropdown-divider"></li>
        `;
    }

    function renderLedgerAccountMenu($menu, query = '') {
        if (!$menu || !$menu.length) {
            return;
        }

        const term = String(query || '').trim().toLowerCase();
        const { parties, brokers, items } = getLedgerAccountOptions();
        const groups = [
            { label: 'Parties', rows: parties },
            { label: 'Brokers', rows: brokers },
            { label: 'Items', rows: items },
        ];

        const html = groups.map((group) => {
            const rows = group.rows.filter((entry) => {
                if (!term) return true;
                return [entry.name, entry.meta, entry.phone].some((value) =>
                    String(value || '').toLowerCase().includes(term)
                );
            });

            if (!rows.length) {
                return '';
            }

            return `
                <li class="ledger-account-group-label">${group.label}</li>
                ${rows.map(buildLedgerAccountOptionMarkup).join('')}
            `;
        }).filter(Boolean).join('');

        const body = html || '<li class="px-3 py-2 text-muted small">No account found</li>';
        $menu.html(buildLedgerAccountActionMarkup() + body);
    }

    function refreshLedgerAccountMenus() {
        $ctx.find('.ledger-account-menu').each(function () {
            const $menu = $(this);
            const query = $menu.closest('.custom-expense-account-wrap').find('.custom-expense-account-input').val() || '';
            renderLedgerAccountMenu($menu, query);
        });
    }

    function normalizeCustomExpenseRow(row = {}) {
        const legacyMode = String(row.mode || row.operator || '+').toUpperCase();
        const mode = legacyMode === 'SAME' ? 'S' : (['+', '-', 'S'].includes(legacyMode) ? legacyMode : '+');
        const rawHeading = String(row.heading || row.title || '').trim();
        const hiddenDefaultHeadings = ['new row', 'broker 1', 'broker 2', 'broker 3', 'bardana', 'mazdoori', 'commission', 'dak', 'karaya naam', 'local'];
        const heading = hiddenDefaultHeadings.includes(rawHeading.toLowerCase()) ? '' : rawHeading;

        let accountType = row.account_type || '';
        let accountId = row.account_id || '';
        let accountName = row.account_name || row.brokerName || '';

        if (!accountType && row.isBroker) {
            accountType = 'broker';
            accountId = row.brokerId || '';
            accountName = row.brokerName || row.heading || '';
        }

        if (!accountType && /^broker/i.test(String(row.heading || row.title || '').trim())) {
            accountType = 'broker';
        }

        return {
            heading,
            mode,
            percentage: row.percentage ?? row.pct ?? '',
            amount: row.amount ?? row.value ?? 0,
            details: row.details || '',
            account_type: accountType,
            account_id: accountId,
            account_name: accountName,
            account_phone: row.account_phone || row.brokerPhone || '',
        };
    }

    function saveExpenseLabels() {
        const labels = {};
        $ctx.find('.editable-expense-label[data-expense-key]').each(function () {
            const key = $(this).data('expenseKey');
            labels[key] = ($(this).text() || '').trim() || key;
        });
        localStorage.setItem(getExpenseLabelStorageKey(), JSON.stringify(labels));
    }

    function loadExpenseLabels() {
        const labels = JSON.parse(localStorage.getItem(getExpenseLabelStorageKey()) || '{}');
        $ctx.find('.editable-expense-label[data-expense-key]').each(function () {
            const key = $(this).data('expenseKey');
            if (labels[key]) {
                $(this).text(labels[key]);
            }
        });
    }

    function serializeCustomExpenseRows() {
        return $ctx.find('.custom-expense-row').map(function () {
            const $row = $(this);
            return {
                heading: ($row.find('.custom-expense-heading').text() || '').trim(),
                mode: $row.find('.custom-expense-mode').val() || '+',
                percentage: $row.find('.custom-expense-pct').val() || '',
                amount: parseFloat($row.find('.custom-expense-value').val() || 0) || 0,
                details: $row.find('.custom-expense-details').val() || '',
                account_type: $row.find('.custom-expense-account-type').val() || '',
                account_id: $row.find('.custom-expense-account-id').val() || '',
                account_name: $row.find('.custom-expense-account-input').val() || '',
                account_phone: $row.find('.custom-expense-account-phone').val() || '',
            };
        }).get();
    }

    function persistCustomExpenseRows() {
        localStorage.setItem(getCustomExpenseStorageKey(), JSON.stringify(serializeCustomExpenseRows()));
    }

    function isFreshSaleCreateMode() {
        return !window.editSaleData
            && !window.sourceEstimateId
            && !window.sourceSaleOrderId
            && !window.sourceChallanId
            && !window.sourceProformaId;
    }

    function createCustomExpenseRow(row = {}) {
        const template = document.getElementById('custom-expense-row-template');
        const container = $ctx.find('.custom-expense-rows').get(0);
        if (!template || !container) return null;

        const normalizedRow = normalizeCustomExpenseRow(row);
        const fragment = template.content.cloneNode(true);
        const rowEl = fragment.querySelector('.custom-expense-row');
        if (!rowEl) return null;

        rowEl.querySelector('.custom-expense-heading').textContent = normalizedRow.heading || '';
        rowEl.querySelector('.custom-expense-value').value = Number(normalizedRow.amount || 0);
        rowEl.querySelector('.custom-expense-pct').value = normalizedRow.percentage || '';
        rowEl.querySelector('.custom-expense-details').value = normalizedRow.details || '';
        rowEl.querySelector('.custom-expense-mode').value = normalizedRow.mode || '+';
        rowEl.querySelector('.custom-expense-account-type').value = normalizedRow.account_type || '';
        rowEl.querySelector('.custom-expense-account-id').value = normalizedRow.account_id || '';
        rowEl.querySelector('.custom-expense-account-phone').value = normalizedRow.account_phone || '';
        rowEl.querySelector('.custom-expense-account-input').value = normalizedRow.account_name || '';
        rowEl.classList.toggle('no-heading', !normalizedRow.heading);

        Array.from(rowEl.querySelectorAll('.custom-mode-btn')).forEach((button) => {
            button.classList.toggle('is-active', button.dataset.mode === normalizedRow.mode);
        });

        container.appendChild(fragment);
        const $newRow = $(container).find('.custom-expense-row').last();
        renderLedgerAccountMenu($newRow.find('.ledger-account-menu'), normalizedRow.account_name || '');
        return $newRow;
    }

    function createBrokerCustomExpenseRow(index = null) {
        const currentBrokerCount = $ctx.find('.custom-expense-row').filter(function () {
            return ($(this).find('.custom-expense-account-type').val() || '') === 'broker';
        }).length;
        const brokerIndex = index || (currentBrokerCount + 1);
        return createCustomExpenseRow({
            heading: '',
            mode: '+',
            percentage: '',
            amount: 0,
            details: '',
            account_type: 'broker',
            account_id: '',
            account_name: '',
        });
    }

    function loadCustomExpenseRows() {
        const useSavedRows = !isFreshSaleCreateMode();
        const savedRows = useSavedRows
            ? JSON.parse(localStorage.getItem(getCustomExpenseStorageKey()) || '[]')
            : [];
        const rowsToRender = Array.isArray(savedRows) && savedRows.length
            ? savedRows
            : getDefaultCustomExpenseRows();

        const $container = $ctx.find('.custom-expense-rows');
        $container.empty();
        rowsToRender.map((row) => normalizeCustomExpenseRow(row)).forEach((row) => createCustomExpenseRow(row));

        if (isFreshSaleCreateMode()) {
            localStorage.removeItem(getCustomExpenseStorageKey());
            persistCustomExpenseRows();
            return;
        }

        if (!Array.isArray(savedRows) || !savedRows.length) {
            persistCustomExpenseRows();
        }
    }

    function syncLegacyBrokerFieldsFromCustomRows() {
        const $primaryBrokerRow = $ctx.find('.custom-expense-row').filter(function () {
            const accountType = ($(this).find('.custom-expense-account-type').val() || '').toLowerCase();
            const mode = ($(this).find('.custom-expense-mode').val() || '+').toUpperCase();
            return accountType === 'broker' && mode !== 'S';
        }).first();
        if (!$primaryBrokerRow.length) {
            return;
        }

        const pctValue = parseFloat($primaryBrokerRow.find('.custom-expense-pct').val() || 0) || 0;
        const amountValue = parseFloat($primaryBrokerRow.find('.custom-expense-value').val() || 0) || 0;
        const brokerageType = pctValue > 0 ? 'custom_pct' : (amountValue > 0 ? 'fixed_rs' : '');
        const brokerageRate = pctValue > 0 ? pctValue : amountValue;

        $ctx.find('.broker-id').val($primaryBrokerRow.find('.custom-expense-account-id').val() || '');
        $ctx.find('.broker-phone-input').val($primaryBrokerRow.find('.custom-expense-account-phone').val() || '');
        $ctx.find('.brokerage-type').val(brokerageType);
        $ctx.find('.brokerage-rate').val(brokerageRate || '');
        $ctx.find('.brokerage-base-amount').val(brokerageRate || '');
        $ctx.find('.brokerage-amount').val((parseFloat($primaryBrokerRow.find('.custom-expense-value').val() || 0) || 0).toFixed(2));
    }

    function getSilentBrokerDeduction(baseAmount = 0) {
        let deduction = 0;

        $ctx.find('.custom-expense-row').each(function () {
            const $row = $(this);
            const mode = ($row.find('.custom-expense-mode').val() || '+').toUpperCase();
            const accountType = ($row.find('.custom-expense-account-type').val() || '').toLowerCase();

            if (mode !== '-' || accountType !== 'broker') {
                return;
            }

            const pct = parseFloat($row.find('.custom-expense-pct').val() || 0) || 0;
            const rawValue = parseFloat($row.find('.custom-expense-value').val() || 0) || 0;
            const amount = pct > 0 ? ((baseAmount * pct) / 100) : rawValue;

            if (pct > 0) {
                $row.find('.custom-expense-value').val(amount.toFixed(2));
            }

            deduction += amount;
        });

        return deduction;
    }

    function getCustomExpenseTotal(baseAmount = 0) {
        let total = 0;

        $ctx.find('.custom-expense-row').each(function () {
            const $row = $(this);
            const mode = ($row.find('.custom-expense-mode').val() || '+').toUpperCase();
            const accountType = ($row.find('.custom-expense-account-type').val() || '').toLowerCase();
            const pct = parseFloat($row.find('.custom-expense-pct').val() || 0) || 0;
            const rawValue = parseFloat($row.find('.custom-expense-value').val() || 0) || 0;
            let amount = rawValue;

            if (pct > 0) {
                amount = (baseAmount * pct) / 100;
                $row.find('.custom-expense-value').val(amount.toFixed(2));
            }

            // Minus adjustments are hidden from visible invoice total.
            // They are handled separately in backend/ledger posting.
            if (mode === '-') {
                return;
            }

            total += amount;
        });
        syncLegacyBrokerFieldsFromCustomRows();
        return total;
    }

    function syncDefaultPaymentFields() {
        const hasDefaultPaymentType = Boolean($ctx.find('.default-payment-type').val());
        const $defaultAmount = $ctx.find('.default-payment-amount');
        const $defaultReference = $ctx.find('.default-payment-reference');

        if (!$defaultAmount.length || !$defaultReference.length) {
            return;
        }

        $defaultAmount.toggleClass('d-none', !hasDefaultPaymentType);
        $defaultReference.toggleClass('d-none', !hasDefaultPaymentType);

        if (!hasDefaultPaymentType) {
            $defaultAmount.val('0');
            $defaultReference.val('');
        }
    }

    function updateBrokerageFields() {
        const brokerageType = ($ctx.find('.brokerage-type').val() || '').toString();
        const totalSafiWazan = Array.from($ctx.find('.item-row')).reduce((sum, row) => {
            return sum + (parseFloat($(row).find('.safi-wazan-input').val() || 0) || 0);
        }, 0);
        const itemAmountBase = parseFloat($ctx.find('.total-base-amount').text() || 0) || 0;
        const $brokerageRate = $ctx.find('.brokerage-rate');
        let rawRate = parseFloat($brokerageRate.val() || 0) || 0;
        const $brokerageAmount = $ctx.find('.brokerage-amount');
        const $brokerageBaseAmount = $ctx.find('.brokerage-base-amount');

        if (brokerageType !== 'broker_rate' && previousBrokerageType === 'broker_rate') {
            lastBrokerRateValue = parseFloat($brokerageRate.val() || 0) || 0;
        }

        if (brokerageType === 'broker_rate' && lastBrokerRateValue > 0) {
            rawRate = lastBrokerRateValue;
        }

        let amount = 0;
        let placeholder = 'Enter value';
        let readOnly = false;
        let step = '0.01';
        let amountReadOnly = true;

        if (brokerageType === 'per_kg') {
            amount = totalSafiWazan * rawRate;
            placeholder = 'Rate per KG';
        } else if (brokerageType === 'broker_rate') {
            amount = itemAmountBase * rawRate;
            placeholder = 'Broker Rate';
        } else if (brokerageType === 'full') {
            rawRate = 0.45;
            amount = itemAmountBase * rawRate / 100;
            placeholder = 'Poori Brokerage %';
            readOnly = true;
            step = '0.001';
        } else if (brokerageType === 'half') {
            rawRate = 0.225;
            amount = itemAmountBase * rawRate / 100;
            placeholder = 'Aadhi Brokerage %';
            readOnly = true;
            step = '0.001';
        } else if (brokerageType === 'custom_pct') {
            amount = itemAmountBase * rawRate / 100;
            placeholder = 'Custom %';
            step = '0.001';
        } else if (brokerageType === 'fixed_rs') {
            amount = rawRate;
            placeholder = 'Rs';
        }

        const rateValue = (brokerageType === 'full' || brokerageType === 'half' || brokerageType === 'custom_pct')
            ? rawRate.toFixed(3)
            : (rawRate > 0 ? rawRate.toFixed(2) : '');

        $brokerageRate.attr('placeholder', placeholder).prop('readonly', readOnly).attr('step', step);
        $brokerageAmount.prop('readonly', amountReadOnly);
        if (brokerageType === 'full' || brokerageType === 'half') {
            $brokerageRate.val(rateValue);
        } else if (brokerageType === 'broker_rate' || brokerageType === 'per_kg' || brokerageType === 'custom_pct') {
            if (String($brokerageRate.val() || '').trim() !== '' || rawRate > 0) {
                $brokerageRate.val(rateValue);
            }
        } else if (brokerageType === 'fixed_rs') {
            $brokerageRate.val(rateValue);
        }
        $brokerageBaseAmount.val(rateValue);
        $brokerageAmount.val(amount.toFixed(2));
        previousBrokerageType = brokerageType;
    }

    function getRowBagWeight($row) {
        const storedValue = parseFloat($row.attr('data-bag-weight') || 0);
        if (!Number.isNaN(storedValue) && storedValue > 0) {
            return storedValue;
        }

        const selectedValue = String($row.find('.item-name').val() || '').trim();
        const selectedItem = getSourceItems().find((item) => String(item.id) === selectedValue);
        if (selectedItem && selectedItem.bag_weight !== undefined && selectedItem.bag_weight !== null) {
            return parseFloat(selectedItem.bag_weight || 0) || 0;
        }

        const optionWeight = parseFloat($row.find('.item-name option:selected').attr('data-bag-weight') || 0);
        return Number.isNaN(optionWeight) ? 0 : optionWeight;
    }

    function syncRowWazanFromBagWeight($row, syncSafi = true) {
        if (!$row || !$row.length) {
            return;
        }

        const qty = parseFloat($row.find('.item-qty').val() || 0) || 0;
        const bagWeight = getRowBagWeight($row);
        const totalWazan = qty * bagWeight;

        if ($row.find('.total-wazan-input').length) {
            $row.find('.total-wazan-input').val(totalWazan.toFixed(2));
        }

        if (syncSafi && $row.find('.safi-wazan-input').length) {
            $row.find('.safi-wazan-input').val(totalWazan.toFixed(2));
        }
    }

    function getMarketExpenseTotal() {
        return (parseFloat($ctx.find('.brokerage-amount').val() || 0) || 0)
            + (parseFloat($ctx.find('.bardana-input').val() || 0) || 0)
            + (parseFloat($ctx.find('.labour-input').val() || 0) || 0)
            + (parseFloat($ctx.find('.rehra-mazdori-input').val() || 0) || 0)
            + (parseFloat($ctx.find('.post-expense-input').val() || 0) || 0)
            + (parseFloat($ctx.find('.extra-expense-input').val() || 0) || 0)
            + getCustomExpenseTotal(parseFloat($ctx.find('.total-base-amount').text() || 0) || 0);
    }

    function updateMarketRowAmount($row) {
        if (!$row || !$row.length) {
            return;
        }

        const mrp = parseFloat($row.find('.item-mrp-input').val() || 0) || 0;
        const explicitRate = parseFloat($row.find('.item-rate').val() || 0) || 0;
        const rate = explicitRate > 0 ? explicitRate : (itemFormSettings.mrp?.enabled ? mrp : 0);
        const itemDiscount = parseFloat($row.find('.item-discount').val() || 0) || 0;
        const qty = parseFloat($row.find('.item-qty').val() || 0) || 0;
        const netW = parseFloat($row.find('.net-w-input').val() || 0) || 0;
        const taxPct = parseFloat($row.find('.item-tax-pct').val() || 0) || 0;
        const taxableAmount = Math.max(((netW > 0 ? netW : qty) * rate) - itemDiscount, 0);
        const typedTaxAmount = parseFloat($row.find('.item-tax-amount').val() || 0) || 0;
        const taxAmount = taxableAmount > 0
            ? (taxPct > 0 ? (taxableAmount * taxPct) / 100 : typedTaxAmount)
            : 0;
        const amount = taxableAmount + taxAmount;

        $row.find('.item-tax-amount').val(taxAmount.toFixed(2));
        $row.find('.item-amount').val(Math.max(amount, 0).toFixed(2));
    }

    if (docType === 'sale_order' || docType === 'delivery_challan') {
        // Show shipping address and dates
        shippingGroup.removeClass('d-none');
        orderDateGroup.removeClass('d-none');
        dueDateGroup.removeClass('d-none');

        // Update labels
        if (docNumberLabel.length) docNumberLabel.text('Order No.');
        if (docDateLabel.length) docDateLabel.text('Order Date');

        // For sale_order: change "Received" to "Advance"
        if (docType === 'sale_order') {
            if (receivedLabelDiv.length) {
                receivedLabelDiv.text('Advance Payment');
            }
            if (receivedInput.length) {
                receivedInput.prop('readonly', true).attr('placeholder', 'Advance amount');
            }
        }
    }
    else if (docType === 'estimate') {
        // Show due date for estimates
        dueDateGroup.removeClass('d-none');

        // Update labels
        if (docNumberLabel.length) docNumberLabel.text('Estimate No.');
        if (docDateLabel.length) docDateLabel.text('Estimate Date');

        // Hide payment section for estimates
        paymentSection.addClass('d-none');
        receivedRow.addClass('d-none');
        balanceRow.addClass('d-none');
    }
    else if (docType === 'proforma') {
        // Update labels for proforma
        if (docNumberLabel.length) docNumberLabel.text('Proforma No.');
    }
    // ========== END TOGGLE FIELDS ==========

    function buildImageUrl(path) {
        if (!path) return '';
        const trimmed = path.toString().trim();
        // If it is already a full URL or absolute path, just normalize it
        if (/^https?:\/\//i.test(trimmed)) {
            return trimmed;
        }
        if (trimmed.startsWith('/')) {
            return encodeURI(trimmed);
        }
        // If it begins with storage/ (relative), use it as absolute
        if (trimmed.startsWith('storage/')) {
            return encodeURI('/' + trimmed);
        }
        // Otherwise assume it's just a filename stored under /storage/images/
        return encodeURI('/storage/images/' + trimmed);
    }

    function setImagePreviewUrl(url) {
        const $preview = $ctx.find('.image-preview');
        const $img = $preview.find('.image-preview-img');
        const $placeholder = $ctx.find('.image-placeholder');

        if (!url) {
            $preview.addClass('d-none');
            $placeholder.removeClass('d-none');
            return;
        }

        $img.attr('src', buildImageUrl(url));
        $preview.removeClass('d-none');
        $placeholder.addClass('d-none');
    }

    function normalizeStoredMediaList(value, type = 'image') {
        const items = Array.isArray(value) ? value : (value ? [value] : []);
        return items
            .map(entry => {
                if (!entry) return null;
                if (typeof entry === 'string') {
                    const trimmed = entry.trim();
                    if (!trimmed) return null;
                    return {
                        name: trimmed.split('/').pop() || trimmed,
                        url: type === 'image' ? buildImageUrl(trimmed) : null,
                    };
                }

                const rawUrl = entry.url || entry.path || entry.image_url || entry.image_path || entry.document_url || entry.document_path || '';
                const name = entry.name || (rawUrl ? String(rawUrl).split('/').pop() : '');
                if (!name && !rawUrl) return null;

                return {
                    name,
                    url: type === 'image' && rawUrl ? buildImageUrl(rawUrl) : (rawUrl || null),
                };
            })
            .filter(Boolean);
    }

    function renderStoredImages() {
        if (!existingImages.length) return '';

        return existingImages.map((item, index) => `
            <div class="image-file-card position-relative border rounded overflow-hidden bg-white" data-existing="1" data-index="${index}">
                <img src="${item.url || ''}" alt="${item.name || 'Image'}" class="img-fluid" style="width:120px;height:120px;object-fit:cover;" />
                <div class="small text-truncate p-1 text-center" style="max-width:120px;">${item.name || 'Image'}</div>
            </div>
        `).join('');
    }

    function renderStoredDocuments() {
        if (!existingDocuments.length) return '';

        return existingDocuments.map((item) => `
            <div class="list-group-item d-flex justify-content-between align-items-center bg-light" data-existing="1">
                <span class="text-truncate" style="max-width: 100%;">${item.name || 'Document'}</span>
            </div>
        `).join('');
    }

    function populateFormFromSale(sale) {
        // Fill header fields
        let selectedPartyRecord = null;
        if (hasCustomPartyDropdown) {
            const party = (window.parties || []).find(p => String(p.id) === String(sale.party_id || ''));
            $ctx.find('.party-id').val(sale.party_id || '');
            if (party) {
                selectedPartyRecord = {
                    ...party,
                    billing_address: party.billing_address || sale.billing_address || '',
                    shipping_address: party.shipping_address || sale.shipping_address || '',
                    phone: party.phone || sale.phone || '',
                };
                const phone = party.phone || sale.phone || '';
                const phoneNumber2 = party.phone_number_2 || '';
                const ptclNumber = party.ptcl_number || party.ptcl || '';
                const email = party.email || '';
                const city = party.city || '';
                const address = party.address || '';
                const billingAddress = party.billing_address || sale.billing_address || '';
                const shippingAddress = party.shipping_address || sale.shipping_address || '';
                let billingDetails = '';

                setPartyDropdownDisplay(party.name || sale.party_name || 'Select Party');
                $ctx.find('.phone-input').val(phone);
                $ctx.find('.city-input').val(city);
                $ctx.find('.ptcl-input').val(ptclNumber);
                $ctx.find('.address-input').val(address);

               $ctx.find('.billing-address').val(billingAddress);
                $ctx.find('.shipping-address').val(shippingAddress);
                $('#hiddenPhone').val(phone);
                $('#hiddenBilling').val(billingDetails.trim());
                $('#hiddenShipping').val(shippingAddress);
            } else {
                selectedPartyRecord = sale.party_id ? {
                    id: sale.party_id,
                    name: sale.party_name || sale.party?.name || 'Select Party',
                    phone: sale.phone || sale.party?.phone || '',
                    phone_number_2: sale.party?.phone_number_2 || '',
                    ptcl_number: sale.party?.ptcl_number || '',
                    email: sale.party?.email || '',
                    city: sale.party?.city || '',
                    address: sale.party?.address || '',
                    billing_address: sale.billing_address || sale.party?.billing_address || '',
                    shipping_address: sale.shipping_address || sale.party?.shipping_address || '',
                    due_days: sale.party?.due_days || 0,
                    opening_balance: sale.party?.opening_balance || 0,
                    transaction_type: sale.party?.transaction_type || '',
                } : null;
                setPartyDropdownDisplay(sale.party_name || 'Select Party');
            }
        } else {
            const partyOption = $ctx.find('.party-select option').filter(function () {
                return $(this).val() == (sale.party_id || '');
            }).first();

            if (partyOption.length) {
                partyOption.prop('selected', true);
                partyOption.trigger('change');
            } else {
                $ctx.find('.party-select').val('');
            }
        }

        $ctx.find('.phone-input').val(sale.phone || '');
        if (!$ctx.find('.billing-address').val()) {
            $ctx.find('.billing-address').val(sale.billing_address || '');
        }
        if (!$ctx.find('.shipping-address').val()) {
            $ctx.find('.shipping-address').val(sale.shipping_address || '');
        }

        window.pendingSelectedPartyRecord = selectedPartyRecord;
        if (typeof window.initializeSelectedPartyCard === 'function' && selectedPartyRecord) {
            window.initializeSelectedPartyCard(selectedPartyRecord);
        }

        $ctx.find('.bill-number').val(sale.bill_number || '');
        const editOrderDateValue = sale.order_date
            ? formatInputDate(sale.order_date)
            : (sale.invoice_date ? formatInputDate(sale.invoice_date) : todayValue);
        $ctx.find('.order-date').val(editOrderDateValue);
        $ctx.find('.invoice-date').val(editOrderDateValue);
        syncDealDaysFromSale(sale);
        $ctx.find('.due-date').val(sale.due_date ? formatInputDate(sale.due_date) : '');

        // Items
        $ctx.find('.item-rows').empty();
        (sale.items || []).forEach(item => {
            addRow();
            const $row = $ctx.find('.item-rows tr').last();
            let matchOption = $row.find('.item-name option').filter(function () {
                return ($(this).data('label') || $(this).text().trim()) === (item.item_name || '');
            }).first();

            if (!matchOption.length && item.item_id) {
                matchOption = $row.find('.item-name option').filter(function () {
                    return String($(this).val()) === String(item.item_id || '');
                }).first();
            }

            if (matchOption.length) {
                matchOption.prop('selected', true);
                $row.find('.item-name').val(matchOption.val()).trigger('change');
            } else {
                $row.attr('data-temp-item-name', item.item_name || '');
                $row.attr('data-temp-item-id', item.item_id || '');
            }

            syncRowCategoryOptions($row, item.item_category || '');
            $row.find('.item-code').val(item.item_code || '');
            $row.find('.item-desc').val(item.item_description || '');
            $row.find('.item-tafseel').val(item.tafseel || '');
            $row.find('.item-discount').val(item.discount || 0);
            updateRowDiscountPercentFromAmount($row);
            $row.find('.item-qty').val(item.quantity || 0);
            $row.find('.item-free-qty').val(item.free_quantity || 0);
            $row.find('.gross-w-input').val(item.gross_w || 0);
            $row.find('.net-w-input').val(item.net_w || 0);
            if (item.unit) {
                ensureUnitOption($row.find('.item-unit'), item.unit);
            }
            $row.find('.item-rate').val(item.unit_price || 0);
            $row.find('.item-amount').val(item.amount || 0);
        });

        // Discount / Tax / Round off
        $ctx.find('.discount-pct').val(sale.discount_pct || 0);
        $ctx.find('.discount-rs').val(sale.discount_rs || 0);
        $ctx.find('.tax-select').val(sale.tax_pct || 0);
        $ctx.find('.round-off-val').val(sale.round_off || 0);
        $ctx.find('.grand-total').val(sale.grand_total || 0);
        currentTransportationDetails = sale.details?.transportation_details || sale.transportation_details || {};
        $ctx.find('.parachi-input').val(sale.tadad || sale.total_qty || '');
        $ctx.find('.total-wazan-input').val(sale.total_wazan || '');
        $ctx.find('.safi-wazan-input').val(sale.safi_wazan || '');
        $ctx.find('.rate-input').val(sale.rate || '');
        $ctx.find('.deo-input').val(sale.deo || '');
        $ctx.find('.bardana-input').val(sale.bardana || '');
        $ctx.find('.labour-input').val(sale.labour || '');
        $ctx.find('.rehra-mazdori-input').val(sale.rehra_mazdori || '');
        $ctx.find('.post-expense-input').val(sale.post_expense || '');
        $ctx.find('.extra-expense-input').val(sale.extra_expense || '');

        // Description (show if already set)
        const desc = sale.description || '';
        $ctx.find('.description-input').val(desc);
        if (desc) {
            $ctx.find('.description-pane').removeClass('d-none');
            $ctx.find('.description-side-fields').removeClass('d-none');
        }

        existingImages.splice(0, existingImages.length, ...normalizeStoredMediaList(sale.image_paths || sale.image_url || sale.image_path || [], 'image'));
        existingDocuments.splice(0, existingDocuments.length, ...normalizeStoredMediaList(sale.document_paths || sale.document_name || sale.document_path || [], 'document'));
        renderSelectedImages();
        renderSelectedDocuments();

        // Payments: treat values as "already received" and allow adding new payments
        window.existingReceivedAmount = parseFloat(sale.received_amount || 0) || 0;
        window.existingBalance = parseFloat(sale.balance || 0) || 0;

        // Pre-select the same bank as the first payment (so user can quickly add more)
        $ctx.find('.default-payment-type').val('cash');
        $ctx.find('.default-payment-direction').val(defaultPaymentDirection);
        $ctx.find('.default-payment-amount').val('0').addClass('d-none');
        $ctx.find('.default-payment-reference').val('').addClass('d-none');
        $ctx.find('.payment-entries').empty();

        (sale.payments || []).forEach((payment, index) => {
            if (index !== 0) return;
            const paymentType = (payment.payment_type || '').toString().toLowerCase();
            $ctx.find('.default-payment-direction').val(defaultPaymentDirection);
            if (paymentType === 'cash') {
                $ctx.find('.default-payment-type').val('cash');
            } else if (paymentType === 'cheques' || paymentType === 'cheque') {
                $ctx.find('.default-payment-type').val('cheques');
            } else if (payment.bank_account_id) {
                $ctx.find('.default-payment-type').val(`bank-${payment.bank_account_id}`);
            }
        });

        const broker = (window.brokers || []).find(b => String(b.id) === String(sale.broker_id || ''));
        $ctx.find('.broker-id').val(sale.broker_id || '');
        if (broker) {
            $ctx.find('#brokerDropdownBtn').val(broker.name || '').attr('placeholder', broker.name || 'Broker');
            $ctx.find('.broker-phone-input').val(broker.phone || '');
        } else {
            $ctx.find('#brokerDropdownBtn').val('').attr('placeholder', 'Broker');
            $ctx.find('.broker-phone-input').val('');
        }

        $ctx.find('.brokerage-type').val(sale.brokerage_type || '');
        const saleBrokerageRate = parseFloat(sale.brokerage_rate ?? sale.broker_amount ?? 0) || 0;
        $ctx.find('.brokerage-rate').val(saleBrokerageRate ? saleBrokerageRate.toFixed(2) : '');
        $ctx.find('.brokerage-base-amount').val(saleBrokerageRate.toFixed(2));
        $ctx.find('.brokerage-amount').val((parseFloat(sale.broker_amount || 0) || 0).toFixed(2));
        const $primaryBrokerRow = $ctx.find('.custom-expense-row').filter(function () {
            return ($(this).find('.custom-expense-account-type').val() || '') === 'broker';
        }).first();
        if ($primaryBrokerRow.length) {
            $primaryBrokerRow.find('.custom-expense-account-type').val('broker');
            $primaryBrokerRow.find('.custom-expense-account-id').val(sale.broker_id || '');
            $primaryBrokerRow.find('.custom-expense-account-phone').val(broker?.phone || '');
            $primaryBrokerRow.find('.custom-expense-account-input').val(broker?.name || '');
            $primaryBrokerRow.find('.custom-expense-pct').val(sale.brokerage_type === 'custom_pct' ? (saleBrokerageRate ? saleBrokerageRate.toFixed(2) : '') : '');
            $primaryBrokerRow.find('.custom-expense-value').val((parseFloat(sale.broker_amount || 0) || 0).toFixed(2));
        }

        const details = sale.details || sale.sale_detail || null;
        const customExpenses = (details && Array.isArray(details.custom_expenses))
            ? details.custom_expenses
            : (Array.isArray(sale.custom_expenses) ? sale.custom_expenses : []);
        if (details) {
            $ctx.find('.warehouse-select').val(details.warehouse_id || '');
            $ctx.find('.delivery-person-input').val(details.delivery_person || '');
            $ctx.find('.po-no-input').val(details.po_no || '');
            $ctx.find('.po-date-input').val(details.po_date || '');
            $ctx.find('.city-input').val(details.city || '');
            $ctx.find('.party-no-input').val(details.party_no || '');
            $ctx.find('.goods-name-input').val(details.goods_name || '');
            $ctx.find('.details-extra-input').val(details.details_extra || '');
            $ctx.find('.bilti-gari-input').val(details.bilti_gari_no || '');
            setTermsConditionSelection(details.terms_condition_name || '', details.terms_condition_text || '');
            currentSaleAdditionalCharges = Array.isArray(details.additional_charges)
                ? details.additional_charges.reduce((acc, item) => {
                    const key = getAdditionalChargeFieldKey(item?.key);
                    if (key) {
                        acc[key] = item;
                    }
                    return acc;
                }, {})
                : {};
            if (details.invoice_extra_fields && typeof details.invoice_extra_fields === 'object') {
                populateDynamicInvoiceFields();
            }
            if (details.payment_term_name || details.payment_term_days) {
                $ctx.find('.settings-payment-term-name').val(details.payment_term_name || saleFormSettings.payment_terms.name || '');
                $ctx.find('.settings-payment-term-days').val(details.payment_term_days || saleFormSettings.payment_terms.days || '');
                if (details.payment_term_days) {
                    setDueDaysSelectionValue(details.payment_term_days);
                }
            }
        }

        if (customExpenses.length) {
            const $container = $ctx.find('.custom-expense-rows');
            $container.empty();
            customExpenses.forEach((row) => createCustomExpenseRow(row));
            persistCustomExpenseRows();
        }

        syncDefaultPaymentFields();
        updateBrokerageFields();
        updateDueDateFromSelection();
        renderAdditionalChargeLiveRows();
        updateAdditionalChargeRowTotals();

        // Show the current received / balance values based on stored sale
        $ctx.find('.payment-total-amount').text((window.existingReceivedAmount || 0).toFixed(2));
        $ctx.find('.balance-amount').text((window.existingBalance || 0).toFixed(2));

        calculateTotals();
    }

    // Party select logic
    $ctx.on('change', '.party-select', function() {
        const selectedId = $(this).val();
        const party = (window.parties || []).find(p => String(p.id) === String(selectedId));
        if (party) {
            $ctx.find('.party-id').val(party.id || '');
            $ctx.find('.phone-input').val(party.phone || '');
            $ctx.find('.billing-address').val(party.billing_address || '');
        } else {
            $ctx.find('.party-id').val('');
            $ctx.find('.phone-input').val('');
            $ctx.find('.billing-address').val('');
        }
    });

  $ctx.on('click', '.party-option', function(e) {
    e.preventDefault();
    e.stopPropagation();

    const $option = $(this);
    const partyId  = String($option.data('id') || '').trim();
    const partyName = $.trim($option.data('name') || $option.find('.party-option-name').text() || '');

    // Read ALL data from the clicked option's data attributes first
    // then fall back to window.parties for anything missing
    const wp = (window.parties || []).find(p => String(p.id) === partyId) || {};

    const name           = $option.data('name')         || wp.name             || partyName;
    const phone          = $option.data('phone')        || wp.phone            || "";
    const phone_number_2 = $option.data('phoneNumber2') || wp.phone_number_2   || "";
    const ptcl_number    = $option.data('ptcl')         || wp.ptcl_number      || "";
    const email          = $option.data('email')        || wp.email            || "";
    const city           = $option.data('city')         || wp.city             || "";
    const party_group    = $option.data('partyGroup')   || wp.party_group      || "";
    const address        = $option.data('address')      || wp.address          || "";
    const billing_addr   = $option.data('billing')      || wp.billing_address  || "";
    const shipping_addr  = $option.data('shipping')     || wp.shipping_address || "";
    const due_days       = $option.data('dueDays')      || wp.due_days         || "";

    // Set party ID and dropdown display
    $ctx.find('.party-id').val(partyId);
    setPartyDropdownDisplay(name || 'Select Party');

    // Set phone field
    $ctx.find('.phone-input').val(phone);

    // Build full party details text
   $ctx.find('.billing-address').val(billing_addr || address);
    $ctx.find('.shipping-address').val(shipping_addr);

    // Show party details section
    $ctx.find('.party-details').removeClass('d-none');
    $ctx.find('.phone-field').show();
    $ctx.find('.billing-address-field').show();
    $ctx.find('.shipping-address-field').show();

    // Close the dropdown
    const dropdownToggle = document.getElementById('partyDropdownBtn');
    if (dropdownToggle) {
        try {
            bootstrap.Dropdown.getOrCreateInstance(dropdownToggle).hide();
        } catch(err) {}
    }
});

    // Add row functionality
    $ctx.off('click', '.add-row-btn').on('click', '.add-row-btn', function(e) {
        e.preventDefault();
        addRow();
    });

    function addRow() {
        const rowCount = $ctx.find('.item-rows tr').length + 1;
        const optionsHtml = buildItemOptionsHtml(getFilteredItems());
        const unitOptionsHtml = buildUnitOptionsHtml();

        const newRow = `
            <tr class="item-row">
                <td class="row-num">
                    <span class="row-index-text">${rowCount}</span>
                    <div class="delete-row-icon"><i class="fa-solid fa-trash-can"></i></div>
                </td>
                <td class="col-barcode-scan d-none">
                    <button type="button" class="btn btn-sm btn-outline-primary open-scan-serial-modal" title="Scan code/serial"><i class="fa-solid fa-qrcode"></i></button>
                </td>
                <td class="col-item-name">
                    <div class="item-picker">
                        <input type="text" class="item-picker-input" placeholder="Search Item">
                        <div class="item-picker-panel">
                            <div class="item-picker-add"><i class="fa-regular fa-square-plus"></i> Add Item</div>
                            <div class="item-picker-head" style="display: grid; grid-template-columns: minmax(0, 2fr) 100px 110px 80px 80px; gap: 12px; padding: 10px 18px; font-size: 12px; font-weight: 700; color: #97a3b6; text-transform: uppercase; background: #f8fbff; border-bottom: 1px solid #e1e8ed;">
                                <span>Item</span>
                                <span>Sale Price</span>
                                <span>Purchase Price</span>
                                <span>Stock</span>
                                <span>Weight</span>
                            </div>
                            <div class="item-picker-list"></div>
                        </div>
                        <select class="form-select item-name d-none">
                            <option value="" selected disabled>Select Item</option>
                            ${optionsHtml}
                        </select>
                    </div>
                </td>
                <td class="col-serial-no d-none"><input type="text" class="item-serial-input" placeholder="Serial No."></td>
                <td class="col-description d-none"><input type="text" class="item-desc" placeholder="Description" readonly></td>
                <td class="col-count d-none"><input type="number" class="item-count-input" value="0" min="0" step="1"></td>
                <td class="col-batch-no d-none"><input type="text" class="item-batch-no-input" placeholder="Batch No."></td>
                <td class="col-model-no d-none"><input type="text" class="item-model-no-input" placeholder="Model No."></td>
                <td class="col-exp-date d-none"><input type="date" class="item-exp-date-input"></td>
                <td class="col-mfg-date d-none"><input type="date" class="item-mfg-date-input"></td>
                <td class="col-mrp d-none"><input type="number" class="item-mrp-input" value="0" min="0" step="0.01"></td>
                <td class="col-size d-none"><input type="text" class="item-size-input" placeholder="Size"></td>
                <td class="col-tafseel"><input type="text" class="item-tafseel" placeholder="Tafseel"></td>
                <td class="col-tadaat"><input type="number" class="item-qty tadaat-input" value="1"></td>
                <td class="col-free-qty d-none"><input type="number" class="item-free-qty" value="0" min="0" step="1"></td>
                <td class="col-gross-w"><input type="number" class="gross-w-input" value="0" min="0" step="0.01"></td>
                <td class="col-net-w"><input type="number" class="net-w-input" value="0" min="0" step="0.01"></td>
                <td class="custom-size-td">
                    <div class="item-unit-wrapper d-flex align-items-center gap-1">
                        <select class="item-unit">${unitOptionsHtml}</select>
                        <button type="button" class="btn btn-sm btn-outline-primary open-add-unit-from-selector" title="Add Unit"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </td>
                <td class="col-rate"><input type="number" class="item-rate" value="0" min="0" step="0.01"></td>
                <td class="col-amount"><input type="number" class="item-amount" value="0" min="0" step="0.01" readonly></td>
                <td class="col-category d-none">
                    <select class="item-category">${buildCategoryOptionsHtml()}</select>
                </td>
                <td class="col-item-code d-none"><input type="text" class="item-code" placeholder="Item Code" readonly></td>
                <td class="col-discount d-none">
                    <div class="item-discount-fields">
                        <input type="number" class="item-discount-pct" value="" min="0" step="0.01" placeholder="%">
                        <input type="number" class="item-discount" value="0" min="0" step="0.01" placeholder="Amount">
                    </div>
                </td>
                <td class="col-item-tax d-none">
                    <div class="item-tax-fields">
                        <input type="number" class="item-tax-pct" value="" min="0" step="0.01" placeholder="%">
                        <input type="number" class="item-tax-amount" value="0" min="0" step="0.01" placeholder="Amount">
                    </div>
                </td>
                <td class="custom-item-field col-custom-field-1 d-none"><input type="text" class="item-custom-field-input item-custom-field-1-input" placeholder="Custom Field 1"></td>
                <td class="custom-item-field col-custom-field-2 d-none"><input type="text" class="item-custom-field-input item-custom-field-2-input" placeholder="Custom Field 2"></td>
                <td class="custom-item-field col-custom-field-3 d-none"><input type="text" class="item-custom-field-input item-custom-field-3-input" placeholder="Custom Field 3"></td>
                <td class="custom-item-field col-custom-field-4 d-none"><input type="text" class="item-custom-field-input item-custom-field-4-input" placeholder="Custom Field 4"></td>
                <td class="custom-item-field col-custom-field-5 d-none"><input type="text" class="item-custom-field-input item-custom-field-5-input" placeholder="Custom Field 5"></td>
                <td class="custom-item-field col-custom-field-6 d-none"><input type="text" class="item-custom-field-input item-custom-field-6-input" placeholder="Custom Field 6"></td>
                <td class="add-col"></td>
            </tr>
        `;
        $ctx.find('.item-rows').append(newRow);
        renderItemSettingsColumns();
        refreshQuickEntryUi();
        calculateTotals();
    }

    function renumberItemRows() {
        $ctx.find('.item-row').each(function(index) {
            $(this).find('.row-index-text').text(index + 1);
        });
    }

    function clearQuickEntryRow($row) {
        clearSelectedItemRow($row);
        $row.find('.item-picker-input').val('Search Item');
        $row.find('.item-tafseel').val('Tafseel');
        $row.find('.item-qty').val(1);
        $row.find('.item-free-qty').val(0);
        $row.find('.gross-w-input, .net-w-input, .item-rate, .item-mrp-input').val(0);
        $row.find('.item-amount').val('0.00');
        $row.find('.item-discount-pct, .item-tax-pct').val('');
        $row.find('.item-discount, .item-tax-amount').val(0);
        $row.find('.item-unit').val('');
        $row.find('.item-desc, .item-serial-input, .item-batch-no-input, .item-model-no-input, .item-size-input, .item-code').val('');
        $row.find('.item-exp-date-input, .item-mfg-date-input').val('');
        $row.find('.item-custom-field-input').val('');
        updateMarketRowAmount($row);
    }

    function refreshQuickEntryUi() {
        const enabled = isQuickEntryEnabled();
        const $rows = $ctx.find('.item-row');
        $rows.removeClass('quick-entry-active');
        $rows.find('.quick-entry-bolt').remove();
        $rows.find('.quick-entry-confirm').remove();

        if (!enabled || !$rows.length) {
            return;
        }

        const $activeRow = $rows.first();
        $activeRow.addClass('quick-entry-active');
        const $rowNumCell = $activeRow.find('.row-num').first();
        if ($rowNumCell.length && !$rowNumCell.find('.quick-entry-bolt').length) {
            $rowNumCell.prepend('<span class="quick-entry-bolt"><i class="fa-solid fa-bolt"></i></span>');
        }
        $activeRow.find('.add-col').html('<button type="button" class="btn btn-sm btn-primary quick-entry-confirm" title="Confirm row"><i class="fa-solid fa-check"></i></button>');
    }

    function commitQuickEntryRow($row) {
        if (!$row || !$row.length) {
            return;
        }

        if (!$row.find('.item-name').val()) {
            $row.find('.item-picker-input').trigger('focus');
            return;
        }

        const $clone = $row.clone(true, false);
        $clone.removeClass('quick-entry-active');
        $row.after($clone);
        clearQuickEntryRow($row);
        renumberItemRows();
        renderItemSettingsColumns();
        refreshQuickEntryUi();
        calculateTotals();
        $row.find('.item-picker-input').trigger('focus');
    }

    function applyColumnVisibility() {
        const isCatVisible = $('.check-category').is(':checked');
        const isCodeVisible = $('.check-item-code').is(':checked');
        const isDescVisible = $('.check-description').is(':checked');
        const isDiscVisible = $('.check-discount').is(':checked');

        $ctx.find('.col-category').toggleClass('d-none', !isCatVisible);
        $ctx.find('.col-item-code').toggleClass('d-none', !isCodeVisible);
        $ctx.find('.col-description').toggleClass('d-none', !isDescVisible);
        $ctx.find('.col-discount').toggleClass('d-none', !isDiscVisible);
        renderItemSettingsColumns();
        $ctx.find('.item-row').each(function () {
            const $row = $(this);
            syncRowCategoryOptions($row, $row.find('.item-category').val() || '');
        });
    }

    function getFilteredItems() {
        const $modal = $('#itemColumnModal');
        const category = ($modal.find('.item-filter-category').val() || '').toString().trim().toLowerCase();
        const code = ($modal.find('.item-filter-code').val() || '').toString().trim().toLowerCase();
        const description = ($modal.find('.item-filter-description').val() || '').toString().trim().toLowerCase();
        const discountFilter = ($modal.find('.item-filter-discount').val() || '').toString().trim();

        return getSourceItems().filter(item => {
            const meta = getItemMeta(item);
            const categoryValue = String(meta.categoryLabel || '').toLowerCase();
            const codeValue = String(meta.itemCode || '').toLowerCase();
            const descValue = String(meta.description || '').toLowerCase();
            const discountValue = parseFloat(meta.discount || 0) || 0;

            if (category && categoryValue !== category) return false;
            if (code && !codeValue.includes(code)) return false;
            if (description && !descValue.includes(description)) return false;
            if (discountFilter === 'has' && discountValue <= 0) return false;
            if (discountFilter === 'none' && discountValue > 0) return false;
            return true;
        });
    }

    function updateItemSelectOptions() {
        $ctx.find('.item-name').each(function () {
            const $select = $(this);
            const $row = $select.closest('tr');
            const currentValue = $select.val();
            const currentCategory = String($row.find('.item-category').val() || '').trim().toLowerCase();
            let filteredItems = getFilteredItems();

            if (currentCategory) {
                filteredItems = filteredItems.filter(item => {
                    const categoryValue = String(getItemMeta(item).categoryLabel || '').trim().toLowerCase();
                    return categoryValue === currentCategory;
                });
            }

            const optionsHtml = buildItemOptionsHtml(filteredItems);

            if (filteredItems.length || getSourceItems().length) {
                $select.empty();
                $select.append('<option value="" selected disabled>Select Item</option>');
                $select.append(optionsHtml);
                if (currentValue) {
                    $select.val(currentValue);
                }
            }

            if (currentValue && !$select.find(`option[value="${currentValue}"]`).length) {
                $select.val('');
                $row.find('.item-picker-input').val('');
                $row.find('.item-code').val('');
                $row.find('.item-desc').val('');
                $row.find('.item-rate').val('0');
                $row.find('.item-amount').val('0');
                $row.find('.item-discount').val('0');
                $row.find('.item-discount-pct').val('');
            }

            syncItemPickerInput($row);
        });
    }

    // Delete row functionality
    $ctx.on('click', '.delete-row-icon', function() {
        if ($ctx.find('.item-rows tr').length > 1) {
            $(this).closest('tr').remove();
            reindexRows();
            calculateTotals();
        } else {
            const $row = $(this).closest('tr');
            $row.find('input').val('');
            $row.find('.item-qty, .item-rate, .item-amount').val('0');
            calculateTotals();
        }
    });

    function reindexRows() {
        $ctx.find('.item-rows tr').each(function(index) {
            $(this).find('.row-index-text').text(index + 1);
        });
    }

    // Auto-fill price/unit and qty when item is selected
    function restoreRichItemDropdownLabels() {
        $ctx.find('.item-name option').each(function() {
            const richLabel = $(this).data('rich-label');
            if (richLabel) {
                $(this).text(richLabel);
            }
        });
    }

    function collapseSelectedItemLabel($select) {
        restoreRichItemDropdownLabels();
        const $selected = $select.find('option:selected');
        const plainLabel = $selected.data('label');
        if (plainLabel) {
            $selected.text(plainLabel);
        }
    }

    function ensureUnitOption($unitSelect, unit) {
        const normalizedUnit = (unit || '').toString().trim();
        if (!normalizedUnit) return;

        let $option = $unitSelect.find('option').filter(function() {
            return ($(this).val() || $(this).text()).toString().trim() === normalizedUnit;
        }).first();

        if (!$option.length) {
            $option = $('<option></option>').val(normalizedUnit).text(normalizedUnit);
            $unitSelect.append($option);
        }

        $unitSelect.find('option').prop('selected', false);
        $option.prop('selected', true);
        $unitSelect.val(normalizedUnit);
    }

    $ctx.on('focus mousedown', '.item-name', function() {
        restoreRichItemDropdownLabels();
    });

    $ctx.on('blur', '.item-name', function() {
        collapseSelectedItemLabel($(this));
    });

    $ctx.on('change', '.item-name', function() {
        const $row = $(this).closest('tr');
        const $selected = $(this).find('option:selected');
        const price = parseFloat($selected.data('price')) || parseFloat($selected.data('sale-price')) || 0;
        const weight = parseFloat($selected.data('weight')) || 0;
        const unit = $selected.data('unit') || '';
        const category = $selected.data('category') || '';
        const itemCode = $selected.data('item-code') || '';
        const description = $selected.data('description') || '';
        const discount = $selected.data('discount');
        const selectedItemId = String($selected.val() || '').trim();
        const selectedItem = getSourceItems().find((item) => String(item.id) === selectedItemId);
        const bagWeight = parseFloat(selectedItem?.bag_weight ?? $selected.attr('data-bag-weight') ?? 0) || 0;

        const $qty = $row.find('.item-qty');
        // Always default selected item quantity to 1 when item is chosen
        $qty.val(1);
        $row.attr('data-bag-weight', bagWeight.toFixed(2));

        // Populate the new fields
        $row.find('.item-bag_weight').val(bagWeight.toFixed(2));
        $row.find('.item-tafseel').val('');
        $row.find('.gross-w-input').val('0');
        $row.find('.net-w-input').val('0');
        $row.find('.item-rate').val(price.toFixed(2));

        syncRowCategoryOptions($row, category);
        $row.find('.item-code').val(itemCode);
        $row.find('.item-desc').val(description);
        if (discount !== undefined && discount !== null && discount !== '') {
            const currentDiscount = parseFloat($row.find('.item-discount').val() || 0) || 0;
            if (currentDiscount === 0) {
                $row.find('.item-discount').val(discount);
            }
            updateRowDiscountPercentFromAmount($row);
        }
        if (unit) {
            ensureUnitOption($row.find('.item-unit'), unit);
        } else {
            $row.find('.item-unit').val('');
        }

        syncItemPickerInput($row);
        $row.find('.item-picker-panel').removeClass('open');
        updateMarketRowAmount($row);
        calculateTotals();
    });

    const loadItemsIfNeeded = ($row, query) => {
        if (getSourceItems().length) {
            return false;
        }

        refreshItemsList().then(() => {
            renderItemPicker($row, query);
        }).catch(() => {
            renderItemPicker($row, query);
        });

        return true;
    };

    $ctx.on('focus click', '.item-picker-input', function() {
        const $row = $(this).closest('tr');
        if ($row.data('suppressItemPickerOpen')) {
            $row.removeData('suppressItemPickerOpen');
            return;
        }
        const rawQuery = String($(this).val() || '').trim();
        const selectedLabel = String($row.find('.item-name option:selected').data('label') || '').trim().toLowerCase();
        const query = (rawQuery.toLowerCase() === 'select item' || (selectedLabel && rawQuery.toLowerCase() === selectedLabel))
            ? ''
            : rawQuery;
        if (loadItemsIfNeeded($row, query)) {
            return;
        }
        renderItemPicker($row, query);
    });

    $ctx.on('input', '.item-picker-input', function() {
        const $row = $(this).closest('tr');
        const rawQuery = String($(this).val() || '').trim();
        if (rawQuery === '') {
            clearSelectedItemRow($row);
            if (loadItemsIfNeeded($row, '')) {
                return;
            }
            renderItemPicker($row, '');
            updateMarketRowAmount($row);
            calculateTotals();
            return;
        }
        const query = rawQuery.toLowerCase() === 'select item' ? '' : rawQuery;
        if (loadItemsIfNeeded($row, query)) {
            return;
        }
        renderItemPicker($row, query);
    });

    // Track selected item in picker for pre-filling modal
    window.selectedItemForModal = null;

    $ctx.on('click', '.item-picker-option', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $row = $(this).closest('tr');
        const itemId = String($(this).data('id') || '');

        // Get item name from the clicked element (item-picker-option)
        const $itemName = $(this).find('.item-picker-name');
        let itemName = $itemName.text().trim();

        // Extract only the item name (remove item code if present)
        if (itemName.includes('(')) {
            itemName = itemName.split('(')[0].trim();
        }

        // Store selected item for modal
        window.selectedItemForModal = {
            id: itemId,
            name: itemName
        };

        // Log to verify (can remove later)
        console.log('Selected item for modal:', window.selectedItemForModal);

        $row.data('suppressItemPickerOpen', true);
        $row.find('.item-picker-input').val(itemName || 'Search Item').blur();
        $row.find('.item-picker-panel').removeClass('open');
        hideItemPickerPanels();
        $row.find('.item-name').val(itemId).trigger('change');
        refreshQuickEntryUi();
    });

    $ctx.on('click', '.quick-entry-confirm', function(e) {
        e.preventDefault();
        e.stopPropagation();
        commitQuickEntryRow($(this).closest('tr'));
    });

    $(document).off('click', '.unit-option').on('click', '.unit-option', function(e) {
        e.preventDefault();
        const unit = $(this).data('unit') || $(this).text().trim();
        $('#newItemUnitBtn').text(unit);
        $('#newItemUnit').val(unit);
    });

    $(document).on('click', '#assignItemCodeBtn', function(e) {
        e.preventDefault();
        const itemName = String($('#newItemName').val() || '').trim();
        const normalized = itemName.toUpperCase().replace(/\s+/g, '-').replace(/[^A-Z0-9-_]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '').substring(0, 24);
        const suffix = String(Math.floor(1000 + Math.random() * 9000));
        const code = normalized ? `${normalized}-${suffix}`.substring(0, 50) : `ITEM-${suffix}`;
        $('#newItemCode').val(code);
    });

    $ctx.on('click', '.item-picker-add', function() {
        const $row = $(this).closest('tr');
        window.activeSaleItemRowIndex = $ctx.find('.item-row').index($row);

        // Close all item picker dropdowns when opening modal
        hideItemPickerPanels();

        // Clear form
        document.getElementById('addItemForm').reset();
        $('#newItemUnitBtn').text('Select Unit');
        $('#newItemUnit').val('');
        renderNewItemUnitMenu();

        // Pre-fill item name from selected item or search input
        let itemNameToFill = '';

        // First priority: selected item from picker
        if (window.selectedItemForModal && window.selectedItemForModal.name) {
            itemNameToFill = window.selectedItemForModal.name;
            window.selectedItemForModal = null; // Reset after use
        } else {
            // Second priority: search text in the input field
            const searchText = String($row.find('.item-picker-input').val() || '').trim();
            if (searchText && searchText.toLowerCase() !== 'search item') {
                itemNameToFill = searchText;
            }
        }

        if (itemNameToFill) {
            $('#newItemName').val(itemNameToFill);
        }

        // Show modal
        const modalEl = document.getElementById('addItemModal');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    $(document).on('click.saleItemPicker', function(e) {
        // Don't close dropdowns if modal is open
        if ($('#addItemModal').hasClass('show')) {
            return;
        }
        if (!$(e.target).closest('.item-picker').length) {
            hideItemPickerPanels();
        }
    });

    // Prevent dropdown from closing when clicking inside panel or input
    $ctx.on('click', '.item-picker-input, .item-picker-panel', function(e) {
        e.stopPropagation();
    });

    // Handle modal events to control item dropdown behavior
    $('#addItemModal').on('show.bs.modal', function() {
        // Close all item picker dropdowns when modal opens
        hideItemPickerPanels();
        refreshUnitsList($('#newItemUnit').val() || '');
        const pricingTabEl = document.getElementById('pricing-tab');
        const stockTabEl = document.getElementById('stock-tab');
        const pricingTabPane = document.getElementById('pricing-tab-pane');
        const stockTabPaneEl = document.getElementById('stock-tab-pane');

        if (pricingTabEl && pricingTabPane) {
            const pricingTab = bootstrap.Tab.getOrCreateInstance(pricingTabEl);
            pricingTab.show();
            $(pricingTabEl).attr('aria-selected', 'true');
            $(pricingTabPane).addClass('active show').show();
        }
        if (stockTabEl && stockTabPaneEl) {
            $(stockTabEl).removeClass('active').attr('aria-selected', 'false');
            $(stockTabPaneEl).removeClass('active show').show();
        }
    });

    $('#addItemModal').on('hidden.bs.modal', function() {
        // Clear form when modal is closed
        document.getElementById('addItemForm').reset();
        $('#newItemUnitBtn').text('Select Unit');
        $('#newItemUnit').val('');
        $('#newItemCategory').val('');
        $('#newItemType').val('product');
        $('#newItemTypeToggle').prop('checked', false);
        $('#newItemProductLabel').text('Product');
        $('#newItemNameLabel').text('Item Name *');
        $('#stock-tab').show().removeClass('active').attr('aria-selected', 'false');
        $('#pricing-tab').addClass('active').attr('aria-selected', 'true');
        $('#stock-tab-pane').removeClass('active show').show();
        $('#pricing-tab-pane').addClass('active show').show();
        $('#purchase-sec').show();
        const thumb = document.getElementById('newItemImageThumb');
        const label = document.getElementById('newItemImageLabel');
        if (thumb) {
            thumb.innerHTML = '<i class="fa-regular fa-image fa-2x text-secondary"></i>';
            thumb.style.border = '1.5px solid #93c5fd';
        }
        if (label) {
            label.textContent = 'Click to choose image';
        }
    });

    $('#newItemCategory').on('change', function() {
        if ($(this).val() !== '__add_new__') {
            return;
        }

        $(this).val('');
        $('#quickCategoryName').val('');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addCategoryModal')).show();
        setTimeout(() => $('#quickCategoryName').trigger('focus'), 150);
    });

    $(document).on('click', '#newItemUnitBtn', function(e) {
        e.preventDefault();
        populateUnitSelectionModal();
        $('#newItemBaseUnitSelect').val($('#newItemUnit').val() || '');
        $('#newItemSecondaryUnitSelect').val($('#newItemSecondaryUnit').val() || '');
        $('#newItemUnitConversionInput').val($('#newItemUnitConversionRate').val() || '0');
        updateNewItemUnitButton();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).show();
    });

    $(document).on('change', '#newItemBaseUnitSelect, #newItemSecondaryUnitSelect', function() {
        $('.base-unit-preview').text($('#newItemBaseUnitSelect').val() ? `1 ${$('#newItemBaseUnitSelect').val()}` : '1 Base Unit');
        $('.secondary-unit-preview').text($('#newItemSecondaryUnitSelect').val() || 'Secondary Unit');
    });

    $(document).on('click', '#saveSelectedUnitsBtn', function(e) {
        e.preventDefault();
        $('#newItemUnit').val($('#newItemBaseUnitSelect').val() || '');
        $('#newItemSecondaryUnit').val($('#newItemSecondaryUnitSelect').val() || '');
        $('#newItemUnitConversionRate').val($('#newItemUnitConversionInput').val() || 0);
        updateNewItemUnitButton();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).hide();
    });

    let reopenUnitSelectorAfterQuickAdd = false;

    $(document).on('click', '#openAddUnitModalBtn, .open-add-unit-from-selector', function(e) {
        e.preventDefault();
        $('#quickUnitName').val('');
        $('#quickUnitShortName').val('');
        if ($(this).hasClass('open-add-unit-from-selector')) {
            reopenUnitSelectorAfterQuickAdd = true;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).hide();
        } else {
            reopenUnitSelectorAfterQuickAdd = false;
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addUnitModal')).show();
        setTimeout(() => $('#quickUnitName').trigger('focus'), 150);
    });

    $(document).off('click', '#saveQuickCategoryBtn').on('click', '#saveQuickCategoryBtn', function() {
        const name = String($('#quickCategoryName').val() || '').trim();
        if (!name) {
            alert('Please enter a category name');
            return;
        }

        fetchJson(itemRoutes.categoryStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ name })
        })
        .then(data => {
            if (!data.category) {
                throw new Error('Category not returned');
            }

            const category = data.category;
            const $categorySelect = $('#newItemCategory');
            const $existing = $categorySelect.find(`option[value="${category.id}"]`);
            if (!$existing.length) {
                $categorySelect.find('option[value="__add_new__"]').before(
                    `<option value="${category.id}">${category.name}</option>`
                );
            }
            $categorySelect.val(String(category.id));
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addCategoryModal')).hide();
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'Error saving category');
        });
    });

    $(document).off('click', '#saveQuickUnitBtn').on('click', '#saveQuickUnitBtn', function() {
        const name = String($('#quickUnitName').val() || '').trim();
        const shortName = String($('#quickUnitShortName').val() || '').trim().toUpperCase();

        if (!name || !shortName) {
            alert('Please enter both unit name and short name');
            return;
        }

        fetchJson(itemRoutes.unitsStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ name, short_name: shortName })
        })
        .then(data => {
            const unitCode = String(data.unit?.short_name || shortName).toUpperCase();
            window.saleUnits = Array.isArray(data.units) ? data.units : getNormalizedSaleUnits();
            renderNewItemUnitMenu(unitCode);
            syncItemUnitSelects();
            if (!$('#newItemUnit').val()) {
                $('#newItemUnit').val(unitCode);
            }
            populateUnitSelectionModal();
            $('#newItemBaseUnitSelect').val($('#newItemUnit').val() || unitCode);
            updateNewItemUnitButton();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addUnitModal')).hide();
            if (reopenUnitSelectorAfterQuickAdd) {
                reopenUnitSelectorAfterQuickAdd = false;
                if (!$('#newItemSecondaryUnit').val()) {
                    $('#newItemSecondaryUnitSelect').val(unitCode);
                }
                setTimeout(() => {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('selectItemUnitModal')).show();
                }, 180);
            }
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'Error saving unit');
        });
    });

    // Handle saving new item
    $(document).off('click', '#saveNewItemBtn').on('click', '#saveNewItemBtn', function() {
        const itemName = document.getElementById('newItemName').value.trim();
        if (!itemName) {
            alert('Please enter an item name');
            return;
        }

        const formData = new FormData();
        formData.append('name', itemName);
        formData.append('category_id', document.getElementById('newItemCategory').value);
        formData.append('unit', document.getElementById('newItemUnit').value);
        formData.append('secondary_unit', document.getElementById('newItemSecondaryUnit')?.value || '');
        formData.append('unit_conversion_rate', document.getElementById('newItemUnitConversionRate')?.value || 0);
        const newItemType = document.getElementById('newItemType')?.value || 'product';
        formData.append('item_type', newItemType);
        formData.append('type', newItemType);
        formData.append('sale_price', document.getElementById('newItemSalePrice').value || 0);
        formData.append('purchase_price', document.getElementById('newItemPurchasePrice').value || 0);
        formData.append('wholesale_price', document.getElementById('newItemWholesalePrice').value || 0);
        formData.append('wholesale_min_qty', document.getElementById('newItemWholesaleMinQty').value || 0);
        formData.append('item_code', document.getElementById('newItemCode').value);
        formData.append('opening_qty', document.getElementById('newItemStock').value || 0);
        formData.append('at_price', document.getElementById('newItemAtPrice').value || 0);
        formData.append('as_of_date', document.getElementById('newItemAsOfDate').value);
        formData.append('bag_weight', document.getElementById('newItemBagWeight').value || 0);
        formData.append('min_stock', document.getElementById('newItemMinStock').value || 0);
        formData.append('location', document.getElementById('newItemLocation').value);
        formData.append('description', document.getElementById('newItemDescription').value);

        // Add image if selected
        const imageInput = document.getElementById('newItemImage');
        if (imageInput && imageInput.files.length > 0) {
            formData.append('item_image', imageInput.files[0]);
        }
        const stockImagesInput = document.getElementById('newItemStockImages');
        if (stockImagesInput && stockImagesInput.files.length > 0) {
            Array.from(stockImagesInput.files).forEach((file) => formData.append('item_images[]', file));
        }

        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetchJson(itemRoutes.store, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(data => {
            if (data.item) {
                // Refresh the item picker from the server so the list stays consistent.
                refreshItemsList(data.item);
                window.refreshLedgerAdjustmentAccountMenus?.();

                // Close modal
                const modalEl = document.getElementById('addItemModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();

                // Add the new item to the current row if applicable
                if (window.activeSaleItemRowIndex !== undefined) {
                    const $targetRow = $ctx.find('.item-row').eq(window.activeSaleItemRowIndex);
                    $targetRow.find('.item-name').val(data.item.id).trigger('change');
                }

                alert('Item saved successfully!');
            } else {
                alert('Error saving item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Error saving item');
        });
    });

    $(window).on('resize scroll', function() {
        $ctx.find('.item-row').each(function() {
            positionItemPickerPanel($(this));
        });
    });

    syncItemUnitSelects();
    renderNewItemUnitMenu();
    refreshUnitsList();
    refreshItemsList();

    $(document).on('click', '.open-item-stock-images-picker', function(e) {
        e.preventDefault();
        document.getElementById('newItemStockImages')?.click();
    });

    $(document).on('change', '#newItemStockImages', function() {
        const files = Array.from(this.files || []);
        const html = files.map(file => {
            const url = URL.createObjectURL(file);
            return `<div class="item-stock-image-card"><img src="${url}" alt="${file.name}"><div class="name">${file.name}</div></div>`;
        }).join('');
        $('#newItemStockImagesList').html(html);
    });

    // Line item calculation
    $ctx.on('keyup change', '.item-qty, .item-rate, .gross-w-input, .net-w-input, .item-discount, .item-discount-pct, .item-tax-pct, .item-tax-amount', function() {
        const $row = $(this).closest('tr');
        const isPercentField = $(this).hasClass('item-discount-pct');
        const isAmountField = $(this).hasClass('item-discount');
        const isTaxPercentField = $(this).hasClass('item-tax-pct');
        const isTaxAmountField = $(this).hasClass('item-tax-amount');

        if (isPercentField) {
            updateRowDiscountFromPercent($row);
        } else if (isAmountField) {
            updateRowDiscountPercentFromAmount($row);
        } else if (($row.find('.item-discount-pct').val() || '').toString().trim() !== '') {
            updateRowDiscountFromPercent($row);
        } else {
            updateRowDiscountPercentFromAmount($row);
        }

        if (isTaxPercentField) {
            updateRowTaxFromPercent($row);
        } else if (isTaxAmountField) {
            updateRowTaxPercentFromAmount($row);
        } else if (($row.find('.item-tax-pct').val() || '').toString().trim() !== '') {
            updateRowTaxFromPercent($row);
        } else {
            updateRowTaxPercentFromAmount($row);
        }

        updateMarketRowAmount($row);
        calculateTotals();
    });

    $ctx.on('keyup change', '.item-mrp-input', function() {
        const $row = $(this).closest('tr');
        const mrp = parseFloat($row.find('.item-mrp-input').val() || 0) || 0;
        if (itemFormSettings.mrp?.calculate_sale_price_from_mrp) {
            const discount = parseFloat($row.find('.item-discount').val() || 0) || 0;
            const rate = Math.max(mrp - discount, 0);
            $row.find('.item-rate').val(rate.toFixed(2));
        } else if (!(parseFloat($row.find('.item-rate').val() || 0) || 0)) {
            $row.find('.item-rate').val(mrp.toFixed(2));
        }
        updateMarketRowAmount($row);
        calculateTotals();
    });

    $ctx.on('focus', '.item-unit', function() {
        $(this).attr('data-last-value', $(this).val() || '');
    });

    $ctx.on('change', '.item-unit', function() {
        const $select = $(this);
        const value = String($select.val() || '').trim();
        if (value === '__add_unit__') {
            const previousValue = String($select.attr('data-last-value') || '').trim();
            $select.val(previousValue);
            reopenUnitSelectorAfterQuickAdd = true;
            $('#quickUnitName').val('');
            $('#quickUnitShortName').val('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addUnitModal')).show();
            setTimeout(() => $('#quickUnitName').trigger('focus'), 150);
            return;
        }
        $select.attr('data-last-value', value);
    });

    let activeScanSerialRow = null;
    $(document).on('click', '.open-scan-serial-modal', function(e) {
        e.preventDefault();
        activeScanSerialRow = $(this).closest('tr');
        $('#scanSerialInput').val(activeScanSerialRow.find('.item-serial-input').val() || activeScanSerialRow.find('.item-code').val() || '');
        $('.scan-serial-count').text(`${String($('#scanSerialInput').val() || '').trim() ? 1 : 0} Entered`);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('scanSerialModal')).show();
    });

    $(document).on('input', '#scanSerialInput', function() {
        $('.scan-serial-count').text(`${String($(this).val() || '').trim() ? 1 : 0} Entered`);
    });

    $(document).on('click', '#confirmScanSerialBtn, #saveScanSerialBtn', function() {
        if (!activeScanSerialRow) return;
        const value = String($('#scanSerialInput').val() || '').trim();
        if (activeScanSerialRow.find('.item-serial-input').length) {
            activeScanSerialRow.find('.item-serial-input').val(value);
        } else {
            activeScanSerialRow.find('.item-code').val(value);
        }
        if (this.id === 'saveScanSerialBtn') {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('scanSerialModal')).hide();
        }
    });

    // Payment entry management
    $ctx.off('click', '.add-payment-entry').on('click', '.add-payment-entry', function(e) {
        e.preventDefault();

        const $defaultAmount = $ctx.find('.default-payment-amount');
        const $defaultReference = $ctx.find('.default-payment-reference');

        // If amount/reference are hidden, show them (this happens on first click)
        if ($defaultAmount.hasClass('d-none') || $defaultReference.hasClass('d-none')) {
            $defaultAmount.removeClass('d-none').focus();
            $defaultReference.removeClass('d-none');
            updatePaymentSummary();
            return;
        }

        // Otherwise, add a new payment row
        const template = document.getElementById('payment-entry-template');
        if (!template) return;

        const clone = template.content.cloneNode(true);
        $ctx.find('.payment-entries').append(clone);

        // Ensure the newly added row is visible and focused for value entry
        const $newEntry = $ctx.find('.payment-entries .payment-entry').last();
        $newEntry.find('.payment-direction-entry').val(defaultPaymentDirection);
        $newEntry.find('.payment-amount').focus();
    });

    // Toast helper
    function showToast(message, isError = false) {
        const toastEl = document.getElementById('sale-toast');
        if (!toastEl) return;

        const toastBody = toastEl.querySelector('.toast-body');
        toastBody.textContent = message;

        toastEl.classList.toggle('text-bg-success', !isError);
        toastEl.classList.toggle('text-bg-danger', isError);

        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
    }

    function validateSaleBeforeSubmit(saleData) {
        if (!saleData.party_id) {
            showToast('Please select party before saving.', true);
            return false;
        }

        const invalidItemRow = Array.from($ctx.find('.item-row')).find(row => {
            const $row = $(row);
            const hasContent = Boolean(
                $row.find('.item-name').val() ||
                parseFloat($row.find('.item-qty').val() || 0) > 0 ||
                parseFloat($row.find('.item-rate').val() || 0) > 0 ||
                parseFloat($row.find('.item-amount').val() || 0) > 0
            );

            return hasContent && !$row.find('.item-name').val();
        });

        if (invalidItemRow || !saleData.items.length) {
            const itemsCount = $ctx.find('.item-row').length;
            if (itemsCount > 0) {
                showToast('Please select items or fill in item details before saving.', true);
            } else {
                showToast('Please select at least one item before saving.', true);
            }
            return false;
        }

        return true;
    }

    // Update payment summary when default payment type is changed
    $ctx.on('change', '.default-payment-type', function() {
        syncDefaultPaymentFields();
        updatePaymentSummary();
    });

    // Ensure amount and reference inputs are kept visible for all payment rows
    $ctx.off('change', '.payment-type-entry').on('change', '.payment-type-entry', function() {
        updatePaymentSummary();
    });

    function normalizeDateForApi(rawValue) {
        const value = String(rawValue || '').trim();
        if (!value) return '';

        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }

        const slashMatch = value.match(/^(\d{1,4})\/(\d{1,2})\/(\d{1,4})$/);
        if (!slashMatch) {
            return value;
        }

        let a = parseInt(slashMatch[1], 10);
        let b = parseInt(slashMatch[2], 10);
        let c = parseInt(slashMatch[3], 10);

        let year;
        let month;
        let day;

        if (String(slashMatch[1]).length === 4) {
            year = a;
            month = b;
            day = c;
        } else {
            year = c;
            if (a > 12) {
                day = a;
                month = b;
            } else if (b > 12) {
                month = a;
                day = b;
            } else {
                month = a;
                day = b;
            }
        }

        if (!year || !month || !day || month < 1 || month > 12 || day < 1 || day > 31) {
            return value;
        }

        const yyyy = String(year).padStart(4, '0');
        const mm = String(month).padStart(2, '0');
        const dd = String(day).padStart(2, '0');

        return `${yyyy}-${mm}-${dd}`;
    }

    // Helper: collect data from form
    function gatherSaleData() {
        const items = Array.from($ctx.find('.item-row')).map(row => {
            const $row = $(row);
            const $selectedOption = $row.find('.item-name option:selected');
            let itemName = String($selectedOption.data('label') || $selectedOption.text() || '').trim();
            if (!itemName) {
                itemName = String($row.attr('data-temp-item-name') || '').trim();
            }
            return {
                item_name: itemName,
                item_category: $row.find('.item-category').val() || '',
                item_code: $row.find('.item-code').val() || '',
                item_description: $row.find('.item-desc').val() || '',
                tafseel: $row.find('.item-tafseel').val() || '',
                quantity: parseInt($row.find('.item-qty').val() || 0, 10) || 0,
                gross_w: parseFloat($row.find('.gross-w-input').val() || 0) || 0,
                net_w: parseFloat($row.find('.net-w-input').val() || 0) || 0,
                unit: $row.find('.item-unit').val() || '',
                unit_price: parseFloat($row.find('.item-rate').val() || 0) || 0,
                discount: parseFloat($row.find('.item-discount').val() || 0) || 0,
                tax_pct: parseFloat($row.find('.item-tax-pct').val() || 0) || 0,
                tax_amount: parseFloat($row.find('.item-tax-amount').val() || 0) || 0,
                free_qty: parseFloat($row.find('.item-free-qty').val() || 0) || 0,
                amount: parseFloat($row.find('.item-amount').val() || 0) || 0,
                extra_fields: {
                    serial_no: $row.find('.item-serial-input').val() || '',
                    count: parseFloat($row.find('.item-count-input').val() || 0) || 0,
                    batch_no: $row.find('.item-batch-no-input').val() || '',
                    model_no: $row.find('.item-model-no-input').val() || '',
                    exp_date: $row.find('.item-exp-date-input').val() || '',
                    mfg_date: $row.find('.item-mfg-date-input').val() || '',
                    mrp: parseFloat($row.find('.item-mrp-input').val() || 0) || 0,
                    size: $row.find('.item-size-input').val() || '',
                    custom_field_1: $row.find('.item-custom-field-1-input').val() || '',
                    custom_field_2: $row.find('.item-custom-field-2-input').val() || '',
                    custom_field_3: $row.find('.item-custom-field-3-input').val() || '',
                    custom_field_4: $row.find('.item-custom-field-4-input').val() || '',
                    custom_field_5: $row.find('.item-custom-field-5-input').val() || '',
                    custom_field_6: $row.find('.item-custom-field-6-input').val() || ''
                }
            };
        }).filter(item => {
            const hasName = item.item_name && String(item.item_name).trim() !== '';
            const hasQtyOrRate = item.quantity > 0 || item.unit_price > 0 || item.amount > 0;
            return hasName || hasQtyOrRate;
        });

        const payments = [];
        const $marketRow = $ctx.find('.item-row').first();

        // Default payment type (amount + reference shown when selected)
        const defaultTypeVal = $ctx.find('.default-payment-type').val();
        if (defaultTypeVal) {
            const isCash = defaultTypeVal === 'cash';
            const isCheque = defaultTypeVal === 'cheques';
            const bankId = defaultTypeVal.startsWith('bank-') ? parseInt(defaultTypeVal.replace('bank-', ''), 10) : null;
            const bank = bankId ? (window.bankAccounts || []).find(b => b.id === bankId) : null;
            const defaultAmount = parseFloat($ctx.find('.default-payment-amount').val() || 0) || 0;
            const defaultReference = $ctx.find('.default-payment-reference').val() || null;
            const defaultDirection = $ctx.find('.default-payment-direction').val() || 'payment_in';

            if (defaultAmount > 0) {
                payments.push({
                    direction: defaultDirection,
                    payment_type: isCheque ? 'Cheques' : (isCash ? 'cash' : (bank?.display_with_account || bank?.display_name || 'Bank')),
                    bank_account_id: bankId || null,
                    amount: defaultAmount,
                    reference: defaultReference,
                });
            }
        }

        // Additional payment rows (with amount)
        Array.from($ctx.find('.payment-entry')).forEach(entry => {
            const $entry = $(entry);
            const rawType = $entry.find('.payment-type-entry').val() || '';
            const isBank = rawType.startsWith('bank-');
            const isCash = rawType === 'cash';
            const isCheque = rawType === 'cheques';
            const bankId = isBank ? rawType.replace('bank-', '') : null;
            const bank = isBank ? (window.bankAccounts || []).find(b => String(b.id) === String(bankId)) : null;

            const amount = parseFloat($entry.find('.payment-amount').val() || 0) || 0;
            const reference = $entry.find('.payment-reference').val() || null;
            const direction = $entry.find('.payment-direction-entry').val() || 'payment_in';
            if (!rawType || amount <= 0) return;

            payments.push({
                direction,
                payment_type: isCheque ? 'Cheques' : (isCash ? 'cash' : (isBank ? (bank?.display_with_account || bank?.display_name || 'Bank') : rawType)),
                bank_account_id: bankId,
                amount: amount,
                reference: reference,
            });
        });

        const previousInvoiceExtraFields = (() => {
            const fromDetails = window.editSaleData?.details?.invoice_extra_fields;
            const fromRoot = window.editSaleData?.invoice_extra_fields;
            return (fromDetails && typeof fromDetails === 'object')
                ? fromDetails
                : ((fromRoot && typeof fromRoot === 'object') ? fromRoot : {});
        })();

        return {

            type: window.docType || $ctx.find('.doc-type').val() || 'invoice',

            source_estimate_id: isDuplicateSaleMode ? null : (window.sourceEstimateId || window.editSaleData?.source_estimate_id || null),
            source_sale_order_id: isDuplicateSaleMode ? null : (window.sourceSaleOrderId || window.editSaleData?.source_sale_order_id || null),
            source_challan_id: isDuplicateSaleMode ? null : (window.sourceChallanId || window.editSaleData?.source_challan_id || null),
            source_proforma_id: isDuplicateSaleMode ? null : (window.sourceProformaId || window.editSaleData?.source_proforma_id || null),
            party_id: $ctx.find('.party-id').val() || $ctx.find('.party-select').val() || null,
            broker_id: $ctx.find('.broker-id').val() || null,
            brokerage_type: $ctx.find('.brokerage-type').val() || null,
            brokerage_rate: parseFloat($ctx.find('.brokerage-base-amount').val() || $ctx.find('.brokerage-rate').val() || 0) || 0,
            broker_amount: parseFloat($ctx.find('.brokerage-amount').val() || 0) || 0,
            party_name: String($ctx.find('.billing-name-input').val() || '').trim() || getPartyDropdownDisplay() || $ctx.find('.party-select option:selected').text() || '',
            phone: document.getElementById('pscPhone')?.value || $ctx.find('.phone-input').val() || '',
            warehouse_id: $ctx.find('.warehouse-select').val() || null,
            delivery_person: $ctx.find('.delivery-person-input').val() || '',
            bilti_no: $ctx.find('.bilti-no-input').val() || '',
            gate_no: $ctx.find('.gate-no-input').val() || '',
            po_no: $ctx.find('.po-no-input').val() || '',
            po_date: normalizeDateForApi($ctx.find('.po-date-input').val() || ''),
            city: $ctx.find('.city-input').val() || '',
            party_no: $ctx.find('.party-no-input').val() || '',
            goods_name: $ctx.find('.goods-name-input').val() || '',
            details_extra: $ctx.find('.details-extra-input').val() || '',
            bilti_gari_no: $ctx.find('.bilti-gari-input').val() || '',
            terms_condition_name: $ctx.find('.terms-condition-select').val() || '',
            terms_condition_text: $ctx.find('.terms-condition-text').val() || '',
            terms_condition_templates: normalizeTermsConditionTemplates(termsConditionTemplates),
            invoice_extra_fields: {
                ...previousInvoiceExtraFields,
                ...serializeInvoiceExtraFields(),
            },
            payment_term_name: String($ctx.find('.settings-payment-term-name').val() || saleFormSettings.payment_terms?.name || '').trim(),
            payment_term_days: parseInt($ctx.find('.settings-payment-term-days').val() || saleFormSettings.payment_terms?.days || 0, 10) || 0,
            additional_charges: serializeAdditionalCharges(),
            transportation_details: serializeTransportationDetails(),
            billing_address: document.getElementById('pscBilling')?.value || $ctx.find('.billing-address').val() || '',
shipping_address: document.getElementById('pscShipping')?.value || $ctx.find('.shipping-address').val() || '',
            bill_number: $ctx.find('.bill-number').val() || '',
            invoice_date: normalizeDateForApi($ctx.find('.invoice-date').val() || ''),
            order_date: normalizeDateForApi($ctx.find('.order-date').val() || ''),
            deal_days: (function() {
                const selectedValue = $ctx.find('.due-days-select').val();
                if (selectedValue === 'custom') {
                    return parseInt($ctx.find('.due-days-custom').val() || 0, 10) || 0;
                }
                return parseInt(selectedValue || 0, 10) || 0;
            })(),
            due_date: normalizeDateForApi($ctx.find('.due-date').val() || ''),
            tadad: parseInt($ctx.find('.parachi-input').val() || 0, 10) || 0,
            total_wazan: parseFloat($ctx.find('.total-wazan-input').val() || 0) || 0,
            safi_wazan: parseFloat($ctx.find('.safi-wazan-input').val() || 0) || 0,
            rate: parseFloat($ctx.find('.rate-input').val() || 0) || 0,
            deo: parseFloat($ctx.find('.deo-input').val() || 0) || 0,
            total_qty: parseInt($ctx.find('.parachi-input').val() || $ctx.find('.total-qty').text() || 0, 10) || 0,
            total_amount: parseFloat($ctx.find('.total-base-amount').text() || 0) || 0,
            labour: parseFloat($ctx.find('.labour-input').val() || 0) || 0,
            bardana: parseFloat($ctx.find('.bardana-input').val() || 0) || 0,
            rehra_mazdori: parseFloat($ctx.find('.rehra-mazdori-input').val() || 0) || 0,
            post_expense: parseFloat($ctx.find('.post-expense-input').val() || 0) || 0,
            extra_expense: parseFloat($ctx.find('.extra-expense-input').val() || 0) || 0,
            custom_expenses: serializeCustomExpenseRows(),
            discount_pct: parseFloat($ctx.find('.discount-pct').val() || 0) || 0,
            discount_rs: parseFloat($ctx.find('.discount-rs').val() || 0) || 0,
            tax_pct: parseFloat($ctx.find('.tax-select').val() || 0) || 0,
            tax_amount: parseFloat($ctx.find('.tax-amount-display').text() || 0) || 0,
            round_off: parseFloat($ctx.find('.round-off-val').val() || 0) || 0,
            grand_total: parseFloat($ctx.find('.grand-total').val() || 0) || 0,
            description: $ctx.find('.description-input').val() || null,
            image_path: selectedImages.length ? selectedImages[0].name : (window.editSaleData?.image_path || null),
            image_paths: selectedImages.map(file => file.name),
            document_path: selectedDocuments.length ? selectedDocuments[0].name : (window.editSaleData?.document_path || null),
            document_paths: selectedDocuments.map(file => file.name),
            items,
            payments,
        };
    }

    function submitSale(btn, options = {}) {
        const saleData = gatherSaleData();

        const persistedTheme = getPersistedInvoiceThemeState();
        if (persistedTheme) {
            saleData.invoice_extra_fields = saleData.invoice_extra_fields || {};
            saleData.invoice_extra_fields.theme_mode = String(persistedTheme.mode || 'regular');
            saleData.invoice_extra_fields.theme_regular_theme_id = parseInt(persistedTheme.regularThemeId || 1, 10) || 1;
            saleData.invoice_extra_fields.theme_thermal_theme_id = parseInt(persistedTheme.thermalThemeId || 1, 10) || 1;
            saleData.invoice_extra_fields.theme_accent = String(persistedTheme.accent || '#1f4e79');
            saleData.invoice_extra_fields.theme_accent2 = String(persistedTheme.accent2 || '#ff981f');
        }

        // Include custom header names in the submission
        const savedHeaders = JSON.parse(localStorage.getItem('itemTableHeaders') || '{}');
        if (Object.keys(savedHeaders).length > 0) {
            saleData.custom_headers = savedHeaders;
        }

        const redirectToShare = Boolean(options.redirectToShare);
        const idleText = options.idleText || 'Save';
        const loadingText = options.loadingText || 'Saving...';
        const successMessage = options.successMessage || 'Sale saved successfully! Redirecting...';

        if (!saleData.items.length) {
            alert('Please add at least one item before saving.');
            return;
        }

        if (!validateSaleBeforeSubmit(saleData)) {
            return;
        }

        btn.prop('disabled', true).text(loadingText);

        const hasUploadFiles = selectedImages.length > 0 || selectedDocuments.length > 0;
        let requestBody;
        const requestHeaders = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        };

        if (hasUploadFiles) {
            const formData = new FormData();
            Object.entries(saleData).forEach(([key, value]) => {
                if (value === undefined || value === null) {
                    return;
                }
                if (typeof value === 'object') {
                    formData.append(key, JSON.stringify(value));
                    return;
                }
                formData.append(key, value);
            });
            selectedImages.forEach(imageFile => formData.append('images[]', imageFile));
            selectedDocuments.forEach(docFile => formData.append('documents[]', docFile));
            requestBody = formData;
        } else {
            requestHeaders['Content-Type'] = 'application/json';
            requestBody = JSON.stringify(saleData);
        }

        fetch(window.saleStoreUrl, {
            method: window.saleMethod || 'POST',
            headers: requestHeaders,
            body: requestBody,
        })
            .then(async res => {
                const text = await res.text();
                let data = null;

                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text);
                }

                if (!res.ok) {
                    const message = (data && data.message) ? data.message : 'Server error';
                    throw new Error(message);
                }

                return data;
            })
            .then(data => {
                if (data && data.success) {
                    localStorage.removeItem(getCustomExpenseStorageKey());
                    if (data.bill_number) {
                        $ctx.find('.bill-number').val(data.bill_number);
                    }

                    showToast(successMessage, false);

                    const targetUrl = redirectToShare ? (data.share_url || data.redirect_url) : data.redirect_url;

                    if (targetUrl) {
                        setTimeout(() => {
                            window.location.href = targetUrl;
                        }, 2000);
                    }

                    return;
                }

                console.error(data);
                showToast('Unable to save sale. See console for details.', true);
            })
            .catch(err => {
                console.error(err);
                showToast(err.message || 'Error saving sale.', true);
            })
            .finally(() => {
                btn.prop('disabled', false).text(idleText);
            });
    }

    // Save button
    $ctx.on('click', '.btn-save', function() {
        const isSaleOrderFlow = getActiveDocType() === 'sale_order';
        const isReturnAfterInvoice = isSaleOrderFlow && new URLSearchParams(window.location.search).get('from_sale_order') === '1';
        submitSale($(this), {
            redirectToShare: isSaleOrderFlow && !isReturnAfterInvoice,
            idleText: 'Save',
            loadingText: 'Saving...',
            successMessage: isSaleOrderFlow
                ? (isReturnAfterInvoice
                    ? 'Invoice saved successfully! Returning to sale orders...'
                    : 'Sale order saved successfully! Opening invoice...')
                : 'Sale saved successfully! Opening invoice preview...',
        });
    });

    // Save & Share button
    $ctx.on('click', '.btn-share-main', function() {
        submitSale($(this), {
            redirectToShare: true,
            idleText: 'Save & Share',
            loadingText: 'Saving & Sharing...',
            successMessage: 'Sale saved successfully! Opening invoice preview...',
        });
    });

    $closeIcon.off('click.saleClose').on('click.saleClose', function () {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/dashboard/sales';
        }
    });

    // Add description/image/document actions
    renderTermsConditionOptions();

    $ctx.off('click', '.open-terms-condition-modal, .add-terms-condition').on('click', '.open-terms-condition-modal, .add-terms-condition', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!termsConditionModal) return;

        const selectedName = String($ctx.find('.terms-condition-select').val() || '').trim();
        const selectedText = String($ctx.find('.terms-condition-text').val() || '').trim();
        $('#termsConditionNameInput').val(selectedName && selectedName !== '__add_new__' ? selectedName : '');
        $('#termsConditionDescriptionInput').val(selectedText);
        $('.terms-applicable-check').prop('checked', false);
        const selectedTemplate = normalizeTermsConditionTemplates(termsConditionTemplates).find(template => template.name === selectedName);
        const applicable = selectedTemplate?.applicable_for?.length ? selectedTemplate.applicable_for : [currentDocType];
        applicable.forEach(value => {
            $(`.terms-applicable-check[value="${value}"]`).prop('checked', true);
        });
        termsConditionModal.show();
    });

    $ctx.off('change', '.terms-condition-select').on('change', '.terms-condition-select', function() {
        const value = String($(this).val() || '').trim();
        if (value === '__add_new__') {
            $(this).val('');
            $ctx.find('.terms-condition-text').val('');
            $ctx.find('.add-terms-condition').trigger('click');
            return;
        }

        if (!value) {
            $ctx.find('.terms-condition-text').val('');
            return;
        }

        const selectedTemplate = normalizeTermsConditionTemplates(termsConditionTemplates).find(template => template.name === value);
        if (selectedTemplate) {
            $ctx.find('.terms-condition-text').val(selectedTemplate.description || '');
        } else {
            $ctx.find('.terms-condition-text').val('');
        }
    });

    $('#saveTermsConditionBtn').off('click').on('click', function() {
        const name = String($('#termsConditionNameInput').val() || '').trim();
        const description = String($('#termsConditionDescriptionInput').val() || '').trim();
        const applicableFor = $('.terms-applicable-check:checked').map(function() {
            return String($(this).val() || '').trim();
        }).get();

        if (!name || !description) {
            alert('Please enter terms name and description.');
            return;
        }

        fetchJson(window.termsConditionStoreUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name,
                description,
                applicable_for: applicableFor.length ? applicableFor : [currentDocType]
            })
        }).then(data => {
            const template = data?.template || {
                name,
                description,
                applicable_for: applicableFor.length ? applicableFor : [currentDocType]
            };
            termsConditionTemplates = normalizeTermsConditionTemplates([
                ...termsConditionTemplates.filter(existingTemplate => existingTemplate.name !== template.name),
                template
            ]);
            window.transactionTermsTemplates = termsConditionTemplates;
            setTermsConditionSelection(template.name, template.description);
            termsConditionModal?.hide();
            showToast('Terms & conditions saved successfully.', false);
        }).catch(error => {
            alert(error.message || 'Unable to save terms & conditions.');
        });
    });

    $ctx.off('click', '.add-description').on('click', '.add-description', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $button = $(this);
        const $container = $button.closest('.bottom-left, .invoice-container, body');
        const $pane = $container.find('.description-pane').first();
        if (!$pane.length) {
            return;
        }
        $pane.toggleClass('d-none');
        $button.toggleClass('d-none');
        if (!$pane.hasClass('d-none')) {
            $pane.find('.description-input').focus();
        }
    });

    $ctx.off('click', '.add-image').on('click', '.add-image', function() {
        const $container = $(this).closest('.invoice-container');
        $container.find('.image-input').trigger('click');
    });

    $ctx.off('click', '.add-document').on('click', '.add-document', function() {
        const $container = $(this).closest('.invoice-container');
        $container.find('.document-input').trigger('click');
    });

    function renderSelectedImages() {
        if (!existingImages.length && !selectedImages.length) {
            $imageFilesList.empty();
            return;
        }

        const existingHtml = existingImages.map((item) => `
            <div class="image-file-card position-relative border rounded overflow-hidden bg-white">
                <img src="${item.url || ''}" alt="${item.name || 'Image'}" class="img-fluid" style="width:120px;height:120px;object-fit:cover;" />
                <div class="small text-truncate p-1 text-center" style="max-width:120px;">${item.name || 'Image'}</div>
            </div>
        `).join('');

        const newHtml = selectedImages.map((file, index) => {
            const url = URL.createObjectURL(file);
            return `
                <div class="image-file-card position-relative border rounded overflow-hidden" data-index="${index}">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-1 remove-selected-image" aria-label="Remove" data-index="${index}"></button>
                    <img src="${url}" alt="${file.name}" class="img-fluid" style="width:120px;height:120px;object-fit:cover;" />
                    <div class="small text-truncate p-1 text-center" style="max-width:120px;">${file.name}</div>
                </div>
            `;
        }).join('');

        $imageFilesList.html(existingHtml + newHtml);
    }

    function renderSelectedDocuments() {
        if (!existingDocuments.length && !selectedDocuments.length) {
            $documentFilesList.empty();
            return;
        }

        const existingHtml = existingDocuments.map((item) => `
            <div class="list-group-item d-flex justify-content-between align-items-center bg-light" data-existing="1">
                <span class="text-truncate" style="max-width: 100%;">${item.name || 'Document'}</span>
            </div>
        `).join('');

        const newHtml = selectedDocuments.map((file, index) => {
            return `
                <div class="list-group-item d-flex justify-content-between align-items-center" data-index="${index}">
                    <span class="text-truncate" style="max-width: calc(100% - 32px);">${file.name}</span>
                    <button type="button" class="btn-close remove-selected-document" aria-label="Remove" data-index="${index}"></button>
                </div>
            `;
        }).join('');

        $documentFilesList.html(existingHtml + newHtml);
    }

    function addSelectedImages(files) {
        selectedImages.length = 0;
        const firstFile = Array.from(files || [])[0];
        if (firstFile) {
            selectedImages.push(firstFile);
        }
        renderSelectedImages();
    }

    function addSelectedDocuments(files) {
        selectedDocuments.length = 0;
        const firstFile = Array.from(files || [])[0];
        if (firstFile) {
            selectedDocuments.push(firstFile);
        }
        renderSelectedDocuments();
    }

    $ctx.on('change', '.image-input', function() {
        addSelectedImages(this.files);
        this.value = '';
    });

    $ctx.on('change', '.document-input', function() {
        addSelectedDocuments(this.files);
        this.value = '';
    });

    $ctx.on('click', '.image-placeholder', function() {
        $ctx.find('.image-input').trigger('click');
    });

    $ctx.on('click', '.replace-image', function() {
        $ctx.find('.image-input').trigger('click');
    });

    $ctx.on('click', '.remove-selected-image', function() {
        const index = Number($(this).data('index'));
        selectedImages.splice(index, 1);
        renderSelectedImages();
    });

    $ctx.on('click', '.remove-selected-document', function() {
        const index = Number($(this).data('index'));
        selectedDocuments.splice(index, 1);
        renderSelectedDocuments();
    });

    function calculateTotals() {
        let totalQty = 0;
        let totalFreeQty = 0;
        let totalGrossW = 0;
        let totalNetW = 0;
        let totalBaseAmount = 0;

        $ctx.find('.item-row').each(function() {
            const $row = $(this);
            const qty = parseFloat($row.find('.item-qty').val() || 0) || 0;
            const freeQty = parseFloat($row.find('.item-free-qty').val() || 0) || 0;
            const rate = parseFloat($row.find('.item-rate').val() || 0) || 0;
            const itemDiscount = parseFloat($row.find('.item-discount').val() || 0) || 0;
            const grossW = parseFloat($row.find('.gross-w-input').val() || 0) || 0;
            const netW = parseFloat($row.find('.net-w-input').val() || 0) || 0;

            totalQty += qty;
            totalFreeQty += freeQty;
            totalGrossW += grossW;
            totalNetW += netW;

            const rowAmount = Math.max((netW * rate) - itemDiscount, 0);

            $row.find('.item-amount').val(rowAmount.toFixed(2));
            totalBaseAmount += rowAmount;
        });

        $ctx.find('.total-qty').text(totalQty);
        $ctx.find('.total-free-qty').text(totalFreeQty);
        $ctx.find('.total-gross-w').text(totalGrossW.toFixed(2));
        $ctx.find('.total-net-w').text(totalNetW.toFixed(2));
        $ctx.find('.total-base-amount').text(totalBaseAmount.toFixed(2));

        updateBrokerageFields();
        applyDiscountTax(totalBaseAmount);
    }

    // Discount and Tax logic
    $ctx.on('keyup change', '.discount-pct, .discount-rs, .tax-select, .round-off-check', function() {
        calculateTotals();
    });

    function applyDiscountTax(base) {
        let finalBase = base;
        const totalsSettings = saleFormSettings.transaction_totals || {};
        const discountEnabled = !!totalsSettings.discount_enabled;
        const taxEnabled = !!totalsSettings.tax_enabled;
        const roundTotalEnabled = !!totalsSettings.round_total_enabled;

        const discPct = discountEnabled ? (parseFloat($ctx.find('.discount-pct').val()) || 0) : 0;
        if (discountEnabled && discPct > 0) {
            finalBase -= (finalBase * discPct / 100);
        }

        const discRs = discountEnabled ? (parseFloat($ctx.find('.discount-rs').val()) || 0) : 0;
        if (discountEnabled && discRs > 0) {
            finalBase -= discRs;
        }

        const taxPct = taxEnabled ? (parseFloat($ctx.find('.tax-select').val()) || 0) : 0;
        let taxAmount = 0;
        if (taxEnabled && taxPct > 0) {
            taxAmount = (finalBase * taxPct / 100);
            finalBase += taxAmount;
        }
        $ctx.find('.tax-amount-display').text(taxAmount.toFixed(2));

        // Add summary expenses
        const parachi = parseFloat($ctx.find('.parachi-input').val() || 0) || 0;
        const rateExpense = parseFloat($ctx.find('.rate-input').val() || 0) || 0;
        const deo = parseFloat($ctx.find('.deo-input').val() || 0) || 0;
        const bardana = parseFloat($ctx.find('.bardana-input').val() || 0) || 0;
        const labour = parseFloat($ctx.find('.labour-input').val() || 0) || 0;
        const rehraMazdori = parseFloat($ctx.find('.rehra-mazdori-input').val() || 0) || 0;
        const postExpense = parseFloat($ctx.find('.post-expense-input').val() || 0) || 0;
        const extraExpense = parseFloat($ctx.find('.extra-expense-input').val() || 0) || 0;
        const customExpenseTotal = getCustomExpenseTotal(finalBase);

        const totalExpenses = parachi + rateExpense + deo + bardana + labour + rehraMazdori + postExpense + extraExpense + customExpenseTotal;
        finalBase += totalExpenses;
        finalBase += getAdditionalChargesTotal();
        updateAdditionalChargeRowTotals();

        const roundOffEnabled = roundTotalEnabled && $ctx.find('.round-off-check').is(':checked');
        let roundOffVal = roundOffEnabled ? (parseFloat($ctx.find('.round-off-val').val()) || 0) : 0;
        let grandTotal = finalBase + roundOffVal;

        $ctx.find('.round-off-val').val(roundOffVal.toFixed(2));
        $ctx.find('.grand-total').val(grandTotal.toFixed(2));

        // Update payment summary (total payments / received / balance) whenever grand total changes
        updatePaymentSummary();
    }

    function updatePaymentSummary() {
        const grandTotal = parseFloat($ctx.find('.grand-total').val() || 0) || 0;

        // Received amount starts from existing sale payments when editing
        let received = 0;
        if (window.editSaleData) {
            received += parseFloat(window.editSaleData.received_amount || 0) || 0;
        }

        // Include the default payment row (first row) as additional payment when editing
        const defaultType = $ctx.find('.default-payment-type').val() || '';
        if (defaultType.startsWith('bank-') || defaultType === 'cash' || defaultType === 'cheques') {
            received += parseFloat($ctx.find('.default-payment-amount').val() || 0) || 0;
        }

        // Include additional payment entries
        received += Array.from($ctx.find('.payment-type-entry')).reduce((sum, el) => {
            const rawType = $(el).val() || '';
            const isBank = rawType.startsWith('bank-');
            const isCash = rawType === 'cash';
            const isCheque = rawType === 'cheques';
            if (!isBank && !isCash && !isCheque) return sum;

            const amountInput = $(el).closest('.payment-entry').find('.payment-amount');
            return sum + (parseFloat(amountInput.val() || 0) || 0);
        }, 0);

        if ($ctx.find('.fill-balance-check').is(':checked')) {
            received = grandTotal;
        }

        const balance = Math.max(0, grandTotal - received);

        $ctx.find('.payment-total-amount').text(received.toFixed(2));
        $paidInput.val(received.toFixed(2));
        $ctx.find('.balance-amount').text(balance.toFixed(2));
    }

    // Recalculate payment summary when payments change
    $ctx.on('keyup change', '.default-payment-amount, .payment-amount', updatePaymentSummary);
    $ctx.on('change', '.default-payment-direction, .payment-direction-entry', updatePaymentSummary);
    $ctx.on('change input', '.brokerage-type, .brokerage-rate', function() {
        updateBrokerageFields();
        calculateTotals();
    });
    $ctx.on('change', '.fill-balance-check, .round-off-check', function() {
        setupAdjustmentControls();
        calculateTotals();
    });
    $ctx.on('input change', '.round-off-val', calculateTotals);
    $ctx.on('input change', '.custom-expense-pct, .custom-expense-value, .custom-expense-details', function() {
        persistCustomExpenseRows();
        calculateTotals();
    });
    $ctx.on('input blur', '.custom-expense-heading', persistCustomExpenseRows);
    $ctx.on('input blur', '.editable-expense-label[data-expense-key]', saveExpenseLabels);
    $ctx.on('click', '.add-custom-expense-row', function() {
        createCustomExpenseRow();
        persistCustomExpenseRows();
    });
    $ctx.on('click', '.remove-custom-expense-row', function() {
        $(this).closest('.custom-expense-row').remove();
        persistCustomExpenseRows();
        calculateTotals();
    });

    // Update when payment rows are removed
    $ctx.off('click', '.remove-payment-entry').on('click', '.remove-payment-entry', function() {
        $(this).closest('.payment-entry').remove();
        updatePaymentSummary();
    });

    setupAdjustmentControls();
    syncDefaultPaymentFields();
    setupPartyDropdownSearch();
    setupBrokerDropdownSearch();
    loadExpenseLabels();
    loadCustomExpenseRows();
    refreshLedgerAccountMenus();
    window.refreshLedgerAdjustmentAccountMenus = refreshLedgerAccountMenus;
    document.getElementById('addPartyModal')?.addEventListener('hidden.bs.modal', function () {
        window.refreshLedgerAdjustmentAccountMenus?.();
    });

    $ctx.on('click', '.custom-mode-btn', function() {
        const $button = $(this);
        const mode = $button.data('mode') || '+';
        const $row = $button.closest('.custom-expense-row');
        $row.find('.custom-mode-btn').removeClass('is-active');
        $button.addClass('is-active');
        $row.find('.custom-expense-mode').val(mode);
        persistCustomExpenseRows();
        calculateTotals();
    });

    $ctx.on('input focus', '.custom-expense-account-input', function() {
        const $input = $(this);
        const $menu = $input.closest('.custom-expense-account-wrap').find('.ledger-account-menu');
        renderLedgerAccountMenu($menu, $input.val() || '');
    });

    $ctx.on('click', '.ledger-account-option', function(e) {
        e.preventDefault();
        const $option = $(this);
        const $row = $option.closest('.custom-expense-row');
        const $input = $row.find('.custom-expense-account-input');
        const accountType = $option.data('accountType') || '';
        const accountId = $option.data('accountId') || '';
        const accountName = String($option.data('accountName') || '').trim();
        const accountPhone = String($option.data('accountPhone') || '').trim();
        const commissionRate = parseFloat($option.data('commissionRate') || 0) || 0;

        $row.find('.custom-expense-account-type').val(accountType);
        $row.find('.custom-expense-account-id').val(accountId);
        $row.find('.custom-expense-account-phone').val(accountPhone);
        $input.val(accountName);

        if (accountType === 'broker') {
            $row.find('.custom-expense-heading').text('');
            $row.find('.custom-expense-pct').val(commissionRate > 0 ? commissionRate.toFixed(2) : '');
        }

        persistCustomExpenseRows();
        calculateTotals();

        const dropdownToggle = $input.get(0);
        if (dropdownToggle) {
            const dropdown = bootstrap.Dropdown.getInstance(dropdownToggle) || bootstrap.Dropdown.getOrCreateInstance(dropdownToggle);
            dropdown.hide();
        }
    });

    $ctx.on('click', '.ledger-account-action', function(e) {
        e.preventDefault();
        const action = $(this).data('action') || '';

        if (action === 'party') {
            const addPartyModalEl = document.getElementById('addPartyModal');
            if (addPartyModalEl) {
                bootstrap.Modal.getOrCreateInstance(addPartyModalEl).show();
            }
            return;
        }

        if (action === 'broker') {
            if (typeof window.openBrokerModalForm === 'function') {
                window.openBrokerModalForm();
            } else {
                const brokerModalEl = document.getElementById('brokerModal');
                if (brokerModalEl) {
                    bootstrap.Modal.getOrCreateInstance(brokerModalEl).show();
                }
            }
            return;
        }

        if (action === 'item') {
            window.activeSaleItemRowIndex = -1;
            const addItemModalEl = document.getElementById('addItemModal');
            if (addItemModalEl) {
                bootstrap.Modal.getOrCreateInstance(addItemModalEl).show();
            }
        }
    });

    $ctx.on('click', '.broker-option', function(e) {
        e.preventDefault();
        const $option = $(this);
        const brokerId = $option.data('id');
        const brokerName = String($option.data('name') || $option.find('.broker-option-name').text() || '').trim();
        const brokerPhone = String($option.data('phone') || $option.find('.broker-option-phone').text() || '').trim();

        $ctx.find('.broker-id').val(brokerId);
        $ctx.find('#brokerDropdownBtn').val(brokerName || '').attr('placeholder', brokerName || 'Broker');
        $ctx.find('.broker-selected-name').text(brokerName || '');
        $ctx.find('.broker-selected-phone').text(brokerPhone || '').closest('.broker-selected-info').toggleClass('visible', !!brokerPhone);
        $ctx.find('.broker-phone-input').val(brokerPhone);

        const dropdownToggle = $option.closest('.broker-dropdown-wrapper').find('.broker-search-input').get(0);
        if (dropdownToggle) {
            const dropdown = bootstrap.Dropdown.getInstance(dropdownToggle) || bootstrap.Dropdown.getOrCreateInstance(dropdownToggle);
            dropdown.hide();
        }
    });

    $ctx.on('submit', '#brokerForm', function(e) {
        e.preventDefault();
        const $form = $(this);
        const url = $form.attr('action');
        const formData = new FormData(this);

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json().catch(() => null);
            if (!response.ok || !data?.success) {
                const message = data?.message || 'Unable to save broker. Please try again.';
                alert(message);
                return;
            }

            const broker = data.broker;
            if (!broker) {
                alert('Broker saved, but response was invalid.');
                return;
            }

            window.brokers = Array.isArray(window.brokers) ? window.brokers.concat([broker]) : [broker];
            const optionHtml = `<li><a class="dropdown-item d-flex justify-content-between align-items-center broker-option" href="#" data-id="${broker.id}" data-phone="${broker.phone || ''}" data-name="${broker.name || ''}" data-commission-rate="${broker.commission_rate ?? 0}"><div class="broker-option-name">${broker.name || ''}</div><div class="broker-option-city text-muted small">${broker.city || '-'}</div></a></li>`;
            const $dropdownMenu = $ctx.find('#brokerDropdownMenu');
            $dropdownMenu.find('li:last-child').before(optionHtml);

            $ctx.find('.broker-id').val(broker.id);
            $ctx.find('#brokerDropdownBtn').val(broker.name || '').attr('placeholder', broker.name || 'Broker');
            $ctx.find('.broker-selected-name').text(broker.name || '');
            $ctx.find('.broker-selected-phone').text(broker.phone || '').closest('.broker-selected-info').toggleClass('visible', !!broker.phone);
            $ctx.find('.broker-phone-input').val(broker.phone || '');
            window.refreshLedgerAdjustmentAccountMenus?.();

            const brokerModalEl = document.getElementById('brokerModal');
            bootstrap.Modal.getOrCreateInstance(brokerModalEl).hide();
        })
        .catch(() => {
            alert('Unable to save broker. Please try again.');
        });
    });

    $ctx.on('input change', '#brokerTotalBrokerage, #brokerPaidBrokerage', updateBrokerRemaining);

    applyColumnVisibility();
    updateItemSelectOptions();
    if (!getSourceItems().length) {
        refreshItemsList();
    }
    calculateTotals();
    updateBrokerageFields();

    $(document).on('change', '.check-category, .check-item-code, .check-description, .check-discount', function() {
        applyColumnVisibility();
    });

    $ctx.on('change', '.item-category', function () {
        const $row = $(this).closest('tr');
        updateItemSelectOptions();
        renderItemPicker($row, '');
    });

    $ctx.on('change', '.due-days-select', updateDueDateFromSelection);
    $ctx.on('input', '.due-days-custom', updateDueDateFromSelection);
    $ctx.on('change', '.invoice-date', function() {
        const invoiceDateValue = $(this).val();
        $ctx.find('.order-date').val(invoiceDateValue);
        updateDueDateFromSelection();
    });
    $ctx.on('change', '.order-date', function() {
        const orderDateValue = $(this).val();
        $ctx.find('.invoice-date').val(orderDateValue);
        updateDueDateFromSelection();
    });
    updateDueDateFromSelection();

    $('#itemColumnModal').on('show.bs.modal', function () {
        const $modal = $(this);
        const categories = Array.from(new Set(baseItems.map(item => getItemMeta(item).categoryLabel).filter(Boolean)));
        const $categorySelect = $modal.find('.item-filter-category');
        if ($categorySelect.length) {
            $categorySelect.empty().append('<option value="">Select Category</option>');
            categories.forEach(cat => {
                $categorySelect.append(`<option value="${cat.toString().toLowerCase()}">${cat}</option>`);
            });
        }
    });

    $('#itemColumnModal').on('change', '.check-category, .check-item-code, .check-description, .check-discount', function () {
        const $modal = $('#itemColumnModal');
        $modal.find('.item-filter-category').prop('disabled', !$('.check-category').is(':checked'));
        $modal.find('.item-filter-code').prop('disabled', !$('.check-item-code').is(':checked'));
        $modal.find('.item-filter-description').prop('disabled', !$('.check-description').is(':checked'));
        $modal.find('.item-filter-discount').prop('disabled', !$('.check-discount').is(':checked'));
    });

    $('#itemColumnModal').on('click', '.item-filter-apply', function () {
        applyColumnVisibility();
        updateItemSelectOptions();
    });

    function setAdditionalChargesEditable(isEnabled) {
        const $modal = $('#additionalChargesModal');
        const disabled = !isEnabled;
        $modal.find('.additional-charge-input, .additional-charge-tax, .additional-charge-tax-check, .additional-charge-check').prop('disabled', disabled);
    }

    $('#additionalChargesModal').on('shown.bs.modal', function () {
        const isEnabled = $('#additionalChargesToggle').is(':checked');
        setAdditionalChargesEditable(isEnabled);
    });

    $(document).on('change', '#additionalChargesToggle', function () {
        setAdditionalChargesEditable($(this).is(':checked'));
    });

    $(document).on('input change', '.additional-charge-input', function () {
        const label = String($(this).val() || '').trim() || 'Charge';
        $(this).closest('.additional-charge-block').find('.form-check-label.small').text(`Enable tax for ${label}`);
    });

    $ctx.on('input change', '.additional-charge-live-input, .additional-charge-live-tax', function() {
        calculateTotals();
    });

    $(document).on('click', '.sale-settings-expand-item, .sale-settings-expand-header', function(e) {
        if ($(e.target).closest('.sale-settings-expand-toggle').length) return;
        if ($(e.target).is('input, select, option, button, label')) return;
        $(this).closest('.sale-settings-expand-item').toggleClass('is-open');
    });

    $(document).on('click', '.sale-settings-expand-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.sale-settings-expand-item').toggleClass('is-open');
    });

    $(document).on('click', '.sale-prefix-toggle-btn, .sale-settings-prefix-header', function(e) {
        if ($(e.target).closest('.settings-prefix-enabled').length) return;
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.sale-settings-prefix-block').toggleClass('is-open');
    });

    $(document).on('click', '.sale-payment-terms-item', function(e) {
        if ($(e.target).is('input, select, option, button, a, label')) return;
        $(this).toggleClass('is-open');
    });

    $(document).on('click', '.open-payment-terms-panel', function(e) {
        e.preventDefault();
        $(this).closest('.sale-payment-terms-item').addClass('is-open');
    });

    $(document).on('change', '.sale-prefix-select', function() {
        const prefix = String($(this).val() || '').trim().toUpperCase();
        saleFormSettings.sale_prefix.active = prefix || saleFormSettings.sale_prefix.active;
        uiFind('.settings-prefix-select').val(prefix);
        applySelectedPrefixToBillNumber(prefix);
    });

    $(document).on('change', '.settings-prefix-select', function() {
        const prefix = String($(this).val() || '').trim().toUpperCase();
        uiFind('.sale-prefix-select').val(prefix);
        applySelectedPrefixToBillNumber(prefix);
    });

    $(document).on('input', '.settings-prefix-input', function() {
        const typedPrefix = String($(this).val() || '').trim().toUpperCase();
        if (!typedPrefix) {
            updatePrefixPreview();
            return;
        }
        const currentNumber = String(uiFind('.bill-number').val() || '').trim().replace(/^[A-Z]+-?/i, '') || '1';
        uiFind('.settings-prefix-preview').text(`${typedPrefix}-${currentNumber}`);
    });

    $(document).on('change', '.settings-prefix-enabled', function() {
        persistSaleFormSettings({ silent: true }).catch(() => {});
    });

    $(document).on('change', '.settings-quick-entry', function() {
        saleFormSettings.quick_entry = $(this).is(':checked');
        saleFormSettings.more_transaction_features = saleFormSettings.more_transaction_features || {};
        saleFormSettings.more_transaction_features.quick_entry = saleFormSettings.quick_entry;
        refreshQuickEntryUi();
        persistSaleFormSettings({ silent: true }).catch(() => {});
    });

    $(document).on('click', '.save-prefix-settings-btn', function() {
        persistSaleFormSettings();
    });

    $(document).on('change', '.settings-payment-terms-enabled', function() {
        const isEnabled = $(this).is(':checked');
        uiFind('.sale-payment-terms-item').toggleClass('is-open', isEnabled);
        if (isEnabled) {
            const days = parseInt(uiFind('.settings-payment-term-days').val() || 0, 10) || 0;
            setDueDaysSelectionValue(days);
        }
        persistSaleFormSettings({ silent: true }).catch(() => {});
    });

    $(document).on('change blur', '.settings-payment-term-days', function() {
        if (uiFind('.settings-payment-terms-enabled').is(':checked')) {
            setDueDaysSelectionValue(parseInt($(this).val() || 0, 10) || 0);
        }
    });

    $(document).on('blur', '.settings-payment-term-name, .settings-payment-term-days', function() {
        persistSaleFormSettings({ silent: true }).catch(() => {});
    });

    $(document).on('change', '.settings-quick-entry, .settings-link-payments', function() {
        persistSaleFormSettings({ silent: true }).catch(() => {});
    });

    $(document).on('click', '.save-sale-settings-btn', function(e) {
        e.preventDefault();
        persistSaleFormSettings();
    });

    $(document).on('click', '.save-additional-charges-btn', function(e) {
        e.preventDefault();
        persistSaleFormSettings().then(() => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('additionalChargesModal')).hide();
            calculateTotals();
        }).catch(() => {});
    });

    renderTermsConditionOptions();
    syncSaleSettingsControls();
    if (!window.editSaleData && saleFormSettings.payment_terms.enabled) {
        setDueDaysSelectionValue(saleFormSettings.payment_terms.days || 0);
    }
    refreshQuickEntryUi();
}
