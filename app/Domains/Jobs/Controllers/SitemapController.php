<?php

namespace App\Domains\Jobs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\JobPostAiContent;
use App\Models\Category;
use App\Models\State;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Cache TTL for sitemap XML (10 minutes).
     */
    protected int $cacheTtl = 600;

    /**
     * Sitemap Index — master file pointing to all child sitemaps.
     * Google, Bing, and Yandex crawl this first.
     *
     * Endpoint: GET /sitemap.xml
     */
    public function index()
    {
        $xml = Cache::remember('sitemap_index_xml', $this->cacheTtl, function () {
            $baseUrl = config('app.url');
            $lastmod = JobPost::published()
                ->orderByDesc('updated_at')
                ->value('updated_at');

            $lastmod = $lastmod ? Carbon::parse($lastmod)->toAtomString() : now()->toAtomString();

            return view('seo.sitemap-index', compact('baseUrl', 'lastmod'))->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * Pages Sitemap — static pages, landing pages, category/state/district URLs.
     *
     * Endpoint: GET /sitemaps/sitemap-pages.xml
     */
    public function pages()
    {
        $xml = Cache::remember('sitemap_pages_xml', $this->cacheTtl, function () {
            $categories = Category::where('is_active', true)->whereNotNull('slug')->get();
            $states = State::with('districts')->whereNotNull('slug')->get();

            return view('seo.sitemap-pages', compact('categories', 'states'))->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * Jobs Sitemap — all published job detail pages with <lastmod>.
     *
     * Endpoint: GET /sitemaps/sitemap-jobs.xml
     */
    public function jobs()
    {
        $xml = Cache::remember('sitemap_jobs_xml', $this->cacheTtl, function () {
            $jobs = JobPost::published()
                ->select('id', 'slug', 'post_type', 'published_at', 'updated_at')
                ->orderByDesc('id')
                ->get();

            return view('seo.sitemap-jobs', compact('jobs'))->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * Image Sitemap — pages with OG images, notification PDFs, and AI-generated thumbnails.
     * Follows Google Image Sitemap Extension spec.
     *
     * Endpoint: GET /sitemaps/sitemap-images.xml
     */
    public function images()
    {
        $xml = Cache::remember('sitemap_images_xml', $this->cacheTtl, function () {
            $jobs = JobPost::published()
                ->select('id', 'slug', 'post_type', 'title', 'notification_pdf_path', 'published_at')
                ->orderByDesc('id')
                ->get();

            $baseUrl = config('app.url');
            $defaultImage = $baseUrl . '/assets/images/icons/pwa-icon-512.png';

            return view('seo.sitemap-images', compact('jobs', 'baseUrl', 'defaultImage'))->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * Video Sitemap — pages with embedded video content.
     * Future-ready skeleton; auto-discovers videos when added to job posts.
     *
     * Endpoint: GET /sitemaps/sitemap-videos.xml
     */
    public function videos()
    {
        $xml = Cache::remember('sitemap_videos_xml', $this->cacheTtl, function () {
            // Future: query jobs with video_url or embedded YouTube links in description
            $jobs = JobPost::published()
                ->where(function ($q) {
                    $q->where('description', 'like', '%youtube.com%')
                      ->orWhere('description', 'like', '%youtu.be%')
                      ->orWhere('description', 'like', '%vimeo.com%');
                })
                ->select('id', 'slug', 'post_type', 'title', 'description', 'published_at')
                ->orderByDesc('id')
                ->limit(500)
                ->get();

            $baseUrl = config('app.url');

            return view('seo.sitemap-videos', compact('jobs', 'baseUrl'))->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * FAQ Sitemap — URLs of all pages containing AI-generated FAQs.
     * Helps Google discover FAQ-rich pages for rich snippet eligibility.
     *
     * Endpoint: GET /sitemaps/sitemap-faqs.xml
     */
    public function faqs()
    {
        $xml = Cache::remember('sitemap_faqs_xml', $this->cacheTtl, function () {
            $aiContents = JobPostAiContent::where('status', 'approved')
                ->whereNotNull('faqs')
                ->with(['jobPost' => function ($q) {
                    $q->published()->select('id', 'slug', 'post_type', 'updated_at');
                }])
                ->get()
                ->filter(fn($ai) => $ai->jobPost !== null && !empty($ai->faqs));

            return view('seo.sitemap-faqs', compact('aiContents'))->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * Google News Sitemap — jobs published within last 48 hours.
     * Compliant with Google News Sitemap protocol.
     *
     * Endpoint: GET /news-sitemap.xml
     */
    public function news()
    {
        $xml = Cache::remember('news_sitemap_xml', $this->cacheTtl, function () {
            $jobs = JobPost::published()
                ->with(['tags', 'category', 'state'])
                ->where('published_at', '>=', Carbon::now()->subHours(48))
                ->orderByDesc('published_at')
                ->get();

            return view('seo.news_sitemap', compact('jobs'))->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * IndexNow API key verification endpoint.
     * Serves the API key as a text file at /{key}.txt
     */
    public function indexNowKey(string $key)
    {
        $configuredKey = config('services.indexnow.api_key');

        if (!$configuredKey || $key !== $configuredKey) {
            abort(404);
        }

        return Response::make($configuredKey, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Helper to generate the detail URL for a job post based on post_type.
     */
    public static function getDetailRoute(JobPost $job): string
    {
        return match ($job->post_type) {
            'result'        => route('seo.result_detail', ['slug' => $job->slug]),
            'admit_card'    => route('seo.admit_card_detail', ['slug' => $job->slug]),
            'answer_key'    => route('seo.answer_key_detail', ['slug' => $job->slug]),
            'syllabus'      => route('seo.syllabus_detail', ['slug' => $job->slug]),
            'cutoff'        => route('seo.cutoff_detail', ['slug' => $job->slug]),
            'exam_calendar' => route('seo.exam_calendar_detail', ['slug' => $job->slug]),
            'prev_paper'    => route('seo.prev_paper_detail', ['slug' => $job->slug]),
            default         => route('seo.job_detail', ['slug' => $job->slug]),
        };
    }

    /**
     * Return an XML response with correct headers.
     */
    protected function xmlResponse(string $xml): \Illuminate\Http\Response
    {
        return response($xml, 200)->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
