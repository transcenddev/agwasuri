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
            <div class="text-sm text-gray-500 mt-1">Showing data
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
            <!-- Desktop table -->
            <div class="hidden md:block">
                <div class="rounded-md overflow-hidden border border-gray-100 shadow-sm">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-0">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-sm font-medium text-gray-700 text-left w-56 sticky top-0 bg-white z-10 border-b border-gray-100">Recorded At</th>
                                    <th class="px-4 py-3 text-sm font-medium text-gray-700 text-center sticky top-0 bg-white z-10 border-b border-gray-100">Status</th>
                                    <th class="px-6 py-3 text-sm font-medium text-red-600 text-right sticky top-0 bg-white z-10 border-b border-gray-100">Temperature (°C)</th>
                                    <th class="px-6 py-3 text-sm font-medium text-green-600 text-right sticky top-0 bg-white z-10 border-b border-gray-100">Salinity (ppt)</th>
                                    <th class="px-6 py-3 text-sm font-medium text-cyan-600 text-right sticky top-0 bg-white z-10 border-b border-gray-100">Dissolved Oxygen (mg/L)</th>
                                    <th class="px-6 py-3 text-sm font-medium text-yellow-600 text-right sticky top-0 bg-white z-10 border-b border-gray-100">pH Level</th>
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
                                        <td class="px-4 py-3 text-sm text-gray-700 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-right">{{ $entry->temperature }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-right">{{ $entry->salinity }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-right">{{ $entry->dissolved_oxygen }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-right">{{ $entry->ph_level }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mobile stacked list -->
            <div class="block md:hidden space-y-3">
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

                            <div class="text-sm text-gray-500">pH <span class="font-semibold">{{ $entry->ph_level }}</span></div>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-sm text-gray-700">
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 flex items-center justify-end gap-1"> <span>🌡️</span> <span>Temperature (°C)</span></div>
                                    <div class="font-semibold">{{ $entry->temperature }}</div>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs text-gray-500 flex items-center justify-end gap-1"> <span>💧</span> <span>DO (mg/L)</span></div>
                                    <div class="font-semibold">{{ $entry->dissolved_oxygen }}</div>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div x-data="{ open: false }" class="w-full">
                                    <div class="flex items-center justify-end">
                                        <button
                                            @click="open = !open"
                                            :aria-expanded="open"
                                            type="button"
                                            aria-controls="more-{{ $loop->index }}"
                                            class="inline-flex items-center text-xs text-blue-600 hover:text-blue-700 focus:outline-none"
                                        >
                                            <svg :class="{ 'rotate-180': open }" class="transform transition-transform duration-150 w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            <span x-text="open ? 'Hide' : 'More'"></span>
                                        </button>
                                    </div>

                                    <div x-show="open" x-transition x-cloak id="more-{{ $loop->index }}" class="mt-2 text-right">
                                        <div class="text-xs text-gray-500 flex items-center justify-end gap-1"> <span>🧂</span> <span>Salinity (ppt)</span></div>
                                        <div class="font-semibold">{{ $entry->salinity }}</div>
                                    </div>
                                </div>
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
