<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\WaterQualityData;

class GenerateHistoricalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'water-quality:generate {userId : The user id to generate data for} {--days=365 : How many days back to generate} {--interval=60 : Interval in minutes between points}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate realistic historical water quality data for a user (for testing)';

    public function handle(): int
    {
        $userId = (int) $this->argument('userId');
        $days = (int) $this->option('days');
        $interval = (int) $this->option('interval');

        if ($days < 1) $days = 1;
        if ($interval < 1) $interval = 60;

        $user = User::find($userId);
        if (! $user) {
            $this->error("User with id {$userId} not found.");
            return 1;
        }

        $now = Carbon::now();
        $start = $now->copy()->subDays($days);
        $end = $now->copy();

        // Total number of points
        $pointsPerDay = intval(24 * (60 / $interval));
        $total = $pointsPerDay * $days;

        $this->info("Generating {$total} records for user {$userId} (from {$start} to {$end})");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $batchSize = 500;
        $batch = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $hour = (int) $current->hour;

            // Base realistic ranges
            // Temperature baseline between 24.0 - 32.0
            $tempBase = 28.0; // central baseline
            // Salinity 25.0 - 35.0 ppt
            $salinity = mt_rand(250, 350) / 10.0;
            // Dissolved oxygen baseline
            $doBase = 7.0;
            // pH baseline
            $ph = mt_rand(65, 85) / 10.0;

            // Apply daily patterns
            $tempVariation = 0.0;
            $doVariation = 0.0;

            if ($hour >= 6 && $hour < 9) {
                // Morning: cooler, higher DO
                $tempVariation = mt_rand(-20, 0) / 10.0; // -2.0 .. 0.0
                $doVariation = mt_rand(5, 15) / 10.0; // +0.5 .. +1.5
            } elseif ($hour >= 12 && $hour < 16) {
                // Afternoon: warmer, lower DO
                $tempVariation = mt_rand(10, 40) / 10.0; // +1.0 .. +4.0
                $doVariation = mt_rand(-15, -5) / 10.0; // -1.5 .. -0.5
            } else {
                // Night / other times: small random fluctuation
                $tempVariation = mt_rand(-10, 10) / 10.0; // -1.0 .. +1.0
                $doVariation = mt_rand(-5, 5) / 10.0; // -0.5 .. +0.5
            }

            $temperature = round($tempBase + $tempVariation + mt_rand(-10,10)/100.0, 2);
            // Clamp to allowed range 24.0 - 32.0
            $temperature = max(24.0, min(32.0, $temperature));

            $dissolvedOxygen = round(max(5.0, min(9.0, $doBase + $doVariation + mt_rand(-10,10)/100.0)), 2);

            // Ensure salinity in 25.0 - 35.0
            $salinity = round(max(25.0, min(35.0, $salinity)), 2);

            $ph = round(max(6.5, min(8.5, $ph + mt_rand(-10,10)/100.0)), 2);

            $row = [
                'user_id' => $userId,
                'temperature' => $temperature,
                'salinity' => $salinity,
                'dissolved_oxygen' => $dissolvedOxygen,
                'ph_level' => $ph,
                'recorded_at' => $current->toDateTimeString(),
                'created_at' => $current->toDateTimeString(),
                'updated_at' => $current->toDateTimeString(),
            ];

            $batch[] = $row;

            if (count($batch) >= $batchSize) {
                // Insert and clear
                DB::table((new WaterQualityData())->getTable())->insert($batch);
                $batch = [];
            }

            $bar->advance();

            $current->addMinutes($interval);
        }

        if (! empty($batch)) {
            DB::table((new WaterQualityData())->getTable())->insert($batch);
        }

        $bar->finish();
        $this->newLine(2);
        $totalInserted = DB::table((new WaterQualityData())->getTable())->where('user_id', $userId)->count();
        $this->info("Generated {$totalInserted} records for user {$userId}");

        return 0;
    }
}
