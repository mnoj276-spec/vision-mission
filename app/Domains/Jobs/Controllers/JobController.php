<?php

namespace App\Domains\Jobs\Controllers;

use App\Domains\Jobs\Services\Contracts\JobServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\Category;
use App\Models\Qualification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JobController — public-facing job browsing.
 * Was HomeController. Thin HTTP adapter only.
 */
class JobController extends Controller
{
    public function __construct(protected JobServiceInterface $jobService) {}

    public function index(Request $request): mixed
    {
        if ($request->ajax()) {
            return $this->handleAjaxFilters($request);
        }

        $data = $this->jobService->getHomePageData();
        return view('home', $data);
    }

    protected function handleAjaxFilters(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'state_id', 'category_id', 'qualification_id', 'min_salary', 'has_no_fee']);
        $jobs    = $this->jobService->getFilteredJobs($filters, 6);

        $formattedJobs = collect($jobs->items())->map(fn ($job) => [
            'id'              => $job->id,
            'title'           => $job->title,
            'slug'            => $job->slug,
            'post_type'       => $job->post_type,
            'category'        => $job->category->name    ?? 'Gov Job',
            'department'      => $job->department->name  ?? 'Government',
            'state'           => $job->state->name       ?? 'Pan India',
            'qualification'   => $job->qualification->name ?? 'Graduate',
            'vacancy_count'   => $job->vacancy_count,
            'salary_min'      => number_format($job->salary_min, 0),
            'salary_max'      => number_format($job->salary_max, 0),
            'application_fee' => number_format($job->application_fee, 2),
            'last_date'       => $job->last_date_to_apply?->format('d M Y') ?? 'N/A',
            'is_featured'     => $job->is_featured,
        ]);

        return response()->json(['status' => 'success', 'data' => ['jobs' => $formattedJobs, 'current_page' => $jobs->currentPage(), 'last_page' => $jobs->lastPage(), 'total' => $jobs->total()]]);
    }

    public function show(string $slug): JsonResponse
    {
        $job = $this->jobService->getJobDetail($slug);
        if (!$job) return response()->json(['status' => 'error', 'message' => 'Job not found.'], 404);

        return response()->json(['status' => 'success', 'data' => [
            'id'                    => $job->id,
            'title'                 => $job->title,
            'post_type'             => $job->post_type,
            'category'              => $job->category->name      ?? 'Gov Job',
            'department'            => $job->department->name    ?? 'Government',
            'state'                 => $job->state->name         ?? 'Pan India',
            'qualification'         => $job->qualification->name ?? 'Graduate',
            'vacancy_count'         => $job->vacancy_count,
            'salary_min'            => number_format($job->salary_min, 0),
            'salary_max'            => number_format($job->salary_max, 0),
            'application_fee'       => number_format($job->application_fee, 2),
            'age_limit'             => $job->age_limit ?? '18-32 Years',
            'last_date'             => $job->last_date_to_apply?->format('d M Y') ?? 'N/A',
            'exam_date'             => $job->exam_date?->format('d M Y') ?? 'Announced Soon',
            'official_website_link' => $job->official_website_link,
            'apply_link'            => $job->apply_link,
            'description'           => $job->description,
            'exam_pattern'          => $job->exam_pattern     ?? 'Objective MCQs.',
            'selection_process'     => $job->selection_process ?? 'Written Exam.',
        ]]);
    }
}
