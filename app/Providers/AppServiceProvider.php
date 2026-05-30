<?php

namespace App\Providers;

use App\Domains\Admin\Services\AdminService;
use App\Domains\Admin\Services\Contracts\AdminServiceInterface;
use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Domains\Jobs\Repositories\Eloquent\JobRepository;
use App\Domains\Jobs\Services\Contracts\JobServiceInterface;
use App\Domains\Jobs\Services\JobService;
use App\Domains\Notifications\Services\Contracts\NotificationServiceInterface;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Scrapers\Repositories\Contracts\ScrapingSourceRepositoryInterface;
use App\Domains\Scrapers\Repositories\Eloquent\ScrapingSourceRepository;
use App\Domains\Scrapers\Services\Contracts\ScrapingServiceInterface;
use App\Domains\Scrapers\Services\ScrapingService;
use App\Domains\Users\Services\AuthService;
use App\Domains\Users\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register all interface → implementation bindings.
     *
     * Following DIP (Dependency Inversion Principle):
     * controllers and services depend on abstractions, not concretions.
     */
    public function register(): void
    {
        // ─── Jobs Domain ──────────────────────────────────────────────────────
        $this->app->bind(JobRepositoryInterface::class, JobRepository::class);
        $this->app->bind(JobServiceInterface::class,    JobService::class);
        $this->app->bind(
            \App\Domains\Jobs\Services\Contracts\SearchServiceInterface::class,
            \App\Domains\Jobs\Services\SearchService::class
        );

        // ─── Scrapers Domain ──────────────────────────────────────────────────
        $this->app->bind(ScrapingSourceRepositoryInterface::class, ScrapingSourceRepository::class);
        $this->app->bind(ScrapingServiceInterface::class,          ScrapingService::class);

        // ─── Users Domain ─────────────────────────────────────────────────────
        $this->app->bind(AuthServiceInterface::class, AuthService::class);

        // ─── Notifications Domain ─────────────────────────────────────────────
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);

        // ─── Admin Domain ─────────────────────────────────────────────────────
        $this->app->bind(AdminServiceInterface::class, AdminService::class);
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        // Gate definition for admin authorization (used by EnsureAdmin middleware)
        Gate::define('admin-access', function (\App\Models\User $user) {
            return $user->getRawOriginal('role') === 'admin' || $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Reviewer', 'Moderator']);
        });

        // Implicitly grant "Super Admin" role and legacy "admin" users all permissions (for backward compatibility)
        Gate::before(function (\App\Models\User $user, string $ability) {
            if ($user->getRawOriginal('role') === 'admin' || $user->hasAnyRole(['Super Admin', 'Admin'])) {
                return true;
            }
        });

        // Prevent N+1 lazy loading issues in development and testing
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(
            !$this->app->isProduction()
        );
    }
}
