<?php

namespace App\Domains\Scrapers\Services;

use App\Models\ScrapingSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class HybridScrapingEngine
{
    public function __construct(
        protected PageFeatureDetector $featureDetector,
        protected CookieManager $cookieManager,
        protected ProxyManager $proxyManager,
        protected RequestQueue $requestQueue,
        protected BrowserPool $browserPool
    ) {}

    /**
     * Fetch page content using the hybrid engine hierarchy.
     *
     * @param ScrapingSource $source
     * @return string Fully rendered page HTML
     * @throws \Exception
     */
    public function fetch(ScrapingSource $source): string
    {
        $url = $source->source_url;

        // Apply politeness throttle based on host
        $this->requestQueue->throttle($url);

        // Calculate adaptive delay based on historical stats
        $delayMs = $this->requestQueue->getAdaptiveDelay($source);
        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }

        $startTime = microtime(true);

        // Acquire session from the pool
        $sessionId = $this->browserPool->acquireSession((string)$source->id);

        try {
            // Determine starting engine: check if preferred engine is cached,
            // or if we have previously detected JS/SPA/Cloudflare characteristics.
            $engineStack = $this->getEngineStack($source);
            $lastError = null;
            $html = '';

            foreach ($engineStack as $engine) {
                try {
                    Log::info("Attempting scrape of [{$url}] using engine: {$engine}");
                    $html = $this->executeEngine($engine, $source);

                    // Validate output: ensure it isn't an empty/error page or captcha
                    $this->validateContent($html);

                    // Success! Log stats and cache preferred engine
                    $durationMs = (int) ((microtime(true) - $startTime) * 1000);
                    $this->requestQueue->recordPerformance($source, $durationMs, true);
                    $source->update(['preferred_engine' => $engine]);

                    // Perform Page Feature Detection on the successful response to update DB
                    $this->updateDetectedFeatures($source, $html);

                    $this->browserPool->releaseSession($sessionId);
                    return $html;
                } catch (\App\Domains\Scrapers\Exceptions\UnchangedContentException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    Log::warning("Engine {$engine} failed for [{$url}]: " . $e->getMessage());
                    $lastError = $e;
                    // Escalate to next engine in stack
                }
            }

            // If we run out of standard engines in the stack, trigger Fallback Engine
            Log::error("All hybrid scraping engines failed for [{$url}]. Invoking Fallback Engine.");
            $html = $this->executeFallback($source);
            
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $this->requestQueue->recordPerformance($source, $durationMs, false);

            $this->browserPool->releaseSession($sessionId);
            return $html;

        } catch (\Exception $e) {
            $this->browserPool->releaseSession($sessionId);
            throw $e;
        }
    }

    /**
     * Resolve the stack of engines to attempt in order.
     *
     * @param ScrapingSource $source
     * @return array
     */
    protected function getEngineStack(ScrapingSource $source): array
    {
        $preferred = $source->preferred_engine;
        $features = $source->detected_features ?? [];

        // Stack base: HTTP Client -> Headless -> Playwright -> Puppeteer
        $stack = ['http', 'headless', 'playwright', 'puppeteer'];

        // If preferred engine is known and is in stack, prioritize it
        if ($preferred && in_array($preferred, $stack)) {
            $stack = array_diff($stack, [$preferred]);
            array_unshift($stack, $preferred);
        }
        // If JS/SPA is detected, skip standard HTTP client
        elseif (!empty($features['javascript_required']) || !empty($features['cloudflare'])) {
            $stack = array_diff($stack, ['http']);
            // Put headless browser/Playwright first
        }

        return $stack;
    }

    /**
     * Execute a specific scraping engine.
     *
     * @param string $engine
     * @param ScrapingSource $source
     * @return string
     * @throws \Exception
     */
    protected function executeEngine(string $engine, ScrapingSource $source): string
    {
        switch ($engine) {
            case 'http':
                return $this->executeHttpClient($source);

            case 'headless':
                return $this->executeHeadlessBrowser($source);

            case 'playwright':
            case 'puppeteer':
                return $this->executeNodeRunner($engine, $source);

            default:
                throw new \InvalidArgumentException("Unknown engine: {$engine}");
        }
    }

    /**
     * Standard HTTP Client execution.
     *
     * @param ScrapingSource $source
     * @return string
     * @throws \Exception
     */
    protected function executeHttpClient(ScrapingSource $source): string
    {
        $url = $source->source_url;
        $cookieHeader = $this->cookieManager->getCookieHeaderString($source);
        
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'
        ];
        if ($cookieHeader) {
            $headers['Cookie'] = $cookieHeader;
        }
        if ($source->last_modified) {
            $headers['If-Modified-Since'] = $source->last_modified;
        }
        if ($source->etag) {
            $headers['If-None-Match'] = $source->etag;
        }

        $request = Http::timeout(15)->withHeaders($headers)->withOptions([
            'allow_redirects' => [
                'max'             => 5,
                'strict'          => false,
                'referer'         => false,
                'protocols'       => ['http', 'https'],
                'track_redirects' => true,
                'on_redirect'     => function(\Psr\Http\Message\RequestInterface $req, \Psr\Http\Message\ResponseInterface $res, \Psr\Http\Message\UriInterface $uri) {
                    $redirectUrl = (string)$uri;
                    if (!\App\Services\UrlSecurity::isSafeUrl($redirectUrl)) {
                        throw new \Exception("SSRF Block: Redirect target '{$redirectUrl}' is not permitted.");
                    }
                }
            ]
        ]);

        // Get rotated proxy
        $proxy = $this->proxyManager->getProxy();
        if ($proxy) {
            $request = $request->withOptions(['proxy' => $proxy]);
        }

        $response = $request->get($url);

        if ($response->status() === 304) {
            throw new \App\Domains\Scrapers\Exceptions\UnchangedContentException("URL {$url} content unchanged (304 Not Modified).");
        }

        if ($response->failed()) {
            if ($proxy) {
                $this->proxyManager->markFailed($proxy);
            }
            throw new \Exception("HTTP Client request failed with status: " . $response->status());
        }

        // Store ETag and Last-Modified
        $lastModified = $response->header('Last-Modified');
        $etag = $response->header('ETag');
        if ($lastModified || $etag) {
            $source->update([
                'last_modified' => $lastModified,
                'etag' => $etag,
            ]);
        }

        // Parse and store cookies
        $setCookieHeaders = $response->header('Set-Cookie');
        if ($setCookieHeaders) {
            $setCookies = is_array($setCookieHeaders) ? $setCookieHeaders : [$setCookieHeaders];
            $parsedCookies = $this->cookieManager->parseSetCookieHeaders($setCookies);
            if (!empty($parsedCookies)) {
                $this->cookieManager->saveCookies($source, array_merge(
                    $this->cookieManager->getCookies($source),
                    $parsedCookies
                ));
            }
        }

        return $response->body();
    }

    /**
     * Simulated Headless Browser (curl-based simulation with custom browser headers).
     *
     * @param ScrapingSource $source
     * @return string
     * @throws \Exception
     */
    protected function executeHeadlessBrowser(ScrapingSource $source): string
    {
        $url = $source->source_url;
        $cookieHeader = $this->cookieManager->getCookieHeaderString($source);

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Sec-Ch-Ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Upgrade-Insecure-Requests' => '1',
        ];

        if ($cookieHeader) {
            $headers['Cookie'] = $cookieHeader;
        }
        if ($source->last_modified) {
            $headers['If-Modified-Since'] = $source->last_modified;
        }
        if ($source->etag) {
            $headers['If-None-Match'] = $source->etag;
        }

        $request = Http::timeout(20)->withHeaders($headers)->withOptions([
            'allow_redirects' => [
                'max'             => 5,
                'strict'          => false,
                'referer'         => false,
                'protocols'       => ['http', 'https'],
                'track_redirects' => true,
                'on_redirect'     => function(\Psr\Http\Message\RequestInterface $req, \Psr\Http\Message\ResponseInterface $res, \Psr\Http\Message\UriInterface $uri) {
                    $redirectUrl = (string)$uri;
                    if (!\App\Services\UrlSecurity::isSafeUrl($redirectUrl)) {
                        throw new \Exception("SSRF Block: Redirect target '{$redirectUrl}' is not permitted.");
                    }
                }
            ]
        ]);

        $proxy = $this->proxyManager->getProxy();
        if ($proxy) {
            $request = $request->withOptions(['proxy' => $proxy]);
        }

        $response = $request->get($url);

        if ($response->status() === 304) {
            throw new \App\Domains\Scrapers\Exceptions\UnchangedContentException("URL {$url} content unchanged (304 Not Modified) via Headless Client.");
        }

        if ($response->failed()) {
            if ($proxy) {
                $this->proxyManager->markFailed($proxy);
            }
            throw new \Exception("Headless Client simulation failed with status: " . $response->status());
        }

        // Store ETag and Last-Modified
        $lastModified = $response->header('Last-Modified');
        $etag = $response->header('ETag');
        if ($lastModified || $etag) {
            $source->update([
                'last_modified' => $lastModified,
                'etag' => $etag,
            ]);
        }

        return $response->body();
    }

    /**
     * Executes the Node.js headless scraper helper using Playwright or Puppeteer.
     *
     * @param string $engine
     * @param ScrapingSource $source
     * @return string
     * @throws \Exception
     */
    protected function executeNodeRunner(string $engine, ScrapingSource $source): string
    {
        $url = $source->source_url;
        $proxy = $this->proxyManager->getProxy();

        $nodePath = 'node'; // Assume node is in path
        $scriptPath = base_path('resources/js/headless-scraper.js');

        $args = [
            $nodePath,
            $scriptPath,
            '--url', $url,
            '--engine', $engine,
        ];

        if ($proxy) {
            $args[] = '--proxy';
            $args[] = $proxy;
        }

        // Run process
        $process = new Process($args);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("Node scraper runner failed execution: " . $process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Resilient Fallback Engine when everything fails.
     *
     * @param ScrapingSource $source
     * @return string
     */
    protected function executeFallback(ScrapingSource $source): string
    {
        Log::error("All scraper engines failed for source: {$source->name}. Mock fallback is DISABLED by security audit.");
        throw new \Exception("Scraping completely failed for source ID: {$source->id}. Mock fallback has been disabled.");
    }

    /**
     * Validate content to ensure it does not contain errors, captchas or Cloudflare blockages.
     *
     * @param string $html
     * @return void
     * @throws \Exception
     */
    protected function validateContent(string $html): void
    {
        if (empty(trim($html))) {
            throw new \Exception("Empty HTML content received.");
        }

        $htmlLower = strtolower($html);

        // Captcha Detection
        if (str_contains($htmlLower, 'g-recaptcha') || 
            str_contains($htmlLower, 'hcaptcha.com') || 
            str_contains($htmlLower, 'cf-turnstile') || 
            str_contains($htmlLower, 'captcha-delivery')) {
            Log::warning("CAPTCHA block detected on target page.");
            throw new \Exception("CAPTCHA Block page detected.");
        }

        // Cloudflare Detection
        if (str_contains($htmlLower, 'cf-ray') && str_contains($htmlLower, 'just a moment...')) {
            throw new \Exception("Cloudflare challenge block page detected.");
        }
    }

    /**
     * Run the feature detector and update the source model in the database.
     *
     * @param ScrapingSource $source
     * @param string $html
     * @return void
     */
    protected function updateDetectedFeatures(ScrapingSource $source, string $html): void
    {
        $features = $this->featureDetector->detect($html);
        $source->update([
            'detected_features' => $features
        ]);
    }
}
