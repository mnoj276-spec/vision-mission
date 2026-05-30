<?php

namespace App\Domains\Jobs\Controllers;

use App\Domains\Admin\Services\Contracts\AdminServiceInterface;
use App\Domains\Jobs\Requests\StoreJobRequest;
use App\Domains\Jobs\Requests\UpdateJobRequest;
use App\Domains\Jobs\Services\Contracts\JobServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AdminJobController — merges JobManagementController + AdminController::storeJob.
 * Authorization handled by EnsureAdmin middleware on the route group.
 * Business logic delegated to JobService. Audit logging via AdminService.
 */
class AdminJobController extends Controller
{
    public function __construct(
        protected JobServiceInterface   $jobService,
        protected AdminServiceInterface $adminService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = JobPost::with(['category', 'department', 'state', 'qualification'])->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }

        $jobs = $query->paginate($request->integer('per_page', 10));
        return response()->json(['status' => 'success', 'data' => ['jobs' => $jobs->items(), 'current_page' => $jobs->currentPage(), 'last_page' => $jobs->lastPage(), 'total' => $jobs->total()]]);
    }

    public function store(StoreJobRequest $request): JsonResponse
    {
        $jobPost = $this->jobService->createJob($request->validated());
        $this->adminService->logAction(Auth::id(), $request->ip(), $request->userAgent() ?? 'N/A', 'Create Job Posting', "Created job '{$jobPost->title}' (ID: {$jobPost->id})");
        return response()->json(['status' => 'success', 'message' => 'Manual job announcement published successfully!', 'job' => $jobPost]);
    }

    public function update(UpdateJobRequest $request, int $id): JsonResponse
    {
        $jobPost = $this->jobService->updateJob($id, $request->validated());
        $this->adminService->logAction(Auth::id(), $request->ip(), $request->userAgent() ?? 'N/A', 'Update Job Posting', "Updated job '{$jobPost->title}' (ID: {$jobPost->id})");
        return response()->json(['status' => 'success', 'message' => 'Job announcement updated successfully!', 'data' => $jobPost]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $jobPost = JobPost::findOrFail($id);
        $title   = $jobPost->title;
        $this->jobService->deleteJob($id);
        $this->adminService->logAction(Auth::id(), $request->ip(), $request->userAgent() ?? 'N/A', 'Delete Job Posting', "Deleted job '{$title}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Job posting deleted successfully!']);
    }
}
