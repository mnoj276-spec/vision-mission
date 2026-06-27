<?php

namespace App\Domains\Jobs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\District;
use App\Models\JobPost;
use App\Models\Category;
use App\Domains\Jobs\Services\SeoService;
use App\Domains\Jobs\Services\InternalLinkingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProgrammaticSeoController extends Controller
{
    protected SeoService $seoService;
    protected InternalLinkingService $linkingService;

    public function __construct(SeoService $seoService, InternalLinkingService $linkingService)
    {
        $this->seoService = $seoService;
        $this->linkingService = $linkingService;
    }

    /**
     * Helper to render dynamic landing page.
     */
    protected function renderLandingPage(string $type, $jobsQuery, array $seoParams, string $breadcrumb, string $categoryName)
    {
        $jobs = $jobsQuery;
        $seo = $this->seoService->getMetadata($type, $seoParams);
        
        $this->logEvent('page_view', request()->getPathInfo());
        $funnelMetrics = $this->getFunnelMetrics(request()->getPathInfo(), $categoryName);

        // Get enhanced internal links for footer explorer
        $explorerData = $this->linkingService->getLinksForLandingPage($type, $seoParams);

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
            ->paginate(20)->withQueryString();

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
            ->paginate(20)->withQueryString();

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
            ->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%Railway%')
                  ->orWhere('title', 'like', '%RRB%')
                  ->orWhere('description', 'like', '%Railway%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%railway%');
                  });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)->withQueryString();

        return $this->renderLandingPage('railway', $jobs, [], 'Railway Jobs', 'railway');
    }

    /**
     * Banking Jobs Page
     */
    public function bankingJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district'])
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
            ->paginate(20)->withQueryString();

        return $this->renderLandingPage('banking', $jobs, [], 'Banking Jobs', 'banking');
    }

    /**
     * SSC Jobs Page
     */
    public function sscJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%SSC%')
                  ->orWhere('description', 'like', '%SSC%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%ssc%');
                  });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)->withQueryString();

        return $this->renderLandingPage('ssc', $jobs, [], 'SSC Jobs', 'ssc');
    }

    /**
     * UPSC Jobs Page
     */
    public function upscJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%UPSC%')
                  ->orWhere('title', 'like', '%IAS%')
                  ->orWhere('description', 'like', '%UPSC%')
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('slug', 'like', '%upsc%');
                  });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)->withQueryString();

        return $this->renderLandingPage('upsc', $jobs, [], 'UPSC Jobs', 'upsc');
    }

    /**
     * Defence Jobs Page
     */
    public function defenceJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district'])
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
            ->paginate(20)->withQueryString();

        return $this->renderLandingPage('defence', $jobs, [], 'Defence Jobs', 'defence');
    }

    /**
     * PSU Jobs Page
     */
    public function psuJobs()
    {
        $jobs = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district'])
            ->where(function ($q) {
                $q->where('title', 'like', '%PSU%')
                  ->orWhere('title', 'like', '%NTPC%')
                  ->orWhere('title', 'like', '%BHEL%')
                  ->orWhere('title', 'like', '%ONGC%')
                  ->orWhere('description', 'like', '%public sector%');
            })
            ->orderBy('id', 'desc')
            ->paginate(20)->withQueryString();

        return $this->renderLandingPage('psu', $jobs, [], 'PSU Jobs', 'psu');
    }

    /**
     * Exam Results Page
     */
    public function results()
    {
        $jobs = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])->results()->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return $this->renderLandingPage('results', $jobs, [], 'Exam Results', 'results');
    }

    /**
     * Admit Cards Page
     */
    public function admitCards()
    {
        $jobs = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])->admitCards()->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return $this->renderLandingPage('admit_cards', $jobs, [], 'Admit Cards', 'admit_cards');
    }

    /**
     * Answer Keys Page
     */
    public function answerKeys()
    {
        $jobs = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])->answerKeys()->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return $this->renderLandingPage('answer_keys', $jobs, [], 'Answer Keys', 'answer_keys');
    }

    /**
     * Syllabus Hub Page
     */
    public function syllabus()
    {
        $jobs = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])->syllabi()->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return $this->renderLandingPage('syllabus', $jobs, [], 'Syllabus Hub', 'syllabus');
    }

    /**
     * Exam Cutoffs Page
     */
    public function cutoffs()
    {
        $jobs = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])->where('post_type', 'cutoff')->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return $this->renderLandingPage('cutoffs', $jobs, [], 'Exam Cutoffs', 'cutoffs');
    }

    /**
     * Exam Calendars Page
     */
    public function examCalendars()
    {
        $jobs = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])->where('post_type', 'exam_calendar')->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return $this->renderLandingPage('exam_calendars', $jobs, [], 'Exam Calendars', 'exam_calendars');
    }

    /**
     * Previous Year Papers Page
     */
    public function previousYearPapers()
    {
        $jobs = JobPost::published()->with(['category', 'department', 'state', 'qualification', 'district'])->where('post_type', 'prev_paper')->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return $this->renderLandingPage('previous_year_papers', $jobs, [], 'Previous Year Papers', 'previous_year_papers');
    }

    /**
     * Standalone individual crawler-friendly page.
     */
    public function showJob(string $slug)
    {
        $job = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'district', 'tags', 'aiContent'])
            ->where('slug', $slug)
            ->firstOrFail();

        $seo = $this->seoService->getMetadata('detail', ['job' => $job]);
        $schema = $this->seoService->getJobSchema($job);

        // Retrieve and override using approved AI content
        $aiContent = ($job->aiContent && $job->aiContent->status === 'approved') ? $job->aiContent : null;
        if ($aiContent) {
            if (!empty($aiContent->meta_title)) {
                $seo['meta_title'] = $aiContent->meta_title;
            }
            if (!empty($aiContent->meta_description)) {
                $seo['meta_description'] = $aiContent->meta_description;
            }
            if (!empty($aiContent->schema_content)) {
                $schema = array_merge($schema, $aiContent->schema_content);
            }
            
            // Inject FAQ Page schema if FAQs are defined
            if (!empty($aiContent->faqs) && is_array($aiContent->faqs)) {
                $faqSchema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => []
                ];
                foreach ($aiContent->faqs as $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $faqSchema['mainEntity'][] = [
                            '@type' => 'Question',
                            'name' => $faq['question'],
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => strip_tags($faq['answer'])
                            ]
                        ];
                    }
                }
                
                // Nest FAQ inside standard schema or pass it along
                $schema['mainEntity'] = $faqSchema['mainEntity'];
            }
        }

        $this->logEvent('page_view', "/job/{$slug}");

        // Track job details view in analytics infrastructure
        try {
            app(\App\Services\AnalyticsService::class)->trackJobEvent($job->id, 'view');
        } catch (\Exception $e) {}

        // Build automated internal links for this detail page
        $internalLinks = $this->linkingService->getLinksForDetailPage($job);

        // Fetch parent + children timeline sorted by published_at & created_at
        $root = $job->parent_id ? JobPost::find($job->parent_id) : $job;
        if (!$root) {
            $root = $job;
        }

        $timeline = JobPost::where('id', $root->id)
            ->orWhere('parent_id', $root->id)
            ->orderBy('published_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('seo_detail', [
            'job' => $job,
            'aiContent' => $aiContent,
            'pageTitle' => $seo['meta_title'],
            'metaDescription' => $seo['meta_description'],
            'metaKeywords' => $seo['meta_keywords'],
            'pageHeader' => $seo['page_header'],
            'breadcrumbs' => $seo['breadcrumbs'],
            'schema' => $schema,
            'internalLinks' => $internalLinks,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Dynamic News Sitemap XML for Google News compliance.
     * Selects only listings published within the last 48 hours.
     */
    public function newsSitemap()
    {
        $xml = \Illuminate\Support\Facades\Cache::remember('news_sitemap_xml', 600, function () {
            $jobs = JobPost::published()
                ->where('published_at', '>=', Carbon::now()->subHours(48))
                ->orderBy('published_at', 'desc')
                ->get();

            return view('seo.news_sitemap', compact('jobs'))->render();
        });

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

        try {
            if ($eventType === 'page_view') {
                app(\App\Services\AnalyticsService::class)->trackPageView($pagePath, request()->header('referer'));
            }
        } catch (\Exception $e) {}
    }

    protected function getFunnelMetrics(string $pagePath, string $categoryName): array
    {
        $cacheKey = "funnel_metrics_" . md5($pagePath . '_' . $categoryName);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($pagePath, $categoryName) {
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
        });
    }

    /**
     * Track internal link click events for analytics.
     */
    public function trackLinkClick(Request $request)
    {
        $this->linkingService->trackClick([
            'source_id'  => $request->input('source_id'),
            'target_id'  => $request->input('target_id'),
            'target_url' => $request->input('target_url', ''),
            'section'    => $request->input('section', 'unknown'),
            'anchor'     => $request->input('anchor', ''),
        ]);

        return response()->json(['status' => 'tracked'], 200);
    }
}
