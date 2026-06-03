function initializeForm(context) {
    const $ctx = $(context);
    const hasCustomPartyDropdown = $ctx.find('.party-id').length > 0;
    const $paidInput = $ctx.find('.received-amount, .advance-amount').first();

    const itemOptionsHtml = (window.items || []).map(item => {
        const plainLabel = item.name || ""; const richLabel = `${plainLabel} | Sale: ${item.sale_price ?? item.price ?? 0} | Stock: ${item.opening_qty ?? 0} | Location: ${item.location ?? ""}`; return `<option value="${item.id}" data-price="${item.price ?? ""}" data-sale-price="${item.sale_price ?? ""}" data-stock="${item.opening_qty ?? ""}" data-location="${item.location ?? ""}" data-label="${plainLabel}" data-rich-label="${richLabel}" data-unit="${item.unit || ''}" data-category="${item.category_name || item.category?.name || item.category || item.category_id || ''}" data-item-code="${item.item_code || ''}" data-description="${item.description || item.item_description || ''}" data-discount="${item.discount ?? 0}">${richLabel}</option>`;
    }).join('');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const selectedImages = [];
    const selectedDocuments = [];
    const $imageFilesList = $ctx.find('.image-files-list');
    const $documentFilesList = $ctx.find('.document-files-list');

    // Auto-fill invoice date and placeholder invoice no
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayValue = `${yyyy}-${mm}-${dd}`;
    $ctx.find('.order-date').val(todayValue);
    $ctx.find('.due-date').val(todayValue);
    $ctx.find('.invoice-date').val(todayValue);

    // If editing an existing sale, populate the form with saved values
    if (window.editSaleData) {
        populateFormFromSale(window.editSaleData);
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
            $paidInput.closest('.calc-inputs').prepend(
                `<label class="d-flex align-items-center gap-1 me-2 mb-0 text-nowrap" style="font-size:12px;">
                    <input type="checkbox" class="fill-balance-check">
                    <span>Full Receive</span>
                </label>`
            );
        }
    }

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

    function renderExistingAttachments(sale) {
        const imagePaths = Array.isArray(sale.image_paths) && sale.image_paths.length
            ? sale.image_paths
            : (sale.image_path ? [sale.image_path] : []);
        const documentPaths = Array.isArray(sale.document_paths) && sale.document_paths.length
            ? sale.document_paths
            : (sale.document_path ? [sale.document_path] : []);

        if (imagePaths.length && !$imageFilesList.children().length && !selectedImages.length) {
            const html = imagePaths.map((path) => {
                const name = String(path || '').split('/').pop() || 'Image';
                return `
                    <div class="image-file-card border rounded overflow-hidden">
                        <img src="${buildImageUrl(path)}" alt="${name}" class="img-fluid" style="width:120px;height:120px;object-fit:cover;" />
                        <div class="small text-truncate p-1 text-center" style="max-width:120px;">${name}</div>
                    </div>
                `;
            }).join('');
            $imageFilesList.html(html);
        }

        if (documentPaths.length && !$documentFilesList.children().length && !selectedDocuments.length) {
            const html = documentPaths.map((path) => {
                const name = String(path || '').split('/').pop() || 'Document';
                return `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-truncate" style="max-width:100%;">${name}</span>
                    </div>
                `;
            }).join('');
            $documentFilesList.html(html);
        }
    }

    function syncPartyFormValues(partyRecord = {}) {
        $ctx.find('.phone-input').val(partyRecord.phone || '');
        $ctx.find('.billing-address').val(partyRecord.billing_address || partyRecord.billing || partyRecord.address || '');
        $ctx.find('.shipping-address').val(partyRecord.shipping_address || partyRecord.shipping || '');
    }

    function setPartyFieldsLocked(locked = false) {
        $ctx.find('.phone-input, .billing-address, .shipping-address')
            .prop('readonly', locked)
            .toggleClass('is-party-locked', locked);
    }

    function renderPartyCard(partyRecord = {}) {
        const wrapper = document.querySelector('.party-dropdown-wrapper');
        const searchInput = document.getElementById('partyDropdownBtn');
        if (!wrapper || !searchInput) return;

        const oldCard = wrapper.querySelector('.party-selected-card');
        if (oldCard) oldCard.remove();

        if (!partyRecord.name) {
            searchInput.style.display = '';
            searchInput.value = '';
            const balanceDisplay = document.getElementById('partyBalanceDisplay');
            if (balanceDisplay) balanceDisplay.innerHTML = '';
            const partyDetailsSection = document.querySelector('.party-details');
            if (partyDetailsSection) partyDetailsSection.classList.add('d-none');
            const partyIdInput = document.querySelector('.party-id');
            if (partyIdInput) partyIdInput.value = '';
            setPartyFieldsLocked(false);
            syncPartyFormValues({});
            return;
        }

        searchInput.style.display = 'none';
        searchInput.value = partyRecord.name || '';
        const partyIdInput = document.querySelector('.party-id');
        if (partyIdInput) {
            partyIdInput.value = partyRecord.id ? String(partyRecord.id) : '';
        }

        const opening = parseFloat(partyRecord.opening_balance || 0) || 0;
        const type = partyRecord.transaction_type;
        let balanceHtml = '';
        if (type === 'pay') {
            balanceHtml = `<span class="party-card-balance text-danger"><i class="fa-solid fa-arrow-up me-1"></i>?${opening.toFixed(2)}</span>`;
        } else if (type === 'receive') {
            balanceHtml = `<span class="party-card-balance text-success"><i class="fa-solid fa-arrow-down me-1"></i>?${opening.toFixed(2)}</span>`;
        } else if (opening) {
            balanceHtml = `<span class="party-card-balance text-muted">?${opening.toFixed(2)}</span>`;
        }

        const lineParts = [];
        const mobiles = [partyRecord.phone, partyRecord.phone_number_2].filter(Boolean);
        if (mobiles.length) lineParts.push(`M: ${mobiles.join(', ')}`);
        if (partyRecord.ptcl_number || partyRecord.ptcl) lineParts.push(`T: ${partyRecord.ptcl_number || partyRecord.ptcl}`);
        if (partyRecord.email) lineParts.push(`Em: ${partyRecord.email}`);
        if (partyRecord.city) lineParts.push(`?? ${partyRecord.city}`);

        const card = document.createElement('div');
        card.className = 'party-selected-card';
        card.innerHTML = `
            <div class="party-card-info">
                <span class="party-card-name">${partyRecord.name}</span>
                ${lineParts.map((line) => `<span class="party-card-line">${line}</span>`).join('')}
                ${balanceHtml}
            </div>
            <button type="button" class="party-card-clear" title="Change Party">?</button>
        `;

        card.querySelector('.party-card-clear')?.addEventListener('click', function (e) {
            e.stopPropagation();
            card.remove();
            searchInput.style.display = '';
            searchInput.value = '';
            searchInput.focus();
            const balanceDisplay = document.getElementById('partyBalanceDisplay');
            if (balanceDisplay) balanceDisplay.innerHTML = '';
            const partyDetailsSection = document.querySelector('.party-details');
            if (partyDetailsSection) partyDetailsSection.classList.add('d-none');
            const partyIdInput = document.querySelector('.party-id');
            if (partyIdInput) partyIdInput.value = '';
            setPartyFieldsLocked(false);
            syncPartyFormValues({});
        });

        wrapper.insertBefore(card, searchInput);
        const balanceDisplay = document.getElementById('partyBalanceDisplay');
        if (balanceDisplay) balanceDisplay.innerHTML = balanceHtml;
        const partyDetailsSection = document.querySelector('.party-details');
        if (partyDetailsSection) partyDetailsSection.classList.remove('d-none');
        setPartyFieldsLocked(Boolean(partyRecord.name));
        syncPartyFormValues(partyRecord);
    }

    function populateFormFromSale(sale) {
        // Fill header fields
        if (hasCustomPartyDropdown) {
            const party = (window.parties || []).find(p => String(p.id) === String(sale.party_id || ''));
            $ctx.find('.party-id').val(sale.party_id || '');
            const selectedParty = party || sale.party || (sale.party_name ? {
                name: sale.party_name,
                phone: sale.phone,
                billing_address: sale.billing_address,
                shipping_address: sale.shipping_address,
            } : {});
            renderPartyCard(selectedParty);
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

        $ctx.find('.phone-input').val(sale.phone || sale.party?.phone || '');
        $ctx.find('.billing-address').val(sale.billing_address || sale.party?.billing_address || '');
        $ctx.find('.shipping-address').val(sale.shipping_address || sale.party?.shipping_address || '');
        $ctx.find('.bill-number').val(sale.bill_number || '');
        const saleDate = sale.order_date || sale.invoice_date || `${yyyy}-${mm}-${dd}`;
        const dueDate = sale.due_date || sale.order_date || sale.invoice_date || `${yyyy}-${mm}-${dd}`;
        $ctx.find('.order-date').val(saleDate ? saleDate.split(' ')[0] : `${yyyy}-${mm}-${dd}`);
        $ctx.find('.due-date').val(dueDate ? dueDate.split(' ')[0] : `${yyyy}-${mm}-${dd}`);
        $ctx.find('.invoice-date').val(saleDate ? saleDate.split(' ')[0] : `${yyyy}-${mm}-${dd}`);

        // Items
        $ctx.find('.item-rows').empty();
        (sale.items || []).forEach(item => {
            addRow();
            const $row = $ctx.find('.item-rows tr').last();
            const matchOption = $row.find('.item-name option').filter(function () {
                return $(this).text().trim() === (item.item_name || '').trim();
            }).first();
            if (matchOption.length) {
                matchOption.prop('selected', true);
            }

            $row.find('.item-category').val(item.item_category || '');
            $row.find('.item-code').val(item.item_code || '');
            $row.find('.item-desc').val(item.item_description || '');
            $row.find('.item-discount').val(item.discount || 0);
            $row.find('.item-qty').val(item.quantity || 0);
            if (item.unit) {
                ensureUnitOption($row.find('.item-unit'), item.unit);
            }
            $row.find('.item-price').val(item.unit_price || 0);
            $row.find('.item-amount').val(item.amount || 0);
        });

        // Discount / Tax / Round off
        $ctx.find('.discount-pct').val(sale.discount_pct || 0);
        $ctx.find('.discount-rs').val(sale.discount_rs || 0);
        $ctx.find('.tax-select').val(sale.tax_pct || 0);
        $ctx.find('.round-off-val').val(sale.round_off || 0);
        $ctx.find('.grand-total').val(sale.grand_total || 0);

        // Description (show if already set)
        const desc = sale.description || '';
        $ctx.find('.description-input').val(desc);
        if (desc) {
            $ctx.find('.description-pane').removeClass('d-none');
        }

        renderExistingAttachments(sale);

        // Payments: treat values as "already received" and allow adding new payments
        window.existingReceivedAmount = parseFloat(sale.received_amount || 0) || 0;
        window.existingBalance = parseFloat(sale.balance || 0) || 0;

        // Pre-select the same bank as the first payment (so user can quickly add more)
        $ctx.find('.default-payment-type').val('cash');
        $ctx.find('.default-payment-amount').val('0').addClass('d-none');
        $ctx.find('.default-payment-reference').val('').addClass('d-none');
        $ctx.find('.payment-entries').empty();

        (sale.payments || []).forEach((payment, index) => {
            if (index === 0 && payment.bank_account_id) {
                $ctx.find('.default-payment-type').val(`bank-${payment.bank_account_id}`);
            }
        });

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
            $ctx.find('.phone-input').val(party.phone || '');
            $ctx.find('.billing-address').val(party.billing_address || '');
        } else {
            $ctx.find('.phone-input').val('');
            $ctx.find('.billing-address').val('');
        }
    });

    $ctx.on('click', '.party-option', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $option = $(this);
        const partyId = String($option.data('id') || '').trim();
        const partyName = $.trim($option.data('name') || $option.find('.party-option-name').text() || '');
        const wp = (window.parties || []).find(p => String(p.id) === partyId) || {};
        const name = $option.data('name') || wp.name || partyName;
        const phone = $option.data('phone') || wp.phone || '';
        const phone2 = $option.data('phone-number-2') || wp.phone_number_2 || '';
        const city = $option.data('city') || wp.city || '';
        const ptclNumber = $option.data('ptcl') || wp.ptcl_number || wp.ptcl || '';
        const email = $option.data('email') || wp.email || '';
        const address = $option.data('address') || wp.address || '';
        const billing = $option.data('billing') || wp.billing_address || wp.billing || address || '';
        const shipping = $option.data('shipping') || wp.shipping_address || wp.shipping || '';
        const openingBalance = parseFloat($option.data('opening') || 0) || 0;
        const transactionType = $option.data('type') || wp.transaction_type || '';

        $ctx.find('.party-id').val(partyId);
        renderPartyCard({
            id: partyId,
            name,
            phone,
            billing_address: billing,
            shipping_address: shipping,
            opening_balance: openingBalance,
            transaction_type: transactionType,
            city,
            phone_number_2: phone2,
            ptcl_number: ptclNumber,
            email,
            address,
        });

        // Close the dropdown after selection
        const dropdownElement = $ctx.find('#partyDropdownBtn').get(0);
        if (dropdownElement && bootstrap.Dropdown) {
            bootstrap.Dropdown.getInstance(dropdownElement)?.hide();
        }
    });

    $ctx.on('click', '.show-party-selector-btn', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        renderPartyCard({});
        $ctx.find('#partyDropdownBtn').focus();
    });

    const partyDropdownMenuEl = document.getElementById('partyDropdownMenu');
    if (partyDropdownMenuEl) {
        partyDropdownMenuEl.addEventListener('click', function (e) {
            const option = e.target.closest('.party-option');
            if (!option) return;

            const partyId = String(option.dataset.id || '').trim();
            const partyName = option.dataset.name || option.querySelector('.party-option-name')?.textContent?.trim() || '';
            const selectedParty = (window.parties || []).find((party) => String(party.id) === partyId) || {};
            const partyRecord = {
                id: partyId,
                name: selectedParty.name ?? option.dataset.name ?? partyName,
                phone: selectedParty.phone ?? option.dataset.phone ?? '',
                phone_number_2: selectedParty.phone_number_2 ?? option.dataset.phoneNumber2 ?? option.dataset.phoneNumber2 ?? '',
                city: selectedParty.city ?? option.dataset.city ?? '',
                ptcl_number: selectedParty.ptcl_number ?? option.dataset.ptcl ?? '',
                email: selectedParty.email ?? option.dataset.email ?? '',
                address: selectedParty.address ?? option.dataset.address ?? '',
                billing_address: selectedParty.billing_address ?? option.dataset.billing ?? '',
                shipping_address: selectedParty.shipping_address ?? option.dataset.shipping ?? '',
                opening_balance: selectedParty.opening_balance ?? option.dataset.opening ?? 0,
                transaction_type: selectedParty.transaction_type ?? option.dataset.type ?? '',
            };

            renderPartyCard(partyRecord);
        }, true);
    }

    // Party search/filter functionality
    $ctx.on('input', '.party-search-input', function(e) {
        e.stopPropagation();
        const searchValue = $(this).val().toLowerCase().trim();
        const $partyOptions = $ctx.find('.party-option');

        $partyOptions.each(function() {
            const $this = $(this);
            const partyName = $.trim($this.data('name') || $this.find('.party-option-name').text() || '').toLowerCase();
            const partyPhone = $this.data('phone') ? String($this.data('phone')).toLowerCase() : '';

            if (searchValue === '' || partyName.includes(searchValue) || partyPhone.includes(searchValue)) {
                $this.closest('li').removeClass('d-none');
            } else {
                $this.closest('li').addClass('d-none');
            }
        });
    });

    // Prevent dropdown from closing when clicking on search input
    $ctx.on('click', '.dropdown-header-search', function(e) {
        e.stopPropagation();
    });

    function refreshPartyDropdown() {
        const $dropdown = $ctx.find('#partyDropdownMenu');
        const $existingItems = $dropdown.find('.party-option').closest('li');

        // Clear existing party options but keep header and footer
        $existingItems.remove();

        // Rebuild party list
        const partiesHtml = (window.parties || []).map(party => `
            <li>
                <a class="dropdown-item d-flex justify-content-between align-items-start party-option" href="#"
                   data-id="${party.id}"
                   data-name="${party.name || ''}"
                   data-phone="${party.phone || ''}"
                   data-phone-number-2="${party.phone_number_2 || ''}"
                   data-city="${party.city || ''}"
                   data-ptcl="${party.ptcl_number || party.ptcl || ''}"
                   data-email="${party.email || ''}"
                   data-address="${(party.address || '').replace(/"/g, '&quot;')}"
                   data-billing="${(party.billing_address || '').replace(/"/g, '&quot;')}"
                   data-shipping="${(party.shipping_address || '').replace(/"/g, '&quot;')}"
                   data-opening="${party.opening_balance || 0}"
                   data-type="${party.transaction_type || ''}">
                    <span class="party-option-main">
                        <span class="party-option-name">${party.name || ''}</span>
                        <span class="party-option-phone">${party.phone || '-'}</span>
                    </span>
                    <span class="${party.transaction_type === 'pay' ? 'text-danger' : 'text-success'}">
                        ${party.transaction_type === 'pay' ? '<i class="fa-solid fa-arrow-up me-1"></i>' : '<i class="fa-solid fa-arrow-down me-1"></i>'}
                        ₹${parseFloat(party.opening_balance || 0).toFixed(2)}
                    </span>
                </a>
            </li>
        `).join('');

        // Insert new items before the divider
        const $divider = $dropdown.find('li:has(> hr.dropdown-divider)');
        if ($divider.length) {
            $divider.before(partiesHtml);
        }
    }

    // Also refresh dropdown after a short delay when parties change
    window.addEventListener('partiesUpdated', function() {
        refreshPartyDropdown();
    });

    // Add row functionality
    $ctx.find('.add-row-btn').on('click', function() {
        addRow();
    });

    function addRow() {
        const rowCount = $ctx.find('.item-rows tr').length + 1;
        const settings = window.getItemColumnSettings ? window.getItemColumnSettings() : { category: false, code: false, description: false, discount: false };
        const isCatVisible = settings.category;
        const isCodeVisible = settings.code;
        const isDescVisible = settings.description;
        const isDiscVisible = settings.discount;

        const newRow = `
            <tr class="item-row">
                <td class="row-num">
                    <span class="row-index-text">${rowCount}</span>
                    <div class="delete-row-icon"><i class="fa-solid fa-trash-can"></i></div>
                </td>
                <td>
                    <select class="form-select item-name">
                        <option value="" selected disabled>Select Item</option>
                        ${itemOptionsHtml}
                    </select>
                </td>
                <td class="col-category ${isCatVisible ? '' : 'd-none'}"><select class="item-category"><option value="">Select Category</option></select></td>
                <td class="col-item-code ${isCodeVisible ? '' : 'd-none'}"><input type="text" class="item-code" placeholder="Item Code" readonly></td>
                <td class="col-description ${isDescVisible ? '' : 'd-none'}"><input type="text" class="item-desc" placeholder="Description" readonly></td>
                <td class="col-discount ${isDiscVisible ? '' : 'd-none'}"><div class="item-discount-fields"><input type="number" class="item-discount-pct" value="" min="0" step="0.01" placeholder="%"><input type="number" class="item-discount" value="0" min="0" step="0.01" placeholder="Amount"></div></td>
                <td><input type="number" class="item-qty" value="1"></td>
                <td>
                    <select class="item-unit"><option value="">Select Unit</option><option value="PCS">PCS (Pieces)</option><option value="BOX">BOX</option><option value="PACK">PACK</option><option value="SET">SET</option><option value="KG">KG (Kilogram)</option><option value="G">Gram</option><option value="M">Meter</option><option value="FT">Feet</option><option value="L">Liter</option><option value="ML">Milliliter</option></select>
                </td>
                <td><input type="number" class="item-price" value="0"></td>
                <td class="col-amount"><input type="text" class="item-amount" value="0" readonly></td>
                <td class="add-col"></td>
            </tr>
        `;
        $ctx.find('.item-rows').append(newRow);
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
            $row.find('.item-qty, .item-price, .item-amount').val('0');
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

        const $qty = $row.find('.item-qty');
        // Always default selected item quantity to 1 when item is chosen
        $qty.val(1);

        $row.find('.item-price').val(price.toFixed(2));
        $row.find('.item-category').val(category);
        $row.find('.item-code').val(itemCode);
        $row.find('.item-desc').val(description);
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

    // Line item calculation
    $ctx.on('keyup change', '.item-qty, .item-price, .item-discount', function() {
        const $row = $(this).closest('tr');
        const qty = parseFloat($row.find('.item-qty').val()) || 0;
        const price = parseFloat($row.find('.item-price').val()) || 0;
        const itemDiscount = parseFloat($row.find('.item-discount').val()) || 0;

        const amount = (qty * price) - itemDiscount;
        $row.find('.item-amount').val(amount.toFixed(2));
        calculateTotals();
    });

    // Payment entry management
    $ctx.on('click', '.add-payment-entry', function(e) {
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

    if (window.proformaIsConverted) {
        $ctx.find('.btn-save, .btn-share-main').prop('disabled', true).addClass('disabled');
        setTimeout(function () {
            showToast(window.proformaConvertedMessage || 'This Data is Converted please close the Tab', true);
        }, 250);
    }

    if (window.proformaIsConverted) {
        $ctx.find('.btn-save, .btn-share-main').prop('disabled', true).addClass('disabled');
        setTimeout(function () {
            showToast(window.proformaConvertedMessage || 'This Data is Converted please close the Tab', true);
        }, 250);
    }

    // Update payment summary when default payment type is changed
    $ctx.on('change', '.default-payment-type', function() {
        updatePaymentSummary();
    });

    // Ensure amount and reference inputs are kept visible for all payment rows
    $ctx.on('change', '.payment-type-entry', function() {
        updatePaymentSummary();
    });


    $ctx.on('click', '.remove-payment-entry', function() {
        $(this).closest('.payment-entry').remove();
    });

    // Helper: collect data from form
    function gatherSaleData() {
        const items = Array.from($ctx.find('.item-row')).map(row => {
            const $row = $(row);
            const $selectedOption = $row.find('.item-name option:selected');
            let itemName = String($selectedOption.data('label') || $selectedOption.text() || '').trim();
            if (!itemName) {
                itemName = String($row.attr('data-temp-item-name') || $row.find('.item-picker-input').val() || '').trim();
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
                unit_price: parseFloat($row.find('.item-rate').val() || $row.find('.item-price').val() || 0) || 0,
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
                    custom_fields: [
                        $row.find('.item-custom-field-1-input').val() || '',
                        $row.find('.item-custom-field-2-input').val() || '',
                        $row.find('.item-custom-field-3-input').val() || '',
                        $row.find('.item-custom-field-4-input').val() || '',
                        $row.find('.item-custom-field-5-input').val() || '',
                        $row.find('.item-custom-field-6-input').val() || '',
                    ],
                },
            };
        }).filter(item => item.item_name || item.quantity || item.amount);

        const data = {
            type: 'proforma',
            party_id: $ctx.find('.party-id').val() || $ctx.find('.party-select').val() || '',
            party_name: ($ctx.find('#partyDropdownBtn').val() || $ctx.find('.party-select option:selected').text() || '').trim(),
            phone: $ctx.find('.phone-input').val() || '',
            billing_address: $ctx.find('.billing-address').val() || '',
            shipping_address: $ctx.find('.shipping-address').val() || '',
            bill_number: $ctx.find('.bill-number').val() || '',
            invoice_date: $ctx.find('.order-date').val() || $ctx.find('.invoice-date').val() || '',
            order_date: $ctx.find('.order-date').val() || '',
            due_date: $ctx.find('.due-date').val() || $ctx.find('.order-date').val() || $ctx.find('.invoice-date').val() || '',
            total_qty: parseInt($ctx.find('.total-qty').text() || 0, 10) || 0,
            total_amount: parseFloat($ctx.find('.total-base-amount').text() || 0) || 0,
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
            payments: [],
        };

        return data;
    }

    function submitProforma(btn, options = {}) {
        if (window.proformaIsConverted) {
            showToast(window.proformaConvertedMessage || 'This Data is Converted please close the Tab', true);
            return;
        }

        const saleData = gatherSaleData();
        const idleText = options.idleText || 'Save';
        const loadingText = options.loadingText || 'Saving...';
        const successMessage = options.successMessage || 'Proforma invoice saved successfully! Redirecting...';
        const redirectToShare = Boolean(options.redirectToShare);

        if (!saleData.items.length) {
            alert('Please add at least one item before saving.');
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
                showToast('Unable to save proforma invoice. See console for details.', true);
            })
            .catch(err => {
                console.error(err);
                showToast('Error saving proforma invoice. ' + (err.message || ''), true);
            })
            .finally(() => {
                btn.prop('disabled', false).text(idleText);
            });
    }

    $ctx.on('click', '.btn-save', function() {
        submitProforma($(this), {
            idleText: 'Save',
            loadingText: 'Saving...',
            successMessage: 'Proforma invoice saved successfully! Redirecting...',
        });
    });

    $ctx.on('click', '.btn-share-main', function() {
        submitProforma($(this), {
            redirectToShare: true,
            idleText: 'Share',
            loadingText: 'Saving...',
            successMessage: 'Proforma invoice saved successfully! Opening invoice preview...',
        });
    });

    // Add description/image/document actions
    $ctx.on('click', '.add-description', function() {
        const $btn = $(this);
        const $pane = $btn.closest('.description-action-group').find('.description-pane');
        const $container = $btn.closest('.description-action-group');

        $btn.addClass('d-none');
        $pane.removeClass('d-none');
        $pane.find('.description-input').focus();
    });

    $ctx.on('click', '.add-image', function() {
        const $container = $(this).closest('.invoice-container');
        $container.find('.image-input').trigger('click');
    });

    $ctx.on('click', '.add-document', function() {
        const $container = $(this).closest('.invoice-container');
        $container.find('.document-input').trigger('click');
    });

    function renderSelectedImages() {
        if (!selectedImages.length) {
            if (!(window.editSaleData && (window.editSaleData.image_paths?.length || window.editSaleData.image_path))) {
                $imageFilesList.empty();
            }
            return;
        }

        const html = selectedImages.map((file, index) => {
            const url = URL.createObjectURL(file);
            return `
                <div class="image-file-card position-relative border rounded overflow-hidden" data-index="${index}">
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-1 remove-selected-image" aria-label="Remove" data-index="${index}"></button>
                    <img src="${url}" alt="${file.name}" class="img-fluid" style="width:120px;height:120px;object-fit:cover;" />
                    <div class="small text-truncate p-1 text-center" style="max-width:120px;">${file.name}</div>
                </div>
            `;
        }).join('');
        $imageFilesList.html(html);
    }

    function renderSelectedDocuments() {
        if (!selectedDocuments.length) {
            if (!(window.editSaleData && (window.editSaleData.document_paths?.length || window.editSaleData.document_path))) {
                $documentFilesList.empty();
            }
            return;
        }

        const html = selectedDocuments.map((file, index) => {
            return `
                <div class="list-group-item d-flex justify-content-between align-items-center" data-index="${index}">
                    <span class="text-truncate" style="max-width: calc(100% - 32px);">${file.name}</span>
                    <button type="button" class="btn-close remove-selected-document" aria-label="Remove" data-index="${index}"></button>
                </div>
            `;
        }).join('');
        $documentFilesList.html(html);
    }

    function addSelectedImages(files) {
        Array.from(files || []).forEach(file => {
            const duplicate = selectedImages.some(existing => existing.name === file.name && existing.size === file.size && existing.type === file.type);
            if (!duplicate) {
                selectedImages.push(file);
            }
        });
        renderSelectedImages();
    }

    function addSelectedDocuments(files) {
        Array.from(files || []).forEach(file => {
            const duplicate = selectedDocuments.some(existing => existing.name === file.name && existing.size === file.size && existing.type === file.type);
            if (!duplicate) {
                selectedDocuments.push(file);
            }
        });
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
        let totalBaseAmount = 0;

        $ctx.find('.item-qty').each(function() {
            totalQty += parseFloat($(this).val()) || 0;
        });

        $ctx.find('.item-amount').each(function() {
            totalBaseAmount += parseFloat($(this).val()) || 0;
        });

        $ctx.find('.total-qty').text(totalQty);
        $ctx.find('.total-base-amount').text(totalBaseAmount.toFixed(2));

        applyDiscountTax(totalBaseAmount);
    }

    // Discount and Tax logic
    $ctx.on('keyup change', '.discount-pct, .discount-rs, .tax-select, .round-off-check', function() {
        const totalBaseAmount = parseFloat($ctx.find('.total-base-amount').text()) || 0;
        applyDiscountTax(totalBaseAmount);
    });

    function applyDiscountTax(base) {
        let finalBase = base;

        const discPct = parseFloat($ctx.find('.discount-pct').val()) || 0;
        if (discPct > 0) {
            finalBase -= (finalBase * discPct / 100);
        }

        const discRs = parseFloat($ctx.find('.discount-rs').val()) || 0;
        if (discRs > 0) {
            finalBase -= discRs;
        }

        const taxPct = parseFloat($ctx.find('.tax-select').val()) || 0;
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
        if (defaultType.startsWith('bank-') || defaultType === 'cash') {
            received += parseFloat($ctx.find('.default-payment-amount').val() || 0) || 0;
        }

        // Include additional payment entries
        received += Array.from($ctx.find('.payment-type-entry')).reduce((sum, el) => {
            const rawType = $(el).val() || '';
            const isBank = rawType.startsWith('bank-');
            const isCash = rawType === 'cash';
            if (!isBank && !isCash) return sum;

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

    // Update when payment rows are removed
    $ctx.on('click', '.remove-payment-entry', function() {
        $(this).closest('.payment-entry').remove();
        updatePaymentSummary();
    });

    $ctx.on('change', '.fill-balance-check, .round-off-check', function() {
        setupAdjustmentControls();
        calculateTotals();
    });
    $ctx.on('input change', '.round-off-val', calculateTotals);

    setupAdjustmentControls();
    calculateTotals();
}
