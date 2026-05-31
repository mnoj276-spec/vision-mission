<?php

namespace App\Observers;

use App\Models\JobPost;
use Illuminate\Support\Facades\Cache;

class JobPostObserver
{
    /**
     * Handle the JobPost "created" event.
     */
    public function created(JobPost $jobPost): void
    {
        $this->flushCache();
    }

    /**
     * Handle the JobPost "updated" event.
     */
    public function updated(JobPost $jobPost): void
    {
        $this->flushCache();
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
        Cache::forget('news_sitemap_xml');
    }
}
