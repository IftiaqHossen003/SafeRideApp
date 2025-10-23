<?php

use App\Http\Controllers\SosController;
use App\Http\Controllers\TraccarWebhookController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

// Traccar webhook routes - NO authentication (uses token validation)
Route::prefix('traccar')->group(function () {
    Route::post('/webhook', [TraccarWebhookController::class, 'handlePositionUpdate']);
    Route::get('/webhook/health', [TraccarWebhookController::class, 'healthCheck']);
});

// Trip API routes - require authentication
Route::middleware('auth')->group(function () {
    Route::post('/trips/start', [TripController::class, 'startTrip']);
    Route::patch('/trips/{trip}/location', [TripController::class, 'updateLocation']);
    Route::post('/trips/{trip}/end', [TripController::class, 'endTrip']);
    
    // SOS Alert routes
    Route::post('/sos', [SosController::class, 'store']);
});
