<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\FeatureManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    /**
     * Super Admin Master Dashboard.
     */
    public function dashboard()
    {
        $totalTenants = Company::where('id', '>', 1)->count();
        $activeTenants = Company::where('id', '>', 1)->where('is_active', true)->where(function ($q) {
            $q->whereNull('subscription_expires_at')->orWhere('subscription_expires_at', '>=', now());
        })->count();
        $suspendedTenants = Company::where('id', '>', 1)->where('is_active', false)->count();
        $expiredTenants = Company::where('id', '>', 1)->where('is_active', true)->where('subscription_expires_at', '<', now())->count();
        $monthlyRevenue = Company::where('id', '>', 1)->where('is_active', true)->sum('monthly_price');

        $expiringSoon = Company::where('id', '>', 1)
            ->where('is_active', true)
            ->whereBetween('subscription_expires_at', [now(), now()->addDays(7)])
            ->with('user')
            ->get();

        $recentTenants = Company::where('id', '>', 1)
            ->with('user')
            ->latest()
            ->take(8)
            ->get();

        return view('superadmin.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'suspendedTenants',
            'expiredTenants',
            'monthlyRevenue',
            'expiringSoon',
            'recentTenants'
        ));
    }

    /**
     * Tenants Directory with search, status filters, and live toggle.
     */
    public function tenants(Request $request)
    {
        $query = Company::where('id', '>', 1)->with('user');

        // Search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        $filter = $request->query('status', 'all');
        if ($filter === 'active') {
            $query->where('is_active', true)->where(function ($q) {
                $q->whereNull('subscription_expires_at')->orWhere('subscription_expires_at', '>=', now());
            });
        } elseif ($filter === 'suspended') {
            $query->where('is_active', false);
        } elseif ($filter === 'expired') {
            $query->where('is_active', true)->where('subscription_expires_at', '<', now());
        }

        $tenants = $query->latest()->paginate(15)->withQueryString();
        $allFeatures = FeatureManager::getAllFeatures();

        return view('superadmin.tenants.index', compact('tenants', 'filter', 'allFeatures'));
    }

    /**
     * Show form to create a new paying tenant customer.
     */
    public function createTenant()
    {
        $allFeatures = FeatureManager::getAllFeatures();
        return view('superadmin.tenants.create', compact('allFeatures'));
    }

    /**
     * Store newly onboarded customer and initialize their workspace.
     */
    public function storeTenant(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:60'],
            'features' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($data) {
            // 1. Create Company
            $features = !empty($data['features']) ? array_values($data['features']) : ['*'];
            $startsAt = now();
            $expiresAt = now()->addMonths((int) $data['duration_months'])->endOfDay();

            $company = Company::create([
                'name' => $data['business_name'],
                'user_id' => null, // filled after user creation
                'status' => 'active',
                'is_active' => true,
                'subscription_plan' => 'custom',
                'monthly_price' => $data['monthly_price'],
                'subscription_starts_at' => $startsAt,
                'subscription_expires_at' => $expiresAt,
                'enabled_features' => $features,
                'max_users' => 5,
                'phone' => $data['phone'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            // 2. Create Primary User (Owner)
            $user = User::create([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'company_id' => $company->id,
                'phone' => $data['phone'] ?? null,
                'is_active' => true,
                'is_super_admin' => false,
            ]);

            // Link Company owner
            $company->user_id = $user->id;
            $company->save();

            // Assign Admin Role to user if exists
            $adminRole = Role::where('name', 'Admin')->orWhere('name', 'admin')->first();
            if ($adminRole) {
                $user->roles()->sync([$adminRole->id]);
            }
        });

        return redirect()->route('superadmin.tenants.index')->with('success', "Customer '{$data['business_name']}' created successfully with custom pricing!");
    }

    /**
     * Show form to edit tenant details & features.
     */
    public function editTenant(Company $tenant)
    {
        $allFeatures = FeatureManager::getAllFeatures();
        $tenant->load('user');
        return view('superadmin.tenants.edit', compact('tenant', 'allFeatures'));
    }

    /**
     * Update tenant details, custom price, and enabled features.
     */
    public function updateTenant(Request $request, Company $tenant)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'features' => ['nullable', 'array'],
            'new_password' => ['nullable', 'string', 'min:6'],
        ]);

        $features = !empty($data['features']) ? array_values($data['features']) : ['*'];

        $tenant->update([
            'name' => $data['business_name'],
            'monthly_price' => $data['monthly_price'],
            'enabled_features' => $features,
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        if ($tenant->user) {
            $tenant->user->name = $data['owner_name'];
            $tenant->user->phone = $data['phone'] ?? null;
            if (!empty($data['new_password'])) {
                $tenant->user->password = Hash::make($data['new_password']);
            }
            $tenant->user->save();
        }

        return redirect()->route('superadmin.tenants.index')->with('success', "Workspace '{$tenant->name}' updated successfully.");
    }

    /**
     * 1-Click Toggle Tenant Active / Suspended status.
     * Preserves 100% of data.
     */
    public function toggleStatus(Request $request, Company $tenant)
    {
        // Prevent disabling default HQ
        if ($tenant->id === 1) {
            return back()->with('error', 'The primary platform company cannot be suspended.');
        }

        $tenant->is_active = !$tenant->is_active;
        $tenant->status = $tenant->is_active ? 'active' : 'suspended';
        $tenant->save();

        $statusLabel = $tenant->is_active ? 'activated' : 'suspended';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $tenant->is_active,
                'status' => $tenant->status,
                'message' => "Business '{$tenant->name}' has been {$statusLabel}.",
            ]);
        }

        return back()->with('success', "Business '{$tenant->name}' has been {$statusLabel}. All data is preserved.");
    }

    /**
     * Extend or renew tenant subscription.
     */
    public function extendSubscription(Request $request, Company $tenant)
    {
        $request->validate([
            'months' => ['nullable', 'integer', 'min:1'],
            'custom_date' => ['nullable', 'date'],
        ]);

        $baseDate = ($tenant->subscription_expires_at && $tenant->subscription_expires_at->isFuture())
            ? $tenant->subscription_expires_at
            : now();

        if ($request->filled('custom_date')) {
            $newExpiry = Carbon::parse($request->input('custom_date'))->endOfDay();
        } else {
            $months = (int) $request->input('months', 1);
            $newExpiry = $baseDate->copy()->addMonths($months)->endOfDay();
        }

        $tenant->subscription_expires_at = $newExpiry;
        $tenant->is_active = true;
        $tenant->status = 'active';
        $tenant->save();

        return back()->with('success', "Subscription for '{$tenant->name}' extended until {$newExpiry->format('d M Y')}!");
    }

    /**
     * 1-Click Impersonate / Login as Customer to troubleshoot for them.
     */
    public function impersonate(Company $tenant)
    {
        session([
            'current_company_id' => $tenant->id,
            'current_company_name' => $tenant->name,
            'is_impersonating' => true,
        ]);

        return redirect()->route('dashboard')->with('info', "You are now viewing '{$tenant->name}' workspace.");
    }

    /**
     * Exit Impersonation and return to Super Admin.
     */
    public function stopImpersonate()
    {
        session()->forget(['current_company_id', 'current_company_name', 'is_impersonating']);
        return redirect()->route('superadmin.dashboard')->with('success', 'Exited tenant workspace. Back to Super Admin Portal.');
    }
}
