@extends('layouts.app')
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> -->



@section('title', 'Vyapar — Parties')
@section('description', 'Manage your business parties, customers, and suppliers in Vyapar accounting software.')
@section('page', 'parties')
 <link rel="stylesheet" href="{{ asset('css/parties.css') }}">
@section('content')

<!-- uper panel -->
<div class="uper-panel">
  <div class="panel-main">

    <!-- Left: Header + Arrow -->
    <div class="text">
      <div class="header-dropdown">
        <h1>Parties</h1>
        <i class="fa fa-chevron-down arrow-icon" id="partyViewDropdownTrigger"></i>

      <div class="header-dropdown-menu">
  <button type="button" class="dropdown-item active" data-party-view-option="parties">
    Parties
    <i class="fa fa-check tick-icon"></i>
  </button>
  <button type="button" class="dropdown-item is-hidden" data-party-view-option="groups" id="partyGroupsViewOption">
    Party Groups
    <i class="fa fa-check tick-icon"></i>
  </button>

</div>
      </div>
    </div>

    <!-- Right: Buttons -->
    <div class="action-buttons">
      <button class="btn-add-entity" data-bs-toggle="modal" data-bs-target="#addPartyModal">
        <i class="fa-solid fa-plus me-1"></i> Add Party
      </button>
      <button class="btn-add-entity btn-party-transfer" type="button" data-bs-toggle="modal" data-bs-target="#partyTransferModal">
        <i class="fa-solid fa-right-left me-1"></i> Party Transfer
      </button>

      <button class="btn-settings" id="partySettingsTrigger" title="Settings">
        <i class="fa-solid fa-gear"></i>
      </button>

      <button class="btn-ellipsis" id="partyMoreOptionsTrigger" title="More Options">
        <i class="fa-solid fa-ellipsis-vertical"></i>
      </button>
    </div>

  </div>
</div>

  <div class="split-pane">


    <!-- Left: Party List -->
    <div class="split-left">
      <div class="list-panel-header">
      <div class="search-box">
  <i class="fa fa-search"></i>
  <input type="text" class="form-control search-input" placeholder="Search Party Name" id="partySearchInput">
</div>

      </div>
      <div class="filter-toolbar">
        <div class="filter-wrapper">
          <div class="parent-arrows" onclick="sortPartySidebar('name', this)">
            <span class="entity-balance positive" style="color: gray !important;">Party Name</span>
            <div class="counter-arrows">
              <i class="fa fa-chevron-up increment"></i>
              <i class="fa fa-chevron-down decrement"></i>
            </div>
          </div>

          <i class="fa fa-filter filter-icon" onclick="toggleFilter()"></i>

          <div class="filter-dropdown" id="filterDropdown">
            <label><input type="checkbox" data-party-filter="all" checked> All</label>
            <label><input type="checkbox" data-party-filter="active"> Active</label>
            <label><input type="checkbox" data-party-filter="inactive"> Inactive</label>
            <label><input type="checkbox" data-party-filter="receive"> To Receive</label>
            <label><input type="checkbox" data-party-filter="pay"> To Pay</label>
            <div class="filter-actions">
              <button class="clear-btn" type="button" id="partyFilterClear">Clear</button>
              <button class="apply-btn" type="button" id="partyFilterApply">Apply</button>
            </div>
          </div>

          <div class="separator"></div>

          <div class="parent-arrows" onclick="sortPartySidebar('amount', this)">
            <span class="entity-balance positive" style="color: gray !important;">Amount</span>
            <div class="counter-arrows">
              <i class="fa fa-chevron-up increment"></i>
              <i class="fa fa-chevron-down decrement"></i>
            </div>
          </div>
        </div>
      </div>

      <ul class="entity-list" id="partiesList">
  @foreach($parties as $party)
    <li class="party-item"
        data-id="{{ $party->id }}"
        data-is-active="{{ $party->is_active ? 1 : 0 }}"
        data-name="{{ $party->name }}"
        data-phone="{{ $party->phone }}"
        data-phone-number-2="{{ $party->phone_number_2 }}"
        data-ptcl-number="{{ $party->ptcl_number }}"
        data-email="{{ $party->email }}"
        data-city="{{ $party->city }}"
        data-address="{{ $party->address }}"
        data-billing-address="{{ $party->billing_address }}"
        data-shipping-address="{{ $party->shipping_address }}"
        data-opening-balance="{{ $party->opening_balance }}"
        data-current-balance="{{ $party->current_balance }}"
        data-as-of-date="{{ $party->as_of_date }}"
        data-transaction-type="{{ $party->transaction_type }}"
        data-party-type="{{ $party->party_type }}"
        data-party-group="{{ $party->party_group }}"
        data-sales-total="{{ (float) $party->sales->sum('grand_total') }}"
        data-display-amount="{{ (float) $party->current_balance }}"
        data-due-days="{{ $party->due_days }}"
        data-credit-limit-enabled="{{ $party->credit_limit_enabled }}"
        data-credit-limit-amount="{{ $party->credit_limit_amount }}"
        data-payment-reminder-enabled="{{ $party->payment_reminder_enabled ? 1 : 0 }}"
        data-payment-reminder-date="{{ optional($party->payment_reminder_date)->format('Y-m-d') }}"
        data-payment-reminder-message="{{ e($party->payment_reminder_message ?? '') }}"
        data-payment-reminder-sent-at="{{ optional($party->payment_reminder_sent_at)->format('Y-m-d H:i:s') }}"
        data-custom-fields="{{ json_encode($party->custom_fields ?? []) }}">
      <span class="entity-name">{{ $party->name }}</span>
      @if(!$party->is_active)
        <span class="party-inactive-pill">Inactive</span>
      @endif
      <span class="entity-balance {{ ((float) $party->current_balance < 0) ? 'negative' : 'positive' }}">
        Rs {{ number_format((float) $party->current_balance, 2) }}
      </span>
      <div class="party-item-menu-wrap">
        <button type="button" class="party-item-menu-btn" data-party-menu-toggle aria-label="Party Actions">
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <div class="party-item-menu">
          <button type="button" class="party-item-menu-action text-danger" data-party-delete-id="{{ $party->id }}">Delete</button>
        </div>
      </div>
    </li>
  @endforeach
</ul>
    </div>
    <!-- Right: Party Details -->
    <div class="split-right">
      <div class="detail-panel-header" style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <div style="flex:1 1 420px; min-width:0;">
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <div class="entity-detail-name" id="partyDetailName" style="font-weight: 400;">abc</div>
            <button class="btn-icon" id="editPartyBtn" title="Edit" type="button">
              <i class="fa-solid fa-pen"></i>
            </button>
          </div>
          <div class="entity-detail-meta-row">

  <div class="entity-detail-meta">
    <div class="meta-heading">Phone Number</div>
    <div class="meta-value"  id="partyPhone"> +91 98765 43210</div>
  </div>

  <div class="entity-detail-meta">
    <div class="meta-heading">Email</div>
    <div class="meta-value" id="partyEmail"> example@email.com</div>
  </div>

  <div class="entity-detail-meta">
    <div class="meta-heading">Billing Address</div>
    <div class="meta-value" id="partyAddress"> 123, Main Street, City</div>
  </div>

  <div class="entity-detail-meta">
    <div class="meta-heading">City / PTCL</div>
    <div class="meta-value" id="partyCityPtcl"> City - PTCL</div>
  </div>

</div>
        </div>
        <div class="action-buttons" style="display:flex; align-items:center; justify-content:flex-end; gap:10px; flex:0 0 auto; padding-top:4px;">
          <button class="btn-icon btn-party-reminder" id="openPartyReminderBtn" title="Payment Reminder" type="button" style="color:#f59e0b;">
            <i class="fa-regular fa-clock"></i>
          </button>
          <button class="btn-icon btn-party-reminder" id="openPartyReminderWhatsappBtn" title="Send Reminder on WhatsApp" type="button" style="color:#25D366;">
            <i class="fa-brands fa-whatsapp"></i>
          </button>
        </div>
      </div>

      <div class="party-transactions-panel">
        <div class="table-header">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <h6 class="mb-0">Transactions</h6>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <input type="date" class="form-control form-control-sm" id="txnDateFrom" style="width:165px;">
              <span class="text-muted">to</span>
              <input type="date" class="form-control form-control-sm" id="txnDateTo" style="width:165px;">
              <button type="button" class="btn btn-sm btn-outline-primary" id="txnDateApply">Apply</button>
              <button type="button" class="btn btn-sm btn-light" id="txnDateClear">Clear</button>
            </div>
          </div>
          <div class="header-icons">
            <div class="txn-toolbar" id="txnToolbar" style="display:none;">
              <input type="text" class="form-control form-control-sm" id="txnSearchInput" placeholder="Search transactions">
            </div>
            <i class="fa fa-search" id="txnSearchToggle" title="Search"></i>
            <i class="fa-solid fa-file-excel" id="txnExcelTrigger" title="Export Excel"></i>
            <i class="fa-solid fa-file-pdf" id="txnPdfTrigger" title="Export PDF"></i>
            <i class="fa-brands fa-whatsapp" id="txnWhatsappTrigger" title="Share on WhatsApp"></i>
            <i class="fa fa-print" id="txnPrintTrigger" title="Print"></i>
          </div>
        </div>

        <div class="table-responsive">
          <table class="txn-table" id="partyTxnTable">
            <thead>
              <tr>
                <th>
                  <div class="table-main" onclick="toggleSort(this)">
                    <span>Date</span>
                    <span class="sort-arrows"><i class="fa fa-chevron-up"></i><i class="fa fa-chevron-down"></i></span>
                    <i class="fa fa-filter table-filter-icon" onclick="toggleFilterDropdown(this)"></i>
                  </div>
                  <div class="filter-dropdown">
                    <input type="text" class="dropdown-input" value="Equal To" readonly>
                    <div class="dropdown-options">
                      <div class="dropdown-option">Equal To</div>
                      <div class="dropdown-option">Less Than</div>
                      <div class="dropdown-option">Greater Than</div>
                      <div class="dropdown-option">Range</div>
                    </div>
                    <input type="date">
                    <div class="filter-actions">
                      <button class="clear-btn" type="button">Clear</button>
                      <button class="apply-btn" type="button">Apply</button>
                    </div>
                  </div>
                </th>
                <th>
                  <div class="table-main" onclick="toggleSort(this)">
                    <span>Type</span>
                    <span class="sort-arrows"><i class="fa fa-chevron-up"></i><i class="fa fa-chevron-down"></i></span>
                    <i class="fa fa-filter table-filter-icon" onclick="toggleFilterDropdown(this)"></i>
                  </div>
                  <div class="filter-dropdown">
                    <label><input type="checkbox" value="Sale"> Sale</label>
                    <label><input type="checkbox" value="Purchase"> Purchase</label>
                    <label><input type="checkbox" value="Estimate"> Estimate</label>
                    <label><input type="checkbox" value="Sale Order"> Sale Order</label>
                    <label><input type="checkbox" value="Proforma Invoice"> Proforma Invoice</label>
                    <label><input type="checkbox" value="Delivery Challan"> Delivery Challan</label>
                    <label><input type="checkbox" value="Credit Note"> Credit Note</label>
                    <label><input type="checkbox" value="Receivable Opening Balance"> Receivable Opening Balance</label>
                    <label><input type="checkbox" value="Payable Opening Balance"> Payable Opening Balance</label>
                    <div class="filter-actions">
                      <button class="clear-btn" type="button">Clear</button>
                      <button class="apply-btn" type="button">Apply</button>
                    </div>
                  </div>
                </th>
                <th>
                  <div class="table-main" onclick="toggleSort(this)">
                    <span>Bill No</span>
                    <span class="sort-arrows"><i class="fa fa-chevron-up"></i><i class="fa fa-chevron-down"></i></span>
                    <i class="fa fa-filter table-filter-icon" onclick="toggleFilterDropdown(this)"></i>
                  </div>
                  <div class="filter-dropdown">
                    <input type="text" class="dropdown-input" value="Contains" readonly>
                    <div class="dropdown-options">
                      <div class="dropdown-option">Contains</div>
                      <div class="dropdown-option">Exact Match</div>
                    </div>
                    <input type="text" placeholder="Bill no">
                    <div class="filter-actions">
                      <button class="clear-btn" type="button">Clear</button>
                      <button class="apply-btn" type="button">Apply</button>
                    </div>
                  </div>
                </th>
                <th>
                  <div class="table-main" onclick="toggleSort(this)">
                    <span>Debit</span>
                    <span class="sort-arrows"><i class="fa fa-chevron-up"></i><i class="fa fa-chevron-down"></i></span>
                    <i class="fa fa-filter table-filter-icon" onclick="toggleFilterDropdown(this)"></i>
                  </div>
                  <div class="filter-dropdown">
                    <input type="text" class="dropdown-input" value="Equal To" readonly>
                    <div class="dropdown-options">
                      <div class="dropdown-option">Equal To</div>
                      <div class="dropdown-option">Less Than</div>
                      <div class="dropdown-option">Greater Than</div>
                      <div class="dropdown-option">Range</div>
                    </div>
                    <input type="text" placeholder="Amount">
                    <div class="filter-actions">
                      <button class="clear-btn" type="button">Clear</button>
                      <button class="apply-btn" type="button">Apply</button>
                    </div>
                  </div>
                </th>
                <th>
                  <div class="table-main" onclick="toggleSort(this)">
                    <span>Credit</span>
                    <span class="sort-arrows"><i class="fa fa-chevron-up"></i><i class="fa fa-chevron-down"></i></span>
                    <i class="fa fa-filter table-filter-icon" onclick="toggleFilterDropdown(this)"></i>
                  </div>
                  <div class="filter-dropdown">
                    <input type="text" class="dropdown-input" value="Equal To" readonly>
                    <div class="dropdown-options">
                      <div class="dropdown-option">Equal To</div>
                      <div class="dropdown-option">Less Than</div>
                      <div class="dropdown-option">Greater Than</div>
                      <div class="dropdown-option">Range</div>
                    </div>
                    <input type="text" placeholder="Amount">
                    <div class="filter-actions">
                      <button class="clear-btn" type="button">Clear</button>
                      <button class="apply-btn" type="button">Apply</button>
                    </div>
                  </div>
                </th>
                <th>
                  <div class="table-main" onclick="toggleSort(this)">
                    <span>Running Balance</span>
                    <span class="sort-arrows"><i class="fa fa-chevron-up"></i><i class="fa fa-chevron-down"></i></span>
                    <i class="fa fa-filter table-filter-icon" onclick="toggleFilterDropdown(this)"></i>
                  </div>
                  <div class="filter-dropdown">
                    <input type="text" class="dropdown-input" value="Equal To" readonly>
                    <div class="dropdown-options">
                      <div class="dropdown-option">Equal To</div>
                      <div class="dropdown-option">Less Than</div>
                      <div class="dropdown-option">Greater Than</div>
                      <div class="dropdown-option">Range</div>
                    </div>
                    <input type="text" placeholder="Amount">
                    <div class="filter-actions">
                      <button class="clear-btn" type="button">Clear</button>
                      <button class="apply-btn" type="button">Apply</button>
                    </div>
                  </div>
                </th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="txnTableBody">
              <tr>
                <td colspan="7" class="text-center" style="padding: 40px;">
                  <i class="fa-solid fa-receipt" style="font-size: 40px; color: #d1d5db;"></i>
                  <p class="mt-2" style="color: #6b7280;">No transactions yet</p>
                  <p style="font-size: 12px; color: #9ca3af;">Select a party to view transactions</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <div class="party-groups-view is-hidden" id="partyGroupsView">
    <div class="party-groups-layout">
      <div class="party-groups-sidebar">
        <div class="party-groups-sidebar-header">
          <div class="party-groups-sidebar-title-row">
            <h3>Groups</h3>

          </div>
          <div class="party-groups-search">
            <i class="fa fa-search"></i>
            <input type="text" id="partyGroupSearchInput" placeholder="Search groups">
          </div>
        </div>
        <div class="party-groups-sidebar-list" id="partyGroupsSidebarList"></div>
      </div>

      <div class="party-groups-content">
        <div class="party-groups-summary-card">
          <div class="party-groups-summary-top">
            <div>
              <div class="party-groups-summary-label">Selected Group</div>
              <h3 id="selectedPartyGroupName">General</h3>
              <div class="party-groups-summary-stats">
                <span id="selectedPartyGroupCount">Parties(0)</span>
                <span id="selectedPartyGroupAmount">Rs 0.00</span>
              </div>
            </div>
            <button type="button" class="party-groups-move-btn" id="movePartiesToGroupBtn">Move To This Group</button>
          </div>
        </div>

        <div class="party-groups-table-card">
          <div class="party-groups-table-header">
            <h4>Parties</h4>
            <div class="party-groups-search party-groups-search-right">
              <i class="fa fa-search"></i>
              <input type="text" id="partyGroupPartySearchInput" placeholder="Search party">
            </div>
          </div>
          <div class="party-groups-table-wrap">
            <table class="party-groups-table">
              <thead>
                <tr>
                  <th>Party</th>
                  <th>Amount</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="partyGroupPartiesTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
@section('modals')
<!-- MODAL: ADD PARTY -->
<div class="modal fade" id="addPartyModal" tabindex="-1" aria-labelledby="addPartyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPartyModalLabel"><i class="fa-solid fa-user-plus me-2"></i>Add Party</h5>
        <div class="d-flex align-items-center gap-2" style="margin-left:79%;">
          <button class="btn btn-sm btn-outline-secondary" type="button" id="partyModalSettingsTrigger" title="Settings"><i class="fa-solid fa-gear"></i></button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body">
        <form id="addPartyForm">
          @csrf
          <div class="row g-3 mb-4">
            <div class="col-md-4" data-party-setting="name">
              <label class="form-label fw-600">Party Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Enter party name" id="partyNameInput" required>
            </div>
            <div class="col-md-4" data-party-setting="phone">
              <label class="form-label fw-600">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                <input type="tel" name="phone" class="form-control" placeholder="Enter phone number" id="partyPhoneInput">
              </div>
            </div>
            <div class="col-md-4" data-party-setting="phone_2">
              <label class="form-label fw-600">Phone Number 2</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone-volume"></i></span>
                <input type="tel" name="phone_number_2" class="form-control" placeholder="Enter second phone number" id="partyPhone2Input">
              </div>
            </div>
            <div class="col-md-4 is-hidden" data-party-setting="party_grouping">
              <label class="form-label fw-600">Party Group</label>
              <div class="party-group-dropdown" id="partyGroupDropdown">
                <button type="button" class="form-control party-group-trigger" id="partyGroupTrigger">
                  <span id="partyGroupTriggerText">Select party group</span>
                  <i class="fa-solid fa-chevron-down"></i>
                </button>
                <input type="hidden" name="party_group" id="partyGroupInput" value="">
                <div class="party-group-menu" id="partyGroupMenu">
                  <button type="button" class="party-group-add-btn" id="openPartyGroupModal">+ New Group</button>
                  <div class="party-group-options" id="partyGroupOptions"></div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">PTCL Number</label>
              <input type="text" name="ptcl_number" class="form-control" placeholder="Enter PTCL number" id="partyPtclInput">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">City</label>
              <input type="text" name="city" class="form-control" placeholder="Enter city" id="partyCityInput">
            </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs" id="partyModalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="party-address-tab" data-bs-toggle="tab" data-bs-target="#partyAddressPane" type="button" role="tab">
                <i class="fa-solid fa-location-dot me-1"></i> Address
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-credit-tab" data-bs-toggle="tab" data-bs-target="#partyCreditPane" type="button" role="tab">
                <i class="fa-solid fa-credit-card me-1"></i> Credit & Balance
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-additional-tab" data-bs-toggle="tab" data-bs-target="#partyAdditionalPane" type="button" role="tab">
                <i class="fa-solid fa-sliders me-1"></i> Additional Fields
              </button>
            </li>
          </ul>

          <div class="tab-content pt-3" id="partyModalTabContent">
            <!-- Address Tab -->
            <div class="tab-pane fade show active" id="partyAddressPane" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-6" data-party-setting="email">
                  <label class="form-label">Email ID</label>
                  <input type="email" name="email" class="form-control" placeholder="example@email.com">
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                  <label class="form-label">Address</label>
                  <textarea id="partyAddressInput" class="form-control" name="address" rows="3" placeholder="Enter address"></textarea>
                </div>
                <div class="col-md-6" data-party-setting="billing_address">
                  <label class="form-label">Billing Address</label>
                  <textarea id="billingAddress" class="form-control" name="billing_address" rows="3" placeholder="Enter billing address"></textarea>
                </div>
                <div class="col-md-6" data-party-setting="shipping_address">
                  <label class="form-label">Shipping Address</label>
                  <textarea  id="shippingAddress" class="form-control" name="shipping_address" rows="3" placeholder="Enter shipping address"></textarea>
                </div>
              </div>
            </div>

            <!-- Credit & Balance Tab -->
          <div class="tab-pane fade" id="partyCreditPane" role="tabpanel">
  <div class="row g-3">
    <div class="col-md-4" data-party-setting="opening_balance">
      <label class="form-label">Opening Balance</label>
      <div class="input-group">
        <span class="input-group-text">₹</span>
        <input type="number" name="opening_balance" class="form-control" placeholder="0.00">
      </div>
    </div>
    <div class="col-md-4" data-party-setting="as_of_date">
      <label class="form-label">As Of Date</label>
      <input type="date" name="as_of_date" class="form-control" value="{{ date('Y-m-d') }}">
    </div>
    <div class="col-md-4" data-party-setting="credit_limit">
      <label class="form-label d-block">Credit Limit</label>
      <div class="form-check form-switch mt-2">
        <input class="form-check-input" name="credit_limit_enabled" type="checkbox" id="creditLimitSwitch">
        <label class="form-check-label" for="creditLimitSwitch">Enable</label>
      </div>
      <div class="input-group mt-2 is-hidden" id="creditLimitAmountWrap">
        <span class="input-group-text">Rs</span>
        <input type="number" name="credit_limit_amount" class="form-control" placeholder="Enter credit limit" id="creditLimitAmountInput" min="0" step="0.01">
      </div>
    </div>
    <div class="col-md-4" data-party-setting="due_days">
      <label class="form-label">Due Days</label>
      <input type="number" name="due_days" class="form-control" placeholder="e.g. 5, 10, 30" min="1" max="100" id="partyDueDaysInput">
    </div>
  </div>

  <!-- To Receive / To Pay Options at the bottom -->
  <div class="mt-4" data-party-setting="transaction_type">
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
</div>
<div class="col-md-6" data-party-setting="party_type">
  <label class="form-label fw-600">Party Type</label>

  <div class="form-check">
    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="customerParty" value="customer">
    <label class="form-check-label" for="customerParty">Customer</label>
  </div>

  <div class="form-check">
    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="supplierParty" value="supplier">
    <label class="form-check-label" for="supplierParty">Supplier</label>
  </div>
  <div class="form-check">
    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="brokerParty" value="broker">
    <label class="form-check-label" for="brokerParty">Broker</label>
  </div>
</div>

            <!-- Additional Fields Tab -->
            <div class="tab-pane fade" id="partyAdditionalPane" role="tabpanel" data-party-setting="additional_fields">
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

                <input type="hidden" id="transactionTypeValue" name="transaction_type">
                @endfor

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
 <button type="button" class="btn btn-primary" id="btnUpdateParty" style="display:none;">Update</button>
    <button type="button" class="btn btn-danger" id="btnDeleteParty" style="display:none;">Delete</button>
          </div>
        </form>

        <div class="txn-option-modal" id="partyGroupModal">
          <div class="txn-option-backdrop" data-close-party-group="true"></div>
          <div class="txn-option-dialog">
            <h3 class="txn-option-title">New Party Group</h3>
            <div>
              <label class="form-label fw-600" for="partyGroupNameInput">Enter Party Group Name</label>
              <input type="text" class="form-control" id="partyGroupNameInput" placeholder="e.g. Wholesale">
            </div>
            <div class="txn-option-actions">
              <button type="button" class="txn-option-btn cancel" id="partyGroupCancel">Cancel</button>
              <button type="button" class="txn-option-btn ok" id="partyGroupSave">OK</button>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
</div>

<div class="party-settings-drawer" id="partySettingsDrawer">
  <div class="party-settings-backdrop" data-close-party-settings="true"></div>
  <div class="party-settings-panel">
    <div class="party-settings-header">
      <h4>Party Settings</h4>
      <button type="button" class="party-settings-close" id="partySettingsClose">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="party-settings-group">
      <div class="party-settings-group-title">General</div>
      <label class="party-settings-item">
        <span>Party Grouping <i class="fa-regular fa-circle-info party-settings-info"></i></span>
        <input type="checkbox" class="party-setting-toggle" data-setting-target="party_grouping" @checked($partySettings['party_grouping'] ?? true)>
      </label>
      <label class="party-settings-item">
        <span>Shipping Address <i class="fa-regular fa-circle-info party-settings-info"></i></span>
        <input type="checkbox" class="party-setting-toggle" data-setting-target="shipping_address" @checked($partySettings['shipping_address'] ?? true)>
      </label>
      <label class="party-settings-item">
        <span>Print Shipping Address <i class="fa-regular fa-circle-info party-settings-info"></i></span>
        <input type="checkbox" class="party-setting-toggle" data-setting-target="print_shipping_address" @checked($partySettings['print_shipping_address'] ?? true)>
      </label>
      <label class="party-settings-item">
        <span>Manage Party Status <i class="fa-regular fa-circle-info party-settings-info"></i></span>
        <input type="checkbox" class="party-setting-toggle" data-setting-target="party_status" @checked($partySettings['party_status'] ?? $partyStatusEnabled)>
      </label>
      <label class="party-settings-item">
        <span>Enable Payment Reminder <i class="fa-regular fa-circle-info party-settings-info"></i></span>
        <input type="checkbox" class="party-setting-toggle" data-setting-target="payment_reminder" @checked($partySettings['payment_reminder'] ?? true)>
      </label>
      <div class="party-settings-subtext">Remind me for payment due in <i class="fa-regular fa-circle-info party-settings-info"></i></div>
      <div class="party-settings-reminder-row">
              <input type="number" min="1" value="{{ $partySettings['payment_reminder_days'] ?? 2 }}" id="partyReminderDays" class="party-settings-reminder-input">
        <span class="party-settings-reminder-suffix">(Days)</span>
      </div>
    </div>
    <div class="party-settings-group">
      <div class="party-settings-group-title">Additional fields <i class="fa-regular fa-circle-info party-settings-info"></i></div>
      <div class="party-settings-extra-field">
        <label class="party-settings-item">
          <span>Additional Field 1</span>
          <input type="checkbox" class="party-setting-toggle" data-setting-target="additional_field_1" @checked($partySettings['additional_field_1'] ?? false)>
        </label>
        <input type="text" class="party-settings-extra-input" id="partyAdditionalField1Name" placeholder="Enter Field Name" value="{{ $partySettings['additional_field_1_name'] ?? '' }}">
        <label class="party-settings-switch-row">
          <span>Show In Print</span>
          <input type="checkbox" class="party-settings-switch" id="partyAdditionalField1Print" @checked($partySettings['additional_field_1_print'] ?? false)>
        </label>
      </div>
      <div class="party-settings-extra-field">
        <label class="party-settings-item">
          <span>Additional Field 2</span>
          <input type="checkbox" class="party-setting-toggle" data-setting-target="additional_field_2" @checked($partySettings['additional_field_2'] ?? false)>
        </label>
        <input type="text" class="party-settings-extra-input" id="partyAdditionalField2Name" placeholder="Enter Field Name" value="{{ $partySettings['additional_field_2_name'] ?? '' }}">
        <label class="party-settings-switch-row">
          <span>Show In Print</span>
          <input type="checkbox" class="party-settings-switch" id="partyAdditionalField2Print" @checked($partySettings['additional_field_2_print'] ?? false)>
        </label>
      </div>
      <button type="button" class="party-settings-more-btn">
        <i class="fa-solid fa-gear"></i> More Settings
      </button>
    </div>
  </div>
</div>

<div class="party-more-menu" id="partyMoreMenu">
  <button type="button" class="party-more-menu-item" id="importExcelOption">Import from Excel</button>
  <button type="button" class="party-more-menu-item" id="importPhoneOption">Import from Phone</button>
  <button type="button" class="party-more-menu-item" id="importContactsOption">Import Via Google Contacts</button>
  <button type="button" class="party-more-menu-item @if(!$partyStatusEnabled) is-hidden @endif" id="managePartyStatusOption">Manage Party Status</button>
  <button type="button" class="party-more-menu-item" id="partyStatementReportOption">Party Statement (Report)</button>
  <button type="button" class="party-more-menu-item" id="allPartiesReportOption">All Parties (Report)</button>
</div>

<input type="file" id="partyExcelImportInput" accept=".csv,.xls,.xlsx" hidden>
<input type="file" id="partyPhoneImportInput" accept=".csv,.vcf,text/vcard" hidden>
<input type="file" id="partyContactsImportInput" accept=".vcf,text/vcard,.csv" hidden>

