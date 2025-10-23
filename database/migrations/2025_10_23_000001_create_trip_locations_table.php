<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores GPS position data for trips, sourced from Traccar or other GPS tracking systems.
     * Each record represents a single position ping during a trip.
     */
    public function up(): void
    {
        Schema::create('trip_locations', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to trips table
            $table->foreignId('trip_id')
                ->constrained('trips')
                ->onDelete('cascade')
                ->comment('Reference to the trip this location belongs to');
            
            // GPS coordinates with 7 decimal precision (~1.11cm accuracy)
            $table->decimal('latitude', 10, 7)
                ->comment('GPS latitude coordinate');
            
            $table->decimal('longitude', 10, 7)
                ->comment('GPS longitude coordinate');
            
            // Optional accuracy information from GPS device
            $table->integer('accuracy')
                ->nullable()
                ->comment('Position accuracy in meters (if provided by device)');
            
            // Additional GPS data
            $table->decimal('speed', 8, 2)
                ->nullable()
                ->comment('Speed in km/h');
            
            $table->decimal('altitude', 8, 2)
                ->nullable()
                ->comment('Altitude in meters');
            
            $table->decimal('bearing', 5, 2)
                ->nullable()
                ->comment('Direction of travel (0-360 degrees)');
            
            // Timestamp when the GPS ping was recorded (from device, not server time)
            $table->timestamp('recorded_at')
                ->comment('Device timestamp when position was recorded');
            
            $table->timestamps();
            
            // Composite index for efficient queries by trip and time range
            $table->index(['trip_id', 'recorded_at'], 'trip_locations_trip_time_idx');
            
            // Index for recorded_at for time-based queries
            $table->index('recorded_at', 'trip_locations_recorded_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_locations');
    }
};
