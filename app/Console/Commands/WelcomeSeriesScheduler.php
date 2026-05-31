<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WelcomeSeriesScheduler extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:welcome-series-scheduler';

    /**
     * The console command description.
     */
    protected $description = 'Trigger scheduled emails for Welcome Series Parts 2 & 3';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting Welcome Series scheduler scan...');

        // 1. Part 2: Users registered >= 1 day ago who have not received welcome_2
        $oneDayAgo = Carbon::now()->subDay();
        $candidatesForPart2 = User::query()
            ->where('role', 'candidate')
            ->where('is_active', true)
            ->where('created_at', '<=', $oneDayAgo)
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('email_logs')
                    ->whereColumn('email_logs.user_id', 'users.id')
                    ->where('email_logs.campaign_type', 'welcome_2');
            })
            ->get();

        foreach ($candidatesForPart2 as $user) {
            SendEmailJob::dispatch($user->email, 'welcome_2', [
                'user_id' => $user->id,
                'name' => $user->name,
            ]);
            $this->line("Dispatched Welcome Part 2 to Candidate: {$user->name} [{$user->email}]");
        }

        // 2. Part 3: Users registered >= 3 days ago who have not received welcome_3
        $threeDaysAgo = Carbon::now()->subDays(3);
        $candidatesForPart3 = User::query()
            ->where('role', 'candidate')
            ->where('is_active', true)
            ->where('created_at', '<=', $threeDaysAgo)
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('email_logs')
                    ->whereColumn('email_logs.user_id', 'users.id')
                    ->where('email_logs.campaign_type', 'welcome_3');
            })
            ->get();

        foreach ($candidatesForPart3 as $user) {
            SendEmailJob::dispatch($user->email, 'welcome_3', [
                'user_id' => $user->id,
                'name' => $user->name,
            ]);
            $this->line("Dispatched Welcome Part 3 to Candidate: {$user->name} [{$user->email}]");
        }

        $this->info('Welcome Series scheduler scan completed.');
    }
}
