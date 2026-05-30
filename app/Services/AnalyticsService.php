<?php

namespace App\Services;

use App\Models\AnalyticsPageView;
use App\Models\AnalyticsJobEvent;
use App\Models\AnalyticsSearchQuery;
use App\Models\AnalyticsRevenueEvent;
use App\Models\JobPost;
use App\Models\JobAlert;
use App\Models\Bookmark;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Track a standard page view event.
     */
    public function trackPageView(string $path, ?string $referer = null): void
    {
        $userAgent = request()->header('User-Agent', '');
        $isBot = preg_match('/bot|crawl|spider|slurp|tracker/i', $userAgent) ? 1 : 0;
        
        $isOrganic = 0;
        if ($referer) {
            $parsedUrl = parse_url($referer);
            if (isset($parsedUrl['host'])) {
                $host = strtolower($parsedUrl['host']);
                if (str_contains($host, 'google') || str_contains($host, 'bing') || str_contains($host, 'yahoo') || str_contains($host, 'duckduckgo')) {
                    $isOrganic = 1;
                }
            }
        }

        AnalyticsPageView::create([
            'session_id' => session()->getId() ?: 'api-session',
            'user_id' => auth()->id(),
            'path' => $path,
            'referer' => $referer,
            'ip_address' => request()->ip() ?: '127.0.0.1',
            'user_agent' => $userAgent,
            'is_bot' => $isBot,
            'is_organic' => $isOrganic,
            'created_at' => now(),
        ]);
    }

    /**
     * Track a job interaction event.
     */
    public function trackJobEvent(int $jobPostId, string $eventType): void
    {
        AnalyticsJobEvent::create([
            'job_post_id' => $jobPostId,
            'event_type' => $eventType,
            'session_id' => session()->getId() ?: 'api-session',
            'user_id' => auth()->id(),
            'ip_address' => request()->ip() ?: '127.0.0.1',
            'created_at' => now(),
        ]);
    }

    /**
     * Track a search query event.
     */
    public function trackSearchQuery(string $query, ?array $filters = null, int $resultsCount = 0): void
    {
        if (empty($query) && empty($filters)) {
            return;
        }

        AnalyticsSearchQuery::create([
            'query' => $query ?: '',
            'filters' => $filters,
            'results_count' => $resultsCount,
            'session_id' => session()->getId() ?: 'api-session',
            'user_id' => auth()->id(),
            'ip_address' => request()->ip() ?: '127.0.0.1',
            'created_at' => now(),
        ]);
    }

    /**
     * Track an ad impression or ad click revenue event.
     */
    public function trackRevenueEvent(string $eventType, string $slotName, float $estimatedRevenue, ?int $jobPostId = null): void
    {
        AnalyticsRevenueEvent::create([
            'event_type' => $eventType,
            'slot_name' => $slotName,
            'estimated_revenue' => $estimatedRevenue,
            'job_post_id' => $jobPostId,
            'session_id' => session()->getId() ?: 'api-session',
            'ip_address' => request()->ip() ?: '127.0.0.1',
            'created_at' => now(),
        ]);
    }

    /**
     * Retrieve aggregated telemetry data for the admin console dashboard charts.
     */
    public function getDashboardAnalytics(?int $days = 14): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        // 1. KPI Metric Summary Cards
        $totalJobViews = AnalyticsJobEvent::where('event_type', 'view')
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalApplyClicks = AnalyticsJobEvent::where('event_type', 'apply_click')
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalBookmarks = AnalyticsJobEvent::where('event_type', 'bookmark')
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalSubmissions = AnalyticsJobEvent::where('event_type', 'apply_submit')
            ->where('created_at', '>=', $startDate)
            ->count();

        // CTR = Total apply clicks & bookmarks / job views (with fallback safety)
        $ctr = $totalJobViews > 0 ? round((($totalApplyClicks + $totalBookmarks) / $totalJobViews) * 100, 2) : 0;

        $totalSearches = AnalyticsSearchQuery::where('created_at', '>=', $startDate)->count();
        $totalRevenue = AnalyticsRevenueEvent::where('created_at', '>=', $startDate)->sum('estimated_revenue');

        // Conversion Rates
        $totalAlertSubscribers = JobAlert::where('created_at', '>=', $startDate)->count();

        // 2. Daily Traffic Chart (Organic vs Bots vs Direct/Other Views)
        $dailyTraffic = [];
        for ($i = 0; $i < $days; $i++) {
            $day = now()->subDays($days - 1 - $i)->format('Y-m-d');
            $dailyTraffic[$day] = [
                'date' => now()->subDays($days - 1 - $i)->format('d M'),
                'organic' => 0,
                'direct' => 0,
                'bots' => 0,
            ];
        }

        $pageViewStats = AnalyticsPageView::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(CASE WHEN is_bot = 1 THEN 1 ELSE 0 END) as bots_count'),
            DB::raw('SUM(CASE WHEN is_organic = 1 AND is_bot = 0 THEN 1 ELSE 0 END) as organic_count'),
            DB::raw('SUM(CASE WHEN is_organic = 0 AND is_bot = 0 THEN 1 ELSE 0 END) as direct_count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->get();

        foreach ($pageViewStats as $stat) {
            $dateStr = $stat->date;
            if (isset($dailyTraffic[$dateStr])) {
                $dailyTraffic[$dateStr]['bots'] = (int) $stat->bots_count;
                $dailyTraffic[$dateStr]['organic'] = (int) $stat->organic_count;
                $dailyTraffic[$dateStr]['direct'] = (int) $stat->direct_count;
            }
        }

        // 3. Daily Revenue Chart (CPC vs CPM Streams)
        $dailyRevenue = [];
        for ($i = 0; $i < $days; $i++) {
            $day = now()->subDays($days - 1 - $i)->format('Y-m-d');
            $dailyRevenue[$day] = [
                'date' => now()->subDays($days - 1 - $i)->format('d M'),
                'cpc' => 0.0000,
                'cpm' => 0.0000,
            ];
        }

        $revenueStats = AnalyticsRevenueEvent::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw("SUM(CASE WHEN event_type = 'ad_click' THEN estimated_revenue ELSE 0 END) as cpc_revenue"),
            DB::raw("SUM(CASE WHEN event_type = 'ad_impression' THEN estimated_revenue ELSE 0 END) as cpm_revenue")
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->get();

        foreach ($revenueStats as $stat) {
            $dateStr = $stat->date;
            if (isset($dailyRevenue[$dateStr])) {
                $dailyRevenue[$dateStr]['cpc'] = round((float) $stat->cpc_revenue, 4);
                $dailyRevenue[$dateStr]['cpm'] = round((float) $stat->cpm_revenue, 4);
            }
        }

        // 4. Funnel Metrics
        $funnel = [
            'views' => $totalJobViews,
            'bookmarks' => $totalBookmarks,
            'clicks' => $totalApplyClicks,
            'submissions' => $totalSubmissions,
        ];

        // 5. Top 10 Search Queries
        $topQueries = AnalyticsSearchQuery::select('query', DB::raw('count(*) as frequency'), DB::raw('avg(results_count) as avg_results'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('query')
            ->orderBy('frequency', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($q) => [
                'query' => $q->query ?: '(empty filter search)',
                'frequency' => $q->frequency,
                'avg_results' => round($q->avg_results, 0)
            ]);

        // 6. Job-level CTR Performance Leaderboard
        $jobPerformance = AnalyticsJobEvent::select(
            'job_post_id',
            DB::raw("SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views_count"),
            DB::raw("SUM(CASE WHEN event_type = 'apply_click' THEN 1 ELSE 0 END) as clicks_count"),
            DB::raw("SUM(CASE WHEN event_type = 'bookmark' THEN 1 ELSE 0 END) as bookmarks_count")
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('job_post_id')
        ->orderBy('views_count', 'desc')
        ->limit(10)
        ->get();

        $jobIds = $jobPerformance->pluck('job_post_id');
        $jobsMap = JobPost::whereIn('id', $jobIds)->pluck('title', 'id');

        $leaderboard = $jobPerformance->map(function ($item) use ($jobsMap) {
            $views = (int) $item->views_count;
            $clicks = (int) $item->clicks_count;
            $bookmarks = (int) $item->bookmarks_count;
            $jobCtr = $views > 0 ? round((($clicks + $bookmarks) / $views) * 100, 1) : 0;

            return [
                'title' => $jobsMap[$item->job_post_id] ?? 'Deleted Recruitment #' . $item->job_post_id,
                'views' => $views,
                'clicks' => $clicks,
                'bookmarks' => $bookmarks,
                'ctr' => $jobCtr
            ];
        });

        // 7. Top User Journey Pathways (Frequent sequences)
        $topJourneys = $this->getTopJourneys($startDate);

        return [
            'kpis' => [
                'job_views' => $totalJobViews,
                'overall_ctr' => $ctr,
                'search_queries' => $totalSearches,
                'estimated_revenue' => round($totalRevenue, 2),
                'alert_subscribers' => $totalAlertSubscribers,
                'bookmarks_created' => $totalBookmarks,
                'applications_submitted' => $totalSubmissions,
            ],
            'charts' => [
                'traffic' => array_values($dailyTraffic),
                'revenue' => array_values($dailyRevenue),
                'funnel' => $funnel,
            ],
            'top_queries' => $topQueries,
            'job_performance' => $leaderboard,
            'user_journeys' => $topJourneys,
        ];
    }

    /**
     * Compute most common user navigation paths (User Journeys)
     */
    protected function getTopJourneys(Carbon $startDate): array
    {
        // We aggregate page path transitions grouped by session ID, ordered by timestamp
        $views = AnalyticsPageView::select('session_id', 'path', 'created_at')
            ->where('created_at', '>=', $startDate)
            ->where('is_bot', 0)
            ->orderBy('session_id')
            ->orderBy('created_at')
            ->get();

        $journeysBySession = [];
        foreach ($views as $view) {
            $journeysBySession[$view->session_id][] = $view->path;
        }

        $journeyCounts = [];
        foreach ($journeysBySession as $sessionId => $paths) {
            // Dedup consecutive repeats e.g. [/home, /home, /search] -> [/home, /search]
            $uniquePaths = [];
            $lastPath = null;
            foreach ($paths as $path) {
                if ($path !== $lastPath) {
                    $uniquePaths[] = $path;
                    $lastPath = $path;
                }
            }

            if (count($uniquePaths) > 1) {
                $pathStr = implode(' → ', array_slice($uniquePaths, 0, 4)); // Limit to first 4 steps
                $journeyCounts[$pathStr] = ($journeyCounts[$pathStr] ?? 0) + 1;
            }
        }

        arsort($journeyCounts);
        $topJourneys = [];
        $limit = 5;
        foreach (array_slice($journeyCounts, 0, $limit, true) as $pathStr => $count) {
            $topJourneys[] = [
                'path' => $pathStr,
                'count' => $count
            ];
        }

        return $topJourneys;
    }
}
