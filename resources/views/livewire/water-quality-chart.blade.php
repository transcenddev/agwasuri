{{-- Outer container using flex column layout --}}
<div class="flex flex-col">
    @if(!empty($lineChartModelArray))
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

            {{-- Chart Area: render via the lightweight wrapper that consumes the serializable model array --}}
            <div class="w-full">
                <div class="relative">
                    @php
                        $__chartId = $lineChartModelArray['jsonConfig']['chart']['id'] ?? 'water-quality-0';
                        $__seriesNames = $lineChartModelArray['meta']['series_names'] ?? array_keys($chartData['series'] ?? []);
                        $__seriesKeys = $lineChartModelArray['meta']['series_keys'] ?? ["temperature", "dissolved_oxygen", "salinity", "ph_level"];
                        // Ensure a sensible, accessible default palette and stroke settings
                        $__defaultColors = [
                            '#E53935', // Temperature (warm red)
                            '#1E88E5', // Dissolved Oxygen (strong blue)
                            '#00ACC1', // Salinity (teal/cyan)
                            '#8E24AA', // pH (purple/violet)
                        ];

                        if (empty($lineChartModelArray['colors'])) {
                            $lineChartModelArray['colors'] = $__defaultColors;
                        }

                        // Make temperature more visually prominent by default
                        $__defaultStrokeWidths = [2.6, 1.6, 1.6, 1.6];
                        if (empty($lineChartModelArray['jsonConfig']['stroke']['width'])) {
                            $lineChartModelArray['jsonConfig']['stroke']['width'] = $__defaultStrokeWidths;
                        }

                        // Small markers to help identify series points without cluttering
                        if (empty($lineChartModelArray['jsonConfig']['markers'])) {
                            $lineChartModelArray['jsonConfig']['markers'] = ['size' => 3];
                        }
                    @endphp

                    <div id="water-quality-chart-root" wire:ignore class="w-full p-4 max-w-content-xl mx-auto"
                         data-chart-id="{{ $__chartId }}"
                         data-series-names='@json($__seriesNames)'
                         data-series-keys='@json($__seriesKeys)'
                         data-initial='@json($chartData)'>
                        @livewire('livewire-charts-line-wrapper', ['lineChartModel' => $lineChartModelArray], key(md5(json_encode($lineChartModelArray))))
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

        // Chart rendering is performed by the vendor view included via the wrapper component.
    </script>

    {{-- Lightweight append-only listener for realtime points. Will attempt to append points to ApexCharts when ready. --}}
    <script>
        (function(){
            const root = document.getElementById('water-quality-chart-root');
            if (!root) return;

            const chartId = root.getAttribute('data-chart-id');
            const seriesNames = JSON.parse(root.getAttribute('data-series-names') || '[]');
            const seriesKeys = JSON.parse(root.getAttribute('data-series-keys') || '[]');

            const queue = [];
            let ready = false;

            // Client-owned series state so we can trim series and keep chart size bounded.
            window.__waterQualitySeriesState = window.__waterQualitySeriesState || {};
            const MAX_POINTS = 200;
            const seriesState = window.__waterQualitySeriesState[chartId] = window.__waterQualitySeriesState[chartId] || (function(){
                let state = { categories: [], series: [] };
                try {
                    const raw = root.getAttribute('data-initial');
                    if (raw) {
                        const init = JSON.parse(raw);
                        state.categories = Array.isArray(init.labels) ? init.labels.slice() : [];
                        const map = init.series || {};
                        state.series = seriesNames.map((name) => ({ name, data: (map[name] || []).map((v, i) => ({ x: state.categories[i] ?? i, y: v })) }));
                    } else {
                        state.series = seriesNames.map((name) => ({ name, data: [] }));
                    }
                } catch (e) {
                    state.series = seriesNames.map((name) => ({ name, data: [] }));
                }
                return state;
            })();

            function trimAndUpdate(cid) {
                try {
                    // Ensure bounded length
                    while (seriesState.categories.length > MAX_POINTS) seriesState.categories.shift();
                    seriesState.series.forEach(s => { while (s.data.length > MAX_POINTS) s.data.shift(); });

                    // Update chart categories and full series to reflect trimmed state
                    try { window.ApexCharts.exec(cid, 'updateOptions', { xaxis: { categories: seriesState.categories } }, false, true); } catch (e) {}

                    const rebuilt = seriesState.series.map(s => ({ name: s.name, data: s.data.map(p => p.y) }));
                    try { window.ApexCharts.exec(cid, 'updateSeries', rebuilt); } catch (e) {}
                } catch (e) {
                    // ignore trim errors
                }
            }

            // Per-chart append buffer so we can replay recent appends if Livewire
            // re-renders the chart DOM. This helps appended points survive a
            // model refresh that may recreate the ApexCharts instance.
            window.__waterQualityAppendBuffer = window.__waterQualityAppendBuffer || {};
            window.__waterQualityAppendedSet = window.__waterQualityAppendedSet || {};
            // Persist mapping chartId -> actual DOM instance id so future appends
            // can target the visible ApexCharts instance immediately.
            window.__waterQualityKnownChartId = window.__waterQualityKnownChartId || {};
            // hydrate from localStorage when available so mapping survives reloads
            try {
                const stored = localStorage.getItem('waterQualityKnownChartId:' + chartId);
                if (stored) {
                    window.__waterQualityKnownChartId[chartId] = stored;
                }
            } catch (e) {
                // ignore localStorage errors (e.g., private mode)
            }

            // Attempt an immediate DOM discovery of the actual ApexCharts instance
            // with retries, so we can set the known mapping after the vendor
            // renderer creates the DOM. This avoids race conditions where the
            // server-side discovery ran too early and returned undefined.
            try {
                (function discoverChartDomIdWithRetries() {
                    if (window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[chartId]) return;

                    let attempts = 0;
                    const MAX_ATTEMPTS = 12; // ~6 seconds of retries
                    const INTERVAL_MS = 500;

                    function tryDiscover() {
                        if (window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[chartId]) return;

                        // Direct id checks
                        let found = document.getElementById(chartId) || document.getElementById('apexcharts' + chartId);

                        // Scan apexcharts canvases for a parent id that contains the chartId
                        if (!found) {
                            const canvases = Array.from(document.querySelectorAll('.apexcharts-canvas'));
                            for (const c of canvases) {
                                const parent = c.closest('[id]');
                                if (parent && parent.id && parent.id.indexOf(chartId) !== -1) {
                                    found = parent;
                                    break;
                                }
                            }
                        }

                        if (found && found.id) {
                            try {
                                window.__waterQualityKnownChartId[chartId] = found.id;
                                localStorage.setItem('waterQualityKnownChartId:' + chartId, found.id);
                            } catch (err) {
                                // ignore storage problems
                            }
                            console.info('WaterQualityChart: discovered chart DOM id', found.id);
                            return;
                        }

                        attempts++;
                        if (attempts < MAX_ATTEMPTS) {
                            setTimeout(tryDiscover, INTERVAL_MS);
                        } else {
                            console.debug('WaterQualityChart: discovery attempts exhausted for', chartId);
                        }
                    }

                    tryDiscover();
                })();
            } catch (e) {
                // ignore discovery errors
            }
            // Track consecutive append failures per chart so we can resync
            // when append attempts repeatedly fail.
            window.__waterQualityFailureCount = window.__waterQualityFailureCount || {};
            const appendBuffer = window.__waterQualityAppendBuffer[chartId] = window.__waterQualityAppendBuffer[chartId] || [];
            const appendedSet = window.__waterQualityAppendedSet[chartId] = window.__waterQualityAppendedSet[chartId] || new Set();
            const MAX_BUFFER = 64; // keep last N appended points to replay after re-render

            function tryFlush(){
                if (!window.ApexCharts || typeof window.ApexCharts.exec !== 'function') return;
                // consider chart ready if exec exists and we can call a no-op
                ready = true;

                while(queue.length){
                    const payload = queue.shift();
                    appendToChart(payload);
                }
            }

            function appendToChart(payload){
                if (!window.ApexCharts || typeof window.ApexCharts.exec !== 'function') {
                    queue.push(payload);
                    return;
                }

                try {
                    // DEBUG: log incoming payloads to help diagnose buffer behavior
                    try { console.debug('WaterQualityChart.appendToChart payload', payload); } catch (e) {}
                    // Build series payload expected by ApexCharts.appendData or exec appendData
                    // Build series payload. Use the same x format as the initial chart labels
                    // (short time string 'HH:mm') because the chart is rendered with category labels.
                    const dt = new Date(payload.recorded_at);
                    const pad = (n) => n.toString().padStart(2, '0');
                    // Use seconds resolution (HH:mm:ss) to make the x key unique
                    // for high-frequency updates and avoid accidental dedupe.
                    const timeLabel = pad(dt.getHours()) + ':' + pad(dt.getMinutes()) + ':' + pad(dt.getSeconds());

                    // Avoid duplicate appends for the same time label (client-side dedupe)
                    // However, if this payload is a replay (from the buffer after a
                    // Livewire re-render) we must bypass the dedupe so the point is
                    // re-applied to the newly-created ApexCharts instance.
                    try {
                        if (!payload.__replay && appendedSet.has(timeLabel)) {
                            return;
                        }
                    } catch (e) {
                        // ignore
                    }

                    const series = seriesKeys.map((key, idx) => {
                        const value = payload[key];
                        return {
                            name: seriesNames[idx] ?? key,
                            data: [{ x: timeLabel, y: value }]
                        };
                    });

                    // Try canonical exec appendData - many livewire-charts bundles support this
                    if (typeof window.ApexCharts.exec === 'function') {
                        try {
                            // Read existing category labels from the chart DOM (last resort)
                            let existingCategories = [];
                            try {
                                const labelNodes = Array.from(document.querySelectorAll('#' + chartId + ' .apexcharts-xaxis-texts-g text'));
                                existingCategories = labelNodes.map(n => n.textContent).filter(Boolean);
                            } catch (domErr) {
                                // ignore DOM errors
                            }

                            // If categories exist and timeLabel missing, attempt to insert it
                            // We prefer a previously-discovered DOM id (if any) so we can
                            // target the visible instance directly.
                            if (existingCategories.length && existingCategories.indexOf(timeLabel) === -1) {
                                const newCategories = existingCategories.concat([timeLabel]);
                                try {
                                    const updateCandidates = [];
                                    const knownCid = window.__waterQualityKnownChartId[chartId];
                                    if (knownCid) updateCandidates.push(knownCid);
                                    updateCandidates.push(chartId, 'apexcharts' + chartId);

                                    try {
                                        const canvases = Array.from(document.querySelectorAll('.apexcharts-canvas'));
                                        canvases.forEach(c => {
                                            const parent = c.closest('[id]');
                                            if (parent && parent.id && !updateCandidates.includes(parent.id)) updateCandidates.push(parent.id);
                                        });
                                    } catch (e) { /* ignore */ }

                                    const candidatesToTry = Array.from(new Set(updateCandidates));

                                    // Try updateOptions + append on each candidate. On success,
                                    // persist the mapping so subsequent appends go directly there.
                                    for (const cid of candidatesToTry) {
                                        try {
                                            window.ApexCharts.exec(cid, 'updateOptions', { xaxis: { categories: newCategories } }, false, true);
                                            window.ApexCharts.exec(cid, 'appendData', { series });

                                            // Persist mapping for future direct targeting
                                            window.__waterQualityKnownChartId[chartId] = cid;
                                            try { localStorage.setItem('waterQualityKnownChartId:' + chartId, cid); } catch (e) {}

                                            console.info('ApexCharts.exec appendData succeeded for', cid);

                                            try { appendedSet.add(timeLabel); } catch (err) {}
                                            try {
                                                if (!payload.__replay) {
                                                    try { console.debug('WaterQualityChart: buffering payload for', chartId, timeLabel); } catch (e) {}
                                                    appendBuffer.push(payload);
                                                    if (appendBuffer.length > MAX_BUFFER) appendBuffer.shift();
                                                    try { console.debug('WaterQualityChart: buffer length', chartId, appendBuffer.length); } catch (e) {}

                                                    // Update client-owned series state and trim if needed
                                                    try {
                                                        // push into state
                                                        seriesState.categories.push(timeLabel);
                                                        seriesState.series.forEach((s, idx) => {
                                                            const val = payload[seriesKeys[idx]];
                                                            s.data.push({ x: timeLabel, y: val });
                                                        });
                                                        trimAndUpdate(window.__waterQualityKnownChartId[chartId] || chartId);
                                                    } catch (e) { /* ignore state update errors */ }
                                                }
                                            } catch (err) {}

                                            return;
                                        } catch (err) {
                                            // candidate failed, try next
                                        }
                                    }
                                } catch (updErr) {
                                    console.warn('Failed to update chart categories (overall)', updErr);
                                }
                            }

                            // Build candidate ids to try: prefer known mapping, then exact id,
                            // then vendor-prefixed id and finally discovered apexcharts container ids
                            const candidates = new Set();
                            const knownCid = window.__waterQualityKnownChartId[chartId];
                            if (knownCid) candidates.add(knownCid);
                            candidates.add(chartId);
                            candidates.add('apexcharts' + chartId);

                            try {
                                // Scan existing apexcharts canvases and add their parent ids
                                const canvases = Array.from(document.querySelectorAll('.apexcharts-canvas'));
                                canvases.forEach(c => {
                                    const parent = c.closest('[id]');
                                    if (parent && parent.id) candidates.add(parent.id);
                                });
                            } catch (scanErr) {
                                // ignore
                            }

                            // Try each candidate until one succeeds
                                    for (const cid of candidates) {
                                        try {
                                            window.ApexCharts.exec(cid, 'appendData', { series });
                                            console.info('ApexCharts.exec appendData succeeded for', cid);

                                            // Persist discovered mapping for faster targeting next time
                                            try { window.__waterQualityKnownChartId[chartId] = cid; try { localStorage.setItem('waterQualityKnownChartId:' + chartId, cid); } catch (e) {} } catch (err) {}

                                            // Reset failure counter on success
                                            try { window.__waterQualityFailureCount[chartId] = 0; } catch (err) {}

                                            // Mark as appended (dedupe) and persist to replay buffer
                                            try { appendedSet.add(timeLabel); } catch (err) {}
                                            try {
                                                if (!payload.__replay) {
                                                    appendBuffer.push(payload);
                                                    if (appendBuffer.length > MAX_BUFFER) appendBuffer.shift();

                                                    // Update client-owned series state and trim if needed
                                                    try {
                                                        seriesState.categories.push(timeLabel);
                                                        seriesState.series.forEach((s, idx) => {
                                                            const val = payload[seriesKeys[idx]];
                                                            s.data.push({ x: timeLabel, y: val });
                                                        });
                                                        trimAndUpdate(cid);
                                                    } catch (e) { /* ignore state errors */ }
                                                }
                                            } catch (err) {}

                                            return;
                                        } catch (e) {
                                            // try next candidate
                                        }
                                    }

                                    // If we reach here no candidate succeeded — increment failure counter
                                    try {
                                        window.__waterQualityFailureCount[chartId] = (window.__waterQualityFailureCount[chartId] || 0) + 1;
                                    } catch (err) {}

                                    const FAIL_THRESHOLD = 3;
                                    try {
                                        if ((window.__waterQualityFailureCount[chartId] || 0) >= FAIL_THRESHOLD) {
                                            console.warn('WaterQualityChart: multiple append failures, attempting resync', chartId);

                                            // Try to find the Livewire component id within the root
                                            try {
                                                // Search for a Livewire root id attribute on or inside our chart root
                                                const lwEl = root.querySelector('[wire\\:id]') || root.closest('[wire\\:id]');
                                                const lwId = lwEl ? lwEl.getAttribute('wire:id') || lwEl.getAttribute('wire\:id') : null;
                                                if (lwId && window.Livewire && typeof window.Livewire.find === 'function') {
                                                    try {
                                                        const comp = window.Livewire.find(lwId);
                                                        if (comp && typeof comp.refresh === 'function') {
                                                            comp.refresh();
                                                            console.info('WaterQualityChart: requested Livewire refresh for', lwId);
                                                            window.__waterQualityFailureCount[chartId] = 0;
                                                            return;
                                                        }
                                                    } catch (err) {
                                                        // fallback to reload
                                                    }
                                                }
                                            } catch (err) {
                                                // ignore
                                            }

                                            // Fallback: reload the page to resync
                                            try { window.location.reload(); } catch (err) { /* ignore */ }
                                        }
                                    } catch (err) {
                                        // ignore any resync errors
                                    }

                            // fallback to calling exec per-series on the original chartId
                            series.forEach((s, i) => {
                                try { window.ApexCharts.exec(chartId, 'appendData', { series: [s] }); } catch (err) { /* noop */ }
                            });
                            return;
                        } catch (e) {
                            // if anything goes wrong here, fallback to per-series append below
                        }
                    }
                } catch (err) {
                    // queue and retry later
                    queue.push(payload);
                }
            }

            // Listen for Livewire-dispatched browser events with single point payloads
            window.addEventListener('water-quality:point', (e) => {
                const payload = e.detail || e;
                if (ready) {
                    appendToChart(payload);
                } else {
                    queue.push(payload);
                }
            });

            // Periodically attempt to flush queued events until ready
            const interval = setInterval(() => { tryFlush(); if (ready) clearInterval(interval); }, 500);

            // Try once after DOMContentLoaded in case ApexCharts loads after
            document.addEventListener('DOMContentLoaded', tryFlush);
            // Also attempt to flush after window load
            // If Livewire re-renders the chart area, replay buffered appends
            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                try {
                    window.Livewire.hook('message.processed', (message, component) => {
                        // After Livewire re-renders the component, the ApexCharts
                        // instance may have been recreated. Clear the per-chart
                        // appendedSet so replayed points are allowed to append again,
                        // then replay the buffer (marking entries as __replay to
                        // bypass dedupe checks).
                        try {
                            if (window.__waterQualityAppendedSet && window.__waterQualityAppendedSet[chartId]) {
                                window.__waterQualityAppendedSet[chartId] = new Set();
                            }
                        } catch (e) {
                            // ignore
                        }

                        const buf = (window.__waterQualityAppendBuffer && window.__waterQualityAppendBuffer[chartId]) || [];
                        if (!buf.length) return;
                        buf.forEach(p => {
                            try {
                                p.__replay = true;
                                appendToChart(p);
                                delete p.__replay;
                            } catch (err) {
                                // ignore per-entry errors
                            }
                        });
                    });
                } catch (e) {
                    // ignore
                }
            }

            window.addEventListener('load', tryFlush);
        })();
    </script>

            @if(config('app.debug'))
                <div id="water-chart-dev-test" class="fixed bottom-4 right-4 z-50">
                    <button onclick="window.__dispatchWaterQualityTestPoint()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded shadow">
                        Test Chart Append
                    </button>
                </div>

                <script>
                    window.__dispatchWaterQualityTestPoint = function() {
                        const now = new Date();
                        const payload = {
                            recorded_at: now.toISOString(),
                            temperature: +(20 + Math.random() * 10).toFixed(2),
                            dissolved_oxygen: +(4 + Math.random() * 4).toFixed(2),
                            salinity: +(5 + Math.random() * 20).toFixed(2),
                            ph_level: +(6.5 + Math.random() * 1.5).toFixed(2)
                        };
                        window.dispatchEvent(new CustomEvent('water-quality:point', { detail: payload }));
                        console.info('Dispatched test water-quality:point (event)', payload);

                        // Also try direct append via ApexCharts.exec so we can diagnose failures immediately
                        try {
                            const root = document.getElementById('water-quality-chart-root');
                            const chartId = root ? root.getAttribute('data-chart-id') : null;
                            const seriesNames = root ? JSON.parse(root.getAttribute('data-series-names') || '[]') : [];
                            const seriesKeys = root ? JSON.parse(root.getAttribute('data-series-keys') || '[]') : [];

                            // Match the category label format used by the chart (HH:mm)
                            const dt = new Date(payload.recorded_at);
                            const pad = (n) => n.toString().padStart(2, '0');
                            const timeLabel = pad(dt.getHours()) + ':' + pad(dt.getMinutes()) + ':' + pad(dt.getSeconds());

                            const series = seriesKeys.map((key, idx) => ({
                                name: seriesNames[idx] ?? key,
                                data: [{ x: timeLabel, y: payload[key] }]
                            }));

                            if (window.ApexCharts && typeof window.ApexCharts.exec === 'function') {
                                try {
                                    window.ApexCharts.exec(chartId, 'appendData', { series });
                                    console.info('ApexCharts.exec appendData called', { chartId, series });
                                } catch (err) {
                                    console.error('ApexCharts.exec appendData threw', err, { chartId, series });
                                }
                            } else {
                                console.warn('ApexCharts.exec not available yet.');
                            }
                        } catch (err) {
                            console.error('Failed to perform direct append attempt', err);
                        }
                    };
                </script>
            @endif

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
