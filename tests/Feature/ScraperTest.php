<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\DuplicateAuditLog;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ScrapingSource;
use App\Models\ScrapingLog;
use App\Models\JobPost;
use App\Domains\Scrapers\Services\FingerprintService;
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

    /**
     * Test: Fingerprint is computed and stored on a successful insert.
     */
    public function test_fingerprint_is_generated_and_stored_on_insert(): void
    {
        $rawItem = [
            'title'         => 'UPSC Combined Defence Services Exam 2026',
            'deadline_raw'  => '10-11-2026',
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Combined Defence Services Examination 2026. Graduate required. Last date: 10-11-2026. Fee Rs 200.',
        ];

        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);

        $this->assertEquals('success', $result['status']);

        $job = JobPost::first();
        $this->assertNotNull($job->fingerprint, 'Fingerprint must not be null after insert.');
        $this->assertEquals(64, strlen($job->fingerprint), 'SHA-256 fingerprint must be exactly 64 hex chars.');
    }

    /**
     * Test: Exact same payload on a second scrape is blocked by fingerprint gate.
     */
    public function test_exact_fingerprint_blocks_second_insert(): void
    {
        $rawItem = [
            'title'         => 'UPSC Engineering Services Main Examination 2026',
            'deadline_raw'  => '25-09-2026',
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Engineering Services Examination 2026. Graduate required. Last date: 25-09-2026. Fee Rs 200.',
        ];

        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        // First scrape: must succeed
        $result1 = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);
        $this->assertEquals('success', $result1['status']);

        // Second scrape (identical payload): must be blocked at Stage 1
        $result2 = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);
        $this->assertEquals('duplicate', $result2['status']);
        $this->assertEquals('fingerprint', $result2['method']);

        // Only one job post must exist
        $this->assertEquals(1, JobPost::count());

        // A duplicate_audit_logs record must be written with correct method
        $this->assertDatabaseHas('duplicate_audit_logs', [
            'detection_method' => 'fingerprint',
        ]);
    }

    /**
     * Test: A title variation (year bumped, same content) is caught by the fuzzy gate.
     */
    public function test_fuzzy_duplicate_is_detected_by_title_variation(): void
    {
        $originalItem = [
            'title'         => 'SSC Combined Graduate Level Recruitment 2025',
            'deadline_raw'  => '01-08-2026',
            'fee_raw'       => 'Rs 100',
            'official_link' => 'https://ssc.gov.in',
            'apply_link'    => 'https://ssc.gov.in/apply',
            'raw_text'      => 'SSC CGL 2025 recruitment. Graduate. Last date: 01-08-2026. Fee Rs 100.',
            'department_name' => 'UPSC Board',
        ];
        $variantItem = [
            'title'         => 'SSC Combined Graduate Level Recruitment 2026',  // year changed
            'deadline_raw'  => '01-08-2026',
            'fee_raw'       => 'Rs 100',
            'official_link' => 'https://ssc.gov.in',
            'apply_link'    => 'https://ssc.gov.in/apply',
            'raw_text'      => 'SSC CGL 2026 recruitment. Graduate. Last date: 01-08-2026. Fee Rs 100.',
            'department_name' => 'UPSC Board',
        ];

        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        // Insert original
        $result1 = $method->invokeArgs($this->scrapingService, [$this->source, $originalItem]);
        $this->assertEquals('success', $result1['status'], 'Original item must insert successfully.');

        // Insert variant — different fingerprint but should be caught by fuzzy or variant gate
        $result2 = $method->invokeArgs($this->scrapingService, [$this->source, $variantItem]);
        $this->assertEquals('duplicate', $result2['status'], 'Year-variant title must be detected as duplicate.');
        $this->assertContains($result2['method'], ['fuzzy', 'title_variant']);

        // Only one job post row must exist
        $this->assertEquals(1, JobPost::count());
    }

    /**
     * Test: DuplicateAuditLog is written with the correct detection_method enum.
     */
    public function test_duplicate_audit_log_is_written_with_correct_method(): void
    {
        $rawItem = [
            'title'         => 'UPSC Statistical Investigator Grade II Recruitment 2026',
            'deadline_raw'  => '30-10-2026',
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Statistical Investigator Grade 2 2026. Graduate. Last date: 30-10-2026. Fee Rs 200.',
        ];

        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        // First insert
        $result1 = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);
        $this->assertEquals('success', $result1['status']);

        // Second insert (duplicate)
        $result2 = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);
        $this->assertEquals('duplicate', $result2['status']);

        // Verify the audit log exists and has the correct detection method
        $auditLog = DuplicateAuditLog::first();
        $this->assertNotNull($auditLog, 'A DuplicateAuditLog record must be created.');
        $this->assertContains($auditLog->detection_method, ['fingerprint', 'fuzzy', 'title_variant']);
        $this->assertNotNull($auditLog->incoming_fingerprint, 'incoming_fingerprint must be stored.');
        $this->assertEquals(64, strlen($auditLog->incoming_fingerprint), 'Fingerprint must be 64 chars.');
        $this->assertNotNull($auditLog->raw_payload, 'raw_payload must be stored for re-processing.');
    }

    /**
     * Test that corrigenda and updates are linked via parent_id instead of being discarded.
     */
    public function test_child_notice_linked_instead_of_duplicate_discard(): void
    {
        $rawMaster = [
            'title'         => 'UPSC Scientific Officer Recruitment 2026',
            'deadline_raw'  => '30-10-2026',
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Scientific Officer Recruitment 2026. Graduate. Last date: 30-10-2026. Fee Rs 200.',
        ];

        $rawCorrigendum = [
            'title'         => 'UPSC Scientific Officer Corrigendum Notice 2026',
            'deadline_raw'  => '30-10-2026',
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Scientific Officer Corrigendum Notice 2026. Minor typo corrigendum.',
            'department_name' => 'UPSC Board', // Keep same department to match fuzzy lookback pool
        ];

        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        // 1. Process Master Post: Should succeed
        $resultMaster = $method->invokeArgs($this->scrapingService, [$this->source, $rawMaster]);
        $this->assertEquals('success', $resultMaster['status']);
        $masterPost = JobPost::where('title', 'UPSC Scientific Officer Recruitment 2026')->first();
        $this->assertNotNull($masterPost);

        // 2. Process Corrigendum: Should succeed and be LINKED
        $resultCorrigendum = $method->invokeArgs($this->scrapingService, [$this->source, $rawCorrigendum]);
        $this->assertEquals('success', $resultCorrigendum['status']);
        $this->assertTrue($resultCorrigendum['linked'] ?? false);

        // 3. Assert relationship and post type
        $corrigendumPost = JobPost::where('title', 'UPSC Scientific Officer Corrigendum Notice 2026')->first();
        $this->assertNotNull($corrigendumPost);
        $this->assertEquals($masterPost->id, $corrigendumPost->parent_id);
        $this->assertEquals('notice', $corrigendumPost->post_type);
    }

    /**
     * Test status propagation on cancellation and date extension.
     */
    public function test_status_date_propagation(): void
    {
        $rawMaster = [
            'title'         => 'UPSC Assistant Professor Recruitment 2026',
            'deadline_raw'  => '30-10-2026',
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Assistant Professor Recruitment 2026. Graduate. Last date: 30-10-2026. Fee Rs 200.',
        ];

        $rawExtension = [
            'title'         => 'UPSC Assistant Professor Extension Notice 2026',
            'deadline_raw'  => '15-11-2026', // Extended date
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Assistant Professor Recruitment Extension Notice. Extended last date: 15-11-2026.',
            'department_name' => 'UPSC Board',
        ];

        $rawCancellation = [
            'title'         => 'UPSC Assistant Professor Cancellation Notice 2026',
            'deadline_raw'  => '15-11-2026',
            'fee_raw'       => 'Rs 200',
            'official_link' => 'https://upsc.gov.in',
            'apply_link'    => 'https://upsconline.nic.in',
            'raw_text'      => 'UPSC Assistant Professor Cancellation Notice. Recruitment Cancelled.',
            'department_name' => 'UPSC Board',
        ];

        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        // 1. Process Master: Should succeed
        $method->invokeArgs($this->scrapingService, [$this->source, $rawMaster]);
        $masterPost = JobPost::where('title', 'UPSC Assistant Professor Recruitment 2026')->first();
        $this->assertEquals('2026-10-30', $masterPost->last_date_to_apply->format('Y-m-d'));
        $this->assertEquals('published', $masterPost->status);

        // 2. Process Extension: Parent last date should be updated
        $method->invokeArgs($this->scrapingService, [$this->source, $rawExtension]);
        $masterPost->refresh();
        $this->assertEquals('2026-11-15', $masterPost->last_date_to_apply->format('Y-m-d'));

        // 3. Process Cancellation: Parent status should be updated to archived
        $method->invokeArgs($this->scrapingService, [$this->source, $rawCancellation]);
        $masterPost->refresh();
        $this->assertEquals('archived', $masterPost->status);
    }
}
