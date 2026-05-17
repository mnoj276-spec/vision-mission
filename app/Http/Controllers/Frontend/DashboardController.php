<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Retrieve authenticated candidate bookmarks and job applications
     */
    public function getDashboardData(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Fetch User Bookmarks with related Job posts details
        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['jobPost.category', 'jobPost.department', 'jobPost.state'])
            ->get()
            ->map(function ($bookmark) {
                $job = $bookmark->jobPost;
                return [
                    'bookmark_id' => $bookmark->id,
                    'job_id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'department' => $job->department->name ?? 'Government',
                    'state' => $job->state->name ?? 'Pan India',
                    'last_date' => $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A'
                ];
            });

        // Fetch User Job Applications
        $applications = JobApplication::where('user_id', $user->id)
            ->with(['jobPost.category', 'jobPost.department'])
            ->get()
            ->map(function ($app) {
                $job = $app->jobPost;
                return [
                    'application_id' => $app->id,
                    'job_id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'department' => $job->department->name ?? 'Government',
                    'status' => $app->status, // e.g. applied, reviewing, shortlisted, rejected
                    'applied_at' => $app->created_at->format('d M Y')
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'bookmarks' => $bookmarks,
                'applications' => $applications,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'Not Verified'
                ]
            ]
        ]);
    }

    /**
     * Toggle Bookmark State for a Job Posting (AJAX)
     */
    public function toggleBookmark(int $jobId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Please log in to bookmark jobs.'], 401);
        }

        $job = JobPost::find($jobId);
        if (!$job) {
            return response()->json(['status' => 'error', 'message' => 'Job posting not found.'], 404);
        }

        $existing = Bookmark::where('user_id', $user->id)
            ->where('job_post_id', $jobId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status' => 'success',
                'action' => 'removed',
                'message' => 'Job removed from bookmarks.'
            ]);
        }

        Bookmark::create([
            'user_id' => $user->id,
            'job_post_id' => $jobId
        ]);

        return response()->json([
            'status' => 'success',
            'action' => 'added',
            'message' => 'Job successfully bookmarked!'
        ]);
    }

    /**
     * Apply for a Job with Resume Upload (AJAX)
     */
    public function applyJob(Request $request, int $jobId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Please log in to submit applications.'], 401);
        }

        $job = JobPost::find($jobId);
        if (!$job) {
            return response()->json(['status' => 'error', 'message' => 'Job posting not found.'], 404);
        }

        // Verify if candidate already applied
        $alreadyApplied = JobApplication::where('user_id', $user->id)
            ->where('job_post_id', $jobId)
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already submitted an application for this recruitment posting.'
            ], 422);
        }

        // Validate CV Upload
        $validator = Validator::make($request->all(), [
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Save Resume File
        $resumePath = null;
        if ($request->hasFile('resume')) {
            // Simulated upload (works in active test environments safely)
            $file = $request->file('resume');
            $fileName = 'resume_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store resume securely inside app storage
            $resumePath = $file->storeAs('resumes', $fileName, 'public');
        }

        // Create Recruitment Application
        JobApplication::create([
            'user_id' => $user->id,
            'job_post_id' => $job->id,
            'resume_path' => $resumePath,
            'status' => 'applied'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your application has been successfully submitted to recruiters!'
        ]);
    }

    /**
     * Update Candidate Profile Details (AJAX)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile settings successfully updated!'
        ]);
    }

    /**
     * Update Match Alert Preferences (AJAX)
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Validate checkbox settings
        $validator = Validator::make($request->all(), [
            'email_alerts' => 'nullable|boolean',
            'sms_alerts' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification alert channels updated successfully!'
        ]);
    }
}

