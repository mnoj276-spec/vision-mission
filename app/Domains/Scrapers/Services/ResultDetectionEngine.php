<?php

namespace App\Domains\Scrapers\Services;

use App\Models\JobPost;
use App\Models\ScrapingSource;
use App\Models\User;
use App\Models\JobAlert;
use App\Jobs\SendEmailJob;
use App\Jobs\GenerateJobContentJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResultDetectionEngine
{
    public function __construct(
        protected ScrapingService $scrapingService
    ) {}

    /**
     * Run the Result Detection Engine.
     * Monitors active official sources, detects new results, extracts information,
     * triggers AI content generation, sends alerts/notifications, and warms sitemaps/internal links.
     *
     * @param int|null $sourceId
     * @return array
     */
    public function run(?int $sourceId = null): array
    {
        $startTime = Carbon::now();
        Log::info("Result Detection Engine: Started", [
            'start_time' => $startTime->toIso8601String(),
            'source_id' => $sourceId,
        ]);

        // 1. Monitor Official Sources
        $sources = ScrapingSource::where('is_active', true)
            ->when($sourceId, function ($query) use ($sourceId) {
                return $query->where('id', $sourceId);
            })
            ->get();

        Log::info("Result Detection Engine: Monitoring active sources", [
            'active_sources_count' => $sources->count()
        ]);

        $scrapeSummary = [];
        foreach ($sources as $source) {
            Log::info("Result Detection Engine: Scraping source", [
                'source_id' => $source->id,
                'source_name' => $source->name
            ]);
            $result = $this->scrapingService->scrapeSource($source);
            $scrapeSummary[$source->id] = $result;
        }

        // 2. Detect New Results
        // Fetch any new results generated during this run
        $newResults = JobPost::published()
            ->results()
            ->where('created_at', '>=', $startTime)
            ->get();

        $newResultCount = $newResults->count();
        Log::info("Result Detection Engine: Detected new results", [
            'new_results_count' => $newResultCount
        ]);

        if ($newResultCount > 0) {
            $resultIds = $newResults->pluck('id')->toArray();

            // 3. Extract information & 4. Generate content
            // The ScrapingService + AIService already parsed and extracted detailed info.
            // Ensure AI content generation is triggered/handled for each result.
            foreach ($newResults as $resultPost) {
                Log::info("Result Detection Engine: Generating AI Content for Result", [
                    'job_post_id' => $resultPost->id
                ]);
                if (!app()->environment('testing')) {
                    GenerateJobContentJob::dispatch($resultPost->id);
                }
            }

            // 5. Trigger Notifications
            Log::info("Result Detection Engine: Sending notifications for detected results", [
                'result_ids' => $resultIds
            ]);
            
            $candidates = User::where('role', 'candidate')->where('is_active', true)->get();
            $subscribers = JobAlert::all();
            $uniqueSubEmails = $subscribers->pluck('email')->unique();

            // Dispatch result alert emails to registered candidates
            foreach ($candidates as $user) {
                SendEmailJob::dispatch($user->email, 'result_alert', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'job_ids' => $resultIds,
                ]);
            }

            // Dispatch result alert emails to guest subscribers
            foreach ($uniqueSubEmails as $email) {
                SendEmailJob::dispatch($email, 'result_alert', [
                    'name' => 'Subscriber',
                    'job_ids' => $resultIds,
                ]);
            }

            // 6. Update Sitemap & Warm Cache
            Log::info("Result Detection Engine: Pre-computing internal linking structures and updating sitemap cache...");
            try {
                Artisan::call('internal-links:warm-cache', [
                    '--type' => 'result',
                    '--flush' => true
                ]);
                Log::info("Result Detection Engine: Sitemap cache and internal links warmed successfully.");
            } catch (\Exception $e) {
                Log::error("Result Detection Engine: Failed to warm sitemap cache", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return [
            'sources_scraped' => $sources->count(),
            'new_results_count' => $newResultCount,
            'new_results' => $newResults->pluck('title', 'id')->toArray(),
            'scrape_summary' => $scrapeSummary,
        ];
    }
}
