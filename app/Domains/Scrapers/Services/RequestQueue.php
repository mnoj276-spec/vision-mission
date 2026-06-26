<?php

namespace App\Domains\Scrapers\Services;

use App\Models\ScrapingSource;
use Illuminate\Support\Facades\Cache;

class RequestQueue
{
    /**
     * Throttle execution based on host name to ensure polite scraping.
     *
     * @param string $url
     * @return void
     */
    public function throttle(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST) ?: 'default_host';
        $key = 'scraper:throttle:' . md5($host);

        // Standard politeness delay (e.g., min 1 second between hits)
        $lastRequest = Cache::get($key);
        if ($lastRequest) {
            $elapsed = microtime(true) - $lastRequest;
            $requiredDelay = 1.0; // 1 second
            if ($elapsed < $requiredDelay) {
                $sleepUs = (int)(($requiredDelay - $elapsed) * 1000000);
                usleep($sleepUs);
            }
        }
        Cache::put($key, microtime(true), 60);
    }

    /**
     * Compute adaptive delay based on historical stats.
     * If the website was slow or has high failure count, we sleep longer to be nice.
     *
     * @param ScrapingSource $source
     * @return int Delay in milliseconds
     */
    public function getAdaptiveDelay(ScrapingSource $source): int
    {
        $stats = $source->performance_stats ?? [];
        $latency = $stats['avg_latency_ms'] ?? 500;
        $failures = $stats['recent_failures'] ?? 0;

        // Base delay is 1000ms.
        // Increase delay by 50% of average latency, plus 1000ms for each recent failure.
        $delay = 1000 + ($latency * 0.5) + ($failures * 1000);

        // Cap delay between 500ms and 10000ms
        return (int) max(500, min(10000, $delay));
    }

    /**
     * Log performance stats after a request.
     *
     * @param ScrapingSource $source
     * @param int $durationMs
     * @param bool $success
     * @return void
     */
    public function recordPerformance(ScrapingSource $source, int $durationMs, bool $success): void
    {
        $stats = $source->performance_stats ?? [];
        $failures = $stats['recent_failures'] ?? 0;
        if ($success) {
            $failures = max(0, $failures - 1);
        } else {
            $failures++;
        }

        // Calculate moving average latency
        $avgLatency = $stats['avg_latency_ms'] ?? 500;
        $avgLatency = ($avgLatency * 4 + $durationMs) / 5;

        $source->update([
            'performance_stats' => [
                'avg_latency_ms' => (int)$avgLatency,
                'recent_failures' => $failures,
                'last_run_timestamp' => time()
            ]
        ]);
    }
}
