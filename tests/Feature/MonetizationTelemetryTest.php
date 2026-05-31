<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\Category;
use App\Models\State;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\User;
use App\Models\JobAlert;
use App\Models\AnalyticsRevenueEvent;
use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonetizationTelemetryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $candidateUser;
    protected State $state;
    protected Category $category;
    protected Department $department;
    protected Qualification $qualification;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create standard metadata lookup references
        $this->state = State::create(['name' => 'Delhi', 'code' => 'DL', 'slug' => 'delhi']);
        $this->category = Category::create(['name' => 'Government Jobs', 'slug' => 'government-jobs']);
        $this->department = Department::create(['name' => 'Delhi Metro Rail Corporation', 'code' => 'DMRC', 'slug' => 'dmrc']);
        $this->qualification = Qualification::create(['name' => 'Graduate', 'slug' => 'graduate']);

        // 2. Create users
        $this->candidateUser = User::create([
            'name' => 'John Candidate',
            'email' => 'candidate@visionmission.com',
            'password' => bcrypt('Password123'),
            'role' => 'candidate',
            'membership_plan' => 'free',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@visionmission.com',
            'password' => bcrypt('AdminPassword123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Seed Spatie Role & Permission to align with Laravel auth/admin middleware policies
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'view_dashboard', 'guard_name' => 'web']);
        $roleAdmin = \Spatie\Permission\Models\Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $roleAdmin->givePermissionTo($permission);
        $this->adminUser->assignRole($roleAdmin);

        $roleCandidate = \Spatie\Permission\Models\Role::create(['name' => 'Candidate', 'guard_name' => 'web']);
        $this->candidateUser->assignRole($roleCandidate);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Test Requirement: Verify sponsored/featured listings are sorted first in search.
     */
    public function test_listings_pinned_and_sorted_correctly(): void
    {
        // Setup 4 distinct job listings with different properties and creation/publish times
        
        // 1. Standard Job (published now)
        $standardJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Standard DMRC Assistant',
            'slug' => 'standard-dmrc-assistant',
            'description' => 'Details.',
            'status' => 'published',
            'is_sponsored' => false,
            'is_featured' => false,
            'published_at' => now(),
        ]);

        // 2. Featured Job (published 1 hour ago)
        $featuredJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Featured DMRC Executive',
            'slug' => 'featured-dmrc-executive',
            'description' => 'Details.',
            'status' => 'published',
            'is_sponsored' => false,
            'is_featured' => true,
            'published_at' => now()->subHour(),
        ]);

        // 3. Sponsored Job (published 2 hours ago)
        $sponsoredJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Sponsored DMRC Associate',
            'slug' => 'sponsored-dmrc-associate',
            'description' => 'Details.',
            'status' => 'published',
            'is_sponsored' => true,
            'is_featured' => false,
            'published_at' => now()->subHours(2),
        ]);

        // 4. Sponsored & Featured Job (published 3 hours ago)
        $sponsoredAndFeaturedJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Sponsored & Featured Director',
            'slug' => 'sponsored-featured-director',
            'description' => 'Details.',
            'status' => 'published',
            'is_sponsored' => true,
            'is_featured' => true,
            'published_at' => now()->subHours(3),
        ]);

        // Retrieve from the repository using the production getPaginatedFiltered method
        $repository = app(JobRepositoryInterface::class);
        $results = $repository->getPaginatedFiltered([]);

        // Assert sorting order of the retrieved collection
        // Expected order: Sponsored & Featured (1st), Sponsored (2nd), Featured (3rd), Standard (4th)
        $this->assertCount(4, $results->items());
        $this->assertEquals($sponsoredAndFeaturedJob->id, $results->items()[0]->id, 'Sponsored & Featured should be listed first.');
        $this->assertEquals($sponsoredJob->id, $results->items()[1]->id, 'Sponsored non-featured should be listed second.');
        $this->assertEquals($featuredJob->id, $results->items()[2]->id, 'Featured non-sponsored should be listed third.');
        $this->assertEquals($standardJob->id, $results->items()[3]->id, 'Standard job should be listed fourth.');
    }

    /**
     * Test Requirement: Verify /go/{slug} redirection returns 302, contains noindex headers, and successfully writes CPC revenue logs.
     */
    public function test_masked_affiliate_redirection_gateway(): void
    {
        $affiliateJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Affiliate Masked Opportunity',
            'slug' => 'affiliate-masked-opportunity',
            'description' => 'Details.',
            'status' => 'published',
            'affiliate_link' => 'https://partner.affiliate.com/offer/12345?sub=direct',
            'apply_link' => 'https://dmrc.org/apply',
            'published_at' => now(),
        ]);

        $this->assertDatabaseEmpty('analytics_revenue_events');

        // Request redirection from the cloaked endpoint
        $response = $this->get(route('monetization.affiliate_redirect', ['slug' => $affiliateJob->slug]));

        // Assert redirect behaviors
        $response->assertStatus(302);
        $response->assertRedirect('https://partner.affiliate.com/offer/12345?sub=direct');

        // Assert strict crawler blocking and SEO safety headers
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);

        // Assert that a CPC telemetry event was automatically written
        $this->assertDatabaseHas('analytics_revenue_events', [
            'event_type' => 'ad_click',
            'slot_name' => 'affiliate_link',
            'estimated_revenue' => 5.0000,
            'job_post_id' => $affiliateJob->id,
        ]);
    }

    /**
     * Test Requirement: Verify membership plan upgrades to premium update the database successfully.
     */
    public function test_membership_plan_upgrade_via_api(): void
    {
        $this->assertEquals('free', $this->candidateUser->membership_plan);

        $response = $this->actingAs($this->candidateUser)
            ->postJson(route('monetization.membership_upgrade'), [
                'plan' => 'premium',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.membership_plan', 'premium');

        $this->candidateUser->refresh();
        $this->assertEquals('premium', $this->candidateUser->membership_plan);
    }

    /**
     * Test Requirement: Verify ad banner placeholders are visible to free/guest users and suppressed for premium/pro users.
     */
    public function test_ad_suppression_logic_in_views(): void
    {
        // 1. Guest user visits homepage: Ad banner must be visible
        $guestResponse = $this->get(route('home'));
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee('id="home_leaderboard_ad"', false);

        // 2. Upgraded Premium user visits homepage: Ad banner must be completely suppressed
        $premiumUser = User::create([
            'name' => 'Premium Candidate',
            'email' => 'premium@visionmission.com',
            'password' => bcrypt('Password123'),
            'role' => 'candidate',
            'membership_plan' => 'premium',
            'is_active' => true,
        ]);
        
        $roleCandidate = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Candidate', 'guard_name' => 'web']);
        $premiumUser->assignRole($roleCandidate);

        $premiumResponse = $this->actingAs($premiumUser)->get(route('home'));
        $premiumResponse->assertStatus(200);
        $premiumResponse->assertDontSee('id="home_leaderboard_ad"', false);
    }

    /**
     * Test Requirement: Verify admin revenue analytics telemetry aggregates and returns correct counts and calculations.
     */
    public function test_admin_consolidated_revenue_dashboard(): void
    {
        $startDate = now()->startOfDay();

        // 1. Seed CPC & CPM Revenue events from the past day
        // Standard Ad Click CPC (₹0.08)
        AnalyticsRevenueEvent::create([
            'event_type' => 'ad_click',
            'slot_name' => 'home_sidebar',
            'estimated_revenue' => 0.0800,
            'session_id' => 'session_x',
            'ip_address' => '127.0.0.1',
            'created_at' => $startDate,
        ]);

        // Standard Ad Impression CPM (₹0.0025)
        AnalyticsRevenueEvent::create([
            'event_type' => 'ad_impression',
            'slot_name' => 'leaderboard_top',
            'estimated_revenue' => 0.0025,
            'session_id' => 'session_x',
            'ip_address' => '127.0.0.1',
            'created_at' => $startDate,
        ]);

        // Affiliate Link Clicks CPC (₹5.00 each) - two clicks
        $affiliateJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Dashboard Affiliate Listing',
            'slug' => 'dashboard-affiliate-listing',
            'description' => 'Details.',
            'status' => 'published',
            'published_at' => $startDate,
        ]);

        AnalyticsRevenueEvent::create([
            'event_type' => 'ad_click',
            'slot_name' => 'affiliate_link',
            'estimated_revenue' => 5.0000,
            'job_post_id' => $affiliateJob->id,
            'session_id' => 'session_y',
            'ip_address' => '127.0.0.1',
            'created_at' => $startDate,
        ]);

        AnalyticsRevenueEvent::create([
            'event_type' => 'ad_click',
            'slot_name' => 'affiliate_link',
            'estimated_revenue' => 5.0000,
            'job_post_id' => $affiliateJob->id,
            'session_id' => 'session_z',
            'ip_address' => '127.0.0.1',
            'created_at' => $startDate,
        ]);

        // 2. Seed Sponsored listings (₹5,000 each) - two listings
        JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Sponsored Opportunity A',
            'slug' => 'sponsored-opportunity-a',
            'description' => 'Details.',
            'status' => 'published',
            'is_sponsored' => true,
            'published_at' => $startDate,
            'created_at' => $startDate,
        ]);

        JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Sponsored Opportunity B',
            'slug' => 'sponsored-opportunity-b',
            'description' => 'Details.',
            'status' => 'published',
            'is_sponsored' => true,
            'published_at' => $startDate,
            'created_at' => $startDate,
        ]);

        // 3. Seed Users with upgraded Membership plans
        // Premium User: 2 upgraded (₹299 each)
        User::create([
            'name' => 'Premium User A',
            'email' => 'prem_a@visionmission.com',
            'password' => bcrypt('Password123'),
            'role' => 'candidate',
            'membership_plan' => 'premium',
            'is_active' => true,
        ]);
        User::create([
            'name' => 'Premium User B',
            'email' => 'prem_b@visionmission.com',
            'password' => bcrypt('Password123'),
            'role' => 'candidate',
            'membership_plan' => 'premium',
            'is_active' => true,
        ]);

        // Pro User: 1 upgraded (₹599 each)
        User::create([
            'name' => 'Pro User A',
            'email' => 'pro_a@visionmission.com',
            'password' => bcrypt('Password123'),
            'role' => 'candidate',
            'membership_plan' => 'pro',
            'is_active' => true,
        ]);

        // 4. Test authorization levels
        // Guest
        $guestResponse = $this->getJson(route('monetization.revenue_analytics'));
        $guestResponse->assertStatus(401);

        // Candidate User
        $candidateResponse = $this->actingAs($this->candidateUser)
            ->getJson(route('monetization.revenue_analytics'));
        $candidateResponse->assertStatus(403);

        // Admin User
        $adminResponse = $this->actingAs($this->adminUser)
            ->getJson(route('monetization.revenue_analytics'));

        $adminResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'kpis' => [
                        'ads_cpc',
                        'ads_cpm',
                        'affiliate',
                        'sponsorship',
                        'subscriptions',
                        'total_revenue',
                    ],
                    'counts' => [
                        'sponsored_jobs',
                        'premium_subscribers',
                        'pro_subscribers',
                    ],
                    'charts' => [
                        'streams',
                    ],
                    'leaderboard',
                ]
            ]);

        // Math Validations:
        // ads_cpc = 0.08
        // ads_cpm = 0.0025
        // affiliate = 5.00 + 5.00 = 10.00
        // sponsorship = 2 sponsored * 5000.00 = 10000.00
        // subscriptions = 2 premium * 299.00 + 1 pro * 599.00 = 598.00 + 599.00 = 1197.00
        // total_revenue = 0.08 + 0.0025 + 10.00 + 10000.00 + 1197.00 = 11207.0825 (~11207.08)
        
        $kpis = $adminResponse->json('data.kpis');
        $this->assertEquals(0.08, $kpis['ads_cpc']);
        $this->assertEquals(0.00, $kpis['ads_cpm']); // rounded to 2 decimal places in JSON
        $this->assertEquals(10.00, $kpis['affiliate']);
        $this->assertEquals(10000.00, $kpis['sponsorship']);
        $this->assertEquals(1197.00, $kpis['subscriptions']);
        $this->assertEquals(11207.08, $kpis['total_revenue']);

        $counts = $adminResponse->json('data.counts');
        $this->assertEquals(2, $counts['sponsored_jobs']);
        $this->assertEquals(2, $counts['premium_subscribers']);
        $this->assertEquals(1, $counts['pro_subscribers']);

        // Leaderboard check
        $leaderboard = $adminResponse->json('data.leaderboard');
        $this->assertNotEmpty($leaderboard);
        $this->assertEquals('Dashboard Affiliate Listing', $leaderboard[0]['title']);
        $this->assertEquals(2, $leaderboard[0]['clicks']);
        $this->assertEquals(10.00, $leaderboard[0]['earnings']);
    }
}
