<div class="p-3 sm:p-4" x-data="{ open: false, menu: false }">
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
    <div class="mb-3 flex justify-center">
        @if($found)
            <img src="{{ asset('assets/species/' . $found) }}" alt="{{ $species }}" class="w-28 h-20 object-fit"/>
        @else
            <svg width="112" height="80" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="rounded-md shadow-sm">
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

    <div class="grid grid-cols-1 gap-3">
        <div class="min-w-0 sm:col-span-2">
            
            <div class="mt-1 flex items-center gap-3">
                <div>
                    <div class="text-lg font-semibold text-cvsu-700 truncate" style="color: #1B651B;">{{ $species }}</div>
                </div>
            </div>

            <div class="mt-2 text-sm text-gray-600 leading-relaxed break-words hidden sm:block">{{ $reason }}</div>
            <div class="mt-2 text-sm text-gray-600 block sm:hidden">{{ Str::limit($reason, 80) }}</div>

            <div class="mt-2 text-sm text-gray-700 font-medium whitespace-normal break-words">{{ $action }}</div>


            
        </div>

        
    </div>

    <div class="mt-3 sm:hidden">
        <button type="button" x-on:click="open = !open" class="text-sm text-cvsu-700 hover:underline focus:outline-none">
            <span x-text="open ? 'Hide details' : 'View details'"></span>
        </button>

        <div x-show="open" x-cloak class="mt-3 text-sm text-gray-600 leading-relaxed">
            <div class="font-medium">Reason</div>
            <div class="mt-1">{{ $reason }}</div>
            <div class="mt-3 text-xs text-gray-400">This is a placeholder recommendation. Integration with the ClassifyFish service will provide dynamic results here.</div>
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
