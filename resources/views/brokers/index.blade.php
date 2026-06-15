@extends('layouts.app')

@section('title', 'Vyapar — Brokers')
@section('description', 'Manage your brokers, commission rates, and brokerage balances in Vyapar accounting software.')
@section('page', 'brokers')
<link rel="stylesheet" href="{{ asset('css/brokers.css') }}">

@section('content')

<!-- Upper Panel -->
<div class="uper-panel">
  <div class="panel-main">

    <!-- Left: Header + Arrow -->
    <div class="text">
      <div class="header-dropdown">
        <h1>Brokers</h1>
        <i class="fa fa-chevron-down arrow-icon" onclick="toggleHeaderDropdown(this)"></i>

        <div class="header-dropdown-menu">
          <label class="dropdown-item">
            Brokers
            <i class="fa fa-check tick-icon"></i>
          </label>
        </div>
      </div>
    </div>

    <!-- Right: Buttons -->
    <div class="action-buttons">
      <button class="btn-add-entity" data-bs-toggle="modal" data-bs-target="#addBrokerModal">
        <i class="fa-solid fa-plus me-1"></i> Add Broker
      </button>

      <button class="btn-settings" id="brokerSettingsTrigger" title="Settings">
        <i class="fa-solid fa-gear"></i>
      </button>

      <button class="btn-ellipsis" id="brokerMoreOptionsTrigger" title="More Options">
        <i class="fa-solid fa-ellipsis-vertical"></i>
      </button>
    </div>

  </div>
</div>

