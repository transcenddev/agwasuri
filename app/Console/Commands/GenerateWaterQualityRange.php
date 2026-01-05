<?php

namespace App\Console\Commands;

use App\Models\WaterQualityData;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateWaterQualityRange extends Command
{
    protected $signature = 'app:generate-water-quality-range
        {user_id : Target user_id}
        {--start=2025-12-04 00:00:00 : Start datetime (Y-m-d H:i:s)}
        {--end=2026-01-03 23:59:59 : End datetime (Y-m-d H:i:s)}
        {--freq=60 : Interval minutes between points}
        {--keep-existing : Keep existing points in the range (no delete)}';

    protected $description = 'Generate synthetic water quality data for a user over a date/time range';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $start = Carbon::parse($this->option('start'));
        $end = Carbon::parse($this->option('end'));
        $freq = max(1, (int) $this->option('freq'));
        $keepExisting = (bool) $this->option('keep-existing');

        if ($end->lessThanOrEqualTo($start)) {
            $this->error('End must be after start.');
            return self::FAILURE;
        }

        $period = CarbonPeriod::create($start, "{$freq} minutes", $end);
        $rows = [];

        // Seed initial values in realistic ranges
        $temp = $this->randf(24.0, 30.0);
        $sal = $this->randf(10.0, 20.0);
        $do = $this->randf(5.0, 8.0);
        $ph = $this->randf(6.8, 7.6);

        foreach ($period as $dt) {
            // Gentle random walk with clamping
            $temp = $this->clamp($temp + $this->randf(-0.4, 0.4), 23.5, 31.0);
            $sal = $this->clamp($sal + $this->randf(-0.3, 0.3), 9.5, 20.5);
            $do = $this->clamp($do + $this->randf(-0.25, 0.25), 4.5, 8.5);
            $ph = $this->clamp($ph + $this->randf(-0.05, 0.05), 6.6, 7.8);

            $rows[] = [
                'user_id' => $userId,
                'recorded_at' => $dt->copy(),
                'temperature' => round($temp, 2),
                'salinity' => round($sal, 2),
                'dissolved_oxygen' => round($do, 2),
                'ph_level' => round($ph, 2),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! $keepExisting) {
            DB::table('water_quality_data')
                ->where('user_id', $userId)
                ->whereBetween('recorded_at', [$start, $end])
                ->delete();
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('water_quality_data')->insert($chunk);
        }

        $this->info('Inserted '.count($rows).' rows for user '.$userId.' between '.$start.' and '.$end.' (freq '.$freq.' minutes)');
        return self::SUCCESS;
    }

    private function randf(float $min, float $max): float
    {
        return $min + ((mt_rand() / mt_getrandmax()) * ($max - $min));
    }

    private function clamp(float $v, float $min, float $max): float
    {
        return min($max, max($min, $v));
    }
}
