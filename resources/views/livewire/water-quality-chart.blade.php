{{-- Outer container using flex column layout --}}
<div class="h-[28rem] flex flex-col">
    @if($lineChartModel)
        {{-- Static Header Section (Title and Manual Legend) --}}
        <div class="px-4 py-2 shrink-0"> {{-- shrink-0 prevents this from shrinking --}}
            <h3 class="text-lg font-semibold text-gray-700">Water Quality Data</h3>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                @foreach($legendData as $item)
                    {{-- Apply styling based on visibility state --}}
                    <span wire:click="toggleSeries('{{ $item['name'] }}')"
                          class="inline-flex items-center text-sm cursor-pointer hover:text-gray-900 {{ $item['visible'] ? 'text-gray-600' : 'text-gray-400 line-through' }}">
                        <span class="w-3 h-3 mr-1.5 rounded-full" style="background-color: {{ $item['color'] }}; {{ !$item['visible'] ? 'opacity: 0.5;' : '' }}"></span>
                        {{ $item['name'] }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Scrollable Chart Area --}}
        {{-- flex-grow makes this take remaining vertical space --}}
        <div class="w-full overflow-x-auto flex-grow">
            {{-- Inner wrapper to enforce min-width --}}
            <div class="min-w-[60rem] h-full"> {{-- Ensure this inner div also takes full height --}}
                {{-- Use a more stable key if possible, or accept re-render. Component ID might be stable enough. --}}
                <livewire:livewire-line-chart key="{{ $lineChartModel->reactiveKey() }}" :line-chart-model="$lineChartModel" />
                {{-- Alternative key: key($this->getId() . '-chart') --}}
            </div>
        </div>
    @else
        {{-- Keep placeholder centered if no chart (takes full height of parent) --}}
        {{-- Also show header/legend even if no data, allowing series toggle for future data --}}
        <div class="px-4 py-2 shrink-0">
            <h3 class="text-lg font-semibold text-gray-700">Water Quality Data</h3>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                 @foreach($legendData as $item)
                     <span wire:click="toggleSeries('{{ $item['name'] }}')"
                           class="inline-flex items-center text-sm cursor-pointer hover:text-gray-900 {{ $item['visible'] ? 'text-gray-600' : 'text-gray-400 line-through' }}">
                         <span class="w-3 h-3 mr-1.5 rounded-full" style="background-color: {{ $item['color'] }}; {{ !$item['visible'] ? 'opacity: 0.5;' : '' }}"></span>
                         {{ $item['name'] }}
                     </span>
                 @endforeach
            </div>
        </div>
        <div class="flex items-center justify-center w-full h-full flex-grow">
             <p class="text-lg text-center text-gray-500">No data available to display for the selected criteria.</p>
        </div>
    @endif

    {{-- Remove the JavaScript listener section --}}
    {{-- @script
    <script>
        // Listen for the custom event dispatched from Livewire
        document.addEventListener('livewire:initialized', () => {
             Livewire.on('toggle-chart-series', (event) => {
                // Use optional chaining ?. in case the chart or method doesn't exist yet
                // Assumes underlying library is ApexCharts
                ApexCharts.exec(event.chartId, 'toggleSeries', event.seriesName);
             });
        });
    </script>
    @endscript --}}

</div>
