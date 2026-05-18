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

    // ─── Admin Dashboard & Analytics ─────────────────────────────────────────
    Route::get('/dashboard',       [AdminDashboardController::class, 'dashboardView'])->name('admin.dashboard');
    Route::get('/data',            [AdminDashboardController::class, 'getAdminData'])->name('admin.data');
    Route::get('/activity-logs',   [AdminDashboardController::class, 'getActivityLogs'])->name('admin.activity-logs');
    Route::post('/seo/update',     [AdminDashboardController::class, 'updateSeoSettings'])->name('admin.seo.update');

    // ─── User Management ─────────────────────────────────────────────────────
    Route::get('/users',           [AdminUserController::class, 'getUsersList'])->name('admin.users.list');
    Route::post('/users/{id}/update', [AdminUserController::class, 'updateUser'])->name('admin.users.update');

    // ─── Job Management ───────────────────────────────────────────────────────
    Route::get('/jobs',            [AdminJobController::class, 'index'])->name('admin.jobs.index');
    Route::post('/jobs/store',     [AdminJobController::class, 'store'])->name('admin.jobs.store');
    Route::post('/jobs',           [AdminJobController::class, 'store'])->name('admin.jobs.store_new');
    Route::post('/jobs/{id}',      [AdminJobController::class, 'update'])->name('admin.jobs.update');
    Route::delete('/jobs/{id}',    [AdminJobController::class, 'destroy'])->name('admin.jobs.destroy');

    // ─── Master Data ──────────────────────────────────────────────────────────
    Route::get('/categories',           [MasterDataController::class, 'getCategories']);
    Route::post('/categories',          [MasterDataController::class, 'storeCategory']);
    Route::post('/categories/{id}',     [MasterDataController::class, 'updateCategory']);
    Route::delete('/categories/{id}',   [MasterDataController::class, 'deleteCategory']);

    Route::get('/departments',          [MasterDataController::class, 'getDepartments']);
    Route::post('/departments',         [MasterDataController::class, 'storeDepartment']);
    Route::post('/departments/{id}',    [MasterDataController::class, 'updateDepartment']);
    Route::delete('/departments/{id}',  [MasterDataController::class, 'deleteDepartment']);

    Route::get('/qualifications',       [MasterDataController::class, 'getQualifications']);
    Route::post('/qualifications',      [MasterDataController::class, 'storeQualification']);
    Route::post('/qualifications/{id}', [MasterDataController::class, 'updateQualification']);
    Route::delete('/qualifications/{id}',[MasterDataController::class,'deleteQualification']);

    Route::get('/states',               [MasterDataController::class, 'getStates']);
    Route::post('/states',              [MasterDataController::class, 'storeState']);
    Route::post('/states/{id}',         [MasterDataController::class, 'updateState']);
    Route::delete('/states/{id}',       [MasterDataController::class, 'deleteState']);
});

// Admin dashboard view (web, not API — no /api prefix)
Route::middleware(['auth', 'admin'])->get('/admin/dashboard', [AdminDashboardController::class, 'dashboardView'])->name('admin.dashboard');
