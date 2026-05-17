<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'action',
        'details'
    ];

    /**
     * Get the user who executed this administrative or system action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
