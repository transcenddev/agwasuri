<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 4 test accounts (2 admins, 2 users) idempotently using updateOrCreate.
     */
    public function run()
    {
        $users = [
            [
                'first_name' => 'Test',
                'last_name' => 'Admin1',
                'email' => 'test.admin1@example.com',
                'password' => Hash::make('Password123!'),
                'account_type' => 'admin',
                'fishpond_name' => 'Demo Pond A',
                'barangay' => 'San Juan I',
                'municipality' => 'Ternate',
                'province' => 'Cavite',
                'total_fishpond_area' => '120 sqm',
                'species_cultured' => json_encode(['Tilapia','Catfish']),
                'water_type' => 'Freshwater',
                'api_key' => Str::random(40),
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Admin2',
                'email' => 'test.admin2@example.com',
                'password' => Hash::make('Password123!'),
                'account_type' => 'admin',
                'fishpond_name' => 'Demo Pond B',
                'barangay' => 'San Juan II',
                'municipality' => 'Tanza',
                'province' => 'Cavite',
                'total_fishpond_area' => '85 sqm',
                'species_cultured' => json_encode(['Tilapia']),
                'water_type' => 'Brackish',
                'api_key' => Str::random(40),
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'User1',
                'email' => 'test.user1@example.com',
                'password' => Hash::make('Password123!'),
                'account_type' => 'user',
                'fishpond_name' => 'Demo Pond C',
                'barangay' => 'Barangay 3',
                'municipality' => 'Naic',
                'province' => 'Cavite',
                'total_fishpond_area' => '60 sqm',
                'species_cultured' => json_encode(['Milkfish']),
                'water_type' => 'Saltwater',
                'api_key' => Str::random(40),
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'User2',
                'email' => 'test.user2@example.com',
                'password' => Hash::make('Password123!'),
                'account_type' => 'user',
                'fishpond_name' => null,
                'barangay' => 'Barangay 4',
                'municipality' => 'Naic',
                'province' => 'Cavite',
                'total_fishpond_area' => null,
                'species_cultured' => json_encode([]),
                'water_type' => 'Freshwater',
                'api_key' => Str::random(40),
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }
    }
}
