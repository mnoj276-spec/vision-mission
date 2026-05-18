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
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Register security middleware aliases
        $middleware->alias([
            'admin'  => \App\Http\Middleware\EnsureAdmin::class,
            'role'   => \App\Http\Middleware\RoleMiddleware::class,
            'active' => \App\Http\Middleware\EnsureActiveUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
