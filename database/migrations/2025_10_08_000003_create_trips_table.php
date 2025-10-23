<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create trips table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Origin coordinates
            $table->decimal('origin_lat', 10, 7);
            $table->decimal('origin_lng', 10, 7);
            
            // Destination coordinates
            $table->decimal('destination_lat', 10, 7);
            $table->decimal('destination_lng', 10, 7);
            
            // Current location (nullable for tracking)
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            
            // Share UUID for public sharing
            $table->uuid('share_uuid')->unique();
            
            // Trip status
            $table->enum('status', ['ongoing', 'completed', 'cancelled'])->default('ongoing');
            
            // Timestamps for trip lifecycle
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
