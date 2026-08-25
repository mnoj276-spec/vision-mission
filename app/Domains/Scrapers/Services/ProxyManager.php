<?php

namespace App\Domains\Scrapers\Services;

use Illuminate\Support\Facades\Cache;

class ProxyManager
{
    protected array $proxies = [];

    public function __construct()
    {
        // Load proxies from environment/config or default mock list
        $this->proxies = config('services.scraper.proxies') ?: [];
    }

    /**
     * Get a proxy to use for a request.
     *
     * @param string|null $excludeProxy Proxy to exclude (e.g. if it failed)
     * @return string|null
     */
    public function getProxy(?string $excludeProxy = null): ?string
    {
        if (empty($this->proxies)) {
            return null;
        }

        $available = array_filter($this->proxies, function ($p) use ($excludeProxy) {
            return $p !== $excludeProxy && !Cache::has("proxy:failed:" . md5($p));
        });

        if (empty($available)) {
            // Reset failed proxy cache if all are marked failed
            foreach ($this->proxies as $p) {
                Cache::forget("proxy:failed:" . md5($p));
            }
            $available = $this->proxies;
        }

        // Return a random available proxy
        return $available[array_rand($available)];
    }

    /**
     * Mark a proxy as failed.
     *
     * @param string $proxy
     * @return void
     */
    public function markFailed(string $proxy): void
    {
        // Mark as failed for 5 minutes
        Cache::put("proxy:failed:" . md5($proxy), true, 300);
    }
}
