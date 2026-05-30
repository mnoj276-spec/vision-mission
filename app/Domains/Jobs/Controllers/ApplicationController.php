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
        app(\App\Services\AnalyticsService::class)->trackJobEvent($jobId, 'bookmark');
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

        // Basic validation first (presence, file, basic size)
        $validator = Validator::make($request->all(), [
            'resume' => 'required|file|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('resume');

        // 1. Strict server-side magic bytes validation (MIME check)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid file format. Only PDF, DOC, and DOCX resumes are allowed.'
            ], 422);
        }

        // 2. Reject dual extensions or mismatch extension-spoofing
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['pdf', 'doc', 'docx'];

        // Reject if original extension isn't in allowed list or if there are multiple extensions
        if (!in_array($extension, $allowedExtensions) || preg_match('/\.[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/', $originalName)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File contains invalid extensions or structure. Dual extension spoofing detected.'
            ], 422);
        }

        // 3. Scan the upload via the AntivirusScanner hook
        if (!\App\Services\AntivirusScanner::scan($file)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Antivirus scan failed. The uploaded file appears to be infected or malicious.'
            ], 422);
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $fileName   = 'resume_' . $user->id . '_' . time() . '.' . $extension;
            $resumePath = $file->storeAs('resumes', $fileName, 'public');
        }

        JobApplication::create(['user_id' => $user->id, 'job_post_id' => $job->id, 'resume_path' => $resumePath, 'status' => 'applied']);
        app(\App\Services\AnalyticsService::class)->trackJobEvent($job->id, 'apply_submit');
        return response()->json(['status' => 'success', 'message' => 'Your application has been successfully submitted!']);
    }
}
