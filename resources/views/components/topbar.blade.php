<div class="w-full py-2 bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-slate-700 relative z-10 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between gap-4">
        {{-- Left: pond selector --}}
        <div class="flex items-center gap-3">
            <label for="pond-select" class="sr-only">Select pond</label>
            @php
                $users = \App\Models\User::orderBy('user_id')->limit(50)->get();
                $sel = request()->get('user') ?? session('selected_user', auth()->id());
            @endphp
            <select id="pond-select" class="rounded-md border-gray-200 bg-white text-sm px-3 py-2 shadow-sm" aria-label="Select pond">
                @foreach($users as $u)
                    <option value="{{ $u->user_id }}" {{ $u->user_id == $sel ? 'selected' : '' }}>{{ $u->name ?? 'User ' . $u->user_id }}</option>
                @endforeach
            </select>
        </div>

        {{-- Middle: last-updated (relative) --}}
        <div class="flex-1 text-center">
            <div id="topbar-last-updated" class="text-sm text-gray-500">Last updated: <span id="topbar-last-updated-text">—</span></div>
        </div>

        {{-- Right: connection status --}}
        <div class="flex items-center gap-2">
            <div id="topbar-conn" role="status" aria-live="polite" class="flex items-center gap-2 text-sm text-gray-600">
                <span id="topbar-conn-dot" class="w-2 h-2 rounded-full bg-gray-300" aria-hidden="true"></span>
                <span id="topbar-conn-label">Unknown</span>
            </div>
        </div>
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
        })();
    </script>
</div>
