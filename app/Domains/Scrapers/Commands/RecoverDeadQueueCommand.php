<?php

namespace App\Domains\Scrapers\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecoverDeadQueueCommand extends Command
{
    protected $signature = 'scraper:recover-dead-queue';
    protected $description = 'Scans failed_jobs table and programmatically retries failed scraper tasks.';

    public function handle(): int
    {
        $this->info("Scanning failed queue for scraper jobs...");
        Log::info("Running scraper:recover-dead-queue Artisan command.");

        // Query failed jobs associated with the scraper pipeline
        $failedJobs = DB::table('failed_jobs')
            ->where('payload', 'like', '%RunWebScraper%')
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->info("No failed scraper jobs detected in the queue.");
            return Command::SUCCESS;
        }

        $count = $failedJobs->count();
        $this->warn("Detected {$count} failed scraper jobs. Recovering...");
        Log::warning("Dead Queue Recovery: Attempting to retry {$count} failed scraping jobs.");

        foreach ($failedJobs as $job) {
            $this->line("Retrying failed job UUID: {$job->uuid} (Queue: {$job->queue})");
            
            // Programmatically execute Laravel's native queue:retry command for the job UUID
            Artisan::call('queue:retry', ['id' => [$job->uuid]]);
        }

        $this->info("Recovery complete! Retried {$count} jobs.");
        Log::info("Dead Queue Recovery successfully retried {$count} failed scraping jobs.");

        return Command::SUCCESS;
    }
}
