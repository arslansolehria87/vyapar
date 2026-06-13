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
            max-width: 100%;
            width: 100%;
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
            width: 32%;
            padding-top: 8px;
        }

        .purchase-doc-grid {
            display: grid;
            gap: 16px;
        }

        .purchase-doc-row {
            display: grid;
            grid-template-columns: 110px 1fr 28px;
            align-items: center;
            gap: 12px;
        }

        .purchase-doc-label {
            text-align: right;
            color: #8b93a7;
            font-size: 13px;
            font-weight: 500;
        }

        .purchase-doc-input {
            border: 0;
            border-bottom: 1px solid #d9deea;
            padding: 8px 0 6px;
            background: transparent;
            color: #1f2937;
            font-size: 13px;
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
            font-size: 18px;
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
            line-height: 1.2;
        }

        #partyInfoPanel small {
            line-height: 1.6;
        }

        #partyInfoPanel .btn {
            transition: all 0.2s ease;
        }

        #partyInfoPanel .btn:hover {
            background-color: #dcfce7 !important;
            border-color: #15803d !important;
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
                    <i class="fa-solid fa-gear" title="Settings"></i>
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
            <div id="partyInfoPanel" class="border rounded-3" style="background-color: #f0fdf4; border: 2px solid #22c55e; padding: 8px; max-width: 220px; flex-shrink: 0;">
                <!-- Header with Name and Close Button -->
                <div class="d-flex justify-content-between align-items-flex-start" style="margin-bottom: 4px;">
                    <div style="flex: 1;">
                        <h5 class="mb-0 fw-700" id="partyInfoName" style="color: #15803d; font-size: 13px; letter-spacing: -0.3px;">Party Name</h5>
                        <div class="d-flex align-items-center gap-1">
                            <i class="fa-solid fa-location-dot" style="color: #dc2626; font-size: 11px;"></i>
                            <span id="partyInfoDescription" style="font-size: 11px; color: #6b7280;">Party details</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm" id="closePartyPanel" title="Close" style="background: white; border: 1px solid #dcfce7; color: #15803d; width: 20px; height: 20px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-solid fa-xmark" style="font-size: 10px;"></i>
                    </button>
                </div>

                <!-- Balance Display -->
                <div style="border-top: 1px solid #dcfce7; padding-top: 6px;">
                    <div class="d-flex align-items-center gap-1">
                        <span id="partyInfoBalance" class="fw-700" style="color: #15803d; font-size: 13px;">₹0.00</span>
                    </div>
                    <small style="color: #666; display: block; margin-top: 1px; font-size: 10px;">BALANCE</small>
                </div>
            </div>

            <!-- Billing Name & Phone Inputs (Side by Side) -->
            <div style="display: flex; gap: 12px; flex: 1.5; align-items: center; margin-left: 100px;">
                <!-- Billing Name Input -->
                <div style="flex: 1.5; min-width: 200px;">
                    <input type="text" class="form-control billing-name-input" style="font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; width: 100%;" placeholder="BILLING NAME (OPTIONAL)">
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

                            </div>

                            <div class="purchase-right-panel">
                                <div class="purchase-doc-grid">
                                    <div class="purchase-doc-row">
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
                                    <div class="purchase-doc-row">
                                        <span class="purchase-doc-label">Date</span>
                                        <input type="text" class="purchase-doc-input due-date-text" placeholder="DD/MM/YYYY" readonly>
                                        <span class="purchase-doc-icon"><i class="bi bi-calendar-date"></i></span>
                                        <input type="date" class="due-date purchase-hidden-date">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success d-none sale-success-msg"></div>

                        <div class="table-container">
                            <table class="item-table">
                                <thead>
                                    <tr>
                                        <th class="row-num">#</th>
                                        <th style="width: 30%;">ITEM</th>
                                        <th class="col-category d-none">CATEGORY</th>
                                        <th class="col-item-code d-none">ITEM CODE</th>
                                        <th class="col-description d-none">DESCRIPTION</th>
                                        <th class="col-discount d-none">DISCOUNT</th>
                                        <th>QTY</th>
                                        <th class="custom-size-th">UNIT</th>
                                        <th>PRICE/UNIT</th>
                                        <th>AMOUNT</th>
                                        <th class="add-col" style="position: relative;">
                                            <button type="button" class="btn-add-circle table-settings-btn">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                            <div class="settings-box">
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
                                        <td>
                                            <select class="form-select item-name">
                                                <option value="" selected disabled>Select Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" data-price="{{ $item->price }}" data-sale-price="{{ $item->sale_price }}" data-stock="{{ $item->opening_qty }}" data-location="{{ $item->location }}" data-label="{{ $item->name }}" data-rich-label="{{ $item->name }} | Sale: {{ $item->sale_price ?? $item->price ?? 0 }} | Stock: {{ $item->opening_qty ?? 0 }} | Location: {{ $item->location ?? '' }}" data-unit="{{ $item->unit }}">{{ $item->name }} | Sale: {{ $item->sale_price ?? $item->price ?? 0 }} | Stock: {{ $item->opening_qty ?? 0 }} | Location: {{ $item->location ?? '' }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="col-category d-none"><input type="text" class="item-category" placeholder="Category"></td>
                                        <td class="col-item-code d-none"><input type="text" class="item-code" placeholder="Item Code"></td>
                                        <td class="col-description d-none"><input type="text" class="item-desc" placeholder="Description"></td>
                                        <td class="col-discount d-none"><input type="number" class="item-discount" value="0"></td>
                                        <td><input type="number" class="item-qty" value="1"></td>
                                        <td class="custom-size-td">
                                            <select class="item-unit"><option value="">Select Unit</option><option value="PCS">PCS (Pieces)</option><option value="BOX">BOX</option><option value="PACK">PACK</option><option value="SET">SET</option><option value="KG">KG (Kilogram)</option><option value="G">Gram</option><option value="M">Meter</option><option value="FT">Feet</option><option value="L">Liter</option><option value="ML">Milliliter</option></select>
                                        </td>
                                        <td><input type="number" class="item-price" value="0"></td>
                                        <td class="col-amount"><input type="text" class="item-amount" value="0" readonly></td>
                                        <td class="add-col"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="table-footer">
                                <button type="button" class="btn-add-row add-row-btn">ADD ROW</button>
                                <div class="footer-totals">
                                    <div>
                                        <span class="total-label">TOTAL QTY</span>
                                        <span class="total-qty">0</span>
                                    </div>
                                    <div>
                                        <span class="total-label">TOTAL AMOUNT</span>
                                        <span class="total-base-amount">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bottom-section">
                            <div class="bottom-left">
                                <div class="payment-section">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control default-payment-type">
                                            <option value="" selected disabled>Select Payment Type</option>
                                            @foreach($bankAccounts as $bank)
                                                <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                            @endforeach
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
                                </div>

                                <template id="payment-entry-template">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control payment-type-entry">
                                            <option value="" selected disabled>Select Bank Account</option>
                                            @foreach($bankAccounts as $bank)
                                                <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" class="input-control payment-amount" placeholder="Amount" min="0" step="0.01">
                                        <input type="text" class="input-control payment-reference" placeholder="Reference">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-payment-entry" title="Remove">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </template>

                                <button type="button" class="btn-action-light w-50 add-description">
                                    <i class="fa-solid fa-align-left"></i>
                                    ADD DESCRIPTION
                                </button>
                                <button type="button" class="btn-action-light w-50 add-image">
                                    <i class="fa-solid fa-camera"></i>
                                    ADD IMAGE
                                </button>
                                <button type="button" class="btn-action-light w-50 add-document">
                                    <i class="fa-solid fa-file-lines"></i>
                                    ADD DOCUMENT
                                </button>

                                <div class="description-pane d-none mt-2">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control description-input" rows="3" placeholder="Enter a remark or description"></textarea>
                                </div>

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
                                <div class="calc-row">
                                    <div class="calc-label">Discount</div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input discount-pct" placeholder="%">
                                        <span>-</span>
                                        <input type="number" class="mini-input discount-rs" placeholder="Rs">
                                    </div>
                                </div>

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

                                <div class="calc-row">
                                    <div class="checkbox-group">
                                        <input type="checkbox" class="custom-checkbox round-off-check" checked>
                                        <label class="link-text">Round Off</label>
                                    </div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input round-off-val" value="0" readonly>
                                    </div>
                                </div>

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
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#partyAddressPane" type="button" role="tab">
                                    <i class="fa-solid fa-location-dot me-1"></i> Address
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#partyCreditPane" type="button" role="tab">
                                    <i class="fa-solid fa-credit-card me-1"></i> Credit & Balance
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#partyAdditionalPane" type="button" role="tab">
                                    <i class="fa-solid fa-sliders me-1"></i> Additional Fields
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3" id="partyModalTabContent">
                            <div class="tab-pane fade show active" id="partyAddressPane" role="tabpanel">
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

                            <div class="tab-pane fade" id="partyCreditPane" role="tabpanel">
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

                            <div class="tab-pane fade" id="partyAdditionalPane" role="tabpanel">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @if(isset($purchaseReturn))
        <script>
            window.items = @json($items ?? []);
            window.parties = @json($parties ?? []);
            window.bankAccounts = @json($bankAccounts ?? []);
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

    <script src="{{ asset('js/salereturnform_script.js') }}"></script>
    <script src="{{ asset('js/scriptreturn.js') }}"></script>
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

            function resetPartyModal() {
                addPartyForm.reset();
                transactionTypeValue.value = '';
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

