<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureAdmin Middleware
 *
 * Replaces the duplicated checkAdmin() / checkAdminAuthorization() methods
 * that were copy-pasted across AdminController, JobManagementController,
 * and MasterDataController. A single, testable, reusable guard.
 *
 * Returns JSON 403 for AJAX requests; redirects for standard web requests.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Gate::denies('admin-access') || !Auth::user()->is_active) {
            // Immediate session termination if admin user is suspended
            if (Auth::check() && !Auth::user()->is_active) {
                Auth::logout();
                
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Access Denied: Only authenticated active administrators can access this panel.',
                ], 403);
            }

            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
