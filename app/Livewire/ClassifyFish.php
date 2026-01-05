<?php

namespace App\Livewire;

use App\Models\ModelMetadata;
use App\Models\WaterQualityData;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;

class ClassifyFish extends Component
{
    public $predictionResults = [];
    public $modelMetrics = [];
    public $error = null;
    public $waterQualityData = null;
    public $explanations = [];

    public ?int $userId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public bool $isAdminView = false;
    public bool $canPredict = false;

    public ?string $lastTrainedTimestamp = null;
    public bool $isTraining = false;

    // FIX: base is root only
    const API_BASE_URL = 'https://api.agwasuri.app';
    const PREDICT_ENDPOINT = self::API_BASE_URL . '/predict';
    const TRAIN_ENDPOINT   = self::API_BASE_URL . '/train_model';
    const MODEL_IDENTIFIER = 'fish_classifier';

    #[On('user-selected')]
    public function updateUser($userId)
    {
        Log::info('ClassifyFish: User selected.', ['userId' => $userId]);
        $this->userId = (int) $userId;
        $this->resetState();
        $this->checkCanPredict();
    }

    #[On('date-range-updated')]
    public function updateDates($startDate, $endDate)
    {
        // Normalize payload from Livewire dispatch (named params) or browser CustomEvent detail arrays
        if (is_array($startDate) && $endDate === null) {
            $payload = $startDate['detail'] ?? $startDate;
            $startDate = $payload['startDate'] ?? $payload['start'] ?? $payload[0] ?? null;
            $endDate = $payload['endDate'] ?? $payload['end'] ?? $payload[1] ?? null;
        }

        Log::info('ClassifyFish: Date range updated.', ['start' => $startDate, 'end' => $endDate]);
        $this->startDate = $startDate ?: null;
        $this->endDate = $endDate ?: null;
        $this->resetState();
        $this->checkCanPredict();
    }

    private function resetState(bool $clearError = true)
    {
        $this->predictionResults = [];
        $this->modelMetrics = [];
        $this->explanations = [];
        $this->waterQualityData = null;
        if ($clearError) {
            $this->error = null;
        }
    }

    private function checkCanPredict()
    {
        $this->canPredict = !is_null($this->userId);
        Log::info('ClassifyFish: CheckCanPredict.', ['userId' => $this->userId, 'canPredict' => $this->canPredict]);
    }

    public function mount()
    {
        Log::info('ClassifyFish: Mounting component.');
        $loggedInUser = Auth::user();

        if (!$loggedInUser || !$loggedInUser->getKey()) {
            Log::error('ClassifyFish: Mount - Failed to get authenticated user or user ID is missing.');
            $this->canPredict = false;
            return;
        }

        $providedUserId = $this->userId; // set via property/route when available
        if ($this->userId === null) {
            $this->userId = $loggedInUser->getKey();
        }

        $this->isAdminView = method_exists($loggedInUser, 'isAdmin') ? $loggedInUser->isAdmin() : false;

        if ($this->isAdminView) {
            // Allow pre-selected user (from query/session) if it differs from admin id; otherwise await selection
            if ($providedUserId !== null && (int) $providedUserId !== (int) $loggedInUser->getKey()) {
                $this->userId = (int) $providedUserId;
                Log::info('ClassifyFish: Mount - Admin with preselected user.', ['userId' => $this->userId]);
            } else {
                $this->userId = null;
                Log::info('ClassifyFish: Mount - Admin view, awaiting user selection.');
            }
            $this->fetchLastTrainedTimestampFromDb();
        } else {
            // Non-admin keeps provided user if set, otherwise defaults to own id
            $this->userId = $providedUserId ?? $this->userId;
            Log::info('ClassifyFish: Mount - Non-admin view.', ['userId' => $this->userId]);
        }
        $this->checkCanPredict();

        // Hydrate from prefilled session payload when coming from dashboard "View Analysis"
        $this->hydrateFromPrefill();
    }