<!-- Split Pane Layout -->
<div class="split-pane">

  <!-- Left: Broker List -->
  <div class="split-left">
    <div class="list-panel-header">
      <div class="search-box">
        <i class="fa fa-search"></i>
        <input type="text" class="form-control search-input" placeholder="Search Broker Name" id="brokerSearchInput">
      </div>
    </div>

    <ul class="entity-list" id="brokerList">
      <li class="active" data-broker="header">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

        <div class="filter-wrapper">
          <div class="parent-arrows" onclick="this.classList.toggle('active')">
            <span class="entity-balance positive" style="color: gray !important;">Broker Name</span>
            <div class="counter-arrows">
              <i class="fa fa-chevron-up increment"></i>
              <i class="fa fa-chevron-down decrement"></i>
            </div>
          </div>

          <i class="fa fa-filter filter-icon" onclick="toggleFilter()"></i>

          <div class="filter-dropdown" id="filterDropdown">
            <label><input type="checkbox" data-broker-filter="all" checked> All</label>
            <label><input type="checkbox" data-broker-filter="active"> Active</label>
            <label><input type="checkbox" data-broker-filter="inactive"> Inactive</label>

            <div class="filter-actions">
              <button class="clear-btn" type="button" id="brokerFilterClear">Clear</button>
              <button class="apply-btn" type="button" id="brokerFilterApply">Apply</button>
            </div>
          </div>

        </div>

        <!-- Vertical separator -->
        <div class="separator"></div>

        <div class="parent-arrows" onclick="this.classList.toggle('active')">
          <span class="entity-balance positive" style="color: gray !important;">Balance</span>
          <div class="counter-arrows">
            <i class="fa fa-chevron-up increment"></i>
            <i class="fa fa-chevron-down decrement"></i>
          </div>
        </div>

      </li>

      <ul id="brokersList">
        @foreach($brokers as $broker)
          @php
            $remaining = (float) ($broker->remaining_brokerage ?? ($broker->total_brokerage - $broker->paid_brokerage));
            $brokerSalesTotal = (float) ($broker->broker_sales_total ?? 0);
            $brokerSummaryTotal = (float) ($broker->total_brokerage ?? 0) + $brokerSalesTotal;
          @endphp
          <li class="broker-item"
              data-id="{{ $broker->id }}"
              data-name="{{ $broker->name }}"
              data-phone="{{ $broker->phone }}"
              data-city="{{ $broker->city }}"
              data-address="{{ $broker->address }}"
              data-commission-type="{{ $broker->commission_type }}"
              data-commission-rate="{{ number_format((float) $broker->commission_rate, 2, '.', '') }}"
              data-total-brokerage="{{ number_format((float) $broker->total_brokerage, 2, '.', '') }}"
              data-broker-sales-total="{{ number_format($brokerSalesTotal, 2, '.', '') }}"
              data-broker-summary-total="{{ number_format($brokerSummaryTotal, 2, '.', '') }}"
              data-paid-brokerage="{{ number_format((float) $broker->paid_brokerage, 2, '.', '') }}"
              data-remaining-brokerage="{{ number_format($remaining, 2, '.', '') }}"
              data-notes="{{ $broker->notes }}"
              data-status="{{ $broker->status ? 1 : 0 }}"
              data-search="{{ strtolower(trim($broker->name . ' ' . ($broker->phone ?? '') . ' ' . ($broker->city ?? ''))) }}">
            <span class="entity-name">{{ $broker->name }}</span>
            <span class="entity-balance" style="color: {{ $brokerSalesTotal > 0 ? '#dc2626' : ($brokerSalesTotal < 0 ? '#0f9d58' : '#6b7280') }} !important;">
              Rs {{ number_format($brokerSalesTotal, 2) }}
            </span>
          </li>
        @endforeach
      </ul>

    </ul>
  </div>

  <!-- Right: Broker Details -->
  <div class="split-right">
    <div class="detail-panel-header">
      <div>
        <div style="display: flex;">
          <div class="entity-detail-name" id="brokerDetailName" style="font-weight: 400;">
            Select a broker
          </div>
          <button class="btn-icon" id="editBrokerBtn" title="Edit" style="display: none;">
            <i class="fa-solid fa-pen"></i>
          </button>
        </div>

        <div class="entity-detail-meta-row">
          <div class="entity-detail-meta">
            <div class="meta-heading">Phone Number</div>
            <div class="meta-value" id="brokerPhone">-</div>
          </div>

          <div class="entity-detail-meta">
            <div class="meta-heading">City</div>
            <div class="meta-value" id="brokerCity">-</div>
          </div>

          <div class="entity-detail-meta">
            <div class="meta-heading">Commission Rate</div>
            <div class="meta-value" id="brokerCommissionRate">-</div>
          </div>

          <div class="entity-detail-meta">
            <div class="meta-heading">Address</div>
            <div class="meta-value" id="brokerAddress">-</div>
          </div>
        </div>
      </div>
    </div>

    <div class="detail-panel-body">
      <div class="table-header">
        <div style="display:flex;align-items:center;gap:12px;">
          <h6 class="fw-600 mb-3" style="font-size: 14px !important; margin-bottom:0 !important;">Brokerage Summary</h6>
        </div>
      </div>

      <div class="broker-summary-cards">
        <div class="summary-card">
          <div class="summary-label">Total Brokerage</div>
          <div class="summary-value" id="brokerTotalSummary">Rs 0.00</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Broker Amount</div>
          <div class="summary-value" id="brokerAmountSummary">Rs 0.00</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Status</div>
          <div class="summary-value" id="brokerStatusSummary">-</div>
        </div>
      </div>

      <div class="broker-history-panel">
        <div class="broker-history-toolbar">
          <div class="broker-history-filter">
            <label class="form-label">Sale Type</label>
            <select id="brokerSaleTypeFilter" class="form-select">
              <option value="">All</option>
              @foreach($salesTypes as $type)
                <option value="{{ $type }}">{{ ucwords(str_replace(['_', '-'], [' ', ' '], $type)) }}</option>
              @endforeach
            </select>
          </div>

          <div class="broker-history-filter">
            <label class="form-label">From</label>
            <input type="date" id="brokerFromFilter" class="form-control">
          </div>

          <div class="broker-history-filter">
            <label class="form-label">To</label>
            <input type="date" id="brokerToFilter" class="form-control">
          </div>

          <div class="broker-history-filter">
            <label class="form-label">Brokerage</label>
            <select id="brokerBrokerageFilter" class="form-select">
              <option value="">All</option>
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>

        <div class="broker-history-meta" id="brokerHistoryInfo">Select a broker to view transaction history.</div>

        <div class="broker-history-table-container">
          <table class="broker-history-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Bill #</th>
                <th>Party</th>
                <th>Item</th>
                <th>Due Date</th>
                <th>Broker Amount</th>
                <th>Brokerage Type</th>
              </tr>
            </thead>
            <tbody id="brokerHistoryBody">
              <tr class="empty-row">
                <td colspan="7">Select a broker to view transaction history.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div style="margin-top: 20px;">
        <label class="form-label" style="font-size: 13px; color: #6b7280;">Notes</label>
        <div id="brokerNotesSummary" style="padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">
          No notes
        </div>
      </div>
    </div>
  </div>

