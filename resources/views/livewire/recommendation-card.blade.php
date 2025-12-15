<div class="w-full" x-data="{ open: false, menu: false, compact: false, showDetails: false }" @toggle-compact-view.window="compact = !compact">
    {{-- Fish Recommendation Card - Inspired by Plant Health Design --}}
    <div class="relative w-full bg-gradient-to-br from-green-500 to-green-700 rounded-3xl p-6 transition-all duration-300 h-full flex flex-col overflow-hidden shadow-xl" 
         x-bind:class="compact ? 'p-4 shadow-lg' : 'p-6 shadow-xl'"
         style="background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);">
        
        {{-- Decorative gradient overlay --}}
        <div class="absolute top-0 right-0 w-48 h-48 bg-white opacity-10 rounded-full -mr-24 -mt-24"></div>
        
        @php
            // Prepare species image once and reuse
            $base = trim(preg_replace('/\(.+\)/', '', $species));
            $slug = Str::slug($base);
            $found = null;
            foreach (['webp','png','jpg','jpeg'] as $ext) {
                $candidate = public_path('assets/species/' . $slug . '.' . $ext);
                if (file_exists($candidate)) { $found = $slug . '.' . $ext; break; }
            }
        @endphp

        {{-- Top icons --}}
        <div class="flex items-start justify-between mb-4 relative z-10">
            {{-- Fish icon --}}
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 48 32">
                    <path d="M4 16c6-8 18-8 28-8 0 0-2.5 4-2.5 8S32 24 32 24C22 24 10 24 4 16z" opacity="0.95" />
                    <circle cx="34" cy="8" r="2" />
                </svg>
            </div>
            {{-- Arrow link icon --}}
            <a href="{{ route('optimal-fish') }}" class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center hover:bg-opacity-30 transition-all">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7m0 0H7m10 0v10"/>
                </svg>
            </a>
        </div>

        {{-- Title --}}
        <h3 class="text-white text-lg font-bold mb-4 relative z-10" x-bind:class="compact ? 'text-base mb-2' : 'text-lg mb-4'">
            Recommended Species
        </h3>

        {{-- Large confidence score with badge --}}
        <div class="flex items-center gap-3 mb-4 relative z-10" x-show="!compact">
            @if($accuracy ?? null)
                <span class="text-5xl font-black text-white">{{ number_format($accuracy, 0) }}%</span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white bg-opacity-20 text-white backdrop-blur-sm">
                    Match
                </span>
            @endif
        </div>

        {{-- Fish image - centered and prominent --}}
        <div class="flex justify-center items-center my-4 relative z-10 flex-grow" x-bind:class="compact ? 'my-2' : 'my-4'">
            @if($found)
                <img src="{{ asset('assets/species/' . $found) }}" 
                     alt="{{ $species }}" 
                     class="object-contain w-full max-w-xs h-auto drop-shadow-2xl" 
                     x-bind:class="compact ? 'w-20 h-20 max-w-none' : ''"/>
            @else
                <svg viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" 
                     class="w-full max-w-xs h-auto drop-shadow-2xl" 
                     x-bind:class="compact ? 'w-20 h-20 max-w-none' : ''">
                    <defs>
                        <linearGradient id="fish-grad" x1="0" x2="1">
                            <stop offset="0%" stop-color="#FFF" stop-opacity="0.9"/>
                            <stop offset="100%" stop-color="#FFF" stop-opacity="0.7"/>
                        </linearGradient>
                    </defs>
                    <path d="M4 16c6-8 18-8 28-8 0 0-2.5 4-2.5 8S32 24 32 24C22 24 10 24 4 16z" fill="url(#fish-grad)"/>
                    <circle cx="34" cy="8" r="3" fill="#FFF"/>
                </svg>
            @endif
        </div>

        {{-- Species name and message --}}
        <div class="relative z-10" x-show="!compact">
            <h4 class="text-white text-xl font-extrabold mb-2">{{ explode(' ', $species)[0] }}</h4>
            <p class="text-white text-sm opacity-90 leading-relaxed">{{ $reason }}</p>
        </div>

        {{-- Bottom CTA badge --}}
        <div class="mt-4 relative z-10" x-show="!compact">
            <a href="{{ route('optimal-fish') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm text-white rounded-lg text-sm font-semibold transition-all">
                View Full Analysis
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

