<div>
    @if($items->isEmpty())
        <div class="text-sm text-gray-500 p-3">No recent measurements</div>
    @else
        <div>
            {{-- Card header legend (4-tier system) --}}
            <!-- <div class="flex items-center justify-between mb-2">
                <div></div>
                <div class="flex items-center gap-3 text-xs text-gray-600">
                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-600"></span> BEST</div>
                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-600"></span> GOOD</div>
                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-yellow-500"></span> BAD</div>
                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-600"></span> HARM</div>
                </div>
            </div> -->

            {{-- Desktop table (lg+) --}}
            <div class="hidden lg:block rounded-md overflow-hidden border border-gray-100 shadow-sm">
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-0">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-sm font-medium text-gray-700 text-left sticky top-0 bg-white z-10 border-b border-gray-100">Recorded At</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-700 text-center sticky top-0 bg-white z-10 border-b border-gray-100">Status</th>
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-temperature)">Temperature (°C)</th>
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-salinity)">Salinity (ppt)</th>
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-do)">Dissolved Oxygen (mg/L)</th>
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-ph)">pH</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $row)
                                        @php
                                        $s = strtoupper($row->ui_status ?? 'GOOD');
                                        $statusBorderClass = $row->statusBorderClass ?? match($s) {
                                            'HARM', 'HARMFUL' => 'border-l-4 border-red-200',
                                            'BAD' => 'border-l-4 border-amber-100',
                                            'GOOD' => 'border-l-4 border-blue-100',
                                            'BEST' => 'border-l-4 border-green-100',
                                            default => 'border-l-4 border-gray-100'
                                        };

                                        $badgeClass = $row->badgeClass ?? match($s) {
                                            'HARM','HARMFUL' => 'text-red-700 bg-red-50',
                                            'BAD' => 'text-amber-700 bg-amber-50',
                                            'GOOD' => 'text-blue-700 bg-blue-50',
                                            'BEST' => 'text-green-700 bg-green-50',
                                            default => 'text-gray-700 bg-gray-50'
                                        };

                                        $valueColor = match($s) {
                                            'HARM','HARMFUL' => 'text-red-600',
                                            'BAD' => 'text-amber-600',
                                            'GOOD' => 'text-blue-600',
                                            'BEST' => 'text-green-600',
                                            default => 'text-gray-700'
                                        };

                                        $dotClass = match($s) {
                                            'HARM','HARMFUL' => 'bg-red-600',
                                            'BAD' => 'bg-amber-500',
                                            'GOOD' => 'bg-blue-500',
                                            'BEST' => 'bg-green-600',
                                            default => 'bg-gray-400'
                                        };

                                        $tempClass = $row->tempValueClass ?? $valueColor;
                                        $salClass = $row->salValueClass ?? $valueColor;
                                        $doClass = $row->doValueClass ?? $valueColor;
                                        $phClass = $row->phValueClass ?? $valueColor;
                                    @endphp

                                        <tr class="bg-white odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 {{ $statusBorderClass }}">{{ optional($row->recorded_at)->format('Y-m-d H:i') ?? $row->recorded_at }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 text-center">
                                                <div x-data="{ open:false }" class="inline-block relative" @mouseenter="open=true" @mouseleave="open=false" @focusin="open=true" @focusout="open=false">
                                                    <span tabindex="0" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                        <span class="inline-block w-2 h-2 rounded-full mr-2 {{ $dotClass }}" aria-hidden="true"></span>
                                                        {{ $s }}
                                                    </span>
                                                    @if(!empty($row->issuesText))
                                                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded shadow-sm text-xs text-gray-700 p-2 z-50">
                                                            <div class="font-medium mb-1">Causes:</div>
                                                            <div>{{ $row->issuesText }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 text-sm text-right">
                                                <span class="font-medium {{ $tempClass }}">{{ isset($row->temperature) ? number_format($row->temperature, 1) : '—' }}</span>
                                            </td>

                                            <td class="px-6 py-3 text-sm text-right">
                                                <span class="font-medium {{ $salClass }}">{{ isset($row->salinity) ? number_format($row->salinity, 1) : '—' }}</span>
                                            </td>

                                            <td class="px-6 py-3 text-sm text-right">
                                                <span class="font-medium {{ $doClass }}">{{ isset($row->dissolved_oxygen) ? number_format($row->dissolved_oxygen, 2) : '—' }}</span>
                                            </td>

                                            <td class="px-6 py-3 text-sm text-right">
                                                <span class="font-medium {{ $phClass }}">{{ isset($row->ph_level) ? number_format($row->ph_level, 2) : '—' }}</span>
                                            </td>
                                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile stacked list (visible up to lg) --}}
            <div class="block lg:hidden space-y-3">
                @foreach($items as $row)
                    @php
                        $s = strtoupper($row->ui_status ?? 'GOOD');
                        $statusClass = match($s) {
                            'HARM','HARMFUL' => 'border-l-4 border-red-200 bg-white',
                            'BAD' => 'border-l-4 border-amber-100 bg-white',
                            'GOOD' => 'border-l-4 border-blue-100 bg-white',
                            'BEST' => 'border-l-4 border-green-100 bg-white',
                            default => 'border-l-4 border-gray-100 bg-white'
                        };
                        $badgeClass = match($s) {
                            'HARM','HARMFUL' => 'text-red-700 bg-red-50',
                            'BAD' => 'text-amber-700 bg-amber-50',
                            'GOOD' => 'text-blue-700 bg-blue-50',
                            'BEST' => 'text-green-700 bg-green-50',
                            default => 'text-gray-700 bg-gray-50'
                        };
                        $valueColor = match($s) {
                            'HARM','HARMFUL' => 'text-red-600',
                            'BAD' => 'text-amber-600',
                            'GOOD' => 'text-blue-600',
                            'BEST' => 'text-green-600',
                            default => 'text-gray-700'
                        };

                        $tempClass = $row->tempValueClass ?? $valueColor;
                        $salClass = $row->salValueClass ?? $valueColor;
                        $doClass = $row->doValueClass ?? $valueColor;
                        $phClass = $row->phValueClass ?? $valueColor;
                    @endphp

                    <div class="bg-white border rounded-md p-3 hover:bg-gray-50 {{ $statusClass }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div x-data="{ open:false }" class="relative inline-block" @click.outside="open=false">
                                    <button @click="open = !open" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        <span class="inline-block w-2 h-2 rounded-full mr-2 {{ $dotClass }}" aria-hidden="true"></span>
                                        {{ $s }}
                                    </button>
                                    @if(!empty($row->issuesText))
                                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded shadow-sm text-xs text-gray-700 p-2 z-50">
                                            <div class="font-medium mb-1">Causes:</div>
                                            <div>{{ $row->issuesText }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-sm font-medium text-gray-700">{{ optional($row->recorded_at)->format('Y-m-d H:i') ?? $row->recorded_at }}</div>
                            </div>
                        </div>

                        <div class="mt-2 grid grid-cols-2 gap-3 text-sm text-gray-700">
                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>🌡️</span> <span>Temperature (°C)</span></div>
                                <div class="font-semibold">
                                    <span class="{{ $row->tempValueClass ?? '' }}">{{ isset($row->temperature) ? number_format($row->temperature,1) : '—' }}</span>
                                </div>
                            </div>

                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>🧂</span> <span>Salinity (ppt)</span></div>
                                <div class="font-semibold">
                                    <span class="{{ $row->salValueClass ?? '' }}">{{ isset($row->salinity) ? number_format($row->salinity,1) : '—' }}</span>
                                </div>
                            </div>

                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>💧</span> <span>Dissolved Oxygen (mg/L)</span></div>
                                <div class="font-semibold">
                                    <span class="{{ $row->doValueClass ?? '' }}">{{ isset($row->dissolved_oxygen) ? number_format($row->dissolved_oxygen,2) : '—' }}</span>
                                </div>
                            </div>

                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>🧪</span> <span>pH</span></div>
                                <div class="font-semibold">
                                    <span class="{{ $row->phValueClass ?? '' }}">{{ isset($row->ph_level) ? number_format($row->ph_level,2) : '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