    private function hydrateFromPrefill(): void
    {
        $prefill = session()->pull('prefill_analysis');
        if (!$prefill || !is_array($prefill)) {
            return;
        }

        // Respect admin selection; if payload user differs from admin context, ignore
        if ($this->isAdminView && (!isset($prefill['user_id']) || (int) $prefill['user_id'] !== (int) ($this->userId ?? 0))) {
            Log::info('ClassifyFish: Prefill ignored due to admin user mismatch.', ['payloadUser' => $prefill['user_id'] ?? null, 'currentUser' => $this->userId]);
            return;
        }

        $this->userId = (int) ($prefill['user_id'] ?? $this->userId);
        $this->canPredict = !is_null($this->userId);
        $this->waterQualityData = $prefill['water_quality'] ?? null;
        $this->predictionResults = $prefill['predictions'] ?? [];
        $this->modelMetrics = $prefill['model_metrics'] ?? [];
        $this->error = null;
        $this->generateExplanations();
        Log::info('ClassifyFish: Hydrated from prefill payload.', ['userId' => $this->userId, 'predictions' => count($this->predictionResults)]);
    }

    public function fetchLastTrainedTimestampFromDb()
    {
        if (!$this->isAdminView) return;

        Log::info('ClassifyFish: Fetching last trained timestamp from DB.');
        try {
            $metadata = ModelMetadata::where('model_name', self::MODEL_IDENTIFIER)->first();

            if ($metadata && $metadata->last_trained_at) {
                $this->lastTrainedTimestamp = $metadata->last_trained_at->format('Y-m-d H:i:s');
                Log::info('ClassifyFish: Last trained timestamp fetched from DB.', ['timestamp' => $this->lastTrainedTimestamp]);
            } else {
                Log::warning('ClassifyFish: Timestamp not found in DB for model.', ['model_name' => self::MODEL_IDENTIFIER]);
                $this->lastTrainedTimestamp = 'N/A';
            }
        } catch (\Exception $e) {
            Log::error('ClassifyFish: Exception fetching last trained timestamp from DB.', ['message' => $e->getMessage()]);
            $this->lastTrainedTimestamp = 'Error fetching date';
        }
    }

