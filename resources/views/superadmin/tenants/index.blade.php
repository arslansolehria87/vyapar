@extends('superadmin.layout')

@section('title', 'Customers & Businesses')

@section('content')

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Customer Businesses (Tenants)</h1>
        <p class="text-muted small mb-0">Manage customer subscriptions, toggle access on/off, customize pricing, and configure enabled modules.</p>
    </div>
    <div>
        <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-cs">
            <i class="fa-solid fa-plus me-1.5"></i> Add New Customer
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card-box p-3 mb-4">
    <div class="row g-2 align-items-center justify-content-between">
        <!-- Status Tabs -->
        <div class="col-md-auto">
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('superadmin.tenants.index', ['status' => 'all', 'search' => request('search')]) }}"
                   class="btn {{ $filter === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    All Clients
                </a>
                <a href="{{ route('superadmin.tenants.index', ['status' => 'active', 'search' => request('search')]) }}"
                   class="btn {{ $filter === 'active' ? 'btn-success' : 'btn-outline-secondary' }}">
                    Active Only
                </a>
                <a href="{{ route('superadmin.tenants.index', ['status' => 'suspended', 'search' => request('search')]) }}"
                   class="btn {{ $filter === 'suspended' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    Suspended (Access Off)
                </a>
                <a href="{{ route('superadmin.tenants.index', ['status' => 'expired', 'search' => request('search')]) }}"
                   class="btn {{ $filter === 'expired' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                    Expired
                </a>
            </div>
        </div>

        <!-- Search Input -->
        <div class="col-md-4">
            <form method="GET" action="{{ route('superadmin.tenants.index') }}" class="d-flex gap-2">
                <input type="hidden" name="status" value="{{ $filter }}">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search business, owner, phone...">
                </div>
                <button type="submit" class="btn btn-sm btn-outline-dark">Search</button>
            </form>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card-box p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Business & Location</th>
                    <th>Owner / Contact</th>
                    <th>Negotiated Fee</th>
                    <th>Features Enabled</th>
                    <th>Subscription Expiry</th>
                    <th>Access Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr id="tenant-row-{{ $tenant->id }}">
                    <!-- Business -->
                    <td class="ps-4">
                        <div class="fw-bold text-dark">{{ $tenant->name }}</div>
                        <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i>{{ $tenant->city ?: 'Pakistan' }}</small>
                    </td>

                    <!-- Owner -->
                    <td>
                        <div class="fw-semibold text-dark">{{ $tenant->user?->name ?? 'N/A' }}</div>
                        <small class="text-muted d-block">{{ $tenant->user?->email }}</small>
                        @if($tenant->phone || $tenant->user?->phone)
                            <small class="text-muted"><i class="fa-solid fa-phone me-1"></i>{{ $tenant->phone ?: $tenant->user?->phone }}</small>
                        @endif
                    </td>

                    <!-- Fee -->
                    <td>
                        <span class="fw-bold text-dark fs-6">PKR {{ number_format($tenant->monthly_price, 0) }}</span>
                        <small class="text-muted d-block">/ month</small>
                    </td>

                    <!-- Features -->
                    <td>
                        @php
                            $featureCount = is_array($tenant->enabled_features) 
                                ? (in_array('*', $tenant->enabled_features) ? count($allFeatures) : count($tenant->enabled_features))
                                : count($allFeatures);
                        @endphp
                        <span class="badge" style="background: rgba(88, 19, 188, 0.1); color: var(--cs-purple); font-size: 12px;">
                            {{ $featureCount }} of {{ count($allFeatures) }} modules
                        </span>
                        <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="text-decoration-none ms-1 text-muted small" title="Configure Modules">
                            <i class="fa-solid fa-gear"></i>
                        </a>
                    </td>

                    <!-- Expiry -->
                    <td>
                        @if($tenant->subscription_expires_at)
                            <div>
                                <span class="fw-semibold {{ $tenant->subscription_expires_at->isPast() ? 'text-danger' : 'text-dark' }}">
                                    {{ $tenant->subscription_expires_at->format('d M Y') }}
                                </span>
                            </div>
                            <small class="{{ $tenant->subscription_expires_at->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                @if($tenant->subscription_expires_at->isPast())
                                    <i class="fa-solid fa-circle-exclamation me-1"></i> Expired {{ $tenant->subscription_expires_at->diffForHumans() }}
                                @else
                                    {{ $tenant->daysUntilExpiry() }} days remaining
                                @endif
                            </small>
                        @else
                            <span class="badge bg-secondary">Lifetime</span>
                        @endif
                    </td>

                    <!-- 1-Click Active/Suspended Switch -->
                    <td>
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input toggle-saas" type="checkbox" role="switch"
                                   id="toggle-{{ $tenant->id }}"
                                   data-tenant-id="{{ $tenant->id }}"
                                   data-tenant-name="{{ $tenant->name }}"
                                   {{ $tenant->is_active ? 'checked' : '' }}
                                   onchange="toggleTenantStatus(this)">
                            <label class="form-check-label small fw-bold {{ $tenant->is_active ? 'text-success' : 'text-danger' }}" id="status-label-{{ $tenant->id }}">
                                {{ $tenant->is_active ? 'Active' : 'Suspended' }}
                            </label>
                        </div>
                    </td>

                    <!-- Actions Dropdown & Buttons -->
                    <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                            <!-- Quick Renew Modal Trigger -->
                            <button type="button" class="btn btn-outline-success rounded-pill px-3 py-1 me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#extendModal"
                                    data-tenant-id="{{ $tenant->id }}"
                                    data-tenant-name="{{ $tenant->name }}"
                                    data-current-expiry="{{ $tenant->subscription_expires_at ? $tenant->subscription_expires_at->format('Y-m-d') : '' }}">
                                <i class="fa-solid fa-calendar-plus me-1"></i> Renew
                            </button>

                            <!-- Manage / Edit -->
                            <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="btn btn-outline-primary rounded-pill px-3 py-1 me-1">
                                <i class="fa-solid fa-sliders me-1"></i> Edit
                            </a>

                            <!-- Impersonate -->
                            <form method="POST" action="{{ route('superadmin.tenants.impersonate', $tenant) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-dark rounded-pill px-2.5 py-1" title="Login into this customer workspace">
                                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-store fs-1 text-muted opacity-50 mb-3 d-block"></i>
                        <h5>No customer businesses found</h5>
                        <p class="small text-muted mb-3">No matching records. Start by onboarding your first paying business customer.</p>
                        <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-cs btn-sm">
                            <i class="fa-solid fa-plus me-1"></i> Add New Customer
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tenants->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">Showing {{ $tenants->firstItem() }} to {{ $tenants->lastItem() }} of {{ $tenants->total() }} clients</span>
        {{ $tenants->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Renew / Extend Subscription Modal -->
<div class="modal fade" id="extendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="extendForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="extendModalTitle">Extend Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3" id="extendModalSubtitle">
                        Select how many months to extend or enter a specific custom date.
                    </p>

                    <!-- Quick Month Presets -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Quick Extend</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-2" onclick="setMonths(1)">+1 Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-2" onclick="setMonths(3)">+3 Months</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-2" onclick="setMonths(6)">+6 Months</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-2" onclick="setMonths(12)">+1 Year</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="monthsInput" class="form-label fw-semibold small text-dark">Months to Add</label>
                        <input type="number" name="months" id="monthsInput" value="1" min="1" max="60" class="form-control rounded-3">
                    </div>

                    <div class="mb-2">
                        <label for="customDateInput" class="form-label fw-semibold small text-dark">Or Set Specific Expiry Date</label>
                        <input type="date" name="custom_date" id="customDateInput" class="form-control rounded-3">
                    </div>

                    <div class="alert alert-info py-2 px-3 small rounded-3 mt-3 mb-0" style="background: #F3F3FF; border: 1px solid #D4C2DF; color: #5813BC;">
                        <i class="fa-solid fa-circle-check me-1"></i> Extending will automatically set the business status to <strong>Active</strong>.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cs rounded-pill px-4">Confirm Renewal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // 1-Click Toggle Tenant Active / Suspended via AJAX
    function toggleTenantStatus(checkbox) {
        const tenantId = checkbox.dataset.tenantId;
        const tenantName = checkbox.dataset.tenantName;
        const label = document.getElementById('status-label-' + tenantId);
        
        checkbox.disabled = true;

        fetch(`/super-admin/tenants/${tenantId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            checkbox.disabled = false;
            if (data.success) {
                if (data.is_active) {
                    label.textContent = 'Active';
                    label.classList.remove('text-danger');
                    label.classList.add('text-success');
                } else {
                    label.textContent = 'Suspended';
                    label.classList.remove('text-success');
                    label.classList.add('text-danger');
                }
            } else {
                alert('Error updating status: ' + (data.message || 'Unknown error'));
                checkbox.checked = !checkbox.checked;
            }
        })
        .catch(err => {
            checkbox.disabled = false;
            checkbox.checked = !checkbox.checked;
            alert('Failed to connect to server.');
        });
    }

    // Modal populate logic
    const extendModal = document.getElementById('extendModal');
    if (extendModal) {
        extendModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const tenantId = button.getAttribute('data-tenant-id');
            const tenantName = button.getAttribute('data-tenant-name');
            
            document.getElementById('extendModalTitle').textContent = `Extend Subscription: ${tenantName}`;
            document.getElementById('extendForm').action = `/super-admin/tenants/${tenantId}/extend`;
            document.getElementById('monthsInput').value = 1;
            document.getElementById('customDateInput').value = '';
        });
    }

    function setMonths(val) {
        document.getElementById('monthsInput').value = val;
        document.getElementById('customDateInput').value = '';
    }
</script>
@endpush
