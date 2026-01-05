<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaterQualityData;
use Illuminate\Support\Carbon;

class RecommendationCard extends Component
{
    public ?int $userId = null;

    public string $species = 'Tilapia (Oreochromis niloticus)';
    public ?float $confidence = null;
    public string $error = '';
    public bool $hasLatestData = false;
    public ?string $latestRecordedAt = null;
    public bool $hasPrediction = false;
    public array $lastPredictionPayload = [];
    
    // Current water quality values for parameter breakdown
    public ?float $temperature = null;
    public ?float $dissolved_oxygen = null;
    public ?float $salinity = null;
    public ?float $ph_level = null;
    
    public bool $done = false;
    public bool $reminded = false;

    const API_BASE_URL = 'https://api.agwasuri.app';
    const PREDICT_ENDPOINT = self::API_BASE_URL . '/predict';

    public function mount(?int $userId = null)
    {
        if ($userId !== null) {
            $this->userId = (int) $userId;
        }

        // Fetch latest water quality data for parameter breakdown
        $latest = WaterQualityData::where('user_id', $this->userId ?? auth()->id())
            ->latest('recorded_at')
            ->first();
            
        if ($latest) {
            $this->hasLatestData = true;
            $this->latestRecordedAt = optional($latest->recorded_at)->format('M d, Y h:i A');
            $this->temperature = $latest->temperature;
            $this->dissolved_oxygen = $latest->dissolved_oxygen;
            $this->salinity = $latest->salinity;
            $this->ph_level = $latest->ph_level;
        }
    }

