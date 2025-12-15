<div class="p-4 w-full">
    {{-- Flash Messages --}}
    @if($error)
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded-lg">
            {{ $error }}
        </div>
    @endif

    @if(session()->has('train_message'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-400 rounded-lg">
            {{ session('train_message') }}
        </div>
    @endif
    
    @if(session()->has('train_error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded-lg">
            {{ session('train_error') }}
        </div>
    @endif

    {{-- ============================================
         STATE 1: EMPTY STATE (Before Prediction)
         ============================================ --}}
    @if(empty($predictionResults))
        <div class="max-w-2xl mx-auto">
            {{-- Centered Empty State Card --}}
            <div class="text-center py-16 px-4">
                {{-- Trust Signal Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full mb-6 shadow-sm" 
                     style="background: rgba(27, 101, 27, 0.05); border: 1px solid rgba(27, 101, 27, 0.2);">
                    <svg class="w-4 h-4" style="color: #1B651B;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs font-semibold" style="color: #1B651B;">✓ AI-Powered Recommendations</span>
                </div>

                {{-- Animated Fish Carousel --}}
                <div class="mx-auto flex items-center justify-center w-72 h-72 mb-6 overflow-visible relative" id="fish-carousel" style="background: transparent;">
                    <img id="carousel-fish-img" src="{{ asset('assets/species/tilapia.png') }}" alt="Fish species" class="w-56 h-48 md:w-64 md:h-56 object-contain transition-all duration-500 opacity-0 mx-auto">
                    {{-- species name intentionally removed for cleaner empty state --}}
                </div>

                {{-- Algorithm Badge with CVSU Green --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full mb-4 shadow-md" 
                     style="background: linear-gradient(135deg, rgba(27, 101, 27, 0.1) 0%, rgba(15, 73, 16, 0.1) 100%); border: 2px solid rgba(27, 101, 27, 0.3);">
                    <svg class="w-4 h-4" style="color: #1B651B;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                    </svg>
                    <span class="text-sm font-bold" style="color: #1B651B;">Powered by Decision Tree Algorithm</span>
                </div>

                {{-- Heading --}}
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">
                    Ready to Predict Optimal Fish Species?
                </h3>

                {{-- Description --}}
                <p class="text-gray-600 text-center mb-6 leading-relaxed text-base">
                    Get instant AI-powered recommendations based on your current water quality. 
                    Analysis completes in seconds.
                </p>

                {{-- Feature Pills (horizontal) --}}
                <div class="flex flex-wrap items-center justify-center gap-3 mb-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center text-xs text-white" style="background: #1B651B;">✓</div>
                        <span class="text-sm font-medium text-gray-700">Water Analysis</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center text-xs text-white" style="background: #1B651B;">✓</div>
                        <span class="text-sm font-medium text-gray-700">AI Predictions</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center text-xs text-white" style="background: #1B651B;">✓</div>
                        <span class="text-sm font-medium text-gray-700">Confidence Score</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center text-xs text-white" style="background: #1B651B;">✓</div>
                        <span class="text-sm font-medium text-gray-700">Recommendations</span>
                    </div>
                </div>

                {{-- Footer Note (moved above button) --}}
                <p class="text-sm text-gray-500 mb-6 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Predictions based on average water quality • ⏱️ ~3 seconds
                </p>

                {{-- Enhanced Predict Button --}}
                <button type="button" wire:click="predictFishType"
                    class="inline-flex items-center gap-2 px-8 py-4 text-white transition rounded-3xl text-lg font-bold shadow-xl hover:shadow-2xl transform hover:scale-105 {{ $canPredict ? 'animate-pulse' : '' }}"
                    style="{{ $canPredict ? 'background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);' : 'background: #9ca3af; cursor: not-allowed;' }}"
                    {{ !$canPredict ? 'disabled' : '' }}
                    wire:loading.attr="disabled" wire:target="predictFishType">
                    <span wire:loading.remove wire:target="predictFishType">🔍 Predict Optimal Fish Species</span>
                    <span wire:loading wire:target="predictFishType">Analyzing Water Quality...</span>
                </button>

                {{-- Footer Note --}}
                <p class="text-sm text-gray-500 mt-8 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Predictions are based on your average water quality over the selected date range
                </p>

                {{-- Model Information Card with CVSU Branding - Collapsed by default --}}
                <details class="mt-8">
                    <summary class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-200 hover:border-green-300 transition text-sm font-medium text-gray-700">
                        ℹ️ Learn about the algorithm
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="mt-4 bg-white rounded-3xl shadow-xl p-6 border-l-4" style="border-color: #1B651B;">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg text-white flex items-center justify-center" style="background: #1B651B;">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 text-left">
                                <h4 class="text-sm font-bold text-gray-900 mb-1">About the Model</h4>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span>Algorithm:</span>
                                        <span class="font-semibold text-gray-900">Decision Tree (CART)</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Parameters Used:</span>
                                        <span class="font-semibold text-gray-900">4 (Temp, DO, Salinity, pH)</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Model Accuracy:</span>
                                        <span class="font-semibold" style="color: #1B651B;">~87%</span>
                                    </div>
                                    @if($lastTrainedTimestamp && $lastTrainedTimestamp !== 'N/A')
                                    <div class="flex items-center justify-between">
                                        <span>Last Trained:</span>
                                        <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($lastTrainedTimestamp)->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </details>
            </div>

            {{-- Train Button (Admin Only) for Empty State --}}
            @if($isAdminView)
                <div class="text-center mt-6">
                    <button type="button" wire:click="trainModel"
                        class="inline-flex items-center gap-2 px-6 py-3 text-white transition rounded-3xl font-semibold shadow-xl text-sm"
                        style="background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);"
                        aria-label="Train model" wire:loading.attr="disabled" wire:target="trainModel" {{ $isTraining ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="trainModel">⚙️ Train Model</span>
                        <span wire:loading wire:target="trainModel">Training...</span>
                    </button>
                    @if($lastTrainedTimestamp && $lastTrainedTimestamp !== 'Error fetching date' && $lastTrainedTimestamp !== 'N/A')
                        <p class="mt-2 text-xs text-gray-500">Last Trained: {{ $lastTrainedTimestamp }}</p>
                    @endif
                </div>
            @endif
        </div>

    @else
        {{-- STATE 2: RESULTS STATE --}}
        
        {{-- Quick Action Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6 p-4 bg-white rounded-3xl shadow-md">
            <button type="button" wire:click="resetPrediction"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-white hover:opacity-90 transition rounded-full text-sm font-semibold shadow-sm"
                style="background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);">
                🔄 Predict Again
            </button>
            
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 transition rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                    📄 Download Report
                </button>
                <button type="button" onclick="if(navigator.share){navigator.share({title:'Fish Prediction',text:'Recommended: {{ $topPrediction['species_name'] ?? '' }}'})}else{alert('Share not supported')}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 transition rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                    📱 Share
                </button>
                @if($isAdminView)
                    <button type="button" wire:click="trainModel"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 transition rounded-full text-sm font-medium text-gray-700 border border-gray-200"
                        wire:loading.attr="disabled" wire:target="trainModel" {{ $isTraining ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="trainModel">⚙️ Train Model</span>
                        <span wire:loading wire:target="trainModel">Training...</span>
                    </button>
                @endif
            </div>
        </div>

        @php
            $topPrediction = $predictionResults[0] ?? null;
            $topImg = $topPrediction ? $this->getSpeciesImage($topPrediction['species_name']) : null;
            $topDisplay = $topPrediction ? trim(explode('(', $topPrediction['species_name'])[0]) : '';
            $confidence = $topPrediction['confidence_percentage'] ?? 0;
            $confidenceLevel = $this->getConfidenceLevel($confidence);
        @endphp

        @if($topPrediction)
            <div class="text-center mb-8">
                <div class="relative inline-block">
                    @if($topImg)
                        <img src="{{ asset('assets/species/' . $topImg) }}" 
                             alt="{{ $topDisplay }}" 
                             class="w-64 md:w-80 h-auto object-cover rounded-3xl mx-auto shadow-xl" />
                    @else
                        <div class="w-64 md:w-80 h-64 md:h-80 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto shadow-xl">
                            <span class="text-6xl">🐟</span>
                        </div>
                    @endif
                    
                    {{-- CVSU Green Gradient Confidence Badge --}}
                    <div class="absolute top-4 right-4 w-24 h-24 rounded-full flex flex-col items-center justify-center shadow-xl" 
                         style="background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);">
                        <span class="text-2xl font-black text-white">{{ number_format($confidence, 0) }}%</span>
                        <span class="text-xs font-semibold text-white/90 uppercase tracking-wide">Match</span>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mt-6 mb-3">
                    {{ $topPrediction['species_name'] }}
                </h2>

                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-semibold text-sm shadow-md" 
                     style="background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%); color: white;">
                    ✅ PERPEKTO PARA SA {{ strtoupper($topDisplay) }} / PERFECT FOR {{ strtoupper($topDisplay) }}
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
            <div class="lg:col-span-2 space-y-6">
                @if(!empty($waterQualityData))
                    <div class="bg-white rounded-3xl shadow-xl p-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-800">
                            📊 Water Quality Analysis
                        </h3>

                        @php 
                            $tempStatus = $this->getWaterQualityStatus('temperature', $waterQualityData['temperature']); 
                            $tempBadgeClass = $tempStatus['color'] === 'green' ? 'bg-green-100 text-green-800' : ($tempStatus['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            $tempIcon = $tempStatus['color'] === 'green' ? '🟢' : ($tempStatus['color'] === 'yellow' ? '🟡' : '🔴');
                        @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $tempIcon }}</span>
                                <span>🌡️</span>
                                <span class="font-medium">Temperature:</span>
                                <span class="text-gray-700 font-semibold">{{ number_format($waterQualityData['temperature'], 2) }}°C</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 {{ $tempBadgeClass }} rounded-full text-xs font-semibold">
                                {{ $tempStatus['badgeTl'] }}
                            </span>
                        </div>

                        @php 
                            $salinityStatus = $this->getWaterQualityStatus('salinity', $waterQualityData['salinity']); 
                            $salBadgeClass = $salinityStatus['color'] === 'green' ? 'bg-green-100 text-green-800' : ($salinityStatus['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            $salIcon = $salinityStatus['color'] === 'green' ? '🟢' : ($salinityStatus['color'] === 'yellow' ? '🟡' : '🔴');
                        @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $salIcon }}</span>
                                <span>🧂</span>
                                <span class="font-medium">Salinity:</span>
                                <span class="text-gray-700 font-semibold">{{ number_format($waterQualityData['salinity'], 2) }} ppt</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 {{ $salBadgeClass }} rounded-full text-xs font-semibold">
                                {{ $salinityStatus['badgeTl'] }}
                            </span>
                        </div>

                        @php 
                            $doStatus = $this->getWaterQualityStatus('dissolved_oxygen', $waterQualityData['dissolved_oxygen']); 
                            $doBadgeClass = $doStatus['color'] === 'green' ? 'bg-green-100 text-green-800' : ($doStatus['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            $doIcon = $doStatus['color'] === 'green' ? '🟢' : ($doStatus['color'] === 'yellow' ? '🟡' : '🔴');
                        @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $doIcon }}</span>
                                <span>💨</span>
                                <span class="font-medium">Dissolved Oxygen:</span>
                                <span class="text-gray-700 font-semibold">{{ number_format($waterQualityData['dissolved_oxygen'], 2) }} mg/L</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 {{ $doBadgeClass }} rounded-full text-xs font-semibold">
                                {{ $doStatus['badgeTl'] }}
                            </span>
                        </div>

                        @php 
                            $phStatus = $this->getWaterQualityStatus('ph_level', $waterQualityData['ph_level']); 
                            $phBadgeClass = $phStatus['color'] === 'green' ? 'bg-green-100 text-green-800' : ($phStatus['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            $phIcon = $phStatus['color'] === 'green' ? '🟢' : ($phStatus['color'] === 'yellow' ? '🟡' : '🔴');
                        @endphp
                        <div class="flex justify-between items-center py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $phIcon }}</span>
                                <span>⚗️</span>
                                <span class="font-medium">pH Level:</span>
                                <span class="text-gray-700 font-semibold">{{ number_format($waterQualityData['ph_level'], 2) }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 {{ $phBadgeClass }} rounded-full text-xs font-semibold">
                                {{ $phStatus['badgeTl'] }}
                            </span>
                        </div>

                        <div class="bg-green-50 p-3 rounded-lg mt-4">
                            <p class="text-sm font-medium text-green-900">
                                Confidence Level: 
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-{{ $confidenceLevel['color'] }}-100 text-{{ $confidenceLevel['color'] }}-800 rounded text-xs font-semibold ml-2">
                                    {{ $confidenceLevel['emoji'] }} {{ $confidenceLevel['textTl'] }} / {{ $confidenceLevel['text'] }}
                                </span>
                            </p>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-3xl shadow-xl p-6 border-l-4" style="border-color: #1B651B;">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2" style="color: #1B651B;">
                        📋 Mga Dapat Gawin / Recommended Actions
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="text-lg">✅</span>
                            <p class="text-sm text-gray-700">Ihanda ang {{ $topDisplay ?? 'fish' }} fingerlings</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-lg">✅</span>
                            <p class="text-sm text-gray-700">Itakda ang feeding schedule: 3x daily (morning, noon, afternoon)</p>
                        </div>
                        @if(isset($salinityStatus) && $salinityStatus['color'] !== 'green')
                            <div class="flex items-start gap-3">
                                <span class="text-lg">⚠️</span>
                                <p class="text-sm text-gray-700">Bantayan ang salinity levels - {{ $salinityStatus['badgeTl'] }}</p>
                            </div>
                        @endif
                        <div class="flex items-start gap-3">
                            <span class="text-lg">📞</span>
                            <p class="text-sm text-gray-700">Contact MAO Ternate for support: +63-XXX-XXX-XXXX</p>
                        </div>
                    </div>
                </div>

                {{-- Detailed Predictions - Collapsible --}}
                <details class="bg-white rounded-3xl shadow-xl overflow-hidden">
                    <summary class="cursor-pointer p-6 hover:bg-gray-50 transition flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800">All Species Rankings</h3>
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-6 space-y-3">
                        @foreach($predictionResults as $prediction)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-gray-700">{{ $prediction['species_name'] }}</span>
                                    <span class="text-sm font-bold" style="color: #1B651B;">{{ number_format($prediction['confidence_percentage'], 2) }}%</span>
                                </div>
                                <div class="relative w-full h-6 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="absolute inset-0 flex items-center justify-center text-white text-xs font-bold transition-all duration-500" 
                                         style="width: {{ $prediction['confidence_percentage'] }}%; background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);">
                                        <span class="px-2">{{ number_format($prediction['confidence_percentage'], 0) }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            </div>

            <div class="lg:col-span-1">
                @if(!empty($modelMetrics))
                    <div class="bg-white rounded-3xl shadow-xl p-6 lg:sticky lg:top-24">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-800">
                            🎯 Model Performance
                        </h3>

                        @if(isset($modelMetrics['accuracy']))
                            <div class="flex justify-between items-center py-3 border-b">
                                <div>
                                    <div class="text-sm text-gray-600 font-semibold">Accuracy</div>
                                    <div class="text-xs text-gray-500">Overall correctness</div>
                                </div>
                                <div class="text-lg font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['accuracy'], 2) }}%</div>
                            </div>
                        @endif

                        @if(isset($modelMetrics['precision']))
                            <div class="flex justify-between items-center py-3 border-b">
                                <div>
                                    <div class="text-sm text-gray-600 font-semibold">Precision</div>
                                    <div class="text-xs text-gray-500">Positive prediction accuracy</div>
                                </div>
                                <div class="text-lg font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['precision'], 2) }}%</div>
                            </div>
                        @endif

                        @if(isset($modelMetrics['recall']))
                            <div class="flex justify-between items-center py-3 border-b">
                                <div>
                                    <div class="text-sm text-gray-600 font-semibold">Recall</div>
                                    <div class="text-xs text-gray-500">Detection rate</div>
                                </div>
                                <div class="text-lg font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['recall'], 2) }}%</div>
                            </div>
                        @endif

                        @if(isset($modelMetrics['f1_score']))
                            <div class="flex justify-between items-center py-3">
                                <div>
                                    <div class="text-sm text-gray-600 font-semibold">F1 Score</div>
                                    <div class="text-xs text-gray-500">Balanced performance</div>
                                </div>
                                <div class="text-lg font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['f1_score'], 2) }}%</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div wire:loading.flex wire:target="predictFishType" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="p-6 bg-white rounded-lg text-center">
            <div class="inline-block w-8 h-8 border-4 border-green-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-gray-700 font-semibold">Processing Prediction...</p>
        </div>
    </div>

    <div wire:loading.flex wire:target="trainModel" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="p-6 bg-white rounded-lg text-center">
            <div class="inline-block w-8 h-8 border-4 border-green-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-gray-700 font-semibold">Initiating Model Training...</p>
        </div>
    </div>
</div>

<script>
// Animated Fish Carousel for Empty State
(function() {
    const species = [
        { name: 'Tilapia', file: 'tilapia' },
        { name: 'Bangus', file: 'bangus' },
        { name: 'Maya-maya', file: 'maya-maya' },
        { name: 'Lapu-lapu', file: 'lapu-lapu' },
        { name: 'Hito', file: 'hito' }
    ];
    
    let currentIndex = 0;
    const carousel = document.getElementById('fish-carousel');
    
    if (!carousel) return; // Exit if carousel not present (prediction results shown)
    
    const img = document.getElementById('carousel-fish-img');
    const nameLabel = document.getElementById('carousel-fish-name');
    
    function updateFish() {
        // Fade out
        img.style.opacity = '0';
        
        setTimeout(() => {
            // Update image
            const fish = species[currentIndex];
            const extensions = ['png', 'jpg', 'jpeg', 'webp'];
            
            // Try to load the image with different extensions
            const basePath = '{{ asset("assets/species") }}';
            img.src = `${basePath}/${fish.file}.png`;
            if (nameLabel) {
                nameLabel.textContent = fish.name;
            }
            
            // Fade in
            setTimeout(() => {
                img.style.opacity = '1';
            }, 50);
            
            // Move to next fish
            currentIndex = (currentIndex + 1) % species.length;
        }, 300);
    }
    
    // Initial display
    setTimeout(() => {
        img.style.opacity = '1';
    }, 100);
    
    // Rotate every 3 seconds
    setInterval(updateFish, 3000);
})();
</script>
