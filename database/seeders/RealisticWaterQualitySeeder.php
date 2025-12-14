<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaterQualityData;
use Carbon\Carbon;

class RealisticWaterQualitySeeder extends Seeder
{
    public function run()
    {
        $userId = 3;
        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();
        $interval = 60; // 1 hour

        $current = $startDate->copy();
        $batch = [];

        while ($current->lte($endDate)) {
            $hour = $current->hour;
            
            // Morning (6am-9am): cooler, higher DO
            // Afternoon (12pm-4pm): warmer, lower DO
            // Night (8pm-6am): stable
            
            $tempBase = 27; // Base temperature
            $tempVariation = match(true) {
                $hour >= 6 && $hour < 9   => rand(-20, 0) / 10,  // Cooler morning
                $hour >= 12 && $hour < 16 => rand(10, 40) / 10,  // Warmer afternoon
                default                   => rand(-10, 10) / 10, // Stable night
            };
            
            $doBase = 7;
            $doVariation = match(true) {
                $hour >= 6 && $hour < 9   => rand(5, 15) / 10,   // Higher DO morning
                $hour >= 12 && $hour < 16 => rand(-15, -5) / 10, // Lower DO afternoon
                default                   => rand(-5, 5) / 10,   // Stable night
            };

            $batch[] = [
                'user_id' => $userId,
                'temperature' => $tempBase + $tempVariation,
                'salinity' => rand(280, 320) / 10,
                'dissolved_oxygen' => max(5.0, $doBase + $doVariation),
                'ph' => rand(70, 80) / 10,
                'timestamp' => $current->toDateTimeString(),
                'created_at' => $current,
                'updated_at' => $current,
            ];

            // Insert in batches of 500 for speed
            if (count($batch) >= 500) {
                WaterQualityData::insert($batch);
                echo ".";
                $batch = [];
            }

            $current->addMinutes($interval);
        }

        // Insert remaining
        if (!empty($batch)) {
            WaterQualityData::insert($batch);
        }

        $total = WaterQualityData::where('user_id', $userId)->count();
        echo "\nGenerated {$total} realistic records for user {$userId}\n";
    }
}
