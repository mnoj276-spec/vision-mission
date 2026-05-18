<?php

namespace App\Domains\Jobs\Services\Contracts;

use App\Models\JobPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface JobServiceInterface
{
    /**
     * Build the data payload for the homepage view.
     */
    public function getHomePageData(): array;

    /**
     * Get AJAX-filtered, paginated job listings.
     */
    public function getFilteredJobs(array $filters, int $perPage = 6): LengthAwarePaginator;

    /**
     * Fetch full detail for a single job by slug.
     */
    public function getJobDetail(string $slug): ?JobPost;

    /**
     * Create a new job posting (manual admin publish).
     * Handles slug generation and default values.
     */
    public function createJob(array $data): JobPost;

    /**
     * Update an existing job posting by ID.
     */
    public function updateJob(int $id, array $data): JobPost;

    /**
     * Delete a job posting by ID.
     */
    public function deleteJob(int $id): void;
}
