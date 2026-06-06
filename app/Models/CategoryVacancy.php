<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryVacancy extends Model
{
    use HasFactory;

    protected $table = 'category_vacancies';

    protected $fillable = [
        'job_post_id',
        'category_name',
        'vacancy_count',
    ];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
