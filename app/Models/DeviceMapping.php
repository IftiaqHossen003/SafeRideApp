<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * DeviceMapping Model
 * 
 * Links SafeRide users to Traccar GPS devices.
 * Enforces business rule: only one active device per user.
 */
class DeviceMapping extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'traccar_device_id',
        'device_name',
        'unique_id',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'traccar_device_id' => 'integer',
        ];
    }

    /**
     * Get the user that owns this device mapping.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active device mappings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get device mappings for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the active device mapping for a specific user.
     * 
     * @param int $userId
     * @return DeviceMapping|null
     */
    public static function getActiveForUser(int $userId): ?DeviceMapping
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Deactivate all other device mappings for this user.
     * Called before activating a new device.
     * 
     * @return void
     */
    public function deactivateOtherDevices(): void
    {
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
    }

    /**
     * Boot method to ensure only one active device per user.
     */
    protected static function boot()
    {
        parent::boot();

        // Before creating/updating, ensure only one active device per user
        static::saving(function ($deviceMapping) {
            if ($deviceMapping->is_active) {
                // Deactivate all other devices for this user
                self::where('user_id', $deviceMapping->user_id)
                    ->where('id', '!=', $deviceMapping->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}