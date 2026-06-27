<?php

namespace App\Observers;

use App\Models\JobPost;
use App\Jobs\SubmitToIndexNow;
use Illuminate\Support\Facades\Cache;

class JobPostObserver
{
    /**
     * Handle the JobPost "created" event.
     */
    public function created(JobPost $jobPost): void
    {
        $this->flushCache();
        $this->notifyIndexNow($jobPost);
    }

    /**
     * Handle the JobPost "updated" event.
     */
    public function updated(JobPost $jobPost): void
    {
        $this->flushCache();
        $this->notifyIndexNow($jobPost);
    }

    /**
     * Handle the JobPost "deleted" event.
     */
    public function deleted(JobPost $jobPost): void
    {
        $this->flushCache();
    }

    /**
     * Handle the JobPost "restored" event.
     */
    public function restored(JobPost $jobPost): void
    {
        $this->flushCache();
        $this->notifyIndexNow($jobPost);
    }

    /**
     * Handle the JobPost "force deleted" event.
     */
    public function forceDeleted(JobPost $jobPost): void
    {
        $this->flushCache();
    }

    /**
     * Flush all frontend sitemap and homepage query cache tags globally.
     */
    protected function flushCache(): void
    {
        Cache::forget('homepage_data');
        Cache::forget('sitemap_xml');
        Cache::forget('sitemap_index_xml');
        Cache::forget('sitemap_pages_xml');
        Cache::forget('sitemap_jobs_xml');
        Cache::forget('sitemap_images_xml');
        Cache::forget('sitemap_videos_xml');
        Cache::forget('sitemap_faqs_xml');
        Cache::forget('news_sitemap_xml');
    }

    /**
     * Dispatch IndexNow submission for published job posts.
     * Runs on the queue to avoid blocking the web request cycle.
     */
    protected function notifyIndexNow(JobPost $jobPost): void
    {
        if ($jobPost->status === 'published') {
            try {
                SubmitToIndexNow::dispatch($jobPost->id)
                    ->onQueue('default')
                    ->delay(now()->addSeconds(5));
            } catch (\Exception $e) {
                // Failsafe — never break the main flow for IndexNow
            }
        }
    }
}

