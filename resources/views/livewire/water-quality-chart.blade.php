{{-- Outer container using flex column layout --}}
<div class="flex flex-col">
    @if(!empty($lineChartModelArray))
        {{-- Single root element for Livewire + compact header --}}
        <div class="w-full">
            <div class="px-4 py-2 flex items-center justify-between"> {{-- header --}}
                <div class="flex items-center gap-2">&nbsp;</div>
            </div>

            {{-- Chart Area: render via the lightweight wrapper that consumes the serializable model array --}}
            <div class="w-full">
                <div class="relative">
                    @php
                        $__chartId = $lineChartModelArray['jsonConfig']['chart']['id'] ?? 'water-quality-0';
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

                    <div id="water-quality-chart-root" wire:ignore class="w-full p-4 max-w-content-xl mx-auto h-64 md:h-80 lg:h-[420px] max-h-[480px] overflow-hidden"
                        data-chart-id="{{ $__chartId }}"
                        data-api-key='@json($__debugApiKey)'
                        data-series-names='@json($__seriesNames)'
                        data-series-keys='@json($__seriesKeys)'
                        data-initial='@json($chartData)'
                        data-user-id="{{ request()->get('user') ?? session('selected_user', auth()->id()) }}">
                        @livewire(\App\Livewire\LivewireChartsLineWrapper::class, ['lineChartModel' => $lineChartModelArray], key('water-quality-' . $__chartId))
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Minimal header when there's no data (header intentionally omitted) --}}
        <div class="px-4 py-2 shrink-0 flex items-center justify-between">
            <div class="flex items-center gap-2">&nbsp;</div>
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

    {{-- Kebab/menu removed: header actions intentionally omitted for cleaner UI --}}

    {{-- Lightweight append-only listener for realtime points. Will attempt to append points to ApexCharts when ready. --}}
    <script>
        (function(){
            const root = document.getElementById('water-quality-chart-root');
            if (!root) return;

            const chartId = root.getAttribute('data-chart-id');
            const seriesNames = JSON.parse(root.getAttribute('data-series-names') || '[]');
            const seriesKeys = JSON.parse(root.getAttribute('data-series-keys') || '[]');

            // Debug helper: dump ApexCharts instances and candidate ids
            try {
                window.__waterQualityDumpInstances = window.__waterQualityDumpInstances || function(){
                    try {
                        console.debug('waterChart: dumping ApexCharts instances...');
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
                            console.debug('waterChart: ApexCharts.getChartByID not available', !!window.ApexCharts);
                        }
                        uniq.forEach(id => {
                            try {
                                const inst = (window.ApexCharts && typeof window.ApexCharts.getChartByID === 'function') ? window.ApexCharts.getChartByID(id) : null;
                                console.debug('waterChart: candidate', id, 'hasInstance', !!inst, inst && inst.w && inst.w.config && inst.w.config.chart && inst.w.config.chart.id ? inst.w.config.chart.id : null);
                            } catch (e) {
                                console.debug('waterChart: getChartByID failed for', id, e);
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

                // lastSeen stores ms timestamp of the last dispatched point (or null)
                let lastSeen = null;
                try {
                    const existing = window.__waterQualitySeriesState && window.__waterQualitySeriesState[chartId];
                    if (existing && existing.series && existing.series[0] && existing.series[0].data && existing.series[0].data.length) {
                        const lp = existing.series[0].data.slice(-1)[0];
                        lastSeen = (typeof lp.x === 'number') ? lp.x : (lp.x ? Date.parse(lp.x) : null);
                    }
                } catch (e) { lastSeen = null; }

                setInterval(async () => {
                    try {
                        if (!userId) return;
                        const res = await fetch(`/api/water-quality/latest?user=${encodeURIComponent(userId)}&limit=10`, { credentials: 'same-origin' });
                        if (!res.ok) return;
                        const json = await res.json();
                        const rows = rowsFromJson(json);
                        if (!rows || !rows.length) return;

                        // Normalize recorded_at to ms and filter rows newer than lastSeen
                        const newRows = [];
                        for (let i = 0; i < rows.length; i++) {
                            const r = rows[i];
                            const parsed = (typeof r.recorded_at === 'number') ? r.recorded_at : (r.recorded_at ? Date.parse(r.recorded_at) : null);
                            if (parsed === null || Number.isNaN(parsed)) continue;
                            if (lastSeen === null || parsed > lastSeen) {
                                newRows.push(r);
                            }
                        }
                        if (!newRows.length) return;

                        // Dispatch events in chronological order
                        newRows.sort((a,b) => Date.parse(a.recorded_at) - Date.parse(b.recorded_at));
                        for (const r of newRows) {
                            try {
                                window.dispatchEvent(new CustomEvent('water-quality:point', { detail: r }));
                            } catch (e) { /* ignore per-row errors */ }
                            try { lastSeen = Date.parse(r.recorded_at); } catch (e) {}
                        }
                    } catch (e) {
                        // ignore polling errors
                    }
                }, POLL_INTERVAL_MS);
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
                    // Ensure bounded length
                    while (seriesState.categories.length > MAX_POINTS) seriesState.categories.shift();
                    seriesState.series.forEach(s => { while (s.data.length > MAX_POINTS) s.data.shift(); });

                    // Update chart categories to reflect trimmed state. We avoid
                    // forcing a full updateSeries here because real-time appends
                    // will be coalesced and flushed separately to reduce redraws.
                    try { safeUpdateOptions(cid, { xaxis: { categories: seriesState.categories } }); } catch (e) {}
                } catch (e) {
                    // ignore trim errors
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

                    // Ensure immutable visual defaults are always present unless explicitly forced off.
                    const immutableDefaults = {
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
                    try { console.debug('waterChart: safeUpdateOptions failed', e); } catch (_) {}
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
                                    console.info('WaterQualityChart: fallback forced updateSeries for', cid, timeKey);
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
                            console.debug('WaterQualityChart: coalesce flush running', { chartId, bufLen });
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
                                    console.debug('WaterQualityChart: coalesce appendData applied', { chartId: cid, bufLen });
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
                    try { console.debug('waterChart: pruneAppendedMap error', e); } catch (_) {}
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
                                                                console.debug('WaterQualityChart: exec-wrapper originalExec failed', innerExecErr);
                                                            }
                                                    window.__waterQualityLastForcedAt[chartId] = now;
                                                } catch (updErr) {
                                                    console.debug('WaterQualityChart: exec-wrapper updateSeries failed', updErr);
                                                }
                                            }
                                        }
                                        // Force updateSeries to not animate regardless of caller to avoid flicker
                                        try {
                                            if (window.ApexCharts && window.ApexCharts.prototype && !window.__waterQualityPatchedUpdateSeries) {
                                                window.__waterQualityPatchedUpdateSeries = true;
                                                const _origUpdateSeries = window.ApexCharts.prototype.updateSeries;
                                                window.ApexCharts.prototype.updateSeries = function(series, animate, redrawPaths) {
                                                    try {
                                                        return _origUpdateSeries.call(this, series, false, redrawPaths);
                                                    } catch (err) {
                                                        // Fallback if original signature differs
                                                        return _origUpdateSeries.call(this, series, animate, redrawPaths);
                                                    }
                                                };
                                            }
                                        } catch (e) {}
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
                    console.debug('WaterQualityChart: failed to wrap ApexCharts.exec', wrapErr);
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
                    try { console.debug('WaterQualityChart.appendToChart payload', payload); } catch (e) {}
                    // Build series payload expected by ApexCharts.appendData or exec appendData
                    // Build series payload. Use the same x format as the initial chart labels
                    // (short time string 'HH:mm') because the chart is rendered with category labels.
                    const dt = new Date(payload.recorded_at);
                    const pad = (n) => n.toString().padStart(2, '0');
                    // Use seconds resolution (HH:mm:ss) as fallback label for category charts
                    const timeLabel = pad(dt.getHours()) + ':' + pad(dt.getMinutes()) + ':' + pad(dt.getSeconds());

                    // Detect whether our series state uses datetime point objects
                    // Accept numeric timestamps or ISO strings with 'T'
                    const isDatetimeMode = !!(seriesState.series && seriesState.series[0] && seriesState.series[0].data && seriesState.series[0].data.length && (typeof seriesState.series[0].data[0].x === 'number' || (typeof seriesState.series[0].data[0].x === 'string' && seriesState.series[0].data[0].x.indexOf('T') !== -1)));
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
                                    console.debug('WaterQualityChart: forced redraw updateOptions failed', e);
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
                                            console.debug('waterChart: direct instance.updateSeries threw', e);
                                            try { if (typeof inst.render === 'function') { inst.render(); console.info('waterChart: direct instance.render() called as fallback'); } } catch(e) {}
                                        }
                                    } else {
                                        console.debug('waterChart: no ApexCharts instance found for direct update', { tried: targetCid, known: window.__waterQualityKnownChartId && window.__waterQualityKnownChartId[cidAttr] });
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

                                            console.info('ApexCharts.exec appendData succeeded for', cid, { timeKey, isDatetimeMode, numericTs: typeof numericTs !== 'undefined' ? numericTs : null });

                                            try { appendedSet.add(timeKey); } catch (err) {}
                                            try {
                                                if (!payload.__replay) {
                                                    try { console.debug('WaterQualityChart: buffering payload for', chartId, timeLabel); } catch (e) {}
                                                    appendBuffer.push(payload);
                                                    if (appendBuffer.length > MAX_BUFFER) appendBuffer.shift();
                                                    try { console.debug('WaterQualityChart: buffer length', chartId, appendBuffer.length); } catch (e) {}

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
                                            console.info('ApexCharts.exec appendData succeeded for', cid, { timeKey, isDatetimeMode, numericTs: typeof numericTs !== 'undefined' ? numericTs : null });

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

                                console.info('WaterQualityChart: fallback updateSeries/updateOptions applied for', fallbackCid);
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
                    console.error('waterChart: Chart not found for logical id:', logicalId);
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

            // Polling fallback: request last N points every 2s and force an updateSeries.
            (function pollingFallback(){
                try {
                    const pollInterval = 2000; // ms
                    const chartId = root.getAttribute('data-chart-id');
                    const userId = root.getAttribute('data-user-id');
                    const limit = 32;
                    if (!userId) return;

                    async function fetchAndUpdate(){
                        try {
                            const res = await fetch(`/api/water-quality/latest?user=${encodeURIComponent(userId)}&limit=${limit}`, { credentials: 'same-origin' });
                            if (!res.ok) return;
                            const json = await res.json();

                            // json expected: { labels: [...], series: { temperature: [...], dissolved_oxygen: [...], salinity: [...], ph_level: [...] } }
                            const labels = json.labels || [];
                            const seriesObj = json.series || {};
                            const keys = JSON.parse(root.getAttribute('data-series-keys') || '[]');

                            const isDatetime = labels.length && typeof labels[0] === 'string' && labels[0].indexOf('T') !== -1;

                            const rebuilt = keys.map((key, idx) => {
                                const arr = seriesObj[key] || [];
                                if (isDatetime) {
                                    return { name: seriesNames[idx] || key, data: arr.map((v,i)=>{ const parsed = Date.parse(labels[i]); return { x: Number.isFinite(parsed) ? parsed : labels[i], y: v }; }) };
                                }
                                return { name: seriesNames[idx] || key, data: arr };
                            });

                            const cid = window.__waterQualityKnownChartId[chartId] || chartId || ('apexcharts' + chartId);
                            if (window.ApexCharts && typeof window.ApexCharts.exec === 'function') {
                                try {
                                    window.ApexCharts.exec(cid, 'updateSeries', rebuilt);
                                } catch (e) {
                                    console.debug('waterChart: polling updateSeries failed', e);
                                }
                            }
                        } catch (e) {
                            // ignore polling errors
                        }
                    }

                    // start after small delay so initial chart setup can complete
                    setTimeout(() => { fetchAndUpdate(); setInterval(fetchAndUpdate, pollInterval); }, 5000);
                } catch (e) {
                    // ignore
                }
            })();
        })();
    </script>

            <!-- debug/test UI removed -->

            <script>
                // Auto-adjust ApexCharts height to fit parent between 360 and 480px
                (function(){
                    const root = document.getElementById('water-quality-chart-root');
                    if (!root) return;
                    const chartId = root.getAttribute('data-chart-id');
                    function computeAndApply(){
                        try {
                            const parent = root.parentElement || root;
                            const avail = parent.clientHeight || root.clientHeight || 420;
                            // leave some breathing room for paddings/legend
                            const offset = 32;
                            let target = Math.round(avail - offset);
                            if (target < 360) target = 360;
                            if (target > 480) target = 480;
                            if (window.ApexCharts && typeof window.ApexCharts.exec === 'function') {
                                try { window.ApexCharts.exec(chartId, 'updateOptions', { chart: { height: target } }, false, true); } catch (_){}
                                try { window.dispatchEvent(new Event('resize')); } catch(_){}
                            }
                        } catch (e) { /* ignore */ }
                    }
                    const debounce = (fn, t=200) => { let id; return () => { clearTimeout(id); id = setTimeout(fn, t); }; };
                    window.addEventListener('resize', debounce(computeAndApply, 250));
                    document.addEventListener('DOMContentLoaded', () => setTimeout(computeAndApply, 600));
                    // Try once immediately and again shortly after
                    try { computeAndApply(); setTimeout(computeAndApply, 900); } catch(e){}
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
