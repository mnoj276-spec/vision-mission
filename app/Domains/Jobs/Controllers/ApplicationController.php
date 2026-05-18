<?php

namespace App\Domains\Jobs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * ApplicationController — candidate job interactions (apply + bookmark).
 * Extracted from DashboardController to respect single responsibility.
 */
class ApplicationController extends Controller
{
    public function toggleBookmark(int $jobId): JsonResponse
    {
        $user = Auth::user();
        $job  = JobPost::find($jobId);
        if (!$job) return response()->json(['status' => 'error', 'message' => 'Job posting not found.'], 404);

        $existing = Bookmark::where('user_id', $user->id)->where('job_post_id', $jobId)->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'success', 'action' => 'removed', 'message' => 'Job removed from bookmarks.']);
        }
        Bookmark::create(['user_id' => $user->id, 'job_post_id' => $jobId]);
        return response()->json(['status' => 'success', 'action' => 'added', 'message' => 'Job successfully bookmarked!']);
    }

    public function applyJob(Request $request, int $jobId): JsonResponse
    {
        $user = Auth::user();
        $job  = JobPost::find($jobId);
        if (!$job) return response()->json(['status' => 'error', 'message' => 'Job posting not found.'], 404);

        if (JobApplication::where('user_id', $user->id)->where('job_post_id', $jobId)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'You have already applied for this posting.'], 422);
        }

        Validator::make($request->all(), ['resume' => 'required|file|mimes:pdf,doc,docx|max:2048'])->validate();

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $file       = $request->file('resume');
            $fileName   = 'resume_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $resumePath = $file->storeAs('resumes', $fileName, 'public');
        }

        JobApplication::create(['user_id' => $user->id, 'job_post_id' => $job->id, 'resume_path' => $resumePath, 'status' => 'applied']);
        return response()->json(['status' => 'success', 'message' => 'Your application has been successfully submitted!']);
    }
}
