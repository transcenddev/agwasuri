<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\FishpondOperator;

class MasterFishpondOperatorsSeeder extends Seeder
{
    public function run()
    {
        $path = database_path('seeders/data/master_fishpond_operators.csv');
        if (!file_exists($path)) {
            $this->command->error('CSV not found: ' . $path);
            return;
        }

        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));

        // Start user_id allocation from current max user_id + 1 so CSV users receive sequential IDs
        $currentMax = DB::table('users')->max('user_id') ?? 0;
        $nextId = max(4, $currentMax + 1);

        foreach ($rows as $row) {
            $data = array_combine($header, $row);
            if (!$data) continue;

            $data = array_map(function ($v) {
                $v = trim($v);
                return $v === '' ? null : $v;
            }, $data);

            $data['species_cultured'] = $data['species_cultured'] ? str_replace('|', ',', $data['species_cultured']) : null;

            $pondName = $data['pond_name'] ?? ($data['name'] . ' Pond');
            $barangay = $data['barangay'] ?? 'San Juan';

            $unique = [
                'pond_name' => $pondName,
                'barangay' => $barangay,
            ];

            FishpondOperator::updateOrCreate($unique, [
                'name' => $data['name'] ?? null,
                'pond_name' => $pondName,
                'barangay' => $barangay,
                'municipality' => $data['municipality'] ?? null,
                'province' => $data['province'] ?? null,
                'total_area_ha' => $data['total_area_ha'] ?? null,
                'species_cultured' => $data['species_cultured'] ?? null,
                'water_type' => $data['water_type'] ?? ($data['freshwater'] ? 'freshwater' : ($data['brackishwater'] ? 'brackishwater' : null)),
                'contact' => $data['contact'] ?? null,
                'notes' => $data['notes'] ?? null,
                'source' => $data['source'] ?? 'masterlist',
            ]);

            // Create a corresponding user so the masterlist can be used as real test data.
            // Determine a numeric user id: prefer CSV `no` if present and unused, otherwise allocate sequentially starting at $nextId
            $csvNo = $data['no'] ?? null;
            $desiredId = is_numeric($csvNo) ? (int) $csvNo : null;
            $fullName = $data['name'] ?? 'Operator';
            $parts = preg_split('/\s+/', $fullName);
            $first = array_shift($parts) ?: null;
            $last = count($parts) ? implode(' ', $parts) : null;

            $emailBase = Str::slug(($first ?: 'user') . ($last ? '.' . $last : ''));
            // if CSV provided a no, include it in the email; otherwise use the nextId for deterministic emails
            $emailIdentifier = $desiredId ?: $nextId;
            $email = strtolower($emailBase) . $emailIdentifier . '@gmail.com';

            // Store species_cultured as a JSON-like array string matching required format
            $species_json = $data['species_cultured'] ? json_encode([$data['species_cultured']]) : null;

            $userData = [
                // use 'user' to match existing app account types
                'account_type' => 'user',
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'fishpond_name' => $pondName,
                'barangay' => $barangay,
                'municipality' => $data['municipality'] ?? null,
                'province' => $data['province'] ?? null,
                'total_fishpond_area' => $data['total_area_ha'] ?? null,
                'species_cultured' => $species_json,
                'water_type' => $data['water_type'] ?? ($data['freshwater'] ? 'freshwater' : ($data['brackishwater'] ? 'brackishwater' : null)),
                'farm_type' => null,
                'culture_system' => null,
                'culture_method' => null,
                'activities_during_pond_preperation' => null,
                'liming' => null,
                'fertilizer' => null,
                'disinfectant' => null,
                'source_of_fingerlings_larvae' => null,
                'stocking_density' => null,
                'culture_period' => null,
                'harvest_method' => null,
                'total_production_per_cropping' => null,
                'feeds' => null,
                'amount_per_day' => null,
                'frequency' => null,
                'product_destination' => null,
                'api_key' => Str::random(40),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert/update user safely to avoid duplicate primary key errors.
            // If user already exists by email, update it. Otherwise insert with a sequential user_id.
            $existing = DB::table('users')->where('email', $email)->first();
            if ($existing) {
                DB::table('users')->where('email', $email)->update($userData);
            } else {
                // choose id: CSV `no` if provided and free, otherwise next available sequential id
                if ($desiredId && !DB::table('users')->where('user_id', $desiredId)->exists()) {
                    $assignId = $desiredId;
                } else {
                    // find next unused id starting from $nextId
                    while (DB::table('users')->where('user_id', $nextId)->exists()) {
                        $nextId++;
                    }
                    $assignId = $nextId;
                    $nextId++; // prepare for next row
                }

                $userData['user_id'] = $assignId;
                DB::table('users')->insert($userData);
            }
        }

        $this->command->info('Seeded master fishpond operators.');
    }
}
