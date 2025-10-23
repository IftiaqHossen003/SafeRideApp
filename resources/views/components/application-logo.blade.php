<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <!-- SafeRide Logo: Shield with Route/Path Symbol -->
    <defs>
        <linearGradient id="safeRideGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#8B5CF6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#EC4899;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Shield outline -->
    <path d="M100 20 L160 40 L160 90 Q160 140 100 180 Q40 140 40 90 L40 40 Z" 
          fill="url(#safeRideGradient)" stroke="none"/>
    
    <!-- Route/Road symbol inside shield -->
    <path d="M100 60 L85 100 L100 140 M100 60 L115 100 L100 140" 
          stroke="white" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    
    <!-- Location pin -->
    <circle cx="100" cy="70" r="8" fill="white"/>
    <circle cx="100" cy="130" r="8" fill="white"/>
</svg>
