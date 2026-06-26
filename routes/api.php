<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Public + Candidate
|--------------------------------------------------------------------------
| Prefix: /api  (applied automatically by bootstrap/app.php)
|
| Public: job detail, auth endpoints
| Protected (auth): dashboard, bookmarks, applications, profile
|*/

// ─── REST API Version 1.0 (Mobile Ready, JWT Guard, Versioned, Throttled) ────
Route::prefix('v1')->group(function () {
    
    // Auth & Password Recovery (Throttled strictly to 5 requests per minute per IP)
    Route::middleware(['throttle:api.auth'])->group(function () {
        Route::post('/register',        [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'register'])->name('api.v1.register');
        Route::post('/login',           [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'login'])->name('api.v1.login');
        Route::post('/refresh',         [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'refresh'])->name('api.v1.refresh');
        Route::post('/logout',          [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout'])->name('api.v1.logout');
        Route::post('/forgot-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'forgotPassword'])->name('api.v1.password.forgot');
        Route::post('/reset-password',  [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'resetPassword'])->name('api.v1.password.reset');
    });

    // Public Core Search & Autocomplete Endpoints (Throttled to 60 requests per minute)
    Route::middleware(['throttle:api'])->group(function () {
        Route::get('/jobs',                [\App\Http\Controllers\Api\V1\Jobs\JobController::class, 'index'])->name('api.v1.jobs.index');
        Route::get('/jobs/{slug}',         [\App\Http\Controllers\Api\V1\Jobs\JobController::class, 'show'])->name('api.v1.jobs.show');
        Route::get('/jobs/{id}/timeline',  [\App\Http\Controllers\Api\V1\Jobs\JobController::class, 'timeline'])->name('api.v1.jobs.timeline');
        Route::get('/search/autocomplete', [\App\Http\Controllers\Api\V1\Search\SearchController::class, 'autocomplete'])->name('api.v1.search.autocomplete');
        Route::get('/search/typo',         [\App\Http\Controllers\Api\V1\Search\SearchController::class, 'typoCorrection'])->name('api.v1.search.typo');
        
        // Universal Notification Extraction Engine API
        Route::post('/extraction/upload',       [\App\Http\Controllers\Api\V1\Extraction\ExtractionController::class, 'upload'])->name('api.v1.extraction.upload');
        Route::get('/extraction/status/{id}',    [\App\Http\Controllers\Api\V1\Extraction\ExtractionController::class, 'status'])->name('api.v1.extraction.status');
        Route::post('/extraction/approve/{id}',  [\App\Http\Controllers\Api\V1\Extraction\ExtractionController::class, 'approve'])->name('api.v1.extraction.approve');
    });

    // Authenticated Candidate Profile & Interaction Endpoints (Throttled to 60 requests per minute)
    Route::middleware(['auth:api', 'active', 'throttle:api'])->group(function () {
        Route::get('/dashboard',            [\App\Http\Controllers\Api\V1\Users\ProfileController::class, 'getDashboardData'])->name('api.v1.dashboard.data');
        Route::post('/profile/update',      [\App\Http\Controllers\Api\V1\Users\ProfileController::class, 'updateProfile'])->name('api.v1.profile.update');
        Route::post('/profile/preferences', [\App\Http\Controllers\Api\V1\Users\ProfileController::class, 'updatePreferences'])->name('api.v1.profile.preferences');
        
        Route::post('/jobs/{id}/bookmark',  [\App\Http\Controllers\Api\V1\Jobs\JobController::class, 'toggleBookmark'])->name('api.v1.jobs.bookmark');
        Route::post('/jobs/{id}/apply',     [\App\Http\Controllers\Api\V1\Jobs\JobController::class, 'applyJob'])->name('api.v1.jobs.apply');
    });
});

