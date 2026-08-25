<?php

namespace App\Domains\Scrapers\Jobs;

use App\Domains\Notifications\Services\NotificationService;
use App\Models\JobPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cache;

class ProcessScrapedJobNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    public bool $afterCommit = true;

    /**
     * Determine the time at which the job should be retried.
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function __construct(protected JobPost $jobPost)
    {
        $this->queue = 'notifications';
    }

    public function handle(NotificationService $notificationService): void
    {
        $lock = Cache::lock("notification:lock:{$this->jobPost->id}", 300); // 5 minutes lock max duration

        if ($lock->get()) {
            try {
                Log::info("Async notification job started for Job ID: {$this->jobPost->id}");
                $notificationService->notifyCandidates($this->jobPost);
                Log::info("Async candidate notification process completed.");
            } finally {
                $lock->release();
            }
        } else {
            Log::warning("Notification job for Job ID {$this->jobPost->id} is already running/processed. Skipping execution.");
        }
    }

    /**
     * Handle a permanent failure of the job.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Notification job for Job ID {$this->jobPost->id} permanently failed: " . $exception->getMessage(), [
            'job_post_id' => $this->jobPost->id,
            'exception' => $exception->getTraceAsString(),
        ]);
    }
}
