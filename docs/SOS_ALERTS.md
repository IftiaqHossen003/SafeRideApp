# SafeRide SOS Alert System

## Overview
The SOS Alert system allows users to quickly trigger emergency alerts with their current location. These alerts are stored in the database and broadcast in real-time to volunteers who can respond to emergencies.

## Architecture

### Database Schema

#### Table: `sos_alerts`
```sql
- id: Primary key
- user_id: Foreign key to users (nullable for anonymous alerts)
- trip_id: Foreign key to trips (nullable if not during a trip)
- latitude: DECIMAL(10,7) - Alert location latitude
- longitude: DECIMAL(10,7) - Alert location longitude  
- message: TEXT (nullable) - Optional message from user
- created_at: TIMESTAMP - When alert was triggered
- resolved_at: TIMESTAMP (nullable) - When alert was resolved
- responder_id: Foreign key to users (nullable) - Volunteer who resolved alert
```

**Indexes:**
- `user_id` - Quick lookup of user's alerts
- `trip_id` - Find alerts associated with trips
- `created_at` - Sort alerts chronologically
- `resolved_at, created_at` - Efficiently find unresolved alerts

**Foreign Keys:**
- `user_id` → `users.id` (CASCADE on delete)
- `trip_id` → `trips.id` (CASCADE on delete)
- `responder_id` → `users.id` (SET NULL on delete)

### Backend Components

#### 1. Model: `SosAlert`
**File**: `app/Models/SosAlert.php`

**Relationships:**
- `user()` - BelongsTo: User who triggered the alert
- `responder()` - BelongsTo: Volunteer who resolved the alert
- `trip()` - BelongsTo: Associated trip (if any)

**Scopes:**
- `unresolved()` - Query only unresolved alerts
- `resolved()` - Query only resolved alerts

**Helper Methods:**
- `isResolved(): bool` - Check if alert has been resolved

**Example Usage:**
```php
// Find all unresolved alerts
$alerts = SosAlert::unresolved()->get();

// Get alert with relationships
$alert = SosAlert::with(['user', 'trip'])->find($id);

// Check if resolved
if ($alert->isResolved()) {
    // Handle resolved alert
}
```

#### 2. Controller: `SosController`
**File**: `app/Http/Controllers/SosController.php`

**Methods:**

##### `store(Request $request): JsonResponse`
Creates a new SOS alert and broadcasts it to volunteers.

**Validation Rules:**
- `lat`: required|numeric|between:-90,90
- `lng`: required|numeric|between:-180,180
- `trip_id`: nullable|integer|exists:trips,id
- `message`: nullable|string|max:1000

**Response** (201 Created):
```json
{
    "success": true,
    "message": "SOS alert created successfully",
    "alert": {
        "id": 1,
        "user_id": 5,
        "trip_id": 10,
        "latitude": 12.345678,
        "longitude": 98.765432,
        "message": "Help! I need assistance.",
        "created_at": "2025-10-08T14:30:00Z"
    },
    "broadcast_channel": "sos.1"
}
```

**Error Response** (422 Unprocessable Entity):
```json
{
    "message": "The lat field is required. (and 1 more error)",
    "errors": {
        "lat": ["The lat field is required."],
        "lng": ["The lng field is required."]
    }
}
```

#### 3. Event: `SosCreated`
**File**: `app/Events/SosCreated.php`

Implements `ShouldBroadcast` to enable real-time broadcasting.

**Broadcasting:**
- **Channel**: `sos.{alert_id}` (Public Channel)
- **Event Name**: `alert.created`
- **Broadcast Data**:
  ```json
  {
      "alert_id": 1,
      "user_id": 5,
      "trip_id": 10,
      "latitude": 12.345678,
      "longitude": 98.765432,
      "message": "Help! I need assistance.",
      "created_at": "2025-10-08T14:30:00Z"
  }
  ```

**Why Public Channel?**
- Allows any volunteer (not just trusted contacts) to subscribe
- No authentication required to listen for SOS alerts
- Volunteers can respond to any emergency in their area

