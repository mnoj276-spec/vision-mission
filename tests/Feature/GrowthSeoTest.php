<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\Category;
use App\Models\State;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\JobAlert;
use App\Models\GrowthAnalytic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrowthSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic category, state, department, and qualification
        $state = State::create(['name' => 'Uttar Pradesh', 'code' => 'UP']);
        $cat = Category::create(['name' => 'UPSC & SSC Jobs', 'slug' => 'upsc-ssc-jobs']);
        $dept = Department::create(['name' => 'Staff Selection Commission', 'code' => 'SSC']);
        $qual = Qualification::create(['name' => 'Graduate Degree', 'slug' => 'graduate']);

        // Create an active SSC job post
        JobPost::create([
            'category_id' => $cat->id,
            'department_id' => $dept->id,
            'state_id' => $state->id,
            'qualification_id' => $qual->id,
            'title' => 'SSC CGL Grade-A Active Recruitment 2026',
            'slug' => 'ssc-cgl-grade-a-active-recruitment-2026',
            'description' => 'Official job advertisement by Staff Selection Commission (SSC).',
            'salary_min' => 44900,
            'salary_max' => 142400,
            'vacancy_count' => 500,
            'application_fee' => 100,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Test that all custom SEO pages render with correct SEO elements and schema markups.
     */
    public function test_seo_landing_pages_render_and_display_data(): void
    {
        $pages = ['/ssc-jobs', '/railway-jobs', '/upsc-jobs', '/state-jobs'];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertStatus(200);

            // Check H1 and breadcrumbs content presence
            $response->assertSee('Conversion Telemetry');
            $response->assertSee('Email Job Alerts');
            $response->assertSee('Desktop Push Alerts');
            $response->assertSee('Joint Telegram Alert Network');

            // Check JSON-LD Schema integration
            $response->assertSee('application/ld+json');
            $response->assertSee('ItemList');
        }

        // Verify that /ssc-jobs specifically lists our created SSC CGL job
        $this->get('/ssc-jobs')
            ->assertSee('SSC CGL Grade-A Active Recruitment 2026')
            ->assertSee('Staff Selection Commission')
            ->assertSee('Uttar Pradesh');
    }

    /**
     * Test automated sitemap generation with correct XML content type.
     */
    public function test_automated_xml_sitemap(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        // Confirm XML structure and link indexing
        $this->assertStringContainsString('<sitemapindex', $response->getContent());

        // Check pages sitemap
        $responsePages = $this->get('/sitemaps/sitemap-pages.xml');
        $responsePages->assertStatus(200);
        $this->assertStringContainsString('<urlset', $responsePages->getContent());
        $this->assertStringContainsString('/ssc-jobs', $responsePages->getContent());

        // Check jobs sitemap
        $responseJobs = $this->get('/sitemaps/sitemap-jobs.xml');
        $responseJobs->assertStatus(200);
        $this->assertStringContainsString('<urlset', $responseJobs->getContent());
        $this->assertStringContainsString('ssc-cgl-grade-a-active-recruitment-2026', $responseJobs->getContent());
    }

    /**
     * Test lead capture for job alerts.
     */
    public function test_lead_capture_alerts_subscription(): void
    {
        $this->assertDatabaseEmpty('job_alerts');

        $response = $this->postJson(route('growth.subscribe'), [
            'email' => 'test_aspirant@govjobs.com',
            'category_name' => 'ssc'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonFragment([
                'message' => 'Successfully subscribed to instant SSC job alerts!'
            ]);

        $this->assertDatabaseHas('job_alerts', [
            'email' => 'test_aspirant@govjobs.com',
            'category_name' => 'ssc'
        ]);
    }

    /**
     * Test programmatic analytics event tracking.
     */
    public function test_programmatic_analytics_event_tracking(): void
    {
        $this->assertDatabaseEmpty('growth_analytics');

        $response = $this->postJson(route('growth.track'), [
            'event_type' => 'apply_click',
            'page_path' => '/ssc-jobs'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('growth_analytics', [
            'event_type' => 'apply_click',
            'page_path' => '/ssc-jobs'
        ]);
    }
}
