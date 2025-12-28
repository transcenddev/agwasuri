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
            <div class="text-center py-8 px-4">
                {{-- Animated Fish Carousel --}}
                <div class="mx-auto flex items-center justify-center w-96 h-96 mb-6 overflow-visible relative" id="fish-carousel" style="background: transparent;">
                    <img id="carousel-fish-img" src="{{ asset('assets/species/tilapia.png') }}" alt="Fish species" class="w-80 h-72 md:w-96 md:h-80 object-contain transition-all duration-500 opacity-0 mx-auto">
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
                        <svg class="w-4 h-4" style="color: #1B651B;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Water Analysis</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100">
                        <svg class="w-4 h-4" style="color: #1B651B;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">AI Predictions</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100">
                        <svg class="w-4 h-4" style="color: #1B651B;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Confidence Score</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100">
                        <svg class="w-4 h-4" style="color: #1B651B;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Recommendations</span>
                    </div>
                </div>

                {{-- Footer Note (moved above button) --}}
                <p class="text-sm text-gray-500 mb-6 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Predictions based on average water quality | ~3 seconds
                </p>

                {{-- Enhanced Predict Button --}}
                <button type="button" wire:click="predictFishType"
                    class="inline-flex items-center gap-2 px-8 py-4 text-white transition rounded-3xl text-lg font-bold shadow-xl hover:shadow-2xl transform hover:scale-105 {{ $canPredict ? 'animate-pulse' : '' }}"
                    style="{{ $canPredict ? 'background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);' : 'background: #9ca3af; cursor: not-allowed;' }}"
                    {{ !$canPredict ? 'disabled' : '' }}
                    wire:loading.attr="disabled" wire:target="predictFishType">
                    <svg wire:loading.remove wire:target="predictFishType" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span wire:loading.remove wire:target="predictFishType">Predict Optimal Fish Species</span>
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
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Learn about the algorithm
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
                        <svg wire:loading.remove wire:target="trainModel" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span wire:loading.remove wire:target="trainModel">Train Model</span>
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
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <button type="button" wire:click="resetPrediction"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-white hover:opacity-90 transition rounded-full text-sm font-semibold shadow-sm"
                style="background: linear-gradient(135deg, #1B651B 0%, #0f4910 100%);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Predict Again
            </button>
            
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 transition rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Report
                </button>
                <button type="button" onclick="if(navigator.share){navigator.share({title:'Fish Prediction',text:'Recommended: {{ $topPrediction['species_name'] ?? '' }}'})}else{alert('Share not supported')}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 transition rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    Share
                </button>
                @if($isAdminView)
                    <button type="button" wire:click="trainModel"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 transition rounded-full text-sm font-medium text-gray-700 border border-gray-200"
                        wire:loading.attr="disabled" wire:target="trainModel" {{ $isTraining ? 'disabled' : '' }}>
                        <svg wire:loading.remove wire:target="trainModel" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span wire:loading.remove wire:target="trainModel">Train Model</span>
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
                             class="w-80 md:w-96 h-auto object-cover mx-auto" />
                    @else
                        <div class="w-80 md:w-96 h-80 md:h-96 bg-gray-100 flex items-center justify-center mx-auto">
                            <svg class="w-32 h-32 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
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
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    PERPEKTO PARA SA {{ strtoupper($topDisplay) }} / PERFECT FOR {{ strtoupper($topDisplay) }}
                </div>
            </div>
        @endif

        {{-- Model Performance Metrics Bar --}}
        @if(!empty($modelMetrics))
            <div class="flex flex-wrap items-center justify-center gap-3 mb-8">
                @if(isset($modelMetrics['accuracy']))
                    <div class="inline-flex flex-col items-center px-6 py-3 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="text-2xl font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['accuracy'], 2) }}%</div>
                        <div class="text-xs text-gray-600 font-semibold">Accuracy</div>
                        <div class="text-xs text-gray-500">Overall correctness</div>
                    </div>
                @endif

                @if(isset($modelMetrics['precision']))
                    <div class="inline-flex flex-col items-center px-6 py-3 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="text-2xl font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['precision'], 2) }}%</div>
                        <div class="text-xs text-gray-600 font-semibold">Precision</div>
                        <div class="text-xs text-gray-500">Positive prediction accuracy</div>
                    </div>
                @endif

                @if(isset($modelMetrics['recall']))
                    <div class="inline-flex flex-col items-center px-6 py-3 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="text-2xl font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['recall'], 2) }}%</div>
                        <div class="text-xs text-gray-600 font-semibold">Recall</div>
                        <div class="text-xs text-gray-500">Detection rate</div>
                    </div>
                @endif

                @if(isset($modelMetrics['f1_score']))
                    <div class="inline-flex flex-col items-center px-6 py-3 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="text-2xl font-bold" style="color: #1B651B;">{{ number_format($modelMetrics['f1_score'], 2) }}%</div>
                        <div class="text-xs text-gray-600 font-semibold">F1 Score</div>
                        <div class="text-xs text-gray-500">Balanced performance</div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Full-width content sections --}}
        <div class="space-y-6">
            @if(!empty($waterQualityData))
                    <div class="bg-white rounded-3xl shadow-xl p-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-800">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Water Quality Analysis
                        </h3>

                        @php 
                            $tempStatus = $this->getWaterQualityStatus('temperature', $waterQualityData['temperature']); 
                            $tempBadgeClass = $tempStatus['color'] === 'green' ? 'bg-green-100 text-green-800' : ($tempStatus['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            $tempCircleClass = $tempStatus['color'] === 'green' ? 'bg-green-500' : ($tempStatus['color'] === 'yellow' ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full {{ $tempCircleClass }}"></span>
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
                            $salCircleClass = $salinityStatus['color'] === 'green' ? 'bg-green-500' : ($salinityStatus['color'] === 'yellow' ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full {{ $salCircleClass }}"></span>
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
                            $doCircleClass = $doStatus['color'] === 'green' ? 'bg-green-500' : ($doStatus['color'] === 'yellow' ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full {{ $doCircleClass }}"></span>
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
                            $phCircleClass = $phStatus['color'] === 'green' ? 'bg-green-500' : ($phStatus['color'] === 'yellow' ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <div class="flex justify-between items-center py-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full {{ $phCircleClass }}"></span>
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
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Mga Dapat Gawin / Recommended Actions
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-gray-700">Ihanda ang {{ $topDisplay ?? 'fish' }} fingerlings</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-gray-700">Itakda ang feeding schedule: 3x daily (morning, noon, afternoon)</p>
                        </div>
                        @if(isset($salinityStatus) && $salinityStatus['color'] !== 'green')
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm text-gray-700">Bantayan ang salinity levels - {{ $salinityStatus['badgeTl'] }}</p>
                            </div>
                        @endif
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
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
