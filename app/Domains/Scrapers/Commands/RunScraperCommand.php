<?php

namespace App\Domains\Scrapers\Commands;

use App\Domains\Scrapers\Jobs\RunWebScraper;
use App\Models\ScrapingSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunScraperCommand extends Command
{
    protected $signature   = 'scraper:run';
    protected $description = 'Orchestrates active scrapers and dispatches crawling tasks to background workers.';

    public function handle(): int
    {
        $lock = \Illuminate\Support\Facades\Cache::lock('command:scraper:run', 600); // 10 minutes max duration

        if (!$lock->get()) {
            $this->warn("Another instance of scraper:run is already active.");
            Log::warning("Overlapping execution of Artisan scraper:run blocked.");
            return Command::SUCCESS;
        }

        try {
            $this->info("Initializing Scraping Engine Scheduler...");
            Log::info("Artisan command scraper:run executed.");

            $sources = ScrapingSource::where('is_active', true)->get();

            if ($sources->isEmpty()) {
                $this->warn("No active scraping sources configured.");
                return Command::SUCCESS;
            }

            $this->info("Found {$sources->count()} active targets. Dispatching...");
            foreach ($sources as $source) {
                $this->line("Dispatching: {$source->name}");
                RunWebScraper::dispatch($source);
            }

            $this->info("All tasks dispatched!");
            return Command::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
