<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\State;
use App\Models\Qualification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyEnhancedDetailsTest extends TestCase
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
     * Test validation rules for store/update.
     */
    public function test_vacancy_details_validation(): void
    {
        $adminUser = User::create([
            'name' => 'Active Admin',
            'email' => 'admin@test.gov.in',
            'phone' => '9999999999',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $invalidData = [
            'title'                 => 'Mock Combined Recruitment 2026 Test Title',
            'category_id'           => 1,
            'department_id'         => 1,
            'state_id'              => 1,
            'qualification_id'      => 1,
            'description'           => 'Job Overview Description that is long enough.',
            'salary_min'            => 35000,
            'salary_max'            => 112000,
            'vacancy_count'         => 10,
            'application_fee'       => 100.00,
            'last_date_to_apply'    => now()->addDays(10)->toDateString(),
            'official_website_link' => 'https://dst.gov.in',
            'vacancy_details' => [
                [
                    'post_name' => '', // Required
                    'total_post' => 'not-a-number', // Should be integer
                    'eligibility' => 'Diploma'
                ]
            ],
            'category_wise_vacancies' => [
                [
                    'post_name' => 'Junior Engineer',
                    'ur' => 'invalid-num', // Should be numeric
                    'ews' => 12,
                    'ebc' => 18,
                    'bc' => 15,
                    'bc_female' => 4,
                    'sc' => 20,
                    'st' => 6,
                    'total' => 120
                ]
            ]
        ];

        $response = $this->actingAs($adminUser, 'api')
            ->postJson(route('admin.jobs.store'), $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'vacancy_details.0.post_name',
            'vacancy_details.0.total_post',
            'category_wise_vacancies.0.ur',
        ]);
    }

    /**
     * Test storing a job post with vacancy details.
     */
    public function test_store_and_sync_vacancies(): void
    {
        $adminUser = User::create([
            'name' => 'Active Admin',
            'email' => 'admin@test.gov.in',
            'phone' => '9999999999',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $data = [
            'title'                 => 'Mock Combined Recruitment 2026 Test Title',
            'category_id'           => 1,
            'department_id'         => 1,
            'state_id'              => 1,
            'qualification_id'      => 1,
            'description'           => 'Job Overview Description that is long enough.',
            'salary_min'            => 35000,
            'salary_max'            => 112000,
            'vacancy_count'         => 120,
            'application_fee'       => 100.00,
            'last_date_to_apply'    => now()->addDays(10)->toDateString(),
            'official_website_link' => 'https://dst.gov.in',
            'vacancy_details' => [
                [
                    'post_name' => 'Junior Engineer',
                    'total_post' => 120,
                    'eligibility' => 'Diploma in Civil Engineering',
                    'sort_order' => 0
                ]
            ],
            'category_wise_vacancies' => [
                [
                    'post_name' => 'Junior Engineer',
                    'ur' => 45,
                    'ews' => 12,
                    'ebc' => 18,
                    'bc' => 15,
                    'bc_female' => 4,
                    'sc' => 20,
                    'st' => 6,
                    'total' => 120,
                    'sort_order' => 0
                ]
            ]
        ];

        // Store
        $response = $this->actingAs($adminUser, 'api')
            ->postJson(route('admin.jobs.store'), $data);

        $response->assertStatus(200);

        $job = JobPost::where('title', 'Mock Combined Recruitment 2026 Test Title')->first();
        $this->assertNotNull($job);
        $this->assertCount(1, $job->vacancyDetails);
        $this->assertCount(1, $job->categoryWiseVacancies);

        $this->assertEquals('Junior Engineer', $job->vacancyDetails[0]->post_name);
        $this->assertEquals(120, $job->vacancyDetails[0]->total_post);
        $this->assertEquals('Diploma in Civil Engineering', $job->vacancyDetails[0]->eligibility);

        $this->assertEquals('Junior Engineer', $job->categoryWiseVacancies[0]->post_name);
        $this->assertEquals(45, $job->categoryWiseVacancies[0]->ur);
        $this->assertEquals(120, $job->categoryWiseVacancies[0]->total);

        // Update with modified vacancy details (test sync)
        $updateData = $data;
        $updateData['title'] = 'Mock Combined Recruitment 2026 Updated';
        $updateData['vacancy_details'] = [
            [
                'post_name' => 'Senior Engineer',
                'total_post' => 80,
                'eligibility' => 'Degree in Civil Engineering',
                'sort_order' => 0
            ]
        ];
        $updateData['category_wise_vacancies'] = [
            [
                'post_name' => 'Senior Engineer',
                'ur' => 30,
                'ews' => 10,
                'ebc' => 10,
                'bc' => 10,
                'bc_female' => 2,
                'sc' => 10,
                'st' => 8,
                'total' => 80,
                'sort_order' => 0
            ]
        ];

        $updateResponse = $this->actingAs($adminUser, 'api')
            ->postJson(route('admin.jobs.update', ['id' => $job->id]), $updateData);

        $updateResponse->assertStatus(200);

        $job->refresh();
        $this->assertCount(1, $job->vacancyDetails);
        $this->assertCount(1, $job->categoryWiseVacancies);
        $this->assertEquals('Senior Engineer', $job->vacancyDetails[0]->post_name);
        $this->assertEquals('Senior Engineer', $job->categoryWiseVacancies[0]->post_name);
        $this->assertEquals(80, $job->categoryWiseVacancies[0]->total);
    }

    /**
     * Test JSON API returns enhanced vacancy details.
     */
    public function test_api_returns_enhanced_vacancies(): void
    {
        $jobPost = JobPost::create([
            'title'                 => 'Mock Combined Recruitment 2026 API Test',
            'slug'                  => 'mock-combined-recruitment-2026-api-test',
            'description'           => 'Job Overview Description',
            'department_id'         => 1,
            'state_id'              => 1,
            'qualification_id'      => 1,
            'category_id'           => 1,
            'post_type'             => 'job',
            'vacancy_count'         => 245,
            'salary_min'            => 35000,
            'salary_max'            => 112000,
            'application_fee'       => 100.00,
            'official_website_link' => 'https://dst.gov.in',
            'apply_link'            => 'https://dst.gov.in',
            'last_date_to_apply'    => '2026-07-20',
            'status'                => 'published',
            'published_at'          => now(),
            'fingerprint'           => 'unique-fingerprint-api-test',
        ]);

        $jobPost->vacancyDetails()->create([
            'post_name' => 'Junior Engineer',
            'total_post' => 120,
            'eligibility' => 'Diploma in Civil Engineering',
            'sort_order' => 0
        ]);

        $jobPost->categoryWiseVacancies()->create([
            'post_name' => 'Junior Engineer',
            'ur' => 45,
            'ews' => 12,
            'ebc' => 18,
            'bc' => 15,
            'bc_female' => 4,
            'sc' => 20,
            'st' => 6,
            'total' => 120,
            'sort_order' => 0
        ]);

        $response = $this->getJson("/api/jobs/{$jobPost->slug}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'post_name'   => 'Junior Engineer',
            'total_post'  => 120,
            'eligibility' => 'Diploma in Civil Engineering',
        ]);
        $response->assertJsonFragment([
            'post_name' => 'Junior Engineer',
            'ur'        => 45,
            'total'     => 120,
        ]);
    }
}
