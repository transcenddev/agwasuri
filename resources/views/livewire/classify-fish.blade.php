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
                {{-- Fish Icon --}}
                <div class="mx-auto flex items-center justify-center w-24 h-24 rounded-full bg-green-100 mb-6">
                    <span class="text-5xl">🐟</span>
                </div>

                {{-- Heading --}}
                <h3 class="text-2xl font-bold text-gray-800 mb-4">
                    No Prediction Yet
                </h3>

                {{-- Description --}}
                <p class="text-gray-600 text-center mb-8 leading-relaxed">
                    Get AI-powered fish species recommendations based on your current water quality parameters. 
                    Click the button above to analyze your pond conditions and discover the most suitable fish species.
                </p>

                {{-- Feature Grid (2x2) --}}
                <div class="bg-gray-50 rounded-xl p-6 mb-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3 text-left">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-600 text-white flex items-center justify-center text-xs mt-0.5">✓</div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Water Analysis</div>
                                <div class="text-xs text-gray-500">Real-time data</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-left">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-600 text-white flex items-center justify-center text-xs mt-0.5">✓</div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">AI Predictions</div>
                                <div class="text-xs text-gray-500">Species match</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-left">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-600 text-white flex items-center justify-center text-xs mt-0.5">✓</div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Confidence Score</div>
                                <div class="text-xs text-gray-500">Accuracy %</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-left">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-600 text-white flex items-center justify-center text-xs mt-0.5">✓</div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Recommendations</div>
                                <div class="text-xs text-gray-500">Actionable tips</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Predict Button --}}
                <button type="button" wire:click="predictFishType"
                    class="inline-flex items-center gap-2 px-6 py-3 text-white transition rounded-lg text-base font-semibold {{ $canPredict ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}"
                    {{ !$canPredict ? 'disabled' : '' }}
                    wire:loading.attr="disabled" wire:target="predictFishType">
                    <span wire:loading.remove wire:target="predictFishType">🔍 Predict Fish Type</span>
                    <span wire:loading wire:target="predictFishType">Predicting...</span>
                </button>

                {{-- Footer Note --}}
                <p class="text-sm text-gray-500 mt-8 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Predictions are based on your average water quality over the selected date range
                </p>
            </div>

            {{-- Train Button (Admin Only) for Empty State --}}
            @if($isAdminView)
                <div class="text-center mt-6">
                    <button type="button" wire:click="trainModel"
                        class="inline-flex items-center px-4 py-2 text-white transition bg-green-600 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-sm font-medium"
                        wire:loading.attr="disabled" wire:target="trainModel" {{ $isTraining ? 'disabled' : '' }}>
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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <button type="button" wire:click="resetPrediction"
                class="inline-flex items-center gap-2 px-4 py-2 border-2 border-green-600 text-green-600 hover:bg-green-50 transition rounded-lg text-sm font-semibold">
                🔄 Predict Again
            </button>

            @if($isAdminView)
                <div class="flex flex-col items-end gap-2">
                    <button type="button" wire:click="trainModel"
                        class="inline-flex items-center px-4 py-2 text-white transition bg-green-600 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-sm font-medium"
                        wire:loading.attr="disabled" wire:target="trainModel" {{ $isTraining ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="trainModel">Train Model</span>
                        <span wire:loading wire:target="trainModel">Training...</span>
                    </button>
                    @if($lastTrainedTimestamp && $lastTrainedTimestamp !== 'Error fetching date' && $lastTrainedTimestamp !== 'N/A')
                        <p class="text-xs text-gray-500">Last Trained: {{ $lastTrainedTimestamp }}</p>
                    @endif
                </div>
            @endif
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
                             class="w-64 md:w-80 h-auto object-cover rounded-2xl mx-auto" />
                    @else
                        <div class="w-64 md:w-80 h-64 md:h-80 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto">
                            <span class="text-6xl">🐟</span>
                        </div>
                    @endif
                    
                    <div class="absolute top-4 right-4 w-20 h-20 bg-green-600 text-white rounded-full flex flex-col items-center justify-center">
                        <span class="text-xl font-bold">{{ number_format($confidence, 0) }}%</span>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mt-6 mb-3">
                    {{ $topPrediction['species_name'] }}
                </h2>

                <div class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full font-semibold text-sm">
                    ✅ PERPEKTO PARA SA {{ strtoupper($topDisplay) }} / PERFECT FOR {{ strtoupper($topDisplay) }}
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
            <div class="lg:col-span-2 space-y-6">
                @if(!empty($waterQualityData))
                    <div class="bg-white rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                            📊 Water Quality Analysis
                        </h3>

                        @php $tempStatus = $this->getWaterQualityStatus('temperature', $waterQualityData['temperature']); @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span>🌡️</span>
                                <span class="font-medium">Temperature:</span>
                                <span class="text-gray-700">{{ number_format($waterQualityData['temperature'], 2) }}°C</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-{{ $tempStatus['color'] }}-100 text-{{ $tempStatus['color'] }}-800 rounded-full text-xs font-semibold">
                                {{ $tempStatus['emoji'] }} {{ $tempStatus['badgeTl'] }} / {{ $tempStatus['badge'] }}
                            </span>
                        </div>

                        @php $salinityStatus = $this->getWaterQualityStatus('salinity', $waterQualityData['salinity']); @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span>🧂</span>
                                <span class="font-medium">Salinity:</span>
                                <span class="text-gray-700">{{ number_format($waterQualityData['salinity'], 2) }} ppt</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-{{ $salinityStatus['color'] }}-100 text-{{ $salinityStatus['color'] }}-800 rounded-full text-xs font-semibold">
                                {{ $salinityStatus['emoji'] }} {{ $salinityStatus['badgeTl'] }} / {{ $salinityStatus['badge'] }}
                            </span>
                        </div>

                        @php $doStatus = $this->getWaterQualityStatus('dissolved_oxygen', $waterQualityData['dissolved_oxygen']); @endphp
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <span>💨</span>
                                <span class="font-medium">Dissolved Oxygen:</span>
                                <span class="text-gray-700">{{ number_format($waterQualityData['dissolved_oxygen'], 2) }} mg/L</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-{{ $doStatus['color'] }}-100 text-{{ $doStatus['color'] }}-800 rounded-full text-xs font-semibold">
                                {{ $doStatus['emoji'] }} {{ $doStatus['badgeTl'] }} / {{ $doStatus['badge'] }}
                            </span>
                        </div>

                        @php $phStatus = $this->getWaterQualityStatus('ph_level', $waterQualityData['ph_level']); @endphp
                        <div class="flex justify-between items-center py-3">
                            <div class="flex items-center gap-2">
                                <span>⚗️</span>
                                <span class="font-medium">pH Level:</span>
                                <span class="text-gray-700">{{ number_format($waterQualityData['ph_level'], 2) }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-{{ $phStatus['color'] }}-100 text-{{ $phStatus['color'] }}-800 rounded-full text-xs font-semibold">
                                {{ $phStatus['emoji'] }} {{ $phStatus['badgeTl'] }} / {{ $phStatus['badge'] }}
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

                <div class="bg-green-50 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
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

                <div class="bg-white rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-4">Detailed Predictions:</h3>
                    <div class="space-y-3">
                        @foreach($predictionResults as $prediction)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-gray-700">{{ $prediction['species_name'] }}</span>
                                    <span class="text-sm font-bold text-green-600">{{ number_format($prediction['confidence_percentage'], 2) }}%</span>
                                </div>
                                <div class="relative w-full h-6 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center text-white text-xs font-bold transition-all duration-500" 
                                         style="width: {{ $prediction['confidence_percentage'] }}%;">
                                        <span class="px-2">{{ number_format($prediction['confidence_percentage'], 0) }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                @if(!empty($modelMetrics))
                    <div class="bg-white rounded-xl p-6 lg:sticky lg:top-24">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                            🎯 Model Performance
                        </h3>

                        @if(isset($modelMetrics['accuracy']))
                            <div class="flex justify-between items-center py-3 border-b">
                                <div>
                                    <div class="text-sm text-gray-600">Accuracy</div>
                                    <div class="text-xs text-gray-500">Overall correctness</div>
                                </div>
                                <div class="text-lg font-bold text-green-600">{{ number_format($modelMetrics['accuracy'], 2) }}%</div>
                            </div>
                        @endif

                        @if(isset($modelMetrics['precision']))
                            <div class="flex justify-between items-center py-3 border-b">
                                <div>
                                    <div class="text-sm text-gray-600">Precision</div>
                                    <div class="text-xs text-gray-500">Positive prediction accuracy</div>
                                </div>
                                <div class="text-lg font-bold text-green-600">{{ number_format($modelMetrics['precision'], 2) }}%</div>
                            </div>
                        @endif

                        @if(isset($modelMetrics['recall']))
                            <div class="flex justify-between items-center py-3 border-b">
                                <div>
                                    <div class="text-sm text-gray-600">Recall</div>
                                    <div class="text-xs text-gray-500">Detection rate</div>
                                </div>
                                <div class="text-lg font-bold text-green-600">{{ number_format($modelMetrics['recall'], 2) }}%</div>
                            </div>
                        @endif

                        @if(isset($modelMetrics['f1_score']))
                            <div class="flex justify-between items-center py-3">
                                <div>
                                    <div class="text-sm text-gray-600">F1 Score</div>
                                    <div class="text-xs text-gray-500">Balanced performance</div>
                                </div>
                                <div class="text-lg font-bold text-green-600">{{ number_format($modelMetrics['f1_score'], 2) }}%</div>
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
