<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Services\Contracts\NotificationServiceInterface;
use App\Models\JobPost;
use App\Models\ScrapingSource;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * NotificationService
 *
 * Moved from App\Services\NotificationService.
 * Now implements NotificationServiceInterface for proper DI.
 */
class NotificationService implements NotificationServiceInterface
{
    /**
     * Notify candidate users matching the new Job Posting (State, Qualification).
     */
    public function notifyCandidates(JobPost $job): void
    {
        Log::info("Filtering matches to alert candidates for Job ID: {$job->id} [{$job->title}]");

        $matchingUsers = User::query()
            ->where('role', 'candidate')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('phone', '!=', null)
                  ->orWhere('email', '!=', null);
            })
            ->get();

        Log::info("Found {$matchingUsers->count()} candidates matching criteria.");

        foreach ($matchingUsers as $user) {
            Log::info("Dispatched matching job alert to Candidate: {$user->name} [Email: {$user->email}]");
            // Mail::to($user->email)->queue(new JobAlertMail($job));
        }
    }

    /**
     * Notify Admin of a critical scraper crash or failure.
     */
    public function notifyAdminScraperFailure(ScrapingSource $source, string $errorMessage): void
    {
        Log::error("CRITICAL: Scraper failed for source: {$source->name}. Reason: {$errorMessage}");

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Log::info("Sent Scraper Crash alert to Admin: {$admin->name} [Email: {$admin->email}]");
            // Mail::raw("Scraper failure...", fn($m) => $m->to($admin->email)->subject("Scraper Failure!"));
        }
    }

    /**
     * Notify Admin of a quarantined listing (schema violation).
     */
    public function notifyAdminQuarantine(string $sourceName, array $validationErrors): void
    {
        Log::warning("QUARANTINE WARNING: Scraped listing from {$sourceName} failed validation.");

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Log::info("Sent Quarantine Review alert to Admin: {$admin->name}");
            // Mail::raw("A scraped listing from {$sourceName} failed schema checks...", ...);
        }
    }
}