</div>

@endsection

@section('modals')

<!-- MODAL: ADD BROKER -->
<div class="modal fade" id="addBrokerModal" tabindex="-1" aria-labelledby="addBrokerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addBrokerModalLabel"><i class="fa-solid fa-user-plus me-2"></i>Add Broker</h5>
        <div class="d-flex align-items-center gap-2" style="margin-left:auto;">
          <button class="btn btn-sm btn-outline-secondary" type="button" id="brokerModalSettingsTrigger" title="Settings"><i class="fa-solid fa-gear"></i></button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body">
        <form id="addBrokerForm" action="{{ route('brokers.store') }}" method="POST">
          @csrf
          <input type="hidden" id="brokerId" name="broker_id" value="">
          <input type="hidden" id="brokerFormMethod" name="_method" value="POST">

          <div class="row g-3 mb-4">
            <div class="col-md-6" data-broker-setting="name">
              <label class="form-label fw-600">Broker Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Enter broker name" id="brokerNameInput" required>
            </div>
            <div class="col-md-6" data-broker-setting="phone">
              <label class="form-label fw-600">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                <input type="tel" name="phone" class="form-control" placeholder="Enter phone number" id="brokerPhoneInput">
              </div>
            </div>
            <div class="col-md-6" data-broker-setting="city">
              <label class="form-label fw-600">City</label>
              <input type="text" name="city" class="form-control" placeholder="Enter city" id="brokerCityInput">
            </div>
            <div class="col-md-6" data-broker-setting="address">
              <label class="form-label fw-600">Address</label>
              <textarea name="address" class="form-control" placeholder="Enter address" id="brokerAddressInput" rows="2"></textarea>
            </div>
            <div class="col-md-6" data-broker-setting="commission_type">
              <label class="form-label fw-600">Commission Type</label>
              <select name="commission_type" class="form-select" id="brokerCommissionTypeInput" required>
                <option value="">Select type</option>
                <option value="fixed">Fixed</option>
                <option value="percent">Percent (%)</option>
              </select>
            </div>
            <div class="col-md-6" data-broker-setting="commission_rate">
              <label class="form-label fw-600">Commission Rate</label>
              <div class="input-group">
                <input type="number" name="commission_rate" class="form-control" placeholder="0.00" id="brokerCommissionRateInput" step="0.01" min="0">
                <span class="input-group-text" id="commissionRateUnit">Rs</span>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs" id="brokerModalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="broker-balance-tab" data-bs-toggle="tab" data-bs-target="#brokerBalancePane" type="button" role="tab">
                <i class="fa-solid fa-wallet me-1"></i> Brokerage & Balance
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="broker-additional-tab" data-bs-toggle="tab" data-bs-target="#brokerAdditionalPane" type="button" role="tab">
                <i class="fa-solid fa-sliders me-1"></i> Additional Info
              </button>
            </li>
          </ul>

          <div class="tab-content pt-3" id="brokerModalTabContent">
            <!-- Brokerage & Balance Tab -->
            <div class="tab-pane fade show active" id="brokerBalancePane" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4" data-broker-setting="total_brokerage">
                  <label class="form-label">Total Brokerage</label>
                  <div class="input-group">
                    <span class="input-group-text">Rs</span>
                    <input type="number" name="total_brokerage" class="form-control" placeholder="0.00" id="brokerTotalBrokerageInput" step="0.01" min="0">
                  </div>
                </div>
                <div class="col-md-4" data-broker-setting="paid_brokerage">
                  <label class="form-label">Paid Brokerage</label>
                  <div class="input-group">
                    <span class="input-group-text">Rs</span>
                    <input type="number" name="paid_brokerage" class="form-control" placeholder="0.00" id="brokerPaidBrokerageInput" step="0.01" min="0">
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Remaining</label>
                  <input type="text" class="form-control" id="brokerRemainingPreview" value="Rs 0.00" readonly>
                </div>
              </div>
            </div>

            <!-- Additional Info Tab -->
            <div class="tab-pane fade" id="brokerAdditionalPane" role="tabpanel" data-broker-setting="additional_info">
              <div class="row g-3">
                <div class="col-md-6" data-broker-setting="status">
                  <label class="form-label d-block">Status</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="brokerStatusInput" name="status" value="1">
                    <label class="form-check-label" for="brokerStatusInput">Keep broker active</label>
                  </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-12" data-broker-setting="notes">
                  <label class="form-label">Notes / Description</label>
                  <textarea name="notes" class="form-control" placeholder="Add any additional notes about this broker" id="brokerNotesInput" rows="3"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" id="btnSaveNewBroker">
              <i class="fa-solid fa-plus me-1"></i> Save & New
            </button>
            <button type="button" class="btn btn-primary" id="btnSaveBroker">
              <i class="fa-solid fa-check me-1"></i> Save
            </button>
            <button type="button" class="btn btn-primary" id="btnUpdateBroker" style="display:none;">Update</button>
            <button type="button" class="btn btn-danger" id="btnDeleteBroker" style="display:none;">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Broker Settings Drawer -->
