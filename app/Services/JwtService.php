<?php

namespace App\Services;

use App\Models\User;
use App\Models\PersonalRefreshToken;

class JwtService
{
    /**
     * Generate a new JWT access token for the given User.
     * Access token defaults to 15 minutes lifetime.
     */
    public function generateToken(User $user, int $lifetime = 900): string
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        
        $now = time();
        $payload = json_encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'exp' => $now + $lifetime,
            'nbf' => $now,
            'jti' => bin2hex(random_bytes(16)),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);

        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->getSecret(), true);
        $base64Signature = $this->base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    /**
     * Validate the provided JWT access token and return its decoded payload.
     * Returns null if signature is invalid, expired, or structural issues are present.
     */
    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        $signature = $this->base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->getSecret(), true);

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($base64Payload), true);
        if (!$payload) {
            return null;
        }

        $now = time();

        // Expired validation
        if (isset($payload['exp']) && $payload['exp'] < $now) {
            return null;
        }

        // Not-before validation
        if (isset($payload['nbf']) && $payload['nbf'] > $now) {
            return null;
        }

        return $payload;
    }

    /**
     * Generate a database-backed refresh token for the user.
     * Returns the raw random token to be sent to the mobile client.
     */
    public function generateRefreshToken(User $user, ?string $deviceName = null): string
    {
        $rawToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $rawToken);

        PersonalRefreshToken::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'device_name' => $deviceName ?: 'API Client',
            'expires_at' => now()->addDays(30),
        ]);

        return $rawToken;
    }

    /**
     * Rotate the refresh token: verifies and deletes the old token,
     * and generates a brand new access and refresh token pair.
     */
    public function rotateRefreshToken(string $rawToken, ?string $deviceName = null): ?array
    {
        $hashedToken = hash('sha256', $rawToken);
        $refreshTokenRecord = PersonalRefreshToken::where('token', $hashedToken)->first();

        if (!$refreshTokenRecord || $refreshTokenRecord->isExpired()) {
            return null;
        }

        $user = $refreshTokenRecord->user;

        // Revoke the current refresh token (prevent replay attacks)
        $refreshTokenRecord->delete();

        // Issue new token pair
        $newAccessToken = $this->generateToken($user);
        $newRefreshToken = $this->generateRefreshToken($user, $deviceName ?: $refreshTokenRecord->device_name);

        return [
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'user'          => $user,
        ];
    }

    /**
     * Revoke a refresh token (used on Logout).
     */
    public function revokeRefreshToken(string $rawToken): bool
    {
        $hashedToken = hash('sha256', $rawToken);
        return (bool) PersonalRefreshToken::where('token', $hashedToken)->delete();
    }

    /**
     * Get the cryptographically secure secret from the application key.
     */
    private function getSecret(): string
    {
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }
        return $key;
    }

    /**
     * Base64URL encoding helper.
     */
    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64URL decoding helper.
     */
    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
