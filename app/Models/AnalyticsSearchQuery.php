<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSearchQuery extends Model
{
    use HasFactory;

    protected $table = 'analytics_search_queries';

    public $timestamps = false;

    protected $fillable = [
        'query',
        'filters',
        'results_count',
        'session_id',
        'user_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
