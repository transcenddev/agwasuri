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
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-temperature)">Temperature (°C)</th>
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-salinity)">Salinity (ppt)</th>
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-do)">Dissolved Oxygen (mg/L)</th>
                            <th class="px-6 py-3 text-sm font-medium text-right sticky top-0 bg-white z-10 border-b border-gray-100" style="color: var(--wq-series-ph)">pH</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $row)
                            @php
                                $labels = [
                                    'temperature' => strtoupper($row->tempLabel ?? 'UNKNOWN'),
                                    'salinity' => strtoupper($row->salLabel ?? 'UNKNOWN'),
                                    'dissolved_oxygen' => strtoupper($row->doLabel ?? 'UNKNOWN'),
                                    'ph' => strtoupper($row->phLabel ?? 'UNKNOWN'),
                                ];
                                $rowBorder = 'border-l-4 border-gray-100';
                                $pill = fn($label) => match(strtoupper($label)) {
                                    'BEST' => 'text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                    'GOOD' => 'text-blue-800 bg-blue-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                    'BAD' => 'text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                    'HARM' => 'text-rose-800 bg-rose-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                    default => 'text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                };
                            @endphp
                            <tr class="bg-white odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 {{ $rowBorder }}">{{ optional($row->recorded_at)->format('Y-m-d H:i') ?? $row->recorded_at }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-700">{{ isset($row->temperature) ? number_format($row->temperature, 2) : '-' }} <span class="ml-2 {{ $pill($row->tempLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->tempLabel ?? 'UNKNOWN') }}</span></td>

                                <td class="px-6 py-3 text-sm text-right text-gray-700">{{ isset($row->salinity) ? number_format($row->salinity, 2) : '-' }} <span class="ml-2 {{ $pill($row->salLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->salLabel ?? 'UNKNOWN') }}</span></td>

                                <td class="px-6 py-3 text-sm text-right text-gray-700">{{ isset($row->dissolved_oxygen) ? number_format($row->dissolved_oxygen, 2) : '-' }} <span class="ml-2 {{ $pill($row->doLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->doLabel ?? 'UNKNOWN') }}</span></td>

                                <td class="px-6 py-3 text-sm text-right text-gray-700">{{ isset($row->ph_level) ? number_format($row->ph_level, 2) : '-' }} <span class="ml-2 {{ $pill($row->phLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->phLabel ?? 'UNKNOWN') }}</span></td>
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
                        $rowBorder = 'border-l-4 border-gray-100';
                        $pill = fn($label) => match(strtoupper($label)) {
                            'BEST' => 'text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                            'GOOD' => 'text-blue-800 bg-blue-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                            'BAD' => 'text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                            'HARM' => 'text-rose-800 bg-rose-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                            default => 'text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                        };
                    @endphp
                    <div class="bg-white border rounded-md p-3 hover:bg-gray-50 {{ $rowBorder }}">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-700">{{ optional($row->recorded_at)->format('Y-m-d H:i') ?? $row->recorded_at }}</div>
                        </div>

                        <div class="mt-2 grid grid-cols-2 gap-3 text-sm text-gray-700">
                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>🌡️</span> <span>Temperature (°C)</span></div>
                                <div class="font-semibold">
                                    <span class="text-gray-800">{{ isset($row->temperature) ? number_format($row->temperature,2) : '-' }}</span>
                                    <span class="ml-2 {{ $pill($row->tempLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->tempLabel ?? 'UNKNOWN') }}</span>
                                </div>
                            </div>

                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>🧂</span> <span>Salinity (ppt)</span></div>
                                <div class="font-semibold">
                                    <span class="text-gray-800">{{ isset($row->salinity) ? number_format($row->salinity,2) : '-' }}</span>
                                    <span class="ml-2 {{ $pill($row->salLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->salLabel ?? 'UNKNOWN') }}</span>
                                </div>
                            </div>

                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>💧</span> <span>Dissolved Oxygen (mg/L)</span></div>
                                <div class="font-semibold">
                                    <span class="text-gray-800">{{ isset($row->dissolved_oxygen) ? number_format($row->dissolved_oxygen,2) : '-' }}</span>
                                    <span class="ml-2 {{ $pill($row->doLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->doLabel ?? 'UNKNOWN') }}</span>
                                </div>
                            </div>

                            <div class="truncate">
                                <div class="text-xs text-gray-500 flex items-center gap-2"> <span>🧪</span> <span>pH</span></div>
                                <div class="font-semibold">
                                    <span class="text-gray-800">{{ isset($row->ph_level) ? number_format($row->ph_level,2) : '-' }}</span>
                                    <span class="ml-2 {{ $pill($row->phLabel ?? 'UNKNOWN') }}">{{ strtoupper($row->phLabel ?? 'UNKNOWN') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