<div class="txn-option-modal" id="partyQrModal">
  <div class="txn-option-backdrop" data-close-party-qr="true"></div>
  <div class="txn-option-dialog">
    <h3 class="txn-option-title">Party QR Code</h3>
    <div style="display:flex;flex-direction:column;align-items:center;gap:14px;">
      <img id="partyQrImage" alt="Party QR Code" style="width:220px;height:220px;border-radius:12px;border:1px solid #e5e7eb;padding:10px;background:#fff;">
      <p id="partyQrText" style="margin:0;text-align:center;color:#6b7280;font-size:13px;"></p>
    </div>
    <div class="txn-option-actions">
      <button type="button" class="txn-option-btn ok" id="partyQrClose">Close</button>
    </div>
  </div>
</div>

<div class="txn-option-modal" id="managePartyStatusModal">
  <div class="txn-option-backdrop" data-close-manage-party-status="true"></div>
  <div class="txn-option-dialog manage-party-status-dialog">
    <div class="manage-party-status-header">
      <div>
        <h3 class="txn-option-title" style="margin-bottom:4px;">Manage Party Status</h3>
        <p class="manage-party-status-subtitle">Set parties active or inactive.</p>
      </div>
      <button type="button" class="manage-party-status-close" id="managePartyStatusClose">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="manage-party-status-search">
      <i class="fa fa-search"></i>
      <input type="text" id="managePartyStatusSearch" class="form-control" placeholder="Search Parties">
    </div>
    <div class="manage-party-status-table">
      <div class="manage-party-status-table-head">
        <div>Parties</div>
        <div>Phone No</div>
        <div>Party Active Status</div>
      </div>
      <div class="manage-party-status-table-body" id="managePartyStatusTableBody">
        <div class="manage-party-status-empty">Loading parties...</div>
      </div>
    </div>
  </div>
</div>

<div class="txn-option-modal" id="partyGroupMoveModal">
  <div class="txn-option-backdrop" data-close-party-group-move="true"></div>
  <div class="txn-option-dialog" style="max-width:620px;">
    <h3 class="txn-option-title">Move Parties To Group</h3>
    <p id="partyGroupMoveTitle" style="margin:0 0 12px;color:#64748b;font-size:14px;">Select parties to move.</p>
    <div class="party-group-move-target">
      <label for="partyGroupMoveTargetSelect" class="form-label fw-600">Move selected parties to</label>
      <select id="partyGroupMoveTargetSelect" class="form-control"></select>
    </div>
    <div class="party-group-move-search">
      <i class="fa fa-search"></i>
      <input type="text" id="partyGroupMoveSearchInput" class="form-control" placeholder="Search parties">
    </div>
    <label class="party-group-move-select-all">
      <input type="checkbox" id="partyGroupMoveSelectAll">
      <span>Select all visible parties</span>
    </label>
    <div class="party-group-move-list" id="partyGroupMoveList"></div>
    <div class="txn-option-actions">
      <button type="button" class="txn-option-btn cancel" id="partyGroupMoveCancel">Cancel</button>
      <button type="button" class="txn-option-btn ok" id="partyGroupMoveSave">Move Selected Parties</button>
    </div>
  </div>
</div>

<div class="modal fade" id="partyTransferModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content party-transfer-modal">
      <div class="modal-header party-transfer-header">
        <h5 class="modal-title">Party To Party Transfer</h5>
        <div class="party-transfer-header-right">
          <div class="party-transfer-date-wrap">
            <label for="partyTransferDate">Date</label>
            <input type="date" id="partyTransferDate" class="form-control" value="{{ date('Y-m-d') }}">
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body party-transfer-body">
        <div class="party-transfer-grid party-transfer-grid-head">
          <div>#</div>
          <div>Entry Type</div>
          <div>Customer Name</div>
          <div>Amount</div>
        </div>

        @for($row = 1; $row <= 2; $row++)
        <div class="party-transfer-grid party-transfer-row" data-transfer-row="{{ $row }}">
          <div class="party-transfer-index">{{ $row }}</div>
          <div>
            <div class="party-transfer-toggle" data-transfer-toggle>
              <button type="button" class="{{ $row === 1 ? 'active' : '' }}" data-transfer-type="received">Received</button>
              <button type="button" class="{{ $row === 2 ? 'active' : '' }}" data-transfer-type="paid">Paid</button>
            </div>
          </div>
          <div>
            <div class="party-transfer-party-select">
              <input type="text" class="form-control transfer-party-input" placeholder="Customer Name" autocomplete="off" data-selected-party-id="">
              <button type="button" class="party-transfer-dropdown-btn"><i class="fa-solid fa-chevron-down"></i></button>
              <div class="party-transfer-party-menu">
                <button type="button" class="party-transfer-add-link" data-bs-toggle="modal" data-bs-target="#addPartyModal">
                  <i class="fa-solid fa-circle-plus"></i> Add Party
                </button>
                <div class="party-transfer-party-list">
                  @foreach($parties as $party)
                    <button type="button" class="party-transfer-party-option" data-party-id="{{ $party->id }}" data-party-name="{{ $party->name }}" data-party-balance="{{ number_format((float) $party->current_balance, 2) }}">
                      <span>{{ $party->name }}</span>
                      <small>{{ number_format((float) $party->current_balance, 2) }}</small>
                    </button>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="party-transfer-balance">Party Balance: <span class="transfer-balance-value">0.00</span></div>
          </div>
          <div>
            <input type="number" min="0" step="0.01" class="form-control transfer-amount-input" placeholder="0.00">
          </div>
        </div>
        @endfor

        <div class="party-transfer-bottom">
          <div class="party-transfer-side-tools">
            <button type="button" class="party-transfer-tool-btn" id="partyTransferDescriptionToggle">
              <i class="fa-regular fa-file-lines"></i> Add Description
            </button>
            <button type="button" class="party-transfer-tool-btn icon-only" id="partyTransferImageTrigger">
              <i class="fa-solid fa-camera"></i>
            </button>
            <input type="file" id="partyTransferImageInput" class="d-none" accept="image/*">
            <div class="party-transfer-extra-panel is-hidden" id="partyTransferDescriptionWrap">
              <textarea id="partyTransferDescription" class="form-control" rows="4" placeholder="Write transfer note or description"></textarea>
            </div>
            <div class="party-transfer-extra-panel is-hidden" id="partyTransferImagePreviewWrap">
              <div class="party-transfer-image-preview-card">
                <img id="partyTransferImagePreview" src="" alt="Transfer attachment preview">
                <button type="button" class="party-transfer-image-remove" id="partyTransferImageRemove">Remove</button>
              </div>
            </div>
          </div>
          <div class="party-transfer-summary">
            <div class="party-transfer-summary-card">
              <span>Total Transfer Amount</span>
              <strong id="partyTransferTotal">Rs 0.00</strong>
            </div>
            <div class="party-transfer-summary-card">
              <span>Selected Parties</span>
              <strong id="partyTransferSelectionCount">0</strong>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer party-transfer-footer">
        <button type="button" class="btn btn-outline-primary" id="partyTransferSaveNew">Save & New</button>
        <button type="button" class="btn btn-primary" id="partyTransferSave">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="partyTxnPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="partyTxnPreviewModalTitle">Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" style="min-height:70vh;">
        <iframe id="partyTxnPreviewFrame" title="Preview" style="width:100%; min-height:70vh; border:0;"></iframe>
      </div>
      <div class="modal-footer justify-content-center gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="partyTxnPreviewOpenPdf">Open PDF</button>
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="partyTxnPreviewPrint">Print</button>
        <button type="button" class="btn btn-outline-success rounded-pill px-4" id="partyTxnPreviewSavePdf">Save PDF</button>
        <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="partyTxnPreviewEmailPdf">Email PDF</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@include('dashboard.partials.document-email-modal', [
  'modalId' => 'documentEmailModal',
  'toId' => 'documentEmailTo',
  'subjectId' => 'documentEmailSubject',
  'messageId' => 'documentEmailMessage',
  'viewPdfBtnId' => 'documentEmailViewPdfBtn',
  'sendBtnId' => 'documentEmailSendBtn',
])

<div class="modal fade" id="partyTxnHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="partyTxnHistoryModalTitle">History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="partyTxnHistoryModalBody">
        <div class="text-muted">Loading...</div>
      </div>
    </div>
  </div>
</div>

<div class="txn-option-modal" id="txnOptionModal">
  <div class="txn-option-backdrop" data-close="true"></div>
  <div class="txn-option-dialog">
    <h3 class="txn-option-title" id="txnOptionTitle">Show Options</h3>
    <div class="txn-option-list">
      <label class="txn-option-item">
        <span>Date</span>
        <input type="checkbox" class="txn-export-column" value="date" checked>
      </label>
      <label class="txn-option-item">
        <span>Type</span>
        <input type="checkbox" class="txn-export-column" value="type" checked>
      </label>
      <label class="txn-option-item">
        <span>Bill No</span>
        <input type="checkbox" class="txn-export-column" value="number" checked>
      </label>
      <label class="txn-option-item">
        <span>Debit</span>
        <input type="checkbox" class="txn-export-column" value="debit" checked>
      </label>
      <label class="txn-option-item">
        <span>Credit</span>
        <input type="checkbox" class="txn-export-column" value="credit" checked>
      </label>
      <label class="txn-option-item">
        <span>Running Balance</span>
        <input type="checkbox" class="txn-export-column" value="running_balance" checked>
      </label>
      <label class="txn-option-item txn-export-extra-item">
        <span>Item Details</span>
        <input type="checkbox" class="txn-export-extra" value="item_details">
      </label>
      <label class="txn-option-item txn-export-extra-item">
        <span>Description</span>
        <input type="checkbox" class="txn-export-extra" value="description">
      </label>
      <label class="txn-option-item txn-export-extra-item">
        <span>Payment Status</span>
        <input type="checkbox" class="txn-export-extra" value="payment_status">
      </label>
      <label class="txn-option-item txn-export-extra-item">
        <span>Payment Information</span>
        <input type="checkbox" class="txn-export-extra" value="payment_information">
      </label>
    </div>
    <div class="txn-option-actions">
      <button type="button" class="txn-option-btn cancel" id="txnOptionCancel">Cancel</button>
      <button type="button" class="txn-option-btn ok" id="txnOptionConfirm">OK</button>
    </div>
  </div>
</div>

<div class="modal fade" id="partyStatementPdfModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">What to display on PDF?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-muted mb-3" id="partyStatementPdfFileName">party_statement.pdf</div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span>Item Details</span>
          <input type="checkbox" id="partyStatementPdfItems">
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span>Description</span>
          <input type="checkbox" id="partyStatementPdfDescription" checked>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span>Payment status</span>
          <input type="checkbox" id="partyStatementPdfPaymentStatus">
        </div>
        <div class="d-flex justify-content-between align-items-center py-2">
          <span>Payment Information</span>
          <input type="checkbox" id="partyStatementPdfPaymentInfo">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="partyStatementPdfApply">Apply</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="partyReminderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Set Reminder</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
          <div>
            <div class="text-muted small text-uppercase fw-semibold">Party</div>
            <div class="fw-bold" id="partyReminderPartyName">-</div>
            <div class="text-muted" id="partyReminderPartyPhone">-</div>
          </div>
          <div class="text-end">
            <div class="text-muted small text-uppercase fw-semibold">Pending Amount</div>
            <div class="fw-bold text-danger" id="partyReminderPartyAmount">Rs 0.00</div>
          </div>
        </div>

        <div class="mb-3 d-flex align-items-center justify-content-between border rounded-3 p-3">
          <div>
            <div class="fw-semibold">Remind me</div>
          </div>
          <input type="checkbox" class="form-check-input m-0" id="partyReminderEnabled">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold" for="partyReminderDate">Reminder Date</label>
          <input type="date" class="form-control" id="partyReminderDate">
        </div>

        <div class="rounded-3 border bg-light p-3 text-muted small">
          Note: You can set payment reminder for yourself on a selected date.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" id="deletePartyReminderBtn">Delete Reminder</button>
        <button type="button" class="btn btn-success" id="savePartyReminderBtn">Done</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="partyReminderWhatsappModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Send Reminder on WhatsApp</h5>
          <div class="text-muted" style="font-size:13px;">Enter the number where the reminder should be sent.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold" for="partyReminderWhatsappPhone">WhatsApp Phone No.</label>
          <input type="text" class="form-control" id="partyReminderWhatsappPhone" placeholder="Enter WhatsApp number">
        </div>
        <div class="text-muted small">The reminder message will use the current party balance and saved reminder template.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="sendPartyReminderWhatsappQuickBtn">
          <i class="fa-brands fa-whatsapp me-1"></i> Send WhatsApp
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="partyLedgerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1" id="partyLedgerModalTitle">Party Ledger</h5>
          <div class="text-muted" style="font-size:13px;">Debit / Credit / Running Balance</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead style="background:#f8fafc;">
              <tr>
                <th>Date</th>
                <th>Transaction ID</th>
                <th>Ref No.</th>
                <th>Type</th>
                <th>Description</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Running Balance</th>
              </tr>
            </thead>
            <tbody id="partyLedgerTableBody">
              <tr>
                <td colspan="8" class="text-center text-muted py-4">Select a party to view payment ledger.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="partyTransferHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1" id="partyTransferHistoryModalTitle">Party Transfer History</h5>
          <div class="text-muted" style="font-size:13px;">Party to party transfer entries only</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead style="background:#f8fafc;">
              <tr>
                <th>Date</th>
                <th>Transaction ID</th>
                <th>Ref No.</th>
                <th>Type</th>
                <th>Counter Party</th>
                <th class="text-end">Amount</th>
                <th>Status</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody id="partyTransferHistoryTableBody">
              <tr>
                <td colspan="8" class="text-center text-muted py-4">Select a party to view transfer history.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@include('dashboard.partials.transaction-passcode-guard')

@endsection

@push('styles')
<style>
  .txn-toolbar {
    display: flex;
    justify-content: flex-end;
    margin: 0 0 14px;
  }

  .header-icons i {
    cursor: pointer;
  }

  .btn-party-transfer {
    background: #eff6ff;
    color: #2563eb;
  }

  .btn-party-transfer:hover {
    background: #dbeafe;
    color: #1d4ed8;
  }

  .party-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .party-item .entity-name {
    flex: 1;
    min-width: 0;
  }

  .party-item[data-is-active="0"] {
    background: #f8fafc;
  }

  .party-inactive-pill {
    flex-shrink: 0;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    color: #b91c1c;
    background: #fee2e2;
  }

  .party-item .entity-balance {
    flex-shrink: 0;
  }

  .party-item-menu-wrap {
    position: relative;
    flex-shrink: 0;
  }

  .party-item-menu-btn {
    border: none;
    background: transparent;
    color: #94a3b8;
    width: 15px;
    height: 8px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .party-item-menu-btn:hover {
    background: #eff6ff;
    color: #2563eb;
  }

  .party-item-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
    padding: 6px;
    display: none;
    z-index: 20;
  }

  .party-item-menu-wrap.open .party-item-menu {
    display: block;
  }

  .party-item-menu-action {
    width: 100%;
    border: none;
    background: transparent;
    text-align: left;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
  }

  .party-item-menu-action:hover {
    background: #fef2f2;
  }

  .txn-option-modal {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1080;
  }

  .txn-option-modal.active {
    display: flex;
  }

  .txn-option-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.28);
    z-index: 0;
  }

  .txn-option-dialog {
    position: relative;
    z-index: 1;
    pointer-events: auto;
    width: min(360px, calc(100vw - 32px));
    background: #fff;
    border-radius: 12px;
    padding: 24px 28px;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.2);
  }

  .manage-party-status-dialog {
    width: min(1050px, calc(100vw - 48px));
    max-width: 1050px;
    padding: 0;
    overflow: hidden;
  }

  .manage-party-status-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 24px 28px 18px;
    border-bottom: 1px solid #e5e7eb;
  }

  .manage-party-status-subtitle {
    margin: 0;
    color: #64748b;
    font-size: 14px;
  }

  .manage-party-status-close {
    border: none;
    background: transparent;
    color: #94a3b8;
    font-size: 22px;
    line-height: 1;
  }

  .manage-party-status-search {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 18px 28px;
    border: 1px solid #dbe2ea;
    border-radius: 999px;
    padding: 0 16px;
    height: 48px;
  }

  .manage-party-status-search i {
    color: #94a3b8;
  }

  .manage-party-status-search input {
    border: none;
    box-shadow: none !important;
    padding: 0;
    height: auto;
  }

  .manage-party-status-table {
    border-top: 1px solid #eef2f7;
  }

  .manage-party-status-table-head,
  .manage-party-status-table-row {
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr) 220px;
    gap: 16px;
    align-items: center;
    padding: 18px 28px;
  }

  .manage-party-status-table-head {
    background: #f8fafc;
    color: #6b7280;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .manage-party-status-table-body {
    max-height: 520px;
    overflow-y: auto;
  }

  .manage-party-status-table-row {
    border-bottom: 1px solid #eef2f7;
    font-size: 15px;
    color: #0f172a;
  }

  .manage-party-status-party {
    font-weight: 600;
  }

  .manage-party-status-phone {
    color: #475569;
  }

  .manage-party-status-empty {
    padding: 42px 28px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
  }

  .manage-party-status-switch {
    position: relative;
    width: 38px;
    height: 22px;
    margin-left: auto;
  }

  .manage-party-status-switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .manage-party-status-slider {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: #cbd5e1;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .manage-party-status-slider::before {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    top: 3px;
    left: 3px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
  }

  .manage-party-status-switch input:checked + .manage-party-status-slider {
    background: #1d8cf8;
  }

  .manage-party-status-switch input:checked + .manage-party-status-slider::before {
    transform: translateX(16px);
  }

  .txn-option-title {
    margin: 0 0 20px;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
  }

  .txn-option-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .txn-option-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    font-size: 15px;
    color: #374151;
  }

  .txn-option-item input[type="checkbox"] {
    width: 22px;
    height: 22px;
    accent-color: #4f46e5;
    cursor: pointer;
  }

  .txn-option-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 28px;
  }

  .txn-option-btn {
    border: none;
    background: transparent;
    padding: 8px 10px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    cursor: pointer;
  }

  .txn-option-btn.cancel,
  .txn-option-btn.ok {
    color: #4f46e5;
  }

  .party-settings-drawer {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 1090;
  }

  .party-settings-drawer.active {
    display: block;
  }

  .party-settings-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.2);
  }

  .party-settings-panel {
    position: absolute;
    top: 0;
    right: 0;
    width: min(380px, 100vw);
    height: 100%;
    background: #fff;
    box-shadow: -10px 0 30px rgba(15, 23, 42, 0.12);
    padding: 24px 22px;
    overflow-y: auto;
  }

  .party-settings-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .party-settings-header h4 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #111827;
  }

  .party-settings-close {
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 20px;
    cursor: pointer;
  }

  .party-settings-group-title {
    background: #f3f4f6;
    color: #374151;
    font-size: 15px;
    font-weight: 600;
    padding: 10px 12px;
    border-radius: 10px;
    margin-bottom: 14px;
  }

  .party-settings-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 12px 4px;
    color: #374151;
    font-size: 15px;
  }

  .party-settings-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #2563eb;
    cursor: pointer;
  }

  .party-settings-info {
    color: #9ca3af;
    font-size: 13px;
  }

  .party-settings-subtext {
    margin: 4px 4px 10px 4px;
    color: #6b7280;
    font-size: 13px;
  }

  .party-settings-reminder-row {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
    margin: 0 4px 16px;
  }

  .party-settings-reminder-input,
  .party-settings-extra-input {
    width: 100%;
    border: none;
    outline: none;
    color: #111827;
    font-size: 15px;
    background: transparent;
  }

  .party-settings-reminder-suffix {
    color: #9ca3af;
    font-size: 14px;
    white-space: nowrap;
  }

  .party-settings-extra-field {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 14px;
    margin-bottom: 14px;
  }

  .party-settings-extra-input {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 14px;
    margin: 0 4px 10px;
    width: calc(100% - 8px);
  }

  .party-settings-switch-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin: 0 6px;
    color: #6b7280;
    font-size: 14px;
  }

  .party-settings-switch {
    appearance: none;
    width: 34px;
    height: 20px;
    border-radius: 999px;
    background: #e5e7eb;
    position: relative;
    cursor: pointer;
    transition: background 0.2s ease;
  }

  .party-settings-switch::after {
    content: "";
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
    transition: transform 0.2s ease;
  }

  .party-settings-switch:checked {
    background: #dbeafe;
  }

  .party-settings-switch:checked::after {
    transform: translateX(14px);
    background: #2563eb;
  }

  .party-settings-more-btn {
    width: 100%;
    border: none;
    background: #fff;
    color: #4b5563;
    border-radius: 14px;
    padding: 14px 16px;
    font-size: 18px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    cursor: pointer;
  }

  .party-transfer-modal {
    border-radius: 18px;
    overflow: hidden;
  }

  .party-transfer-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding: 22px 24px 18px;
    border-bottom: 1px solid #eef2f7;
  }

  .party-transfer-header .modal-title {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
  }

  .party-transfer-header-right {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .party-transfer-source,
  .party-transfer-date-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .party-transfer-source-label,
  .party-transfer-date-wrap label {
    margin: 0;
    font-size: 13px;
    color: #64748b;
    white-space: nowrap;
  }

  .party-transfer-source select,
  .party-transfer-date-wrap input {
    min-width: 180px;
    border-radius: 10px;
  }

  .party-transfer-body {
    padding: 0 0 18px;
  }

  .party-transfer-grid {
    display: grid;
    grid-template-columns: 72px 260px 1fr 180px;
    gap: 18px;
    align-items: start;
  }

  .party-transfer-grid-head {
    padding: 14px 24px;
    border-bottom: 1px solid #eef2f7;
    background: #fafafa;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .party-transfer-row {
    padding: 18px 24px;
    border-bottom: 1px solid #eef2f7;
  }

  .party-transfer-index {
    color: #94a3b8;
    font-size: 18px;
    font-weight: 600;
    padding-top: 8px;
  }

  .party-transfer-toggle {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border-radius: 999px;
    padding: 4px;
    gap: 4px;
  }

  .party-transfer-toggle button {
    border: none;
    background: transparent;
    color: #64748b;
    border-radius: 999px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 700;
  }

  .party-transfer-toggle button.active[data-transfer-type="received"] {
    background: #10b981;
    color: #fff;
  }

  .party-transfer-toggle button.active[data-transfer-type="paid"] {
    background: #ef4444;
    color: #fff;
  }

  .party-transfer-party-select {
    position: relative;
  }

  .party-transfer-dropdown-btn {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #94a3b8;
  }

  .party-transfer-party-menu {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    padding: 10px 0;
    display: none;
    z-index: 30;
  }

  .party-transfer-party-select.open .party-transfer-party-menu {
    display: block;
  }

  .party-transfer-add-link,
  .party-transfer-party-option {
    width: 100%;
    border: none;
    background: transparent;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    text-align: left;
  }

  .party-transfer-add-link {
    color: #2563eb;
    font-weight: 600;
    border-bottom: 1px solid #eef2f7;
  }

  .party-transfer-party-list {
    max-height: 178px;
    overflow-y: auto;
  }

  .party-transfer-party-option:hover,
  .party-transfer-add-link:hover {
    background: #f8fafc;
  }

  .party-transfer-party-option span {
    color: #111827;
    font-size: 14px;
  }

  .party-transfer-party-option small {
    color: #64748b;
    font-size: 12px;
  }

  .party-transfer-balance {
    margin-top: 10px;
    color: #64748b;
    font-size: 13px;
  }

  .party-transfer-balance .transfer-balance-value {
    color: #111827;
    font-weight: 700;
  }

  .party-transfer-bottom {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 280px;
    gap: 20px;
    padding: 20px 24px 0;
  }

  .party-transfer-side-tools {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .party-transfer-tool-btn {
    width: fit-content;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 14px;
    font-weight: 600;
    padding: 0;
  }

  .party-transfer-tool-btn.icon-only {
    font-size: 22px;
  }

  .party-transfer-extra-panel.is-hidden {
    display: none;
  }

  .party-transfer-image-preview-card {
    display: inline-flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #f8fafc;
    width: min(280px, 100%);
  }

  .party-transfer-image-preview-card img {
    width: 100%;
    max-height: 180px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #dbe3ef;
  }

  .party-transfer-image-remove {
    align-self: flex-end;
    border: none;
    background: transparent;
    color: #dc2626;
    font-size: 13px;
    font-weight: 600;
  }

  .party-transfer-summary {
    display: grid;
    gap: 12px;
  }

  .party-transfer-summary-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px;
    background: #f8fafc;
  }

  .party-transfer-summary-card span {
    display: block;
    color: #64748b;
    font-size: 13px;
    margin-bottom: 6px;
  }

  .party-transfer-summary-card strong {
    color: #111827;
    font-size: 22px;
  }

  .party-transfer-footer {
    padding: 0 24px 22px;
    border-top: none;
    gap: 12px;
  }

  .party-group-dropdown {
    position: relative;
  }

  @media (max-width: 991px) {
    .party-transfer-header,
    .party-transfer-header-right {
      flex-direction: column;
      align-items: stretch;
    }

    .party-transfer-grid {
      grid-template-columns: 1fr;
    }

    .party-transfer-grid-head {
      display: none;
    }

    .party-transfer-row {
      border: 1px solid #eef2f7;
      border-radius: 14px;
      margin: 14px 14px 0;
    }

    .party-transfer-bottom {
      grid-template-columns: 1fr;
      padding: 20px 14px 0;
    }
  }

  .party-group-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-align: left;
  }

  .party-group-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #dbe3ea;
    border-radius: 10px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    padding: 8px 0;
    display: none;
    z-index: 15;
  }

  .party-group-menu.active {
    display: block;
  }

  .party-group-options {
    max-height: 160px;
    overflow-y: auto;
  }

  .party-group-add-btn,
  .party-group-option {
    width: 100%;
    border: none;
    background: transparent;
    text-align: left;
    padding: 10px 14px;
    font-size: 15px;
    cursor: pointer;
  }

  .party-group-add-btn {
    color: #2563eb;
    font-weight: 600;
  }

  .party-group-option {
    color: #374151;
  }

  .party-group-add-btn:hover,
  .party-group-option:hover {
    background: #f8fafc;
  }

  .is-hidden {
    display: none !important;
  }

  .header-dropdown-menu .dropdown-item .tick-icon {
    visibility: hidden;
  }

  .header-dropdown-menu .dropdown-item.active .tick-icon {
    visibility: visible;
  }

  .header-dropdown-menu .dropdown-item {
    width: 100%;
    border: none;
    background: transparent;
    text-align: left;
  }

  .party-groups-view {
    margin-top: 14px;
  }

  .party-groups-layout {
    display: grid;
    grid-template-columns: 290px minmax(0, 1fr);
    gap: 0;
    min-height: 640px;
    border: 1px solid #d7e3ef;
    border-radius: 18px;
    overflow: hidden;
    background: #f8fbff;
  }

  .party-groups-sidebar {
    background: #ffffff;
    border-right: 1px solid #dbe5ef;
    display: flex;
    flex-direction: column;
  }

  .party-groups-sidebar-header {
    padding: 18px;
    border-bottom: 1px solid #e7eef6;
  }

  .party-groups-sidebar-title-row,
  .party-groups-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .party-groups-sidebar-title-row h3,
  .party-groups-table-header h4,
  .party-groups-summary-card h3 {
    margin: 0;
    color: #1e293b;
  }

  .party-groups-add-btn {
    border: none;
    border-radius: 999px;
    background: #ff4d6d;
    color: #fff;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
  }

  .party-groups-search {
    margin-top: 14px;
    position: relative;
  }

  .party-groups-search i {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #94a3b8;
  }

  .party-groups-search input {
    width: 100%;
    border: 1px solid #d9e2ec;
    border-radius: 12px;
    padding: 11px 14px 11px 40px;
    outline: none;
    background: #fff;
  }

  .party-groups-sidebar-list {
    padding: 10px;
    overflow-y: auto;
  }

  .party-group-sidebar-item,
  .party-group-party-row {
    display: grid;
    align-items: center;
    gap: 12px;
  }

  .party-group-sidebar-item {
    grid-template-columns: minmax(0, 1fr) auto auto;
    padding: 14px 12px;
    border-radius: 12px;
    cursor: pointer;
    color: #334155;
    margin-bottom: 8px;
  }

  .party-group-sidebar-item.active {
    background: #d9edff;
  }

  .party-group-sidebar-name {
    font-weight: 600;
  }

  .party-group-sidebar-meta,
  .party-groups-summary-label,
  .party-group-empty {
    color: #64748b;
    font-size: 13px;
  }

  .party-group-sidebar-amount,
  .party-group-party-amount,
  .party-groups-summary-stats span:last-child {
    color: #16a34a;
    font-weight: 700;
  }

  .party-group-sidebar-actions,
  .party-group-party-actions {
    position: relative;
  }

  .party-group-action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #64748b;
  }

  .party-group-action-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    min-width: 145px;
    background: #fff;
    border: 1px solid #dbe3ea;
    border-radius: 12px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
    padding: 8px 0;
    display: none;
    z-index: 30;
  }

  .party-group-action-menu.active {
    display: block;
  }

  .party-group-action-menu button {
    width: 100%;
    border: none;
    background: transparent;
    text-align: left;
    padding: 9px 14px;
    color: #334155;
  }

  .party-group-action-menu button:hover {
    background: #f8fafc;
  }

  .party-groups-content {
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .party-groups-summary-card,
  .party-groups-table-card {
    background: #fff;
    border: 1px solid #dbe5ef;
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
  }

  .party-groups-summary-card {
    padding: 18px 22px;
  }

  .party-groups-summary-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
  }

  .party-groups-summary-stats {
    display: flex;
    gap: 16px;
    margin-top: 10px;
    font-weight: 600;
  }

  .party-groups-move-btn {
    border: none;
    border-radius: 10px;
    background: #2f8df6;
    color: #fff;
    padding: 12px 18px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 8px 18px rgba(47, 141, 246, 0.24);
  }

  .party-groups-table-header {
    padding: 18px 22px;
    border-bottom: 1px solid #edf2f7;
  }

  .party-groups-search-right {
    margin-top: 0;
    width: 260px;
  }

  .party-groups-table {
    width: 100%;
    border-collapse: collapse;
  }

  .party-groups-table th,
  .party-groups-table td {
    padding: 16px 18px;
    border-bottom: 1px solid #edf2f7;
    text-align: left;
  }

  .party-groups-table th {
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .party-group-party-row {
    grid-template-columns: minmax(0, 1fr) auto auto;
  }

  .party-group-party-name {
    color: #0f172a;
    font-weight: 600;
  }

  .party-group-empty {
    text-align: center;
    padding: 44px 16px;
  }

  .party-group-move-search {
    position: relative;
    margin-bottom: 14px;
  }

  .party-group-move-target {
    margin-bottom: 12px;
  }

  .party-group-move-search i {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #94a3b8;
  }

  .party-group-move-search input {
    padding-left: 40px;
  }

  .party-group-move-list {
    max-height: 320px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px;
    background: #f8fafc;
  }

  .party-group-move-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 10px;
    border-radius: 10px;
  }

  .party-group-move-item:hover {
    background: #eef6ff;
  }

  .party-group-move-select-all {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    color: #334155;
    font-size: 14px;
    font-weight: 600;
  }

  .party-group-move-meta strong {
    display: block;
    color: #0f172a;
    font-size: 14px;
  }

  .party-group-move-meta span {
    color: #64748b;
    font-size: 12px;
  }

  @media (max-width: 991px) {
    .party-groups-layout {
      grid-template-columns: 1fr;
    }

    .party-groups-sidebar {
      border-right: none;
      border-bottom: 1px solid #dbe5ef;
    }

    .party-groups-search-right {
      width: 100%;
    }

    .party-groups-summary-top,
    .party-groups-table-header,
    .party-groups-sidebar-title-row {
      flex-direction: column;
      align-items: stretch;
    }
  }

  .party-more-menu {
    position: absolute;
    top: 70px;
    right: 24px;
    min-width: 190px;
    width: 190px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 8px 0;
    display: none;
    z-index: 1085;
  }

  .party-more-menu.active {
    display: block;
  }

  .party-more-menu-item {
    width: 100%;
    border: none;
    background: transparent;
    text-align: left;
    padding: 10px 14px;
    font-size: 13px;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
  }

  .party-more-menu-item:hover {
    background: #f3f4f6;
    color: #1f2937;
  }

  [data-party-setting].is-hidden {
    display: none !important;
  }

  .party-txn-action-menu .dropdown-menu,
  .party-txn-action-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    left: auto;
    min-width: 220px;
    width: max-content;
    z-index: 10550;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.16);
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 6px;
    background: #fff;
    display: none;
  }

  .party-txn-action-menu .dropdown-menu li,
  .party-txn-action-dropdown li {
    list-style: none;
  }

  .party-txn-action-menu .dropdown-item,
  .party-txn-action-dropdown .dropdown-item {
    display: flex;
    align-items: center;
    min-height: 32px;
    padding: 7px 10px;
    border-radius: 6px;
    color: #111827;
    font-size: 13px;
    line-height: 1.2;
    text-decoration: none;
    white-space: nowrap;
  }

  .party-txn-action-menu .dropdown-item:hover,
  .party-txn-action-menu .dropdown-item:focus,
  .party-txn-action-dropdown .dropdown-item:hover,
  .party-txn-action-dropdown .dropdown-item:focus {
    background: #f3f4f6;
    color: #111827;
  }

  .party-txn-action-menu .dropdown-item.is-disabled,
  .party-txn-action-menu .dropdown-item.is-disabled:hover,
  .party-txn-action-menu .dropdown-item.is-disabled:focus,
  .party-txn-action-dropdown .dropdown-item.is-disabled,
  .party-txn-action-dropdown .dropdown-item.is-disabled:hover,
  .party-txn-action-dropdown .dropdown-item.is-disabled:focus {
    background: transparent;
    color: #9ca3af;
    cursor: not-allowed;
  }

  .party-txn-action-menu.show .dropdown-menu {
    display: block;
  }

  .party-txn-action-btn {
    border: none;
    background: transparent;
    color: #64748b;
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    padding: 0;
    transition: background-color 0.15s ease, color 0.15s ease;
  }

  .party-txn-action-btn:hover,
  .party-txn-action-btn:focus,
  .party-txn-action-menu.show .party-txn-action-btn {
    background: #eef2f7;
    color: #334155;
  }

  .party-txn-action-menu {
    position: relative;
    display: inline-flex;
    justify-content: flex-end;
  }

  .party-txn-actions-cell {
    position: relative;
    overflow: visible;
    z-index: 5;
  }

  .party-txn-action-menu.show {
    z-index: 4001;
  }

  .party-transactions-panel .table-responsive {
    overflow: visible;
  }

  #partyTxnTable thead th {
    position: relative;
    overflow: visible;
  }

  #partyTxnTable thead .table-main {
    position: relative;
    overflow: visible;
  }

  #partyTxnTable thead .filter-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 220px;
    background: #fff;
    border: 1px solid #dbe3ea;
    border-radius: 12px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
    z-index: 3000;
    display: none;
    padding: 12px;
  }

  #partyTxnTable thead .filter-dropdown .dropdown-options {
    position: absolute;
    left: 12px;
    right: 12px;
    top: calc(100% + 6px);
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    z-index: 3001;
  }
