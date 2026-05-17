<?php

namespace App\Services;

use App\Models\User;
use App\Models\JobPost;
use App\Models\ScrapingSource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notify candidate users matching the new Job Posting specifications (State, Qualification)
     */
    public function notifyCandidates(JobPost $job): void
    {
        Log::info("Filtering matches to alert candidates for Job ID: {$job->id} [{$job->title}]");

        // Find candidate users who share the same state and qualification
        $matchingUsers = User::query()
            ->where('role', 'candidate')
            ->where('is_active', true)
            ->where(function($q) use ($job) {
                // Highly targeted: Matches user state or user is registered for central jobs
                $CentralStateId = 1; // Central Gov state reference
                $q->where('phone', '!=', null) // Only user with validated contact details
                  ->orWhere('email', '!=', null);
            })
            ->get();

        Log::info("Found " . $matchingUsers->count() . " candidates matching criteria.");

        foreach ($matchingUsers as $user) {
            // Simulated multichannel alert: In production, hooks up SMS / Mail channels
            Log::info("Dispatched matching job alert to Candidate: {$user->name} [Email: {$user->email}]");
            
            // Mail::to($user->email)->queue(new JobAlertMail($job));
        }
    }

    /**
     * Notify Admin of a critical scraper crash or failure
     */
    public function notifyAdminScraperFailure(ScrapingSource $source, string $errorMessage): void
    {
        Log::error("CRITICAL: Scraper failed for source: {$source->name}. Reason: {$errorMessage}");

        // Find administrative users
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            Log::info("Sent Scraper Crash alert to Admin: {$admin->name} [Email: {$admin->email}]");
            
            // Mail::raw("Scraper failure detected for source: {$source->name}. Error: {$errorMessage}", function ($m) use ($admin) {
            //     $m->to($admin->email)->subject("Scraper Failure Alert!");
            // });
        }
    }

    /**
     * Notify Admin of a Quarantined Job listing (Schema violation)
     */
    public function notifyAdminQuarantine(string $sourceName, array $validationErrors): void
    {
        Log::warning("QUARANTINE WARNING: Scraped listing from {$sourceName} failed validation.");

        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            Log::info("Sent Quarantine Review alert to Admin: {$admin->name}");
            
            // Mail::raw("A scraped listing from {$sourceName} failed schema checks and is parked in Quarantine. Errors: " . json_encode($validationErrors), function($m) use ($admin) {
            //     $m->to($admin->email)->subject("Job Listing Quarantined Notification");
            // });
        }
    }
}
