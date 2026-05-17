<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Qualification extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    /**
     * Get all job postings that require this qualification.
     */
    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }
}
