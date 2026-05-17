<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    /**
     * Get all job postings recruited by this department.
     */
    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }
}
