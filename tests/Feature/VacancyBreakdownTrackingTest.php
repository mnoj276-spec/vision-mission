<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ExtractedNotification;
use App\Models\User;
use App\Domains\Scrapers\Services\AIService;
use App\Domains\Extraction\Services\AiStructuringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyBreakdownTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        Category::create(['name' => 'Banking & Finance', 'slug' => 'banking-finance']);
        Department::create(['name' => 'Department of Science and Technology', 'code' => 'DST']);
        Qualification::create(['name' => 'Bachelor of Technology', 'slug' => 'graduate']);
    }

    /**
     * Verify simulator extraction of vacancy breakdown list.
     */
    public function test_simulator_extracts_breakdown_correctly(): void
    {
        $structuringService = new AiStructuringService();
        $text = "Job Title: Junior Engineer Recruitment\n"
            . "Department: Department of Science and Technology\n"
            . "Vacancy Details: Junior Engineer - 120, Assistant - 80, Clerk - 45.\n"
            . "Caste Breakdown: UR - 100, OBC - 80, SC - 40, ST - 25.\n"
            . "Fee: 500\n"
            . "Last Date: 2026-07-15\n"
            . "Official Website: http://dst.gov.in";

        $structured = $structuringService->structureText($text);

        $this->assertEquals(245, $structured['vacancy_count'] ?? 0);
        $this->assertCount(7, $structured['vacancies_breakdown'] ?? []);

        // Assert specific items inside breakdown
        $items = collect($structured['vacancies_breakdown']);
        
        $je = $items->firstWhere('name', 'Junior Engineer');
        $this->assertNotNull($je);
        $this->assertEquals(120, $je['count']);
        $this->assertEquals('post', $je['type']);

        $ur = $items->firstWhere('name', 'UR');
        $this->assertNotNull($ur);
        $this->assertEquals(100, $ur['count']);
        $this->assertEquals('caste_category', $ur['type']);
    }

    /**
     * Test approving notification saves breakdowns to database and updates vacancy_count.
     */
    public function test_approving_saves_breakdowns_to_db_and_recalculates_count(): void
    {
        $adminUser = User::create([
            'name' => 'Active Admin',
            'email' => 'admin@test.gov.in',
            'phone' => '9999999999',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $notification = ExtractedNotification::create([
            'file_path'          => '/path/to/file.pdf',
            'original_filename'  => 'file.pdf',
            'file_type'          => 'pdf',
            'status'             => 'processed',
            'validation_status'  => 'valid',
            'extracted_data'     => [
                'title' => 'Technical Officer Combined Exam 2026',
                'department' => 'Department of Science and Technology',
                'vacancy_count' => 0, // Should be recalculated
                'qualification' => 'Bachelor of Technology',
                'age_limit' => '21 to 30 Years',
                'salary' => 'Rs. 35,400 to Rs. 1,12,400',
                'application_fee' => 500.00,
                'selection_process' => 'Written test and interview.',
                'important_dates' => [
                    'start_date' => '2026-06-10',
                    'last_date_to_apply' => '2026-07-15',
                ],
                'official_website' => 'http://dst.gov.in',
                'vacancies_breakdown' => [
                    ['name' => 'Junior Engineer', 'count' => 120, 'type' => 'post'],
                    ['name' => 'Assistant', 'count' => 80, 'type' => 'post'],
                    ['name' => 'Clerk', 'count' => 45, 'type' => 'post'],
                    ['name' => 'UR', 'count' => 100, 'type' => 'caste_category'],
                    ['name' => 'OBC', 'count' => 80, 'type' => 'caste_category'],
                ]
            ],
        ]);

        $response = $this->actingAs($adminUser, 'api')
            ->postJson(route('api.v1.extraction.approve', ['id' => $notification->id]));

        $response->assertStatus(200);

        // Assert job_posts table contains total sum of posts (120+80+45 = 245)
        $this->assertDatabaseHas('job_posts', [
            'title'         => 'Technical Officer Combined Exam 2026',
            'vacancy_count' => 245,
        ]);

        $jobPost = JobPost::where('title', 'Technical Officer Combined Exam 2026')->first();
        $this->assertNotNull($jobPost);

        // Assert category_vacancies has 5 rows
        $this->assertDatabaseCount('category_vacancies', 5);
        $this->assertDatabaseHas('category_vacancies', [
            'job_post_id'   => $jobPost->id,
            'category_name' => 'Junior Engineer',
            'vacancy_count' => 120,
            'type'          => 'post',
        ]);
        $this->assertDatabaseHas('category_vacancies', [
            'job_post_id'   => $jobPost->id,
            'category_name' => 'UR',
            'vacancy_count' => 100,
            'type'          => 'caste_category',
        ]);
    }

    /**
     * Test job details API returns the category vacancies breakdown list.
     */
    public function test_job_details_api_returns_category_vacancies(): void
    {
        $jobPost = JobPost::create([
            'title'                 => 'Mock Combined Recruitment 2026',
            'slug'                  => 'mock-combined-recruitment-2026',
            'description'           => 'Job Overview Description',
            'department_id'         => 1,
            'state_id'              => 1,
            'qualification_id'      => 1,
            'category_id'           => 1,
            'post_type'             => 'job',
            'vacancy_count'         => 245,
            'application_fee'       => 100.00,
            'official_website_link' => 'http://dst.gov.in',
            'apply_link'            => 'http://dst.gov.in',
            'last_date_to_apply'    => '2026-07-20',
            'status'                => 'published',
            'published_at'          => now(),
            'fingerprint'           => 'unique-fingerprint-12345',
        ]);

        \App\Models\CategoryVacancy::create([
            'job_post_id'   => $jobPost->id,
            'category_name' => 'Assistant',
            'vacancy_count' => 80,
            'type'          => 'post',
        ]);

        // Call show details JSON API
        $response = $this->getJson("/api/jobs/{$jobPost->slug}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'category_name' => 'Assistant',
            'vacancy_count' => 80,
            'type'          => 'post',
        ]);
    }
}
