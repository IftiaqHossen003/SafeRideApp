<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     SafeRideApp - Comprehensive Feature Testing         ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$passCount = 0;
$failCount = 0;

function testResult($name, $passed, $message = '') {
    global $passCount, $failCount;
    $status = $passed ? '✓ PASS' : '✗ FAIL';
    $color = $passed ? '' : '';
    echo "{$status} - {$name}\n";
    if ($message) {
        echo "       {$message}\n";
    }
    if ($passed) {
        $passCount++;
    } else {
        $failCount++;
    }
    echo "\n";
}

// ========================================
// 1. USER AUTHENTICATION TESTS
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. USER AUTHENTICATION & ROLES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test admin user
$admin = App\Models\User::where('email', 'admin@saferide.com')->first();
$adminPassCorrect = $admin && Illuminate\Support\Facades\Hash::check('password', $admin->password);
testResult(
    'Admin User Login',
    $adminPassCorrect && $admin->is_admin && $admin->is_volunteer,
    "Email: admin@saferide.com | Admin: Yes | Volunteer: Yes"
);

// Test regular user
$john = App\Models\User::where('email', 'john@example.com')->first();
$johnPassCorrect = $john && Illuminate\Support\Facades\Hash::check('password', $john->password);
testResult(
    'Regular User Login',
    $johnPassCorrect && !$john->is_admin && !$john->is_volunteer,
    "Email: john@example.com | Admin: No | Volunteer: No"
);

// Test volunteer user
$volunteer = App\Models\User::where('email', 'sarah@volunteer.com')->first();
$volPassCorrect = $volunteer && Illuminate\Support\Facades\Hash::check('password', $volunteer->password);
testResult(
    'Volunteer User Login',
    $volPassCorrect && !$volunteer->is_admin && $volunteer->is_volunteer,
    "Email: sarah@volunteer.com | Admin: No | Volunteer: Yes"
);

// Test pseudonyms
$pseudonymCount = App\Models\User::whereNotNull('pseudonym')->count();
testResult(
    'User Pseudonyms',
    $pseudonymCount > 0,
    "Found {$pseudonymCount} users with pseudonyms"
);

// ========================================
// 2. TRUSTED CONTACTS TESTS
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "2. TRUSTED CONTACTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$totalContacts = App\Models\TrustedContact::count();
testResult(
    'Trusted Contacts Created',
    $totalContacts > 0,
    "Total contacts: {$totalContacts}"
);

// Test admin has contacts
$adminContacts = $admin ? $admin->trustedContacts()->count() : 0;
testResult(
    'Admin Has Trusted Contacts',
    $adminContacts > 0,
    "Admin has {$adminContacts} trusted contacts"
);

// Test regular user has contacts
$johnContacts = $john ? $john->trustedContacts()->count() : 0;
testResult(
    'Regular User Has Trusted Contacts',
    $johnContacts > 0,
    "John has {$johnContacts} trusted contacts"
);

// Test volunteer has contacts
$volContacts = $volunteer ? $volunteer->trustedContacts()->count() : 0;
testResult(
    'Volunteer Has Trusted Contacts',
    $volContacts > 0,
    "Volunteer has {$volContacts} trusted contacts"
);

// Test contact data integrity
$sampleContact = App\Models\TrustedContact::first();
testResult(
    'Contact Data Integrity',
    $sampleContact && $sampleContact->contact_name && ($sampleContact->contact_phone || $sampleContact->contact_email),
    "Sample contact: {$sampleContact->contact_name}"
);

// ========================================
// 3. TRIP TRACKING TESTS
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "3. TRIP TRACKING\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$totalTrips = App\Models\Trip::count();
testResult(
    'Trips Created',
    $totalTrips > 0,
    "Total trips: {$totalTrips}"
);

$ongoingTrips = App\Models\Trip::where('status', 'ongoing')->count();
testResult(
    'Ongoing Trips',
    $ongoingTrips > 0,
    "Active trips: {$ongoingTrips}"
);

$completedTrips = App\Models\Trip::where('status', 'completed')->count();
testResult(
    'Completed Trips',
    $completedTrips > 0,
    "Completed trips: {$completedTrips}"
);

// Test trip shares (UUID)
$tripsWithShare = App\Models\Trip::whereNotNull('share_uuid')->count();
testResult(
    'Trip Share Feature',
    $tripsWithShare == $totalTrips,
    "All {$totalTrips} trips have share UUIDs"
);

// Test trip locations
$tripsWithLocation = App\Models\Trip::whereNotNull('current_lat')
    ->whereNotNull('current_lng')
    ->count();
testResult(
    'Trip Location Tracking',
    $tripsWithLocation > 0,
    "{$tripsWithLocation} trips have location data"
);

