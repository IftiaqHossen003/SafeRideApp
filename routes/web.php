<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TripViewerController;
use App\Http\Controllers\TrustedContactController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\VolunteerDashboardController;
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
    
    // Volunteer routes
    Route::post('/volunteer/toggle', [VolunteerController::class, 'toggle'])->name('volunteer.toggle');
    Route::get('/volunteer/dashboard', [VolunteerDashboardController::class, 'index'])->name('volunteer.dashboard');
});

Route::resource('trusted-contacts', TrustedContactController::class)->middleware('auth');

// Admin reporting routes
Route::prefix('admin')->middleware(['auth', 'can:view-reports'])->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export', [ReportsController::class, 'exportCsv'])->name('admin.reports.export');
});

// Public trip viewer - no authentication required
Route::get('/trip/view/{share_uuid}', [TripViewerController::class, 'show'])->name('trip.view');

require __DIR__.'/auth.php';
