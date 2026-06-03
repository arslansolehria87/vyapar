<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
    @if(isset($proforma))
        Edit
    @else
        Create
    @endif
    Proforma Invoice | Vyapar
</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Form Styles -->
    <link rel="stylesheet" href="{{ asset('css/estimateform_style.css') }}">
    @include('dashboard.shared.party-item-create-styles')

<style>
    /* Dropdown with two columns and scrollbar */
    #partyDropdownMenu {
        min-width: 250px; /* Adjust as needed */
        max-width: 100%;  /* Never exceed container */
        max-height: 350px; /* Add max-height for scrollbar */
        overflow-y: auto; /* Enable vertical scrolling */
        overflow-x: hidden; /* Hide horizontal scroll */
    }

    /* Scrollbar styling for responsive dropdown */
    #partyDropdownMenu::-webkit-scrollbar {
        width: 8px;
    }

    #partyDropdownMenu::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    #partyDropdownMenu::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    #partyDropdownMenu::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Firefox scrollbar */
    #partyDropdownMenu {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }

    .party-option span {
        display: inline-block;
        width: 100%;
    }
    .party-option .party-option-main {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        width: 60%;
    }
    .party-option .party-option-name {
        font-weight: 500;
    }
    .party-option .party-option-phone {
        font-size: 12px;
        color: #64748b;
    }
    .party-option > span:first-child {
        width: 60%;
    }
    .party-option > span:last-child {
        width: 40%; /* Opening balance */
        text-align: right;
    }
    .selected-party-summary {
        margin-top: 8px;
        padding: 8px 10px;
        border: 1px solid #dbe4f0;
        border-radius: 8px;
        background: #f8fbff;
        font-size: 12px;
        line-height: 1.6;
    }
    .selected-party-summary strong {
        display: inline-block;
        min-width: 72px;
    }
    .dropdown-header {
        position: sticky;
        top: 0;
        z-index: 1020;
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 1px solid #ddd;
    }

    .browser-toolbar {
        gap: 10px;
    }

    .toolbar-spacer {
        flex: 1 1 auto;
    }

    .toolbar-warehouse-block {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    .toolbar-warehouse-label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin: 0;
    }

    .toolbar-warehouse-select {
        min-width: 120px;
        height: 32px;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        font-size: 12px;
    }

    .toolbar-user-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: 6px;
    }

    .toolbar-user-avatar {
        width: 26px;
        height: 26px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
    }

    .toolbar-user-name {
        font-size: 12px;
        font-weight: 600;
        color: #334155;
    }

    .item-picker .item-name {
        display: none !important;
    }

    .floating-input-wrapper {
        position: relative;
        width: 100%;
    }

    .floating-input-wrapper .meta-control,
    textarea.meta-control {
        width: 100%;
        min-height: 44px;
        padding: 16px 12px 8px;
        border: 1px solid #d7e0ea;
        border-radius: 8px;
        background: #fff;
        color: #111827;
        font-size: 14px;
        resize: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .floating-input-wrapper .meta-control:focus,
    textarea.meta-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .floating-input-wrapper label {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        pointer-events: none;
        transition: all 0.18s ease;
        background: #fff;
        padding: 0 4px;
    }

    .floating-input-wrapper textarea.meta-control + label,
    .description-pane .floating-input-wrapper label {
        top: 16px;
        transform: none;
    }

    .floating-input-wrapper .meta-control:focus + label,
    .floating-input-wrapper .meta-control:not(:placeholder-shown) + label,
    .floating-input-wrapper textarea.meta-control:focus + label,
    .floating-input-wrapper textarea.meta-control:not(:placeholder-shown) + label {
        top: 0;
        transform: translateY(-50%);
        color: #2563eb;
        font-size: 10px;
    }

    .party-meta-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .description-pane .description-input {
        min-height: 140px !important;
        padding: 18px 12px 12px !important;
        font-size: 14px !important;
    }

    .description-content-row {
        width: 100%;
        max-width: 100%;
    }

    .bottom-right .custom-expense-section {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
    }

    .bottom-right .custom-expense-rows {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .bottom-right .custom-expense-row {
        padding: 8px 10px;
        border: 1px solid #dbe4f0;
        border-radius: 10px;
        background: #fbfdff;
    }

    .bottom-right .custom-expense-row.no-heading .calc-label {
        display: none;
    }

    .bottom-right .custom-expense-inputs {
        gap: 6px;
        flex-wrap: wrap;
    }

    .bottom-right .custom-expense-mode-group {
        display: inline-flex;
        align-items: center;
        border: 1px solid #d7e0ea;
        border-radius: 999px;
        background: #fff;
        padding: 2px;
        gap: 2px;
    }

    .bottom-right .custom-mode-btn {
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #334155;
        font-weight: 700;
        font-size: 14px;
    }

    .bottom-right .custom-mode-btn.is-active {
        background: #2563eb;
        color: #fff;
    }

    .bottom-right .custom-expense-account-wrap {
        min-width: 190px;
    }

    .bottom-right .custom-expense-account-input,
    .bottom-right .custom-expense-details,
    .bottom-right .custom-expense-pct,
    .bottom-right .custom-expense-value {
        min-height: 34px;
        height: 34px;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #d7e0ea;
        background: #fff;
        font-size: 12px;
    }

    .bottom-right .custom-expense-pct {
        width: 70px;
        text-align: right;
    }

    .bottom-right .custom-expense-details {
        width: 140px;
    }

    .bottom-right .custom-expense-value {
        width: 100px;
        text-align: right;
    }

    .bottom-right .remove-custom-expense-row {
        width: 30px;
        height: 30px;
        border: 1px solid #fecaca;
        border-radius: 6px;
        background: #fff5f5;
        color: #dc2626;
    }

    .bottom-right .add-custom-expense-row {
        min-width: 120px;
        max-width: 140px;
    }

    .item-totals-row .column-total-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
    }

    .item-totals-row .column-total-value {
        display: block;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    .tfoot-add-row-cell {
        text-align: center;
    }

    .action-fields-layout.meta-stack-layout {
        display: grid;
        grid-template-columns: minmax(220px, 250px) minmax(0, 360px);
        gap: 18px;
        align-items: start;
    }

    .action-fields-layout.meta-stack-layout .description-side-fields {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
        max-width: 360px;
        margin-left: 0 !important;
        margin-top: 0 !important;
        padding-top: 0;
    }

    .action-fields-layout.meta-stack-layout .party-meta-field {
        width: 100%;
    }

    .action-fields-layout.meta-stack-layout .floating-input-wrapper .meta-control {
        width: 100%;
        max-width: none;
        min-height: 50px;
        padding: 14px 16px 8px;
    }

    .terms-condition-pane {
        width: 100%;
    }

    .meta-right-stack {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        flex: 1 1 auto;
        min-width: 0;
    }

    .terms-condition-card {
        border: 1px solid #dfe5ee;
        border-radius: 10px;
        background: #fff;
        padding: 18px 16px 16px;
        box-shadow: none;
    }

    .terms-condition-card-title {
        margin: 0 0 14px;
        font-size: 17px;
        font-weight: 700;
        color: #4b5563;
    }

    .terms-condition-row {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 14px;
    }

    .terms-condition-field {
        flex: 1 1 auto;
    }

    .terms-condition-field-label {
        display: block;
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
    }

    .terms-condition-select,
    .terms-condition-text {
        width: 100%;
        border: 1px solid #d7dee8;
        border-radius: 8px;
        background: #fff;
        color: #1f2937;
        font-size: 14px;
    }

    .terms-condition-select {
        height: 40px;
    }

    .terms-condition-text {
        min-height: 240px;
        resize: vertical;
        padding: 14px 16px;
        line-height: 1.55;
    }

    .additional-charge-live-section {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin: 8px 0 12px;
    }

    .additional-charge-live-row {
        display: grid;
        grid-template-columns: 90px 1fr 86px 54px;
        gap: 8px;
        align-items: center;
    }

    .additional-charge-live-label {
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
    }

    .additional-charge-live-input,
    .additional-charge-live-tax {
        width: 100%;
        min-height: 38px;
        border: 1px solid #d7dee8;
        border-radius: 8px;
        background: #fff;
        padding: 8px 12px;
    }

    .transportation-details-live-section {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-start;
        padding: 8px 0;
    }

    .transportation-details-live-section .form-group {
        flex: 1 1 calc(50% - 4px);
        margin-bottom: 0;
        min-width: 150px;
    }

    .transportation-details-live-section input,
    .transportation-details-live-section select,
    .transportation-details-live-section textarea {
        font-size: 12px;
        padding: 6px 8px;
        min-height: auto;
        height: 32px;
    }

    .transportation-details-live-section textarea {
        height: 60px;
        resize: vertical;
    }

.transportation-details-live-section label {
    font-size: 11px;
    margin-bottom: 3px;
}

/* Match sales/create.blade.php terms + meta layout */
.terms-condition-group {
    flex: 0 0 305px;
    max-width: 305px;
    width: 305px;
}

.terms-condition-pane {
    width: 100%;
}

.meta-right-stack {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    flex: 1 1 auto;
    min-width: 0;
}

.action-fields-layout.meta-stack-layout {
    display: flex;
    align-items: flex-start;
    gap: 22px;
}

.action-fields-layout.meta-stack-layout .action-buttons-column {
    flex: 0 0 270px;
    max-width: 270px;
}

.action-fields-layout.meta-stack-layout .description-side-fields {
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 0 0 315px;
    max-width: 315px;
    width: 315px;
    margin-left: 0 !important;
    margin-top: 0 !important;
    padding-top: 0;
}

.action-fields-layout.meta-stack-layout .party-meta-field {
    width: 100%;
}

.action-fields-layout.meta-stack-layout .floating-input-wrapper .meta-control {
    width: 100%;
    max-width: none;
}

.action-fields-layout.meta-stack-layout.no-terms-layout {
    gap: 0 !important;
}

.action-fields-layout.meta-stack-layout.no-terms-layout .meta-right-stack {
    width: 100% !important;
    max-width: 100% !important;
}

.action-fields-layout.meta-stack-layout.no-terms-layout .action-buttons-column {
    flex: 0 0 270px;
    max-width: 270px;
}

.action-fields-layout.meta-stack-layout.no-terms-layout .description-side-fields {
    flex: 0 0 315px;
    max-width: 315px;
    width: 315px;
}

.terms-condition-card {
    border: 1px solid #dfe5ee;
    border-radius: 10px;
    background: #fff;
    padding: 18px 16px 16px;
    box-shadow: none;
}

.terms-condition-card-title {
    margin: 0 0 14px;
    font-size: 17px;
    font-weight: 700;
    color: #4b5563;
}

.terms-condition-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 14px;
}

.terms-condition-field {
    flex: 1 1 auto;
}

.terms-condition-field-label {
    display: block;
    margin: 0 0 6px;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
}

.terms-condition-select,
.terms-condition-text {
    width: 100%;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    background: #fff;
    color: #1f2937;
    font-size: 14px;
}

.terms-condition-select {
    height: 40px;
    padding: 8px 40px 8px 12px;
}

.terms-condition-add-btn {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: 1px solid #cfd8e3;
    background: #fff;
    color: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 19px;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
}

.terms-condition-text {
    min-height: 250px !important;
    resize: vertical;
    padding: 14px 12px !important;
    line-height: 1.45;
}
</style>

</head>

<body>

    <div class="container-fluid min-vh-100 d-flex flex-column p-0">
        <!-- Explorer / Tab Bar Area -->
        <header class="tab-system-header">
            <div class="tab-strip-wrapper justify-content-between">
                <div class="d-flex align-items-end flex-grow-1 overflow-hidden">
                    <div id="tab-strip" class="tab-strip d-flex align-items-end">
                        <!-- Tabs will be dynamically inserted here -->
                    </div>
                    <button id="add-tab-btn" class="btn add-tab-btn" title="New Tab">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div class="window-controls d-flex align-items-center px-2 gap-3">
                    <i id="calc-icon" class="fa-solid fa-calculator" title="Calculator"></i>
                            <button type="button" class="sale-settings-trigger-btn text-reset border-0 bg-transparent p-0" title="Settings" data-bs-toggle="offcanvas" data-bs-target="#saleSettingsSidebar" aria-controls="saleSettingsSidebar">
                                <i class="fa-solid fa-gear"></i>
                            </button>
                    <i class="fa-solid fa-xmark close-app-icon" title="Close Window"></i>
                </div>
            </div>
            <!-- Browser Toolbar / Heading Area -->
            <div class="browser-toolbar d-flex align-items-center px-3">
                <p class="mt-3 ms-3 mb-0 me-3 mb-2">Proforma Invoice</p>
                <div class="toolbar-spacer"></div>
                <div class="toolbar-warehouse-block">
                    <p class="toolbar-warehouse-label">Warehouse</p>
                    <select class="toolbar-warehouse-select warehouse-select" name="warehouse_id">
                        @forelse(($warehouses ?? []) as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                data-handler-name="{{ $warehouse->handler_name }}"
                                data-handler-phone="{{ $warehouse->handler_phone }}">
                                {{ $warehouse->name }}
                            </option>
                        @empty
                            <option value="">Main Store</option>
                        @endforelse
                        <option value="add_new_warehouse">+ Add New Warehouse</option>
                    </select>
                </div>
                <div class="toolbar-user-chip">
                    <span class="toolbar-user-avatar">{{ strtoupper(substr(trim((string) (auth()->user()->name ?? 'U')), 0, 1)) }}</span>
                    <span class="toolbar-user-name">{{ auth()->user()->name ?? 'User' }}</span>
                </div>

            </div>
        </header>

        <!-- Content Area -->
        <main id="content-area" class="">
            <!-- Tab contents will be dynamically inserted here
            <button id="global-save-btn" class="btn btn-primary position-absolute bottom-0 end-0 m-4 shadow-lg z-3">
                <i class="bi bi-save me-2"></i>Save
            </button> -->
            <!-- Form Template -->
            <template id="form-template">
                <div class="invoice-container">
                    <div class="invoice-form invoice-card">

                        <!-- Header Section -->
                        <div class="header-section">
                              <div class="input-group">
                                <!-- Party dropdown button -->
<div class="party-dropdown-wrapper dropdown" style="position: relative; display: inline-block;">
    <input type="text" class="form-control party-search-input w-100" placeholder="Search party..." id="partyDropdownBtn" data-bs-toggle="dropdown" style="font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px; min-height: 34px;">
    <!-- Balance display -->
    <div id="partyBalanceDisplay" style="color: #007bff; font-weight: 600; margin-top: 4px;">
        <!-- JS will populate balance here -->
    </div>

    <!-- Dropdown menu (existing) -->
    <ul class="dropdown-menu w-100" aria-labelledby="partyDropdownBtn" id="partyDropdownMenu">
        <li class="dropdown-header d-flex justify-content-between px-3">
            <span>Party Name</span>
            <span>Opening Balance</span>
        </li>
          @foreach($parties as $party)
    <li>
        <a class="dropdown-item d-flex justify-content-between align-items-start party-option" href="#"
           data-id="{{ $party->id }}"
           data-name="{{ $party->name }}"
           data-phone="{{ $party->phone }}"
           data-phone-number-2="{{ $party->phone_number_2 }}"
           data-city="{{ $party->city }}"
           data-ptcl="{{ $party->ptcl_number }}"
           data-email="{{ $party->email }}"
           data-address="{{ addslashes($party->address ?? '') }}"
           data-billing="{{ addslashes($party->billing_address ?? '') }}"
           data-shipping="{{ addslashes($party->shipping_address ?? '') }}"
           data-party-group="{{ $party->party_group }}"
           data-due-days="{{ $party->due_days ?? '' }}"
           data-opening="{{ $party->opening_balance ?? 0 }}"
           data-type="{{ $party->transaction_type }}"
           data-party-type="{{ is_array($party->party_type) ? implode(',', $party->party_type) : ($party->party_type ?? '') }}"
           data-credit-limit-enabled="{{ $party->credit_limit_enabled ?? 0 }}"
           data-credit-limit-amount="{{ $party->credit_limit_amount ?? '' }}"
           data-custom-fields="{{ e(json_encode($party->custom_fields ?? [])) }}">
            <span class="party-option-main">
                <span class="party-option-name">{{ $party->name }}</span>
                <span class="party-option-phone">{{ $party->phone ?: '-' }}</span>
            </span>
         <span
    @if($party->transaction_type == 'pay')
        class="text-danger"
    @elseif($party->transaction_type == 'receive')
        class="text-success"
    @endif
>
    @if($party->transaction_type == 'pay')
        <i class="fa-solid fa-arrow-up me-1"></i>
    @elseif($party->transaction_type == 'receive')
        <i class="fa-solid fa-arrow-down me-1"></i>
    @endif

    ₹{{ number_format($party->opening_balance ?? 0, 2) }}
</span>
        </a>
    </li>
@endforeach
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-primary" href="#" id="addNewPartyBtn">+ Add New Party</a></li>
    </ul>
</div>
<input type="hidden" class="party-id" name="party_id">
<div class="selected-party-summary d-none" id="selectedPartySummary">
    <div><strong>Party:</strong> <span class="party-summary-name">-</span></div>
    <div><strong>Phone:</strong> <span class="party-summary-phone">-</span></div>
    <div><strong>Billing:</strong> <span class="party-summary-billing">-</span></div>
    <div><strong>Shipping:</strong> <span class="party-summary-shipping">-</span></div>
</div>

                                </div>

                            <div class="header-right w-25">
                                <div class="header-mini-fields-grid dynamic-invoice-fields-row"></div>
                                <div class="input-group invoice-number-group">
                                    <span>Invoice No.</span>
                                    <div class="invoice-prefix-stack">
                                        <select class="input-control underline-input sale-prefix-select"></select>
                                        <input type="text" class="input-control underline-input bill-number" value="{{ $nextInvoiceNumber ?? 'Auto' }}" readonly>
                                    </div>
                                </div>
                                <div class="input-group date-wrapper invoice-date-group">
                                    <span>Invoice Date</span>
                                    <input type="text" class="input-control underline-input invoice-date" placeholder="dd/mm/yyyy" readonly>
                                </div>
                                <div class="input-group date-wrapper transaction-time-group d-none">
                                    <span>Invoice Time</span>
                                    <input type="text" class="input-control underline-input transaction-time-display" placeholder="03:45 PM" readonly>
                                </div>
                                <div class="input-group date-wrapper deal-days-group">
                                    <span>Deal Days</span>
                                    <select class="input-control underline-input due-days-select">
                                        <option value="0">0 Days</option>
                                        <option value="5">5 Days</option>
                                        <option value="10">10 Days</option>
                                        <option value="15">15 Days</option>
                                        <option value="30">30 Days</option>
                                        <option value="45">45 Days</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                    <input type="number" class="input-control underline-input due-days-custom d-none" placeholder="Custom deal days" min="0">
                                </div>
                                <div class="input-group date-wrapper final-due-date-group">
                                    <span>Due Date</span>
                                    <input type="text" class="input-control underline-input due-date" placeholder="dd/mm/yyyy" readonly>
                                </div>

                            </div>
                        </div>

                        <div class="alert alert-success d-none sale-success-msg"></div>

                        <!-- Table Section -->
                        @php
                            $itemFormSettings = $itemFormSettings ?? json_decode(\App\Models\AppSetting::getValue('item_form_settings', '{}'), true) ?: [];
                            $itemEnable = (bool) data_get($itemFormSettings, 'enable_item', true);
                            $sellType = (string) data_get($itemFormSettings, 'sell_type', 'both');
                            $showProducts = $itemEnable && in_array($sellType, ['product', 'both'], true);
                            $showServices = $itemEnable && in_array($sellType, ['service', 'both'], true);

                            $saleItemsSource = $showProducts ? collect($items ?? []) : collect();
                            if ($saleItemsSource->isEmpty() && $showProducts) {
                                $saleItemsSource = \App\Models\Item::with('category')
                                    ->where(function ($query) {
                                        $query->where('type', 'product')
                                            ->orWhereNull('type');
                                    })
                                    ->where(function ($query) {
                                        $query->where('is_active', true)
                                            ->orWhereNull('is_active');
                                    })
                                    ->orderBy('name')
                                    ->get();
                            }

                            $serviceItemsSource = $showServices ? collect($serviceItemsSource ?? collect()) : collect();
                            if ($serviceItemsSource->isEmpty() && $showServices) {
                                $serviceItemsSource = \App\Models\Item::with('category')
                                    ->where('type', 'service')
                                    ->where(function ($query) {
                                        $query->where('is_active', true)
                                            ->orWhereNull('is_active');
                                    })
                                    ->orderBy('name')
                                    ->get();
                            }

                            $saleCategoryOptions = collect($saleItemsSource)
                                ->concat($serviceItemsSource)
                                ->map(function ($item) {
                                    return $item->category->name ?? $item->category_name ?? $item->category_id ?? null;
                                })
                                ->filter()
                                ->map(fn ($value) => trim((string) $value))
                                ->filter()
                                ->unique()
                                ->sort()
                                ->values();
                        @endphp
                        @include('dashboard.sales.partials._create_item_table')

                        <!-- Bottom Split Section -->
                        <div class="bottom-section">
                            <!-- Left Column -->
                            <div class="bottom-left">
                                <div class="payment-section">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control default-payment-direction d-none" style="max-width: 140px;">
                                            <option value="payment_in" selected>Payment In</option>
                                            <option value="payment_out">Payment Out</option>
                                        </select>
                                        <select class="input-control default-payment-type">
                                            <option value="">Select Payment Type</option>
                                            <option value="cash" selected>Cash</option>
                                            <option value="cheques">Cheques</option>
                                            @foreach($bankAccounts as $bank)
                                                <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                            @endforeach
                                            <option value="add_new_bank">+ Add Bank Account</option>
                                        </select>
                                                                                <input type="text" class="input-control default-payment-reference d-none" placeholder="Reference">

                                        <input type="number" class="input-control default-payment-amount d-none" placeholder="Amount" min="0" step="0.01">
                                    </div>

                                    <div class="payment-entries">
                                        <!-- Payment rows will be added here when "Add Payment type" is clicked -->
                                    </div>

                                    <div class="payment-total d-flex justify-content-between align-items-center mt-2">
                                        <span class="text-muted">Total payment:</span>
                                        <span class="fw-bold payment-total-amount">0</span>
                                    </div>

                                    <a href="#" class="link-text add-payment-entry">+ Add Payment type</a>
                                    <div class="transportation-details-live-section d-none mt-3"></div>
                                </div>

                                <template id="payment-entry-template">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control payment-direction-entry d-none" style="max-width: 140px;">
                                            <option value="payment_in" selected>Payment In</option>
                                            <option value="payment_out">Payment Out</option>
                                        </select>
                                         <select class="input-control payment-type-entry">
                                              <option value="">Select Bank Account</option>
                                              <option value="cash" selected>Cash</option>
                                              <option value="cheques">Cheques</option>
                                              @foreach($bankAccounts as $bank)
                                                  <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                              @endforeach
                                              <option value="add_new_bank">+ Add Bank Account</option>
                                          </select>
                                                                                  <input type="text" class="input-control payment-reference" placeholder="Reference">

                                        <input type="number" class="input-control payment-amount" placeholder="Amount" min="0" step="0.01">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-payment-entry" title="Remove">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </template>

<div class="d-flex flex-column align-items-start w-100">

                                <div class="action-fields-layout meta-stack-layout w-100">
                                    <div class="terms-condition-group mb-2">

                                        <div class="terms-condition-pane mt-2">
                                            <div class="terms-condition-card">
                                                <h6 class="terms-condition-card-title">Terms &amp; Conditions</h6>
                                                <div class="terms-condition-row">
                                                    <div class="terms-condition-field">
                                                        <label class="terms-condition-field-label">Terms &amp; Conditions</label>
                                                        <select class="form-select terms-condition-select">
                                                            <option value="">Select Terms</option>
                                                        </select>
                                                    </div>
                                                    <button type="button" class="terms-condition-add-btn open-terms-condition-modal" title="Add Terms & Conditions">
                                                        <i class="fa-solid fa-plus"></i>
                                                    </button>
                                                </div>
                                                <textarea class="form-control terms-condition-text" rows="5" placeholder="Thanks for doing business with us!"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="meta-right-stack d-flex gap-3">
                                    <div class="action-buttons-column">
                                        <div class="description-action-group mb-2 w-100 d-flex">
                                            <button type="button" class="btn-action-light action-btn add-description">
                                                <i class="fa-solid fa-align-left"></i>
                                                ADD DESCRIPTION
                                            </button>
                                            <div class="description-content-row">
                                                <div class="description-pane d-none">
                                                    <div class="floating-input-wrapper">
                                                        <textarea class="form-control description-input meta-control" rows="3" placeholder=" "></textarea>
                                                        <label>Description</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="action-buttons d-flex flex-wrap gap-2 mb-2 w-100">
                                            <button type="button" class="btn-action-light action-btn add-image">
                                                <i class="fa-solid fa-camera"></i>
                                                ADD IMAGE
                                            </button>

                                            <button type="button" class="btn-action-light action-btn add-document">
                                                <i class="fa-solid fa-align-left"></i>
                                                ADD DOCUMENT
                                            </button>
                                        </div>
                                    </div>

                                    <div class="description-side-fields compact-side-fields d-flex flex-column gap-2">
                                        <div class="party-meta-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="goods_name" class="meta-control goods-name-input" placeholder=" ">
                                                <label>Goodz / Name</label>
                                            </div>
                                        </div>
                                        <div class="party-meta-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="bilti_gari_no" class="meta-control bilti-gari-input" placeholder=" ">
                                                <label>Bilti No / Gari No</label>
                                            </div>
                                        </div>
                                        <div class="party-meta-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="details_extra" class="meta-control details-extra-input" placeholder=" ">
                                                <label>Details Extra</label>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>

                               <div class="image-upload-section mt-2">
                                    <div class="image-placeholder text-center p-3 border border-dashed rounded" style="cursor:pointer;">
                                        <div class="text-muted">Click to select image(s)</div>
                                        <div class="small text-muted">(PNG/JPG, up to 5MB each)</div>
                                    </div>
                                    <div class="image-files-list d-flex flex-wrap gap-2 mt-2"></div>
                                    <div class="document-files-list list-group mt-2"></div>
                                </div>

                                <input type="file" class="d-none image-input" accept="image/*" multiple />
                                <input type="file" class="d-none document-input" accept=".pdf,.doc,.docx" multiple />
                            </div>

                        </div>

                            <!-- Right Column -->
                            <div class="bottom-right">
                                <div class="calc-row broker-calc-row d-none legacy-broker-calc-row">
                                    <div class="calc-label">Broker</div>
                                    <div class="calc-inputs broker-calc-inputs">
                                        <div class="broker-dropdown-wrapper dropdown" data-bs-auto-close="outside" style="position: relative; display: inline-block; width: 260px; max-width: 100%;">
                                            <input type="text" class="form-control broker-search-input w-100" placeholder="Broker" id="brokerDropdownBtn" data-bs-toggle="dropdown" autocomplete="off">
                                            <div class="broker-selected-info">
                                                <div class="broker-selected-name"></div>
                                                <div class="broker-selected-phone"></div>
                                            </div>

                                            <ul class="dropdown-menu w-100" aria-labelledby="brokerDropdownBtn" id="brokerDropdownMenu">
                                                @foreach($brokers as $broker)
                                                <li>
                                                    <a class="dropdown-item d-flex justify-content-between align-items-center broker-option" href="#"
                                                       data-id="{{ $broker->id }}"
                                                       data-phone="{{ $broker->phone }}"
                                                       data-name="{{ $broker->name }}"
                                                       data-commission-rate="{{ $broker->commission_rate ?? 0 }}">
                                                        <div class="broker-option-name">{{ $broker->name }}</div>
                                                        <div class="broker-option-city text-muted small">{{ $broker->city ?: '-' }}</div>
                                                    </a>
                                                </li>
                                                @endforeach
                                               </ul>
                                        </div>
                                        <button type="button" class="broker-inline-add-btn open-broker-modal-btn">+ Broker</button>
                                        <div class="brokerage-inputs">
                                        <select class="brokerage-type">
                                            <option value="">Condition</option>
                                            <option value="broker_rate">Broker Rate</option>
                                            <option value="full">Poori Brokerage (0.45%)</option>
                                            <option value="half">Aadhi Brokerage (0.225%)</option>
                                            <option value="custom_pct">Custom %</option>
                                            <option value="fixed_rs">Rs</option>
                                            <option value="per_kg">Per KG (Safi Wazan)</option>
                                        </select>
                                        <input type="number" class="brokerage-rate" min="0" step="0.01" placeholder="Value">
                                        <input type="hidden" class="brokerage-base-amount" value="0">
                                        <input type="number" class="brokerage-amount" min="0" step="0.01" value="0" readonly>
                                    </div>
                                        <input type="hidden" class="broker-id" name="broker_id">
                                        <input type="hidden" class="broker-phone-input" name="broker_phone">
                                    </div>
                                </div>



                                <!-- Discount -->
                                <div class="calc-row">
                                    <div class="calc-label">Discount</div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input discount-pct" placeholder="%">
                                        <span>-</span>
                                        <input type="number" class="mini-input discount-rs" placeholder="Rs">
                                    </div>
                                </div>

                                <!-- Tax -->
                                <div class="calc-row">
                                    <div class="calc-label">Tax</div>
                                    <div class="calc-inputs">
                                        <select class="mini-input tax-select" style="width: 100px;">
                                            <option value="0">NONE</option>
                                            <option value="5">GST@5%</option>
                                            <option value="12">GST@12%</option>
                                            <option value="18">GST@18%</option>
                                        </select>
                                        <span class="tax-amount-display">0</span>
                                    </div>
                                </div>

                                <div class="additional-charge-live-section d-none"></div>

                                <!-- Summary Expense Grid -->


                                <div class="custom-expense-section">
                                    <div class="custom-expense-rows"></div>
                                    <button type="button" class="btn-action-light action-btn add-custom-expense-row">ADD ROW</button>
                                </div>

                                <!-- Round Off -->
                                <div class="calc-row">
                                    <div class="checkbox-group">
                                        <input type="checkbox" class="custom-checkbox round-off-check" checked>
                                        <label class="link-text">Round Off</label>
                                    </div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input round-off-val" value="0" readonly>
                                    </div>
                                </div>

                                <!-- Final Total -->
                                <div class="final-total-group">
                                    <div class="calc-row" style="margin-bottom: 5px;">
                                        <div class="calc-label" style="font-weight: 700;">Total</div>
                                    </div>
                                    <input type="text" class="total-input-large grand-total" value="0" readonly>
                                </div>

                                <div class="calc-row">
                                    <div class="calc-label">Paid Amount</div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input received-amount" value="0" readonly>
                                    </div>
                                </div>

                                <div class="calc-row">
                                    <div class="calc-label">Remaining Amount</div>
                                    <div class="calc-inputs">
                                        <span class="fw-bold balance-amount">0</span>
                                    </div>
                                </div>
                            </div>

                            <template id="custom-expense-row-template">
                                <div class="calc-row custom-expense-row">
                                    <div class="calc-label">
                                        <span class="editable-expense-label custom-expense-heading" contenteditable="true" spellcheck="false">New Row</span>
                                    </div>
                                    <div class="calc-inputs custom-expense-inputs">
                                        <div class="custom-expense-mode-group" role="group" aria-label="Adjustment mode">
                                            <button type="button" class="custom-mode-btn" data-mode="-">-</button>
                                            <button type="button" class="custom-mode-btn is-active" data-mode="+">+</button>
                                            <button type="button" class="custom-mode-btn" data-mode="S">S</button>
                                        </div>
                                        <div class="broker-dropdown-wrapper dropdown custom-expense-account-wrap" data-bs-auto-close="outside" style="position: relative; display: inline-block; width: 190px; max-width: 100%;">
                                            <input type="text" class="form-control custom-expense-account-input w-100" placeholder="Party / Broker / Item" data-bs-toggle="dropdown" autocomplete="off">
                                            <ul class="dropdown-menu w-100 ledger-account-menu"></ul>
                                        </div>
                                        <input type="text" class="mini-input custom-expense-details" value="" placeholder="Tafseel">
                                        <input type="number" class="mini-input custom-expense-pct" value="" min="0" step="0.01" placeholder="%">
                                        <span class="text-muted small">-</span>
                                        <input type="number" class="mini-input custom-expense-value" value="0" min="0" step="0.01" placeholder="Amt">
                                        <input type="hidden" class="custom-expense-mode" value="+">
                                        <input type="hidden" class="custom-expense-account-type" value="">
                                        <input type="hidden" class="custom-expense-account-id" value="">
                                        <input type="hidden" class="custom-expense-account-phone" value="">
                                        <button type="button" class="remove-custom-expense-row" title="Remove">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Fixed Action Bar -->
                    <div class="sticky-actions">
                        <div class="btn-share">
                            <button class="btn-share-main">Share</button>
                            <button class="btn-share-arrow"><i class="fa-solid fa-chevron-down"></i></button>
                        </div>
                        <button class="btn-save" type="button">Save</button>
                    </div>
                </div>
            </template>
        </main>
    </div>



    <!-- Tab Limit Modal -->
    <div class="modal fade" id="tabLimitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-dark border-secondary">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
                    <h5>Maximum Limit Reached</h5>
                    <p>You can open a maximum of 10 transactions at a time.</p>
                    <button type="button" class="btn btn-primary px-4 mt-2" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Confirmation Modal -->
    <div class="modal fade" id="closeConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Close Tab?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to close this tab? Your purchase will not be saved. Use the Save button on
                        the bottom right of the screen to save.</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-close-btn" class="btn btn-danger">Close</button>
                </div>
            </div>
        </div>
    </div>
    @include('components.modals.party-modal')
    @include('components.modals.item-modal')

    <div class="modal fade" id="brokerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content broker-modal-card">
                <form id="brokerForm" action="{{ route('brokers.store') }}">
                    @csrf
                    <div class="modal-header broker-modal-header">
                        <div>
                            <h5 class="modal-title">Add Broker</h5>
                            <p class="broker-modal-subtitle mb-0">Save broker details and commission rate.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Broker Name</label>
                                <input type="text" class="form-control" name="name" id="brokerName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" id="brokerPhone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" id="brokerCity">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Type</label>
                                <select class="form-select" name="commission_type" id="brokerCommissionType">
                                    <option value="fixed">Fixed</option>
                                    <option value="percent">Percent</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Rate</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="commission_rate" id="brokerCommissionRate" value="0">
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="status" id="brokerStatus" checked>
                                    <label class="form-check-label" for="brokerStatus">Keep broker active</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Brokerage</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="total_brokerage" id="brokerTotalBrokerage" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Paid Brokerage</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="paid_brokerage" id="brokerPaidBrokerage" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Remaining</label>
                                <input type="text" class="form-control" id="brokerRemainingBrokerage" value="0.00" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" id="brokerAddress">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" id="brokerNotes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer broker-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn brokers-submit-btn">Save Broker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="warehouseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content broker-modal-card shadow-lg">
                <form id="warehouseForm" action="{{ route('warehouses.store') }}">
                    @csrf
                    <div class="modal-header broker-modal-header bg-gradient-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-warehouse me-3 fs-4"></i>
                            <div>
                                <h5 class="modal-title mb-0">Add New Warehouse</h5>
                                <p class="broker-modal-subtitle mb-0 opacity-75">Configure warehouse details and management information</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fa-solid fa-info-circle me-2"></i>Basic Information
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-building me-1"></i>Warehouse Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" name="name" id="warehouseName" required placeholder="Enter warehouse name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-phone me-1"></i>Phone
                                </label>
                                <input type="text" class="form-control form-control-lg" name="phone" id="warehousePhone" placeholder="Contact number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-envelope me-1"></i>Email
                                </label>
                                <input type="email" class="form-control form-control-lg" name="email" id="warehouseEmail" placeholder="warehouse@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-city me-1"></i>City
                                </label>
                                <input type="text" class="form-control form-control-lg" name="city" id="warehouseCity" placeholder="City location">
                            </div>
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fa-solid fa-cogs me-2"></i>Type & Capacity
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-tags me-1"></i>Warehouse Type
                                </label>
                                <select class="form-select form-select-lg" name="type" id="warehouseType">
                                    <option value="storage">Storage Warehouse</option>
                                    <option value="distribution">Distribution Center</option>
                                    <option value="cold_storage">Cold Storage</option>
                                    <option value="manufacturing">Manufacturing Warehouse</option>
                                    <option value="retail">Retail Warehouse</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-weight-hanging me-1"></i>Capacity
                                </label>
                                <input type="number" class="form-control form-control-lg" name="capacity" id="warehouseCapacity" step="0.01" min="0" placeholder="Storage capacity">
                            </div>
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fa-solid fa-user-tie me-2"></i>Management
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-user me-1"></i>Manager Name
                                </label>
                                <input type="text" class="form-control form-control-lg" name="manager_name" id="warehouseManagerName" placeholder="Warehouse manager">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-id-badge me-1"></i>Handler Name
                                </label>
                                <input type="text" class="form-control form-control-lg" name="handler_name" id="warehouseHandlerName" placeholder="Person handling deliveries">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-mobile-screen me-1"></i>Handler Phone
                                </label>
                                <input type="text" class="form-control form-control-lg" name="handler_phone" id="warehouseHandlerPhone" placeholder="Handler contact">
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="warehouseIsActive" checked>
                                    <label class="form-check-label fw-semibold ms-2" for="warehouseIsActive">Keep warehouse active</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fa-solid fa-location-dot me-2"></i>Address
                                </h6>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-map-marker-alt me-1"></i>Complete Address
                                </label>
                                <textarea class="form-control form-control-lg" name="address" id="warehouseAddress" rows="3" placeholder="Enter complete warehouse address"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-file-text me-1"></i>Description
                                </label>
                                <textarea class="form-control form-control-lg" name="description" id="warehouseDescription" rows="3" placeholder="Additional notes about the warehouse"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer broker-modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
                            <i class="fa-solid fa-save me-2"></i>Save Warehouse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @isset($proforma)
        <script>
            window.items = @json(($saleItemsSource ?? collect())->concat($serviceItemsSource ?? collect())->values());
            window.parties = @json($parties ?? []);
            window.brokers = @json($brokers ?? []);
            window.partyGroups = @json($partyGroups ?? []);
            window.bankAccounts = @json($bankAccounts ?? []);
            window.bankAccountRoutes = { store: "{{ route('bank-accounts.store') }}" };
            window.transactionSettings = { countEnabled: @json(\App\Models\AppSetting::getValue('transaction_items_count_enabled', '0') === '1'), countLabel: 'Count' };
            window.partyStoreUrl = "{{ route('parties.store') }}";
            window.partyGroupStoreUrl = "{{ route('party-groups.store') }}";
            window.itemRoutes = { index: "{{ url('dashboard/items') }}", store: "{{ url('dashboard/items') }}", unitsIndex: "{{ url('dashboard/items/units') }}", unitsStore: "{{ url('dashboard/items/units') }}", categoryStore: "{{ url('dashboard/items/category') }}" };
            window.saleStoreUrl = "{{ route('proforma-invoice.update', $proforma->id) }}";
            window.saleMethod = 'PUT';
            window.proformaId = {{ $proforma->id }};
            window.editSaleData = @json($proforma->load(['items', 'party', 'details'])->toArray());
            window.docType = 'proforma';
        </script>
    @elseif(isset($duplicateProforma))
        @php
            $duplicateProformaData = array_merge(
                $duplicateProforma->load(['items', 'party', 'details'])->toArray(),
                ['bill_number' => $nextInvoiceNumber]
            );
        @endphp
        <script>
            window.items = @json(($saleItemsSource ?? collect())->concat($serviceItemsSource ?? collect())->values());
            window.parties = @json($parties ?? []);
            window.brokers = @json($brokers ?? []);
            window.partyGroups = @json($partyGroups ?? []);
            window.bankAccounts = @json($bankAccounts ?? []);
            window.bankAccountRoutes = { store: "{{ route('bank-accounts.store') }}" };
            window.transactionSettings = { countEnabled: @json(\App\Models\AppSetting::getValue('transaction_items_count_enabled', '0') === '1'), countLabel: 'Count' };
            window.partyStoreUrl = "{{ route('parties.store') }}";
            window.partyGroupStoreUrl = "{{ route('party-groups.store') }}";
            window.itemRoutes = { index: "{{ url('dashboard/items') }}", store: "{{ url('dashboard/items') }}", unitsIndex: "{{ url('dashboard/items/units') }}", unitsStore: "{{ url('dashboard/items/units') }}", categoryStore: "{{ url('dashboard/items/category') }}" };
            window.saleStoreUrl = "{{ route('proforma-invoice.store') }}";
            window.saleMethod = 'POST';
            window.proformaId = null;
            window.editSaleData = @json($duplicateProformaData);
            window.docType = 'proforma';
        </script>
    @else
        <script>
            window.items = @json(($saleItemsSource ?? collect())->concat($serviceItemsSource ?? collect())->values());
            window.parties = @json($parties ?? []);
            window.brokers = @json($brokers ?? []);
            window.partyGroups = @json($partyGroups ?? []);
            window.bankAccounts = @json($bankAccounts ?? []);
            window.bankAccountRoutes = { store: "{{ route('bank-accounts.store') }}" };
            window.transactionSettings = { countEnabled: @json(\App\Models\AppSetting::getValue('transaction_items_count_enabled', '0') === '1'), countLabel: 'Count' };
            window.partyStoreUrl = "{{ route('parties.store') }}";
            window.partyGroupStoreUrl = "{{ route('party-groups.store') }}";
            window.itemRoutes = { index: "{{ url('dashboard/items') }}", store: "{{ url('dashboard/items') }}", unitsIndex: "{{ url('dashboard/items/units') }}", unitsStore: "{{ url('dashboard/items/units') }}", categoryStore: "{{ url('dashboard/items/category') }}" };
            window.saleStoreUrl = "{{ route('proforma-invoice.store') }}";
            window.saleMethod = 'POST';
            window.proformaId = null;
            window.editSaleData = null;
            window.docType = 'proforma';
        </script>
    @endisset

    <!-- Toast container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <div id="sale-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @include('dashboard.shared.item-create-modals')
    @include('dashboard.shared.item-column-modal')
    @include('components.bank-account-modal')

    <div class="modal fade" id="termsConditionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Terms &amp; Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="terms-modal-field">
                        <label class="terms-modal-label">Terms and Conditions</label>
                        <input type="text" class="form-control" id="termsConditionNameInput" placeholder="">
                        <div class="terms-modal-help">You can select the term based on the header you select here</div>
                    </div>
                    <div class="terms-modal-field">
                        <label class="terms-modal-label">Description</label>
                        <textarea class="form-control terms-modal-textarea" id="termsConditionDescriptionInput" placeholder="Paste/Write your terms and conditions here"></textarea>
                    </div>
                    <div class="terms-modal-field mb-0">
                        <label class="terms-modal-label">Applicable for:</label>
                        <div class="terms-modal-grid">
                            <label class="terms-modal-check"><input type="checkbox" value="invoice" class="terms-applicable-check"> Sale Invoice</label>
                            <label class="terms-modal-check"><input type="checkbox" value="sale_order" class="terms-applicable-check"> Sale Order</label>
                            <label class="terms-modal-check"><input type="checkbox" value="delivery_challan" class="terms-applicable-check"> Delivery Challan</label>
                            <label class="terms-modal-check"><input type="checkbox" value="estimate" class="terms-applicable-check"> Estimation/Quotation</label>
                            <label class="terms-modal-check"><input type="checkbox" value="purchase_bill" class="terms-applicable-check"> Purchase Bill</label>
                            <label class="terms-modal-check"><input type="checkbox" value="purchase_order" class="terms-applicable-check"> Purchase Order</label>
                            <label class="terms-modal-check"><input type="checkbox" value="proforma" class="terms-applicable-check"> Proforma Invoice</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer terms-modal-actions">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTermsConditionBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Logic -->
    <script>
        window.transactionTermsTemplates = @json($termsConditionTemplates ?? []);
        window.saleFormSettings = @json($saleFormSettings ?? (json_decode(\App\Models\AppSetting::getValue('sale_form_settings', '{}'), true) ?: []));
        window.itemFormSettings = @json($itemFormSettings ?? (json_decode(\App\Models\AppSetting::getValue('item_form_settings', '{}'), true) ?: []));
        window.termsConditionTemplates = @json($termsConditionTemplates ?? []);
        window.saleSettingsUpdateUrl = @json(route('sale.settings.update'));
        window.termsConditionStoreUrl = @json(route('sale.terms-conditions.store'));
        window.saleNextNumberUrl = @json(route('sale.next-number'));
    </script>
    <script src="{{ asset('js/saleform_script.js') }}"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/perfoma_script.js') }}"></script>
    <script src="{{ asset('js/shared-party-item-create.js') }}"></script>
    <script src="{{ asset('js/bank-account-modal.js') }}"></script>
    <script src="{{ asset('js/transaction-count-column.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const brokerForm = document.getElementById('brokerForm');
        const brokerModalEl = document.getElementById('brokerModal');
        const brokerModal = brokerModalEl ? new bootstrap.Modal(brokerModalEl) : null;
        const brokerTotalBrokerageInput = document.getElementById('brokerTotalBrokerage');
        const brokerPaidBrokerageInput = document.getElementById('brokerPaidBrokerage');
        const brokerRemainingBrokerageInput = document.getElementById('brokerRemainingBrokerage');
        const warehouseSelect = document.querySelector('.warehouse-select');
        const warehouseModalEl = document.getElementById('warehouseModal');
        const warehouseForm = document.getElementById('warehouseForm');
        const warehouseModal = warehouseModalEl ? new bootstrap.Modal(warehouseModalEl) : null;
        const partyDropdownMenu = document.getElementById('partyDropdownMenu');
        const partySearchInput = document.getElementById('partyDropdownBtn');
        let lastWarehouseValue = warehouseSelect?.value || '';

        const escapeHtml = function (value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        const refreshPartyDropdownMenu = function () {
            if (!partyDropdownMenu) return;

            const parties = Array.isArray(window.parties) ? window.parties : [];
            const partyOptions = parties.map((party) => `
                <li>
                    <a class="dropdown-item d-flex justify-content-between align-items-start party-option"
                       href="#"
                       data-id="${party.id || ''}"
                       data-name="${escapeHtml(party.name || '')}"
                       data-phone="${escapeHtml(party.phone || '')}"
                       data-phone-number-2="${escapeHtml(party.phone_number_2 || '')}"
                       data-city="${escapeHtml(party.city || '')}"
                       data-ptcl="${escapeHtml(party.ptcl_number || '')}"
                       data-email="${escapeHtml(party.email || '')}"
                       data-address="${escapeHtml(party.address || '')}"
                       data-billing="${escapeHtml(party.billing_address || '')}"
                       data-shipping="${escapeHtml(party.shipping_address || '')}"
                       data-party-group="${escapeHtml(party.party_group || '')}"
                       data-due-days="${escapeHtml(party.due_days || '')}"
                       data-opening="${party.opening_balance || 0}"
                       data-type="${escapeHtml(party.transaction_type || '')}">
                        <span class="party-option-main">
                            <span class="party-option-name">${escapeHtml(party.name || '')}</span>
                            <span class="party-option-phone">${escapeHtml(party.phone || '-')}</span>
                        </span>
                        <span class="${party.transaction_type === 'pay' ? 'text-danger' : (party.transaction_type === 'receive' ? 'text-success' : '')}">
                            ${party.transaction_type === 'pay' ? '<i class="fa-solid fa-arrow-up me-1"></i>' : party.transaction_type === 'receive' ? '<i class="fa-solid fa-arrow-down me-1"></i>' : ''}
                            Rs ${Number(party.opening_balance || 0).toFixed(2)}
                        </span>
                    </a>
                </li>
            `).join('');

            partyDropdownMenu.innerHTML = `
                <li class="dropdown-header d-flex justify-content-between px-3">
                    <span>Party Name</span>
                    <span>Opening Balance</span>
                </li>
                ${partyOptions}
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-primary" href="#" id="addNewPartyBtn">+ Add New Party</a></li>
            `;
        };

        partySearchInput?.addEventListener('input', function () {
            const searchTerm = String(this.value || '').trim().toLowerCase();
            partyDropdownMenu?.querySelectorAll('.party-option').forEach((option) => {
                const partyName = String(option.dataset.name || option.querySelector('.party-option-name')?.textContent || '').toLowerCase();
                const partyPhone = String(option.dataset.phone || option.querySelector('.party-option-phone')?.textContent || '').toLowerCase();
                const isVisible = !searchTerm || partyName.includes(searchTerm) || partyPhone.includes(searchTerm);
                option.closest('li')?.classList.toggle('d-none', !isVisible);
            });
        });

        partySearchInput?.addEventListener('focus', function () {
            this.dispatchEvent(new Event('input'));
        });

        const updateBrokerRemaining = function () {
            const total = parseFloat(brokerTotalBrokerageInput?.value || 0) || 0;
            const paid = parseFloat(brokerPaidBrokerageInput?.value || 0) || 0;
            if (brokerRemainingBrokerageInput) {
                brokerRemainingBrokerageInput.value = (total - paid).toFixed(2);
            }
        };

        brokerTotalBrokerageInput?.addEventListener('input', updateBrokerRemaining);
        brokerPaidBrokerageInput?.addEventListener('input', updateBrokerRemaining);

        window.openBrokerModalForm = function () {
            brokerForm?.reset();
            const brokerStatusField = document.getElementById('brokerStatus');
            if (brokerStatusField) brokerStatusField.checked = true;
            updateBrokerRemaining();
            brokerModal?.show();
        };

        document.addEventListener('click', function (event) {
            if (event.target.closest('.ledger-account-action[data-action="broker"]')) {
                event.preventDefault();
                window.openBrokerModalForm?.();
            }
        });

        brokerForm?.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(brokerForm);
            formData.set('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
            formData.set('status', document.getElementById('brokerStatus')?.checked ? '1' : '0');

            fetch("{{ route('brokers.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok || !data.success || !data.broker) {
                    throw new Error(data.message || 'Failed to save broker');
                }
                return data.broker;
            })
            .then((broker) => {
                window.brokers = Array.isArray(window.brokers) ? window.brokers : [];
                window.brokers = window.brokers.filter((entry) => String(entry.id) !== String(broker.id));
                window.brokers.unshift(broker);
                brokerModal?.hide();
                brokerForm.reset();
                updateBrokerRemaining();
            })
            .catch((error) => {
                alert(error.message || 'Unable to save broker.');
            });
        });

        const openWarehouseModal = function () {
            warehouseForm?.reset();
            const isActiveSwitch = document.getElementById('warehouseIsActive');
            if (isActiveSwitch) isActiveSwitch.checked = true;
            warehouseModal?.show();
        };

        if (warehouseSelect) {
            warehouseSelect.addEventListener('focus', function () {
                lastWarehouseValue = this.value;
            });

            warehouseSelect.addEventListener('change', function () {
                if (this.value === 'add_new_warehouse') {
                    this.value = lastWarehouseValue || '';
                    openWarehouseModal();
                    return;
                }
                lastWarehouseValue = this.value;
            });
        }

        warehouseForm?.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(warehouseForm);
            formData.set('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
            formData.set('is_active', document.getElementById('warehouseIsActive')?.checked ? '1' : '0');

            fetch("{{ route('warehouses.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok || !data.success || !data.warehouse) {
                    throw new Error(data.message || 'Failed to save warehouse');
                }
                return data.warehouse;
            })
            .then((warehouse) => {
                if (!warehouseSelect) return;

                const option = document.createElement('option');
                option.value = warehouse.id;
                option.dataset.handlerName = warehouse.handler_name || '';
                option.dataset.handlerPhone = warehouse.handler_phone || '';
                option.textContent = warehouse.name || 'New Warehouse';

                const addNewOption = warehouseSelect.querySelector('option[value="add_new_warehouse"]');
                if (addNewOption) {
                    addNewOption.insertAdjacentElement('beforebegin', option);
                } else {
                    warehouseSelect.appendChild(option);
                }
                warehouseSelect.value = String(warehouse.id);
                lastWarehouseValue = warehouseSelect.value;
                warehouseModal?.hide();
                warehouseForm.reset();
            })
            .catch((error) => {
                alert(error.message || 'Unable to save warehouse.');
            });
        });

        refreshPartyDropdownMenu();
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const closeIcon = document.querySelector('.close-app-icon');
        if (!closeIcon) return;
        closeIcon.addEventListener('click', function () {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/dashboard/sales';
            }
        });
    });
    </script>

</body>


</html>
