<div>
    {{-- Subtitle: show which user and how many records --}}
    @php
        $userName = null;
        if (!empty($userId)) {
            try {
                $u = \App\Models\User::find($userId);
                $userName = $u ? trim($u->first_name . ' ' . $u->last_name) : null;
            } catch (\Throwable $e) {
                $userName = null;
            }
        }

        // Determine record count: paginator provides total(), plain collection uses count()
        $recordCount = method_exists($water_quality_data, 'total') ? $water_quality_data->total() : $water_quality_data->count();
    @endphp

    <div class="mb-4">
        <div class="text-sm text-gray-600">
            @if($userName)
                <span class="font-semibold">Historical Data for {{ $userName }}</span>
                <span class="text-gray-500">({{ $recordCount }} record{{ $recordCount == 1 ? '' : 's' }})</span>
            @else
                <span class="font-semibold">Historical Data</span>
                <span class="text-gray-500">({{ $recordCount }} record{{ $recordCount == 1 ? '' : 's' }})</span>
            @endif
        </div>

        @if(!empty($startDate) || !empty($endDate))
            @php
                try {
                    $startText = $startDate ? \Illuminate\Support\Carbon::parse($startDate)->format('M d, Y') : null;
                    $endText = $endDate ? \Illuminate\Support\Carbon::parse($endDate)->format('M d, Y') : null;
                } catch (\Throwable $e) {
                    $startText = $startDate;
                    $endText = $endDate;
                }
            @endphp
            <div class="mt-1 text-sm text-gray-500">Showing data
                @if($startText && $endText)
                    from {{ $startText }} to {{ $endText }}
                @elseif($startText)
                    from {{ $startText }} onwards
                @elseif($endText)
                    up to {{ $endText }}
                @endif
            </div>
        @endif
    </div>

    <script>
        // Listen for a signed URL from Livewire and open it to trigger the browser download.
        window.addEventListener('download-url', function (e) {
            try {
                const url = e.detail.url;
                if (!url) return;
                // Use a navigation to the signed URL which streams a CSV response
                window.location.href = url;
            } catch (err) {
                console.error('download-url handler failed', err);
            }
        });
    </script>
    <div class="w-full overflow-x-auto">
        @php $hasRows = $water_quality_data->count() >= 1; @endphp

        @if($hasRows)
            <!-- Desktop table (shown from large screens and up) -->
            <div class="hidden lg:block">
                <div class="overflow-hidden border border-gray-100 rounded-md shadow-sm">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full min-w-0 text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="sticky top-0 z-10 w-56 px-6 py-3 text-sm font-medium text-left text-gray-700 bg-white border-b border-gray-100">Recorded At</th>
                                    <th class="sticky top-0 z-10 px-4 py-3 text-sm font-medium text-center text-gray-700 bg-white border-b border-gray-100">Status</th>
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-temperature)">Temperature (°C)</th>
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-salinity)">Salinity (ppt)</th>
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-do)">Dissolved Oxygen (mg/L)</th>
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-ph)">pH Level</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @foreach ($water_quality_data as $entry)
                                    @php
                                            // extract per-metric labels from formatted strings (existing format: "value (LABEL)")
                                            preg_match('/\((.+?)\)/', $entry->tempFormatted ?? '', $mTemp); $tempLabel = strtoupper($mTemp[1] ?? 'UNKNOWN');
                                            preg_match('/\((.+?)\)/', $entry->salFormatted ?? '', $mSal); $salLabel = strtoupper($mSal[1] ?? 'UNKNOWN');
                                            preg_match('/\((.+?)\)/', $entry->doFormatted ?? '', $mDo); $doLabel = strtoupper($mDo[1] ?? 'UNKNOWN');
                                            preg_match('/\((.+?)\)/', $entry->phFormatted ?? '', $mPh); $phLabel = strtoupper($mPh[1] ?? 'UNKNOWN');

                                            // determine worst status and culprit (priority HARM > BAD > GOOD > BEST)
                                            $labels = ['temperature' => $tempLabel, 'salinity' => $salLabel, 'dissolved_oxygen' => $doLabel, 'ph' => $phLabel];
                                            $priority = ['HARM' => 4, 'BAD' => 3, 'GOOD' => 2, 'BEST' => 1, 'UNKNOWN' => 0];
                                            $worst = 'UNKNOWN'; $worstScore = 0; $culprit = null;
                                            foreach($labels as $k => $v) {
                                                $score = $priority[$v] ?? 0;
                                                if ($score > $worstScore) { $worstScore = $score; $worst = $v; $culprit = $k; }
                                            }

                                            $borderMap = ['BEST' => 'border-l-4 border-emerald-200', 'GOOD' => 'border-l-4 border-sky-200', 'BAD' => 'border-l-4 border-amber-200', 'HARM' => 'border-l-4 border-rose-200', 'UNKNOWN' => 'border-l-4 border-gray-200'];
                                            $statusBorderClass = $borderMap[$worst] ?? 'border-l-4 border-gray-200';

                                            $pill = function($label) {
                                                return match($label) {
                                                    'BEST' => 'text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                                    'GOOD' => 'text-blue-800 bg-blue-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                                    'BAD' => 'text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                                    'HARM' => 'text-rose-800 bg-rose-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                                    default => 'text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                                };
                                            };
                                        @endphp

                                        <tr class="bg-white odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 {{ $statusBorderClass }}">{{ $entry->recorded_at }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div x-data="{ open:false }" class="inline-block relative" @mouseenter="open=true" @mouseleave="open=false" @focusin="open=true" @focusout="open=false">
                                                    <span tabindex="0" class="inline-flex items-center {{ $pill($worst) }}">{{ $worst }}</span>
                                                    @if($culprit)
                                                        <div x-show="open" x-cloak class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-48 bg-white border border-gray-200 rounded shadow-lg text-xs text-gray-700 p-2 z-50">
                                                            <div class="font-medium mb-1">Cause</div>
                                                            <div>{{ $worst }}: {{ ucfirst(str_replace('_',' ',$culprit)) }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->temperature !== null ? number_format($entry->temperature, 2) : '-' }} <span class="{{ $pill($tempLabel) }}">{{ $tempLabel }}</span></td>
                                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->salinity !== null ? number_format($entry->salinity, 2) : '-' }} <span class="{{ $pill($salLabel) }}">{{ $salLabel }}</span></td>
                                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->dissolved_oxygen !== null ? number_format($entry->dissolved_oxygen, 2) : '-' }} <span class="{{ $pill($doLabel) }}">{{ $doLabel }}</span></td>
                                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->ph_level !== null ? number_format($entry->ph_level, 2) : '-' }} <span class="{{ $pill($phLabel) }}">{{ $phLabel }}</span></td>
                                        </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mobile stacked list (visible up to tablet sizes) -->
            <div class="block space-y-3 lg:hidden">
                @foreach ($water_quality_data as $entry)
                    @php
                        // extract labels
                        preg_match('/\((.+?)\)/', $entry->tempFormatted ?? '', $mTemp); $tempLabel = strtoupper($mTemp[1] ?? 'UNKNOWN');
                        preg_match('/\((.+?)\)/', $entry->salFormatted ?? '', $mSal); $salLabel = strtoupper($mSal[1] ?? 'UNKNOWN');
                        preg_match('/\((.+?)\)/', $entry->doFormatted ?? '', $mDo); $doLabel = strtoupper($mDo[1] ?? 'UNKNOWN');
                        preg_match('/\((.+?)\)/', $entry->phFormatted ?? '', $mPh); $phLabel = strtoupper($mPh[1] ?? 'UNKNOWN');

                        $labels = ['temperature' => $tempLabel, 'salinity' => $salLabel, 'dissolved_oxygen' => $doLabel, 'ph' => $phLabel];
                        $priority = ['HARM' => 4, 'BAD' => 3, 'GOOD' => 2, 'BEST' => 1, 'UNKNOWN' => 0];
                        $worst = 'UNKNOWN'; $worstScore = 0; $culprit = null;
                        foreach($labels as $k => $v) { $score = $priority[$v] ?? 0; if ($score > $worstScore) { $worstScore = $score; $worst = $v; $culprit = $k; } }
                        $borderMap = ['BEST' => 'border-l-4 border-emerald-200', 'GOOD' => 'border-l-4 border-sky-200', 'BAD' => 'border-l-4 border-amber-200', 'HARM' => 'border-l-4 border-rose-200', 'UNKNOWN' => 'border-l-4 border-gray-200'];
                        $statusClass = ($borderMap[$worst] ?? 'border-l-4 border-gray-200') . ' bg-white';

                        $pill = function($label) {
                            return match($label) {
                                'BEST' => 'text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                'GOOD' => 'text-blue-800 bg-blue-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                'BAD' => 'text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                'HARM' => 'text-rose-800 bg-rose-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                default => 'text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                            };
                        };
                    @endphp

                    <div class="bg-white border rounded-md p-3 hover:bg-gray-50 {{ $statusClass }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div x-data="{ open:false }" class="relative inline-block" @click.outside="open=false">
                                    <button @click="open = !open" class="inline-flex items-center {{ $pill($worst) }}">
                                        {{ $worst }}
                                    </button>
                                    @if($culprit)
                                        <div x-show="open" x-cloak class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-44 bg-white border border-gray-200 rounded shadow-lg text-xs text-gray-700 p-2 z-50">
                                            <div class="font-medium mb-1">Cause</div>
                                            <div>{{ $worst }}: {{ ucfirst(str_replace('_',' ',$culprit)) }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-sm font-medium text-gray-700">{{ $entry->recorded_at }}</div>
                            </div>

                            <div class="text-sm text-gray-500 md:hidden">&nbsp;</div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-2 text-sm text-gray-700">
                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>🌡️</span> <span style="color: var(--wq-series-temperature)">Temperature (°C)</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->temperature !== null ? number_format($entry->temperature, 2) : '-' }} <span class="{{ $pill($tempLabel) }}">{{ $tempLabel }}</span></div>
                            </div>

                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>🧂</span> <span style="color: var(--wq-series-salinity)">Salinity (ppt)</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->salinity !== null ? number_format($entry->salinity, 2) : '-' }} <span class="{{ $pill($salLabel) }}">{{ $salLabel }}</span></div>
                            </div>

                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>💧</span> <span style="color: var(--wq-series-do)">Dissolved Oxygen (mg/L)</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->dissolved_oxygen !== null ? number_format($entry->dissolved_oxygen, 2) : '-' }} <span class="{{ $pill($doLabel) }}">{{ $doLabel }}</span></div>
                            </div>

                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>🧪</span> <span style="color: var(--wq-series-ph)">pH Level</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->ph_level !== null ? number_format($entry->ph_level, 2) : '-' }} <span class="{{ $pill($phLabel) }}">{{ $phLabel }}</span></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-8 text-center">
                <div class="max-w-xl mx-auto">
                    @if(!empty($startDate) || !empty($endDate))
                        <div class="text-lg font-semibold text-gray-700">No records for this user in the selected date range.</div>
                        <div class="mt-3">
                            <button wire:click="$dispatch('clear-filters')" type="button" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm bg-white text-gray-700 hover:bg-gray-50">
                                Clear filters
                            </button>
                        </div>
                    @else
                        <div class="text-lg text-gray-500">No data available to display.</div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="mt-5">
        {{ $water_quality_data->links(data: ['scrollTo' => false]) }}
    </div>
</div>
