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

        try {
            \Log::info('WaterQualityDataTable:mount', ['initialUserId' => $this->userId]);
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

        $water_quality_data = $query->latest('recorded_at')
            ->paginate(perPage: $this->paginate_num);

        // Annotate each row with a computed status using the shared classifier.
        $collection = $water_quality_data->getCollection()->transform(function ($entry) {
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

            // Consider these labels as OK (non-critical)
            $okish = ['GOOD', 'LOW', 'MODERATE', 'OK', 'OPTIMAL'];

            $temp_ok = in_array($tempLabel, $okish, true);
            $ph_ok = in_array($phLabel, $okish, true);
            $do_ok = in_array($doLabel, $okish, true);
            $sal_ok = in_array($salLabel, $okish, true);

            $ok_count = ($temp_ok ? 1 : 0) + ($ph_ok ? 1 : 0) + ($do_ok ? 1 : 0) + ($sal_ok ? 1 : 0);

            if ($ok_count === 4) {
                $statusBorderClass = 'border-l-4 border-green-500';
                $badgeClass = 'text-green-700 bg-green-100';
                $statusLabel = 'Optimal';
            } elseif ($ok_count === 0) {
                $statusBorderClass = 'border-l-4 border-red-500';
                $badgeClass = 'text-red-700 bg-red-100';
                $statusLabel = 'Critical';
            } else {
                $statusBorderClass = 'border-l-4 border-yellow-400';
                $badgeClass = 'text-yellow-700 bg-yellow-100';
                $statusLabel = 'Warning';
            }

            $entry->statusLabel = $statusLabel;
            $entry->statusBadgeClass = $badgeClass;
            $entry->statusBorderClass = $statusBorderClass;

            return $entry;
        });

        $water_quality_data->setCollection($collection);

        return \view('livewire.water-quality-data-table', compact('water_quality_data'));
    }
}
