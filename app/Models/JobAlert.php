<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAlert extends Model
{
    use HasFactory;

    protected $table = 'job_alerts';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'category_name',
    ];
}
