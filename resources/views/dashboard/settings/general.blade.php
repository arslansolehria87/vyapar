<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Settings Dashboard</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Font Awesome (required icon class names like fa-pencil-alt, fa-times, fa-crown) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

  <link href="{{ asset('css/setting/styles.css') }}" rel="stylesheet" />
</head>

<body>
  @if(session('success'))
    <div class="alert alert-success m-3">{{ session('success') }}</div>
  @endif
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
        <a class="sidebar__nav-item is-active" href="{{ route('settings.general') }}" data-nav="general">GENERAL</a>
        <a class="sidebar__nav-item" href="{{ route('settings.transactions') }}" data-nav="transaction">TRANSACTION</a>
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
          <div class="section__title">Application</div>

          <form method="POST" action="{{ route('settings.general.update') }}" class="mb-4">
            @csrf
            <div class="field-block">
              <div class="field-row" style="display:block;">
                <div class="field-label mb-2">
                  Bank Account Password
                  <i class="fa fa-info-circle field-label__info" aria-hidden="true"></i>
                </div>
                <input
                  type="password"
                  name="bank_account_password"
                  class="form-control"
                  placeholder="{{ $bankAccountPasswordSet ? 'Enter new password to update' : 'Enter password' }}"
                />
                <div class="mt-2 text-muted" style="font-size:12px;">
                  Yehi password bank accounts ko bulk active karte waqt use hoga.
                </div>
                @error('bank_account_password')
                  <div class="text-danger mt-2" style="font-size:12px;">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <button
              type="submit"
              class="apply-btn mt-2"
              style="padding: 6px 14px; font-size: 13px; width: auto; min-width: 140px; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;"
            >
              Save Password
            </button>
          </form>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Enable Passcode</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <div class="field-block">
            <div class="field-row">
              <div class="field-label">
                Business Currency
                <i class="fa fa-info-circle field-label__info" aria-hidden="true"></i>
              </div>

              <div class="currency-select" aria-hidden="true">
                <span class="currency-select__rs"></span>
                <select class="currency-select__select">
                  <option>--None--</option>
                  <option selected>Rs</option>
                  <option value="₹">₹ </option>
                  <option value="$">$</option>
                  <option value="Ƀ">Ƀ</option>
                  <option value="AED">AED</option>
                  <option value="Dh">Dh</option>
                  <option value="ر.س">ر.س</option>
                  <option value="SAR">SAR</option>
                  <option value="RM">RM</option>
                  <option value="﷼">﷼</option>
                  <option value="₦">₦</option>
                  <option value="؋">؋</option>
                  <option value="€">€</option>
                  <option value="L">L</option>
                  <option value="د.ج">د.ج</option>
                  <option value="Kz">Kz</option>
                  <option value="A$">A$</option>
                  <option value="₼">₼</option>
                  <option value="ƒ">ƒ</option>
                  <option value="ман">ман</option>
                  <option value="BD">BD</option>
                  <option value="p.">p.</option>
                  <option value="BZ$">BZ$</option>
                  <option value="CFA">CFA</option>
                  <option value="Nu.">Nu.</option>
                  <option value="$b">$b</option>
                  <option value="KM">KM</option>
                  <option value="P">P</option>
                  <option value="kr">kr</option>
                  <option value="R$">R$</option>
                  <option value="лв">лв</option>
                  <option value="FBu">FBu</option>
                  <option value="៛">៛</option>
                  <option value="CVE">Esc</option>
                  <option value="JPY">¥</option>
                  <option value="COP">COL$</option>
                  <option value="CHF">CF</option>
                  <option value="FCFA">FC</option>
                  <option value="FR">Fr.</option>
                  <option value="CRC">₡</option>
                  <option value="HRK">kn</option>
                  <option value="PHP">₱</option>
                  <option value="GBP">£</option>
                  <option value="CZK">Kč</option>
                  <option value="DJF">Fdj</option>
                  <option value="DOP">RD$</option>
                  <option value="EGP">E£</option>
                  <option value="EGP_AR">ج.م </option>
                  <option value="ETB">Br.</option>
                  <option value="F">₣</option>
                  <option value="XPF">CFP</option>
                  <option value="GMD">D</option>
                  <option value="GEL">ლ</option>
                  <option value="GHS_SYM">₵</option>
                  <option value="GHS">GH₵</option>
                  <option value="GTQ">Q</option>
                  <option value="GNF">GFr</option>
                  <option value="HTG">G</option>
                  <option value="HUF">Ft</option>
                  <option value="IDR">Rp</option>
                  <option value="IRR">ریال </option>
                  <option value="IQD">ع.د </option>
                  <option value="ILS">₪ </option>
                  <option value="JMD_SYM">£</option>
                  <option value="JMD">J$ </option>
                  <option value="JOD">JD</option>
                  <option value="KES">KSh</option>
                  <option value="KRW">₩ </option>
                  <option value="KWD_AR">د.ك </option>
                  <option value="KWD">KWD </option>
                  <option value="LAK">₭ </option>
                  <option value="LVL">Ls</option>
                  <option value="LBP">ل.ل </option>
                  <option value="LYD">ل.د </option>
                  <option value="CHF">CHF </option>
                  <option value="LTL">Lt </option>
                  <option value="MKD_CYR">ден </option>
                  <option value="MGA">Ar </option>
                  <option value="MKD">MK </option>
                  <option value="MVR_SYM">.ރ </option>
                  <option value="MUR_SYM">/- </option>
                  <option value="MVR_RF">Rf </option>
                  <option value="MVR">MVR </option>
                  <option value="MRO">UM </option>
                  <option value="MNT">₮ </option>
                  <option value="MAD">د.م. </option>
                  <option value="MZN">MT </option>
                  <option value="MMK">Ks </option>
                  <option value="NAD">N$ </option>
                  <option value="NIO">C$ </option>
                  <option value="OMR_AR">ر.ع. </option>
                  <option value="OMR">OMR</option>
                  <option value="OR">OR </option>
                  <option value="PAB">د.إ </option>
                  <option value="PAB_SYM">B/. </option>
                  <option value="PGK">K </option>
                  <option value="PYG">₲ </option>
                  <option value="PEN">S/. </option>
                  <option value="PLN">zł </option>
                  <option value="QAR">QR </option>
                  <option value="RON">lei </option>
                  <option value="RUB_SYM">₽ </option>
                  <option value="RUB">руб </option>
                  <option value="RWF">RF </option>
                  <option value="STN">Db </option>
                  <option value="RSD">РСД </option>
                  <option value="SLL">Le</option>
                  <option value="SGD">S$ </option>
                  <option value="SOS">Sk </option>
                  <option value="SOS_SH">Sh </option>
                  <option value="ZAR">R </option>
                  <option value="SSP">SSP </option>
                  <option value="SYP">ل.س </option>
                  <option value="TWD">NT$ </option>
                  <option value="TJS">SM </option>
                  <option value="THB">฿ </option>
                  <option value="TOP">T$</option>
                  <option value="TTD">TT$ </option>
                  <option value="TND">د.ت </option>
                  <option value="TRY">₺ </option>
                  <option value="TMT">m </option>
                  <option value="UAH">₴ </option>
                  <option value="UYU">$U</option>
                  <option value="VUV">Vt</option>
                  <option value="VES">Bs</option>
                  <option value="VND">₫</option>
                  <option value="ZMW">ZK</option>
                  <option value="ZWK">ZWK</option>
                </select>
                <i class="fa fa-caret-down currency-select__caret" aria-hidden="true"></i>
              </div>
            </div>
          </div>

          <div class="field-block">
            <div class="field-row field-row--amount">
              <div class="field-label field-label--amount">
                <div>Amount</div>
                <div class="field-label__sub">(upto Decimal Places)</div>
              </div>

              <div class="amount-row">
                <input type="number" class="amount-input" value="2" min="0" step="1" />
                <div class="amount-hint">e.g. 0.00</div>
              </div>
            </div>
          </div>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">TIN Number</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Stop Sale on Negative Stock</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Block New Items from Txn Form</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <label class="check-row">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Block New Parties from Txn Form</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
        </section>

        <!-- Column 1 (bottom): More Transactions -->
        <section class="section section--more-transactions">
          <div class="section__title">More Transactions</div>

          <form method="POST" action="{{ route('settings.general.update') }}" id="moreTransactionsForm">
            @csrf

            <label class="check-row check-row--sm">
              <input type="hidden" name="more_transactions[estimate_quotation_enabled]" value="0">
              <input type="checkbox" class="check-row__input" id="estimateQuotationCheck" name="more_transactions[estimate_quotation_enabled]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.estimate_quotation_enabled')) ? 'checked' : '' }} />
              <span class="check-row__label">Estimate/Quotation</span>
              <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            </label>

            <label class="check-row check-row--sm">
              <input type="hidden" name="more_transactions[proforma_invoice_enabled]" value="0">
              <input type="checkbox" class="check-row__input" id="proformaInvoiceCheck" name="more_transactions[proforma_invoice_enabled]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.proforma_invoice_enabled')) ? 'checked' : '' }} />
              <span class="check-row__label">Proforma Invoice</span>
              <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            </label>

            <label class="check-row check-row--sm">
              <input type="hidden" name="more_transactions[sale_purchase_order_enabled]" value="0">
              <input type="checkbox" class="check-row__input" id="salePurchaseOrderCheck" name="more_transactions[sale_purchase_order_enabled]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.sale_purchase_order_enabled')) ? 'checked' : '' }} />
              <span class="check-row__label">Sale/Purchase Order</span>
              <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            </label>

            <label class="check-row check-row--sm">
              <input type="hidden" name="more_transactions[other_income_enabled]" value="0">
              <input type="checkbox" class="check-row__input" id="otherIncomeCheck" name="more_transactions[other_income_enabled]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.other_income_enabled')) ? 'checked' : '' }} />
              <span class="check-row__label">Other Income</span>
              <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            </label>

            <label class="check-row check-row--sm">
              <input type="hidden" name="more_transactions[fixed_assets_enabled]" value="0">
              <input type="checkbox" class="check-row__input" id="fixedAssetsCheck" name="more_transactions[fixed_assets_enabled]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.fixed_assets_enabled')) ? 'checked' : '' }} />
              <span class="check-row__label">Fixed Assets (FA)</span>
              <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            </label>

            <label class="check-row check-row--sm">
              <input type="hidden" name="more_transactions[delivery_challan_enabled]" value="0">
              <input type="checkbox" class="check-row__input" id="deliveryChallanCheck" name="more_transactions[delivery_challan_enabled]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.delivery_challan_enabled')) ? 'checked' : '' }} />
              <span class="check-row__label">Delivery Challan</span>
              <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            </label>
            <div id="deliveryChallanOptions" class="{{ !empty(data_get($generalSettings ?? [], 'more_transactions.delivery_challan_enabled')) ? '' : 'd-none' }}">
              <label class="check-row check-row--sm ps-4">
                <input type="hidden" name="more_transactions[goods_return_on_delivery_challan]" value="0">
                <input type="checkbox" class="check-row__input" id="goodsReturnOnDeliveryChallanCheck" name="more_transactions[goods_return_on_delivery_challan]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.goods_return_on_delivery_challan')) ? 'checked' : '' }} />
                <span class="check-row__label">Goods return on <span class="fw-bold"> Delivery Challan</span></span>
                <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
              </label>
              <label class="check-row check-row--sm ps-4">
                <input type="hidden" name="more_transactions[print_amount_in_delivery_challan]" value="0">
                <input type="checkbox" class="check-row__input" id="printAmountInDeliveryChallanCheck" name="more_transactions[print_amount_in_delivery_challan]" value="1" {{ !empty(data_get($generalSettings ?? [], 'more_transactions.print_amount_in_delivery_challan')) ? 'checked' : '' }} />
                <span class="check-row__label">Print amount in <span class="fw-bold">Delivery Challan</span></span>
                <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
              </label>
            </div>

            <button type="submit" class="apply-btn mt-3" style="padding:6px 14px;font-size:13px;width:auto;min-width:160px;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;">Save More Transactions</button>
          </form>
        </section>

        <!-- Column 2 (top): Multi Firm -->
        <section class="section section--multi-firm">
          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" id="multiFirmCheckbox" />
            <span class="check-row__label">Multi Firm</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            <!-- <button class="btn bg-transparent btn-sm ps-5 ms-3 d-none" id="addFirmBtn" type="button"><span
                class="text-primary">+ Add Firm</span></button> -->
            <!-- Button trigger modal -->
            <button type="button" class="btn bg-transparent btn-sm ps-5 ms-3 d-none" data-bs-toggle="modal"
              data-bs-target="#exampleModal" id="addFirmBtn">
              <span class="text-primary">+ Add Firm</span>
            </button>
          </label>

          <div class="radio-box" id="multiFirmBox">
            <div class="radio-box__row">
              <label class="radio-row">
                <input type="radio" class="radio-row__input" name="firm" checked />
                <span class="radio-row__label">Grocery Store</span>
              </label>
              <div class="radio-box__meta">
                <span class="radio-box__default">DEFAULT</span>
                <i class="fa fa-pencil-alt radio-box__edit" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </section>

        <!-- Column 2 (bottom): Stock Transfer Between Stores -->
        <section class="section section--stock-transfer">
          <div class="section__title">Stock Transfer Between Stores</div>
          <div class="section__desc">
            Manage all your stores/godowns and transfer stock seamlessly between them. Using this feature, you can
            transfer stock between stores/godowns and manage your inventory more efficiently.
          </div>

          <label class="check-row check-row--stock">
            <input type="checkbox" class="check-row__input" checked />
            <span class="check-row__label">Store management &amp; Stock transfer</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
            <span class="stock-icons" aria-hidden="true">
              <i class="fa fa-camera stock-icons__icon stock-icons__icon--pink"></i>
              <i class="fa fa-user-circle stock-icons__icon stock-icons__icon--blue"></i>
            </span>
          </label>
        </section>

        <!-- Column 3 (top): Backup & History -->
        <section class="section section--backup">
          <div class="section__title">Backup &amp; History</div>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" />
            <span class="check-row__label">Auto Backup</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>

          <div class="backup-line">
            <span class="backup-line__text">Last Backup 10/03/2026 | 11:03 AM</span>
            <i class="fa fa-info-circle backup-line__info" aria-hidden="true"></i>
          </div>

          <label class="check-row check-row--sm">
            <input type="checkbox" class="check-row__input" checked />
            <span class="check-row__label">Transaction History</span>
            <i class="fa fa-info-circle check-row__info" aria-hidden="true"></i>
          </label>
        </section>

        <!-- Column 3 (bottom): Customize Your View -->
        <section class="section section--customize">
          <div class="section__title">Customize Your View</div>
          <div class="section__sub">Choose Your Screen Zoom/Scale</div>
          <div class="section__desc">
            You can use this setting to resize the Vyapar screen, making it larger or smaller to fit your preferences.
          </div>

          <div class="zoom-row">
            <div class="zoom-slider-wrap">
              <input id="zoomRange" class="zoom-range" type="range" min="70" max="130" step="1" value="100"
                aria-label="Screen Zoom/Scale" />

              <div class="zoom-ticks" id="zoomTicks" aria-hidden="true">
                <span class="zoom-tick" data-value="70">70%</span>
                <span class="zoom-tick" data-value="80">80%</span>
                <span class="zoom-tick" data-value="90">90%</span>
                <span class="zoom-tick" data-value="100">100%</span>
                <span class="zoom-tick" data-value="110">110%</span>
                <span class="zoom-tick" data-value="115">115%</span>
                <span class="zoom-tick" data-value="120">120%</span>
                <span class="zoom-tick" data-value="130">130%</span>
              </div>
            </div>

            <button class="apply-btn" id="applyBtn" type="button">Apply</button>
          </div>
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
              <img id="logoPreview" class="d-none border rounded" style="max-width: 100%; max-height: 200px; object-fit: contain;" alt="" />
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
                <input type="text" class="form-control" id="validationCustom02"  required >

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
            reader.onload = function(evt) {
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
    })();
  </script>
</body>

</html>
