<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        foreach ($rows as $row) {
            $data = array_combine($header, $row);
            if (!$data) continue;

            $data = array_map(function ($v) {
                $v = trim($v);
                return $v === '' ? null : $v;
            }, $data);

            $data['species_cultured'] = $data['species_cultured'] ? str_replace('|', ',', $data['species_cultured']) : null;

            $unique = [
                'pond_name' => $data['pond_name'] ?? $data['name'],
                'barangay' => $data['barangay'] ?? 'San Juan',
            ];

            FishpondOperator::updateOrCreate($unique, [
                'name' => $data['name'] ?? null,
                'pond_name' => $data['pond_name'] ?? null,
                'barangay' => $data['barangay'] ?? null,
                'municipality' => $data['municipality'] ?? null,
                'province' => $data['province'] ?? null,
                'total_area_ha' => $data['total_area_ha'] ?? null,
                'species_cultured' => $data['species_cultured'] ?? null,
                'water_type' => $data['water_type'] ?? null,
                'contact' => $data['contact'] ?? null,
                'notes' => $data['notes'] ?? null,
                'source' => $data['source'] ?? 'masterlist',
            ]);
        }

        $this->command->info('Seeded master fishpond operators.');
    }
}
