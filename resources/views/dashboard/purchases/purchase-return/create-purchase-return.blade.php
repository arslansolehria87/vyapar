<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($purchaseReturn) ? 'Edit' : 'Create' }} Purchase Return | Vyapar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/saleorderform_style.css') }}">
    <style>
        .item-picker {
            position: relative;
            min-width: 260px;
            flex: 1;
            overflow: visible;
        }

        .item-picker-input {
            width: 100%;
            border: 1px solid #cfd8e3;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 14px;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .item-picker-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .item-picker-panel {
            position: fixed;
            top: 0;
            left: 0;
            width: 760px;
            min-width: 520px;
            max-width: calc(100vw - 24px);
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
            z-index: 2050;
            display: none;
            overflow: hidden;
            box-sizing: border-box;
        }

        .item-picker-panel.open {
            display: block !important;
        }

        .item-picker-head > span:first-child,
        .item-picker-row > .item-picker-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .item-picker-list {
            max-height: 320px;
            overflow-y: auto;
        }

        .item-picker-list::-webkit-scrollbar {
            width: 8px;
        }

        .item-picker-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .item-picker-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .item-picker-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .item-picker-add {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            color: #2563eb;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .item-picker-add:hover {
            background: #f8fbff;
        }

        .item-picker-head,
        .item-picker-row {
            display: grid;
            grid-template-columns: minmax(0, 2.4fr) 110px 120px 90px;
            gap: 12px;
            align-items: center;
        }

        .item-picker-head {
            padding: 10px 18px;
            font-size: 12px;
            font-weight: 700;
            color: #97a3b6;
            text-transform: uppercase;
        }

        .item-picker-row {
            padding: 12px 18px;
            cursor: pointer;
            border-top: 1px solid #f4f7fb;
        }

        @media (max-width: 768px) {
            .item-picker-head,
            .item-picker-row {
                grid-template-columns: minmax(0, 2fr) 90px 100px 90px;
                gap: 8px;
            }

            .item-picker-panel {
                max-width: 400px;
            }
        }

        @media (max-width: 576px) {
            .item-picker {
                min-width: 200px;
            }

            .item-picker-head,
            .item-picker-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .item-picker-head span:nth-child(2),
            .item-picker-head span:nth-child(3),
            .item-picker-head span:nth-child(4),
            .item-picker-row > div:nth-child(2),
            .item-picker-row > div:nth-child(3),
            .item-picker-row > div:nth-child(4) {
                display: none;
            }

            .item-picker-panel {
                max-width: 300px;
            }
        }

        .item-picker-row:hover {
            background: #f8fbff;
        }

        .item-picker-name small {
            color: #8a94a6;
            margin-left: 6px;
        }

        .item-picker-stock.neg {
            color: #dc3545;
        }

        .item-picker-empty {
            padding: 14px 18px;
            color: #8a94a6;
            font-size: 13px;
        }

        .col-item-name {
            width: auto;
            min-width: 0;
            max-width: none;
        }

        .table-container {
            position: relative;
            overflow: visible;
        }

        .purchase-return-table-scroll {
            overflow-x: hidden;
            overflow-y: visible;
            width: 100%;
        }

        .purchase-return-table-scroll .item-table {
            width: 100%;
            min-width: 100%;
            table-layout: fixed;
        }

        .purchase-return-table-scroll .item-table th,
        .purchase-return-table-scroll .item-table td {
            min-width: 0;
            padding-left: 6px;
            padding-right: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .purchase-return-table-scroll .item-table .row-num {
            width: 42px;
        }

        .purchase-return-table-scroll .item-table .add-col {
            width: 42px;
        }

        .purchase-return-table-scroll .item-table .col-barcode-scan {
            width: 54px;
        }

        .purchase-return-table-scroll .item-table .col-item-name {
            width: 24%;
            min-width: 190px;
        }

        .purchase-return-table-scroll .item-table .col-tafseel {
            width: 12%;
            min-width: 110px;
        }

        .purchase-return-table-scroll .item-table .col-tadaat,
        .purchase-return-table-scroll .item-table .col-free-qty,
        .purchase-return-table-scroll .item-table .col-gross-w,
        .purchase-return-table-scroll .item-table .col-net-w,
        .purchase-return-table-scroll .item-table .col-rate,
        .purchase-return-table-scroll .item-table .col-amount {
            width: 7.5%;
            min-width: 68px;
        }

        .purchase-return-table-scroll .item-table .custom-size-th,
        .purchase-return-table-scroll .item-table .custom-size-td,
        .purchase-return-table-scroll .item-table .col-serial-no,
        .purchase-return-table-scroll .item-table .col-model-no,
        .purchase-return-table-scroll .item-table .col-description,
        .purchase-return-table-scroll .item-table .col-batch-no,
        .purchase-return-table-scroll .item-table .col-exp-date,
        .purchase-return-table-scroll .item-table .col-mfg-date,
        .purchase-return-table-scroll .item-table .col-size,
        .purchase-return-table-scroll .item-table .col-category,
        .purchase-return-table-scroll .item-table .col-item-code,
        .purchase-return-table-scroll .item-table .col-discount,
        .purchase-return-table-scroll .item-table .col-item-tax,
        .purchase-return-table-scroll .item-table .custom-item-field {
            width: 9%;
            min-width: 84px;
        }

        .purchase-return-table-scroll .item-table .item-picker,
        .purchase-return-table-scroll .item-table input,
        .purchase-return-table-scroll .item-table select {
            min-width: 0;
            max-width: 100%;
        }

        .purchase-return-table-scroll .item-table input,
        .purchase-return-table-scroll .item-table select,
        .purchase-return-table-scroll .item-table .item-picker-input {
            padding-left: 4px;
            padding-right: 4px;
            text-overflow: ellipsis;
        }

        .table-footer {
            width: 100%;
            box-sizing: border-box;
        }

        .purchase-return-table-scroll .item-table th {
            position: relative;
            cursor: pointer;
            user-select: none;
        }

        .purchase-return-table-scroll .item-table th:hover {
            background-color: #f0f4f8;
        }

        .purchase-return-table-scroll .item-table th.editable-header::after {
            content: '\270E';
            margin-left: 4px;
            opacity: 0.5;
            font-size: 0.85em;
        }

        .purchase-return-table-scroll .item-table th.editable-header:hover::after {
            opacity: 1;
        }

        .purchase-return-table-scroll .item-table tfoot td {
            background: #fbfdff;
            border-top: 1px solid #d8e0ea;
            border-bottom: 0;
            padding: 12px 10px;
        }

        .purchase-return-table-scroll .item-table .column-total-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .purchase-return-table-scroll .item-table .column-total-value {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
        }

        .purchase-return-table-scroll .item-table .tfoot-add-row-cell {
            text-align: left;
        }

        .header-mini-fields-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
            margin-top: 8px;
            width: 100%;
        }

        .header-mini-field {
            min-width: 0;
        }

        .floating-input-wrapper {
            position: relative;
        }

        .header-mini-field .meta-control {
            width: 100%;
            height: 34px;
            border: 1px solid #d7e0ea;
            border-radius: 6px;
            background: #fbfdff;
            font-size: 12px;
            padding: 10px 8px 5px;
        }

        .header-mini-field .floating-input-wrapper label {
            position: absolute;
            top: 2px;
            left: 8px;
            color: #64748b;
            font-size: 9px;
            pointer-events: none;
        }

        .transaction-billing-name-field .meta-control {
            width: 100%;
            height: 34px;
            border: 1px solid #d7e0ea;
            border-radius: 6px;
            background: #fff;
            font-size: 12px;
            padding: 10px 8px 5px;
        }

        .transaction-billing-name-field .floating-input-wrapper label {
            position: absolute;
            top: 2px;
            left: 8px;
            color: #64748b;
            font-size: 9px;
            pointer-events: none;
        }

        .transportation-details-live-section {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            align-items: flex-start;
            padding: 8px 0;
            width: 100%;
        }

        .transportation-details-live-section .transportation-live-field {
            flex: 1 1 0;
            min-width: 0;
        }

        .transportation-details-live-section .floating-input-wrapper {
            position: relative;
            width: 100%;
        }

        .transportation-details-live-section .meta-control {
            width: 100%;
            height: 46px;
            border: 1px solid #d7e0ea;
            border-radius: 6px;
            background: #fff;
            padding: 17px 20px 6px;
            font-size: 13px;
            color: #1f2937;
            outline: none;
        }

        .transportation-details-live-section label {
            position: absolute;
            top: 8px;
            left: 20px;
            color: #42526b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            pointer-events: none;
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

        .terms-condition-card {
            max-width: 460px;
            padding: 14px;
            border: 1px solid #dfe5ec;
            border-radius: 8px;
            background: #fff;
        }

        .terms-condition-card-title {
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 700;
        }

        .modal-stack-top {
            z-index: 1085;
        }

        #addItemModal {
            z-index: 3060;
        }

        .modal-backdrop.add-item-backdrop {
            z-index: 3050;
        }

        .item-unit-conversion-row {
            display: grid;
            grid-template-columns: auto auto minmax(120px, 1fr) auto;
            gap: 10px;
            align-items: center;
        }

        .item-stock-images-trigger {
            border: 1px dashed #93c5fd;
            border-radius: 8px;
            padding: 14px;
            color: #2563eb;
            cursor: pointer;
            background: #f8fbff;
        }

        .item-stock-images-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .item-stock-image-card {
            width: 110px;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 6px;
            background: #fff;
        }

        .item-stock-image-card img {
            width: 100%;
            height: 76px;
            object-fit: cover;
            border-radius: 6px;
        }

        .item-stock-image-card .name {
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .purchase-party-phone {
            margin-top: 14px;
            max-width: 240px;
        }

        .purchase-party-phone input {
            width: 100%;
            border: 0;
            border-bottom: 1px solid #d7dbe5;
            padding: 8px 2px;
            font-size: 15px;
            color: #374151;
            background: transparent;
            outline: none;
        }

        .party-selection-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 12px;
        }

        #partyInfoPanel {
            max-width: 280px;
            width: 280px;
        }

        .party-selection-container > div:not(#partyInfoPanel) {
            flex: 1;
        }

        .party-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .party-details-grid > div:nth-child(3) {
            grid-column: 1 / 3;
        }

        #partyDetailsInputs {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        #partyDetailsInputs > div {
            display: flex;
            flex-direction: column;
        }

        #partyDetailsInputs textarea,
        #partyDetailsInputs input {
            background-color: #ffffff;
        }
            grid-column: 2;
            grid-row: 1;
        }

        .party-selection-container > div:nth-child(3) {
            grid-column: 3;
            grid-row: 1;
        }

        .party-selection-container > div:nth-child(4) {
            grid-column: 1 / 2;
            grid-row: 2;
        }

        .party-selection-container > div:nth-child(5) {
            grid-column: 2 / 4;
            grid-row: 2;
        }

        .purchase-right-panel {
            width: 28%;
            padding-top: 4px;
        }

        .purchase-doc-grid {
            display: grid;
            gap: 10px;
        }

        .purchase-doc-row {
            display: grid;
            grid-template-columns: 92px 1fr 24px;
            align-items: center;
            gap: 8px;
        }

        .purchase-doc-label {
            text-align: right;
            color: #8b93a7;
            font-size: 12px;
            font-weight: 500;
        }

        .purchase-doc-input {
            border: 0;
            border-bottom: 1px solid #d9deea;
            padding: 5px 0 4px;
            background: transparent;
            color: #1f2937;
            font-size: 12px;
            font-weight: 500;
            outline: none;
        }

        .purchase-doc-input::placeholder {
            color: #a0a7bb;
            font-weight: 500;
        }

        .purchase-doc-input[readonly] {
            color: #1f2937;
        }

        .purchase-doc-icon {
            color: #0d75df;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .purchase-hidden-date {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 1px;
            height: 1px;
        }

        /* Party Info Panel Styling */
        #partyInfoPanel {
            animation: slideDown 0.3s ease-in-out;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.12);
        }

        #partyInfoPanel h5 {
            letter-spacing: -0.3px;
            line-height: 1.1;
        }

        #partyInfoPanel small {
            line-height: 1.2;
        }

        #partyInfoPanel .party-info-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            line-height: 1.15;
        }

        #partyInfoPanel .party-info-phone {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            min-width: 0;
            color: #047857;
        }

        #partyInfoPanel .party-info-phone span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #partyInfoPanel .btn {
            transition: all 0.2s ease;
        }

        #partyInfoPanel .btn:hover {
            background-color: #dcfce7 !important;
            border-color: #15803d !important;
        }

        .purchase-return-link-spacer {
            flex: 1;
        }

        .purchase-return-link-payment-btn {
            display: none;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 6px;
            background: #20d9b0;
            color: #fff;
            font-weight: 800;
            padding: 12px 24px;
            letter-spacing: .01em;
        }

        .purchase-return-link-payment-btn.is-visible {
            display: inline-flex;
        }

        .purchase-return-link-payment-btn .lp-question {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .32);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .purchase-return-link-payment-modal .modal-dialog {
            max-width: 800px;
        }

        .purchase-return-link-payment-modal .modal-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        }

        .link-payment-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            padding: 12px 0 16px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 14px;
        }

        .link-payment-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 28px;
            align-items: end;
        }

        .link-payment-label {
            font-size: 12px;
            color: #0ea5e9;
            font-weight: 700;
            display: block;
            margin-bottom: 5px;
        }

        .link-payment-value {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .link-payment-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .link-payment-grid-wrap {
            max-height: 430px;
            overflow: auto;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .link-payment-grid thead th {
            background: #fafafa;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 2;
            white-space: nowrap;
        }

        .link-payment-grid tbody td {
            font-size: 13px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .link-payment-empty {
            text-align: center;
            color: #9ca3af;
            padding: 22px !important;
        }

        .unused-amount-negative {
            color: #dc2626;
        }

        .purchase-return-link-payment-modal .modal-footer {
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        .link-payment-total {
            font-weight: 700;
            color: #111827;
        }

        .link-payment-mini-btn {
            min-width: 72px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid min-vh-100 d-flex flex-column p-0">
        <header class="tab-system-header">
            <div class="tab-strip-wrapper justify-content-between">
                <div class="d-flex align-items-end flex-grow-1 overflow-hidden">
                    <div id="tab-strip" class="tab-strip d-flex align-items-end"></div>
                    <button id="add-tab-btn" class="btn add-tab-btn" title="New Tab">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div class="window-controls d-flex align-items-center px-2 gap-3">
                    <i id="calc-icon" class="fa-solid fa-calculator" title="Calculator"></i>
                    <button type="button" class="purchase-return-settings-trigger text-reset border-0 bg-transparent p-0" title="Settings" data-bs-toggle="offcanvas" data-bs-target="#purchaseReturnSettingsSidebar" aria-controls="purchaseReturnSettingsSidebar">
                        <i class="fa-solid fa-gear"></i>
                    </button>
                    <i class="fa-solid fa-xmark close-app-icon" title="Close Window"></i>
                </div>
            </div>
            <div class="browser-toolbar d-flex align-items-center px-3">
                <p class="mt-3 ms-3 mb-0 me-3 mb-2">Purchase Return / Debit Note</p>
            </div>
        </header>

        <main id="content-area">
            <template id="form-template">
                <div class="invoice-container">
                    <div class="invoice-form invoice-card">
                        <div class="header-section">
                            <div class="header-left">
                             <div class="input-group">
                                <!-- Party dropdown button -->
<div class="party-dropdown-wrapper" style="position: relative; display: inline-block; width: 100%;">
    <input type="text" class="form-control party-search-input w-100" placeholder="Search party..." id="partyDropdownBtn" data-bs-toggle="dropdown" style="font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px; min-height: 34px;">

    <!-- Party Selection Container (shown after selection) - positioned right below input -->
    <div id="partySelectionContainer" class="d-none party-selection-container" style="display: flex; flex-direction: column; gap: 12px; margin-top: 12px;">

        <!-- Top Row: Party Card + Billing Name & Phone -->
        <div style="display: flex; gap: 12px; align-items: flex-start;">
            <!-- Party Info Card Panel -->
            <div id="partyInfoPanel" class="border rounded-3" style="background-color: #f0fdf4; border: 2px solid #22c55e; padding: 6px 10px; width: 280px; max-width: 280px; flex-shrink: 0;">
                <!-- Header with Name and Close Button -->
                <div class="d-flex justify-content-between align-items-flex-start" style="margin-bottom: 2px;">
                    <div style="flex: 1;">
                        <h5 class="mb-0 fw-700" id="partyInfoName" style="color: #15803d; font-size: 13px; letter-spacing: -0.3px;">Party Name</h5>
                        <div class="party-info-meta">
                            <i class="fa-solid fa-location-dot" style="color: #dc2626; font-size: 11px;"></i>
                            <span id="partyInfoDescription" style="font-size: 11px; color: #6b7280;">Party details</span>
                            <span id="partyInfoPhoneWrap" class="party-info-phone d-none" style="font-size: 11px;">
                                <i class="fa-solid fa-phone" style="font-size: 10px;"></i>
                                <span id="partyInfoPhone"></span>
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm" id="closePartyPanel" title="Close" style="background: white; border: 1px solid #dcfce7; color: #15803d; width: 20px; height: 20px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-solid fa-xmark" style="font-size: 10px;"></i>
                    </button>
                </div>

                <!-- Balance Display -->
                <div style="border-top: 1px solid #dcfce7; padding-top: 5px;">
                    <div class="d-flex align-items-center gap-1">
                        <span id="partyInfoBalance" class="fw-700" style="color: #15803d; font-size: 13px;">₹0.00</span>
                    </div>
                    <small style="color: #666; display: block; margin-top: 0; font-size: 10px;">BALANCE</small>
                </div>
            </div>

            <!-- Billing Name & Phone Inputs (Side by Side) -->
            <div style="display: flex; gap: 12px; flex: 1.5; align-items: center; margin-left: 100px;">
                <!-- Billing Name Input -->
                <div class="transaction-billing-name-field" style="flex: 1.5; min-width: 200px;">
                    <div class="floating-input-wrapper">
                        <input type="text" class="meta-control billing-name-input" placeholder=" ">
                        <label>Billing Name (Optional)</label>
                    </div>
                </div>

                <!-- Phone Number Input -->
                <div style="flex: 1.5; min-width: 200px;">
                    <input type="text" class="form-control party-phone-input" style="font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; width: 100%;" placeholder="PHONE NO.">
                </div>
            </div>
        </div>

        <!-- Bottom Row: Addresses (Full Width) -->
        <div class="party-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-left: 16px;">
            <!-- Billing Address Input -->
            <div>
                <textarea class="form-control billing-address-input" style="font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; resize: vertical;" rows="3" placeholder="Enter billing address"></textarea>
            </div>

            <!-- Shipping Address Input -->
            <div>
                <textarea class="form-control shipping-address-input" style="font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; resize: vertical;" rows="3" placeholder="Enter shipping address"></textarea>
            </div>
        </div>
    </div>

    <!-- Dropdown menu (existing) -->
    <ul class="dropdown-menu w-110" aria-labelledby="partyDropdownBtn" id="partyDropdownMenu">
        <li class="dropdown-header d-flex justify-content-between px-3">
            <span>Party Name</span>
            <span>Opening Balance</span>
        </li>
          @foreach($parties as $party)
    <li>
        <a class="dropdown-item d-flex justify-content-between party-option" href="#"
           data-id="{{ $party->id }}"
           data-name="{{ $party->name }}"
           data-phone="{{ $party->phone }}"
           data-billing="{{ addslashes($party->billing_address ?? '') }}"
           data-shipping="{{ addslashes($party->shipping_address ?? '') }}"
           data-opening="{{ $party->current_balance ?? $party->opening_balance ?? 0 }}"
           data-type="{{ $party->transaction_type }}">
            <span>{{ $party->name }}</span>
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

                                </div>

                                <div class="header-mini-fields-grid customer-po-settings-fields d-none">
                                    <div class="party-meta-field header-mini-field">
                                        <div class="floating-input-wrapper">
                                            <input type="text" class="meta-control po-no-input" placeholder=" ">
                                            <label>PO No.</label>
                                        </div>
                                    </div>
                                    <div class="party-meta-field header-mini-field">
                                        <div class="floating-input-wrapper">
                                            <input type="date" class="meta-control po-date-input" placeholder=" ">
                                            <label>PO Date</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="header-mini-fields-grid dynamic-invoice-fields-row d-none"></div>
                            </div>

                            <div class="purchase-right-panel">
                                <div class="purchase-doc-grid">
                                    <div class="purchase-doc-row transaction-invoice-number-row">
                                        <span class="purchase-doc-label">Return No.</span>
                                        <input type="text" class="purchase-doc-input bill-number" value="{{ $nextInvoiceNumber ?? 'PR-0001' }}" readonly>
                                        <span></span>
                                    </div>
                                    <div class="purchase-doc-row">
                                        <span class="purchase-doc-label">Bill Number</span>
                                        <input type="text" class="purchase-doc-input reference-bill-number" placeholder="">
                                        <span></span>
                                    </div>
                                    <div class="purchase-doc-row">
                                        <span class="purchase-doc-label">Bill Date</span>
                                        <input type="text" class="purchase-doc-input order-date-text" placeholder="DD/MM/YYYY" readonly>
                                        <span class="purchase-doc-icon"><i class="bi bi-calendar-date"></i></span>
                                        <input type="date" class="order-date purchase-hidden-date">
                                    </div>
                                    <div class="purchase-doc-row transaction-due-date-row">
                                        <span class="purchase-doc-label">Date</span>
                                        <input type="text" class="purchase-doc-input due-date-text" placeholder="DD/MM/YYYY" readonly>
                                        <span class="purchase-doc-icon"><i class="bi bi-calendar-date"></i></span>
                                        <input type="date" class="due-date purchase-hidden-date">
                                    </div>
                                    <div class="purchase-doc-row transaction-time-row d-none">
                                        <span class="purchase-doc-label">Time</span>
                                        <input type="text" class="purchase-doc-input transaction-time-display" readonly>
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success d-none sale-success-msg"></div>

                        <div class="table-container">
                            <div class="purchase-return-table-scroll">
                            <table class="item-table">
                                <thead>
                                    <tr>
                                        <th class="row-num">#</th>
                                        <th class="col-barcode-scan d-none">SCAN</th>
                                        <th class="col-item-name">ITEM</th>
                                        <th class="col-serial-no">SERIAL NO.</th>
                                        <th class="col-description d-none">DESCRIPTION</th>
                                        <th class="col-count d-none">COUNT</th>
                                        <th class="col-batch-no d-none">BATCH NO.</th>
                                        <th class="col-model-no">MODEL NO.</th>
                                        <th class="col-exp-date d-none">EXP. DATE</th>
                                        <th class="col-mfg-date d-none">MFG. DATE</th>
                                        <th class="col-mrp d-none">MRP</th>
                                        <th class="col-size d-none">SIZE</th>
                                        <th class="col-tafseel">TAFSEEL</th>
                                        <th class="col-tadaat">TADAAT</th>
                                        <th class="col-free-qty">FREE QTY</th>
                                        <th class="col-gross-w">GROSS W</th>
                                        <th class="col-net-w">NET W</th>
                                        <th class="custom-size-th">UNIT</th>
                                        <th class="col-rate">RATE</th>
                                        <th class="col-amount">AMOUNT</th>
                                        <th class="col-category d-none">CATEGORY</th>
                                        <th class="col-item-code d-none">ITEM CODE</th>
                                        <th class="col-discount d-none">DISCOUNT</th>
                                        <th class="col-item-tax d-none">TAX</th>
                                        @for($i = 1; $i <= 6; $i++)
                                            <th class="custom-item-field col-custom-field-{{ $i }} d-none">CUSTOM FIELD {{ $i }}</th>
                                        @endfor
                                        <th class="add-col" style="position: relative;">
                                            <button type="button" class="btn-add-circle table-settings-btn" data-bs-toggle="modal" data-bs-target="#purchaseReturnItemColumnModal">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                            <div class="settings-box d-none">
                                                <div class="settings-item">
                                                    <input type="checkbox" class="check-category">
                                                    <label>Item Category</label>
                                                </div>
                                                <div class="settings-item">
                                                    <input type="checkbox" class="check-item-code">
                                                    <label>Item Code</label>
                                                </div>
                                                <div class="settings-item">
                                                    <input type="checkbox" class="check-description">
                                                    <label>Description</label>
                                                </div>
                                                <div class="settings-item">
                                                    <input type="checkbox" class="check-discount">
                                                    <label>Discount</label>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="item-rows">
                                    <tr class="item-row">
                                        <td class="row-num">
                                            <span class="row-index-text">1</span>
                                            <div class="delete-row-icon"><i class="fa-solid fa-trash-can"></i></div>
                                        </td>
                                        <td class="col-barcode-scan d-none">
                                            <button type="button" class="btn btn-sm btn-outline-primary open-scan-serial-modal" title="Scan code/serial"><i class="fa-solid fa-qrcode"></i></button>
                                        </td>
                                        <td class="col-item-name">
                                            <div class="item-picker">
                                                <input type="text" class="item-picker-input" placeholder="Search Item">
                                                <div class="item-picker-panel">
                                                    <div class="item-picker-add" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; color: #2563eb; font-weight: 600; cursor: pointer; border-bottom: 1px solid #e1e8ed;"><i class="fa-regular fa-square-plus"></i> Add Item</div>
                                                    <div class="item-picker-head" style="display: grid; grid-template-columns: minmax(0, 2fr) 100px 110px 80px; gap: 12px; padding: 10px 18px; font-size: 12px; font-weight: 700; color: #97a3b6; text-transform: uppercase; background: #f8fbff; border-bottom: 1px solid #e1e8ed;">
                                                        <span>Item</span>
                                                        <span>Purchase Price</span>
                                                        <span>Sale Price</span>
                                                        <span>Stock</span>
                                                    </div>
                                                    <div class="item-picker-list" style="max-height: 280px; overflow-y: auto;">
                                                        @forelse($items as $item)
                                                            <div class="item-picker-row item-picker-option" data-id="{{ $item->id }}" data-type="product">
                                                                <div class="item-picker-name">
                                                                    {{ $item->name }}
                                                                    @if(!empty($item->item_code))
                                                                        <small>({{ $item->item_code }})</small>
                                                                    @endif
                                                                </div>
                                                                <div>{{ number_format((float) ($item->purchase_price ?? 0), 2, '.', '') }}</div>
                                                                <div>{{ number_format((float) ($item->sale_price ?? $item->price ?? 0), 2, '.', '') }}</div>
                                                                <div class="item-picker-stock {{ (float) ($item->opening_qty ?? 0) < 0 ? 'neg' : '' }}">{{ (float) ($item->opening_qty ?? 0) }}</div>
                                                            </div>
                                                        @empty
                                                            <div class="item-picker-empty">No items found</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                                <select class="form-select item-name d-none">
                                                    <option value="" selected disabled>Select Item</option>
                                                    @foreach($items as $item)
                                                        <option value="{{ $item->id }}"
                                                            data-price="{{ $item->price }}"
                                                            data-purchase-price="{{ $item->purchase_price ?? $item->price }}"
                                                            data-sale-price="{{ $item->sale_price }}"
                                                            data-stock="{{ $item->opening_qty }}"
                                                            data-location="{{ $item->location }}"
                                                            data-label="{{ $item->name }}"
                                                            data-rich-label="{{ $item->name }} | Sale: {{ $item->sale_price ?? $item->price ?? 0 }} | Stock: {{ $item->opening_qty ?? 0 }} | Location: {{ $item->location ?? '' }}"
                                                            data-unit="{{ $item->unit }}"
                                                            data-weight="{{ $item->bag_weight ?? 0 }}"
                                                            data-category="{{ $item->category->name ?? $item->category_name ?? $item->category_id ?? '' }}"
                                                            data-item-code="{{ $item->item_code ?? '' }}"
                                                            data-description="{{ $item->description ?? $item->item_description ?? '' }}"
                                                            data-discount="{{ $item->discount ?? 0 }}"
                                                            data-type="product">
                                                            {{ $item->name }} | Sale: {{ $item->sale_price ?? $item->price ?? 0 }} | Stock: {{ $item->opening_qty ?? 0 }} | Location: {{ $item->location ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td class="col-serial-no"><input type="text" class="item-serial-input" placeholder="Serial No."></td>
                                        <td class="col-description d-none"><input type="text" class="item-desc" placeholder="Description"></td>
                                        <td class="col-count d-none"><input type="number" class="item-count-input" value="0" min="0" step="1"></td>
                                        <td class="col-batch-no d-none"><input type="text" class="item-batch-no-input" placeholder="Batch No."></td>
                                        <td class="col-model-no"><input type="text" class="item-model-no-input" placeholder="Model No."></td>
                                        <td class="col-exp-date d-none"><input type="date" class="item-exp-date-input"></td>
                                        <td class="col-mfg-date d-none"><input type="date" class="item-mfg-date-input"></td>
                                        <td class="col-mrp d-none"><input type="number" class="item-mrp-input" value="0" min="0" step="0.01"></td>
                                        <td class="col-size d-none"><input type="text" class="item-size-input" placeholder="Size"></td>
                                        <td class="col-tafseel"><input type="text" class="item-tafseel" placeholder="Tafseel"></td>
                                        <td class="col-tadaat"><input type="number" class="item-qty tadaat-input" value="1"></td>
                                        <td class="col-free-qty"><input type="number" class="item-free-qty" value="0" min="0" step="1"></td>
                                        <td class="col-gross-w"><input type="number" class="gross-w-input" value="0" min="0" step="0.01"></td>
                                        <td class="col-net-w"><input type="number" class="net-w-input" value="0" min="0" step="0.01"></td>
                                        <td class="custom-size-td">
                                            <select class="item-unit"><option value="">Select Unit</option><option value="PCS">PCS (Pieces)</option><option value="BOX">BOX</option><option value="PACK">PACK</option><option value="SET">SET</option><option value="KG">KG (Kilogram)</option><option value="G">Gram</option><option value="M">Meter</option><option value="FT">Feet</option><option value="L">Liter</option><option value="ML">Milliliter</option></select>
                                        </td>
                                        <td class="col-rate"><input type="number" class="item-rate" value="0" min="0" step="0.01"></td>
                                        <td class="col-amount"><input type="number" class="item-amount" value="0" min="0" step="0.01" readonly></td>
                                        <td class="col-category d-none"><input type="text" class="item-category" placeholder="Category"></td>
                                        <td class="col-item-code d-none"><input type="text" class="item-code" placeholder="Item Code"></td>
                                        <td class="col-discount d-none"><input type="number" class="item-discount" value="0" min="0" step="0.01"></td>
                                        <td class="col-item-tax d-none"><input type="number" class="item-tax-amount" value="0" min="0" step="0.01"></td>
                                        @for($i = 1; $i <= 6; $i++)
                                            <td class="custom-item-field col-custom-field-{{ $i }} d-none">
                                                <input type="text" class="item-custom-field-input item-custom-field-{{ $i }}-input" placeholder="Custom Field {{ $i }}">
                                            </td>
                                        @endfor
                                        <td class="add-col"></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="item-totals-row">
                                        <td class="tfoot-add-row-cell">
                                            <span class="column-total-label">#</span>
                                        </td>
                                        <td class="col-barcode-scan d-none"></td>
                                        <td class="tfoot-add-row-cell">
                                            <button type="button" class="btn-add-row add-row-btn">ADD ROW</button>
                                        </td>
                                        <td class="col-serial-no"></td>
                                        <td class="col-description d-none"></td>
                                        <td class="col-count d-none"></td>
                                        <td class="col-batch-no d-none"></td>
                                        <td class="col-model-no"></td>
                                        <td class="col-exp-date d-none"></td>
                                        <td class="col-mfg-date d-none"></td>
                                        <td class="col-mrp d-none"></td>
                                        <td class="col-size d-none"></td>
                                        <td class="col-tafseel"></td>
                                        <td class="col-tadaat">
                                            <span class="column-total-label">Total Tadaat</span>
                                            <span class="column-total-value total-qty">0</span>
                                        </td>
                                        <td class="col-free-qty">
                                            <span class="column-total-label">Free Qty</span>
                                            <span class="column-total-value total-free-qty">0</span>
                                        </td>
                                        <td class="col-gross-w">
                                            <span class="column-total-label">Total Gross W</span>
                                            <span class="column-total-value total-gross-w">0.00</span>
                                        </td>
                                        <td class="col-net-w">
                                            <span class="column-total-label">Total Net W</span>
                                            <span class="column-total-value total-net-w">0.00</span>
                                        </td>
                                        <td class="custom-size-td"></td>
                                        <td class="col-rate"></td>
                                        <td class="col-amount">
                                            <span class="column-total-label">Total</span>
                                            <span class="column-total-value total-base-amount">0.00</span>
                                        </td>
                                        <td class="col-category d-none"></td>
                                        <td class="col-item-code d-none"></td>
                                        <td class="col-discount d-none"></td>
                                        <td class="col-item-tax d-none"></td>
                                        @for($i = 1; $i <= 6; $i++)
                                            <td class="custom-item-field col-custom-field-{{ $i }} d-none"></td>
                                        @endfor
                                        <td class="add-col"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                        </div>

                        <div class="bottom-section">
                            <div class="bottom-left">
                                <div class="payment-section">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control default-payment-type">
                                            <option value="" selected disabled>Select Payment Type</option>
                                            <option value="cash">Cash</option>
                                            <option value="cheques">Cheques</option>
                                            @foreach($bankAccounts as $bank)
                                                <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                            @endforeach
                                            <option value="add_new_bank">+ Add Bank Account</option>
                                        </select>
                                        <input type="number" class="input-control default-payment-amount d-none" placeholder="Amount" min="0" step="0.01">
                                        <input type="text" class="input-control default-payment-reference d-none" placeholder="Reference">
                                    </div>

                                    <div class="payment-entries"></div>

                                    <div class="payment-total d-flex justify-content-between align-items-center mt-2">
                                        <span class="text-muted">Total payment:</span>
                                        <span class="fw-bold payment-total-amount">0</span>
                                    </div>

                                    <a href="#" class="link-text add-payment-entry">+ Add Payment type</a>
                                    <div class="transportation-details-live-section d-none mt-3"></div>
                                </div>

                                <template id="payment-entry-template">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control payment-type-entry">
                                            <option value="" selected disabled>Select Bank Account</option>
                                            <option value="cash">Cash</option>
                                            <option value="cheques">Cheques</option>
                                            @foreach($bankAccounts as $bank)
                                                <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                            @endforeach
                                            <option value="add_new_bank">+ Add Bank Account</option>
                                        </select>
                                        <input type="number" class="input-control payment-amount" placeholder="Amount" min="0" step="0.01">
                                        <input type="text" class="input-control payment-reference" placeholder="Reference">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-payment-entry" title="Remove">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </template>

                                <div class="description-action-group">
                                    <button type="button" class="btn-action-light w-50 add-description">
                                        <i class="fa-solid fa-align-left"></i>
                                        ADD DESCRIPTION
                                    </button>
                                    <div class="description-pane d-none mt-2">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control description-input" rows="3" placeholder="Enter a remark or description" style="max-width: 400px;"></textarea>
                                    </div>
                                </div>

                                <button type="button" class="btn-action-light w-50 add-image">
                                    <i class="fa-solid fa-camera"></i>
                                    ADD IMAGE
                                </button>
                                <button type="button" class="btn-action-light w-50 add-document">
                                    <i class="fa-solid fa-file-lines"></i>
                                    ADD DOCUMENT
                                </button>

                                <div class="image-upload-section mt-2">
                                    <div class="image-preview d-none">
                                        <img class="image-preview-img" src="" alt="Selected Image" />
                                        <div class="image-preview-actions mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary replace-image">Replace</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-image">Remove</button>
                                        </div>
                                    </div>
                                    <div class="image-placeholder text-center p-3 border border-dashed rounded" style="cursor:pointer;">
                                        <div class="text-muted">Click to select an image</div>
                                        <div class="small text-muted">(PNG/JPG, up to 5MB)</div>
                                    </div>
                                    <div class="selected-document-name text-muted mt-2"></div>
                                </div>

                                <input type="file" class="d-none image-input" accept="image/*" />
                                <input type="file" class="d-none document-input" accept=".pdf,.doc,.docx" />
                            </div>

                            <div class="bottom-right">
                                <div class="calc-row transaction-discount-row">
                                    <div class="calc-label">Discount</div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input discount-pct" placeholder="%">
                                        <span>-</span>
                                        <input type="number" class="mini-input discount-rs" placeholder="Rs">
                                    </div>
                                </div>

                                <div class="calc-row transaction-tax-row">
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

                                <div class="calc-row transaction-round-off-row">
                                    <div class="checkbox-group">
                                        <input type="checkbox" class="custom-checkbox round-off-check" checked>
                                        <label class="link-text">Round Off</label>
                                    </div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input round-off-val" value="0" readonly>
                                    </div>
                                </div>

                                <div class="additional-charge-live-section d-none"></div>

                                <div class="final-total-group">
                                    <div class="calc-row" style="margin-bottom: 5px;">
                                        <div class="calc-label" style="font-weight: 700;">Total</div>
                                    </div>
                                    <input type="text" class="total-input-large grand-total" value="0" readonly>
                                </div>

                                <div class="calc-row">
                                    <div class="calc-label">Received</div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input advance-amount" value="0" min="0" step="0.01">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="sticky-actions">
                        <button type="button" class="purchase-return-link-payment-btn">
                            LINK PAYMENT <span class="lp-question">?</span>
                        </button>
                        <input type="hidden" class="linked-rows-json" value="[]">
                        <div class="purchase-return-link-spacer"></div>
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

    <div class="modal fade" id="closeConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Close Tab?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to close this tab? Your purchase return will not be saved.</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-close-btn" class="btn btn-danger">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade purchase-return-link-payment-modal" id="purchaseReturnLinkPaymentModal" tabindex="-1" aria-labelledby="purchaseReturnLinkPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseReturnLinkPaymentModalLabel">Link Payment to Txns</h5>
                    <button type="button" class="btn-close" aria-label="Close" id="purchaseReturnLinkPaymentCloseBtn"></button>
                </div>
                <div class="modal-body">
                    <div class="link-payment-header">
                        <div class="link-payment-summary">
                            <div>
                                <span class="link-payment-label">Party</span>
                                <div class="link-payment-value" id="purchaseReturnLinkPartyName">-</div>
                            </div>
                            <div>
                                <span class="link-payment-label">Paid Amount</span>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="purchaseReturnLinkPaidInput" min="0" step="0.01">
                                    <span class="input-group-text"><i class="fa-solid fa-pen"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="link-payment-tools">
                            <button type="button" class="btn btn-info text-white link-payment-mini-btn" id="purchaseReturnLinkAutoBtn">AUTO LINK</button>
                            <button type="button" class="btn btn-light" id="purchaseReturnLinkResetBtn" title="Reset">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                        <select class="form-select" id="purchaseReturnLinkTypeFilter" style="max-width:280px;">
                            <option value="all">All transactions</option>
                        </select>
                        <input type="text" class="form-control" id="purchaseReturnLinkSearch" placeholder="Search transaction" style="max-width:290px;">
                    </div>

                    <div class="link-payment-grid-wrap">
                        <table class="table mb-0 link-payment-grid">
                            <thead>
                                <tr>
                                    <th style="width:54px;"></th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Ref/Inv No.</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Balance</th>
                                    <th style="width:180px;">Linked Amount</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseReturnLinkRows">
                                <tr>
                                    <td colspan="7" class="link-payment-empty">Select a party to load transactions.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div class="fw-semibold">
                        Unused Amount :
                        <span id="purchaseReturnLinkUnusedAmount" class="link-payment-total">0</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" id="purchaseReturnLinkCancelBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="purchaseReturnLinkDoneBtn">Done</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPartyModal" tabindex="-1" aria-labelledby="addPartyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPartyModalLabel"><i class="fa-solid fa-user-plus me-2"></i>Add Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPartyForm">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Party Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter party name" id="partyNameInput" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" name="phone" class="form-control" placeholder="Enter phone number" id="partyPhoneInput">
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="partyModalTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="party-address-tab" data-bs-toggle="tab" data-bs-target="#partyAddressPane" type="button" role="tab" aria-controls="partyAddressPane" aria-selected="true">
                                    <i class="fa-solid fa-location-dot me-1"></i> Address
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="party-credit-tab" data-bs-toggle="tab" data-bs-target="#partyCreditPane" type="button" role="tab" aria-controls="partyCreditPane" aria-selected="false">
                                    <i class="fa-solid fa-credit-card me-1"></i> Credit & Balance
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="party-additional-tab" data-bs-toggle="tab" data-bs-target="#partyAdditionalPane" type="button" role="tab" aria-controls="partyAdditionalPane" aria-selected="false">
                                    <i class="fa-solid fa-sliders me-1"></i> Additional Fields
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3" id="partyModalTabContent">
                            <div class="tab-pane fade show active" id="partyAddressPane" role="tabpanel" aria-labelledby="party-address-tab">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email ID</label>
                                        <input type="email" name="email" class="form-control" placeholder="example@email.com">
                                    </div>
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6">
                                        <label class="form-label">Billing Address</label>
                                        <textarea class="form-control" name="billing_address" rows="3" placeholder="Enter billing address"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Shipping Address</label>
                                        <textarea class="form-control" name="shipping_address" rows="3" placeholder="Enter shipping address"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="partyCreditPane" role="tabpanel" aria-labelledby="party-credit-tab">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Opening Balance <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rs</span>
                                            <input type="number" name="opening_balance" class="form-control" placeholder="0.00" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">As Of Date</label>
                                        <input type="date" name="as_of_date" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label d-block">Credit Limit</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" name="credit_limit_enabled" type="checkbox" id="creditLimitSwitch">
                                            <label class="form-check-label" for="creditLimitSwitch">Enable</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="form-label d-block">Transaction Type</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="toReceive" value="receive">
                                        <label class="form-check-label" for="toReceive">To Receive</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="toPay" value="pay">
                                        <label class="form-check-label" for="toPay">To Pay</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-4">
                                    <label class="form-label fw-600">Party Type</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="party_type" id="customerParty" value="customer" checked>
                                        <label class="form-check-label" for="customerParty">Customer Party</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="party_type" id="supplierParty" value="supplier">
                                        <label class="form-check-label" for="supplierParty">Supplier Party</label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="partyAdditionalPane" role="tabpanel" aria-labelledby="party-additional-tab">
                                <p class="text-muted mb-3" style="font-size:13px;">Add custom fields to track additional information.</p>
                                <div class="row g-3">
                                    @for($i=1; $i<=4; $i++)
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="customField{{$i}}Check">
                                                <label class="form-check-label" for="customField{{$i}}Check">Custom Field {{$i}}</label>
                                            </div>
                                            <input type="text" name="custom_fields[]" class="form-control form-control-sm" placeholder="Field name">
                                        </div>
                                    @endfor
                                    <input type="hidden" id="transactionTypeValue" name="transaction_type">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary" id="btnSaveNewParty">
                                <i class="fa-solid fa-plus me-1"></i> Save & New
                            </button>
                            <button type="button" class="btn btn-primary" id="btnSaveParty">
                                <i class="fa-solid fa-check me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="purchaseReturnItemColumnModal" tabindex="-1" aria-labelledby="purchaseReturnItemColumnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="purchaseReturnItemColumnModalLabel">Add fields to items</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input purchase-return-column-check" data-target-check=".check-category" type="checkbox" id="purchaseReturnCategoryCheck">
                        <label class="form-check-label" for="purchaseReturnCategoryCheck">Item Category</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input purchase-return-column-check" data-target-check=".check-item-code" type="checkbox" id="purchaseReturnItemCodeCheck">
                        <label class="form-check-label" for="purchaseReturnItemCodeCheck">Item Code</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input purchase-return-column-check" data-target-check=".check-description" type="checkbox" id="purchaseReturnDescriptionCheck">
                        <label class="form-check-label" for="purchaseReturnDescriptionCheck">Description</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input purchase-return-column-check" data-target-check=".check-discount" type="checkbox" id="purchaseReturnDiscountCheck">
                        <label class="form-check-label" for="purchaseReturnDiscountCheck">Discount</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="purchaseReturnSettingsSidebar" aria-labelledby="purchaseReturnSettingsSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="purchaseReturnSettingsSidebarLabel">Purchase Return Settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group mb-3">
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#purchaseReturnItemColumnModal">
                    <div>
                        <div class="fw-semibold">Add fields to invoice</div>
                        <div class="text-muted small">Select columns to show</div>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <a href="{{ route('settings.transactions') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Transaction Settings</div>
                        <div class="text-muted small">Count, totals and transaction options</div>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <a href="{{ route('settings.items') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Item Settings</div>
                        <div class="text-muted small">Category, description and item options</div>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <a href="{{ route('settings.print-layout') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">Print Settings</div>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>

            <a href="{{ route('settings.transactions') }}" class="btn btn-link text-decoration-none p-0">
                <i class="fa-solid fa-gear me-1"></i> More Settings
            </a>
        </div>
    </div>

    <div class="modal fade" id="scanSerialModal" tabindex="-1" aria-labelledby="scanSerialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="scanSerialModalLabel">Scan code/serial</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Enter code/serial:</label>
                        <small class="text-muted scan-serial-count">0 Entered</small>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control" id="scanSerialInput" placeholder="Enter/scan">
                        <button class="btn btn-primary" type="button" id="confirmScanSerialBtn"><i class="fa-solid fa-check"></i></button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveScanSerialBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    @include('components.modals.item-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @php
        $purchaseReturnSaleFormSettings = $saleFormSettings ?? (json_decode(\App\Models\AppSetting::getValue('sale_form_settings', '{}'), true) ?: []);
        $purchaseReturnItemFormSettings = $itemFormSettings ?? (json_decode(\App\Models\AppSetting::getValue('item_form_settings', '{}'), true) ?: []);
        $purchaseReturnCountEnabled = \App\Models\AppSetting::getValue('transaction_items_count_enabled', '0') === '1';
        $purchaseReturnCountLabel = data_get($purchaseReturnSaleFormSettings, 'items_table.count_label', 'Count') ?: 'Count';
    @endphp

    <script>
        window.saleFormSettings = @json($purchaseReturnSaleFormSettings);
        window.itemFormSettings = @json($purchaseReturnItemFormSettings);
        window.bankAccountRoutes = {
            store: "{{ route('bank-accounts.store') }}"
        };
        window.transactionSettings = {
            countEnabled: @json($purchaseReturnCountEnabled),
            countLabel: @json($purchaseReturnCountLabel)
        };
    </script>

    @if(isset($purchaseReturn))
        <script>
            window.items = @json($items ?? []);
            window.parties = @json($parties ?? []);
            window.bankAccounts = @json($bankAccounts ?? []);
            window.itemRoutes = {
                store: "{{ url('dashboard/items') }}",
                categoryStore: "{{ url('dashboard/items/category') }}",
                unitsStore: "{{ url('dashboard/items/units') }}"
            };
            window.saleReturnStoreUrl = "{{ route('purchase-return.update', $purchaseReturn->id) }}";
            window.saleReturnMethod = 'PUT';
            window.editSaleReturnData = @json($purchaseReturn->load(['items', 'payments'])->toArray());
            window.docType = 'purchase_return';
        </script>
    @elseif(isset($duplicatePurchaseReturn))
        <script>
            window.items = @json($items ?? []);
            window.parties = @json($parties ?? []);
            window.bankAccounts = @json($bankAccounts ?? []);
            window.itemRoutes = {
                store: "{{ url('dashboard/items') }}",
                categoryStore: "{{ url('dashboard/items/category') }}",
                unitsStore: "{{ url('dashboard/items/units') }}"
            };
            window.saleReturnStoreUrl = "{{ route('purchase-return.store') }}";
            window.saleReturnMethod = 'POST';
            window.editSaleReturnData = @json(array_merge($duplicatePurchaseReturn->load(['items', 'payments'])->toArray(), ['bill_number' => $nextInvoiceNumber]));
            window.docType = 'purchase_return';
        </script>
    @else
        <script>
            window.items = @json($items ?? []);
            window.parties = @json($parties ?? []);
            window.bankAccounts = @json($bankAccounts ?? []);
            window.itemRoutes = {
                store: "{{ url('dashboard/items') }}",
                categoryStore: "{{ url('dashboard/items/category') }}",
                unitsStore: "{{ url('dashboard/items/units') }}"
            };
            window.saleReturnStoreUrl = "{{ route('purchase-return.store') }}";
            window.saleReturnMethod = 'POST';
            window.editSaleReturnData = null;
            window.docType = 'purchase_return';
        </script>
    @endif

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <div id="sale-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @include('components.bank-account-modal')

    <script src="{{ asset('js/salereturnform_script.js') }}"></script>
    <script src="{{ asset('js/scriptreturn.js') }}"></script>
    <script src="{{ asset('js/bank-account-modal.js') }}"></script>
    <script src="{{ asset('js/transaction-count-column.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let linkPaymentRows = [];
            let appliedLinkPaymentRows = [];
            let activeLinkPane = null;

            function activePurchaseReturnPane() {
                return document.querySelector('#content-area .tab-pane.active')
                    || document.querySelector('#content-area .tab-pane')
                    || document.getElementById('content-area');
            }

            function formatLinkPaymentCurrency(value) {
                return Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function getLinkPaymentModalInstance() {
                const modalElement = document.getElementById('purchaseReturnLinkPaymentModal');
                return modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
            }

            function getActivePaidAmount() {
                return parseFloat(activeLinkPane?.querySelector('.advance-amount, .received-amount')?.value || 0) || 0;
            }

            function getActivePartyId() {
                return activeLinkPane?.querySelector('.party-id')?.value || '';
            }

            function getActivePartyName() {
                const panelName = activeLinkPane?.querySelector('#partyInfoName')?.textContent?.trim();
                const inputValue = activeLinkPane?.querySelector('#partyDropdownBtn')?.value?.trim();
                return panelName || inputValue || '-';
            }

            function calculateLinkedTotal() {
                return linkPaymentRows.reduce((sum, row) => sum + (parseFloat(row.selected_amount || 0) || 0), 0);
            }

            function refreshLinkPaymentSummary() {
                const paidAmount = parseFloat(document.getElementById('purchaseReturnLinkPaidInput')?.value || getActivePaidAmount()) || 0;
                const unusedAmount = paidAmount - calculateLinkedTotal();
                const unusedEl = document.getElementById('purchaseReturnLinkUnusedAmount');
                if (!unusedEl) return;

                unusedEl.textContent = formatLinkPaymentCurrency(unusedAmount);
                unusedEl.classList.toggle('unused-amount-negative', unusedAmount < 0);
            }

            function persistAppliedLinkRows() {
                const cleaned = linkPaymentRows
                    .filter(row => (parseFloat(row.selected_amount || 0) || 0) > 0)
                    .map(row => ({
                        purchase_id: row.sale_id || row.transaction_id ? null : (row.purchase_id || null),
                        sale_id: row.sale_id || null,
                        transaction_id: row.transaction_id || null,
                        amount: Number(parseFloat(row.selected_amount).toFixed(2)),
                    }));

                appliedLinkPaymentRows = cleaned;
                const hiddenField = activeLinkPane?.querySelector('.linked-rows-json');
                if (hiddenField) hiddenField.value = JSON.stringify(cleaned);
            }

            function renderLinkPaymentRows() {
                const filter = (document.getElementById('purchaseReturnLinkTypeFilter')?.value || 'all').toLowerCase();
                const search = (document.getElementById('purchaseReturnLinkSearch')?.value || '').trim().toLowerCase();
                const tbody = document.getElementById('purchaseReturnLinkRows');
                if (!tbody) return;

                const filteredRows = linkPaymentRows.filter((row) => {
                    const matchesType = filter === 'all' || String(row.type || '').toLowerCase() === filter;
                    const haystack = `${row.date} ${row.type} ${row.ref_no}`.toLowerCase();
                    return matchesType && (!search || haystack.includes(search));
                });

                if (!filteredRows.length) {
                    tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">No transactions found.</td></tr>';
                    refreshLinkPaymentSummary();
                    return;
                }

                tbody.innerHTML = filteredRows.map((row) => {
                    const rowKey = row.id || row.purchase_id || row.sale_id || row.transaction_id;
                    const selectedAmount = parseFloat(row.selected_amount || 0) || 0;
                    const maxAmount = parseFloat(row.balance || 0) || 0;
                    const checked = selectedAmount > 0 ? 'checked' : '';
                    const disabled = selectedAmount > 0 ? '' : 'disabled';
                    return `
                        <tr data-link-row-key="${rowKey}">
                            <td><input type="checkbox" class="form-check-input purchase-return-link-check" data-link-row-key="${rowKey}" ${checked}></td>
                            <td>${row.date}</td>
                            <td>${row.type}</td>
                            <td>${row.ref_no}</td>
                            <td class="text-end">${formatLinkPaymentCurrency(row.total)}</td>
                            <td class="text-end">${formatLinkPaymentCurrency(row.balance)}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm purchase-return-link-amount" data-link-row-key="${rowKey}" min="0" max="${maxAmount}" step="0.01" value="${selectedAmount > 0 ? selectedAmount.toFixed(2) : ''}" ${disabled}>
                            </td>
                        </tr>
                    `;
                }).join('');

                refreshLinkPaymentSummary();
            }

            function syncLinkPaymentSelectionState(target) {
                const rowKey = target?.dataset?.linkRowKey;
                if (!rowKey) return;

                const row = linkPaymentRows.find((entry) => String(entry.id || entry.purchase_id || entry.sale_id || entry.transaction_id) === String(rowKey));
                if (!row) return;

                const checkbox = document.querySelector(`.purchase-return-link-check[data-link-row-key="${CSS.escape(String(rowKey))}"]`);
                const amountInput = document.querySelector(`.purchase-return-link-amount[data-link-row-key="${CSS.escape(String(rowKey))}"]`);
                const isChecked = checkbox ? checkbox.checked : false;
                const maxAmount = parseFloat(row.balance || 0) || 0;
                const amountValue = parseFloat(amountInput?.value || 0) || 0;

                if (!isChecked) {
                    row.selected_amount = 0;
                } else {
                    row.selected_amount = Number(Math.max(0, Math.min(amountValue || maxAmount, maxAmount)).toFixed(2));
                }

                if (amountInput) {
                    amountInput.disabled = !isChecked;
                    if (!isChecked) {
                        amountInput.value = '';
                    } else if (!amountInput.value) {
                        amountInput.value = Math.min(maxAmount, getActivePaidAmount()).toFixed(2);
                    }
                }

                persistAppliedLinkRows();
                refreshLinkPaymentSummary();
            }

            function loadLinkablePurchases(partyId) {
                const tbody = document.getElementById('purchaseReturnLinkRows');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="7" class="link-payment-empty">Loading transactions...</td></tr>';
                }

                fetch(`/dashboard/payment-out/linkable-purchases/${partyId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    }
                })
                    .then(async (response) => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Transactions load nahi ho sakin.');
                        }
                        return data;
                    })
                    .then((data) => {
                        document.getElementById('purchaseReturnLinkPartyName').textContent = data.party?.name || getActivePartyName();
                        linkPaymentRows = (data.rows || []).map((row) => {
                            const applied = appliedLinkPaymentRows.find((item) => (
                                (item.sale_id && String(item.sale_id) === String(row.sale_id))
                                || (item.transaction_id && String(item.transaction_id) === String(row.transaction_id))
                                || (item.purchase_id && String(item.purchase_id) === String(row.purchase_id))
                            ));
                            return {
                                ...row,
                                selected_amount: applied ? applied.amount : 0,
                            };
                        });

                        const typeFilter = document.getElementById('purchaseReturnLinkTypeFilter');
                        if (typeFilter) {
                            const types = [...new Set(linkPaymentRows.map(row => String(row.type || '').trim()).filter(Boolean))].sort();
                            typeFilter.innerHTML = '<option value="all">All transactions</option>' + types.map(type => `<option value="${type}">${type}</option>`).join('');
                            typeFilter.value = 'all';
                        }

                        renderLinkPaymentRows();
                    })
                    .catch((error) => {
                        if (tbody) {
                            tbody.innerHTML = `<tr><td colspan="7" class="link-payment-empty">${error.message || 'Transactions load nahi ho sakin.'}</td></tr>`;
                        }
                    });
            }

            function openLinkPaymentModal() {
                activeLinkPane = activePurchaseReturnPane();
                const partyId = getActivePartyId();
                if (!partyId) {
                    alert('Pehle party select karein.');
                    return;
                }

                try {
                    appliedLinkPaymentRows = JSON.parse(activeLinkPane?.querySelector('.linked-rows-json')?.value || '[]');
                } catch (e) {
                    appliedLinkPaymentRows = [];
                }

                document.getElementById('purchaseReturnLinkPartyName').textContent = getActivePartyName();
                document.getElementById('purchaseReturnLinkPaidInput').value = getActivePaidAmount().toFixed(2);
                document.getElementById('purchaseReturnLinkSearch').value = '';
                document.getElementById('purchaseReturnLinkTypeFilter').value = 'all';

                loadLinkablePurchases(partyId);
                getLinkPaymentModalInstance()?.show();
            }

            function closeLinkPaymentModal() {
                getLinkPaymentModalInstance()?.hide();
            }

            function autoAllocateLinkPayments() {
                let remaining = parseFloat(document.getElementById('purchaseReturnLinkPaidInput')?.value || getActivePaidAmount()) || 0;
                linkPaymentRows = linkPaymentRows.map((row) => {
                    const available = parseFloat(row.balance || 0) || 0;
                    const allocate = Math.max(0, Math.min(remaining, available));
                    remaining -= allocate;
                    return {
                        ...row,
                        selected_amount: Number(allocate.toFixed(2)),
                    };
                });
                persistAppliedLinkRows();
                renderLinkPaymentRows();
            }

            document.addEventListener('click', function (event) {
                if (event.target.closest('.purchase-return-link-payment-btn')) {
                    event.preventDefault();
                    openLinkPaymentModal();
                }
            });

            document.getElementById('purchaseReturnLinkAutoBtn')?.addEventListener('click', autoAllocateLinkPayments);
            document.getElementById('purchaseReturnLinkResetBtn')?.addEventListener('click', function () {
                linkPaymentRows = linkPaymentRows.map(row => ({ ...row, selected_amount: 0 }));
                persistAppliedLinkRows();
                renderLinkPaymentRows();
            });
            document.getElementById('purchaseReturnLinkDoneBtn')?.addEventListener('click', function () {
                persistAppliedLinkRows();
                closeLinkPaymentModal();
            });
            document.getElementById('purchaseReturnLinkCancelBtn')?.addEventListener('click', closeLinkPaymentModal);
            document.getElementById('purchaseReturnLinkPaymentCloseBtn')?.addEventListener('click', closeLinkPaymentModal);
            document.getElementById('purchaseReturnLinkSearch')?.addEventListener('input', renderLinkPaymentRows);
            document.getElementById('purchaseReturnLinkTypeFilter')?.addEventListener('change', renderLinkPaymentRows);
            document.getElementById('purchaseReturnLinkPaidInput')?.addEventListener('input', refreshLinkPaymentSummary);
            document.getElementById('purchaseReturnLinkRows')?.addEventListener('change', function (event) {
                const target = event.target;
                if (target.classList.contains('purchase-return-link-check') || target.classList.contains('purchase-return-link-amount')) {
                    syncLinkPaymentSelectionState(target);
                }
            });
            document.getElementById('purchaseReturnLinkRows')?.addEventListener('click', function (event) {
                const target = event.target;
                if (target.classList.contains('purchase-return-link-check')) {
                    syncLinkPaymentSelectionState(target);
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const settingsSidebar = document.getElementById('purchaseReturnSettingsSidebar');
            const itemColumnModal = document.getElementById('purchaseReturnItemColumnModal');

            function activePurchaseReturnPane() {
                return document.querySelector('#content-area .tab-pane.active')
                    || document.querySelector('#content-area .tab-pane')
                    || document.getElementById('content-area');
            }

            function syncPurchaseReturnColumnModal() {
                const pane = activePurchaseReturnPane();
                if (!pane) return;

                document.querySelectorAll('.purchase-return-column-check').forEach(function (modalCheck) {
                    const target = pane.querySelector(modalCheck.dataset.targetCheck);
                    modalCheck.checked = Boolean(target?.checked);
                });
            }

            settingsSidebar?.addEventListener('show.bs.offcanvas', syncPurchaseReturnColumnModal);
            itemColumnModal?.addEventListener('show.bs.modal', syncPurchaseReturnColumnModal);

            document.querySelectorAll('.purchase-return-column-check').forEach(function (modalCheck) {
                modalCheck.addEventListener('change', function () {
                    const pane = activePurchaseReturnPane();
                    const target = pane?.querySelector(this.dataset.targetCheck);
                    if (!target) return;

                    target.checked = this.checked;
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            initializePurchaseReturnEditableHeaders();
        });

        function initializePurchaseReturnEditableHeaders() {
            const storageKey = 'purchaseReturnItemTableHeaders';
            const tableHeaders = document.querySelectorAll('.purchase-return-table-scroll .item-table th');

            tableHeaders.forEach(function (th, index) {
                const text = th.textContent.trim();
                const headerKey = Array.from(th.classList)
                    .find(function (cls) {
                        return cls.startsWith('col-') || cls === 'row-num' || cls === 'custom-size-th';
                    }) || `header-${index}`;

                th.dataset.headerKey = headerKey;

                if (!text || th.classList.contains('add-col')) {
                    return;
                }

                th.classList.add('editable-header');
                th.addEventListener('click', function (event) {
                    if (event.target.closest('button')) return;

                    const currentText = th.textContent.trim();
                    const nextText = prompt('Edit column name:', currentText);

                    if (nextText !== null && nextText.trim() !== '') {
                        const trimmedText = nextText.trim();
                        th.textContent = trimmedText;

                        const savedHeaders = JSON.parse(localStorage.getItem(storageKey) || '{}');
                        savedHeaders[headerKey] = trimmedText;
                        localStorage.setItem(storageKey, JSON.stringify(savedHeaders));
                    }
                });
            });

            const savedHeaders = JSON.parse(localStorage.getItem(storageKey) || '{}');
            tableHeaders.forEach(function (th) {
                const headerKey = th.dataset.headerKey;
                if (headerKey && savedHeaders[headerKey]) {
                    th.textContent = savedHeaders[headerKey];
                }
            });
        }
    </script>
    <script>
        // Item Picker Functionality using Event Delegation
        document.addEventListener('DOMContentLoaded', function () {
            const contentArea = document.getElementById('content-area');
            const dropdownGap = 6;

            function positionItemPickerPanel(itemPicker) {
                const input = itemPicker?.querySelector('.item-picker-input');
                const panel = itemPicker?.querySelector('.item-picker-panel');

                if (!input || !panel || !panel.classList.contains('open')) {
                    return;
                }

                const rect = input.getBoundingClientRect();
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                const preferredWidth = Math.max(760, rect.width);
                const width = Math.min(preferredWidth, viewportWidth - 24);
                const left = Math.max(12, Math.min(rect.left, viewportWidth - width - 12));
                const list = panel.querySelector('.item-picker-list');
                const spaceBelow = viewportHeight - rect.bottom - dropdownGap - 12;
                const spaceAbove = rect.top - dropdownGap - 12;
                const panelHeight = Math.min(360, Math.max(220, Math.max(spaceBelow, spaceAbove)));
                const openAbove = spaceBelow < 220 && spaceAbove > spaceBelow;
                const top = openAbove
                    ? Math.max(12, rect.top - panelHeight - dropdownGap)
                    : Math.min(rect.bottom + dropdownGap, viewportHeight - panelHeight - 12);

                panel.style.width = `${width}px`;
                panel.style.left = `${left}px`;
                panel.style.top = `${top}px`;
                panel.style.maxHeight = `${panelHeight}px`;

                if (list) {
                    list.style.maxHeight = `${Math.max(120, panelHeight - 96)}px`;
                }
            }

            function openItemPicker(input) {
                const itemPicker = input.closest('.item-picker');
                const panel = itemPicker?.querySelector('.item-picker-panel');
                if (!itemPicker || !panel) return;

                document.querySelectorAll('.item-picker-panel.open').forEach(openPanel => {
                    if (openPanel !== panel) {
                        openPanel.classList.remove('open');
                    }
                });

                panel.classList.add('open');
                positionItemPickerPanel(itemPicker);
            }

            function closeItemPicker(panel) {
                if (!panel) return;
                panel.classList.remove('open');
                panel.style.removeProperty('width');
                panel.style.removeProperty('left');
                panel.style.removeProperty('top');
                panel.style.removeProperty('max-height');
            }

            // Handle item picker input focus
            contentArea.addEventListener('focus', function(e) {
                if (e.target.classList.contains('item-picker-input')) {
                    openItemPicker(e.target);
                }
            }, true);

            contentArea.addEventListener('click', function(e) {
                if (e.target.classList.contains('item-picker-input')) {
                    openItemPicker(e.target);
                }
            });

            // Handle item picker input blur
            contentArea.addEventListener('blur', function(e) {
                if (e.target.classList.contains('item-picker-input')) {
                    const itemPicker = e.target.closest('.item-picker');
                    const panel = itemPicker.querySelector('.item-picker-panel');
                    if (panel) {
                        setTimeout(() => {
                            closeItemPicker(panel);
                        }, 200);
                    }
                }
            }, true);

            // Handle item picker input typing for filtering
            contentArea.addEventListener('input', function(e) {
                if (e.target.classList.contains('item-picker-input')) {
                    const input = e.target;
                    const itemPicker = input.closest('.item-picker');
                    const panel = itemPicker.querySelector('.item-picker-panel');
                    const searchText = input.value.toLowerCase();
                    const options = panel.querySelectorAll('.item-picker-option');
                    let hasVisibleOptions = false;

                    openItemPicker(input);

                    options.forEach(option => {
                        const itemName = option.querySelector('.item-picker-name').textContent.toLowerCase();
                        if (itemName.includes(searchText)) {
                            option.style.display = '';
                            hasVisibleOptions = true;
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Show/hide empty message
                    const emptyMsg = panel.querySelector('.item-picker-empty');
                    if (emptyMsg) {
                        emptyMsg.style.display = hasVisibleOptions ? 'none' : '';
                    }
                }
            });

            // Handle item picker option clicks
            contentArea.addEventListener('click', function(e) {
                const option = e.target.closest('.item-picker-option');
                if (option) {
                    e.preventDefault();
                    e.stopPropagation();

                    const itemPicker = option.closest('.item-picker');
                    const input = itemPicker.querySelector('.item-picker-input');
                    const selectElement = itemPicker.querySelector('.item-name');
                    const panel = itemPicker.querySelector('.item-picker-panel');

                    const itemId = option.dataset.id;
                    const itemName = option.querySelector('.item-picker-name').textContent.trim();

                    // Update input
                    input.value = itemName;

                    // Update hidden select
                    selectElement.value = itemId;

                    // Trigger change event to update row data
                    const event = new Event('change', { bubbles: true });
                    selectElement.dispatchEvent(event);

                    // Hide panel
                    closeItemPicker(panel);
                }
            });

            // Close panels when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.item-picker')) {
                    document.querySelectorAll('.item-picker-panel.open').forEach(panel => {
                        closeItemPicker(panel);
                    });
                }
            });

            window.addEventListener('resize', function () {
                document.querySelectorAll('.item-picker-panel.open').forEach(panel => {
                    positionItemPickerPanel(panel.closest('.item-picker'));
                });
            });

            window.addEventListener('scroll', function () {
                document.querySelectorAll('.item-picker-panel.open').forEach(panel => {
                    positionItemPickerPanel(panel.closest('.item-picker'));
                });
            }, true);

            // Handle Add Item button click - using event delegation
            contentArea.addEventListener('click', function(e) {
                const addItemBtn = e.target.closest('.item-picker-add');
                if (addItemBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.activePurchaseReturnItemRow = addItemBtn.closest('tr.item-row');
                    document.querySelectorAll('.item-picker-panel.open').forEach(panel => {
                        closeItemPicker(panel);
                    });
                    const addItemModal = document.getElementById('addItemModal');
                    if (addItemModal) {
                        const modal = bootstrap.Modal.getOrCreateInstance(addItemModal);
                        modal.show();
                    }
                    return false;
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const addItemModalEl = document.getElementById('addItemModal');
            const addItemForm = document.getElementById('addItemForm');
            const unitModalEl = document.getElementById('selectItemUnitModal');
            const categoryModalEl = document.getElementById('addCategoryModal');
            const addUnitModalEl = document.getElementById('addUnitModal');

            addItemModalEl?.addEventListener('shown.bs.modal', function () {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops[backdrops.length - 1]?.classList.add('add-item-backdrop');
            });

            addItemModalEl?.addEventListener('hidden.bs.modal', function () {
                document.querySelectorAll('.modal-backdrop.add-item-backdrop').forEach(backdrop => {
                    backdrop.classList.remove('add-item-backdrop');
                });
            });

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function escapeSelector(value) {
                if (window.CSS && typeof CSS.escape === 'function') {
                    return CSS.escape(String(value));
                }

                return String(value).replace(/["\\]/g, '\\$&');
            }

            function itemOptionHtml(item) {
                const name = escapeHtml(item.name || '');
                const salePrice = item.sale_price ?? item.price ?? 0;
                const stock = item.opening_qty ?? 0;

                return `<option value="${item.id}"
                    data-price="${item.price ?? ''}"
                    data-purchase-price="${item.purchase_price ?? item.price ?? ''}"
                    data-sale-price="${item.sale_price ?? ''}"
                    data-stock="${stock}"
                    data-location="${escapeHtml(item.location || '')}"
                    data-label="${name}"
                    data-rich-label="${name} | Sale: ${salePrice} | Stock: ${stock} | Location: ${escapeHtml(item.location || '')}"
                    data-unit="${escapeHtml(item.unit || '')}"
                    data-weight="${item.bag_weight ?? 0}"
                    data-category="${escapeHtml(item.category_name || item.category?.name || item.category || item.category_id || '')}"
                    data-item-code="${escapeHtml(item.item_code || '')}"
                    data-description="${escapeHtml(item.description || item.item_description || '')}"
                    data-discount="${item.discount ?? 0}"
                    data-type="${escapeHtml(item.item_type || item.type || 'product')}">${name} | Sale: ${salePrice} | Stock: ${stock} | Location: ${escapeHtml(item.location || '')}</option>`;
            }

            function pickerRowHtml(item) {
                const stock = parseFloat(item.opening_qty ?? 0) || 0;
                return `<div class="item-picker-row item-picker-option" data-id="${item.id}" data-type="${escapeHtml(item.item_type || item.type || 'product')}">
                    <div class="item-picker-name">${escapeHtml(item.name || '')}${item.item_code ? ` <small>(${escapeHtml(item.item_code)})</small>` : ''}</div>
                    <div>${(parseFloat(item.purchase_price ?? 0) || 0).toFixed(2)}</div>
                    <div>${(parseFloat(item.sale_price ?? item.price ?? 0) || 0).toFixed(2)}</div>
                    <div class="item-picker-stock ${stock < 0 ? 'neg' : ''}">${stock}</div>
                </div>`;
            }

            function appendItemToPickers(item) {
                window.items = Array.isArray(window.items) ? window.items : [];
                window.items = window.items.filter(existing => String(existing.id) !== String(item.id));
                window.items.push(item);

                document.querySelectorAll('select.item-name').forEach(select => {
                    select.querySelector(`option[value="${escapeSelector(item.id)}"]`)?.remove();
                    select.insertAdjacentHTML('beforeend', itemOptionHtml(item));
                });

                document.querySelectorAll('.item-picker-list').forEach(list => {
                    list.querySelector('.item-picker-empty')?.remove();
                    list.querySelector(`.item-picker-option[data-id="${escapeSelector(item.id)}"]`)?.remove();
                    list.insertAdjacentHTML('beforeend', pickerRowHtml(item));
                });
            }

            function resetItemModal() {
                addItemForm?.reset();
                document.getElementById('newItemType').value = 'product';
                document.getElementById('newItemProductLabel').textContent = 'Product';
                document.getElementById('newItemNameLabel').textContent = 'Item Name *';
                document.getElementById('newItemUnitBtn').textContent = 'Select Unit';
                document.getElementById('newItemImageThumb').innerHTML = '<i class="fa-regular fa-image fa-2x text-secondary"></i>';
                document.getElementById('newItemImageLabel').textContent = 'Click to choose image';
                document.getElementById('newItemStockImagesList').innerHTML = '';
                document.querySelector('.wholesale-pricing')?.classList.add('d-none');
                document.getElementById('toggleWholesalePricing').textContent = '+ Add Wholesale Price';
            }

            function selectItemInActiveRow(item) {
                const row = window.activePurchaseReturnItemRow || document.querySelector('.item-row');
                if (!row) return;

                const select = row.querySelector('select.item-name');
                const input = row.querySelector('.item-picker-input');
                if (select) {
                    select.value = String(item.id);
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (input) {
                    input.value = item.name || '';
                }
            }

            document.getElementById('newItemTypeToggle')?.addEventListener('change', function () {
                const isService = this.checked;
                document.getElementById('newItemType').value = isService ? 'service' : 'product';
                document.getElementById('newItemProductLabel').textContent = isService ? 'Service' : 'Product';
                document.getElementById('newItemNameLabel').textContent = isService ? 'Service Name *' : 'Item Name *';
                document.getElementById('stock-tab').style.display = isService ? 'none' : '';
                document.getElementById('stock-tab-pane').style.display = isService ? 'none' : '';
            });

            document.getElementById('assignItemCodeBtn')?.addEventListener('click', function () {
                const seed = (document.getElementById('newItemName').value || 'ITEM')
                    .toUpperCase()
                    .replace(/[^A-Z0-9]+/g, '')
                    .substring(0, 6) || 'ITEM';
                document.getElementById('newItemCode').value = `${seed}-${Math.floor(Math.random() * 9000) + 1000}`;
            });

            document.getElementById('toggleWholesalePricing')?.addEventListener('click', function () {
                const section = document.querySelector('.wholesale-pricing');
                section?.classList.toggle('d-none');
                this.textContent = section?.classList.contains('d-none') ? '+ Add Wholesale Price' : '- Remove Wholesale Price';
            });

            document.querySelector('.open-item-image-picker')?.addEventListener('click', function () {
                document.getElementById('newItemImage')?.click();
            });

            document.getElementById('newItemImage')?.addEventListener('change', function () {
                const file = this.files?.[0];
                const thumb = document.getElementById('newItemImageThumb');
                const label = document.getElementById('newItemImageLabel');
                if (!file) return;
                thumb.innerHTML = `<img src="${URL.createObjectURL(file)}" style="width:100%;height:100%;object-fit:cover;">`;
                label.textContent = file.name;
            });

            document.querySelector('.open-item-stock-images-picker')?.addEventListener('click', function () {
                document.getElementById('newItemStockImages')?.click();
            });

            document.getElementById('newItemStockImages')?.addEventListener('change', function () {
                const html = Array.from(this.files || []).map(file => {
                    return `<div class="item-stock-image-card"><img src="${URL.createObjectURL(file)}" alt="${escapeHtml(file.name)}"><div class="name">${escapeHtml(file.name)}</div></div>`;
                }).join('');
                document.getElementById('newItemStockImagesList').innerHTML = html;
            });

            document.getElementById('newItemUnitBtn')?.addEventListener('click', function () {
                bootstrap.Modal.getOrCreateInstance(unitModalEl).show();
            });

            document.getElementById('saveSelectedUnitsBtn')?.addEventListener('click', function () {
                const baseUnit = (document.getElementById('newItemBaseUnitSelect').value || '').toUpperCase();
                const secondaryUnit = (document.getElementById('newItemSecondaryUnitSelect').value || '').toUpperCase();
                document.getElementById('newItemUnit').value = baseUnit;
                document.getElementById('newItemSecondaryUnit').value = secondaryUnit;
                document.getElementById('newItemUnitConversionRate').value = document.getElementById('newItemUnitConversionInput').value || 0;
                document.getElementById('newItemUnitBtn').textContent = secondaryUnit && secondaryUnit !== baseUnit ? `${baseUnit} / ${secondaryUnit}` : (baseUnit || 'Select Unit');
                bootstrap.Modal.getOrCreateInstance(unitModalEl).hide();
            });

            document.getElementById('newItemCategory')?.addEventListener('change', function () {
                if (this.value === '__add_new__') {
                    this.value = '';
                    bootstrap.Modal.getOrCreateInstance(categoryModalEl).show();
                }
            });

            document.querySelector('.open-add-unit-from-selector')?.addEventListener('click', function () {
                bootstrap.Modal.getOrCreateInstance(unitModalEl).hide();
                bootstrap.Modal.getOrCreateInstance(addUnitModalEl).show();
            });

            document.getElementById('saveQuickCategoryBtn')?.addEventListener('click', function () {
                const nameInput = document.getElementById('quickCategoryName');
                const name = nameInput.value.trim();
                if (!name) {
                    alert('Enter category name');
                    return;
                }

                fetch(window.itemRoutes.categoryStore, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name })
                })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Unable to save category.');
                        return data.category || data.data || data;
                    })
                    .then(category => {
                        const select = document.getElementById('newItemCategory');
                        const option = new Option(category.name || name, category.id || '');
                        select.add(option, select.querySelector('option[value="__add_new__"]'));
                        select.value = option.value;
                        nameInput.value = '';
                        bootstrap.Modal.getOrCreateInstance(categoryModalEl).hide();
                    })
                    .catch(error => alert(error.message || 'Unable to save category.'));
            });

            document.getElementById('saveQuickUnitBtn')?.addEventListener('click', function () {
                const nameInput = document.getElementById('quickUnitName');
                const shortInput = document.getElementById('quickUnitShortName');
                const name = nameInput.value.trim();
                const shortName = shortInput.value.trim().toUpperCase();
                if (!name || !shortName) {
                    alert('Enter unit name and short name');
                    return;
                }

                fetch(window.itemRoutes.unitsStore, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, short_name: shortName })
                })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Unable to save unit.');
                        return data.unit || data.data || { name, short_name: shortName };
                    })
                    .then(unit => {
                        const value = (unit.short_name || shortName).toUpperCase();
                        const label = unit.name && unit.name.toUpperCase() !== value ? `${unit.name} (${value})` : value;
                        ['newItemBaseUnitSelect', 'newItemSecondaryUnitSelect'].forEach(id => {
                            const select = document.getElementById(id);
                            if (select && !Array.from(select.options).some(option => option.value === value)) {
                                select.add(new Option(label, value));
                            }
                        });
                        document.getElementById('newItemBaseUnitSelect').value = value;
                        document.getElementById('newItemUnit').value = value;
                        document.getElementById('newItemUnitBtn').textContent = value;
                        nameInput.value = '';
                        shortInput.value = '';
                        bootstrap.Modal.getOrCreateInstance(addUnitModalEl).hide();
                    })
                    .catch(error => alert(error.message || 'Unable to save unit.'));
            });

            document.getElementById('saveNewItemBtn')?.addEventListener('click', function () {
                const name = document.getElementById('newItemName').value.trim();
                if (!name) {
                    alert('Please enter an item name');
                    return;
                }

                const formData = new FormData();
                formData.append('name', name);
                formData.append('category_id', document.getElementById('newItemCategory').value || '');
                formData.append('unit', document.getElementById('newItemUnit').value || '');
                formData.append('secondary_unit', document.getElementById('newItemSecondaryUnit').value || '');
                formData.append('unit_conversion_rate', document.getElementById('newItemUnitConversionRate').value || 0);
                formData.append('item_type', document.getElementById('newItemType').value || 'product');
                formData.append('type', document.getElementById('newItemType').value || 'product');
                formData.append('sale_price', document.getElementById('newItemSalePrice').value || 0);
                formData.append('purchase_price', document.getElementById('newItemPurchasePrice').value || 0);
                formData.append('wholesale_price', document.getElementById('newItemWholesalePrice').value || 0);
                formData.append('wholesale_min_qty', document.getElementById('newItemWholesaleMinQty').value || 0);
                formData.append('item_code', document.getElementById('newItemCode').value || '');
                formData.append('opening_qty', document.getElementById('newItemStock').value || 0);
                formData.append('at_price', document.getElementById('newItemAtPrice').value || 0);
                formData.append('as_of_date', document.getElementById('newItemAsOfDate').value || '');
                formData.append('bag_weight', document.getElementById('newItemBagWeight').value || 0);
                formData.append('min_stock', document.getElementById('newItemMinStock').value || 0);
                formData.append('location', document.getElementById('newItemLocation').value || '');
                formData.append('description', document.getElementById('newItemDescription').value || '');

                const imageInput = document.getElementById('newItemImage');
                if (imageInput?.files?.length) {
                    formData.append('item_image', imageInput.files[0]);
                }

                Array.from(document.getElementById('newItemStockImages')?.files || []).forEach(file => {
                    formData.append('item_images[]', file);
                });

                fetch(window.itemRoutes.store, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Unable to save item.');
                        return data;
                    })
                    .then(data => {
                        const item = data.item || data.data;
                        if (!item) throw new Error('Item was not returned from server.');
                        appendItemToPickers(item);
                        selectItemInActiveRow(item);
                        bootstrap.Modal.getOrCreateInstance(addItemModalEl).hide();
                        resetItemModal();
                    })
                    .catch(error => alert(error.message || 'Unable to save item.'));
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addPartyModalEl = document.getElementById('addPartyModal');
            const addPartyModal = new bootstrap.Modal(addPartyModalEl);
            const addPartyForm = document.getElementById('addPartyForm');
            const transactionTypeValue = document.getElementById('transactionTypeValue');
            const toReceive = document.getElementById('toReceive');
            const toPay = document.getElementById('toPay');

            function formatDateForDisplay(value) {
                if (!value) return '';
                const parts = value.split('-');
                if (parts.length !== 3) return value;
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            function attachDatePickerSync(hiddenSelector, textSelector) {
                document.querySelectorAll('.tab-pane').forEach(function (pane) {
                    const hiddenInput = pane.querySelector(hiddenSelector);
                    const textInput = pane.querySelector(textSelector);
                    const icon = textInput ? textInput.closest('.purchase-doc-row')?.querySelector('.purchase-doc-icon') : null;

                    if (!hiddenInput || !textInput || textInput.dataset.bound === '1') {
                        return;
                    }

                    textInput.dataset.bound = '1';
                    textInput.value = formatDateForDisplay(hiddenInput.value);

                    const openPicker = function () {
                        hiddenInput.showPicker ? hiddenInput.showPicker() : hiddenInput.click();
                    };

                    textInput.addEventListener('focus', openPicker);
                    textInput.addEventListener('click', openPicker);

                    if (icon) {
                        icon.addEventListener('click', openPicker);
                    }

                    hiddenInput.addEventListener('change', function () {
                        textInput.value = formatDateForDisplay(hiddenInput.value);
                    });
                });
            }

            setTimeout(function () {
                attachDatePickerSync('.order-date', '.order-date-text');
                attachDatePickerSync('.due-date', '.due-date-text');
            }, 0);

            [toReceive, toPay].forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        [toReceive, toPay].forEach(function (cb) {
                            if (cb !== checkbox) cb.checked = false;
                        });
                        transactionTypeValue.value = checkbox.value;
                    } else {
                        transactionTypeValue.value = '';
                    }
                });
            });

            function resetPartyTabs() {
                const addressTab = document.getElementById('party-address-tab');
                if (addressTab && window.bootstrap && bootstrap.Tab) {
                    bootstrap.Tab.getOrCreateInstance(addressTab).show();
                }

                document.querySelectorAll('#partyModalTabs .nav-link').forEach(function (tab) {
                    const isAddress = tab.getAttribute('data-bs-target') === '#partyAddressPane';
                    tab.classList.toggle('active', isAddress);
                    tab.setAttribute('aria-selected', isAddress ? 'true' : 'false');
                });

                document.querySelectorAll('#partyModalTabContent .tab-pane').forEach(function (pane) {
                    const isAddress = pane.id === 'partyAddressPane';
                    pane.classList.toggle('show', isAddress);
                    pane.classList.toggle('active', isAddress);
                });
            }

            function resetPartyModal() {
                addPartyForm.reset();
                transactionTypeValue.value = '';
                resetPartyTabs();
            }

            function appendPartyToDropdowns(party) {
                document.querySelectorAll('#partyDropdownMenu').forEach(function (menu) {
                    const divider = menu.querySelector('.dropdown-divider');
                    const li = document.createElement('li');
                    const amount = Number(party.current_balance || party.opening_balance || 0).toFixed(2);
                    const type = party.transaction_type || '';
                    const colorClass = type === 'pay' ? 'text-danger' : (type === 'receive' ? 'text-success' : '');
                    const arrowIcon = type === 'pay'
                        ? '<i class="fa-solid fa-arrow-up me-1"></i>'
                        : (type === 'receive' ? '<i class="fa-solid fa-arrow-down me-1"></i>' : '');

                    li.innerHTML = `
                        <a class="dropdown-item d-flex justify-content-between party-option" href="#"
                           data-id="${party.id}"
                           data-phone="${party.phone || ''}"
                           data-billing="${party.billing_address || ''}"
                           data-opening="${party.current_balance || party.opening_balance || 0}"
                           data-type="${type}">
                            <span>${party.name}</span>
                            <span class="${colorClass}">${arrowIcon}Rs ${amount}</span>
                        </a>
                    `;

                    if (divider && divider.parentElement) {
                        menu.insertBefore(li, divider.parentElement);
                    } else {
                        menu.appendChild(li);
                    }
                });
            }

            function selectCreatedParty(party) {
                const activePane = document.querySelector('.tab-pane.active') || document;
                const button = activePane.querySelector('#partyDropdownBtn');
                const hiddenInput = activePane.querySelector('.party-id');
                const balance = activePane.querySelector('#partyBalanceDisplay');

                if (button) button.textContent = party.name || 'Select Party';
                if (hiddenInput) hiddenInput.value = party.id || '';
                if (balance) {
                    const amount = Number(party.current_balance || party.opening_balance || 0).toFixed(2);
                    if (party.transaction_type === 'pay') {
                        balance.innerHTML = `<i class="fa-solid fa-arrow-up text-danger me-1"></i>Rs ${amount}`;
                    } else if (party.transaction_type === 'receive') {
                        balance.innerHTML = `<i class="fa-solid fa-arrow-down text-success me-1"></i>Rs ${amount}`;
                    } else {
                        balance.textContent = `Rs ${amount}`;
                    }
                }
            }

            function saveParty(closeAfterSave) {
                const formData = new FormData(addPartyForm);
                formData.set('transaction_type', transactionTypeValue.value || '');
                formData.set('credit_limit_enabled', document.getElementById('creditLimitSwitch').checked ? 1 : 0);

                fetch("{{ route('parties.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(async function (response) {
                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Unable to save party.');
                        }
                        return data;
                    })
                    .then(function (data) {
                        const party = data.party;
                        window.parties = Array.isArray(window.parties) ? window.parties : [];
                        window.parties.unshift(party);
                        appendPartyToDropdowns(party);
                        selectCreatedParty(party);

                        if (closeAfterSave) {
                            addPartyModal.hide();
                            resetPartyModal();
                        } else {
                            resetPartyModal();
                        }
                    })
                    .catch(function (error) {
                        alert(error.message || 'Unable to save party.');
                    });
            }

            document.addEventListener('click', function (event) {
                const addPartyBtn = event.target.closest('#addNewPartyBtn');
                if (addPartyBtn) {
                    event.preventDefault();
                    resetPartyModal();
                    addPartyModal.show();
                }
            });

            addPartyModalEl.addEventListener('shown.bs.modal', resetPartyTabs);

            document.getElementById('btnSaveParty').addEventListener('click', function () {
                saveParty(true);
            });

            document.getElementById('btnSaveNewParty').addEventListener('click', function () {
                saveParty(false);
            });
        });
    </script>
</body>

</html>
