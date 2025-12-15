<div x-data="{ open: false, menu: false, compact: false, showDetails: false }" @toggle-compact-view.window="compact = !compact">
    @php
        // Prepare species image once and reuse
        $base = trim(preg_replace('/\(.+\)/', '', $species));
        $slug = Str::slug($base);
        $found = null;
        foreach (['webp','png','jpg','jpeg'] as $ext) {
            $candidate = public_path('assets/species/' . $slug . '.' . $ext);
            if (file_exists($candidate)) { $found = $slug . '.' . $ext; break; }
        }
    @endphp

    {{-- Prominent species image above card content --}}
    <div class="flex justify-center transition-all duration-300" x-bind:class="compact ? 'mb-1' : 'mb-3'">
        @if($found)
            <img src="{{ asset('assets/species/' . $found) }}" alt="{{ $species }}" class="object-contain rounded-lg transition-all duration-300" x-bind:class="compact ? 'w-16 h-16' : 'w-28 h-20'"/>
        @else
            <svg viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="rounded-md transition-all duration-300" x-bind:class="compact ? 'w-16 h-16' : 'w-28 h-20'">
                <defs>
                    <linearGradient id="g1" x1="0" x2="1">
                        <stop offset="0%" stop-color="#34D399" />
                        <stop offset="100%" stop-color="#10B981" />
                    </linearGradient>
                </defs>
                <path d="M4 16c6-8 18-8 28-8 0 0-2.5 4-2.5 8S32 24 32 24C22 24 10 24 4 16z" fill="url(#g1)" opacity="0.95" />
                <circle cx="34" cy="8" r="3" fill="#F97316" />
            </svg>
        @endif
    </div>

    {{-- Species title with status badge --}}
    <div class="text-center" x-bind:class="compact ? 'mb-1' : 'mb-2'">
        <h3 class="font-bold text-cvsu-700 truncate transition-all duration-300" style="color: #1B651B;" x-bind:class="compact ? 'text-sm' : 'text-lg'">{{ $species }}</h3>
        <div class="flex items-center justify-center gap-2 mt-1" x-show="!compact" x-transition>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Recommended
            </span>
        </div>
    </div>

    {{-- Action summary - always visible --}}
    <div class="transition-all duration-300" x-bind:class="compact ? 'mt-1' : 'mt-2'">
        <p class="font-semibold text-center transition-all duration-300" x-bind:class="compact ? 'text-xs text-gray-700' : 'text-sm text-gray-900'">{{ $action }}</p>
    </div>

    {{-- Collapsible reason details --}}
    <div x-show="!compact" x-transition class="mt-3">
        <button @click="showDetails = !showDetails" class="w-full flex items-center justify-center gap-1 text-xs text-cvsu-700 hover:text-cvsu-800 focus:outline-none">
            <svg class="w-4 h-4 transition-transform duration-200" :class="showDetails ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <span x-text="showDetails ? 'Hide details' : 'Why this species?'"></span>
        </button>
        
        <div x-show="showDetails" x-collapse class="mt-2 p-3 bg-green-50 rounded-lg border border-green-100">
            <p class="text-sm text-gray-700 leading-relaxed">{{ $reason }}</p>
        </div>
    </div>

    {{-- Mobile collapsible (legacy) --}}
    <div class="mt-3 sm:hidden" x-show="!compact">
        <button type="button" x-on:click="open = !open" class="text-sm text-cvsu-700 hover:underline focus:outline-none">
            <span x-text="open ? 'Hide details' : 'View details'"></span>
        </button>

        <div x-show="open" x-cloak class="mt-3 text-sm text-gray-600 leading-relaxed">
            <div class="font-medium">Reason</div>
            <div class="mt-1">{{ $reason }}</div>
        </div>
    </div>
</div>

<script>
    (function(){
        const user = '{{ request()->get('user') ?? session('selected_user', auth()->id()) }}';
        async function fetchLatest() {
            try {
                const res = await fetch(`/api/water-quality/latest?user=${encodeURIComponent(user)}&limit=24`, { credentials: 'same-origin' });
                if (!res.ok) return null;
                return await res.json();
            } catch (e) { return null; }
        }

        function renderSpark(id, values, color) {
            const container = document.getElementById(id);
            if (!container) return;
            const w = container.clientWidth || 120;
            const h = 32;
            const max = Math.max(...values, 1);
            const min = Math.min(...values, 0);
            const range = (max - min) || 1;
            const pts = values.map((v,i) => `${Math.round(i*(w/(values.length-1)||0))},${Math.round(h - ((v - min)/range)*h)}`).join(' ');
            container.innerHTML = `<svg width="${w}" height="${h}" viewBox="0 0 ${w} ${h}" preserveAspectRatio="none"><polyline fill="none" stroke="${color}" stroke-width="1.6" points="${pts}" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const json = await fetchLatest();
            if (!json) return;
            // json.rows expected: array of rows with keys temperature,dissolved_oxygen,salinity
            const rows = json.rows || [];
            const temps = rows.map(r => Number(r.temperature)).filter(v => !isNaN(v));
            const dos = rows.map(r => Number(r.dissolved_oxygen)).filter(v => !isNaN(v));
            const sals = rows.map(r => Number(r.salinity)).filter(v => !isNaN(v));
            if (temps.length) renderSpark('spark-temp', temps.slice(-20), '#EF4444');
            if (dos.length) renderSpark('spark-do', dos.slice(-20), '#06B6D4');
            if (sals.length) renderSpark('spark-salinity', sals.slice(-20), '#10B981');
        });
    })();
</script>
