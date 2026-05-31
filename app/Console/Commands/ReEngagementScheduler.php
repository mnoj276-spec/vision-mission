<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\JobPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReEngagementScheduler extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:send-reengagement';

    /**
     * The console command description.
     */
    protected $description = 'Trigger re-engagement campaigns for inactive users';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Scanning for inactive candidates...');

        $fourteenDaysAgo = Carbon::now()->subDays(14);
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Find users registered before 14 days ago who have been totally inactive
        $inactiveUsers = User::query()
            ->where('role', 'candidate')
            ->where('is_active', true)
            ->where('created_at', '<=', $fourteenDaysAgo)
            // 1. No page views in the last 14 days
            ->whereNotExists(function ($query) use ($fourteenDaysAgo) {
                $query->selectRaw(1)
                    ->from('analytics_page_views')
                    ->whereColumn('analytics_page_views.user_id', 'users.id')
                    ->where('analytics_page_views.created_at', '>=', $fourteenDaysAgo);
            })
            // 2. No job interaction events in the last 14 days
            ->whereNotExists(function ($query) use ($fourteenDaysAgo) {
                $query->selectRaw(1)
                    ->from('analytics_job_events')
                    ->whereColumn('analytics_job_events.user_id', 'users.id')
                    ->where('analytics_job_events.created_at', '>=', $fourteenDaysAgo);
            })
            // 3. Haven't received a re-engagement campaign in the last 30 days
            ->whereNotExists(function ($query) use ($thirtyDaysAgo) {
                $query->selectRaw(1)
                    ->from('email_logs')
                    ->whereColumn('email_logs.user_id', 'users.id')
                    ->where('email_logs.campaign_type', 're_engagement')
                    ->where('email_logs.created_at', '>=', $thirtyDaysAgo);
            })
            ->get();

        if ($inactiveUsers->isEmpty()) {
            $this->info('No inactive candidates found matching criteria.');
            return;
        }

        // Fetch top active job listings to showcase in the email
        $activeJobIds = JobPost::published()
            ->jobs()
            ->latest('published_at')
            ->take(4)
            ->pluck('id')
            ->toArray();

        if (empty($activeJobIds)) {
            $this->warn('No active jobs to showcase. Skipping re-engagement scan.');
            return;
        }

        $this->line("Found " . $inactiveUsers->count() . " inactive candidates. Queueing re-engagement dispatches...");

        foreach ($inactiveUsers as $user) {
            SendEmailJob::dispatch($user->email, 're_engagement', [
                'user_id' => $user->id,
                'name' => $user->name,
                'job_ids' => $activeJobIds,
            ]);
            $this->line("Queued re-engagement email to: {$user->name} [{$user->email}]");
        }

        $this->info('Re-engagement scan completed.');
    }
}
