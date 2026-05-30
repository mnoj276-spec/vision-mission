<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalLinkingHeaders
{
    /*
    |--------------------------------------------------------------------------
    | Crawl Optimization Middleware
    |--------------------------------------------------------------------------
    |
    | Injects HTTP headers that optimize how search engine crawlers discover
    | and prioritize internal links. Applied to all SEO-facing routes.
    |
    */

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $config = config('internal_linking.crawl_optimization', []);

        if (empty($config)) {
            return $response;
        }

        // 1. Canonical URL enforcement via Link header
        if ($config['canonical_enforcement'] ?? true) {
            $canonical = $request->url();
            $response->headers->set(
                'Link',
                "<{$canonical}>; rel=\"canonical\"",
                false // Don't replace existing Link headers
            );
        }

        // 2. X-Robots-Tag for rich snippet optimization
        if (!empty($config['x_robots_tag'])) {
            $response->headers->set('X-Robots-Tag', $config['x_robots_tag']);
        }

        // 3. Preload hints for top related links (when available)
        if ($config['preload_headers'] ?? true) {
            $viewData = $response->original ?? null;

            if ($viewData && method_exists($viewData, 'getData')) {
                $data = $viewData->getData();
                $preloadLinks = $this->extractPreloadLinks($data);

                foreach ($preloadLinks as $link) {
                    $response->headers->set(
                        'Link',
                        "<{$link}>; rel=\"preload\"; as=\"document\"",
                        false
                    );
                }
            }
        }

        // 4. Add Vary header for proper caching
        $response->headers->set('Vary', 'Accept-Encoding', false);

        return $response;
    }

    /**
     * Extract up to 3 preload URLs from view data for crawler hints.
     */
    protected function extractPreloadLinks(array $data): array
    {
        $links = [];
        $maxPreload = 3;

        // From internal linking data
        if (!empty($data['internalLinks']['related_jobs'])) {
            $routeMap = config('internal_linking.post_type_routes', []);

            foreach ($data['internalLinks']['related_jobs'] as $job) {
                if (count($links) >= $maxPreload) {
                    break;
                }

                $routeName = $routeMap[$job->post_type ?? 'job'] ?? 'seo.job_detail';
                try {
                    $links[] = route($routeName, ['slug' => $job->slug]);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // From cross-type links
        if (!empty($data['internalLinks']['cross_type'])) {
            foreach ($data['internalLinks']['cross_type'] as $crossLink) {
                if (count($links) >= $maxPreload) {
                    break;
                }
                if (!empty($crossLink['url'])) {
                    $links[] = $crossLink['url'];
                }
            }
        }

        return array_unique($links);
    }
}
