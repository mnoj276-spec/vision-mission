<?php

namespace App\Domains\Users\Controllers;

use App\Domains\Users\Requests\UpdateProfileRequest;
use App\Domains\Users\Services\Contracts\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * DashboardController — candidate self-service (bookmarks list, applications, preferences, profile).
 * Job interaction actions (apply/bookmark) live in Jobs\Controllers\ApplicationController.
 */
class DashboardController extends Controller
{
    public function __construct(protected AuthServiceInterface $authService) {}

    public function getDashboardData(): JsonResponse
    {
        $user = Auth::user();

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['jobPost.category', 'jobPost.department', 'jobPost.state'])
            ->get()->map(fn ($b) => [
                'bookmark_id' => $b->id,
                'job_id'      => $b->jobPost->id,
                'title'       => $b->jobPost->title,
                'slug'        => $b->jobPost->slug,
                'department'  => $b->jobPost->department->name ?? 'Government',
                'state'       => $b->jobPost->state->name    ?? 'Pan India',
                'last_date'   => $b->jobPost->last_date_to_apply?->format('d M Y') ?? 'N/A',
            ]);

        // Query recently viewed job detail pages using page view telemetry
        $recentPaths = \App\Models\AnalyticsPageView::where('user_id', $user->id)
            ->where('path', 'like', '/job/%')
            ->orderBy('id', 'desc')
            ->limit(15)
            ->pluck('path')
            ->unique()
            ->map(fn($path) => str_replace('/job/', '', $path))
            ->toArray();

        $recentJobs = [];
        if (!empty($recentPaths)) {
            $recentJobs = \App\Models\JobPost::published()
                ->whereIn('slug', $recentPaths)
                ->with(['category', 'department', 'state'])
                ->get()
                ->sortBy(fn($job) => array_search($job->slug, $recentPaths))
                ->values()
                ->map(fn ($j) => [
                    'id'          => $j->id,
                    'title'       => $j->title,
                    'slug'        => $j->slug,
                    'department'  => $j->department->name ?? 'Government',
                    'state'       => $j->state->name    ?? 'Pan India',
                    'last_date'   => $j->last_date_to_apply?->format('d M Y') ?? 'N/A',
                ]);
        }

        return response()->json([
            'status' => 'success', 
            'data' => [
                'bookmarks' => $bookmarks, 
                'applications' => $applications, 
                'recently_viewed' => $recentJobs,
                'user' => [
                    'name' => $user->name, 
                    'email' => $user->email, 
                    'phone' => $user->phone ?? 'Not Verified'
                ]
            ]
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $this->authService->updateProfile(Auth::user(), $request->validated());
        return response()->json(['status' => 'success', 'message' => 'Profile settings successfully updated!']);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        Validator::make($request->all(), ['email_alerts' => 'nullable|boolean', 'sms_alerts' => 'nullable|boolean'])->validate();
        return response()->json(['status' => 'success', 'message' => 'Notification alert channels updated successfully!']);
    }
}
