<?php

namespace App\Domains\Users\Controllers;

use App\Domains\Users\Requests\LoginRequest;
use App\Domains\Users\Requests\RegisterRequest;
use App\Domains\Users\Services\Contracts\AuthServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AuthController — thin HTTP adapter.
 * All business logic delegated to AuthService.
 */
class AuthController extends Controller
{
    public function __construct(protected AuthServiceInterface $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Registration completed successfully!', 'user' => ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->attemptLogin($request->email, $request->password);

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials or account deactivated.', 'errors' => ['email' => ['These credentials do not match our records.']]], 401);
        }

        return response()->json(['status' => 'success', 'message' => 'Logged in successfully!', 'user' => ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]]);
    }

    public function logout(Request $request): mixed
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Logged out successfully!']);
        }
        return redirect('/');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|string|email']);

        try {
            $result = $this->authService->generateResetOtp($request->email);
            return response()->json(['status' => 'success', 'message' => 'OTP sent successfully!', 'otp_code' => $result['otp_code']]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['status' => 'error', 'message' => 'No account found with this email.', 'errors' => ['email' => ['No account found.']]], 404);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|string|email', 'otp_code' => 'required|string', 'password' => 'required|string|min:6|confirmed']);

        try {
            $user = $this->authService->resetPassword($request->email, $request->otp_code, $request->password);
            return response()->json(['status' => 'success', 'message' => 'Password reset completed! You are now logged in.', 'user' => ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'errors' => ['otp_code' => ['OTP code mismatch.']]], 400);
        }
    }
}
