<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle AJAX User Registration
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create Candidate User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'candidate',
            'is_active' => true
        ]);

        // Login Candidate automatically
        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration completed successfully!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }

    /**
     * Handle AJAX User Login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is deactivated. Please contact support.'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Logged in successfully!',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid email credentials or password.',
            'errors' => [
                'email' => ['These credentials do not match our records.']
            ]
        ], 401);
    }

    /**
     * Handle AJAX or Standard Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully!'
            ]);
        }

        return redirect('/');
    }

    /**
     * Send simulated Password Reset OTP
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user account registered under this email address.',
                'errors' => [
                    'email' => ['No account found.']
                ]
            ], 404);
        }

        // Generate mock OTP
        $otp = '123456';
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Failsafe OTP code sent successfully! Check simulated inbox.',
            'otp_code' => $otp
        ]);
    }

    /**
     * Reset password using verification code
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp_code' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp_code || now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification code.',
                'errors' => [
                    'otp_code' => ['OTP code matches failed.']
                ]
            ], 400);
        }

        // Reset password parameters
        $user->password = Hash::make($request->password);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Login user
        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset completed! You are now logged in.',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }
}

