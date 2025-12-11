<div>
    {{-- Connection status indicator --}}
    <div class="flex items-center justify-end gap-4 text-sm text-gray-600 mb-3">
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">Last updated: {{ $lastUpdated ? \Illuminate\Support\Carbon::parse($lastUpdated)->format('M d, Y H:i:s') : 'Never' }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if($connectionStatus === 'connected')
                <div class="relative w-3 h-3 flex items-center justify-center" aria-hidden="true">
                    <span class="absolute inset-0 m-auto inline-flex h-3 w-3 rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
                </div>
                <span class="font-medium text-emerald-600">Connected</span>
                <span class="sr-only"> Sensor online</span>
            @else
                <div class="relative w-3 h-3 flex items-center justify-center" aria-hidden="true">
                    <span class="absolute inset-0 m-auto inline-flex h-3 w-3 rounded-full bg-rose-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-400"></span>
                </div>
                <span class="font-medium text-rose-600">Disconnected</span>
                <span class="sr-only"> Sensor offline</span>
            @endif
        </div>
    </div>

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
        // Badge classes: base badge styling + status-specific classes
        $badgeBase = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold';
        if ($statusKey === 'disconnected') {
            $badgeClasses = $badgeBase . ' bg-amber-100 text-amber-800';
        } else {
            $badgeClasses = $badgeBase . ' ' . $this->overallClassesForStatus($status);
        }
    @endphp

    <div class="mb-3">
        <div>
            @if($statusKey !== 'disconnected')
                <span class="{{ $badgeClasses }}">
                    {{ $status }}
                </span>
                @if(!empty($reasonText))
                    <div class="mt-1 text-xs text-gray-500 truncate max-w-[18rem] sm:max-w-none">{{ $reasonText }}</div>
                @endif
            @else
                {{-- badge hidden when disconnected to avoid duplicate messaging (alert shown above) --}}
            @endif
        </div>
    </div>
    {{-- When disconnected, show a more prominent alert with troubleshooting link --}}
    @if(strtolower($overallStatus ?? $this->overallStatus) === 'disconnected')
        <div class="mb-4">
            <div class="rounded-md bg-amber-50 p-3 border border-amber-100">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm text-amber-700">
                        Sensor offline. Check power, battery, or network connection.
                        <a href="#troubleshooting" class="ml-2 text-sm text-amber-700 underline">Troubleshooting tips</a>
                    </div>
                </div>
            </div>
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

        {{-- Temperature (°C) --}}
        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($tempStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Temperature (°C)
                @if(!is_null($this->temperatureDelta))
                    <div class="mt-1 text-xs">
                        <span class="inline-flex items-center gap-1 {{ $this->trendClass($this->temperatureDelta) }}">
                            @if($this->temperatureDelta > 0)
                                {{-- up arrow --}}
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 4a1 1 0 01.707.293l4 4a1 1 0 11-1.414 1.414L11 7.414V16a1 1 0 11-2 0V7.414L6.707 9.707A1 1 0 015.293 8.293l4-4A1 1 0 0110 4z" clip-rule="evenodd"/></svg>
                            @elseif($this->temperatureDelta < 0)
                                {{-- down arrow --}}
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 16a1 1 0 01-.707-.293l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 112 0v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4A1 1 0 0110 16z" clip-rule="evenodd"/></svg>
                            @else
                                {{-- neutral dot --}}
                                <svg class="w-2 h-2 rounded-full" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><circle cx="4" cy="4" r="3"/></svg>
                            @endif
                            <span>{{ $this->formatDelta('temperature', $this->temperatureDelta) }}</span>
                        </span>
                    </div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($tempStatus) }}">{{ $this->temperature !== null ? number_format($this->temperature, 1) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('temperature') }}</span>
            </dd>
            
            @if(!empty($temperatureClassification['label']))
                <div class="mt-2 text-xs">
                    <span class="font-medium {{ $temperatureClassification['color'] ?? 'text-gray-700' }}">{{ $temperatureClassification['label'] }}</span>
                    @if(!empty($temperatureClassification['note']))
                        <span class="ml-2 text-gray-500">{{ $temperatureClassification['note'] }}</span>
                    @endif
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($temperatureSparklinePath))
                    <svg class="w-20 h-6" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $temperatureSparklinePath }}" fill="none" stroke="var(--wq-series-temperature)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        {{-- Salinity (ppt) --}}
        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($salStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Salinity (ppt)
                @if(!is_null($this->salinityDelta))
                    <div class="mt-1 text-xs">
                        <span class="inline-flex items-center gap-1 {{ $this->trendClass($this->salinityDelta) }}">
                            @if($this->salinityDelta > 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 4a1 1 0 01.707.293l4 4a1 1 0 11-1.414 1.414L11 7.414V16a1 1 0 11-2 0V7.414L6.707 9.707A1 1 0 015.293 8.293l4-4A1 1 0 0110 4z" clip-rule="evenodd"/></svg>
                            @elseif($this->salinityDelta < 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 16a1 1 0 01-.707-.293l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 112 0v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4A1 1 0 0110 16z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-2 h-2 rounded-full" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><circle cx="4" cy="4" r="3"/></svg>
                            @endif
                            <span>{{ $this->formatDelta('salinity', $this->salinityDelta) }}</span>
                        </span>
                    </div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($salStatus) }}">{{ $this->salinity !== null ? number_format($this->salinity, 2) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('salinity') }}</span>
            </dd>
            
            @if(!empty($salinityClassification['label']))
                <div class="mt-2 text-xs">
                    <span class="font-medium {{ $salinityClassification['color'] ?? 'text-gray-700' }}">{{ $salinityClassification['label'] }}</span>
                    @if(!empty($salinityClassification['note']))
                        <span class="ml-2 text-gray-500">{{ $salinityClassification['note'] }}</span>
                    @endif
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($salinitySparklinePath))
                    <svg class="w-20 h-6" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $salinitySparklinePath }}" fill="none" stroke="var(--wq-series-salinity)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        {{-- Dissolved Oxygen (mg/L) --}}
        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($doStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Dissolved Oxygen (mg/L)
                @if(!is_null($this->dissolvedOxygenDelta))
                    <div class="mt-1 text-xs">
                        <span class="inline-flex items-center gap-1 {{ $this->trendClass($this->dissolvedOxygenDelta) }}">
                            @if($this->dissolvedOxygenDelta > 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 4a1 1 0 01.707.293l4 4a1 1 0 11-1.414 1.414L11 7.414V16a1 1 0 11-2 0V7.414L6.707 9.707A1 1 0 015.293 8.293l4-4A1 1 0 0110 4z" clip-rule="evenodd"/></svg>
                            @elseif($this->dissolvedOxygenDelta < 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 16a1 1 0 01-.707-.293l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 112 0v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4A1 1 0 0110 16z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-2 h-2 rounded-full" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><circle cx="4" cy="4" r="3"/></svg>
                            @endif
                            <span>{{ $this->formatDelta('dissolved_oxygen', $this->dissolvedOxygenDelta) }}</span>
                        </span>
                    </div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($doStatus) }}">{{ $this->dissolved_oxygen !== null ? number_format($this->dissolved_oxygen, 1) : '-' }}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('dissolved_oxygen') }}</span>
            </dd>
            
            @if(!empty($dissolvedOxygenClassification['label']))
                <div class="mt-2 text-xs">
                    <span class="font-medium {{ $dissolvedOxygenClassification['color'] ?? 'text-gray-700' }}">{{ $dissolvedOxygenClassification['label'] }}</span>
                    @if(!empty($dissolvedOxygenClassification['note']))
                        <span class="ml-2 text-gray-500">{{ $dissolvedOxygenClassification['note'] }}</span>
                    @endif
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($doSparklinePath))
                    <svg class="w-20 h-6" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $doSparklinePath }}" fill="none" stroke="var(--wq-series-do)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        {{-- pH Level --}}
        <div class="flex flex-col items-center justify-center px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($phStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">pH Level
                @if(!is_null($this->phLevelDelta))
                    <div class="mt-1 text-xs">
                        <span class="inline-flex items-center gap-1 {{ $this->trendClass($this->phLevelDelta) }}">
                            @if($this->phLevelDelta > 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 4a1 1 0 01.707.293l4 4a1 1 0 11-1.414 1.414L11 7.414V16a1 1 0 11-2 0V7.414L6.707 9.707A1 1 0 015.293 8.293l4-4A1 1 0 0110 4z" clip-rule="evenodd"/></svg>
                            @elseif($this->phLevelDelta < 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 16a1 1 0 01-.707-.293l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 112 0v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4A1 1 0 0110 16z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-2 h-2 rounded-full" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><circle cx="4" cy="4" r="3"/></svg>
                            @endif
                            <span>{{ $this->formatDelta('ph_level', $this->phLevelDelta) }}</span>
                        </span>
                    </div>
                @endif
            </dt>

            <dd class="flex items-baseline gap-2">
                <span class="text-[clamp(1.5rem,4vw,3rem)] font-extrabold {{ $this->numberColorForStatus($phStatus) }}">{{ $this->ph_level !== null ? number_format($this->ph_level, 2) : '-'}}</span>
                <span class="text-sm text-gray-600">{{ $this->unitFor('ph_level') }}</span>
            </dd>
            
            @if(!empty($phClassification['label']))
                <div class="mt-2 text-xs">
                    <span class="font-medium {{ $phClassification['color'] ?? 'text-gray-700' }}">{{ $phClassification['label'] }}</span>
                    @if(!empty($phClassification['note']))
                        <span class="ml-2 text-gray-500">{{ $phClassification['note'] }}</span>
                    @endif
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($phSparklinePath))
                    <svg class="w-20 h-6" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $phSparklinePath }}" fill="none" stroke="var(--wq-series-ph)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>
    </dl>
</div>
