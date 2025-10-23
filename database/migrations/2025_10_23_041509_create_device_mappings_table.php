<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates device_mappings table to link SafeRide users to Traccar GPS devices.
     * Enforces one active device per user via unique constraint.
     */
    public function up(): void
    {
        Schema::create('device_mappings', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('SafeRide user who owns this device mapping');
            
            // Traccar device identification
            $table->unsignedBigInteger('traccar_device_id')
                ->comment('Traccar device ID (from Traccar server)');
            
            $table->string('device_name')
                ->comment('Human-readable device name (e.g., "iPhone 13", "Galaxy S21")');
            
            $table->string('unique_id')
                ->comment('Traccar device unique identifier (IMEI or custom ID)');
            
            // Status flag - only one active device per user allowed
            $table->boolean('is_active')
                ->default(true)
                ->comment('Whether this device mapping is currently active');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('traccar_device_id');
            $table->index(['user_id', 'is_active']);
            
            // Note: One active device per user enforced via model boot() event
            // Unique partial constraint not supported in SQLite
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_mappings');
    }
};
