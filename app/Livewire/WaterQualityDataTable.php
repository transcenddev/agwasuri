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
    public $userId;

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

    public function mount()
    {
        $this->paginate_num = Route::currentRouteName() === 'historical-data' ? 50 : 10;
        $this->userId = Auth::id();
        try {
            \Log::info('WaterQualityDataTable:mount', ['initialUserId' => $this->userId]);
        } catch (\Throwable $e) {}
    }

    public function render()
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

        $water_quality_data = $query->latest('recorded_at')
            ->paginate(perPage: $this->paginate_num);

        return \view('livewire.water-quality-data-table', compact('water_quality_data'));
    }
}
