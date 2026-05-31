<?php

namespace App\Domains\Scrapers\Commands;

use App\Domains\Scrapers\Services\ResultDetectionEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RunResultEngineCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'scraper:detect-results {--source= : Optional specific Scraping Source ID to run}';

    /**
     * The console command description.
     */
    protected $description = 'Executes the Result Detection Engine to monitor sources, detect results, alert candidates, and warm sitemaps';

    /**
     * Execute the console command.
     */
    public function handle(ResultDetectionEngine $engine): int
    {
        $lock = Cache::lock('command:scraper:detect-results', 600); // 10 minutes maximum duration

        if (!$lock->get()) {
            $this->warn("Another instance of scraper:detect-results is already active.");
            Log::warning("Overlapping execution of Artisan scraper:detect-results blocked.");
            return Command::SUCCESS;
        }

        try {
            $this->info("Initializing Result Detection Engine...");
            Log::info("Artisan command scraper:detect-results executed.");

            $sourceId = $this->option('source') ? (int) $this->option('source') : null;

            $result = $engine->run($sourceId);

            $this->info("Execution complete!");
            $this->line("Sources Scraped: " . $result['sources_scraped']);
            $this->line("New Results Detected: " . $result['new_results_count']);

            if ($result['new_results_count'] > 0) {
                $this->info("Detected Results:");
                foreach ($result['new_results'] as $id => $title) {
                    $this->line(" - [ID: {$id}] {$title}");
                }
            }

            return Command::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
