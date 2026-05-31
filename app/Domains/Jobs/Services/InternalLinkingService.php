<?php

namespace App\Domains\Jobs\Services;

use App\Models\Category;
use App\Models\JobPost;
use App\Models\State;
use App\Models\District;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InternalLinkingService
{
    /*
    |--------------------------------------------------------------------------
    | Automated Internal Linking Intelligence Engine
    |--------------------------------------------------------------------------
    |
    | Provides seven linking strategies for maximizing crawl efficiency,
    | PageRank distribution, and contextual anchor text across all page types.
    |
    */

    protected array $config;
    protected array $scoring;

    public function __construct()
    {
        $this->config  = config('internal_linking', []);
        $this->scoring = $this->config['scoring'] ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Full Detail Page Link Package
    |--------------------------------------------------------------------------
    */

    /**
     * Returns all link sections for a job detail page in a single call.
     * Handles deduplication across sections.
     */
    public function getLinksForDetailPage(JobPost $job): array
    {
        if (!($this->config['enabled'] ?? true)) {
            return $this->emptyLinkPackage();
        }

        $cacheKey = "internal_links:detail:{$job->id}";
        $ttl      = $this->config['cache_ttl'] ?? 3600;

        return Cache::remember($cacheKey, $ttl, function () use ($job) {
            $relatedJobs      = $this->getRelatedJobs($job);
            $relatedResults   = $this->getRelatedResults($job);
            $relatedAdmitCards= $this->getRelatedAdmitCards($job);
            $categories       = $this->getRelatedCategories($job);
            $stateRecos       = $this->getStateRecommendations($job);
            $crossType        = $this->getCrossTypeLinks($job);

            // Deduplicate: remove any IDs already shown in relatedJobs from other sections
            $usedIds = $relatedJobs->pluck('id')->toArray();

            $relatedResults = $relatedResults->reject(fn($r) => in_array($r->id, $usedIds));
            $usedIds = array_merge($usedIds, $relatedResults->pluck('id')->toArray());

            $relatedAdmitCards = $relatedAdmitCards->reject(fn($a) => in_array($a->id, $usedIds));

            return [
                'related_jobs'       => $relatedJobs,
                'related_results'    => $relatedResults->values(),
                'related_admit_cards'=> $relatedAdmitCards->values(),
                'categories'         => $categories,
                'state_recommendations' => $stateRecos,
                'cross_type'         => $crossType,
                'current_job'        => $job,
            ];
        });
    }

    /**
     * Enhanced replacement for the old getExplorerLinks() method on landing pages.
     */
    public function getLinksForLandingPage(string $type, array $params): array
    {
        $cacheKey = "internal_links:landing:" . md5($type . serialize($params));
        $ttl      = $this->config['cache_ttl'] ?? 3600;

        return Cache::remember($cacheKey, $ttl, function () use ($type, $params) {
            $maxStates = $this->config['max_state_recommendations'] ?? 6;

            // States with live job counts
            $states = State::whereHas('jobPosts', function ($q) {
                    $q->published();
                })
                ->withCount(['jobPosts' => function ($q) {
                    $q->published();
                }])
                ->orderByDesc('job_posts_count')
                ->limit($maxStates)
                ->get();

            // Districts (contextual - when viewing a state page)
            $districts = collect();
            if (in_array($type, ['state', 'district'])) {
                $stateName = $params['state_name'] ?? '';
                $state = State::where('name', $stateName)->first();
                if ($state) {
                    $districts = District::where('state_id', $state->id)
                        ->withCount(['jobPosts' => function ($q) {
                            $q->published();
                        }])
                        ->orderByDesc('job_posts_count')
                        ->get();
                }
            }

            // Sector categories with routes
            $sectors = [];
            foreach ($this->config['sector_routes'] ?? [] as $key => $sector) {
                try {
                    $sectors[] = [
                        'label' => $sector['label'],
                        'url'   => route($sector['route']),
                        'icon'  => $sector['icon'] ?? '💼',
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Post-type utility pages with freshness counts
            $utilities = [
                ['label' => 'Exam Results',  'url' => route('seo.results'),      'icon' => '📊', 'count' => $this->getTypeCount('result')],
                ['label' => 'Admit Cards',   'url' => route('seo.admit_cards'),   'icon' => '🎫', 'count' => $this->getTypeCount('admit_card')],
                ['label' => 'Answer Keys',   'url' => route('seo.answer_keys'),   'icon' => '🔑', 'count' => $this->getTypeCount('answer_key')],
                ['label' => 'Syllabus PDFs', 'url' => route('seo.syllabus'),      'icon' => '📚', 'count' => $this->getTypeCount('syllabus')],
            ];

            // Trending categories (actual DB categories with counts)
            $trendingCategories = Category::where('is_active', true)
                ->whereHas('jobPosts', fn($q) => $q->published())
                ->withCount(['jobPosts' => fn($q) => $q->published()])
                ->orderByDesc('job_posts_count')
                ->limit($this->config['max_related_categories'] ?? 8)
                ->get();

            return [
                'states'               => $states,
                'districts'            => $districts,
                'sectors'              => $sectors,
                'utilities'            => $utilities,
                'trending_categories'  => $trendingCategories,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Related Jobs (Multi-Signal Relevance Scoring)
    |--------------------------------------------------------------------------
    */

    /**
     * Find related jobs using multi-signal scoring.
     */
    public function getRelatedJobs(JobPost $job, ?int $limit = null): Collection
    {
        $limit = $limit ?? ($this->config['max_related_jobs'] ?? 6);

        $candidates = JobPost::published()
            ->jobs()
            ->where('id', '!=', $job->id)
            ->with(['category', 'department', 'state', 'qualification', 'tags'])
            ->where(function ($q) use ($job) {
                $q->where('department_id', $job->department_id)
                  ->orWhere('category_id', $job->category_id)
                  ->orWhere('state_id', $job->state_id)
                  ->orWhere('qualification_id', $job->qualification_id);
            })
            ->limit(50) // Pre-filter ceiling
            ->get();

        // Load shared tags for scoring
        $jobTagIds = $job->tags->pluck('id')->toArray();

        // Score each candidate
        $scored = $candidates->map(function ($candidate) use ($job, $jobTagIds) {
            $score = 0;

            if ($candidate->department_id === $job->department_id) {
                $score += $this->scoring['same_department'] ?? 30;
            }
            if ($candidate->category_id === $job->category_id) {
                $score += $this->scoring['same_category'] ?? 25;
            }
            if ($candidate->state_id === $job->state_id) {
                $score += $this->scoring['same_state'] ?? 20;
            }
            if ($candidate->qualification_id === $job->qualification_id) {
                $score += $this->scoring['same_qualification'] ?? 15;
            }

            // Shared tags bonus
            if (!empty($jobTagIds)) {
                $candidateTagIds = $candidate->tags->pluck('id')->toArray();
                $shared = count(array_intersect($jobTagIds, $candidateTagIds));
                $score += $shared * ($this->scoring['shared_tags'] ?? 10);
            }

            // Recency bonus (published within last 7 days)
            if ($candidate->published_at && $candidate->published_at->diffInDays(now()) <= 7) {
                $score += $this->scoring['recency_bonus'] ?? 10;
            }

            // High vacancy bonus
            if ($candidate->vacancy_count > 100) {
                $score += $this->scoring['vacancy_bonus'] ?? 5;
            }

            $candidate->_relevance_score = $score;
            return $candidate;
        });

        return $scored->sortByDesc('_relevance_score')->take($limit)->values();
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Related Results
    |--------------------------------------------------------------------------
    */

    public function getRelatedResults(JobPost $job, ?int $limit = null): Collection
    {
        $limit = $limit ?? ($this->config['max_related_results'] ?? 4);

        return JobPost::published()
            ->results()
            ->where('id', '!=', $job->id)
            ->with(['department', 'state'])
            ->where(function ($q) use ($job) {
                $q->where('department_id', $job->department_id)
                  ->orWhere('category_id', $job->category_id);
            })
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Related Admit Cards
    |--------------------------------------------------------------------------
    */

    public function getRelatedAdmitCards(JobPost $job, ?int $limit = null): Collection
    {
        $limit = $limit ?? ($this->config['max_related_admit_cards'] ?? 4);

        return JobPost::published()
            ->admitCards()
            ->where('id', '!=', $job->id)
            ->with(['department', 'state'])
            ->where(function ($q) use ($job) {
                $q->where('department_id', $job->department_id)
                  ->orWhere('category_id', $job->category_id)
                  ->orWhere('state_id', $job->state_id);
            })
            ->orderByRaw('CASE WHEN exam_date >= ? THEN 0 ELSE 1 END', [\Carbon\Carbon::today()->toDateString()]) // Upcoming first
            ->orderBy('exam_date', 'asc')
            ->limit($limit)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Related Categories
    |--------------------------------------------------------------------------
    */

    public function getRelatedCategories(JobPost $job, ?int $limit = null): array
    {
        $limit = $limit ?? ($this->config['max_related_categories'] ?? 8);

        $categories = Category::where('is_active', true)
            ->whereHas('jobPosts', fn($q) => $q->published())
            ->withCount(['jobPosts' => fn($q) => $q->published()])
            ->orderByDesc('job_posts_count')
            ->limit($limit)
            ->get();

        return $categories->map(function ($cat) {
            $slug = $cat->slug ?: Str::slug($cat->name);
            // Try to match to a sector route, fallback to search by category
            $sectorKey = $this->matchCategoryToSector($slug);
            $url = $sectorKey
                ? route($this->config['sector_routes'][$sectorKey]['route'] ?? 'search.category', ['category_slug' => $slug])
                : route('search.category', ['category_slug' => $slug]);

            return [
                'id'    => $cat->id,
                'name'  => $cat->name,
                'slug'  => $slug,
                'count' => $cat->job_posts_count,
                'url'   => $url,
            ];
        })->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | 6. State Recommendations
    |--------------------------------------------------------------------------
    */

    public function getStateRecommendations(JobPost $job, ?int $limit = null): \Illuminate\Support\Collection
    {
        $limit = $limit ?? ($this->config['max_state_recommendations'] ?? 6);

        $states = State::whereHas('jobPosts', fn($q) => $q->published())
            ->withCount(['jobPosts' => fn($q) => $q->published()])
            ->orderByRaw(
                $job->state_id
                    ? "CASE WHEN id = {$job->state_id} THEN 0 ELSE 1 END, job_posts_count DESC"
                    : "job_posts_count DESC"
            )
            ->limit($limit)
            ->get();

        return $states->map(function ($state) {
            $slug = $state->slug ?: Str::slug($state->name);
            return [
                'id'    => $state->id,
                'name'  => $state->name,
                'slug'  => $slug,
                'count' => $state->job_posts_count,
                'url'   => route('seo.dynamic_state', ['state_slug' => $slug]),
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Cross-Type Navigation Links (Job Lifecycle)
    |--------------------------------------------------------------------------
    */

    /**
     * For a given job, find related posts of different types in the same
     * department or category — creating the Job → Admit Card → Result → Answer Key lifecycle.
     */
    public function getCrossTypeLinks(JobPost $job): array
    {
        $limit     = $this->config['max_cross_type_links'] ?? 4;
        $routeMap  = $this->config['post_type_routes'] ?? [];
        $labelMap  = $this->config['post_type_labels'] ?? [];

        // All post types except the current one
        $otherTypes = array_keys($routeMap);
        $otherTypes = array_filter($otherTypes, fn($t) => $t !== $job->post_type);

        $links = [];

        foreach ($otherTypes as $postType) {
            $related = JobPost::published()
                ->where('post_type', $postType)
                ->where('id', '!=', $job->id)
                ->where(function ($q) use ($job) {
                    $q->where('department_id', $job->department_id)
                      ->orWhere('category_id', $job->category_id);
                })
                ->orderByDesc('published_at')
                ->first();

            if ($related && isset($routeMap[$postType])) {
                $links[] = [
                    'type'   => $postType,
                    'label'  => $labelMap[$postType] ?? ucfirst(str_replace('_', ' ', $postType)),
                    'title'  => $related->title,
                    'url'    => route($routeMap[$postType], ['slug' => $related->slug ?: Str::slug($related->title)]),
                    'anchor' => $this->generateAnchor($related),
                    'date'   => $related->published_at,
                ];
            }
        }

        return array_slice($links, 0, $limit);
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Anchor Text Generation
    |--------------------------------------------------------------------------
    */

    /**
     * Generate an SEO-optimized anchor text for a job post link.
     * Template: "{Department} {PostType} {Year} - {State}"
     * Truncated to 65 chars (SERP-friendly).
     */
    public function generateAnchor(JobPost $job, string $variant = 'default'): string
    {
        $maxLen = $this->config['anchor_max_length'] ?? 65;

        if ($variant === 'deadline' && $job->last_date_to_apply) {
            $anchor = "{$job->title} - Apply Before " . $job->last_date_to_apply->format('d M Y');
        } elseif ($variant === 'department') {
            $dept = $job->department->name ?? 'Govt';
            $type = $this->config['post_type_labels'][$job->post_type] ?? 'Jobs';
            $year = $job->published_at ? $job->published_at->year : date('Y');
            $state = $job->state->name ?? 'India';
            $anchor = "{$dept} {$type} {$year} - {$state}";
        } else {
            // Default: clean title-based anchor
            $anchor = $job->title;
            if ($job->vacancy_count > 0) {
                $anchor .= " ({$job->vacancy_count} Posts)";
            }
        }

        return Str::limit($anchor, $maxLen, '…');
    }

    /**
     * Generate the canonical URL for a job post based on its post_type.
     */
    public function getDetailUrl(JobPost $job): string
    {
        $routeMap = $this->config['post_type_routes'] ?? [];
        $routeName = $routeMap[$job->post_type] ?? 'seo.job_detail';

        try {
            return route($routeName, ['slug' => $job->slug ?: Str::slug($job->title)]);
        } catch (\Exception $e) {
            return url("/job/" . ($job->slug ?: Str::slug($job->title)));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Link Click Tracking
    |--------------------------------------------------------------------------
    */

    /**
     * Record an internal link click event.
     */
    public function trackClick(array $data): void
    {
        try {
            DB::table('internal_link_clicks')->insert([
                'source_job_post_id' => $data['source_id'],
                'target_job_post_id' => $data['target_id'] ?? null,
                'target_url'         => $data['target_url'] ?? '',
                'link_section'       => $data['section'] ?? 'unknown',
                'anchor_text'        => Str::limit($data['anchor'] ?? '', 255),
                'session_id'         => session()->getId(),
                'clicked_at'         => now(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Exception $e) {
            // Failsafe — never break the user flow for analytics
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cache Management
    |--------------------------------------------------------------------------
    */

    /**
     * Warm the internal links cache for all published posts.
     */
    public function warmCache(?string $postType = null): int
    {
        $query = JobPost::published()
            ->with(['category', 'department', 'state', 'qualification', 'tags']);

        if ($postType) {
            $query->where('post_type', $postType);
        }

        $count = 0;
        $query->chunk(100, function ($jobs) use (&$count) {
            foreach ($jobs as $job) {
                $this->getLinksForDetailPage($job);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Flush all internal linking caches.
     */
    public function flushCache(): void
    {
        // Since we use database cache, we target by key prefix pattern
        try {
            DB::table('cache')
                ->where('key', 'like', '%internal_links:%')
                ->delete();
        } catch (\Exception $e) {
            // Fallback: flush everything (safe for development)
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Helpers
    |--------------------------------------------------------------------------
    */

    protected function getTypeCount(string $postType): int
    {
        return Cache::remember("internal_links:count:{$postType}", 1800, function () use ($postType) {
            return JobPost::published()->where('post_type', $postType)->count();
        });
    }

    protected function matchCategoryToSector(string $slug): ?string
    {
        $mappings = [
            'railway' => ['railway', 'rrb', 'rail'],
            'banking' => ['banking', 'bank', 'finance', 'rbi', 'sbi', 'ibps'],
            'ssc'     => ['ssc', 'staff-selection'],
            'upsc'    => ['upsc', 'civil-service', 'ias'],
            'defence' => ['defence', 'defense', 'police', 'army', 'navy'],
            'psu'     => ['psu', 'public-sector', 'ntpc', 'bhel', 'ongc'],
        ];

        foreach ($mappings as $sector => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($slug, $keyword)) {
                    return $sector;
                }
            }
        }

        return null;
    }

    protected function emptyLinkPackage(): array
    {
        return [
            'related_jobs'          => collect(),
            'related_results'       => collect(),
            'related_admit_cards'   => collect(),
            'categories'            => [],
            'state_recommendations' => collect(),
            'cross_type'            => [],
            'current_job'           => null,
        ];
    }
}
