<?php

namespace App\Domains\Scrapers\Repositories\Eloquent;

use App\Domains\Scrapers\Repositories\Contracts\ScrapingSourceRepositoryInterface;
use App\Models\JobPost;
use App\Models\ScrapingLog;
use App\Models\ScrapingSource;
use Illuminate\Database\Eloquent\Collection;

/**
 * ScrapingSourceRepository
 * Centralises all scraper source and log queries that previously lived
 * inlined directly in AdminController (637 lines).
 */
class ScrapingSourceRepository implements ScrapingSourceRepositoryInterface
{
    public function getAll(): Collection
    {
        return ScrapingSource::with('latestLog')->orderBy('id', 'desc')->get();
    }

    public function findOrFail(int $id): ScrapingSource
    {
        return ScrapingSource::findOrFail($id);
    }

    public function create(array $data): ScrapingSource
    {
        return ScrapingSource::create($data);
    }

    public function update(ScrapingSource $source, array $data): ScrapingSource
    {
        $source->update($data);
        return $source->fresh();
    }

    public function delete(ScrapingSource $source): void
    {
        $source->delete();
    }

    public function toggle(ScrapingSource $source): ScrapingSource
    {
        $source->is_active = !$source->is_active;
        $source->save();
        return $source;
    }

    public function getRecentLogs(int $limit = 10): Collection
    {
        return ScrapingLog::with('source')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getQuarantinedLogs(): Collection
    {
        return ScrapingLog::with('source')
            ->where('status', 'quarantined')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMetrics(): array
    {
        return [
            'total_sources'    => ScrapingSource::count(),
            'active_sources'   => ScrapingSource::where('is_active', true)->count(),
            'total_jobs_posted'=> JobPost::count(),
            'success_runs'     => ScrapingLog::where('status', 'success')->count(),
            'quarantine_runs'  => ScrapingLog::where('status', 'quarantined')->count(),
            'failed_runs'      => ScrapingLog::where('status', 'failed')->count(),
        ];
    }

    public function findQuarantinedLog(int $logId): ?ScrapingLog
    {
        return ScrapingLog::where('status', 'quarantined')->find($logId);
    }
}
