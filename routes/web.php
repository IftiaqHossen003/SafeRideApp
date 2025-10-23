<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SosController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripViewerController;
use App\Http\Controllers\TrustedContactController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\VolunteerDashboardController;
use App\Http\Controllers\DeviceMappingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Trips routes - Live Trip Sharing & Route Monitoring
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::post('/trips/start', [TripController::class, 'startTrip'])->name('trips.start');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::patch('/trips/{trip}/location', [TripController::class, 'updateLocation'])->name('trips.update-location');
    Route::patch('/trips/{trip}/end', [TripController::class, 'endTrip'])->name('trips.end');
    Route::get('/trips/history', [TripController::class, 'history'])->name('trips.history');
    
    // SOS Alert routes - Emergency SOS Alerts
    Route::get('/sos-alerts', [SosController::class, 'index'])->name('sos-alerts.index');
    Route::post('/sos', [SosController::class, 'store'])->name('sos.store');
    
    // Volunteer routes - Community Guardian Mode
    Route::post('/volunteer/toggle', [VolunteerController::class, 'toggle'])->name('volunteer.toggle');
    Route::get('/volunteer/dashboard', [VolunteerDashboardController::class, 'index'])->name('volunteer.dashboard');
    Route::post('/volunteer/respond/{alert}', [VolunteerDashboardController::class, 'respond'])->name('volunteer.respond');
});

// Trusted Contacts routes - Trusted Circle System
Route::resource('trusted-contacts', TrustedContactController::class)->middleware('auth');

// Device Mapping routes - GPS Device Management
Route::middleware(['auth'])->group(function () {
    Route::get('/my-devices', [DeviceMappingController::class, 'myDevices'])->name('my-devices');
    Route::post('/my-devices/{deviceMapping}/set-active', [DeviceMappingController::class, 'setActive'])->name('my-devices.set-active');
});

// Admin device mapping routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('device-mappings', DeviceMappingController::class);
    Route::post('/device-mappings/{deviceMapping}/toggle', [DeviceMappingController::class, 'toggleActive'])->name('device-mappings.toggle');
});

// Admin reporting routes - Trip History & Reports
Route::prefix('admin')->middleware(['auth', 'can:view-reports'])->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export', [ReportsController::class, 'exportCsv'])->name('admin.reports.export');
});

// Public trip viewer - Live Trip Sharing (Privacy Control)
Route::get('/trip/view/{share_uuid}', [TripViewerController::class, 'show'])->name('trip.view');

require __DIR__.'/auth.php';
