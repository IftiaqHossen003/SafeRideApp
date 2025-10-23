# SafeRide Database Factories and Seeders

This document explains the database factories and seeders created for the SafeRide application to generate realistic test data.

## Overview

The SafeRide application now includes comprehensive factories and seeders that create:
- 10+ users (including volunteers and admin)
- 20 trips with various statuses
- 8 SOS alerts (some linked to trips)
- Trusted contacts for all users

## Files Created

### 1. Enhanced TripFactory (`database/factories/TripFactory.php`)
**Enhanced existing factory with realistic data generation:**

- **Realistic Coordinates**: Uses San Francisco Bay Area coordinates as an example
- **Smart Trip Progression**: Current location calculated based on trip progress
- **Multiple States**: 
  - `ongoing()` - Trips currently in progress
  - `completed()` - Successfully finished trips
  - `cancelled()` - Interrupted journeys
- **Relationship Support**: `forUser(User $user)` method

**Key Features:**
```php
// Create different types of trips
Trip::factory()->ongoing()->create();
Trip::factory()->completed()->create();
Trip::factory()->cancelled()->create();
Trip::factory()->forUser($user)->create();
```

### 2. New SosAlertFactory (`database/factories/SosAlertFactory.php`)
**Comprehensive factory for emergency alerts:**

- **Realistic Emergency Messages**: Pre-defined emergency scenarios
- **Location Coordination**: Uses realistic city coordinates
- **Trip Relationships**: Can be linked to specific trips
- **Resolution States**: Supports resolved/unresolved alerts
- **Volunteer Integration**: Resolved alerts linked to volunteer responders

**Key Features:**
```php
// Create different types of SOS alerts
SosAlert::factory()->unresolved()->create();
SosAlert::factory()->resolved()->create();
SosAlert::factory()->forTrip($trip)->create();
SosAlert::factory()->withoutTrip()->create();
SosAlert::factory()->withMessage('Custom emergency message')->create();
```

### 3. SafeRideSeeder (`database/seeders/SafeRideSeeder.php`)
**Comprehensive seeder for complete application data:**

**Creates:**
- **1 Admin User**: Full admin access for testing admin features
- **1 Regular Test User**: Standard user for testing user features  
- **3 Volunteer Users**: Users who can respond to SOS alerts
- **6 Additional Users**: Mixed regular users and volunteers
- **6 Ongoing Trips**: Currently active trips
- **10 Completed Trips**: Successfully finished journeys
- **4 Cancelled Trips**: Interrupted or cancelled trips
- **5 Trip-linked SOS Alerts**: Emergency alerts during trips
- **3 Standalone SOS Alerts**: Emergency alerts not during trips
- **Trusted Contacts**: 1-3 contacts per user

### 4. Updated DatabaseSeeder (`database/seeders/DatabaseSeeder.php`)
**Modified to call SafeRideSeeder automatically**

## Usage Instructions

### Running the Seeders

1. **Fresh Database with All Data:**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Run Seeders Only (on existing database):**
   ```bash
   php artisan db:seed
   ```

3. **Run Specific Seeder:**
   ```bash
   php artisan db:seed --class=SafeRideSeeder
   ```

### Test Login Credentials

After seeding, you can login with these accounts:

| Role | Email | Password | Features |
|------|--------|----------|----------|
| **Admin** | admin@saferide.com | password | Full admin access, reports, user management |
| **Regular User** | john@example.com | password | Create trips, SOS alerts, manage contacts |
| **Volunteer** | sarah@volunteer.com | password | Respond to SOS alerts, volunteer dashboard |
| **Volunteer** | mike@volunteer.com | password | Respond to SOS alerts, volunteer dashboard |
| **Volunteer** | emma@volunteer.com | password | Respond to SOS alerts, volunteer dashboard |

### Factory Usage in Tests

```php
// Create realistic test data in your tests
class TripTest extends TestCase
{
    public function test_ongoing_trip_has_current_location()
    {
        $trip = Trip::factory()->ongoing()->create();
        
        $this->assertNotNull($trip->current_lat);
        $this->assertNotNull($trip->current_lng);
        $this->assertEquals('ongoing', $trip->status);
    }
    
    public function test_sos_alert_for_specific_trip()
    {
        $trip = Trip::factory()->ongoing()->create();
        $alert = SosAlert::factory()->forTrip($trip)->create();
        
        $this->assertEquals($trip->id, $alert->trip_id);
        $this->assertEquals($trip->user_id, $alert->user_id);
    }
}
```

## Data Relationships

The seeded data includes proper relationships:

- **Users → Trips**: Each trip belongs to a user
- **Users → SOS Alerts**: Each alert is created by a user
- **Trips → SOS Alerts**: 5 alerts are linked to specific trips
- **Users → Trusted Contacts**: Each user has 1-3 emergency contacts
- **Volunteers → Resolved Alerts**: Volunteers can be assigned as responders

## Data Characteristics

### Geographic Data
- **Coordinates**: San Francisco Bay Area (37.7-37.8°N, 122.3-122.5°W)
- **Trip Distances**: Realistic city-scale distances (5-50km)
- **Current Locations**: Calculated based on trip progress

### Temporal Data
- **Trip Durations**: Realistic timing (1 hour to 1 week ago)
- **SOS Alert Timing**: Recent alerts (within last week)
- **Resolution Times**: Logical resolution times after alert creation

### User Diversity
- **Pseudonyms**: Unique, realistic pseudonyms for privacy
- **Roles**: Mix of regular users, volunteers, and admin
- **Emergency Contacts**: Multiple contacts per user for safety

## Customization

### Adding New Factory States

```php
// Add to TripFactory
public function longDistance(): static
{
    return $this->state(function (array $attributes) {
        // Create trips with longer distances
        $originLat = fake()->latitude(37.7000, 37.8000);
        $originLng = fake()->longitude(-122.5000, -122.3000);
        
        // Destination much farther away
        $destinationLat = $originLat + fake()->randomFloat(4, -0.2, 0.2);
        $destinationLng = $originLng + fake()->randomFloat(4, -0.2, 0.2);
        
        return [
            'origin_lat' => $originLat,
            'origin_lng' => $originLng,
            'destination_lat' => $destinationLat,
            'destination_lng' => $destinationLng,
        ];
    });
}
```

### Customizing Seeder Data

Modify `SafeRideSeeder.php` to adjust:
- Number of users created
- Ratio of volunteers to regular users
- Trip status distribution
- SOS alert scenarios
- Geographic regions

## Testing with Seeded Data

The seeded data provides excellent test scenarios:

1. **Admin Reports**: View comprehensive trip history
2. **SOS Alert System**: Test with pre-existing alerts
3. **Volunteer Dashboard**: See nearby alerts and responses
4. **Trip Tracking**: Monitor ongoing trips
5. **User Authentication**: Test with different user roles

## Production Considerations

**⚠️ Important**: This seeder is designed for development and testing only.

For production:
1. Remove or modify the SafeRideSeeder
2. Create production-specific seeders for initial data
3. Use more secure default passwords
4. Adjust geographic coordinates for your service area

## Summary

The SafeRide factories and seeders provide:
- **Realistic Test Data**: Coordinates, timing, and relationships
- **Comprehensive Coverage**: All major application features
- **Easy Testing**: Pre-configured users and scenarios
- **Flexible Usage**: Modular factories for custom test cases
- **Documentation**: Clear instructions and examples

This setup enables thorough testing of all SafeRide features with realistic, interconnected data that mirrors real-world usage patterns.