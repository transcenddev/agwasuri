<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class WaterQualityCard extends Component
{
    public $temperature;
    public $dissolved_oxygen;
    public $salinity;
    public $ph_level;
    // classification arrays returned by classifyWaterParam
    public $temperatureClassification = [];
    public $dissolvedOxygenClassification = [];
    public $salinityClassification = [];
    public $phClassification = [];
    public $lastUpdated;
    public $connectionStatus = 'disconnected';
    public $overallStatus = 'Disconnected';
    public $overallScore = 0; // Overall water quality score (0-100)

    // Delta properties (difference between latest and previous measurement)
    public ?float $temperatureDelta = null;
    public ?float $dissolvedOxygenDelta = null;
    public ?float $phLevelDelta = null;
    public ?float $salinityDelta = null;

    // Sparkline SVG path strings for small trend charts on the cards
    public string $temperatureSparklinePath = '';
    public string $salinitySparklinePath = '';
    public string $doSparklinePath = '';
    public string $phSparklinePath = '';

    // Sparkline stroke colors sourced from config/water_colors
    public array $sparklineColors = [];

    public ?int $userId = null;

    public ?string $startDate = null;
    public ?string $endDate = null;

    #[On('user-selected')]
    public function updateUser($userId)
    {
        $this->userId = $userId;
        $this->loadData();
    }

    #[On('date-range-updated')]
    public function updateDateRange(?string $startDate, ?string $endDate)
    {
        // Accept both Livewire-dispatched named params and CustomEvent detail arrays
        if (is_array($startDate) && $endDate === null) {
            $payload = $startDate['detail'] ?? $startDate;
            $startDate = $payload['startDate'] ?? $payload['start'] ?? $payload[0] ?? null;
            $endDate = $payload['endDate'] ?? $payload['end'] ?? $payload[1] ?? null;
        }

        $this->startDate = $startDate ?: null;
        $this->endDate = $endDate ?: null;
        $this->loadData();
    }

    #[On('echo:water-quality-data-created,WaterQualityCreated')]
    public function handleNewDataCreated(array $event) {
        $matchesUser = isset($event['waterQualityData']['user_id']) && $event['waterQualityData']['user_id'] == $this->userId;
        if (!$matchesUser) {
            return;
        }

        $matchesDate = true;
        if ($this->startDate || $this->endDate) {
            try {
                $recordDate = Carbon::parse($event['waterQualityData']['recorded_at']);
                if ($this->startDate && $this->endDate) {
                    $matchesDate = $recordDate->greaterThanOrEqualTo(Carbon::parse($this->startDate)->startOfDay());
                } elseif ($this->startDate) {
                    $matchesDate = $recordDate->isSameDay(Carbon::parse($this->startDate));
                }
            } catch (\Exception $e) {
                $matchesDate = false;
            }
        }

        if ($matchesDate) {
            $this->loadData();
        }
    }

    public function mount(?int $userId = null): void
    {
        if ($userId !== null) {
            $this->userId = (int) $userId;
        } elseif (request()->has('user') && request('user')) {
            $this->userId = (int) request('user');
        } else {
            $this->userId = (int) session('selected_user', Auth::id());
        }

        $this->loadData();
    }

    public function loadData()
    {
        $baseQuery = WaterQualityData::where('user_id', $this->userId);

        if ($this->startDate && $this->endDate) {
            $baseQuery->whereBetween('recorded_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        } elseif ($this->startDate) {
            $baseQuery->whereDate('recorded_at', $this->startDate);
        }

        // latest reading
        $waterQualityData = (clone $baseQuery)->orderBy('recorded_at', 'desc')->first();

        if ($waterQualityData) {
            $this->temperature = $waterQualityData->temperature;
            $this->salinity = $waterQualityData->salinity;
            $this->ph_level = $waterQualityData->ph_level;
            $this->dissolved_oxygen = $waterQualityData->dissolved_oxygen;
            $this->lastUpdated = Carbon::parse($waterQualityData->recorded_at)->toDateTimeString();
            $this->connectionStatus = 'connected';

            // Try to fetch the previous reading (if any) to compute deltas
            $previous = (clone $baseQuery)
                ->where('recorded_at', '<', $waterQualityData->recorded_at)
                ->orderBy('recorded_at', 'desc')
                ->first();

            if ($previous) {
                $this->temperatureDelta = is_numeric($this->temperature) && is_numeric($previous->temperature)
                    ? (float) $this->temperature - (float) $previous->temperature
                    : null;

                $this->dissolvedOxygenDelta = is_numeric($this->dissolved_oxygen) && is_numeric($previous->dissolved_oxygen)
                    ? (float) $this->dissolved_oxygen - (float) $previous->dissolved_oxygen
                    : null;

                $this->phLevelDelta = is_numeric($this->ph_level) && is_numeric($previous->ph_level)
                    ? (float) $this->ph_level - (float) $previous->ph_level
                    : null;

                $this->salinityDelta = is_numeric($this->salinity) && is_numeric($previous->salinity)
                    ? (float) $this->salinity - (float) $previous->salinity
                    : null;
            } else {
                $this->temperatureDelta = null;
                $this->dissolvedOxygenDelta = null;
                $this->phLevelDelta = null;
                $this->salinityDelta = null;
            }

            // compute classifications for each metric using global helper
            try {
                $this->temperatureClassification = classifyWaterParam('temperature', $this->temperature);
            } catch (\Throwable $e) {
                $this->temperatureClassification = ['label' => 'UNKNOWN', 'note' => 'classification error', 'min' => null, 'max' => null];
            }

            try {
                $this->dissolvedOxygenClassification = classifyWaterParam('do', $this->dissolved_oxygen);
            } catch (\Throwable $e) {
                $this->dissolvedOxygenClassification = ['label' => 'UNKNOWN', 'note' => 'classification error', 'min' => null, 'max' => null];
            }

            try {
                $this->phClassification = classifyWaterParam('ph', $this->ph_level);
            } catch (\Throwable $e) {
                $this->phClassification = ['label' => 'UNKNOWN', 'note' => 'classification error', 'min' => null, 'max' => null];
            }

            try {
                $this->salinityClassification = classifyWaterParam('salinity', $this->salinity);
            } catch (\Throwable $e) {
                $this->salinityClassification = ['label' => 'UNKNOWN', 'note' => 'classification error', 'min' => null, 'max' => null];
            }

            // add color suggestion derived from label for convenient view usage
            $this->addClassificationColors();

            // Build sparklines for recent points (12 most recent)
            try {
                $recent = (clone $baseQuery)->orderBy('recorded_at', 'desc')->limit(12)->get()->reverse()->values();

                $tempValues = $recent->map(fn($r) => is_null($r->temperature) ? null : (float) $r->temperature)->filter(fn($v) => !is_null($v))->values()->all();
                $salValues = $recent->map(fn($r) => is_null($r->salinity) ? null : (float) $r->salinity)->filter(fn($v) => !is_null($v))->values()->all();
                $doValues = $recent->map(fn($r) => is_null($r->dissolved_oxygen) ? null : (float) $r->dissolved_oxygen)->filter(fn($v) => !is_null($v))->values()->all();
                $phValues = $recent->map(fn($r) => is_null($r->ph_level) ? null : (float) $r->ph_level)->filter(fn($v) => !is_null($v))->values()->all();

                $this->temperatureSparklinePath = $this->buildSparklinePath($tempValues, 72, 28, 4);
                $this->salinitySparklinePath = $this->buildSparklinePath($salValues, 72, 28, 4);
                $this->doSparklinePath = $this->buildSparklinePath($doValues, 72, 28, 4);
                $this->phSparklinePath = $this->buildSparklinePath($phValues, 72, 28, 4);
            } catch (\Throwable $e) {
                $this->temperatureSparklinePath = '';
                $this->salinitySparklinePath = '';
                $this->doSparklinePath = '';
                $this->phSparklinePath = '';
            }

            // Sparkline stroke colors (use series colors from config, fallback to sensible defaults)
            $series = config('water_colors.series', []);
            $this->sparklineColors = [
                'temperature' => $series['temperature'] ?? '#ef4444',
                'salinity' => $series['salinity'] ?? '#14b8a6',
                'dissolved_oxygen' => $series['do'] ?? '#2563eb',
                'ph_level' => $series['ph'] ?? '#8b5cf6',
            ];

            // compute overall status after loading latest metrics
            $this->computeOverallStatus();
            $this->calculateOverallScore();
        } else {
            $this->temperature = null;
            $this->salinity = null;
            $this->ph_level = null;
            $this->dissolved_oxygen = null;
            $this->lastUpdated = null;
            $this->connectionStatus = 'disconnected';
            $this->overallStatus = 'Disconnected';

            $this->temperatureDelta = null;
            $this->dissolvedOxygenDelta = null;
            $this->phLevelDelta = null;
            $this->salinityDelta = null;
        }
    }

    /**
     * Compute an aggregated overall status from individual metric statuses.
     * Priority: harm > bad > good > best > disconnected
     */
    protected function computeOverallStatus(): void
    {
        $statuses = [];

        $statuses[] = $this->statusFor('temperature', $this->temperature);
        $statuses[] = $this->statusFor('salinity', $this->salinity);
        $statuses[] = $this->statusFor('dissolved_oxygen', $this->dissolved_oxygen);
        $statuses[] = $this->statusFor('ph_level', $this->ph_level);

        if (in_array('harm', $statuses, true)) {
            $this->overallStatus = 'HARM';
            return;
        }

        if (in_array('bad', $statuses, true)) {
            $this->overallStatus = 'BAD';
            return;
        }

        if (in_array('good', $statuses, true)) {
            $this->overallStatus = 'GOOD';
            return;
        }

        // if all available metrics are best
        if (collect($statuses)->filter(fn($s) => $s !== 'unknown')->every(fn($s) => $s === 'best')) {
            $this->overallStatus = 'BEST';
            return;
        }

        // if all unknown or null, mark disconnected
        if (collect($statuses)->every(fn($s) => $s === 'unknown')) {
            $this->overallStatus = 'Disconnected';
            return;
        }

        $this->overallStatus = 'BEST';
    }

    /**
     * Calculate overall water quality score (0-100) as weighted average
     * Weights: DO 30%, Temp 25%, pH 25%, Salinity 20%
     */
    protected function calculateOverallScore(): void
    {
        $scores = [
            'temperature' => ['weight' => 0.25, 'score' => $this->parameterScore('temperature', $this->temperature)],
            'dissolved_oxygen' => ['weight' => 0.30, 'score' => $this->parameterScore('dissolved_oxygen', $this->dissolved_oxygen)],
            'ph_level' => ['weight' => 0.25, 'score' => $this->parameterScore('ph_level', $this->ph_level)],
            'salinity' => ['weight' => 0.20, 'score' => $this->parameterScore('salinity', $this->salinity)],
        ];

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($scores as $param) {
            if ($param['score'] !== null) {
                $totalScore += $param['score'] * $param['weight'];
                $totalWeight += $param['weight'];
            }
        }

        // Normalize to 0-100; default to 0 if no valid parameters
        $this->overallScore = $totalWeight > 0 ? round($totalScore / $totalWeight) : 0;
    }

    /**
     * Calculate individual parameter score (0-100) based on status
     * good = 100%, warning = 70-90% (interpolated), danger = 0-69% (interpolated)
     */
    protected function parameterScore(string $metric, $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $status = $this->statusFor($metric, $value);

        return match($status) {
            'good' => 100,
            'warning' => 80, // mid-range for warning
            'danger' => 40,  // mid-range for danger
            'unknown' => 0,
            default => 0,
        };
    }

    /**
     * Return badge text and CSS classes for overall score display
     */
    public function scoreStatusBadge(): array
    {
        if ($this->overallScore >= 90) {
            return [
                'text' => 'EXCELLENT',
                'classes' => 'bg-green-100 text-green-800',
                'icon' => '🟢'
            ];
        }

        if ($this->overallScore >= 70) {
            return [
                'text' => 'MODERATE',
                'classes' => 'bg-yellow-100 text-yellow-800',
                'icon' => '🟡'
            ];
        }

        return [
            'text' => 'CRITICAL',
            'classes' => 'bg-red-100 text-red-800',
            'icon' => '🔴'
        ];
    }

    /**
     * Return description text based on score
     */
    public function scoreDescription(): string
    {
        if ($this->overallScore >= 90) {
            return 'Water quality is excellent. All parameters optimal.';
        }

        if ($this->overallScore >= 70) {
            return 'Water quality is acceptable but needs monitoring.';
        }

        return 'Water quality is critical. Immediate action required.';
    }

    public function overallClassesForStatus(string $status): string
    {
        return match(strtolower($status)) {
            'best' => 'bg-green-50 text-green-800',
            'good' => 'bg-blue-50 text-blue-800',
            'bad' => 'bg-yellow-50 text-yellow-800',
            'harm' => 'bg-rose-50 text-rose-700',
            'disconnected' => 'bg-amber-50 text-amber-800',
            default => 'bg-gray-50 text-gray-700',
        };
    }

    /**
     * Return a status string for a given metric value: good|warning|danger
     */
    public function statusFor(string $metric, $value): string
    {
        if ($value === null) {
            return 'unknown';
        }

        switch ($metric) {
            case 'temperature':
                // good: 24-30, warning: 20-24 or 30-34, danger: otherwise
                if ($value >= 24 && $value <= 30) return 'good';
                if (($value >= 20 && $value < 24) || ($value > 30 && $value <= 34)) return 'warning';
                return 'danger';

            case 'salinity':
                // salinity in ppt: good <=35, warning 35-50, danger >50
                if ($value <= 35) return 'good';
                if ($value > 35 && $value <= 50) return 'warning';
                return 'danger';

            case 'dissolved_oxygen':
                // mg/L: good >=5, warning 3-5, danger <3
                if ($value >= 5) return 'good';
                if ($value >= 3 && $value < 5) return 'warning';
                return 'danger';

            case 'ph_level':
                // pH: good 6.5-8.5, warning 6.0-6.5 or 8.5-9.0, danger otherwise
                if ($value >= 6.5 && $value <= 8.5) return 'good';
                if (($value >= 6.0 && $value < 6.5) || ($value > 8.5 && $value <= 9.0)) return 'warning';
                return 'danger';

            default:
                return 'unknown';
        }
    }

    public function classesForStatus(string $status): string
    {
        return match($status) {
            'best' => 'bg-green-50 border-green-200 text-green-700',
            'good' => 'bg-blue-50 border-blue-200 text-blue-700',
            'bad' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
            'harm' => 'bg-rose-50 border-rose-200 text-rose-700',
            default => 'bg-gray-50 border-gray-200 text-gray-700',
        };
    }

    public function unitFor(string $metric): string
    {
        return match($metric) {
            'temperature' => '°C',
            'salinity' => 'ppt',
            'dissolved_oxygen' => 'mg/L',
            'ph_level' => 'pH',
            default => ''
        };
    }

    /**
     * Returns classes for the numeric value text color given a status
     */
    public function numberColorForStatus(string $status): string
    {
        return match($status) {
            'best' => 'text-green-700',
            'good' => 'text-blue-700',
            'bad' => 'text-yellow-700',
            'harm' => 'text-rose-700',
            default => 'text-gray-700',
        };
    }

    /**
     * Format a delta value for display with a metric's unit and sign
     */
    public function formatDelta(string $metric, ?float $delta): string
    {
        if ($delta === null) return '';

        // choose decimals per metric
        $decimals = $metric === 'ph_level' ? 2 : ($metric === 'salinity' ? 2 : 1);
        $sign = $delta > 0 ? '+' : ($delta < 0 ? '' : '');
        $abs = number_format(abs($delta), $decimals);
        $unit = $this->unitFor($metric);

        return "{$sign}{$abs} {$unit}";
    }

    /**
     * Return a text color class for a trend delta: positive -> green, negative -> red
     */
    public function trendClass(?float $delta): string
    {
        if ($delta === null) return 'text-gray-500';
        if ($delta > 0) return 'text-green-600';
        if ($delta < 0) return 'text-rose-600';
        return 'text-gray-500';
    }

    /**
     * Add a `color` key to each classification array derived from the label.
     */
    protected function addClassificationColors(): void
    {
        $this->temperatureClassification['color'] = $this->classificationColor($this->temperatureClassification['label'] ?? null);
        $this->dissolvedOxygenClassification['color'] = $this->classificationColor($this->dissolvedOxygenClassification['label'] ?? null);
        $this->phClassification['color'] = $this->classificationColor($this->phClassification['label'] ?? null);
        $this->salinityClassification['color'] = $this->classificationColor($this->salinityClassification['label'] ?? null);
    }

    /**
     * Map a classification label to a Tailwind-friendly text color suggestion.
     */
    protected function classificationColor(?string $label): string
    {
        if ($label === null) return 'text-gray-700';

        $key = strtoupper(trim((string) $label));

        return match($key) {
            'BEST' => 'text-green-700',
            'GOOD' => 'text-blue-700',
            'BAD' => 'text-yellow-700',
            'HARM' => 'text-rose-700',
            default => 'text-gray-700',
        };
    }

    /**
     * Return bilingual status label (Filipino / English) for a given status
     */
    public function bilingualStatusLabel(string $status): string
    {
        // Return bilingual label without emoji so rendering can use a consistent CSS dot.
        return match(strtolower($status)) {
            'good' => 'MABUTI / GOOD',
            'warning' => 'BABALA / WARNING',
            'danger' => 'MAPANGANIB / HARMFUL',
            'unknown' => 'HINDI ALAM / UNKNOWN',
            default => 'HINDI ALAM / UNKNOWN',
        };
    }

    /**
     * Return badge CSS classes for status pill styling
     */
    public function badgeClassForStatus(string $status): string
    {
        return match(strtolower($status)) {
            'good' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'danger' => 'bg-red-100 text-red-800',
            'unknown' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Return a small dot color class for a status (used to render a simple circle)
     */
    public function dotClassForStatus(string $status): string
    {
        return match(strtolower($status)) {
            'good' => 'bg-green-600',
            'warning' => 'bg-yellow-600',
            'danger' => 'bg-rose-600',
            'unknown' => 'bg-gray-400',
            default => 'bg-gray-400',
        };
    }

    /**
     * Build a simple SVG polyline path (string) from an array of numeric values.
     * Returns an empty string when insufficient data is available.
     *
     * @param array<int|float> $values
     */
    protected function buildSparklinePath(array $values, int $width = 72, int $height = 28, int $padding = 4): string
    {
        if (empty($values) || count($values) < 2) {
            return '';
        }

        $count = count($values);
        $min = min($values);
        $max = max($values);
        // avoid divide by zero
        $range = ($max - $min) > 0 ? ($max - $min) : 1.0;

        $stepX = ($width - ($padding * 2)) / max(1, $count - 1);
        $points = [];
        foreach ($values as $i => $v) {
            $x = $padding + ($i * $stepX);
            $normalized = ($v - $min) / $range;
            // invert y because SVG y grows downward
            $y = $padding + (1 - $normalized) * ($height - ($padding * 2));
            $points[] = round($x, 2) . ',' . round($y, 2);
        }

        // Return a polyline-compatible points string; blade will wrap into <polyline>
        return implode(' ', $points);
    }

    public function render()
    {
        return \view('livewire.water-quality-card');
    }
}
