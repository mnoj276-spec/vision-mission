<?php

namespace App\Domains\Users\Services;

use App\Domains\Users\Services\Contracts\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * AuthService
 *
 * Owns all authentication and profile business logic extracted from:
 * – AuthController (register, login, OTP, password reset)
 * – DashboardController (updateProfile)
 */
class AuthService implements AuthServiceInterface
{
    /**
     * Register a new candidate user and auto-login.
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => Hash::make($data['password']),
            'role'      => 'candidate',
            'is_active' => true,
        ]);

        Auth::login($user);

        return $user;
    }

    /**
     * Attempt login; returns the authenticated user or null on failure.
     * Enforces the is_active guard and auto-logouts inactive accounts.
     */
    public function attemptLogin(string $email, string $password): ?User
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return null;
        }

        return $user;
    }

    /**
     * Generate and persist a mock OTP for password recovery.
     * In production, replace with a signed URL or proper mail token.
     */
    public function generateResetOtp(string $email): array
    {
        $user = User::where('email', $email)->firstOrFail();

        $otp = '123456'; // Simulated OTP — swap with Str::random(6) + mail in production
        $user->otp_code       = $otp;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        return ['otp_code' => $otp, 'user' => $user];
    }

    /**
     * Verify the OTP and reset the user's password, then auto-login.
     */
    public function resetPassword(string $email, string $otp, string $newPassword): User
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($user->otp_code !== $otp || now()->greaterThan($user->otp_expires_at)) {
            throw new \InvalidArgumentException('Invalid or expired verification code.');
        }

        $user->password       = Hash::make($newPassword);
        $user->otp_code       = null;
        $user->otp_expires_at = null;
        $user->save();

        Auth::login($user);

        return $user;
    }

    /**
     * Update an authenticated candidate's profile fields.
     */
    public function updateProfile(User $user, array $data): void
    {
        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
    }
}
