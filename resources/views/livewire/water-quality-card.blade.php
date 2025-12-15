<div>
    {{-- Connection status moved to topbar; avoid duplicate indicator here --}}

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

    {{-- Contextual Alert Banner (replaces simple badge) --}}
    @if($statusKey !== 'disconnected' && $statusKey === 'warning')
        <div class="mb-4 rounded-lg bg-amber-50 border-l-4 border-amber-500 p-4" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-show="!compact" x-transition>
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-amber-800">⚠️ {{ $status }}</h3>
                    <p class="mt-1 text-sm text-amber-700">{{ $reasonText }}</p>
                    <div class="mt-3">
                        <a href="{{ route('historical-data') }}" class="inline-flex items-center text-sm font-medium text-amber-800 hover:text-amber-900 underline">
                            View details
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @elseif($statusKey !== 'disconnected' && $statusKey === 'critical')
        <div class="mb-4 rounded-lg bg-red-50 border-l-4 border-red-500 p-4" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-show="!compact" x-transition>
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-800">🔴 {{ $status }}</h3>
                    <p class="mt-1 text-sm text-red-700">{{ $reasonText }}</p>
                    <div class="mt-3">
                        <a href="{{ route('historical-data') }}" class="inline-flex items-center text-sm font-medium text-red-800 hover:text-red-900 underline">
                            View details
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @elseif($statusKey !== 'disconnected' && $statusKey === 'healthy')
        <div class="mb-4 rounded-lg bg-green-50 border-l-4 border-green-500 p-4" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-show="!compact" x-transition>
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-green-800">🟢 {{ $status }}</h3>
                    <p class="mt-1 text-sm text-green-700">{{ $reasonText }}</p>
                </div>
            </div>
        </div>
    @endif
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
    <dl class="grid grid-cols-2 md:grid-cols-4 transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'gap-4' : 'gap-6'">
        @php
            $tempStatus = $this->statusFor('temperature', $this->temperature);
            $salStatus = $this->statusFor('salinity', $this->salinity);
            $doStatus = $this->statusFor('dissolved_oxygen', $this->dissolved_oxygen);
            $phStatus = $this->statusFor('ph_level', $this->ph_level);
        @endphp

        {{-- Temperature (°C) --}}
        <div class="flex flex-col items-center justify-center text-center rounded-2xl transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'" class="{{ $this->classesForStatus($tempStatus) }}">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Temperature (°C)</dt>

            <dd class="flex flex-col items-center gap-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-extrabold transition-all duration-300 {{ $this->numberColorForStatus($tempStatus) }}" x-bind:class="compact ? 'text-2xl' : 'text-[clamp(1.5rem,4vw,3rem)]'">{{ $this->temperature !== null ? number_format($this->temperature, 1) : '-' }}</span>
                    <span class="text-sm text-gray-600">{{ $this->unitFor('temperature') }}</span>
                </div>
                @if(!is_null($this->temperatureDelta))
                    <div class="text-xs text-gray-500" x-show="!compact">
                        <span class="inline-flex items-center gap-1 {{ $this->trendClass($this->temperatureDelta) }}">
                            @if($this->temperatureDelta > 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 4a1 1 0 01.707.293l4 4a1 1 0 11-1.414 1.414L11 7.414V16a1 1 0 11-2 0V7.414L6.707 9.707A1 1 0 015.293 8.293l4-4A1 1 0 0110 4z" clip-rule="evenodd"/></svg>
                            @elseif($this->temperatureDelta < 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 16a1 1 0 01-.707-.293l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 112 0v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4A1 1 0 0110 16z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-2 h-2 rounded-full" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><circle cx="4" cy="4" r="3"/></svg>
                            @endif
                            <span>{{ $this->formatDelta('temperature', $this->temperatureDelta) }}</span>
                        </span>
                    </div>
                @endif
            </dd>
            
                @if(!empty($temperatureClassification['label']))
                <div class="mt-2 flex flex-col items-center gap-1" x-show="!compact">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full {{ $this->dotClassForStatus($tempStatus) }}" aria-hidden="true"></span>
                            <span class="text-xs font-semibold {{ $this->badgeClassForStatus($tempStatus) }} px-2.5 py-0.5 rounded-full">
                                {{ ucfirst($tempStatus) }}
                            </span>
                    </span>
                    {{-- simplified: only show badge (bilingual) and dot; remove duplicate label and technical notes --}}
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
        <div class="flex flex-col items-center justify-center text-center rounded-2xl transition-all duration-300 {{ $this->classesForStatus($salStatus) }}" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Salinity (ppt)</dt>

            <dd class="flex flex-col items-center gap-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-extrabold {{ $this->numberColorForStatus($salStatus) }} transition-all duration-300" x-bind:class="compact ? 'text-2xl' : 'text-[clamp(1.5rem,4vw,3rem)]'">{{ $this->salinity !== null ? number_format($this->salinity, 2) : '-' }}</span>
                    <span class="text-sm text-gray-600">{{ $this->unitFor('salinity') }}</span>
                </div>
                @if(!is_null($this->salinityDelta))
                    <div class="text-xs text-gray-500" x-show="!compact">
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
            </dd>
            
                @if(!empty($salinityClassification['label']))
                <div class="mt-2 flex flex-col items-center gap-1" x-show="!compact">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full {{ $this->dotClassForStatus($salStatus) }}" aria-hidden="true"></span>
                        <span class="text-xs font-semibold {{ $this->badgeClassForStatus($salStatus) }} px-2.5 py-0.5 rounded-full">
                            {{ ucfirst($salStatus) }}
                        </span>
                    </span>
                    {{-- simplified: only show badge (bilingual) and dot; remove duplicate label and technical notes --}}
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500" x-show="!compact">
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
        <div class="flex flex-col items-center justify-center text-center rounded-2xl transition-all duration-300 {{ $this->classesForStatus($doStatus) }}" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Dissolved Oxygen (mg/L)</dt>

            <dd class="flex flex-col items-center gap-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-extrabold {{ $this->numberColorForStatus($doStatus) }} transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'text-2xl' : 'text-[clamp(1.5rem,4vw,3rem)]'">{{ $this->dissolved_oxygen !== null ? number_format($this->dissolved_oxygen, 1) : '-' }}</span>
                    <span class="text-sm text-gray-600">{{ $this->unitFor('dissolved_oxygen') }}</span>
                </div>
                @if(!is_null($this->dissolvedOxygenDelta))
                    <div class="text-xs text-gray-500" x-show="!compact">
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
            </dd>
            
                @if(!empty($dissolvedOxygenClassification['label']))
                <div class="mt-2 flex flex-col items-center gap-1" x-show="!compact">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full {{ $this->dotClassForStatus($doStatus) }}" aria-hidden="true"></span>
                        <span class="text-xs font-semibold {{ $this->badgeClassForStatus($doStatus) }} px-2.5 py-0.5 rounded-full">
                            {{ ucfirst($doStatus) }}
                        </span>
                    </span>
                    {{-- simplified: only show badge (bilingual) and dot; remove duplicate label and technical notes --}}
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500" x-show="!compact">
                @if(!empty($doSparklinePath))
                    <svg class="w-20 h-6" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $doSparklinePath }}" fill="none" stroke="var(--wq-series-do)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        {{-- pH --}}
        <div class="flex flex-col items-center justify-center text-center rounded-2xl transition-all duration-300 {{ $this->classesForStatus($phStatus) }}" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">pH</dt>

            <dd class="flex flex-col items-center gap-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-extrabold {{ $this->numberColorForStatus($phStatus) }} transition-all duration-300" x-bind:class="compact ? 'text-2xl' : 'text-[clamp(1.5rem,4vw,3rem)]'">{{ $this->ph_level !== null ? number_format($this->ph_level, 2) : '-'}}</span>
                    <span class="text-sm text-gray-600">{{ $this->unitFor('ph_level') }}</span>
                </div>
                @if(!is_null($this->phLevelDelta))
                    <div class="text-xs text-gray-500" x-show="!compact">
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
            </dd>
            
                @if(!empty($phClassification['label']))
                <div class="mt-2 flex flex-col items-center gap-1" x-show="!compact">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full {{ $this->dotClassForStatus($phStatus) }}" aria-hidden="true"></span>
                        <span class="text-xs font-semibold {{ $this->badgeClassForStatus($phStatus) }} px-2.5 py-0.5 rounded-full">
                            {{ ucfirst($phStatus) }}
                        </span>
                    </span>
                    {{-- simplified: only show badge (bilingual) and dot; remove duplicate label and technical notes --}}
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500" x-show="!compact">
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
