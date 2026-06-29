<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!feature_enabled($feature)) {
            try {
                \App\Models\AuditLog::create([
                    'user_id'    => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent() ?? 'N/A',
                    'action'     => 'feature_disabled_access',
                    'details'    => "Denied access to disabled feature '{$feature}' on URI: " . $request->getRequestUri(),
                ]);
            } catch (\Exception $e) {}

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This feature belongs to a future release version and is currently disabled.',
                ], 403);
            }

            return redirect('/admin/dashboard')->with('error', 'This feature belongs to a future release version and is currently disabled.');
        }

        return $next($request);
    }
}
