<?php

namespace App\Domains\Jobs\Controllers;

use App\Domains\Jobs\Services\Contracts\SearchServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function __construct(
        protected SearchServiceInterface $searchService
    ) {}

    /**
     * Retrieve global lookup metadata cached for 24 hours.
     */
    protected function getSearchMetadata(): array
    {
        return [
            'states'         => Cache::remember('metadata_states', 86400, fn() => State::all()),
            'categories'     => Cache::remember('metadata_categories', 86400, fn() => Category::where('is_active', true)->get()),
            'qualifications' => Cache::remember('metadata_qualifications', 86400, fn() => Qualification::all()),
            'departments'    => Cache::remember('metadata_departments', 86400, fn() => Department::all()),
        ];
    }

    /**
     * Main unified search dashboard and AJAX JSON response channel.
     */
    public function search(Request $request): mixed
    {
        $filters = $request->only([
            'search', 'state_id', 'category_id', 'qualification_id', 'department_id',
            'state_slug', 'category_slug', 'qualification_slug', 'department_slug',
            'min_salary', 'has_no_fee'
        ]);

        if ($request->ajax()) {
            $jobs = $this->searchService->searchJobs($filters, 8);
            
            // Track search
            try {
                app(\App\Services\AnalyticsService::class)->trackSearchQuery((string) ($request->input('search') ?? ''), $filters, $jobs->total());
            } catch (\Throwable $e) {}
            
            $formattedJobs = collect($jobs->items())->map(fn ($job) => [
                'id'              => $job->id,
                'title'           => $job->title,
                'slug'            => $job->slug,
                'post_type'       => $job->post_type,
                'category'        => $job->category->name    ?? 'Gov Job',
                'department'      => $job->department->name  ?? 'Government',
                'state'           => $job->state->name       ?? 'Pan India',
                'qualification'   => $job->qualification->name ?? 'Graduate',
                'vacancy_count'   => $job->vacancy_count,
                'salary_min'      => number_format($job->salary_min, 0),
                'salary_max'      => number_format($job->salary_max, 0),
                'application_fee' => number_format($job->application_fee, 2),
                'last_date'       => $job->last_date_to_apply?->format('d M Y') ?? 'N/A',
                'is_featured'     => $job->is_featured,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'jobs' => $formattedJobs,
                    'current_page' => $jobs->currentPage(),
                    'last_page' => $jobs->lastPage(),
                    'total' => $jobs->total(),
                    'typo_suggestion' => !empty($filters['search']) ? $this->searchService->getSpellCorrection($filters['search']) : null
                ]
            ]);
        }

        // Standard HTML view load
        $jobs = $this->searchService->searchJobs($filters, 8);
        $spellcheck = !empty($filters['search']) ? $this->searchService->getSpellCorrection($filters['search']) : null;

        // Track page view and search query
        try {
            app(\App\Services\AnalyticsService::class)->trackPageView('/search', $request->header('referer'));
        } catch (\Throwable $e) {}
        try {
            app(\App\Services\AnalyticsService::class)->trackSearchQuery((string) ($request->input('search') ?? ''), $filters, $jobs->total());
        } catch (\Throwable $e) {}

        return view('search', array_merge($this->getSearchMetadata(), [
            'jobs' => $jobs,
            'typoSuggestion' => $spellcheck,
            'activeFilters' => $filters,
            'pageTitle' => 'Advanced Search — Government Jobs & Admit Cards 2026',
            'pageHeader' => 'Advanced Job Finder',
            'metaDescription' => 'Search, filter, and discover verified government jobs by category, state, qualification, and organization. Equipped with auto typo-corrections and sub-millisecond suggestions.',
            'breadcrumbs' => ['Search' => null]
        ]));
    }

    /**
     * SEO Route: Search by State
     */
    public function stateSearch(string $slug)
    {
        $state = State::where('slug', $slug)->firstOrFail();

        $filters = ['state_slug' => $slug];
        $jobs = $this->searchService->searchJobs($filters, 8);

        // Track state search view
        try {
            app(\App\Services\AnalyticsService::class)->trackPageView('/search/state/' . $slug, request()->header('referer'));
        } catch (\Throwable $e) {}
        try {
            app(\App\Services\AnalyticsService::class)->trackSearchQuery('', $filters, $jobs->total());
        } catch (\Throwable $e) {}

        return view('search', array_merge($this->getSearchMetadata(), [
            'jobs' => $jobs,
            'typoSuggestion' => null,
            'activeFilters' => $filters,
            'pageTitle' => "Government Jobs in {$state->name} 2026 — Verified Vacancies",
            'pageHeader' => "Government Jobs in {$state->name}",
            'metaDescription' => "Discover live, verified government jobs, syllabus, and results in {$state->name}. Find openings matching your qualification.",
            'breadcrumbs' => ['State Jobs' => route('seo.state'), $state->name => null]
        ]));
    }

    /**
     * SEO Route: Search by Category
     */
    public function categorySearch(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $filters = ['category_slug' => $slug];
        $jobs = $this->searchService->searchJobs($filters, 8);

        // Track category search view
        try {
            app(\App\Services\AnalyticsService::class)->trackPageView('/search/category/' . $slug, request()->header('referer'));
        } catch (\Throwable $e) {}
        try {
            app(\App\Services\AnalyticsService::class)->trackSearchQuery('', $filters, $jobs->total());
        } catch (\Throwable $e) {}

        return view('search', array_merge($this->getSearchMetadata(), [
            'jobs' => $jobs,
            'typoSuggestion' => null,
            'activeFilters' => $filters,
            'pageTitle' => "{$category->name} Government Recruitments 2026",
            'pageHeader' => "{$category->name} Board Jobs",
            'metaDescription' => "Get active vacancies and exam announcements from {$category->name}. Apply online directly with complete syllabus guidelines.",
            'breadcrumbs' => [$category->name => null]
        ]));
    }

    /**
     * SEO Route: Search by Qualification
     */
    public function qualificationSearch(string $slug)
    {
        $qual = Qualification::where('slug', $slug)->firstOrFail();

        $filters = ['qualification_slug' => $slug];
        $jobs = $this->searchService->searchJobs($filters, 8);

        // Track qualification search view
        try {
            app(\App\Services\AnalyticsService::class)->trackPageView('/search/qualification/' . $slug, request()->header('referer'));
        } catch (\Throwable $e) {}
        try {
            app(\App\Services\AnalyticsService::class)->trackSearchQuery('', $filters, $jobs->total());
        } catch (\Throwable $e) {}

        return view('search', array_merge($this->getSearchMetadata(), [
            'jobs' => $jobs,
            'typoSuggestion' => null,
            'activeFilters' => $filters,
            'pageTitle' => "Government Jobs for {$qual->name} Candidates 2026",
            'pageHeader' => "{$qual->name} Vacancy Board",
            'metaDescription' => "Filter government jobs that specifically require {$qual->name} eligibility. Instant apply guidelines, syllabus breakdowns, and dates.",
            'breadcrumbs' => ["{$qual->name} Jobs" => null]
        ]));
    }

    /**
     * SEO Route: Search by Organization/Department
     */
    public function organizationSearch(string $slug)
    {
        $dept = Department::where('slug', $slug)->firstOrFail();

        $filters = ['department_slug' => $slug];
        $jobs = $this->searchService->searchJobs($filters, 8);

        // Track organization search view
        try {
            app(\App\Services\AnalyticsService::class)->trackPageView('/search/organization/' . $slug, request()->header('referer'));
        } catch (\Throwable $e) {}
        try {
            app(\App\Services\AnalyticsService::class)->trackSearchQuery('', $filters, $jobs->total());
        } catch (\Throwable $e) {}

        return view('search', array_merge($this->getSearchMetadata(), [
            'jobs' => $jobs,
            'typoSuggestion' => null,
            'activeFilters' => $filters,
            'pageTitle' => "{$dept->name} ({$dept->code}) Recruitments 2026",
            'pageHeader' => "{$dept->name} Vacancies",
            'metaDescription' => "Search active recruitments under {$dept->name}. Verify exam dates, age limits, syllabus, and application fee structures in real-time.",
            'breadcrumbs' => [$dept->code => null]
        ]));
    }

    /**
     * API: Autocomplete suggestions prefix matching.
     */
    public function apiAutocomplete(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        return response()->json([
            'status' => 'success',
            'data' => $this->searchService->getAutocompleteSuggestions($q)
        ]);
    }

    /**
     * API: Typo spelling check corrections.
     */
    public function apiTypoCorrection(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        return response()->json([
            'status' => 'success',
            'data' => [
                'suggestion' => $this->searchService->getSpellCorrection($q)
            ]
        ]);
    }
}
