<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHorizonDependencies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce check in local or testing environments to prevent production overhead
        if (app()->environment('local', 'testing')) {
            $queueDriver = config('queue.default');
            $hasRedisExtension = class_exists('Redis');
            
            $redisWorking = false;
            if ($hasRedisExtension) {
                try {
                    $redis = \Illuminate\Support\Facades\Redis::connection();
                    $redis->ping();
                    $redisWorking = true;
                } catch (\Throwable $e) {
                    $redisWorking = false;
                }
            }

            // Horizon strictly requires Redis queue connection and a running Redis server
            if ($queueDriver !== 'redis' || !$hasRedisExtension || !$redisWorking) {
                if ($request->is('horizon/api*')) {
                    return response()->json([
                        'status' => 'inactive',
                        'message' => 'Horizon is inactive in this environment. Queue driver is ' . $queueDriver . '.',
                        'error' => 'Redis is not available or not configured.'
                    ], 503);
                }

                return response()->view('errors.horizon-inactive', [
                    'queueDriver' => $queueDriver,
                    'hasRedisExtension' => $hasRedisExtension,
                    'redisWorking' => $redisWorking,
                ], 503);
            }
        }

        return $next($request);
    }
}
