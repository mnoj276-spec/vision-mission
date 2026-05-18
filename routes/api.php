<?php

use App\Domains\Jobs\Controllers\ApplicationController;
use App\Domains\Jobs\Controllers\JobController;
use App\Domains\Users\Controllers\AuthController;
use App\Domains\Users\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Public + Candidate
|--------------------------------------------------------------------------
| Prefix: /api  (applied automatically by bootstrap/app.php)
|
| Public: job detail, auth endpoints
| Protected (auth): dashboard, bookmarks, applications, profile
*/

// ─── Public Endpoints ────────────────────────────────────────────────────────
Route::get('/jobs/{slug}',       [JobController::class, 'show'])->name('jobs.show');

// ─── Authentication ───────────────────────────────────────────────────────────
Route::post('/register',         [AuthController::class, 'register'])->name('register');
Route::post('/login',            [AuthController::class, 'login'])->name('login');
Route::post('/logout',           [AuthController::class, 'logout'])->name('logout');
Route::post('/forgot-password',  [AuthController::class, 'forgotPassword'])->name('password.forgot');
Route::post('/reset-password',   [AuthController::class, 'resetPassword'])->name('password.reset');

// ─── Candidate Authenticated Endpoints ───────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard',             [DashboardController::class,  'getDashboardData'])->name('dashboard.data');
    Route::post('/jobs/{id}/bookmark',   [ApplicationController::class,'toggleBookmark'])->name('jobs.bookmark');
    Route::post('/jobs/{id}/apply',      [ApplicationController::class,'applyJob'])->name('jobs.apply');
    Route::post('/profile/update',       [DashboardController::class,  'updateProfile'])->name('profile.update');
    Route::post('/profile/preferences',  [DashboardController::class,  'updatePreferences'])->name('profile.preferences');
});
