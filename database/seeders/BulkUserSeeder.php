<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

class BulkUserSeeder extends Seeder
{
    public function run()
    {
        $faker = FakerFactory::create('en_PH');

        $barangays = [
            'Poblacion I', 'Poblacion II', 'Poblacion III',
            'San Jose', 'San Juan I', 'San Juan II',
            'Sapang I', 'Sapang II', 'Bucana', 'Zapang'
        ];

        $speciesPool = ['Tilapia', 'Bangus', 'Shrimp', 'Mud Crab'];
        $fishpondPrefixes = ['Ternate Bay', 'San Juan', 'Poblacion', 'Sapang', 'Bucana'];

        // Create 30 realistic demo users
        for ($i = 1; $i <= 30; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $fullName = trim($firstName . ' ' . $lastName);

            // create a safe email derived from name and ensure uniqueness
            $base = Str::of($firstName . '.' . $lastName)->lower()->replaceMatches('/[^a-z0-9\.]/', '')->__toString();
            $email = $base . '@ternatefishfarm.ph';
            $suffix = 1;
            while (User::where('email', $email)->exists()) {
                $email = $base . $suffix++ . '@ternatefishfarm.ph';
            }

            $barangay = $faker->randomElement($barangays);
            $municipality = 'Ternate';
            $province = 'Cavite';
            $location = "{$barangay}, Ternate, Cavite";

            // fishpond name and species
            $pondName = $faker->randomElement($fishpondPrefixes) . ' Fishpond ' . $faker->unique()->numberBetween(1, 99);
            $speciesCount = rand(1, 2);
            $species = $faker->randomElements($speciesPool, $speciesCount);

            // area in hectares (0.5 - 8.0)
            $area = $faker->randomFloat(2, 0.5, 8.0);

            User::updateOrCreate([
                'email' => $email
            ], [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'account_type' => 'user',
                'password' => bcrypt('password123'),
                'barangay' => $barangay,
                'municipality' => $municipality,
                'province' => $province,
                'fishpond_name' => $pondName,
                'total_fishpond_area' => $area,
                'species_cultured' => $species,
                'water_type' => in_array('Shrimp', $species) || in_array('Mud Crab', $species) ? 'Brackish' : 'Freshwater',
                'api_key' => Str::random(32),
            ]);
        }
    }
}
