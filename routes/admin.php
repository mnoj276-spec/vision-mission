<?php

use App\Domains\Admin\Controllers\AdminDashboardController;
use App\Domains\Admin\Controllers\MasterDataController;
use App\Domains\Jobs\Controllers\AdminJobController;
use App\Domains\Users\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| All admin panel endpoints. Protected by auth + EnsureAdmin middleware.
| URL prefix: /api/admin  (kept identical to original to avoid JS changes)
*/

Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {

    // ─── Admin Dashboard & Analytics (Dashboard View) ────────────────────────
    Route::middleware('permission:view_dashboard')->group(function () {
        Route::get('/dashboard',       [AdminDashboardController::class, 'dashboardView'])->name('admin.dashboard');
        Route::get('/data',            [AdminDashboardController::class, 'getAdminData'])->name('admin.data');
    });

    // ─── Administrative Audit Logs ───────────────────────────────────────────
    Route::get('/activity-logs', [AdminDashboardController::class, 'getActivityLogs'])
        ->middleware('permission:view_audit_logs')
        ->name('admin.activity-logs');

    // ─── Global SEO Settings ─────────────────────────────────────────────────
    Route::post('/seo/update', [AdminDashboardController::class, 'updateSeoSettings'])
        ->middleware('permission:manage_seo')
        ->name('admin.seo.update');

    // ─── Queue & DLQ Management ──────────────────────────────────────────────
    Route::middleware('permission:manage_queues')->group(function () {
        Route::get('/queues/metrics',           [\App\Domains\Admin\Controllers\QueueManagementController::class, 'getMetrics'])->name('admin.queues.metrics');
        Route::get('/queues/failed',            [\App\Domains\Admin\Controllers\QueueManagementController::class, 'getFailedJobs'])->name('admin.queues.failed');
        Route::post('/queues/failed/retry-all', [\App\Domains\Admin\Controllers\QueueManagementController::class, 'retryAll'])->name('admin.queues.retry-all');
        Route::post('/queues/failed/flush',     [\App\Domains\Admin\Controllers\QueueManagementController::class, 'flushAll'])->name('admin.queues.flush');
        Route::post('/queues/failed/{uuid}/retry', [\App\Domains\Admin\Controllers\QueueManagementController::class, 'retryJob'])->name('admin.queues.retry');
        Route::delete('/queues/failed/{uuid}',  [\App\Domains\Admin\Controllers\QueueManagementController::class, 'deleteJob'])->name('admin.queues.delete');
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
