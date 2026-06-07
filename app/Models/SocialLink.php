<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = ['platform', 'url', 'icon', 'is_active', 'order_index'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