### API Endpoints

#### Create SOS Alert
```http
POST /api/sos
Authorization: Bearer {token} OR Session Cookie
Content-Type: application/json

{
    "lat": 12.345678,
    "lng": 98.765432,
    "trip_id": 10,  // optional
    "message": "Help! I need assistance."  // optional
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/sos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "lat": 12.345678,
    "lng": 98.765432,
    "message": "Emergency situation"
  }'
```

## Frontend Integration

### JavaScript - Listening for SOS Alerts

Volunteers can subscribe to all SOS alerts or specific alerts:

```javascript
// Listen for new SOS alerts
window.Echo.channel('sos.*')
    .listen('.alert.created', (data) => {
        console.log('New SOS Alert:', data);
        
        // Extract alert data
        const {
            alert_id,
            user_id,
            trip_id,
            latitude,
            longitude,
            message,
            created_at
        } = data;
        
        // Show notification to volunteer
        showSOSNotification(data);
        
        // Update map with alert location
        addAlertMarker(latitude, longitude);
        
        // Play alert sound
        playAlertSound();
    });

// Listen for a specific SOS alert
window.Echo.channel(`sos.${alertId}`)
    .listen('.alert.created', (data) => {
        console.log('SOS Alert Updated:', data);
    });
```

### Example: SOS Button Component

```html
<div x-data="sosAlert()">
    <button 
        @click="triggerSOS()" 
        :disabled="sending"
        class="bg-red-600 text-white px-6 py-3 rounded-lg"
    >
        <span x-show="!sending">🆘 Send SOS</span>
        <span x-show="sending">Sending...</span>
    </button>
    
    <div x-show="success" class="mt-4 text-green-600">
        SOS Alert sent successfully!
    </div>
</div>

<script>
function sosAlert() {
    return {
        sending: false,
        success: false,
        
        async triggerSOS() {
            this.sending = true;
            this.success = false;
            
            // Get user's current location
            navigator.geolocation.getCurrentPosition(async (position) => {
                try {
                    const response = await axios.post('/api/sos', {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        message: 'Emergency - immediate assistance needed'
                    });
                    
                    this.success = true;
                    console.log('SOS Alert:', response.data);
                    
                    // Optionally navigate to alert tracking page
                    // window.location.href = `/sos/${response.data.alert.id}`;
                } catch (error) {
                    console.error('Failed to send SOS:', error);
                    alert('Failed to send SOS alert. Please try again.');
                } finally {
                    this.sending = false;
                }
            }, (error) => {
                console.error('Location error:', error);
                alert('Unable to get your location. Please enable location services.');
                this.sending = false;
            });
        }
    };
}
</script>
```

## Testing

### Running Tests
```bash
# Run all SOS Alert tests
php artisan test --filter SosAlertTest

# Run specific test
php artisan test --filter test_authenticated_user_can_create_sos_alert
```

### Test Coverage
✅ **12 tests, 44 assertions**

1. ✓ Authenticated user can create SOS alert
2. ✓ SOS alert can be created with trip_id
3. ✓ SOS alert can be created without message
4. ✓ SOS alert requires valid coordinates
5. ✓ Latitude must be within valid range (-90 to 90)
6. ✓ Longitude must be within valid range (-180 to 180)
7. ✓ Unauthenticated user cannot create SOS alert
8. ✓ SosCreated event is dispatched
9. ✓ Broadcast channel name is returned in response
10. ✓ Event broadcast contains correct data
11. ✓ Event broadcasts on public channel
12. ✓ Event broadcasts with custom event name

### Manual Testing with Pusher Debug Console

1. Configure Pusher credentials in `.env`
2. Open Pusher Debug Console
3. Subscribe to channel: `sos.*` or `sos.{id}`
4. Create SOS alert via API
5. Observe event `alert.created` in Debug Console
6. Verify payload contains all required fields

## Security Considerations

