(function (window, document, $) {
    'use strict';

    if (!$ || window.__bankAccountModalLoaded) {
        return;
    }

    window.__bankAccountModalLoaded = true;

    let activePaymentSelect = null;

    function getModalElement() {
        return document.getElementById('bankAccountModal');
    }

    function getForm() {
        return $('#bankAccountForm');
    }

    function getStoreUrl() {
        return (window.bankAccountRoutes && window.bankAccountRoutes.store) || '/dashboard/bank-accounts';
    }

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val() || '';
    }

    function showToast(message, isError) {
        const toastEl = document.getElementById('sale-toast');
        const bodyEl = toastEl ? toastEl.querySelector('.toast-body') : null;

        if (!toastEl || !bodyEl || typeof bootstrap === 'undefined') {
            if (message) {
                console[isError ? 'error' : 'log'](message);
            }
            return;
        }

        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');
        bodyEl.textContent = message;
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
    }

    function getBankLabel(record) {
        const displayName = String(record.display_name || record.name || '').trim();
        const accountNumber = String(record.account_number || '').trim();
        const displayWithAccount = String(record.display_with_account || '').trim();

        if (displayWithAccount) {
            return displayWithAccount;
        }

        return displayName + (accountNumber ? ' - ' + accountNumber : '');
    }

    function updateWindowBankAccounts(record) {
        if (!Array.isArray(window.bankAccounts)) {
            window.bankAccounts = [];
        }

        const normalized = {
            id: record.id,
            display_name: record.display_name || record.name || '',
            account_number: record.account_number || '',
            display_with_account: getBankLabel(record)
        };

        window.bankAccounts = window.bankAccounts.filter(function (bank) {
            return String(bank.id) !== String(normalized.id);
        });

        window.bankAccounts.push(normalized);
    }

    function appendBankOptionToSelect($select, value, label) {
        if (!$select.length) {
            return;
        }

        if ($select.find('option[value="' + value + '"]').length) {
            $select.find('option[value="' + value + '"]').text(label);
            return;
        }

        const $option = $('<option>', { value: value, text: label });
        const $specialOption = $select.find('option[value="add_new_bank"]');

        if ($specialOption.length) {
            $option.insertBefore($specialOption.first());
        } else {
            $select.append($option);
        }
    }

    function refreshPaymentTypeDropdowns(record, selectNewOption) {
        const optionValue = 'bank-' + record.id;
        const optionLabel = getBankLabel(record);
        const rawBankValue = String(record.id);

        $('.default-payment-type, .payment-type-entry').each(function () {
            appendBankOptionToSelect($(this), optionValue, optionLabel);
        });

        $('.payment-bank').each(function () {
            appendBankOptionToSelect($(this), rawBankValue, optionLabel);
        });

        if (selectNewOption && activePaymentSelect) {
            const $activeSelect = $(activePaymentSelect);
            const selectedValue = $activeSelect.hasClass('payment-bank') ? rawBankValue : optionValue;
            $activeSelect.val(selectedValue).trigger('change');
        }
    }

    function resetBankAccountForm() {
        const $form = getForm();
        const formEl = $form.get(0);
        const $extraFields = $form.find('.extra-fields');
        const $toggleBtn = $form.find('.add-more-fields-btn');
        const today = new Date().toISOString().slice(0, 10);

        if (formEl) {
            formEl.reset();
        }

        $form.find('#bankAsOfDate').val(today);
        $extraFields.addClass('d-none').hide();
        $toggleBtn.text('+ Add More Fields');
        $form.find('.is-invalid').removeClass('is-invalid');
    }

    function openBankAccountModal(targetSelect) {
        activePaymentSelect = targetSelect || null;
        const modalEl = getModalElement();
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        resetBankAccountForm();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    $(document).on('change', '.payment-type-entry, .default-payment-type, .payment-bank', function () {
        if ($(this).val() !== 'add_new_bank') {
            return;
        }

        $(this).val('').trigger('change');
        openBankAccountModal(this);
    });

    $(document).on('click', '.add-bank-btn, .open-bank-account-modal', function (event) {
        event.preventDefault();

        const $scope = $(this).closest('.payment-entry, .payment-row, .payment-section, .bottom-left, .bottom-section, .col-md-4');
        const targetSelect = $scope
            .find('.payment-bank, .payment-type-entry, .default-payment-type')
            .first()
            .get(0);

        openBankAccountModal(targetSelect || null);
    });

    $(document).on('click', '.add-more-fields-btn', function (event) {
        event.preventDefault();

        const $button = $(this);
        const $extraFields = $button.closest('form').find('.extra-fields');
        const isHidden = $extraFields.hasClass('d-none') || !$extraFields.is(':visible');

        if (isHidden) {
            $extraFields
                .removeClass('d-none')
                .hide()
                .stop(true, true)
                .slideDown(180);
            $button.text('- Hide Extra Fields');
            return;
        }

        $extraFields.stop(true, true).slideUp(180, function () {
            $extraFields.addClass('d-none');
        });
        $button.text('+ Add More Fields');
    });

    $(document).on('submit', '#bankAccountForm', function (event) {
        event.preventDefault();

        const $form = $(this);
        const formData = new FormData(this);
        const modalEl = getModalElement();
        const submitButton = $form.find('.save-bank-account-btn');

        submitButton.prop('disabled', true);

        $.ajax({
            url: getStoreUrl(),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        }).done(function (response) {
            const record = response.bank || response;

            updateWindowBankAccounts(record);
            refreshPaymentTypeDropdowns(record, true);

            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            showToast(response.message || 'Bank account added successfully.');
        }).fail(function (xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : null;
            const firstError = errors ? Object.values(errors)[0] : null;
            const message = Array.isArray(firstError) ? firstError[0] : (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to save bank account.';

            showToast(message, true);
        }).always(function () {
            submitButton.prop('disabled', false);
        });
    });

    $(function () {
        const modalEl = getModalElement();

        if (!modalEl) {
            return;
        }

        modalEl.addEventListener('hidden.bs.modal', function () {
            resetBankAccountForm();
            activePaymentSelect = null;
        });
    });
})(window, document, window.jQuery);
