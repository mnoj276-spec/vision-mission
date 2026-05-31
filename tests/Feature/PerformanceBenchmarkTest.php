<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\Category;
use App\Models\State;
use App\Models\Department;
use App\Models\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected State $state;
    protected Category $category;
    protected Department $department;
    protected Qualification $qualification;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary reference tables
        $this->state = State::create(['name' => 'Delhi', 'code' => 'DL', 'slug' => 'delhi']);
        $this->category = Category::create(['name' => 'Government Jobs', 'slug' => 'government-jobs']);
        $this->department = Department::create(['name' => 'Delhi Metro Rail Corporation', 'code' => 'DMRC', 'slug' => 'dmrc']);
        $this->qualification = Qualification::create(['name' => 'Graduate', 'slug' => 'graduate']);

        // Seed mock Job Posts (20 posts to simulate populated database lists)
        for ($i = 1; $i <= 20; $i++) {
            JobPost::create([
                'category_id' => $this->category->id,
                'department_id' => $this->department->id,
                'state_id' => $this->state->id,
                'qualification_id' => $this->qualification->id,
                'title' => "DMRC Recruitment Officer Slot {$i}",
                'slug' => "dmrc-recruitment-officer-slot-{$i}",
                'description' => "Details for government job recruitment slot {$i}.",
                'salary_min' => 45000 + ($i * 100),
                'salary_max' => 95000 + ($i * 100),
                'vacancy_count' => $i * 5,
                'application_fee' => 500,
                'status' => 'published',
                'is_sponsored' => $i % 4 === 0,
                'is_featured' => $i % 3 === 0,
                'published_at' => now()->subMinutes($i),
            ]);
        }
    }

    /**
     * Test caching, query count reduction, and cache busting on the Homepage.
     */
    public function test_homepage_performance_caching_and_cache_busting(): void
    {
        Cache::flush();

        // ─── Phase 1: Cold Run (Cache Miss) ───
        $queriesOnColdRun = 0;
        DB::listen(function () use (&$queriesOnColdRun) {
            $queriesOnColdRun++;
        });

        $startTime = microtime(true);
        $response1 = $this->get(route('home'));
        $coldDuration = microtime(true) - $startTime;

        $response1->assertStatus(200);
        $this->assertGreaterThan(0, $queriesOnColdRun, 'Cold run should execute database queries.');

        // ─── Phase 2: Warm Run (Cache Hit) ───
        $queriesOnWarmRun = 0;
        DB::listen(function ($query) use (&$queriesOnWarmRun) {
            if (!str_contains($query->sql, 'analytics_page_views')) {
                $queriesOnWarmRun++;
            }
        });

        $startTime = microtime(true);
        $response2 = $this->get(route('home'));
        $warmDuration = microtime(true) - $startTime;

        $response2->assertStatus(200);

        // Warm run should execute 0 content database queries because of getHomePageData cache!
        $this->assertEquals(0, $queriesOnWarmRun, 'Warm run should trigger 0 content database queries.');

        // ─── Phase 3: Cache Invalidation (Cache Busting) ───
        $newJob = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Cache Buster Extraordinaire',
            'slug' => 'cache-buster-extraordinaire',
            'description' => 'Details.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Subsequent page fetch should experience a cold run again (cache busted!)
        $queriesAfterBusting = 0;
        DB::listen(function () use (&$queriesAfterBusting) {
            $queriesAfterBusting++;
        });

        $response3 = $this->get(route('home'));
        $response3->assertStatus(200);

        $this->assertGreaterThan(0, $queriesAfterBusting, 'Observer should bust the homepage cache on Job creation.');

        // Print benchmark statistics
        fwrite(STDERR, sprintf(
            "\n[BENCHMARK - HOMEPAGE]\n  Cold Render: %.4f seconds (%d queries)\n  Warm Render: %.4f seconds (%d queries) -> %.2f%% faster!\n",
            $coldDuration,
            $queriesOnColdRun,
            $warmDuration,
            $queriesOnWarmRun,
            (($coldDuration - $warmDuration) / $coldDuration) * 100
        ));
    }

    /**
     * Test caching and cache busting on the main public XML Sitemap.
     */
    public function test_sitemap_xml_caching_and_invalidation(): void
    {
        Cache::flush();

        // 1. Cold Run
        $coldQueries = 0;
        DB::listen(function () use (&$coldQueries) {
            $coldQueries++;
        });
        $res1 = $this->get(route('sitemap'));
        $res1->assertStatus(200);
        $this->assertGreaterThan(0, $coldQueries);

        // 2. Warm Run
        $warmQueries = 0;
        DB::listen(function () use (&$warmQueries) {
            $warmQueries++;
        });
        $res2 = $this->get(route('sitemap'));
        $res2->assertStatus(200);
        $this->assertEquals(0, $warmQueries, 'Sitemap caching should result in 0 database queries.');

        // 3. Bust cache by updating a job post
        $job = JobPost::first();
        $job->update(['title' => 'Sitemap Title Buster']);

        $afterBustQueries = 0;
        DB::listen(function () use (&$afterBustQueries) {
            $afterBustQueries++;
        });
        $res3 = $this->get(route('sitemap'));
        $res3->assertStatus(200);
        $this->assertGreaterThan(0, $afterBustQueries, 'Sitemap cache should be flushed when a JobPost is updated.');
    }

    /**
     * Test N+1 eager preloading on programmatic SEO Landing Pages.
     */
    public function test_seo_landing_pages_lazy_loading_elimination(): void
    {
        // Model::preventLazyLoading is enabled by default in development and test environments inside AppServiceProvider.
        // If there were any lazy loading (N+1 query) on the SEO categories landing page, accessing it would throw a LazyLoadingViolationException.
        // Therefore, a simple 200 OK assert guarantees that all views and layouts have successfully eliminated N+1 relation access!
        
        $response = $this->get(route('seo.dynamic_railway'));
        $response->assertStatus(200);

        $responseResults = $this->get(route('seo.results'));
        $responseResults->assertStatus(200);
        
        $responseAdmitCards = $this->get(route('seo.admit_cards'));
        $responseAdmitCards->assertStatus(200);
    }

    /**
     * Test search metadata 24-hour cache loading.
     */
    public function test_search_dashboard_metadata_caching(): void
    {
        Cache::flush();

        // First search render (caches lookups)
        $this->get(route('search.index'))->assertStatus(200);

        // Verify cache contains lookups
        $this->assertTrue(Cache::has('metadata_states'));
        $this->assertTrue(Cache::has('metadata_categories'));
        $this->assertTrue(Cache::has('metadata_qualifications'));
        $this->assertTrue(Cache::has('metadata_departments'));

        // subsequent visits should pull from cache
        $queriesCount = 0;
        DB::listen(function () use (&$queriesCount) {
            $queriesCount++;
        });
        
        $this->get(route('search.index'))->assertStatus(200);
        // Under a warm cache, the metadata retrieval queries are completely bypassed!
        $this->assertTrue($queriesCount < 10, 'Search dashboard should experience query reduction.');
    }
}
