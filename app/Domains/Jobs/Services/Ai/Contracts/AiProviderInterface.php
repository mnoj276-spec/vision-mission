<?php

namespace App\Domains\Jobs\Services\Ai\Contracts;

use App\Models\JobPost;

interface AiProviderInterface
{
    /**
     * Generate content for the given job.
     * Must return an array with:
     * [
     *     'summary' => string,
     *     'eligibility' => string,
     *     'selection_process' => string,
     *     'faqs' => array, // [{"question": "...", "answer": "..."}]
     *     'meta_title' => string,
     *     'meta_description' => string,
     *     'schema_content' => array
     * ]
     *
     * @param JobPost $job
     * @return array
     */
    public function generateContent(JobPost $job): array;
}