</style>
@endpush
@push('scripts')
<script src="{{ asset('js/document-email-preview.js') }}"></script>
<script>
// ============ GLOBAL FUNCTIONS (filter, sort, dropdown) ============
function toggleFilter(){
    let dropdown = document.getElementById("filterDropdown");
    if(dropdown.style.display === "block"){
        dropdown.style.display = "none";
    }else{
        dropdown.style.display = "block";
    }
}

function toggleHeaderDropdown(element) {
    const headerDropdown = element.closest('.header-dropdown');
    if (!headerDropdown) return;

    const dropdownMenu = headerDropdown.querySelector('.header-dropdown-menu');
    const isVisible = headerDropdown.classList.contains('is-open');

    document.querySelectorAll('.header-dropdown.is-open').forEach((dropdown) => {
        dropdown.classList.remove('is-open');
        const menu = dropdown.querySelector('.header-dropdown-menu');
        const arrow = dropdown.querySelector('.arrow-icon');
        if (menu) menu.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    });

    if (isVisible) {
        return;
    }

    headerDropdown.classList.add('is-open');
    if (dropdownMenu) dropdownMenu.style.display = 'block';
    element.style.transform = 'rotate(180deg)';
}

function closeHeaderDropdown() {
    document.querySelectorAll('.header-dropdown.is-open').forEach((dropdown) => {
        dropdown.classList.remove('is-open');
        const menu = dropdown.querySelector('.header-dropdown-menu');
        const arrow = dropdown.querySelector('.arrow-icon');
        if (menu) menu.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    });
}

function toggleSort(el){
    if (window.event) {
        const clickedInsideFilter = window.event.target?.closest?.('.filter-wrapper, .table-filter-icon, .filter-dropdown');
        if (clickedInsideFilter) {
            return;
        }
    }

    const th = el.closest('th');
    if (!th) return;

    const headers = Array.from(document.querySelectorAll('#partyTxnTable thead th'));
    const headerIndex = headers.indexOf(th);
    const column = ['date', 'type', 'number', 'debit', 'credit', 'running_balance'][headerIndex] || null;
    if (!column) return;

    const currentSort = window.partyTxnSortState || { column: null, direction: 'asc' };
    const nextDirection = currentSort.column === column && currentSort.direction === 'asc' ? 'desc' : 'asc';
    window.partyTxnSortState = { column, direction: nextDirection };

    document.querySelectorAll('#partyTxnTable thead .table-main').forEach((header) => {
        header.classList.remove('active', 'sort-desc');
    });

    el.classList.add('active');
    if (nextDirection === 'desc') {
        el.classList.add('sort-desc');
    }

    if (typeof window.applyPartyTxnRenderedState === 'function') {
        window.applyPartyTxnRenderedState();
    }
}

function toggleParentArrows(el){
    el.classList.toggle('active');
}

window.partySidebarSortState = window.partySidebarSortState || { column: null, direction: 'asc' };

function sortPartySidebar(column, el) {
    if (window.event) {
        const blockedTarget = window.event.target?.closest?.('.filter-icon, .filter-dropdown');
        if (blockedTarget) {
            return;
        }
    }

    const nextDirection = window.partySidebarSortState.column === column && window.partySidebarSortState.direction === 'asc'
        ? 'desc'
        : 'asc';

    window.partySidebarSortState = { column, direction: nextDirection };

    document.querySelectorAll('.filter-toolbar .parent-arrows').forEach((arrow) => {
        arrow.classList.remove('active', 'sort-desc');
    });

    if (el) {
        el.classList.add('active');
        if (nextDirection === 'desc') {
            el.classList.add('sort-desc');
        }
    }

    const partyList = document.getElementById('partiesList');
    if (!partyList) return;

    const rows = Array.from(partyList.querySelectorAll('.party-item'));
    rows.sort((first, second) => {
        let left = '';
        let right = '';

        if (column === 'amount') {
            left = parseFloat(first.dataset.displayAmount || first.dataset.currentBalance || 0);
            right = parseFloat(second.dataset.displayAmount || second.dataset.currentBalance || 0);
        } else {
            left = String(first.dataset.name || '').toLowerCase();
            right = String(second.dataset.name || '').toLowerCase();
        }

        if (left < right) return nextDirection === 'asc' ? -1 : 1;
        if (left > right) return nextDirection === 'asc' ? 1 : -1;
        return 0;
    });

    rows.forEach((row) => partyList.appendChild(row));
}

function toggleFilterDropdown(icon){
    if (window.event) {
        window.event.stopPropagation();
        window.event.preventDefault();
    }

    const dropdown = icon.nextElementSibling || icon.closest('.table-main')?.nextElementSibling;
    if (!dropdown) return;
    dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}

function closePartyTxnActionMenus(exceptMenu = null) {
    document.querySelectorAll('.party-txn-action-menu.show').forEach((menu) => {
        if (exceptMenu && menu === exceptMenu) return;
        menu.classList.remove('show');
        const toggle = menu.querySelector('.party-txn-action-btn');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        const dropdown = menu.__openDropdown || menu.querySelector('.dropdown-menu');
        if (dropdown) {
            dropdown.style.position = '';
            dropdown.style.top = '';
            dropdown.style.left = '';
            dropdown.style.right = '';
            dropdown.style.bottom = '';
            dropdown.style.transform = '';
            dropdown.style.minWidth = '';
            dropdown.style.visibility = '';
            dropdown.style.zIndex = '';
            dropdown.style.display = 'none';
        }
        if (dropdown?.__anchorParent && dropdown.parentNode === document.body) {
            if (dropdown.__anchorNext && dropdown.__anchorNext.parentNode === dropdown.__anchorParent) {
                dropdown.__anchorParent.insertBefore(dropdown, dropdown.__anchorNext);
            } else {
                dropdown.__anchorParent.appendChild(dropdown);
            }
        }
        if (dropdown) {
            dropdown.__anchorParent = null;
            dropdown.__anchorNext = null;
            dropdown.__anchorMenu = null;
        }
        menu.__openDropdown = null;
    });
}

function positionPartyTxnActionDropdown(button, dropdown) {
    const menu = button.closest('.party-txn-action-menu');
    if (!menu) return;

    if (!dropdown.__anchorParent) {
        dropdown.__anchorParent = dropdown.parentNode;
        dropdown.__anchorNext = dropdown.nextSibling;
        dropdown.__anchorMenu = menu;
    }
    menu.__openDropdown = dropdown;
    if (dropdown.parentNode !== document.body) {
        document.body.appendChild(dropdown);
    }

    const rect = button.getBoundingClientRect();
    dropdown.style.display = 'block';
    dropdown.style.visibility = 'hidden';
    dropdown.style.position = 'fixed';
    dropdown.style.right = 'auto';
    dropdown.style.bottom = 'auto';
    dropdown.style.transform = 'none';
    dropdown.style.minWidth = '220px';
    dropdown.style.zIndex = '10550';

    const gap = 6;
    const viewportPadding = 8;
    const dropdownRect = dropdown.getBoundingClientRect();
    const dropdownWidth = dropdownRect.width || 220;
    const dropdownHeight = dropdownRect.height || 320;
    const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
    const openAbove = spaceBelow < dropdownHeight && rect.top > dropdownHeight + gap;
    const top = openAbove ? rect.top - dropdownHeight - gap : rect.bottom + gap;
    const left = Math.max(viewportPadding, Math.min(rect.right - dropdownWidth, window.innerWidth - dropdownWidth - viewportPadding));

    dropdown.style.top = `${Math.max(viewportPadding, top)}px`;
    dropdown.style.left = `${left}px`;
    dropdown.style.visibility = 'visible';
}

function togglePartyTxnActionMenu(event, button) {
    event.preventDefault();
    event.stopPropagation();

    const menu = button.closest('.party-txn-action-menu');
    if (!menu) return;

    const dropdown = menu.querySelector('.dropdown-menu');
    const willOpen = !menu.classList.contains('show');
    if (!willOpen) {
        closePartyTxnActionMenus();
        return;
    }

    closePartyTxnActionMenus(menu);
    menu.classList.add('show');
    button.setAttribute('aria-expanded', 'true');

    if (!dropdown) return;
    positionPartyTxnActionDropdown(button, dropdown);
}

document.addEventListener('click', function(e){
    document.querySelectorAll('.filter-dropdown').forEach(dd=>{
        if(!dd.contains(e.target) && !dd.previousElementSibling?.contains(e.target)){
            dd.style.display = 'none';
        }
    });

    if (!e.target.closest('.party-txn-action-menu')) {
        closePartyTxnActionMenus();
    }

    if (!e.target.closest('.header-dropdown')) {
        closeHeaderDropdown();
    }
});

