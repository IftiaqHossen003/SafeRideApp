# SafeRide Event Broadcasting Setup

## Overview
This document describes the real-time location broadcasting implementation for SafeRide trips using Laravel's event broadcasting system.

## Architecture

### Backend Components

#### 1. Event: `TripLocationUpdated`
**File**: `app/Events/TripLocationUpdated.php`

Implements `ShouldBroadcast` to enable real-time broadcasting.

```php
- Channel: private-trip.{tripId}
- Event Name: location.updated
- Broadcast Data:
  - trip_id: int
  - current_lat: decimal(10,7)
  - current_lng: decimal(10,7)
  - timestamp: ISO 8601 datetime
```

#### 2. Channel Authorization
**File**: `routes/channels.php`

Authorizes users to listen to trip location updates:
- ✅ Trip owner (user who started the trip)
- ✅ Trusted contacts of the trip owner

```php
Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
    $trip = Trip::find($tripId);
    
    // Allow trip owner OR trusted contacts
    return $user->id === $trip->user_id 
        || $trip->user->trustedContacts()
            ->where('contact_user_id', $user->id)
            ->exists();
});
```

#### 3. Broadcasting Trigger
**File**: `app/Http/Controllers/TripController.php`

The `updateLocation()` method dispatches the event after updating coordinates:

```php
broadcast(new TripLocationUpdated($trip))->toOthers();
```

### Frontend Components

#### 1. Trip Location Listener
**File**: `resources/js/trip-location-listener.js`

Provides functions to subscribe/unsubscribe to trip location updates:

```javascript
// Start listening to location updates
initTripLocationTracking(tripId);

// Stop listening
stopTripLocationTracking(tripId);
```

**Usage in Blade Templates**:
```javascript
<script>
// Start tracking when page loads
window.addEventListener('DOMContentLoaded', () => {
    initTripLocationTracking({{ $trip->id }});
});

// Listen for location updates
window.addEventListener('trip-location-updated', (event) => {
    const { trip_id, current_lat, current_lng, timestamp } = event.detail;
    
    // Update map marker position
    updateMapMarker(current_lat, current_lng);
    
    // Update UI elements
    console.log(`Trip ${trip_id} updated: ${current_lat}, ${current_lng}`);
});
</script>
```

#### 2. Global Availability
**File**: `resources/js/app.js`

The tracking functions are exposed globally:
```javascript
window.initTripLocationTracking = initTripLocationTracking;
window.stopTripLocationTracking = stopTripLocationTracking;
```

## Installation Steps

### 1. Install Required NPM Packages
```bash
npm install --save-dev laravel-echo pusher-js
```

### 2. Configure Laravel Echo
Uncomment the Echo configuration in `resources/js/bootstrap.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
```

### 3. Environment Variables
Add to `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 4. Build Frontend Assets
```bash
npm run dev
# or for production
npm run build
```

### 5. Start Queue Worker (if using queued broadcasts)
```bash
php artisan queue:work
```

## Testing

### Running Broadcasting Tests
```bash
php artisan test --filter TripLocationBroadcastTest
```

**Test Coverage**:
- ✅ Event is dispatched on location update
- ✅ Event is NOT dispatched on validation failure
- ✅ Event is NOT dispatched on unauthorized access
- ✅ Event broadcast data contains correct fields
- ✅ Event broadcasts on correct private channel
- ✅ Event broadcasts with custom event name

### Manual Testing with Pusher Debug Console

1. Open Pusher Debug Console in your dashboard
2. Start a trip as User A
3. Update location via API
4. Observe event on channel `private-trip.{tripId}`
5. Verify data payload contains trip_id, current_lat, current_lng, timestamp

## API Endpoints

### Update Trip Location
```http
PATCH /api/trips/{trip}/location
Authorization: Bearer {token} OR Session Cookie
Content-Type: application/json

{
    "lat": 12.345678,
    "lng": 98.765432
}
```

**Response**:
```json
{
    "success": true,
    "message": "Location updated successfully",
    "trip": {
        "id": 1,
        "current_lat": 12.345678,
        "current_lng": 98.765432,
        "status": "ongoing"
    }
}
```

## Security

### Channel Authorization
- Uses Laravel's built-in broadcasting authorization
- Only trip owner and their trusted contacts can subscribe
- Authorization checked before allowing WebSocket connection

### Data Privacy
- Broadcasts use private channels (require authentication)
- Only current coordinates are broadcast (origin/destination not exposed)
- Share UUID is not broadcast (prevents unauthorized public access)

## Performance Considerations

### Reducing Broadcast Frequency
For mobile clients sending frequent updates:

```javascript
// Throttle location updates to every 5 seconds
let lastUpdate = 0;
const UPDATE_INTERVAL = 5000; // 5 seconds

function updateLocation(lat, lng) {
    const now = Date.now();
    if (now - lastUpdate < UPDATE_INTERVAL) {
        return; // Skip this update
    }
    
    axios.patch(`/api/trips/${tripId}/location`, { lat, lng });
    lastUpdate = now;
}
```

### Queue Broadcasts
For high-traffic scenarios, implement `ShouldBroadcastNow` → `ShouldBroadcast` and use queues:

```php
// config/queue.php - ensure 'default' => 'redis' or 'database'
php artisan queue:work --queue=high,default
```

## Troubleshooting

### Issue: Events not broadcasting
**Solutions**:
- Verify `BROADCAST_DRIVER=pusher` in `.env`
- Check Pusher credentials are correct
- Ensure queue worker is running if using queued broadcasts
- Verify `ShouldBroadcast` interface is implemented

### Issue: Authorization failures
**Solutions**:
- Check `routes/channels.php` authorization logic
- Verify user is authenticated
- Confirm user is trip owner OR in trusted contacts

### Issue: Frontend not receiving events
**Solutions**:
- Verify Laravel Echo is initialized in `bootstrap.js`
- Check browser console for WebSocket errors
- Confirm `npm run dev` is running (for local development)
- Verify channel name matches: `private-trip.{tripId}`

## Future Enhancements

### 1. Add Geofencing Alerts
Broadcast event when trip leaves designated safe zone:
```php
class TripGeofenceExited implements ShouldBroadcast { ... }
```

### 2. Trip Status Changes
Broadcast when trip starts/ends:
```php
class TripStatusChanged implements ShouldBroadcast { ... }
```

### 3. SOS Emergency Broadcasts
High-priority channel for emergency alerts:
```php
class EmergencySOS implements ShouldBroadcast {
    public function broadcastOn() {
        return new PrivateChannel('emergency.trip.' . $this->trip->id);
    }
}
```

## Resources
- [Laravel Broadcasting Documentation](https://laravel.com/docs/11.x/broadcasting)
- [Laravel Echo Documentation](https://laravel.com/docs/11.x/broadcasting#client-side-installation)
- [Pusher Documentation](https://pusher.com/docs)
