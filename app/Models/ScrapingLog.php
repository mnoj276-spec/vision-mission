<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapingLog extends Model
{
    use HasFactory;

    protected $table = 'scraping_logs';

    protected $fillable = [
        'scraping_source_id',
        'job_post_id',
        'status',
        'raw_payload',
        'validation_errors',
        'error_message',
        'items_found'
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'validation_errors' => 'array',
    ];

    /**
     * Get the scraping source this log belongs to.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapingSource::class, 'scraping_source_id');
    }

    /**
     * Get the job post that was created by this scraping run (if successful).
     */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