// ============ MAIN PARTY CRUD ============
document.addEventListener("DOMContentLoaded", function () {

    const saveBtn = document.getElementById("btnSaveParty");
    const saveNewBtn = document.getElementById("btnSaveNewParty");
    const updateBtn = document.getElementById("btnUpdateParty");
    const deleteBtn = document.getElementById("btnDeleteParty");
    const partyList = document.getElementById("partiesList");
    const addModalEl = document.getElementById('addPartyModal');
    const addModal = new bootstrap.Modal(addModalEl);
    const txnToolbar = document.getElementById("txnToolbar");
    const txnSearchToggle = document.getElementById("txnSearchToggle");
    const txnSearchInput = document.getElementById("txnSearchInput");
    const txnPrintTrigger = document.getElementById("txnPrintTrigger");
    const txnExcelTrigger = document.getElementById("txnExcelTrigger");
    const txnPdfTrigger = document.getElementById("txnPdfTrigger");
    const txnWhatsappTrigger = document.getElementById("txnWhatsappTrigger");
    const txnDateFrom = document.getElementById("txnDateFrom");
    const txnDateTo = document.getElementById("txnDateTo");
    const txnDateApply = document.getElementById("txnDateApply");
    const txnDateClear = document.getElementById("txnDateClear");
    const txnOptionModal = document.getElementById("txnOptionModal");
    const txnOptionTitle = document.getElementById("txnOptionTitle");
    const txnOptionCancel = document.getElementById("txnOptionCancel");
    const txnOptionConfirm = document.getElementById("txnOptionConfirm");
    const partyTxnPreviewModalEl = document.getElementById("partyTxnPreviewModal");
    const partyTxnPreviewModal = partyTxnPreviewModalEl ? bootstrap.Modal.getOrCreateInstance(partyTxnPreviewModalEl) : null;
    const partyTxnPreviewFrame = document.getElementById("partyTxnPreviewFrame");
    const partyTxnPreviewModalTitle = document.getElementById("partyTxnPreviewModalTitle");
    const partyTxnPreviewOpenPdfBtn = document.getElementById("partyTxnPreviewOpenPdf");
    const partyTxnPreviewPrintBtn = document.getElementById("partyTxnPreviewPrint");
    const partyTxnPreviewSavePdfBtn = document.getElementById("partyTxnPreviewSavePdf");
    const partyTxnPreviewEmailPdfBtn = document.getElementById("partyTxnPreviewEmailPdf");
    const partyTxnHistoryModalEl = document.getElementById("partyTxnHistoryModal");
    const partyTxnHistoryModal = partyTxnHistoryModalEl ? bootstrap.Modal.getOrCreateInstance(partyTxnHistoryModalEl) : null;
    const partyTxnHistoryModalTitle = document.getElementById("partyTxnHistoryModalTitle");
    const partyTxnHistoryModalBody = document.getElementById("partyTxnHistoryModalBody");
    const partyStatementPdfModalEl = document.getElementById("partyStatementPdfModal");
    const partyStatementPdfModal = partyStatementPdfModalEl ? bootstrap.Modal.getOrCreateInstance(partyStatementPdfModalEl) : null;
    const partyStatementPdfFileName = document.getElementById("partyStatementPdfFileName");
    const partyStatementPdfItems = document.getElementById("partyStatementPdfItems");
    const partyStatementPdfDescription = document.getElementById("partyStatementPdfDescription");
    const partyStatementPdfPaymentStatus = document.getElementById("partyStatementPdfPaymentStatus");
    const partyStatementPdfPaymentInfo = document.getElementById("partyStatementPdfPaymentInfo");
    const partyStatementPdfApply = document.getElementById("partyStatementPdfApply");
    const partyStatementEmailComposer = window.DocumentEmailPreview?.init({
        name: 'party-statement-email-preview',
        previewModalId: 'partyTxnPreviewModal',
        previewFrameId: 'partyTxnPreviewFrame',
        emailModalId: 'documentEmailModal',
        emailToId: 'documentEmailTo',
        emailSubjectId: 'documentEmailSubject',
        emailMessageId: 'documentEmailMessage',
        viewPdfBtnId: 'documentEmailViewPdfBtn',
        sendBtnId: 'documentEmailSendBtn',
        openButtonId: 'partyTxnPreviewEmailPdf',
        toastId: 'documentEmailToast',
        defaultSubject: (context) => `Party Statement PDF${context.partyName ? ' - ' + context.partyName : ''}`,
        defaultMessage: (context) => {
            const pdfLink = context.pdfUrl || context.previewUrl || '';
            return `Dear ${context.partyName || 'Sir'},\n\nPlease find the party statement PDF attached below.\n${pdfLink ? 'PDF Link: ' + pdfLink + '\n' : ''}\nThank you for doing business with us.\nThanks and regards.`;
        },
    });
    const partyReminderModalEl = document.getElementById("partyReminderModal");
    const partyReminderModal = partyReminderModalEl ? bootstrap.Modal.getOrCreateInstance(partyReminderModalEl) : null;
    const partyReminderPartyName = document.getElementById("partyReminderPartyName");
    const partyReminderPartyPhone = document.getElementById("partyReminderPartyPhone");
    const partyReminderPartyAmount = document.getElementById("partyReminderPartyAmount");
    const partyReminderEnabled = document.getElementById("partyReminderEnabled");
    const partyReminderPhone = document.getElementById("partyReminderPhone");
    const partyReminderDate = document.getElementById("partyReminderDate");
    const partyReminderMessage = document.getElementById("partyReminderMessage");
    const sendPartyReminderWhatsappBtn = document.getElementById("sendPartyReminderWhatsappBtn");
    const savePartyReminderBtn = document.getElementById("savePartyReminderBtn");
    const deletePartyReminderBtn = document.getElementById("deletePartyReminderBtn");
    const partyReminderWhatsappModalEl = document.getElementById("partyReminderWhatsappModal");
    const partyReminderWhatsappModal = partyReminderWhatsappModalEl ? bootstrap.Modal.getOrCreateInstance(partyReminderWhatsappModalEl) : null;
    const partyReminderWhatsappPhone = document.getElementById("partyReminderWhatsappPhone");
    const sendPartyReminderWhatsappQuickBtn = document.getElementById("sendPartyReminderWhatsappQuickBtn");
    const partySettingsTrigger = document.getElementById("partySettingsTrigger");
    const partyModalSettingsTrigger = document.getElementById("partyModalSettingsTrigger");
    const partySettingsDrawer = document.getElementById("partySettingsDrawer");
    const partySettingsClose = document.getElementById("partySettingsClose");
    const partyMoreOptionsTrigger = document.getElementById("partyMoreOptionsTrigger");
    const partyMoreMenu = document.getElementById("partyMoreMenu");
    const partyExcelImportInput = document.getElementById("partyExcelImportInput");
    const partyPhoneImportInput = document.getElementById("partyPhoneImportInput");
    const partyContactsImportInput = document.getElementById("partyContactsImportInput");
    const importExcelOption = document.getElementById("importExcelOption");
    const importPhoneOption = document.getElementById("importPhoneOption");
    const importContactsOption = document.getElementById("importContactsOption");
    const partyStatementReportOption = document.getElementById("partyStatementReportOption");
    const allPartiesReportOption = document.getElementById("allPartiesReportOption");
    const managePartyStatusOption = document.getElementById("managePartyStatusOption");
    const partyQrModal = document.getElementById("partyQrModal");
    const partyQrImage = document.getElementById("partyQrImage");
    const partyQrText = document.getElementById("partyQrText");
    const partyQrClose = document.getElementById("partyQrClose");
    const managePartyStatusModal = document.getElementById("managePartyStatusModal");
    const managePartyStatusClose = document.getElementById("managePartyStatusClose");
    const managePartyStatusSearch = document.getElementById("managePartyStatusSearch");
    const managePartyStatusTableBody = document.getElementById("managePartyStatusTableBody");
    const partyViewDropdownTrigger = document.getElementById("partyViewDropdownTrigger");
    const partyViewDropdownMenu = document.querySelector(".header-dropdown-menu");
    const splitPane = document.querySelector(".split-pane");
    const partyGroupsView = document.getElementById("partyGroupsView");
    const partyGroupsViewOption = document.getElementById("partyGroupsViewOption");
    const partyGroupsSidebarList = document.getElementById("partyGroupsSidebarList");
    const selectedPartyGroupName = document.getElementById("selectedPartyGroupName");
    const selectedPartyGroupCount = document.getElementById("selectedPartyGroupCount");
    const selectedPartyGroupAmount = document.getElementById("selectedPartyGroupAmount");
    const partyGroupPartiesTableBody = document.getElementById("partyGroupPartiesTableBody");
    const partyGroupSearchInput = document.getElementById("partyGroupSearchInput");
    const partyGroupPartySearchInput = document.getElementById("partyGroupPartySearchInput");
    const partyGroupsAddBtn = document.getElementById("partyGroupsAddBtn");
    const movePartiesToGroupBtn = document.getElementById("movePartiesToGroupBtn");
    const partyGroupMoveModal = document.getElementById("partyGroupMoveModal");
    const partyGroupMoveTitle = document.getElementById("partyGroupMoveTitle");
    const partyGroupMoveList = document.getElementById("partyGroupMoveList");
    const partyGroupMoveSearchInput = document.getElementById("partyGroupMoveSearchInput");
    const partyGroupMoveTargetSelect = document.getElementById("partyGroupMoveTargetSelect");
    const partyGroupMoveSelectAll = document.getElementById("partyGroupMoveSelectAll");
    const partyGroupMoveCancel = document.getElementById("partyGroupMoveCancel");
    const partyGroupMoveSave = document.getElementById("partyGroupMoveSave");
    const partyGroupInput = document.getElementById("partyGroupInput");
    const partyGroupTrigger = document.getElementById("partyGroupTrigger");
    const partyGroupTriggerText = document.getElementById("partyGroupTriggerText");
    const partyGroupMenu = document.getElementById("partyGroupMenu");
    const partyGroupOptions = document.getElementById("partyGroupOptions");
    const openPartyGroupModal = document.getElementById("openPartyGroupModal");
    const partyGroupModal = document.getElementById("partyGroupModal");
    const partyGroupNameInput = document.getElementById("partyGroupNameInput");
    const partyGroupCancel = document.getElementById("partyGroupCancel");
    const partyGroupSave = document.getElementById("partyGroupSave");
    const creditLimitAmountWrap = document.getElementById("creditLimitAmountWrap");
    const creditLimitAmountInput = document.getElementById("creditLimitAmountInput");
    const partyReminderDays = document.getElementById("partyReminderDays");
    const partyAdditionalField1Name = document.getElementById("partyAdditionalField1Name");
    const partyAdditionalField1Print = document.getElementById("partyAdditionalField1Print");
    const partyAdditionalField2Name = document.getElementById("partyAdditionalField2Name");
    const partyAdditionalField2Print = document.getElementById("partyAdditionalField2Print");
    const partyTransferModalEl = document.getElementById("partyTransferModal");
    const partyTransferModal = partyTransferModalEl ? bootstrap.Modal.getOrCreateInstance(partyTransferModalEl) : null;
    const partyTransferSave = document.getElementById("partyTransferSave");
    const partyTransferSaveNew = document.getElementById("partyTransferSaveNew");
    const partyTransferDate = document.getElementById("partyTransferDate");
    const partyTransferTotal = document.getElementById("partyTransferTotal");
    const partyTransferSelectionCount = document.getElementById("partyTransferSelectionCount");
    const partyTransferDescriptionToggle = document.getElementById("partyTransferDescriptionToggle");
    const partyTransferDescriptionWrap = document.getElementById("partyTransferDescriptionWrap");
    const partyTransferDescriptionInput = document.getElementById("partyTransferDescription");
    const partyTransferImageTrigger = document.getElementById("partyTransferImageTrigger");
    const partyTransferImageInput = document.getElementById("partyTransferImageInput");
    const partyTransferImagePreviewWrap = document.getElementById("partyTransferImagePreviewWrap");
    const partyTransferImagePreview = document.getElementById("partyTransferImagePreview");
    const partyTransferImageRemove = document.getElementById("partyTransferImageRemove");
    const openLedgerModalBtn = document.getElementById("openLedgerModalBtn");
    const openTransferHistoryModalBtn = document.getElementById("openTransferHistoryModalBtn");
    const partyLedgerModalEl = document.getElementById("partyLedgerModal");
    const partyLedgerModal = partyLedgerModalEl ? bootstrap.Modal.getOrCreateInstance(partyLedgerModalEl) : null;
    const partyLedgerTableBody = document.getElementById("partyLedgerTableBody");
    const partyLedgerModalTitle = document.getElementById("partyLedgerModalTitle");
    const partyTransferHistoryModalEl = document.getElementById("partyTransferHistoryModal");
    const partyTransferHistoryModal = partyTransferHistoryModalEl ? bootstrap.Modal.getOrCreateInstance(partyTransferHistoryModalEl) : null;
    const partyTransferHistoryTableBody = document.getElementById("partyTransferHistoryTableBody");
    const partyTransferHistoryModalTitle = document.getElementById("partyTransferHistoryModalTitle");
    const appCompanyName = @json(config('app.name', 'My Company'));

    let currentPartyId = null;
    let currentPartyView = 'parties';
    let selectedPartyGroupNameValue = 'General';
    let selectedMovePartyIds = [];
    let transactionsState = [];
    let filteredTransactionsState = [];
    let pendingTxnAction = null;
    const PARTY_GROUPS_STORAGE_KEY = 'partyGroups';
    const existingPartyGroups = Array.from(document.querySelectorAll('.party-item'))
        .map((item) => (item.dataset.partyGroup || '').trim())
        .filter(Boolean);
    const serverPartyGroups = @json($partyGroups->map(fn ($group) => ['id' => $group->id, 'name' => $group->name])->values());
    let partyGroups = Array.from(new Set(['General', ...serverPartyGroups.map(group => group.name), ...existingPartyGroups]))
        .map((name) => {
            const matchedGroup = serverPartyGroups.find(group => group.name === name);
            return {
                id: matchedGroup?.id || null,
                name
            };
        });
    let managePartyStatusRows = [];
    @php
      $partySettingsData = $partySettings ?? [
        'party_grouping' => true,
        'shipping_address' => true,
        'print_shipping_address' => true,
        'party_status' => true,
        'payment_reminder' => true,
        'payment_reminder_days' => 2,
        'additional_field_1' => false,
        'additional_field_1_name' => '',
        'additional_field_1_print' => false,
        'additional_field_2' => false,
        'additional_field_2_name' => '',
        'additional_field_2_print' => false,
      ];
    @endphp
    let partySettingsState = @json($partySettingsData);
    const exportColumns = [
        { key: 'date', label: 'Date' },
        { key: 'type', label: 'Type' },
        { key: 'number', label: 'Bill No' },
        { key: 'debit', label: 'Debit' },
        { key: 'credit', label: 'Credit' },
        { key: 'running_balance', label: 'Running Balance' }
    ];
    let transactionColumnFilters = {
        date: { operator: 'equal_to', value: '', value_to: '' },
        type: { values: [] },
        number: { operator: 'contains', value: '' },
        debit: { operator: 'equal_to', value: '' },
        credit: { operator: 'equal_to', value: '' },
        running_balance: { operator: 'equal_to', value: '' }
    };
    let transactionDateRange = { from: '', to: '' };
    const partyFilterDropdown = document.getElementById("filterDropdown");
    const partyFilterApply = document.getElementById("partyFilterApply");
    const partyFilterClear = document.getElementById("partyFilterClear");
    const partySearchInput = document.getElementById("partySearchInput");
    const partyFilterInputs = partyFilterDropdown
        ? Array.from(partyFilterDropdown.querySelectorAll('input[data-party-filter]'))
        : [];

    function parseBalance(value) {
        const numeric = parseFloat(String(value || '').replace(/,/g, ''));
        return Number.isFinite(numeric) ? numeric : 0;
    }

    function getSelectedPartyFilters() {
        if (!partyFilterInputs.length) return ['all'];
        const selected = partyFilterInputs.filter(input => input.checked).map(input => input.dataset.partyFilter);
        if (!selected.length || selected.includes('all')) {
            return ['all'];
        }
        return selected.filter(value => value !== 'all');
    }

    function isPartyInactive(li) {
        return String(li?.dataset?.isActive || '1') !== '1';
    }

    function updateManagePartyStatusVisibility() {
        managePartyStatusOption?.classList.toggle('is-hidden', !partySettingsState.party_status);
    }

    function applyPartyFilters() {
        if (!partyList) return;
        const selectedFilters = getSelectedPartyFilters();
        const query = (partySearchInput?.value || '').trim().toLowerCase();
        const partyItems = Array.from(partyList.querySelectorAll('.party-item'));
        let firstVisible = null;

        partyItems.forEach(li => {
            const balance = parseBalance(li.dataset.currentBalance);
            const transactionType = String(li.dataset.transactionType || '').toLowerCase();
            const name = String(li.dataset.name || '').toLowerCase();
            const phone = String(li.dataset.phone || '').toLowerCase();
            const isActive = !isPartyInactive(li);
            const isReceive = balance > 0 || transactionType === 'receive';
            const isPay = balance < 0 || transactionType === 'pay';
            const matchesSearch = !query || name.includes(query) || phone.includes(query);

            const matchesFilter = selectedFilters.includes('all') || selectedFilters.some(filter => {
                if (filter === 'active') return isActive;
                if (filter === 'inactive') return !isActive;
                if (filter === 'receive') return isReceive;
                if (filter === 'pay') return isPay;
                return false;
            });

            const shouldShow = matchesSearch && matchesFilter;

            li.style.display = shouldShow ? '' : 'none';
            if (shouldShow && !firstVisible) {
                firstVisible = li;
            }
        });

        const activeLi = currentPartyId
            ? partyList.querySelector(`.party-item[data-id="${currentPartyId}"]`)
            : null;

        if (!activeLi || activeLi.style.display === 'none') {
            document.querySelectorAll('.party-item').forEach(item => item.classList.remove('active'));
            currentPartyId = null;

            if (firstVisible) {
                firstVisible.dispatchEvent(new MouseEvent('click', { bubbles: true }));
            } else {
                showTxnMessage('fa-solid fa-receipt', 'No parties found', 'Try a different filter');
            }
        }
    }

    // Checkbox mutually exclusive
    const toReceive = document.getElementById('toReceive');
    const toPay = document.getElementById('toPay');
    const creditLimitSwitch = document.getElementById("creditLimitSwitch");
    const transactionTypeValue = document.getElementById('transactionTypeValue');

    [toReceive, toPay].forEach(checkbox => {
      if (!checkbox) return;
      checkbox.addEventListener('change', function () {
        if (this.checked) {
          [toReceive, toPay].forEach(cb => {
            if (cb !== this && cb) cb.checked = false;
          });
          if (transactionTypeValue) transactionTypeValue.value = this.value;
        } else {
          if (transactionTypeValue) transactionTypeValue.value = '';
        }
      });
    });
    creditLimitSwitch?.addEventListener('change', syncCreditLimitVisibility);

    if (partyFilterInputs.length) {
        const allInput = partyFilterInputs.find(input => input.dataset.partyFilter === 'all');

        partyFilterInputs.forEach(input => {
            input.addEventListener('change', function () {
                if (this.dataset.partyFilter === 'all' && this.checked) {
                    partyFilterInputs.forEach(other => {
                        if (other !== this) other.checked = false;
                    });
                    return;
                }

                if (this.dataset.partyFilter !== 'all' && this.checked && allInput) {
                    allInput.checked = false;
                }
            });
        });

        partyFilterApply?.addEventListener('click', function () {
            applyPartyFilters();
            if (partyFilterDropdown) partyFilterDropdown.style.display = 'none';
        });

        partyFilterClear?.addEventListener('click', function () {
            partyFilterInputs.forEach(input => {
                input.checked = input.dataset.partyFilter === 'all';
            });
            applyPartyFilters();
            if (partyFilterDropdown) partyFilterDropdown.style.display = 'none';
        });
    }

    partySearchInput?.addEventListener('input', function () {
        applyPartyFilters();
    });

    document.querySelectorAll('[data-transfer-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', function (event) {
            const button = event.target.closest('[data-transfer-type]');
            if (!button) return;
            const currentRow = button.closest('.party-transfer-row');
            const currentRowNumber = Number(currentRow?.dataset.transferRow || 0);
            const oppositeRow = document.querySelector(`.party-transfer-row[data-transfer-row="${currentRowNumber === 1 ? 2 : 1}"]`);
            const selectedType = button.dataset.transferType;
            const oppositeType = selectedType === 'received' ? 'paid' : 'received';

            toggle.querySelectorAll('[data-transfer-type]').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');

            if (oppositeRow) {
                oppositeRow.querySelectorAll('[data-transfer-type]').forEach((item) => item.classList.remove('active'));
                oppositeRow.querySelector(`[data-transfer-type="${oppositeType}"]`)?.classList.add('active');
            }
        });
    });

    document.querySelectorAll('.party-transfer-dropdown-btn').forEach((button) => {
        button.addEventListener('click', function () {
            const select = button.closest('.party-transfer-party-select');
            const shouldOpen = !select.classList.contains('open');
            closeAllTransferMenus();
            if (shouldOpen) {
                select.classList.add('open');
            }
        });
    });

    document.querySelectorAll('.party-transfer-party-option').forEach((option) => {
        option.addEventListener('click', function () {
            const select = option.closest('.party-transfer-party-select');
            const input = select.querySelector('.transfer-party-input');
            const balance = select.parentElement.querySelector('.transfer-balance-value');
            if (input) {
                input.value = option.dataset.partyName || '';
                input.dataset.selectedPartyId = option.dataset.partyId || '';
            }
            if (balance) balance.textContent = option.dataset.partyBalance || '0.00';
            select.classList.remove('open');
            updatePartyTransferSummary();
        });
    });

    document.querySelectorAll('.transfer-party-input').forEach((input) => {
        input.addEventListener('focus', function () {
            closeAllTransferMenus();
            input.closest('.party-transfer-party-select')?.classList.add('open');
        });

        input.addEventListener('input', function () {
            const query = input.value.trim().toLowerCase();
            const select = input.closest('.party-transfer-party-select');
            input.dataset.selectedPartyId = '';
            const balance = select?.parentElement.querySelector('.transfer-balance-value');
            if (balance) balance.textContent = '0.00';
            select?.querySelectorAll('.party-transfer-party-option').forEach((option) => {
                option.style.display = (option.dataset.partyName || '').toLowerCase().includes(query) ? '' : 'none';
            });
            updatePartyTransferSummary();
        });
    });

    document.querySelectorAll('.transfer-amount-input').forEach((input) => {
        input.addEventListener('input', updatePartyTransferSummary);
    });

    document.querySelectorAll('.party-transfer-add-link').forEach((button) => {
        button.addEventListener('click', function () {
            partyTransferModal?.hide();
            closeAllTransferMenus();
        });
    });

    partyTransferModalEl?.addEventListener('show.bs.modal', resetPartyTransferModal);
    partyTransferSave?.addEventListener('click', () => persistPartyTransfer(true));
    partyTransferSaveNew?.addEventListener('click', () => persistPartyTransfer(false));
    partyTransferDescriptionToggle?.addEventListener('click', togglePartyTransferDescription);
    partyTransferImageTrigger?.addEventListener('click', () => partyTransferImageInput?.click());
    partyTransferImageInput?.addEventListener('change', handlePartyTransferImageSelection);
    partyTransferImageRemove?.addEventListener('click', clearPartyTransferImage);

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.party-transfer-party-select')) {
            closeAllTransferMenus();
        }
    });

    // Clear filter buttons
    document.querySelectorAll('.clear-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const checkboxes = this.closest('.filter-dropdown')?.querySelectorAll('input[type="checkbox"]');
            if (checkboxes) checkboxes.forEach(cb => cb.checked = false);
        });
    });

    // RESET MODAL
    function resetModal() {
        document.getElementById("addPartyForm").reset();
        renderPartyGroupOptions();
        syncCreditLimitVisibility();
        saveBtn.style.display = "inline-block";
        saveNewBtn.style.display = "inline-block";
        updateBtn.style.display = "none";
        deleteBtn.style.display = "none";
        currentPartyId = null;
    }

    function closeAllTransferMenus() {
        document.querySelectorAll('.party-transfer-party-select.open').forEach((menu) => {
            menu.classList.remove('open');
        });
    }

    function updatePartyTransferSummary() {
        let total = 0;
        let selected = 0;

        document.querySelectorAll('.transfer-amount-input').forEach((input) => {
            total += Number(input.value || 0);
        });

        document.querySelectorAll('.transfer-party-input').forEach((input) => {
            if ((input.value || '').trim()) {
                selected += 1;
            }
        });

        if (partyTransferTotal) {
            partyTransferTotal.textContent = `Rs ${total.toFixed(2)}`;
        }

        if (partyTransferSelectionCount) {
            partyTransferSelectionCount.textContent = String(selected);
        }
    }

    function togglePartyTransferDescription(forceVisible = null) {
        if (!partyTransferDescriptionWrap) return;

        const shouldShow = forceVisible === null
            ? partyTransferDescriptionWrap.classList.contains('is-hidden')
            : forceVisible;

        partyTransferDescriptionWrap.classList.toggle('is-hidden', !shouldShow);

        if (shouldShow) {
            partyTransferDescriptionInput?.focus();
        }
    }

    function clearPartyTransferImage() {
        if (partyTransferImageInput) {
            partyTransferImageInput.value = '';
        }

        if (partyTransferImagePreview) {
            partyTransferImagePreview.src = '';
        }

        partyTransferImagePreviewWrap?.classList.add('is-hidden');
    }

    function handlePartyTransferImageSelection(event) {
        const file = event.target.files?.[0];

        if (!file) {
            clearPartyTransferImage();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (loadEvent) {
            if (partyTransferImagePreview) {
                partyTransferImagePreview.src = loadEvent.target?.result || '';
            }
            partyTransferImagePreviewWrap?.classList.remove('is-hidden');
        };
        reader.readAsDataURL(file);
    }

    function resetPartyTransferModal() {
        if (!partyTransferModalEl) return;

        if (partyTransferDate) {
            partyTransferDate.value = "{{ date('Y-m-d') }}";
        }

        const descriptionBox = document.getElementById("partyTransferDescription");
        if (descriptionBox) {
            descriptionBox.value = '';
        }
        togglePartyTransferDescription(false);
        clearPartyTransferImage();

        document.querySelectorAll('.party-transfer-row').forEach((row, index) => {
            const partyInput = row.querySelector('.transfer-party-input');
            const amountInput = row.querySelector('.transfer-amount-input');
            const balanceLabel = row.querySelector('.transfer-balance-value');

            if (partyInput) {
                partyInput.value = '';
                partyInput.dataset.selectedPartyId = '';
            }
            if (amountInput) amountInput.value = '';
            if (balanceLabel) balanceLabel.textContent = '0.00';

            row.querySelectorAll('[data-transfer-type]').forEach((button) => button.classList.remove('active'));
            const defaultType = index === 0 ? 'received' : 'paid';
            row.querySelector(`[data-transfer-type="${defaultType}"]`)?.classList.add('active');
        });

        closeAllTransferMenus();
        updatePartyTransferSummary();
    }

    // GET FORM DATA
    function getPartyData() {
        const selectedPartyTypes = Array.from(document.querySelectorAll('input[name="party_type[]"]:checked'))
            .map((input) => input.value);
        const customFieldInputs = Array.from(document.querySelectorAll('#partyAdditionalPane input[type="text"][name="custom_fields[]"]'));
        const customFieldChecks = Array.from(document.querySelectorAll('#partyAdditionalPane .form-check-input[type="checkbox"]'));
        const customFields = customFieldInputs
            .map((input, index) => {
                const label = String(input?.value || '').trim();
                const enabled = !!customFieldChecks[index]?.checked;
                return enabled || label ? label : '';
            })
            .filter(Boolean);

        return {
            name: document.getElementById("partyNameInput").value,
            phone: document.getElementById("partyPhoneInput").value,
            phone_number_2: document.getElementById("partyPhone2Input").value,
            ptcl_number: document.getElementById("partyPtclInput").value,
            party_group: partyGroupInput?.value || '',
            email: document.querySelector('#partyAddressPane input[type="email"]').value,
            city: document.getElementById("partyCityInput").value,
            address: document.getElementById("partyAddressInput").value,
            billing_address: document.getElementById("billingAddress").value,
            shipping_address: document.getElementById("shippingAddress").value,
            due_days: document.getElementById("partyDueDaysInput")?.value || '',
            opening_balance: document.querySelector('#partyCreditPane input[type="number"]').value,
            as_of_date: document.querySelector('#partyCreditPane input[type="date"]').value,
            credit_limit_enabled: document.getElementById("creditLimitSwitch").checked ? 1 : 0,
            credit_limit_amount: creditLimitAmountInput?.value || '',
            transaction_type: document.getElementById("toReceive").checked
                ? 'receive'
                : document.getElementById("toPay").checked
                    ? 'pay'
                    : null,
            party_type: selectedPartyTypes,
            custom_fields: customFields,
        };
    }

    function formatTxnType(rawType) {
        if (rawType === 'pay') return 'Payable Opening Balance';
        if (rawType === 'receive') return 'Receivable Opening Balance';
        return rawType || '-';
    }

    function formatTxnStatus(rawStatus) {
        const normalizedStatusText = (rawStatus || '').toLowerCase();

        if (normalizedStatusText === 'receive') return 'To Receive';
        if (normalizedStatusText === 'pay') return 'To Pay';
        if (['paid', 'completed', 'closed', 'converted'].includes(normalizedStatusText)) return 'Paid';
        if (['partial', 'pending', 'confirmed'].includes(normalizedStatusText)) return rawStatus;
        return rawStatus || 'Open';
    }

    function getTransactionExportRows() {
        return filteredTransactionsState.map(txn => ({
            date: txn.date || '-',
            type: formatTxnType(txn.type),
            number: txn.number || '-',
            debit: `Rs ${txn.debit ?? 0}`,
            credit: `Rs ${txn.credit ?? 0}`,
            running_balance: `Rs ${txn.running_balance ?? 0}`,
            description: txn.description || '',
            payment_status_text: txn.payment_status_text || formatTxnStatus(txn.status),
            item_details: txn.item_details || [],
            payment_information: txn.payment_information || []
        }));
    }

    function renderPaymentLedgerTable(rows, partyName = 'Party') {
        if (!partyLedgerTableBody) return;

        if (partyLedgerModalTitle) {
            partyLedgerModalTitle.textContent = `${partyName} Payment Ledger`;
        }

        if (!rows.length) {
            partyLedgerTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No payment ledger entries found.</td>
                </tr>
            `;
            return;
        }

        partyLedgerTableBody.innerHTML = rows.map((row) => `
            <tr>
                <td>${escapeHtml(row.date || '-')}</td>
                <td>${escapeHtml(row.id || '-')}</td>
                <td>${escapeHtml(row.number || '-')}</td>
                <td>${escapeHtml(row.type || '-')}</td>
                <td>${escapeHtml(row.description || '-')}</td>
                <td class="text-end">${escapeHtml(row.debit || '0.00')}</td>
                <td class="text-end">${escapeHtml(row.credit || '0.00')}</td>
                <td class="text-end">${escapeHtml(row.running_balance || row.balance || '0.00')}</td>
            </tr>
        `).join('');
    }

    function openLedgerModal() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        if (partyLedgerTableBody) {
            partyLedgerTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Loading payment ledger...</td>
                </tr>
            `;
        }

        partyLedgerModal?.show();

        fetch(`/dashboard/parties/${currentPartyId}/ledger`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    throw new Error('Unable to load payment ledger.');
                }

                renderPaymentLedgerTable(Array.isArray(data.ledger) ? data.ledger : [], data.party_name || 'Party');
            })
            .catch(error => {
                console.error('Payment Ledger Load Error:', error);
                if (partyLedgerTableBody) {
                    partyLedgerTableBody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-danger py-4">Unable to load payment ledger.</td>
                        </tr>
                    `;
                }
            });
    }

    function renderTransferHistoryTable(rows, partyName = 'Party') {
        if (!partyTransferHistoryTableBody) return;

        if (partyTransferHistoryModalTitle) {
            partyTransferHistoryModalTitle.textContent = `${partyName} Transfer History`;
        }

        if (!rows.length) {
            partyTransferHistoryTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No transfer history found.</td>
                </tr>
            `;
            return;
        }

        partyTransferHistoryTableBody.innerHTML = rows.map((row) => `
            <tr>
                <td>${escapeHtml(row.date || '-')}</td>
                <td>${escapeHtml(row.id || '-')}</td>
                <td>${escapeHtml(row.ref_no || '-')}</td>
                <td>${escapeHtml(row.type || '-')}</td>
                <td>${escapeHtml(row.counter_party || '-')}</td>
                <td class="text-end">${escapeHtml(row.amount || '0.00')}</td>
                <td>${escapeHtml(row.status || '-')}</td>
                <td>${escapeHtml(row.description || '-')}</td>
            </tr>
        `).join('');
    }

    function openTransferHistoryModal() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        if (partyTransferHistoryTableBody) {
            partyTransferHistoryTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Loading transfer history...</td>
                </tr>
            `;
        }

        partyTransferHistoryModal?.show();

        fetch(`/dashboard/parties/${currentPartyId}/transfer-history`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    throw new Error('Unable to load transfer history.');
                }

                renderTransferHistoryTable(Array.isArray(data.transfers) ? data.transfers : [], data.party_name || 'Party');
            })
            .catch(error => {
                console.error('Transfer History Load Error:', error);
                if (partyTransferHistoryTableBody) {
                    partyTransferHistoryTableBody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-danger py-4">Unable to load transfer history.</td>
                        </tr>
                    `;
                }
            });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function persistPartyTransfer(closeAfterSave = true) {
        const rows = Array.from(document.querySelectorAll('.party-transfer-row')).map((row) => {
            const partyInput = row.querySelector('.transfer-party-input');
            return {
                party_id: partyInput?.dataset.selectedPartyId || '',
                party_name: partyInput?.value.trim() || '',
                type: row.querySelector('[data-transfer-type].active')?.dataset.transferType || '',
                amount: Number(row.querySelector('.transfer-amount-input')?.value || 0),
            };
        }).filter((row) => row.party_name || row.amount > 0);

        if (!rows.length) {
            alert('Please fill at least one transfer row.');
            return;
        }

        const invalidRow = rows.find((row) => !row.party_id || row.amount <= 0);
        if (invalidRow) {
            alert('Please select a valid party and enter amount for each transfer row.');
            return;
        }

        const paidRows = rows.filter((row) => row.type === 'paid');
        const receivedRows = rows.filter((row) => row.type === 'received');

        if (paidRows.length !== 1 || receivedRows.length !== 1) {
            alert('One party must be Paid and one party must be Received.');
            return;
        }

        if (String(paidRows[0].party_id) === String(receivedRows[0].party_id)) {
            alert('Paid party and Received party cannot be same.');
            return;
        }

        if (Number(paidRows[0].amount) !== Number(receivedRows[0].amount)) {
            alert('Paid amount and Received amount must be equal.');
            return;
        }

        const formData = new FormData();
        formData.append('transfer_date', partyTransferDate?.value || '');
        formData.append('description', partyTransferDescriptionInput?.value || '');
        formData.append('rows', JSON.stringify(rows.map(({ party_id, type, amount }) => ({ party_id, type, amount }))));

        if (partyTransferImageInput?.files?.[0]) {
            formData.append('attachment', partyTransferImageInput.files[0]);
        }

        fetch("{{ route('parties.transfer.store') }}", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data?.message || 'Transfer save failed.');
            }
            return data;
        })
        .then((data) => {
            alert(data.message || 'Party transfer saved successfully.');

            if (closeAfterSave) {
                partyTransferModal?.hide();
            }

            resetPartyTransferModal();
            window.location.reload();
        })
        .catch((error) => {
            console.error('Party Transfer Error:', error);
            alert(error.message || 'Unable to save party transfer.');
        });
    }

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                ...(options.headers || {}),
            },
            ...options,
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data?.message || 'Request failed.');
        }

        return data;
    }

    function openPartyTxnPreview(url, title, options = {}) {
        if (!url) {
            alert('Preview is not available for this transaction.');
            return;
        }

        if (!partyTxnPreviewModal || !partyTxnPreviewFrame) {
            window.open(options.pdfUrl || url, '_blank');
            return;
        }

        partyTxnPreviewModalTitle.textContent = title || 'Preview';
        partyTxnPreviewFrame.src = url;
        partyTxnPreviewFrame.dataset.pdfUrl = options.pdfUrl || url;
        partyTxnPreviewFrame.dataset.printUrl = options.printUrl || '';
        if (partyTxnPreviewOpenPdfBtn) {
            partyTxnPreviewOpenPdfBtn.disabled = !(options.pdfUrl || url);
        }
        if (partyTxnPreviewPrintBtn) {
            partyTxnPreviewPrintBtn.disabled = !(options.printUrl || url);
        }
        partyTxnPreviewModal.show();
    }

    function openPartyTxnHistory(title, rows) {
        if (!partyTxnHistoryModal || !partyTxnHistoryModalBody) {
            return;
        }

        partyTxnHistoryModalTitle.textContent = title || 'History';

        if (!rows.length) {
            partyTxnHistoryModalBody.innerHTML = `<div class="text-muted">No records found.</div>`;
            partyTxnHistoryModal.show();
            return;
        }

        const tableRows = rows.map((row, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${escapeHtml(row.bank_name || row.bank || '-')}</td>
                <td>${escapeHtml(row.transaction_type || row.type || '-')}</td>
                <td>${escapeHtml(row.amount || '-')}</td>
                <td>${escapeHtml(row.reference_no || row.reference || '-')}</td>
                <td>${escapeHtml(row.payment_date || row.created_at || '-')}</td>
            </tr>
        `).join('');

        partyTxnHistoryModalBody.innerHTML = `
            <div class="table-responsive">
                <table class="table table-bordered table-sm history-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Bank</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </div>
        `;

        partyTxnHistoryModal.show();
    }

    partyTxnPreviewOpenPdfBtn?.addEventListener('click', function () {
        const pdfUrl = partyTxnPreviewFrame?.dataset?.pdfUrl || partyTxnPreviewFrame?.src;
        if (!pdfUrl) {
            alert('Preview is not available for this transaction.');
            return;
        }
        window.open(pdfUrl, '_blank');
    });

    partyTxnPreviewPrintBtn?.addEventListener('click', function () {
        const previewMode = partyTxnPreviewFrame?.dataset?.previewMode || '';
        if (previewMode === 'party-statement' && partyTxnPreviewFrame?.contentWindow) {
            try {
                partyTxnPreviewFrame.contentWindow.focus();
                partyTxnPreviewFrame.contentWindow.print();
                return;
            } catch (error) {
                console.warn('Preview frame print failed, falling back to opening the PDF.', error);
            }
        }

        const printUrl = partyTxnPreviewFrame?.dataset?.printUrl || partyTxnPreviewFrame?.dataset?.pdfUrl || partyTxnPreviewFrame?.src;
        if (!printUrl) {
            alert('Print is not available for this transaction.');
            return;
        }
        window.open(printUrl, '_blank');
    });

    partyTxnPreviewSavePdfBtn?.addEventListener('click', function () {
        const downloadUrl = partyTxnPreviewFrame?.dataset?.downloadUrl || partyTxnPreviewFrame?.dataset?.pdfUrl || partyTxnPreviewFrame?.src;
        if (!downloadUrl) {
            alert('Save PDF is not available for this transaction.');
            return;
        }
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.target = '_blank';
        link.rel = 'noopener';
        document.body.appendChild(link);
        link.click();
        link.remove();
    });

    partyTxnPreviewModalEl?.addEventListener('hidden.bs.modal', function () {
        if (partyTxnPreviewFrame) {
            partyTxnPreviewFrame.removeAttribute('srcdoc');
            partyTxnPreviewFrame.src = 'about:blank';
            delete partyTxnPreviewFrame.dataset.pdfUrl;
            delete partyTxnPreviewFrame.dataset.downloadUrl;
            delete partyTxnPreviewFrame.dataset.printUrl;
            delete partyTxnPreviewFrame.dataset.previewUrl;
            delete partyTxnPreviewFrame.dataset.emailUrl;
            delete partyTxnPreviewFrame.dataset.partyEmail;
            delete partyTxnPreviewFrame.dataset.partyName;
            delete partyTxnPreviewFrame.dataset.saleNumber;
            delete partyTxnPreviewFrame.dataset.documentLabel;
            delete partyTxnPreviewFrame.dataset.previewMode;
        }
    });

    function getPartyTxnActionsHtml(txn) {
        const actions = txn.actions || {};
        const actionItem = (action, label, url) => {
            const className = url ? 'dropdown-item' : 'dropdown-item is-disabled';
            const disabledAttrs = url ? '' : ' aria-disabled="true" tabindex="-1"';
            return `<li><a class="${className}" href="#" data-action="${action}"${disabledAttrs}>${label}</a></li>`;
        };

        return `
            <div class="dropdown party-txn-action-menu"
                 data-id="${escapeHtml(txn.id)}"
                 data-type="${escapeHtml(txn.raw_type || '')}"
                 data-number="${escapeHtml(txn.number || '-')}"
                 data-view-url="${escapeHtml(actions.view || '')}"
                 data-delete-url="${escapeHtml(actions.delete || '')}"
                 data-cancel-url="${escapeHtml(actions.cancel || '')}"
                 data-duplicate-url="${escapeHtml(actions.duplicate || '')}"
                 data-pdf-url="${escapeHtml(actions.pdf || '')}"
                 data-preview-url="${escapeHtml(actions.preview || '')}"
                 data-print-url="${escapeHtml(actions.print || '')}"
                 data-preview-delivery-url="${escapeHtml(actions.preview_delivery || '')}"
                 data-convert-return-url="${escapeHtml(actions.convert_return || '')}"
                 data-history-url="${escapeHtml(actions.history || '')}">
              <button class="btn btn-sm party-txn-action-btn" type="button" onclick="togglePartyTxnActionMenu(event, this)" aria-expanded="false">
                <i class="fa-solid fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end party-txn-action-dropdown">
                ${actionItem('view', 'View / Edit', actions.view)}
                ${actionItem('delete', 'Delete', actions.delete)}
                ${actionItem('cancel', 'Cancel', actions.cancel)}
                ${actionItem('duplicate', 'Duplicate', actions.duplicate)}
                ${actionItem('pdf', 'Open PDF', actions.pdf)}
                ${actionItem('preview', 'Preview', actions.preview)}
                ${actionItem('print', 'Print', actions.print)}
                ${actionItem('preview-delivery', 'Preview Delivery Challan', actions.preview_delivery)}
                ${actionItem('convert-return', 'Convert to Return', actions.convert_return)}
                ${actionItem('history', 'View History', actions.history)}
              </ul>
            </div>
        `;
    }

    function getPartyTxnTableBody() {
        let tbody = document.getElementById("txnTableBody") || document.querySelector("#partyTxnTable tbody");
        if (!tbody) {
            const table = document.getElementById("partyTxnTable");
            if (!table) return null;
            tbody = document.createElement("tbody");
            tbody.id = "txnTableBody";
            table.appendChild(tbody);
        }
        return tbody;
    }

    function showTxnMessage(iconClass, title, subtitle) {
        const tbody = getPartyTxnTableBody();
        if (!tbody) return;
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center" style="padding: 40px;">
                    <i class="${iconClass}" style="font-size: 40px; color: #d1d5db;"></i>
                    <p class="mt-2" style="color: #6b7280;">${title}</p>
                    <p style="font-size: 12px; color: #9ca3af;">${subtitle}</p>
                </td>
            </tr>
        `;
    }

    function normalizeTxnFilterLabel(value) {
        const normalized = String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');

        return ({
            'sale order': 'sale order',
            'sale order ': 'sale order',
            'sale(e-invoice)': 'sale',
            'credit note(e-invoice)': 'credit note',
            'performance invoice': 'proforma invoice',
            'payment-in': 'payment in',
            'payment-out': 'payment out',
        })[normalized] || normalized.replace(/-/g, ' ');
    }

    function getTxnTypeAliases(txn) {
        const visibleType = normalizeTxnFilterLabel(txn.type || '');
        const formattedType = normalizeTxnFilterLabel(formatTxnType(txn.type));
        const rawType = normalizeTxnFilterLabel(txn.raw_type || '');
        const aliases = new Set([visibleType, formattedType]);

        if (rawType) {
            aliases.add(rawType);
        }

        if (rawType === 'invoice') aliases.add('sale');
        if (rawType === 'estimate') aliases.add('estimate');
        if (rawType === 'sale_return') {
            aliases.add('sale return');
            aliases.add('credit note');
        }
        if (rawType === 'sale_order') aliases.add('sale order');
        if (rawType === 'proforma') {
            aliases.add('proforma invoice');
            aliases.add('performance invoice');
        }
        if (rawType === 'delivery_challan') aliases.add('delivery challan');
        if (rawType === 'pay') aliases.add('payable opening balance');
        if (rawType === 'receive') aliases.add('receivable opening balance');

        return aliases;
    }

    function hasActiveTransactionFilters() {
        return Boolean(
            transactionDateRange.from ||
            transactionDateRange.to ||
            (transactionColumnFilters.type.values || []).length ||
            (transactionColumnFilters.number.value || '').trim() ||
            (transactionColumnFilters.date.value || '').trim() ||
            (transactionColumnFilters.date.value_to || '').trim() ||
            (transactionColumnFilters.debit.value || '').trim() ||
            (transactionColumnFilters.debit.value_to || '').trim() ||
            (transactionColumnFilters.credit.value || '').trim() ||
            (transactionColumnFilters.credit.value_to || '').trim() ||
            (transactionColumnFilters.running_balance.value || '').trim() ||
            (transactionColumnFilters.running_balance.value_to || '').trim()
        );
    }

    function parseTxnDate(value) {
        const raw = String(value || '').trim();
        if (!raw) return null;

        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
            const parsed = new Date(`${raw}T00:00:00`);
            return Number.isNaN(parsed.getTime()) ? null : parsed.getTime();
        }

        const parts = raw.split('/');
        if (parts.length === 3) {
            const [day, month, year] = parts.map((part) => parseInt(part, 10));
            const parsed = new Date(year, (month || 1) - 1, day || 1);
            return Number.isNaN(parsed.getTime()) ? null : parsed.getTime();
        }

        const parsed = new Date(raw);
        return Number.isNaN(parsed.getTime()) ? null : parsed.getTime();
    }

    function applyTransactionDateRangeFilter(rows) {
        const from = transactionDateRange.from ? parseTxnDate(transactionDateRange.from) : null;
        const to = transactionDateRange.to ? parseTxnDate(transactionDateRange.to) : null;

        if (!from && !to) {
            return rows;
        }

        return rows.filter((txn) => {
            const current = parseTxnDate(txn.date || '');
            if (current === null) return false;
            if (from !== null && current < from) return false;
            if (to !== null && current > to) return false;
            return true;
        });
    }

    function buildPartyStatementPdfUrl(download = false) {
        if (!currentPartyId) return null;

        const params = new URLSearchParams();
        if (transactionDateRange.from) params.set('from', transactionDateRange.from);
        if (transactionDateRange.to) params.set('to', transactionDateRange.to);
        if (partyStatementPdfItems?.checked) params.set('item_details', '1');
        if (partyStatementPdfDescription?.checked) params.set('description', '1');
        if (partyStatementPdfPaymentStatus?.checked) params.set('payment_status', '1');
        if (partyStatementPdfPaymentInfo?.checked) params.set('payment_information', '1');
        if (download) params.set('download', '1');

        return `/dashboard/parties/${currentPartyId}/statement-pdf?${params.toString()}`;
    }

    function buildPartyStatementEmailUrl() {
        if (!currentPartyId) return null;

        const params = new URLSearchParams();
        if (transactionDateRange.from) params.set('from', transactionDateRange.from);
        if (transactionDateRange.to) params.set('to', transactionDateRange.to);
        if (partyStatementPdfItems?.checked) params.set('item_details', '1');
        if (partyStatementPdfDescription?.checked) params.set('description', '1');
        if (partyStatementPdfPaymentStatus?.checked) params.set('payment_status', '1');
        if (partyStatementPdfPaymentInfo?.checked) params.set('payment_information', '1');

        return `/dashboard/parties/${currentPartyId}/statement-email?${params.toString()}`;
    }

    function parseTxnNumber(value) {
        const numeric = parseFloat(String(value ?? '').replace(/[^0-9.-]/g, ''));
        return Number.isFinite(numeric) ? numeric : 0;
    }

    function normalizeTxnOperator(value) {
        const normalized = String(value || '').trim().toLowerCase();

        return ({
            'contains': 'contains',
            'exact match': 'exact_match',
            'equal to': 'equal_to',
            'less than': 'less_than',
            'greater than': 'greater_than',
            'range': 'range'
        })[normalized] || 'contains';
    }

    function matchesTxnTextFilter(value, filter) {
        const expected = String(filter?.value || '').trim().toLowerCase();
        if (!expected) return true;

        const source = String(value || '').trim().toLowerCase();
        return filter?.operator === 'exact_match'
            ? source === expected
            : source.includes(expected);
    }

    function matchesTxnNumericFilter(value, filter) {
        const expected = String(filter?.value || '').trim();
        if (!expected) return true;

        const source = parseTxnNumber(value);
        const first = parseTxnNumber(expected);
        const second = parseTxnNumber(filter?.value_to || '');

        if (filter?.operator === 'less_than') return source < first;
        if (filter?.operator === 'greater_than') return source > first;
        if (filter?.operator === 'range') return source >= Math.min(first, second) && source <= Math.max(first, second);

        return source === first;
    }

    function matchesTxnDateFilter(value, filter) {
        const expected = String(filter?.value || '').trim();
        if (!expected) return true;

        const source = parseTxnDate(value);
        const first = parseTxnDate(expected);
        const second = parseTxnDate(filter?.value_to || '');

        if (source === null || first === null) return false;
        if (filter?.operator === 'less_than') return source < first;
        if (filter?.operator === 'greater_than') return source > first;
        if (filter?.operator === 'range' && second !== null) return source >= Math.min(first, second) && source <= Math.max(first, second);

        return source === first;
    }

    function applyTransactionColumnFilters(rows) {
        const typeValues = transactionColumnFilters.type.values || [];
        if (typeValues.length && rows.length) {
            console.log('🔍 DEBUG TYPE FILTER:');
            console.log('Selected filter values:', typeValues);
            console.log('Normalized filter values:', typeValues.map(v => normalizeTxnFilterLabel(v)));
            console.log('First 3 transactions:');
            rows.slice(0, 3).forEach((txn, idx) => {
                const aliases = getTxnTypeAliases(txn);
                const normalizedFilters = typeValues.map(v => normalizeTxnFilterLabel(v));
                const doesMatch = normalizedFilters.some(fval => aliases.has(fval));
                console.log(`  [${idx}] type="${txn.type}" raw_type="${txn.raw_type}" → aliases=${JSON.stringify(Array.from(aliases))} → matches=${doesMatch}`);
            });
        }

        return rows.filter((txn) => {
            const typeValues = transactionColumnFilters.type.values || [];
            if (typeValues.length) {
                const aliases = getTxnTypeAliases(txn);
                const normalizedFilters = typeValues.map(v => normalizeTxnFilterLabel(v));
                const hasMatch = normalizedFilters.some(fval => aliases.has(fval));
                if (!hasMatch) {
                    return false;
                }
            }

            if (!matchesTxnTextFilter(txn.number || '-', transactionColumnFilters.number)) return false;
            if (!matchesTxnDateFilter(txn.date || '', transactionColumnFilters.date)) return false;
            if (!matchesTxnNumericFilter(txn.debit || 0, transactionColumnFilters.debit)) return false;
            if (!matchesTxnNumericFilter(txn.credit || 0, transactionColumnFilters.credit)) return false;
            if (!matchesTxnNumericFilter(txn.running_balance || 0, transactionColumnFilters.running_balance)) return false;

            return true;
        });
    }

    function ensureTxnStatusFilter() {
        return;
    }

    function resetTransactionFilters(resetUi = true, preserveDateRange = false) {
        transactionColumnFilters = {
            date: { operator: 'equal_to', value: '', value_to: '' },
            type: { values: [] },
            number: { operator: 'contains', value: '' },
            debit: { operator: 'equal_to', value: '' },
            credit: { operator: 'equal_to', value: '' },
            running_balance: { operator: 'equal_to', value: '' }
        };

        if (txnSearchInput) {
            txnSearchInput.value = '';
        }
        if (!preserveDateRange) {
            transactionDateRange = { from: '', to: '' };
            if (txnDateFrom) txnDateFrom.value = '';
            if (txnDateTo) txnDateTo.value = '';
        }

        if (!resetUi) {
            return;
        }

        document.querySelectorAll('#partyTxnTable thead .filter-dropdown').forEach((dropdown) => {
            dropdown.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                checkbox.checked = false;
            });
            dropdown.querySelectorAll('input[type="text"], input[type="date"]').forEach((input) => {
                input.value = '';
            });
            const rangeInput = dropdown.querySelector('.txn-range-input');
            if (rangeInput) {
                rangeInput.value = '';
                rangeInput.style.display = 'none';
            }
        });
    }

    function initializeTransactionFilterControls() {
        ensureTxnStatusFilter();

        const table = document.getElementById('partyTxnTable');
        const dropdowns = table ? Array.from(table.querySelectorAll('thead .filter-dropdown')) : [];
        const columns = ['date', 'type', 'number', 'debit', 'credit', 'running_balance'];

        dropdowns.forEach((dropdown, index) => {
            const column = columns[index];
            if (!column) return;

            const dropdownInput = dropdown.querySelector('.dropdown-input');
            const dropdownOptions = dropdown.querySelector('.dropdown-options');

            if (dropdownInput && dropdownOptions && !dropdownInput.dataset.bound) {
                dropdownInput.dataset.bound = '1';
                dropdownInput.addEventListener('click', function (event) {
                    event.stopPropagation();
                    dropdownOptions.style.display = dropdownOptions.style.display === 'block' ? 'none' : 'block';
                });

                dropdownOptions.querySelectorAll('.dropdown-option').forEach((option) => {
                    option.addEventListener('click', function (event) {
                        event.stopPropagation();
                        dropdownInput.value = this.textContent.trim();
                        dropdownOptions.style.display = 'none';

                        if (column === 'date') {
                            const rangeInput = dropdown.querySelector('.txn-range-input');
                            if (rangeInput) {
                                rangeInput.style.display = normalizeTxnOperator(dropdownInput.value) === 'range' ? 'block' : 'none';
                            }
                        }
                    });
                });
            }

            if (column === 'type') {
                dropdown.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                    if (!checkbox.value) {
                        const label = checkbox.closest('label');
                        checkbox.value = (label ? label.textContent : '').trim();
                    }

                    if (!checkbox.dataset.liveFilterBound) {
                        checkbox.dataset.liveFilterBound = '1';
                        checkbox.addEventListener('change', function (event) {
                            event.stopPropagation();
                            saveTxnFilterFromDropdown(column, dropdown);
                            applyTransactionSearch();
                        });
                    }
                });
            }

            if (column === 'date' && !dropdown.querySelector('.txn-range-input')) {
                const firstDateInput = dropdown.querySelector('input[type="date"]');
                if (firstDateInput) {
                    const rangeInput = document.createElement('input');
                    rangeInput.type = 'date';
                    rangeInput.className = 'txn-range-input';
                    rangeInput.style.cssText = 'border:1px solid #d9dfe5; border-radius:6px; height:5vh; color:#9ca3af; padding:6px; margin-top:8px; display:none;';
                    firstDateInput.insertAdjacentElement('afterend', rangeInput);
                }
            }

            if (!dropdown.dataset.buttonsBound) {
                dropdown.dataset.buttonsBound = '1';

                dropdown.querySelector('.apply-btn')?.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    saveTxnFilterFromDropdown(column, dropdown);
                    dropdown.style.display = 'none';
                    applyTransactionSearch();
                });

                dropdown.querySelector('.clear-btn')?.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (column === 'type') {
                        dropdown.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                            checkbox.checked = false;
                        });
                        transactionColumnFilters[column] = { values: [] };
                    } else {
                        dropdown.querySelectorAll('input[type="text"], input[type="date"]').forEach((input) => {
                            input.value = '';
                        });
                        const rangeInput = dropdown.querySelector('.txn-range-input');
                        if (rangeInput) {
                            rangeInput.style.display = 'none';
                        }

                        transactionColumnFilters[column] = column === 'number'
                            ? { operator: 'contains', value: '' }
                            : { operator: 'equal_to', value: '', value_to: '' };
                    }

                    dropdown.style.display = 'none';
                    applyTransactionSearch();
                });
            }
        });
    }

    function initializePartyTxnColumnResize() {
        const table = document.getElementById('partyTxnTable');
        if (!table || table.dataset.resizeBound === '1') {
            return;
        }

        const headers = Array.from(table.querySelectorAll('thead th'));
        if (!headers.length) {
            return;
        }

        table.dataset.resizeBound = '1';

        const minWidths = [84, 108, 112, 92, 92, 122, 68];

        const applyWidthToColumn = (columnIndex, width) => {
            table.querySelectorAll(`tr > *:nth-child(${columnIndex + 1})`).forEach((cell) => {
                cell.style.width = `${width}px`;
                cell.style.minWidth = `${width}px`;
                cell.style.maxWidth = `${width}px`;
            });
        };

        headers.forEach((th, index) => {
            if (index >= headers.length - 1) {
                return;
            }

            let handle = th.querySelector('.txn-col-resize-handle');
            if (!handle) {
                handle = document.createElement('div');
                handle.className = 'txn-col-resize-handle';
                th.appendChild(handle);
            }

            const existingWidth = parseFloat(window.getComputedStyle(th).width) || th.getBoundingClientRect().width || minWidths[index] || 80;
            const baseWidth = Math.max(minWidths[index] || 80, Math.round(existingWidth));
            applyWidthToColumn(index, baseWidth);

            let startX = 0;
            let startWidth = 0;
            let resizing = false;

            const onMouseMove = (event) => {
                if (!resizing) return;
                const nextWidth = Math.max(minWidths[index] || 80, startWidth + (event.clientX - startX));
                applyWidthToColumn(index, nextWidth);
            };

            const stopResize = () => {
                if (!resizing) return;
                resizing = false;
                handle.classList.remove('is-resizing');
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', stopResize);
            };

            handle.addEventListener('mousedown', function (event) {
                event.preventDefault();
                event.stopPropagation();

                resizing = true;
                handle.classList.add('is-resizing');
                startX = event.clientX;
                startWidth = th.getBoundingClientRect().width || baseWidth;

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', stopResize);
            });
        });
    }

    function getTxnFilterColumnFromDropdown(dropdown) {
        const th = dropdown?.closest('th');
        if (!th) return null;

        const allHeaders = Array.from(document.querySelectorAll('#partyTxnTable thead th'));
        const headerIndex = allHeaders.indexOf(th);

        return ['date', 'type', 'number', 'debit', 'credit', 'running_balance'][headerIndex] || null;
    }

    function saveTxnFilterFromDropdown(column, dropdown) {
        if (!column || !dropdown) return;

        if (column === 'type') {
            transactionColumnFilters[column] = {
                values: Array.from(dropdown.querySelectorAll('input[type="checkbox"]:checked'))
                    .map((checkbox) => String(checkbox.value || '').trim())
                    .filter(Boolean)
            };
            return;
        }

        const operatorInput = dropdown.querySelector('.dropdown-input');
        const valueInput = column === 'date'
            ? dropdown.querySelector('input[type="date"]:not(.txn-range-input)')
            : dropdown.querySelector('input[type="text"]:not(.dropdown-input)');
        const rangeInput = dropdown.querySelector('.txn-range-input');

        transactionColumnFilters[column] = {
            operator: normalizeTxnOperator(operatorInput?.value || (column === 'number' ? 'Contains' : 'Equal To')),
            value: valueInput?.value?.trim?.() || '',
            value_to: rangeInput?.value?.trim?.() || ''
        };
    }

    function clearTxnFilterFromDropdown(column, dropdown) {
        if (!column || !dropdown) return;

        if (column === 'type' || column === 'status') {
            dropdown.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                checkbox.checked = false;
            });
            transactionColumnFilters[column] = { values: [] };
            return;
        }

        dropdown.querySelectorAll('input[type="text"], input[type="date"]').forEach((input) => {
            input.value = '';
        });

        const rangeInput = dropdown.querySelector('.txn-range-input');
        if (rangeInput) {
            rangeInput.value = '';
            rangeInput.style.display = 'none';
        }

        transactionColumnFilters[column] = column === 'number'
            ? { operator: 'contains', value: '' }
            : { operator: 'equal_to', value: '', value_to: '' };
    }

    function sortTransactions(rows) {
        const sortState = window.partyTxnSortState || { column: null, direction: 'asc' };
        if (!sortState.column) {
            return [...rows];
        }

        const direction = sortState.direction === 'desc' ? -1 : 1;
        const sorted = [...rows];

        sorted.sort((a, b) => {
            let first = '';
            let second = '';

            if (sortState.column === 'type') {
                first = normalizeTxnFilterLabel(a.type || '');
                second = normalizeTxnFilterLabel(b.type || '');
            } else if (sortState.column === 'number') {
                first = String(a.number || '').toLowerCase();
                second = String(b.number || '').toLowerCase();
            } else if (sortState.column === 'date') {
                first = parseTxnDate(a.date || '') || 0;
                second = parseTxnDate(b.date || '') || 0;
            } else if (sortState.column === 'debit') {
                first = parseTxnNumber(a.debit || 0);
                second = parseTxnNumber(b.debit || 0);
            } else if (sortState.column === 'credit') {
                first = parseTxnNumber(a.credit || 0);
                second = parseTxnNumber(b.credit || 0);
            } else if (sortState.column === 'running_balance') {
                first = parseTxnNumber(a.running_balance || 0);
                second = parseTxnNumber(b.running_balance || 0);
            }

            if (first < second) return -1 * direction;
            if (first > second) return 1 * direction;
            return 0;
        });

        return sorted;
    }

    function renderTransactionsTable(transactions) {
        const tbody = getPartyTxnTableBody();
        if (!tbody) return;
        const sortedTransactions = sortTransactions(transactions);
        const openingBalanceSeen = new Set();
        const dedupedTransactions = sortedTransactions.filter((txn) => {
            const rawType = String(txn?.raw_type || txn?.type || '').toLowerCase();
            const number = String(txn?.number || '').trim().toUpperCase();
            const isOpeningBalance = ['receive', 'pay'].includes(rawType) && number.startsWith('TXN');

            if (!isOpeningBalance) {
                return true;
            }

            const openingKey = `${rawType}|${number || 'txn'}`;
            if (openingBalanceSeen.has(openingKey)) {
                return false;
            }

            openingBalanceSeen.add(openingKey);
            return true;
        });
        filteredTransactionsState = [...dedupedTransactions];

        if (!dedupedTransactions.length) {
            const hasSearch = txnSearchInput && txnSearchInput.value.trim() !== '';
            const hasFilters = hasActiveTransactionFilters();
            showTxnMessage(
                'fa-solid fa-receipt',
                (hasSearch || hasFilters) ? 'No matching transactions' : 'No transactions yet',
                (hasSearch || hasFilters) ? 'Try a different filter or search keyword' : 'Create a sale or purchase for this party'
            );
            return;
        }

        tbody.innerHTML = '';

        dedupedTransactions.forEach(txn => {
            const row = document.createElement('tr');
            const viewUrl = txn.actions?.view || '';
            const typeText = txn.type ? formatTxnType(txn.type) : '';
            const cleanTypeColors = {
                'Receivable Opening Balance': { bg: '#f8fafc', color: '#475569' },
                'Payable Opening Balance': { bg: '#f8fafc', color: '#475569' },
                'Sale': { bg: '#eff6ff', color: '#2563eb' },
                'Purchase': { bg: '#fffbeb', color: '#d97706' },
                'Estimate': { bg: '#fff7ed', color: '#ea580c' },
                'Sale Order': { bg: '#ecfeff', color: '#0891b2' },
                'Proforma Invoice': { bg: '#f5f3ff', color: '#7c3aed' },
                'Delivery Challan': { bg: '#ecfdf5', color: '#15803d' },
                'Credit Note': { bg: '#fef2f2', color: '#dc2626' },
                'POS': { bg: '#fdf2f8', color: '#be185d' },
            };
            const cleanTypeStyle = cleanTypeColors[typeText] || { bg: '#f8fafc', color: '#334155' };
            const cleanTypeBadge = typeText
                ? `<span style="display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;background:${cleanTypeStyle.bg};color:${cleanTypeStyle.color};font-size:12px;font-weight:600;white-space:nowrap;">${typeText}</span>`
                : `<span style="color:#94a3b8;">-</span>`;
            const cleanBalanceNumber = parseFloat(String(txn.running_balance || 0).replace(/,/g, '')) || 0;
            const cleanBalanceColor = cleanBalanceNumber < 0 ? '#dc2626' : '#16a34a';

            row.innerHTML = `
                <td style="background:#fff;color:#64748b;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;">${txn.date}</td>
                <td style="background:#fff;color:#334155;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;">${cleanTypeBadge}</td>
                <td style="background:#fff;color:#64748b;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;">${txn.number || '-'}</td>
                <td style="background:#fff;color:#475569;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;font-weight:500;text-align:right;">${parseTxnNumber(txn.debit || 0) > 0 ? `Rs ${txn.debit}` : '-'}</td>
                <td style="background:#fff;color:#475569;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;font-weight:500;text-align:right;">${parseTxnNumber(txn.credit || 0) > 0 ? `Rs ${txn.credit}` : '-'}</td>
                <td style="background:#fff;color:${cleanBalanceColor};font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;font-weight:600;text-align:right;">Rs ${txn.running_balance}</td>
        <td class="party-txn-actions-cell" style="background:#fff;position:relative;overflow:visible;padding:14px 16px;border-bottom:1px solid #eef2f7;">${getPartyTxnActionsHtml(txn)}</td>
            `;

            if (viewUrl) {
                row.dataset.viewUrl = viewUrl;
                row.style.cursor = 'pointer';
                row.title = 'Double click to open edit';
                row.addEventListener('dblclick', function (event) {
                    if (event.target.closest('.party-txn-action-menu')) {
                        return;
                    }
                    window.location.href = viewUrl;
                });
            }

            tbody.appendChild(row);
        });
    }

    function applyTransactionSearch() {
        const keyword = txnSearchInput ? txnSearchInput.value.trim().toLowerCase() : '';
        const dateFilteredRows = applyTransactionDateRangeFilter(transactionsState);
        const columnFilteredRows = applyTransactionColumnFilters(dateFilteredRows);

        if (!keyword) {
            renderTransactionsTable(columnFilteredRows);
            return;
        }

        const filteredRows = columnFilteredRows.filter(txn => {
            const values = [
                txn.date,
                formatTxnType(txn.type),
                txn.number,
                txn.debit,
                txn.credit,
                txn.running_balance
            ];

            return values.some(value => String(value ?? '').toLowerCase().includes(keyword));
        });

        renderTransactionsTable(filteredRows);
    }

    window.applyPartyTxnRenderedState = applyTransactionSearch;

    function toggleTransactionSearch() {
        if (!txnToolbar || !txnSearchInput) return;

        const shouldShow = txnToolbar.style.display === 'none' || txnToolbar.style.display === '';
        txnToolbar.style.display = shouldShow ? 'flex' : 'none';

        if (shouldShow) {
            txnSearchInput.focus();
        } else {
            txnSearchInput.value = '';
            applyTransactionSearch();
        }
    }

    function openTxnOptionModal(actionType) {
        pendingTxnAction = actionType;
        txnOptionTitle.textContent = actionType === 'print' ? 'Print Options' : 'Excel Options';
        txnOptionModal.classList.add('active');
    }

    function closeTxnOptionModal() {
        pendingTxnAction = null;
        txnOptionModal.classList.remove('active');
    }

    function getSelectedExportColumns() {
        const selected = Array.from(document.querySelectorAll('.txn-export-column:checked'))
            .map(input => input.value);

        return exportColumns.filter(column => selected.includes(column.key));
    }

    function getSelectedExportExtras() {
        return Array.from(document.querySelectorAll('.txn-export-extra:checked'))
            .map(input => input.value);
    }

    function formatTxnItemDetails(txn) {
        if (!Array.isArray(txn.item_details) || !txn.item_details.length) return '';

        return txn.item_details.map((item, index) => {
            const qty = item.tadaat ?? item.quantity ?? 0;
            const unit = item.unit || '';
            const price = Number(item.price ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const amount = Number(item.amount ?? item.grand_total ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return `${index + 1}. ${item.name || '-'} | Qty: ${qty} ${unit}`.trim() + ` | Price: Rs ${price} | Amount: Rs ${amount}`;
        }).join('\n');
    }

    function formatTxnPaymentInfo(txn) {
        if (!Array.isArray(txn.payment_information) || !txn.payment_information.length) return '';

        return txn.payment_information.map((payment, index) => {
            const amount = Number(payment.amount ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return `${index + 1}. ${payment.payment_type || '-'} | ${payment.bank_name || '-'} | Rs ${amount} | Ref: ${payment.reference || '-'}`;
        }).join('\n');
    }

    function exportTransactionsToExcel(columns, rows, extras = []) {
        if (!rows.length) {
            alert('No transactions available for Excel export.');
            return;
        }

        const extraColumns = [];
        if (extras.includes('item_details')) extraColumns.push({ key: 'item_details', label: 'Item Details' });
        if (extras.includes('description')) extraColumns.push({ key: 'description', label: 'Description' });
        if (extras.includes('payment_status')) extraColumns.push({ key: 'payment_status', label: 'Payment Status' });
        if (extras.includes('payment_information')) extraColumns.push({ key: 'payment_information', label: 'Payment Information' });

        const finalColumns = [...columns, ...extraColumns];
        const csvLines = [
            finalColumns.map(column => `"${column.label.replace(/"/g, '""')}"`).join(',')
        ];

        rows.forEach(row => {
            const exportRow = {
                ...row,
                item_details: formatTxnItemDetails(row),
                description: row.description || '',
                payment_status: row.payment_status_text || formatTxnStatus(row.status),
                payment_information: formatTxnPaymentInfo(row),
            };

            csvLines.push(
                finalColumns
                    .map(column => `"${String(exportRow[column.key] ?? '').replace(/"/g, '""')}"`)
                    .join(',')
            );
        });

        const blob = new Blob(["\uFEFF" + csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `party-transactions-${currentPartyId || 'export'}.csv`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    }

    function printTransactions(columns, rows) {
        if (!rows.length) {
            alert('No transactions available to print.');
            return;
        }

        openTxnPrintPreview(columns, rows);
    }

    function buildTxnPrintPreviewHtml(options = {}) {
        const rows = (filteredTransactionsState && filteredTransactionsState.length ? filteredTransactionsState : transactionsState) || [];
        const partyName = document.getElementById("partyDetailName")?.textContent?.trim() || 'Party';
        const partyPhone = document.getElementById("partyPhone")?.textContent?.trim() || '-';
        const partyEmail = document.getElementById("partyEmail")?.textContent?.trim() || '-';
        const partyAddress = document.getElementById("partyAddress")?.textContent?.trim() || '-';
        const partyCityPtcl = document.getElementById("partyCityPtcl")?.textContent?.trim() || '-';
        const dateFrom = transactionDateRange.from || 'Start';
        const dateTo = transactionDateRange.to || 'Today';
        const showItemDetails = Boolean(options.item_details);
        const showDescription = Boolean(options.description);
        const showPaymentStatus = Boolean(options.payment_status);
        const showPaymentInfo = Boolean(options.payment_information);
        const money = (value) => `Rs ${Number(value || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const normalizeStatus = (value) => {
            const raw = String(value || '').trim().toLowerCase();
            if (['paid', 'completed', 'closed', 'converted'].includes(raw)) return 'Paid';
            if (['partial', 'pending', 'confirmed'].includes(raw)) return 'Partial';
            return 'Unpaid';
        };
        const fmtPartyMeta = (label, value) => `<div class="party-meta"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value || '-')}</div>`;
        const totalDebit = rows.reduce((sum, row) => sum + parseTxnNumber(row.debit || 0), 0);
        const totalCredit = rows.reduce((sum, row) => sum + parseTxnNumber(row.credit || 0), 0);
        const closingBalance = rows.length ? parseTxnNumber(rows[rows.length - 1].running_balance || 0) : parseTxnNumber(0);

        const transactionRows = rows.map((txn) => {
            const receivedPaid = parseTxnNumber(txn.received_amount ?? txn.paid_amount ?? 0);
            const rowBalance = parseTxnNumber(txn.row_left_balance ?? txn.running_balance ?? 0);
            const receivableBalance = rowBalance > 0 ? rowBalance : 0;
            const payableBalance = rowBalance < 0 ? Math.abs(rowBalance) : 0;
            const paymentStatus = normalizeStatus(txn.status);
            const extraRows = [];

            if (showItemDetails && Array.isArray(txn.item_details) && txn.item_details.length) {
                const itemsHtml = txn.item_details.map((item, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(item.name || '-')}</td>
                        <td class="num">${escapeHtml(item.tadaat ?? item.quantity ?? 0)}</td>
                        <td class="num">${escapeHtml(item.unit || '-')}</td>
                        <td class="num">${money(item.price ?? 0)}</td>
                        <td class="num">${money(item.amount ?? item.grand_total ?? 0)}</td>
                    </tr>
                `).join('');

                extraRows.push(`
                    <tr>
                        <td colspan="9">
                            <div class="section-box">
                                <div class="section-title">Item Details</div>
                                <table class="inner-table">
                                    <thead>
                                        <tr>
                                            <th style="width:42px;">#</th>
                                            <th>Item name</th>
                                            <th class="num" style="width:90px;">Quantity</th>
                                            <th class="num" style="width:110px;">Unit</th>
                                            <th class="num" style="width:130px;">Price / Unit</th>
                                            <th class="num" style="width:130px;">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                `);
            }

            if (showDescription && txn.description) {
                extraRows.push(`
                    <tr>
                        <td colspan="9">
                            <div class="section-box">
                                <div class="section-title">Description</div>
                                <div>${escapeHtml(txn.description)}</div>
                            </div>
                        </td>
                    </tr>
                `);
            }

            if (showPaymentStatus) {
                extraRows.push(`
                    <tr>
                        <td colspan="9">
                            <div class="section-box">
                                <div class="section-title">Payment Status</div>
                                <div>Status: ${escapeHtml(paymentStatus)} | Paid: ${money(receivedPaid)} | Left: ${money(rowBalance)}</div>
                            </div>
                        </td>
                    </tr>
                `);
            }

            if (showPaymentInfo && Array.isArray(txn.payment_information) && txn.payment_information.length) {
                const paymentsHtml = txn.payment_information.map((payment) => `
                    <tr>
                        <td>${escapeHtml(payment.payment_type || '-')}</td>
                        <td>${escapeHtml(payment.bank_name || '-')}</td>
                        <td class="num">${money(payment.amount || 0)}</td>
                        <td>${escapeHtml(payment.reference || '-')}</td>
                    </tr>
                `).join('');

                extraRows.push(`
                    <tr>
                        <td colspan="9">
                            <div class="section-box">
                                <div class="section-title">Payment Information</div>
                                <table class="inner-table">
                                    <thead>
                                        <tr>
                                            <th>Payment Type</th>
                                            <th>Bank / Cash</th>
                                            <th class="num">Amount</th>
                                            <th>Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody>${paymentsHtml}</tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                `);
            }

            return `
                <tr>
                    <td>${escapeHtml(txn.date || '-')}</td>
                    <td>${escapeHtml(txn.type || '-')}</td>
                    <td>${escapeHtml(txn.number || '-')}</td>
                    <td>${showPaymentStatus ? escapeHtml(paymentStatus) : '-'}</td>
                    <td class="num">${money(txn.total || 0)}</td>
                    <td class="num">${money(receivedPaid)}</td>
                    <td class="num">${money(txn.running_balance || 0)}</td>
                    <td class="num">${money(receivableBalance)}</td>
                    <td class="num">${money(payableBalance)}</td>
                </tr>
                ${extraRows.join('')}
            `;
        }).join('');

        const supportText = document.querySelector('.customer-support, .header-support')?.textContent?.trim() || '';

        return `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    :root { color-scheme: light; }
                    html, body { margin: 0; padding: 0; background: #f4f5f7; color: #111827; }
                    body { font-family: Arial, sans-serif; }
                    .sheet {
                        width: min(1120px, calc(100vw - 48px));
                        margin: 18px auto;
                        background: #fff;
                        border: 1px solid #e5e7eb;
                        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
                        padding: 18px 20px 24px;
                    }
                    .company {
                        text-align: center;
                        font-size: 22px;
                        font-weight: 700;
                        line-height: 1.2;
                    }
                    .company-meta {
                        text-align: center;
                        font-size: 12px;
                        color: #6b7280;
                        margin-top: 2px;
                    }
                    .title {
                        text-align: center;
                        font-size: 24px;
                        font-weight: 700;
                        text-decoration: underline;
                        margin: 22px 0 18px;
                    }
                    .party-block { margin-bottom: 12px; }
                    .party-name {
                        font-size: 18px;
                        font-weight: 700;
                        margin-bottom: 8px;
                    }
                    .party-meta {
                        font-size: 12px;
                        line-height: 1.6;
                        color: #374151;
                    }
                    .summary-grid {
                        display: grid;
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                        border: 1px solid #dbe1ea;
                        margin: 14px 0 18px;
                    }
                    .summary-card {
                        border-right: 1px solid #dbe1ea;
                        padding: 12px 14px;
                    }
                    .summary-card:last-child { border-right: 0; }
                    .summary-label { color: #64748b; font-size: 12px; }
                    .summary-value { font-size: 20px; font-weight: 700; margin-top: 4px; }
                    .range {
                        font-size: 12px;
                        color: #6b7280;
                        margin: 8px 0 14px;
                    }
                    table { width: 100%; border-collapse: collapse; font-size: 12px; }
                    th, td {
                        border: 1px solid #d1d5db;
                        padding: 8px 10px;
                        text-align: left;
                        vertical-align: top;
                    }
                    th {
                        background: #e5e7eb;
                        font-weight: 700;
                    }
                    .num { text-align: right; white-space: nowrap; }
                    .section-box {
                        border: 1px solid #dbe1ea;
                        background: #fafafa;
                        padding: 10px 12px;
                        margin-top: 8px;
                    }
                    .section-title {
                        font-size: 11px;
                        font-weight: 700;
                        color: #475569;
                        margin-bottom: 6px;
                    }
                    .inner-table th, .inner-table td { font-size: 11px; }
                    .footer-note {
                        margin-top: 10px;
                        font-size: 10px;
                        color: #64748b;
                    }
                </style>
            </head>
            <body>
                <div class="sheet">
                    <div class="company">${escapeHtml(appCompanyName || 'My Company')}</div>
                    <div class="company-meta">${escapeHtml(supportText)}</div>
                    <div class="title">Party statement</div>
                    <div class="party-block">
                        <div class="party-name">Party name: ${escapeHtml(partyName)}</div>
                        ${fmtPartyMeta('Contact No', partyPhone)}
                        ${fmtPartyMeta('Email', partyEmail)}
                        ${fmtPartyMeta('Address', partyAddress)}
                        ${fmtPartyMeta('City / PTCL', partyCityPtcl)}
                    </div>
                    <div class="summary-grid">
                        <div class="summary-card">
                            <div class="summary-label">Total Debit</div>
                            <div class="summary-value">${money(totalDebit)}</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-label">Total Credit</div>
                            <div class="summary-value">${money(totalCredit)}</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-label">Closing Balance</div>
                            <div class="summary-value">${money(closingBalance)}</div>
                        </div>
                    </div>
                    <div class="range">Statement Period: ${escapeHtml(dateFrom)} to ${escapeHtml(dateTo)}</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width:11%;">Date</th>
                                <th style="width:16%;">Type</th>
                                <th style="width:12%;">Bill No</th>
                                <th style="width:14%;">Payment Status</th>
                                <th class="num" style="width:11%;">Total</th>
                                <th class="num" style="width:12%;">Received / Paid</th>
                                <th class="num" style="width:12%;">Txn Balance</th>
                                <th class="num" style="width:12%;">Receivable Balance</th>
                                <th class="num" style="width:12%;">Payable Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${transactionRows || '<tr><td colspan="9" style="text-align:center;color:#64748b;padding:18px;">No transactions found.</td></tr>'}
                        </tbody>
                    </table>
                    <div class="footer-note">Generated on ${new Date().toLocaleString()}</div>
                </div>
            </body>
            </html>
        `;
    }

    function openTxnPrintPreview() {
        const options = {
            item_details: partyStatementPdfItems?.checked,
            description: partyStatementPdfDescription?.checked,
            payment_status: partyStatementPdfPaymentStatus?.checked,
            payment_information: partyStatementPdfPaymentInfo?.checked,
        };

        const previewHtml = buildTxnPrintPreviewHtml(options);
        const pdfUrl = buildPartyStatementPdfUrl(false);
        const downloadUrl = buildPartyStatementPdfUrl(true);
        const emailUrl = buildPartyStatementEmailUrl();
        const partyName = document.getElementById("partyDetailName")?.textContent?.trim() || 'Party Statement';
        const partyEmail = document.getElementById("partyEmail")?.textContent?.trim() || '';

        if (!partyTxnPreviewModal || !partyTxnPreviewFrame) {
            window.open(pdfUrl || 'about:blank', '_blank');
            return;
        }

        partyTxnPreviewModalTitle.textContent = 'Preview';
        partyTxnPreviewFrame.removeAttribute('src');
        partyTxnPreviewFrame.srcdoc = previewHtml;
        partyTxnPreviewFrame.dataset.pdfUrl = pdfUrl || '';
        partyTxnPreviewFrame.dataset.downloadUrl = downloadUrl || '';
        partyTxnPreviewFrame.dataset.printUrl = pdfUrl || '';
        partyTxnPreviewFrame.dataset.previewUrl = pdfUrl || '';
        partyTxnPreviewFrame.dataset.emailUrl = emailUrl || '';
        partyTxnPreviewFrame.dataset.partyEmail = partyEmail || '';
        partyTxnPreviewFrame.dataset.partyName = partyName || '';
        partyTxnPreviewFrame.dataset.saleNumber = partyName || '';
        partyTxnPreviewFrame.dataset.documentLabel = 'Party Statement';
        partyTxnPreviewFrame.dataset.previewMode = 'party-statement';
        if (partyTxnPreviewOpenPdfBtn) {
            partyTxnPreviewOpenPdfBtn.disabled = !pdfUrl;
        }
        if (partyTxnPreviewPrintBtn) {
            partyTxnPreviewPrintBtn.disabled = false;
        }
        if (partyTxnPreviewSavePdfBtn) {
            partyTxnPreviewSavePdfBtn.disabled = !downloadUrl;
        }
        if (partyTxnPreviewEmailPdfBtn) {
            partyTxnPreviewEmailPdfBtn.disabled = !downloadUrl;
        }
        partyTxnPreviewModal.show();
    }

    function runPendingTxnAction() {
        const columns = getSelectedExportColumns();
        const extras = getSelectedExportExtras();
        const rows = getTransactionExportRows();

        if (!columns.length) {
            alert('Please select at least one column.');
            return;
        }

        if (pendingTxnAction === 'print') {
            openTxnPrintPreview(columns, rows);
        } else if (pendingTxnAction === 'excel') {
            exportTransactionsToExcel(columns, rows, extras);
        }

        closeTxnOptionModal();
    }

    function renderPartyGroupOptions(selectedValue = '') {
        if (!partyGroupOptions || !partyGroupInput || !partyGroupTriggerText) return;

        partyGroupOptions.innerHTML = '';
        partyGroupInput.value = selectedValue || '';
        partyGroupTriggerText.textContent = selectedValue || 'Select party group';

        partyGroups.forEach(group => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'party-group-option';
            option.textContent = group.name;
            option.addEventListener('click', function () {
                partyGroupInput.value = group.name;
                partyGroupTriggerText.textContent = group.name;
                partyGroupMenu.classList.remove('active');
            });
            partyGroupOptions.appendChild(option);
        });
    }

    function persistPartyGroups() {
        localStorage.setItem(PARTY_GROUPS_STORAGE_KEY, JSON.stringify(partyGroups));
    }

    function hydratePartyGroups() {
        try {
            const savedGroups = JSON.parse(localStorage.getItem(PARTY_GROUPS_STORAGE_KEY) || '[]');
            if (Array.isArray(savedGroups) && savedGroups.length) {
                savedGroups.forEach((group) => {
                    const normalizedGroup = typeof group === 'string'
                        ? { id: null, name: group.trim() }
                        : { id: group?.id || null, name: String(group?.name || '').trim() };

                    if (!normalizedGroup.name) return;
                    if (!partyGroups.some((item) => item.name === normalizedGroup.name)) {
                        partyGroups.push(normalizedGroup);
                    }
                });
            }
        } catch (error) {
            console.warn('Unable to load saved party groups.', error);
        }
    }

    function openPartyGroupCreateModal() {
        partyGroupMenu?.classList.remove('active');
        partyGroupNameInput.value = '';
        partyGroupModal.classList.add('active');
        setTimeout(() => {
            partyGroupNameInput.focus();
            partyGroupNameInput.click();
        }, 0);
    }

    function closePartyGroupCreateModal() {
        partyGroupModal.classList.remove('active');
    }

    function savePartyGroupLocally() {
        const groupName = partyGroupNameInput.value.trim();
        if (!groupName) {
            alert('Enter party group name.');
            return;
        }

        fetch("{{ route('party-groups.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ name: groupName })
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Unable to create party group.');
            }
            return data;
        })
        .then((data) => {
            const createdGroup = {
                id: data.partyGroup?.id || null,
                name: data.partyGroup?.name || groupName
            };

            if (!partyGroups.some((group) => group.name === createdGroup.name)) {
                partyGroups.push(createdGroup);
            }

            persistPartyGroups();
            renderPartyGroupOptions(createdGroup.name);
            renderPartyGroupsView();
            switchPartyView('groups');
            selectPartyGroup(createdGroup.name);
            closePartyGroupCreateModal();
            partyGroupMenu.classList.remove('active');
        })
        .catch((error) => {
            alert(error.message || 'Unable to create party group.');
        });
    }

    function formatCurrency(value) {
        const amount = Number(value || 0);
        return `Rs ${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function closeAllGroupActionMenus() {
        document.querySelectorAll('.party-group-action-menu.active').forEach((menu) => {
            menu.classList.remove('active');
        });
    }

    function createGroupActionMenu(type, payload) {
        const wrapper = document.createElement('div');
        wrapper.className = type === 'group' ? 'party-group-sidebar-actions' : 'party-group-party-actions';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'party-group-action-btn';
        button.innerHTML = '<i class="fa-solid fa-ellipsis-vertical"></i>';

        const menu = document.createElement('div');
        menu.className = 'party-group-action-menu';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = type === 'group' ? 'Edit Group' : 'Edit Party';

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.textContent = type === 'group' ? 'Delete Group' : 'Delete Party';

        editButton.addEventListener('click', function (event) {
            event.stopPropagation();
            closeAllGroupActionMenus();
            if (type === 'group') {
                if (window.requestTransactionPasscode) {
                    window.requestTransactionPasscode(() => editPartyGroup(payload));
                } else {
                    editPartyGroup(payload);
                }
                return;
            }
            openPartyEditorById(payload.id);
        });

        deleteButton.addEventListener('click', function (event) {
            event.stopPropagation();
            closeAllGroupActionMenus();
            if (type === 'group') {
                if (window.requestTransactionPasscode) {
                    window.requestTransactionPasscode(() => deletePartyGroup(payload));
                } else {
                    deletePartyGroup(payload);
                }
                return;
            }
            deletePartyById(payload.id);
        });

        button.addEventListener('click', function (event) {
            event.stopPropagation();
            const shouldOpen = !menu.classList.contains('active');
            closeAllGroupActionMenus();
            menu.classList.toggle('active', shouldOpen);
        });

        menu.append(editButton, deleteButton);
        wrapper.append(button, menu);

        return wrapper;
    }

    function getNormalizedGroupName(value) {
        const groupName = String(value || '').trim();
        return groupName || 'General';
    }

    function getPartyItemsByGroup(groupName) {
        const normalizedGroupName = getNormalizedGroupName(groupName);
        return Array.from(document.querySelectorAll('.party-item')).filter((item) => {
            return getNormalizedGroupName(item.dataset.partyGroup) === normalizedGroupName;
        });
    }

    function buildPartyGroupSummary() {
        return partyGroups.map((group) => {
            const parties = getPartyItemsByGroup(group.name);
            const totalAmount = parties.reduce((sum, item) => sum + Number(item.dataset.salesTotal || 0), 0);
            return {
                id: group.id,
                name: group.name,
                parties,
                totalAmount
            };
        });
    }

    function renderPartyGroupsView() {
        if (!partyGroupsSidebarList) return;

        const groupQuery = (partyGroupSearchInput?.value || '').trim().toLowerCase();
        const summaries = buildPartyGroupSummary().filter((group) => group.name.toLowerCase().includes(groupQuery));

        partyGroupsSidebarList.innerHTML = '';

        if (!summaries.length) {
            partyGroupsSidebarList.innerHTML = '<div class="party-group-empty">No party groups found.</div>';
        } else {
            summaries.forEach((group) => {
                const item = document.createElement('div');
                item.className = `party-group-sidebar-item${selectedPartyGroupNameValue === group.name ? ' active' : ''}`;
                item.dataset.groupName = group.name;

                const meta = document.createElement('div');
                meta.innerHTML = `
                    <div class="party-group-sidebar-name">${group.name}</div>
                    <div class="party-group-sidebar-meta">${group.parties.length} parties</div>
                `;

                const amount = document.createElement('div');
                amount.className = 'party-group-sidebar-amount';
                amount.textContent = formatCurrency(group.totalAmount);

                item.append(meta, amount, createGroupActionMenu('group', group));
                item.addEventListener('click', function () {
                    selectPartyGroup(group.name);
                });
                partyGroupsSidebarList.appendChild(item);
            });
        }

        const hasSelectedGroup = summaries.some((group) => group.name === selectedPartyGroupNameValue);
        if (!hasSelectedGroup) {
            selectPartyGroup(summaries[0]?.name || 'General');
            return;
        }

        renderSelectedPartyGroupDetails();
    }

    function renderSelectedPartyGroupDetails() {
        const groupName = selectedPartyGroupNameValue || 'General';
        const parties = getPartyItemsByGroup(groupName);
        const query = (partyGroupPartySearchInput?.value || '').trim().toLowerCase();
        const filteredParties = parties.filter((item) => (item.dataset.name || '').toLowerCase().includes(query));
        const totalAmount = parties.reduce((sum, item) => sum + Number(item.dataset.salesTotal || 0), 0);

        selectedPartyGroupName.textContent = groupName;
        selectedPartyGroupCount.textContent = `Parties(${parties.length})`;
        selectedPartyGroupAmount.textContent = formatCurrency(totalAmount);

        partyGroupPartiesTableBody.innerHTML = '';

        if (!filteredParties.length) {
            partyGroupPartiesTableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="party-group-empty">No parties found for this group.</td>
                </tr>
            `;
            return;
        }

        filteredParties.forEach((item) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="party-group-party-name">${item.dataset.name || ''}</td>
                <td class="party-group-party-amount">${formatCurrency(item.dataset.salesTotal || 0)}</td>
                <td></td>
            `;
            row.children[2].appendChild(createGroupActionMenu('party', { id: item.dataset.id }));
            partyGroupPartiesTableBody.appendChild(row);
        });
    }

    function selectPartyGroup(groupName) {
        selectedPartyGroupNameValue = getNormalizedGroupName(groupName);
        renderPartyGroupsView();
    }

    function openPartyGroupMoveModal() {
        if (!selectedPartyGroupNameValue) return;
        selectedMovePartyIds = [];
        if (partyGroupMoveSearchInput) {
            partyGroupMoveSearchInput.value = '';
        }
        if (partyGroupMoveSelectAll) {
            partyGroupMoveSelectAll.checked = false;
        }
        if (partyGroupMoveTitle) {
            partyGroupMoveTitle.textContent = 'Select parties and choose the party group where you want to move them.';
        }
        renderPartyGroupMoveTargetOptions();
        renderPartyGroupMoveList();
        partyGroupMoveModal?.classList.add('active');
    }

    function closePartyGroupMoveModal() {
        partyGroupMoveModal?.classList.remove('active');
    }

    function renderPartyGroupMoveList() {
        if (!partyGroupMoveList) return;

        const query = (partyGroupMoveSearchInput?.value || '').trim().toLowerCase();
        const targetGroupName = getNormalizedGroupName(partyGroupMoveTargetSelect?.value || selectedPartyGroupNameValue);
        const candidates = Array.from(document.querySelectorAll('.party-item')).filter((item) => {
            const nameMatches = (item.dataset.name || '').toLowerCase().includes(query);
            return nameMatches;
        });

        partyGroupMoveList.innerHTML = '';

        if (!candidates.length) {
            partyGroupMoveList.innerHTML = '<div class="party-group-empty">No parties found.</div>';
            return;
        }

        candidates.forEach((item) => {
            const currentGroupName = getNormalizedGroupName(item.dataset.partyGroup);
            const wrapper = document.createElement('label');
            wrapper.className = 'party-group-move-item';
            wrapper.innerHTML = `
                <div class="party-group-move-meta">
                    <strong>${item.dataset.name || ''}</strong>
                    <span>${currentGroupName} -> ${targetGroupName}</span>
                </div>
            `;

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = item.dataset.id || '';
            checkbox.checked = selectedMovePartyIds.includes(item.dataset.id || '');
            checkbox.disabled = currentGroupName === targetGroupName;
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    selectedMovePartyIds = Array.from(new Set([...selectedMovePartyIds, this.value]));
                } else {
                    selectedMovePartyIds = selectedMovePartyIds.filter((id) => id !== this.value);
                }
                syncPartyGroupMoveSelectAllState();
            });

            wrapper.appendChild(checkbox);
            partyGroupMoveList.appendChild(wrapper);
        });

        syncPartyGroupMoveSelectAllState();
    }

    function renderPartyGroupMoveTargetOptions() {
        if (!partyGroupMoveTargetSelect) return;

        partyGroupMoveTargetSelect.innerHTML = '';

        partyGroups.forEach((group) => {
            const option = document.createElement('option');
            option.value = group.name;
            option.textContent = group.name;
            option.selected = group.name === selectedPartyGroupNameValue;
            partyGroupMoveTargetSelect.appendChild(option);
        });
    }

    function syncPartyGroupMoveSelectAllState() {
        if (!partyGroupMoveSelectAll || !partyGroupMoveList) return;

        const enabledCheckboxes = Array.from(partyGroupMoveList.querySelectorAll('input[type="checkbox"]:not(:disabled)'));
        if (!enabledCheckboxes.length) {
            partyGroupMoveSelectAll.checked = false;
            return;
        }

        partyGroupMoveSelectAll.checked = enabledCheckboxes.every((checkbox) => checkbox.checked);
    }

    function savePartyGroupMove() {
        if (!selectedMovePartyIds.length) {
            alert('Select at least one party.');
            return;
        }

        const targetGroupName = getNormalizedGroupName(partyGroupMoveTargetSelect?.value || selectedPartyGroupNameValue);

        fetch("{{ route('parties.groups.move') }}", {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                party_ids: selectedMovePartyIds,
                party_group: targetGroupName === 'General' ? '' : targetGroupName
            })
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Unable to move parties.');
            }
            return data;
        })
        .then(() => {
            document.querySelectorAll('.party-item').forEach((item) => {
                if (selectedMovePartyIds.includes(item.dataset.id || '')) {
                    item.dataset.partyGroup = targetGroupName === 'General' ? '' : targetGroupName;
                }
            });

            selectedPartyGroupNameValue = targetGroupName;
            renderPartyGroupOptions(partyGroupInput?.value || '');
            renderPartyGroupsView();
            closePartyGroupMoveModal();
            alert('Parties moved successfully.');
        })
        .catch((error) => {
            alert(error.message || 'Unable to move parties.');
        });
    }

    function switchPartyView(view) {
        const normalizedView = view === 'groups' && partySettingsState.party_grouping ? 'groups' : 'parties';
        currentPartyView = normalizedView;
        const isGroupsView = normalizedView === 'groups';
        splitPane?.classList.toggle('is-hidden', isGroupsView);
        partyGroupsView?.classList.toggle('is-hidden', !isGroupsView);

        document.querySelectorAll('[data-party-view-option]').forEach((option) => {
            const isActive = option.dataset.partyViewOption === normalizedView;
            option.classList.toggle('active', isActive);
            const tick = option.querySelector('.tick-icon');
            if (tick) tick.style.visibility = isActive ? 'visible' : 'hidden';
        });

        if (isGroupsView) {
            renderPartyGroupsView();
        }

        closeHeaderDropdown();
    }

    function openPartyEditorById(partyId) {
        const li = document.querySelector(`.party-item[data-id="${partyId}"]`);
        if (!li) return;

        const openEditor = () => {
            li.dispatchEvent(new MouseEvent('click', { bubbles: true }));
            document.getElementById("editPartyBtn")?.click();
        };

        if (window.requestTransactionPasscode) {
            window.requestTransactionPasscode(openEditor);
            return;
        }

        openEditor();
    }

    function deletePartyById(partyId) {
        const openDelete = () => {
            currentPartyId = partyId;
            deleteBtn?.click();
        };

        if (window.requestTransactionPasscode) {
            window.requestTransactionPasscode(openDelete);
            return;
        }

        openDelete();
    }

    function editPartyGroup(group) {
        if (!group?.id) {
            alert('This group is not ready to edit yet.');
            return;
        }

        const nextName = prompt('Enter new party group name', group.name);
        if (!nextName || !nextName.trim() || nextName.trim() === group.name) {
            return;
        }

        fetch(`/dashboard/party-groups/${group.id}`, {
            method: 'PUT',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ name: nextName.trim() })
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Unable to update party group.');
            return data;
        })
        .then((data) => {
            const updatedName = data.partyGroup?.name || nextName.trim();
            partyGroups = partyGroups.map((item) => {
                if (item.name === group.name) {
                    return { id: data.partyGroup?.id || item.id, name: updatedName };
                }
                return item;
            });

            document.querySelectorAll('.party-item').forEach((item) => {
                if (getNormalizedGroupName(item.dataset.partyGroup) === group.name) {
                    item.dataset.partyGroup = updatedName;
                }
            });

            if (partyGroupInput?.value === group.name) {
                renderPartyGroupOptions(updatedName);
            } else {
                renderPartyGroupOptions(partyGroupInput?.value || '');
            }

            if (selectedPartyGroupNameValue === group.name) {
                selectedPartyGroupNameValue = updatedName;
            }

            persistPartyGroups();
            renderPartyGroupsView();
        })
        .catch((error) => {
            alert(error.message || 'Unable to update party group.');
        });
    }

    function deletePartyGroup(group) {
        if (!group?.id) {
            alert('This group is not ready to delete yet.');
            return;
        }

        if (!confirm(`Delete "${group.name}" group? Parties will move to General.`)) {
            return;
        }

        fetch(`/dashboard/party-groups/${group.id}`, {
            method: 'DELETE',
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Unable to delete party group.');
            return data;
        })
        .then(() => {
            partyGroups = partyGroups.filter((item) => item.name !== group.name);
            document.querySelectorAll('.party-item').forEach((item) => {
                if (getNormalizedGroupName(item.dataset.partyGroup) === group.name) {
                    item.dataset.partyGroup = '';
                }
            });

            if (partyGroupInput?.value === group.name) {
                renderPartyGroupOptions('');
            } else {
                renderPartyGroupOptions(partyGroupInput?.value || '');
            }

            if (selectedPartyGroupNameValue === group.name) {
                selectedPartyGroupNameValue = 'General';
            }

            persistPartyGroups();
            renderPartyGroupsView();
        })
        .catch((error) => {
            alert(error.message || 'Unable to delete party group.');
        });
    }

    function syncCreditLimitVisibility() {
        const isEnabled = document.getElementById("creditLimitSwitch").checked;
        creditLimitAmountWrap.classList.toggle('is-hidden', !isEnabled);
        creditLimitAmountInput.disabled = !isEnabled;
        if (!isEnabled) {
            creditLimitAmountInput.value = '';
        }
    }

    function getDisplayBalanceValue(partyData, fallbackBalance = 0) {
        return parseFloat(fallbackBalance ?? partyData.current_balance ?? partyData.opening_balance ?? 0).toFixed(2);
    }

    function getSidebarAmountValue(partyData, fallbackBalance = 0) {
        return parseFloat(fallbackBalance ?? partyData.current_balance ?? partyData.opening_balance ?? 0).toFixed(2);
    }

    function getPartyListItemById(partyId) {
        return document.querySelector(`.party-item[data-id="${partyId}"]`);
    }

    function getPartyReminderDefaultsFromItem(li) {
        const reminderMessageFromSettings = @json($partySettings['payment_reminder_message'] ?? "Dear [Party Name],\n\nYour payment of [Amount] is pending with [Business Name].\n\n[Additional Message]\n\nIf you already have made the payment, kindly ignore this message.");
        const partyName = li?.dataset?.name || '';
        const partyPhone = li?.dataset?.paymentReminderPhone || li?.dataset?.phone || '';
        const amount = parseFloat(li?.dataset?.currentBalance || li?.dataset?.displayAmount || li?.dataset?.openingBalance || 0) || 0;
        const reminderDate = li?.dataset?.paymentReminderDate || new Date().toISOString().slice(0, 10);
        const reminderEnabled = String(li?.dataset?.paymentReminderEnabled || '0') === '1';
        const reminderMessage = li?.dataset?.paymentReminderMessage || reminderMessageFromSettings;

        return {
            partyName,
            partyPhone,
            amount,
            reminderDate,
            reminderEnabled,
            reminderMessage,
        };
    }

    function openPartyReminderModal() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        const li = getPartyListItemById(currentPartyId);
        if (!li) {
            alert('Party not found.');
            return;
        }

        const defaults = getPartyReminderDefaultsFromItem(li);
        if (partyReminderPartyName) partyReminderPartyName.textContent = defaults.partyName || '-';
        if (partyReminderPartyPhone) partyReminderPartyPhone.textContent = defaults.partyPhone || '-';
        if (partyReminderPartyAmount) partyReminderPartyAmount.textContent = `Rs ${defaults.amount.toFixed(2)}`;
        if (partyReminderEnabled) partyReminderEnabled.checked = defaults.reminderEnabled;
        if (partyReminderDate) partyReminderDate.value = defaults.reminderDate;

        partyReminderModal?.show();
    }

    function openPartyReminderWhatsappModal() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        const li = getPartyListItemById(currentPartyId);
        if (!li) {
            alert('Party not found.');
            return;
        }

        const defaults = getPartyReminderDefaultsFromItem(li);
        if (partyReminderWhatsappPhone) partyReminderWhatsappPhone.value = defaults.partyPhone || '';
        partyReminderWhatsappModal?.show();
    }

    function savePartyReminder() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        const li = getPartyListItemById(currentPartyId);
        if (!li) {
            alert('Party not found.');
            return;
        }

        const defaults = getPartyReminderDefaultsFromItem(li);

        const enabled = !!partyReminderEnabled?.checked;
        const reminderDate = partyReminderDate?.value || '';
        if (enabled && !reminderDate) {
            alert('Please select reminder date.');
            return;
        }

        const payload = {
            enabled,
            phone: defaults.partyPhone || '',
            reminder_date: reminderDate || null,
            message: defaults.reminderMessage || reminderMessageFromSettings || '',
        };

        fetch(`/dashboard/parties/${currentPartyId}/reminder`, {
            method: 'POST',
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(payload)
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Unable to save reminder.');
            return data;
        })
        .then((data) => {
            const reminder = data.reminder || {};
            li.dataset.paymentReminderEnabled = reminder.enabled ? '1' : '0';
            li.dataset.paymentReminderPhone = reminder.phone || '';
            li.dataset.paymentReminderDate = reminder.reminder_date || '';
            li.dataset.paymentReminderMessage = reminder.message || '';
            li.dataset.paymentReminderSentAt = '';
            partyReminderModal?.hide();
            alert('Payment reminder set successfully.');
        })
        .catch((error) => {
            alert(error.message || 'Unable to save reminder.');
        });
    }

    function deletePartyReminder() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        const li = getPartyListItemById(currentPartyId);
        if (!li) {
            alert('Party not found.');
            return;
        }

        fetch(`/dashboard/parties/${currentPartyId}/reminder`, {
            method: 'POST',
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                enabled: false,
                phone: '',
                reminder_date: null,
                message: '',
            })
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Unable to delete reminder.');
            return data;
        })
        .then((data) => {
            const reminder = data.reminder || {};
            li.dataset.paymentReminderEnabled = reminder.enabled ? '1' : '0';
            li.dataset.paymentReminderPhone = reminder.phone || '';
            li.dataset.paymentReminderDate = '';
            li.dataset.paymentReminderMessage = '';
            li.dataset.paymentReminderSentAt = '';
            partyReminderModal?.hide();
            alert('Payment reminder deleted successfully.');
        })
        .catch((error) => {
            alert(error.message || 'Unable to delete reminder.');
        });
    }

    function sendPartyReminderWhatsapp() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        const li = getPartyListItemById(currentPartyId);
        if (!li) {
            alert('Party not found.');
            return;
        }

        const phone = String(partyReminderPhone?.value || '').replace(/\D+/g, '');
        if (!phone) {
            alert('Please enter WhatsApp phone number.');
            return;
        }

        const defaults = getPartyReminderDefaultsFromItem(li);
        const partyName = defaults.partyName || 'Party';
        const amountText = `Rs ${defaults.amount.toFixed(2)}`;
        const message = (partyReminderMessage?.value || defaults.reminderMessage || '')
            .replace(/\[Party Name\]/g, partyName)
            .replace(/\[Amount\]/g, amountText)
            .replace(/\[Business Name\]/g, @json(config('app.name', 'My Company')))
            .replace(/\[Additional Message\]/g, '');

        window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
    }

    function sendPartyReminderWhatsappQuick() {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }

        const li = getPartyListItemById(currentPartyId);
        if (!li) {
            alert('Party not found.');
            return;
        }

        const phone = String(partyReminderWhatsappPhone?.value || '').replace(/\D+/g, '');
        if (!phone) {
            alert('Please enter WhatsApp phone number.');
            return;
        }

        const defaults = getPartyReminderDefaultsFromItem(li);
        const partyName = defaults.partyName || 'Party';
        const amountText = `Rs ${defaults.amount.toFixed(2)}`;
        const message = (defaults.reminderMessage || reminderMessageFromSettings || '')
            .replace(/\[Party Name\]/g, partyName)
            .replace(/\[Amount\]/g, amountText)
            .replace(/\[Business Name\]/g, @json(config('app.name', 'My Company')))
            .replace(/\[Additional Message\]/g, '');

        partyReminderWhatsappModal?.hide();
        window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
    }

    function updatePartySidebarBalance(partyId, balanceValue) {
        const sidebarParty = document.querySelector(`.party-item[data-id="${partyId}"]`);
        if (!sidebarParty) return;

        const numericBalance = parseFloat(String(balanceValue ?? 0).replace(/,/g, ''));
        const normalizedBalance = Number.isFinite(numericBalance) ? numericBalance.toFixed(2) : '0.00';
        sidebarParty.dataset.currentBalance = normalizedBalance;

        const balanceEl = sidebarParty.querySelector(".entity-balance");
        if (!balanceEl) return;

        const displayAmount = getSidebarAmountValue({
            transaction_type: sidebarParty.dataset.transactionType,
            sales_total: sidebarParty.dataset.salesTotal,
            current_balance: normalizedBalance,
        }, normalizedBalance);

        balanceEl.textContent = `Rs ${displayAmount}`;
        balanceEl.classList.remove('positive', 'negative');
        balanceEl.classList.add(parseFloat(displayAmount) < 0 ? 'negative' : 'positive');
    }

    function applyPartySettings() {
        partyGroupsViewOption?.classList.toggle('is-hidden', !partySettingsState.party_grouping);
        updateManagePartyStatusVisibility();
        if (partyViewDropdownMenu) {
            partyViewDropdownMenu.style.display = 'none';
        }
        if (partyViewDropdownTrigger) {
            partyViewDropdownTrigger.style.transform = 'rotate(0deg)';
        }

        document.querySelectorAll('[data-party-setting="party_grouping"]').forEach(section => {
            section.classList.toggle('is-hidden', !partySettingsState.party_grouping);
            section.querySelectorAll('input, textarea, select, button').forEach(field => {
                if (field.type !== 'hidden') field.disabled = !partySettingsState.party_grouping;
            });
        });

        if (!partySettingsState.party_grouping) {
            if (partyGroupInput) {
                partyGroupInput.value = '';
            }
            if (partyGroupTriggerText) {
                partyGroupTriggerText.textContent = 'Select party group';
            }
        }

        document.querySelectorAll('[data-party-setting="shipping_address"]').forEach(section => {
            section.classList.toggle('is-hidden', !partySettingsState.shipping_address);
            section.querySelectorAll('input, textarea, select').forEach(field => {
                if (field.type !== 'hidden') field.disabled = !partySettingsState.shipping_address;
            });
        });

        const additionalPane = document.querySelector('[data-party-setting="additional_fields"]');
        if (additionalPane) {
            const shouldShowAdditionalPane = partySettingsState.additional_field_1 || partySettingsState.additional_field_2;
            additionalPane.classList.toggle('is-hidden', !shouldShowAdditionalPane);
        }

        const additionalFieldCheckboxes = document.querySelectorAll('#partyAdditionalPane .form-check-input[type="checkbox"]');
        const additionalFieldInputs = document.querySelectorAll('#partyAdditionalPane input[type="text"]');

        [partySettingsState.additional_field_1, partySettingsState.additional_field_2].forEach((enabled, index) => {
            const fieldWrap = additionalFieldInputs[index]?.closest('.col-md-6');
            if (fieldWrap) fieldWrap.classList.toggle('is-hidden', !enabled);
            if (additionalFieldCheckboxes[index]) additionalFieldCheckboxes[index].disabled = !enabled;
            if (additionalFieldInputs[index]) {
                additionalFieldInputs[index].disabled = !enabled;
            }
            if (index === 0 && partyAdditionalField1Print) {
                partyAdditionalField1Print.disabled = !enabled;
            }
            if (index === 1 && partyAdditionalField2Print) {
                partyAdditionalField2Print.disabled = !enabled;
            }
        });

        if (additionalFieldInputs[0]) {
            additionalFieldInputs[0].placeholder = partyAdditionalField1Name?.value || 'Field name';
        }

        if (additionalFieldInputs[1]) {
            additionalFieldInputs[1].placeholder = partyAdditionalField2Name?.value || 'Field name';
        }

        if (partyReminderDays) {
            partyReminderDays.disabled = !partySettingsState.payment_reminder;
        }

        if (!partySettingsState.party_grouping && currentPartyView === 'groups') {
            switchPartyView('parties');
        }

        if (partySettingsState.party_grouping && currentPartyView !== 'groups') {
            switchPartyView('parties');
        }
    }

    function savePartyStatusSetting(enabled) {
        savePartySettings({ party_status: !!enabled });
    }

    let partySettingsSaveTimer = null;
    function buildPartySettingsPayload(overrides = {}) {
        return {
            party_grouping: !!partySettingsState.party_grouping,
            shipping_address: !!partySettingsState.shipping_address,
            print_shipping_address: !!partySettingsState.print_shipping_address,
            party_status: !!partySettingsState.party_status,
            payment_reminder: !!partySettingsState.payment_reminder,
            payment_reminder_days: Number(partyReminderDays?.value || partySettingsState.payment_reminder_days || 2),
            additional_field_1: !!partySettingsState.additional_field_1,
            additional_field_1_name: partyAdditionalField1Name?.value || partySettingsState.additional_field_1_name || '',
            additional_field_1_print: document.getElementById('partyAdditionalField1Print')?.checked ?? !!partySettingsState.additional_field_1_print,
            additional_field_2: !!partySettingsState.additional_field_2,
            additional_field_2_name: partyAdditionalField2Name?.value || partySettingsState.additional_field_2_name || '',
            additional_field_2_print: document.getElementById('partyAdditionalField2Print')?.checked ?? !!partySettingsState.additional_field_2_print,
            ...overrides
        };
    }

    function savePartySettings(overrides = {}) {
        const payload = buildPartySettingsPayload(overrides);
        fetch(`/dashboard/parties/settings/update`, {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(payload)
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Unable to save party settings.');
            }
            if (data.settings) {
                partySettingsState = { ...partySettingsState, ...data.settings };
            } else {
                partySettingsState = { ...partySettingsState, ...payload };
            }
            applyPartySettings();
        })
        .catch(() => {});
    }

    function queueSavePartySettings() {
        clearTimeout(partySettingsSaveTimer);
        partySettingsSaveTimer = setTimeout(() => savePartySettings(), 250);
    }

    function openPartySettingsDrawer() {
        partySettingsDrawer.classList.add('active');
    }

    function closePartySettingsDrawer() {
        partySettingsDrawer.classList.remove('active');
    }

    function setPartyInactiveUi(li, isActive) {
        if (!li) return;

        li.dataset.isActive = isActive ? '1' : '0';
        let pill = li.querySelector('.party-inactive-pill');

        if (!isActive) {
            if (!pill) {
                pill = document.createElement('span');
                pill.className = 'party-inactive-pill';
                pill.textContent = 'Inactive';
                li.insertBefore(pill, li.querySelector('.entity-balance'));
            }
        } else if (pill) {
            pill.remove();
        }
    }

    function showInactivePartyMessage(li) {
        const partyName = li?.dataset?.name || 'This party';
        const editBtn = document.getElementById("editPartyBtn");
        if (editBtn) {
            editBtn.disabled = true;
            editBtn.classList.add('disabled');
        }
        showTxnMessage('fa-solid fa-ban', 'Inactive party', `${partyName} transactions cannot be viewed while inactive.`);
    }

    function renderManagePartyStatusRows() {
        if (!managePartyStatusTableBody) return;

        const query = (managePartyStatusSearch?.value || '').trim().toLowerCase();
        const rows = managePartyStatusRows.filter((party) => {
            const name = String(party.name || '').toLowerCase();
            const phone = String(party.phone || '').toLowerCase();
            return !query || name.includes(query) || phone.includes(query);
        });

        if (!rows.length) {
            managePartyStatusTableBody.innerHTML = '<div class="manage-party-status-empty">No parties found.</div>';
            return;
        }

        managePartyStatusTableBody.innerHTML = rows.map((party) => `
            <div class="manage-party-status-table-row" data-party-status-row="${party.id}">
                <div class="manage-party-status-party">${escapeHtml(party.name || '-')}</div>
                <div class="manage-party-status-phone">${escapeHtml(party.phone || '-')}</div>
                <div>
                    <label class="manage-party-status-switch">
                        <input type="checkbox" data-party-status-toggle="${party.id}" ${party.is_active ? 'checked' : ''}>
                        <span class="manage-party-status-slider"></span>
                    </label>
                </div>
            </div>
        `).join('');
    }

    function openManagePartyStatusModal() {
        if (!managePartyStatusModal || !partySettingsState.party_status) {
            return;
        }

        managePartyStatusTableBody.innerHTML = '<div class="manage-party-status-empty">Loading parties...</div>';
        managePartyStatusSearch.value = '';
        managePartyStatusModal.classList.add('active');

        fetch(`/dashboard/parties/status/list`, {
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Unable to load parties.');
            }
            managePartyStatusRows = Array.isArray(data.parties) ? data.parties : [];
            renderManagePartyStatusRows();
        })
        .catch((error) => {
            managePartyStatusTableBody.innerHTML = `<div class="manage-party-status-empty">${escapeHtml(error.message || 'Unable to load parties.')}</div>`;
        });
    }

    function closeManagePartyStatusModal() {
        managePartyStatusModal?.classList.remove('active');
    }

    function persistPartyStatus(partyId, isActive) {
        fetch(`/dashboard/parties/status/update`, {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                parties: [{ id: Number(partyId), is_active: !!isActive }]
            })
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Unable to update party status.');
            }

            const li = document.querySelector(`.party-item[data-id="${partyId}"]`);
            setPartyInactiveUi(li, isActive);

            if (currentPartyId === String(partyId)) {
                const editBtn = document.getElementById("editPartyBtn");
                if (editBtn) {
                    editBtn.disabled = !isActive;
                    editBtn.classList.toggle('disabled', !isActive);
                }
                if (!isActive) {
                    showInactivePartyMessage(li);
                } else {
                    loadPartyTransactions(currentPartyId);
                }
            }
        })
        .catch((error) => {
            const row = managePartyStatusRows.find((item) => String(item.id) === String(partyId));
            if (row) row.is_active = !isActive;
            renderManagePartyStatusRows();
            alert(error.message || 'Unable to update party status.');
        });
    }

    function togglePartyMoreMenu() {
        partyMoreMenu.classList.toggle('active');
    }

    function closePartyMoreMenu() {
        partyMoreMenu.classList.remove('active');
    }

    function openPartyQrModal() {
        const partyName = document.getElementById("partyDetailName")?.textContent?.trim();
        const partyPhone = document.getElementById("partyPhone")?.textContent?.trim();
        const partyEmail = document.getElementById("partyEmail")?.textContent?.trim();

        if (!partyName) {
            alert('Select a party first to generate QR code.');
            return;
        }

        const qrPayload = `Party: ${partyName}\nPhone: ${partyPhone || '-'}\nEmail: ${partyEmail || '-'}`;
        partyQrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrPayload)}`;
        partyQrText.textContent = qrPayload.replace(/\n/g, ' | ');
        partyQrModal.classList.add('active');
    }

    function closePartyQrModal() {
        partyQrModal.classList.remove('active');
    }

    function importContactFile(file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            const content = String(event.target?.result || '');
            const fullName = content.match(/FN:(.*)/)?.[1]?.trim() || file.name.replace(/\.[^.]+$/, '');
            const phone = content.match(/TEL[^:]*:(.*)/)?.[1]?.trim() || '';
            const email = content.match(/EMAIL[^:]*:(.*)/)?.[1]?.trim() || '';

            resetModal();
            document.getElementById("partyNameInput").value = fullName;
            document.getElementById("partyPhoneInput").value = phone;
            document.getElementById("partyPtclInput").value = '';
            const emailInput = document.querySelector('#partyAddressPane input[type="email"]');
            if (emailInput) emailInput.value = email;
            addModal.show();
        };
        reader.readAsText(file);
    }

    function importExcelFile(file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            const content = String(event.target?.result || '');
            const rows = content.split(/\r?\n/).filter(Boolean);

            if (rows.length < 2) {
                alert('Excel import ke liye CSV file me header aur kam az kam ek row honi chahiye.');
                return;
            }

            const headers = rows[0].split(',').map(value => value.trim().toLowerCase());
            const values = rows[1].split(',').map(value => value.trim());
            const rowData = headers.reduce((acc, header, index) => {
                acc[header] = values[index] || '';
                return acc;
            }, {});

            resetModal();
            document.getElementById("partyNameInput").value = rowData.name || rowData.party || '';
            document.getElementById("partyPhoneInput").value = rowData.phone || rowData.mobile || '';
            document.getElementById("partyPhone2Input").value = rowData.phone_number_2 || rowData.phone2 || '';
            document.getElementById("partyPtclInput").value = rowData.ptcl_number || '';
            document.getElementById("partyCityInput").value = rowData.city || '';
            const emailInput = document.querySelector('#partyAddressPane input[type="email"]');
            if (emailInput) emailInput.value = rowData.email || '';
            document.getElementById("partyAddressInput").value = rowData.address || '';
            document.getElementById("billingAddress").value = rowData.billing_address || rowData.address || '';
            document.getElementById("shippingAddress").value = rowData.shipping_address || '';
            addModal.show();
        };
        reader.readAsText(file);
    }

    // ADD PARTY
    function addParty(closeModal = true) {
        const partyData = getPartyData();

        fetch("{{ route('parties.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(partyData)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || "Unable to save party.");
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                const party = data.party;
                const li = document.createElement("li");
                li.className = "party-item";
                li.dataset.id = party.id;
                li.dataset.isActive = party.is_active ? "1" : "0";
                li.dataset.name = party.name;
                li.dataset.phone = party.phone || "";
                li.dataset.phoneNumber2 = party.phone_number_2 || "";
                li.dataset.ptclNumber = party.ptcl_number || "";
                li.dataset.partyGroup = partyData.party_group || "";
                li.dataset.email = party.email || "";
                li.dataset.city = party.city || "";
                li.dataset.address = party.address || "";
                li.dataset.billingAddress = party.billing_address || "";
                li.dataset.shippingAddress = party.shipping_address || "";
                li.dataset.openingBalance = party.opening_balance || 0;
                li.dataset.currentBalance = getDisplayBalanceValue(party, party.current_balance || 0);
                li.dataset.asOfDate = party.as_of_date || "";
                li.dataset.transactionType = party.transaction_type || "";
                li.dataset.partyType = Array.isArray(partyData.party_type) ? partyData.party_type.join(',') : (party.party_type || "");
                li.dataset.salesTotal = Number(party.sales_total || 0).toFixed(2);
                li.dataset.creditLimitEnabled = party.credit_limit_enabled || 0;
                li.dataset.creditLimitAmount = partyData.credit_limit_amount || "";
                li.dataset.paymentReminderEnabled = party.payment_reminder_enabled ? "1" : "0";
                li.dataset.paymentReminderDate = party.payment_reminder_date || "";
                li.dataset.paymentReminderMessage = party.payment_reminder_message || "";
                li.dataset.paymentReminderSentAt = party.payment_reminder_sent_at || "";
                li.dataset.customFields = JSON.stringify(party.custom_fields || []);

                const displayAmount = getSidebarAmountValue(party, party.current_balance || 0);
                li.innerHTML = `
                    <span class="entity-name">${party.name}</span>
                    ${party.is_active ? '' : '<span class="party-inactive-pill">Inactive</span>'}
                    <span class="entity-balance ${parseFloat(displayAmount) < 0 ? 'negative' : 'positive'}">Rs ${displayAmount}</span>
                    <div class="party-item-menu-wrap">
                      <button type="button" class="party-item-menu-btn" data-party-menu-toggle aria-label="Party Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                      </button>
                      <div class="party-item-menu">
                        <button type="button" class="party-item-menu-action text-danger" data-party-delete-id="${party.id}">Delete</button>
                      </div>
                    </div>
                `;

                partyList.prepend(li);

                if (closeModal) {
                    addModal.hide();
                    resetModal();
                } else {
                    document.getElementById("addPartyForm").reset();
                }

                renderPartyGroupsView();
                alert("Party saved successfully!");
            }
        })
        .catch(err => {
            console.error("Add Party Error:", err);
            alert(err.message || "Unable to save party.");
        });
    }

    saveBtn?.addEventListener("click", () => addParty(true));
    saveNewBtn?.addEventListener("click", () => addParty(false));
    openLedgerModalBtn?.addEventListener("click", openLedgerModal);
    openTransferHistoryModalBtn?.addEventListener("click", openTransferHistoryModal);
    txnSearchToggle?.addEventListener("click", toggleTransactionSearch);
    txnSearchInput?.addEventListener("input", applyTransactionSearch);
    initializeTransactionFilterControls();
    initializePartyTxnColumnResize();
    document.addEventListener('click', function (e) {
        const filterDropdown = e.target.closest('#partyTxnTable thead .filter-dropdown');
        if (filterDropdown) {
            e.stopPropagation();
        }

        const applyBtn = e.target.closest('#partyTxnTable thead .filter-dropdown .apply-btn');
        if (applyBtn) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = applyBtn.closest('.filter-dropdown');
            const column = getTxnFilterColumnFromDropdown(dropdown);
            saveTxnFilterFromDropdown(column, dropdown);
            dropdown.style.display = 'none';
            applyTransactionSearch();
            return;
        }

        const clearBtn = e.target.closest('#partyTxnTable thead .filter-dropdown .clear-btn');
        if (clearBtn) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = clearBtn.closest('.filter-dropdown');
            const column = getTxnFilterColumnFromDropdown(dropdown);
            clearTxnFilterFromDropdown(column, dropdown);
            dropdown.style.display = 'none';
            applyTransactionSearch();
        }
    });
    txnPrintTrigger?.addEventListener("click", () => partyStatementPdfModal?.show());
    txnExcelTrigger?.addEventListener("click", () => openTxnOptionModal('excel'));
    txnPdfTrigger?.addEventListener("click", () => {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }
        const partyName = document.getElementById("partyDetailName")?.textContent?.trim() || 'party_statement';
        const fromLabel = transactionDateRange.from || 'start';
        const toLabel = transactionDateRange.to || 'today';
        if (partyStatementPdfFileName) {
            partyStatementPdfFileName.textContent = `${partyName}_${fromLabel}_to_${toLabel}.pdf`;
        }
        partyStatementPdfModal?.show();
    });
    txnWhatsappTrigger?.addEventListener("click", () => {
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }
        const url = buildPartyStatementPdfUrl(false);
        const partyName = document.getElementById("partyDetailName")?.textContent?.trim() || 'Party';
        const message = `Party statement for ${partyName}\n${url}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
    });
    txnDateApply?.addEventListener("click", () => {
        transactionDateRange = {
            from: txnDateFrom?.value || '',
            to: txnDateTo?.value || '',
        };
        if (currentPartyId) {
            loadPartyTransactions(currentPartyId);
        }
    });
    txnDateClear?.addEventListener("click", () => {
        transactionDateRange = { from: '', to: '' };
        if (txnDateFrom) txnDateFrom.value = '';
        if (txnDateTo) txnDateTo.value = '';
        if (currentPartyId) {
            loadPartyTransactions(currentPartyId);
        }
    });
    partyStatementPdfApply?.addEventListener("click", () => {
        const url = buildPartyStatementPdfUrl(false);
        if (!url) {
            alert('Select Party First');
            return;
        }
        partyStatementPdfModal?.hide();
        openTxnPrintPreview();
    });
    savePartyReminderBtn?.addEventListener('click', savePartyReminder);
    deletePartyReminderBtn?.addEventListener('click', deletePartyReminder);
    sendPartyReminderWhatsappBtn?.addEventListener('click', sendPartyReminderWhatsapp);
    sendPartyReminderWhatsappQuickBtn?.addEventListener('click', sendPartyReminderWhatsappQuick);
    txnOptionCancel.addEventListener("click", closeTxnOptionModal);
    txnOptionConfirm.addEventListener("click", runPendingTxnAction);
    txnOptionModal.addEventListener("click", function (e) {
        if (e.target.dataset.close === 'true') {
            closeTxnOptionModal();
        }
    });
    partyViewDropdownTrigger?.addEventListener("click", function (e) {
        e.stopPropagation();
        toggleHeaderDropdown(this);
    });
    document.querySelectorAll('[data-party-view-option]').forEach((option) => {
        option.addEventListener('click', function () {
            const view = option.dataset.partyViewOption;
            if (view === 'groups' && !partySettingsState.party_grouping) {
                return;
            }
            switchPartyView(view);
        });
    });
    partyGroupTrigger?.addEventListener("click", function (e) {
        e.stopPropagation();
        partyGroupMenu.classList.toggle('active');
    });
    partyGroupsAddBtn?.addEventListener("click", openPartyGroupCreateModal);
    movePartiesToGroupBtn?.addEventListener("click", openPartyGroupMoveModal);
    partyGroupSearchInput?.addEventListener("input", renderPartyGroupsView);
    partyGroupPartySearchInput?.addEventListener("input", renderSelectedPartyGroupDetails);
    partyGroupMoveSearchInput?.addEventListener("input", renderPartyGroupMoveList);
    partyGroupMoveTargetSelect?.addEventListener("change", function () {
        selectedMovePartyIds = [];
        if (partyGroupMoveSelectAll) {
            partyGroupMoveSelectAll.checked = false;
        }
        renderPartyGroupMoveList();
    });
    partyGroupMoveSelectAll?.addEventListener("change", function () {
        const enabledCheckboxes = Array.from(document.querySelectorAll('#partyGroupMoveList input[type="checkbox"]:not(:disabled)'));
        enabledCheckboxes.forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
        selectedMovePartyIds = this.checked
            ? enabledCheckboxes.map((checkbox) => checkbox.value)
            : [];
    });
    partyGroupMoveCancel?.addEventListener("click", closePartyGroupMoveModal);
    partyGroupMoveSave?.addEventListener("click", savePartyGroupMove);
    openPartyGroupModal?.addEventListener("click", openPartyGroupCreateModal);
    partyGroupCancel?.addEventListener("click", closePartyGroupCreateModal);
    partyGroupSave?.addEventListener("click", savePartyGroupLocally);
    partyGroupModal?.addEventListener("click", function (e) {
        if (e.target.dataset.closePartyGroup === 'true') {
            closePartyGroupCreateModal();
        }
    });
    partyGroupMoveModal?.addEventListener("click", function (e) {
        if (e.target.dataset.closePartyGroupMove === 'true') {
            closePartyGroupMoveModal();
        }
    });
    partySettingsTrigger.addEventListener("click", openPartySettingsDrawer);
    partyModalSettingsTrigger.addEventListener("click", openPartySettingsDrawer);
    partySettingsClose.addEventListener("click", closePartySettingsDrawer);
    partySettingsDrawer.addEventListener("click", function (e) {
        if (e.target.dataset.closePartySettings === 'true') {
            closePartySettingsDrawer();
        }
    });
    document.querySelectorAll('.party-setting-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            partySettingsState[this.dataset.settingTarget] = this.checked;
            applyPartySettings();
            queueSavePartySettings();
        });
    });
    partyAdditionalField1Name?.addEventListener('input', function () {
        applyPartySettings();
        queueSavePartySettings();
    });
    partyAdditionalField2Name?.addEventListener('input', function () {
        applyPartySettings();
        queueSavePartySettings();
    });
    partyReminderDays?.addEventListener('input', queueSavePartySettings);
    document.getElementById('partyAdditionalField1Print')?.addEventListener('change', queueSavePartySettings);
    document.getElementById('partyAdditionalField2Print')?.addEventListener('change', queueSavePartySettings);
    partyMoreOptionsTrigger.addEventListener("click", function (e) {
        e.stopPropagation();
        togglePartyMoreMenu();
    });
    importExcelOption.addEventListener("click", function () {
        closePartyMoreMenu();
        partyExcelImportInput.click();
    });
    importPhoneOption.addEventListener("click", function () {
        closePartyMoreMenu();
        partyPhoneImportInput.click();
    });
    importContactsOption.addEventListener("click", function () {
        closePartyMoreMenu();
        partyContactsImportInput.click();
    });
    partyStatementReportOption?.addEventListener("click", function () {
        closePartyMoreMenu();
        if (!currentPartyId) {
            alert('Select Party First');
            return;
        }
        window.open(`/dashboard/reports?party_id=${encodeURIComponent(currentPartyId)}#Party%20Statement`, '_blank');
    });
    allPartiesReportOption?.addEventListener("click", function () {
        closePartyMoreMenu();
        window.open(`/dashboard/reports#All%20Parties`, '_blank');
    });
    managePartyStatusOption?.addEventListener("click", function () {
        closePartyMoreMenu();
        if (!partySettingsState.party_status) {
            alert('Enable Manage Party Status from settings first.');
            return;
        }
        openManagePartyStatusModal();
    });
    partyExcelImportInput.addEventListener("change", function () {
        const file = this.files?.[0];
        if (file) {
            importExcelFile(file);
        }
        this.value = '';
    });
    partyContactsImportInput.addEventListener("change", function () {
        const file = this.files?.[0];
        if (file) {
            importContactFile(file);
        }
        this.value = '';
    });
    partyQrClose.addEventListener("click", closePartyQrModal);
    managePartyStatusClose?.addEventListener("click", closeManagePartyStatusModal);
    managePartyStatusSearch?.addEventListener("input", renderManagePartyStatusRows);
    managePartyStatusModal?.addEventListener("click", function (e) {
        if (e.target.dataset.closeManagePartyStatus === 'true') {
            closeManagePartyStatusModal();
        }
    });
    managePartyStatusTableBody?.addEventListener("change", function (e) {
        const toggle = e.target.closest('[data-party-status-toggle]');
        if (!toggle) return;

        const partyId = toggle.dataset.partyStatusToggle;
        const nextValue = toggle.checked;
        const row = managePartyStatusRows.find((item) => String(item.id) === String(partyId));
        if (row) row.is_active = nextValue;
        persistPartyStatus(partyId, nextValue);
    });
    partyQrModal.addEventListener("click", function (e) {
        if (e.target.dataset.closePartyQr === 'true') {
            closePartyQrModal();
        }
    });
    document.addEventListener("click", function (e) {
        if (!document.getElementById("partyGroupDropdown")?.contains(e.target)) {
            partyGroupMenu?.classList.remove('active');
        }
        if (!partyMoreMenu.contains(e.target) && !partyMoreOptionsTrigger.contains(e.target)) {
            closePartyMoreMenu();
        }
        if (!e.target.closest('.party-group-action-menu') && !e.target.closest('.party-group-action-btn')) {
            closeAllGroupActionMenus();
        }
    });
    document.addEventListener('click', async function (e) {
        const actionItem = e.target.closest('.party-txn-action-dropdown .dropdown-item');
        if (!actionItem) return;

        e.preventDefault();

        const dropdown = actionItem.closest('.dropdown-menu');
        const menu = actionItem.closest('.party-txn-action-menu') || dropdown?.__anchorMenu;
        closePartyTxnActionMenus();
        if (actionItem.classList.contains('is-disabled')) return;
        if (!menu) return;

        const action = actionItem.dataset.action;
        const txnNumber = menu.dataset.number || 'Transaction';
        const viewUrl = menu.dataset.viewUrl;
        const deleteUrl = menu.dataset.deleteUrl;
        const cancelUrl = menu.dataset.cancelUrl;
        const duplicateUrl = menu.dataset.duplicateUrl;
        const pdfUrl = menu.dataset.pdfUrl;
        const previewUrl = menu.dataset.previewUrl;
        const printUrl = menu.dataset.printUrl;
        const previewDeliveryUrl = menu.dataset.previewDeliveryUrl;
        const convertReturnUrl = menu.dataset.convertReturnUrl;
        const historyUrl = menu.dataset.historyUrl;

        if (action === 'view') {
            if (!viewUrl) return alert('View/Edit is not available for this transaction.');
            if (window.requestTransactionPasscode) {
                window.requestTransactionPasscode(() => { window.location.href = viewUrl; });
            } else {
                window.location.href = viewUrl;
            }
            return;
        }

        if (action === 'delete') {
            if (!deleteUrl) return alert('Delete is not available for this transaction.');
            const performDelete = async () => {
                if (!confirm('Are you sure you want to delete this transaction?')) return;

                try {
                    const data = await fetchJson(deleteUrl, { method: 'DELETE' });
                    alert(data.message || 'Transaction deleted successfully.');
                    if (currentPartyId) loadPartyTransactions(currentPartyId);
                } catch (error) {
                    alert(error.message || 'Unable to delete transaction.');
                }
            };
            if (window.requestTransactionPasscode) {
                window.requestTransactionPasscode(performDelete);
            } else {
                await performDelete();
            }
            return;
        }

        if (action === 'cancel') {
            if (!cancelUrl) return alert('Cancel is not available for this transaction.');
            if (!confirm('Are you sure you want to cancel this transaction?')) return;

            try {
                const data = await fetchJson(cancelUrl, { method: 'POST' });
                alert(data.message || 'Transaction cancelled successfully.');
                if (currentPartyId) loadPartyTransactions(currentPartyId);
            } catch (error) {
                alert(error.message || 'Unable to cancel transaction.');
            }
            return;
        }

        if (action === 'duplicate') {
            if (!duplicateUrl) return alert('Duplicate is not available for this transaction.');
            window.location.href = duplicateUrl;
            return;
        }

        if (action === 'pdf') {
            if (!pdfUrl) return alert('PDF is not available for this transaction.');
            window.open(pdfUrl, '_blank');
            return;
        }

        if (action === 'preview') {
            return openPartyTxnPreview(previewUrl || pdfUrl, `Preview - ${txnNumber}`, {
                pdfUrl: pdfUrl || previewUrl,
                printUrl: printUrl || previewUrl,
                downloadUrl: (pdfUrl || previewUrl) ? (pdfUrl || previewUrl) + ((pdfUrl || previewUrl).includes('?') ? '&' : '?') + 'download=1' : '',
            });
        }

        if (action === 'print') {
            if (!printUrl) return alert('Print is not available for this transaction.');
            window.open(printUrl, '_blank');
            return;
        }

        if (action === 'preview-delivery') {
            return openPartyTxnPreview(previewDeliveryUrl, `Delivery Challan - ${txnNumber}`, {
                pdfUrl: previewDeliveryUrl,
                printUrl: printUrl || previewDeliveryUrl,
                downloadUrl: previewDeliveryUrl ? previewDeliveryUrl + (previewDeliveryUrl.includes('?') ? '&' : '?') + 'download=1' : '',
            });
        }

        if (action === 'convert-return') {
            if (!convertReturnUrl) return alert('Convert to return is not available for this transaction.');
            window.location.href = convertReturnUrl;
            return;
        }

        if (action === 'history') {
            if (!historyUrl) return alert('View history is not available for this transaction.');
            try {
                const data = await fetchJson(historyUrl);
                openPartyTxnHistory(`History - ${txnNumber}`, data.entries || data.history || data.transactions || data.bank_history || []);
            } catch (error) {
                alert(error.message || 'Unable to load history.');
            }
        }
    });
    hydratePartyGroups();
    renderPartyGroupOptions();
    applyPartySettings();
    renderPartyGroupsView();
    switchPartyView('parties');

    const initialParty = partyList.querySelector('.party-item.active') || partyList.querySelector('.party-item');
    if (initialParty) {
        initialParty.click();
    }

    // PARTY CLICK → RIGHT PANEL + SELECT
    partyList.addEventListener("click", function (e) {
        if (e.target.closest('[data-party-menu-toggle]') || e.target.closest('[data-party-delete-id]')) {
            return;
        }

        const li = e.target.closest(".party-item");
        if (!li) return;

        // Remove active from all, add to clicked
        document.querySelectorAll('.party-item').forEach(item => item.classList.remove('active'));
        li.classList.add('active');

        currentPartyId = li.dataset.id;

        console.log("✅ Party Selected - ID:", currentPartyId);

        document.getElementById("partyDetailName").textContent = li.dataset.name || '';
        document.getElementById("partyPhone").textContent = [li.dataset.phone, li.dataset.phoneNumber2].filter(Boolean).join(' / ');
        document.getElementById("partyEmail").textContent = li.dataset.email || '';
        document.getElementById("partyAddress").textContent = li.dataset.billingAddress || '';
        document.getElementById("partyCityPtcl").textContent = `${li.dataset.city || '-'} / ${li.dataset.ptclNumber || '-'}`;
    });

    // OPEN ADD MODAL
    document.querySelector(".btn-add-entity").addEventListener("click", function () {
        resetModal();
        addModal.show();
    });

    // POPULATE MODAL FOR EDITING
   function populatePartyModal(party) {
    currentPartyId = party.id;

    document.getElementById("partyNameInput").value = party.name || '';
    document.getElementById("partyPhoneInput").value = party.phone || '';
    document.getElementById("partyPhone2Input").value = party.phone_number_2 || '';
    renderPartyGroupOptions(party.party_group || '');
    document.querySelector('#partyAddressPane input[type="email"]').value = party.email || '';
    document.getElementById("partyCityInput").value = party.city || '';
    document.getElementById("partyPtclInput").value = party.ptcl_number || '';
    document.getElementById("partyAddressInput").value = party.address || '';
    document.getElementById("billingAddress").value = party.billing_address || '';
    document.getElementById("shippingAddress").value = party.shipping_address || '';
    document.getElementById("partyDueDaysInput").value = party.due_days || '';
    document.querySelector('#partyCreditPane input[type="number"]').value = party.opening_balance || 0;

    // ✅ FIX: Date ko properly format karein
    let dateValue = party.as_of_date || '';
    if (dateValue && dateValue.includes('T')) {
        dateValue = dateValue.split('T')[0]; // "2025-01-15T00:00:00Z" → "2025-01-15"
    }
    document.querySelector('#partyCreditPane input[type="date"]').value = dateValue;
    if (creditLimitAmountInput) {
        creditLimitAmountInput.value = party.credit_limit_amount || '';
    }

    // ✅ FIX: Credit limit switch - string "1" / "true" dono handle karein
    const creditSwitch = document.getElementById("creditLimitSwitch");
    creditSwitch.checked = (party.credit_limit_enabled == 1 || party.credit_limit_enabled === 'true' || party.credit_limit_enabled === true);
    syncCreditLimitVisibility();
    creditSwitch.disabled = false; // ✅ Make sure it's NOT disabled

    // Transaction type
    if (party.transaction_type === 'receive') {
       statusBadge = `<span class="badge">Receivable Opening Balance</span>`;
        toReceive.checked = true;
        toPay.checked = false;
    } else if (party.transaction_type === 'pay') {
      statusBadge = `<span class="badge">Payable Opening Balance</span>`;
        toReceive.checked = false;
        toPay.checked = true;
    } else {
        toReceive.checked = false;
        toPay.checked = false;
    }

    document.querySelectorAll('input[name="party_type[]"]').forEach((checkbox) => {
        checkbox.checked = false;
    });

    const selectedPartyTypes = Array.isArray(party.party_type)
        ? party.party_type
        : String(party.party_type || '')
            .split(',')
            .map((value) => value.trim())
            .filter(Boolean);

    selectedPartyTypes.forEach((value) => {
        const checkbox = document.querySelector(`input[name="party_type[]"][value="${value}"]`);
        if (checkbox) checkbox.checked = true;
    });

    // ✅ FIX: Additional fields / Custom fields
    const customFieldInputs = document.querySelectorAll('#partyAdditionalPane input[type="text"]');
    const customFieldChecks = document.querySelectorAll('#partyAdditionalPane input[type="checkbox"]');

    if (party.custom_fields && Array.isArray(party.custom_fields)) {
        party.custom_fields.forEach((field, index) => {
            const fieldValue = typeof field === 'object' && field !== null
                ? String(field.label || field.value || field.name || '')
                : String(field || '');
            if (customFieldInputs[index]) {
                customFieldInputs[index].value = fieldValue;
            }
            if (customFieldChecks[index]) {
                customFieldChecks[index].checked = !!fieldValue;
            }
        });
    }

    saveBtn.style.display = "none";
    saveNewBtn.style.display = "none";
    updateBtn.style.display = "inline-block";
    deleteBtn.style.display = "inline-block";

    addModal.show();
}
    // EDIT PARTY BUTTON
    document.getElementById("editPartyBtn").addEventListener("click", function () {
        if (!currentPartyId) return alert("Select Party First");

        const li = document.querySelector(`.party-item[data-id='${currentPartyId}']`);
        if (!li) return alert("Party nahi mili!");
        if (isPartyInactive(li)) {
            return alert("Inactive party cannot be edited.");
        }

        console.log("🔍 Edit - All dataset:", JSON.stringify({...li.dataset}));

        const party = {
            id: li.dataset.id,
            name: li.dataset.name,
            phone: li.dataset.phone,
            phone_number_2: li.dataset.phoneNumber2,
            ptcl_number: li.dataset.ptclNumber,
            party_group: li.dataset.partyGroup,
            email: li.dataset.email,
            city: li.dataset.city,
            address: li.dataset.address,
            billing_address: li.dataset.billingAddress,
            shipping_address: li.dataset.shippingAddress,
            opening_balance: li.dataset.openingBalance,
            as_of_date: li.dataset.asOfDate,
            party_type: li.dataset.partyType,
            credit_limit_enabled: li.dataset.creditLimitEnabled,
            credit_limit_amount: li.dataset.creditLimitAmount,
            transaction_type: li.dataset.transactionType || '',
             custom_fields: li.dataset.customFields ? JSON.parse(li.dataset.customFields) : []  // ✅ ADD
        };

        populatePartyModal(party);
    });

    document.getElementById("openPartyReminderBtn")?.addEventListener("click", function () {
        openPartyReminderModal();
    });
    document.getElementById("openPartyReminderWhatsappBtn")?.addEventListener("click", function () {
        openPartyReminderWhatsappModal();
    });

    // ✅ UPDATE PARTY
    updateBtn.addEventListener("click", function (e) {
        e.preventDefault();
        console.log("🔄 Update clicked! currentPartyId:", currentPartyId);

        if (!currentPartyId) {
            alert("No party selected!");
            return;
        }

        const partyData = getPartyData();
        console.log("📤 Sending data:", partyData);
fetch(`/dashboard/parties/${currentPartyId}`, {
    method: "PUT",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(partyData)
        })
        .then(async res => {
            console.log("📥 Response status:", res.status);
            const text = await res.text();
            let data = null;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error("Update non-JSON response:", text);
                throw new Error(text.includes("<!DOCTYPE") ? "Server returned HTML error page. Check validation/server error." : text);
            }

            if (!res.ok) {
                throw new Error(data.message || "Update failed");
            }

            return data;
        })
        .then(data => {
            console.log("📥 Response data:", data);

            if (data.success) {
                const li = document.querySelector(`.party-item[data-id="${currentPartyId}"]`);

                li.dataset.name = partyData.name;
                li.dataset.phone = partyData.phone;
                li.dataset.phoneNumber2 = partyData.phone_number_2;
                li.dataset.ptclNumber = partyData.ptcl_number;
                li.dataset.partyGroup = partyData.party_group;
                li.dataset.email = partyData.email;
                li.dataset.city = partyData.city;
                li.dataset.address = partyData.address;
                li.dataset.billingAddress = partyData.billing_address;
                li.dataset.shippingAddress = partyData.shipping_address;
                li.dataset.dueDays = partyData.due_days;
                li.dataset.openingBalance = partyData.opening_balance;
                li.dataset.currentBalance = getDisplayBalanceValue(data.party, data.party.current_balance || 0);
                li.dataset.asOfDate = partyData.as_of_date;
                li.dataset.partyType = Array.isArray(partyData.party_type) ? partyData.party_type.join(',') : '';
                li.dataset.salesTotal = Number(data.party?.sales_total || li.dataset.salesTotal || 0).toFixed(2);
                li.dataset.creditLimitEnabled = partyData.credit_limit_enabled;
                li.dataset.creditLimitAmount = partyData.credit_limit_amount;
                li.dataset.paymentReminderEnabled = partyData.payment_reminder_enabled ? "1" : "0";
                li.dataset.paymentReminderDate = partyData.payment_reminder_date || "";
                li.dataset.paymentReminderMessage = partyData.payment_reminder_message || "";
                li.dataset.transactionType = partyData.transaction_type;
                li.dataset.isActive = data.party?.is_active ? '1' : '0';

                li.querySelector(".entity-name").textContent = partyData.name;
                setPartyInactiveUi(li, !!data.party?.is_active);
                const updatedDisplayAmount = getDisplayBalanceValue(data.party, data.party.current_balance || 0);
                const balanceNode = li.querySelector(".entity-balance");
                balanceNode.textContent = "Rs " + updatedDisplayAmount;
                balanceNode.classList.remove('positive', 'negative');
                balanceNode.classList.add(parseFloat(updatedDisplayAmount) < 0 ? 'negative' : 'positive');

                document.getElementById("partyDetailName").textContent = partyData.name;
                document.getElementById("partyPhone").textContent = [partyData.phone, partyData.phone_number_2].filter(Boolean).join(' / ');
                document.getElementById("partyEmail").textContent = partyData.email;
                document.getElementById("partyAddress").textContent = partyData.billing_address;
                document.getElementById("partyCityPtcl").textContent = `${partyData.city || '-'} / ${partyData.ptcl_number || '-'}`;

                alert("Party updated successfully!");
                addModal.hide();
                resetModal();
                renderPartyGroupsView();
            } else {
                alert("❌ Update failed: " + JSON.stringify(data));
            }
        })
        .catch(err => {
            console.error("❌ Update Error:", err);
            alert("❌ Network error: " + err.message);
        });
    });
    // ============ PARTY CLICK → LOAD TRANSACTIONS ============
