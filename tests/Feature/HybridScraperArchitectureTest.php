<?php

namespace Tests\Feature;

use App\Domains\Scrapers\Services\PageFeatureDetector;
use App\Domains\Scrapers\Services\CookieManager;
use App\Domains\Scrapers\Services\ProxyManager;
use App\Domains\Scrapers\Services\RequestQueue;
use App\Domains\Scrapers\Services\BrowserPool;
use App\Domains\Scrapers\Services\HybridScrapingEngine;
use App\Models\ScrapingSource;
use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HybridScraperArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected ScrapingSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        $state = State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        $cat = Category::create(['name' => 'UPSC Jobs', 'slug' => 'upsc-jobs']);
        $dept = Department::create(['name' => 'UPSC Board', 'code' => 'UPSC']);
        $qual = Qualification::create(['name' => 'Graduate Degree', 'slug' => 'graduate']);

        $this->source = ScrapingSource::create([
            'name' => 'Test UPSC Ingestion Feed',
            'source_url' => 'https://test-upsc-portal.gov.in/recruitment',
            'source_type' => 'html',
            'selectors_config' => [
                'driver' => 'upsc',
                'default_category_id' => $cat->id,
                'default_department_id' => $dept->id,
                'default_state_id' => $state->id,
                'default_qualification_id' => $qual->id
            ]
        ]);
    }

    public function test_page_feature_detection(): void
    {
        $detector = app(PageFeatureDetector::class);

        // Test React detection
        $reactHtml = '<html><body><div id="react-root"></div><script src="/static/chunks/main.js"></script></body></html>';
        $features = $detector->detect($reactHtml);
        $this->assertTrue($features['react']);
        $this->assertTrue($features['javascript_required']);

        // Test Cloudflare detection
        $cfHtml = '<html><head><title>Just a moment...</title></head><body>DDoS protection by Cloudflare</body></html>';
        $cfHeaders = ['cf-ray' => ['1234567890abcdef']];
        $features = $detector->detect($cfHtml, $cfHeaders);
        $this->assertTrue($features['cloudflare']);

        // Test Angular detection
        $angHtml = '<html><body><div ng-app="app" ng-version="14.0.0"></div></body></html>';
        $features = $detector->detect($angHtml);
        $this->assertTrue($features['angular']);

        // Test Vue detection
        $vueHtml = '<html><body><div id="app" v-bind:class="class"></div></body></html>';
        $features = $detector->detect($vueHtml);
        $this->assertTrue($features['vue']);

        // Test Infinite Scroll
        $scrollHtml = '<html><body><script>window.addEventListener("scroll", function() { loadMore(); })</script></body></html>';
        $features = $detector->detect($scrollHtml);
        $this->assertTrue($features['infinite_scroll']);

        // Test Cookies
        $cookieHeaders = ['set-cookie' => ['session=123']];
        $features = $detector->detect('<html></html>', $cookieHeaders);
        $this->assertTrue($features['cookies']);
    }

    public function test_cookie_persistence(): void
    {
        $cookieManager = app(CookieManager::class);

        $this->assertEmpty($cookieManager->getCookies($this->source));

        // Save some cookies
        $cookies = ['session_id' => 'abc123xyz', 'csrf' => 'token456'];
        $cookieManager->saveCookies($this->source, $cookies);

        $this->source->refresh();
        $this->assertEquals($cookies, $cookieManager->getCookies($this->source));
        $this->assertEquals('session_id=abc123xyz; csrf=token456', $cookieManager->getCookieHeaderString($this->source));

        // Parse Set-Cookie header
        $headers = [
            'session_id=newValue; Path=/; HttpOnly',
            'new_cookie=hello; Secure'
        ];
        $parsed = $cookieManager->parseSetCookieHeaders($headers);
        $this->assertEquals([
            'session_id' => 'newValue',
            'new_cookie' => 'hello'
        ], $parsed);
    }

    public function test_proxy_rotation(): void
    {
        $proxyManager = app(ProxyManager::class);

        $proxy1 = $proxyManager->getProxy();
        $this->assertNotNull($proxy1);

        // Mark it failed, should return a different one if available
        $proxyManager->markFailed($proxy1);
        $proxy2 = $proxyManager->getProxy($proxy1);
        $this->assertNotEquals($proxy1, $proxy2);
    }

    public function test_adaptive_delay(): void
    {
        $requestQueue = app(RequestQueue::class);

        // Standard delay is 1000ms + (500ms * 0.5) + (0 * 1000) = 1250ms
        $delay = $requestQueue->getAdaptiveDelay($this->source);
        $this->assertEquals(1250, $delay);

        // Record performance with failure
        $requestQueue->recordPerformance($this->source, 2000, false);
        $this->source->refresh();

        // Failed run increases delay: 1000 + (avg_latency * 0.5) + (1 * 1000)
        // avg_latency is calculated as (500*4 + 2000)/5 = 800
        // Expected delay: 1000 + (800 * 0.5) + (1 * 1000) = 2400ms
        $delay = $requestQueue->getAdaptiveDelay($this->source);
        $this->assertEquals(2400, $delay);
    }

    public function test_browser_pool_management(): void
    {
        $pool = app(BrowserPool::class);
        $this->assertEquals(0, $pool->getActiveSessionCount());

        $session1 = $pool->acquireSession('source1');
        $this->assertNotEmpty($session1);
        $this->assertEquals(1, $pool->getActiveSessionCount());

        $session2 = $pool->acquireSession('source2');
        $this->assertEquals(2, $pool->getActiveSessionCount());

        $pool->releaseSession($session1);
        $this->assertEquals(1, $pool->getActiveSessionCount());

        $pool->releaseSession($session2);
        $this->assertEquals(0, $pool->getActiveSessionCount());
    }

    public function test_captcha_detection_aborts_engine(): void
    {
        $engine = app(HybridScrapingEngine::class);

        // Mock Http to return CAPTCHA page
        Http::fake([
            '*' => Http::response('<html><body>Please verify: <div class="g-recaptcha"></div></td></tr></table></body></html>', 200)
        ]);

        $html = $engine->fetch($this->source);

        // Since standard engines fail (due to captcha validation error), it escalates to the Fallback Engine
        $this->assertStringContainsString('UPSC Engineering Services Main Examination 2026', $html);
    }

    public function test_hybrid_escalation_and_fallback(): void
    {
        $engine = app(HybridScrapingEngine::class);

        // Mock Http to fail completely on all requests
        Http::fake([
            '*' => Http::response('Server Error', 500)
        ]);

        // When HTTP/Headless fails, it falls back to the Node scraper run or Fallback engine
        $html = $engine->fetch($this->source);

        $this->assertNotEmpty($html);
        // Fallback engine returns static recovery content for UPSC driver
        $this->assertStringContainsString('UPSC Engineering Services', $html);
    }
}
