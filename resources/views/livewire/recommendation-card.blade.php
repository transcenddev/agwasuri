@php
    // Keep the original green theme
    $cardGradient = 'linear-gradient(135deg, #1B651B 0%, #0f4910 100%)';
    // Get species image filename
    $speciesImage = $this->getSpeciesImage($species);
@endphp
<div class="w-full" x-data="{ open: false, menu: false, compact: false, showDetails: false }" @toggle-compact-view.window="compact = !compact">
    {{-- Fish Recommendation Card --}}
    <div class="relative w-full rounded-3xl p-6 transition-all duration-300 h-full flex flex-col overflow-hidden shadow-xl" 
         x-bind:class="compact ? 'p-4 shadow-lg' : 'p-6 shadow-xl'"
         style="background: {{ $cardGradient }};">
        
        {{-- Decorative gradient overlay --}}
        <div class="absolute top-0 right-0 w-48 h-48 bg-white opacity-10 rounded-full -mr-24 -mt-24 z-0"></div>

        {{-- Top icons --}}
        <div class="flex items-start justify-between mb-4 relative z-10">
            {{-- Fish icon --}}
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 48 32">
                    <path d="M4 16c6-8 18-8 28-8 0 0-2.5 4-2.5 8S32 24 32 24C22 24 10 24 4 16z" opacity="0.95" />
                    <circle cx="34" cy="8" r="2" />
                </svg>
            </div>
            {{-- Arrow link icon (prefill analysis when available) --}}
            <button type="button" wire:click="viewFullAnalysis" class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center hover:bg-opacity-30 transition-all">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7m0 0H7m10 0v10"/>
                </svg>
            </button>
        </div>

        {{-- Title --}}
        <h3 class="text-white text-lg font-bold mb-4 relative z-10" x-bind:class="compact ? 'text-base mb-2' : 'text-lg mb-4'">
            Optimal Species Predictor
        </h3>

        @if(!$hasPrediction)
            {{-- EMPTY STATE: Awaiting prediction --}}
            {{-- CTA badge --}}
            <div class="flex items-center gap-3 mb-4 relative z-10" x-show="!compact">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white bg-opacity-20 text-white">
                    Awaiting prediction
                </span>
            </div>

            {{-- Animated fish carousel --}}
            <div id="dashboard-fish-carousel" data-base="{{ asset('assets/species') }}" class="flex justify-center items-center my-4 relative z-20 flex-grow" x-bind:class="compact ? 'my-2' : 'my-4'">
                <img id="dashboard-fish-img" src="{{ asset('assets/species/tilapia.png') }}" 
                     alt="Fish species" 
                     class="object-contain w-full max-w-xs h-auto drop-shadow-2xl" 
                     style="transition: opacity 0.45s ease, transform 0.45s ease; opacity: 0;"
                     x-bind:class="compact ? 'w-20 h-20 max-w-none' : ''"/>
            </div>

            {{-- Ready message --}}
            <div class="relative z-10" x-show="!compact">
                <h4 class="text-white text-xl font-extrabold mb-2">Ready to analyze</h4>
                @if($hasLatestData)
                    <p class="text-white text-sm opacity-90 leading-relaxed">Using your latest measurements from {{ $latestRecordedAt }}. Run the predictor to get a tailored species recommendation.</p>
                @else
                    <p class="text-white text-sm opacity-90 leading-relaxed">Add a new water quality measurement, then run the predictor to see the best species for current conditions.</p>
                @endif
            </div>

            {{-- Error message --}}
            @if($error)
                <div class="mt-3 px-3 py-2 bg-red-100 text-red-800 rounded-lg text-xs font-medium relative z-10" x-show="!compact">
                    {{ $error }}
                </div>
            @endif

            {{-- Run Prediction Button --}}
            <div class="mt-4 relative z-10" x-show="!compact">
                <button wire:click="predictFishType" wire:loading.attr="disabled"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm text-white rounded-lg text-sm font-semibold transition-all disabled:opacity-50">
                    <svg wire:loading.remove wire:target="predictFishType" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span wire:loading.remove wire:target="predictFishType">Run Prediction</span>
                    <span wire:loading wire:target="predictFishType">Analyzing...</span>
                </button>
            </div>
        @else
            {{-- RESULT STATE: Show prediction --}}
            {{-- Confidence badge --}}
            <div class="flex items-center gap-3 mb-4 relative z-10" x-show="!compact">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white text-green-800">
                    {{ number_format($confidence, 0) }}% Match
                </span>
            </div>

            {{-- Fish image --}}
            <div class="flex justify-center items-center my-4 relative z-20 flex-grow" x-bind:class="compact ? 'my-2' : 'my-4'">
                @if($speciesImage)
                    <img src="{{ asset('assets/species/' . $speciesImage) }}" 
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

            {{-- Species name and confidence --}}
            <div class="relative z-10" x-show="!compact">
                <h4 class="text-white text-xl font-extrabold mb-2">{{ explode(' ', $species)[0] }}</h4>
                <p class="text-white text-sm opacity-90 leading-relaxed">{{ $species }}</p>
            </div>

            {{-- Action buttons --}}
            <div class="mt-4 flex gap-2 relative z-10" x-show="!compact">
                <button type="button" wire:click="viewFullAnalysis" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm text-white rounded-lg text-sm font-semibold transition-all">
                    View Full Analysis
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button wire:click="resetPrediction"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white bg-opacity-10 hover:bg-opacity-20 backdrop-blur-sm text-white rounded-lg text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </button>
            </div>
        @endif
    </div>
</div>

{{-- Rotate fish imagery when awaiting prediction --}}
@if(!$hasPrediction)
<script>
(function() {
    const carousel = document.getElementById('dashboard-fish-carousel');
    const img = document.getElementById('dashboard-fish-img');
    if (!carousel || !img) return;

    const basePath = carousel.dataset.base || '';
    const species = [
        { name: 'Tilapia', file: 'tilapia' },
        { name: 'Bangus', file: 'bangus' },
        { name: 'Maya-maya', file: 'maya-maya' },
        { name: 'Lapu-lapu', file: 'lapu-lapu' },
        { name: 'Hito', file: 'hito' }
    ];

    let idx = 0;
    function rotate() {
        img.style.opacity = '0';
        img.style.transform = 'scale(0.96)';
        setTimeout(() => {
            const fish = species[idx];
            img.src = `${basePath}/${fish.file}.png`;
            img.alt = fish.name;
            setTimeout(() => {
                img.style.opacity = '1';
                img.style.transform = 'scale(1)';
            }, 40);
            idx = (idx + 1) % species.length;
        }, 220);
    }

    // Initial fade-in
    setTimeout(() => {
        img.style.opacity = '1';
        img.style.transform = 'scale(1)';
    }, 100);
    // Rotate every 3 seconds
    setInterval(rotate, 3000);
})();
</script>
@endif

