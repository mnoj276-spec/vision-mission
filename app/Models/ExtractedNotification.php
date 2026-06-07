<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractedNotification extends Model
{
    use HasFactory;

    protected $table = 'extracted_notifications';

    protected $fillable = [
        'file_path',
        'original_filename',
        'file_type',
        'raw_text',
        'extracted_data',
        'validation_status',
        'validation_errors',
        'status',
        'job_post_id',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'validation_errors' => 'array',
    ];

    /**
     * Get the job post generated from this notification, if approved.
     */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
