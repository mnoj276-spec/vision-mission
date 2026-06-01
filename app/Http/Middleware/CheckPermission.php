<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
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

        // Enforce active user validation: Boot inactive users instantly
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

        // Validate user permission using Laravel's native can() gate
        if (!$user->can($permission)) {
            try {
                \App\Models\AuditLog::create([
                    'user_id'    => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent() ?? 'N/A',
                    'action'     => 'permission_denied',
                    'details'    => "Denied action requiring '{$permission}' on URI: " . $request->getRequestUri(),
                ]);
            } catch (\Exception $e) {}

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access Denied: You do not have the required clearance for this action.'
                ], 403);
            }
            return redirect('/')->with('error', 'Access Denied: Unauthorized action.');
        }

        return $next($request);
    }
}
