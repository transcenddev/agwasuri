<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaterQualityData;
use Carbon\Carbon;

class SeedWaterQualitySamples extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Accepts one or more user_ids and an optional count per user.
     */
    protected $signature = 'demo:water-samples {user_ids*} {--count=10} {--start=now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert sample water-quality readings for given user ids (dev only)';

    public function handle()
    {
        $userIds = $this->argument('user_ids');
        $count = (int) $this->option('count');
        $startOpt = $this->option('start');

        $start = $startOpt === 'now' ? Carbon::now() : Carbon::parse($startOpt);

        $this->info("Seeding $count samples per user for: " . implode(', ', $userIds));

        foreach ($userIds as $userId) {
            $this->info("  -> user_id=$userId");
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            for ($i = 0; $i < $count; $i++) {
                $ts = (clone $start)->subMinutes($i);

                $temperature = round(25 + mt_rand(-50, 50) / 10, 2); // 20.0 - 30.0
                $salinity = round(12 + mt_rand(-30, 30) / 10, 2);    // ~9.0 - 15.0
                $dissolvedOxygen = round(6 + mt_rand(-30, 30) / 10, 2); // ~3.0 - 9.0
                $phLevel = round(7 + mt_rand(-20, 20) / 100, 2);      // ~6.8 - 7.2

                WaterQualityData::create([
                    'user_id' => $userId,
                    'recorded_at' => $ts->toDateTimeString(),
                    'temperature' => $temperature,
                    'salinity' => $salinity,
                    'dissolved_oxygen' => $dissolvedOxygen,
                    'ph_level' => $phLevel,
                ]);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('Done seeding samples.');
        return 0;
    }
}
