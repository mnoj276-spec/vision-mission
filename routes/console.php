<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Automatically execute the Scraping Engine scheduler every 5 minutes safely
Schedule::command('scraper:run')->everyFiveMinutes()->withoutOverlapping(10)->onOneServer();

// Automatically execute the Result Detection Engine every 10 minutes safely
Schedule::command('scraper:detect-results')->everyTenMinutes()->withoutOverlapping(10)->onOneServer();

// Marketing Automation Email Schedulers
Schedule::command('email:welcome-series-scheduler')->daily()->withoutOverlapping(10)->onOneServer();
Schedule::command('email:send-alerts')->hourly()->withoutOverlapping(10)->onOneServer();
Schedule::command('email:send-weekly-digest')->weeklyOn(1, '09:00')->withoutOverlapping(10)->onOneServer();
Schedule::command('email:send-reengagement')->daily()->withoutOverlapping(10)->onOneServer();
