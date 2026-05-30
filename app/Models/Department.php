<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'slug'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($department) {
            if (empty($department->slug)) {
                $baseSlug = str()->slug($department->name) ?: (str()->slug($department->code) ?: 'dept');
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->where('id', '!=', $department->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $department->slug = $slug;
            }
        });
    }

    /**
     * Get all job postings recruited by this department.
     */
    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }
}
