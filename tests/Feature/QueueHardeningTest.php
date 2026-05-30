<?php

namespace Tests\Feature;

use App\Domains\Scrapers\Jobs\RunWebScraper;
use App\Domains\Scrapers\Jobs\ProcessScrapedJobNotification;
use App\Models\User;
use App\Models\ScrapingSource;
use App\Models\JobPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

/**
 * QueueHardeningTest
 * 
 * Verifies distributed locking, isolated queue properties,tries, backoffs,
 * scheduler double-locking configs, and administrative metrics / DLQ REST APIs.
 */
class QueueHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $candidate;
    protected ScrapingSource $source;
    protected JobPost $job;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed lookup relations first to satisfy foreign key constraints
        \App\Models\Category::create(['id' => 1, 'name' => 'IT & Software', 'slug' => 'it-software']);
        \App\Models\Department::create(['id' => 1, 'name' => 'Department of Electronics', 'code' => 'DEPT-ELECTRONICS']);
        \App\Models\State::create(['id' => 1, 'name' => 'Delhi', 'code' => 'DL']);
        \App\Models\Qualification::create(['id' => 1, 'name' => 'B.Tech / B.E.', 'slug' => 'btech-be']);

        // Setup users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@govjobs.gov.in',
            'phone' => '9999999999',
            'password' => bcrypt('Admin@12345'),
            'role' => 'admin',
            'is_active' => true
        ]);

        $this->candidate = User::create([
            'name' => 'Candidate User',
            'email' => 'candidate@example.com',
            'phone' => '8888888888',
            'password' => bcrypt('Password123'),
            'role' => 'candidate',
            'is_active' => true
        ]);

        // Setup models
        $this->source = ScrapingSource::create([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://example.gov.in/rss',
            'source_type' => 'html',
            'is_active' => true,
            'selectors_config' => [
                'default_category_id' => 1,
                'default_department_id' => 1,
                'default_state_id' => 1,
                'default_qualification_id' => 1,
            ]
        ]);

        $this->job = JobPost::create([
            'category_id' => 1,
            'department_id' => 1,
            'state_id' => 1,
            'qualification_id' => 1,
            'title' => 'Executive Officer Grade I',
            'slug' => 'executive-officer-grade-i',
            'description' => 'Government recruitment for executive operations.',
            'vacancy_count' => 10,
            'application_fee' => 100.00,
            'official_website_link' => 'https://example.gov.in',
            'apply_link' => 'https://example.gov.in/apply',
            'last_date_to_apply' => '2026-12-31',
            'status' => 'published',
        ]);
    }

    /**
     * Test 1: Verify isolated queues, tries, and backoffs on RunWebScraper
     */
    public function test_web_scraper_job_isolated_properties(): void
    {
        $jobInstance = new RunWebScraper($this->source);

        $this->assertEquals('scrapers', $jobInstance->queue);
        $this->assertEquals(3, $jobInstance->tries);
        $this->assertEquals([10, 30, 60], $jobInstance->backoff());
    }

    /**
     * Test 2: Verify isolated queues, tries, and backoffs on ProcessScrapedJobNotification
     */
    public function test_notification_job_isolated_properties(): void
    {
        $jobInstance = new ProcessScrapedJobNotification($this->job);

        $this->assertEquals('notifications', $jobInstance->queue);
        $this->assertEquals(3, $jobInstance->tries);
        $this->assertEquals([5, 15, 30], $jobInstance->backoff());
    }

    /**
     * Test 3: Verify Scraper distributed Cache Lock blocks concurrent executions
     */
    public function test_scraper_job_distributed_concurrency_lock(): void
    {
        Cache::shouldReceive('lock')
            ->once()
            ->with("scraper:lock:{$this->source->id}", 600)
            ->andReturn(new class {
                public function get() { return false; } // Mock lock is already taken!
            });

        // Capture logs or observe behavior to confirm block
        $jobInstance = new RunWebScraper($this->source);
        
        // This should hit the 'else' block and skip execution
        $jobInstance->handle(app(\App\Domains\Scrapers\Services\ScrapingService::class));
    }

    /**
     * Test 4: Verify Notification distributed Cache Lock blocks concurrent executions
     */
    public function test_notification_job_distributed_concurrency_lock(): void
    {
        Cache::shouldReceive('lock')
            ->once()
            ->with("notification:lock:{$this->job->id}", 300)
            ->andReturn(new class {
                public function get() { return false; } // Mock lock is already taken!
            });

        $jobInstance = new ProcessScrapedJobNotification($this->job);
        
        // Should bypass the notification service trigger
        $jobInstance->handle(app(\App\Domains\Notifications\Services\NotificationService::class));
    }

    /**
     * Test 5: Verify Scheduler overlap prevention settings are registered
     */
    public function test_scheduler_crontab_overlap_prevention(): void
    {
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $events = collect($schedule->events());

        // Find the scraper:run scheduled event
        $scraperEvent = $events->first(function ($event) {
            return str_contains($event->command, 'scraper:run');
        });

        $this->assertNotNull($scraperEvent, "Scheduled command scraper:run was not registered.");
        $this->assertEquals('*/5 * * * *', $scraperEvent->expression);
        $this->assertTrue($scraperEvent->onOneServer, "Single-server flag should be enabled.");
    }

    /**
     * Test 6: Verify Queue Metrics REST API security and payload details
     */
    public function test_admin_queue_metrics_endpoint(): void
    {
        // 1. Candidate must receive 403 Access Denied
        $this->actingAs($this->candidate);
        $response = $this->getJson(route('admin.queues.metrics'));
        $response->assertStatus(403);

        // 2. Admin receives 200 with complete metrics payload
        $this->actingAs($this->admin);
        $response = $this->getJson(route('admin.queues.metrics'));
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'driver',
                    'metrics' => [
                        'pending_scrapers',
                        'pending_notifications',
                        'pending_default',
                        'total_pending',
                        'processing',
                        'failed_dlq',
                        'avg_latency_seconds',
                    ]
                ]
            ]);
    }

    /**
     * Test 7: Verify Admin Failed Jobs (DLQ) Browser & purging APIs
     */
    public function test_admin_dlq_management_operations(): void
    {
        $this->actingAs($this->admin);

        // Seed a mock failed job directly into the failed_jobs database table
        $uuid = 'mock-failed-job-uuid-12345';
        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'redis',
            'queue' => 'scrapers',
            'payload' => json_encode([
                'displayName' => 'App\\Domains\\Scrapers\\Jobs\\RunWebScraper',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            ]),
            'exception' => 'RuntimeException: Network connection lost during RSS parse.',
            'failed_at' => now()->toDateTimeString(),
        ]);

        // 1. Verify getFailedJobs list API
        $response = $this->getJson(route('admin.queues.failed'));
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.uuid', $uuid)
            ->assertJsonPath('data.items.0.job_name', 'RunWebScraper');

        // 2. Verify retryJob api is called (mocking Artisan command)
        Artisan::shouldReceive('call')
            ->once()
            ->with('queue:retry', ['id' => [$uuid]])
            ->andReturn(0);

        $retryResponse = $this->postJson(route('admin.queues.retry', ['uuid' => $uuid]));
        $retryResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // 3. Verify deleteJob api deletes the DLQ item
        Artisan::shouldReceive('call')
            ->once()
            ->with('queue:forget', ['id' => $uuid])
            ->andReturn(0);

        $deleteResponse = $this->deleteJson(route('admin.queues.delete', ['uuid' => $uuid]));
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }
}
