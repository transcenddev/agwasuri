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
    public $lastUpdated;
    public $connectionStatus = 'disconnected';

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
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
        $query = WaterQualityData::where('user_id', $this->userId);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('recorded_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        } elseif ($this->startDate) {
            $query->whereDate('recorded_at', $this->startDate);
        }

        $waterQualityData = $query->latest('recorded_at')->first();

        if ($waterQualityData) {
            $this->temperature = $waterQualityData->temperature;
            $this->salinity = $waterQualityData->salinity;
            $this->ph_level = $waterQualityData->ph_level;
            $this->dissolved_oxygen = $waterQualityData->dissolved_oxygen;
            $this->lastUpdated = Carbon::parse($waterQualityData->recorded_at)->toDateTimeString();
            // connection status can be extended later; for now mark connected when we have a row
            $this->connectionStatus = 'connected';
        } else {
            $this->temperature = null;
            $this->salinity = null;
            $this->ph_level = null;
            $this->dissolved_oxygen = null;
            $this->lastUpdated = null;
            $this->connectionStatus = 'disconnected';
        }
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
            'good' => 'bg-cvsu-50 border-cvsu-200 text-cvsu-700',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
            'danger' => 'bg-rose-50 border-rose-200 text-rose-700',
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

    public function render()
    {
        return \view('livewire.water-quality-card');
    }
}
