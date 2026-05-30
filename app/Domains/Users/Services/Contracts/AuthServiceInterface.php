<?php

namespace App\Domains\Users\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new candidate user and auto-login.
     */
    public function register(array $data): User;

    /**
     * Attempt login, returning the user on success or null on failure.
     */
    public function attemptLogin(string $email, string $password): ?User;

    /**
     * Generate and store an OTP for password reset.
     */
    public function generateResetOtp(string $email): array;

    /**
     * Verify OTP and reset the user's password.
     */
    public function resetPassword(string $email, string $otp, string $newPassword): User;

    /**
     * Update an authenticated user's profile details.
     */
    public function updateProfile(User $user, array $data): void;
}
