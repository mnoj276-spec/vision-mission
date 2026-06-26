<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ScrapingSource;
use App\Models\JobPost;
use App\Domains\Jobs\Services\RecruitmentLifecycleManager;
use App\Domains\Scrapers\Services\ScrapingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class RecruitmentLifecycleTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected ScrapingService $scrapingService;
    protected ScrapingSource $source;
    protected JobPost $parentPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scrapingService = $this->app->make(ScrapingService::class);

        // Seed basic dependencies
        $state = State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        $cat = Category::create(['name' => 'UPSC Jobs', 'slug' => 'upsc-jobs']);
        $dept = Department::create(['name' => 'UPSC Board', 'code' => 'UPSC']);
        $qual = Qualification::create(['name' => 'Graduate Degree', 'slug' => 'graduate']);

        $this->source = ScrapingSource::create([
            'name' => 'Test Ingestion Source',
            'source_url' => 'https://test-upsc-portal.gov.in/recruitment',
            'source_type' => 'html',
            'selectors_config' => [
                'default_category_id' => $cat->id,
                'default_department_id' => $dept->id,
                'default_state_id' => $state->id,
                'default_qualification_id' => $qual->id
            ]
        ]);

        // Create a root parent job post
        $this->parentPost = JobPost::create([
            'department_id' => $dept->id,
            'state_id' => $state->id,
            'qualification_id' => $qual->id,
            'category_id' => $cat->id,
            'source_id' => $this->source->id,
            'post_type' => 'job',
            'title' => 'UPSC Assistant Director Recruitment 2026',
            'slug' => 'upsc-assistant-director-recruitment-2026',
            'description' => 'Recruitment for UPSC Assistant Director.',
            'last_date_to_apply' => '2026-12-01',
            'expires_at' => '2026-12-01',
            'status' => 'published',
            'published_at' => '2026-06-01 10:00:00',
            'fingerprint' => 'parent-fingerprint-hash-xyz'
        ]);
    }

    /**
     * Test that child notices automatically attach to parent via fuzzy/variant title matches.
     */
    public function test_child_notices_automatically_attach_to_parent(): void
    {
        $rawAdmitCard = [
            'title' => 'UPSC Assistant Director Admit Card 2026',
            'deadline_raw' => '2026-12-01',
            'fee_raw' => 'Rs. 0',
            'official_link' => 'https://test-upsc-portal.gov.in/recruitment',
            'apply_link' => 'https://test-upsc-portal.gov.in/admit-card',
            'raw_text' => 'Download Admit Card for Assistant Director post.',
            'department_name' => 'UPSC Board'
        ];

        // Call scraping service processing logic (via reflection)
        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->scrapingService, [$this->source, $rawAdmitCard]);

        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['linked'] ?? false);

        $childPost = JobPost::where('title', 'UPSC Assistant Director Admit Card 2026')->first();
        $this->assertNotNull($childPost);
        $this->assertEquals($this->parentPost->id, $childPost->parent_id);
    }

    /**
     * Test the state machine transitions in RecruitmentLifecycleManager for all event types.
     */
    public function test_lifecycle_state_machine_transitions(): void
    {
        $lifecycleManager = app(RecruitmentLifecycleManager::class);

        // Reset parent status
        $this->parentPost->update(['status' => 'published']);

        // 1. Admit Card Released
        $childAdmit = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'admit_card',
            'title' => 'UPSC Assistant Director Admit Card 2026',
            'slug' => 'upsc-assistant-director-admit-card-2026',
            'description' => 'Admit Card for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childAdmit);
        $this->parentPost->refresh();
        $this->assertEquals('admit_card_released', $this->parentPost->status);

        // 2. Answer Key Released
        $childAnswerKey = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'answer_key',
            'title' => 'UPSC Assistant Director Answer Key 2026',
            'slug' => 'upsc-assistant-director-answer-key-2026',
            'description' => 'Answer Key for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childAnswerKey);
        $this->parentPost->refresh();
        $this->assertEquals('answer_key_released', $this->parentPost->status);

        // 3. Document Verification
        $childDV = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'notice',
            'title' => 'UPSC Assistant Director Document Verification Schedule',
            'slug' => 'upsc-assistant-director-dv-schedule',
            'description' => 'DV Schedule for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childDV);
        $this->parentPost->refresh();
        $this->assertEquals('dv_schedule', $this->parentPost->status);

        // 4. Interview Schedule
        $childInterview = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'notice',
            'title' => 'UPSC Assistant Director Personality Test Interview Viva Schedule',
            'slug' => 'upsc-assistant-director-interview-schedule',
            'description' => 'Interview Schedule for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childInterview);
        $this->parentPost->refresh();
        $this->assertEquals('interview_schedule', $this->parentPost->status);

        // 5. Medical Examination
        $childMedical = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'notice',
            'title' => 'UPSC Assistant Director Medical Exam Call Letter',
            'slug' => 'upsc-assistant-director-medical-exam',
            'description' => 'Medical Exam for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childMedical);
        $this->parentPost->refresh();
        $this->assertEquals('medical_exam', $this->parentPost->status);

        // 6. Result Declared
        $childResult = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'result',
            'title' => 'UPSC Assistant Director Selection Result Merit List',
            'slug' => 'upsc-assistant-director-result',
            'description' => 'Result declared for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childResult);
        $this->parentPost->refresh();
        $this->assertEquals('result_declared', $this->parentPost->status);

        // 7. Joining / Final Appointment
        $childJoining = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'notice',
            'title' => 'UPSC Assistant Director Appointment Order Joining Instructions',
            'slug' => 'upsc-assistant-director-joining',
            'description' => 'Joining information for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childJoining);
        $this->parentPost->refresh();
        $this->assertEquals('final_selection', $this->parentPost->status);

        // 8. Cancellation Notice
        $childCancel = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'notice',
            'title' => 'UPSC Assistant Director Cancellation Postponement Notice 2026',
            'slug' => 'upsc-assistant-director-cancellation',
            'description' => 'Cancellation Notice for Assistant Director.',
            'status' => 'published',
        ]);
        $lifecycleManager->transition($this->parentPost, $childCancel);
        $this->parentPost->refresh();
        $this->assertEquals('archived', $this->parentPost->status);
    }

    /**
     * Test that date extensions update last_date_to_apply and expires_at.
     */
    public function test_date_extension_updates_parent_dates(): void
    {
        $lifecycleManager = app(RecruitmentLifecycleManager::class);

        // Pre-assert current parent dates
        $this->assertEquals('2026-12-01', $this->parentPost->last_date_to_apply->format('Y-m-d'));
        $this->assertEquals('2026-12-01', $this->parentPost->expires_at->format('Y-m-d'));

        // Extension Notice with extended date
        $childExtension = JobPost::create([
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'notice',
            'title' => 'UPSC Assistant Director Date Extension Reopen Notice',
            'slug' => 'upsc-assistant-director-date-extension',
            'description' => 'Date extension for Assistant Director application.',
            'last_date_to_apply' => '2026-12-15',
            'expires_at' => '2026-12-15',
            'status' => 'published',
        ]);

        $lifecycleManager->transition($this->parentPost, $childExtension);
        $this->parentPost->refresh();

        // Assert that both the apply date and expires date were bumped
        $this->assertEquals('2026-12-15', $this->parentPost->last_date_to_apply->format('Y-m-d'));
        $this->assertEquals('2026-12-15', $this->parentPost->expires_at->format('Y-m-d'));
        $this->assertEquals('published', $this->parentPost->status);
    }

    /**
     * Test the historical timeline REST API GET /api/v1/jobs/{id}/timeline.
     */
    public function test_timeline_endpoint_returns_sorted_history(): void
    {
        // 1. Create multiple timeline event posts linked to the parent
        $child1 = JobPost::create([
            'parent_id' => $this->parentPost->id,
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'admit_card',
            'title' => 'UPSC Assistant Director Admit Card 2026',
            'slug' => 'upsc-assistant-director-admit-card-2026',
            'description' => 'Admit Card description.',
            'published_at' => '2026-07-01 09:00:00',
            'status' => 'published',
        ]);

        $child2 = JobPost::create([
            'parent_id' => $this->parentPost->id,
            'department_id' => $this->parentPost->department_id,
            'state_id' => $this->parentPost->state_id,
            'qualification_id' => $this->parentPost->qualification_id,
            'category_id' => $this->parentPost->category_id,
            'post_type' => 'result',
            'title' => 'UPSC Assistant Director Selection Result 2026',
            'slug' => 'upsc-assistant-director-result-2026',
            'description' => 'Result description.',
            'published_at' => '2026-08-01 12:00:00',
            'status' => 'published',
        ]);

        // 2. Query the timeline API endpoint by passing the parent ID
        $response = $this->getJson(route('api.v1.jobs.timeline', ['id' => $this->parentPost->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'recruitment_id',
                    'recruitment_title',
                    'current_status',
                    'timeline' => [
                        '*' => [
                            'id',
                            'title',
                            'slug',
                            'post_type',
                            'status',
                            'published_at',
                            'last_date_to_apply',
                            'official_website_link',
                            'apply_link',
                        ]
                    ]
                ],
                'message'
            ]);

        $data = $response->json('data.timeline');
        $this->assertCount(3, $data); // Parent + 2 Children

        // Check chronological order by published_at
        $this->assertEquals($this->parentPost->id, $data[0]['id']);
        $this->assertEquals($child1->id, $data[1]['id']);
        $this->assertEquals($child2->id, $data[2]['id']);

        // 3. Query the timeline API endpoint by passing one of the child IDs
        $responseChild = $this->getJson(route('api.v1.jobs.timeline', ['id' => $child1->id]));
        $responseChild->assertStatus(200);
        $this->assertEquals($this->parentPost->id, $responseChild->json('data.recruitment_id'));
    }

    /**
     * Test the timeline endpoint returns 404 for non-existent IDs.
     */
    public function test_timeline_endpoint_returns_404_if_not_found(): void
    {
        $response = $this->getJson(route('api.v1.jobs.timeline', ['id' => 999999]));
        $response->assertStatus(404);
    }
}
