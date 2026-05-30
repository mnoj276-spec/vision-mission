<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPostAiContent extends Model
{
    use HasFactory;

    protected $table = 'job_post_ai_contents';

    protected $fillable = [
        'job_post_id',
        'provider',
        'summary',
        'eligibility',
        'selection_process',
        'faqs',
        'meta_title',
        'meta_description',
        'schema_content',
        'status',
        'error_message',
    ];

    protected $casts = [
        'faqs' => 'array',
        'schema_content' => 'array',
    ];

    /**
     * Get the associated job posting.
     */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
