<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\JobAlert;
use App\Models\JobPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WeeklyDigestScheduler extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:send-weekly-digest';

    /**
     * The console command description.
     */
    protected $description = 'Dispatch the Weekly Government Careers Digest newsletter';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Compiling Weekly Government Careers Digest...');

        $oneWeekAgo = Carbon::now()->subDays(7);

        // Fetch top published items in the last 7 days
        $recentJobs = JobPost::published()
            ->jobs()
            ->where('published_at', '>=', $oneWeekAgo)
            ->latest('published_at')
            ->take(5)
            ->pluck('id')
            ->toArray();

        $admitCards = JobPost::published()
            ->admitCards()
            ->where('published_at', '>=', $oneWeekAgo)
            ->latest('published_at')
            ->take(5)
            ->pluck('id')
            ->toArray();

        $results = JobPost::published()
            ->results()
            ->where('published_at', '>=', $oneWeekAgo)
            ->latest('published_at')
            ->take(5)
            ->pluck('id')
            ->toArray();

        // If there is absolutely no content to report, exit safely to save mail quota
        if (empty($recentJobs) && empty($admitCards) && empty($results)) {
            $this->warn('No new content published in the last week. Skipping Weekly Digest.');
            return;
        }

        $candidates = User::where('role', 'candidate')->where('is_active', true)->get();
        $subscribers = JobAlert::all();

        $this->line("Dispatching weekly newsletter to " . $candidates->count() . " candidates and " . $subscribers->count() . " subscribers...");

        $payload = [
            'recent_job_ids' => $recentJobs,
            'admit_card_ids' => $admitCards,
            'result_ids' => $results,
        ];

        // Send to registered users
        foreach ($candidates as $user) {
            $userPayload = array_merge($payload, [
                'user_id' => $user->id,
                'name' => $user->name,
            ]);
            SendEmailJob::dispatch($user->email, 'weekly_digest', $userPayload);
        }

        // Send to guest alert subscribers
        $uniqueEmails = $subscribers->pluck('email')->unique();
        foreach ($uniqueEmails as $email) {
            $subPayload = array_merge($payload, [
                'name' => 'Subscriber',
            ]);
            SendEmailJob::dispatch($email, 'weekly_digest', $subPayload);
        }

        $this->info('Weekly Digest dispatches successfully queued.');
    }
}
