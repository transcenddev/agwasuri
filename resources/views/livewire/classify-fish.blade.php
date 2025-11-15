<div class="p-4">
    @if($error)
    <div class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
        {{ $error }}
    </div>
    @endif

    <button wire:click="predictFishType" class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
        Predict Fish Type
    </button>

    @if(!empty($predictionResults))
    <div class="mt-6">
        <!-- Analysis Summary Card -->
        <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
            <h4 class="mb-3 text-xl font-semibold text-gray-800">Analysis Summary</h4>
            <p class="mb-2 text-gray-600">{{ $explanations['predictions'] }}</p>
            <p class="mb-2 font-semibold text-gray-600">{{ $explanations['top_prediction'] }}</p>
            <p class="font-medium text-gray-700">
                Confidence Level:
                <span class="font-semibold"> {{ $explanations['confidence_context'] }}</span>
            </p>
        </div>

        <!-- Detailed Predictions -->
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

        <!-- Model Performance Metrics -->
        @if(!empty($modelMetrics))
        <div class="mt-6">
            <h4 class="mb-4 text-xl font-semibold text-gray-800">Model Performance Metrics:</h4>
            <div class="overflow-hidden bg-white rounded-lg shadow-md">
                <div class="divide-y divide-gray-200">
                    <!-- Accuracy -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-700">Accuracy</span>
                            <span class="font-semibold text-blue-600">
                                {{ number_format($modelMetrics['accuracy'], 2) }}%
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ $explanations['metrics']['accuracy'] }}
                        </p>
                    </div>

                    <!-- Precision -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-700">Precision</span>
                            <span class="font-semibold text-blue-600">
                                {{ number_format($modelMetrics['precision'], 2) }}%
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ $explanations['metrics']['precision'] }}
                        </p>
                    </div>

                    <!-- Recall -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-700">Recall</span>
                            <span class="font-semibold text-blue-600">
                                {{ number_format($modelMetrics['recall'], 2) }}%
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ $explanations['metrics']['recall'] }}
                        </p>
                    </div>

                    <!-- F1 Score -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-700">F1 Score</span>
                            <span class="font-semibold text-blue-600">
                                {{ number_format($modelMetrics['f1_score'], 2) }}%
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ $explanations['metrics']['f1_score'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="p-4 bg-white rounded-lg shadow-xl">
            <div class="w-8 h-8 mx-auto border-b-2 border-blue-600 rounded-full animate-spin"></div>
            <p class="mt-2 text-gray-700">Processing...</p>
        </div>
    </div>
</div>
