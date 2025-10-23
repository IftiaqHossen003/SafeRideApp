import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Import trip location listener
 * Usage: import { initTripLocationTracking } from './trip-location-listener';
 */
import { initTripLocationTracking, stopTripLocationTracking } from './trip-location-listener';

// Make available globally for easy access in Blade templates
window.initTripLocationTracking = initTripLocationTracking;
window.stopTripLocationTracking = stopTripLocationTracking;

