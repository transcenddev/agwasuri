<div>
    {{-- Overall status pill (aggregated) --}}
    @php
        $status = $overallStatus ?? $this->overallStatus;
        $statusKey = strtolower($status ?? '');
        $reasons = [
            'healthy' => 'All parameters within safe range',
            'warning' => 'Dissolved oxygen below recommended level',
            'critical' => 'Immediate action needed: DO critically low',
        ];
        $reasonText = $reasons[$statusKey] ?? '';
    @endphp

    <div class="mb-3">
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $this->overallClassesForStatus($status) }}">
                {{ $status }}
            </span>
            @if(!empty($reasonText))
                <div class="mt-1 text-xs text-gray-500 truncate max-w-[18rem] sm:max-w-none">{{ $reasonText }}</div>
            @endif
        </div>
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

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($doStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Dissolved Oxygen
                @if(!is_null($this->dissolvedOxygenDelta))
                    <div class="mt-1 text-xs {{ $this->trendClass($this->dissolvedOxygenDelta) }}">{{ $this->formatDelta('dissolved_oxygen', $this->dissolvedOxygenDelta) }}</div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($doStatus) }}">{{ $this->dissolved_oxygen !== null ? number_format($this->dissolved_oxygen, 1) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('dissolved_oxygen') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($tempStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Temperature
                @if(!is_null($this->temperatureDelta))
                    <div class="mt-1 text-xs {{ $this->trendClass($this->temperatureDelta) }}">{{ $this->formatDelta('temperature', $this->temperatureDelta) }}</div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($tempStatus) }}">{{ $this->temperature !== null ? number_format($this->temperature, 1) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('temperature') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder (reserved slot) --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($phStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">pH
                @if(!is_null($this->phLevelDelta))
                    <div class="mt-1 text-xs {{ $this->trendClass($this->phLevelDelta) }}">{{ $this->formatDelta('ph_level', $this->phLevelDelta) }}</div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($phStatus) }}">{{ $this->ph_level !== null ? number_format($this->ph_level, 2) : '-'}}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('ph_level') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($salStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Salinity
                @if(!is_null($this->salinityDelta))
                    <div class="mt-1 text-xs {{ $this->trendClass($this->salinityDelta) }}">{{ $this->formatDelta('salinity', $this->salinityDelta) }}</div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($salStatus) }}">{{ $this->salinity !== null ? number_format($this->salinity, 2) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('salinity') }}</span>
            </dd>

            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                {{-- Trend / sparkline placeholder --}}
                <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
            </div>
        </div>
    </dl>
</div>