partyList.addEventListener("click", function (e) {
    const menuToggle = e.target.closest('[data-party-menu-toggle]');
    if (menuToggle) {
        e.preventDefault();
        e.stopPropagation();
        const menuWrap = menuToggle.closest('.party-item-menu-wrap');
        document.querySelectorAll('.party-item-menu-wrap.open').forEach((wrap) => {
            if (wrap !== menuWrap) wrap.classList.remove('open');
        });
        menuWrap?.classList.toggle('open');
        return;
    }

    const deleteAction = e.target.closest('[data-party-delete-id]');
    if (deleteAction) {
        e.preventDefault();
        e.stopPropagation();
        deletePartyById(deleteAction.dataset.partyDeleteId);
        return;
    }

    const li = e.target.closest(".party-item");
    if (!li) return;

    // Remove active from all, add to clicked
    document.querySelectorAll('.party-item').forEach(item => item.classList.remove('active'));
    li.classList.add('active');

    currentPartyId = li.dataset.id;

    console.log("✅ Party Selected - ID:", currentPartyId);

    // Update right panel header info
    document.getElementById("partyDetailName").textContent = li.dataset.name || '';
    document.getElementById("partyPhone").textContent = [li.dataset.phone, li.dataset.phoneNumber2].filter(Boolean).join(' / ');
    document.getElementById("partyEmail").textContent = li.dataset.email || '';
    document.getElementById("partyAddress").textContent = li.dataset.billingAddress || '';
    document.getElementById("partyCityPtcl").textContent = `${li.dataset.city || '-'} / ${li.dataset.ptclNumber || '-'}`;
    const editBtn = document.getElementById("editPartyBtn");
    if (editBtn) {
        editBtn.disabled = isPartyInactive(li);
        editBtn.classList.toggle('disabled', isPartyInactive(li));
    }

    if (isPartyInactive(li)) {
        showInactivePartyMessage(li);
        return;
    }

    loadPartyTransactions(currentPartyId);
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('.party-item-menu-wrap')) {
        document.querySelectorAll('.party-item-menu-wrap.open').forEach((wrap) => wrap.classList.remove('open'));
    }
});

