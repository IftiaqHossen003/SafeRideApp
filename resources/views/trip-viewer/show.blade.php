<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeRide - Trip View</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

            <!-- Map Placeholder -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Live Location</h2>
                <div class="bg-gray-200 rounded-lg flex items-center justify-center" style="height: 400px;">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="mt-4 text-gray-600 font-medium">Map View (Coming Soon)</p>
                        <p class="text-sm text-gray-500">Live map tracking will be added in a future update</p>
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
                <p class="mt-1">This is a read-only view. Location updates in real-time (feature coming soon).</p>
            </div>
        </div>
    </div>
</body>
</html>
