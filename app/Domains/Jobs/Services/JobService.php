<?php

namespace App\Domains\Jobs\Services;

use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Domains\Jobs\Services\Contracts\JobServiceInterface;
use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\Qualification;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * JobService
 *
 * Single owner of job-domain business logic:
 * – slug generation (deduped from 2 controllers)
 * – default field population
 * – homepage data assembly
 *
 * Controllers remain thin HTTP adapters.
 */
class JobService implements JobServiceInterface
{
    public function __construct(
        protected JobRepositoryInterface $jobRepo
    ) {}

    public function getHomePageData(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('homepage_data', 600, function () {
            $relations = ['category', 'department', 'state', 'qualification', 'source'];

            return [
                'states'        => State::all(),
                'categories'    => Category::where('is_active', true)->get(),
                'qualifications' => Qualification::all(),
                'departments'   => Department::all(),
                'featuredJobs'  => JobPost::published()->rootPosts()->with($relations)->featured()->latest('published_at')->take(6)->get(),
                'recentJobs'    => JobPost::published()->rootPosts()->with($relations)->jobs()->latest('published_at')->take(12)->get(),
                'admitCards'    => JobPost::published()->with($relations)->admitCards()->latest('published_at')->take(12)->get(),
                'results'       => JobPost::published()->with($relations)->results()->latest('published_at')->take(12)->get(),
                'answerKeys'    => JobPost::published()->with($relations)->answerKeys()->latest('published_at')->take(10)->get(),
                'syllabi'       => JobPost::published()->with($relations)->syllabi()->latest('published_at')->take(10)->get(),
                'admissions'    => JobPost::published()->with($relations)->admissions()->latest('published_at')->take(10)->get(),
                'scholarships'  => JobPost::published()->with($relations)->scholarships()->latest('published_at')->take(10)->get(),
                'notices'       => JobPost::published()->with($relations)->notices()->latest('published_at')->take(10)->get(),
                'tickerNotices' => JobPost::published()->with($relations)->latest('published_at')->take(8)->get(),
            ];
        });
    }

    /**
     * Retrieve AJAX-filtered, paginated job listings.
     */
    public function getFilteredJobs(array $filters, int $perPage = 6): LengthAwarePaginator
    {
        // Coerce fee filter to boolean
        if (isset($filters['has_no_fee'])) {
            $filters['has_no_fee'] = filter_var($filters['has_no_fee'], FILTER_VALIDATE_BOOLEAN);
        }

        return $this->jobRepo->getPaginatedFiltered($filters, $perPage);
    }

    /**
     * Fetch full detail for a single job by its slug.
     */
    public function getJobDetail(string $slug): ?JobPost
    {
        return $this->jobRepo->findBySlug($slug);
    }

    /**
     * Create a new published job posting with safe slug generation.
     * Deduplicates the slug logic that previously existed in 2 controllers.
     */
    public function createJob(array $data): JobPost
    {
        $data = $this->sanitizeJobFields($data);
        $data = $this->applyJobDefaults($data);
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['status'] = 'published';
        $data['published_at'] = Carbon::now();
        if (isset($data['last_date_to_apply'])) {
            $data['expires_at'] = $data['last_date_to_apply'];
        }

        return $this->jobRepo->create($data);
    }

    /**
     * Update an existing job posting, refreshing the slug safely.
     */
    public function updateJob(int $id, array $data): JobPost
    {
        $data = $this->sanitizeJobFields($data);
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        if (isset($data['last_date_to_apply'])) {
            $data['expires_at'] = $data['last_date_to_apply'];
        }

        $this->jobRepo->update($id, $data);

        return JobPost::findOrFail($id);
    }

    /**
     * Sanitize JobPost fields to prevent XSS.
     */
    private function sanitizeJobFields(array $data): array
    {
        if (isset($data['title'])) {
            $data['title'] = \App\Services\HtmlSanitizer::sanitizeString($data['title']);
        }
        if (isset($data['description'])) {
            $data['description'] = \App\Services\HtmlSanitizer::sanitizeHtml($data['description']);
        }
        if (isset($data['exam_pattern'])) {
            $data['exam_pattern'] = \App\Services\HtmlSanitizer::sanitizeHtml($data['exam_pattern']);
        }
        if (isset($data['selection_process'])) {
            $data['selection_process'] = \App\Services\HtmlSanitizer::sanitizeHtml($data['selection_process']);
        }
        if (isset($data['age_limit'])) {
            $data['age_limit'] = \App\Services\HtmlSanitizer::sanitizeString($data['age_limit']);
        }
        return $data;
    }

    /**
     * Hard-delete a job posting.
     */
    public function deleteJob(int $id): void
    {
        $this->jobRepo->delete($id);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Generate a unique URL slug by appending a random suffix.
     * Previously duplicated as: str()->slug($title) . '-' . rand(100, 999)
     */
    private function generateUniqueSlug(string $title): string
    {
        return str()->slug($title) . '-' . rand(1000, 9999);
    }

    /**
     * Apply sensible defaults for fields not explicitly provided.
     */
    private function applyJobDefaults(array $data): array
    {
        return array_merge([
            'age_limit'   => '18 - 35 Years',
            'apply_link'  => $data['official_website_link'] ?? null,
        ], $data);
    }
}
