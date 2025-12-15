<div x-data="{ open: false, menu: false, compact: false, showDetails: false }" @toggle-compact-view.window="compact = !compact">
    {{-- Simplified Fish Recommendation Card (Dashboard Teaser) --}}
    <div class="bg-white rounded-2xl p-6 transition-all duration-300 h-full flex flex-col" x-bind:class="compact ? 'p-4' : 'p-6'">
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

        {{-- Responsive species image for dashboard --}}
        <div class="flex justify-center items-center transition-all duration-300 flex-grow" x-bind:class="compact ? 'mb-2' : 'mb-2'">
            @if($found)
                <img src="{{ asset('assets/species/' . $found) }}" alt="{{ $species }}" class="object-contain rounded-xl transition-all duration-300 w-full max-w-md h-auto" x-bind:class="compact ? 'w-20 h-20 md:w-24 md:h-24 max-w-none' : ''"/>
            @else
            <svg viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="rounded-xl transition-all duration-300 w-full max-w-md h-auto" x-bind:class="compact ? 'w-20 h-20 md:w-24 md:h-24 max-w-none' : ''">
                <defs>
                    <linearGradient id="g1" x1="0" x2="1">
                        <stop offset="0%" stop-color="#34D399" />
                        <stop offset="100%" stop-color="#10B981" />
                    </linearGradient>
                </defs>
                <path d="M4 16c6-8 18-8 28-8 0 0-2.5 4-2.5 8S32 24 32 24C22 24 10 24 4 16z" fill="url(#g1)" opacity="0.95" />
                <circle cx="34" cy="8" r="3" fill="#F97316" />
            </svg>
        @endif
    </div>

    {{-- Simplified species title with badges --}}
    <div class="text-center" x-bind:class="compact ? 'mb-1' : 'mb-2'">
        <h3 class="font-extrabold text-cvsu-700 transition-all duration-300" style="color: #1B651B;" x-bind:class="compact ? 'text-base' : 'text-xl'">{{ $species }}</h3>
        <div class="flex items-center justify-center gap-2 mt-2" x-show="!compact" x-transition>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 shadow-sm">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Recommended
            </span>
            @if($accuracy ?? null)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 shadow-sm">
                {{ number_format($accuracy, 1) }}%
            </span>
            @endif
        </div>
        
        {{-- Simple summary message --}}
        <div class="mt-2" x-show="!compact">
            <p class="text-sm text-gray-700 leading-relaxed">{{ $reason }}</p>
        </div>
    </div>

    {{-- Single Call-to-Action to full analysis --}}
    <div class="mt-3" x-show="!compact">
        <a href="{{ route('optimal-fish') }}" class="block w-full px-4 py-2.5 text-center text-sm font-bold text-white bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 rounded-xl shadow-md transition-all duration-200 transform hover:scale-105">
            See Full Analysis →
        </a>
    </div>
    </div>

</div>

