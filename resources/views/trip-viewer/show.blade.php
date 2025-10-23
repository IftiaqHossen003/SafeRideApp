<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeRide - Trip View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Mapbox GL JS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js'></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-3xl font-bold text-gray-900">SafeRide Trip Tracker</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($trip->status === 'ongoing') bg-green-100 text-green-800
                        @elseif($trip->status === 'completed') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($trip->status) }}
                    </span>
                </div>
                <p class="text-gray-600">Tracking trip by: <span class="font-semibold">{{ $userDisplayName }}</span></p>
                <p class="text-sm text-gray-500 mt-2">Share ID: {{ $trip->share_uuid }}</p>
            </div>

            <!-- Live Map -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-4 bg-gradient-to-r from-purple-500 to-blue-500">
                    <h2 class="text-xl font-semibold text-white">Live Location</h2>
                    <p class="text-sm text-purple-100">Real-time GPS tracking</p>
                </div>
                <div id="map" class="w-full" style="height: 500px;"></div>
            </div>
            
            <!-- GPS Metrics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-md p-4">
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
                <div class="bg-white rounded-lg shadow-md p-4">
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
                <div class="bg-white rounded-lg shadow-md p-4">
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
                <div class="bg-white rounded-lg shadow-md p-4">
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

            <!-- Trip Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Origin -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Origin
                    </h3>
                    <div class="space-y-2">
                        <p class="text-gray-700">
                            <span class="font-medium">Latitude:</span> {{ $trip->origin_lat }}
                        </p>
                        <p class="text-gray-700">
                            <span class="font-medium">Longitude:</span> {{ $trip->origin_lng }}
                        </p>
                    </div>
                </div>

                <!-- Destination -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Destination
                    </h3>
                    <div class="space-y-2">
                        <p class="text-gray-700">
                            <span class="font-medium">Latitude:</span> {{ $trip->destination_lat }}
                        </p>
                        <p class="text-gray-700">
                            <span class="font-medium">Longitude:</span> {{ $trip->destination_lng }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Current Location -->
            @if($trip->current_lat && $trip->current_lng)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="h-5 w-5 text-blue-500 mr-2 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                    Current Location
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <p class="text-gray-700">
                        <span class="font-medium">Latitude:</span> {{ $trip->current_lat }}
                    </p>
                    <p class="text-gray-700">
                        <span class="font-medium">Longitude:</span> {{ $trip->current_lng }}
                    </p>
                </div>
                <p class="text-sm text-gray-500 mt-3">
                    <span class="font-medium">Last Updated:</span> {{ $trip->updated_at->diffForHumans() }}
                </p>
            </div>
            @endif

            <!-- Trip Timeline -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Trip Timeline</h3>
                <div class="space-y-3">
                    @if($trip->started_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Trip Started</p>
                            <p class="text-sm text-gray-500">{{ $trip->started_at->format('M d, Y - h:i A') }}</p>
                            <p class="text-xs text-gray-400">{{ $trip->started_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endif

                    @if($trip->ended_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Trip Ended</p>
                            <p class="text-sm text-gray-500">{{ $trip->ended_at->format('M d, Y - h:i A') }}</p>
                            <p class="text-xs text-gray-400">{{ $trip->ended_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @elseif($trip->status === 'ongoing')
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Trip In Progress</p>
                            <p class="text-sm text-gray-500">Currently traveling...</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>SafeRide - Keeping you safe on every journey</p>
                <p class="mt-1">This is a read-only view with real-time location updates.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Trip data from backend
        const tripData = @json($trip);
        const tripLocations = @json($trip->locations ?? []);
        
        // Mapbox configuration
        mapboxgl.accessToken = '{{ env("MAPBOX_KEY", "pk.eyJ1Ijoic2FmZXJpZGVhcHAiLCJhIjoiY2x6dzUwZ2Q2MGZyNTJqczFrODRpN3l0diJ9.gBz6fZXXN9TBKxFGDWZJ1g") }}';
        let map, currentMarker;
        const routeCoordinates = [];
        
        // Initialize Mapbox
        function initMap() {
            const centerLat = tripData.current_lat || tripData.origin_lat;
            const centerLng = tripData.current_lng || tripData.origin_lng;
            
            map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [centerLng, centerLat],
                zoom: 13
            });
            
            map.addControl(new mapboxgl.NavigationControl());
            map.addControl(new mapboxgl.FullscreenControl());
            
            // Add origin marker (green)
            new mapboxgl.Marker({ color: '#10b981' })
                .setLngLat([tripData.origin_lng, tripData.origin_lat])
                .setPopup(new mapboxgl.Popup().setHTML('<strong>Trip Start</strong>'))
                .addTo(map);
            
            // Add destination marker (red)
            new mapboxgl.Marker({ color: '#ef4444' })
                .setLngLat([tripData.destination_lng, tripData.destination_lat])
                .setPopup(new mapboxgl.Popup().setHTML('<strong>Destination</strong>'))
                .addTo(map);
            
            // Add current location marker (blue)
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
                
                // Update GPS metrics with latest location
                if (tripLocations.length > 0) {
                    const latest = tripLocations[tripLocations.length - 1];
                    updateGPSMetrics(latest);
                }
            }
        }
        
        // Update GPS metrics UI
        function updateGPSMetrics(position) {
            if (position.speed !== null && position.speed !== undefined) {
                document.getElementById('gpsSpeed').textContent = Math.round(position.speed) + ' km/h';
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
        
        // Update map with new position (for real-time updates)
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
        
        // Auto-refresh every 10 seconds to get latest data
        function autoRefresh() {
            if (tripData.status === 'ongoing') {
                setInterval(() => {
                    console.log('Refreshing trip data...');
                    location.reload();
                }, 10000); // Refresh every 10 seconds for ongoing trips
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            autoRefresh();
        });
    </script>
</body>
</html>
