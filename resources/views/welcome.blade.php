<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SafeRide - Your Safety Companion</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-pink-50 via-purple-50 to-pink-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-pink-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-br from-pink-500 to-purple-600 p-2 rounded-xl">
                        <svg class="w-8 h-8" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="logo-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#FFFFFF;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#FFFFFF;stop-opacity:0.8" />
                                </linearGradient>
                            </defs>
                            <path d="M100 20 L160 40 L160 90 Q160 140 100 180 Q40 140 40 90 L40 40 Z" 
                                  fill="url(#logo-gradient)" stroke="none"/>
                            <path d="M100 60 L85 100 L100 140 M100 60 L115 100 L100 140" 
                                  stroke="#9333EA" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="100" cy="70" r="8" fill="#9333EA"/>
                            <circle cx="100" cy="130" r="8" fill="#9333EA"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">SafeRide</span>
                </div>
                
                @if (Route::has('login'))
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white px-6 py-2 rounded-full font-semibold hover:shadow-lg transition-all transform hover:scale-105">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-pink-600 font-semibold transition">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold py-2 px-6 rounded-full shadow-lg transform hover:scale-105 transition-all duration-200">
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative min-h-screen flex items-center justify-center px-4 py-20">
        <!-- Content -->
        <div class="relative max-w-7xl mx-auto text-center">
            <div class="mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-4 leading-tight">
                    <span class="text-gray-900">Your Journey,</span><br/>
                    <span class="bg-gradient-to-r from-pink-500 to-purple-600 bg-clip-text text-transparent text-5xl md:text-7xl">Our Priority</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    Travel with confidence. SafeRide keeps you connected and protected throughout your journey.
                </p>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-20">
                @guest
                    <a href="{{ route('register') }}" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700 font-bold py-4 px-10 rounded-full shadow-2xl transform hover:scale-105 transition-all duration-200 text-lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Start Your Safe Journey
                    </a>
                    <a href="{{ route('login') }}" class="bg-white text-pink-600 hover:bg-pink-50 font-bold py-4 px-10 rounded-full shadow-2xl transform hover:scale-105 transition-all duration-200 text-lg border-2 border-pink-200">
                        Sign In
                    </a>
                @else
                    <a href="{{ url('/dashboard') }}" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white hover:from-pink-600 hover:to-purple-700 font-bold py-4 px-10 rounded-full shadow-2xl transform hover:scale-105 transition-all duration-200 text-lg">
                        Go to Dashboard
                    </a>
                @endguest
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Feature 1 -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:scale-105">
                    <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Trusted Contacts</h3>
                    <p class="text-gray-700">Keep your loved ones informed about your journey automatically</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:scale-105">
                    <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Emergency SOS</h3>
                    <p class="text-gray-700">One-tap emergency alerts sent to your trusted contacts instantly</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:scale-105">
                    <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Real-time Tracking</h3>
                    <p class="text-gray-700">Live location sharing with route monitoring and anomaly detection</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="relative bg-white/10 backdrop-blur-md border-t border-white/20 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-gray-900 mb-2">24/7</div>
                    <div class="text-gray-700">Protection</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-gray-900 mb-2">100%</div>
                    <div class="text-gray-700">Privacy</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-gray-900 mb-2">Instant</div>
                    <div class="text-gray-700">Alerts</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-gray-900 mb-2">Free</div>
                    <div class="text-gray-700">Forever</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="relative bg-white/50 backdrop-blur-md border-t border-pink-100 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-800 mb-2 font-semibold">
                    © {{ date('Y') }} SafeRide. Keeping you safe, every mile.
                </p>
                <p class="text-sm text-gray-600">
                    Your journey companion for a safer tomorrow.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
