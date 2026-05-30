<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsPageView extends Model
{
    use HasFactory;

    protected $table = 'analytics_page_views';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'user_id',
        'path',
        'referer',
        'ip_address',
        'user_agent',
        'is_bot',
        'is_organic',
        'created_at',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'is_organic' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
