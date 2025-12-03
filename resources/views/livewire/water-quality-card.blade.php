<div>
    {{-- Overall status pill (aggregated) --}}
    <div class="mb-3 flex items-center justify-between">
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $this->overallClassesForStatus($overallStatus ?? $this->overallStatus) }}">
                {{ $overallStatus ?? $this->overallStatus }}
            </span>
        </div>
        <div class="text-xs text-gray-500">Based on current metrics</div>
    </div>
    @if(strtolower($overallStatus ?? $this->overallStatus) === 'disconnected')
        <div class="mb-4">
            <p class="text-xs text-amber-700">Check power or sensor cable</p>
        </div>
    @endif
    {{-- Mobile-first: 2 columns (2x2 grid) by default, expand to 4 columns on larger screens --}}
    <dl class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @php
            $tempStatus = $this->statusFor('temperature', $this->temperature);
            $salStatus = $this->statusFor('salinity', $this->salinity);
            $doStatus = $this->statusFor('dissolved_oxygen', $this->dissolved_oxygen);
            $phStatus = $this->statusFor('ph_level', $this->ph_level);
        @endphp

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 border {{ $this->classesForStatus($doStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Dissolved Oxygen</dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold text-cvsu-600">{{ $this->dissolved_oxygen !== null ? number_format($this->dissolved_oxygen, 1) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('dissolved_oxygen') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 border {{ $this->classesForStatus($tempStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Temperature</dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold text-cvsu-600">{{ $this->temperature !== null ? number_format($this->temperature, 1) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('temperature') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder (reserved slot) --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 border {{ $this->classesForStatus($phStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">pH</dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold text-cvsu-600">{{ $this->ph_level !== null ? number_format($this->ph_level, 2) : '-'}}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('ph_level') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 border {{ $this->classesForStatus($salStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Salinity</dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold text-cvsu-600">{{ $this->salinity !== null ? number_format($this->salinity, 2) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('salinity') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>
    </dl>
</div>
