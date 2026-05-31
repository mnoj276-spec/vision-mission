<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ScrapingSource;
use App\Models\User;
use App\Models\JobAlert;
use App\Models\JobPost;
use App\Jobs\SendEmailJob;
use App\Domains\Scrapers\Services\ResultDetectionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ResultDetectionEngineTest extends TestCase
{
    use RefreshDatabase;

    protected ResultDetectionEngine $engine;
    protected ScrapingSource $source;
    protected User $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->engine = app(ResultDetectionEngine::class);

        // Seed lookups
        $state = State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        $cat = Category::create(['name' => 'UPSC Jobs', 'slug' => 'upsc-jobs']);
        $dept = Department::create(['name' => 'UPSC Board', 'code' => 'UPSC']);
        $qual = Qualification::create(['name' => 'Graduate Degree', 'slug' => 'graduate']);

        // Create standard Scraping Source on allowed domain
        $this->source = ScrapingSource::create([
            'name' => 'UPSC Test Portal',
            'source_url' => 'https://test-upsc-portal.gov.in/recruitment-results',
            'source_type' => 'html',
            'is_active' => true,
            'selectors_config' => [
                'item_selector' => 'div.result-item',
                'title_selector' => 'h3',
                'deadline_selector' => 'span',
                'default_category_id' => $cat->id,
                'default_department_id' => $dept->id,
                'default_state_id' => $state->id,
                'default_qualification_id' => $qual->id
            ]
        ]);

        // Create a candidate user
        $this->candidate = User::create([
            'name' => 'Candidate User',
            'email' => 'candidate@example.com',
            'phone' => '8888888888',
            'password' => bcrypt('Password123'),
            'role' => 'candidate',
            'is_active' => true
        ]);

        // Create a guest job alert subscriber
        JobAlert::create([
            'email' => 'subscriber@example.com',
            'category_name' => 'UPSC Jobs'
        ]);
    }

    /**
     * Test result detection engine monitors sources, detects results,
     * triggers content generation, dispatches alerts and updates sitemaps.
     */
    public function test_result_detection_engine_pipeline_execution(): void
    {
        Queue::fake([SendEmailJob::class]);

        // Mock HTTP response with a valid result page
        Http::fake([
            'https://test-upsc-portal.gov.in/recruitment-results' => Http::response('
                <div class="result-item">
                    <h3>UPSC Civil Services Exam Merit List Result 2026</h3>
                    <span>20-12-2026</span>
                    <a href="https://upsc.gov.in/result.pdf">Download Result</a>
                </div>
            ', 200)
        ]);

        // Execute engine
        $response = $this->engine->run();

        // 1. Assert sources scraped
        $this->assertEquals(1, $response['sources_scraped']);
        $this->assertEquals(1, $response['new_results_count']);

        // 2. Assert result post is created
        $this->assertDatabaseHas('job_posts', [
            'title' => 'UPSC Civil Services Exam Merit List Result 2026',
            'post_type' => 'result',
        ]);

        $createdPost = JobPost::where('post_type', 'result')->first();
        $this->assertNotNull($createdPost);

        // 3. Assert notifications queued for candidate and subscriber
        Queue::assertPushed(SendEmailJob::class, function ($job) use ($createdPost) {
            return $job->campaignType === 'result_alert' &&
                   $job->recipientEmail === 'candidate@example.com' &&
                   in_array($createdPost->id, $job->data['job_ids']);
        });

        Queue::assertPushed(SendEmailJob::class, function ($job) use ($createdPost) {
            return $job->campaignType === 'result_alert' &&
                   $job->recipientEmail === 'subscriber@example.com' &&
                   in_array($createdPost->id, $job->data['job_ids']);
        });
    }

    /**
     * Test that the Artisan console command executes the engine successfully.
     */
    public function test_artisan_command_executes_engine(): void
    {
        Queue::fake([SendEmailJob::class]);

        Http::fake([
            'https://test-upsc-portal.gov.in/recruitment-results' => Http::response('
                <div class="result-item">
                    <h3>UPSC IAS Mains Written Exam Result 2026</h3>
                    <span>20-12-2026</span>
                    <a href="https://upsc.gov.in/result-mains.pdf">Download Result</a>
                </div>
            ', 200)
        ]);

        $this->artisan('scraper:detect-results')
            ->assertSuccessful()
            ->expectsOutputToContain('Initializing Result Detection Engine...')
            ->expectsOutputToContain('Execution complete!')
            ->expectsOutputToContain('New Results Detected: 1');

        $this->assertDatabaseHas('job_posts', [
            'title' => 'UPSC IAS Mains Written Exam Result 2026',
            'post_type' => 'result',
        ]);
    }
}
