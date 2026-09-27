<?php

namespace App\Http\Middleware;

use App\Services\FeatureManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckTenantFeature
{
    /**
     * Handle an incoming request and check if the tenant plan includes this feature.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super Admin bypasses all feature locks
        if ($user->is_super_admin || $user->id === 1) {
            return $next($request);
        }

        $company = $user->currentCompany();

        if ($company && !FeatureManager::isEnabled($company, $feature)) {
            $allFeatures = FeatureManager::getAllFeatures();
            $featureInfo = $allFeatures[$feature] ?? ['name' => ucfirst($feature), 'description' => 'This feature requires an upgraded plan.'];

            return response()->view('dashboard.feature-locked', [
                'featureKey' => $feature,
                'featureInfo' => $featureInfo,
                'company' => $company,
            ], 403);
        }

        return $next($request);
    }
}
