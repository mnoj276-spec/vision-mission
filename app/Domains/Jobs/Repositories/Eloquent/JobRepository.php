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
            ->rootPosts()
            ->jobs()
            ->search($filters['search'] ?? null)
            ->filterBy($filters)
            ->with(['category', 'department', 'state', 'qualification', 'source'])
            ->orderBy('is_sponsored', 'desc')
            ->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getByState(int $stateId, int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where('state_id', $stateId)->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getByDistrict(int $stateId, int $districtId, int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where('state_id', $stateId)->where('district_id', $districtId)->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getRailwayJobs(int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%Railway%')->orWhere('title', 'like', '%RRB%')->orWhere('title', 'like', '%RRC%')->orWhere('description', 'like', '%railway%');
            })->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getBankingJobs(int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%Bank%')->orWhere('title', 'like', '%IBPS%')->orWhere('title', 'like', '%SBI%')->orWhere('title', 'like', '%RBI%');
            })->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getSscJobs(int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%SSC%')->orWhere('title', 'like', '%Staff Selection%')->orWhere('title', 'like', '%CHSL%')->orWhere('title', 'like', '%CGL%');
            })->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getUpscJobs(int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%UPSC%')->orWhere('title', 'like', '%Union Public Service%')->orWhere('title', 'like', '%Civil Services%')->orWhere('title', 'like', '%NDA%');
            })->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getDefenceJobs(int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%Army%')->orWhere('title', 'like', '%Navy%')->orWhere('title', 'like', '%Air Force%')->orWhere('title', 'like', '%Defence%')->orWhere('title', 'like', '%Police%');
            })->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getPsuJobs(int $perPage = 20): LengthAwarePaginator
    {
        return JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%PSU%')->orWhere('title', 'like', '%NTPC%')->orWhere('title', 'like', '%BHEL%')->orWhere('title', 'like', '%ONGC%')->orWhere('description', 'like', '%public sector%');
            })->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getByType(string $postType, int $perPage = 20): LengthAwarePaginator
    {
        $query = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district']);
        
        switch($postType) {
            case 'results': $query->results(); break;
            case 'admit_cards': $query->admitCards(); break;
            case 'answer_keys': $query->answerKeys(); break;
            case 'syllabus': $query->syllabi(); break;
            default: $query->where('post_type', $postType); break;
        }
        
        return $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getJobForSeoDetail(string $slug): ?JobPost
    {
        return JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district', 'tags', 'aiContent', 'vacancyDetails', 'categoryWiseVacancies'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getNewsSitemapJobs(int $hoursBack = 48): Collection
    {
        return JobPost::published()
            ->where('published_at', '>=', Carbon::now()->subHours($hoursBack))
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function getFeatured(int $limit = 5): Collection
    {
        return JobPost::query()->published()->rootPosts()->featured()
            ->with(['category', 'department', 'state', 'qualification', 'source'])
            ->limit($limit)->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return JobPost::query()->published()->rootPosts()
            ->with(['category', 'department', 'state', 'qualification', 'source'])
            ->orderBy('published_at', 'desc')->limit($limit)->get();
    }

    public function findBySlug(string $slug): ?JobPost
    {
        return JobPost::query()->published()
            ->with(['category', 'department', 'state', 'qualification', 'tags', 'source', 'parent', 'children', 'categoryVacancies', 'vacancyDetails', 'categoryWiseVacancies'])
            ->where('slug', $slug)->first();
    }

    public function findById(int $id): ?JobPost
    {
        return JobPost::query()
            ->with(['category', 'department', 'state', 'qualification', 'tags', 'source', 'categoryVacancies', 'vacancyDetails', 'categoryWiseVacancies'])
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
            ->select(['id', 'title', 'fingerprint', 'parent_id'])
            ->get();
    }
}
