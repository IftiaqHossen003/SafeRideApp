<x-app-layout>
    <!-- Mapbox GL JS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js'></script>
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🚗 {{ __('Active Trip') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Live tracking and monitoring</p>
            </div>
            <button onclick="endTrip()" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-semibold transition-all">
                End Trip
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Trip Status Card -->
            <div class="bg-gradient-to-br from-green-500 to-blue-600 rounded-xl shadow-2xl p-8 text-white">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Trip In Progress</h3>
                        <p class="text-green-100">Started at {{ $trip->started_at->format('h:i A') }}</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm px-6 py-3 rounded-full">
                        <span class="text-2xl font-bold" id="tripDuration">00:00</span>
                    </div>
                </div>
                
                <!-- Share Link -->
                <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 mb-4">
                    <label class="block text-sm font-semibold mb-2">Share Trip Link with Trusted Contacts:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               id="shareLink" 
                               value="{{ route('trip.view', $trip->share_uuid) }}" 
                               readonly 
                               class="flex-1 px-4 py-2 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50">
                        <button onclick="copyShareLink()" class="bg-white text-green-600 hover:bg-green-50 px-6 py-2 rounded-lg font-semibold transition-all">
                            📋 Copy
                        </button>
                    </div>
                </div>

                <!-- Route Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 backdrop-blur-md rounded-lg p-4">
                        <p class="text-sm text-green-100 mb-1">From</p>
                        <p class="font-semibold" id="originAddress">Loading...</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-lg p-4">
                        <p class="text-sm text-green-100 mb-1">To</p>
                        <p class="font-semibold" id="destinationAddress">Loading...</p>
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div id="map" class="w-full" style="height: 500px;"></div>
            </div>
            
            <!-- GPS Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Speed</p>
                            <p class="font-bold text-lg" id="gpsSpeed">0 km/h</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Altitude</p>
                            <p class="font-bold text-lg" id="gpsAltitude">0 m</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-2 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Bearing</p>
                            <p class="font-bold text-lg" id="gpsBearing">0°</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Accuracy</p>
                            <p class="font-bold text-lg" id="gpsAccuracy">0 m</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency SOS Button -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Emergency Assistance</h3>
                        <p class="text-gray-600">Trigger instant SOS alert to all trusted contacts and nearby volunteers</p>
                    </div>
                    <button onclick="triggerSOS()" class="bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg transform hover:scale-105 transition-all">
                        🚨 SOS Alert
                    </button>
                </div>
            </div>

            <!-- Current Status Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Location Status -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Current Location</p>
                            <p class="font-bold text-gray-800" id="currentLocation">Tracking...</p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500" id="lastUpdate">Last updated: Just now</div>
                </div>

                <!-- Speed Status -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Current Speed</p>
                            <p class="font-bold text-gray-800"><span id="currentSpeed">0</span> km/h</p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">Monitoring speed changes</div>
                </div>

                <!-- Distance Status -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-pink-100 p-3 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Distance Traveled</p>
                            <p class="font-bold text-gray-800"><span id="distanceTraveled">0.0</span> km</p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">Tracking route progress</div>
                </div>
            </div>

            <!-- Trusted Contacts Monitoring -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">👥 Trusted Contacts Notified</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse(auth()->user()->trustedContacts as $contact)
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            <div class="bg-green-100 p-2 rounded-full mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $contact->name }}</p>
                                <p class="text-xs text-gray-500">{{ $contact->phone }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-8 text-gray-500">
                            <p>No trusted contacts added yet</p>
                            <a href="{{ route('trusted-contacts.index') }}" class="text-purple-600 hover:text-purple-700 font-semibold">Add Contacts</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Alerts History -->
            @if(($trip->routeAlerts && $trip->routeAlerts->count() > 0) || ($trip->sosAlerts && $trip->sosAlerts->count() > 0))
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">⚠️ Trip Alerts</h3>
                <div class="space-y-3">
                    @if($trip->routeAlerts)
                        @foreach($trip->routeAlerts as $alert)
                        <div class="flex items-start p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-800">{{ ucfirst($alert->type) }} Detected</p>
                                <p class="text-sm text-gray-600">{{ $alert->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                    @endif
                    @if($trip->sosAlerts)
                        @foreach($trip->sosAlerts as $sos)
                        <div class="flex items-start p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-800">SOS Alert Triggered</p>
                                <p class="text-sm text-gray-600">{{ $sos->created_at->diffForHumans() }}</p>
                                @if($sos->message)
                                    <p class="text-sm text-gray-700 mt-1">{{ $sos->message }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

    @vite(['resources/js/app.js'])
    
    <script>
        // Trip data from backend
        const tripData = @json($trip);
        const tripLocations = @json($trip->locations);
        let startTime = new Date('{{ $trip->started_at }}');
        
        // Mapbox configuration
        mapboxgl.accessToken = '{{ env("MAPBOX_KEY", "pk.eyJ1Ijoic2FmZXJpZGVhcHAiLCJhIjoiY2x6dzUwZ2Q2MGZyNTJqczFrODRpN3l0diJ9.gBz6fZXXN9TBKxFGDWZJ1g") }}';
        let map, currentMarker, routeLine;
        const routeCoordinates = [];

        // Initialize Mapbox
        function initMap() {
            // Default center (use trip origin or current location)
            const centerLat = tripData.current_lat || tripData.origin_lat;
            const centerLng = tripData.current_lng || tripData.origin_lng;
            
            map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [centerLng, centerLat],
                zoom: 14
            });
            
            map.addControl(new mapboxgl.NavigationControl());
            map.addControl(new mapboxgl.FullscreenControl());
            
            // Add origin marker
            new mapboxgl.Marker({ color: '#10b981' })
                .setLngLat([tripData.origin_lng, tripData.origin_lat])
                .setPopup(new mapboxgl.Popup().setHTML('<strong>Trip Start</strong>'))
                .addTo(map);
            
            // Add destination marker
            new mapboxgl.Marker({ color: '#ef4444' })
                .setLngLat([tripData.destination_lng, tripData.destination_lat])
                .setPopup(new mapboxgl.Popup().setHTML('<strong>Destination</strong>'))
                .addTo(map);
            
            // Add current location marker
            if (tripData.current_lat && tripData.current_lng) {
                currentMarker = new mapboxgl.Marker({ color: '#3b82f6' })
                    .setLngLat([tripData.current_lng, tripData.current_lat])
                    .setPopup(new mapboxgl.Popup().setHTML('<strong>Current Location</strong>'))
                    .addTo(map);
            }
            
            // Load historical route
            map.on('load', function () {
                loadHistoricalRoute();
            });
        }
        
        // Load historical route from trip_locations
        function loadHistoricalRoute() {
            if (tripLocations && tripLocations.length > 0) {
                tripLocations.forEach(loc => {
                    routeCoordinates.push([loc.longitude, loc.latitude]);
                });
                
                // Add route line to map
                if (map.getSource('route')) {
                    map.getSource('route').setData({
                        type: 'Feature',
                        properties: {},
                        geometry: {
                            type: 'LineString',
                            coordinates: routeCoordinates
                        }
                    });
                } else {
                    map.addSource('route', {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'LineString',
                                coordinates: routeCoordinates
                            }
                        }
                    });
                    
                    map.addLayer({
                        id: 'route',
                        type: 'line',
                        source: 'route',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#3b82f6',
                            'line-width': 4,
                            'line-opacity': 0.7
                        }
                    });
                }
            }
        }
        
        // Update map with new position
        function updateMapPosition(latitude, longitude) {
            if (!map || !currentMarker) return;
            
            const newLngLat = [longitude, latitude];
            
            // Update marker position
            currentMarker.setLngLat(newLngLat);
            
            // Add to route
            routeCoordinates.push(newLngLat);
            if (map.getSource('route')) {
                map.getSource('route').setData({
                    type: 'Feature',
                    properties: {},
                    geometry: {
                        type: 'LineString',
                        coordinates: routeCoordinates
                    }
                });
            }
            
            // Pan map to new location
            map.panTo(newLngLat);
        }
        
        // Update GPS metrics UI
        function updateGPSMetrics(position) {
            if (position.speed !== null && position.speed !== undefined) {
                document.getElementById('gpsSpeed').textContent = Math.round(position.speed) + ' km/h';
                document.getElementById('currentSpeed').textContent = Math.round(position.speed);
            }
            if (position.altitude !== null && position.altitude !== undefined) {
                document.getElementById('gpsAltitude').textContent = Math.round(position.altitude) + ' m';
            }
            if (position.bearing !== null && position.bearing !== undefined) {
                document.getElementById('gpsBearing').textContent = Math.round(position.bearing) + '°';
            }
            if (position.accuracy !== null && position.accuracy !== undefined) {
                document.getElementById('gpsAccuracy').textContent = Math.round(position.accuracy) + ' m';
            }
        }

        // Update trip duration
        function updateDuration() {
            const now = new Date();
            const diff = Math.floor((now - startTime) / 1000);
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;
            
            document.getElementById('tripDuration').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        // Copy share link
        function copyShareLink() {
            const shareLink = document.getElementById('shareLink');
            shareLink.select();
            document.execCommand('copy');
            alert('Share link copied! Send this to your trusted contacts.');
        }

        // End trip
        function endTrip() {
            if (confirm('Are you sure you want to end this trip?')) {
                fetch(`/trips/${tripData.id}/end`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Trip ended successfully!');
                        window.location.href = '/trips';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to end trip. Please try again.');
                });
            }
        }

        // Trigger SOS
        function triggerSOS() {
            if (confirm('⚠️ EMERGENCY SOS ALERT\n\nThis will notify all your trusted contacts and nearby volunteers immediately.\n\nProceed?')) {
                // Get current location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((position) => {
                        fetch('/sos', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                                trip_id: tripData.id,
                                message: 'Emergency SOS triggered during trip'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('🚨 SOS ALERT SENT!\n\nAll trusted contacts and volunteers have been notified of your emergency.');
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to send SOS alert. Please try calling emergency services.');
                        });
                    });
                } else {
                    alert('Geolocation not supported. Please enable location services.');
                }
            }
        }

        // Simulate location updates (in production, use actual GPS)
        function updateLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    fetch(`/trips/${tripData.id}/location`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('lastUpdate').textContent = 'Last updated: Just now';
                        document.getElementById('currentSpeed').textContent = 
                            Math.round(position.coords.speed || 0);
                    });
                });
            }
        }

        // Initialize Laravel Echo for real-time updates
        function initEcho() {
            if (window.Echo) {
                console.log('Subscribing to trip.' + tripData.id);
                
                window.Echo.private(`trip.${tripData.id}`)
                    .listen('TripLocationUpdated', (e) => {
                        console.log('Location update received:', e);
                        
                        // Update map
                        if (e.latest_position) {
                            updateMapPosition(
                                e.latest_position.latitude,
                                e.latest_position.longitude
                            );
                            
                            // Update GPS metrics
                            updateGPSMetrics(e.latest_position);
                            
                            // Update current location display
                            document.getElementById('currentLocation').textContent = 
                                `${e.latest_position.latitude.toFixed(4)}, ${e.latest_position.longitude.toFixed(4)}`;
                            document.getElementById('lastUpdate').textContent = 
                                'Last updated: Just now';
                        }
                    });
            } else {
                console.warn('Laravel Echo not available. Real-time updates disabled.');
                setTimeout(initEcho, 1000); // Retry after 1 second
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map
            initMap();
            
            // Initialize Echo for real-time updates
            initEcho();
            
            // Update duration every second
            setInterval(updateDuration, 1000);
            updateDuration();

            // Update location every 10 seconds
            setInterval(updateLocation, 10000);
            updateLocation();

            // Load addresses
            document.getElementById('originAddress').textContent = 
                `${tripData.origin_lat.toFixed(4)}, ${tripData.origin_lng.toFixed(4)}`;
            document.getElementById('destinationAddress').textContent = 
                `${tripData.destination_lat.toFixed(4)}, ${tripData.destination_lng.toFixed(4)}`;
        });
    </script>
</x-app-layout>
