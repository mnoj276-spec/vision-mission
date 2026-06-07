<?php

namespace App\Domains\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\JobPostAiContent;
use App\Jobs\GenerateJobContentJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiContentManagementController extends Controller
{
    /**
     * Get paginated AI content drafts and system telemetry.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->string('status', 'all');
        $search = $request->string('search', '');
        $perPage = $request->integer('per_page', 10);

        $query = JobPostAiContent::with(['jobPost.department', 'jobPost.category'])
            ->orderBy('id', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->whereHas('jobPost', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $paginated = $query->paginate($perPage);

        // Calculate telemetry metrics
        $telemetry = [
            'total_generated' => JobPostAiContent::count(),
            'pending_count'   => JobPostAiContent::where('status', 'pending')->count(),
            'approved_count'  => JobPostAiContent::where('status', 'approved')->count(),
            'rejected_count'  => JobPostAiContent::where('status', 'rejected')->count(),
            'active_provider' => config('services.ai.provider', 'gemini'),
        ];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'items'     => $paginated->items(),
                'telemetry' => $telemetry,
                'pagination'=> [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'total'        => $paginated->total(),
                    'per_page'     => $paginated->perPage(),
                ]
            ]
        ]);
    }

    /**
     * Approve AI Content.
     */
    public function approve(int $id): JsonResponse
    {
        $content = JobPostAiContent::findOrFail($id);
        $content->update([
            'status'        => 'approved',
            'error_message' => null
        ]);

        Log::info("Admin: Approved AI Content ID [{$id}] for Job [{$content->job_post_id}]");

        return response()->json([
            'status'  => 'success',
            'message' => 'AI Content draft has been successfully approved and is now active publicly.'
        ]);
    }

    /**
     * Reject AI Content.
     */
    public function reject(int $id): JsonResponse
    {
        $content = JobPostAiContent::findOrFail($id);
        $content->update([
            'status' => 'rejected'
        ]);

        Log::info("Admin: Rejected AI Content ID [{$id}] for Job [{$content->job_post_id}]");

        return response()->json([
            'status'  => 'success',
            'message' => 'AI Content draft has been marked as rejected.'
        ]);
    }

    /**
     * Update draft details manually.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $content = JobPostAiContent::findOrFail($id);

        $validated = $request->validate([
            'summary'           => 'required|string',
            'eligibility'       => 'required|string',
            'selection_process' => 'required|string',
            'faqs'              => 'nullable|array',
            'meta_title'        => 'required|string|max:100',
            'meta_description'  => 'required|string|max:255',
            'schema_content'    => 'nullable|array',
        ]);

        $content->update($validated);

        Log::info("Admin: Updated AI Content draft ID [{$id}]");

        return response()->json([
            'status'  => 'success',
            'message' => 'Draft content changes successfully saved.'
        ]);
    }

    /**
     * Manually trigger/regenerate AI content.
     */
    public function generate(Request $request, int $jobPostId): JsonResponse
    {
        $jobPost = JobPost::findOrFail($jobPostId);
        $provider = $request->input('provider');

        // Dispatch job in the background
        GenerateJobContentJob::dispatch($jobPost->id, $provider, true);

        Log::info("Admin: Manually dispatched AI generation for Job [{$jobPostId}] with provider [{$provider}]");

        return response()->json([
            'status'  => 'success',
            'message' => 'Content generation task queued. The AI is working on it in the background.'
        ]);
    }
}
