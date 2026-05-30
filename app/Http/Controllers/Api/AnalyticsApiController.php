<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsApiController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    /**
     * Public API endpoint to track page views and simple navigation.
     */
    public function trackPageView(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string|max:255',
            'referer' => 'nullable|string|max:500',
        ]);

        $this->analyticsService->trackPageView($request->path, $request->referer);

        return response()->json([
            'status' => 'success',
            'message' => 'Page view recorded successfully.'
        ]);
    }

    /**
     * Public API endpoint to track job interactions (views, apply clicks, bookmarks).
     */
    public function trackJobInteraction(Request $request): JsonResponse
    {
        $request->validate([
            'job_post_id' => 'required|integer|exists:job_posts,id',
            'event_type' => 'required|string|in:view,apply_click,bookmark,apply_submit',
        ]);

        $this->analyticsService->trackJobEvent($request->job_post_id, $request->event_type);

        return response()->json([
            'status' => 'success',
            'message' => 'Job interaction event recorded.'
        ]);
    }

    /**
     * Public API endpoint to track ad monetization events.
     */
    public function trackAdEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string|in:ad_impression,ad_click',
            'slot_name' => 'required|string|max:100',
            'job_post_id' => 'nullable|integer|exists:job_posts,id',
        ]);

        // Architect grade ad monetization constants
        $estimatedRevenue = 0.0000;
        if ($request->event_type === 'ad_click') {
            $estimatedRevenue = 0.0800; // $0.08 CPC
        } elseif ($request->event_type === 'ad_impression') {
            $estimatedRevenue = 0.0025; // $2.50 CPM / 1000 = $0.0025 per view
        }

        $this->analyticsService->trackRevenueEvent(
            $request->event_type,
            $request->slot_name,
            $estimatedRevenue,
            $request->job_post_id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Ad monetization event recorded.',
            'estimated_revenue' => $estimatedRevenue
        ]);
    }
}
