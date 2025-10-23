<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RouteAlert Model
 *
 * Represents a route anomaly alert (deviation or stoppage) for a trip.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $trip_id
 * @property string $alert_type
 * @property array|null $details
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Trip $trip
 */
class RouteAlert extends Model
{
    use HasFactory;

    /**
     * Alert type constants
     */
    const TYPE_DEVIATION = 'deviation';
    const TYPE_STOPPAGE = 'stoppage';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'route_alerts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trip_id',
        'alert_type',
        'details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * Get the trip that owns the route alert.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Check if this is a deviation alert.
     *
     * @return bool
     */
    public function isDeviation(): bool
    {
        return $this->alert_type === self::TYPE_DEVIATION;
    }

    /**
     * Check if this is a stoppage alert.
     *
     * @return bool
     */
    public function isStoppage(): bool
    {
        return $this->alert_type === self::TYPE_STOPPAGE;
    }
}
