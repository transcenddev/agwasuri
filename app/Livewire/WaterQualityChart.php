<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use App\Services\WaterQualityChartService;

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
        // Only handle events for the currently selected user
        $matchesUser = isset($event['waterQualityData']['user_id']) && $event['waterQualityData']['user_id'] == $this->userId;
        if (! $matchesUser) {
            return;
        }

        // Respect the current date range filter if set — ignore events outside range
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
                // If parsing fails, accept the event to avoid dropping real-time updates
                $matchesDate = true;
            }
        }

        if (! $matchesDate) {
            return;
        }

        // Build a compact payload containing the expected five fields and dispatch
        $w = $event['waterQualityData'];

        $payload = [
            'recorded_at' => isset($w['recorded_at']) ? Carbon::parse($w['recorded_at'])->toIsoString() : now()->toIsoString(),
            'temperature' => array_key_exists('temperature', $w) ? (is_null($w['temperature']) ? null : (float) $w['temperature']) : null,
            'dissolved_oxygen' => array_key_exists('dissolved_oxygen', $w) ? (is_null($w['dissolved_oxygen']) ? null : (float) $w['dissolved_oxygen']) : null,
            'salinity' => array_key_exists('salinity', $w) ? (is_null($w['salinity']) ? null : (float) $w['salinity']) : null,
            'ph_level' => array_key_exists('ph_level', $w) ? (is_null($w['ph_level']) ? null : (float) $w['ph_level']) : null,
        ];

        // Emit a Livewire event to the client. IMPORTANT: do NOT call $this->loadData() here
        // because it triggers a Livewire re-render which recreates the chart DOM.
        // Emitting lets the frontend (Livewire.on) receive the payload reliably.
        $this->dispatch('water-quality:point', $payload);
    }

    /**
     * Compatibility helper: Livewire v3 doesn't provide dispatchBrowserEvent by default
     * in the Component base used here. Provide a small shim that uses the `js()` helper
     * to emit a DOM CustomEvent on the client with the given payload.
     */
    public function dispatchBrowserEvent(string $eventName, $payload = null): void
    {
        // Safely JSON-encode payload for inline JS
        $payloadJson = $payload !== null ? json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) : 'null';

        $expression = "window.dispatchEvent(new CustomEvent(" . json_encode($eventName) . ", { detail: " . $payloadJson . " }));";

        // Use Livewire's js() helper to schedule the JS to run on the client side after the update
        if (method_exists($this, 'js')) {
            $this->js($expression);
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

        // Use ISO timestamps for series points so the client can use a datetime x-axis.
        // This is more robust for real-time updates than category labels ('HH:mm:ss').
        $labels = $rows->map(fn($r) => Carbon::parse($r->recorded_at)->toIsoString())->all();

        $temperatureSeries = $rows->map(fn($r) => [
            'x' => Carbon::parse($r->recorded_at)->toIsoString(),
            'y' => $r->temperature !== null ? (float) $r->temperature : null,
        ])->all();

        $doSeries = $rows->map(fn($r) => [
            'x' => Carbon::parse($r->recorded_at)->toIsoString(),
            'y' => $r->dissolved_oxygen !== null ? (float) $r->dissolved_oxygen : null,
        ])->all();

        $salinitySeries = $rows->map(fn($r) => [
            'x' => Carbon::parse($r->recorded_at)->toIsoString(),
            'y' => $r->salinity !== null ? (float) $r->salinity : null,
        ])->all();

        $phSeries = $rows->map(fn($r) => [
            'x' => Carbon::parse($r->recorded_at)->toIsoString(),
            'y' => $r->ph_level !== null ? (float) $r->ph_level : null,
        ])->all();

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

        // Provide series as arrays of {x: ISO, y: value} objects for datetime charts
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
                // fallback to service-built array shape
                $service = new WaterQualityChartService();
                $this->lineChartModelArray = $service->buildLineChartModelArray($labels, $series, $colors, $visible, (int) $this->userId);
            }
        } catch (\Throwable $e) {
            // If something goes wrong building the model, keep a safe-array fallback
            $service = new WaterQualityChartService();
            $this->lineChartModelArray = $service->buildLineChartModelArray($labels, $series, $colors, $visible, (int) $this->userId);
        }

        // Ensure x-axis density and labels rotation are included for the client
        $labelCount = count($labels);
        $tickAmount = min(8, max(2, $labelCount));

        if (! isset($this->lineChartModelArray['xaxis']) || ! is_array($this->lineChartModelArray['xaxis'])) {
            $this->lineChartModelArray['xaxis'] = [];
        }
        $this->lineChartModelArray['xaxis']['tickAmount'] = $this->lineChartModelArray['xaxis']['tickAmount'] ?? $tickAmount;
        $this->lineChartModelArray['xaxis']['labels'] = $this->lineChartModelArray['xaxis']['labels'] ?? ['rotate' => -30];

        // Ensure jsonConfig.chart.height and id exist so vendor JS receives numeric height and predictable id
        if (! isset($this->lineChartModelArray['jsonConfig']) || ! is_array($this->lineChartModelArray['jsonConfig'])) {
            $this->lineChartModelArray['jsonConfig'] = [];
        }
        if (! isset($this->lineChartModelArray['jsonConfig']['chart']) || ! is_array($this->lineChartModelArray['jsonConfig']['chart'])) {
            $this->lineChartModelArray['jsonConfig']['chart'] = [];
        }
        $this->lineChartModelArray['jsonConfig']['chart']['height'] = $this->lineChartModelArray['jsonConfig']['chart']['height'] ?? ($this->lineChartModelArray['chart']['height'] ?? 700);
        $this->lineChartModelArray['jsonConfig']['chart']['id'] = $this->lineChartModelArray['jsonConfig']['chart']['id'] ?? 'water-quality-' . ($this->userId ?? '0');

        // Promote visual defaults into jsonConfig so the vendor bundle (ApexCharts) picks them up directly.
        // Colors: ensure an ordered array matching series order.
        $this->lineChartModelArray['jsonConfig']['colors'] = $this->lineChartModelArray['jsonConfig']['colors'] ?? array_values($colors);

        // Stroke widths (temperature emphasized)
        $this->lineChartModelArray['jsonConfig']['stroke'] = $this->lineChartModelArray['jsonConfig']['stroke'] ?? ['width' => [2.6, 1.6, 1.6, 1.6]];

        // Markers
        $this->lineChartModelArray['jsonConfig']['markers'] = $this->lineChartModelArray['jsonConfig']['markers'] ?? ['size' => 3];

        // Mirror x-axis density and label rotation into jsonConfig.xaxis for vendor merging
        if (! isset($this->lineChartModelArray['jsonConfig']['xaxis']) || ! is_array($this->lineChartModelArray['jsonConfig']['xaxis'])) {
            $this->lineChartModelArray['jsonConfig']['xaxis'] = [];
        }
        $this->lineChartModelArray['jsonConfig']['xaxis']['tickAmount'] = $this->lineChartModelArray['jsonConfig']['xaxis']['tickAmount'] ?? $tickAmount;
        $this->lineChartModelArray['jsonConfig']['xaxis']['labels'] = $this->lineChartModelArray['jsonConfig']['xaxis']['labels'] ?? ['rotate' => -30];

        // Use a datetime x-axis so appends can use ISO timestamps reliably.
        $this->lineChartModelArray['jsonConfig']['xaxis']['type'] = $this->lineChartModelArray['jsonConfig']['xaxis']['type'] ?? 'datetime';

        // Ensure metadata for client-side mapping (series_keys in same order as series names)
        if (! isset($this->lineChartModelArray['meta']) || ! is_array($this->lineChartModelArray['meta'])) {
            $this->lineChartModelArray['meta'] = [];
        }
        $this->lineChartModelArray['meta']['series_names'] = $this->lineChartModelArray['meta']['series_names'] ?? array_keys($series);
        $this->lineChartModelArray['meta']['series_keys'] = $this->lineChartModelArray['meta']['series_keys'] ?? ['temperature','dissolved_oxygen','salinity','ph_level'];
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
