<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class DynamicMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass maintenance check for admin login, admin dashboard, logout, and static assets
        if ($request->is('api/admin*') || 
            $request->is('admin*') || 
            $request->is('api/login') || 
            $request->is('api/logout') || 
            $request->is('assets/*') ||
            $request->is('favicon.ico')
        ) {
            return $next($request);
        }

        // Bypass for logged in administrators
        if (auth()->check() && (
            auth()->user()->getRawOriginal('role') === 'admin' || 
            auth()->user()->hasAnyRole(['Super Admin', 'Admin'])
        )) {
            return $next($request);
        }

        $hasSettingsTable = \Illuminate\Support\Facades\Cache::rememberForever('has_settings_table', function () {
            return Schema::hasTable('settings');
        });

        if ($hasSettingsTable) {
            try {
                if (setting('maintenance_mode') == '1') {
                    $message = setting('maintenance_message', 'Website is undergoing scheduled updates. Please check back shortly.');
                    
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => 'maintenance',
                            'message' => $message,
                        ], 503);
                    }

                    // Render inline modern design if errors.503 view doesn't render
                    return response()->view('errors.503', ['message' => $message], 503);
                }
            } catch (\Exception $e) {
                // Failsafe
            }
        }

        return $next($request);
    }
}
