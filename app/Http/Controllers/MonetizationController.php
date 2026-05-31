<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\User;
use App\Models\AnalyticsRevenueEvent;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonetizationController extends Controller
{
    /**
     * Cloaked Affiliate Link Redirection Gateway.
     * Masked under "/go/{slug}". Registers SEO headers and tracks clicks.
     */
    public function redirectAffiliate(string $slug)
    {
        $jobPost = JobPost::where('slug', $slug)->firstOrFail();
        
        $targetUrl = $jobPost->affiliate_link ?: $jobPost->apply_link ?: $jobPost->official_website_link;

        if (empty($targetUrl)) {
            abort(404, 'No redirection target defined for this listing.');
        }

        // Track affiliate link click as a CPC revenue event (estimated ₹5.00 value per click)
        try {
            app(AnalyticsService::class)->trackRevenueEvent('ad_click', 'affiliate_link', 5.0000, $jobPost->id);
        } catch (\Exception $e) {
            // Failsafe
        }

        // SEO Safe Redirection: Return 302 Found with strict crawl block headers
        return redirect()->away($targetUrl, 302, [
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Candidate Membership Upgrade API.
     * Upgrades membership plan to trigger ad suppression.
     */
    public function upgradeMembership(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => 'required|string|in:free,premium,pro',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        // Update the plan column
        $user->update([
            'membership_plan' => $request->plan,
        ]);

        $planLabel = ucfirst($request->plan);

        return response()->json([
            'success' => true,
            'message' => "Successfully upgraded to {$planLabel} Membership! Enjoy your Ad-Free premium experience.",
            'data' => [
                'name' => $user->name,
                'membership_plan' => $user->membership_plan,
            ]
        ]);
    }

    /**
     * Consolidated Admin Revenue Analytics.
     * Aggregates CPM/CPC streams, memberships, sponsorships, and leaderboard CTRs.
     */
    public function getRevenueAnalytics(Request $request): JsonResponse
    {
        // Restrict to admins
        if (!auth()->check() || !\Illuminate\Support\Facades\Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Administrative access required.',
            ], 403);
        }

        $days = $request->query('days', 14);
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        // 1. CPM / CPC Ad revenue from AnalyticsRevenueEvent
        $adClicksRevenue = AnalyticsRevenueEvent::where('event_type', 'ad_click')
            ->where('slot_name', '!=', 'affiliate_link')
            ->where('created_at', '>=', $startDate)
            ->sum('estimated_revenue');

        $adImpressionsRevenue = AnalyticsRevenueEvent::where('event_type', 'ad_impression')
            ->where('created_at', '>=', $startDate)
            ->sum('estimated_revenue');

        // 2. Affiliate revenue from AnalyticsRevenueEvent (affiliate_link CPC events)
        $affiliateRevenue = AnalyticsRevenueEvent::where('slot_name', 'affiliate_link')
            ->where('created_at', '>=', $startDate)
            ->sum('estimated_revenue');

        // 3. Sponsored listings count (estimated at ₹5,000 flat setup fee per sponsored job)
        $sponsoredJobsCount = JobPost::sponsored()
            ->where('created_at', '>=', $startDate)
            ->count();
        $sponsorshipRevenue = $sponsoredJobsCount * 5000.00;

        // 4. Membership plans (Premium candidates upgraded @ ₹299 each, Pro @ ₹599 each)
        $premiumUsersCount = User::where('membership_plan', 'premium')->count();
        $proUsersCount = User::where('membership_plan', 'pro')->count();
        
        $membershipRevenue = ($premiumUsersCount * 299.00) + ($proUsersCount * 599.00);

        // Grand Total Estimated Income
        $totalEstimatedRevenue = $adClicksRevenue + $adImpressionsRevenue + $affiliateRevenue + $sponsorshipRevenue + $membershipRevenue;

        // 5. Build Daily Monetization Streams Chart Data
        $dailyStreams = [];
        for ($i = 0; $i < $days; $i++) {
            $day = Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d');
            $dailyStreams[$day] = [
                'date' => Carbon::now()->subDays($days - 1 - $i)->format('d M'),
                'ads' => 0.00,
                'affiliate' => 0.00,
                'subscriptions' => 0.00,
            ];
        }

        // Aggregate daily ad impressions/clicks
        $adDaily = AnalyticsRevenueEvent::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw("SUM(estimated_revenue) as rev")
        )
        ->where('slot_name', '!=', 'affiliate_link')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->get();

        foreach ($adDaily as $stat) {
            if (isset($dailyStreams[$stat->date])) {
                $dailyStreams[$stat->date]['ads'] = round((float)$stat->rev, 2);
            }
        }

        // Aggregate daily affiliate clicks
        $affDaily = AnalyticsRevenueEvent::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw("SUM(estimated_revenue) as rev")
        )
        ->where('slot_name', 'affiliate_link')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->get();

        foreach ($affDaily as $stat) {
            if (isset($dailyStreams[$stat->date])) {
                $dailyStreams[$stat->date]['affiliate'] = round((float)$stat->rev, 2);
            }
        }

        // 6. Top Affiliate Earners (Job listings producing most affiliate click revenues)
        $topAffiliatePerformers = AnalyticsRevenueEvent::select(
            'job_post_id',
            DB::raw('count(*) as click_count'),
            DB::raw('sum(estimated_revenue) as total_earned')
        )
        ->where('slot_name', 'affiliate_link')
        ->where('created_at', '>=', $startDate)
        ->groupBy('job_post_id')
        ->orderBy('total_earned', 'desc')
        ->limit(5)
        ->get();

        $jobIds = $topAffiliatePerformers->pluck('job_post_id');
        $jobsMap = JobPost::whereIn('id', $jobIds)->pluck('title', 'id');

        $affiliateLeaderboard = $topAffiliatePerformers->map(function ($item) use ($jobsMap) {
            return [
                'title' => $jobsMap[$item->job_post_id] ?? 'Recruitment #' . $item->job_post_id,
                'clicks' => $item->click_count,
                'earnings' => round((float)$item->total_earned, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'kpis' => [
                    'ads_cpc' => round($adClicksRevenue, 2),
                    'ads_cpm' => round($adImpressionsRevenue, 2),
                    'affiliate' => round($affiliateRevenue, 2),
                    'sponsorship' => round($sponsorshipRevenue, 2),
                    'subscriptions' => round($membershipRevenue, 2),
                    'total_revenue' => round($totalEstimatedRevenue, 2),
                ],
                'counts' => [
                    'sponsored_jobs' => $sponsoredJobsCount,
                    'premium_subscribers' => $premiumUsersCount,
                    'pro_subscribers' => $proUsersCount,
                ],
                'charts' => [
                    'streams' => array_values($dailyStreams),
                ],
                'leaderboard' => $affiliateLeaderboard,
            ]
        ]);
    }
}
