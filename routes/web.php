<?php

use App\Domains\Jobs\Controllers\JobController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Only the SPA shell (public homepage Blade view).
| All AJAX/API calls are handled in routes/api.php, routes/admin.php, routes/scraper.php.
*/

Route::get('/', [JobController::class, 'index'])->name('home');

// Growth & SEO Landing Pages
Route::get('/ssc-jobs',     [JobController::class, 'sscJobs'])->name('seo.ssc');
Route::get('/railway-jobs', [JobController::class, 'railwayJobs'])->name('seo.railway');
Route::get('/upsc-jobs',    [JobController::class, 'upscJobs'])->name('seo.upsc');
Route::get('/state-jobs',   [JobController::class, 'stateJobs'])->name('seo.state');

// Automated XML Sitemap
Route::get('/sitemap.xml',  [JobController::class, 'sitemap'])->name('sitemap');

// Lead Capture & Growth Analytics APIs
Route::post('/api/growth/subscribe', [JobController::class, 'subscribeAlerts'])->name('growth.subscribe');
Route::post('/api/growth/track',     [JobController::class, 'trackEvent'])->name('growth.track');
