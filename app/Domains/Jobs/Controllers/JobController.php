<?php

namespace App\Domains\Jobs\Controllers;

use App\Domains\Jobs\Services\Contracts\JobServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\Category;
use App\Models\Qualification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JobController — public-facing job browsing.
 * Was HomeController. Thin HTTP adapter only.
 */
class JobController extends Controller
{
    public function __construct(protected JobServiceInterface $jobService) {}

    public function index(Request $request): mixed
    {
        if ($request->ajax()) {
            return $this->handleAjaxFilters($request);
        }

        // Track homepage view
        try {
            app(\App\Services\AnalyticsService::class)->trackPageView('/', $request->header('referer'));
        } catch (\Exception $e) {}

        $data = $this->jobService->getHomePageData();
        return view('home', $data);
    }

    protected function handleAjaxFilters(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'state_id', 'category_id', 'qualification_id', 'min_salary', 'has_no_fee']);
        $jobs    = $this->jobService->getFilteredJobs($filters, 6);

        // Track AJAX search
        try {
            if ($request->filled('search') || !empty(array_filter($filters))) {
                app(\App\Services\AnalyticsService::class)->trackSearchQuery((string) ($request->input('search') ?? ''), $filters, $jobs->total());
            }
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
            'is_sponsored'    => (bool) $job->is_sponsored,
            'affiliate_link'  => $job->affiliate_link,
        ]);

        return response()->json(['status' => 'success', 'data' => ['jobs' => $formattedJobs, 'current_page' => $jobs->currentPage(), 'last_page' => $jobs->lastPage(), 'total' => $jobs->total()]]);
    }

    public function show(string $slug): JsonResponse
    {
        $job = $this->jobService->getJobDetail($slug);
        if (!$job) return response()->json(['status' => 'error', 'message' => 'Job not found.'], 404);

        // Track job views
        try {
            app(\App\Services\AnalyticsService::class)->trackJobEvent($job->id, 'view');
            app(\App\Services\AnalyticsService::class)->trackPageView('/job/' . $slug, request()->header('referer'));
        } catch (\Exception $e) {}

        return response()->json(['status' => 'success', 'data' => [
            'id'                    => $job->id,
            'title'                 => $job->title,
            'post_type'             => $job->post_type,
            'category'              => $job->category->name      ?? 'Gov Job',
            'department'            => $job->department->name    ?? 'Government',
            'state'                 => $job->state->name         ?? 'Pan India',
            'qualification'         => $job->qualification->name ?? 'Graduate',
            'vacancy_count'         => $job->vacancy_count,
            'salary_min'            => number_format($job->salary_min, 0),
            'salary_max'            => number_format($job->salary_max, 0),
            'application_fee'       => number_format($job->application_fee, 2),
            'age_limit'             => $job->age_limit ?? '18-32 Years',
            'last_date'             => $job->last_date_to_apply?->format('d M Y') ?? 'N/A',
            'exam_date'             => $job->exam_date?->format('d M Y') ?? 'Announced Soon',
            'official_website_link' => $job->official_website_link,
            'apply_link'            => $job->apply_link,
            'affiliate_link'        => $job->affiliate_link,
            'description'           => $job->description,
            'exam_pattern'          => $job->exam_pattern     ?? 'Objective MCQs.',
            'selection_process'     => $job->selection_process ?? 'Written Exam.',
        ]]);
    }

    public function sscJobs()
    {
        $jobs = \App\Models\JobPost::with(['category', 'department', 'state', 'qualification'])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->where('title', 'like', '%SSC%')
                  ->orWhere('description', 'like', '%SSC%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%ssc%');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        $this->logEvent('page_view', '/ssc-jobs');
        $funnelMetrics = $this->getFunnelMetrics('/ssc-jobs');

        return view('seo_landing', [
            'pageTitle' => 'Latest SSC (Staff Selection Commission) Government Jobs 2026',
            'metaDescription' => 'Find active, verified Staff Selection Commission (SSC) job alerts, CGL, CHSL, MTS, and GD constable recruitments. Complete syllabus, exam dates, and salary range details.',
            'metaKeywords' => 'ssc jobs, staff selection commission, ssc cgl, ssc chsl, ssc vacancy, ssc recruitment',
            'pageHeader' => 'SSC Government Jobs & Recruitments',
            'breadcrumb' => 'SSC Jobs',
            'categoryName' => 'ssc',
            'jobs' => $jobs,
            'funnel' => $funnelMetrics
        ]);
    }

    public function railwayJobs()
    {
        $jobs = \App\Models\JobPost::with(['category', 'department', 'state', 'qualification'])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->where('title', 'like', '%Railway%')
                  ->orWhere('title', 'like', '%RRB%')
                  ->orWhere('description', 'like', '%Railway%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%railway%');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        $this->logEvent('page_view', '/railway-jobs');
        $funnelMetrics = $this->getFunnelMetrics('/railway-jobs');

        return view('seo_landing', [
            'pageTitle' => 'Latest Railway (RRB / NTPC) Government Jobs 2026',
            'metaDescription' => 'Get real-time updates on Indian Railways recruitment boards (RRB). Active vacancies for ALP, NTPC, Group D, and technical cadres. Apply online today!',
            'metaKeywords' => 'railway jobs, rrb alp, rrb ntpc, indian railways recruitment, rrb vacancy',
            'pageHeader' => 'Indian Railway (RRB) Jobs',
            'breadcrumb' => 'Railway Jobs',
            'categoryName' => 'railway',
            'jobs' => $jobs,
            'funnel' => $funnelMetrics
        ]);
    }

    public function upscJobs()
    {
        $jobs = \App\Models\JobPost::with(['category', 'department', 'state', 'qualification'])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->where('title', 'like', '%UPSC%')
                  ->orWhere('title', 'like', '%IAS%')
                  ->orWhere('description', 'like', '%UPSC%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%upsc%');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        $this->logEvent('page_view', '/upsc-jobs');
        $funnelMetrics = $this->getFunnelMetrics('/upsc-jobs');

        return view('seo_landing', [
            'pageTitle' => 'Latest UPSC (Union Public Service Commission) Jobs 2026',
            'metaDescription' => 'Browse live Union Public Service Commission (UPSC) recruitment campaigns. Direct alerts for Civil Services IAS, IFS, NDA, CDS, and specialist officers.',
            'metaKeywords' => 'upsc jobs, union public service commission, upsc ias, upsc exam syllabus, upsc recruitment',
            'pageHeader' => 'UPSC Government Jobs & Civil Services',
            'breadcrumb' => 'UPSC Jobs',
            'categoryName' => 'upsc',
            'jobs' => $jobs,
            'funnel' => $funnelMetrics
        ]);
    }

    public function stateJobs()
    {
        $jobs = \App\Models\JobPost::with(['category', 'department', 'state', 'qualification'])
            ->where('status', 'published')
            ->whereHas('state', function ($q) {
                $q->where('code', '!=', 'CENTRAL');
            })
            ->orderBy('id', 'desc')
            ->get();

        $this->logEvent('page_view', '/state-jobs');
        $funnelMetrics = $this->getFunnelMetrics('/state-jobs');

        return view('seo_landing', [
            'pageTitle' => 'Latest State-Level Government Jobs & Recruitments 2026',
            'metaDescription' => 'Discover state-specific government recruitments (UPPSC, MPSC, KPSC, etc.). Local public service commission postings, eligibility terms, and local state notifications.',
            'metaKeywords' => 'state government jobs, state psc vacancy, local government recruitment, state board jobs',
            'pageHeader' => 'State Government Jobs & PSC Boards',
            'breadcrumb' => 'State Jobs',
            'categoryName' => 'state',
            'jobs' => $jobs,
            'funnel' => $funnelMetrics
        ]);
    }

    public function subscribeAlerts(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'category_name' => 'required|string|max:50'
        ]);

        \App\Models\JobAlert::create([
            'email' => $request->email,
            'category_name' => $request->category_name
        ]);

        $this->logEvent('subscribe', '/' . $request->category_name . '-jobs');

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully subscribed to instant ' . strtoupper($request->category_name) . ' job alerts!'
        ]);
    }

    public function trackEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string|in:page_view,subscribe,apply_click',
            'page_path' => 'required|string|max:255'
        ]);

        $this->logEvent($request->event_type, $request->page_path);

        return response()->json([
            'status' => 'success',
            'message' => 'Telemetry logged successfully.'
        ]);
    }

    public function sitemap()
    {
        $xml = \Illuminate\Support\Facades\Cache::remember('sitemap_xml', 3600, function () {
            $jobs = \App\Models\JobPost::where('status', 'published')->orderBy('id', 'desc')->get();
            $categories = \App\Models\Category::all();

            return view('seo.sitemap', compact('jobs', 'categories'))->render();
        });

        return response($xml, 200)
            ->header('Content-Type', 'text/xml');
    }

    protected function logEvent(string $eventType, string $pagePath): void
    {
        \App\Models\GrowthAnalytic::create([
            'event_type' => $eventType,
            'page_path'  => $pagePath
        ]);

        try {
            if ($eventType === 'page_view') {
                app(\App\Services\AnalyticsService::class)->trackPageView($pagePath, request()->header('referer'));
            }
        } catch (\Exception $e) {}
    }

    protected function getFunnelMetrics(string $pagePath): array
    {
        $views = \App\Models\GrowthAnalytic::where('event_type', 'page_view')->where('page_path', $pagePath)->count() + 120;
        $subs = \App\Models\JobAlert::where('category_name', str_replace('-jobs', '', str_replace('/', '', $pagePath)))->count() + 34;
        $applies = \App\Models\GrowthAnalytic::where('event_type', 'apply_click')->where('page_path', $pagePath)->count() + 18;

        $conversionRate = $views > 0 ? round(($subs / $views) * 100, 1) : 0;

        return [
            'views' => $views,
            'subscribers' => $subs,
            'applies' => $applies,
            'conversion_rate' => $conversionRate
        ];
    }

    /**
     * PWA Offline fallback view controller channel.
     */
    public function offline()
    {
        return view('offline', [
            'pageTitle' => 'Offline Mode — GovJobs Recruitment Portal',
            'metaDescription' => 'Your device is currently disconnected from the internet. Standby offline engine is running.',
            'breadcrumbs' => ['Offline Mode' => null]
        ]);
    }
}
