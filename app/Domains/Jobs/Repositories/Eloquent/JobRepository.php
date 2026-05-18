<?php

namespace App\Domains\Jobs\Repositories\Eloquent;

use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Models\JobPost;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * JobRepository — moved to domain namespace from App\Repositories\Eloquent.
 * All logic preserved exactly.
 */
class JobRepository implements JobRepositoryInterface
{
    public function getPaginatedFiltered(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return JobPost::query()
            ->published()
            ->search($filters['search'] ?? null)
            ->filterBy($filters)
            ->with(['category', 'department', 'state', 'qualification', 'source'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getFeatured(int $limit = 5): Collection
    {
        return JobPost::query()->published()->featured()
            ->with(['category', 'department', 'state', 'qualification', 'source'])
            ->limit($limit)->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return JobPost::query()->published()
            ->with(['category', 'department', 'state', 'qualification', 'source'])
            ->orderBy('published_at', 'desc')->limit($limit)->get();
    }

    public function findBySlug(string $slug): ?JobPost
    {
        return JobPost::query()->published()
            ->with(['category', 'department', 'state', 'qualification', 'tags', 'source'])
            ->where('slug', $slug)->first();
    }

    public function findById(int $id): ?JobPost
    {
        return JobPost::query()
            ->with(['category', 'department', 'state', 'qualification', 'tags', 'source'])
            ->find($id);
    }

    public function create(array $data): JobPost
    {
        return JobPost::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $job = JobPost::find($id);
        return $job ? $job->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $job = JobPost::find($id);
        return $job ? (bool) $job->delete() : false;
    }

    public function exists(string $title, int $departmentId, string $lastDate): bool
    {
        return JobPost::query()
            ->where('department_id', $departmentId)
            ->where(function ($q) use ($title) {
                $q->where('title', $title)
                  ->orWhere('slug', str()->slug($title))
                  ->orWhere('slug', 'like', str()->slug($title) . '-%');
            })->exists();
    }

    /**
     * Exact fingerprint lookup via the unique index — O(1), race-condition safe.
     */
    public function findByFingerprint(string $fingerprint): ?JobPost
    {
        return JobPost::where('fingerprint', $fingerprint)->first();
    }

    /**
     * Return recent posts in the same department for PHP-side fuzzy scoring.
     * Only `id`, `title`, and `fingerprint` are fetched to minimise memory.
     */
    public function findFuzzyDuplicates(int $departmentId, int $lookbackDays = 90): Collection
    {
        return JobPost::query()
            ->where('department_id', $departmentId)
            ->where('created_at', '>=', Carbon::now()->subDays($lookbackDays))
            ->select(['id', 'title', 'fingerprint'])
            ->get();
    }
}
