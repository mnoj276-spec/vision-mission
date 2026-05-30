<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\JobPostResource;
use App\Http\Resources\Api\V1\JobApplicationResource;
use App\Domains\Users\Services\Contracts\AuthServiceInterface;
use App\Models\Bookmark;
use App\Models\JobApplication;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AuthServiceInterface $authService) {}

    /**
     * Retrieve authenticated candidate dashboard details.
     */
    public function getDashboardData(): JsonResponse
    {
        $user = auth('api')->user();

        // Retrieve bookmarked job postings loaded with relationships
        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['jobPost.category', 'jobPost.department', 'jobPost.state', 'jobPost.qualification', 'jobPost.district'])
            ->get()
            ->pluck('jobPost')
            ->filter();

        // Retrieve candidate applications loaded with job posting relationship
        $applications = JobApplication::where('user_id', $user->id)
            ->with(['jobPost'])
            ->get();

        return $this->successResponse([
            'user'         => new UserResource($user),
            'bookmarks'    => JobPostResource::collection($bookmarks),
            'applications' => JobApplicationResource::collection($applications),
        ], 'Dashboard data retrieved successfully.');
    }

    /**
     * Update candidate profile properties.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'    => 'required|string|max:15|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $this->authService->updateProfile($user, $validator->validated());

        return $this->successResponse(
            new UserResource($user->fresh()),
            'Profile settings successfully updated!'
        );
    }

    /**
     * Update candidate contact and notification channel alerts.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'emailAlerts' => 'nullable|boolean',
            'smsAlerts'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->successResponse(
            null,
            'Notification alert channels updated successfully!'
        );
    }
}
