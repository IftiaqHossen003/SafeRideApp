<?php

namespace Database\Seeders;

use App\Models\SosAlert;
use App\Models\Trip;
use App\Models\TrustedContact;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * SafeRideSeeder
 * 
 * Comprehensive seeder for populating the SafeRide application with realistic test data.
 * Creates users, trips, and SOS alerts with proper relationships and realistic scenarios.
 */
class SafeRideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('🚗 Starting SafeRide application seeding...');

        // Create users with different roles and characteristics
        $this->createUsers();
        
        // Create trips with various statuses
        $this->createTrips();
        
        // Create SOS alerts linked to some trips
        $this->createSosAlerts();
        
        // Create some trusted contacts relationships
        $this->createTrustedContacts();
        
        $this->command->info('✅ SafeRide seeding completed successfully!');
        $this->displaySeededDataSummary();
    }

    /**
     * Create users with various roles and realistic data.
     *
     * @return void
     */
    private function createUsers(): void
    {
        $this->command->info('👥 Creating users...');
        
        // Create an admin user for testing admin features
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@saferide.com',
            'password' => Hash::make('password'),
            'pseudonym' => 'SafeRideAdmin',
            'is_volunteer' => true,
            'is_admin' => true,
        ]);

        // Create a regular test user
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'pseudonym' => 'JohnnyRider',
            'is_volunteer' => false,
            'is_admin' => false,
        ]);

        // Create volunteer users (who can respond to SOS alerts)
        $volunteers = [
            ['name' => 'Sarah Wilson', 'email' => 'sarah@volunteer.com', 'pseudonym' => 'SafetyFirst'],
            ['name' => 'Mike Johnson', 'email' => 'mike@volunteer.com', 'pseudonym' => 'RoadGuardian'],
            ['name' => 'Emma Davis', 'email' => 'emma@volunteer.com', 'pseudonym' => 'CityHelper'],
        ];

        foreach ($volunteers as $volunteer) {
            User::factory()->create([
                'name' => $volunteer['name'],
                'email' => $volunteer['email'],
                'password' => Hash::make('password'),
                'pseudonym' => $volunteer['pseudonym'],
                'is_volunteer' => true,
                'is_admin' => false,
            ]);
        }

        // Create regular users with unique pseudonyms
        $regularUsers = [
            'TechieRider',
            'CoffeeLover',
            'NightOwl',
            'BookwormTraveler',
            'MusicFan',
            'AdventureSeeker',
        ];

        foreach ($regularUsers as $pseudonym) {
            User::factory()->create([
                'pseudonym' => $pseudonym,
                'is_volunteer' => fake()->boolean(20), // 20% chance of being volunteer
                'is_admin' => false,
            ]);
        }

        $this->command->info("   ✓ Created " . User::count() . " users (including volunteers and admin)");
    }

    /**
     * Create trips with various statuses and realistic scenarios.
     *
     * @return void
     */
    private function createTrips(): void
    {
        $this->command->info('🛣️  Creating trips...');
        
        $users = User::all();
        
        // Create ongoing trips (active users currently traveling)
        $ongoingTrips = Trip::factory()
            ->count(6)
            ->ongoing()
            ->create([
                'user_id' => fake()->randomElement($users)->id,
            ]);

        // Create completed trips (successful journeys)
        $completedTrips = Trip::factory()
            ->count(10)
            ->completed()
            ->create([
                'user_id' => fake()->randomElement($users)->id,
            ]);

        // Create cancelled trips (interrupted journeys)
        $cancelledTrips = Trip::factory()
            ->count(4)
            ->cancelled()
            ->create([
                'user_id' => fake()->randomElement($users)->id,
            ]);

        $totalTrips = $ongoingTrips->count() + $completedTrips->count() + $cancelledTrips->count();
        $this->command->info("   ✓ Created {$totalTrips} trips (6 ongoing, 10 completed, 4 cancelled)");
    }

    /**
     * Create SOS alerts with realistic scenarios.
     *
     * @return void
     */
    private function createSosAlerts(): void
    {
        $this->command->info('🚨 Creating SOS alerts...');
        
        $trips = Trip::with('user')->get();
        $users = User::all();
        $volunteers = User::where('is_volunteer', true)->get();
        
        // Create SOS alerts linked to random trips
        $tripAlerts = collect();
        for ($i = 0; $i < 5; $i++) {
            $randomTrip = fake()->randomElement($trips);
            
            $alert = SosAlert::factory()
                ->forTrip($randomTrip)
                ->create();
                
            // 60% chance of being resolved by a volunteer
            if (fake()->boolean(60) && $volunteers->count() > 0) {
                $alert->update([
                    'resolved_at' => fake()->dateTimeBetween($alert->created_at, 'now'),
                    'responder_id' => fake()->randomElement($volunteers)->id,
                ]);
            }
            
            $tripAlerts->push($alert);
        }

        // Create standalone SOS alerts (not during trips)
        $standaloneAlerts = collect();
        for ($i = 0; $i < 3; $i++) {
            $alert = SosAlert::factory()
                ->withoutTrip()
                ->forUser(fake()->randomElement($users))
                ->create();
                
            // 40% chance of being resolved
            if (fake()->boolean(40) && $volunteers->count() > 0) {
                $alert->update([
                    'resolved_at' => fake()->dateTimeBetween($alert->created_at, 'now'),
                    'responder_id' => fake()->randomElement($volunteers)->id,
                ]);
            }
            
            $standaloneAlerts->push($alert);
        }

        $totalAlerts = $tripAlerts->count() + $standaloneAlerts->count();
        $resolvedCount = SosAlert::whereNotNull('resolved_at')->count();
        $this->command->info("   ✓ Created {$totalAlerts} SOS alerts (5 trip-related, 3 standalone, {$resolvedCount} resolved)");
    }

    /**
     * Create trusted contacts relationships between users.
     *
     * @return void
     */
    private function createTrustedContacts(): void
    {
        $this->command->info('👥 Creating trusted contacts...');
        
        $users = User::all();
        $contactsCreated = 0;
        
        // Each user gets 1-3 trusted contacts
        foreach ($users as $user) {
            $contactCount = fake()->numberBetween(1, 3);
            
            for ($i = 0; $i < $contactCount; $i++) {
                // Avoid creating duplicate contacts for the same user
                $existingContacts = $user->trustedContacts()->pluck('contact_phone')->toArray();
                
                do {
                    $phone = fake()->phoneNumber();
                } while (in_array($phone, $existingContacts));
                
                TrustedContact::factory()->create([
                    'user_id' => $user->id,
                    'contact_name' => fake()->name(),
                    'contact_phone' => $phone,
                ]);
                
                $contactsCreated++;
            }
        }
        
        $this->command->info("   ✓ Created {$contactsCreated} trusted contact relationships");
    }

    /**
     * Display a summary of the seeded data.
     *
     * @return void
     */
    private function displaySeededDataSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 Seeding Summary:');
        $this->command->info('==================');
        
        // Users summary
        $totalUsers = User::count();
        $volunteers = User::where('is_volunteer', true)->count();
        $admins = User::where('is_admin', true)->count();
        $this->command->info("👥 Users: {$totalUsers} total ({$volunteers} volunteers, {$admins} admins)");
        
        // Trips summary
        $totalTrips = Trip::count();
        $ongoingTrips = Trip::where('status', 'ongoing')->count();
        $completedTrips = Trip::where('status', 'completed')->count();
        $cancelledTrips = Trip::where('status', 'cancelled')->count();
        $this->command->info("🛣️  Trips: {$totalTrips} total ({$ongoingTrips} ongoing, {$completedTrips} completed, {$cancelledTrips} cancelled)");
        
        // SOS alerts summary
        $totalAlerts = SosAlert::count();
        $resolvedAlerts = SosAlert::whereNotNull('resolved_at')->count();
        $unresolvedAlerts = $totalAlerts - $resolvedAlerts;
        $tripLinkedAlerts = SosAlert::whereNotNull('trip_id')->count();
        $this->command->info("🚨 SOS Alerts: {$totalAlerts} total ({$resolvedAlerts} resolved, {$unresolvedAlerts} unresolved, {$tripLinkedAlerts} trip-linked)");
        
        // Trusted contacts summary
        $totalContacts = TrustedContact::count();
        $this->command->info("📞 Trusted Contacts: {$totalContacts} total");
        
        $this->command->info('');
        $this->command->info('🔑 Test Login Credentials:');
        $this->command->info('  Admin: admin@saferide.com / password');
        $this->command->info('  User:  john@example.com / password');
        $this->command->info('  Volunteers: sarah@volunteer.com, mike@volunteer.com, emma@volunteer.com / password');
        $this->command->info('');
        $this->command->info('🎉 Ready to test SafeRide features!');
    }
}