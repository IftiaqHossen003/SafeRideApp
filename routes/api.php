<?php

use App\Http\Controllers\SosController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

// Trip API routes - require authentication
Route::middleware('auth')->group(function () {
    Route::post('/trips/start', [TripController::class, 'startTrip']);
    Route::patch('/trips/{trip}/location', [TripController::class, 'updateLocation']);
    Route::post('/trips/{trip}/end', [TripController::class, 'endTrip']);
    
    // SOS Alert routes
    Route::post('/sos', [SosController::class, 'store']);
});
