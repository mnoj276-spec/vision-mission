<?php

namespace App\Console\Commands;

use App\Models\JobPost;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FeatureExpiryScheduler extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'monetization:expire-features';

    /**
     * The console command description.
     */
    protected $description = 'Automatically expire featured and sponsored jobs whose application deadline or expiration date has passed';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $today = Carbon::today();
        $this->info("Scanning for expired featured or sponsored listings as of {$today->format('Y-m-d')}...");

        // Find active featured or sponsored posts where either expires_at or last_date_to_apply has passed
        $expiredPosts = JobPost::where(function ($query) {
                $query->where('is_featured', true)
                      ->orWhere('is_sponsored', true);
            })
            ->where(function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->whereNotNull('expires_at')
                      ->where('expires_at', '<', $today);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereNotNull('last_date_to_apply')
                      ->where('last_date_to_apply', '<', $today);
                });
            })
            ->get();

        if ($expiredPosts->isEmpty()) {
            $this->info("No expired featured or sponsored listings found.");
            return;
        }

        $this->line("Found {$expiredPosts->count()} expired listings. Processing updates...");

        $count = 0;
        foreach ($expiredPosts as $post) {
            $post->is_featured = false;
            $post->is_sponsored = false;
            $post->save();
            $this->line("-> Expired feature/sponsor options for: '{$post->title}' (ID: {$post->id})");
            $count++;
        }

        $this->info("Successfully expired monetization attributes for {$count} government job listings.");
    }
}
