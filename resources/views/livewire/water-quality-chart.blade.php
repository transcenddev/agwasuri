<div class="h-[28rem] flex justify-center items-center">
    @if($lineChartModel)
        <livewire:livewire-line-chart key="{{ $lineChartModel->reactiveKey() }}" :line-chart-model="$lineChartModel" />
    @else
        <p class="text-lg text-center text-gray-500">No data available to display.</p>
    @endif
</div>
