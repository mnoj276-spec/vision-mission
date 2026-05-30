<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/scraper.php'));
        },
    )
    ->withCommands([
        \App\Domains\Scrapers\Commands\RunScraperCommand::class,
        \App\Console\Commands\WarmInternalLinksCache::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Register security middleware aliases
        $middleware->alias([
            'admin'              => \App\Http\Middleware\EnsureAdmin::class,
            'role'               => \App\Http\Middleware\RoleMiddleware::class,
            'active'             => \App\Http\Middleware\EnsureActiveUser::class,
            'permission'         => \App\Http\Middleware\CheckPermission::class,
            'spatie_role'        => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'spatie_permission'  => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'internal_linking'   => \App\Http\Middleware\InternalLinkingHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Laravel Validation Exception (422)
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed.',
                        'data'    => null,
                        'errors'  => $e->errors(),
                        'meta'    => [
                            'timestamp'   => time(),
                            'api_version' => 'v1',
                        ]
                    ], 422);
                }

                // Model Not Found / Not Found Http (404)
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource not found.',
                        'data'    => null,
                        'errors'  => null,
                        'meta'    => [
                            'timestamp'   => time(),
                            'api_version' => 'v1',
                        ]
                    ], 404);
                }

                // Authentication Exception (401)
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized or invalid token.',
                        'data'    => null,
                        'errors'  => null,
                        'meta'    => [
                            'timestamp'   => time(),
                            'api_version' => 'v1',
                        ]
                    ], 401);
                }

                // Rate Limit / Throttle (429)
                if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please slow down.',
                        'data'    => null,
                        'errors'  => null,
                        'meta'    => [
                            'timestamp'   => time(),
                            'api_version' => 'v1',
                        ]
                    ], 429);
                }

                // Standard Server Exception (500)
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = ($statusCode === 500 && !config('app.debug'))
                    ? 'Internal Server Error.' 
                    : $e->getMessage();

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data'    => null,
                    'errors'  => config('app.debug') ? [
                        'exception' => get_class($e),
                        'file'      => $e->getFile(),
                        'line'      => $e->getLine(),
                        'trace'     => explode("\n", $e->getTraceAsString()),
                    ] : null,
                    'meta'    => [
                        'timestamp'   => time(),
                        'api_version' => 'v1',
                    ]
                ], $statusCode);
            }
        });
    })->create();
