<?php

namespace App\Domains\Jobs\Services;

use App\Domains\Jobs\Services\Contracts\SearchServiceInterface;
use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\Qualification;
use App\Models\State;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchService implements SearchServiceInterface
{
    /**
     * Search job postings with relevance scoring under MySQL, standard filters, pagination, and dynamic caching.
     */
    public function searchJobs(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        // Construct a unique cache key based on search terms, filters, and page index
        $page = request('page', 1);
        $cacheKey = 'jobs_search_results_' . md5(serialize($filters) . '_page_' . $page);

        // Cache search results for 5 minutes to protect DB under peak traffic spikes
        return Cache::remember($cacheKey, 300, function () use ($filters, $perPage) {
            $query = JobPost::query()->published();

            // 1. Relevance-Ranked Full-Text Search
            if (!empty($filters['search'])) {
                $term = trim($filters['search']);
                // Clean the term to avoid boolean query parser errors
                $termClean = trim(preg_replace('/[+\-><()~*\"@]+/u', ' ', $term));

                if (!empty($termClean)) {
                    $driver = DB::getDriverName();
                    if ($driver === 'mysql') {
                        $query->whereRaw(
                            "MATCH(title, description) AGAINST(? IN BOOLEAN MODE)",
                            [$termClean . '*']
                        );
                        // Add select for relevance ranking to sort accordingly
                        $query->select('job_posts.*')
                            ->selectRaw("MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance", [$termClean])
                            ->orderByDesc('relevance');
                    } else {
                        // SQLITE or standard database fallback using wildcard LIKE
                        $words = array_filter(explode(' ', $termClean));
                        $query->where(function ($q) use ($words) {
                            foreach ($words as $word) {
                                $q->where(function ($sub) use ($word) {
                                    $sub->where('title', 'like', "%{$word}%")
                                        ->orWhere('description', 'like', "%{$word}%");
                                });
                            }
                        });
                    }
                }
            }

            // 2. State & Region Filtering (supports ID and SEO slug)
            if (!empty($filters['state_id'])) {
                $query->where('state_id', $filters['state_id']);
            } elseif (!empty($filters['state_slug'])) {
                $query->whereHas('state', function ($q) use ($filters) {
                    $q->where('slug', $filters['state_slug']);
                });
            }

            // 3. Category Filtering (supports ID and SEO slug)
            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            } elseif (!empty($filters['category_slug'])) {
                $query->whereHas('category', function ($q) use ($filters) {
                    $q->where('slug', $filters['category_slug']);
                });
            }

            // 4. Qualification Filtering (supports ID and SEO slug)
            if (!empty($filters['qualification_id'])) {
                $query->where('qualification_id', $filters['qualification_id']);
            } elseif (!empty($filters['qualification_slug'])) {
                $query->whereHas('qualification', function ($q) use ($filters) {
                    $q->where('slug', $filters['qualification_slug']);
                });
            }

            // 5. Department/Organization Filtering (supports ID and SEO slug)
            if (!empty($filters['department_id'])) {
                $query->where('department_id', $filters['department_id']);
            } elseif (!empty($filters['department_slug'])) {
                $query->whereHas('department', function ($q) use ($filters) {
                    $q->where('slug', $filters['department_slug']);
                });
            }

            // 6. Base Salary & Fee filters
            if (isset($filters['min_salary'])) {
                $query->where('salary_max', '>=', $filters['min_salary']);
            }
            if (isset($filters['has_no_fee']) && filter_var($filters['has_no_fee'], FILTER_VALIDATE_BOOLEAN)) {
                $query->where('application_fee', 0);
            }

            // Eager-load relations to prevent N+1 queries
            $query->with(['category', 'department', 'state', 'qualification', 'district']);

            // Secondary sorting criteria
            $query->orderBy('is_featured', 'desc')
                  ->orderBy('published_at', 'desc')
                  ->orderBy('id', 'desc');

            return $query->paginate($perPage);
        });
    }

    /**
     * Generate autocomplete suggestions grouped by category with 1-hour caching.
     */
    public function getAutocompleteSuggestions(string $query): array
    {
        $cleanQuery = strtolower(trim($query));
        if (strlen($cleanQuery) < 2) {
            return [
                'jobs' => [],
                'categories' => [],
                'states' => [],
                'qualifications' => [],
                'departments' => []
            ];
        }

        $cacheKey = 'autocomplete_suggest_' . md5($cleanQuery);

        return Cache::remember($cacheKey, 3600, function () use ($cleanQuery) {
            // Match Job Post Titles
            $jobs = JobPost::published()
                ->where('title', 'like', "%{$cleanQuery}%")
                ->limit(5)
                ->get(['title', 'slug', 'post_type'])
                ->map(fn($j) => [
                    'title' => $j->title,
                    'slug' => $j->slug,
                    'post_type' => $j->post_type
                ])->toArray();

            // Match Category Names
            $categories = Category::where('is_active', true)
                ->where('name', 'like', "%{$cleanQuery}%")
                ->limit(3)
                ->get(['name', 'slug'])
                ->map(fn($c) => [
                    'name' => $c->name,
                    'slug' => $c->slug
                ])->toArray();

            // Match State Names
            $states = State::where('name', 'like', "%{$cleanQuery}%")
                ->limit(3)
                ->get(['name', 'slug'])
                ->map(fn($s) => [
                    'name' => $s->name,
                    'slug' => $s->slug
                ])->toArray();

            // Match Qualification Names
            $qualifications = Qualification::where('name', 'like', "%{$cleanQuery}%")
                ->limit(3)
                ->get(['name', 'slug'])
                ->map(fn($q) => [
                    'name' => $q->name,
                    'slug' => $q->slug
                ])->toArray();

            // Match Department Names/Codes
            $departments = Department::where('name', 'like', "%{$cleanQuery}%")
                ->orWhere('code', 'like', "%{$cleanQuery}%")
                ->limit(3)
                ->get(['name', 'slug', 'code'])
                ->map(fn($d) => [
                    'name' => $d->name,
                    'code' => $d->code,
                    'slug' => $d->slug
                ])->toArray();

            return [
                'jobs' => $jobs,
                'categories' => $categories,
                'states' => $states,
                'qualifications' => $qualifications,
                'departments' => $departments
            ];
        });
    }

    /**
     * Compute spelling corrections based on levenshtein edits over vocabulary.
     */
    public function getSpellCorrection(string $query): ?string
    {
        // Load or auto-rebuild vocabulary
        $vocab = Cache::remember('search_vocabulary', 86400, function () {
            $this->rebuildVocabulary();
            return Cache::get('search_vocabulary', []);
        });

        if (empty($vocab)) {
            return null;
        }

        $words = array_filter(explode(' ', strtolower(trim($query))));
        $correctedWords = [];
        $changed = false;

        foreach ($words as $word) {
            // Keep very short words or numbers unchanged
            if (strlen($word) < 3 || is_numeric($word)) {
                $correctedWords[] = $word;
                continue;
            }

            // Word exists in dictionary, keep it
            if (in_array($word, $vocab)) {
                $correctedWords[] = $word;
                continue;
            }

            $closestWord = null;
            $shortestDistance = -1;

            // Loop through vocabulary to calculate Levenshtein distance
            foreach ($vocab as $vocabWord) {
                $lev = levenshtein($word, $vocabWord);

                if ($lev === 0) {
                    $closestWord = $vocabWord;
                    $shortestDistance = 0;
                    break;
                }

                // If edit distance is <= 2 edits, we treat it as a plausible candidate
                if ($lev <= 2 && ($shortestDistance === -1 || $lev < $shortestDistance)) {
                    $closestWord = $vocabWord;
                    $shortestDistance = $lev;
                }
            }

            if ($shortestDistance > 0 && $closestWord) {
                $correctedWords[] = $closestWord;
                $changed = true;
            } else {
                $correctedWords[] = $word;
            }
        }

        return $changed ? implode(' ', $correctedWords) : null;
    }

    /**
     * Rebuild the vocabulary dictionary cached for spell check.
     */
    public function rebuildVocabulary(): void
    {
        $dictionary = [];
        $stopWords = ['and', 'the', 'for', 'with', 'from', 'online', 'apply', 'date', 'form', 'exam', 'post', 'posts', 'jobs', 'vacancy', 'vacancies'];

        // 1. Pull from Category Names
        foreach (Category::pluck('name') as $name) {
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
            $dictionary = array_merge($dictionary, explode(' ', $cleanName));
        }

        // 2. Pull from State Names
        foreach (State::pluck('name') as $name) {
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
            $dictionary = array_merge($dictionary, explode(' ', $cleanName));
        }

        // 3. Pull from Qualification Names
        foreach (Qualification::pluck('name') as $name) {
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
            $dictionary = array_merge($dictionary, explode(' ', $cleanName));
        }

        // 4. Pull from Department Names and Codes
        foreach (Department::all() as $dept) {
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $dept->name));
            $dictionary = array_merge($dictionary, explode(' ', $cleanName));
            $dictionary[] = strtolower($dept->code);
        }

        // 5. Parse published job titles for popular keywords
        $titles = JobPost::published()->pluck('title')->toArray();
        foreach ($titles as $title) {
            $cleanTitle = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $title));
            $tokens = array_filter(explode(' ', $cleanTitle));
            foreach ($tokens as $token) {
                if (strlen($token) >= 3 && !in_array($token, $stopWords) && !is_numeric($token)) {
                    $dictionary[] = $token;
                }
            }
        }

        // Filter out empty spaces, extremely short tokens, or stop words
        $dictionary = array_filter($dictionary, function ($word) use ($stopWords) {
            return strlen($word) >= 3 && !in_array($word, $stopWords);
        });

        // Deduplicate vocabulary index
        $dictionary = array_unique($dictionary);

        Cache::forever('search_vocabulary', array_values($dictionary));
    }
}
