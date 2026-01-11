<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\WaterQualityData;

class LiveEnvironmentDataSeeder extends Seeder
{
    /**
     * Seed live environment data from CSV for Danilo Ramos.
     *
     * Expected CSV headers:
     * datetime,temperature_c,ph,dissolved_oxygen_mgL,salinity_ppt
     *
     * Default credentials:
     * Email: danilo.ramos@fishpond.com
     * Password: danilo123
     */
    public function run(): void
    {
        // Create or update Danilo Ramos user FIRST before checking CSV
        $user = User::updateOrCreate(
            [
                'email' => 'danilo.ramos@fishpond.com'
            ],
            [
                'first_name' => 'Danilo',
                'last_name' => 'Ramos',
                'account_type' => 'user',
                'password' => bcrypt('danilo123'),
                'fishpond_name' => 'Ramos Fishpond',
                'barangay' => 'Barangay',
                'municipality' => 'Municipality',
                'province' => 'Province',
                'total_fishpond_area' => 15.00,
                'species_cultured' => ['Tilapia', 'Bangus'],
                'water_type' => 'Brackish',
                'api_key' => Str::random(32),
            ]
        );

        $userId = $user->user_id;

        // Now check for CSV file
        $path = database_path('seeders/data/live_environment_data.csv');

        if (!file_exists($path)) {
            return;
        }

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

        $header = null;
        $rows = [];
        $skipped = 0;

        foreach ($file as $row) {
            // Skip empty rows
            if (!$row || (count($row) === 1 && ($row[0] === null || trim((string)$row[0]) === ''))) {
                continue;
            }

            // First row is header
            if ($header === null) {
                $header = array_map(fn($h) => trim((string)$h), $row);
                continue;
            }

            // Pad row to match header length
            $row = array_pad($row, count($header), null);
            $data = array_combine($header, $row);

            if (!$data) {
                $skipped++;
                continue;
            }

            $datetimeRaw = trim((string)($data['datetime'] ?? ''));
            if ($datetimeRaw === '') {
                $skipped++;
                continue;
            }

            // Parse datetime
            try {
                $recordedAt = Carbon::createFromFormat('Y-m-d H:i:s', $datetimeRaw);
            } catch (\Throwable $e) {
                $skipped++;
                continue;
            }

            // Extract and validate numeric values
            $temperature = (float)($data['temperature_c'] ?? 0);
            $phLevel     = (float)($data['ph'] ?? 0);
            $do          = (float)($data['dissolved_oxygen_mgL'] ?? 0);
            $salinity    = (float)($data['salinity_ppt'] ?? 0);

            $rows[] = [
                'user_id'          => $userId,
                'recorded_at'      => $recordedAt,
                'temperature'      => round($temperature, 2),
                'ph_level'         => round($phLevel, 2),
                'dissolved_oxygen' => round($do, 2),
                'salinity'         => round($salinity, 2),
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if (empty($rows)) {
            return;
        }

        // Insert in chunks using upsert (idempotent: same user_id + recorded_at gets updated)
        $inserted = 0;
        foreach (array_chunk($rows, 500) as $chunk) {
            WaterQualityData::upsert(
                $chunk,
                ['user_id', 'recorded_at'],
                ['temperature', 'ph_level', 'dissolved_oxygen', 'salinity', 'updated_at']
            );
            $inserted += count($chunk);
        }

    }
}
