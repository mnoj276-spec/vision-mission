<?php

namespace App\Console\Commands;

use App\Models\ScrapingSource;
use App\Jobs\RunWebScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunScraperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Orchestrates active scrapers and dispatches crawling tasks to background workers.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Initializing Scraping Engine Scheduler...");
        Log::info("Artisan command scraper:run executed.");

        // Query active scraper targets
        $sources = ScrapingSource::where('is_active', true)->get();

        if ($sources->isEmpty()) {
            $this->warn("No active scraping sources configured in the database.");
            return Command::SUCCESS;
        }

        $this->info("Found " . $sources->count() . " active scraper targets. Dispatching to queue...");

        foreach ($sources as $source) {
            $this->line("Dispatching Scraper Job for: {$source->name}");
            
            // Dispatch asynchronously to the queue workers
            RunWebScraper::dispatch($source);
        }

        $this->info("All scraping tasks successfully dispatched to the queue!");
        return Command::SUCCESS;
    }
}
