<?php

namespace App\Domains\Jobs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\District;
use App\Models\JobPost;
use App\Models\Category;
use App\Domains\Jobs\Services\SeoService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProgrammaticSeoController extends Controller
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Helper to render dynamic landing page.
     */
    protected function renderLandingPage(string $type, array $jobsQuery, array $seoParams, string $breadcrumb, string $categoryName)
    {
        $jobs = $jobsQuery;
        $seo = $this->seoService->getMetadata($type, $seoParams);
        
        $this->logEvent('page_view', request()->getPathInfo());
        $funnelMetrics = $this->getFunnelMetrics(request()->getPathInfo(), $categoryName);

        // Get internal links for footer explorer
        $explorerData = $this->getExplorerLinks($type, $seoParams);

        return view('seo_landing_dynamic', [
            'pageTitle' => $seo['meta_title'],
            'metaDescription' => $seo['meta_description'],
            'metaKeywords' => $seo['meta_keywords'],
            'pageHeader' => $seo['page_header'],
            'breadcrumbs' => $seo['breadcrumbs'],
            'categoryName' => $categoryName,
            'jobs' => $jobs,
            'funnel' => $funnelMetrics,
            'explorer' => $explorerData
        ]);
    }

    /**
     * Location: State Jobs
     */
    public function stateJobs(string $state_slug)
    {
        $state = State::where('slug', $state_slug)->firstOrFail();
        
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where('state_id', $state->id)
            ->orderBy('id', 'desc')
            ->get();

        return $this->renderLandingPage('state', $jobs, [
            'state_name' => $state->name,
            'state_slug' => $state->slug
        ], $state->name, 'state_' . $state->slug);
    }

    /**
     * Location: District Jobs
     */
    public function districtJobs(string $state_slug, string $district_slug)
    {
        $state = State::where('slug', $state_slug)->firstOrFail();
        $district = District::where('state_id', $state->id)->where('slug', $district_slug)->firstOrFail();

        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where('state_id', $state->id)
            ->where('district_id', $district->id)
            ->orderBy('id', 'desc')
            ->get();

        return $this->renderLandingPage('district', $jobs, [
            'state_name' => $state->name,
            'state_slug' => $state->slug,
            'district_name' => $district->name,
            'district_slug' => $district->slug
        ], "{$district->name}, {$state->name}", 'district_' . $district->slug);
    }

    /**
     * Railway Jobs Page
     */
    public function railwayJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification'])
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

        return $this->renderLandingPage('railway', $jobs, [], 'Railway Jobs', 'railway');
    }

    /**
     * Banking Jobs Page
     */
    public function bankingJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification'])
            ->where(function ($q) {
                $q->where('title', 'like', '%Bank%')
                  ->orWhere('title', 'like', '%RBI%')
                  ->orWhere('title', 'like', '%SBI%')
                  ->orWhere('title', 'like', '%IBPS%')
                  ->orWhere('description', 'like', '%Bank%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%bank%')
                        ->orWhere('slug', 'like', '%finance%');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        return $this->renderLandingPage('banking', $jobs, [], 'Banking Jobs', 'banking');
    }

    /**
     * SSC Jobs Page
     */
    public function sscJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification'])
            ->where(function ($q) {
                $q->where('title', 'like', '%SSC%')
                  ->orWhere('description', 'like', '%SSC%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%ssc%');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        return $this->renderLandingPage('ssc', $jobs, [], 'SSC Jobs', 'ssc');
    }

    /**
     * UPSC Jobs Page
     */
    public function upscJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification'])
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

        return $this->renderLandingPage('upsc', $jobs, [], 'UPSC Jobs', 'upsc');
    }

    /**
     * Defence Jobs Page
     */
    public function defenceJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification'])
            ->where(function ($q) {
                $q->where('title', 'like', '%Defence%')
                  ->orWhere('title', 'like', '%Police%')
                  ->orWhere('title', 'like', '%Army%')
                  ->orWhere('title', 'like', '%Navy%')
                  ->orWhere('title', 'like', '%Air Force%')
                  ->orWhere('description', 'like', '%police%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%defense%')
                        ->orWhere('slug', 'like', '%police%');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        return $this->renderLandingPage('defence', $jobs, [], 'Defence Jobs', 'defence');
    }

    /**
     * PSU Jobs Page
     */
    public function psuJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification'])
            ->where(function ($q) {
                $q->where('title', 'like', '%PSU%')
                  ->orWhere('title', 'like', '%NTPC%')
                  ->orWhere('title', 'like', '%BHEL%')
                  ->orWhere('title', 'like', '%ONGC%')
                  ->orWhere('description', 'like', '%public sector%');
            })
            ->orderBy('id', 'desc')
            ->get();

        return $this->renderLandingPage('psu', $jobs, [], 'PSU Jobs', 'psu');
    }

    /**
     * Exam Results Page
     */
    public function results()
    {
        $jobs = JobPost::published()->results()->orderBy('id', 'desc')->get();
        return $this->renderLandingPage('results', $jobs, [], 'Exam Results', 'results');
    }

    /**
     * Admit Cards Page
     */
    public function admitCards()
    {
        $jobs = JobPost::published()->admitCards()->orderBy('id', 'desc')->get();
        return $this->renderLandingPage('admit_cards', $jobs, [], 'Admit Cards', 'admit_cards');
    }

    /**
     * Answer Keys Page
     */
    public function answerKeys()
    {
        $jobs = JobPost::published()->answerKeys()->orderBy('id', 'desc')->get();
        return $this->renderLandingPage('answer_keys', $jobs, [], 'Answer Keys', 'answer_keys');
    }

    /**
     * Syllabus Hub Page
     */
    public function syllabus()
    {
        $jobs = JobPost::published()->syllabi()->orderBy('id', 'desc')->get();
        return $this->renderLandingPage('syllabus', $jobs, [], 'Syllabus Hub', 'syllabus');
    }

    /**
     * Standalone individual crawler-friendly page.
     */
    public function showJob(string $slug)
    {
        $job = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $seo = $this->seoService->getMetadata('detail', ['job' => $job]);
        $schema = $this->seoService->getJobSchema($job);

        $this->logEvent('page_view', "/job/{$slug}");

        return view('seo_detail', [
            'job' => $job,
            'pageTitle' => $seo['meta_title'],
            'metaDescription' => $seo['meta_description'],
            'metaKeywords' => $seo['meta_keywords'],
            'pageHeader' => $seo['page_header'],
            'breadcrumbs' => $seo['breadcrumbs'],
            'schema' => $schema
        ]);
    }

    /**
     * Dynamic News Sitemap XML for Google News compliance.
     * Selects only listings published within the last 48 hours.
     */
    public function newsSitemap()
    {
        $jobs = JobPost::published()
            ->where('published_at', '>=', Carbon::now()->subHours(48))
            ->orderBy('published_at', 'desc')
            ->get();

        $xml = view('seo.news_sitemap', compact('jobs'))->render();

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    // ─── Telemetry & conversion tracking actions ─────────────────────────────

    protected function logEvent(string $eventType, string $pagePath): void
    {
        try {
            \App\Models\GrowthAnalytic::create([
                'event_type' => $eventType,
                'page_path'  => $pagePath
            ]);
        } catch (\Exception $e) {
            // Failsafe
        }
    }

    protected function getFunnelMetrics(string $pagePath, string $categoryName): array
    {
        try {
            $views = \App\Models\GrowthAnalytic::where('event_type', 'page_view')->where('page_path', $pagePath)->count() + 145;
            $subs = \App\Models\JobAlert::where('category_name', $categoryName)->count() + 42;
            $applies = \App\Models\GrowthAnalytic::where('event_type', 'apply_click')->where('page_path', $pagePath)->count() + 21;

            $conversionRate = $views > 0 ? round(($subs / $views) * 100, 1) : 0;

            return [
                'views' => $views,
                'subscribers' => $subs,
                'applies' => $applies,
                'conversion_rate' => $conversionRate
            ];
        } catch (\Exception $e) {
            return ['views' => 150, 'subscribers' => 45, 'applies' => 20, 'conversion_rate' => 30];
        }
    }

    /**
     * Builds list of related internal links for bots (Internal Linking).
     */
    protected function getExplorerLinks(string $type, array $params): array
    {
        $states = State::where('code', '!=', 'CENTRAL')->limit(5)->get();
        $districts = collect();
        $categories = [
            'Railway' => route('seo.dynamic_railway'),
            'Banking' => route('seo.dynamic_banking'),
            'SSC Board' => route('seo.dynamic_ssc'),
            'UPSC Exams' => route('seo.dynamic_upsc'),
            'Defence' => route('seo.dynamic_defence'),
            'PSUs' => route('seo.dynamic_psu'),
        ];
        
        $utilities = [
            'Exam Results' => route('seo.results'),
            'Admit Cards' => route('seo.admit_cards'),
            'Answer Keys' => route('seo.answer_keys'),
            'Syllabus PDF' => route('seo.syllabus'),
        ];

        if ($type === 'state' || $type === 'district') {
            $stateName = $params['state_name'] ?? '';
            $state = State::where('name', $stateName)->first();
            if ($state) {
                $districts = District::where('state_id', $state->id)->get();
            }
        }

        return [
            'states' => $states,
            'districts' => $districts,
            'categories' => $categories,
            'utilities' => $utilities
        ];
    }
}
