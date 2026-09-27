<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->is_super_admin || $user->id === 1) {
            return $next($request);
        }

        abort(403, 'Unauthorized access. Only CodiceSync Platform Super-Administrators can access this portal.');
    }
}
