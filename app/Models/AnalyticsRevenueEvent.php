<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsRevenueEvent extends Model
{
    use HasFactory;

    protected $table = 'analytics_revenue_events';

    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'slot_name',
        'estimated_revenue',
        'job_post_id',
        'session_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'estimated_revenue' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
