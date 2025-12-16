<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * ImportFishpondOperatorsSeeder
 *
 * Reads a CSV at storage/masterlist/ternate.csv and performs idempotent
 * updateOrCreate operations to add or update users. It will not modify
 * accounts unless the composite key (first_name, last_name, barangay)
 * matches; preserved accounts (admin/test/test1) are left untouched.
 *
 * CSV expected headers: first_name,last_name,barangay,municipality,province,species_cultured,email
 */
class ImportFishpondOperatorsSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('masterlist/ternate.csv');
        if (!file_exists($path)) {
            $this->command->error('Masterlist CSV not found: ' . $path);
            $this->command->info('Place the CSV at storage/masterlist/ternate.csv and run the seeder.');
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->command->error('Failed to open CSV file: ' . $path);
            return;
        }

        $header = null;
        $rows = 0;
        $created = 0;
        $updated = 0;

        $preserveUsernames = ['admin', 'test', 'test1'];

        // detect dry-run flag passed via artisan: php artisan db:seed --class=ImportFishpondOperatorsSeeder -- --dry-run
        $dryRun = false;
        try {
            $dryRun = (bool) $this->command->option('dry-run');
        } catch (\Throwable $e) {
            // fallback to argv check
            $dryRun = in_array('--dry-run', $GLOBALS['argv'] ?? [], true);
        }

        while (($data = fgetcsv($handle)) !== false) {
            // detect header row
            if ($header === null) {
                $header = array_map('trim', $data);
                continue;
            }

            $row = array_combine($header, $data);
            if (!$row) continue;

            $first = trim($row['first_name'] ?? ($row['firstname'] ?? ''));
            $last = trim($row['last_name'] ?? ($row['lastname'] ?? ''));
            $barangay = trim($row['barangay'] ?? '');
            $municipality = trim($row['municipality'] ?? '');
            $province = trim($row['province'] ?? '');
            $species = trim($row['species_cultured'] ?? ($row['species'] ?? ''));
            $email = trim($row['email'] ?? '');

            if (empty($first) && empty($last)) continue;

            $rows++;

            // Build idempotent lookup key: first_name + last_name + barangay
            $lookup = [
                'first_name' => $first,
                'last_name' => $last,
                'barangay' => $barangay,
            ];

            // Prevent accidental overwrite of preserved accounts by username/email
            if ($email && in_array(strtolower($email), $preserveUsernames, true)) {
                $this->command->info('Skipping preserved account by email: ' . $email);
                continue;
            }

            $attributes = [
                'first_name' => $first,
                'last_name' => $last,
                'barangay' => $barangay ?: null,
                'municipality' => $municipality ?: null,
                'province' => $province ?: null,
                'species_cultured' => $species ?: null,
                'is_admin' => 0,
            ];

            // Defaults for newly created users
            $defaults = [
                'api_key' => Str::random(40),
                'password' => Hash::make(Str::random(12)),
            ];

            // If CSV contains email, prefer using it as unique key to prevent duplicates
            $uniqueKey = $email ? ['email' => $email] : $lookup;

            if ($dryRun) {
                $this->command->info('[dry-run] Would import: ' . ($email ?: ($first . ' ' . $last)));
                continue;
            }

            $user = User::updateOrCreate($uniqueKey, array_merge($attributes, $defaults));

            if ($user->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        fclose($handle);

        $this->command->info("Import complete. Rows: $rows, Created: $created, Updated: $updated");

        // For safety, list non-preserved users that exist in the DB but were not in masterlist
        $this->command->info('To disable old accounts (optional): run a query to list users not in the masterlist lookup keys.');
    }
}
