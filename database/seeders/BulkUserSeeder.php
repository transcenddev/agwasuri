<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class BulkUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a handful of realistic random users
        User::factory()->count(50)->create();

        // Create predictable "Bulk User" samples for UI / pagination testing
        $start = 1;
        $count = 120; // create 120 sample users
        for ($i = $start; $i <= $count; $i++) {
            $first = 'Bulk User' . ($i);
            $last = '';
            $email = "test.bulk{$i}@example.com";

            // Avoid duplicate email collisions by using upsert-like createOrUpdate
            User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'email_verified_at' => now(),
                    'password' => User::factory()->make()->password,
                    'remember_token' => User::factory()->make()->remember_token,
                    'account_type' => 'user',
                    'barangay' => null,
                    'municipality' => null,
                    'province' => null,
                    'total_fishpond_area' => null,
                    'species_cultured' => null,
                    'water_type' => null,
                    'api_key' => null,
                ]
            );
        }
    }
}