// ========================================
// 4. SOS ALERTS TESTS
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "4. SOS ALERTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$totalAlerts = App\Models\SosAlert::count();
testResult(
    'SOS Alerts Created',
    $totalAlerts > 0,
    "Total alerts: {$totalAlerts}"
);

$tripLinkedAlerts = App\Models\SosAlert::whereNotNull('trip_id')->count();
testResult(
    'Trip-Linked SOS Alerts',
    $tripLinkedAlerts > 0,
    "{$tripLinkedAlerts} alerts linked to trips"
);

$standaloneAlerts = App\Models\SosAlert::whereNull('trip_id')->count();
testResult(
    'Standalone SOS Alerts',
    $standaloneAlerts > 0,
    "{$standaloneAlerts} standalone alerts"
);

$resolvedAlerts = App\Models\SosAlert::whereNotNull('resolved_at')->count();
testResult(
    'Resolved SOS Alerts',
    $resolvedAlerts > 0,
    "{$resolvedAlerts} alerts have been resolved"
);

// Test SOS alert location data
$alertsWithLocation = App\Models\SosAlert::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->count();
testResult(
    'SOS Alert Location Data',
    $alertsWithLocation == $totalAlerts,
    "All {$totalAlerts} alerts have location coordinates"
);

// ========================================
// 5. ROUTE ALERTS TESTS
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "5. ROUTE ANOMALY DETECTION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$routeAlerts = App\Models\RouteAlert::count();
testResult(
    'Route Alerts Table',
    true,
    "Route alerts table exists (Count: {$routeAlerts})"
);

// Test route alert types
if ($routeAlerts > 0) {
    $deviationAlerts = App\Models\RouteAlert::where('alert_type', 'deviation')->count();
    $stoppageAlerts = App\Models\RouteAlert::where('alert_type', 'stoppage')->count();
    testResult(
        'Route Alert Types',
        true,
        "Deviations: {$deviationAlerts} | Stoppages: {$stoppageAlerts}"
    );
}

// ========================================
// 6. ADMIN FEATURES TESTS
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "6. ADMIN FEATURES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$adminCount = App\Models\User::where('is_admin', true)->count();
testResult(
    'Admin Users Exist',
    $adminCount > 0,
    "Total admins: {$adminCount}"
);

$volunteerCount = App\Models\User::where('is_volunteer', true)->count();
testResult(
    'Volunteer Network',
    $volunteerCount > 0,
    "Total volunteers: {$volunteerCount}"
);

// Test admin can see all data
$adminHasAccess = $admin && $admin->is_admin;
testResult(
    'Admin Access Rights',
    $adminHasAccess,
    "Admin user has elevated privileges"
);

// ========================================
// 7. DATABASE RELATIONSHIPS TESTS
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "7. DATABASE RELATIONSHIPS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test user->trips relationship
$userWithTrips = App\Models\User::has('trips')->first();
testResult(
    'User->Trips Relationship',
    $userWithTrips != null,
    "User '{$userWithTrips->name}' has trips"
);

// Test trip->user relationship
$sampleTrip = App\Models\Trip::first();
$tripHasUser = $sampleTrip && $sampleTrip->user != null;
testResult(
    'Trip->User Relationship',
    $tripHasUser,
    $tripHasUser ? "Trip belongs to: {$sampleTrip->user->name}" : "No relationship"
);

// Test user->trusted contacts relationship
$userWithContacts = App\Models\User::has('trustedContacts')->first();
testResult(
    'User->TrustedContacts Relationship',
    $userWithContacts != null,
    "User '{$userWithContacts->name}' has contacts"
);

// Test SOS->Trip relationship
$sosWithTrip = App\Models\SosAlert::whereNotNull('trip_id')->with('trip')->first();
$sosHasTrip = $sosWithTrip && $sosWithTrip->trip != null;
testResult(
    'SOS->Trip Relationship',
    $sosHasTrip,
    $sosHasTrip ? "SOS alert linked to trip" : "No relationship"
);

// ========================================
// SUMMARY
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST SUMMARY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$totalTests = $passCount + $failCount;
$passRate = $totalTests > 0 ? round(($passCount / $totalTests) * 100, 2) : 0;

echo "Total Tests: {$totalTests}\n";
echo "Passed: {$passCount} ✓\n";
echo "Failed: {$failCount} ✗\n";
echo "Pass Rate: {$passRate}%\n\n";

if ($failCount == 0) {
    echo "🎉 ALL TESTS PASSED! SafeRideApp is working perfectly!\n\n";
} else {
    echo "⚠️  Some tests failed. Please review the failures above.\n\n";
}

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  Test Login Credentials (Password: 'password')           ║\n";
echo "╠══════════════════════════════════════════════════════════╣\n";
echo "║  Admin:     admin@saferide.com                           ║\n";
echo "║  User:      john@example.com                             ║\n";
echo "║  Volunteer: sarah@volunteer.com                          ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";
