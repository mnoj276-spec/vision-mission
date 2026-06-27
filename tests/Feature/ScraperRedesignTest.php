<?php

namespace Tests\Feature;

use App\Domains\Scrapers\Exceptions\RateLimitExceededException;
use App\Domains\Scrapers\Exceptions\UnchangedContentException;
use App\Domains\Scrapers\Jobs\RunWebScraper;
use App\Domains\Scrapers\Services\RequestQueue;
use App\Domains\Scrapers\Services\ScrapingService;
use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\Qualification;
use App\Models\ScrapingLog;
use App\Models\ScrapingSource;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ScraperRedesignTest extends TestCase
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
            'name' => 'Test Redesigned Feed',
            'source_url' => 'https://test-redesign.gov.in/jobs',
            'source_type' => 'html',
            'selectors_config' => [
                'driver' => 'upsc',
                'default_category_id' => $cat->id,
                'default_department_id' => $dept->id,
                'default_state_id' => $state->id,
                'default_qualification_id' => $qual->id
            ],
            'priority' => 'high',
            'crawl_interval_minutes' => 60,
            'is_active' => true,
        ]);
    }

    /**
     * Test priority queue routing of scraping tasks.
     */
    public function test_dynamic_priority_queue_routing(): void
    {
        // 1. High priority source
        $jobHigh = new RunWebScraper($this->source);
        $this->assertEquals('scrapers-high', $jobHigh->queue);

        // 2. Low priority source
        $lowSource = ScrapingSource::create([
            'name' => 'Low Priority Feed',
            'source_url' => 'https://low-priority.gov.in/jobs',
            'source_type' => 'html',
            'selectors_config' => $this->source->selectors_config,
            'priority' => 'low',
            'is_active' => true
        ]);
        
        $jobLow = new RunWebScraper($lowSource);
        $this->assertEquals('scrapers-low', $jobLow->queue);
    }

    /**
     * Test incremental delta crawl returning 304 skip.
     */
    public function test_delta_crawl_304_skip(): void
    {
        $this->source->update([
            'last_modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
            'etag' => 'W/"506971b4"',
        ]);

        Http::fake([
            'https://test-redesign.gov.in/*' => Http::response('', 304)
        ]);

        $service = app(ScrapingService::class);
        $result = $service->scrapeSource($this->source);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['unchanged'] ?? false);
        $this->assertEquals(0, $result['summary']['success']);

        // Assert log has been recorded successfully
        $this->assertDatabaseHas('scraping_logs', [
            'scraping_source_id' => $this->source->id,
            'status'             => 'success',
            'items_found'        => 0,
            'error_message'      => '[Delta Crawl] 304 Not Modified. Content unchanged.'
        ]);
    }

    /**
     * Test ETag and Last-Modified caching on successful 200 HTTP response.
     */
    public function test_caching_etag_and_last_modified_on_200(): void
    {
        Http::fake([
            'https://test-redesign.gov.in/*' => Http::response(
                '<html><body><table><tr class="views-table"><td class="title">UPSC Engineering Services Main Examination 2026</td><td class="last-date">25-09-2026</td></tr></table></body></html>', 
                200, 
                [
                    'Last-Modified' => 'Fri, 26 Jun 2026 12:00:00 GMT',
                    'ETag' => '"12345xyz"'
                ]
            )
        ]);

        $service = app(ScrapingService::class);
        $result = $service->scrapeSource($this->source);

        $this->assertTrue($result['success']);
        $this->source->refresh();

        $this->assertEquals('Fri, 26 Jun 2026 12:00:00 GMT', $this->source->last_modified);
        $this->assertEquals('"12345xyz"', $this->source->etag);
    }

    /**
     * Test adaptive crawl frequency logic.
     */
    public function test_adaptive_crawl_frequency(): void
    {
        $service = app(ScrapingService::class);

        // Configure sequential responses: 304 first (increases interval), then 200 (resets interval)
        Http::fake([
            'https://test-redesign.gov.in/*' => Http::sequence()
                ->push('', 304)
                ->push(
                    '<html><body><table><tr class="views-table"><td class="title">UPSC Engineering Services Main Examination 2026</td><td class="last-date">25-09-2026</td></tr></table></body></html>', 
                    200
                )
        ]);

        // 1. First run: 304 Not Modified. Crawl interval increases by 1.5x (60 -> 90 mins)
        $service->scrapeSource($this->source);
        $this->source->refresh();
        $this->assertEquals(90, $this->source->crawl_interval_minutes);
        $this->assertNotNull($this->source->next_run_at);

        // 2. Second run: 200 OK. Crawl interval resets back to 30 mins
        $service->scrapeSource($this->source);
        $this->source->refresh();
        $this->assertEquals(30, $this->source->crawl_interval_minutes);
    }

    /**
     * Test rate limiter throws exception when attempts exceeded.
     */
    public function test_rate_limiting_exceeded(): void
    {
        $queue = app(RequestQueue::class);
        $url = 'https://test-redesign.gov.in/jobs';

        RateLimiter::clear('scraper:ratelimit:' . md5('test-redesign.gov.in'));

        // Trigger requests up to limits
        for ($i = 0; $i < 30; $i++) {
            $queue->throttle($url);
        }

        // 31st request must trigger RateLimitExceededException
        $this->expectException(RateLimitExceededException::class);
        $queue->throttle($url);
    }

    /**
     * Test dead queue recovery console command.
     */
    public function test_dead_queue_recovery(): void
    {
        // Seed a failed job
        DB::table('failed_jobs')->insert([
            'uuid' => 'test-uuid-1234',
            'connection' => 'sync',
            'queue' => 'scrapers-high',
            'payload' => json_encode([
                'displayName' => 'App\Domains\Scrapers\Jobs\RunWebScraper',
                'job' => 'Illuminate\Queue\CallQueuedHandler@call',
                'data' => [
                    'commandName' => 'App\Domains\Scrapers\Jobs\RunWebScraper',
                    'command' => serialize(new RunWebScraper($this->source))
                ]
            ]),
            'exception' => 'Connection timed out',
            'failed_at' => now()
        ]);

        $exitCode = Artisan::call('scraper:recover-dead-queue');
        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test scaling metrics report console command.
     */
    public function test_scaling_report_command(): void
    {
        // Create a mock log to verify calculations
        ScrapingLog::create([
            'scraping_source_id' => $this->source->id,
            'status'             => 'success',
            'items_found'        => 5,
            'error_message'      => 'Harvested: 5 new.',
            'raw_payload'        => json_encode(['success' => 5])
        ]);

        $exitCode = Artisan::call('scraper:scaling-report');
        $this->assertEquals(0, $exitCode);
    }
}
