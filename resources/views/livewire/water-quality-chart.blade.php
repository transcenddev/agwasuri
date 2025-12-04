{{-- Outer container using flex column layout --}}
<div class="flex flex-col">
    @if($lineChartModel)
        {{-- Single root element for Livewire + compact header --}}
        <div class="w-full">
            <div class="px-4 py-2 flex items-center justify-between"> {{-- header --}}
                <h3 class="text-lg font-semibold text-gray-700">Water Quality Data</h3>

                <div class="flex items-center gap-2">
                        {{-- Reset removed (UI simplified) --}}

                    <div class="sm:hidden relative flex items-center gap-2">
                        <div class="relative">
                            <button id="waterChart-kebab-btn" aria-haspopup="true" aria-expanded="false" type="button" class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-cvsu/20">
                                <span class="sr-only">Open menu</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="text-slate-700 dark:text-slate-200">
                                    <path d="M12 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>

                            <div id="waterChart-kebab-menu" class="hidden absolute right-0 mt-2 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-lg z-50">
                                <ul class="py-1 text-sm text-slate-700 dark:text-slate-200">
                                    {{-- Reset menu item removed for simplified chart actions --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Area: render ApexCharts using the lineChartModel produced server-side --}}
            <div class="w-full">
                <div class="relative">
                    <div class="w-full p-4 max-w-content-xl mx-auto">
                        <div id="livewire-charts-container-{{ md5(json_encode($lineChartModel)) }}" class="h-64" data-model='@json($lineChartModel)'></div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Minimal header when there's no data --}}
        <div class="px-4 py-2 shrink-0 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-700">Water Quality Data</h3>
            <div class="flex items-center gap-2">
                {{-- Reset removed (UI simplified) --}}
            </div>
        </div>

        {{-- Even when there's no data, output the canvases so the JS initializer can find them.
             We attach an explicit (possibly empty) chart payload so the client has a stable shape. --}}
        @php
            $payload = $chartData ?? ['labels' => [], 'series' => [], 'colors' => [], 'visible' => []];
        @endphp

        <div class="w-full">
            <div class="relative">
                <div class="w-full p-4 max-w-content-xl mx-auto">
                    <canvas id="waterChart-main" data-chart='@json($payload)'></canvas>
                </div>

                {{-- Friendly overlay when there's no actual data to plot or when user hides all series --}}
                <div id="chart-empty-overlay" class="absolute inset-0 flex items-center justify-center pointer-events-none hidden">
                    <div class="bg-white/80 dark:bg-black/70 text-gray-700 dark:text-gray-200 rounded-md px-6 py-3 shadow">
                        No series selected or no data available. Click a legend to show data.
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Kebab menu small script: wire mobile menu to existing helpers --}}
    <script>
        (function(){
            // kebab menu interactions remain; keep minimal logic here
            const kebabBtn = document.getElementById('waterChart-kebab-btn');
            const kebabMenu = document.getElementById('waterChart-kebab-menu');

            function closeMenu() { if (!kebabMenu) return; kebabMenu.classList.add('hidden'); if (kebabBtn) kebabBtn.setAttribute('aria-expanded','false'); }
            function openMenu() { if (!kebabMenu) return; kebabMenu.classList.remove('hidden'); if (kebabBtn) kebabBtn.setAttribute('aria-expanded','true'); }

            if (kebabBtn) kebabBtn.addEventListener('click', (e) => { e.stopPropagation(); if (!kebabMenu) return; if (kebabMenu.classList.contains('hidden')) openMenu(); else closeMenu(); });

            // Reset helper and kebab reset listener removed — no visible reset controls exist.

            document.addEventListener('click', (e) => { if (!kebabMenu) return; if (kebabMenu.classList.contains('hidden')) return; const target = e.target; if (kebabMenu.contains(target) || (kebabBtn && kebabBtn.contains(target))) return; closeMenu(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
        })();

        // Render ApexCharts from the server-built lineChartModel for each container
        (function(){
            // helper to safely get/parse model from data-model attribute
            function parseModel(el) {
                try { return JSON.parse(el.getAttribute('data-model') || '{}'); } catch(e) { return null; }
            }

            // destroy existing charts cache
            window._wq_charts = window._wq_charts || {};

            document.querySelectorAll('[id^="livewire-charts-container-"]').forEach(function(container){
                const key = container.id;
                const model = parseModel(container);
                if (!model || !model.data) return;

                // build series for ApexCharts
                const series = Object.keys(model.data).map(function(name){
                    return { name: name, data: model.data[name].map(function(p){ return p.value; }) };
                });

                const categories = (model.xAxis && model.xAxis.categories && model.xAxis.categories.length) ? model.xAxis.categories : (model.data && Object.values(model.data)[0].map(function(p){ return p.title; }));

                // destroy previous chart if present
                if (window._wq_charts[key]) {
                    try { window._wq_charts[key].destroy(); } catch(e){}
                }

                const options = {
                    chart: { type: 'line', height: '100%', toolbar: { show: false } },
                    series: series,
                    xaxis: { categories: categories },
                    stroke: { curve: 'smooth' },
                    markers: { size: 3 },
                    tooltip: { shared: true, intersect: false }
                };

                try {
                    if (typeof ApexCharts === 'undefined') {
                        console.warn('ApexCharts not found - include it in your build');
                        return;
                    }
                    const chart = new ApexCharts(container, options);
                    chart.render();
                    window._wq_charts[key] = chart;
                } catch (e) {
                    console.error('Failed to render water quality chart', e);
                }
            });
        })();
    </script>

    <!-- Screen-reader live region for reset announcements -->
    <span id="chart-reset-live" class="sr-only" aria-live="polite" aria-atomic="true"></span>

    {{-- Mobile selector removed; mobile uses legend + kebab menu only. --}}

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
