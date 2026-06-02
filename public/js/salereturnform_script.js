function initializeForm(context) {
    const $ctx = $(context);
    const docType = window.docType || 'sale_return';
    const docLabel = docType === 'purchase_return' ? 'purchase return' : 'sale return';
    const docLabelTitle = docType === 'purchase_return' ? 'Purchase return' : 'Sale return';
    const isDuplicateSaleReturnMode = Boolean(window.isDuplicateSaleReturnMode);
    const hasCustomPartyDropdown = $ctx.find('.party-id').length > 0;
    const $paidInput = $ctx.find('.received-amount, .advance-amount').first();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const itemOptionsHtml = (window.items || []).map(item => {
        const plainLabel = item.name || ""; const richLabel = `${plainLabel} | Sale: ${item.sale_price ?? item.price ?? 0} | Stock: ${item.opening_qty ?? 0} | Location: ${item.location ?? ""}`; return `<option value="${item.id}" data-price="${item.price ?? ""}" data-sale-price="${item.sale_price ?? ""}" data-stock="${item.opening_qty ?? ""}" data-location="${item.location ?? ""}" data-label="${plainLabel}" data-rich-label="${richLabel}" data-unit="${item.unit || ''}" data-category="${item.category_name || item.category?.name || item.category || item.category_id || ''}" data-item-code="${item.item_code || ''}" data-description="${item.description || item.item_description || ''}" data-discount="${item.discount ?? 0}">${richLabel}</option>`;
    }).join('');

    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayValue = `${yyyy}-${mm}-${dd}`;
    let selectedImages = [];
    let selectedDocuments = [];
    const imageObjectUrls = new Set();

    function revokeImageUrls() {
        imageObjectUrls.forEach(url => URL.revokeObjectURL(url));
        imageObjectUrls.clear();
    }

    function renderImagePreviews() {
        const $list = $ctx.find('.image-files-list');
        if (!$list.length) return;

        revokeImageUrls();
        if (!selectedImages.length) {
            $list.empty();
            return;
        }

        const html = selectedImages.map((file, index) => {
            const url = URL.createObjectURL(file);
            imageObjectUrls.add(url);
            return `
                <div class="position-relative border rounded p-1 bg-white" style="width: 110px;">
                    <img src="${url}" alt="${file.name}" class="img-fluid rounded" style="width: 100%; height: 80px; object-fit: cover;">
                    <div class="small text-truncate mt-1" title="${file.name}">${file.name}</div>
                    <button type="button" class="btn btn-sm btn-light position-absolute top-0 end-0 remove-selected-image" data-index="${index}" style="width: 22px; height: 22px; line-height: 1; padding: 0;">&times;</button>
                </div>
            `;
        }).join('');

        $list.html(html);
    }

    function renderDocumentPreviews() {
        const $list = $ctx.find('.document-files-list');
        if (!$list.length) return;

        if (!selectedDocuments.length) {
            $list.empty();
            return;
        }

        const html = selectedDocuments.map((file, index) => `
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                <div class="text-truncate me-2" style="max-width: calc(100% - 36px);" title="${file.name}">
                    <i class="fa-solid fa-file-lines me-2 text-secondary"></i>${file.name}
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-selected-document" data-index="${index}" style="width: 28px; height: 28px; padding: 0; line-height: 1;">&times;</button>
            </div>
        `).join('');

        $list.html(html);
    }

    $ctx.find('.order-date').val(todayValue);
    $ctx.find('.due-date').val(todayValue);

    function formatDateForDisplay(value) {
        if (!value) return '';
        const parts = value.split('-');
        if (parts.length !== 3) return value;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function bindDateMirror(hiddenSelector, textSelector) {
        const $hidden = $ctx.find(hiddenSelector).first();
        const $text = $ctx.find(textSelector).first();
        const $icon = $text.closest('.purchase-doc-row').find('.purchase-doc-icon').first();

        if (!$hidden.length || !$text.length) {
            return;
        }

        $text.val(formatDateForDisplay($hidden.val()));

        const openPicker = function () {
            const input = $hidden.get(0);
            if (!input) return;
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.click();
            }
        };

        $text.off('.dateMirror');
        $hidden.off('.dateMirror');
        $icon.off('.dateMirror');

        $text.on('focus.dateMirror click.dateMirror', openPicker);
        $icon.on('click.dateMirror', openPicker);
        $hidden.on('change.dateMirror', function () {
            $text.val(formatDateForDisplay($hidden.val()));
        });
    }

    bindDateMirror('.order-date', '.order-date-text');
    bindDateMirror('.due-date', '.due-date-text');

    if (window.editSaleReturnData) {
        populateFormFromSaleReturn(window.editSaleReturnData);
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

        if (docType !== 'purchase_return' && $paidInput.length && !$ctx.find('.fill-balance-check').length) {
            $paidInput.closest('.calc-inputs').prepend(
                `<label class="d-flex align-items-center gap-1 me-2 mb-0 text-nowrap" style="font-size:12px;">
                    <input type="checkbox" class="fill-balance-check">
                    <span>Full Advance</span>
                </label>`
            );
        }
    }

    function showToast(message, isError = false) {
        const toastEl = document.getElementById('sale-toast');
        if (!toastEl) return;

        const toastBody = toastEl.querySelector('.toast-body');
        toastBody.textContent = message;
        toastEl.classList.toggle('text-bg-success', !isError);
        toastEl.classList.toggle('text-bg-danger', isError);
        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 5000 }).show();
    }

    function reindexRows() {
        $ctx.find('.item-rows tr').each(function(index) {
            $(this).find('.row-index-text').text(index + 1);
        });
    }

    function populateFormFromSaleReturn(saleReturn) {
        const sourceDate = docType === 'purchase_return'
            ? (saleReturn.bill_date || saleReturn.order_date || saleReturn.invoice_date || todayValue)
            : (saleReturn.order_date || saleReturn.invoice_date || todayValue);
        const sourceDueDate = saleReturn.due_date || sourceDate || todayValue;

        if (hasCustomPartyDropdown) {
            const party = (window.parties || []).find(p => String(p.id) === String(saleReturn.party_id || ''));
            $ctx.find('.party-id').val(saleReturn.party_id || '');
            if (party) {
                $ctx.find('#partyDropdownBtn').text(party.name || 'Select Party');
                $ctx.find('.phone-input').val(party.phone || saleReturn.phone || '');
                $ctx.find('.billing-address').val(party.billing_address || saleReturn.billing_address || '');
            } else {
                $ctx.find('#partyDropdownBtn').text('Select Party');
            }
        } else {
            const partyOption = $ctx.find('.party-select option').filter(function () {
                return $(this).val() == (saleReturn.party_id || '');
            }).first();

            if (partyOption.length) {
                partyOption.prop('selected', true);
                partyOption.trigger('change');
            }
        }

        $ctx.find('.phone-input').val(saleReturn.phone || '');
        $ctx.find('.billing-address').val(saleReturn.billing_address || '');
        $ctx.find('.shipping-address').val(saleReturn.shipping_address || '');
        $ctx.find('.source-sale-id').val(saleReturn.source_sale_id || '');
        $ctx.find('.bill-number').val(saleReturn.bill_number || '');
        $ctx.find('.reference-bill-number').val(saleReturn.reference_bill_number || '');
        $ctx.find('.order-date').val(sourceDate);
        $ctx.find('.due-date').val(sourceDueDate);
        $ctx.find('.order-date-text').val(formatDateForDisplay($ctx.find('.order-date').val()));
        $ctx.find('.due-date-text').val(formatDateForDisplay($ctx.find('.due-date').val()));

        $ctx.find('.item-rows').empty();
        (saleReturn.items || []).forEach(item => {
            addRow();
            const $row = $ctx.find('.item-rows tr').last();
            const matchOption = $row.find('.item-name option').filter(function () {
                const optionLabel = (($(this).data('label') || $(this).text()) + '').trim().toLowerCase();
                const itemLabel = ((item.item_name || '') + '').trim().toLowerCase();
                const optionValue = (($(this).val() || '') + '').trim();
                const itemId = ((item.item_id || '') + '').trim();

                return optionLabel === itemLabel || (itemId && optionValue === itemId);
            }).first();

            if (matchOption.length) {
                matchOption.prop('selected', true);
            } else if (item.item_name) {
                $row.find('.item-name').append(
                    `<option value="${item.item_id || ''}" data-label="${item.item_name}" selected>${item.item_name}</option>`
                );
            }

            $row.find('.item-category').val(item.item_category || '');
            $row.find('.item-code').val(item.item_code || '');
            $row.find('.item-desc').val(item.item_description || '');
            $row.find('.item-tafseel').val(item.tafseel || '');
            $row.find('.item-discount').val(item.discount || 0);
            $row.find('.item-qty').val(item.quantity || 1);
            $row.find('.gross-w-input').val(item.gross_w || 0);
            $row.find('.net-w-input').val(item.net_w || 0);
            ensureUnitOption($row.find('.item-unit'), item.unit || 'NONE');
            $row.find('.item-rate').val(item.unit_price || 0);
            $row.find('.item-amount').val(item.amount || 0);
            collapseSelectedItemLabel($row.find('.item-name'));
        });

        $ctx.find('.discount-pct').val(saleReturn.discount_pct || 0);
        $ctx.find('.discount-rs').val(saleReturn.discount_rs || 0);
        $ctx.find('.tax-select').val(saleReturn.tax_pct || 0);
        $ctx.find('.tax-amount-display').text(parseFloat(saleReturn.tax_amount || 0).toFixed(2));
        $ctx.find('.round-off-val').val(parseFloat(saleReturn.round_off || 0).toFixed(2));
        $ctx.find('.grand-total').val(parseFloat(saleReturn.grand_total || 0).toFixed(2));
        $ctx.find('.balance-amount').text(parseFloat(saleReturn.balance || saleReturn.grand_total || 0).toFixed(2));
        $paidInput.val(parseFloat((docType === 'purchase_return' ? saleReturn.paid_amount : saleReturn.received_amount) || 0).toFixed(2));
        $ctx.find('.description-input').val(saleReturn.description || '');

        calculateTotals();
    }

    function addRow() {
        const rowCount = $ctx.find('.item-rows tr').length + 1;
        const optionsHtml = itemOptionsHtml;
        const unitOptionsHtml = `<option value="">Select Unit</option><option value="PCS">PCS (Pieces)</option><option value="BOX">BOX</option><option value="PACK">PACK</option><option value="SET">SET</option><option value="KG">KG (Kilogram)</option><option value="G">Gram</option><option value="M">Meter</option><option value="FT">Feet</option><option value="L">Liter</option><option value="ML">Milliliter</option><option value="__add_unit__">+ Add Unit</option>`;

        const newRow = `
            <tr class="item-row">
                <td class="row-num">
                    <span class="row-index-text">${rowCount}</span>
                    <div class="delete-row-icon"><i class="fa-solid fa-trash-can"></i></div>
                </td>
                <td class="col-item-name">
                    <div class="item-picker">
                        <input type="text" class="item-picker-input" placeholder="Search Item" style="position: relative; z-index: 10;">
                        <div class="item-picker-panel">
                            <div class="item-picker-add" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; color: #2563eb; font-weight: 600; cursor: pointer; border-bottom: 1px solid #e1e8ed;"><i class="fa-regular fa-square-plus"></i> Add Item</div>
                            <div class="item-picker-head" style="display: grid; grid-template-columns: minmax(0, 2fr) 100px 110px 80px 80px; gap: 12px; padding: 10px 18px; font-size: 12px; font-weight: 700; color: #97a3b6; text-transform: uppercase; background: #f8fbff; border-bottom: 1px solid #e1e8ed;">
                                <span>Item</span>
                                <span>Sale Price</span>
                                <span>Purchase Price</span>
                                <span>Stock</span>
                                <span>Weight</span>
                            </div>
                            <div class="item-picker-list" style="max-height: 280px; overflow-y: auto;"></div>
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
                <td class="col-tafseel"><input type="text" class="item-tafseel" placeholder="Detail"></td>
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
                <td class="col-category d-none"><select class="item-category"><option value="">Select Category</option></select></td>
                <td class="col-item-code d-none"><input type="text" class="item-code" placeholder="Item Code" readonly></td>
                <td class="col-discount d-none"><div class="item-discount-fields"><input type="number" class="item-discount-pct" value="" min="0" step="0.01" placeholder="%"><input type="number" class="item-discount" value="0" min="0" step="0.01" placeholder="Amount"></div></td>
                <td class="col-item-tax d-none"><div class="item-tax-fields"><input type="number" class="item-tax-pct" value="" min="0" step="0.01" placeholder="%"><input type="number" class="item-tax-amount" value="0" min="0" step="0.01" placeholder="Amount"></div></td>
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
    }

    function updatePaymentSummary() {
        if (docType === 'purchase_return') {
            const received = parseFloat($paidInput.val() || 0) || 0;
            $ctx.find('.payment-total-amount').text(received.toFixed(2));
            return;
        }

        const grandTotal = parseFloat($ctx.find('.grand-total').val() || 0) || 0;
        let received = 0;

        const defaultType = $ctx.find('.default-payment-type').val() || '';
        if (defaultType.startsWith('bank-') || defaultType === 'cash') {
            received += parseFloat($ctx.find('.default-payment-amount').val() || 0) || 0;
        }

        received += Array.from($ctx.find('.payment-type-entry')).reduce((sum, el) => {
            const rawType = $(el).val() || '';
            if (!rawType.startsWith('bank-') && rawType !== 'cash') {
                return sum;
            }

            const amountInput = $(el).closest('.payment-entry').find('.payment-amount');
            return sum + (parseFloat(amountInput.val() || 0) || 0);
        }, 0);

        if (docType !== 'purchase_return' && $ctx.find('.fill-balance-check').is(':checked')) {
            received = grandTotal;
        }

        const balance = Math.max(0, grandTotal - received);
        $ctx.find('.payment-total-amount').text(received.toFixed(2));
        $paidInput.val(received.toFixed(2));
        $ctx.find('.balance-amount').text(balance.toFixed(2));
    }

    function applyDiscountTax(base) {
        let finalBase = base;
        const discPct = parseFloat($ctx.find('.discount-pct').val()) || 0;
        const discRs = parseFloat($ctx.find('.discount-rs').val()) || 0;
        const taxPct = parseFloat($ctx.find('.tax-select').val()) || 0;

        if (discPct > 0) {
            finalBase -= (finalBase * discPct / 100);
        }

        if (discRs > 0) {
            finalBase -= discRs;
        }

        let taxAmount = 0;
        if (taxPct > 0) {
            taxAmount = (finalBase * taxPct / 100);
            finalBase += taxAmount;
        }

        $ctx.find('.tax-amount-display').text(taxAmount.toFixed(2));

        const roundOffEnabled = $ctx.find('.round-off-check').is(':checked');
        let roundOffVal = roundOffEnabled ? (parseFloat($ctx.find('.round-off-val').val()) || 0) : 0;
        let grandTotal = finalBase + roundOffVal;

        $ctx.find('.round-off-val').val(roundOffVal.toFixed(2));
        $ctx.find('.grand-total').val(grandTotal.toFixed(2));
        updatePaymentSummary();
    }

    function calculateTotals() {
        let totalQty = 0;
        let totalBaseAmount = 0;

        $ctx.find('.item-row').each(function() {
            const $row = $(this);
            const qty = parseFloat($row.find('.item-qty').val()) || 0;
            const rate = parseFloat($row.find('.item-rate').val()) || 0;
            const netW = parseFloat($row.find('.net-w-input').val()) || 0;
            const discount = parseFloat($row.find('.item-discount').val()) || 0;
            const baseQty = netW > 0 ? netW : qty;
            const amount = Math.max((baseQty * rate) - discount, 0);

            totalQty += qty;
            totalBaseAmount += amount;
            $row.find('.item-amount').val(amount.toFixed(2));
        });

        $ctx.find('.total-qty').text(totalQty);
        $ctx.find('.total-base-amount').text(totalBaseAmount.toFixed(2));
        applyDiscountTax(totalBaseAmount);
    }

    function gatherSaleReturnData() {
        const items = Array.from($ctx.find('.item-row')).map(row => {
            const $row = $(row);
            const itemName = $row.find('.item-name option:selected').data('label') || $row.find('.item-name option:selected').text() || '';

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
                amount: parseFloat($row.find('.item-amount').val() || 0) || 0,
            };
        }).filter(item => item.item_name || item.quantity || item.amount);

        const payments = [];
        const defaultTypeVal = $ctx.find('.default-payment-type').val();

        if (defaultTypeVal) {
            const isCash = defaultTypeVal === 'cash';
            const bankId = isCash ? null : parseInt(defaultTypeVal.replace('bank-', ''), 10);
            const bank = !isCash ? (window.bankAccounts || []).find(b => b.id === bankId) : null;
            const defaultAmount = parseFloat($ctx.find('.default-payment-amount').val() || 0) || 0;
            const defaultReference = $ctx.find('.default-payment-reference').val() || null;

            if (defaultAmount > 0) {
                payments.push({
                    payment_type: isCash ? 'cash' : (bank?.display_with_account || bank?.display_name || 'Bank'),
                    bank_account_id: bankId || null,
                    amount: defaultAmount,
                    reference: defaultReference,
                });
            }
        }

        Array.from($ctx.find('.payment-entries .payment-entry')).forEach(entry => {
            const $entry = $(entry);
            const rawType = $entry.find('.payment-type-entry').val() || '';
            const isBank = rawType.startsWith('bank-');
            const isCash = rawType === 'cash';
            const bankId = isBank ? rawType.replace('bank-', '') : null;
            const bank = isBank ? (window.bankAccounts || []).find(b => String(b.id) === String(bankId)) : null;
            const amount = parseFloat($entry.find('.payment-amount').val() || 0) || 0;
            const reference = $entry.find('.payment-reference').val() || null;

            if (!rawType || amount <= 0) {
                return;
            }

            payments.push({
                payment_type: isCash ? 'cash' : (isBank ? (bank?.display_with_account || bank?.display_name || 'Bank') : rawType),
                bank_account_id: bankId,
                amount,
                reference,
            });
        });

        return {
            _token: csrfToken,
            type: docType,
            source_sale_id: isDuplicateSaleReturnMode ? null : ($ctx.find('.source-sale-id').val() || null),
            party_id: $ctx.find('.party-id').val() || $ctx.find('.party-select').val() || null,
            party_name: ($ctx.find('#partyDropdownBtn').text() || '').trim() === 'Select Party' ? '' : ($ctx.find('#partyDropdownBtn').text() || '').trim(),
            phone: $ctx.find('.phone-input').val() || '',
            billing_address: $ctx.find('.billing-address').val() || '',
            shipping_address: $ctx.find('.shipping-address').val() || '',
            bill_number: $ctx.find('.bill-number').val() || '',
            reference_bill_number: $ctx.find('.reference-bill-number').val() || '',
            bill_date: $ctx.find('.order-date').val() || '',
            order_date: $ctx.find('.order-date').val() || '',
            due_date: $ctx.find('.due-date').val() || '',
            invoice_date: $ctx.find('.order-date').val() || '',
            total_qty: parseInt($ctx.find('.total-qty').text() || 0, 10) || 0,
            total_amount: parseFloat($ctx.find('.total-base-amount').text() || 0) || 0,
            discount_pct: parseFloat($ctx.find('.discount-pct').val() || 0) || 0,
            discount_rs: parseFloat($ctx.find('.discount-rs').val() || 0) || 0,
            tax_pct: parseFloat($ctx.find('.tax-select').val() || 0) || 0,
            tax_amount: parseFloat($ctx.find('.tax-amount-display').text() || 0) || 0,
            round_off: parseFloat($ctx.find('.round-off-val').val() || 0) || 0,
            grand_total: parseFloat($ctx.find('.grand-total').val() || 0) || 0,
            paid_amount: parseFloat($paidInput.val() || 0) || 0,
            balance: parseFloat($ctx.find('.balance-amount').text() || 0) || 0,
            description: $ctx.find('.description-input').val() || null,
            image_path: (() => {
                const file = $ctx.find('.image-input')[0]?.files?.[0];
                return file ? file.name : null;
            })(),
            document_path: (() => {
                const file = $ctx.find('.document-input')[0]?.files?.[0];
                return file ? file.name : null;
            })(),
            items,
            payments,
        };
    }

    $ctx.on('change', '.party-select', function() {
        const selectedId = $(this).val();
        const party = (window.parties || []).find(p => String(p.id) === String(selectedId));

        if (party) {
            $ctx.find('.phone-input').val(party.phone || '');
            $ctx.find('.billing-address').val(party.billing_address || '');
            $ctx.find('.shipping-address').val(party.shipping_address || '');
        } else {
            $ctx.find('.phone-input').val('');
            $ctx.find('.billing-address').val('');
            $ctx.find('.shipping-address').val('');
        }
    });

    $ctx.on('click', '.party-option', function(e) {
    e.preventDefault();
    const $option = $(this);
    const partyId = $option.data('id') || '';
    const partyName = $.trim($option.data('name') || $option.find('.party-option-name').text() || '');

    // Find full party from window.parties
   const selectedParty = (window.parties || []).find(p => String(p.id) === String(partyId)) || {};

const partyRecord = {
    name:             $option.data('name')         || selectedParty.name             || partyName,
    phone:            $option.data('phone')        || selectedParty.phone            || "",
    phone_number_2:   $option.data('phoneNumber2') || selectedParty.phone_number_2   || "",
    ptcl_number:      $option.data('ptcl')         || selectedParty.ptcl_number      || "",
    email:            $option.data('email')        || selectedParty.email            || "",
    city:             $option.data('city')         || selectedParty.city             || "",
    party_group:      $option.data('partyGroup')   || selectedParty.party_group      || "",
    address:          $option.data('address')      || selectedParty.address          || "",
    billing_address:  $option.data('billing')      || selectedParty.billing_address  || "",
    shipping_address: $option.data('shipping')     || selectedParty.shipping_address || "",
    due_days:         $option.data('dueDays')      || selectedParty.due_days         || "",
};
    $ctx.find('.party-id').val(partyId);
    setPartyDropdownDisplay(partyName || 'Select Party');

    // Fill phone field
    $ctx.find('.phone-input').val(partyRecord.phone);

    // Build FULL party details content
    let billingContent = "";
    if (partyRecord.name)           billingContent += "Name:  " + partyRecord.name          + "\n";
    if (partyRecord.phone)          billingContent += "Mob:   " + partyRecord.phone          + "\n";
    if (partyRecord.phone_number_2) billingContent += "WUP:   " + partyRecord.phone_number_2 + "\n";
    if (partyRecord.ptcl_number)    billingContent += "TEL:   " + partyRecord.ptcl_number    + "\n";
    if (partyRecord.email)          billingContent += "Email: " + partyRecord.email          + "\n";
    if (partyRecord.city)           billingContent += "City:  " + partyRecord.city           + "\n";
    if (partyRecord.party_group)    billingContent += "Group: " + partyRecord.party_group    + "\n";
    const billingAddr = partyRecord.billing_address || partyRecord.address || "";
    if (billingAddr) billingContent += "\nAddress:\n" + billingAddr;

    $ctx.find('.billing-address').val(billingContent.trim());
    $ctx.find('.shipping-address').val(partyRecord.shipping_address || "");
    $ctx.find('.party-details').removeClass('d-none');

    const dropdownToggle = document.getElementById('partyDropdownBtn');
    if (dropdownToggle) {
        const dropdown = bootstrap.Dropdown.getOrCreateInstance(dropdownToggle);
        if (dropdown) dropdown.hide();
    }
});

    // Prevent dropdown from closing when clicking on search input
    $ctx.on('click', '.party-search-input', function(e) {
        e.stopPropagation();
    });

    // Prevent dropdown from closing when typing in search
    $ctx.on('keydown keyup', '.party-search-input', function(e) {
        e.stopPropagation();
    });

    // Clear search input when dropdown closes
    $ctx.on('hidden.bs.dropdown', '#partyDropdownMenu', function() {
        $ctx.find('.party-search-input').val('');
        $ctx.find('.party-option').closest('li').removeClass('d-none');
    });

    $ctx.find('.add-row-btn').on('click', function() {
        addRow();
    });

    $ctx.on('click', '.delete-row-icon', function() {
        if ($ctx.find('.item-rows tr').length > 1) {
            $(this).closest('tr').remove();
            reindexRows();
            calculateTotals();
            return;
        }

        const $row = $(this).closest('tr');
        $row.find('input').val('');
        $row.find('.item-qty').val('1');
        $row.find('.gross-w-input, .net-w-input, .item-rate, .item-amount, .item-discount').val('0');
        calculateTotals();
    });

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

        const existingOption = $unitSelect.find('option').filter(function() {
            return ($(this).val() || $(this).text()).toString().trim() === normalizedUnit;
        }).first();

        if (!existingOption.length) {
            $unitSelect.append(`<option value="${normalizedUnit}">${normalizedUnit}</option>`);
        }

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
        const unit = $selected.data('unit') || '';
        const category = $selected.data('category') || '';
        const itemCode = $selected.data('item-code') || '';
        const description = $selected.data('description') || '';
        const discount = $selected.data('discount');

        $row.find('.item-qty').val(1);
        $row.find('.gross-w-input').val('0');
        $row.find('.net-w-input').val('0');
        $row.find('.item-rate').val(price.toFixed(2));
        $row.find('.item-category').val(category);
        $row.find('.item-code').val(itemCode);
        $row.find('.item-desc').val(description);
        $row.find('.item-tafseel').val('');
        if (discount !== undefined && discount !== null && discount !== '') {
            const currentDiscount = parseFloat($row.find('.item-discount').val() || 0) || 0;
            if (currentDiscount === 0) {
                $row.find('.item-discount').val(discount);
            }
        }
        if (unit) {
            ensureUnitOption($row.find('.item-unit'), unit);
        }

        $row.find('.item-qty').trigger('change');
    });

    $ctx.on('keyup change', '.item-qty, .gross-w-input, .net-w-input, .item-rate, .item-discount', function() {
        const $row = $(this).closest('tr');
        const qty = parseFloat($row.find('.item-qty').val()) || 0;
        const rate = parseFloat($row.find('.item-rate').val()) || 0;
        const netW = parseFloat($row.find('.net-w-input').val()) || 0;
        const discount = parseFloat($row.find('.item-discount').val()) || 0;
        const baseQty = netW > 0 ? netW : qty;
        const amount = Math.max((baseQty * rate) - discount, 0);

        $row.find('.item-amount').val(amount.toFixed(2));
        calculateTotals();
    });

    $ctx.on('click', '.add-payment-entry', function(e) {
        e.preventDefault();

        const $defaultAmount = $ctx.find('.default-payment-amount');
        const $defaultReference = $ctx.find('.default-payment-reference');

        if ($defaultAmount.hasClass('d-none') || $defaultReference.hasClass('d-none')) {
            $defaultAmount.removeClass('d-none').focus();
            $defaultReference.removeClass('d-none');
            updatePaymentSummary();
            return;
        }

        const template = document.getElementById('payment-entry-template');
        if (!template) {
            return;
        }

        const clone = template.content.cloneNode(true);
        $ctx.find('.payment-entries').append(clone);
        $ctx.find('.payment-entries .payment-entry').last().find('.payment-amount').focus();
    });

    $ctx.on('change', '.default-payment-type, .payment-type-entry', updatePaymentSummary);
    $ctx.on('keyup change', '.default-payment-amount, .payment-amount', updatePaymentSummary);

    $ctx.on('click', '.remove-payment-entry', function() {
        $(this).closest('.payment-entry').remove();
        updatePaymentSummary();
    });

    $ctx.on('keyup change', '.discount-pct, .discount-rs, .tax-select, .round-off-check', function() {
        const totalBaseAmount = parseFloat($ctx.find('.total-base-amount').text()) || 0;
        applyDiscountTax(totalBaseAmount);
    });
    $ctx.on('keyup change', '.advance-amount', function() {
        const value = parseFloat($(this).val() || 0) || 0;
        $(this).val(value.toFixed(2));
    });
    $ctx.on('change', '.fill-balance-check, .round-off-check', function() {
        setupAdjustmentControls();
        calculateTotals();
    });
    $ctx.on('keyup change', '.advance-amount', updatePaymentSummary);
    $ctx.on('input change', '.round-off-val', calculateTotals);

    $ctx.on('click', '.add-description', function() {
        const $btn = $(this);
        const $pane = $btn.closest('.description-action-group').find('.description-pane');

        $btn.addClass('d-none');
        $pane.removeClass('d-none');
        $pane.find('.description-input').focus();
    });

    $ctx.on('click', '.add-image', function() {
        $ctx.find('.image-input').trigger('click');
    });

    $ctx.on('click', '.add-document', function() {
        $ctx.find('.document-input').trigger('click');
    });

    $ctx.on('change', '.image-input', function() {
        selectedImages = Array.from(this.files || []);
        renderImagePreviews();
    });

    $ctx.on('click', '.image-placeholder, .replace-image', function() {
        $ctx.find('.image-input').trigger('click');
    });

    $ctx.on('click', '.remove-selected-image', function() {
        const index = parseInt($(this).data('index'), 10);
        if (Number.isNaN(index)) return;
        selectedImages.splice(index, 1);
        renderImagePreviews();
        const dt = new DataTransfer();
        selectedImages.forEach(file => dt.items.add(file));
        const input = $ctx.find('.image-input').get(0);
        if (input) input.files = dt.files;
    });

    $ctx.on('change', '.document-input', function() {
        selectedDocuments = Array.from(this.files || []);
        renderDocumentPreviews();
    });

    $ctx.on('click', '.remove-selected-document', function() {
        const index = parseInt($(this).data('index'), 10);
        if (Number.isNaN(index)) return;
        selectedDocuments.splice(index, 1);
        renderDocumentPreviews();
        const dt = new DataTransfer();
        selectedDocuments.forEach(file => dt.items.add(file));
        const input = $ctx.find('.document-input').get(0);
        if (input) input.files = dt.files;
    });

    function submitSaleReturn(btn, options = {}) {
        const saleReturnData = gatherSaleReturnData();
        const idleText = options.idleText || 'Save';
        const loadingText = options.loadingText || 'Saving...';
        const successMessage = options.successMessage || (docLabelTitle + ' saved successfully! Redirecting...');
        const redirectToShare = Boolean(options.redirectToShare);

        if (!saleReturnData.items.length) {
            showToast('Please add at least one item before saving.', true);
            return;
        }

        btn.prop('disabled', true).text(loadingText);

        fetch(window.saleReturnStoreUrl, {
            method: window.saleReturnMethod || 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(saleReturnData),
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
                    throw new Error(data?.message || 'Server error');
                }

                return data;
            })
            .then(data => {
                if (data.success) {
                    if (data.bill_number) {
                        $ctx.find('.bill-number').val(data.bill_number);
                    }

                    showToast(successMessage);
                    const targetUrl = redirectToShare ? (data.share_url || data.redirect_url) : data.redirect_url;
                    if (targetUrl) {
                        setTimeout(() => {
                            window.location.href = targetUrl;
                        }, 1500);
                    }
                    return;
                }

                showToast('Unable to save ' + docLabel + '.', true);
            })
            .catch(err => {
                console.error(err);
                showToast('Error saving ' + docLabel + '. ' + (err.message || ''), true);
            })
            .finally(() => {
                btn.prop('disabled', false).text(idleText);
            });
    }

    $ctx.on('click', '.btn-save', function() {
        submitSaleReturn($(this), {
            idleText: 'Save',
            loadingText: 'Saving...',
            successMessage: docLabelTitle + ' saved successfully! Redirecting...',
        });
    });

    $ctx.on('click', '.btn-share-main', function() {
        submitSaleReturn($(this), {
            redirectToShare: true,
            idleText: 'Share',
            loadingText: 'Saving...',
            successMessage: docLabelTitle + ' saved successfully! Opening invoice preview...',
        });
    });

    setupAdjustmentControls();
    calculateTotals();
}
