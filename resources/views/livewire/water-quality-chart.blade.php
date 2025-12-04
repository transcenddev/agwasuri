{{-- Outer container using flex column layout --}}
<div class="h-[28rem] flex flex-col">
    @if($lineChartModel)
        {{-- Static Header Section (Title and Manual Legend) --}}
        <div class="px-4 py-2 shrink-0"> {{-- shrink-0 prevents this from shrinking --}}
            <h3 class="text-lg font-semibold text-gray-700">Water Quality Data</h3>
            <div class="flex items-center justify-between mt-1">
                <div class="flex flex-wrap gap-x-4 gap-y-1">
                    @foreach($legendData as $item)
                        {{-- Use client-side toggle and keep Livewire in sync via emitted event --}}
                        <button type="button"
                                data-series="{{ $item['name'] }}"
                                onclick="onLegendChipClick('{{ $item['name'] }}')"
                                aria-pressed="{{ $item['visible'] ? 'true' : 'false' }}"
                                class="inline-flex items-center text-sm cursor-pointer focus:outline-none {{ $item['visible'] ? 'text-gray-600' : 'text-gray-400 opacity-50' }}">
                            <span class="w-3 h-3 mr-1.5 rounded-full" style="background-color: {{ $item['color'] }}; {{ !$item['visible'] ? 'opacity: 0.5;' : '' }}"></span>
                            {{ $item['name'] }}
                        </button>
                    @endforeach
                </div>

                <div class="">
                    {{-- Desktop / tablet: show full button set (sm and up) --}}
                    <div class="hidden sm:flex items-center gap-2">
                        <button
                            type="button"
                            id="chart-show-all"
                            onclick="window._showAllSeries?.()"
                            title="Show all series"
                            class="text-sm px-3 py-2 rounded-lg inline-flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-cvsu/30 transition bg-cvsu text-white hover:bg-cvsu-700 shadow-sm"
                        >
                            Show all
                        </button>

                        <button
                            type="button"
                            id="chart-reset-zoom"
                            onclick="window._resetZoom?.()"
                            title="Reset zoom"
                            class="text-sm px-3 py-2 rounded-lg inline-flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-cvsu/20 transition bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700"
                        >
                            Reset Zoom
                        </button>

                        {{-- Mini-chart toggle (collapsible) --}}
                        <button
                            id="waterChart-mini-toggle"
                            type="button"
                            aria-pressed="false"
                            data-mini-toggle
                            title="Toggle timeline overview"
                            class="text-sm px-3 py-2 rounded-lg inline-flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-cvsu/20 transition bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-800 opacity-100 pointer-events-auto"
                        >
                            <svg id="waterChart-mini-toggle-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 transform transition-transform duration-200">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 111.1 1.02l-4.25 4.655a.75.75 0 01-1.1 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                            <span class="whitespace-nowrap">Timeline</span>
                        </button>
                    </div>

                    {{-- Mobile only: simple selector for series + kebab menu for actions --}}
                    <div class="sm:hidden relative flex items-center gap-2">
                        <label for="chart-mobile-show" class="sr-only">Show series</label>
                        <select id="chart-mobile-show" class="text-sm px-2 py-1 rounded border border-slate-200 bg-white" aria-label="Show series">
                            <option value="do-only">DO only</option>
                            <option value="all">All parameters</option>
                        </select>

                        <div class="relative">
                        <button id="waterChart-kebab-btn" aria-haspopup="true" aria-expanded="false" type="button" class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-cvsu/20">
                            <span class="sr-only">Open menu</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="text-slate-700 dark:text-slate-200">
                                <path d="M12 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>

                        <div id="waterChart-kebab-menu" class="hidden absolute right-0 mt-2 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-lg z-50">
                            <ul class="py-1 text-sm text-slate-700 dark:text-slate-200">
                                <li>
                                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700" id="kebab-show-all">Show all</button>
                                </li>
                                <li>
                                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700" id="kebab-reset-zoom">Reset zoom</button>
                                </li>
                                <li>
                                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700" id="kebab-toggle-timeline">Timeline</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Scrollable Chart Area --}}
        {{-- flex-grow makes this take remaining vertical space --}}
        <div class="w-full flex-grow">
            <div class="relative h-full">
                <!-- Main chart (interactive) -->
                <div class="w-full h-3/4 p-4">
                    <canvas id="waterChart-main" data-chart='@json($chartData)'></canvas>
                </div>

                <!-- Overview / mini-map below (compact scrubber) -->
                <div id="waterChart-mini-wrap" class="w-full h-12 md:h-16 p-2 hidden sm:block transition-all duration-200">
                    <canvas id="waterChart-mini" data-chart='@json($chartData)'></canvas>
                </div>
            </div>
        </div>
    @else
        {{-- Header/legend should still display even if there's no data --}}
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

        {{-- Even when there's no data, output the canvases so the JS initializer can find them.
             We attach an explicit (possibly empty) chart payload so the client has a stable shape. --}}
        @php
            $payload = $chartData ?? ['labels' => [], 'series' => [], 'colors' => [], 'visible' => []];
        @endphp

        <div class="w-full flex-grow">
            <div class="relative h-full">
                <div class="w-full h-3/4 p-4">
                    <canvas id="waterChart-main" data-chart='@json($payload)'></canvas>
                </div>

                <div id="waterChart-mini-wrap" class="w-full h-12 md:h-16 p-4 hidden sm:block transition-all duration-200">
                    <canvas id="waterChart-mini" data-chart='@json($payload)'></canvas>
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
            const kebabBtn = document.getElementById('waterChart-kebab-btn');
            const kebabMenu = document.getElementById('waterChart-kebab-menu');
            const showAll = document.getElementById('kebab-show-all');
            const resetZoom = document.getElementById('kebab-reset-zoom');
            const toggleTimeline = document.getElementById('kebab-toggle-timeline');

            function closeMenu() {
                if (!kebabMenu) return;
                kebabMenu.classList.add('hidden');
                if (kebabBtn) kebabBtn.setAttribute('aria-expanded','false');
            }

            function openMenu() {
                if (!kebabMenu) return;
                kebabMenu.classList.remove('hidden');
                if (kebabBtn) kebabBtn.setAttribute('aria-expanded','true');
            }

            if (kebabBtn) {
                kebabBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (!kebabMenu) return;
                    if (kebabMenu.classList.contains('hidden')) openMenu(); else closeMenu();
                });
            }

            // Click handlers for menu items
            if (showAll) showAll.addEventListener('click', (e) => { e.stopPropagation(); closeMenu(); if (window._showAllSeries) window._showAllSeries(); });
            if (resetZoom) resetZoom.addEventListener('click', (e) => { e.stopPropagation(); closeMenu(); if (window._resetZoom) window._resetZoom(); });
            if (toggleTimeline) toggleTimeline.addEventListener('click', (e) => { e.stopPropagation(); closeMenu(); const btn = document.getElementById('waterChart-mini-toggle'); if (btn) btn.click(); });

            // Close the menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!kebabMenu) return;
                if (kebabMenu.classList.contains('hidden')) return;
                const target = e.target;
                if (kebabMenu.contains(target) || (kebabBtn && kebabBtn.contains(target))) return;
                closeMenu();
            });

            // Close on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeMenu();
            });
        })();
    </script>

    <script>
        // Wire mobile select to Livewire for series visibility and default to DO-only on small screens
        (function(){
            const mobileSelect = document.getElementById('chart-mobile-show');
            function setMode(mode){
                if (window.Livewire && typeof Livewire.emit === 'function') {
                    Livewire.emit('set-series-visibility', mode);
                }
            }

            if (mobileSelect) {
                mobileSelect.addEventListener('change', (e) => {
                    setMode(e.target.value);
                });
            }

            // When Livewire is ready, default mobile to DO only if viewport is small
            document.addEventListener('livewire:load', () => {
                try {
                    const isSmall = window.innerWidth < 640; // tailwind 'sm' breakpoint
                    if (isSmall) {
                        const defaultMode = mobileSelect ? mobileSelect.value || 'do-only' : 'do-only';
                        setMode(defaultMode);
                        if (mobileSelect) mobileSelect.value = defaultMode;
                    }
                } catch (e) {
                    // ignore
                }
            });
        })();
    </script>

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
