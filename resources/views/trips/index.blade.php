<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🚗 {{ __('My Rides') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Track and manage your journeys</p>
            </div>
            <a href="{{ route('trips.history') }}" class="text-purple-600 hover:text-purple-700 font-semibold flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                View History
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Where to today? Section -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h3 class="text-2xl font-bold bg-gradient-to-r from-pink-500 to-purple-600 bg-clip-text text-transparent mb-4">
                    Where to today?
                </h3>
                <div class="flex items-center space-x-4">
                    <div class="flex-1 relative">
                        <input type="text" 
                               id="addressInput"
                               placeholder="Find an address" 
                               class="w-full pl-12 pr-4 py-4 border-2 border-pink-300 rounded-xl focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all">
                        <svg class="w-6 h-6 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <button onclick="startRide()" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white px-8 py-4 rounded-xl font-bold hover:from-pink-600 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                        Start Ride
                    </button>
                </div>

                <!-- Your favorite locations -->
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-800">Your favorite locations</h4>
                        <button class="text-purple-600 hover:text-purple-700 text-sm font-medium">View all</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div onclick="addLocation('home')" class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-purple-50 cursor-pointer transition-all border border-gray-200 hover:border-purple-300">
                            <div class="bg-purple-100 p-3 rounded-lg mr-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Home</p>
                                <p class="text-sm text-gray-500" id="homeLocation">Add location</p>
                            </div>
                        </div>
                        <div onclick="addLocation('work')" class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-purple-50 cursor-pointer transition-all border border-gray-200 hover:border-purple-300">
                            <div class="bg-pink-100 p-3 rounded-lg mr-4">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Work</p>
                                <p class="text-sm text-gray-500" id="workLocation">Add location</p>
                            </div>
                        </div>
                        <div onclick="addLocation('favorite')" class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-purple-50 cursor-pointer transition-all border border-gray-200 hover:border-purple-300">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Favorite</p>
                                <p class="text-sm text-gray-500" id="favoriteLocation">Add location</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SafeRide Options -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">SafeRide Options</h3>
                    <div class="flex space-x-2">
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition-all">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition-all">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Standard Ride -->
                    <div onclick="selectRideOption('standard')" class="bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl p-6 text-white cursor-pointer hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="text-lg font-bold mb-1">Standard SafeRide</h4>
                                <p class="text-sm text-pink-100">Basic tracking & alerts</p>
                            </div>
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-bold">FREE</span>
                        </div>
                        <div class="relative h-32 mb-4">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-24 h-24 text-white/30" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                </svg>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Real-time tracking
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Emergency SOS
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Route monitoring
                            </li>
                        </ul>
                    </div>

                    <!-- Premium Ride -->
                    <div onclick="selectRideOption('plus')" class="bg-white border-2 border-amber-300 rounded-2xl p-6 cursor-pointer hover:border-amber-500 hover:shadow-xl transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-gray-800 mb-1">SafeRide Plus</h4>
                                <p class="text-sm text-gray-600">Enhanced protection</p>
                            </div>
                            <span class="bg-gradient-to-r from-amber-400 to-yellow-500 text-white px-3 py-1 rounded-full text-sm font-bold">PRO</span>
                        </div>
                        <div class="relative h-32 mb-4">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-24 h-24 text-amber-200" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                </svg>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                All Standard features
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                AI anomaly detection
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                24/7 volunteer support
                            </li>
                        </ul>
                    </div>

                    <!-- Emergency Only -->
                    <div onclick="selectRideOption('emergency')" class="bg-white border-2 border-red-300 rounded-2xl p-6 cursor-pointer hover:border-red-500 hover:shadow-xl transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-gray-800 mb-1">Emergency Mode</h4>
                                <p class="text-sm text-gray-600">SOS alerts only</p>
                            </div>
                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">SOS</span>
                        </div>
                        <div class="relative h-32 mb-4">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-24 h-24 text-red-200" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Instant SOS alerts
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Location sharing
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Quick response
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent Rides -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Recent Rides</h3>
                    <button class="text-purple-600 hover:text-purple-700 font-medium">View All</button>
                </div>

                <div class="space-y-4">
                    <!-- Sample ride -->
                    <div class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-purple-50 cursor-pointer transition-all border border-gray-200">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-blue-500 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-gray-800">Completed Ride</h4>
                                <span class="text-xs text-gray-500">2 hours ago</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">Downtown → University Campus</p>
                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    25 mins
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    12.5 km
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Completed</span>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p>No previous rides</p>
                        <p class="text-sm">Start your first SafeRide journey today!</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Store favorite locations in localStorage
        let favoriteLocations = JSON.parse(localStorage.getItem('favoriteLocations') || '{"home": null, "work": null, "favorite": null}');

        // Load saved locations on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Restore saved locations
            if (favoriteLocations.home) {
                document.getElementById('homeLocation').textContent = favoriteLocations.home;
            }
            if (favoriteLocations.work) {
                document.getElementById('workLocation').textContent = favoriteLocations.work;
            }
            if (favoriteLocations.favorite) {
                document.getElementById('favoriteLocation').textContent = favoriteLocations.favorite;
            }

            // Allow Enter key to start ride
            const addressInput = document.getElementById('addressInput');
            addressInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    startRide();
                }
            });
        });

        // Add location function
        function addLocation(type) {
            const address = prompt(`Enter your ${type} address:`);
            if (address && address.trim() !== '') {
                favoriteLocations[type] = address.trim();
                localStorage.setItem('favoriteLocations', JSON.stringify(favoriteLocations));
                document.getElementById(`${type}Location`).textContent = address.trim();
                alert(`${type.charAt(0).toUpperCase() + type.slice(1)} location saved: ${address}`);
            }
        }

        // Start Ride function - Simulated with coordinates
        function startRide() {
            const addressInput = document.getElementById('addressInput');
            const destination = addressInput.value.trim();
            
            if (destination === '') {
                alert('Please enter a destination address first!');
                addressInput.focus();
                return;
            }
            
            // Get current location
            if (navigator.geolocation) {
                alert('Getting your current location...');
                
                // Request location with proper options
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        // Simulate destination coordinates (in real app, use geocoding API)
                        const originLat = position.coords.latitude;
                        const originLng = position.coords.longitude;
                        const destLat = originLat + 0.01; // Simulate destination
                        const destLng = originLng + 0.01;

                        // Start trip via API
                        fetch('{{ route("trips.start") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                origin_lat: originLat,
                                origin_lng: originLng,
                                destination_lat: destLat,
                                destination_lng: destLng
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(`🚗 SafeRide Started!\n\nDestination: ${destination}\n\nYour trip is now being tracked. Trusted contacts have been notified.`);
                                window.location.href = `/trips/${data.trip.id}`;
                            } else {
                                alert('Failed to start trip: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to start trip. Please try again.');
                        });
                    },
                    (error) => {
                        // Better error handling based on error code
                        let errorMessage = '';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = '⚠️ Location access denied!\n\nPlease:\n1. Click the location icon in browser address bar\n2. Select "Allow"\n3. Refresh the page\n4. Try again';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = '⚠️ Location information unavailable.\n\nPlease check:\n• GPS is enabled on your device\n• You have internet connection\n• Try again in a moment';
                                break;
                            case error.TIMEOUT:
                                errorMessage = '⚠️ Location request timed out.\n\nPlease try again.';
                                break;
                            default:
                                errorMessage = '⚠️ Unknown error occurred while getting location.\n\nError: ' + error.message;
                        }
                        alert(errorMessage);
                        console.error('Geolocation error:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                alert('⚠️ Geolocation not supported!\n\nYour browser doesn\'t support location services.\nPlease use a modern browser like Chrome, Firefox, or Edge.');
            }
        }

        // Select Ride Option function
        function selectRideOption(option) {
            let optionName = '';
            let features = '';
            
            switch(option) {
                case 'standard':
                    optionName = 'Standard SafeRide';
                    features = '✓ Real-time tracking\n✓ Emergency SOS\n✓ Route monitoring\n✓ Trusted contacts notified\n\nPrice: FREE';
                    break;
                case 'plus':
                    optionName = 'SafeRide Plus';
                    features = '✓ All Standard features\n✓ AI anomaly detection\n✓ 24/7 volunteer support\n✓ Priority response\n\nPrice: Premium';
                    break;
                case 'emergency':
                    optionName = 'Emergency Mode';
                    features = '✓ Instant SOS alerts\n✓ Location sharing\n✓ Quick response\n✓ Immediate volunteer dispatch\n\nPrice: FREE';
                    break;
            }
            
            const confirmed = confirm(`Selected: ${optionName}\n\n${features}\n\nWould you like to proceed with this option?`);
            
            if (confirmed) {
                // Store selected option
                localStorage.setItem('selectedRideOption', option);
                alert(`✅ ${optionName} activated!\n\nEnter your destination to start your safe journey.`);
                document.getElementById('addressInput').focus();
            }
        }
    </script>
</x-app-layout>