    public function trainModel()
    {
        if (!$this->isAdminView) {
            session()->flash('train_error', 'You do not have permission to train the model.');
            return;
        }

        if ($this->isTraining) {
            return;
        }

        Log::info('ClassifyFish: Initiating model training request.');
        $this->isTraining = true;
        session()->forget(['train_message', 'train_error']);

        try {
            // FIX: send JSON + timeout
            $response = Http::timeout(30)->asJson()->post(self::TRAIN_ENDPOINT);

            if ($response->successful()) {
                $responseData = $response->json();
                $message = $responseData['message'] ?? 'Model training completed successfully.';
                session()->flash('train_message', $message);
                Log::info('ClassifyFish: Model training request successful.', ['response' => $responseData]);

                if (!empty($responseData['timestamp'])) {
                    $timestampString = $responseData['timestamp'];
                    try {
                        $parsedTimestamp = Carbon::parse($timestampString);

                        $metadata = ModelMetadata::updateOrCreate(
                            ['model_name' => self::MODEL_IDENTIFIER],
                            ['last_trained_at' => $parsedTimestamp]
                        );

                        $this->lastTrainedTimestamp = $metadata->last_trained_at->format('Y-m-d H:i:s');
                        Log::info('ClassifyFish: Saved/Updated last trained timestamp in DB.', ['timestamp' => $this->lastTrainedTimestamp]);

                    } catch (\Exception $parseOrSaveError) {
                        Log::error('ClassifyFish: Failed to parse or save timestamp from train response.', [
                            'timestamp_string' => $timestampString,
                            'error' => $parseOrSaveError->getMessage()
                        ]);
                        session()->flash('train_error', 'Training successful, but failed to record timestamp locally.');
                        $this->fetchLastTrainedTimestampFromDb();
                    }
                } else {
                    Log::warning('ClassifyFish: Timestamp missing in successful train response.', ['response' => $responseData]);
                    session()->flash('train_error', 'Training successful, but timestamp was missing in response.');
                }

            } else {
                $errorDetail = data_get($response->json(), 'detail', 'Unknown error occurred during training.');
                $errorMessage = 'Failed to train model (Status: ' . $response->status() . ') - ' . $errorDetail;
                session()->flash('train_error', $errorMessage);
                Log::error('ClassifyFish: Model training request failed.', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            $errorMessage = 'An unexpected error occurred while contacting the training service: ' . $e->getMessage();
            session()->flash('train_error', $errorMessage);
            Log::error('ClassifyFish: Exception during trainModel request.', ['message' => $e->getMessage()]);
        } finally {
            $this->isTraining = false;
        }
    }

    public function predictFishType()
    {
        if (!$this->canPredict || is_null($this->userId)) {
            $this->error = 'Please select a user to perform prediction.';
            Log::warning('ClassifyFish: Predict attempt without user selected.', ['userId' => $this->userId]);
            return;
        }

        Log::info('ClassifyFish: Starting prediction.', ['userId' => $this->userId, 'startDate' => $this->startDate, 'endDate' => $this->endDate]);

        // Clear prior results but keep error until success
        $this->resetState(clearError: false);

        try {
            // Basic date guard
            $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
            $end   = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;

            $query = WaterQualityData::where('user_id', $this->userId)
                ->whereNotNull('temperature')
                ->whereNotNull('salinity')
                ->whereNotNull('dissolved_oxygen')
                ->whereNotNull('ph_level');

            if ($start && $end) {
                $query->whereBetween('recorded_at', [$start, $end]);
            } elseif ($start) {
                $query->where('recorded_at', '>=', $start);
            } elseif ($end) {
                $query->where('recorded_at', '<=', $end);
            }

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
                $this->error = 'No complete water quality data found for the selected user' . ($this->startDate || $this->endDate ? ' in the specified date range.' : '.');
                Log::warning('ClassifyFish: No data found for averaging.', ['userId' => $this->userId, 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
                return;
            }

            // canonical order: temperature, salinity, dissolved_oxygen, ph_level
            $this->waterQualityData = [
                'temperature' => round((float)$averageData->temperature, 2),
                'salinity' => round((float)$averageData->salinity, 2),
                'dissolved_oxygen' => round((float)$averageData->dissolved_oxygen, 2),
                'ph_level' => round((float)$averageData->ph_level, 2),
            ];

            // FIX: send JSON + timeout
            $response = Http::timeout(20)->asJson()->post(self::PREDICT_ENDPOINT, [
                'optimal_temperature_C' => (float) $this->waterQualityData['temperature'],
                'optimal_dissolved_oxygen_mgL' => (float) $this->waterQualityData['dissolved_oxygen'],
                'optimal_salinity_ppt' => (float) $this->waterQualityData['salinity'],
                'optimal_ph_value' => (float) $this->waterQualityData['ph_level']
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Guard keys
                $this->predictionResults = (array) data_get($data, 'predictions', []);
                $this->modelMetrics = [
                    'accuracy' => (float) data_get($data, 'model_accuracy_percentage', 0),
                    'precision' => (float) data_get($data, 'precision_percentage', 0),
                    'recall' => (float) data_get($data, 'recall_percentage', 0),
                    'f1_score' => (float) data_get($data, 'f1_score_percentage', 0),
                ];

                $this->generateExplanations();
                $this->error = null; // success clears error
            } else {
                Log::error('ClassifyFish: Prediction API request failed.', ['status' => $response->status(), 'body' => $response->body()]);
                $this->error = 'Failed to get prediction from API (Status: ' . $response->status() . ')';
                // keep prior diagnostics
            }
        } catch (\Exception $e) {
            Log::error('ClassifyFish: Exception during prediction.', ['message' => $e->getMessage()]);
            $this->error = 'An unexpected error occurred during prediction: ' . $e->getMessage();
        }
    }

    private function generateExplanations()
    {
        if (is_null($this->waterQualityData)) {
            return;
        }

        $dateRangeText = '';
        if($this->startDate && $this->endDate) {
            $dateRangeText = " between {$this->startDate} and {$this->endDate}";
        } elseif ($this->startDate) {
            $dateRangeText = " from {$this->startDate} onwards";
        } elseif ($this->endDate) {
            $dateRangeText = " up to {$this->endDate}";
        } else {
            $dateRangeText = " across all available records";
        }

        $this->explanations['predictions'] = "Based on the average water quality measurements for the selected user{$dateRangeText}:
            Temperature: {$this->waterQualityData['temperature']}°C,
            Salinity: {$this->waterQualityData['salinity']} ppt,
            Dissolved Oxygen: {$this->waterQualityData['dissolved_oxygen']} mg/L,
            pH: {$this->waterQualityData['ph_level']}.";

        if (!empty($this->predictionResults)) {
            $topPrediction = $this->predictionResults[0];
            $name = data_get($topPrediction, 'species_name', 'N/A');
            $conf = (float) data_get($topPrediction, 'confidence_percentage', 0);
            $this->explanations['top_prediction'] = "The model predicts that {$name} is the most suitable species with {$conf}% confidence.";

            if ($conf >= 90) {
                $this->explanations['confidence_context'] = "This is a very high confidence prediction.";
            } elseif ($conf >= 70) {
                $this->explanations['confidence_context'] = "This is a moderately high confidence prediction.";
            } else {
                $this->explanations['confidence_context'] = "This prediction has lower confidence. Consider monitoring water quality parameters closely.";
            }
        } else {
            $this->explanations['top_prediction'] = "No prediction could be made.";
            $this->explanations['confidence_context'] = '';
        }

        if (!empty($this->modelMetrics)) {
            $this->explanations['metrics'] = [
                'accuracy' => "The model correctly identifies fish species {$this->modelMetrics['accuracy']}% of the time.",
                'precision' => "When the model predicts a specific fish species, it is correct {$this->modelMetrics['precision']}% of the time.",
                'recall' => "The model successfully identifies {$this->modelMetrics['recall']}% of actual occurrences of each fish species.",
                'f1_score' => "The overall balanced performance score of the model is {$this->modelMetrics['f1_score']}%."
            ];
        } else {
            $this->explanations['metrics'] = [];
        }
    }

    /**
     * Resolve a species image filename from a species name returned by the model.
     * Returns the filename (e.g. 'tilapia.png') or null when not found.
     */
    public function getSpeciesImage(string $speciesName): ?string
    {
        // Discover available species images
        $speciesFiles = glob(public_path('assets/species/*.{png,jpg,jpeg,svg,gif}', GLOB_BRACE)) ?: [];
        $speciesMap = [];
        foreach ($speciesFiles as $file) {
            $filename = basename($file);
            $base = pathinfo($file, PATHINFO_FILENAME);
            $key = strtolower(preg_replace('/[^a-z0-9]+/', '', $base));
            $speciesMap[$key] = $filename;
        }

        // Alias map for common synonyms (map normalized keys to actual filenames)
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

        // Helper: normalize (remove non-alnum and lowercase)
        $normalize = function ($s) {
            return strtolower(preg_replace('/[^a-z0-9]+/', '', trim($s)));
        };

        // Primary name: text before parentheses
        $parts = explode('(', $speciesName);
        $primary = $normalize($parts[0] ?? $speciesName);
        $fullNormalized = $normalize($speciesName);

        // Strategy 1: alias map by primary name
        if (isset($aliasMap[$primary])) {
            return $aliasMap[$primary];
        }

        // Strategy 2: alias map by full normalized
        if (isset($aliasMap[$fullNormalized])) {
            return $aliasMap[$fullNormalized];
        }

        // Strategy 3: direct match to discovered filenames
        if (isset($speciesMap[$primary])) {
            return $speciesMap[$primary];
        }

        // Strategy 4: substring matching
        foreach ($speciesMap as $k => $filename) {
            if (strpos($primary, $k) !== false || strpos($k, $primary) !== false) {
                return $filename;
            }
        }

        return null;
    }

    /**
     * Get water quality status with bilingual badge and color
     */
    public function getWaterQualityStatus(string $parameter, float $value): array
    {
        $status = ['badge' => 'OKAY', 'badgeTl' => 'OKAY', 'color' => 'gray', 'emoji' => '⚪'];

        switch ($parameter) {
            case 'temperature':
                if ($value >= 28 && $value <= 32) {
                    $status = ['badge' => 'GOOD', 'badgeTl' => 'MABUTI', 'color' => 'green', 'emoji' => '🟢'];
                } elseif (($value >= 25 && $value < 28) || ($value > 32 && $value <= 35)) {
                    $status = ['badge' => 'MODERATE', 'badgeTl' => 'KATAMTAMAN', 'color' => 'yellow', 'emoji' => '🟡'];
                } else {
                    $status = ['badge' => 'POOR', 'badgeTl' => 'HINDI MAGANDA', 'color' => 'red', 'emoji' => '🔴'];
                }
                break;

            case 'salinity':
                if ($value >= 15 && $value <= 25) {
                    $status = ['badge' => 'GOOD', 'badgeTl' => 'MABUTI', 'color' => 'green', 'emoji' => '🟢'];
                } elseif (($value >= 10 && $value < 15) || ($value > 25 && $value <= 30)) {
                    $status = ['badge' => 'MODERATE', 'badgeTl' => 'KATAMTAMAN', 'color' => 'yellow', 'emoji' => '🟡'];
                } else {
                    $status = ['badge' => 'POOR', 'badgeTl' => 'HINDI MAGANDA', 'color' => 'red', 'emoji' => '🔴'];
                }
                break;

            case 'dissolved_oxygen':
                if ($value > 5) {
                    $status = ['badge' => 'GOOD', 'badgeTl' => 'MABUTI', 'color' => 'green', 'emoji' => '🟢'];
                } elseif ($value >= 3 && $value <= 5) {
                    $status = ['badge' => 'MODERATE', 'badgeTl' => 'KATAMTAMAN', 'color' => 'yellow', 'emoji' => '🟡'];
                } else {
                    $status = ['badge' => 'LOW', 'badgeTl' => 'MABABA', 'color' => 'red', 'emoji' => '🔴'];
                }
                break;

            case 'ph_level':
                if ($value >= 7 && $value <= 8.5) {
                    $status = ['badge' => 'GOOD', 'badgeTl' => 'MABUTI', 'color' => 'green', 'emoji' => '🟢'];
                } elseif (($value >= 6.5 && $value < 7) || ($value > 8.5 && $value <= 9)) {
                    $status = ['badge' => 'MODERATE', 'badgeTl' => 'KATAMTAMAN', 'color' => 'yellow', 'emoji' => '🟡'];
                } else {
                    $status = ['badge' => 'POOR', 'badgeTl' => 'HINDI MAGANDA', 'color' => 'red', 'emoji' => '🔴'];
                }
                break;
        }

        return $status;
    }

    /**
     * Get confidence level description
     */
    public function getConfidenceLevel(float $percentage): array
    {
        if ($percentage >= 80) {
            return [
                'text' => 'HIGH CONFIDENCE',
                'textTl' => 'MATAAS ANG CONFIDENCE',
                'color' => 'green',
                'emoji' => '🟢'
            ];
        } elseif ($percentage >= 60) {
            return [
                'text' => 'MODERATE CONFIDENCE',
                'textTl' => 'KATAMTAMANG CONFIDENCE',
                'color' => 'yellow',
                'emoji' => '🟡'
            ];
        } else {
            return [
                'text' => 'LOW CONFIDENCE',
                'textTl' => 'MABABANG CONFIDENCE',
                'color' => 'red',
                'emoji' => '🔴'
            ];
        }
    }

    /**
     * Reset prediction to show empty state
     */
    public function resetPrediction()
    {
        $this->resetState();
        Log::info('ClassifyFish: Prediction reset by user.');
    }

    public function render()
    {
        return view('livewire.classify-fish', [
            'predictionResults' => $this->predictionResults,
            'modelMetrics' => $this->modelMetrics,
            'error' => $this->error,
            'explanations' => $this->explanations,
            'waterQualityData' => $this->waterQualityData,
            'canPredict' => $this->canPredict,
            'isTraining' => $this->isTraining,
            'lastTrainedTimestamp' => $this->lastTrainedTimestamp
        ]);
    }
}
