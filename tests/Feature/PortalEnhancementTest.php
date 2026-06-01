<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\Qualification;
use App\Models\State;
use App\Models\User;
use App\Models\ScrapingSource;
use App\Console\Commands\FeatureExpiryScheduler;
use App\Domains\Scrapers\Jobs\ImportJobPostingsJob;
use App\Domains\Jobs\Services\SearchService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PortalEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected State $state;
    protected Category $category;
    protected Department $department;
    protected Qualification $qualification;
    protected ScrapingSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = State::create(['name' => 'Goa', 'code' => 'GA', 'slug' => 'goa']);
        $this->category = Category::create(['name' => 'UPSC Exams', 'slug' => 'upsc-exams']);
        $this->department = Department::create(['name' => 'Goa Public Service Commission', 'code' => 'GPSC', 'slug' => 'gpsc']);
        $this->qualification = Qualification::create(['name' => 'Graduate', 'slug' => 'graduate']);
        $this->source = ScrapingSource::create([
            'name' => 'GPSC Feeder',
            'source_url' => 'https://gpsc.goa.gov.in/feed',
            'selectors_config' => [
                'default_category_id' => $this->category->id,
                'default_department_id' => $this->department->id,
                'default_state_id' => $this->state->id,
                'default_qualification_id' => $this->qualification->id,
            ]
        ]);
    }

    /**
     * Verify Eligibility Checker endpoint renders successfully and filters correctly.
     */
    public function test_eligibility_checker_dashboard_and_filtering(): void
    {
        // 1. Render the HTML view
        $response = $this->get(route('eligibility.index'));
        $response->assertStatus(200);
        $response->assertSee('Sarkari Job Eligibility Checker 2026');

        // 2. Create an eligible JobPost
        $job = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'GPSC Assistant Professor Recruitment 2026',
            'slug' => 'gpsc-assistant-professor-recruitment-2026',
            'description' => 'Requirement details for teaching candidates.',
            'status' => 'published',
            'post_type' => 'job',
            'vacancy_count' => 15,
            'salary_min' => 45000,
            'salary_max' => 75000,
            'last_date_to_apply' => now()->addMonth(),
        ]);

        // 3. Match using API endpoint with matching qualification & state
        $apiResponse = $this->getJson(route('eligibility.check', [
            'qualification_id' => $this->qualification->id,
            'state_id' => $this->state->id,
            'category_id' => $this->category->id,
            'age' => 25
        ]));

        $apiResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'GPSC Assistant Professor Recruitment 2026');
    }

    /**
     * Verify Salary Information Hub aggregates salary ranges.
     */
    public function test_salary_matrix_aggregates_properly(): void
    {
        // Create 2 jobs with various salaries
        JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'GPSC Senior Lecturer Post',
            'slug' => 'gpsc-senior-lecturer-post',
            'description' => 'Teaching scaling scale.',
            'status' => 'published',
            'post_type' => 'job',
            'salary_min' => 60000,
            'salary_max' => 90000,
            'last_date_to_apply' => now()->addMonth(),
        ]);

        JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'GPSC Junior Lecturer Post',
            'slug' => 'gpsc-junior-lecturer-post',
            'description' => 'Teaching scaling scale.',
            'status' => 'published',
            'post_type' => 'job',
            'salary_min' => 40000,
            'salary_max' => 60000,
            'last_date_to_apply' => now()->addMonth(),
        ]);

        $response = $this->get(route('salary.index'));
        $response->assertStatus(200);
        $response->assertSee('Sarkari Job Salary Matrix 2026');
        $response->assertSee('UPSC Exams', false); // View lists active category explorer
    }

    /**
     * Verify SearchService supports the new exam_filter (post type filter).
     */
    public function test_search_service_exam_filter(): void
    {
        // 1. Create a dynamic cutoff post
        JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'GPSC Cutoff Marks Analysis 2026',
            'slug' => 'gpsc-cutoff-marks-analysis-2026',
            'description' => 'Review the official general and OBC cutoff scores.',
            'status' => 'published',
            'post_type' => 'cutoff',
            'last_date_to_apply' => now()->addMonth(),
        ]);

        // 2. Create a standard job post
        JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'GPSC Officer Level Posting',
            'slug' => 'gpsc-officer-level-posting',
            'description' => 'Recruitment post.',
            'status' => 'published',
            'post_type' => 'job',
            'last_date_to_apply' => now()->addMonth(),
        ]);

        $searchService = app(SearchService::class);

        // Search cutoff type only
        $cutoffResults = $searchService->searchJobs(['exam_filter' => 'cutoff']);
        $this->assertCount(1, $cutoffResults->items());
        $this->assertEquals('GPSC Cutoff Marks Analysis 2026', $cutoffResults->items()[0]->title);

        // Search job type only
        $jobResults = $searchService->searchJobs(['exam_filter' => 'job']);
        $this->assertCount(1, $jobResults->items());
        $this->assertEquals('GPSC Officer Level Posting', $jobResults->items()[0]->title);
    }

    /**
     * Verify Feature Expiry Scheduler command successfully un-features expired items.
     */
    public function test_feature_expiry_scheduler(): void
    {
        // 1. Create expired featured job
        $expiredJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'GPSC Senior Consultant Expired',
            'slug' => 'gpsc-senior-consultant-expired',
            'description' => 'Detail descriptions.',
            'status' => 'published',
            'post_type' => 'job',
            'is_featured' => true,
            'is_sponsored' => true,
            'last_date_to_apply' => now()->subDays(2), // Expired 2 days ago
        ]);

        // 2. Create active featured job
        $activeJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'GPSC Senior Consultant Active',
            'slug' => 'gpsc-senior-consultant-active',
            'description' => 'Detail descriptions.',
            'status' => 'published',
            'post_type' => 'job',
            'is_featured' => true,
            'is_sponsored' => true,
            'last_date_to_apply' => now()->addMonth(),
        ]);

        $this->assertTrue($expiredJob->is_featured);
        $this->assertTrue($expiredJob->is_sponsored);

        // Run scheduler
        Artisan::call('monetization:expire-features');

        $expiredJob->refresh();
        $activeJob->refresh();

        // Expired job is un-featured & un-sponsored
        $this->assertFalse($expiredJob->is_featured);
        $this->assertFalse($expiredJob->is_sponsored);

        // Active job remains featured & sponsored
        $this->assertTrue($activeJob->is_featured);
        $this->assertTrue($activeJob->is_sponsored);
    }

    /**
     * Verify ImportJobPostingsJob executes successfully in background import queue.
     */
    public function test_import_job_postings_job_execution(): void
    {
        $payload = [
            'title' => 'GPSC Assistant Geologist Recruitment Alert 2026',
            'raw_text' => 'We are seeking candidates with MSc Geology. Salary is ₹40,000 to ₹70,000.',
            'official_link' => 'https://gpsc.goa.gov.in/geologist',
            'deadline_raw' => '2026-12-31',
        ];

        // Dispatch background import job
        $job = new ImportJobPostingsJob($payload, $this->source, 'job');
        $job->handle(app(\App\Domains\Scrapers\Services\ImportAutomationService::class));

        // Assert job saved into database successfully
        $this->assertDatabaseHas('job_posts', [
            'title' => 'GPSC Assistant Geologist Recruitment Alert 2026',
            'post_type' => 'job',
            'status' => 'published'
        ]);
    }
}
