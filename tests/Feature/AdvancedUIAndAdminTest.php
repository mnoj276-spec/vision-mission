<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\State;
use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\JobPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedUIAndAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $candidate;
    protected User $admin;
    protected Category $category;
    protected Department $department;
    protected State $state;
    protected Qualification $qualification;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed data
        $this->state = State::create(['name' => 'Bihar Region', 'code' => 'BIHAR']);
        $this->category = Category::create(['name' => 'Railways', 'slug' => 'railways']);
        $this->department = Department::create(['name' => 'Railway Board', 'code' => 'RRB']);
        $this->qualification = Qualification::create(['name' => 'Intermediate 12th', 'slug' => 'intermediate']);

        // Create user profiles
        $this->candidate = User::create([
            'name' => 'Candidate User One',
            'email' => 'candidate1@example.com',
            'phone' => '8888888888',
            'password' => bcrypt('password123'),
            'role' => 'candidate',
            'is_active' => true
        ]);

        $this->admin = User::create([
            'name' => 'Head Admin',
            'email' => 'admin@govjobs.com',
            'phone' => '9999999999',
            'password' => bcrypt('Admin@12345'),
            'role' => 'admin',
            'is_active' => true
        ]);
    }

    /**
     * Verify forgot password simulation & verification OTP
     */
    public function test_forgot_password_simulates_otp_and_resets_hash(): void
    {
        // 1. Trigger simulated OTP verification code dispatch
        $otpResponse = $this->postJson('/api/forgot-password', [
            'email' => 'candidate1@example.com'
        ]);

        $otpResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('otp_code', '123456');

        // 2. Trigger password override with the mock OTP
        $resetResponse = $this->postJson('/api/reset-password', [
            'email' => 'candidate1@example.com',
            'otp_code' => '123456',
            'password' => 'newsecurepass',
            'password_confirmation' => 'newsecurepass'
        ]);

        $resetResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // 3. Attempt login with the new password
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'candidate1@example.com',
            'password' => 'newsecurepass'
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * Verify Candidate profile and alert notification settings
     */
    public function test_candidate_profile_updates_and_notification_preferences(): void
    {
        $this->actingAs($this->candidate);

        // 1. Update personal details
        $profileResponse = $this->postJson('/api/profile/update', [
            'name' => 'Candidate User Modified',
            'email' => 'candidate_mod@example.com',
            'phone' => '7777777777',
            'password' => 'modpass123',
            'password_confirmation' => 'modpass123'
        ]);

        $profileResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', [
            'id' => $this->candidate->id,
            'name' => 'Candidate User Modified',
            'email' => 'candidate_mod@example.com'
        ]);

        // 2. Update alerts preference checkboxes
        $prefResponse = $this->postJson('/api/profile/preferences', [
            'email_alerts' => '1',
            'sms_alerts' => '0'
        ]);

        $prefResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Notification alert channels updated successfully!');
    }

    /**
     * Verify Admin direct manual recruitment announcements publishers
     */
    public function test_admin_manual_jobs_publisher_saves_recruitment(): void
    {
        $this->actingAs($this->admin);

        $jobData = [
            'title' => 'RRB Assistant Station Master Exam 2026',
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'description' => 'Comprehensive eligible details for Station Master selections.',
            'salary_min' => 45000,
            'salary_max' => 120000,
            'vacancy_count' => 1500,
            'application_fee' => 250,
            'last_date_to_apply' => now()->addDays(20)->toDateString(),
            'official_website_link' => 'https://rrbpatna.gov.in'
        ];

        $response = $this->postJson('/api/admin/jobs/store', $jobData);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('job_posts', [
            'title' => 'RRB Assistant Station Master Exam 2026',
            'vacancy_count' => 1500,
            'status' => 'published'
        ]);
    }

    /**
     * Verify Admin role elevations & status suspensions
     */
    public function test_admin_role_elevations_and_account_suspensions(): void
    {
        $this->actingAs($this->admin);

        // 1. Promote Candidate to Admin
        $promoteResponse = $this->postJson("/api/admin/users/{$this->candidate->id}/update", [
            'role' => 'admin'
        ]);

        $promoteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', [
            'id' => $this->candidate->id,
            'role' => 'admin'
        ]);

        // 2. Suspend candidate access
        $suspendResponse = $this->postJson("/api/admin/users/{$this->candidate->id}/update", [
            'is_active' => '0'
        ]);

        $suspendResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', [
            'id' => $this->candidate->id,
            'is_active' => false
        ]);

        // 3. Ensure administrator cannot suspend themselves
        $selfLockResponse = $this->postJson("/api/admin/users/{$this->admin->id}/update", [
            'is_active' => '0'
        ]);

        $selfLockResponse->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'You cannot deactivate or alter your own administrator session!');
    }

    /**
     * Verify Admin dynamic SEO setting JSON write caching cycles
     */
    public function test_admin_seo_tags_caching_writes_to_filesystem(): void
    {
        $this->actingAs($this->admin);

        $seoData = [
            'meta_title' => 'Custom Automated Title Tag',
            'meta_description' => 'Custom Meta Description',
            'meta_keywords' => 'custom, keyword, checks'
        ];

        $response = $this->postJson('/api/admin/seo/update', $seoData);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $settingsPath = storage_path('app/seo_settings.json');
        $this->assertFileExists($settingsPath);

        $cachedData = json_decode(file_get_contents($settingsPath), true);
        $this->assertEquals('Custom Automated Title Tag', $cachedData['meta_title']);
        $this->assertEquals('Custom Meta Description', $cachedData['meta_description']);
    }
}
