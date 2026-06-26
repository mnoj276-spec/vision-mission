<?php

namespace App\Domains\Scrapers\Services;

class PageFeatureDetector
{
    /**
     * Inspect HTML content and response headers to detect page features.
     *
     * @param string $html
     * @param array $headers
     * @return array
     */
    public function detect(string $html, array $headers = []): array
    {
        $htmlLower = strtolower($html);
        
        // 1. Detect Cloudflare
        $cloudflare = false;
        if (str_contains($htmlLower, 'cloudflare') || str_contains($htmlLower, 'just a moment...')) {
            $cloudflare = true;
        }
        foreach ($headers as $name => $value) {
            $nameLower = strtolower($name);
            if (str_contains($nameLower, 'cf-ray') || str_contains($nameLower, 'cf-cache-status') || str_contains($nameLower, 'cloudflare')) {
                $cloudflare = true;
                break;
            }
            if (is_array($value)) {
                $valueStr = strtolower(implode(' ', $value));
            } else {
                $valueStr = strtolower((string)$value);
            }
            if (str_contains($valueStr, 'cloudflare')) {
                $cloudflare = true;
                break;
            }
        }

        // 2. Detect React
        $react = (
            str_contains($htmlLower, 'react-root') || 
            str_contains($htmlLower, '_next') || 
            str_contains($htmlLower, '__next_data__') ||
            preg_match('/react[^a-zA-Z0-9]/i', $html) ||
            str_contains($htmlLower, 'static/chunks')
        ) ? true : false;

        // 3. Detect Angular
        $angular = (
            str_contains($htmlLower, 'ng-app') || 
            str_contains($htmlLower, 'ng-version') || 
            str_contains($htmlLower, 'ng-scope') || 
            str_contains($htmlLower, 'angular.js')
        ) ? true : false;

        // 4. Detect Vue
        $vue = (
            str_contains($htmlLower, 'v-bind') || 
            str_contains($htmlLower, 'v-model') || 
            str_contains($htmlLower, '__vue__') ||
            str_contains($htmlLower, 'vue.js') ||
            str_contains($htmlLower, 'vue-router')
        ) ? true : false;

        // 5. Detect Infinite Scroll
        $infiniteScroll = (
            str_contains($htmlLower, 'infinite-scroll') || 
            str_contains($htmlLower, 'scrollposition') || 
            str_contains($htmlLower, 'window.scroll') ||
            str_contains($htmlLower, 'scrollheight') ||
            str_contains($htmlLower, 'loadmore') ||
            str_contains($htmlLower, 'load-more')
        ) ? true : false;

        // 6. Detect Cookies
        $cookies = false;
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'set-cookie') {
                $cookies = true;
                break;
            }
        }
        if (str_contains($htmlLower, 'document.cookie')) {
            $cookies = true;
        }

        // 7. Detect Login Session
        $loginRequired = (
            str_contains($htmlLower, 'type="password"') || 
            str_contains($htmlLower, 'name="password"') || 
            str_contains($htmlLower, '/login') || 
            str_contains($htmlLower, '/signin')
        ) ? true : false;

        // 8. JS Rendering Required
        $javascriptRequired = $react || $angular || $vue || $infiniteScroll || str_contains($htmlLower, 'noscript') || str_contains($htmlLower, 'enable javascript');

        return [
            'cloudflare' => $cloudflare,
            'react' => $react,
            'angular' => $angular,
            'vue' => $vue,
            'infinite_scroll' => $infiniteScroll,
            'cookies' => $cookies,
            'login_required' => $loginRequired,
            'javascript_required' => $javascriptRequired,
        ];
    }
}
