<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    /**
     * Get all job postings located in this state.
     */
    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }
}
