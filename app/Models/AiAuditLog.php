<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAuditLog extends Model
{
    use HasFactory;

    protected $table = 'ai_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'scraping_source_id',
        'raw_text',
        'extracted_json',
        'confidence_scores',
        'overall_score',
        'status',
    ];

    protected $casts = [
        'extracted_json' => 'array',
        'confidence_scores' => 'array',
        'overall_score' => 'float',
    ];

    /**
     * Get the scraping source associated with this AI audit log.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapingSource::class, 'scraping_source_id');
    }
}
