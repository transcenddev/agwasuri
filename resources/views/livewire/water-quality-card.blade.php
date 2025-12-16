<div>
    {{-- Connection status moved to topbar; avoid duplicate indicator here --}}

    @php
        $status = $overallStatus ?? $this->overallStatus;
        $statusKey = strtolower($status ?? '');
    @endphp

    {{-- Contextual Alert Banner - ONLY show when score < 70% (critical state) --}}
    @if($statusKey !== 'disconnected' && $this->overallScore < 70)
        <div class="mb-4 rounded-lg bg-red-50 border-l-4 border-red-600 p-4 animate-pulse" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-show="!compact" x-transition>
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-800">🚨 CRITICAL: Multiple parameters out of range</h3>
                    <p class="mt-1 text-sm text-red-700">Immediate action required to prevent fish mortality and productivity loss.</p>
                    <div class="mt-3">
                        <a href="{{ route('historical-data') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                            Take Action Now
                            <svg class="ml-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
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
    {{-- Mobile-first: 1 col mobile, 2 cols tablet, 5 cols desktop for all KPI cards in one row --}}
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'gap-4' : 'gap-6'">
        @php
            $scoreBadge = $this->scoreStatusBadge();
            $tempStatus = $this->statusFor('temperature', $this->temperature);
            $salStatus = $this->statusFor('salinity', $this->salinity);
            $doStatus = $this->statusFor('dissolved_oxygen', $this->dissolved_oxygen);
            $phStatus = $this->statusFor('ph_level', $this->ph_level);
        @endphp

        {{-- Water Quality Score Card (1st KPI) - Inspired by Plant Health Design --}}
        <div class="flex flex-col items-center justify-center text-center relative overflow-hidden rounded-3xl shadow-xl transition-all duration-300 {{ $this->overallScore < 70 ? 'animate-pulse' : '' }}"
             x-data="{ compact: false }"
             @toggle-compact-view.window="compact = !compact"
             x-bind:class="compact ? 'px-3 py-4' : 'px-5 py-6'"
             style="background: linear-gradient(135deg, {{ $this->overallScore >= 90 ? '#1B651B 0%, #0f4910 100%' : ($this->overallScore >= 70 ? '#f59e0b 0%, #d97706 100%' : '#dc2626 0%, #b91c1c 100%') }});">
            {{-- Decorative gradient overlay --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>

            {{-- Icon --}}
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mb-3 relative z-10">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <dd class="flex flex-col items-center gap-2 mb-3 relative z-10">
                <div class="flex items-baseline gap-1">
                    <span class="font-black text-white transition-all duration-300" x-bind:class="compact ? 'text-4xl' : 'text-5xl'">{{ $this->overallScore }}</span>
                    <span class="text-2xl font-bold text-white opacity-90">%</span>
                </div>

                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white bg-opacity-20 text-white backdrop-blur-sm uppercase tracking-wide">
                    {{ is_array($scoreBadge) ? $scoreBadge['text'] : $scoreBadge }}
                </span>
            </dd>

            <dt class="text-sm font-semibold text-white opacity-90 mb-2 relative z-10">Water Quality Score</dt>

            <p class="text-xs text-white opacity-80 leading-relaxed max-w-[200px] relative z-10">
                {{ $this->overallScore >= 90 ? 'All parameters optimal.' : ($this->overallScore >= 70 ? 'Some parameters need attention.' : 'Immediate action required.') }}
            </p>
        </div>

        {{-- Temperature (°C) --}}
        <div class="flex flex-col items-center justify-center text-center bg-white rounded-2xl shadow-md transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'">
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
                    @php
                        $tempBadgeClass = match(strtoupper($temperatureClassification['label'])) {
                            'BEST' => 'bg-green-100 text-green-800',
                            'GOOD' => 'bg-blue-100 text-blue-800',
                            'BAD' => 'bg-yellow-100 text-yellow-800',
                            'HARM' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-600'
                        };
                        $tempSparkClass = match(strtoupper($temperatureClassification['label'])) {
                            'BEST' => 'text-green-500',
                            'GOOD' => 'text-blue-500',
                            'BAD' => 'text-yellow-500',
                            'HARM' => 'text-red-500',
                            default => 'text-gray-400'
                        };
                    @endphp
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $tempBadgeClass }}">
                        {{ $temperatureClassification['label'] }}
                    </span>
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($temperatureSparklinePath))
                    <svg class="w-20 h-6 {{ $tempSparkClass ?? 'text-gray-400' }}" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $temperatureSparklinePath }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        {{-- Salinity (ppt) --}}
        <div class="flex flex-col items-center justify-center text-center bg-white rounded-2xl shadow-md transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Salinity (ppt)</dt>

            <dd class="flex flex-col items-center gap-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-extrabold transition-all duration-300 {{ $this->numberColorForStatus($salStatus) }}" x-bind:class="compact ? 'text-2xl' : 'text-[clamp(1.5rem,4vw,3rem)]'">{{ $this->salinity !== null ? number_format($this->salinity, 2) : '-' }}</span>
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
                    @php
                        $salBadgeClass = match(strtoupper($salinityClassification['label'])) {
                            'BEST' => 'bg-green-100 text-green-800',
                            'GOOD' => 'bg-blue-100 text-blue-800',
                            'BAD' => 'bg-yellow-100 text-yellow-800',
                            'HARM' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-600'
                        };
                        $salSparkClass = match(strtoupper($salinityClassification['label'])) {
                            'BEST' => 'text-green-500',
                            'GOOD' => 'text-blue-500',
                            'BAD' => 'text-yellow-500',
                            'HARM' => 'text-red-500',
                            default => 'text-gray-400'
                        };
                    @endphp
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $salBadgeClass }}">
                        {{ $salinityClassification['label'] }}
                    </span>
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($salinitySparklinePath))
                    <svg class="w-20 h-6 {{ $salSparkClass ?? 'text-gray-400' }}" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $salinitySparklinePath }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        {{-- Dissolved Oxygen (mg/L) --}}
        <div class="flex flex-col items-center justify-center text-center bg-white rounded-2xl shadow-md transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'">
            <dt class="order-last text-sm sm:text-base font-medium text-gray-500">Dissolved Oxygen (mg/L)</dt>

            <dd class="flex flex-col items-center gap-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-extrabold transition-all duration-300 {{ $this->numberColorForStatus($doStatus) }}" x-bind:class="compact ? 'text-2xl' : 'text-[clamp(1.5rem,4vw,3rem)]'">{{ $this->dissolved_oxygen !== null ? number_format($this->dissolved_oxygen, 1) : '-' }}</span>
                    <span class="text-sm text-gray-600">{{ $this->unitFor('dissolved_oxygen') }}</span>
                </div>
                @if(!is_null($this->dissolvedOxygenDelta))
                    <div class="text-xs text-gray-500" x-show="!compact">
                        <span class="inline-flex items-center gap-1 {{ $this->trendClass($this->dissolvedOxygenDelta) }}">
                            @if($this->dissolvedOxygenDelta > 0)
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 4a1 1 0 01.707.293l4 4a1 1 0 11-1.414 1.414L11 7.414V16a1 1 0 11-2 0V7.414L6.707 9.707A1 1 0 15.293 8.293l4-4A1 1 0 0110 4z" clip-rule="evenodd"/></svg>
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
                    @php
                        $doBadgeClass = match(strtoupper($dissolvedOxygenClassification['label'])) {
                            'BEST' => 'bg-green-100 text-green-800',
                            'GOOD' => 'bg-blue-100 text-blue-800',
                            'BAD' => 'bg-yellow-100 text-yellow-800',
                            'HARM' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-600'
                        };
                        $doSparkClass = match(strtoupper($dissolvedOxygenClassification['label'])) {
                            'BEST' => 'text-green-500',
                            'GOOD' => 'text-blue-500',
                            'BAD' => 'text-yellow-500',
                            'HARM' => 'text-red-500',
                            default => 'text-gray-400'
                        };
                    @endphp
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $doBadgeClass }}">
                        {{ $dissolvedOxygenClassification['label'] }}
                    </span>
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($doSparklinePath))
                    <svg class="w-20 h-6 {{ $doSparkClass ?? 'text-gray-400' }}" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $doSparklinePath }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        {{-- pH Level --}}
        <div class="flex flex-col items-center justify-center text-center bg-white rounded-2xl shadow-md transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'px-3 py-4 h-32' : 'px-5 py-6 sm:py-6 min-h-24'">
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
                <div class="mt-2 flex flex-col items-center gap-1" x-show="!compact">
                    @php
                        $phBadgeClass = match(strtoupper($phClassification['label'])) {
                            'BEST' => 'bg-green-100 text-green-800',
                            'GOOD' => 'bg-blue-100 text-blue-800',
                            'BAD' => 'bg-yellow-100 text-yellow-800',
                            'HARM' => 'bg-red-100 text-red-800',
                            default => 'text-gray-700'
                        };
                        $phSparkClass = match(strtoupper($phClassification['label'])) {
                            'BEST' => 'text-green-500',
                            'GOOD' => 'text-blue-500',
                            'BAD' => 'text-yellow-500',
                            'HARM' => 'text-red-500',
                            default => 'text-gray-400'
                        };
                    @endphp
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $phBadgeClass }}">{{ $phClassification['label'] }}</span>
                </div>
            @endif
            <div class="mt-3 w-full flex items-center justify-center text-xs text-gray-500">
                @if(!empty($phSparklinePath))
                    <svg class="w-20 h-6 {{ $phSparkClass ?? 'text-gray-400' }}" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $phSparklinePath }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                    </svg>
                @else
                    <div class="w-20 h-6 bg-white/0" aria-hidden="true"></div>
                @endif
            </div>
        </div>
    </dl>
</div>

