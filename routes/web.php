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
