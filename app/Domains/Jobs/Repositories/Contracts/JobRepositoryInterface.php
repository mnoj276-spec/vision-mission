<?php

namespace App\Domains\Jobs\Repositories\Contracts;

use App\Models\JobPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

interface JobRepositoryInterface
{
    /**
     * Get paginated, searched, and filtered published job posts.
     */
    public function getPaginatedFiltered(array $filters, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get active featured job posts.
     */
    public function getFeatured(int $limit = 5): Collection;

    /**
     * Get active recent job posts.
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Find a published job posting by its SEO-friendly slug.
     */
    public function findBySlug(string $slug): ?JobPost;

    /**
     * Find a job posting by its ID (published and drafts).
     */
    public function findById(int $id): ?JobPost;

    /**
     * Create a new job posting.
     */
    public function create(array $data): JobPost;

    /**
     * Update an existing job posting.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a job posting.
     */
    public function delete(int $id): bool;

    /**
     * Check if a job post already exists based on title, department and date.
     * Retained for backwards-compatibility with existing callers.
     */
    public function exists(string $title, int $departmentId, string $lastDate): bool;

    /**
     * Look up an existing job post by its exact SHA-256 fingerprint.
     * Uses the unique index — O(1) lookup, safe under concurrent inserts.
     */
    public function findByFingerprint(string $fingerprint): ?JobPost;

    /**
     * Return a Collection of recent job posts in the same department for
     * fuzzy-similarity scoring in PHP.
     *
     * Scoped to the last $lookbackDays days to keep the candidate pool small.
     */
    public function findFuzzyDuplicates(int $departmentId, int $lookbackDays = 90): Collection;
}
