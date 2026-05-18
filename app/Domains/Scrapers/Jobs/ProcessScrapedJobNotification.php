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

class ProcessScrapedJobNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected JobPost $jobPost) {}

    public function handle(NotificationService $notificationService): void
    {
        Log::info("Async notification job started for Job ID: {$this->jobPost->id}");
        $notificationService->notifyCandidates($this->jobPost);
        Log::info("Async candidate notification process completed.");
    }
}
