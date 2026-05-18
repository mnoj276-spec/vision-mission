<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\JobPost;
use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use App\Services\UrlSecurity;
use App\Services\HtmlSanitizer;
use App\Services\AntivirusScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $candidateUser;
    protected JobPost $jobPost;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard seeders
        $category = Category::create(['name' => 'IT Jobs', 'slug' => 'it-jobs']);
        $dept = Department::create(['name' => 'NIC Center', 'code' => 'NIC']);
        $state = State::create(['name' => 'Delhi', 'code' => 'DL']);
        $qual = Qualification::create(['name' => 'B.Tech', 'slug' => 'btech']);

        // Create active admin user
        $this->adminUser = User::create([
            'name' => 'Active Admin',
            'email' => 'admin@test.gov.in',
            'phone' => '9999999999',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create active candidate user
        $this->candidateUser = User::create([
            'name' => 'Active Candidate',
            'email' => 'candidate@test.gov.in',
            'phone' => '8888888888',
            'password' => bcrypt('password123'),
            'role' => 'candidate',
            'is_active' => true,
        ]);

        // Create a standard job post
        $this->jobPost = JobPost::create([
            'department_id' => $dept->id,
            'state_id' => $state->id,
            'qualification_id' => $qual->id,
            'category_id' => $category->id,
            'post_type' => 'job',
            'title' => 'Senior Developer Recruitment 2026',
            'slug' => 'senior-developer-recruitment-2026',
            'description' => '<p>Valid rich text description</p>',
            'age_limit' => '18 - 35 Years',
            'official_website_link' => 'https://nic.gov.in',
            'apply_link' => 'https://nic.gov.in/apply',
            'last_date_to_apply' => '2026-12-31',
            'vacancy_count' => 10,
            'application_fee' => 100.00,
            'status' => 'published',
        ]);
    }

    /**
     * 1. Test that suspended admins are instantly logged out and denied access.
     */
    public function test_suspended_admin_is_instantly_logged_out()
    {
        $suspendedAdmin = User::create([
            'name' => 'Suspended Admin',
            'email' => 'suspendedadmin@test.gov.in',
            'phone' => '1111111111',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => false,
        ]);

        $this->actingAs($suspendedAdmin);

        $response = $this->getJson(route('admin.data'));
        $response->assertStatus(403);
        $response->assertJsonFragment(['status' => 'error']);

        // Assert session is invalidated (user is logged out)
        $this->assertFalse(Auth::check());
    }

    /**
     * 2. Test that suspended candidates are instantly logged out and blocked.
     */
    public function test_suspended_candidate_is_blocked_from_authenticated_routes()
    {
        $suspendedCandidate = User::create([
            'name' => 'Suspended Candidate',
            'email' => 'suspendedcand@test.gov.in',
            'phone' => '2222222222',
            'password' => bcrypt('password123'),
            'role' => 'candidate',
            'is_active' => false,
        ]);

        $this->actingAs($suspendedCandidate);

        $response = $this->getJson(route('dashboard.data'));
        $response->assertStatus(403);

        // Assert session is terminated
        $this->assertFalse(Auth::check());
    }

    /**
     * 3. Test admin dashboard API requires admin role and denies candidates.
     */
    public function test_admin_dashboard_api_demands_admin_role_and_denies_candidates()
    {
        // Candidate user tries to access /api/admin/dashboard
        $this->actingAs($this->candidateUser);
        $response = $this->getJson(route('admin.dashboard'));
        $response->assertStatus(403);

        // Active Admin user accesses /api/admin/dashboard
        $this->actingAs($this->adminUser);
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    /**
     * 4. Test that the SSRF engine blocks loopback and unauthorized domains.
     */
    public function test_ssrf_engine_blocks_loopback_and_unauthorized_domains()
    {
        // Safe allowed domains (.gov.in, .nic.in, or approved whitelist)
        $this->assertTrue(UrlSecurity::isSafeUrl('https://ssc.gov.in/recruitment'));
        $this->assertTrue(UrlSecurity::isSafeUrl('https://generativelanguage.googleapis.com/v1/models'));

        // Unsafe domains
        $this->assertFalse(UrlSecurity::isSafeUrl('https://evil-hacker-site.com'));
        $this->assertFalse(UrlSecurity::isSafeUrl('https://localhost/admin'));
        $this->assertFalse(UrlSecurity::isSafeUrl('https://127.0.0.1/private'));
        $this->assertFalse(UrlSecurity::isSafeUrl('http://169.254.169.254/latest/meta-data'));
        $this->assertFalse(UrlSecurity::isSafeUrl(null));
        $this->assertFalse(UrlSecurity::isSafeUrl('invalid-url-format'));
    }

    /**
     * 5. Test file upload strict MIME checks reject extension-spoofed scripts.
     */
    public function test_file_upload_strict_mime_check_rejects_spoofed_extensions()
    {
        $this->actingAs($this->candidateUser);

        // Create a fake PHP script and name it "resume.pdf"
        $maliciousFile = UploadedFile::fake()->createWithContent('resume.pdf', '<?php phpinfo(); ?>');

        $response = $this->postJson(route('jobs.apply', $this->jobPost->id), [
            'resume' => $maliciousFile
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['status' => 'error']);
        $this->assertStringContainsString('Invalid file format', $response->json('message'));
    }

    /**
     * 6. Test that EICAR test signature file is blocked by antivirus scan.
     */
    public function test_antivirus_scan_blocks_eicar_test_files()
    {
        $this->actingAs($this->candidateUser);

        // EICAR threat file signature
        $eicarContent = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
        // Create dummy PDF containing EICAR threat string (spoofed as a PDF)
        $infectedFile = UploadedFile::fake()->createWithContent('resume.pdf', "%PDF-1.4\n" . $eicarContent);

        $response = $this->postJson(route('jobs.apply', $this->jobPost->id), [
            'resume' => $infectedFile
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['status' => 'error']);
        $this->assertStringContainsString('Antivirus scan failed', $response->json('message'));
    }

    /**
     * 7. Test stored XSS payloads are fully sanitized before saving.
     */
    public function test_stored_xss_payloads_are_fully_sanitized_before_save()
    {
        $this->actingAs($this->adminUser);

        $xssTitle = 'Senior Engineer <script>alert("XSS Title")</script>';
        $xssDescription = '<p>Normal text <img src="x" onerror="alert(1)"> and <script>alert(2)</script></p>';

        $response = $this->postJson(route('admin.jobs.store'), [
            'title' => $xssTitle,
            'category_id' => $this->jobPost->category_id,
            'department_id' => $this->jobPost->department_id,
            'state_id' => $this->jobPost->state_id,
            'qualification_id' => $this->jobPost->qualification_id,
            'description' => $xssDescription,
            'salary_min' => 40000.00,
            'salary_max' => 90000.00,
            'vacancy_count' => 5,
            'application_fee' => 50.00,
            'last_date_to_apply' => '2026-11-30',
            'official_website_link' => 'https://ssc.gov.in',
        ]);

        $response->assertStatus(200);

        // Retrieve and assert sanitization has occurred in the database
        $createdJob = JobPost::where('official_website_link', 'https://ssc.gov.in')
            ->where('title', '!=', 'Senior Developer Recruitment 2026')
            ->first();

        $this->assertNotNull($createdJob);
        // Expect <script> tags removed from title
        $this->assertStringNotContainsString('<script>', $createdJob->title);
        $this->assertStringNotContainsString('alert', $createdJob->title);

        // Expect script and img onerror tags stripped from description
        $this->assertStringNotContainsString('<script>', $createdJob->description);
        $this->assertStringNotContainsString('onerror', $createdJob->description);
        $this->assertStringContainsString('Normal text', $createdJob->description);
    }
}
