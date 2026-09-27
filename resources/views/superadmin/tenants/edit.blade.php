@extends('superadmin.layout')

@section('title', 'Manage ' . $tenant->name)

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-10">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('superadmin.tenants.index') }}" class="text-decoration-none text-muted small mb-1 d-inline-block">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Customer Directory
                </a>
                <h1 class="h3 fw-bold text-dark mb-0">Manage: {{ $tenant->name }}</h1>
            </div>
            
            <!-- Quick Impersonate -->
            <form method="POST" action="{{ route('superadmin.tenants.impersonate', $tenant) }}">
                @csrf
                <button type="submit" class="btn btn-outline-dark rounded-pill btn-sm px-3">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Login into Customer Workspace
                </button>
            </form>
        </div>

        <form method="POST" action="{{ route('superadmin.tenants.update', $tenant) }}">
            @csrf
            @method('PUT')

            <!-- Business & Pricing Info -->
            <div class="card-box p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-3 p-2" style="background: rgba(88, 19, 188, 0.1); color: var(--cs-purple);">
                        <i class="fa-solid fa-store fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Business Profile & Agreed Pricing</h5>
                        <small class="text-muted">Modify negotiated monthly rate and contact information.</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="business_name" class="form-label small fw-semibold text-dark">Business / Shop Name <span class="text-danger">*</span></label>
                        <input type="text" name="business_name" id="business_name" value="{{ old('business_name', $tenant->name) }}" required
                               class="form-control rounded-3">
                        @error('business_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="monthly_price" class="form-label small fw-semibold text-dark">Negotiated Monthly Price (PKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-dark">PKR</span>
                            <input type="number" name="monthly_price" id="monthly_price" value="{{ old('monthly_price', $tenant->monthly_price) }}" min="0" step="50" required
                                   class="form-control rounded-end-3 fs-5 fw-bold">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="owner_name" class="form-label small fw-semibold text-dark">Owner Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name', $tenant->user?->name) }}" required
                               class="form-control rounded-3">
                    </div>

                    <div class="col-md-4">
                        <label for="phone" class="form-label small fw-semibold text-dark">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $tenant->phone ?? $tenant->user?->phone) }}" class="form-control rounded-3">
                    </div>

                    <div class="col-md-4">
                        <label for="city" class="form-label small fw-semibold text-dark">City</label>
                        <input type="text" name="city" id="city" value="{{ old('city', $tenant->city) }}" class="form-control rounded-3">
                    </div>

                    <div class="col-md-6">
                        <label for="address" class="form-label small fw-semibold text-dark">Address</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $tenant->address) }}" class="form-control rounded-3">
                    </div>

                    <div class="col-md-6">
                        <label for="new_password" class="form-label small fw-semibold text-dark">Reset Password (leave blank to keep current)</label>
                        <input type="text" name="new_password" id="new_password" class="form-control rounded-3" placeholder="New password if client requested reset">
                    </div>
                </div>
            </div>

            <!-- Features Checklist -->
            <div class="card-box p-4 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 p-2" style="background: rgba(172, 34, 203, 0.1); color: var(--cs-magenta);">
                            <i class="fa-solid fa-sliders fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Configured Features for this Plan</h5>
                            <small class="text-muted">Turn features on or off as this customer upgrades or downgrades.</small>
                        </div>
                    </div>

                    <!-- Presets -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" onclick="presetFeatures('all')">Select All</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="presetFeatures('standard')">Standard Package</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="presetFeatures('basic')">Basic Only</button>
                    </div>
                </div>

                @php
                    $tenantFeatures = is_array($tenant->enabled_features) ? $tenant->enabled_features : ['*'];
                    $hasAll = in_array('*', $tenantFeatures);
                @endphp

                <!-- Features Grid -->
                <div class="row g-3">
                    @foreach($allFeatures as $key => $feature)
                    @php
                        $isChecked = $hasAll || in_array($key, $tenantFeatures);
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3 border h-100 feature-card transition-all" style="background: #FAFAFD;">
                            <div class="form-check d-flex align-items-start gap-2">
                                <input class="form-check-input mt-1 feature-checkbox" type="checkbox" name="features[]"
                                       value="{{ $key }}" id="feature_{{ $key }}" {{ $isChecked ? 'checked' : '' }}>
                                <label class="form-check-label w-100 cursor-pointer" for="feature_{{ $key }}">
                                    <div class="d-flex align-items-center gap-1.5 mb-1">
                                        <i class="fa-solid {{ $feature['icon'] ?? 'fa-cube' }}" style="color: var(--cs-purple); font-size: 13px;"></i>
                                        <strong class="text-dark small">{{ $feature['name'] }}</strong>
                                    </div>
                                    <p class="text-muted mb-0" style="font-size: 11.5px; line-height: 1.35;">
                                        {{ $feature['description'] }}
                                    </p>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-light px-4 rounded-pill">Cancel</a>
                <button type="submit" class="btn btn-cs px-5 rounded-pill fs-6">
                    <i class="fa-solid fa-save me-2"></i> Save Changes
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const presets = {
        all: @json(array_keys($allFeatures)),
        standard: ['pos', 'estimates', 'proforma', 'orders', 'sale_return', 'banking', 'expenses', 'reports_basic', 'reminders'],
        basic: ['reports_basic', 'sale_return', 'expenses'],
    };

    function presetFeatures(type) {
        const selected = presets[type] || [];
        document.querySelectorAll('.feature-checkbox').forEach(cb => {
            cb.checked = selected.includes(cb.value);
        });
    }
</script>
@endpush
