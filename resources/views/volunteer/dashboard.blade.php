<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Volunteer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search/Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Search Parameters</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Latitude</label>
                            <p class="mt-1 text-sm text-gray-900">{{ number_format($volunteerLat, 6) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Longitude</label>
                            <p class="mt-1 text-sm text-gray-900">{{ number_format($volunteerLng, 6) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Search Radius</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $radiusKm }} km</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Nearby SOS Alerts</h3>
                        <span class="text-sm text-gray-600">
                            {{ $alerts->total() }} alert(s) found within {{ $radiusKm }} km
                        </span>
                    </div>

                    @if($alerts->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm">No unresolved SOS alerts in your area</p>
                            <p class="text-xs text-gray-400 mt-1">Check back later or adjust your search radius</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($alerts as $alert)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    🆘 Alert #{{ $alert->id }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ number_format($alert->distance_km, 2) }} km away
                                                </span>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <p class="text-xs text-gray-500">Location</p>
                                                    <p class="text-sm font-mono text-gray-700">
                                                        {{ number_format($alert->latitude, 6) }}, {{ number_format($alert->longitude, 6) }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500">Time</p>
                                                    <p class="text-sm text-gray-700">
                                                        {{ $alert->created_at->diffForHumans() }}
                                                        <span class="text-xs text-gray-400">
                                                            ({{ $alert->created_at->format('M d, Y h:i A') }})
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            @if($alert->message)
                                                <div class="mb-3">
                                                    <p class="text-xs text-gray-500">Message</p>
                                                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                                                        {{ $alert->message }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if($alert->user_id)
                                                <div>
                                                    <p class="text-xs text-gray-500">User ID: {{ $alert->user_id }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="ml-4">
                                            <button 
                                                type="button"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                onclick="claimAlert({{ $alert->id }})"
                                            >
                                                Claim
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Action Links -->
                                    <div class="mt-3 pt-3 border-t border-gray-100 flex space-x-4">
                                        <a href="https://www.google.com/maps?q={{ $alert->latitude }},{{ $alert->longitude }}" 
                                           target="_blank"
                                           class="text-xs text-blue-600 hover:text-blue-800">
                                            📍 View on Map
                                        </a>
                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $alert->latitude }},{{ $alert->longitude }}" 
                                           target="_blank"
                                           class="text-xs text-blue-600 hover:text-blue-800">
                                            🧭 Get Directions
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $alerts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function claimAlert(alertId) {
            // Placeholder for claim functionality
            // This will be implemented in a future feature
            alert(`Claiming alert #${alertId}. Claim functionality coming soon!`);
            console.log('Claim alert:', alertId);
        }
    </script>
    @endpush
</x-app-layout>
