<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Asantibanez\LivewireCharts\Models\LineChartModel;

class WaterQualityChart extends Component
{
    public ?int $userId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Payload consumed by the existing client-side initializer
    public array $chartData = [];

    // A lightweight legend structure used by the blade
    public array $legendData = [];

    // Livewire-charts model (protected) and a serializable array for Blade
    protected $lineChartModel;
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

        // Build a livewire-charts LineChartModel and expose a serializable array.
        try {
            $model = new LineChartModel();

            // prefer multi-line behavior when available
            if (method_exists($model, 'multiLine')) {
                $model->multiLine();
            }

            // add series points
            foreach ($series as $seriesName => $values) {
                foreach ($labels as $idx => $label) {
                    $v = $values[$idx] ?? null;
                    if (method_exists($model, 'addSeriesPoint')) {
                        $model->addSeriesPoint($seriesName, $label, $v);
                    }
                }
            }

            if (method_exists($model, 'setColors')) {
                // setColors often expects an array of hex values matching series order
                $model->setColors(array_values($colors));
            }

            if (method_exists($model, 'setTitle')) {
                $model->setTitle('Water Quality Data');
            }

            $this->lineChartModel = $model;

            if (method_exists($model, 'toArray')) {
                $this->lineChartModelArray = $model->toArray();
            } else {
                // fallback to a conservative array shape
                $lineData = [];
                foreach ($series as $seriesName => $values) {
                    $points = [];
                    foreach ($labels as $idx => $label) {
                        $points[] = ['title' => $label, 'value' => $values[$idx] ?? null, 'extras' => new \stdClass()];
                    }
                    $lineData[$seriesName] = $points;
                }

                $this->lineChartModelArray = [
                    'title' => 'Water Quality Data',
                    'data' => $lineData,
                    'xAxis' => ['categories' => $labels],
                    'colors' => $colors,
                    'animated' => false,
                ];
            }
        } catch (\Throwable $e) {
            // If something goes wrong building the model, keep a safe-array fallback
            $lineData = [];
            foreach ($series as $seriesName => $values) {
                $points = [];
                foreach ($labels as $idx => $label) {
                    $points[] = ['title' => $label, 'value' => $values[$idx] ?? null, 'extras' => new \stdClass()];
                }
                $lineData[$seriesName] = $points;
            }

            $this->lineChartModelArray = [
                'title' => 'Water Quality Data',
                'data' => $lineData,
                'xAxis' => ['categories' => $labels],
                'colors' => $colors,
                'animated' => false,
            ];
        }
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
