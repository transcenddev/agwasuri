<?php

namespace App\Services;

use App\Models\User;
use App\Models\WaterQualityData;
use App\Events\WaterQualityCreated;
use Illuminate\Support\Carbon;

class SampleWaterQualityService
{
    /**
     * Create a simulated water quality row for the given user and optionally broadcast.
     *
     * @param User $user
     * @param Carbon $recordedAt
     * @param bool $noBroadcast
     * @return WaterQualityData
     */
    public function createSampleForUser(User $user, Carbon $recordedAt, bool $noBroadcast = false): WaterQualityData
    {
        $temperature = round(24 + lcg_value() * 7, 1);
        $ph = round(6 + lcg_value() * 2.5, 2);
        $dissolvedOxygen = round(3 + lcg_value() * 6, 2);
        $salinity = round(lcg_value() * 15, 2);

        $data = WaterQualityData::create([
            'user_id' => $user->user_id,
            'recorded_at' => $recordedAt->toDateTimeString(),
            'temperature' => $temperature,
            'ph_level' => $ph,
            'dissolved_oxygen' => $dissolvedOxygen,
            'salinity' => $salinity,
        ]);

        if (!$noBroadcast) {
            event(new WaterQualityCreated($data));
        }

        return $data;
    }
}
