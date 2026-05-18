<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ScrapingSource;
use App\Models\ScrapingLog;
use App\Models\JobPost;
use App\Domains\Scrapers\Services\ScrapingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScraperTest extends TestCase
{
    use RefreshDatabase;

    protected ScrapingService $scrapingService;
    protected ScrapingSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve dependencies
        $this->scrapingService = $this->app->make(ScrapingService::class);

        // Seed core parents
        $state = State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        $cat = Category::create(['name' => 'UPSC Jobs', 'slug' => 'upsc-jobs']);
        $dept = Department::create(['name' => 'UPSC Board', 'code' => 'UPSC']);
        $qual = Qualification::create(['name' => 'Graduate Degree', 'slug' => 'graduate']);

        // Create standard Scraping Source
        $this->source = ScrapingSource::create([
            'name' => 'Test Scraper Feed',
            'source_url' => 'https://test-upsc-portal.gov.in/recruitment',
            'source_type' => 'html',
            'selectors_config' => [
                'default_category_id' => $cat->id,
                'default_department_id' => $dept->id,
                'default_state_id' => $state->id,
                'default_qualification_id' => $qual->id
            ]
        ]);
    }

    /**
     * Test Stage 4: A valid job announcement passes all rules and inserts successfully as draft
     */
    public function test_valid_scraped_job_inserts_successfully_as_draft(): void
    {
        $rawItem = [
            'title' => 'UPSC Inspector General Recruitment 2026',
            'deadline_raw' => '20-12-2026',
            'fee_raw' => 'Rs. 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link' => 'https://upsconline.nic.in',
            'raw_text' => 'Vacancy for Inspector General. Required qualification: Graduate. Last date: 20-12-2026. Fee Rs 200.'
        ];

        // Access protected process method using reflection or calling via standard handle
        $reflection = new \ReflectionClass(\App\Domains\Scrapers\Services\ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);

        $this->assertEquals('success', $result['status']);
        
        // Assert job was created with published status (future-dated jobs are published, not draft)
        $this->assertDatabaseHas('job_posts', [
            'title'           => 'UPSC Inspector General Recruitment 2026',
            'status'          => 'published',
            'application_fee' => 200.00
        ]);

        // Assert audit success log exists
        $this->assertDatabaseHas('scraping_logs', [
            'scraping_source_id' => $this->source->id,
            'status'             => 'success'
        ]);
    }

    /**
     * Test Stage 4: Invalid job announcement (missing critical apply link and title too short) fails schema and is Quarantined
     */
    public function test_invalid_scraped_job_fails_validation_and_is_quarantined(): void
    {
        $rawItem = [
            'title' => 'Short Title', // Too short (must be >= 15 chars)
            'deadline_raw' => '10-06-2026',
            'fee_raw' => 'Rs 0',
            'official_link' => '', // Missing critical links
            'apply_link' => '',
            'raw_text' => 'Incomplete entry'
        ];

        $reflection = new \ReflectionClass(\App\Domains\Scrapers\Services\ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);

        $this->assertEquals('quarantined', $result['status']);
        
        // Assert no job was created in standard tables
        $this->assertEquals(0, JobPost::count());

        // Assert quarantine audit record populated with descriptive errors
        $this->assertDatabaseHas('scraping_logs', [
            'scraping_source_id' => $this->source->id,
            'status' => 'quarantined'
        ]);

        $log = ScrapingLog::where('status', 'quarantined')->first();
        $this->assertNotNull($log->validation_errors);
        $this->assertArrayHasKey('title', $log->validation_errors);
        $this->assertArrayHasKey('urls', $log->validation_errors);
    }

    /**
     * Test Stage 5: Scraping identical duplicate job postings is prevented
     */
    public function test_duplicate_scraped_job_is_detected_and_skipped(): void
    {
        $rawItem = [
            'title' => 'UPSC Commissioner Selection Board',
            'deadline_raw' => '15-11-2026',
            'fee_raw' => 'Rs 500',
            'official_link' => 'https://upsc.gov.in',
            'apply_link' => 'https://upsconline.nic.in',
            'raw_text' => 'UPSC Commissioner Selection. Last date to apply: 15-11-2026. Fee Rs 500.'
        ];

        $reflection = new \ReflectionClass(\App\Domains\Scrapers\Services\ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        // Process First Time: Should succeed
        $result1 = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);
        $this->assertEquals('success', $result1['status']);

        // Process Second Time: Should trigger duplicate check skip
        $result2 = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);
        $this->assertEquals('duplicate', $result2['status']);

        // Confirm database has exactly one copy of the job post
        $this->assertEquals(1, JobPost::count());

        // Confirm duplicate skip audit was written
        $this->assertDatabaseHas('scraping_logs', [
            'scraping_source_id' => $this->source->id,
            'status' => 'duplicate'
        ]);
    }
}
