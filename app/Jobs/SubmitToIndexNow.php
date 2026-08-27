<?php

namespace App\Jobs;

use App\Domains\Jobs\Controllers\SitemapController;
use App\Domains\Jobs\Services\IndexNowService;
use App\Models\JobPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitToIndexNow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;


    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $jobPostId
    ) {}

    /**
     * Execute the job.
     *
     * Resolves the job post's canonical URL and submits it to IndexNow.
     * Runs on the queue to avoid blocking the web request cycle.
     */
    public function handle(IndexNowService $indexNowService): void
    {
        if (!$indexNowService->isReady()) {
            Log::channel('single')->debug('[IndexNow] Skipped — service not configured');
            return;
        }

        $jobPost = JobPost::find($this->jobPostId);

        if (!$jobPost || $jobPost->status !== 'published') {
            Log::channel('single')->debug('[IndexNow] Skipped — post not found or not published', [
                'id' => $this->jobPostId,
            ]);
            return;
        }

        try {
            $url = SitemapController::getDetailRoute($jobPost);
            $indexNowService->submitUrl($url);
        } catch (\Exception $e) {
            Log::channel('single')->warning('[IndexNow] Queue job failed', [
                'id'    => $this->jobPostId,
                'error' => $e->getMessage(),
            ]);

            // Rethrow to trigger retry
            throw $e;
        }
    }
}
