<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DuplicateAuditLog
 *
 * Records every duplicate detection event with full forensic detail.
 * One row is written for each incoming scraped item that is blocked by
 * any of the 3 pipeline stages:
 *   - Stage 1 (fingerprint)   — exact SHA-256 collision
 *   - Stage 2 (fuzzy)         — similar_text() ≥ 85 %
 *   - Stage 3 (title_variant) — year-stripped / acronym-expanded match
 *
 * @property int|null    $job_post_id          The incoming (rejected) record
 * @property int|null    $master_job_post_id   The existing master record
 * @property string      $detection_method     fingerprint | fuzzy | title_variant
 * @property float|null  $similarity_score     Percentage (fuzzy/variant only)
 * @property string      $incoming_fingerprint SHA-256 of the incoming payload
 * @property string|null $master_fingerprint   SHA-256 of the master record
 * @property array|null  $raw_payload          Full scraped item that was rejected
 * @property \Carbon\Carbon|null $resolved_at  Set when admin marks as reviewed
 */
class DuplicateAuditLog extends Model
{
    protected $table = 'duplicate_audit_logs';

    protected $fillable = [
        'job_post_id',
        'master_job_post_id',
        'detection_method',
        'similarity_score',
        'incoming_fingerprint',
        'master_fingerprint',
        'raw_payload',
        'resolved_at',
    ];

    protected $casts = [
        'similarity_score' => 'float',
        'raw_payload'      => 'array',
        'resolved_at'      => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The incoming scraped record that was blocked (may be null if never inserted).
     */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    /**
     * The existing canonical master record that the incoming item collided with.
     */
    public function masterJobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'master_job_post_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Filter to only unresolved duplicate events (pending admin review).
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Filter by detection method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('detection_method', $method);
    }
}
