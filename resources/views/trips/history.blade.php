<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📋 {{ __('Trip History & Reports') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">View all your past trips and safety events</p>
            </div>
            <a href="{{ route('trips.index') }}" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                Start New Trip
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Trips</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $trips->total() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Completed</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $trips->where('status', 'completed')->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Route Alerts</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $trips->sum(fn($t) => $t->routeAlerts->count()) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="bg-red-100 p-3 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">SOS Alerts</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $trips->sum(fn($t) => $t->sosAlerts->count()) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trips List -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800">Your Trips</h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse($trips as $trip)
                        <div class="p-6 hover:bg-gray-50 transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            {{ $trip->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $trip->status === 'ongoing' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $trip->status === 'cancelled' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            {{ ucfirst($trip->status) }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $trip->started_at->format('M d, Y • h:i A') }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">From</p>
                                            <p class="text-sm font-semibold text-gray-800">
                                                {{ number_format($trip->origin_lat, 4) }}, {{ number_format($trip->origin_lng, 4) }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">To</p>
                                            <p class="text-sm font-semibold text-gray-800">
                                                {{ number_format($trip->destination_lat, 4) }}, {{ number_format($trip->destination_lng, 4) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Trip Stats -->
                                    <div class="flex items-center space-x-6 text-xs text-gray-600">
                                        @if($trip->ended_at)
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Duration: {{ $trip->started_at->diffInMinutes($trip->ended_at) }} mins
                                            </span>
                                        @endif
                                        @if($trip->sosAlerts->count() > 0)
                                            <span class="flex items-center text-red-600 font-semibold">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $trip->sosAlerts->count() }} SOS Alert(s)
                                            </span>
                                        @endif
                                        @if($trip->routeAlerts->count() > 0)
                                            <span class="flex items-center text-yellow-600 font-semibold">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $trip->routeAlerts->count() }} Route Alert(s)
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Alerts Details -->
                                    @if($trip->sosAlerts->count() > 0 || $trip->routeAlerts->count() > 0)
                                        <div class="mt-4 space-y-2">
                                            @foreach($trip->sosAlerts as $sos)
                                                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded text-sm">
                                                    <p class="font-semibold text-red-800">🚨 SOS Alert</p>
                                                    <p class="text-red-700">{{ $sos->created_at->format('h:i A') }}
                                                        @if($sos->message) - {{ $sos->message }}@endif
                                                    </p>
                                                </div>
                                            @endforeach
                                            @foreach($trip->routeAlerts as $alert)
                                                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded text-sm">
                                                    <p class="font-semibold text-yellow-800">⚠️ {{ ucfirst($alert->type) }} Detected</p>
                                                    <p class="text-yellow-700">{{ $alert->created_at->format('h:i A') }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-6 flex flex-col space-y-2">
                                    @if($trip->status === 'ongoing')
                                        <a href="{{ route('trips.show', $trip) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold text-center transition-all">
                                            View Live
                                        </a>
                                    @endif
                                    <a href="{{ route('trip.view', $trip->share_uuid) }}" target="_blank" class="bg-purple-100 hover:bg-purple-200 text-purple-700 px-4 py-2 rounded-lg text-sm font-semibold text-center transition-all">
                                        Share Link
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">No trips yet</h3>
                            <p class="text-gray-600 mb-4">Start your first SafeRide journey today!</p>
                            <a href="{{ route('trips.index') }}" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                                Start First Trip
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($trips->hasPages())
                    <div class="p-6 border-t border-gray-200">
                        {{ $trips->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
