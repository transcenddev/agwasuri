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
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-temperature)">Temperature (°C)</th>
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-salinity)">Salinity (ppt)</th>
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-do)">Dissolved Oxygen (mg/L)</th>
                                    <th class="sticky top-0 z-10 px-6 py-3 text-sm font-medium text-right bg-white border-b border-gray-100" style="color: var(--wq-series-ph)">pH Level</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @foreach ($water_quality_data as $entry)
                                    @php
                                        $statusBorderClass = $entry->statusBorderClass ?? 'border-l-4 border-gray-200';
                                        $badgeClass = $entry->statusBadgeClass ?? 'text-gray-700 bg-gray-100';
                                        $statusLabel = $entry->statusLabel ?? 'Unknown';
                                    @endphp

                                    <tr class="bg-white odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 {{ $statusBorderClass }}">{{ $entry->recorded_at }}</td>
                                        <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->temperature !== null ? number_format($entry->temperature, 2) : '-' }} <span class="{{ $entry->tempValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->tempFormatted, $m) ? '('.$m[1].')' : '' }}</span></td>
                                        <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->salinity !== null ? number_format($entry->salinity, 2) : '-' }} <span class="{{ $entry->salValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->salFormatted, $m) ? '('.$m[1].')' : '' }}</span></td>
                                        <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->dissolved_oxygen !== null ? number_format($entry->dissolved_oxygen, 2) : '-' }} <span class="{{ $entry->doValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->doFormatted, $m) ? '('.$m[1].')' : '' }}</span></td>
                                        <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $entry->ph_level !== null ? number_format($entry->ph_level, 2) : '-' }} <span class="{{ $entry->phValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->phFormatted, $m) ? '('.$m[1].')' : '' }}</span></td>
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
                        $statusClass = $entry->statusBorderClass ? ($entry->statusBorderClass . ' bg-white') : 'border-l-4 border-gray-200 bg-white';
                        $badgeClass = $entry->statusBadgeClass ?? 'text-gray-700 bg-gray-100';
                        $statusLabel = $entry->statusLabel ?? 'Unknown';
                    @endphp

                    <div class="bg-white border rounded-md p-3 hover:bg-gray-50 {{ $statusClass }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $statusLabel }}</span>
                                <div class="text-sm font-medium text-gray-700">{{ $entry->recorded_at }}</div>
                            </div>

                            <div class="text-sm text-gray-500 md:hidden">&nbsp;</div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-2 text-sm text-gray-700">
                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>🌡️</span> <span style="color: var(--wq-series-temperature)">Temperature (°C)</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->temperature !== null ? number_format($entry->temperature, 2) : '-' }} <span class="{{ $entry->tempValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->tempFormatted, $m) ? '('.$m[1].')' : '' }}</span></div>
                            </div>

                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>🧂</span> <span style="color: var(--wq-series-salinity)">Salinity (ppt)</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->salinity !== null ? number_format($entry->salinity, 2) : '-' }} <span class="{{ $entry->salValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->salFormatted, $m) ? '('.$m[1].')' : '' }}</span></div>
                            </div>

                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>💧</span> <span style="color: var(--wq-series-do)">Dissolved Oxygen (mg/L)</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->dissolved_oxygen !== null ? number_format($entry->dissolved_oxygen, 2) : '-' }} <span class="{{ $entry->doValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->doFormatted, $m) ? '('.$m[1].')' : '' }}</span></div>
                            </div>

                            <div class="truncate">
                                <div class="flex items-center gap-2 text-xs text-gray-500"> <span>🧪</span> <span style="color: var(--wq-series-ph)">pH Level</span></div>
                                <div class="font-semibold text-gray-700">{{ $entry->ph_level !== null ? number_format($entry->ph_level, 2) : '-' }} <span class="{{ $entry->phValueClass }}">{{ preg_match('/\((.+?)\)/', $entry->phFormatted, $m) ? '('.$m[1].')' : '' }}</span></div>
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
                            <button wire:click="$emit('clear-filters')" type="button" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm bg-white text-gray-700 hover:bg-gray-50">
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
