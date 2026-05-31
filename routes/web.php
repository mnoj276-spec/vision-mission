<?php

use App\Domains\Jobs\Controllers\JobController;
use App\Domains\Jobs\Controllers\ApplicationController;
use App\Domains\Users\Controllers\AuthController;
use App\Domains\Users\Controllers\DashboardController;
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

// PWA Offline Fallback View
Route::get('/offline',      [JobController::class, 'offline'])->name('pwa.offline');

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

// High-Performance Analytics Telemetry APIs
use App\Http\Controllers\Api\AnalyticsApiController;
Route::post('/api/analytics/page-view',  [AnalyticsApiController::class, 'trackPageView'])->name('analytics.page_view');
Route::post('/api/analytics/job-event',  [AnalyticsApiController::class, 'trackJobInteraction'])->name('analytics.job_event');
Route::post('/api/analytics/ad-event',   [AnalyticsApiController::class, 'trackAdEvent'])->name('analytics.ad_event');

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

// Standalone Crawler-Friendly Individual Detail Pages (with Crawl Optimization Headers)
Route::middleware('internal_linking')->group(function () {
    Route::get('/job/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.job_detail');
    Route::get('/result/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.result_detail');
    Route::get('/admit-card/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.admit_card_detail');
    Route::get('/answer-key/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.answer_key_detail');
    Route::get('/syllabus/{slug}', [ProgrammaticSeoController::class, 'showJob'])->name('seo.syllabus_detail');
});

// Dynamic Google News Compliant News Sitemap
Route::get('/news-sitemap.xml', [ProgrammaticSeoController::class, 'newsSitemap'])->name('seo.news_sitemap');

// ─── Developer Interactive OpenAPI Documentation ────────────────────────────
Route::get('/docs', [\App\Http\Controllers\Api\DocsController::class, 'index'])->name('api.docs');

// ─── Internal Link Click Tracking ───────────────────────────────────────────
Route::post('/api/internal-link/click', [ProgrammaticSeoController::class, 'trackLinkClick'])
    ->name('internal_link.track');

// ─── Email Automation Tracking Routes ───────────────────────────────────────
use App\Http\Controllers\EmailTrackingController;
Route::get('/email/track/open/{token}', [EmailTrackingController::class, 'trackOpen'])->name('email.track.open');
Route::get('/email/track/click/{token}', [EmailTrackingController::class, 'trackClick'])->name('email.track.click');

// ─── Monetization & Revenue Infrastructure Routes ───────────────────────────
use App\Http\Controllers\MonetizationController;
Route::get('/go/{slug}', [MonetizationController::class, 'redirectAffiliate'])->name('monetization.affiliate_redirect');
Route::post('/api/membership/upgrade', [MonetizationController::class, 'upgradeMembership'])->middleware('auth')->name('monetization.membership_upgrade');
Route::get('/api/admin/revenue-analytics', [MonetizationController::class, 'getRevenueAnalytics'])->middleware(['auth', 'admin'])->name('monetization.revenue_analytics');

// ─── Stateful AJAX Front-end APIs (Session & CSRF Protected) ─────────────────
Route::prefix('api')->group(function () {
    // Public AJAX Endpoints
    Route::get('/jobs/{slug}',       [JobController::class, 'show'])->name('jobs.show');
    Route::get('/search/autocomplete', [\App\Domains\Jobs\Controllers\SearchController::class, 'apiAutocomplete'])->name('api.search.autocomplete');
    Route::get('/search/typo',         [\App\Domains\Jobs\Controllers\SearchController::class, 'apiTypoCorrection'])->name('api.search.typo');

    // Authentication
    Route::post('/register',         [AuthController::class, 'register'])->name('register');
    Route::post('/login',            [AuthController::class, 'login'])->name('login');
    Route::post('/logout',           [AuthController::class, 'logout'])->name('logout');
    Route::post('/forgot-password',  [AuthController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/reset-password',   [AuthController::class, 'resetPassword'])->name('password.reset');

    // Candidate Authenticated Actions
    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('/dashboard',             [DashboardController::class,  'getDashboardData'])->name('dashboard.data');
        Route::post('/jobs/{id}/bookmark',   [ApplicationController::class,'toggleBookmark'])->name('jobs.bookmark');
        Route::post('/jobs/{id}/apply',      [ApplicationController::class,'applyJob'])->name('jobs.apply');
        Route::post('/profile/update',       [DashboardController::class,  'updateProfile'])->name('profile.update');
        Route::post('/profile/preferences',  [DashboardController::class,  'updatePreferences'])->name('profile.preferences');
    });
});


