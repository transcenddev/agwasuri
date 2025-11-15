<div>
    <div class="w-full">
        <table class="w-full text-center border-collapse">
            <thead>
                <tr>
                    <th class="pb-4">Recorded At</th>
                    <th class="pb-4 text-red-600">Temperature</th>
                    <th class="pb-4 text-green-600">Salinity</th>
                    <th class="pb-4 text-cyan-600">Dissolved Oxygen</th>
                    <th class="pb-4 text-yellow-600">pH Level</th>
                </tr>
            </thead>
            <tbody>
                @if($water_quality_data->count() >= 1)
                    @foreach ($water_quality_data as $entry)
                        <tr>
                            <td class="pb-3">{{ $entry->recorded_at }}</td>
                            <td class="pb-3">{{ $entry->temperature }}</td>
                            <td class="pb-3">{{ $entry->salinity }}</td>
                            <td class="pb-3">{{ $entry->dissolved_oxygen }}</td>
                            <td class="pb-3">{{ $entry->ph_level }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="py-4 text-lg text-center text-gray-500">
                            No data available to display.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $water_quality_data->links(data: ['scrollTo' => false]) }}
    </div>
</div>
