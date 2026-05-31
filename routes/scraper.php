<?php

use App\Domains\Scrapers\Controllers\ScraperController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Scraper Routes
|--------------------------------------------------------------------------
| All scraper management endpoints. Protected by auth + EnsureAdmin middleware.
| URL prefix: /api/admin  (kept identical to original to avoid JS changes)
*/

Route::middleware(['auth', 'admin', 'permission:create_jobs'])->prefix('api/admin')->group(function () {

    // ─── Scraper Source CRUD ─────────────────────────────────────────────────
    Route::get('/scrapers',          [ScraperController::class, 'index'])->name('admin.scrapers.list');
    Route::post('/scrapers',         [ScraperController::class, 'store'])->name('admin.scrapers.store');
    Route::post('/scrapers/{id}',    [ScraperController::class, 'update'])->name('admin.scrapers.update');
    Route::delete('/scrapers/{id}',  [ScraperController::class, 'destroy'])->name('admin.scrapers.delete');

    // ─── Scraper Operations ──────────────────────────────────────────────────
    Route::post('/scraper/{id}/toggle',       [ScraperController::class, 'toggle'])->name('admin.scraper.toggle');
    Route::post('/scraper/{id}/run',          [ScraperController::class, 'run'])->name('admin.scraper.run');
    Route::post('/quarantine/{id}/rescue',    [ScraperController::class, 'rescue'])->name('admin.quarantine.rescue');
});
