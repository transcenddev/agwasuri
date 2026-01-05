<div class="w-full pt-4 pb-2 bg-white dark:bg-slate-900 relative z-999 px-4 sm:px-6 lg:px-8">
    @php
        $isAdmin = auth()->check() && (method_exists(auth()->user(), 'isAdmin') ? auth()->user()->isAdmin() : (
            (isset(auth()->user()->role) && auth()->user()->role === 'admin') ||
            (isset(auth()->user()->role_id) && auth()->user()->role_id === 1)
        ));
    @endphp
    <div class="flex items-center justify-between gap-4">
        {{-- Left: logo, title and (admin) pond selector / search --}}
        <div class="flex items-center gap-4">
            {{-- Topbar logo removed to avoid duplication with sidebar logo. --}}
            {{-- Keep admin search here for quick access. --}}
            @if($isAdmin)
                <div class="flex items-center gap-3">
                    <label for="pond-select" class="sr-only">Search user or fishpond</label>
                    <livewire:user-filter />
                </div>
            @endif
        </div>

        {{-- Spacer (center) - intentionally empty to push range to right --}}
        <div class="flex-1"></div>

        {{-- Compact range presets (hidden on small screens) - moved to the right. No inner changes. --}}
        <div id="topbar-range" x-data="{ open:false, customOpen:false, label: '24h', customLabel: 'Custom' }" @keydown.escape.window="open=false; customOpen=false" class="hidden md:flex items-center gap-2 {{ $isAdmin ? 'ms-4' : '' }}">
            <!-- Separate Custom pill to the left of the dropdown -->
            <div class="relative order-first">
                <button @click="customOpen = !customOpen; open = false" type="button" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl border border-gray-200 bg-white text-xs hover:bg-gray-50" aria-haspopup="dialog" :aria-expanded="customOpen" :aria-controls="'topbar-custom-pop'">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span x-text="customLabel">Custom</span>
                    <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="customOpen" x-trap="customOpen" @click.outside="customOpen=false" x-cloak id="topbar-custom-pop" role="dialog" aria-modal="true" class="absolute left-0 top-full z-50 mt-2 w-[18rem] bg-white rounded-md shadow-lg p-3">
                    <label class="text-xs text-gray-600">Start Date</label>
                    <input x-ref="customStart" type="date" id="topbar-custom-start" class="block w-full h-9 px-3 py-2 border border-gray-200 rounded-md">
                    <label class="text-xs text-gray-600 mt-2">End Date</label>
                    <input type="date" id="topbar-custom-end" class="block w-full h-9 px-3 py-2 border border-gray-200 rounded-md">
                    <div class="flex items-center gap-2 mt-2">
                        <button type="button" id="topbar-custom-apply" @click="customOpen=false; const start = document.getElementById('topbar-custom-start').value; const end = document.getElementById('topbar-custom-end').value; if(start && end) { const s = new Date(start).toLocaleDateString('en-US', {month: 'short', day: '2-digit'}); const e = new Date(end).toLocaleDateString('en-US', {month: 'short', day: '2-digit'}); customLabel = s + ' - ' + e; }" class="inline-flex items-center px-3 py-1.5 rounded-md bg-cvsu text-white text-sm">Apply</button>
                        <button type="button" @click.prevent="customOpen=false" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 text-sm">Cancel</button>
                    </div>
                </div>
                <script>
                    (function(){
                        // when the Custom popover opens, focus the start input for keyboard users
                        document.addEventListener('click', function(e){
                            try {
                                const btn = e.target.closest('[aria-controls="topbar-custom-pop"]');
                                if (!btn) return;
                                // small timeout to wait for Alpine to set x-show
                                setTimeout(function(){
                                    const start = document.getElementById('topbar-custom-start');
                                    if (start) start.focus();
                                }, 50);
                            } catch (err) {}
                        });
                    })();
                </script>
            </div>

            <div class="relative">
                <button @click="open = !open; customOpen=false" type="button" class="inline-flex items-center gap-2 px-3 py-1 rounded-2xl border border-gray-200 bg-white text-xs hover:bg-gray-50" aria-haspopup="menu" :aria-expanded="open">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                    </svg>
                    <span id="topbar-range-display" class="text-xs" x-text="label">24h</span>
                    <svg class="w-3 h-3 text-gray-500 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.outside="open=false; customOpen=false" x-cloak class="absolute left-0 top-full z-50 mt-2 min-w-[6rem] bg-white rounded-md shadow-lg p-2">
                    <div class="flex flex-col gap-1">
                        <!-- presets only inside dropdown -->
                        <div class="pt-1"></div>
                        <button type="button" data-range="1h" class="topbar-range-btn text-left w-full px-3 py-2 text-sm rounded hover:bg-gray-50">1h</button>
                        <button type="button" data-range="24h" class="topbar-range-btn text-left w-full px-3 py-2 text-sm rounded hover:bg-gray-50">24h</button>
                        <button type="button" data-range="7d" class="topbar-range-btn text-left w-full px-3 py-2 text-sm rounded hover:bg-gray-50">7d</button>
                        <button type="button" data-range="30d" class="topbar-range-btn text-left w-full px-3 py-2 text-sm rounded hover:bg-gray-50">30d</button>
                    </div>
                </div>
            </div>
            
            <!-- Export Data Button -->
            <button type="button" onclick="alert('Export functionality coming soon!')" class="inline-flex items-center gap-2 px-3 py-1 rounded-2xl border border-gray-200 bg-white text-xs hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export</span>
            </button>
        </div>

        {{-- Middle and connection status removed per design --}}
    </div>

    <script>
        (function(){
            const select = document.getElementById('pond-select');
            if (select) {
                select.addEventListener('change', function(){
                    // navigate with query to pick user quickly
                    const val = select.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('user', val);
                    window.location.href = url.toString();
                });
            }

            const lastEl = document.getElementById('topbar-last-updated-text');
            let lastTs = null;
            function fmtRelative(ts){
                if (!ts) return '—';
                const d = Math.max(0, Math.floor((Date.now() - ts)/1000));
                if (d < 5) return 'just now';
                if (d < 60) return d + 's ago';
                if (d < 3600) return Math.floor(d/60) + 'm ago';
                if (d < 86400) return Math.floor(d/3600) + 'h ago';
                return new Date(ts).toLocaleString();
            }

            window.addEventListener('water-quality:point', (e) => {
                try {
                    const detail = e && e.detail ? e.detail : null;
                    const rec = detail && detail.recorded_at ? Date.parse(detail.recorded_at) : Date.now();
                    lastTs = rec;
                    if (lastEl) lastEl.textContent = fmtRelative(lastTs);
                    // mark connected
                    setConnection(true);
                } catch (err) {}
            });

            // Listen for a connection status event
            window.addEventListener('water-quality:connection', (e) => {
                try { setConnection(e && e.detail && e.detail.connected); } catch(e){}
            });

            function setConnection(connected){
                const dot = document.getElementById('topbar-conn-dot');
                const label = document.getElementById('topbar-conn-label');
                if (!dot || !label) return;
                if (connected) {
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-green-500');
                    label.textContent = 'Connected';
                } else {
                    dot.classList.remove('bg-green-500');
                    dot.classList.add('bg-red-500');
                    label.textContent = 'Disconnected';
                }
            }

            // update relative display every 30s
            setInterval(() => { if (lastTs && lastEl) lastEl.textContent = fmtRelative(lastTs); }, 30000);

            // Range preset buttons in topbar: update URL query params and dispatch event
            function isoNow(){ return new Date().toISOString(); }
            function isoNowMinus(ms){ return new Date(Date.now() - ms).toISOString(); }
            
            function updateDateRange(start, end) {
                // Update URL query parameters
                const url = new URL(window.location.href);
                if (start) url.searchParams.set('start', start);
                else url.searchParams.delete('start');
                if (end) url.searchParams.set('end', end);
                else url.searchParams.delete('end');
                
                // Update URL without page reload
                window.history.pushState({}, '', url.toString());
                
                // Dispatch event for Livewire components (DOM + Livewire bus)
                window.dispatchEvent(new CustomEvent('date-range-updated', { detail: [ start, end ] }));
                try {
                    if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                        window.Livewire.dispatch('date-range-updated', {
                            startDate: start,
                            endDate: end,
                            startTime: null,
                            endTime: null,
                            startDateTime: start,
                            endDateTime: end,
                        });
                    }
                } catch (e) {
                    // swallow client dispatch errors
                }
                try {
                    console.debug('topbar: dispatched date-range-updated', { start, end });
                } catch (e) {}
            }

            // Attach handlers once DOM ready
            document.addEventListener('DOMContentLoaded', function(){
                try {
                    document.querySelectorAll('.topbar-range-btn').forEach(function(btn){
                        btn.addEventListener('click', function(){
                            const r = btn.getAttribute('data-range');
                            let start = null, end = isoNow();
                            if (r === '1h') start = isoNowMinus(1000*60*60);
                            else if (r === '24h') start = isoNowMinus(1000*60*60*24);
                            else if (r === '7d') start = isoNowMinus(1000*60*60*24*7);
                            else if (r === '30d') start = isoNowMinus(1000*60*60*24*30);
                            
                            updateDateRange(start, end);
                            
                            // Update visible label in dropdown
                            try {
                                const disp = document.getElementById('topbar-range-display');
                                if (disp) disp.textContent = r;
                            } catch (e) {}
                        });
                    });

                    const apply = document.getElementById('topbar-custom-apply');
                    if (apply) {
                        apply.addEventListener('click', function(){
                            const s = document.getElementById('topbar-custom-start').value;
                            const e = document.getElementById('topbar-custom-end').value;
                            if (!s || !e) {
                                alert('Please pick both start and end dates');
                                return;
                            }
                            
                            updateDateRange(s, e);
                            
                            // Update dropdown label to 'Custom'
                            try {
                                const disp = document.getElementById('topbar-range-display');
                                if (disp) disp.textContent = 'Custom';
                            } catch (e) {}
                            // close any open dropdown by blurring
                            try { document.activeElement.blur(); } catch(e){}
                        });
                    }
                } catch (e) {
                    // swallow
                }
            });
        })();
    </script>
</div>
