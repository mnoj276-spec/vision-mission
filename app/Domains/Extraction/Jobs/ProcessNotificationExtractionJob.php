<?php

namespace App\Domains\Extraction\Jobs;

use App\Models\ExtractedNotification;
use App\Domains\Extraction\Services\ExtractionPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNotificationExtractionJob implements ShouldQueue
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

    /**
     * Create a new job instance.
     */
    public function __construct(protected ExtractedNotification $notification)
    {
        $this->queue = 'extractions';
    }

    /**
     * Execute the job.
     */
    public function handle(ExtractionPipeline $pipeline): void
    {
        Log::info("ProcessNotificationExtractionJob started for ID: {$this->notification->id}");
        $pipeline->process($this->notification);
    }

    /**
     * Handle a permanent failure of the job.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessNotificationExtractionJob permanently failed for ID: {$this->notification->id} with message: " . $exception->getMessage());
        
        $this->notification->update([
            'status' => 'failed',
            'validation_status' => 'invalid',
            'validation_errors' => ['job' => [$exception->getMessage()]],
        ]);
    }
}
