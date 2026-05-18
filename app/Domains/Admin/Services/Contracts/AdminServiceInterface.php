<?php

namespace App\Domains\Admin\Services\Contracts;

interface AdminServiceInterface
{
    /**
     * Fetch all data for the admin dashboard (sources, logs, quarantines, metrics).
     */
    public function getDashboardData(): array;

    /**
     * Persist SEO settings to storage.
     */
    public function updateSeoSettings(array $settings): void;

    /**
     * Load SEO settings from storage with sensible defaults.
     */
    public function getSeoSettings(): array;

    /**
     * Get paginated activity / audit logs.
     */
    public function getActivityLogs(int $perPage = 10): array;

    /**
     * Record an administrative audit log entry.
     */
    public function logAction(int $userId, string $ip, string $userAgent, string $action, string $details): void;
}
