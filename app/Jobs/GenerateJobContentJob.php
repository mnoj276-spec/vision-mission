<?php

namespace App\Jobs;

use App\Domains\Jobs\Services\Ai\AiManager;
use App\Models\JobPost;
use App\Models\JobPostAiContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateJobContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public bool $afterCommit = true;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $jobPostId,
        protected ?string $provider = null,
        protected bool $forceRegenerate = false
    ) {}

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [5, 15, 30]; // Exponential backoff intervals
    }

    /**
     * Execute the job.
     */
    public function handle(AiManager $aiManager): void
    {
        $jobPost = JobPost::find($this->jobPostId);

        if (!$jobPost) {
            Log::warning("GenerateJobContentJob: JobPost ID [{$this->jobPostId}] not found. Aborting.");
            return;
        }

        // Avoid duplicate calls unless forced
        $existing = JobPostAiContent::where('job_post_id', $this->jobPostId)->first();
        if ($existing && $existing->status === 'approved' && !$this->forceRegenerate) {
            Log::info("GenerateJobContentJob: Approved AI Content already exists for Job ID [{$this->jobPostId}]. Skipping.");
            return;
        }

        $activeProvider = $this->provider ?: config('services.ai.provider', 'gemini');

        // Upsert standard draft state to indicate processing
        $aiRecord = JobPostAiContent::updateOrCreate(
            ['job_post_id' => $this->jobPostId],
            [
                'provider'      => $activeProvider,
                'error_message' => null, // Reset previous errors
            ]
        );

        try {
            $driver = $aiManager->driver($activeProvider);
            $generated = $driver->generateContent($jobPost);

            $aiRecord->update([
                'summary'           => $generated['summary'],
                'eligibility'       => $generated['eligibility'],
                'selection_process' => $generated['selection_process'],
                'faqs'              => $generated['faqs'],
                'meta_title'        => $generated['meta_title'],
                'meta_description'  => $generated['meta_description'],
                'schema_content'    => $generated['schema_content'],
                'error_message'     => null,
            ]);

            Log::info("GenerateJobContentJob: Successfully generated content for Job ID [{$this->jobPostId}] using [{$activeProvider}]");
        } catch (\Exception $e) {
            Log::error("GenerateJobContentJob failed for Job ID [{$this->jobPostId}]: " . $e->getMessage());

            // Write telemetry logs back into database for administrative diagnostics
            $aiRecord->update([
                'error_message' => $e->getMessage(),
            ]);

            // Re-throw exception to let Laravel Queue system handle attempts & retries
            throw $e;
        }
    }
}
