<?php

use App\Domains\Admin\Controllers\AdminDashboardController;
use App\Domains\Admin\Controllers\MasterDataController;
use App\Domains\Admin\Controllers\AdManagementController;
use App\Domains\Admin\Controllers\SettingsManagementController;
use App\Domains\Jobs\Controllers\AdminJobController;
use App\Domains\Users\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| All admin panel endpoints. Protected by auth + EnsureAdmin middleware.
| URL prefix: /api/admin  (kept identical to original to avoid JS changes)
| */

Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {

    // ─── Admin Dashboard & Analytics (Dashboard View) ────────────────────────
    Route::middleware('permission:view_dashboard')->group(function () {
        Route::get('/dashboard',       [AdminDashboardController::class, 'dashboardView'])->name('admin.dashboard.api');
        Route::get('/data',            [AdminDashboardController::class, 'getAdminData'])->name('admin.data');
        Route::get('/analytics/metrics', [AdminDashboardController::class, 'getAnalyticsData'])->name('admin.analytics.metrics');
    });

    // ─── Administrative Audit Logs ───────────────────────────────────────────
    Route::get('/activity-logs', [AdminDashboardController::class, 'getActivityLogs'])
        ->middleware('permission:view_audit_logs')
        ->name('admin.activity-logs');

    // ─── Global SEO & Ad Settings & Unified Dynamic Settings Module ──────────
    Route::middleware('permission:manage_seo')->group(function () {
        Route::post('/seo/update', [AdminDashboardController::class, 'updateSeoSettings'])->name('admin.seo.update');
        Route::get('/advertisements', [AdManagementController::class, 'index'])->name('admin.ads.index');
        Route::post('/advertisements', [AdManagementController::class, 'storeOrUpdate'])->name('admin.ads.store_update');
        Route::post('/advertisements/{id}/toggle', [AdManagementController::class, 'toggleActive'])->name('admin.ads.toggle');

        // Dynamic Settings Module APIs
        Route::get('/settings', [SettingsManagementController::class, 'getSettings'])->name('admin.settings.index');
        Route::post('/settings/general', [SettingsManagementController::class, 'updateGeneralSettings'])->name('admin.settings.general');
        Route::post('/settings/logo', [SettingsManagementController::class, 'uploadLogo'])->name('admin.settings.logo');
        Route::post('/settings/theme', [SettingsManagementController::class, 'updateThemeSettings'])->name('admin.settings.theme');
        Route::post('/settings/seo', [SettingsManagementController::class, 'updateSeoSettings'])->name('admin.settings.seo');
        Route::post('/settings/email', [SettingsManagementController::class, 'updateEmailSettings'])->name('admin.settings.email');
        Route::post('/settings/email/test', [SettingsManagementController::class, 'testSmtpConnection'])->name('admin.settings.email.test');
        Route::post('/settings/api', [SettingsManagementController::class, 'updateApiSettings'])->name('admin.settings.api');
        Route::post('/settings/social', [SettingsManagementController::class, 'updateSocialLinks'])->name('admin.settings.social');
        
        Route::get('/settings/menus', [SettingsManagementController::class, 'getMenus'])->name('admin.settings.menus');
        Route::post('/settings/menus', [SettingsManagementController::class, 'saveMenuItem'])->name('admin.settings.menus.save');
        Route::post('/settings/menus/reorder', [SettingsManagementController::class, 'reorderMenuItems'])->name('admin.settings.menus.reorder');
        Route::delete('/settings/menus/{id}', [SettingsManagementController::class, 'deleteMenuItem'])->name('admin.settings.menus.delete');
        
        Route::get('/settings/cms-pages', [SettingsManagementController::class, 'getCmsPages'])->name('admin.settings.cms.index');
        Route::get('/settings/cms-pages/{id}', [SettingsManagementController::class, 'getCmsPageDetail'])->name('admin.settings.cms.detail');
        Route::post('/settings/cms-pages', [SettingsManagementController::class, 'saveCmsPage'])->name('admin.settings.cms.save');
        Route::delete('/settings/cms-pages/{id}', [SettingsManagementController::class, 'deleteCmsPage'])->name('admin.settings.cms.delete');
        
        Route::get('/settings/media', [SettingsManagementController::class, 'getMedia'])->name('admin.settings.media.index');
        Route::post('/settings/media/upload', [SettingsManagementController::class, 'uploadMedia'])->name('admin.settings.media.upload');
        Route::post('/settings/media/folder', [SettingsManagementController::class, 'createFolder'])->name('admin.settings.media.folder');
        Route::delete('/settings/media', [SettingsManagementController::class, 'deleteMedia'])->name('admin.settings.media.delete');
        
        Route::get('/settings/backups', [SettingsManagementController::class, 'getBackups'])->name('admin.settings.backups.index');
        Route::post('/settings/backups/generate', [SettingsManagementController::class, 'generateBackup'])->name('admin.settings.backups.generate');
        Route::post('/settings/backups/restore', [SettingsManagementController::class, 'restoreBackup'])->name('admin.settings.backups.restore');
        Route::delete('/settings/backups/{filename}', [SettingsManagementController::class, 'deleteBackup'])->name('admin.settings.backups.delete');
        Route::get('/settings/backups/download/{filename}', [SettingsManagementController::class, 'downloadBackup'])->name('admin.settings.backups.download');
    });

    // ─── Queue & DLQ Management ──────────────────────────────────────────────
    Route::middleware('permission:manage_queues')->group(function () {
        Route::get('/queues/metrics',           [\App\Domains\Admin\Controllers\QueueManagementController::class, 'getMetrics'])->name('admin.queues.metrics');
        Route::get('/queues/failed',            [\App\Domains\Admin\Controllers\QueueManagementController::class, 'getFailedJobs'])->name('admin.queues.failed');
        Route::post('/queues/failed/retry-all', [\App\Domains\Admin\Controllers\QueueManagementController::class, 'retryAll'])->name('admin.queues.retry-all');
        Route::post('/queues/failed/flush',     [\App\Domains\Admin\Controllers\QueueManagementController::class, 'flushAll'])->name('admin.queues.flush');
        Route::post('/queues/failed/{uuid}/retry', [\App\Domains\Admin\Controllers\QueueManagementController::class, 'retryJob'])->name('admin.queues.retry');
        Route::delete('/queues/failed/{uuid}',  [\App\Domains\Admin\Controllers\QueueManagementController::class, 'deleteJob'])->name('admin.queues.delete');

        // ─── Marketing Automation & Email Tracking ───
        Route::get('/marketing/stats',          [\App\Domains\Admin\Controllers\MarketingController::class, 'getStats'])->name('admin.marketing.stats');
        Route::get('/marketing/logs',           [\App\Domains\Admin\Controllers\MarketingController::class, 'getLogs'])->name('admin.marketing.logs');
        Route::post('/marketing/trigger-test',  [\App\Domains\Admin\Controllers\MarketingController::class, 'triggerTest'])->name('admin.marketing.trigger-test');
    });

    // ─── User Management ─────────────────────────────────────────────────────
    Route::middleware('permission:manage_users')->group(function () {
        Route::get('/users',           [AdminUserController::class, 'getUsersList'])->name('admin.users.list');
        Route::post('/users/{id}/update', [AdminUserController::class, 'updateUser'])->name('admin.users.update');
    });

    // ─── Job Management ───────────────────────────────────────────────────────
    Route::get('/jobs', [AdminJobController::class, 'index'])
        ->middleware('permission:view_jobs')
        ->name('admin.jobs.index');

    Route::get('/applications/{id}/resume', [\App\Domains\Admin\Controllers\ResumeDownloadController::class, 'download'])
        ->middleware('permission:view_jobs')
        ->name('admin.applications.resume.download');

    Route::middleware('permission:create_jobs')->group(function () {
        Route::post('/jobs/store', [AdminJobController::class, 'store'])->name('admin.jobs.store');
        Route::post('/jobs',       [AdminJobController::class, 'store'])->name('admin.jobs.store_new');
    });

    Route::post('/jobs/{id}', [AdminJobController::class, 'update'])
        ->middleware('permission:edit_jobs')
        ->name('admin.jobs.update');

    Route::delete('/jobs/{id}', [AdminJobController::class, 'destroy'])
        ->middleware('permission:delete_jobs')
        ->name('admin.jobs.destroy');

    Route::post('/jobs/{id}/toggle-featured', [AdminJobController::class, 'toggleFeatured'])
        ->middleware('permission:edit_jobs')
        ->name('admin.jobs.toggle-featured');

    Route::post('/jobs/{id}/toggle-sponsored', [AdminJobController::class, 'toggleSponsored'])
        ->middleware('permission:edit_jobs')
        ->name('admin.jobs.toggle-sponsored');

    // ─── AI Content Management ────────────────────────────────────────────────
    Route::get('/ai-contents', [\App\Domains\Admin\Controllers\AiContentManagementController::class, 'index'])
        ->middleware('permission:view_ai_content')
        ->name('admin.ai-contents.index');

    Route::middleware('permission:approve_ai_content')->group(function () {
        Route::post('/ai-contents/{id}/approve', [\App\Domains\Admin\Controllers\AiContentManagementController::class, 'approve'])->name('admin.ai-contents.approve');
    });

    Route::middleware('permission:reject_ai_content')->group(function () {
        Route::post('/ai-contents/{id}/reject', [\App\Domains\Admin\Controllers\AiContentManagementController::class, 'reject'])->name('admin.ai-contents.reject');
    });

    Route::post('/ai-contents/{id}/update', [\App\Domains\Admin\Controllers\AiContentManagementController::class, 'update'])
        ->middleware('permission:edit_ai_content')
        ->name('admin.ai-contents.update');

    Route::post('/ai-contents/generate/{job_post_id}', [\App\Domains\Admin\Controllers\AiContentManagementController::class, 'generate'])
        ->middleware('permission:generate_ai_content')
        ->name('admin.ai-contents.generate');

    // ─── Master Data ──────────────────────────────────────────────────────────
    // Get master data is viewable by all administrative roles
    Route::middleware('permission:view_master_data')->group(function () {
        Route::get('/categories',     [MasterDataController::class, 'getCategories']);
        Route::get('/departments',    [MasterDataController::class, 'getDepartments']);
        Route::get('/qualifications', [MasterDataController::class, 'getQualifications']);
        Route::get('/states',         [MasterDataController::class, 'getStates']);
    });

    // Write master data is restricted to manage_master_data permission
    Route::middleware('permission:manage_master_data')->group(function () {
        Route::post('/categories',          [MasterDataController::class, 'storeCategory']);
        Route::post('/categories/{id}',     [MasterDataController::class, 'updateCategory']);
        Route::delete('/categories/{id}',   [MasterDataController::class, 'deleteCategory']);

        Route::post('/departments',         [MasterDataController::class, 'storeDepartment']);
        Route::post('/departments/{id}',    [MasterDataController::class, 'updateDepartment']);
        Route::delete('/departments/{id}',  [MasterDataController::class, 'deleteDepartment']);

        Route::post('/qualifications',      [MasterDataController::class, 'storeQualification']);
        Route::post('/qualifications/{id}', [MasterDataController::class, 'updateQualification']);
        Route::delete('/qualifications/{id}',[MasterDataController::class, 'deleteQualification']);

        Route::post('/states',              [MasterDataController::class, 'storeState']);
        Route::post('/states/{id}',         [MasterDataController::class, 'updateState']);
        Route::delete('/states/{id}',       [MasterDataController::class, 'deleteState']);
    });
});

// Admin dashboard view (web, not API — no /api prefix)
Route::middleware(['auth', 'admin'])->get('/admin/dashboard', [AdminDashboardController::class, 'dashboardView'])->name('admin.dashboard');
