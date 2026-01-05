{{-- Outer container using flex column layout --}}
<div class="flex flex-col">
    @if(!empty($lineChartModelArray))
        {{-- Single root element for Livewire + compact header --}}
        <div class="w-full">
            {{-- Legend intentionally removed for simplified UI (series toggling handled elsewhere) --}}

            {{-- Chart Area: render via the lightweight wrapper that consumes the serializable model array --}}
            <div class="w-full" wire:key="chart-container-{{ $chartId ?? ($userId ?? (request()->get('user') ?? session('selected_user', auth()->id()))) }}">
                <div class="relative">
                    @php
                        $__chartId = $chartId ?? ($lineChartModelArray['jsonConfig']['chart']['id'] ?? 'water-quality-0');
                        $__debugApiKey = config('app.debug') ? optional(\App\Models\User::find(request()->get('user') ?? session('selected_user', auth()->id())))->api_key : null;
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

                        // CRITICAL FIX: Ensure smooth curves and no markers (wavy lines, no circles)
                        if (empty($lineChartModelArray['jsonConfig']['stroke'])) {
                            $lineChartModelArray['jsonConfig']['stroke'] = [
                                'curve' => 'smooth',
                                'width' => [2.6, 1.6, 1.6, 1.6]
                            ];
                        } elseif (empty($lineChartModelArray['jsonConfig']['stroke']['curve'])) {
                            $lineChartModelArray['jsonConfig']['stroke']['curve'] = 'smooth';
                        }

                        // CRITICAL FIX: Disable markers completely (no circle points)
                        if (empty($lineChartModelArray['jsonConfig']['markers'])) {
                            $lineChartModelArray['jsonConfig']['markers'] = [
                                'size' => 0,
                                'strokeWidth' => 0,
                                'hover' => ['size' => 0]
                            ];
                        }
                    @endphp

                    @php
                        $__resolvedUserId = $userId ?? (request()->get('user') ?? session('selected_user', auth()->id()));
                    @endphp
                    <div id="water-quality-chart-root-{{ $__chartId }}" class="w-full px-4 pb-2 max-w-content-xl mx-auto"
                        wire:key="chart-root-{{ $__chartId }}"
                        style="position: relative; display: block; height: 480px;"
                        data-chart-id="{{ $__chartId }}"
                        data-api-key='@json($__debugApiKey)'
                        data-series-names='@json($__seriesNames)'
                        data-series-keys='@json($__seriesKeys)'
                        data-initial='@json($chartData)'
                        data-chart-config='@json($lineChartModelArray)'
                        data-user-id="{{ $__resolvedUserId }}">
                        {{-- Chart will be created by inline JS below, not by vendor wrapper --}}
                        <div id="{{ $__chartId }}" class="apexcharts-canvas-wrapper" style="min-height:480px;height:480px;width:100%;position:relative;"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Kebab/menu removed: header actions intentionally omitted for cleaner UI --}}

    {{-- Lightweight append-only listener for realtime points. Will attempt to append points to ApexCharts when ready. --}}
    <script>
        (function(){
            const rootId = 'water-quality-chart-root-{{ $__chartId }}';
            const root = document.getElementById(rootId) || document.querySelector(`[data-chart-id="{{ $__chartId }}"]`);
            if (!root) {
                console.error('❌ Chart root element not found!');
                return;
            }

            const chartId = root.getAttribute('data-chart-id');
            const seriesNames = JSON.parse(root.getAttribute('data-series-names') || '[]');
            const seriesKeys = JSON.parse(root.getAttribute('data-series-keys') || '[]');
            const chartConfig = JSON.parse(root.getAttribute('data-chart-config') || '{}');
            
            // CRITICAL DEBUG: Log what we received from Livewire
            console.log('📊 Chart initialization data:', {
                chartId,
                hasConfig: !!chartConfig,
                hasJsonConfig: !!chartConfig.jsonConfig,
                seriesCount: chartConfig?.jsonConfig?.series?.length,
                firstSeriesDataLength: chartConfig?.jsonConfig?.series?.[0]?.data?.length,
                allSeriesDataLengths: chartConfig?.jsonConfig?.series?.map(s => s.data?.length)
            });

            // CRITICAL FIX: Create the chart manually with explicit timing control
            let chartInstance = null;

            function createChart() {
                try {
                    // Check if ApexCharts library is loaded
                    if (typeof ApexCharts === 'undefined' || !window.ApexCharts) {
                        console.error('ApexCharts library not loaded! Chart cannot be initialized.');
                        return;
                    }

                    // Use global registry to prevent double initialization
                    window.__waterQualityChartRegistry = window.__waterQualityChartRegistry || {};
                    
                    if (window.__waterQualityChartRegistry[chartId]) {
                        console.debug('Chart already exists, skipping init:', chartId);
                        chartInstance = window.__waterQualityChartRegistry[chartId];
                        return chartInstance;
                    }
                    
                    const container = document.getElementById(chartId);
                    if (!container) {
                        console.error('Chart container not found:', chartId);
                        return;
                    }

                    console.debug('Creating chart with ID:', chartId);
                    console.debug('Container dimensions:', container.clientWidth, 'x', container.clientHeight);

                    // CRITICAL: Ensure we have valid config and series structure
                    if (!chartConfig || !chartConfig.jsonConfig) {
                        console.error('Chart config missing', chartConfig);
                        return;
                    }
                    
                    console.debug('Chart config:', chartConfig.jsonConfig);

                    // Destroy existing instance first
                    if (chartInstance && typeof chartInstance.destroy === 'function') {
                        chartInstance.destroy();
                        chartInstance = null;
                    }

                    // CRITICAL: Deep clone and validate options to prevent undefined crashes
                    const options = JSON.parse(JSON.stringify(chartConfig.jsonConfig || {}));
                    if (!options.chart) options.chart = {};
                    options.chart.height = 480;
                    options.chart.width = '100%';
                    options.chart.maintainAspectRatio = false;
                    options.chart.id = chartId; // enforce stable id for instance lookup
                    
                    // CRITICAL: Ensure series is ALWAYS an array (prevents .length undefined error)
                    if (!Array.isArray(options.series)) {
                        options.series = [];
                    }
                    // Ensure each series has valid data array
                    options.series = options.series.map(s => ({
                        name: s?.name || 'Unknown',
                        data: Array.isArray(s?.data) ? s.data : []
                    }));
                    
                    // If no series at all, create empty placeholders for 4 parameters
                    if (options.series.length === 0) {
                        options.series = [
                            { name: 'Temperature', data: [] },
                            { name: 'Salinity', data: [] },
                            { name: 'Dissolved Oxygen', data: [] },
                            { name: 'pH', data: [] }
                        ];
                    }

                    console.debug('About to create ApexCharts instance with options:', {
                        chartId: chartId,
                        seriesCount: options.series.length,
                        height: options.chart.height,
                        hasData: options.series[0]?.data?.length > 0
                    });

                    // CRITICAL FIX: Force render even if all series are empty (shows axes/grid)
                    options.noData = {
                        text: 'No water quality data available yet',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: { fontSize: '14px', color: '#999' }
                    };

                    // Create the ApexCharts instance with validated options
                    chartInstance = new ApexCharts(container, options);
                    
                    // Force immediate render to prevent blank chart
                    chartInstance.render().then(() => {
                        console.log('✅ Chart rendered successfully:', chartId, 'with', options.series[0]?.data?.length || 0, 'data points');
                    }).catch(err => {
                        console.error('❌ Chart render failed:', err);
                    });

                    // Store in global registries
                    window.__waterQualityChartRegistry[chartId] = chartInstance;
                    window.__waterQualityKnownChartId = window.__waterQualityKnownChartId || {};
                    window.__waterQualityKnownChartId[chartId] = options.chart.id || chartId;
                    
                    if (!window.__waterQualityChartInstances) {
                        window.__waterQualityChartInstances = {};
                    }
                    window.__waterQualityChartInstances[chartId] = chartInstance;
                    window.__agwasuriCharts = window.__agwasuriCharts || {};
                    window.__agwasuriCharts[chartId] = chartInstance;
                    
                    console.debug('Chart created:', chartId);

                } catch (e) {
                    console.error('WaterQualityChart: chart creation failed', e);
                }
            }

            // CRITICAL: Wait for DOM to be ready AND Livewire to finish updating
            function initChartWithTiming() {
                // Use requestAnimationFrame to ensure layout is complete
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        console.debug('initChartWithTiming: About to create chart');
                        createChart();
                    }, 150); // Increased delay to ensure Livewire hydration completes
                });
            }

            // Initial creation
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initChartWithTiming);
            } else {
                initChartWithTiming();
            }

            // CRITICAL FIX: Destroy chart instance on Livewire update to prevent stale instances
            window.addEventListener('water-quality:destroy-chart', () => {
                try {
                    // Clean up global registry
                    if (window.__waterQualityChartRegistry && window.__waterQualityChartRegistry[chartId]) {
                        delete window.__waterQualityChartRegistry[chartId];
                    }
                    
                    // Find and destroy ApexCharts instance
                    const targetCid = (window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[chartId]) || chartId || ('apexcharts' + chartId);
                    
                    if (window.ApexCharts && typeof window.ApexCharts.getChartByID === 'function') {
                        const inst = window.ApexCharts.getChartByID(targetCid);
                        if (inst && typeof inst.destroy === 'function') {
                            inst.destroy();
                            console.debug('Chart destroyed:', chartId);
                        }
                    }
                    
                    if (chartInstance && typeof chartInstance.destroy === 'function') {
                        chartInstance.destroy();
                        chartInstance = null;
                    }
                    
                    // Clear cached chart ID mapping
                    if (window.__waterQualityKnownChartId) {
                        delete window.__waterQualityKnownChartId[chartId];
                    }
                    try {
                        localStorage.removeItem('waterQualityKnownChartId:' + chartId);
                    } catch (e) {}
                    
                    // Clear series state to force fresh initialization
                    if (window.__waterQualitySeriesState) {
                        delete window.__waterQualitySeriesState[chartId];
                    }
                    
                    // DON'T recreate here - let Livewire hook handle it
                    
                } catch (e) {
                    console.error('WaterQualityChart: destroy error', e);
                }
            });

            // CRITICAL FIX: Listen for Livewire component updates and force chart re-render
            // This handles user switches, date filters, and any Livewire state changes
            if (window.Livewire && typeof Livewire.hook === 'function') {
                // Hook into Livewire's commit phase to recreate chart after data updates
                Livewire.hook('commit', ({ component, commit, respond }) => {
                    // Only act on this specific component
                    if (!component.el.querySelector('#water-quality-chart-root')) return;
                    
                    console.debug('Livewire commit detected, will recreate chart after render');
                    
                    // Schedule chart recreation after Livewire finishes DOM updates
                    setTimeout(() => {
                        const currentRoot = document.getElementById('water-quality-chart-root');
                        if (currentRoot && currentRoot.getAttribute('data-chart-id') === chartId) {
                            console.debug('Re-parsing chart config after Livewire update');
                            // Re-read config from updated DOM attributes
                            const freshConfig = JSON.parse(currentRoot.getAttribute('data-chart-config') || '{}');
                            if (freshConfig && freshConfig.jsonConfig) {
                                // Destroy old instance
                                if (chartInstance && typeof chartInstance.destroy === 'function') {
                                    chartInstance.destroy();
                                    chartInstance = null;
                                }
                                delete window.__waterQualityChartRegistry[chartId];
                                // Recreate with fresh data
                                createChart();
                            }
                        }
                    }, 200);
                });
            }
            
            // Listen for Livewire re-render and recreate chart after DOM updates
            if (window.Livewire && typeof Livewire.hook === 'function') {
                Livewire.hook('message.processed', (message, component) => {
                    setTimeout(() => {
                        const container = document.getElementById(chartId);
                        if (container && !window.__waterQualityChartRegistry[chartId]) {
                            console.debug('Recreating chart after Livewire update');
                            createChart();
                        }
                    }, 150);
                });
                
                Livewire.on('water-quality:destroy-chart', () => {
                    window.dispatchEvent(new CustomEvent('water-quality:destroy-chart'));
                });
            }

            // Debug helper: dump ApexCharts instances and candidate ids
            try {
                window.__waterQualityDumpInstances = window.__waterQualityDumpInstances || function(){
                    try {
                        // console.debug('waterChart: dumping ApexCharts instances...');
                        const candidateIds = [];
                        try { candidateIds.push(chartId); } catch(e){}
                        try { candidateIds.push('apexcharts' + chartId); } catch(e){}
                        try { if (window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[chartId]) candidateIds.push(window.__waterQualityKnownChartId[chartId]); } catch(e){}
                        try {
                            const canvases = Array.from(document.querySelectorAll('.apexcharts-canvas'));
                            canvases.forEach(c => { const p = c.closest('[id]'); if (p && p.id) candidateIds.push(p.id); });
                        } catch(e){}
                        const uniq = Array.from(new Set(candidateIds.filter(Boolean)));
                        if (!window.ApexCharts || typeof window.ApexCharts.getChartByID !== 'function') {
                            // console.debug('waterChart: ApexCharts.getChartByID not available', !!window.ApexCharts);
                        }
                        uniq.forEach(id => {
                            try {
                                const inst = (window.ApexCharts && typeof window.ApexCharts.getChartByID === 'function') ? window.ApexCharts.getChartByID(id) : null;
                                // console.debug('waterChart: candidate', id, 'hasInstance', !!inst, inst && inst.w && inst.w.config && inst.w.config.chart && inst.w.config.chart.id ? inst.w.config.chart.id : null);
                            } catch (e) {
                                // console.debug('waterChart: getChartByID failed for', id, e);
                            }
                        });
                    } catch(e){}
                };
            } catch(e){}

            // Forward Livewire-emitted events to the existing window event path.
            // Bind reliably even if script load order means Livewire isn't available
            // at the time this inline script runs.
            (function(){
                function bindForwarding(){
                    try {
                        if (window.Livewire && typeof Livewire.on === 'function'){
                            // Avoid double-binding
                            if (window.__waterQualityLivewireForwardBound) return true;
                            window.__waterQualityLivewireForwardBound = true;
                            Livewire.on('water-quality:point', (data) => {
                                try {
                                    window.dispatchEvent(new CustomEvent('water-quality:point', { detail: data }));
                                } catch (err) { /* ignore dispatch errors */ }
                            });
                            return true;
                        }
                    } catch (e) {}
                    return false;
                }

                // Try to bind immediately; if Livewire isn't present, listen for the
                // common `livewire:load` document event and as a last resort poll.
                try {
                    if (!bindForwarding()){
                        document.addEventListener('livewire:load', () => { try { bindForwarding(); } catch(e){} });
                        const _lwInterval = setInterval(() => { if (bindForwarding()) clearInterval(_lwInterval); }, 300);
                    }
                } catch (e) {}
            })();

            // Polling fallback: periodically fetch latest rows and dispatch events
            try {
                const userId = root.getAttribute('data-user-id');
                const POLL_INTERVAL_MS = 2000;

                // Helper to parse rows from server response (supports both `rows` or `labels`+`series` shapes)
                function rowsFromJson(json) {
                    if (!json) return [];
                    if (Array.isArray(json.rows) && json.rows.length) {
                        return json.rows;
                    }
                    const labels = json.labels || [];
                    const seriesObj = json.series || {};
                    const keys = JSON.parse(root.getAttribute('data-series-keys') || '[]');
                    const rows = [];
                    for (let i = 0; i < labels.length; i++) {
                        const r = { recorded_at: labels[i] };
                        for (let k = 0; k < keys.length; k++) {
                            const key = keys[k];
                            const arr = seriesObj[key] || [];
                            r[key] = (typeof arr[i] !== 'undefined') ? arr[i] : null;
                        }
                        rows.push(r);
                    }
                    return rows;
                }

                // Polling removed - relying on Reverb real-time events only
            } catch (e) {
                // ignore setup errors
            }

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

                            // Detect whether series entries are objects with {x,y}
                            const map = init.series || {};
                            const sampleArr = Object.values(map)[0] || [];
                            const sampleItem = Array.isArray(sampleArr) && sampleArr.length ? sampleArr[0] : null;
                            const isPointObjects = sampleItem && typeof sampleItem === 'object' && sampleItem.hasOwnProperty('x');

                            if (isPointObjects) {
                                // series are arrays of {x: ISO, y: value}
                                state.series = seriesNames.map((name) => {
                                    const arr = map[name] || [];
                                    return { name, data: arr.map(p => {
                                        const parsed = Date.parse(p.x);
                                        return { x: Number.isFinite(parsed) ? parsed : p.x, y: p.y };
                                    }) };
                                });
                                // categories are the x values of the first series (if present)
                                state.categories = (state.series[0] && state.series[0].data.map(d => d.x)) || [];
                            } else {
                                // legacy numeric series (category-based)
                                state.categories = Array.isArray(init.labels) ? init.labels.slice() : [];
                                state.series = seriesNames.map((name) => ({ name, data: (map[name] || []).map((v, i) => ({ x: state.categories[i] ?? i, y: v })) }));
                            }
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
                    if (window.ApexCharts && typeof window.ApexCharts.getChartByID === 'function') {
                        const inst = window.ApexCharts.getChartByID(cid);
                        if (inst && inst.w && inst.w.config && inst.w.config.series) {
                            const series = inst.w.config.series;
                            // Guard against undefined
                            if (!Array.isArray(series) || series.length === 0) {
                                return;
                            }
                            
                            const rebuilt = series.map((s, idx) => {
                                const state = seriesState.series[idx];
                                if (!state || !Array.isArray(state.data)) return s;
                                
                                if (state.data.length > MAX_POINTS) {
                                    const trimmed = state.data.slice(-MAX_POINTS);
                                    state.data = trimmed;
                                }
                                return { name: s.name, data: state.data };
                            });
                            
                            inst.updateSeries(rebuilt, false);
                        }
                    }
                } catch (e) {
                    console.debug('trimAndUpdate error:', e);
                }
            }

            // Trim-only helper: updates internal state lengths and x-axis
            // categories but does NOT call updateSeries. Use this during
            // high-frequency appends; a coalesced flush will call updateSeries.
            function trimStateOnly(cid) {
                try {
                    while (seriesState.categories.length > MAX_POINTS) seriesState.categories.shift();
                    seriesState.series.forEach(s => { while (s.data.length > MAX_POINTS) s.data.shift(); });
                    try { safeUpdateOptions(cid, { xaxis: { categories: seriesState.categories } }); } catch (e) {}
                } catch (e) {}
            }

            // Guarded updateOptions helper: applies given options only once per
            // chart id unless `force` is true. This prevents frequent reconfiguration
            // that can change stroke/marker settings and cause visual glitches.
            window.__waterQualityOptionsApplied = window.__waterQualityOptionsApplied || {};
            function safeUpdateOptions(cid, opts, force = false) {
                try {
                    if (!window.ApexCharts || typeof window.ApexCharts.exec !== 'function') return;
                    if (!cid) return;
                    window.__waterQualityOptionsApplied[cid] = window.__waterQualityOptionsApplied[cid] || {};
                    const normalizedOpts = opts || {};

                    // CRITICAL FIX: Ensure immutable visual defaults INCLUDING HEIGHT are always present
                    const immutableDefaults = {
                        chart: { height: 540, width: '100%', maintainAspectRatio: false },  // Prevent height collapse
                        stroke: { show: true, curve: 'smooth', width: 2, lineCap: 'round' },
                        markers: { size: 0 },
                        xaxis: { type: 'datetime' },
                        dataLabels: { enabled: false },
                        // Ensure vendor annotations/labels are cleared so only legend/tooltips remain
                        annotations: { yaxis: [], points: [], xaxis: [] }
                    };

                    // Shallow merge - caller opts override defaults where specified
                    const finalOpts = Object.assign({}, immutableDefaults, normalizedOpts);

                    const sig = JSON.stringify(finalOpts || {});
                    if (!force && window.__waterQualityOptionsApplied[cid][sig]) return;
                    window.ApexCharts.exec(cid, 'updateOptions', finalOpts, false, true);
                    window.__waterQualityOptionsApplied[cid][sig] = true;
                } catch (e) {
                    try { /* console.debug('waterChart: safeUpdateOptions failed', e); */ } catch (_) {}
                }
            }

            // After an append attempt, schedule a short check and force a full
            // updateSeries if the appended label/point is not visible. This
            // handles races where appendData succeeds internally but the DOM
            // rendering hasn't applied the new point yet.
            function scheduleFallbackCheck(cid, payload, timeKey, timeLabel, isDatetimeMode) {
                try {
                    setTimeout(() => {
                        try {
                            // Inspect x-axis label nodes for the expected label
                            let found = false;
                            try {
                                const nodes = Array.from(document.querySelectorAll('#' + cid + ' .apexcharts-xaxis-texts-g text'));
                                for (const n of nodes) {
                                    const t = (n.textContent || '').trim();
                                    if (!t) continue;
                                    if (isDatetimeMode) {
                                        // timeLabel contains HH:mm:ss — check for that fragment
                                        if (t.indexOf(timeLabel) !== -1 || t.indexOf(timeLabel.slice(0,5)) !== -1) { found = true; break; }
                                    } else {
                                        if (t.indexOf(timeLabel) !== -1) { found = true; break; }
                                    }
                                }
                            } catch (e) {
                                // ignore DOM query errors
                            }

                            if (!found) {
                                try {
                                    const state = window.__waterQualitySeriesState && window.__waterQualitySeriesState[chartId];
                                    if (!state) return;
                                    const rebuilt = isDatetimeMode
                                        ? state.series.map(s => ({ name: s.name, data: s.data.map(p => ({ x: p.x, y: p.y })) }))
                                        : state.series.map(s => ({ name: s.name, data: s.data.map(p => p.y) }));
                                    window.ApexCharts.exec(cid, 'updateSeries', rebuilt);
                                    // console.info('WaterQualityChart: fallback forced updateSeries for', cid, timeKey);
                                } catch (e) {
                                    console.error('WaterQualityChart: fallback updateSeries failed', e);
                                }
                            }
                        } catch (e) {
                            // ignore inner errors
                        }
                    }, 150);
                } catch (e) {
                    // ignore scheduling errors
                }
            }

            // Per-chart append buffer so we can replay recent appends if Livewire
            // re-renders the chart DOM. This helps appended points survive a
            // model refresh that may recreate the ApexCharts instance.
            window.__waterQualityAppendBuffer = window.__waterQualityAppendBuffer || {};
            // Use a Map for appended keys so we can store insertion timestamps
            window.__waterQualityAppendedMap = window.__waterQualityAppendedMap || {};
            // Coalesce buffers to reduce frequent updateSeries calls
            window.__waterQualityCoalesce = window.__waterQualityCoalesce || {};
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
                            // console.info('WaterQualityChart: discovered chart DOM id', found.id);
                            return;
                        }

                        attempts++;
                        if (attempts < MAX_ATTEMPTS) {
                            setTimeout(tryDiscover, INTERVAL_MS);
                        } else {
                            // console.debug('WaterQualityChart: discovery attempts exhausted for', chartId);
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
            const appendedMap = window.__waterQualityAppendedMap[chartId] = window.__waterQualityAppendedMap[chartId] || new Map();
            // track last forced update timestamp per chart to rate-limit forced redraws
            window.__waterQualityLastForcedAt = window.__waterQualityLastForcedAt || {};
            const MAX_BUFFER = 64; // keep last N appended points to replay after re-render

            // Initialize coalesce container for this chart
            const coalesce = window.__waterQualityCoalesce[chartId] = window.__waterQualityCoalesce[chartId] || { buf: [], timer: null };

            function scheduleCoalesceFlush() {
                try {
                    clearTimeout(coalesce.timer);
                    coalesce.timer = setTimeout(() => {
                        try {
                            const bufLen = (coalesce.buf || []).length;
                            // console.debug('WaterQualityChart: coalesce flush running', { chartId, bufLen });
                            if (!bufLen) {
                                // nothing to do
                                coalesce.timer = null;
                                return;
                            }

                            const cid = window.__waterQualityKnownChartId[chartId] || chartId || ('apexcharts' + chartId);
                            const state = window.__waterQualitySeriesState && window.__waterQualitySeriesState[chartId];
                            if (!state) {
                                coalesce.timer = null;
                                return;
                            }
                            const isDatetime = !!(state.series && state.series[0] && state.series[0].data && state.series[0].data[0] && (typeof state.series[0].data[0].x === 'number' || (typeof state.series[0].data[0].x === 'string' && state.series[0].data[0].x.indexOf('T') !== -1)));
                            const rebuilt = isDatetime
                                ? state.series.map(s => ({ name: s.name, data: s.data.map(p => ({ x: p.x, y: p.y })) }))
                                : state.series.map(s => ({ name: s.name, data: s.data.map(p => p.y) }));
                            try {
                                // Build append payloads from the coalesce buffer (chronological)
                                const appendSeries = state.series.map((s, idx) => {
                                    const name = s.name;
                                    const arr = [];
                                    for (const p of (coalesce.buf || [])) {
                                        if (!p) continue;
                                        try {
                                            const dt = new Date(p.recorded_at);
                                            const isDt = !!(state.series && state.series[0] && state.series[0].data && state.series[0].data[0] && (typeof state.series[0].data[0].x === 'number' || (typeof state.series[0].data[0].x === 'string' && state.series[0].data[0].x.indexOf('T') !== -1)));
                                            if (isDt) {
                                                arr.push({ x: (typeof p.recorded_at === 'string' ? p.recorded_at : new Date(p.recorded_at).toISOString()), y: p[seriesKeys[idx]] });
                                            } else {
                                                const pad = (n) => n.toString().padStart(2, '0');
                                                const lbl = pad(dt.getHours()) + ':' + pad(dt.getMinutes()) + ':' + pad(dt.getSeconds());
                                                arr.push({ x: lbl, y: p[seriesKeys[idx]] });
                                            }
                                        } catch (inner) { /* ignore per-payload errors */ }
                                    }
                                    return { name: name, data: arr };
                                });

                                try {
                                    window.ApexCharts.exec(cid, 'appendData', { series: appendSeries });
                                    // console.debug('WaterQualityChart: coalesce appendData applied', { chartId: cid, bufLen });
                                    // Force an immediate redraw to ensure the appended points render
                                    try { safeUpdateOptions(cid, {}); } catch (e) {}
                                    coalesce.buf = [];
                                } catch (appendErr) {
                                    console.error('WaterQualityChart: coalesce appendData failed, falling back to updateSeries', appendErr, { chartId: cid, bufLen });
                                    // fallback to full update
                                    window.ApexCharts.exec(cid, 'updateSeries', rebuilt);
                                    coalesce.buf = [];
                                }
                            } catch (err) {
                                console.error('WaterQualityChart: coalesce appendData failed', err, { chartId: cid, bufLen });
                            }
                        } catch (e) {
                            console.error('WaterQualityChart: coalesce flush error', e);
                        } finally {
                            coalesce.timer = null;
                        }
                    }, 150);
                } catch (e) { console.error('WaterQualityChart: scheduleCoalesceFlush scheduling failed', e); }
            }

            // Prune appendedMap to limit memory: keep at most `MAX_APPENDED_KEYS` entries
            // and remove entries older than `MAX_APPENDED_AGE_MS`.
            const MAX_APPENDED_KEYS = 500;
            const MAX_APPENDED_AGE_MS = 30 * 60 * 1000; // 30 minutes
            function pruneAppendedMap(map) {
                try {
                    if (!map || typeof map.entries !== 'function') return;

                    const now = Date.now();

                    // First remove entries older than age threshold
                    for (const [key, ts] of map.entries()) {
                        if (now - ts > MAX_APPENDED_AGE_MS) {
                            map.delete(key);
                        }
                    }

                    // If map still too large, remove oldest entries (Map preserves insertion order)
                    while (map.size > MAX_APPENDED_KEYS) {
                        const firstKey = map.keys().next().value;
                        if (!firstKey) break;
                        map.delete(firstKey);
                    }
                } catch (e) {
                    // ignore pruning errors
                    try { /* console.debug('waterChart: pruneAppendedMap error', e); */ } catch (_) {}
                }
            }

            function tryFlush(){
                if (!window.ApexCharts || typeof window.ApexCharts.exec !== 'function') return;
                // consider chart ready if exec exists and we can call a no-op
                ready = true;

                // Enable dynamic animations for realtime updates and ensure datetime axis if needed
                try {
                    const cid = window.__waterQualityKnownChartId[chartId] || chartId || ('apexcharts' + chartId);
                    safeUpdateOptions(
                        cid,
                        {
                            chart: {
                                id: cid,
                                animations: {
                                    enabled: true,
                                    easing: 'linear',
                                    dynamicAnimation: { enabled: false, speed: 350 }
                                }
                            }
                        }
                    );
                    // Force-disable any data labels or vendor annotations that may have
                    // been added by bundled chart code so only legend/tooltips remain.
                    try {
                        safeUpdateOptions(cid, { dataLabels: { enabled: false }, annotations: { yaxis: [], points: [], xaxis: [] } }, true);
                    } catch (e) {}
                    // DOM-cleanup fallback: remove any leftover annotation/datalabel nodes
                    try {
                        function removeAnnotationNodes(targetCid){
                            try {
                                const container = document.getElementById(targetCid);
                                if (!container) return;
                                // Remove common ApexCharts annotation/datalabel groups
                                const selectors = [
                                    '.apexcharts-annotations',
                                    '.apexcharts-annotations-group',
                                    '.apexcharts-datalabels',
                                    '.apexcharts-datalabel',
                                    '.apexcharts-annotation',
                                    '.apexcharts-series-annotations',
                                    '.apexcharts-point-annotations',
                                    '.apexcharts-point-annotation',
                                    '.apexcharts-point-annotation-label',
                                    '.apexcharts-point-annotations-custom-svg',
                                    '.apexcharts-xaxis-annotations',
                                    '.apexcharts-yaxis-annotations',
                                    '.apexcharts-xaxis-annotation-label',
                                    '.apexcharts-yaxis-annotation-label',
                                ];

                                // Remove explicit selectors
                                selectors.forEach(sel => {
                                    try {
                                        const nodes = Array.from(container.querySelectorAll(sel));
                                        nodes.forEach(n => { if (n && n.parentNode) n.parentNode.removeChild(n); });
                                    } catch(e){}
                                });

                                // Broad fallback: remove any child nodes that have class names
                                // containing 'annotation' or 'datalabel' (catch vendor naming variations)
                                try {
                                    const broad = Array.from(container.querySelectorAll('*')).filter(el => {
                                        try {
                                            const cls = el.className || '';
                                            if (typeof cls !== 'string') return false;
                                            const lower = cls.toLowerCase();
                                            return lower.indexOf('annotation') !== -1 || lower.indexOf('datalabel') !== -1 || lower.indexOf('point-annotation') !== -1;
                                        } catch(e){ return false; }
                                    });
                                    broad.forEach(n => { try { if (n && n.parentNode) n.parentNode.removeChild(n); } catch(e){} });
                                } catch(e){}
                            } catch(e){}
                        }
                        try { removeAnnotationNodes(cid); } catch(e){}
                        // Try again shortly after in case vendor re-inserts nodes
                        setTimeout(() => { try { removeAnnotationNodes(cid); } catch(e){} }, 500);
                        setTimeout(() => { try { removeAnnotationNodes(cid); } catch(e){} }, 1200);
                    } catch (e) {}
                    try {
                        // If our client state uses numeric timestamps, ensure chart xaxis is datetime
                        const state = window.__waterQualitySeriesState && window.__waterQualitySeriesState[chartId];
                            if (state && state.series && state.series[0] && state.series[0].data && state.series[0].data[0] && typeof state.series[0].data[0].x === 'number') {
                            safeUpdateOptions(cid, { xaxis: { type: 'datetime' } });
                        }
                    } catch (e) {}
                } catch (e) {}

                // Wrap ApexCharts.exec once so we can trigger a rate-limited
                // forced updateSeries after appendData calls. This keeps the
                // normal coalescing behavior but ensures visibility for many
                // append cases without changing appendToChart internals.
                try {
                    if (!window.__waterQualityExecWrapped && window.ApexCharts && typeof window.ApexCharts.exec === 'function') {
                        const originalExec = window.ApexCharts.exec.bind(window.ApexCharts);
                        try { window.__waterQualityOriginalExec = originalExec; } catch (e) {}
                        window.ApexCharts.exec = function(cid, method, payload) {
                            const res = originalExec(cid, method, payload);
                            try {
                                if (method === 'appendData') {
                                    try {
                                        const now = Date.now();
                                        const last = window.__waterQualityLastForcedAt[chartId] || 0;
                                        const FORCE_INTERVAL = 150;
                                        if ((now - last) > FORCE_INTERVAL) {
                                            const state = window.__waterQualitySeriesState && window.__waterQualitySeriesState[chartId];
                                            if (state && state.series) {
                                                const isDatetime = !!(state.series && state.series[0] && state.series[0].data && state.series[0].data[0] && (typeof state.series[0].data[0].x === 'number' || (typeof state.series[0].data[0].x === 'string' && state.series[0].data[0].x.indexOf('T') !== -1)));
                                                const rebuilt = isDatetime
                                                    ? state.series.map(s => ({ name: s.name, data: s.data.map(p => ({ x: p.x, y: p.y })) }))
                                                    : state.series.map(s => ({ name: s.name, data: s.data.map(p => p.y) }));
                                                try {
                                                            // Use saved original exec to avoid recursion into the wrapper
                                                            try {
                                                                if (window.__waterQualityOriginalExec) {
                                                                    window.__waterQualityOriginalExec(cid, 'updateSeries', rebuilt);
                                                                } else {
                                                                    originalExec(cid, 'updateSeries', rebuilt);
                                                                }
                                                            } catch (innerExecErr) {
                                                                // console.debug('WaterQualityChart: exec-wrapper originalExec failed', innerExecErr);
                                                            }
                                                    window.__waterQualityLastForcedAt[chartId] = now;
                                                } catch (updErr) {
                                                    // console.debug('WaterQualityChart: exec-wrapper updateSeries failed', updErr);
                                                }
                                            }
                                        }
                                    } catch (e) {
                                        // ignore wrapper errors
                                    }
                                }
                            } catch (e) {}
                            return res;
                        };
                        window.__waterQualityExecWrapped = true;
                    }
                } catch (wrapErr) {
                    // console.debug('WaterQualityChart: failed to wrap ApexCharts.exec', wrapErr);
                }

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
                    try { /* console.debug('WaterQualityChart.appendToChart payload', payload); */ } catch (e) {}
                    // Build series payload expected by ApexCharts.appendData or exec appendData
                    // Build series payload. Use the same x format as the initial chart labels
                    // (short time string 'HH:mm') because the chart is rendered with category labels.
                    const dt = new Date(payload.recorded_at);
                    const pad = (n) => n.toString().padStart(2, '0');
                    // Use seconds resolution (HH:mm:ss) as fallback label for category charts
                    const timeLabel = pad(dt.getHours()) + ':' + pad(dt.getMinutes()) + ':' + pad(dt.getSeconds());

                    // Detect whether our series state uses datetime point objects (with null-safe access)
                    // Accept numeric timestamps or ISO strings with 'T'
                    const isDatetimeMode = !!(seriesState?.series?.[0]?.data?.length && (typeof seriesState.series[0].data[0]?.x === 'number' || (typeof seriesState.series[0].data[0]?.x === 'string' && seriesState.series[0].data[0].x.indexOf('T') !== -1)));
                    const parsedTs = Date.parse(payload.recorded_at);
                    const numericTs = Number.isFinite(parsedTs) ? parsedTs : payload.recorded_at;
                    const timeKey = isDatetimeMode ? numericTs : timeLabel;

                    // Avoid duplicate appends for the same time key (client-side dedupe)
                    try {
                        if (!payload.__replay && appendedSet.has(timeKey)) {
                            return;
                        }
                    } catch (e) {
                        // ignore
                    }

                    const series = seriesKeys.map((key, idx) => {
                        const value = payload[key];
                        return {
                            name: seriesNames[idx] ?? key,
                            data: isDatetimeMode ? [{ x: payload.recorded_at, y: value }] : [{ x: timeLabel, y: value }]
                        };
                    });

                    // Try canonical exec appendData - many livewire-charts bundles support this
                    if (typeof window.ApexCharts.exec === 'function') {
                        try {
                            // Read existing category labels from the chart DOM (last resort)
                            let existingCategories = [];
                                } catch (e) {
                                    // console.debug('WaterQualityChart: forced redraw updateOptions failed', e);
                                }

                                // Attempt direct instance updateSeries (preferred over ApexCharts.exec)
                                try {
                                    window.__waterQualityChartInstances = window.__waterQualityChartInstances || {};
                                    let inst = null;

                                    if (window.ApexCharts && typeof window.ApexCharts.getChartByID === 'function') {
                                        try { inst = window.ApexCharts.getChartByID(targetCid); } catch(e) { inst = null; }
                                    }

                                    // Try known persisted mapping if direct id returned nothing
                                    if (!inst && window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[cidAttr]) {
                                        try { inst = window.ApexCharts.getChartByID(window.__waterQualityKnownChartId[cidAttr]); } catch(e) { inst = null; }
                                    }

                                    // Try scanning visible apexcharts canvases for an instance id
                                    if (!inst) {
                                        try {
                                            const canvases = Array.from(document.querySelectorAll('.apexcharts-canvas'));
                                            for (const c of canvases) {
                                                const p = c.closest('[id]');
                                                if (p && p.id) {
                                                    try { inst = window.ApexCharts.getChartByID(p.id); } catch(e) { inst = null; }
                                                    if (inst) break;
                                                }
                                            }
                                        } catch(e) { /* ignore */ }
                                    }

                                    if (inst) {
                                        try {
                                            // store for future direct access
                                            try { const id = inst.w && inst.w.config && inst.w.config.chart && inst.w.config.chart.id; if (id) window.__waterQualityChartInstances[id] = inst; } catch(e) {}
                                            if (typeof inst.updateSeries === 'function') {
                                                inst.updateSeries(rebuilt, false);
                                                console.log('✅ Direct instance updateSeries called', { chartId: inst.w && inst.w.config && inst.w.config.chart && inst.w.config.chart.id });
                                            }
                                        } catch(e) {
                                            // console.debug('waterChart: direct instance.updateSeries threw', e);
                                            try { if (typeof inst.render === 'function') { inst.render(); /* console.info('waterChart: direct instance.render() called as fallback'); */ } } catch(e) {}
                                        }
                                    } else {
                                        // console.debug('waterChart: no ApexCharts instance found for direct update', { tried: targetCid, known: window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[cidAttr] });
                                    }
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
                                            safeUpdateOptions(cid, { xaxis: { categories: newCategories } });
                                            window.ApexCharts.exec(cid, 'appendData', { series });

                                            // Persist mapping for future direct targeting
                                            window.__waterQualityKnownChartId[chartId] = cid;
                                            try { localStorage.setItem('waterQualityKnownChartId:' + chartId, cid); } catch (e) {}

                                            // console.info('ApexCharts.exec appendData succeeded for', cid, { timeKey, isDatetimeMode, numericTs: typeof numericTs !== 'undefined' ? numericTs : null });

                                            try { appendedSet.add(timeKey); } catch (err) {}
                                            try {
                                                if (!payload.__replay) {
                                                    try { /* console.debug('WaterQualityChart: buffering payload for', chartId, timeLabel); */ } catch (e) {}
                                                    appendBuffer.push(payload);
                                                    if (appendBuffer.length > MAX_BUFFER) appendBuffer.shift();
                                                    try { /* console.debug('WaterQualityChart: buffer length', chartId, appendBuffer.length); */ } catch (e) {}

                                                    // Update client-owned series state and trim if needed
                                                    try {
                                                        // push into state (use numeric timestamps for datetime mode)
                                                        seriesState.categories.push(timeKey);
                                                        seriesState.series.forEach((s, idx) => {
                                                            const val = payload[seriesKeys[idx]];
                                                            s.data.push({ x: isDatetimeMode ? numericTs : timeLabel, y: val });
                                                        });
                                                        // Trim state but avoid immediate full redraw; coalesce flush will updateSeries
                                                        trimStateOnly(window.__waterQualityKnownChartId[chartId] || chartId);
                                                        // add to coalesce buffer and schedule a flush
                                                        try { coalesce.buf.push(payload); if (coalesce.buf.length > MAX_BUFFER) coalesce.buf.shift(); } catch (e) {}
                                                        try { scheduleCoalesceFlush(); } catch (e) {}
                                                    } catch (e) { /* ignore state update errors */ }
                                                }
                                            } catch (err) {}

                                            // Schedule a short fallback check to force a redraw
                                            // if the appended point is not visible within ~150ms.
                                            try { scheduleFallbackCheck(cid, payload, timeKey, timeLabel, isDatetimeMode); } catch (e) {}

                                            // Immediately attempt a rate-limited forced updateSeries (diagnostic)
                                            try {
                                                const now = Date.now();
                                                const last = window.__waterQualityLastForcedAt[chartId] || 0;
                                                const FORCE_IMMEDIATE = 150; // ms
                                                if ((now - last) > FORCE_IMMEDIATE) {
                                                    const state = window.__waterQualitySeriesState && window.__waterQualitySeriesState[chartId];
                                                    if (state && state.series) {
                                                        const isDatetime = !!(state.series && state.series[0] && state.series[0].data && state.series[0].data[0] && (typeof state.series[0].data[0].x === 'number' || (typeof state.series[0].data[0].x === 'string' && state.series[0].data[0].x.indexOf('T') !== -1)));
                                                        const rebuilt = isDatetime
                                                            ? state.series.map(s => ({ name: s.name, data: s.data.map(p => ({ x: p.x, y: p.y })) }))
                                                            : state.series.map(s => ({ name: s.name, data: s.data.map(p => p.y) }));
                                                        try {
                                                            // debug last point
                                                            try { const lastPoint = state.series[0] && state.series[0].data && state.series[0].data[state.series[0].data.length-1]; console.debug('WaterQualityChart: immediate forced appendData - lastPoint', { cid, lastPoint }); } catch (e) {}
                                                            // Build per-series single-point arrays
                                                            const appendSeriesNow = state.series.map(s => {
                                                                const last = (s.data && s.data.length) ? s.data.slice(-1)[0] : null;
                                                                return { name: s.name, data: last ? [ { x: last.x, y: last.y } ] : [] };
                                                            });
                                                            try {
                                                                if (window.__waterQualityOriginalExec) {
                                                                    window.__waterQualityOriginalExec(cid, 'appendData', { series: appendSeriesNow });
                                                                } else {
                                                                    window.ApexCharts.exec(cid, 'appendData', { series: appendSeriesNow });
                                                                }
                                                                window.__waterQualityLastForcedAt[chartId] = now;
                                                            } catch (e) {
                                                                // fallback to updateSeries if appendData not supported
                                                                try {
                                                                    if (window.__waterQualityOriginalExec) {
                                                                        window.__waterQualityOriginalExec(cid, 'updateSeries', rebuilt);
                                                                    } else {
                                                                        window.ApexCharts.exec(cid, 'updateSeries', rebuilt);
                                                                    }
                                                                    window.__waterQualityLastForcedAt[chartId] = now;
                                                                } catch (updErr) {
                                                                    console.debug('WaterQualityChart: immediate appendData/updateSeries failed', updErr);
                                                                }
                                                            }
                                                        } catch (e) { console.debug('WaterQualityChart: immediate appendData scheduling failed', e); }
                                                    }
                                                }
                                            } catch (e) { console.debug('WaterQualityChart: immediate forced updateSeries scheduling failed', e); }

                                                            // Also request authoritative snapshot from server (rate-limited)
                                                            try {
                                                                window.__waterQualityLastSnapshotAt = window.__waterQualityLastSnapshotAt || {};
                                                                const lastSnap = window.__waterQualityLastSnapshotAt[chartId] || 0;
                                                                const SNAP_INTERVAL = 500; // ms
                                                                const nowSnap = Date.now();
                                                                if ((nowSnap - lastSnap) > SNAP_INTERVAL) {
                                                                    window.__waterQualityLastSnapshotAt[chartId] = nowSnap;
                                                                    (async function(){
                                                                        try {
                                                                            const userId = root.getAttribute('data-user-id');
                                                                            const limit = 32;
                                                                            const res = await fetch(`/api/water-quality/latest?user=${encodeURIComponent(userId)}&limit=${limit}`, { credentials: 'same-origin' });
                                                                            if (!res.ok) return;
                                                                            const json = await res.json();
                                                                            const labels = json.labels || [];
                                                                            const seriesObj = json.series || {};
                                                                            const keys = JSON.parse(root.getAttribute('data-series-keys') || '[]');
                                                                            const rebuiltSnapshot = keys.map((key, idx) => {
                                                                                const arr = seriesObj[key] || [];
                                                                                return { name: seriesNames[idx] || key, data: arr.map((v,i)=>{ const parsed = Date.parse(labels[i]); return { x: Number.isFinite(parsed) ? parsed : labels[i], y: v }; }) };
                                                                            });
                                                                            const targetCid = window.__waterQualityKnownChartId[chartId] || chartId || ('apexcharts' + chartId);
                                                                            try {
                                                                                if (window.__waterQualityOriginalExec) {
                                                                                    window.__waterQualityOriginalExec(targetCid, 'updateSeries', rebuiltSnapshot);
                                                                                } else {
                                                                                    window.ApexCharts.exec(targetCid, 'updateSeries', rebuiltSnapshot);
                                                                                }
                                                                                try {
                                                                                    seriesState.series = rebuiltSnapshot.map(s => ({ name: s.name, data: s.data.slice() }));
                                                                                    seriesState.categories = rebuiltSnapshot[0] ? rebuiltSnapshot[0].data.map(d=>d.x) : [];
                                                                                } catch (e) {}
                                                                            } catch (e) { console.debug('WaterQualityChart: snapshot updateSeries failed', e); }
                                                                        } catch (e) { /* ignore snapshot errors */ }
                                                                    })();
                                                                }
                                                            } catch (e) { /* ignore rate-limit errors */ }

                                            // Also request authoritative snapshot from server (rate-limited)
                                            try {
                                                window.__waterQualityLastSnapshotAt = window.__waterQualityLastSnapshotAt || {};
                                                const lastSnap = window.__waterQualityLastSnapshotAt[chartId] || 0;
                                                const SNAP_INTERVAL = 500; // ms
                                                const nowSnap = Date.now();
                                                if ((nowSnap - lastSnap) > SNAP_INTERVAL) {
                                                    window.__waterQualityLastSnapshotAt[chartId] = nowSnap;
                                                    (async function(){
                                                        try {
                                                            const userId = root.getAttribute('data-user-id');
                                                            const limit = 32;
                                                            const res = await fetch(`/api/water-quality/latest?user=${encodeURIComponent(userId)}&limit=${limit}`, { credentials: 'same-origin' });
                                                            if (!res.ok) return;
                                                            const json = await res.json();
                                                            const labels = json.labels || [];
                                                            const seriesObj = json.series || {};
                                                            const keys = JSON.parse(root.getAttribute('data-series-keys') || '[]');
                                                            const rebuiltSnapshot = keys.map((key, idx) => {
                                                                const arr = seriesObj[key] || [];
                                                                return { name: seriesNames[idx] || key, data: arr.map((v,i)=>{ const parsed = Date.parse(labels[i]); return { x: Number.isFinite(parsed) ? parsed : labels[i], y: v }; }) };
                                                            });
                                                            const targetCid = window.__waterQualityKnownChartId[chartId] || chartId || ('apexcharts' + chartId);
                                                            try {
                                                                if (window.__waterQualityOriginalExec) {
                                                                    window.__waterQualityOriginalExec(targetCid, 'updateSeries', rebuiltSnapshot);
                                                                } else {
                                                                    window.ApexCharts.exec(targetCid, 'updateSeries', rebuiltSnapshot);
                                                                }
                                                                // update local seriesState too
                                                                try {
                                                                    seriesState.series = rebuiltSnapshot.map(s => ({ name: s.name, data: s.data.slice() }));
                                                                    seriesState.categories = rebuiltSnapshot[0] ? rebuiltSnapshot[0].data.map(d=>d.x) : [];
                                                                } catch (e) {}
                                                            } catch (e) { console.debug('WaterQualityChart: snapshot updateSeries failed', e); }
                                                        } catch (e) { /* ignore snapshot errors */ }
                                                    })();
                                                }
                                            } catch (e) { /* ignore rate-limit errors */ }

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
                                            // console.info('ApexCharts.exec appendData succeeded for', cid, { timeKey, isDatetimeMode, numericTs: typeof numericTs !== 'undefined' ? numericTs : null });

                                            // Persist discovered mapping for faster targeting next time
                                            try { window.__waterQualityKnownChartId[chartId] = cid; try { localStorage.setItem('waterQualityKnownChartId:' + chartId, cid); } catch (e) {} } catch (err) {}

                                            // Reset failure counter on success
                                            try { window.__waterQualityFailureCount[chartId] = 0; } catch (err) {}

                                            // Mark as appended (dedupe) and persist to replay buffer
                                            try { appendedSet.add(timeKey); } catch (err) {}
                                            try {
                                                if (!payload.__replay) {
                                                    appendBuffer.push(payload);
                                                    if (appendBuffer.length > MAX_BUFFER) appendBuffer.shift();

                                                        // Update client-owned series state and trim (no immediate full redraw)
                                                    try {
                                                        seriesState.categories.push(timeKey);
                                                        seriesState.series.forEach((s, idx) => {
                                                            const val = payload[seriesKeys[idx]];
                                                            s.data.push({ x: isDatetimeMode ? numericTs : timeLabel, y: val });
                                                        });
                                                        trimStateOnly(cid);
                                                        // add to coalesce buffer and schedule a flush
                                                        try { coalesce.buf.push(payload); if (coalesce.buf.length > MAX_BUFFER) coalesce.buf.shift(); } catch (e) {}
                                                        try { scheduleCoalesceFlush(); } catch (e) {}
                                                    } catch (e) { /* ignore state errors */ }
                                                }
                                            } catch (err) {}

                                            // Schedule a fallback check to force a redraw if rendering lagged
                                                try { scheduleFallbackCheck(cid, payload, timeKey, timeLabel, isDatetimeMode); } catch (e) {}

                                                // Immediately attempt a rate-limited forced updateSeries (diagnostic)
                                                try {
                                                    const now = Date.now();
                                                    const last = window.__waterQualityLastForcedAt[chartId] || 0;
                                                    const FORCE_IMMEDIATE = 150; // ms
                                                    if ((now - last) > FORCE_IMMEDIATE) {
                                                        const state = window.__waterQualitySeriesState && window.__waterQualitySeriesState[chartId];
                                                        if (state && state.series) {
                                                            const isDatetime = !!(state.series && state.series[0] && state.series[0].data && state.series[0].data[0] && (typeof state.series[0].data[0].x === 'number' || (typeof state.series[0].data[0].x === 'string' && state.series[0].data[0].x.indexOf('T') !== -1)));
                                                            const rebuilt = isDatetime
                                                                ? state.series.map(s => ({ name: s.name, data: s.data.map(p => ({ x: p.x, y: p.y })) }))
                                                                : state.series.map(s => ({ name: s.name, data: s.data.map(p => p.y) }));
                                                            try {
                                                                // debug last point
                                                                try { const lastPoint = state.series[0] && state.series[0].data && state.series[0].data[state.series[0].data.length-1]; console.debug('WaterQualityChart: immediate forced updateSeries - lastPoint', { cid, lastPoint }); } catch (e) {}
                                                                if (window.__waterQualityOriginalExec) {
                                                                    window.__waterQualityOriginalExec(cid, 'updateSeries', rebuilt);
                                                                } else {
                                                                    window.ApexCharts.exec(cid, 'updateSeries', rebuilt);
                                                                }
                                                                window.__waterQualityLastForcedAt[chartId] = now;
                                                            } catch (e) { console.debug('WaterQualityChart: immediate updateSeries failed', e); }
                                                        }
                                                    }
                                                } catch (e) { console.debug('WaterQualityChart: immediate forced updateSeries scheduling failed', e); }

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
                                                            // console.info('WaterQualityChart: requested Livewire refresh for', lwId);
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

                            // As a robust fallback: if appendData couldn't be applied to any candidate,
                            // try a deterministic full update using updateOptions + updateSeries
                            // which is more reliable for category-based charts.
                            try {
                                const fallbackCid = window.__waterQualityKnownChartId[chartId] || chartId || 'apexcharts' + chartId;
                                        // ensure our client-side seriesState includes this payload
                                        try {
                                                if (!payload.__replay) {
                                                seriesState.categories.push(timeKey);
                                                seriesState.series.forEach((s, idx) => {
                                                    const val = payload[seriesKeys[idx]];
                                                    s.data.push({ x: isDatetimeMode ? numericTs : timeLabel, y: val });
                                                });
                                            }
                                        } catch (e) { /* ignore */ }

                                const rebuilt = seriesState.series.map(s => ({ name: s.name, data: s.data.map(p => p.y) }));
                                const categoriesCopy = seriesState.categories.slice();

                                try { safeUpdateOptions(fallbackCid, { xaxis: { categories: categoriesCopy } }); } catch (e) {}
                                try { window.ApexCharts.exec(fallbackCid, 'updateSeries', rebuilt); } catch (e) {}

                                try { appendedSet.add(timeLabel); } catch (e) {}
                                try {
                                    if (!payload.__replay) {
                                        appendBuffer.push(payload);
                                        if (appendBuffer.length > MAX_BUFFER) appendBuffer.shift();
                                    }
                                } catch (e) {}

                                // console.info('WaterQualityChart: fallback updateSeries/updateOptions applied for', fallbackCid);
                                return;
                            } catch (e) {
                                // ignore fallback errors and continue
                            }
                        } catch (e) {
                            // if anything goes wrong here, fallback to per-series append below
                        }
                    }
                } catch (err) {
                    // queue and retry later
                    queue.push(payload);
                }
            }

            // Minimal realtime handler (Option B): authoritative updateSeries-only path.
            // We update the small client-side series snapshot then call updateOptions/updateSeries
            // so the chart always renders the newest point reliably (no appendData reliance).
            window.addEventListener('water-quality:point', (event) => {
                // Accept either a single point or an array of points. If an array
                // is received, process each point in order.
                const payloads = [];
                if (event && event.detail) {
                    if (Array.isArray(event.detail)) payloads.push(...event.detail);
                    else payloads.push(event.detail);
                } else if (event) {
                    payloads.push(event);
                }

                if (!payloads.length) return;

                const logicalId = root.getAttribute('data-chart-id') || chartId;

                function locateChartInstanceByLogicalId(id) {
                    const tried = new Set();
                    const candidates = [];
                    try {
                        if (window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[id]) candidates.push(window.__waterQualityKnownChartId[id]);
                        candidates.push(id);
                        candidates.push('apexcharts' + id);
                        candidates.push('apexcharts-' + id);
                        candidates.push(id + '-chart');

                        // Search DOM ids that contain or are contained by logical id
                        const all = Array.from(document.querySelectorAll('[id]'));
                        for (const el of all) {
                            const cid = el.id;
                            if (!cid) continue;
                            if (cid.indexOf(id) !== -1 || id.indexOf(cid) !== -1) candidates.push(cid);
                        }

                        // Consider visible apexcharts canvases and their closest id'd parent
                        const canvases = Array.from(document.querySelectorAll('.apexcharts-canvas'));
                        for (const c of canvases) {
                            const p = c.closest('[id]');
                            if (p && p.id) candidates.push(p.id);
                        }
                    } catch (e) { /* ignore DOM search errors */ }

                    for (const cid of candidates) {
                        if (!cid || tried.has(cid)) continue;
                        tried.add(cid);
                        try {
                            // Prefer ApexCharts.getChartByID when available
                            if (window.ApexCharts && typeof window.ApexCharts.getChartByID === 'function') {
                                const inst = window.ApexCharts.getChartByID(cid);
                                if (inst && typeof inst.updateSeries === 'function') return { instance: inst, id: cid };
                            }

                            // Fallback: element stored instance
                            const el = document.getElementById(cid);
                            if (el && el.__apexChartInstance) return { instance: el.__apexChartInstance, id: cid };
                        } catch (e) {
                            // ignore candidate errors and continue
                        }
                    }
                    return null;
                }

                const located = locateChartInstanceByLogicalId(logicalId);
                if (!located) {
                    // Chart instance not found - this can happen legitimately during destroy/recreate cycles
                    // Only log error if chart container exists (otherwise it's expected during teardown)
                    const container = document.getElementById(logicalId);
                    if (container) {
                        console.warn('waterChart: Chart instance not ready for logical id:', logicalId, '- will retry on next event');
                    }
                    return;
                }

                // Persist mapping for faster lookups later
                try {
                    window.__waterQualityKnownChartId = window.__waterQualityKnownChartId || {};
                    window.__waterQualityKnownChartId[logicalId] = located.id;
                    try { localStorage.setItem('waterQualityKnownChartId:' + logicalId, located.id); } catch (e) {}
                } catch (e) {}

                const chart = located.instance;

                // Process each payload sequentially (apps may send multiple points at once)
                for (const data of payloads) {
                    try {
                        const currentSeries = (chart.w && chart.w.config && Array.isArray(chart.w.config.series)) ? chart.w.config.series : (chart.config && Array.isArray(chart.config.series) ? chart.config.series : []);
                        const timestamp = Number.isFinite(Date.parse(data.recorded_at)) ? Date.parse(data.recorded_at) : Date.now();

                        const updatedSeries = currentSeries.map((series) => {
                            const name = series.name || '';
                            let yValue = null;
                            const lname = name.toLowerCase();
                            if (lname.includes('temp')) yValue = parseFloat(data.temperature);
                            else if (lname.includes('dissolved')) yValue = parseFloat(data.dissolved_oxygen);
                            else if (lname.includes('salinity')) yValue = parseFloat(data.salinity);
                            else if (lname.includes('ph')) yValue = parseFloat(data.ph_level);

                            const existing = Array.isArray(series.data) ? series.data.slice() : [];
                            existing.push({ x: timestamp, y: yValue });
                            return { name: series.name, data: existing.slice(-200) };
                        });

                        try {
                            chart.updateSeries(updatedSeries, false);
                            console.log('waterChart: chart updated with new point');
                        } catch (err) {
                            console.error('waterChart: chart.updateSeries failed', err);
                        }
                    } catch (err) {
                        console.error('waterChart: processing payload failed', err);
                    }
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

            // Polling removed - relying solely on Reverb WebSocket real-time updates
        })();
    </script>

            <!-- debug/test UI removed -->

            <script>
                // Auto-adjust ApexCharts height with debouncing (prevents infinite loops)
                (function(){
                    const root = document.getElementById('water-quality-chart-root');
                    if (!root) return;
                    const chartId = root.getAttribute('data-chart-id');
                    
                    // CRITICAL: Use global registry to prevent multiple instances running simultaneously
                    window.__waterQualityHeightAdjuster = window.__waterQualityHeightAdjuster || {};
                    
                    // If already initialized for this chart, skip
                    if (window.__waterQualityHeightAdjuster[chartId]) {
                        console.debug('Height adjuster already running for:', chartId);
                        return;
                    }
                    
                    let adjusting = false;
                    let lastAdjustTime = 0;
                    const ADJUST_INTERVAL = 2000;
                    
                    function computeAndApply(){
                        const now = Date.now();
                        if (adjusting || (now - lastAdjustTime < ADJUST_INTERVAL)) return;
                        
                        // Check if root still exists in DOM (prevents execution after component unmount)
                        if (!document.getElementById('water-quality-chart-root')) {
                            console.debug('Root gone, stopping height adjuster');
                            return;
                        }
                        
                        adjusting = true;
                        lastAdjustTime = now;
                        
                        try {
                            const targetCid = (window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[chartId]) || chartId;
                            
                            if (window.ApexCharts && typeof window.ApexCharts.getChartByID === 'function') {
                                const instance = window.ApexCharts.getChartByID(targetCid);
                                if (instance) {
                                    const parent = root.parentElement || root;
                                    const avail = parent.clientHeight || root.clientHeight || 420;
                                    const offset = 32;
                                    let target = Math.round(avail - offset);
                                    if (target < 360) target = 360;
                                    if (target > 480) target = 480;
                                    instance.updateOptions({ chart: { height: target } }, false, false);
                                }
                            }
                        } catch (e) { 
                            console.debug('computeAndApply error:', e);
                        } finally {
                            adjusting = false;
                        }
                    }
                    
                    const debounce = (fn, t=200) => { let id; return () => { clearTimeout(id); id = setTimeout(fn, t); }; };
                    const debouncedCompute = debounce(computeAndApply, 250);
                    
                    // Store listener reference for cleanup
                    window.__waterQualityHeightAdjuster[chartId] = {
                        active: true,
                        listener: debouncedCompute
                    };
                    
                    window.addEventListener('resize', debouncedCompute);
                    
                    // Run only if DOM is ready (no DOMContentLoaded listener needed - inline script runs after DOM)
                    if (document.readyState !== 'loading') {
                        setTimeout(computeAndApply, 100);
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
