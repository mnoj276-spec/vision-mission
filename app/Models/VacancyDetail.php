<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyDetail extends Model
{
    use HasFactory;

    protected $table = 'vacancy_details';

    protected $fillable = [
        'job_post_id',
        'post_name',
        'total_post',
        'eligibility',
        'sort_order',
    ];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
