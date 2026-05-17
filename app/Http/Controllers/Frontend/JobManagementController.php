<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\AuditLog;
use App\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class JobManagementController extends Controller
{
    protected JobRepositoryInterface $jobRepo;

    public function __construct(JobRepositoryInterface $jobRepo)
    {
        $this->jobRepo = $jobRepo;
    }

    protected function checkAdmin(): ?JsonResponse
    {
        if (Gate::denies('admin-access')) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden Access.'], 403);
        }
        return null;
    }

    protected function logAction(Request $request, string $action, string $details): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'N/A',
            'action' => $action,
            'details' => $details
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;

        $query = JobPost::with(['category', 'department', 'state', 'qualification'])
            ->orderBy('id', 'desc');

        // Apply live keyword search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply pagination
        $perPage = $request->integer('per_page', 10);
        $jobs = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'jobs' => $jobs->items(),
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'total' => $jobs->total()
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;

        $request->validate([
            'title' => 'required|string|min:10|max:255',
            'category_id' => 'required|exists:categories,id',
            'department_id' => 'required|exists:departments,id',
            'state_id' => 'required|exists:states,id',
            'qualification_id' => 'required|exists:qualifications,id',
            'description' => 'required|string|min:20',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0',
            'vacancy_count' => 'required|integer|min:1',
            'application_fee' => 'required|numeric|min:0',
            'last_date_to_apply' => 'required|date|after_or_equal:today',
            'official_website_link' => 'required|url',
        ]);

        $jobData = [
            'category_id' => $request->category_id,
            'department_id' => $request->department_id,
            'state_id' => $request->state_id,
            'qualification_id' => $request->qualification_id,
            'title' => $request->title,
            'slug' => str()->slug($request->title) . '-' . rand(100, 999),
            'description' => $request->description,
            'age_limit' => '18 - 35 Years',
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'vacancy_count' => $request->vacancy_count,
            'application_fee' => $request->application_fee,
            'official_website_link' => $request->official_website_link,
            'apply_link' => $request->official_website_link,
            'last_date_to_apply' => $request->last_date_to_apply,
            'status' => 'published',
            'published_at' => Carbon::now()
        ];

        $jobPost = $this->jobRepo->create($jobData);

        $this->logAction($request, 'Create Job Posting', "Created job '{$jobPost->title}' (ID: {$jobPost->id})");
        return response()->json(['status' => 'success', 'message' => 'Manual job announcement published successfully!', 'data' => $jobPost]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;

        $jobPost = JobPost::findOrFail($id);

        $request->validate([
            'title' => 'required|string|min:10|max:255',
            'category_id' => 'required|exists:categories,id',
            'department_id' => 'required|exists:departments,id',
            'state_id' => 'required|exists:states,id',
            'qualification_id' => 'required|exists:qualifications,id',
            'description' => 'required|string|min:20',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0',
            'vacancy_count' => 'required|integer|min:1',
            'application_fee' => 'required|numeric|min:0',
            'last_date_to_apply' => 'required|date',
            'official_website_link' => 'required|url',
        ]);

        $jobPost->update([
            'category_id' => $request->category_id,
            'department_id' => $request->department_id,
            'state_id' => $request->state_id,
            'qualification_id' => $request->qualification_id,
            'title' => $request->title,
            'slug' => str()->slug($request->title) . '-' . rand(100, 999),
            'description' => $request->description,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'vacancy_count' => $request->vacancy_count,
            'application_fee' => $request->application_fee,
            'official_website_link' => $request->official_website_link,
            'apply_link' => $request->official_website_link,
            'last_date_to_apply' => $request->last_date_to_apply,
        ]);

        $this->logAction($request, 'Update Job Posting', "Updated job '{$jobPost->title}' (ID: {$jobPost->id})");
        return response()->json(['status' => 'success', 'message' => 'Job announcement updated successfully!', 'data' => $jobPost]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;

        $jobPost = JobPost::findOrFail($id);
        $title = $jobPost->title;
        
        $jobPost->delete();

        $this->logAction($request, 'Delete Job Posting', "Deleted job '{$title}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Job posting deleted successfully!']);
    }
}
