<?php

namespace App\Domains\Admin\Services\Contracts;

interface SettingsServiceInterface
{
    /**
     * Update bulk general settings.
     */
    public function updateGeneralSettings(array $data): void;

    /**
     * Update logo settings.
     */
    public function updateLogo(string $key, $file): string;

    /**
     * Update Theme settings.
     */
    public function updateThemeSettings(array $data): void;

    /**
     * Update SEO settings.
     */
    public function updateSeoSettings(array $data): void;

    /**
     * Update Email/SMTP settings.
     */
    public function updateEmailSettings(array $data): void;

    /**
     * Update API settings.
     */
    public function updateApiSettings(array $data): void;

    /**
     * Verify SMTP connection by testing connection dynamically.
     */
    public function verifySmtpConnection(array $config): array;

    /**
     * Send test email dynamically.
     */
    public function sendTestEmail(string $recipient, array $config): void;

    /**
     * Generate SQL Database backup.
     */
    public function generateBackup(): string;

    /**
     * Restore database from SQL backup file.
     */
    public function restoreBackup(string $filename): void;

    /**
     * Media Manager: List files
     */
    public function listMedia(string $path = ''): array;
}
