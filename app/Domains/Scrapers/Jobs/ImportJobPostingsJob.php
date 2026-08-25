<?php

namespace App\Domains\Scrapers\Jobs;

use App\Domains\Scrapers\Services\ImportAutomationService;
use App\Models\ScrapingSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportJobPostingsJob implements ShouldQueue
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
        return [10, 30, 60];
    }

    public function __construct(
        protected array $payload,
        protected ScrapingSource $source,
        protected string $importType = 'job'
    ) {
        $this->queue = 'imports';
    }

    public function handle(ImportAutomationService $importService): void
    {
        Log::info("Async import job '{$this->importType}' started for source ID: {$this->source->id}");

        $result = match ($this->importType) {
            'result'      => $importService->importResults($this->payload, $this->source),
            'admit_card'  => $importService->importAdmitCards($this->payload, $this->source),
            'answer_key'  => $importService->importAnswerKeys($this->payload, $this->source),
            'syllabus'    => $importService->importSyllabi($this->payload, $this->source),
            'cutoff'      => $importService->importCutoffs($this->payload, $this->source),
            default       => $importService->importJobs($this->payload, $this->source),
        };

        if (isset($result['status']) && $result['status'] === 'success') {
            Log::info("Async import job completed successfully. Job Post ID: " . $result['job_post_id']);
        } else {
            $reason = $result['error'] ?? $result['error_message'] ?? 'Duplicate, quarantined, or failed validation.';
            Log::warning("Async import job finished with status: " . ($result['status'] ?? 'unknown') . ". Reason: " . $reason);
        }
    }

    /**
     * Handle a permanent failure of the job.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Async import job permanently failed: " . $exception->getMessage(), [
            'source_id' => $this->source->id,
            'import_type' => $this->importType,
            'exception' => $exception->getTraceAsString(),
        ]);
    }
}
