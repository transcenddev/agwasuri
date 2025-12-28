<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::updateOrCreate([
            'email' => 'admin@admin.com'
        ], [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'account_type' => 'admin',
            'password' => bcrypt('admin1234'),
            'barangay' => null,
            'municipality' => null,
            'fishpond_name' => null,
            'province' => null,
            'total_fishpond_area' => null,
            'species_cultured' => null,
            'water_type' => null,
            'api_key' => Str::random(32),
        ]);

        User::updateOrCreate([
            'email' => 'test@test.com'
        ], [
            'first_name' => 'Test',
            'last_name' => 'Test',
            'account_type' => 'user',
            'fishpond_name' => 'Test Fishpond',
            'password' => bcrypt('test1234'),
            'barangay' => 'Test Barangay',
            'municipality' => 'Test City',
            'province' => 'Test Province',
            'total_fishpond_area' => 10.50,
            'species_cultured' => ['Tilapia', 'Bangus'],
            'water_type' => 'Freshwater',
            'api_key' => Str::random(32),
        ]);

        User::updateOrCreate([
            'email' => 'test_1@test.com'
        ], [
            'first_name' => 'Test_1',
            'last_name' => 'Test_1',
            'account_type' => 'user',
            'fishpond_name' => 'Test Fishpond_1',
            'password' => bcrypt('test1234'),
            'barangay' => 'Test Barangay_1',
            'municipality' => 'Test City_1',
            'province' => 'Test Province_1',
            'total_fishpond_area' => 10.50,
            'species_cultured' => ['Tilapia', 'Bangus'],
            'water_type' => 'Saltwater',
            'api_key' => Str::random(32),
        ]);

        // Seed master fishpond operators (idempotent)
        $this->call(\Database\Seeders\MasterFishpondOperatorsSeeder::class);

        // Existing water quality data seeder (if present)
        if (class_exists(\Database\Seeders\WaterQualityDataSeeder::class)) {
            $this->call(WaterQualityDataSeeder::class);
        }
    }
}
