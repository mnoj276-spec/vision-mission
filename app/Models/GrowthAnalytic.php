<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrowthAnalytic extends Model
{
    use HasFactory;

    protected $table = 'growth_analytics';

    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'page_path',
    ];
}
