<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $primaryKey = 'user_id';
    /**
     * Automatically append virtual attributes to model arrays/JSON.
     */
    protected $appends = ['name'];
    protected $fillable = [
        // 'name', // Removed
        'first_name', // Added
        'last_name', // Added
        'email',
        'password',
        'account_type',
        'fishpond_name', // Added
        'barangay',
        'municipality',
        'province',
        'total_fishpond_area',
        'species_cultured',
        'water_type',
        'api_key',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'species_cultured' => 'array',
        ];
    }

    /**
     * Virtual name accessor that combines first and last name.
     * Returns a fallback of 'User' when both are empty.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: 'User'
        );
    }

    public function isAdmin()
    {
        return $this->account_type === 'admin';
    }

    /**
     * Backwards-compatible `id` attribute for tests and libraries
     * that expect `$user->id` when the primary key is `user_id`.
     */
    public function getIdAttribute()
    {
        return $this->getAttribute($this->getKeyName());
    }
}
