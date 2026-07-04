<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ScrapingSource;
use App\Models\JobPost;
use App\Domains\Jobs\Services\Contracts\JobServiceInterface;
use App\Domains\Scrapers\Services\ScrapingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected JobServiceInterface $jobService;
    protected ScrapingService $scrapingService;
    protected ScrapingSource $source;
    protected Category $category;
    protected Department $department;
    protected State $state;
    protected Qualification $qualification;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobService = $this->app->make(JobServiceInterface::class);
        $this->scrapingService = $this->app->make(ScrapingService::class);

        // Seed parents
        $this->state = State::create(['name' => 'Delhi', 'code' => 'DL']);
        $this->category = Category::create(['name' => 'SSC Exam Jobs', 'slug' => 'ssc-exam-jobs']);
        $this->department = Department::create(['name' => 'Staff Selection Board', 'code' => 'SSB-01']);
        $this->qualification = Qualification::create(['name' => 'High School Degree', 'slug' => 'high-school']);

        // Scraping Source
        $this->source = ScrapingSource::create([
            'name' => 'Direct Feed Portal',
            'source_url' => 'https://ssb-portal.gov.in/jobs',
            'source_type' => 'html',
            'selectors_config' => [
                'default_category_id' => $this->category->id,
                'default_department_id' => $this->department->id,
                'default_state_id' => $this->state->id,
                'default_qualification_id' => $this->qualification->id
            ]
        ]);
    }

    /**
     * Test Soft Deletes Trait integration
     */
    public function test_soft_deletes_are_supported(): void
    {
        $job = JobPost::create([
            'title' => 'SSB Senior Officer Recruitment 2026',
            'slug' => 'ssb-senior-officer-recruitment-2026',
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'category_id' => $this->category->id,
            'post_type' => 'jobs',
            'description' => 'Recruitment for Senior Officers in SSB.',
            'official_website_link' => 'https://ssb.gov.in',
            'apply_link' => 'https://ssb.gov.in/apply',
            'last_date_to_apply' => '2026-10-31',
            'status' => 'published',
            'is_featured' => false,
            'is_historical' => false,
        ]);

        $this->assertDatabaseHas('job_posts', ['id' => $job->id]);

        // Soft Delete
        $job->delete();

        // Should not be in normal select query output
        $this->assertEquals(0, JobPost::count());
        $this->assertNull(JobPost::find($job->id));

        // Should be in withTrashed query output
        $this->assertNotNull(JobPost::withTrashed()->find($job->id));
        $this->assertTrue($job->trashed());

        // Restore
        $job->restore();
        $this->assertEquals(1, JobPost::count());
        $this->assertFalse($job->trashed());
    }

    /**
     * Test automated data propagation of source_id and expires_at for scraped posts
     */
    public function test_scraped_job_ingestion_propagates_performance_fields(): void
    {
        $rawItem = [
            'title' => 'SSB Inspector General Recruitment 2026',
            'deadline_raw' => '15-11-2026',
            'fee_raw' => 'Rs. 100',
            'official_link' => 'https://ssb.gov.in',
            'apply_link' => 'https://ssb.gov.in/apply-now',
            'raw_text' => 'SSB Inspector General Recruitment 2026. Age: 18-35. Apply before 15-11-2026. Description details.'
        ];

        $reflection = new \ReflectionClass(\App\Domains\Scrapers\Services\ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->scrapingService, [$this->source, $rawItem]);
        $this->assertEquals('success', $result['status']);

        // Assert job has source_id and expires_at properly populated and indexed
        $job = JobPost::where('title', 'SSB Inspector General Recruitment 2026')->first();

        $this->assertNotNull($job);
        $this->assertEquals($this->source->id, $job->source_id);
        $this->assertEquals('2026-11-15', $job->expires_at->format('Y-m-d'));

        // Verify direct belongsTo relation exists
        $this->assertNotNull($job->source);
        $this->assertEquals('Direct Feed Portal', $job->source->name);
    }

    /**
     * Test manual job input sets expires_at field automatically
     */
    public function test_manually_created_job_populates_expires_at(): void
    {
        $manualJob = $this->jobService->createJob([
            'title' => 'SSB Head Constable Recruitment 2026',
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'category_id' => $this->category->id,
            'post_type' => 'jobs',
            'description' => 'Manual job listing description.',
            'official_website_link' => 'https://ssb.gov.in',
            'apply_link' => 'https://ssb.gov.in/apply',
            'last_date_to_apply' => '2026-12-25',
        ]);

        $this->assertNotNull($manualJob->expires_at);
        $this->assertEquals('2026-12-25', $manualJob->expires_at->format('Y-m-d'));
    }

    /**
     * Test the driver-aware fulltext search scope behavior
     */
    public function test_search_scope_finds_jobs_with_keyword(): void
    {
        JobPost::create([
            'title' => 'Special Senior Telecom Analyst Job',
            'slug' => 'special-telecom-analyst-job',
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'category_id' => $this->category->id,
            'post_type' => 'jobs',
            'official_website_link' => 'https://telecom.gov.in',
            'apply_link' => 'https://telecom.gov.in/apply',
            'description' => 'Looking for telecom specialists who can handle communication routing.',
            'last_date_to_apply' => '2026-10-31',
            'status' => 'published',
        ]);

        // Search for title match
        $results = JobPost::search('Special Analyst')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Special Senior Telecom Analyst Job', $results->first()->title);

        // Search for description match
        $resultsDesc = JobPost::search('communication routing')->get();
        $this->assertCount(1, $resultsDesc);
        $this->assertEquals('Special Senior Telecom Analyst Job', $resultsDesc->first()->title);
    }
}
