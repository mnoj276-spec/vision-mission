<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'key',
        'value',
        'type',
        'options',
        'display_name',
        'description',
        'is_secret'
    ];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SettingGroup::class, 'group_id');
    }

    /**
     * Get decrypted value if it is secret.
     */
    public function getValueAttribute($value)
    {
        if ($this->is_secret && !empty($value)) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
    }

    /**
     * Set encrypted value if it is secret.
     */
    public function setValueAttribute($value)
    {
        if ($this->is_secret && !empty($value)) {
            $this->attributes['value'] = encrypt($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
