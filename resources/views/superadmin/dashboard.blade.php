@extends('superadmin.layout')

@section('title', 'SaaS Dashboard')

@section('content')

<!-- Header Title -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Platform Overview</h1>
        <p class="text-muted small mb-0">Monitor your active customer subscriptions, monthly revenue, and client renewals.</p>
    </div>
    <div>
        <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-cs">
            <i class="fa-solid fa-user-plus me-1.5"></i> Onboard New Customer
        </a>
    </div>
</div>

<!-- Metrics Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Clients -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Total Businesses</span>
                <div class="rounded-3 p-2" style="background: rgba(88, 19, 188, 0.1); color: var(--cs-purple);">
                    <i class="fa-solid fa-store fs-5"></i>
                </div>
            </div>
            <div class="h2 fw-bold text-dark mb-1">{{ number_format($totalTenants) }}</div>
            <span class="small text-muted">Registered tenant workspaces</span>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Active Subscriptions</span>
                <div class="rounded-3 p-2" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                </div>
            </div>
            <div class="h2 fw-bold text-dark mb-1">{{ number_format($activeTenants) }}</div>
            <span class="small text-success fw-medium">Active paying customers</span>
        </div>
    </div>

    <!-- Suspended / Paused -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Suspended (Access Off)</span>
                <div class="rounded-3 p-2" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fa-solid fa-circle-pause fs-5"></i>
                </div>
            </div>
            <div class="h2 fw-bold text-dark mb-1">{{ number_format($suspendedTenants) }}</div>
            <span class="small text-danger fw-medium">Unpaid / paused clients (data safe)</span>
        </div>
    </div>

    <!-- Monthly Recurring Revenue (MRR) -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Est. Monthly Revenue</span>
                <div class="rounded-3 p-2" style="background: rgba(172, 34, 203, 0.1); color: var(--cs-magenta);">
                    <i class="fa-solid fa-money-bill-trend-up fs-5"></i>
                </div>
            </div>
            <div class="h2 fw-bold text-dark mb-1">PKR {{ number_format($monthlyRevenue, 0) }}</div>
            <span class="small text-muted">From active subscriptions</span>
        </div>
    </div>
</div>

<!-- Expiring Soon Notice Section (Next 7 Days) -->
@if($expiringSoon->count() > 0)
<div class="card-box mb-4 p-4 border-warning" style="background: #FFFDF5; border-left: 4px solid #F59E0B !important;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-clock text-warning fs-5"></i>
            <h5 class="fw-bold mb-0 text-dark">Subscriptions Expiring in Next 7 Days ({{ $expiringSoon->count() }})</h5>
        </div>
        <span class="small text-muted">Contact these clients for monthly renewal fee</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead>
                <tr class="text-muted">
                    <th>Business Name</th>
                    <th>Owner / Contact</th>
                    <th>Expires On</th>
                    <th>Fee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expiringSoon as $tenant)
                <tr>
                    <td class="fw-bold text-dark">{{ $tenant->name }}</td>
                    <td>
                        <div>{{ $tenant->user?->name ?? 'N/A' }}</div>
                        <small class="text-muted">{{ $tenant->phone ?? $tenant->user?->phone ?? $tenant->user?->email }}</small>
                    </td>
                    <td>
                        <span class="badge bg-warning text-dark px-2.5 py-1">
                            {{ $tenant->subscription_expires_at?->format('d M Y') }} ({{ $tenant->daysUntilExpiry() }} days left)
                        </span>
                    </td>
                    <td class="fw-semibold">PKR {{ number_format($tenant->monthly_price, 0) }}</td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.tenants.extend', $tenant) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="months" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle-plus me-1"></i> Renew +1 Month
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Recent Clients Directory -->
<div class="card-box p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-dark">Recently Added Businesses</h5>
        <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            View All Businesses <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th>Business</th>
                    <th>Owner</th>
                    <th>Monthly Fee</th>
                    <th>Expires At</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $tenant->name }}</div>
                        <small class="text-muted">{{ $tenant->city ?: 'Pakistan' }}</small>
                    </td>
                    <td>
                        <div>{{ $tenant->user?->name ?? 'No Owner' }}</div>
                        <small class="text-muted">{{ $tenant->user?->email }}</small>
                    </td>
                    <td class="fw-bold text-dark">
                        PKR {{ number_format($tenant->monthly_price, 0) }}
                    </td>
                    <td>
                        @if($tenant->subscription_expires_at)
                            <span class="small {{ $tenant->subscription_expires_at->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $tenant->subscription_expires_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Unlimited</span>
                        @endif
                    </td>
                    <td>
                        @if(!$tenant->is_active)
                            <span class="badge bg-danger">Suspended</span>
                        @elseif($tenant->subscription_expires_at && $tenant->subscription_expires_at->isPast())
                            <span class="badge bg-warning text-dark">Expired</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Manage
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        No clients onboarded yet. Click "Onboard New Customer" above to register your first business client!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
