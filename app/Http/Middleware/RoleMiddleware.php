<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string  $role
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            return redirect('/')->with('error', 'Please login first.');
        }

        $user = Auth::user();

        // 1. Enforce active user validation: Boot inactive users instantly
        if (!$user->is_active) {
            Auth::logout();
            
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access Denied: Your account has been suspended.'
                ], 403);
            }
            return redirect('/')->with('error', 'Your account has been suspended.');
        }

        // 2. Validate user role (checks Spatie roles & raw role column for full backward compatibility)
        $roleNameNormalized = ucwords(str_replace('_', ' ', $role));
        if ($user->role !== $role && !$user->hasRole($role) && !$user->hasRole($roleNameNormalized)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access Denied: Unauthorized role.'
                ], 403);
            }
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
