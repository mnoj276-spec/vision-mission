<?php

namespace App\Domains\Jobs\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    /**
     * IndexNow API endpoint.
     */
    protected string $endpoint = 'https://api.indexnow.org/indexnow';

    /**
     * The IndexNow API key (from .env).
     */
    protected ?string $apiKey;

    /**
     * Whether IndexNow submissions are enabled.
     */
    protected bool $enabled;

    public function __construct()
    {
        $this->apiKey  = config('services.indexnow.api_key');
        $this->enabled = config('services.indexnow.enabled', false);
    }

    /**
     * Submit a single URL to IndexNow.
     *
     * @param  string  $url  Fully qualified URL to submit
     * @return bool True if accepted (HTTP 200/202)
     */
    public function submitUrl(string $url): bool
    {
        if (!$this->isReady()) {
            return false;
        }

        try {
            $response = Http::timeout(10)->get($this->endpoint, [
                'url'    => $url,
                'key'    => $this->apiKey,
            ]);

            $success = in_array($response->status(), [200, 202]);

            Log::channel('single')->info('[IndexNow] Single URL submission', [
                'url'    => $url,
                'status' => $response->status(),
                'ok'     => $success,
            ]);

            return $success;
        } catch (\Exception $e) {
            Log::channel('single')->warning('[IndexNow] Submission failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Submit a batch of URLs to IndexNow (up to 10,000 per call).
     *
     * @param  array  $urls  Array of fully qualified URLs
     * @return bool True if accepted (HTTP 200/202)
     */
    public function submitBatch(array $urls): bool
    {
        if (!$this->isReady() || empty($urls)) {
            return false;
        }

        // IndexNow batch limit is 10,000
        $urls = array_slice($urls, 0, 10000);

        $host = parse_url($urls[0], PHP_URL_HOST);

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->endpoint, [
                    'host'        => $host,
                    'key'         => $this->apiKey,
                    'keyLocation' => url("/{$this->apiKey}.txt"),
                    'urlList'     => array_values($urls),
                ]);

            $success = in_array($response->status(), [200, 202]);

            Log::channel('single')->info('[IndexNow] Batch submission', [
                'count'  => count($urls),
                'status' => $response->status(),
                'ok'     => $success,
            ]);

            return $success;
        } catch (\Exception $e) {
            Log::channel('single')->warning('[IndexNow] Batch submission failed', [
                'count' => count($urls),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if IndexNow is configured and enabled.
     */
    public function isReady(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }
}
