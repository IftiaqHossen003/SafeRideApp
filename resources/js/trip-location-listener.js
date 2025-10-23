/**
 * Trip Location Listener
 * 
 * Subscribes to trip location updates via Laravel Echo.
 * Listens on private channel 'trip.{tripId}' for real-time location updates.
 */

/**
 * Initialize trip location tracking for a specific trip
 * 
 * @param {number} tripId - The ID of the trip to track
 * @returns {object} Echo channel instance
 */
export function initTripLocationTracking(tripId) {
    if (typeof window.Echo === 'undefined') {
        console.error('Laravel Echo is not initialized. Please ensure Echo is configured.');
        return null;
    }

    console.log(`Subscribing to trip location updates for trip ${tripId}...`);

    // Subscribe to the private trip channel
    const channel = window.Echo.private(`trip.${tripId}`)
        .listen('.location.updated', (event) => {
            console.log('Trip location updated:', {
                tripId: event.trip_id,
                latitude: event.current_lat,
                longitude: event.current_lng,
                timestamp: event.timestamp
            });

            // Dispatch custom event for other parts of the app to listen to
            window.dispatchEvent(new CustomEvent('trip-location-updated', {
                detail: {
                    tripId: event.trip_id,
                    lat: event.current_lat,
                    lng: event.current_lng,
                    timestamp: event.timestamp
                }
            }));
        })
        .error((error) => {
            console.error('Error subscribing to trip channel:', error);
        });

    console.log(`Successfully subscribed to trip.${tripId} channel`);
    
    return channel;
}

/**
 * Stop tracking a trip's location updates
 * 
 * @param {number} tripId - The ID of the trip to stop tracking
 */
export function stopTripLocationTracking(tripId) {
    if (typeof window.Echo === 'undefined') {
        console.error('Laravel Echo is not initialized.');
        return;
    }

    console.log(`Unsubscribing from trip location updates for trip ${tripId}...`);
    window.Echo.leave(`trip.${tripId}`);
}

/**
 * Example usage (for testing):
 * 
 * // In your Blade template or JS file:
 * import { initTripLocationTracking, stopTripLocationTracking } from './trip-location-listener';
 * 
 * // Start tracking
 * const tripId = 1;
 * const channel = initTripLocationTracking(tripId);
 * 
 * // Listen for updates in your app
 * window.addEventListener('trip-location-updated', (event) => {
 *     console.log('Update received:', event.detail);
 *     // Update map marker, UI, etc.
 * });
 * 
 * // Stop tracking when done
 * stopTripLocationTracking(tripId);
 */
