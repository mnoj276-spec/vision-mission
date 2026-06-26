<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapingSource extends Model
{
    use HasFactory;

    protected $table = 'scraping_sources';

    protected $fillable = [
        'name',
        'source_url',
        'source_type',
        'selectors_config',
        'cron_expression',
        'is_active',
        'detected_features',
        'preferred_engine',
        'cookies',
        'performance_stats'
    ];

    protected $casts = [
        'selectors_config' => 'array',
        'is_active' => 'boolean',
        'detected_features' => 'array',
        'cookies' => 'array',
        'performance_stats' => 'array',
    ];

    /**
     * Get all automation audit logs associated with this scraper source.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ScrapingLog::class);
    }
}
