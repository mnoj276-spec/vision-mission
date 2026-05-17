<?php

namespace App\Repositories\Eloquent;

use App\Models\JobPost;
use App\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class JobRepository implements JobRepositoryInterface
{
    /**
     * Get paginated, searched, and filtered published job posts.
     */
    public function getPaginatedFiltered(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return JobPost::query()
            ->published()
            ->search($filters['search'] ?? null)
            ->filterBy($filters)
            ->with(['category', 'department', 'state', 'qualification'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active featured job posts.
     */
    public function getFeatured(int $limit = 5): Collection
    {
        return JobPost::query()
            ->published()
            ->featured()
            ->with(['category', 'department', 'state', 'qualification'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get active recent job posts by category code or raw list.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return JobPost::query()
            ->published()
            ->with(['category', 'department', 'state', 'qualification'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find a published job posting by its SEO friendly slug.
     */
    public function findBySlug(string $slug): ?JobPost
    {
        return JobPost::query()
            ->published()
            ->with(['category', 'department', 'state', 'qualification', 'tags'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Find a job posting by its ID (both published and drafts).
     */
    public function findById(int $id): ?JobPost
    {
        return JobPost::query()
            ->with(['category', 'department', 'state', 'qualification', 'tags'])
            ->find($id);
    }

    /**
     * Create a new job posting (primarily under DRAFT status).
     */
    public function create(array $data): JobPost
    {
        return JobPost::create($data);
    }

    /**
     * Update an existing job posting.
     */
    public function update(int $id, array $data): bool
    {
        $job = JobPost::find($id);
        if ($job) {
            return $job->update($data);
        }
        return false;
    }

    /**
     * Delete a job posting.
     */
    public function delete(int $id): bool
    {
        $job = JobPost::find($id);
        if ($job) {
            return $job->delete();
        }
        return false;
    }

    /**
     * Check if a job post already exists based on title, department and date.
     */
    public function exists(string $title, int $departmentId, string $lastDate): bool
    {
        return JobPost::query()
            ->where('department_id', $departmentId)
            ->where(function($q) use ($title) {
                $q->where('title', $title)
                  ->orWhere('slug', str()->slug($title))
                  ->orWhere('slug', 'like', str()->slug($title) . '-%');
            })
            ->exists();
    }
}
