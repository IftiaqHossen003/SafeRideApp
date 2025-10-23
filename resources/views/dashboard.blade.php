<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Hello, {{ Auth::user()->pseudonym ?? Auth::user()->name }}! 👋
                </h2>
                <p class="text-sm text-gray-600 mt-1">Where would you like to go today?</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Emergency SOS Button - Most Prominent -->
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Emergency SOS</h3>
                        <p class="text-red-100">Press this button if you need immediate help</p>
                    </div>
                    <button onclick="triggerSOS()" class="bg-white text-red-600 hover:bg-red-50 font-bold py-4 px-8 rounded-full shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xl">SOS</span>
                    </button>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Start New Trip Card -->
                <a href="{{ route('trips.index') }}" class="bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200 cursor-pointer block">
                    <div class="flex flex-col h-full">
                        <div class="flex-1">
                            <div class="bg-white/20 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Start New Trip</h3>
                            <p class="text-purple-100 text-sm">Begin tracking your journey</p>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm font-semibold">Tap to start →</span>
                        </div>
                    </div>
                </a>

                <!-- Trusted Contacts Card -->
                <a href="{{ route('trusted-contacts.index') }}" class="bg-white rounded-xl shadow-lg p-6 border-2 border-purple-100 hover:border-purple-300 transform hover:scale-105 transition-all duration-200">
                    <div class="flex flex-col h-full">
                        <div class="flex-1">
                            <div class="bg-gradient-to-br from-purple-500 to-pink-500 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-2 text-gray-800">Trusted Contacts</h3>
                            <p class="text-gray-600 text-sm">Manage your emergency contacts</p>
                        </div>
                        <div class="mt-4">
                            <span class="text-purple-600 text-sm font-semibold">View contacts →</span>
                        </div>
                    </div>
                </a>

                <!-- Profile Card -->
                <a href="{{ route('profile.edit') }}" class="bg-white rounded-xl shadow-lg p-6 border-2 border-purple-100 hover:border-purple-300 transform hover:scale-105 transition-all duration-200">
                    <div class="flex flex-col h-full">
                        <div class="flex-1">
                            <div class="bg-gradient-to-br from-purple-500 to-pink-500 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-2 text-gray-800">My Profile</h3>
                            <p class="text-gray-600 text-sm">Update your information</p>
                        </div>
                        <div class="mt-4">
                            <span class="text-purple-600 text-sm font-semibold">Edit profile →</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Recent Activity / Trip History -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Recent Activity</h3>
                    <span class="text-sm text-purple-600 font-semibold cursor-pointer hover:text-purple-700">View All</span>
                </div>
                
                <div class="space-y-4">
                    <!-- Placeholder for trips - will be populated dynamically -->
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <div class="bg-purple-100 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">No trips yet</h4>
                            <p class="text-sm text-gray-600">Start your first SafeRide journey</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Safety Tips -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl shadow-lg p-6 border border-purple-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🛡️ Safety Tips</h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Always add trusted contacts before starting a trip
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Keep the SOS button easily accessible during trips
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Share your trip details with friends and family
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <script>
        function triggerSOS() {
            if (confirm('⚠️ EMERGENCY SOS ALERT\n\nThis will immediately notify:\n• All your trusted contacts\n• Nearby volunteers\n• Emergency services\n\nProceed?')) {
                // Get current location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            fetch('{{ route("sos.store") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                    trip_id: null,
                                    message: 'Emergency SOS triggered from dashboard'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('🚨 SOS ALERT SENT!\n\nYour trusted contacts and nearby volunteers have been notified.\n\nStay safe. Help is on the way.');
                                } else {
                                    alert('Failed to send SOS: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Failed to send SOS alert. Please call emergency services directly.');
                            });
                        },
                        (error) => {
                            // If location fails, still send SOS with no coordinates
                            console.warn('Location unavailable for SOS:', error.code, error.message);
                            
                            if (confirm('⚠️ Location unavailable.\n\nSend SOS alert without location?\n\n(Your contacts will be notified but won\'t see your exact location)')) {
                                fetch('{{ route("sos.store") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        lat: 0,
                                        lng: 0,
                                        trip_id: null,
                                        message: 'Emergency SOS triggered (location unavailable)'
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    alert('🚨 SOS ALERT SENT!\n\n(Location could not be determined)\n\nYour trusted contacts have been notified.');
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Failed to send SOS. Please call emergency services: 999');
                                });
                            }
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 5000,
                            maximumAge: 0
                        }
                    );
                } else {
                    // No geolocation support - offer to send SOS anyway
                    if (confirm('⚠️ Your browser doesn\'t support location services.\n\nSend SOS alert without location?')) {
                        fetch('{{ route("sos.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                lat: 0,
                                lng: 0,
                                trip_id: null,
                                message: 'Emergency SOS triggered (geolocation not supported)'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert('🚨 SOS ALERT SENT!\n\nYour trusted contacts have been notified.');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to send SOS. Please call emergency services: 999');
                        });
                    }
                }
            }
        }
    </script>
</x-app-layout>
