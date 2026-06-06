<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Settings Dashboard</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Font Awesome (required icon class names like fa-pencil-alt, fa-times, fa-crown) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

  <link href="{{ asset('css/setting/styles.css') }}" rel="stylesheet" />
</head>

<body>
  <div class="settings-layout">
    <aside class="sidebar">
      <div class="sidebar__header">
        <div class="sidebar__header-left">
          <a href="{{ route('dashboard') }}" class="sidebar__back" title="Back to bank">
            <i class="fa fa-arrow-left" aria-hidden="true"></i>
          </a>
          <div class="sidebar__title">Settings</div>
        </div>
        <i class="fa fa-search sidebar__search" aria-hidden="true"></i>
      </div>

      <nav class="sidebar__nav" aria-label="Settings navigation">
        <a class="sidebar__nav-item " href="{{ route('settings.general') }}" data-nav="general">GENERAL</a>
        <a class="sidebar__nav-item  is-active" href="{{ route('settings.transactions') }}" data-nav="transaction">TRANSACTION</a>
        <a class="sidebar__nav-item" href="{{ route('settings.print-layout') }}" data-nav="print">PRINT</a>
        <a class="sidebar__nav-item" href="{{ route('settings.taxes') }}" data-nav="taxes">TAXES</a>
        <a class="sidebar__nav-item" href="{{ route('settings.transaction-messages') }}" data-nav="transaction-message">TRANSACTION MESSAGE</a>
        <a class="sidebar__nav-item" href="{{ route('settings.parties') }}" data-nav="party">PARTY</a>
        <a class="sidebar__nav-item" href="{{ route('settings.items') }}" data-nav="item">ITEM</a>
        <a class="sidebar__nav-item" href="#" data-nav="service-reminders">
          <span>SERVICE REMINDERS</span>
          <i class="fa fa-crown sidebar__crown" aria-hidden="true"></i>
        </a>
      </nav>
    </aside>

    <main class="main-content">
      <!-- <button class="main-close" type="button" aria-label="Close">
        <i class="fa fa-times" aria-hidden="true"></i>
      </button> -->

      <div class="main-grid">
        <!-- Column 1 (top): Application -->
        <section class="section section--application">
          <div class="section__title">Transaction Header</div>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" id="invoiceNoCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'transaction_header.invoice_number_enabled')) ? 'checked' : '' }} />
            <span class="check-row__label">Invoice/ Bill No.</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>





          <label class="check-row">
            <input type="checkbox" class="check-row__input" id="transactionTimeCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'transaction_header.transaction_time_enabled')) ? 'checked' : '' }} />
            <span class="check-row__label">Add Time on Transaction</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" id="cashSaleDefaultCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'transaction_header.cash_sale_default')) ? 'checked' : '' }} />
            <span class="check-row__label">Cash Sale by Default</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" id="billingNameCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'transaction_header.billing_name_enabled')) ? 'checked' : '' }} />
            <span class="check-row__label">Billing Name of Parties</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" id="customerPoDetailsCheckbox" {{ !empty($customerPoDetailsEnabled) ? 'checked' : '' }} />
            <span class="check-row__label">Customer P.O. Details for transactions</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
        </section>

        <!-- Column 1 (bottom): More Transactions -->
        <section class="section section--more-transactions">
          <div class="section__title">More Transactions Features</div>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="quickEntryCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.quick_entry')) ? 'checked' : '' }} />
            <span class="check-row__label">Quick Entry </span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="invoicePreviewCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.do_not_show_invoice_preview')) ? 'checked' : '' }} />
            <span class="check-row__label">Do not show Invoice Preview</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="transactionPasscodeCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.passcode_enabled')) ? 'checked' : '' }} />
            <span class="check-row__label">Enable passcode for transactions edit/delete</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <div class="ms-4 mb-2 {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.passcode_enabled')) && filled(data_get($transactionSettings ?? [], 'more_transaction_features.transaction_passcode_hash')) ? '' : 'd-none' }}" id="changeTransactionPasscodeWrap">
            <a href="#" class="text-decoration-none small" id="changeTransactionPasscodeLink">Change passcode</a>
          </div>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Discount during payments</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="linkPaymentsCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.link_payment_to_invoices')) ? 'checked' : '' }} />
            <span class="check-row__label">Link payments to invoices</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="paymentTermsCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.due_dates_payment_terms_enabled')) ? 'checked' : '' }} />
            <span class="check-row__label">Due dates and payment terms</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <div class="ms-4 mb-3">
            <a href="#" class="text-decoration-none small" id="setPaymentTermsLink">Set Payment terms</a>
            <div class="mt-2 d-none" id="paymentTermsEditor">
              <input type="text" class="form-control form-control-sm mb-2" id="paymentTermNameInput" placeholder="Term name" value="{{ data_get($transactionSettings ?? [], 'payment_terms.name', 'Net 15') }}">
              <label class="form-label small mb-1" for="paymentTermDaysInput">Deal Days</label>
              <input type="number" class="form-control form-control-sm" id="paymentTermDaysInput" min="0" placeholder="Deal days" value="{{ data_get($transactionSettings ?? [], 'payment_terms.days', 15) }}">
              <label class="form-label small mb-1 mt-2" for="paymentTermDueDatePreview">Due Date Preview</label>
              <input type="date" class="form-control form-control-sm" id="paymentTermDueDatePreview" readonly>
            </div>
          </div>
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="termsConditionsCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.terms_conditions_enabled')) ? 'checked' : '' }} />
            <span class="check-row__label">Terms and Conditions</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="deliveryChallanCheck" />
            <span class="check-row__label">Show profit while making sale invoices</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <div class="mt-3 d-flex flex-column gap-2 align-items-start">
            <button type="button" class="btn btn-outline-primary btn-sm" id="transportationDetailsBtn">Transportation Details</button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="additionalChargesSettingsBtn">Additional Charges</button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="termsConditionsSettingsBtn">Terms and Conditions</button>
          </div>

        </section>

        <section class="section section--backup">
          <div class="section__title">Taxes, Discount &amp; Total</div>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="transactionDiscountCheckbox" {{ data_get($transactionSettings ?? [], 'transaction_totals.discount_enabled', true) ? 'checked' : '' }} />
            <span class="check-row__label">Transaction wise discount</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="transactionTaxCheckbox" {{ data_get($transactionSettings ?? [], 'transaction_totals.tax_enabled', true) ? 'checked' : '' }} />
            <span class="check-row__label">Transaction wise tax</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="roundTotalCheck" {{ data_get($transactionSettings ?? [], 'transaction_totals.round_total_enabled', true) ? 'checked' : '' }} />
            <span class="check-row__label">Round of total</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <div id="roundTotalOptions" class="ms-4 mb-3 mt-1 {{ data_get($transactionSettings ?? [], 'transaction_totals.round_total_enabled', true) ? '' : 'd-none' }}">
            <select id="roundTotalModeSelect" style="border:none; border-bottom:1px solid black; padding-bottom:2px;">
              <option value="nearest" {{ data_get($transactionSettings ?? [], 'transaction_totals.round_total_mode', 'down-to') === 'nearest' ? 'selected' : '' }}>Nearest</option>
              <option value="down-to" {{ data_get($transactionSettings ?? [], 'transaction_totals.round_total_mode', 'down-to') === 'down-to' ? 'selected' : '' }}>Down to</option>
              <option value="up-to" {{ data_get($transactionSettings ?? [], 'transaction_totals.round_total_mode', 'down-to') === 'up-to' ? 'selected' : '' }}>Up to</option>
            </select>
            <span class="mx-3 text-dark">To</span>
            @php($roundPrecision = (string) data_get($transactionSettings ?? [], 'transaction_totals.round_total_precision', 100))
            <select id="roundTotalPrecisionSelect" style="border:none; border-bottom:1px solid black; padding-bottom:2px;">
              <option value="1" {{ $roundPrecision === '1' ? 'selected' : '' }}>1</option>
              <option value="10" {{ $roundPrecision === '10' ? 'selected' : '' }}>10</option>
              <option value="50" {{ $roundPrecision === '50' ? 'selected' : '' }}>50</option>
              <option value="100" {{ $roundPrecision === '100' ? 'selected' : '' }}>100</option>
              <option value="1000" {{ $roundPrecision === '1000' ? 'selected' : '' }}>1000</option>
            </select>
          </div>
        </section>

        <!-- Column 2 (top): Multi Firm -->
        <section class="section section--multi-firm">
          <div class="section__title">Items table</div>
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Inclusive/Exclusive Tax on Rate(Price/Unit)</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Display Purchase Price of items</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Show Last 5 Sale Prices of items</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="freeItemQtyCheckbox" {{ !empty(data_get($transactionSettings ?? [], 'items_table.free_item_qty_enabled')) ? 'checked' : '' }} />
            <span class="check-row__label">Free item quantity</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="countCheckbox" {{ !empty($countEnabled) ? 'checked' : '' }} />
            <span class="check-row__label">Count</span>
            <span class="ps-4 text-muted" id="changeTextBtn" style="font-size: 12px; transition: color 0.2s;">Change
              text</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
        </section>

        <!-- Column 2 (bottom): Stock Transfer Between Stores -->
        <section class="section section--stock-transfer">
          <div class="section__title pb-2">Transaction Prefixes</div>

          <div class="prefix-settings-wrapper">
            <!-- Firm Dropdown -->
            <div class="custom-fieldset mb-4">
              <label class="custom-label">Firm</label>
              <select class="custom-select prefix-select" id="firmSelect">
                <option value="Grocery Store" selected>Grocery Store</option>
              </select>
              <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </div>

            <!-- Prefixes Container -->
            <div class="custom-fieldset custom-fieldset--large mb-0">
              <label class="custom-label mb-0" style="left: 14px;">Prefixes</label>

              <div class="row g-3 pt-1">
                <!-- Sale -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Sale</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="sale">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>

                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <!-- Credit Note -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Credit Note</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="credit_note">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>
                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <!-- Sale Order -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Sale Order</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="sale_order">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>
                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <!-- Purchase Order -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Purchase Order</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="purchase_order">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>
                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <!-- Estimate -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Estimate</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="estimate">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>
                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <!-- Proforma Invoice -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Proforma Invoice</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="proforma_invoice">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>
                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <!-- Delivery Challan -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Delivery Challan</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="delivery_challan">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>
                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
                <!-- Payment In -->
                <div class="col-md-6">
                  <div class="custom-fieldset mb-0">
                    <label class="custom-label">Payment In</label>
                    <select class="custom-select prefix-select transaction-prefix-select" data-prefix-type="payment_in">
                      <option value="None">None</option>
                      <option value="Standard">INV (for invoice, EST)</option>
                      <option value="Custom">GS (Firm name initials)</option>
                    </select>
                    <svg class="dropdown-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 6L8 10L12 6" stroke="#A1A1A1" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </section>

        <!-- Column 3 (top): Backup & History -->
     
        <section class="section section--customize ps-5">
          <div class="section__title">Billing Type</div>

          <label class="check-row check-row--sm">
            <input type="radio" class="radio-row__input" name="billingType" id="liteSale" checked />
            <span class="check-row__label">Lite Sale</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
          <label class="check-row check-row--sm">
            <input type="radio" class="radio-row__input" name="billingType" id="fullSale" />
            <span class="check-row__label">Full Sale</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>


        </section>
      </div>
    </main>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Add Firm</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body d-flex">
          <div class="col-6 py-5 d-flex justify-content-center align-items-center">
            <div id="addLogoContainer" style="cursor: pointer; position: relative;">
              <p id="addLogoText" class="h4 text-secondary border rounded p-4 text-center m-0">Add logo</p>
              <img id="logoPreview" class="d-none border rounded"
                style="max-width: 100%; max-height: 200px; object-fit: contain;" alt="" />
              <input type="file" id="logoInput" accept="image/*" style="display: none;">
            </div>
          </div>
          <div class="col-6 py-5">
            <form class="row g-3 needs-validation" novalidate>
              <div class="col-12">
                <label for="validationCustom01" class="form-label">Business name</label>
                <input type="text" class="form-control" id="validationCustom01" required>

              </div>
              <div class="col-12">
                <label for="validationCustom02" class="form-label">Phone No.</label>
                <input type="text" class="form-control" id="validationCustom02" required>

              </div>
              <div class="col-12">
                <label for="validationCustomUsername" class="form-label">Email ID</label>
                <div class="input-group has-validation">
                  <input type="email" class="form-control" id="validationCustomUsername"
                    aria-describedby="inputGroupPrepend" required>
                  <div class="invalid-feedback">
                    Please choose a username.
                  </div>
                </div>
              </div>

            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Change Text Modal -->
  <div class="modal fade" id="changeTextModal" tabindex="-1" aria-labelledby="changeTextModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content" style="width: 100% !important;">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="changeTextModalLabel">Edit text</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" id="changeTextInput" placeholder="Enter new text" value="Count">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="transactionPasscodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="transactionPasscodeModalLabel">Set Transaction Passcode</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="password" class="form-control mb-3" id="transactionPasscodeInput" maxlength="4" placeholder="New 4-digit passcode">
          <input type="password" class="form-control" id="transactionPasscodeConfirmInput" maxlength="4" placeholder="Confirm 4-digit passcode">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary w-100" id="saveTransactionPasscodeBtn">Save Passcode</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="transportationDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Transportation Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="transportationDetailsModalBody"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="saveTransportationSettingsBtn">Done</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="transactionAdditionalChargesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Additional Charges</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="fw-semibold">Enable Additional Charges</span>
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" id="transactionAdditionalChargesToggle">
            </div>
          </div>
          <div id="transactionAdditionalChargesBody"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger w-100" id="saveTransactionAdditionalChargesBtn">Save Details</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="transactionPrefixModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Prefix</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="transactionPrefixTypeInput">
          <input type="text" class="form-control" id="transactionPrefixValueInput" placeholder="Enter prefix e.g. SD">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary w-100" id="saveTransactionPrefixBtn">Save Prefix</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      const navItems = document.querySelectorAll('.sidebar__nav-item');
      // Auto-highlight active nav based on URL
      const currentPath = window.location.pathname.split('/').pop() || 'general.html';

      navItems.forEach((a) => {
        const href = a.getAttribute('href');
        if (href === currentPath) {
          a.classList.add('is-active');
        } else {
          a.classList.remove('is-active');
        }

        // Prevent default only for empty links
        if (href === '#') {
          a.addEventListener('click', (e) => e.preventDefault());
        }
      });

      const slider = document.getElementById('zoomRange');
      const applyBtn = document.getElementById('applyBtn');
      const ticks = document.querySelectorAll('.zoom-tick');

      if (slider && applyBtn) {
        const min = 70;
        const max = 130;
        const tickValues = Array.from(ticks).map((t) => Number(t.dataset.value));

        const clamp = (n, a, b) => Math.max(a, Math.min(b, n));
        const setActiveTick = (value) => {
          ticks.forEach((t) => t.classList.remove('is-active'));
          const match = [...ticks].find((t) => Number(t.dataset.value) === value);
          if (match) match.classList.add('is-active');
        };

        const setZoomFromSlider = () => {
          const value = clamp(Number(slider.value), min, max);
          const mainGrid = document.querySelector('.main-grid');
          if (mainGrid) {
            mainGrid.style.zoom = `${value}%`;
          }
          // Highlight the nearest labeled tick so the "displayed value" visually follows the knob.
          const nearest = tickValues.reduce((best, v) => {
            const db = Math.abs(best - value);
            const dv = Math.abs(v - value);
            return dv < db ? v : best;
          }, tickValues[0]);
          setActiveTick(nearest);
        };

        // Position ticks (absolute) based on min/max so labels align with knob positions.
        const positionTicks = () => {
          ticks.forEach((t) => {
            const v = Number(t.dataset.value);
            const leftPct = ((v - min) / (max - min)) * 100;
            t.style.left = `${leftPct}%`;
            t.style.transform = 'translateX(-50%)';
          });
        };

        positionTicks();
        setZoomFromSlider();
        slider.addEventListener('input', setZoomFromSlider);
        applyBtn.addEventListener('click', setZoomFromSlider);
      }

      // Multi Firm logic
      const multiFirmCheckbox = document.getElementById('multiFirmCheckbox');
      const addFirmBtn = document.getElementById('addFirmBtn');
      const multiFirmBox = document.getElementById('multiFirmBox');

      if (multiFirmCheckbox) {
        multiFirmCheckbox.addEventListener('change', (e) => {
          if (addFirmBtn) addFirmBtn.classList.toggle('d-none', !e.target.checked);
        });
      }

      if (addFirmBtn) {
        addFirmBtn.addEventListener('click', (e) => {
          e.preventDefault(); // Prevents the click from bubbling and unchecking the multi-firm label
        });
      }

      // Add Logo Upload Logic
      const logoContainer = document.getElementById('addLogoContainer');
      const logoInput = document.getElementById('logoInput');
      const logoPreview = document.getElementById('logoPreview');
      const logoText = document.getElementById('addLogoText');

      if (logoContainer && logoInput) {
        logoContainer.addEventListener('click', () => logoInput.click());

        logoInput.addEventListener('change', (e) => {
          if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function (evt) {
              logoPreview.src = evt.target.result;
              logoPreview.classList.remove('d-none');
              logoText.classList.add('d-none');
            };
            reader.readAsDataURL(e.target.files[0]);
          }
        });
      }

      // Delivery Challan Logic
      const deliveryChallanCheck = document.getElementById('deliveryChallanCheck');
      const deliveryChallanOptions = document.getElementById('deliveryChallanOptions');
      if (deliveryChallanCheck && deliveryChallanOptions) {
        deliveryChallanCheck.addEventListener('change', (e) => {
          deliveryChallanOptions.classList.toggle('d-none', !e.target.checked);
        });
      }

      // Round Total Logic
      const roundTotalCheck = document.getElementById('roundTotalCheck');
      const roundTotalOptions = document.getElementById('roundTotalOptions');
      if (roundTotalCheck && roundTotalOptions) {
        roundTotalCheck.addEventListener('change', (e) => {
          roundTotalOptions.classList.toggle('d-none', !e.target.checked);
        });
      }

      // Count Change Text Logic
      const countCheckbox = document.getElementById('countCheckbox');
      const customerPoDetailsCheckbox = document.getElementById('customerPoDetailsCheckbox');
      const changeTextBtn = document.getElementById('changeTextBtn');
      const changeTextModalEl = document.getElementById('changeTextModal');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const transactionSettingsUpdateUrl = @json(route('settings.transactions.update'));

      if (countCheckbox && changeTextBtn && changeTextModalEl) {
        const toggleChangeText = () => {
          if (countCheckbox.checked) {
            changeTextBtn.classList.remove('text-muted');
            changeTextBtn.classList.add('text-primary');
            changeTextBtn.style.cursor = 'pointer';
          } else {
            changeTextBtn.classList.add('text-muted');
            changeTextBtn.classList.remove('text-primary');
            changeTextBtn.style.cursor = 'default';
          }
        };

        // Initial state
        toggleChangeText();

        countCheckbox.addEventListener('change', async function () {
          toggleChangeText();
          const defaultText = 'Change text';
          changeTextBtn.textContent = 'Saving...';

          try {
            const response = await fetch(transactionSettingsUpdateUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: JSON.stringify({
                count_enabled: countCheckbox.checked ? 1 : 0
              })
            });

            if (!response.ok) {
              throw new Error('Failed to save setting');
            }

            changeTextBtn.textContent = 'Saved';
          } catch (error) {
            countCheckbox.checked = !countCheckbox.checked;
            toggleChangeText();
            changeTextBtn.textContent = 'Save failed';
          } finally {
            setTimeout(() => {
              changeTextBtn.textContent = defaultText;
            }, 1200);
          }
        });

        changeTextBtn.addEventListener('click', (e) => {
          if (!countCheckbox.checked) return; // Do nothing if inactive
          e.preventDefault(); // Prevent bubbling up to the label!
          e.stopPropagation(); // Stop label from toggling checkbox again

          const changeTextModal = new bootstrap.Modal(changeTextModalEl);
          changeTextModal.show();
        });
      }

      if (customerPoDetailsCheckbox) {
        customerPoDetailsCheckbox.addEventListener('change', async function () {
          try {
            const response = await fetch(transactionSettingsUpdateUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: JSON.stringify({
                customer_po_enabled: customerPoDetailsCheckbox.checked ? 1 : 0
              })
            });

            if (!response.ok) {
              throw new Error('Failed to save setting');
            }
          } catch (error) {
            customerPoDetailsCheckbox.checked = !customerPoDetailsCheckbox.checked;
          }
        });
      }

      // Prefix Settings Logic
      const prefixSelects = document.querySelectorAll('.prefix-select');
      prefixSelects.forEach(select => {
        const updateColor = () => {
          if (select.value === 'None') {
            select.style.color = '#757575'; // muted gray for None
          } else {
            select.style.color = '#212529'; // dark gray for other options
          }
        };
        updateColor();
        select.addEventListener('change', updateColor);
      });
    })();
  </script>
  <script>
    (() => {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const updateUrl = @json(route('settings.transactions.update'));
      let transactionSettings = @json($transactionSettings ?? []);
      let transactionPasscodePreviousState = {{ !empty(data_get($transactionSettings ?? [], 'more_transaction_features.passcode_enabled')) ? 'true' : 'false' }};
      let transactionPasscodeSaveCommitted = false;
      let transactionPasscodeMode = 'create';

      const mergeDeep = (target, source) => {
        const output = Array.isArray(target) ? [...target] : { ...target };
        if (!source || typeof source !== 'object') return output;
        Object.keys(source).forEach(key => {
          const value = source[key];
          if (Array.isArray(value)) {
            output[key] = value.map(item => (item && typeof item === 'object' ? { ...item } : item));
          } else if (value && typeof value === 'object') {
            output[key] = mergeDeep(output[key] && typeof output[key] === 'object' ? output[key] : {}, value);
          } else {
            output[key] = value;
          }
        });
        return output;
      };

      const defaultSettings = {
        transaction_header: { invoice_number_enabled: true, transaction_time_enabled: false, cash_sale_default: false, billing_name_enabled: true, customer_po_enabled: false },
        items_table: { free_item_qty_enabled: false, count_enabled: false, count_label: 'Count' },
        more_transaction_features: { terms_conditions_enabled: true, due_dates_payment_terms_enabled: true, quick_entry: false, link_payment_to_invoices: true, passcode_enabled: false, do_not_show_invoice_preview: false },
        transaction_totals: { discount_enabled: true, tax_enabled: true, round_total_enabled: true, round_total_mode: 'down-to', round_total_precision: 100 },
        sale_prefix: { enabled: true, active: 'INV', options: ['INV'] },
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
        payment_terms: { enabled: true, name: 'Net 15', days: 15 },
        transportation_details: { enabled: false, fields: [
          { key: 'field_1', label: 'Transport Name', enabled: false, show_in_print: true },
          { key: 'field_2', label: 'Vehicle Number', enabled: false, show_in_print: true },
          { key: 'field_3', label: 'Delivery Date', enabled: false, show_in_print: true },
          { key: 'field_4', label: 'Delivery Location', enabled: false, show_in_print: true },
          { key: 'field_5', label: 'Field 5', enabled: false, show_in_print: true }
        ]},
        additional_charges: { enabled: true, items: [
          { key: 'shipping', enabled: true, label: 'Shipping', tax_rate: 'NONE', tax_enabled: false },
          { key: 'packaging', enabled: true, label: 'Packaging', tax_rate: 'NONE', tax_enabled: false },
          { key: 'adjustment', enabled: true, label: 'Adjustment', tax_rate: 'NONE', tax_enabled: false }
        ]}
      };

      const qs = (selector) => document.querySelector(selector);
      const qsa = (selector) => Array.from(document.querySelectorAll(selector));
      const normalize = (settings) => mergeDeep(defaultSettings, settings || {});
      const hasStoredTransactionPasscode = () => !!transactionSettings?.more_transaction_features?.transaction_passcode_hash;
      const updatePasscodeChangeLink = () => {
        const wrap = qs('#changeTransactionPasscodeWrap');
        if (!wrap) return;
        const shouldShow = !!transactionSettings?.more_transaction_features?.passcode_enabled && hasStoredTransactionPasscode();
        wrap.classList.toggle('d-none', !shouldShow);
      };
      const openTransactionPasscodeModal = (mode = 'create') => {
        transactionPasscodeMode = mode;
        const title = qs('#transactionPasscodeModalLabel');
        if (title) {
          title.textContent = mode === 'change' ? 'Change Transaction Passcode' : 'Set Transaction Passcode';
        }
        transactionPasscodeSaveCommitted = false;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('transactionPasscodeModal')).show();
      };
      transactionSettings = normalize(transactionSettings);

      function saveSettings(extraPayload = {}) {
        const payload = mergeDeep(collectSettings(), extraPayload);
        return fetch(updateUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(payload)
        }).then(async response => {
          const data = await response.json();
          if (!response.ok) {
            throw new Error(data?.message || 'Failed to save transaction settings.');
          }
          transactionSettings = normalize(data.settings || payload);
          renderSettings();
          return data;
        });
      }

      function collectSettings() {
        return {
          transaction_header: {
            invoice_number_enabled: !!qs('#invoiceNoCheckbox')?.checked,
            transaction_time_enabled: !!qs('#transactionTimeCheckbox')?.checked,
            cash_sale_default: !!qs('#cashSaleDefaultCheckbox')?.checked,
            billing_name_enabled: !!qs('#billingNameCheckbox')?.checked,
            customer_po_enabled: !!qs('#customerPoDetailsCheckbox')?.checked
          },
          items_table: {
            free_item_qty_enabled: !!qs('#freeItemQtyCheckbox')?.checked,
            count_enabled: !!qs('#countCheckbox')?.checked,
            count_label: String(qs('#changeTextInput')?.value || 'Count').trim() || 'Count'
          },
          more_transaction_features: {
            quick_entry: !!qs('#quickEntryCheckbox')?.checked,
            do_not_show_invoice_preview: !!qs('#invoicePreviewCheckbox')?.checked,
            passcode_enabled: !!qs('#transactionPasscodeCheckbox')?.checked,
            link_payment_to_invoices: !!qs('#linkPaymentsCheckbox')?.checked,
            due_dates_payment_terms_enabled: !!qs('#paymentTermsCheckbox')?.checked,
            terms_conditions_enabled: !!qs('#termsConditionsCheckbox')?.checked
          },
          transaction_totals: {
            discount_enabled: !!qs('#transactionDiscountCheckbox')?.checked,
            tax_enabled: !!qs('#transactionTaxCheckbox')?.checked,
            round_total_enabled: !!qs('#roundTotalCheck')?.checked,
            round_total_mode: String(qs('#roundTotalModeSelect')?.value || 'down-to'),
            round_total_precision: parseInt(qs('#roundTotalPrecisionSelect')?.value || 100, 10) || 100
          },
          payment_terms: {
            enabled: !!qs('#paymentTermsCheckbox')?.checked,
            name: String(qs('#paymentTermNameInput')?.value || 'Net 15').trim() || 'Net 15',
            days: parseInt(qs('#paymentTermDaysInput')?.value || 0, 10) || 0
          },
          transportation_details: {
            enabled: qsa('.transport-enabled-toggle').some(el => el.checked),
            fields: qsa('.transport-setting-row').map(row => ({
              key: row.dataset.key,
              label: row.querySelector('.transport-label-input')?.value || row.dataset.key,
              enabled: !!row.querySelector('.transport-enabled-toggle')?.checked,
              show_in_print: !!row.querySelector('.transport-print-toggle')?.checked
            }))
          },
          additional_charges: {
            enabled: !!qs('#transactionAdditionalChargesToggle')?.checked,
            items: qsa('.transaction-charge-row').map(row => ({
              key: row.dataset.key,
              enabled: !!row.querySelector('.transaction-charge-enabled')?.checked,
              label: row.querySelector('.transaction-charge-label')?.value || row.dataset.key,
              tax_rate: row.querySelector('.transaction-charge-tax')?.value || 'NONE',
              tax_enabled: !!row.querySelector('.transaction-charge-tax-enabled')?.checked
            }))
          },
          transaction_prefixes: Object.fromEntries(qsa('.transaction-prefix-select').map(select => {
            const type = select.dataset.prefixType;
            const options = Array.from(select.options).map(option => option.value).filter(value => value && value !== '__add__');
            return [type, {
              active: select.value === '__add__' ? (transactionSettings.transaction_prefixes?.[type]?.active || '') : select.value,
              options: Array.from(new Set(options))
            }];
          })),
          sale_prefix: {
            enabled: true,
            active: qs('.transaction-prefix-select[data-prefix-type="sale"]')?.value || 'INV',
            options: Array.from(new Set(Array.from(qs('.transaction-prefix-select[data-prefix-type="sale"]')?.options || []).map(option => option.value).filter(value => value && value !== '__add__')))
          },
          count_enabled: !!qs('#countCheckbox')?.checked,
          customer_po_enabled: !!qs('#customerPoDetailsCheckbox')?.checked
        };
      }

      function renderTransportModal() {
        const body = qs('#transportationDetailsModalBody');
        if (!body) return;
        body.innerHTML = transactionSettings.transportation_details.fields.map(field => `
          <div class="transport-setting-row mb-3" data-key="${field.key}">
            <div class="small text-muted mb-1">${field.key.replace('_', ' ').toUpperCase()}</div>
            <div class="d-flex align-items-center gap-2 mb-2">
              <input type="text" class="form-control transport-label-input" value="${String(field.label || '').replace(/"/g, '&quot;')}">
              <input type="checkbox" class="form-check-input transport-enabled-toggle" ${field.enabled ? 'checked' : ''}>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input transport-print-toggle" type="checkbox" ${field.show_in_print ? 'checked' : ''}>
              <label class="form-check-label">Show in print</label>
            </div>
          </div>
        `).join('');
      }

      function renderAdditionalChargesModal() {
        const body = qs('#transactionAdditionalChargesBody');
        if (!body) return;
        qs('#transactionAdditionalChargesToggle').checked = !!transactionSettings.additional_charges.enabled;
        body.innerHTML = transactionSettings.additional_charges.items.map(item => `
          <div class="transaction-charge-row border-top pt-3 mt-3" data-key="${item.key}">
            <div class="d-flex align-items-center gap-2 mb-2">
              <input type="checkbox" class="form-check-input transaction-charge-enabled" ${item.enabled ? 'checked' : ''}>
              <input type="text" class="form-control transaction-charge-label" value="${String(item.label || '').replace(/"/g, '&quot;')}">
              <select class="form-select transaction-charge-tax">
                <option value="NONE" ${item.tax_rate === 'NONE' ? 'selected' : ''}>NONE</option>
                <option value="GST 5%" ${item.tax_rate === 'GST 5%' ? 'selected' : ''}>GST 5%</option>
                <option value="GST 12%" ${item.tax_rate === 'GST 12%' ? 'selected' : ''}>GST 12%</option>
              </select>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input transaction-charge-tax-enabled" type="checkbox" ${item.tax_enabled ? 'checked' : ''}>
              <label class="form-check-label">Enable tax for ${item.label || item.key}</label>
            </div>
          </div>
        `).join('');
      }

      function renderPrefixSelects() {
        qsa('.transaction-prefix-select').forEach(select => {
          const type = select.dataset.prefixType;
          const prefixConfig = transactionSettings.transaction_prefixes?.[type] || { active: '', options: [] };
          const options = Array.from(new Set([...(prefixConfig.options || []), prefixConfig.active].filter(Boolean)));
          select.innerHTML = options.map(value => `<option value="${value}" ${value === prefixConfig.active ? 'selected' : ''}>${value}</option>`).join('') + '<option value="__add__">+ Add Prefix</option>';
        });
      }

      function renderSettings() {
        transactionSettings = normalize(transactionSettings);
        qs('#invoiceNoCheckbox').checked = !!transactionSettings.transaction_header.invoice_number_enabled;
        qs('#transactionTimeCheckbox').checked = !!transactionSettings.transaction_header.transaction_time_enabled;
        qs('#cashSaleDefaultCheckbox').checked = !!transactionSettings.transaction_header.cash_sale_default;
        qs('#billingNameCheckbox').checked = !!transactionSettings.transaction_header.billing_name_enabled;
        qs('#customerPoDetailsCheckbox').checked = !!transactionSettings.transaction_header.customer_po_enabled;
        qs('#freeItemQtyCheckbox').checked = !!transactionSettings.items_table.free_item_qty_enabled;
        qs('#countCheckbox').checked = !!transactionSettings.items_table.count_enabled;
        qs('#changeTextInput').value = transactionSettings.items_table.count_label || 'Count';
        qs('#quickEntryCheckbox').checked = !!transactionSettings.more_transaction_features.quick_entry;
        qs('#invoicePreviewCheckbox').checked = !!transactionSettings.more_transaction_features.do_not_show_invoice_preview;
        qs('#transactionPasscodeCheckbox').checked = !!transactionSettings.more_transaction_features.passcode_enabled;
        qs('#linkPaymentsCheckbox').checked = !!transactionSettings.more_transaction_features.link_payment_to_invoices;
        qs('#paymentTermsCheckbox').checked = !!transactionSettings.more_transaction_features.due_dates_payment_terms_enabled;
        qs('#termsConditionsCheckbox').checked = !!transactionSettings.more_transaction_features.terms_conditions_enabled;
        qs('#transactionDiscountCheckbox').checked = !!transactionSettings.transaction_totals.discount_enabled;
        qs('#transactionTaxCheckbox').checked = !!transactionSettings.transaction_totals.tax_enabled;
        qs('#roundTotalCheck').checked = !!transactionSettings.transaction_totals.round_total_enabled;
        qs('#roundTotalModeSelect').value = transactionSettings.transaction_totals.round_total_mode || 'down-to';
        qs('#roundTotalPrecisionSelect').value = String(transactionSettings.transaction_totals.round_total_precision || 100);
        qs('#roundTotalOptions')?.classList.toggle('d-none', !transactionSettings.transaction_totals.round_total_enabled);
        qs('#paymentTermsEditor')?.classList.toggle('d-none', !transactionSettings.more_transaction_features.due_dates_payment_terms_enabled);
        qs('#paymentTermNameInput').value = transactionSettings.payment_terms.name || 'Net 15';
        qs('#paymentTermDaysInput').value = transactionSettings.payment_terms.days || 0;
        updatePasscodeChangeLink();
        updatePaymentTermsPreview();
        renderTransportModal();
        renderAdditionalChargesModal();
        renderPrefixSelects();
      }

      function updatePaymentTermsPreview() {
        const preview = qs('#paymentTermDueDatePreview');
        const daysInput = qs('#paymentTermDaysInput');
        if (!preview || !daysInput) return;
        const days = parseInt(daysInput.value || 0, 10) || 0;
        const base = new Date();
        if (Number.isNaN(base.getTime())) {
          preview.value = '';
          return;
        }
        base.setDate(base.getDate() + days);
        const yyyy = base.getFullYear();
        const mm = String(base.getMonth() + 1).padStart(2, '0');
        const dd = String(base.getDate()).padStart(2, '0');
        preview.value = `${yyyy}-${mm}-${dd}`;
      }

      qsa('#invoiceNoCheckbox,#transactionTimeCheckbox,#cashSaleDefaultCheckbox,#billingNameCheckbox,#customerPoDetailsCheckbox,#freeItemQtyCheckbox,#countCheckbox,#quickEntryCheckbox,#invoicePreviewCheckbox,#linkPaymentsCheckbox,#paymentTermsCheckbox,#termsConditionsCheckbox,#transactionDiscountCheckbox,#transactionTaxCheckbox,#roundTotalModeSelect,#roundTotalPrecisionSelect').forEach(el => {
        el?.addEventListener('change', () => saveSettings().catch(() => {}));
      });

      qs('#setPaymentTermsLink')?.addEventListener('click', function(e) {
        e.preventDefault();
        const editor = qs('#paymentTermsEditor');
        if (!editor) return;
        editor.classList.toggle('d-none');
        if (!editor.classList.contains('d-none')) {
          updatePaymentTermsPreview();
        }
      });

      qs('#paymentTermNameInput')?.addEventListener('input', () => saveSettings().catch(() => {}));
      qs('#paymentTermDaysInput')?.addEventListener('input', () => {
        updatePaymentTermsPreview();
        saveSettings().catch(() => {});
      });

      qs('#roundTotalCheck')?.addEventListener('change', function() {
        qs('#roundTotalOptions')?.classList.toggle('d-none', !this.checked);
        saveSettings().catch(() => {});
      });

      qsa('.transaction-prefix-select').forEach(select => {
        select.addEventListener('change', function() {
          if (this.value === '__add__') {
            qs('#transactionPrefixTypeInput').value = this.dataset.prefixType || '';
            qs('#transactionPrefixValueInput').value = '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('transactionPrefixModal')).show();
            return;
          }
          saveSettings().catch(() => {});
        });
      });

      qs('#saveTransactionPrefixBtn')?.addEventListener('click', function() {
        const type = qs('#transactionPrefixTypeInput')?.value || 'sale';
        const value = String(qs('#transactionPrefixValueInput')?.value || '').trim().toUpperCase();
        if (!value) return;
        const current = transactionSettings.transaction_prefixes?.[type] || { active: value, options: [] };
        current.active = value;
        current.options = Array.from(new Set([...(current.options || []), value]));
        transactionSettings.transaction_prefixes[type] = current;
        if (type === 'sale') {
          transactionSettings.sale_prefix.active = value;
          transactionSettings.sale_prefix.options = Array.from(new Set([...(transactionSettings.sale_prefix.options || []), value]));
        }
        saveSettings().then(() => bootstrap.Modal.getOrCreateInstance(document.getElementById('transactionPrefixModal')).hide()).catch(() => {});
      });

      qs('#transportationDetailsBtn')?.addEventListener('click', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('transportationDetailsModal')).show());
      qs('#saveTransportationSettingsBtn')?.addEventListener('click', () => saveSettings().then(() => bootstrap.Modal.getOrCreateInstance(document.getElementById('transportationDetailsModal')).hide()).catch(() => {}));
      qs('#additionalChargesSettingsBtn')?.addEventListener('click', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('transactionAdditionalChargesModal')).show());
      qs('#saveTransactionAdditionalChargesBtn')?.addEventListener('click', () => saveSettings().then(() => bootstrap.Modal.getOrCreateInstance(document.getElementById('transactionAdditionalChargesModal')).hide()).catch(() => {}));

      qs('#transactionPasscodeCheckbox')?.addEventListener('change', function() {
        if (this.checked) {
          transactionPasscodePreviousState = !!transactionSettings.more_transaction_features.passcode_enabled;
          if (hasStoredTransactionPasscode()) {
            saveSettings().catch(() => {
              this.checked = transactionPasscodePreviousState;
            });
            return;
          }
          openTransactionPasscodeModal('create');
          return;
        }
        transactionPasscodePreviousState = false;
        saveSettings().catch(() => {});
      });

      qs('#changeTransactionPasscodeLink')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!qs('#transactionPasscodeCheckbox')?.checked && !hasStoredTransactionPasscode()) {
          qs('#transactionPasscodeCheckbox').checked = true;
        }
        openTransactionPasscodeModal('change');
      });

      qs('#saveTransactionPasscodeBtn')?.addEventListener('click', function() {
        saveSettings({
          transaction_passcode: qs('#transactionPasscodeInput')?.value || '',
          transaction_passcode_confirmation: qs('#transactionPasscodeConfirmInput')?.value || ''
        }).then(() => {
          transactionPasscodeSaveCommitted = true;
          bootstrap.Modal.getOrCreateInstance(document.getElementById('transactionPasscodeModal')).hide();
          qs('#transactionPasscodeInput').value = '';
          qs('#transactionPasscodeConfirmInput').value = '';
          updatePasscodeChangeLink();
        }).catch(() => {});
      });

      document.getElementById('transactionPasscodeModal')?.addEventListener('hidden.bs.modal', function() {
        if (!transactionPasscodeSaveCommitted) {
          qs('#transactionPasscodeCheckbox').checked = transactionPasscodePreviousState;
        }
        transactionPasscodeSaveCommitted = false;
        transactionPasscodeMode = 'create';
        updatePasscodeChangeLink();
      });

      renderSettings();
    })();
  </script>
</body>

</html>