    public function predictFishType()
    {
        $this->error = '';
        $this->hasPrediction = false;

        try {
            $query = WaterQualityData::where('user_id', $this->userId ?? auth()->id())
                ->whereNotNull('temperature')
                ->whereNotNull('salinity')
                ->whereNotNull('dissolved_oxygen')
                ->whereNotNull('ph_level');

            $averageData = $query->selectRaw(
                'AVG(temperature) as temperature,
                 AVG(salinity) as salinity,
                 AVG(dissolved_oxygen) as dissolved_oxygen,
                 AVG(ph_level) as ph_level'
            )->first();

            if (
                is_null($averageData) ||
                is_null($averageData->temperature) ||
                is_null($averageData->dissolved_oxygen) ||
                is_null($averageData->salinity) ||
                is_null($averageData->ph_level)
            ) {
                $this->error = 'No complete water quality data found.';
                Log::warning('RecommendationCard: No data found for averaging.', ['userId' => $this->userId]);
                return;
            }

            $response = Http::timeout(20)->asJson()->post(self::PREDICT_ENDPOINT, [
                'optimal_temperature_C' => (float) $averageData->temperature,
                'optimal_dissolved_oxygen_mgL' => (float) $averageData->dissolved_oxygen,
                'optimal_salinity_ppt' => (float) $averageData->salinity,
                'optimal_ph_value' => (float) $averageData->ph_level
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $predictions = (array) data_get($data, 'predictions', []);
                
                if (!empty($predictions)) {
                    $topPrediction = $predictions[0];
                    $this->species = data_get($topPrediction, 'species_name', 'Unknown');
                    $this->confidence = (float) data_get($topPrediction, 'confidence_percentage', 0);
                    $this->hasPrediction = true;

                    // Cache payload for handoff to full analysis page
                    $this->lastPredictionPayload = [
                        'user_id' => $this->userId ?? auth()->id(),
                        'water_quality' => [
                            'temperature' => round((float) $averageData->temperature, 2),
                            'salinity' => round((float) $averageData->salinity, 2),
                            'dissolved_oxygen' => round((float) $averageData->dissolved_oxygen, 2),
                            'ph_level' => round((float) $averageData->ph_level, 2),
                        ],
                        'predictions' => $predictions,
                        'model_metrics' => [
                            'accuracy' => (float) data_get($data, 'model_accuracy_percentage', 0),
                            'precision' => (float) data_get($data, 'precision_percentage', 0),
                            'recall' => (float) data_get($data, 'recall_percentage', 0),
                            'f1_score' => (float) data_get($data, 'f1_score_percentage', 0),
                        ],
                    ];
                }
            } else {
                Log::error('RecommendationCard: Prediction API failed.', ['status' => $response->status()]);
                $this->error = 'Failed to get prediction from API.';
            }
        } catch (\Exception $e) {
            Log::error('RecommendationCard: Exception during prediction.', ['message' => $e->getMessage()]);
            $this->error = 'An unexpected error occurred during prediction.';
        }
    }

    public function resetPrediction()
    {
        $this->hasPrediction = false;
        $this->error = '';
        $this->species = 'Tilapia (Oreochromis niloticus)';
        $this->confidence = null;
        $this->lastPredictionPayload = [];
    }

    public function viewFullAnalysis()
    {
        if (empty($this->lastPredictionPayload)) {
            $this->error = 'Run a prediction first to view full analysis.';
            return;
        }

        session()->put('prefill_analysis', $this->lastPredictionPayload);
        return redirect()->route('optimal-fish', ['user' => $this->userId ?? auth()->id()]);
    }

    public function getSpeciesImage(string $speciesName): ?string
    {
        $speciesFiles = glob(public_path('assets/species/*.{png,jpg,jpeg,svg,gif,webp}', GLOB_BRACE)) ?: [];
        $speciesMap = [];
        foreach ($speciesFiles as $file) {
            $filename = basename($file);
            $base = pathinfo($file, PATHINFO_FILENAME);
            $key = strtolower(preg_replace('/[^a-z0-9]+/', '', $base));
            $speciesMap[$key] = $filename;
        }

        $aliasMap = [
            'tilapia' => $speciesMap['tilapia'] ?? 'tilapia.png',
            'aquaticchicken' => $speciesMap['tilapia'] ?? 'tilapia.png',
            'oreochromisniloticus' => $speciesMap['tilapia'] ?? 'tilapia.png',
            'bangus' => $speciesMap['bangus'] ?? 'bangus.png',
            'milkfish' => $speciesMap['bangus'] ?? 'bangus.png',
            'sugpo' => $speciesMap['shrimp'] ?? 'shrimp.png',
            'prawn' => $speciesMap['shrimp'] ?? 'shrimp.png',
            'shrimp' => $speciesMap['shrimp'] ?? 'shrimp.png',
            'alimango' => $speciesMap['mudcrab'] ?? 'mudcrab.png',
            'mudcrab' => $speciesMap['mudcrab'] ?? 'mudcrab.png',
            'catfish' => $speciesMap['hito'] ?? 'hito.png',
            'pangasius' => $speciesMap['hito'] ?? 'hito.png',
            'hito' => $speciesMap['hito'] ?? 'hito.png',
            'dalag' => $speciesMap['dalag'] ?? 'dalag.png',
            'ayungin' => $speciesMap['ayungin'] ?? 'ayungin.png',
            'lapulapu' => $speciesMap['lapu-lapu'] ?? 'lapu-lapu.png',
            'grouper' => $speciesMap['lapu-lapu'] ?? 'lapu-lapu.png',
            'talakitok' => $speciesMap['talakitok'] ?? 'talakitok.png',
            'trevally' => $speciesMap['talakitok'] ?? 'talakitok.png',
            'mayamaya' => $speciesMap['maya-maya'] ?? 'maya-maya.png',
            'redsnapper' => $speciesMap['maya-maya'] ?? 'maya-maya.png',
        ];

        $normalize = function ($s) {
            return strtolower(preg_replace('/[^a-z0-9]+/', '', trim($s)));
        };

        $parts = explode('(', $speciesName);
        $primary = $normalize($parts[0] ?? $speciesName);
        $fullNormalized = $normalize($speciesName);

        if (isset($aliasMap[$primary])) {
            return $aliasMap[$primary];
        }

        if (isset($aliasMap[$fullNormalized])) {
            return $aliasMap[$fullNormalized];
        }

        if (isset($speciesMap[$primary])) {
            return $speciesMap[$primary];
        }

        foreach ($speciesMap as $k => $filename) {
            if (strpos($primary, $k) !== false || strpos($k, $primary) !== false) {
                return $filename;
            }
        }

        return null;
    }

    public function markAsDone()
    {
        $this->done = true;
        $this->dispatchBrowserEvent('notification', ['message' => 'Recommendation marked as done.']);
    }

    public function remindLater()
    {
        $this->reminded = true;
        $this->dispatchBrowserEvent('notification', ['message' => 'Reminder scheduled for later.']);
    }

    public function render()
    {
        return view('livewire.recommendation-card');
    }
}