<div class="broker-settings-drawer" id="brokerSettingsDrawer">
  <div class="broker-settings-backdrop" data-close-broker-settings="true"></div>
  <div class="broker-settings-panel">
    <div class="broker-settings-header">
      <h4>Broker Settings</h4>
      <button type="button" class="broker-settings-close" id="brokerSettingsClose">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="broker-settings-group">
      <div class="broker-settings-group-title">General</div>
      <label class="broker-settings-item">
        <span>Show Phone Number <i class="fa-regular fa-circle-info broker-settings-info"></i></span>
        <input type="checkbox" class="broker-setting-toggle" data-setting-target="phone" checked>
      </label>
      <label class="broker-settings-item">
        <span>Show Address <i class="fa-regular fa-circle-info broker-settings-info"></i></span>
        <input type="checkbox" class="broker-setting-toggle" data-setting-target="address" checked>
      </label>
      <label class="broker-settings-item">
        <span>Show Commission Details <i class="fa-regular fa-circle-info broker-settings-info"></i></span>
        <input type="checkbox" class="broker-setting-toggle" data-setting-target="commission" checked>
      </label>
      <label class="broker-settings-item">
        <span>Show Brokerage Balance <i class="fa-regular fa-circle-info broker-settings-info"></i></span>
        <input type="checkbox" class="broker-setting-toggle" data-setting-target="balance" checked>
      </label>
    </div>
  </div>
</div>

<!-- Broker More Menu -->
<div class="broker-more-menu" id="brokerMoreMenu">
  <button type="button" class="broker-more-menu-item" id="importExcelOption">Import from Excel</button>
  <button type="button" class="broker-more-menu-item" id="exportExcelOption">Export to Excel</button>
</div>

<input type="file" id="brokerExcelImportInput" accept=".csv,.xls,.xlsx" hidden>

@endsection

