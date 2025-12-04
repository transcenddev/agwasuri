<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class WaterQualityChart extends Component
{
    public ?int $userId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Payload consumed by the existing client-side initializer
    public array $chartData = [];

    // A lightweight legend structure used by the blade
    public array $legendData = [];

    // Keep the actual LineChartModel as a protected object to avoid Livewire
    // attempting to synthesize it. Expose a serializable array for the view.
    protected $lineChartModel;

    // Public serializable model (array) consumed by the Blade and client JS.
    public array $lineChartModelArray = [];

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

    public function loadData(): void
    {
        $query = WaterQualityData::where('user_id', $this->userId);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('recorded_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        } elseif ($this->startDate) {
            $query->whereDate('recorded_at', $this->startDate);
        }

        // fetch a reasonable number of points for the chart (most recent first)
        $rows = $query->orderBy('recorded_at', 'desc')->limit(200)->get()->reverse()->values();

        if ($rows->isEmpty()) {
            $this->chartData = ['labels' => [], 'series' => [], 'colors' => [], 'visible' => []];
            $this->legendData = [];
            return;
        }

        $labels = $rows->map(fn($r) => Carbon::parse($r->recorded_at)->format('Y-m-d H:i'))->all();

        $temperatureSeries = $rows->map(fn($r) => $r->temperature !== null ? (float) $r->temperature : null)->all();
        $doSeries = $rows->map(fn($r) => $r->dissolved_oxygen !== null ? (float) $r->dissolved_oxygen : null)->all();
        $salinitySeries = $rows->map(fn($r) => $r->salinity !== null ? (float) $r->salinity : null)->all();
        $phSeries = $rows->map(fn($r) => $r->ph_level !== null ? (float) $r->ph_level : null)->all();

        $colors = [
            'Temperature' => '#ef4444', // red-ish
            'Dissolved Oxygen' => '#2563eb', // blue
            'Salinity' => '#06b6d4', // teal
            'pH' => '#a78bfa', // purple
        ];

        $series = [
            'Temperature' => $temperatureSeries,
            'Dissolved Oxygen' => $doSeries,
            'Salinity' => $salinitySeries,
            'pH' => $phSeries,
        ];

        $visible = [
            'Temperature' => true,
            'Dissolved Oxygen' => true,
            'Salinity' => true,
            'pH' => true,
        ];

        $this->chartData = [
            'labels' => $labels,
            'series' => $series,
            'colors' => $colors,
            'visible' => $visible,
        ];

        $this->legendData = collect($colors)->map(function($color, $name) use ($visible) {
            return [
                'name' => $name,
                'color' => $color,
                'visible' => $visible[$name] ?? true,
            ];
        })->values()->all();

        // Build an official LineChartModel (multi-line) provided by
        // asantibanez/livewire-charts so we can render the canonical
        // `livewire-line-chart` component in the Blade.
        $model = (new LineChartModel())->multiLine()->setTitle('Water Quality Data')->setAnimated(false);

        // Add points per series using the package API. The method
        // `addSeriesPoint($seriesName, $title, $value)` will build the
        // multi-series payload the component expects.
        foreach ($series as $seriesName => $values) {
            foreach ($labels as $idx => $label) {
                $v = $values[$idx] ?? null;
                $model->addSeriesPoint($seriesName, $label, $v);
            }
        }

        // Apply colors in the same order as our series array.
        $model->setColors(array_values($colors));

        $this->lineChartModel = $model;

        // Expose an array copy for Livewire's hydration/dehydration.
        $this->lineChartModelArray = $this->lineChartModel->toArray();

        // Do not call client event methods here (compatibility across
        // Livewire versions). The Blade's JS will detect DOM updates
        // and re-render the chart after Livewire finishes processing.
    }

    /**
     * Toggle visibility for a named series. Called from Blade via wire:click.
     */
    public function toggleSeries(string $name): void
    {
        if (!isset($this->chartData['visible'][$name])) {
            // If the series isn't known, ignore gracefully
            return;
        }

        $this->chartData['visible'][$name] = ! $this->chartData['visible'][$name];

        // Keep legendData in sync
        foreach ($this->legendData as &$item) {
            if (isset($item['name']) && $item['name'] === $name) {
                $item['visible'] = $this->chartData['visible'][$name];
                break;
            }
        }

        // Emit an event so client code can react if necessary
        $this->emit('chartSeriesToggled', $name, $this->chartData['visible'][$name]);
    }

    public function render()
    {
        return view('livewire.water-quality-chart');
    }
}
