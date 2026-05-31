<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\JobAlert;
use App\Models\JobPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AlertsScheduler extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:send-alerts {--hours=24 : The execution window in hours}';

    /**
     * The console command description.
     */
    protected $description = 'Dispatch matched job alerts, results alerts, and admit card alerts';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $hours = (int) $this->option('hours');
        $this->info("Scanning for publications in the last {$hours} hours...");

        $window = Carbon::now()->subHours($hours);

        // Fetch all published posts in the window
        $recentPosts = JobPost::published()
            ->with(['category', 'department', 'state'])
            ->where('published_at', '>=', $window)
            ->get();

        if ($recentPosts->isEmpty()) {
            $this->info('No new publications found in this window.');
            return;
        }

        $jobs = $recentPosts->where('post_type', 'job');
        $results = $recentPosts->where('post_type', 'result');
        $admitCards = $recentPosts->where('post_type', 'admit_card');

        $this->line("Found: " . $jobs->count() . " jobs, " . $results->count() . " results, " . $admitCards->count() . " admit cards.");

        // Fetch candidates and subscribers
        $candidates = User::where('role', 'candidate')->where('is_active', true)->get();
        $subscribers = JobAlert::all();

        // 1. Process Job Alerts
        if ($jobs->isNotEmpty()) {
            $jobIds = $jobs->pluck('id')->toArray();

            // Dispatch aggregated alert to active candidates
            foreach ($candidates as $user) {
                SendEmailJob::dispatch($user->email, 'job_alert', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'job_ids' => $jobIds,
                ]);
            }

            // Dispatch matching category alerts to guest subscribers
            foreach ($subscribers as $sub) {
                // Find jobs matching the guest preference category name
                $matchedJobs = $jobs->filter(function ($job) use ($sub) {
                    if (empty($sub->category_name)) return true; // Global match
                    return strcasecmp($job->category->name ?? '', $sub->category_name) === 0;
                });

                if ($matchedJobs->isNotEmpty()) {
                    SendEmailJob::dispatch($sub->email, 'job_alert', [
                        'name' => 'Subscriber',
                        'job_ids' => $matchedJobs->pluck('id')->toArray(),
                    ]);
                }
            }
        }

        // 2. Process Result Alerts
        if ($results->isNotEmpty()) {
            $resultIds = $results->pluck('id')->toArray();

            // Send result alerts to candidates
            foreach ($candidates as $user) {
                SendEmailJob::dispatch($user->email, 'result_alert', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'job_ids' => $resultIds,
                ]);
            }

            // Send to guest subscribers too (results are high-priority)
            $uniqueSubEmails = $subscribers->pluck('email')->unique();
            foreach ($uniqueSubEmails as $email) {
                SendEmailJob::dispatch($email, 'result_alert', [
                    'name' => 'Subscriber',
                    'job_ids' => $resultIds,
                ]);
            }
        }

        // 3. Process Admit Card Alerts
        if ($admitCards->isNotEmpty()) {
            $admitIds = $admitCards->pluck('id')->toArray();

            // Send admit card alerts to candidates
            foreach ($candidates as $user) {
                SendEmailJob::dispatch($user->email, 'admit_card_alert', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'job_ids' => $admitIds,
                ]);
            }

            // Send to guest subscribers
            $uniqueSubEmails = $subscribers->pluck('email')->unique();
            foreach ($uniqueSubEmails as $email) {
                SendEmailJob::dispatch($email, 'admit_card_alert', [
                    'name' => 'Subscriber',
                    'job_ids' => $admitIds,
                ]);
            }
        }

        $this->info('Alerts dispatch logic completed.');
    }
}
