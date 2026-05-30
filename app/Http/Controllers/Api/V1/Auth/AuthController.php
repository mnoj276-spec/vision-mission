<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\JwtService;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Register a new candidate.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'phone'       => 'required|string|max:15|unique:users',
            'password'    => 'required|string|min:6|confirmed',
            'deviceName'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name'      => \App\Services\HtmlSanitizer::sanitizeString($validated['name']),
            'email'     => \App\Services\HtmlSanitizer::sanitizeString($validated['email']),
            'phone'     => \App\Services\HtmlSanitizer::sanitizeString($validated['phone']),
            'password'  => Hash::make($validated['password']),
            'role'      => 'candidate',
            'is_active' => true,
        ]);

        $accessToken = $this->jwtService->generateToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user, $request->input('deviceName'));

        return $this->successResponse([
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
            'user'         => new UserResource($user),
        ], 'Registration completed successfully!', 201);
    }

    /**
     * Login candidate.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'      => 'required|string|email',
            'password'   => 'required|string',
            'deviceName' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $validated = $validator->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401, [
                'email' => ['These credentials do not match our records.']
            ]);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Access Denied: Your account has been suspended.', 403);
        }

        $accessToken = $this->jwtService->generateToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user, $request->input('deviceName'));

        return $this->successResponse([
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
            'user'         => new UserResource($user),
        ], 'Logged in successfully!');
    }

    /**
     * Refresh access and refresh tokens.
     */
    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refreshToken' => 'required|string',
            'deviceName'   => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $result = $this->jwtService->rotateRefreshToken(
            $request->input('refreshToken'),
            $request->input('deviceName')
        );

        if (!$result) {
            return $this->errorResponse('Invalid or expired refresh token.', 401);
        }

        return $this->successResponse([
            'accessToken'  => $result['access_token'],
            'refreshToken' => $result['refresh_token'],
            'user'         => new UserResource($result['user']),
        ], 'Token refreshed successfully!');
    }

    /**
     * Logout candidate (revokes refresh token).
     */
    public function logout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refreshToken' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $this->jwtService->revokeRefreshToken($request->input('refreshToken'));

        return $this->successResponse(null, 'Logged out successfully!');
    }

    /**
     * Forgot Password - issue simulated OTP.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return $this->errorResponse('No account found with this email.', 404, [
                'email' => ['No account found.']
            ]);
        }

        $otp = '123456';
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        return $this->successResponse([
            'otpCode' => $otp,
        ], 'OTP sent successfully!');
    }

    /**
     * Reset Password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'otpCode'  => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return $this->errorResponse('No account found with this email.', 404);
        }

        if ($user->otp_code !== $request->input('otpCode') || now()->greaterThan($user->otp_expires_at)) {
            return $this->errorResponse('Invalid or expired verification code.', 400, [
                'otpCode' => ['OTP code mismatch.']
            ]);
        }

        $user->password       = Hash::make($request->input('password'));
        $user->otp_code       = null;
        $user->otp_expires_at = null;
        $user->save();

        $accessToken = $this->jwtService->generateToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user, 'API Client');

        return $this->successResponse([
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
            'user'         => new UserResource($user),
        ], 'Password reset completed! You are now logged in.');
    }
}
