<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pseudonym',
        'is_volunteer',
        'is_admin',
    ];

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
            'is_volunteer' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the trusted contacts for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trustedContacts(): HasMany
    {
        return $this->hasMany(TrustedContact::class);
    }

    /**
     * Get the trips for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Get the SOS alerts for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sosAlerts(): HasMany
    {
        return $this->hasMany(SosAlert::class);
    }

    /**
     * Get the device mappings for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deviceMappings(): HasMany
    {
        return $this->hasMany(DeviceMapping::class);
    }

    /**
     * Get the active device mapping for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeDeviceMapping(): HasMany
    {
        return $this->hasMany(DeviceMapping::class)->where('is_active', true);
    }
}
