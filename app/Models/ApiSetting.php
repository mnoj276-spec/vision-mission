<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'is_encrypted'];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get decrypted value if it is encrypted.
     */
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && !empty($value)) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
    }

    /**
     * Set encrypted value if it is encrypted.
     */
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && !empty($value)) {
            $this->attributes['value'] = encrypt($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
