<?php

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Requests\SeoSettingsRequest;
use App\Domains\Admin\Services\Contracts\AdminServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AdminDashboardController — analytics, SEO settings, activity logs.
 * Extracted from the fat AdminController. Authorization via EnsureAdmin middleware.
 */
class AdminDashboardController extends Controller
{
    public function __construct(protected AdminServiceInterface $adminService) {}

    public function dashboardView(): mixed
    {
        return view('admin.index', [
            'categories'     => Category::orderBy('name')->get(),
            'departments'    => Department::orderBy('name')->get(),
            'qualifications' => Qualification::orderBy('id')->get(),
            'states'         => State::orderBy('name')->get(),
            'seo'            => $this->adminService->getSeoSettings(),
        ]);
    }

    public function getAdminData(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $this->adminService->getDashboardData()]);
    }

    public function getActivityLogs(Request $request): JsonResponse
    {
        $data = $this->adminService->getActivityLogs($request->integer('per_page', 10));
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function updateSeoSettings(SeoSettingsRequest $request): JsonResponse
    {
        $this->adminService->updateSeoSettings($request->validated());
        return response()->json(['status' => 'success', 'message' => 'SEO Meta tags synchronized successfully!']);
    }

    public function getAnalyticsData(Request $request, \App\Services\AnalyticsService $analyticsService): JsonResponse
    {
        $days = $request->integer('days', 14);
        $data = $analyticsService->getDashboardAnalytics($days);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
