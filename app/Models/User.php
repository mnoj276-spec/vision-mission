<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'otp_code',
        'otp_expires_at',
        'is_active',
    ];

    /**
     * Get the user's role dynamically synchronized with Spatie's roles.
     * Prevents breaking any existing code that accesses $user->role directly.
     */
    public function getRoleAttribute($value)
    {
        $spatieRole = $this->roles->first()?->name;
        if ($spatieRole) {
            return strtolower(str_replace(' ', '_', $spatieRole));
        }
        return $value ?? 'candidate';
    }

    /**
     * Boot the model.
     * Listen to the saved event to automatically sync Spatie roles back to the database role column.
     */
    protected static function booted(): void
    {
        static::saved(function ($user) {
            $spatieRole = $user->roles()->first()?->name;
            if ($spatieRole) {
                $mappedRole = strtolower(str_replace(' ', '_', $spatieRole));
                if ($user->getRawOriginal('role') !== $mappedRole) {
                    $user->withoutEvents(function () use ($user, $mappedRole) {
                        $user->update(['role' => $mappedRole]);
                    });
                }
            }
        });

        static::created(function ($user) {
            if ($user->role === 'candidate' && !empty($user->email)) {
                \App\Jobs\SendEmailJob::dispatch($user->email, 'welcome_1', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                ]);
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
