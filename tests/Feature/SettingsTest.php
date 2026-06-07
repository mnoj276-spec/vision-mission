<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use App\Models\ThemeSetting;
use App\Models\EmailSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize user sessions
        $this->candidate = User::create([
            'name' => 'Candidate User One',
            'email' => 'candidate1@example.com',
            'phone' => '8888888888',
            'password' => bcrypt('password123'),
            'role' => 'candidate',
            'is_active' => true
        ]);

        $this->admin = User::create([
            'name' => 'Portal Admin',
            'email' => 'admin@govjobs.com',
            'phone' => '9999999999',
            'password' => bcrypt('Admin@12345'),
            'role' => 'admin',
            'is_active' => true
        ]);

        // Spatie Roles & Permissions Seeding
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
        
        // Assign roles to administrative users
        $this->admin->syncRoles(['Super Admin']);
        // Seed default settings
        $this->artisan('db:seed', ['--class' => 'SettingsSeeder']);
    }

    /**
     * Verify Admin can retrieve all settings settings.
     */
    public function test_admin_can_retrieve_all_settings(): void
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.settings.index'));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'general',
                    'theme',
                    'seo',
                    'email',
                    'api',
                    'social'
                ]
            ]);
    }

    /**
     * Verify Candidate role is barred from loading settings configuration.
     */
    public function test_candidate_cannot_retrieve_settings(): void
    {
        $this->actingAs($this->candidate);

        $response = $this->getJson(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    /**
     * Verify update of general configurations.
     */
    public function test_admin_can_update_general_settings(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'website_name' => 'GovJobs Updated',
            'website_title' => 'Portal Updated Title',
            'website_tagline' => 'New Tagline slogan',
            'website_description' => 'New Description metadata',
            'website_keywords' => 'keyword1, keyword2',
            'website_author' => 'Author Admin',
            'website_contact_email' => 'admin@newgovjobs.com',
            'website_contact_mobile' => '+91 1122334455',
            'support_email' => 'support@newgovjobs.com',
            'support_phone' => '+91 5566778899',
            'office_address' => 'New Delhi HQ 1',
            'copyright_text' => 'Copyright 2026 Updated',
            'timezone' => 'Asia/Kolkata',
            'date_format' => 'Y-m-d',
            'currency' => 'INR',
            'language' => 'en',
            'maintenance_mode' => '1',
            'maintenance_message' => 'Scheduled Upgrade Maintenance',
            'email_notifications' => '1',
            'push_notifications' => '0',
            'admin_notifications' => '1',
            'user_notifications' => '1',
            'header_scripts' => '<script>console.log("header");</script>',
            'footer_scripts' => '<script>console.log("footer");</script>',
        ];

        $response = $this->postJson(route('admin.settings.general'), $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('settings', [
            'key' => 'website_name',
            'value' => 'GovJobs Updated'
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'maintenance_mode',
            'value' => '1'
        ]);

        // Ensure cache is cleared immediately
        $this->assertNull(Cache::get('site_settings_general'));
    }

    /**
     * Verify update of theme styling configurations.
     */
    public function test_admin_can_update_theme_settings(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'primary_color' => '#ff0000',
            'secondary_color' => '#00ff00',
            'accent_color' => '#0000ff',
            'background_color' => '#ffffff',
            'text_color' => '#111111',
            'dark_primary_color' => '#ff00ff',
            'dark_background_color' => '#222222',
        ];

        $response = $this->postJson(route('admin.settings.theme'), $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('theme_settings', [
            'key' => 'primary_color',
            'value' => '#ff0000'
        ]);
    }

    /**
     * Verify update of SMTP settings and dynamic configuration boot binding.
     */
    public function test_admin_can_update_smtp_settings(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => 2525,
            'smtp_username' => 'testuser123',
            'smtp_password' => 'secretpass',
            'smtp_encryption' => 'tls',
            'sender_name' => 'Custom GovJobs Sender',
            'sender_email' => 'no-reply@mygovjobs.com'
        ];

        $response = $this->postJson(route('admin.settings.email'), $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('email_settings', [
            'key' => 'smtp_host',
            'value' => 'smtp.mailtrap.io'
        ]);

        // Clear cache and trigger settings reload boot
        settings_clear_cache();

        // Boot Service Provider logic is tested by resolving configuration value
        $host = email_setting('smtp_host');
        $this->assertEquals('smtp.mailtrap.io', $host);
    }
}
