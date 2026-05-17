<?php

namespace App\Jobs;

use App\Models\ScrapingSource;
use App\Services\ScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunWebScraper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ScrapingSource $source;

    /**
     * Create a new job instance.
     */
    public function __construct(ScrapingSource $source)
    {
        $this->source = $source;
    }

    /**
     * Execute the job in the background.
     */
    public function handle(ScrapingService $scrapingService): void
    {
        Log::info("Asynchronous scraper job started for source ID: {$this->source->id}");
        
        $result = $scrapingService->scrapeSource($this->source);
        
        if ($result['success']) {
            Log::info("Asynchronous scraper job completed successfully.");
        } else {
            Log::error("Asynchronous scraper job failed: " . ($result['error'] ?? 'Unknown Error'));
        }
    }
}