// ============ FETCH & RENDER TRANSACTIONS ============
function loadPartyTransactions(partyId) {
    const tbody = getPartyTxnTableBody();
    const currentParty = document.querySelector(`.party-item[data-id="${partyId}"]`);
    transactionsState = [];
    filteredTransactionsState = [];
    resetTransactionFilters(true, true);

    if (currentParty && isPartyInactive(currentParty)) {
        showInactivePartyMessage(currentParty);
        return;
    }

    showTxnMessage('fa fa-spinner fa-spin', 'Loading transactions...', 'Please wait while we fetch party transactions');

    const params = new URLSearchParams();
    if (transactionDateRange.from) params.set('from', transactionDateRange.from);
    if (transactionDateRange.to) params.set('to', transactionDateRange.to);
    const query = params.toString();

    fetch(`/dashboard/parties/${partyId}/transactions${query ? `?${query}` : ''}`)
        .then(res => res.json())
        .then(data => {
          if (data.inactive) {
                showInactivePartyMessage(currentParty);
                return;
          }
          if (data.success) {
                updatePartySidebarBalance(partyId, data.total_balance || 0);
                transactionsState = Array.isArray(data.transactions) ? data.transactions : [];
                applyTransactionSearch();
                return;
          } else {
                showTxnMessage('fa-solid fa-receipt', 'No transactions yet', 'Create a sale or purchase for this party');
                return;
          }
          if (data.success && data.transactions.length > 0) {
    tbody.innerHTML = '';

    data.transactions.forEach(txn => {
        const row = document.createElement('tr');

        // Status badge
        let statusBadge = '';
        const badgeStyleBase = `
            color:#6b7280;
            border-radius:8px;
            font-size:13px;
            font-weight:500;
        `;
        if (txn.status === 'receive') {
            statusBadge = `<span class="badge" style="${badgeStyleBase}">To Receive</span>`;
        } else if (txn.status === 'pay') {
            statusBadge = `<span class="badge" style="${badgeStyleBase}">To Pay</span>`;
        } else if (['paid', 'completed', 'closed', 'converted'].includes((txn.status || '').toLowerCase())) {
            statusBadge = `<span class="badge" style="color:#2563eb; border-radius:12px; font-size:13px;">Paid</span>`;
        } else if (['partial', 'pending', 'confirmed'].includes((txn.status || '').toLowerCase())) {
            statusBadge = `<span class="badge" style="color:#d97706; border-radius:12px; font-size:13px;">${txn.status}</span>`;
        } else {
            statusBadge = `<span class="badge" style="color:#6b7280; border-radius:12px; font-size:13px;">${txn.status || 'Open'}</span>`;
        }

        // Transaction Type badge
        let typeText = txn.type === 'pay'
            ? 'Payable Opening Balance'
            : txn.type === 'receive'
                ? 'Receivable Opening Balance'
                : txn.type;

        const typeColors = {
            'Receivable Opening Balance': { color: 'gray' },
            'Payable Opening Balance': { color: 'gray' },
            'Sale': { bg: '#dbeafe', color: '#2563eb' },
            'Purchase': { bg: '#fef3c7', color: '#d97706' },
            'Estimate': { bg: '#fef3c7', color: '#d97706' },
            'Sale Order': { bg: '#e0f2fe', color: '#0369a1' },
            'Proforma Invoice': { bg: '#ede9fe', color: '#7c3aed' },
            'Delivery Challan': { bg: '#dcfce7', color: '#15803d' },
            'Credit Note': { bg: '#fee2e2', color: '#dc2626' },
            'POS': { bg: '#fce7f3', color: '#be185d' },
        };
        const typeStyle = typeColors[typeText] || { bg: '#f3f4f6', color: '#374151' };

      typeBadge = `<span style="
    background:${typeStyle.bg};
    color:${typeStyle.color};

    border-radius:12px;
    font-size:13px;
    display:inline-block;
    margin-left:2px;
   padding-top:12px;
    white-space: nowrap; /* prevents wrapping */
"> ${typeText} </span>`;
        // Balance color
        let balanceColor = txn.status === 'receive' ? '#16a34a' : txn.status === 'pay' ? '#dc2626' : '#6b7280';

        // Row HTML with flex inside first <td> to force left alignment
        row.innerHTML = `
            <td style="display:flex; justify-content:flex-start; align-items:center;">${typeBadge}</td>
          <td style="color:#6b7280; font-size:14px;">${txn.number || '-'}</td>
            <td style="color:#6b7280; font-size:14px;">${txn.date}</td>
            <td style="color:#6b7280; font-size:14px;">₹ ${txn.total}</td>
            <td style="color:${balanceColor}; font-size:14px; font-weight:600;">
                ₹ ${txn.balance}
                <br>${statusBadge}
            </td>
        `;

        const normalizedStatusText = (txn.status || '').toLowerCase();
        const cleanStatusBadge = normalizedStatusText === 'receive'
            ? `<span style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:#ecfdf5;color:#15803d;font-size:12px;font-weight:600;">To Receive</span>`
            : normalizedStatusText === 'pay'
                ? `<span style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:#fef2f2;color:#dc2626;font-size:12px;font-weight:600;">To Pay</span>`
                : ['paid', 'completed', 'closed', 'converted'].includes(normalizedStatusText)
                    ? `<span style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:#ecfdf5;color:#15803d;font-size:12px;font-weight:600;">Paid</span>`
                    : ['partial', 'pending', 'confirmed'].includes(normalizedStatusText)
                        ? `<span style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:#fff7ed;color:#d97706;font-size:12px;font-weight:600;text-transform:capitalize;">${txn.status}</span>`
                        : `<span style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:#eff6ff;color:#2563eb;font-size:12px;font-weight:600;text-transform:capitalize;">${txn.status || 'Open'}</span>`;

        const cleanTypeColors = {
            'Receivable Opening Balance': { bg: '#f8fafc', color: '#475569' },
            'Payable Opening Balance': { bg: '#f8fafc', color: '#475569' },
            'Sale': { bg: '#eff6ff', color: '#2563eb' },
            'Purchase': { bg: '#fffbeb', color: '#d97706' },
            'Estimate': { bg: '#fff7ed', color: '#ea580c' },
            'Sale Order': { bg: '#ecfeff', color: '#0891b2' },
            'Proforma Invoice': { bg: '#f5f3ff', color: '#7c3aed' },
            'Delivery Challan': { bg: '#ecfdf5', color: '#15803d' },
            'Credit Note': { bg: '#fef2f2', color: '#dc2626' },
            'POS': { bg: '#fdf2f8', color: '#be185d' },
        };
        const cleanTypeStyle = cleanTypeColors[typeText] || { bg: '#f8fafc', color: '#334155' };
        const cleanTypeBadge = `<span style="display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;background:${cleanTypeStyle.bg};color:${cleanTypeStyle.color};font-size:12px;font-weight:600;white-space:nowrap;">${typeText}</span>`;
        const cleanBalanceColor = normalizedStatusText === 'receive' ? '#16a34a' : normalizedStatusText === 'pay' ? '#dc2626' : '#475569';

        row.innerHTML = `
            <td style="background:#fff;color:#334155;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;">${cleanTypeBadge}</td>
            <td style="background:#fff;color:#64748b;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;">${txn.number || '-'}</td>
            <td style="background:#fff;color:#64748b;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;">${txn.date}</td>
            <td style="background:#fff;color:#475569;font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;font-weight:500;">Rs ${txn.total}</td>
            <td style="background:#fff;color:${cleanBalanceColor};font-size:14px;padding:14px 16px;border-bottom:1px solid #eef2f7;font-weight:600;">Rs ${txn.balance}</td>
            <td style="background:#fff;padding:14px 16px;border-bottom:1px solid #eef2f7;">${cleanStatusBadge}</td>
        `;

        tbody.appendChild(row);
    });
} else{                // No transactions
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 40px;">
                            <i class="fa-solid fa-receipt" style="font-size: 40px; color: #d1d5db;"></i>
                            <p class="mt-2" style="color: #6b7280;">No transactions yet</p>
                            <p style="font-size: 12px; color: #9ca3af;">Create a sale or purchase for this party</p>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(err => {
            console.error("❌ Transaction Load Error:", err);
            showTxnMessage('fa-solid fa-exclamation-triangle', 'Error loading transactions', 'Please try again in a moment');
            return;
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger" style="padding: 30px;">
                        <i class="fa-solid fa-exclamation-triangle" style="font-size: 30px;"></i>
                        <p class="mt-2">Error loading transactions</p>
                    </td>
                </tr>
            `;
        });
}

    // DELETE PARTY
    deleteBtn.addEventListener("click", function () {
        if (!currentPartyId) return;
        if (!confirm("Delete this party?")) return;


      fetch(`/dashboard/parties/${currentPartyId}`, {
    method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const li = document.querySelector(`.party-item[data-id="${currentPartyId}"]`);
                const nextLi = li?.nextElementSibling || li?.previousElementSibling || document.querySelector('.party-item:not([data-id="' + currentPartyId + '"])');
                li?.remove();
                document.getElementById("partyDetailName").textContent = "";
                document.getElementById("partyPhone").textContent = "";
                document.getElementById("partyEmail").textContent = "";
                document.getElementById("partyAddress").textContent = "";
                document.getElementById("partyCityPtcl").textContent = "";
                currentPartyId = null;
                addModal.hide();
                resetModal();
                renderPartyGroupsView();
                alert("Party deleted successfully!");
                if (nextLi) {
                    nextLi.click();
                } else {
                    showTxnMessage('fa-solid fa-receipt', 'No transactions yet', 'Select a party to view transactions');
                }
            }
        })
        .catch(err => console.error("Delete Error:", err));
    });

});
</script>
@endpush
