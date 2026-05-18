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
        if (!Auth::check() || Gate::denies('admin-access')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Access Denied: Only authenticated administrators can access this panel.',
                ], 403);
            }

            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
