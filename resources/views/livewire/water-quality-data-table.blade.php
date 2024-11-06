<div>
    <table>
        <thead>
            <tr>
                <th>Datetime</th>
                <th>Temperature</th>
                <th>Salinity</th>
                <th>Dissolved Oxygen</th>
                <th>pH Level</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($waterqualitydata as $entry)
            <tr>
                <td>{{ $entry->recorded_at }}</td>
                <td>{{ $entry->temperature }}</td>
                <td>{{ $entry->salinity }}</td>
                <td>{{ $entry->dissolved_oxygen }}</td>
                <td>{{ $entry->ph_level }}</td>
                <td>{{ $entry->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $waterqualitydata->links() }}
</div>
