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

// ─── Advanced Search System Routes ──────────────────────────────────────────
use App\Domains\Jobs\Controllers\SearchController;

Route::get('/search', [SearchController::class, 'search'])->name('search.index');
Route::get('/search/state/{state_slug}', [SearchController::class, 'stateSearch'])->name('search.state');
Route::get('/search/category/{category_slug}', [SearchController::class, 'categorySearch'])->name('search.category');
Route::get('/search/qualification/{qualification_slug}', [SearchController::class, 'qualificationSearch'])->name('search.qualification');
Route::get('/search/organization/{department_slug}', [SearchController::class, 'organizationSearch'])->name('search.organization');

// Lead Capture & Growth Analytics APIs
Route::post('/api/growth/subscribe', [JobController::class, 'subscribeAlerts'])->name('growth.subscribe');
Route::post('/api/growth/track',     [JobController::class, 'trackEvent'])->name('growth.track');

// ─── Programmatic SEO Engine Routes ──────────────────────────────────────────
use App\Domains\Jobs\Controllers\ProgrammaticSeoController;

// Dynamic Administrative Utilities
Route::get('/results', [ProgrammaticSeoController::class, 'results'])->name('seo.results');
Route::get('/admit-cards', [ProgrammaticSeoController::class, 'admitCards'])->name('seo.admit_cards');
Route::get('/answer-keys', [ProgrammaticSeoController::class, 'answerKeys'])->name('seo.answer_keys');
Route::get('/syllabus', [ProgrammaticSeoController::class, 'syllabus'])->name('seo.syllabus');

// Dynamic Job Categories
Route::get('/jobs/railway', [ProgrammaticSeoController::class, 'railwayJobs'])->name('seo.dynamic_railway');
Route::get('/jobs/banking', [ProgrammaticSeoController::class, 'bankingJobs'])->name('seo.dynamic_banking');
Route::get('/jobs/ssc', [ProgrammaticSeoController::class, 'sscJobs'])->name('seo.dynamic_ssc');
Route::get('/jobs/upsc', [ProgrammaticSeoController::class, 'upscJobs'])->name('seo.dynamic_upsc');
Route::get('/jobs/defence', [ProgrammaticSeoController::class, 'defenceJobs'])->name('seo.dynamic_defence');
Route::get('/jobs/psu', [ProgrammaticSeoController::class, 'psuJobs'])->name('seo.dynamic_psu');

// Dynamic Geographic Hierarchies
Route::get('/jobs/state/{state_slug}', [ProgrammaticSeoController::class, 'stateJobs'])->name('seo.dynamic_state');
Route::get('/jobs/state/{state_slug}/{district_slug}', [ProgrammaticSeoController::class, 'districtJobs'])->name('seo.dynamic_district');

// Standalone Crawler-Friendly Individual Detail Pages
Route::get('/job/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.job_detail');
Route::get('/result/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.result_detail');
Route::get('/admit-card/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.admit_card_detail');
Route::get('/answer-key/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.answer_key_detail');
Route::get('/syllabus/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.syllabus_detail');

// Dynamic Google News Compliant News Sitemap
Route::get('/news-sitemap.xml', [ProgrammaticSeoController::class, 'newsSitemap'])->name('seo.news_sitemap');