### Authentication
- ✅ Only authenticated users can create SOS alerts
- ✅ User ID is automatically set from authenticated user
- ❌ No authorization check on trip_id (users can reference any trip)

### Data Privacy
- ⚠️ **Public Channel**: All broadcast data is publicly accessible
- ℹ️ Consider anonymizing user data in broadcast if privacy is concern
- ℹ️ User's name/email are NOT broadcast (only user_id)

### Rate Limiting
Consider adding rate limiting to prevent SOS spam:

```php
// In routes/api.php
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::post('/sos', [SosController::class, 'store']);
});
```

## Future Enhancements

### 1. Volunteer Matching
Automatically notify nearby volunteers based on location:
```php
// Find volunteers within 10km radius
$nearbyVolunteers = User::where('is_volunteer', true)
    ->withinDistance($alert->latitude, $alert->longitude, 10)
    ->get();

foreach ($nearbyVolunteers as $volunteer) {
    // Send push notification
    $volunteer->notify(new SOSAlertNotification($alert));
}
```

### 2. Alert Resolution
Add endpoint for volunteers to resolve alerts:
```php
public function resolve(SosAlert $alert): JsonResponse
{
    $alert->update([
        'resolved_at' => now(),
        'responder_id' => Auth::id(),
    ]);
    
    broadcast(new SosResolved($alert));
    
    return response()->json(['success' => true]);
}
```

### 3. Alert History
View all SOS alerts for a user:
```php
public function index(Request $request): JsonResponse
{
    $alerts = Auth::user()
        ->sosAlerts()
        ->latest('created_at')
        ->paginate(20);
    
    return response()->json($alerts);
}
```

### 4. Geofencing
Send automatic SOS if user leaves designated safe zone:
```php
if ($trip->isOutsideSafeZone($lat, $lng)) {
    SosAlert::create([
        'user_id' => $trip->user_id,
        'trip_id' => $trip->id,
        'latitude' => $lat,
        'longitude' => $lng,
        'message' => 'User has left the safe zone',
    ]);
}
```

### 5. Twilio/SMS Integration
Send SMS to trusted contacts when SOS is triggered:
```php
foreach ($user->trustedContacts as $contact) {
    Twilio::sendSMS($contact->phone, 
        "SOS Alert: {$user->name} needs help at {$lat}, {$lng}"
    );
}
```

## Troubleshooting

### Issue: SOS Alert not broadcasting
**Solutions:**
- Verify `BROADCAST_DRIVER=pusher` in `.env`
- Check Pusher credentials are correct
- Ensure queue worker is running: `php artisan queue:work`
- Check Laravel logs: `storage/logs/laravel.log`

### Issue: Invalid coordinates error
**Solutions:**
- Ensure latitude is between -90 and 90
- Ensure longitude is between -180 and 180
- Check GPS permissions are enabled
- Verify coordinates are numbers, not strings

### Issue: User ID is null
**Solutions:**
- Verify user is authenticated
- Check `auth` middleware is applied to route
- Confirm session/token is valid

## Database Queries

### Find all unresolved alerts
```php
$unresolved = SosAlert::whereNull('resolved_at')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Find alerts within time range
```php
$recent = SosAlert::whereBetween('created_at', [
    now()->subHours(24),
    now()
])->get();
```

### Find alerts by location radius
```php
// Note: Requires spatial extensions or custom implementation
$nearby = SosAlert::selectRaw("
    *,
    (6371 * acos(cos(radians(?)) * cos(radians(latitude))
    * cos(radians(longitude) - radians(?))
    + sin(radians(?)) * sin(radians(latitude)))) AS distance
", [$lat, $lng, $lat])
    ->having('distance', '<', 10)
    ->orderBy('distance')
    ->get();
```

## Resources
- [Laravel Broadcasting Documentation](https://laravel.com/docs/11.x/broadcasting)
- [Pusher Channels Documentation](https://pusher.com/docs/channels)
- [Geolocation API](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API)
