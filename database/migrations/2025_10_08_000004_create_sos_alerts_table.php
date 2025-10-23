<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for creating the sos_alerts table.
 *
 * This table stores SOS emergency alerts triggered by users during trips
 * or standalone emergency situations.
 *
 * @package Database\Migrations
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
        Schema::create('sos_alerts', function (Blueprint $table) {
            $table->id();
            
            // User who triggered the SOS (nullable for anonymous alerts)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');
            
            // Associated trip (nullable if not during a trip)
            $table->foreignId('trip_id')
                ->nullable()
                ->constrained('trips')
                ->onDelete('cascade');
            
            // Location coordinates where SOS was triggered
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            
            // Optional message from user
            $table->text('message')->nullable();
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            
            // Volunteer/responder who resolved the alert
            $table->foreignId('responder_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            // Indexes for common queries
            $table->index('user_id');
            $table->index('trip_id');
            $table->index('created_at');
            $table->index(['resolved_at', 'created_at']); // For finding unresolved alerts
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sos_alerts');
    }
};
