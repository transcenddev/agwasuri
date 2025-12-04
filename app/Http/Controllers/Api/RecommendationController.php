<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Livewire\ClassifyFish;
use App\Models\WaterQualityData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    /**
     * Return a compact recommendation JSON for a user.
     * Cached for a short TTL to keep dashboard perf good.
     */
    public function show(Request $request)
    {
        $userId = $request->query('user') ?? session('selected_user', auth()->id());

        if (!$userId) {
            return response()->json(['error' => 'user_required'], 422);
        }

        $cacheKey = "recommendation:user:{$userId}";

        $result = Cache::remember($cacheKey, 120, function () use ($userId) {
            try {
                $avg = WaterQualityData::where('user_id', $userId)
                    ->selectRaw('AVG(temperature) as temperature, AVG(dissolved_oxygen) as dissolved_oxygen, AVG(salinity) as salinity, AVG(ph_level) as ph_level')
                    ->first();

                if (!$avg) {
                    return [
                        'species' => 'No data',
                        'confidence' => '0%',
                        'reason' => 'No water quality records available for this user.',
                        'computed_at' => now()->toDateTimeString(),
                    ];
                }

                $payload = [
                    'optimal_temperature_C' => (float) $avg->temperature,
                    'optimal_dissolved_oxygen_mgL' => (float) $avg->dissolved_oxygen,
                    'optimal_salinity_ppt' => (float) $avg->salinity,
                    'optimal_ph_value' => (float) $avg->ph_level,
                ];

                // Call external prediction service (same endpoint used by ClassifyFish)
                $response = Http::timeout(10)->asJson()->post(ClassifyFish::PREDICT_ENDPOINT, $payload);

                if ($response->successful()) {
                    $body = $response->json();

                    // Attempt to map response to compact shape
                    $top = data_get($body, 'predictions.0') ?: data_get($body, 'predictions');

                    if (is_array($top)) {
                        $species = data_get($top, 'species_name', 'Unknown');
                        $conf = data_get($top, 'confidence_percentage', data_get($body, 'model_accuracy_percentage', 0));
                    } else {
                        $species = data_get($body, 'predictions.0.species_name', 'Unknown');
                        $conf = data_get($body, 'model_accuracy_percentage', 0);
                    }

                    $confidenceText = is_numeric($conf) ? round((float) $conf) . '%' : (string) $conf;

                    return [
                        'species' => $species,
                        'confidence' => $confidenceText,
                        'reason' => 'Based on recent averaged water quality metrics.',
                        'computed_at' => now()->toDateTimeString(),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('RecommendationController: error while generating recommendation', ['message' => $e->getMessage()]);
            }

            // Fallback minimal response
            return [
                'species' => 'Tilapia (Oreochromis niloticus)',
                'confidence' => '60%',
                'reason' => 'Fallback heuristic: temperature and salinity appear suitable for Tilapia.',
                'computed_at' => now()->toDateTimeString(),
            ];
        });

        return response()->json($result);
    }
}
