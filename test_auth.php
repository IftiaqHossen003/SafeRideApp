<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing SafeRideApp Authentication ===\n\n";

// Test seeded users
$testUsers = [
    ['email' => 'admin@saferide.com', 'name' => 'Admin User'],
    ['email' => 'john@example.com', 'name' => 'John Doe'],
    ['email' => 'sarah@volunteer.com', 'name' => 'Sarah Wilson'],
];

foreach ($testUsers as $testUser) {
    $user = App\Models\User::where('email', $testUser['email'])->first();
    
    if ($user) {
        $passwordWorks = Illuminate\Support\Facades\Hash::check('password', $user->password);
        $status = $passwordWorks ? '✓ PASS' : '✗ FAIL';
        echo "{$status} - {$user->name} ({$user->email})\n";
        echo "       Password: " . ($passwordWorks ? "Correct" : "Incorrect") . "\n";
        echo "       Is Admin: " . ($user->is_admin ? 'Yes' : 'No') . "\n";
        echo "       Is Volunteer: " . ($user->is_volunteer ? 'Yes' : 'No') . "\n";
    } else {
        echo "✗ FAIL - User not found: {$testUser['email']}\n";
    }
    echo "\n";
}

// Test trusted contacts
echo "=== Testing Trusted Contacts ===\n\n";
$contactCount = App\Models\TrustedContact::count();
echo "Total trusted contacts in database: {$contactCount}\n\n";

// Show contacts for admin user
$admin = App\Models\User::where('email', 'admin@saferide.com')->first();
if ($admin) {
    $adminContacts = $admin->trustedContacts()->count();
    echo "Admin user has {$adminContacts} trusted contacts\n";
}

echo "\n=== Test Complete ===\n";
