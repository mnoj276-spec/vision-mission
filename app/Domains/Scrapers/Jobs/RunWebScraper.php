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

class RunWebScraper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected ScrapingSource $source) {}

    public function handle(ScrapingService $scrapingService): void
    {
        Log::info("Async scraper job started for source ID: {$this->source->id}");
        $result = $scrapingService->scrapeSource($this->source);
        $result['success']
            ? Log::info("Async scraper job completed successfully.")
            : Log::error("Async scraper job failed: " . ($result['error'] ?? 'Unknown Error'));
    }
}
