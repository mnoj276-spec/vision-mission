<?php

namespace App\Domains\Notifications\Services\Contracts;

use App\Models\JobPost;
use App\Models\ScrapingSource;

interface NotificationServiceInterface
{
    /**
     * Alert candidate users whose profile matches the new job posting.
     */
    public function notifyCandidates(JobPost $job): void;

    /**
     * Alert administrators of a critical scraper crash.
     */
    public function notifyAdminScraperFailure(ScrapingSource $source, string $errorMessage): void;

    /**
     * Alert administrators that a listing has been quarantined for review.
     */
    public function notifyAdminQuarantine(string $sourceName, array $validationErrors): void;
}
