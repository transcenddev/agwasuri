<div class="p-4">
    {{-- Flash Messages for General Errors --}}
    @if($error)
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded">
            {{ $error }}
        </div>
    @endif

    {{-- Flash Messages for Training Action (Keep green/red for status) --}}
    @if(session()->has('train_message'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-400 rounded">
            {{ session('train_message') }}
        </div>
    @endif
    @if(session()->has('train_error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded">
            {{ session('train_error') }}
        </div>
    @endif


    {{-- Buttons Row --}}
    <div class="flex items-start justify-between mb-4 space-x-4"> {{-- Use flex for row layout --}}
        {{-- Predict Button (Keep blue) --}}
        <div>
            <button wire:click="predictFishType"
                    class="px-4 py-2 text-white transition rounded {{ $canPredict ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed' }}"
                    {{ !$canPredict ? 'disabled' : '' }}
                    wire:loading.attr="disabled" wire:target="predictFishType, trainModel" {{-- Disable while predicting OR training --}}
                    title="{{ !$canPredict && $isAdminView ? 'Please select a user first' : 'Predict suitable fish type based on average water quality' }}">
                 <span wire:loading.remove wire:target="predictFishType">Predict Fish Type</span>
                 <span wire:loading wire:target="predictFishType">Predicting...</span>
            </button>
        </div>

        {{-- Train Button and Timestamp (Admin Only) --}}
        @if($isAdminView)
            <div class="text-right"> {{-- Align content to the right --}}
                 {{-- Changed button color to blue --}}
                <button wire:click="trainModel"
                        class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled" wire:target="predictFishType, trainModel" {{-- Disable while predicting OR training --}}
                        {{ $isTraining ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="trainModel">Train Model</span>
                    <span wire:loading wire:target="trainModel">Training...</span>
                </button>
                {{-- Keep timestamp text gray --}}
                @if($lastTrainedTimestamp && $lastTrainedTimestamp !== 'Error fetching date' && $lastTrainedTimestamp !== 'N/A')
                    <p class="mt-1 text-xs text-gray-500">Last Trained: {{ $lastTrainedTimestamp }}</p>
                @elseif($lastTrainedTimestamp)
                     <p class="mt-1 text-xs text-red-500">{{ $lastTrainedTimestamp }}</p> {{-- Show errors/NA in red --}}
                 @endif
            </div>
        @endif
    </div>


    {{-- Show results or skeleton --}}
    @if(!empty($predictionResults) || !empty($modelMetrics))
        {{-- Actual Results Display --}}
        <div class="mt-6">
            {{-- Analysis Summary Card --}}
            @if(!empty($explanations['predictions']))
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <h4 class="mb-3 text-xl font-semibold text-gray-800">Analysis Summary</h4>
                    <p class="mb-2 text-gray-600 whitespace-pre-line">{{ $explanations['predictions'] }}</p>
                    @if(!empty($explanations['top_prediction']))
                        <p class="mb-2 font-semibold text-gray-600">{{ $explanations['top_prediction'] }}</p>
                    @endif
                    @if(!empty($explanations['confidence_context']))
                        <p class="font-medium text-gray-700">
                            Confidence Level:
                            <span class="font-semibold"> {{ $explanations['confidence_context'] }}</span>
                        </p>
                    @endif
                </div>
            @endif

            {{-- Detailed Predictions --}}
            @if(!empty($predictionResults))
                <div class="mb-6">
                    <h4 class="mb-4 text-xl font-semibold text-gray-800">Detailed Predictions:</h4>
                    @foreach($predictionResults as $prediction)
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-700">{{ $prediction['species_name'] }}</span>
                            <span class="text-sm text-gray-600">
                                {{ number_format($prediction['confidence_percentage'], 2) }}%
                            </span>
                        </div>
                        <div class="w-full h-4 bg-gray-200 rounded-full">
                            <div class="h-4 transition-all duration-500 bg-blue-600 rounded-full" style="width: {{ $prediction['confidence_percentage'] }}%">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif

            {{-- Model Performance Metrics --}}
            @if(!empty($explanations['metrics']))
                <div class="mt-6">
                    <h4 class="mb-4 text-xl font-semibold text-gray-800">Model Performance Metrics:</h4>
                    <div class="overflow-hidden bg-white rounded-lg shadow-md">
                        <div class="divide-y divide-gray-200">
                             @if(isset($modelMetrics['accuracy']) && isset($explanations['metrics']['accuracy']))
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-gray-700">Accuracy</span>
                                        <span class="font-semibold text-blue-600">{{ number_format($modelMetrics['accuracy'], 2) }}%</span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $explanations['metrics']['accuracy'] }}</p>
                                </div>
                            @endif
                            @if(isset($modelMetrics['precision']) && isset($explanations['metrics']['precision']))
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-gray-700">Precision</span>
                                        <span class="font-semibold text-blue-600">{{ number_format($modelMetrics['precision'], 2) }}%</span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $explanations['metrics']['precision'] }}</p>
                                </div>
                            @endif
                             @if(isset($modelMetrics['recall']) && isset($explanations['metrics']['recall']))
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-gray-700">Recall</span>
                                        <span class="font-semibold text-blue-600">{{ number_format($modelMetrics['recall'], 2) }}%</span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $explanations['metrics']['recall'] }}</p>
                                </div>
                            @endif
                           @if(isset($modelMetrics['f1_score']) && isset($explanations['metrics']['f1_score']))
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-gray-700">F1 Score</span>
                                        <span class="font-semibold text-blue-600">{{ number_format($modelMetrics['f1_score'], 2) }}%</span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $explanations['metrics']['f1_score'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
         {{-- Skeleton Loader - Shown when not loading and no results/error --}}
         <div wire:loading.remove wire:target="predictFishType, trainModel" class="mt-6">

            {{-- Skeleton for Analysis Summary --}}
            <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                 <h4 class="mb-3 text-xl font-semibold text-gray-800">Analysis Summary</h4>
                 <div class="space-y-2 animate-pulse">
                    <div class="w-full h-4 bg-gray-300 rounded"></div>
                    <div class="w-3/4 h-4 bg-gray-300 rounded"></div>
                    <div class="w-1/2 h-4 bg-gray-300 rounded"></div>
                    <div class="w-1/4 h-4 bg-gray-300 rounded"></div>
                 </div>
            </div>

            {{-- Skeleton for Detailed Predictions --}}
            <div class="mb-6">
                <h4 class="mb-4 text-xl font-semibold text-gray-800">Detailed Predictions:</h4>
                @for ($i = 0; $i < 3; $i++)
                    <div class="mb-4 animate-pulse">
                        <div class="flex items-center justify-between mb-1">
                            <div class="w-1/4 h-4 bg-gray-300 rounded"></div>
                            <div class="w-1/6 h-4 bg-gray-300 rounded"></div>
                        </div>
                        <div class="w-full h-4 bg-gray-300 rounded-full"></div>
                    </div>
                @endfor
            </div>

            {{-- Skeleton for Model Performance --}}
            <div class="mt-6">
                 <h4 class="mb-4 text-xl font-semibold text-gray-800">Model Performance Metrics:</h4>
                 <div class="overflow-hidden bg-white rounded-lg shadow-md">
                     <div class="divide-y divide-gray-200">
                          @for ($i = 0; $i < 4; $i++)
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-gray-700">Accuracy</span>
                                    <div class="w-1/6 h-4 bg-gray-300 rounded animate-pulse"></div>
                                </div>
                                <div class="w-full h-3 mt-1 bg-gray-300 rounded animate-pulse"></div>
                            </div>
                         @endfor
                     </div>
                 </div>
            </div>
         </div>
    @endif


    {{-- Loading State for Prediction (Keep blue spinner) --}}
    <div wire:loading.flex wire:target="predictFishType" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="p-6 bg-white rounded-lg shadow-xl text-center">
            <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-gray-700 font-semibold">Processing Prediction...</p>
        </div>
    </div>

     {{-- Loading State specifically for Training (Changed spinner to blue) --}}
    <div wire:loading.flex wire:target="trainModel" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="p-6 bg-white rounded-lg shadow-xl text-center">
            <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-gray-700 font-semibold">Initiating Model Training...</p>
        </div>
    </div>
</div>