@push('scripts')
<script>
  (function () {
    const brokerModal = document.getElementById('addBrokerModal');
    const brokerForm = document.getElementById('addBrokerForm');
    const brokersList = document.getElementById('brokersList');
    const brokerSearchInput = document.getElementById('brokerSearchInput');
    const brokerDetailName = document.getElementById('brokerDetailName');
    const editBrokerBtn = document.getElementById('editBrokerBtn');
    const brokerSettingsDrawer = document.getElementById('brokerSettingsDrawer');
    const brokerMoreMenu = document.getElementById('brokerMoreMenu');
    const brokerSettingsTrigger = document.getElementById('brokerSettingsTrigger');
    const brokerMoreOptionsTrigger = document.getElementById('brokerMoreOptionsTrigger');
    const brokerModalLabel = document.getElementById('addBrokerModalLabel');
    const brokerId = document.getElementById('brokerId');
    const brokerFormMethod = document.getElementById('brokerFormMethod');
    const brokerSaleTypeFilter = document.getElementById('brokerSaleTypeFilter');
    const brokerFromFilter = document.getElementById('brokerFromFilter');
    const brokerToFilter = document.getElementById('brokerToFilter');
    const brokerBrokerageFilter = document.getElementById('brokerBrokerageFilter');
    const brokerHistoryBody = document.getElementById('brokerHistoryBody');
    const brokerHistoryInfo = document.getElementById('brokerHistoryInfo');

    const fields = {
      name: document.getElementById('brokerNameInput'),
      phone: document.getElementById('brokerPhoneInput'),
      city: document.getElementById('brokerCityInput'),
      address: document.getElementById('brokerAddressInput'),
      commissionType: document.getElementById('brokerCommissionTypeInput'),
      commissionRate: document.getElementById('brokerCommissionRateInput'),
      totalBrokerage: document.getElementById('brokerTotalBrokerageInput'),
      paidBrokerage: document.getElementById('brokerPaidBrokerageInput'),
      remaining: document.getElementById('brokerRemainingPreview'),
      status: document.getElementById('brokerStatusInput'),
      notes: document.getElementById('brokerNotesInput'),
    };

    let selectedBrokerElement = null;
    let brokerModalMode = 'create';

    // Format currency
    const formatCurrency = (value) => `Rs ${Number(value || 0).toFixed(2)}`;

    // Update remaining preview
    const updateRemainingPreview = () => {
      const total = Number(fields.totalBrokerage.value || 0);
      const paid = Number(fields.paidBrokerage.value || 0);
      const remaining = Math.max(0, total - paid);
      fields.remaining.value = formatCurrency(remaining);
    };

    // Update commission rate unit
    const updateCommissionRateUnit = () => {
      const unit = fields.commissionType.value === 'percent' ? '%' : 'Rs';
      document.getElementById('commissionRateUnit').textContent = unit;
    };

    // Display broker details
    const displayBrokerDetails = (brokerItem) => {
      const totalBrokerAmount = Number(brokerItem.dataset.brokerSalesTotal || 0);
      brokerDetailName.textContent = brokerItem.dataset.name;
      document.getElementById('brokerPhone').textContent = brokerItem.dataset.phone || '-';
      document.getElementById('brokerCity').textContent = brokerItem.dataset.city || '-';
      document.getElementById('brokerAddress').textContent = brokerItem.dataset.address || '-';

      const commissionType = brokerItem.dataset.commissionType;
      const commissionRate = brokerItem.dataset.commissionRate;
      const commissionLabel = commissionType === 'percent'
        ? `${commissionRate}%`
        : `Rs ${commissionRate}`;
      document.getElementById('brokerCommissionRate').textContent = commissionLabel;

      document.getElementById('brokerTotalSummary').textContent = formatCurrency(brokerItem.dataset.brokerSummaryTotal || 0);
      document.getElementById('brokerAmountSummary').textContent = formatCurrency(totalBrokerAmount);

      const status = brokerItem.dataset.status === '1' ? 'Active' : 'Inactive';
      document.getElementById('brokerStatusSummary').textContent = status;

      document.getElementById('brokerNotesSummary').textContent = brokerItem.dataset.notes || 'No notes';

      editBrokerBtn.style.display = 'block';
    };

    const getBrokerHistoryFilters = () => {
      return {
        type: brokerSaleTypeFilter?.value || '',
        from: brokerFromFilter?.value || '',
        to: brokerToFilter?.value || '',
        brokerage: brokerBrokerageFilter?.value || '',
      };
    };

    const renderBrokerHistory = (sales) => {
      brokerHistoryBody.innerHTML = '';

      if (!sales || sales.length === 0) {
        brokerHistoryBody.innerHTML = '<tr class="empty-row"><td colspan="7">No matching transactions found.</td></tr>';
        brokerHistoryInfo.textContent = 'No transactions match the selected filters.';
        return;
      }

      sales.forEach((sale) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${sale.invoice_date || '-'}</td>
          <td>${sale.bill_number || '-'}</td>
          <td>${sale.party_name || '-'}</td>
          <td>${sale.item_names || '-'}</td>
          <td>${sale.due_date || '-'}</td>
          <td>Rs ${Number(sale.broker_amount || 0).toFixed(2)}</td>
          <td>${sale.brokerage_type || '-'}</td>
        `;
        brokerHistoryBody.appendChild(row);
      });

      brokerHistoryInfo.textContent = `${sales.length} transaction${sales.length === 1 ? '' : 's'} shown.`;
    };

    const loadBrokerHistory = async (brokerId) => {
      if (!brokerId) {
        brokerHistoryBody.innerHTML = '<tr class="empty-row"><td colspan="7">Select a broker to view transaction history.</td></tr>';
        brokerHistoryInfo.textContent = 'Select a broker to view transaction history.';
        return;
      }

      brokerHistoryBody.innerHTML = '<tr class="empty-row"><td colspan="7">Loading transaction history...</td></tr>';
      brokerHistoryInfo.textContent = 'Loading transaction history...';

      const params = new URLSearchParams(getBrokerHistoryFilters());
      const url = `/dashboard/brokers/${brokerId}/history?${params.toString()}`;

      try {
        const response = await fetch(url, {
          headers: {
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Unable to load broker history.');
        }

        const data = await response.json();
        renderBrokerHistory(data.sales || []);
      } catch (error) {
        console.error(error);
        brokerHistoryBody.innerHTML = '<tr class="empty-row"><td colspan="7">Unable to load history.</td></tr>';
        brokerHistoryInfo.textContent = 'Unable to load history.';
      }
    };

    const updateBrokerHistory = () => {
      if (!selectedBrokerElement) return;
      loadBrokerHistory(selectedBrokerElement.dataset.id);
    };

    [brokerSaleTypeFilter, brokerFromFilter, brokerToFilter, brokerBrokerageFilter].forEach((filter) => {
      filter?.addEventListener('change', updateBrokerHistory);
    });

    // Handle broker item click - display details
    brokersList.addEventListener('click', (e) => {
      const brokerItem = e.target.closest('.broker-item');
      if (brokerItem) {
        // Remove active class from previous
        document.querySelectorAll('.broker-item.active').forEach(el => el.classList.remove('active'));
        brokerItem.classList.add('active');
        selectedBrokerElement = brokerItem;
        displayBrokerDetails(brokerItem);
        loadBrokerHistory(brokerItem.dataset.id);
      }
    });

    // Initialize history state
    loadBrokerHistory(null);

    // Reset form for create
    const resetFormForCreate = () => {
      brokerModalMode = 'create';
      brokerForm.action = "{{ route('brokers.store') }}";
      brokerForm.method = 'POST';
      brokerFormMethod.value = 'POST';
      brokerModalLabel.textContent = 'Add Broker';
      brokerForm.reset();
      brokerId.value = '';
      fields.status.checked = true;
      fields.commissionType.value = '';
      updateCommissionRateUnit();
      document.getElementById('btnSaveBroker').style.display = 'block';
      document.getElementById('btnSaveNewBroker').style.display = 'block';
      document.getElementById('btnUpdateBroker').style.display = 'none';
      document.getElementById('btnDeleteBroker').style.display = 'none';
      updateRemainingPreview();
    };

    // Handle edit button
    editBrokerBtn.addEventListener('click', () => {
      if (!selectedBrokerElement) return;

      const modal = bootstrap.Modal.getOrCreateInstance(brokerModal);
      brokerModalMode = 'edit';
      brokerForm.method = 'POST';
      brokerFormMethod.value = 'PUT';
      brokerModalLabel.textContent = 'Edit Broker';

      brokerId.value = selectedBrokerElement.dataset.id;
      fields.name.value = selectedBrokerElement.dataset.name || '';
      fields.phone.value = selectedBrokerElement.dataset.phone || '';
      fields.city.value = selectedBrokerElement.dataset.city || '';
      fields.address.value = selectedBrokerElement.dataset.address || '';
      fields.commissionType.value = selectedBrokerElement.dataset.commissionType || '';
      fields.commissionRate.value = selectedBrokerElement.dataset.commissionRate || '0';
      fields.totalBrokerage.value = selectedBrokerElement.dataset.totalBrokerage || '0';
      fields.paidBrokerage.value = selectedBrokerElement.dataset.paidBrokerage || '0';
      fields.status.checked = selectedBrokerElement.dataset.status === '1';
      fields.notes.value = selectedBrokerElement.dataset.notes || '';

      document.getElementById('btnSaveBroker').style.display = 'none';
      document.getElementById('btnSaveNewBroker').style.display = 'none';
      document.getElementById('btnUpdateBroker').style.display = 'block';
      document.getElementById('btnDeleteBroker').style.display = 'block';

      updateCommissionRateUnit();
      updateRemainingPreview();

      brokerForm.action = `/dashboard/brokers/${brokerId.value}`;
      modal.show();
    });

    // Handle form submission
    document.getElementById('btnSaveBroker').addEventListener('click', async (e) => {
      e.preventDefault();
      await submitBrokerForm(false);
    });

    document.getElementById('btnSaveNewBroker').addEventListener('click', async (e) => {
      e.preventDefault();
      await submitBrokerForm(true);
    });

    document.getElementById('btnUpdateBroker').addEventListener('click', async (e) => {
      e.preventDefault();
      await submitBrokerForm(false, true);
    });

    async function submitBrokerForm(saveNew = false, isUpdate = false) {
      if (!brokerForm.reportValidity()) {
        return;
      }

      const formData = new FormData(brokerForm);
      const method = (brokerFormMethod.value || 'POST').toUpperCase();
      const url = brokerForm.action || "{{ route('brokers.store') }}";
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

      formData.set('_method', method === 'PUT' ? 'PUT' : 'POST');

      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: formData,
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

        if (response.ok) {
          showSuccessToast(isUpdate ? 'Broker updated successfully' : 'Broker added successfully');

          if (!saveNew) {
            const modal = bootstrap.Modal.getInstance(brokerModal);
            modal?.hide();
          }

          window.location.reload();
        } else {
          const message = data?.message || (data?.errors ? 'Please review the form values and try again.' : 'Failed to save broker');
          showErrorToast(message);
        }
      } catch (error) {
        console.error('Error:', error);
        showErrorToast('An error occurred while saving broker');
      }
    }

    // Handle delete button
    document.getElementById('btnDeleteBroker').addEventListener('click', async () => {
      if (!confirm('Are you sure you want to delete this broker?')) return;

      try {
        const response = await fetch(`/dashboard/brokers/${brokerId.value}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          }
        });

        if (response.ok) {
          const modal = bootstrap.Modal.getInstance(brokerModal);
          modal.hide();
          location.reload();
          showSuccessToast('Broker deleted successfully');
        } else {
          showErrorToast('Failed to delete broker');
        }
      } catch (error) {
        console.error('Error:', error);
        showErrorToast('An error occurred while deleting broker');
      }
    });

    // Modal open event
    brokerModal.addEventListener('show.bs.modal', (event) => {
      const trigger = event.relatedTarget;
      const isCreateTrigger = Boolean(trigger && trigger.getAttribute('data-bs-target') === '#addBrokerModal');
      if (brokerModalMode !== 'edit' && isCreateTrigger) {
        resetFormForCreate();
      }
    });

    brokerModal.addEventListener('hidden.bs.modal', () => {
      brokerModalMode = 'create';
    });

    // Update remaining when values change
    [fields.totalBrokerage, fields.paidBrokerage].forEach((input) => {
      input?.addEventListener('input', updateRemainingPreview);
    });

    // Update commission rate unit when type changes
    fields.commissionType?.addEventListener('change', updateCommissionRateUnit);

    // Search functionality
    brokerSearchInput?.addEventListener('input', () => {
      const query = brokerSearchInput.value.trim().toLowerCase();
      const brokerItems = document.querySelectorAll('.broker-item');

      brokerItems.forEach((item) => {
        const searchData = item.dataset.search || '';
        const matches = !query || searchData.includes(query);
        item.style.display = matches ? '' : 'none';
      });
    });

    // Settings drawer toggle
    brokerSettingsTrigger?.addEventListener('click', () => {
      brokerSettingsDrawer.classList.toggle('active');
    });

    document.getElementById('brokerSettingsClose')?.addEventListener('click', () => {
      brokerSettingsDrawer.classList.remove('active');
    });

    document.querySelector('[data-close-broker-settings]')?.addEventListener('click', () => {
      brokerSettingsDrawer.classList.remove('active');
    });

    // More menu toggle
    brokerMoreOptionsTrigger?.addEventListener('click', (e) => {
      e.stopPropagation();
      brokerMoreMenu.classList.toggle('active');
    });

    document.addEventListener('click', () => {
      brokerMoreMenu.classList.remove('active');
    });

    // Helper functions
    function showSuccessToast(message) {
      // Implement your toast notification here
      console.log('Success:', message);
    }

    function showErrorToast(message) {
      // Implement your toast notification here
      console.log('Error:', message);
    }

    // Initialize
    updateRemainingPreview();
  })();

  // Header dropdown toggle
  function toggleHeaderDropdown(icon) {
    const menu = icon.nextElementSibling;
    menu?.classList.toggle('active');
  }

  // Filter toggle
  function toggleFilter() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown?.classList.toggle('active');
  }
</script>
@endpush
