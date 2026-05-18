<?php

namespace App\Domains\Scrapers\Repositories\Contracts;

use App\Models\ScrapingSource;
use App\Models\ScrapingLog;
use Illuminate\Database\Eloquent\Collection;

interface ScrapingSourceRepositoryInterface
{
    /**
     * Get all scraping sources ordered by latest.
     */
    public function getAll(): Collection;

    /**
     * Find a scraping source by ID or fail.
     */
    public function findOrFail(int $id): ScrapingSource;

    /**
     * Create a new scraping source.
     */
    public function create(array $data): ScrapingSource;

    /**
     * Update an existing scraping source.
     */
    public function update(ScrapingSource $source, array $data): ScrapingSource;

    /**
     * Delete a scraping source.
     */
    public function delete(ScrapingSource $source): void;

    /**
     * Toggle the active state of a scraping source.
     */
    public function toggle(ScrapingSource $source): ScrapingSource;

    /**
     * Get the latest N scraping log entries with their source.
     */
    public function getRecentLogs(int $limit = 10): Collection;

    /**
     * Get all quarantined log entries.
     */
    public function getQuarantinedLogs(): Collection;

    /**
     * Get aggregate scraping metrics for the admin dashboard.
     */
    public function getMetrics(): array;

    /**
     * Find a quarantined log by ID.
     */
    public function findQuarantinedLog(int $logId): ?ScrapingLog;
}
