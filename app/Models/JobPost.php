<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\SoftDeletes;

class JobPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'job_posts';

    protected $fillable = [
        'department_id',
        'state_id',
        'district_id',
        'qualification_id',
        'category_id',
        'source_id',
        'post_type',
        'title',
        'slug',
        'description',
        'exam_pattern',
        'selection_process',
        'age_limit',
        'salary_min',
        'salary_max',
        'vacancy_count',
        'application_fee',
        'official_website_link',
        'apply_link',
        'affiliate_link',
        'notification_pdf_path',
        'last_date_to_apply',
        'exam_date',
        'status',
        'published_at',
        'is_featured',
        'is_sponsored',
        'is_historical',
        'fingerprint',
        'expires_at',
        'advertisement_number',
        'pdf_hash',
        'experience_required',
        'start_date',
        'result_date',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'vacancy_count' => 'integer',
        'application_fee' => 'decimal:2',
        'last_date_to_apply' => 'date',
        'exam_date' => 'date',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_historical' => 'boolean',
        'expires_at' => 'date',
        'start_date' => 'date',
        'result_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapingSource::class, 'source_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'job_post_tags', 'job_post_id', 'tag_id');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function categoryVacancies(): HasMany
    {
        return $this->hasMany(CategoryVacancy::class, 'job_post_id');
    }

    /**
     * Get the AI-generated content associated with this job post.
     */
    public function aiContent(): HasOne
    {
        return $this->hasOne(JobPostAiContent::class, 'job_post_id');
    }

    /**
     * All duplicate audit events where this post was the incoming (rejected) record.
     */
    public function duplicationLogs(): HasMany
    {
        return $this->hasMany(DuplicateAuditLog::class, 'job_post_id');
    }

    /**
     * All duplicate audit events where this post was chosen as the canonical master.
     */
    public function duplicatesBlocked(): HasMany
    {
        return $this->hasMany(DuplicateAuditLog::class, 'master_job_post_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes for AJAX Filtering & Searches
    |--------------------------------------------------------------------------
    */

    /**
     * Scopes for post type categorization.
     */
    public function scopeJobs(Builder $query): Builder
    {
        return $query->where('post_type', 'job');
    }

    public function scopeResults(Builder $query): Builder
    {
        return $query->where('post_type', 'result');
    }

    public function scopeAdmitCards(Builder $query): Builder
    {
        return $query->where('post_type', 'admit_card');
    }

    public function scopeAnswerKeys(Builder $query): Builder
    {
        return $query->where('post_type', 'answer_key');
    }

    public function scopeSyllabi(Builder $query): Builder
    {
        return $query->where('post_type', 'syllabus');
    }

    public function scopeNotices(Builder $query): Builder
    {
        return $query->where('post_type', 'notice');
    }

    public function scopeAdmissions(Builder $query): Builder
    {
        return $query->where('post_type', 'admission');
    }

    public function scopeScholarships(Builder $query): Builder
    {
        return $query->where('post_type', 'scholarship');
    }

    /**
     * Scope to retrieve only published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    /**
     * Scope to retrieve featured posts.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to retrieve sponsored posts.
     */
    public function scopeSponsored(Builder $query): Builder
    {
        return $query->where('is_sponsored', true);
    }

    /**
     * Scope to search by key phrases or titles.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        // Clean the search term to prevent syntax errors
        $term = trim(preg_replace('/[+\-><()~*\"@]+/u', ' ', $term));

        if (empty($term)) {
            return $query;
        }

        $connection = $query->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            return $query->whereRaw(
                "MATCH(title, description) AGAINST(? IN BOOLEAN MODE)",
                [$term . '*']
            );
        }

        return $query->where(function ($q) use ($term) {
            $words = array_filter(explode(' ', $term));
            foreach ($words as $word) {
                $q->where(function ($sub) use ($word) {
                    $sub->where('title', 'like', "%{$word}%")
                        ->orWhere('description', 'like', "%{$word}%");
                });
            }
        });
    }

    /**
     * Scope to handle dynamic filter configurations.
     */
    public function scopeFilterBy(Builder $query, array $filters): Builder
    {
        return $query->when(!empty($filters['state_id']), function ($q) use ($filters) {
            $q->where('state_id', $filters['state_id']);
        })->when(!empty($filters['district_id']), function ($q) use ($filters) {
            $q->where('district_id', $filters['district_id']);
        })->when(!empty($filters['category_id']), function ($q) use ($filters) {
            $q->where('category_id', $filters['category_id']);
        })->when(!empty($filters['qualification_id']), function ($q) use ($filters) {
            $q->where('qualification_id', $filters['qualification_id']);
        })->when(!empty($filters['department_id']), function ($q) use ($filters) {
            $q->where('department_id', $filters['department_id']);
        })->when(isset($filters['min_salary']), function ($q) use ($filters) {
            $q->where('salary_max', '>=', $filters['min_salary']);
        })->when(isset($filters['has_no_fee']) && $filters['has_no_fee'] == true, function ($q) {
            $q->where('application_fee', 0);
        });
    }
}
