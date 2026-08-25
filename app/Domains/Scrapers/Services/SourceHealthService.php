<?php

namespace App\Domains\Scrapers\Services;

use App\Models\ScrapingSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SourceHealthService
{
    /**
     * Record a successful crawl outcome.
     */
    public function recordSuccess(ScrapingSource $source, int $successCount, int $duplicates, int $quarantined, int $failed): void
    {
        $now = Carbon::now();
        $source->last_attempted_at = $now;
        $source->last_succeeded_at = $now;
        $source->consecutive_failures = 0;
        $source->last_records_found = $successCount + $duplicates + $quarantined + $failed;
        $source->last_records_published = $successCount;
        
        $source->health_status = $this->computeHealthStatus($source);
        $source->save();
    }

    /**
     * Record a failed crawl outcome.
     */
    public function recordFailure(ScrapingSource $source, string $errorMessage): void
    {
        $now = Carbon::now();
        $source->last_attempted_at = $now;
        $source->last_failed_at = $now;
        $source->last_failure_reason = $this->classifyFailureType($errorMessage) . ': ' . $errorMessage;
        $source->consecutive_failures++;
        $source->last_records_found = 0;
        $source->last_records_published = 0;

        $source->health_status = $this->computeHealthStatus($source);
        $source->save();
    }

    /**
     * Compute the health status based on failures and inactivity.
     */
    protected function computeHealthStatus(ScrapingSource $source): string
    {
        if (!$source->is_active) {
            return 'inactive';
        }

        if ($source->consecutive_failures >= 3) {
            return 'critical';
        }

        if ($source->last_succeeded_at) {
            $daysSinceSuccess = Carbon::now()->diffInDays($source->last_succeeded_at);
            
            if ($daysSinceSuccess > 7) {
                return 'critical';
            }
            
            if ($daysSinceSuccess > 2) {
                return 'degraded';
            }
        }

        if ($source->consecutive_failures > 0 && $source->consecutive_failures < 3) {
            return 'degraded';
        }

        return 'healthy';
    }

    /**
     * Parse error messages to determine the class of failure.
     */
    protected function classifyFailureType(string $errorMessage): string
    {
        $msg = strtolower($errorMessage);

        if (str_contains($msg, 'could not resolve host') || str_contains($msg, 'dns') || str_contains($msg, 'name resolution')) {
            return '[DNS Failure]';
        }

        if (preg_match('/status:\s*[45]\d{2}/', $msg) || str_contains($msg, 'http error') || str_contains($msg, 'server error') || str_contains($msg, 'forbidden')) {
            return '[HTTP Error]';
        }

        if (str_contains($msg, 'selectors yielded no matching') || str_contains($msg, 'failed to parse') || str_contains($msg, 'node list empty') || str_contains($msg, 'schema validation')) {
            return '[Extraction Failure]';
        }

        if (str_contains($msg, 'timeout') || str_contains($msg, 'timed out') || str_contains($msg, 'too long')) {
            return '[Timeout]';
        }

        return '[Unknown Error]';
    }
}
