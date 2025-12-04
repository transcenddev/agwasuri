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

            {{-- Chart Area: render using the official livewire-charts component. --}}
            <div class="w-full">
                <div class="relative">
                    <div class="w-full p-4 max-w-content-xl mx-auto">
                        {{-- Render the chart container and attach a serializable model
                            produced server-side. We avoid passing the LineChartModel
                            object directly to Livewire to prevent synthesizer errors. --}}
                        <div id="livewire-charts-container-{{ md5(json_encode($lineChartModelArray)) }}" class="h-64" data-model='@json($lineChartModelArray)'></div>
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

            document.addEventListener('click', (e) => { if (!kebabMenu) return; if (kebabMenu.classList.contains('hidden')) return; const target = e.target; if (kebabMenu.contains(target) || (kebabBtn && kebabBtn.contains(target))) return; closeMenu(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
        })();

        // Minimal ApexCharts renderer + update handler. It reads the
        // serialized model (from LineChartModel->toArray()) and renders a
        // multi-series line chart. It also listens for the
        // `wq-chart-updated` browser event to re-render on updates.
        (function(){
            // helper to build Apex-compatible series from the package model
            function modelToSeries(model) {
                if (!model || !model.data) return [];

                // model.data: { seriesName: [ {seriesName,title,value,...}, ... ] }
                return Object.keys(model.data).map(function(name){
                    var points = (model.data[name] || []).map(function(p){ return p.value; });
                    return { name: name, data: points };
                });
            }

            function modelToCategories(model) {
                // Extract categories (titles) from the first series or xAxis
                if (model.xAxis && model.xAxis.categories && model.xAxis.categories.length) return model.xAxis.categories;
                var first = Object.values(model.data || {})[0] || [];
                return first.map(function(p){ return p.title; });
            }

            window._wq_charts = window._wq_charts || {};

            function renderContainer(container) {
                var key = container.id;
                var model = JSON.parse(container.getAttribute('data-model') || '{}');
                if (!model || !model.data) return;

                var series = modelToSeries(model);
                var categories = modelToCategories(model);

                if (window._wq_charts[key]) {
                    try { window._wq_charts[key].destroy(); } catch(e){}
                }

                var options = {
                    chart: { type: 'line', height: '100%', toolbar: { show: false } },
                    series: series,
                    xaxis: { categories: categories },
                    stroke: { curve: 'smooth' },
                    markers: { size: 3 },
                    tooltip: { shared: true, intersect: false }
                };

                if (typeof ApexCharts === 'undefined') {
                    console.warn('ApexCharts not found - include it in your build');
                    return;
                }

                try {
                    var chart = new ApexCharts(container, options);
                    chart.render();
                    window._wq_charts[key] = chart;
                } catch (e) {
                    console.error('Failed to render water quality chart', e);
                }
            }

            // Initial render for existing containers
            document.querySelectorAll('[id^="livewire-charts-container-"]').forEach(renderContainer);

            // Listen for Livewire-emitted events and update the chart.
            if (typeof Livewire !== 'undefined' && Livewire.on) {
                Livewire.on('wqChartUpdated', function(model){
                    try {
                        document.querySelectorAll('[id^="livewire-charts-container-"]').forEach(function(container){
                            container.setAttribute('data-model', JSON.stringify(model || {}));
                            renderContainer(container);
                        });
                    } catch (err) { console.warn('wqChartUpdated handler failed', err); }
                });
            } else {
                console.warn('Livewire not found - real-time chart updates via Livewire will not work.');
            }
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
