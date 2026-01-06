<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportPagination\WithoutUrlPagination;
use Livewire\WithPagination;

class WaterQualityDataTable extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $paginate_num;
    public ?int $userId = null;

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $startTime = null;
    public ?string $endTime = null;
    public ?string $startDateTime = null;
    public ?string $endDateTime = null;
    
    protected $queryString = [
        'startDate' => ['as' => 'start', 'except' => ''],
        'endDate' => ['as' => 'end', 'except' => ''],
    ];

    #[On('user-selected')]
    public function updateUser($userId)
    {
        // Debug: log when an updateUser event is received and the incoming value
        try {
            \Log::info('WaterQualityDataTable:updateUser called', ['userId' => $userId, 'type' => gettype($userId)]);
        } catch (\Throwable $e) {}

        $this->userId = $userId;
        $this->resetPage();
    }

    #[On('date-range-updated')]
    public function updateDateRange(?string $startDate, ?string $endDate, ?string $startTime = null, ?string $endTime = null, ?string $startDateTime = null, ?string $endDateTime = null)
    {
        // Handle CustomEvent detail arrays as well as named parameters
        if (is_array($startDate) && $endDate === null) {
            $payload = $startDate['detail'] ?? $startDate;
            $startDate = $payload['startDate'] ?? $payload['start'] ?? $payload[0] ?? null;
            $endDate = $payload['endDate'] ?? $payload['end'] ?? $payload[1] ?? null;
            $startTime = $payload['startTime'] ?? null;
            $endTime = $payload['endTime'] ?? null;
            $startDateTime = $payload['startDateTime'] ?? null;
            $endDateTime = $payload['endDateTime'] ?? null;
        }

        $this->startDate = $startDate ?: null;
        $this->endDate = $endDate ?: null;
        $this->startTime = $startTime ?: null;
        $this->endTime = $endTime ?: null;
        $this->startDateTime = $startDateTime ?: null;
        $this->endDateTime = $endDateTime ?: null;
        $this->resetPage();
    }

    #[On('echo:water-quality-data-created,WaterQualityCreated')]
    public function handleNewDataCreated(array $event)
    {
        $matchesUser = isset($event['waterQualityData']['user_id']) && $event['waterQualityData']['user_id'] == $this->userId;
        if (!$matchesUser) {
            return;
        }

        $matchesDate = true;
        $range = $this->computeBounds();
        if ($range['start'] || $range['end']) {
            try {
                $recordDate = Carbon::parse($event['waterQualityData']['recorded_at']);
                if ($range['start'] && $range['end']) {
                    $matchesDate = $recordDate->betweenIncluded($range['start'], $range['end']);
                } elseif ($range['start']) {
                    $matchesDate = $recordDate->greaterThanOrEqualTo($range['start']);
                } elseif ($range['end']) {
                    $matchesDate = $recordDate->lessThanOrEqualTo($range['end']);
                }
            } catch (\Exception $e) {
                $matchesDate = false;
            }
        }

        if ($matchesDate) {
            $this->resetPage();
        }
    }

    private function computeBounds(): array
    {
        $start = null; $end = null;
        try {
            if ($this->startDateTime) {
                $start = Carbon::parse($this->startDateTime);
            } elseif ($this->startDate) {
                $start = Carbon::parse($this->startDate)->startOfDay();
                if ($this->startTime) { $start->setTimeFromTimeString($this->startTime); }
            }
        } catch (\Throwable $e) {}

        try {
            if ($this->endDateTime) {
                $end = Carbon::parse($this->endDateTime);
            } elseif ($this->endDate) {
                $end = Carbon::parse($this->endDate)->endOfDay();
                if ($this->endTime) { $end->setTimeFromTimeString($this->endTime); }
            }
        } catch (\Throwable $e) {}

        return ['start' => $start, 'end' => $end];
    }

    #[On('clear-filters')]
    public function clearFilters(): void
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->startTime = null;
        $this->endTime = null;
        $this->startDateTime = null;
        $this->endDateTime = null;
        $this->resetPage();
    }

    public function mount(?int $userId = null): void
    {
        $this->paginate_num = Route::currentRouteName() === 'historical-data' ? 50 : 10;

        // Prefer explicit parameter passed from Blade, then request, then session, then auth
        try {
            if ($userId !== null) {
                $this->userId = (int) $userId;
            } elseif (request()->has('user') && request('user')) {
                $this->userId = (int) request('user');
            } else {
                $this->userId = (int) session('selected_user', Auth::id());
            }
        } catch (\Throwable $e) {
            $this->userId = session('selected_user', Auth::id());
        }
        
        // Initialize date range from URL query params
        if (request()->has('start')) {
            $this->startDate = request('start');
        }
        if (request()->has('end')) {
            $this->endDate = request('end');
        }

        try {
            \Log::info('WaterQualityDataTable:mount', ['initialUserId' => $this->userId, 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
        } catch (\Throwable $e) {}
    }

    public function render()
    {
        $query = WaterQualityData::where('user_id', $this->userId);

        // Debug: log how many rows the query would return for the current userId
        try {
            \Log::info('WaterQualityDataTable:render - query count', ['userId' => $this->userId, 'count' => $query->count()]);
        } catch (\Throwable $e) {}

        $range = $this->computeBounds();
        if ($range['start'] && $range['end']) {
            $query->whereBetween('recorded_at', [$range['start'], $range['end']]);
        } elseif ($range['start']) {
            $query->where('recorded_at', '>=', $range['start']);
        } elseif ($range['end']) {
            $query->where('recorded_at', '<=', $range['end']);
        }

        // Calculate summary statistics - clone query to avoid affecting pagination
        $summaryQuery = clone $query;
        $summaryStats = $summaryQuery->selectRaw('
            AVG(temperature) as avg_temp,
            AVG(salinity) as avg_salinity,
            AVG(dissolved_oxygen) as avg_do,
            AVG(ph_level) as avg_ph
        ')->first();

        // Deterministic ordering to match dashboard preview: newest by recorded_at, then by id
        $water_quality_data = $query
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->paginate(perPage: $this->paginate_num);

        // Annotate each row with a computed status using the shared classifier.
        $collection = $water_quality_data->getCollection()->transform(function ($entry) {
            // Use classifier helper to get parameter labels, then map to unified statuses
            try {
                $tempLabel = strtoupper(classifyWaterParam('temperature', $entry->temperature)['label'] ?? 'UNKNOWN');
            } catch (\Throwable $e) { $tempLabel = 'UNKNOWN'; }
            try {
                $phLabel = strtoupper(classifyWaterParam('ph', $entry->ph_level)['label'] ?? 'UNKNOWN');
            } catch (\Throwable $e) { $phLabel = 'UNKNOWN'; }
            try {
                $doLabel = strtoupper(classifyWaterParam('do', $entry->dissolved_oxygen)['label'] ?? 'UNKNOWN');
            } catch (\Throwable $e) { $doLabel = 'UNKNOWN'; }
            try {
                $salLabel = strtoupper(classifyWaterParam('salinity', $entry->salinity)['label'] ?? 'UNKNOWN');
            } catch (\Throwable $e) { $salLabel = 'UNKNOWN'; }

            // Determine overall row status based on new classification system
            // Worst-parameter-wins: any HARM downgrades to HARM
            $labels = [$tempLabel, $phLabel, $doLabel, $salLabel];
            $hasHarm = in_array('HARM', $labels, true);
            $hasBad = in_array('BAD', $labels, true);
            $hasGood = in_array('GOOD', $labels, true);
            $allBest = collect($labels)->filter(fn($l) => $l !== 'UNKNOWN')->every(fn($l) => $l === 'BEST');

            if ($hasHarm) {
                $statusBorderClass = 'border-l-4 border-red-600';
                $badgeClass = 'bg-red-100 text-red-800';
                $statusLabel = 'HARM';
            } elseif ($hasBad) {
                $statusBorderClass = 'border-l-4 border-yellow-500';
                $badgeClass = 'bg-yellow-100 text-yellow-800';
                $statusLabel = 'BAD';
            } elseif ($hasGood) {
                $statusBorderClass = 'border-l-4 border-blue-500';
                $badgeClass = 'bg-blue-100 text-blue-800';
                $statusLabel = 'GOOD';
            } elseif ($allBest) {
                $statusBorderClass = 'border-l-4 border-green-500';
                $badgeClass = 'bg-green-100 text-green-800';
                $statusLabel = 'BEST';
            } else {
                $statusBorderClass = 'border-l-4 border-gray-400';
                $badgeClass = 'bg-gray-100 text-gray-600';
                $statusLabel = 'Unknown';
            }

            // Map individual parameter labels to a text color class for values.
            $mapLabelToClass = function (?string $label) {
                $lab = strtoupper((string) $label);
                if ($lab === 'UNKNOWN' || $lab === '') {
                    return 'text-gray-600';
                }
                return match($lab) {
                    'BEST' => 'text-green-800',
                    'GOOD' => 'text-blue-800',
                    'BAD' => 'text-yellow-800',
                    'HARM' => 'text-red-800',
                    default => 'text-gray-600',
                };
            };

            $entry->tempValueClass = $mapLabelToClass($tempLabel);
            $entry->phValueClass = $mapLabelToClass($phLabel);
            $entry->doValueClass = $mapLabelToClass($doLabel);
            $entry->salValueClass = $mapLabelToClass($salLabel);

            // Format values with labels for display: "30.62 (BEST)"
            $entry->tempFormatted = $entry->temperature !== null
                ? number_format($entry->temperature, 2) . ' (' . $tempLabel . ')'
                : '-';
            $entry->salFormatted = $entry->salinity !== null
                ? number_format($entry->salinity, 2) . ' (' . $salLabel . ')'
                : '-';
            $entry->doFormatted = $entry->dissolved_oxygen !== null
                ? number_format($entry->dissolved_oxygen, 2) . ' (' . $doLabel . ')'
                : '-';
            $entry->phFormatted = $entry->ph_level !== null
                ? number_format($entry->ph_level, 2) . ' (' . $phLabel . ')'
                : '-';

            $entry->statusLabel = $statusLabel;
            $entry->statusBadgeClass = $badgeClass;
            $entry->statusBorderClass = $statusBorderClass;

            return $entry;
        });

        $water_quality_data->setCollection($collection);

        return \view('livewire.water-quality-data-table', compact('water_quality_data', 'summaryStats'));
    }
}
