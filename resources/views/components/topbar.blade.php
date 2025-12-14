<div class="w-full py-2 bg-white dark:bg-slate-900 relative z-999 px-4 sm:px-6 lg:px-8">
    @php
        $isAdmin = auth()->check() && (
            (isset(auth()->user()->role) && auth()->user()->role === 'admin') ||
            (isset(auth()->user()->role_id) && auth()->user()->role_id === 1)
        );
    @endphp
    <div class="flex items-center justify-between gap-4">
        {{-- Left: pond selector / search (use Livewire omnisearch) --}}
        @if($isAdmin)
            <div class="flex items-center gap-3 w-full lg:w-1/3">
                <label for="pond-select" class="sr-only">Search user or fishpond</label>
                <livewire:user-filter />
            </div>
        @endif

        {{-- Compact range presets (hidden on small screens) --}}
        <div id="topbar-range" class="hidden md:flex items-center gap-2 {{ $isAdmin ? 'ms-4' : '' }}">
            <button type="button" data-range="1h" class="topbar-range-btn inline-flex items-center px-3 py-1 rounded-md border border-gray-200 bg-white text-xs hover:bg-gray-50">1h</button>
            <button type="button" data-range="24h" class="topbar-range-btn inline-flex items-center px-3 py-1 rounded-md border border-gray-200 bg-white text-xs hover:bg-gray-50">24h</button>
            <button type="button" data-range="7d" class="topbar-range-btn inline-flex items-center px-3 py-1 rounded-md border border-gray-200 bg-white text-xs hover:bg-gray-50">7d</button>
            <button type="button" data-range="30d" class="topbar-range-btn inline-flex items-center px-3 py-1 rounded-md border border-gray-200 bg-white text-xs hover:bg-gray-50">30d</button>
            <div x-data="{ open:false }" class="relative">
                <button @click.prevent="open = !open" type="button" class="inline-flex items-center px-3 py-1 rounded-md border border-gray-200 bg-white text-xs hover:bg-gray-50">Custom ▾</button>
                <div x-show="open" @click.outside="open=false" x-cloak class="absolute z-50 mt-2 w-64 bg-white rounded-md shadow-lg p-3">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs text-gray-600">Start Date</label>
                        <input type="date" id="topbar-custom-start" class="block w-full h-9 px-3 py-2 border border-gray-200 rounded-md">
                        <label class="text-xs text-gray-600">End Date</label>
                        <input type="date" id="topbar-custom-end" class="block w-full h-9 px-3 py-2 border border-gray-200 rounded-md">
                        <div class="flex items-center gap-2 mt-2">
                            <button type="button" id="topbar-custom-apply" class="inline-flex items-center px-3 py-1.5 rounded-md bg-cvsu text-white text-sm">Apply</button>
                            <button type="button" @click.prevent="open=false" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 text-sm">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
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

            // Range preset buttons in topbar: dispatch date-range-updated with [start, end]
            function isoNow(){ return new Date().toISOString(); }
            function isoNowMinus(ms){ return new Date(Date.now() - ms).toISOString(); }

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
                            window.dispatchEvent(new CustomEvent('date-range-updated', { detail: [ start, end ] }));
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
                            // send YYYY-MM-DD strings; server expands to day bounds
                            window.dispatchEvent(new CustomEvent('date-range-updated', { detail: [ s, e ] }));
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
