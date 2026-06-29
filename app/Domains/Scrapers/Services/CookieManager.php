<?php

namespace App\Domains\Scrapers\Services;

use App\Models\ScrapingSource;

class CookieManager
{
    /**
     * Store cookies for a ScrapingSource.
     *
     * @param ScrapingSource $source
     * @param array $cookies
     * @return void
     */
    public function saveCookies(ScrapingSource $source, array $cookies): void
    {
        $source->update([
            'cookies' => $cookies
        ]);
    }

    /**
     * Retrieve cookies for a ScrapingSource.
     *
     * @param ScrapingSource $source
     * @return array
     */
    public function getCookies(ScrapingSource $source): array
    {
        return $source->cookies ?? [];
    }

    /**
     * Parse cookies from "Set-Cookie" headers.
     *
     * @param array $setCookieHeaders
     * @return array
     */
    public function parseSetCookieHeaders(array $setCookieHeaders): array
    {
        $cookies = [];
        foreach ($setCookieHeaders as $header) {
            $parts = explode(';', $header);
            $cookiePart = trim(array_shift($parts));
            if (!empty($cookiePart) && str_contains($cookiePart, '=')) {
                list($name, $value) = explode('=', $cookiePart, 2);
                $cookies[trim($name)] = trim($value);
            }
        }
        return $cookies;
    }

    /**
     * Get Cookie header string for HTTP request.
     *
     * @param ScrapingSource $source
     * @return string|null
     */
    public function getCookieHeaderString(ScrapingSource $source): ?string
    {
        $cookies = $this->getCookies($source);
        if (empty($cookies)) {
            return null;
        }

        $parts = [];
        foreach ($cookies as $name => $value) {
            $parts[] = "{$name}={$value}";
        }
        return implode('; ', $parts);
    }
}
