<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\Category;
use App\Models\State;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\User;
use App\Models\AnalyticsPageView;
use App\Models\AnalyticsJobEvent;
use App\Models\AnalyticsSearchQuery;
use App\Models\AnalyticsRevenueEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTelemetryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected JobPost $jobPost;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create seed data
        $state = State::create(['name' => 'Maharashtra', 'code' => 'MH', 'slug' => 'maharashtra']);
        $cat = Category::create(['name' => 'Banking Jobs', 'slug' => 'banking-jobs']);
        $dept = Department::create(['name' => 'Reserve Bank of India', 'code' => 'RBI', 'slug' => 'rbi']);
        $qual = Qualification::create(['name' => 'Post Graduate', 'slug' => 'post-graduate']);

        $this->jobPost = JobPost::create([
            'category_id' => $cat->id,
            'department_id' => $dept->id,
            'state_id' => $state->id,
            'qualification_id' => $qual->id,
            'title' => 'RBI Grade B Recruitment 2026 Officer Post',
            'slug' => 'rbi-grade-b-recruitment-2026-officer',
            'description' => 'Officer Grade B recruitment details.',
            'salary_min' => 55000,
            'salary_max' => 125000,
            'vacancy_count' => 150,
            'application_fee' => 850,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 2. Create administrative user
        $this->adminUser = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@visionmission.com',
            'password' => bcrypt('AdminPassword123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Seed Spatie Role & Permission for Testing
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'view_dashboard', 'guard_name' => 'web']);
        $role = \Spatie\Permission\Models\Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $this->adminUser->assignRole($role);

        // Flush cached permissions to ensure instant synchronization
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Test client-side page views tracking api.
     */
    public function test_page_view_telemetry_tracking(): void
    {
        $this->assertDatabaseEmpty('analytics_page_views');

        $response = $this->postJson(route('analytics.page_view'), [
            'path' => '/about-us',
            'referer' => 'https://www.google.com/'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('analytics_page_views', [
            'path' => '/about-us',
            'referer' => 'https://www.google.com/',
            'is_organic' => true,
            'is_bot' => false
        ]);
    }

    /**
     * Test job detail view and interaction logs.
     */
    public function test_job_interactions_telemetry_tracking(): void
    {
        $this->assertDatabaseEmpty('analytics_job_events');

        $response = $this->postJson(route('analytics.job_event'), [
            'job_post_id' => $this->jobPost->id,
            'event_type' => 'view'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('analytics_job_events', [
            'job_post_id' => $this->jobPost->id,
            'event_type' => 'view'
        ]);

        // Click apply link
        $responseClick = $this->postJson(route('analytics.job_event'), [
            'job_post_id' => $this->jobPost->id,
            'event_type' => 'apply_click'
        ]);

        $responseClick->assertStatus(200);
        $this->assertDatabaseHas('analytics_job_events', [
            'job_post_id' => $this->jobPost->id,
            'event_type' => 'apply_click'
        ]);
    }

    /**
     * Test ad revenue telemetry events logging.
     */
    public function test_ad_revenue_telemetry_tracking(): void
    {
        $this->assertDatabaseEmpty('analytics_revenue_events');

        // Ad Impression
        $responseImp = $this->postJson(route('analytics.ad_event'), [
            'event_type' => 'ad_impression',
            'slot_name' => 'home_sidebar',
            'job_post_id' => $this->jobPost->id
        ]);

        $responseImp->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('estimated_revenue', 0.0025);

        // Ad Click
        $responseClick = $this->postJson(route('analytics.ad_event'), [
            'event_type' => 'ad_click',
            'slot_name' => 'job_details_top',
            'job_post_id' => $this->jobPost->id
        ]);

        $responseClick->assertStatus(200)
            ->assertJsonPath('estimated_revenue', 0.08);

        $this->assertDatabaseHas('analytics_revenue_events', [
            'event_type' => 'ad_impression',
            'slot_name' => 'home_sidebar',
            'estimated_revenue' => 0.0025
        ]);

        $this->assertDatabaseHas('analytics_revenue_events', [
            'event_type' => 'ad_click',
            'slot_name' => 'job_details_top',
            'estimated_revenue' => 0.0800
        ]);
    }

    /**
     * Test admin metrics aggregation dashboard API.
     */
    public function test_admin_metrics_reporting_endpoint(): void
    {
        // 1. Seed some mock events
        AnalyticsPageView::create([
            'session_id' => 'session_123',
            'path' => '/',
            'referer' => 'https://www.google.com/',
            'ip_address' => '127.0.0.1',
            'is_bot' => false,
            'is_organic' => true,
            'created_at' => now(),
        ]);

        AnalyticsPageView::create([
            'session_id' => 'session_123',
            'path' => '/search',
            'ip_address' => '127.0.0.1',
            'is_bot' => false,
            'is_organic' => false,
            'created_at' => now(),
        ]);

        AnalyticsSearchQuery::create([
            'query' => 'RBI Officer',
            'filters' => ['state_id' => 1],
            'results_count' => 10,
            'session_id' => 'session_123',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        AnalyticsJobEvent::create([
            'job_post_id' => $this->jobPost->id,
            'event_type' => 'view',
            'session_id' => 'session_123',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        AnalyticsJobEvent::create([
            'job_post_id' => $this->jobPost->id,
            'event_type' => 'bookmark',
            'session_id' => 'session_123',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        AnalyticsRevenueEvent::create([
            'event_type' => 'ad_click',
            'slot_name' => 'home_sidebar',
            'estimated_revenue' => 0.0800,
            'session_id' => 'session_123',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        // 2. Fetch admin telemetry as admin user
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.analytics.metrics', ['days' => 7]));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'kpis' => [
                        'job_views',
                        'overall_ctr',
                        'search_queries',
                        'estimated_revenue',
                        'alert_subscribers',
                        'bookmarks_created',
                        'applications_submitted'
                    ],
                    'charts' => [
                        'traffic',
                        'revenue',
                        'funnel'
                    ],
                    'top_queries',
                    'job_performance',
                    'user_journeys'
                ]
            ]);

        $kpis = $response->json('data.kpis');
        $this->assertEquals(1, $kpis['job_views']);
        $this->assertEquals(1, $kpis['search_queries']);
        $this->assertEquals(0.08, $kpis['estimated_revenue']);
        $this->assertEquals(100.0, $kpis['overall_ctr']); // 1 bookmark / 1 view = 100% CTR

        $journeys = $response->json('data.user_journeys');
        $this->assertNotEmpty($journeys);
        $this->assertEquals('/ → /search', $journeys[0]['path']);
    }
}
