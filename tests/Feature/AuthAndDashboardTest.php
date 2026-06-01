<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\State;
use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\JobPost;
use App\Models\ScrapingSource;
use App\Models\ScrapingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $candidate;
    protected User $admin;
    protected JobPost $job;
    protected ScrapingSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed supporting data
        $state = State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        $cat = Category::create(['name' => 'Banking', 'slug' => 'banking']);
        $dept = Department::create(['name' => 'UPSC Board', 'code' => 'UPSC']);
        $qual = Qualification::create(['name' => 'Graduate Degree', 'slug' => 'graduate']);

        // 2. Create Users
        $this->candidate = User::create([
            'name' => 'John Candidate',
            'email' => 'candidate@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'role' => 'candidate',
            'is_active' => true
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@govjobs.com',
            'phone' => '9999999999',
            'password' => bcrypt('Admin@12345'),
            'role' => 'admin',
            'is_active' => true
        ]);

        // 3. Create active Scraper and Job
        $this->source = ScrapingSource::create([
            'name' => 'Recruitment RSS Feed',
            'source_url' => 'https://upsc.gov.in/feed',
            'source_type' => 'html',
            'selectors_config' => [
                'default_category_id' => $cat->id,
                'default_department_id' => $dept->id,
                'default_state_id' => $state->id,
                'default_qualification_id' => $qual->id
            ]
        ]);

        $this->job = JobPost::create([
            'category_id' => $cat->id,
            'department_id' => $dept->id,
            'state_id' => $state->id,
            'qualification_id' => $qual->id,
            'title' => 'RBI Assistant Selection Examination',
            'slug' => 'rbi-assistant-selection-examination',
            'description' => 'Recruitment for RBI Assistant posts in departments.',
            'vacancy_count' => 150,
            'application_fee' => 450.00,
            'official_website_link' => 'https://rbi.org.in',
            'apply_link' => 'https://rbi.org.in/apply',
            'last_date_to_apply' => now()->addDays(30)->toDateString(),
            'status' => 'published',
            'is_featured' => true
        ]);
    }

    /**
     * Test AJAX Login and Registration behaviors
     */
    public function test_candidate_registration_and_login_validation(): void
    {
        // 1. Test Registration
        $regResponse = $this->postJson(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '9876543210',
            'password' => 'secret123',
            'password_confirmation' => 'secret123'
        ]);

        $regResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

        // 2. Test Login
        $loginResponse = $this->postJson(route('login'), [
            'email' => 'candidate@example.com',
            'password' => 'password123'
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // 3. Test Invalid Login
        $failResponse = $this->postJson(route('login'), [
            'email' => 'candidate@example.com',
            'password' => 'wrongpassword'
        ]);

        $failResponse->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    /**
     * Test bookmarked job lists and bookmark status toggles
     */
    public function test_bookmark_toggling_requires_auth_and_persists(): void
    {
        // Unauthenticated user bookmark should fail
        $guestResponse = $this->postJson(route('jobs.bookmark', ['id' => $this->job->id]));
        $guestResponse->assertStatus(401);

        // Authenticated candidate bookmark toggle
        $this->actingAs($this->candidate);
        
        // 1. Add Bookmark
        $addResponse = $this->postJson(route('jobs.bookmark', ['id' => $this->job->id]));
        $addResponse->assertStatus(200)
            ->assertJsonPath('action', 'added');

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->candidate->id,
            'job_post_id' => $this->job->id
        ]);

        // 2. Remove Bookmark (Toggle off)
        $removeResponse = $this->postJson(route('jobs.bookmark', ['id' => $this->job->id]));
        $removeResponse->assertStatus(200)
            ->assertJsonPath('action', 'removed');

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $this->candidate->id,
            'job_post_id' => $this->job->id
        ]);
    }

    /**
     * Test job applications with resume PDF files
     */
    public function test_job_application_submits_resume_correctly(): void
    {
        Storage::fake('public');
        $this->actingAs($this->candidate);

        // Generate fake resume file with valid PDF magic bytes
        $resumeFile = UploadedFile::fake()->createWithContent('my_resume.pdf', "%PDF-1.4\n" . str_repeat('A', 500));

        $response = $this->postJson(route('jobs.apply', ['id' => $this->job->id]), [
            'resume' => $resumeFile
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('job_applications', [
            'user_id' => $this->candidate->id,
            'job_post_id' => $this->job->id,
            'status' => 'applied'
        ]);
    }

    /**
     * Test admin permissions and override rescues on quarantined items
     */
    public function test_admin_control_restrictions_and_quarantine_rescues(): void
    {
        // 1. Verify candidate receives 403 on admin endpoints
        $this->actingAs($this->candidate);
        $candidateDataResponse = $this->getJson(route('admin.data'));
        $candidateDataResponse->assertStatus(403);

        // 2. Verify Admin gets successful analytics
        $this->actingAs($this->admin);
        $adminDataResponse = $this->getJson(route('admin.data'));
        $adminDataResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // 3. Create a quarantined record
        $quarantineLog = ScrapingLog::create([
            'scraping_source_id' => $this->source->id,
            'status' => 'quarantined',
            'raw_payload' => [
                'title' => 'Short Title',
                'deadline_raw' => '12/12/2026',
                'fee_raw' => 'Rs 0',
                'raw_text' => 'Incomplete raw scraper capture payload.'
            ],
            'validation_errors' => ['urls' => 'A valid official website or apply link must be present.']
        ]);

        // 4. Admin rescues the quarantined item with valid inputs
        $rescueResponse = $this->postJson(route('admin.quarantine.rescue', ['id' => $quarantineLog->id]), [
            'title' => 'UPSC Senior Administrative Officer Recruitment 2026',
            'last_date_to_apply' => now()->addDays(40)->toDateString(),
            'official_website_link' => 'https://upsc.gov.in',
            'apply_link' => 'https://upsconline.nic.in',
            'application_fee' => 100.00,
            'vacancy_count' => 54
        ]);

        $rescueResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Confirm new JobPost was generated
        $this->assertDatabaseHas('job_posts', [
            'title' => 'UPSC Senior Administrative Officer Recruitment 2026',
            'status' => 'published',
            'vacancy_count' => 54
        ]);

        // Confirm quarantined log updated to success
        $this->assertDatabaseHas('scraping_logs', [
            'id' => $quarantineLog->id,
            'status' => 'success'
        ]);
    }

    /**
     * Test admin user registry access under strict lazy loading prevention.
     */
    public function test_admin_user_registry_access_under_lazy_loading_prevention(): void
    {
        // Act as admin
        $this->actingAs($this->admin);

        // Access the user access panel API endpoint
        $response = $this->getJson(route('admin.users.list'));

        // Assert response status is 200 OK and structure is correct
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'users' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone',
                            'role',
                            'is_active',
                        ]
                    ]
                ]
            ]);

        // Assert that the listed roles are returned correctly
        $users = $response->json('data.users');
        $this->assertNotEmpty($users);

        // Find the admin user in the returned list and assert their role is 'admin'
        $adminInList = collect($users)->firstWhere('email', $this->admin->email);
        $this->assertNotNull($adminInList);
        $this->assertEquals('admin', $adminInList['role']);
    }
}
