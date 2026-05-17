<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Models\State;
use App\Models\Category;
use App\Models\Qualification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    protected JobRepositoryInterface $jobRepo;

    public function __construct(JobRepositoryInterface $jobRepo)
    {
        $this->jobRepo = $jobRepo;
    }

    /**
     * Handle the landing page view and dynamic AJAX filter queries
     */
    public function index(Request $request)
    {
        // 1. Check if the request is a dynamic jQuery AJAX call
        if ($request->ajax()) {
            return $this->handleAjaxFilters($request);
        }

        // 2. Fetch master lists to seed dropdown selectors
        $states = State::all();
        $categories = Category::where('is_active', true)->get();
        $qualifications = Qualification::all();
        $departments = \App\Models\Department::all();
        
        // 3. Fetch initial featured and recent job posts
        $featuredJobs = $this->jobRepo->getFeatured(4);
        $recentJobs = $this->jobRepo->getRecent(5);

        return view('home', compact('states', 'categories', 'qualifications', 'departments', 'featuredJobs', 'recentJobs'));
    }

    /**
     * Process dynamic AJAX filtering requests and return a JSON payload
     */
    protected function handleAjaxFilters(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'state_id',
            'category_id',
            'qualification_id',
            'min_salary',
            'has_no_fee'
        ]);

        // Convert fee checkbox filter to boolean
        if (isset($filters['has_no_fee'])) {
            $filters['has_no_fee'] = filter_var($filters['has_no_fee'], FILTER_VALIDATE_BOOLEAN);
        }

        // Pull paginated matching posts from the Repository layer
        $jobs = $this->jobRepo->getPaginatedFiltered($filters, 6);

        // Map and structure return fields precisely
        $formattedJobs = collect($jobs->items())->map(function($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'category' => $job->category->name ?? 'Gov Job',
                'department' => $job->department->name ?? 'Government',
                'state' => $job->state->name ?? 'Pan India',
                'qualification' => $job->qualification->name ?? 'Graduate',
                'vacancy_count' => $job->vacancy_count,
                'salary_min' => number_format($job->salary_min, 0),
                'salary_max' => number_format($job->salary_max, 0),
                'application_fee' => number_format($job->application_fee, 2),
                'last_date' => $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A',
                'is_featured' => $job->is_featured
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'jobs' => $formattedJobs,
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'total' => $jobs->total()
            ]
        ]);
    }

    /**
     * Fetch detailed job criteria for AJAX modals or detailed pages
     */
    public function show(string $slug): JsonResponse
    {
        $job = $this->jobRepo->findBySlug($slug);
        
        if (!$job) {
            return response()->json([
                'status' => 'error',
                'message' => 'Requested job announcement not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $job->id,
                'title' => $job->title,
                'category' => $job->category->name ?? 'Gov Job',
                'department' => $job->department->name ?? 'Government',
                'state' => $job->state->name ?? 'Pan India',
                'qualification' => $job->qualification->name ?? 'Graduate',
                'vacancy_count' => $job->vacancy_count,
                'salary_min' => number_format($job->salary_min, 0),
                'salary_max' => number_format($job->salary_max, 0),
                'application_fee' => number_format($job->application_fee, 2),
                'age_limit' => $job->age_limit ?? '18-32 Years',
                'last_date' => $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A',
                'exam_date' => $job->exam_date ? $job->exam_date->format('d M Y') : 'Announced Soon',
                'official_website_link' => $job->official_website_link,
                'apply_link' => $job->apply_link,
                'description' => $job->description,
                'exam_pattern' => $job->exam_pattern ?? 'Objective MCQs.',
                'selection_process' => $job->selection_process ?? 'Written Exam.'
            ]
        ]);
    }
}
