<?php

namespace App\Console\Commands;

use App\Events\TripLocationUpdated;
use App\Models\Trip;
use App\Models\TripLocation;
use App\Services\TraccarService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * TraccarFetch Command
 *
 * Fetch GPS positions from Traccar server and store them in trip_locations table.
 * Intended to be run periodically via cron/scheduler.
 *
 * Usage:
 *   php artisan traccar:fetch               # Fetch for all active trips (last 24h)
 *   php artisan traccar:fetch --trip=123    # Fetch for specific trip
 *   php artisan traccar:fetch --hours=48    # Fetch last 48 hours
 */
class TraccarFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traccar:fetch
                            {--trip= : Specific Trip ID to sync}
                            {--hours=24 : Number of hours to look back}
                            {--device= : Specific Traccar device ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch GPS positions from Traccar and store in trip_locations';

    /**
     * Traccar service instance
     *
     * @var TraccarService
     */
    protected TraccarService $traccar;

    /**
     * Create a new command instance.
     */
    public function __construct(TraccarService $traccar)
    {
        parent::__construct();
        $this->traccar = $traccar;
    }

    /**
     * Execute the console command.
     *
     * @return int Command exit code
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Traccar position fetch...');

        $tripId = $this->option('trip');
        $hours = (int) $this->option('hours');
        $deviceId = $this->option('device') ? (int) $this->option('device') : null;

        // Calculate time range
        $to = Carbon::now();
        $from = $to->copy()->subHours($hours);

        $this->info("📅 Time range: {$from->toDateTimeString()} → {$to->toDateTimeString()}");

        try {
            if ($tripId) {
                // Sync specific trip
                $this->syncTripPositions((int) $tripId, $from, $to);
            } elseif ($deviceId) {
                // Sync by device ID
                $this->syncDevicePositions($deviceId, $from, $to);
            } else {
                // Sync all active trips with device mappings
                $this->syncAllActiveTrips($from, $to);
            }

            $this->info('✅ Traccar fetch completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Traccar fetch failed: ' . $e->getMessage());
            Log::error('Traccar fetch command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Sync positions for a specific trip.
     *
     * @param  int     $tripId
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return void
     */
    protected function syncTripPositions(int $tripId, Carbon $from, Carbon $to): void
    {
        $trip = Trip::find($tripId);

        if (!$trip) {
            $this->error("Trip #{$tripId} not found.");
            return;
        }

        $this->info("🔄 Syncing Trip #{$trip->id}: {$trip->start_location} → {$trip->end_location}");

        // Check if trip has device mapping (PART C feature)
        $deviceId = $trip->traccar_device_id ?? null;

        if (!$deviceId) {
            $this->warn("⚠️  Trip #{$trip->id} has no Traccar device mapping. Skipping.");
            return;
        }

        // Fetch positions from Traccar
        $positions = $this->traccar->fetchDevicePositionsForTimeRange(
            $from->toDateTime(),
            $to->toDateTime(),
            $deviceId
        );

        $this->info("📍 Fetched " . count($positions) . " positions from Traccar");

        // Insert positions into trip_locations
        $insertedCount = $this->insertPositions($trip, $positions);

        $this->info("💾 Inserted {$insertedCount} new position records");
    }

    /**
     * Sync positions for a specific device ID.
     *
     * @param  int     $deviceId
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return void
     */
    protected function syncDevicePositions(int $deviceId, Carbon $from, Carbon $to): void
    {
        $this->info("🔄 Syncing device #{$deviceId}");

        // Fetch positions from Traccar
        $positions = $this->traccar->fetchDevicePositionsForTimeRange(
            $from->toDateTime(),
            $to->toDateTime(),
            $deviceId
        );

        $this->info("📍 Fetched " . count($positions) . " positions");

        // Find trip(s) associated with this device
        $trips = Trip::where('traccar_device_id', $deviceId)
            ->where('status', 'in_progress')
            ->get();

        if ($trips->isEmpty()) {
            $this->warn("⚠️  No active trips found for device #{$deviceId}");
            return;
        }

        foreach ($trips as $trip) {
            $insertedCount = $this->insertPositions($trip, $positions);
            $this->info("💾 Trip #{$trip->id}: Inserted {$insertedCount} positions");
        }
    }

    /**
     * Sync positions for all active trips with device mappings.
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return void
     */
    protected function syncAllActiveTrips(Carbon $from, Carbon $to): void
    {
        $this->info('🔄 Syncing all active trips with Traccar devices...');

        // Get all active/in-progress trips that have device mapping
        $trips = Trip::where('status', 'in_progress')
            ->whereNotNull('traccar_device_id')
            ->get();

        if ($trips->isEmpty()) {
            $this->info('ℹ️  No active trips with device mappings found.');
            return;
        }

        $this->info("Found {$trips->count()} active trip(s)");

        $progressBar = $this->output->createProgressBar($trips->count());
        $progressBar->start();

        $totalInserted = 0;

        foreach ($trips as $trip) {
            try {
                $positions = $this->traccar->fetchDevicePositionsForTimeRange(
                    $from->toDateTime(),
                    $to->toDateTime(),
                    $trip->traccar_device_id
                );

                $insertedCount = $this->insertPositions($trip, $positions);
                $totalInserted += $insertedCount;
            } catch (\Exception $e) {
                Log::error("Failed to sync trip #{$trip->id}", [
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("💾 Total positions inserted: {$totalInserted}");
    }

    /**
     * Insert positions into trip_locations table.
     * Avoids duplicates by checking existing records.
     *
     * @param  Trip   $trip
     * @param  array  $positions  Array of Traccar position objects
     * @return int    Number of inserted records
     */
    protected function insertPositions(Trip $trip, array $positions): int
    {
        $insertedCount = 0;

        foreach ($positions as $position) {
            // Parse recorded timestamp (use fixTime as the GPS timestamp)
            $recordedAt = Carbon::parse($position['fixTime']);

            // Check if position already exists (avoid duplicates)
            $exists = TripLocation::where('trip_id', $trip->id)
                ->where('recorded_at', $recordedAt)
                ->where('latitude', $position['latitude'])
                ->where('longitude', $position['longitude'])
                ->exists();

            if ($exists) {
                continue; // Skip duplicate
            }

            // Create new trip location record
            TripLocation::create([
                'trip_id' => $trip->id,
                'latitude' => $position['latitude'],
                'longitude' => $position['longitude'],
                'accuracy' => $position['accuracy'] ?? null,
                'speed' => $position['speed'] ?? null,
                'altitude' => $position['altitude'] ?? null,
                'bearing' => $position['course'] ?? null,
                'recorded_at' => $recordedAt,
            ]);

            $insertedCount++;
        }

        // Broadcast location update event if positions were inserted
        if ($insertedCount > 0) {
            // Update trip's current location to latest position
            $latestPosition = end($positions);
            if ($latestPosition) {
                $trip->update([
                    'current_lat' => $latestPosition['latitude'],
                    'current_lng' => $latestPosition['longitude'],
                    'last_location_update_at' => now(),
                ]);
            }

            // Broadcast event
            broadcast(new TripLocationUpdated($trip))->toOthers();
        }

        return $insertedCount;
    }
}
