<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryWiseVacancy extends Model
{
    use HasFactory;

    protected $table = 'category_wise_vacancies';

    protected $fillable = [
        'job_post_id',
        'post_name',
        'ur',
        'ews',
        'ebc',
        'bc',
        'bc_female',
        'sc',
        'st',
        'total',
        'sort_order',
    ];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
