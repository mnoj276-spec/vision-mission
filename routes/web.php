<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\AdminController;

// Public Homepage & Dynamic Asynchronous Search Details
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/api/jobs/{slug}', [HomeController::class, 'show'])->name('jobs.show');

// Asynchronous Authentication Endpoints
Route::post('/api/register', [AuthController::class, 'register'])->name('register');
Route::post('/api/login', [AuthController::class, 'login'])->name('login');
Route::post('/api/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/api/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
Route::post('/api/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Candidate Actions & Interactions (Enforce Auth Middleware)
Route::middleware('auth')->group(function() {
    // Dynamic Dashboard Stats and Tables
    Route::get('/api/dashboard', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    
    // Interactions: Save Bookmarks and Upload Resume Applications
    Route::post('/api/jobs/{id}/bookmark', [DashboardController::class, 'toggleBookmark'])->name('jobs.bookmark');
    Route::post('/api/jobs/{id}/apply', [DashboardController::class, 'applyJob'])->name('jobs.apply');
    
    // Candidate Profile & Preference management
    Route::post('/api/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/api/profile/preferences', [DashboardController::class, 'updatePreferences'])->name('profile.preferences');

    // Standalone Admin Dashboard View
    Route::get('/admin/dashboard', [AdminController::class, 'dashboardView'])->name('admin.dashboard');

    // Enterprise Administration Controls
    Route::get('/api/admin/data', [AdminController::class, 'getAdminData'])->name('admin.data');
    Route::post('/api/admin/scraper/{id}/toggle', [AdminController::class, 'toggleScraper'])->name('admin.scraper.toggle');
    Route::post('/api/admin/scraper/{id}/run', [AdminController::class, 'runScraper'])->name('admin.scraper.run');
    Route::post('/api/admin/quarantine/{id}/rescue', [AdminController::class, 'rescueQuarantine'])->name('admin.quarantine.rescue');
    
    // Admin Management Board Extras
    Route::get('/api/admin/users', [AdminController::class, 'getUsersList'])->name('admin.users.list');
    Route::post('/api/admin/users/{id}/update', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/api/admin/jobs/store', [AdminController::class, 'storeJob'])->name('admin.jobs.store');
    Route::post('/api/admin/seo/update', [AdminController::class, 'updateSeoSettings'])->name('admin.seo.update');
    Route::get('/api/admin/activity-logs', [AdminController::class, 'getActivityLogs'])->name('admin.activity-logs');

    // Job Posting Operations (CRUD)
    Route::get('/api/admin/jobs', [\App\Http\Controllers\Frontend\JobManagementController::class, 'index'])->name('admin.jobs.index');
    Route::post('/api/admin/jobs', [\App\Http\Controllers\Frontend\JobManagementController::class, 'store'])->name('admin.jobs.store_new');
    Route::post('/api/admin/jobs/{id}', [\App\Http\Controllers\Frontend\JobManagementController::class, 'update'])->name('admin.jobs.update');
    Route::delete('/api/admin/jobs/{id}', [\App\Http\Controllers\Frontend\JobManagementController::class, 'destroy'])->name('admin.jobs.destroy');

    // Master Data Operations (Categories, Departments, Qualifications, States) (CRUD)
    Route::get('/api/admin/categories', [\App\Http\Controllers\Frontend\MasterDataController::class, 'getCategories']);
    Route::post('/api/admin/categories', [\App\Http\Controllers\Frontend\MasterDataController::class, 'storeCategory']);
    Route::post('/api/admin/categories/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'updateCategory']);
    Route::delete('/api/admin/categories/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'deleteCategory']);

    Route::get('/api/admin/departments', [\App\Http\Controllers\Frontend\MasterDataController::class, 'getDepartments']);
    Route::post('/api/admin/departments', [\App\Http\Controllers\Frontend\MasterDataController::class, 'storeDepartment']);
    Route::post('/api/admin/departments/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'updateDepartment']);
    Route::delete('/api/admin/departments/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'deleteDepartment']);

    Route::get('/api/admin/qualifications', [\App\Http\Controllers\Frontend\MasterDataController::class, 'getQualifications']);
    Route::post('/api/admin/qualifications', [\App\Http\Controllers\Frontend\MasterDataController::class, 'storeQualification']);
    Route::post('/api/admin/qualifications/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'updateQualification']);
    Route::delete('/api/admin/qualifications/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'deleteQualification']);

    Route::get('/api/admin/states', [\App\Http\Controllers\Frontend\MasterDataController::class, 'getStates']);
    Route::post('/api/admin/states', [\App\Http\Controllers\Frontend\MasterDataController::class, 'storeState']);
    Route::post('/api/admin/states/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'updateState']);
    Route::delete('/api/admin/states/{id}', [\App\Http\Controllers\Frontend\MasterDataController::class, 'deleteState']);

    // Dynamic Crawler Source Management (CRUD)
    Route::get('/api/admin/scrapers', [AdminController::class, 'getScrapersList'])->name('admin.scrapers.list');
    Route::post('/api/admin/scrapers', [AdminController::class, 'storeScraper'])->name('admin.scrapers.store');
    Route::post('/api/admin/scrapers/{id}', [AdminController::class, 'updateScraperSource'])->name('admin.scrapers.update');
    Route::delete('/api/admin/scrapers/{id}', [AdminController::class, 'deleteScraper'])->name('admin.scrapers.delete');
});

