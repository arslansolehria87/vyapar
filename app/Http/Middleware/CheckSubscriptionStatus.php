<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 1. Super Admin always has unrestricted access
        if ($user->is_super_admin || $user->id === 1) {
            return $next($request);
        }

        // 2. Check if individual user account is active
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => 'Your user account has been disabled. Please contact your company administrator or CodiceSync support.',
            ]);
        }

        // 3. Resolve active company
        $company = $user->currentCompany();

        if ($company) {
            // Check if tenant is suspended or inactive
            if (!$company->is_active || $company->status === 'suspended') {
                return redirect()->route('subscription.expired')->with([
                    'reason' => 'suspended',
                    'company_name' => $company->name,
                ]);
            }

            // Check if subscription has expired
            if ($company->subscription_expires_at && $company->subscription_expires_at->isPast()) {
                return redirect()->route('subscription.expired')->with([
                    'reason' => 'expired',
                    'company_name' => $company->name,
                    'expired_at' => $company->subscription_expires_at->format('d M Y'),
                ]);
            }
        }

        return $next($request);
    }
}
