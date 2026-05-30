<?php

namespace App\Domains\Scrapers\Jobs;

use App\Domains\Scrapers\Services\ScrapingService;
use App\Models\ScrapingSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cache;

class RunWebScraper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Determine the time at which the job should be retried.
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(protected ScrapingSource $source)
    {
        $this->queue = 'scrapers';
    }

    public function handle(ScrapingService $scrapingService): void
    {
        $lock = Cache::lock("scraper:lock:{$this->source->id}", 600); // 10 minutes lock max duration

        if ($lock->get()) {
            try {
                Log::info("Async scraper job started for source ID: {$this->source->id} (Attempt #{$this->attempts()})");
                $result = $scrapingService->scrapeSource($this->source, $this->attempts());
                $result['success']
                    ? Log::info("Async scraper job completed successfully.")
                    : Log::error("Async scraper job failed: " . ($result['error'] ?? 'Unknown Error'));
            } finally {
                $lock->release();
            }
        } else {
            Log::warning("Scraper job for source ID {$this->source->id} is already running. Skipping execution.");
        }
    }

    /**
     * Handle a permanent failure of the job.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Scraper job for source ID {$this->source->id} permanently failed: " . $exception->getMessage(), [
            'source_id' => $this->source->id,
            'exception' => $exception->getTraceAsString(),
        ]);
    }
}
