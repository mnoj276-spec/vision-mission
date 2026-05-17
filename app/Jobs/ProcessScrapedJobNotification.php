<?php

namespace App\Jobs;

use App\Models\JobPost;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScrapedJobNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected JobPost $jobPost;

    /**
     * Create a new job instance.
     */
    public function __construct(JobPost $jobPost)
    {
        $this->jobPost = $jobPost;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info("Asynchronous notification job started for newly published Job ID: {$this->jobPost->id}");
        
        $notificationService->notifyCandidates($this->jobPost);
        
        Log::info("Asynchronous candidate notification process completed.");
    }
}
