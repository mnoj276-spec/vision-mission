<?php

namespace App\Http\Controllers\Api\V1\Jobs;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\JobPostResource;
use App\Http\Resources\Api\V1\JobApplicationResource;
use App\Http\Resources\Api\V1\PaginationResource;
use App\Domains\Jobs\Services\Contracts\JobServiceInterface;
use App\Models\Bookmark;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected JobServiceInterface $jobService
    ) {}

    /**
     * List all job postings with advanced filtering and mobile-friendly pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'state_id',
            'category_id',
            'qualification_id',
            'min_salary',
            'has_no_fee',
            'post_type'
        ]);

        // Map mobile client camelCase parameters to database snake_case
        if ($request->has('stateId')) $filters['state_id'] = $request->input('stateId');
        if ($request->has('categoryId')) $filters['category_id'] = $request->input('categoryId');
        if ($request->has('qualificationId')) $filters['qualification_id'] = $request->input('qualificationId');
        if ($request->has('minSalary')) $filters['min_salary'] = $request->input('minSalary');
        if ($request->has('hasNoFee')) $filters['has_no_fee'] = $request->input('hasNoFee');
        if ($request->has('postType')) $filters['post_type'] = $request->input('postType');

        $perPage = $request->input('perPage', 10);
        $jobs = $this->jobService->getFilteredJobs($filters, (int) $perPage);

        return $this->successResponse([
            'jobs'       => JobPostResource::collection($jobs->items()),
            'pagination' => PaginationResource::format($jobs),
        ], 'Jobs list retrieved successfully.');
    }

    /**
     * Get details of a single job posting by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $job = $this->jobService->getJobDetail($slug);
        
        if (!$job) {
            return $this->errorResponse('Job posting not found.', 404);
        }

        return $this->successResponse(
            new JobPostResource($job),
            'Job details retrieved successfully.'
        );
    }

    /**
     * Toggle bookmark status for a job posting.
     */
    public function toggleBookmark(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $job = JobPost::find($id);

        if (!$job) {
            return $this->errorResponse('Job posting not found.', 404);
        }

        $existing = Bookmark::where('user_id', $user->id)->where('job_post_id', $id)->first();

        if ($existing) {
            $existing->delete();
            return $this->successResponse([
                'isBookmarked' => false,
                'action'       => 'removed'
            ], 'Job removed from bookmarks successfully.');
        }

        Bookmark::create([
            'user_id'     => $user->id,
            'job_post_id' => $id
        ]);

        return $this->successResponse([
            'isBookmarked' => true,
            'action'       => 'added'
        ], 'Job successfully bookmarked!');
    }

    /**
     * Apply for a job posting, implementing rigorous security filters.
     */
    public function applyJob(Request $request, int $id): JsonResponse
    {
        $user = auth('api')->user();
        $job = JobPost::find($id);

        if (!$job) {
            return $this->errorResponse('Job posting not found.', 404);
        }

        // 1. Double application prevention
        if (JobApplication::where('user_id', $user->id)->where('job_post_id', $id)->exists()) {
            return $this->errorResponse('You have already applied for this posting.', 422);
        }

        // 2. Initial size validation
        $validator = Validator::make($request->all(), [
            'resume' => 'required|file|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $file = $request->file('resume');

        // 3. Strict magic bytes validation (MIME check)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            return $this->errorResponse('Invalid file format. Only PDF, DOC, and DOCX resumes are allowed.', 422);
        }

        // 4. Reject dual extension-spoofing
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['pdf', 'doc', 'docx'];

        if (!in_array($extension, $allowedExtensions) || preg_match('/\.[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/', $originalName)) {
            return $this->errorResponse('File contains invalid extensions. Dual extension spoofing detected.', 422);
        }

        // 5. Antivirus Scan hook integration
        if (!\App\Services\AntivirusScanner::scan($file)) {
            return $this->errorResponse('Antivirus scan failed. The uploaded file appears to be infected or malicious.', 422);
        }

        // 6. Secure upload storage
        $fileName = 'resume_' . $user->id . '_' . time() . '.' . $extension;
        $resumePath = $file->storeAs('resumes', $fileName, 'public');

        $application = JobApplication::create([
            'user_id'     => $user->id,
            'job_post_id' => $job->id,
            'resume_path' => $resumePath,
            'status'      => 'applied'
        ]);

        return $this->successResponse(
            new JobApplicationResource($application),
            'Your application has been successfully submitted!',
            201
        );
    }

    /**
     * Get the historical timeline of a recruitment.
     */
    public function timeline(int $id): JsonResponse
    {
        $job = JobPost::find($id);

        if (!$job) {
            return $this->errorResponse('Job posting not found.', 404);
        }

        // If this is a child post, resolve the root parent
        $root = $job->parent_id ? JobPost::find($job->parent_id) : $job;
        if (!$root) {
            $root = $job;
        }

        // Fetch child updates/notices/results ordered by date to build the timeline
        $timelineEvents = JobPost::where('id', $root->id)
            ->orWhere('parent_id', $root->id)
            ->orderBy('published_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedEvents = $timelineEvents->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'post_type' => $event->post_type,
                'status' => $event->status,
                'published_at' => $event->published_at ? $event->published_at->toIso8601String() : null,
                'last_date_to_apply' => $event->last_date_to_apply ? $event->last_date_to_apply->toIso8601String() : null,
                'official_website_link' => $event->official_website_link,
                'apply_link' => $event->apply_link,
            ];
        });

        return $this->successResponse([
            'recruitment_id' => $root->id,
            'recruitment_title' => $root->title,
            'current_status' => $root->status,
            'timeline' => $formattedEvents,
        ], 'Recruitment timeline retrieved successfully.');
    }
}
