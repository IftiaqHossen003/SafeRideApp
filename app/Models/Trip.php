<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trip Model
 *
 * Represents a user's trip with location tracking in the SafeRide application.
 */
class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'current_lat',
        'current_lng',
        'share_uuid',
        'status',
        'started_at',
        'ended_at',
        'last_location_update_at',
        'traccar_device_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'origin_lat' => 'decimal:7',
            'origin_lng' => 'decimal:7',
            'destination_lat' => 'decimal:7',
            'destination_lng' => 'decimal:7',
            'current_lat' => 'decimal:7',
            'current_lng' => 'decimal:7',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'last_location_update_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the trip.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route alerts for the trip.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function routeAlerts(): HasMany
    {
        return $this->hasMany(RouteAlert::class);
    }

    /**
     * Get the SOS alerts for the trip.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sosAlerts(): HasMany
    {
        return $this->hasMany(SosAlert::class);
    }

    /**
     * Get the GPS location history for the trip.
     *
     * Locations are typically sourced from Traccar or other GPS tracking systems.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(TripLocation::class);
    }
}
