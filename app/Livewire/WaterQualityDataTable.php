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
    public function updateDateRange(?string $startDate, ?string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
        if ($this->startDate || $this->endDate) {
             try {
                $recordDate = Carbon::parse($event['waterQualityData']['recorded_at']);
                if ($this->startDate && $this->endDate) {
                    $matchesDate = $recordDate->betweenIncluded(Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay());
                } elseif ($this->startDate) {
                    $matchesDate = $recordDate->isSameDay(Carbon::parse($this->startDate));
                }
             } catch (\Exception $e) {
                 $matchesDate = false;
             }
        }

        if ($matchesDate) {
            $this->resetPage();
        }
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

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('recorded_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        } elseif ($this->startDate) {
            $query->whereDate('recorded_at', $this->startDate);
        }

        // Calculate summary statistics - clone query to avoid affecting pagination
        $summaryQuery = clone $query;
        $summaryStats = $summaryQuery->selectRaw('
            AVG(temperature) as avg_temp,
            AVG(salinity) as avg_salinity,
            AVG(dissolved_oxygen) as avg_do,
            AVG(ph_level) as avg_ph
        ')->first();

        $water_quality_data = $query->latest('recorded_at')
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

            // Map classifier labels to simplified ui statuses: GOOD / BAD / HARMFUL
            $toUiStatus = function (?string $label) {
                $lab = strtoupper((string) $label);
                $goodish = ['GOOD', 'LOW', 'MODERATE', 'OK', 'OPTIMAL'];
                if ($lab === 'UNKNOWN' || $lab === '') return 'BAD';
                if (in_array($lab, $goodish, true)) return 'GOOD';
                if ($lab === 'CRITICAL') return 'HARMFUL';
                return 'BAD';
            };

            $tUi = $toUiStatus($tempLabel);
            $pUi = $toUiStatus($phLabel);
            $dUi = $toUiStatus($doLabel);
            $sUi = $toUiStatus($salLabel);

            // Aggregate row status: any HARMFUL -> HARMFUL, else any BAD -> BAD, else GOOD
            $rowStatuses = [$tUi, $pUi, $dUi, $sUi];
            if (in_array('HARMFUL', $rowStatuses, true)) {
                $uiStatus = 'HARMFUL';
            } elseif (in_array('BAD', $rowStatuses, true)) {
                $uiStatus = 'BAD';
            } else {
                $uiStatus = 'GOOD';
            }

            // Apply centralized UI classes from config if present
            $statusMap = config('water_quality.status_map', []);
            $mapEntry = $statusMap[$uiStatus] ?? null;
            if ($mapEntry) {
                $statusBorderClass = $mapEntry['border'] ?? ($uiStatus === 'HARMFUL' ? 'border-l-4 border-red-200' : ($uiStatus === 'BAD' ? 'border-l-4 border-amber-100' : 'border-l-4 border-green-100'));
                $badgeClass = $mapEntry['badge'] ?? ($uiStatus === 'HARMFUL' ? 'text-red-700 bg-red-50' : ($uiStatus === 'BAD' ? 'text-amber-700 bg-amber-50' : 'text-green-700 bg-green-50'));
            } else {
                // fallback to existing palette
                $statusBorderClass = $uiStatus === 'HARMFUL' ? 'border-l-4 border-red-500' : ($uiStatus === 'BAD' ? 'border-l-4 border-yellow-400' : 'border-l-4 border-green-500');
                $badgeClass = $uiStatus === 'HARMFUL' ? 'text-red-700 bg-red-100' : ($uiStatus === 'BAD' ? 'text-yellow-700 bg-yellow-100' : 'text-green-700 bg-green-100');
            }

            // Map ui status to per-value classes (prefer more specific per-param logic elsewhere)
            $uiToValueClass = function (?string $ui) {
                if ($ui === 'GOOD') return 'text-green-700';
                if ($ui === 'HARMFUL') return 'text-red-700 font-semibold';
                return 'text-amber-600';
            };

            $entry->tempValueClass = $uiToValueClass($tUi);
            $entry->phValueClass = $uiToValueClass($pUi);
            $entry->doValueClass = $uiToValueClass($dUi);
            $entry->salValueClass = $uiToValueClass($sUi);

            $entry->statusLabel = $uiStatus;
            $entry->statusBadgeClass = $badgeClass;
            $entry->statusBorderClass = $statusBorderClass;

            return $entry;
        });

        $water_quality_data->setCollection($collection);

        return \view('livewire.water-quality-data-table', compact('water_quality_data', 'summaryStats'));
    }
}
