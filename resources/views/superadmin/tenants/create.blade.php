@extends('superadmin.layout')

@section('title', 'Onboard New Customer')

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-10">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('superadmin.tenants.index') }}" class="text-decoration-none text-muted small mb-1 d-inline-block">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Customer Directory
                </a>
                <h1 class="h3 fw-bold text-dark mb-0">Onboard New Business Client</h1>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.tenants.store') }}">
            @csrf

            <!-- Card 1: Business Details -->
            <div class="card-box p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-3 p-2" style="background: rgba(88, 19, 188, 0.1); color: var(--cs-purple);">
                        <i class="fa-solid fa-store fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Business Profile</h5>
                        <small class="text-muted">Enter the company/shop details for this new client.</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="business_name" class="form-label small fw-semibold text-dark">Business / Shop Name <span class="text-danger">*</span></label>
                        <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" required
                               class="form-control rounded-3" placeholder="e.g. Al-Madina Super Store, Apex Solutions">
                        @error('business_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="city" class="form-label small fw-semibold text-dark">City</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" class="form-control rounded-3" placeholder="e.g. Lahore, Karachi">
                    </div>

                    <div class="col-md-3">
                        <label for="phone" class="form-label small fw-semibold text-dark">Business Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control rounded-3" placeholder="e.g. 03001234567">
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label small fw-semibold text-dark">Shop / Office Address</label>
                        <textarea name="address" id="address" rows="2" class="form-control rounded-3" placeholder="Full address or market location...">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Card 2: Owner Login Account -->
            <div class="card-box p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-3 p-2" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fa-solid fa-user-shield fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Owner Account Credentials</h5>
                        <small class="text-muted">These login credentials will be used by the customer to sign in.</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="owner_name" class="form-label small fw-semibold text-dark">Owner Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name') }}" required
                               class="form-control rounded-3" placeholder="e.g. Muhammad Asif">
                        @error('owner_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="email" class="form-label small fw-semibold text-dark">Login Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="form-control rounded-3" placeholder="e.g. asif@almadina.com">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="password" class="form-label small fw-semibold text-dark">Password <span class="text-danger">*</span></label>
                        <input type="text" name="password" id="password" value="{{ old('password', 'Pass@1234') }}" required
                               class="form-control rounded-3" placeholder="Initial password">
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>

            <!-- Card 3: Subscription & Negotiated Pricing -->
            <div class="card-box p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-3 p-2" style="background: rgba(172, 34, 203, 0.1); color: var(--cs-magenta);">
                        <i class="fa-solid fa-handshake-simple fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Negotiated Pricing & Period</h5>
                        <small class="text-muted">Set the custom agreed fee and starting duration for this client.</small>
                    </div>
                </div>

                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label for="monthly_price" class="form-label small fw-semibold text-dark">Negotiated Monthly Price (PKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-dark">PKR</span>
                            <input type="number" name="monthly_price" id="monthly_price" value="{{ old('monthly_price', '3000') }}" min="0" step="50" required
                                   class="form-control rounded-end-3 fs-5 fw-bold" placeholder="e.g. 2500, 4000, 7500">
                        </div>
                        <small class="text-muted">Any custom amount you agreed with the customer.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="duration_months" class="form-label small fw-semibold text-dark">Initial Duration <span class="text-danger">*</span></label>
                        <select name="duration_months" id="duration_months" class="form-select rounded-3 fs-6 fw-semibold">
                            <option value="1" {{ old('duration_months') == '1' ? 'selected' : '' }}>1 Month (Standard Monthly)</option>
                            <option value="3" {{ old('duration_months') == '3' ? 'selected' : '' }}>3 Months (Quarterly Deal)</option>
                            <option value="6" {{ old('duration_months') == '6' ? 'selected' : '' }}>6 Months (Half-Yearly)</option>
                            <option value="12" {{ old('duration_months') == '12' ? 'selected' : '' }}>12 Months (1 Full Year Advance)</option>
                        </select>
                        <small class="text-muted">Subscription will expire automatically after this period.</small>
                    </div>
                </div>
            </div>

            <!-- Card 4: Modular Features Checklist -->
            <div class="card-box p-4 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 p-2" style="background: rgba(88, 19, 188, 0.1); color: var(--cs-purple);">
                            <i class="fa-solid fa-list-check fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Plan Feature Toggles</h5>
                            <small class="text-muted">Check or uncheck features based on the price paid by the customer.</small>
                        </div>
                    </div>

                    <!-- Presets -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" onclick="presetFeatures('all')">Select All (Full Suite)</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="presetFeatures('standard')">Standard Package</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="presetFeatures('basic')">Basic Invoicing</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="presetFeatures('none')">Clear</button>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="row g-3">
                    @foreach($allFeatures as $key => $feature)
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3 border h-100 feature-card transition-all" style="background: #FAFAFD;">
                            <div class="form-check d-flex align-items-start gap-2">
                                <input class="form-check-input mt-1 feature-checkbox" type="checkbox" name="features[]"
                                       value="{{ $key }}" id="feature_{{ $key }}" checked>
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
                    <i class="fa-solid fa-check me-2"></i> Register & Activate Customer
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
        none: []
    };

    function presetFeatures(type) {
        const selected = presets[type] || [];
        document.querySelectorAll('.feature-checkbox').forEach(cb => {
            cb.checked = selected.includes(cb.value);
        });
    }
</script>
@endpush
