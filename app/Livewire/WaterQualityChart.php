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
        // Dispatch chart cleanup BEFORE loadData to ensure old instance is destroyed
        $this->dispatchBrowserEvent('water-quality:destroy-chart');
        
        // Small delay to allow browser event to process
        usleep(50000); // 50ms
        
        $this->loadData();
    }

    #[On('date-range-updated')]
    public function updateDateRange(?string $startDate, ?string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        // Dispatch chart cleanup BEFORE loadData
        $this->dispatchBrowserEvent('water-quality:destroy-chart');
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
            // canonical order: temperature, salinity, dissolved_oxygen, ph_level
            'temperature' => array_key_exists('temperature', $w) ? (is_null($w['temperature']) ? null : (float) $w['temperature']) : null,
            'salinity' => array_key_exists('salinity', $w) ? (is_null($w['salinity']) ? null : (float) $w['salinity']) : null,
            'dissolved_oxygen' => array_key_exists('dissolved_oxygen', $w) ? (is_null($w['dissolved_oxygen']) ? null : (float) $w['dissolved_oxygen']) : null,
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
            // Accept both date-only (YYYY-MM-DD) and ISO datetime strings.
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);
            // If inputs look like date-only strings (length <= 10), expand to full day
            if (strlen($this->startDate) <= 10) { $start = $start->startOfDay(); }
            if (strlen($this->endDate) <= 10) { $end = $end->endOfDay(); }
            $query->whereBetween('recorded_at', [$start, $end]);
        } elseif ($this->startDate) {
            // If startDate contains time component, use >= comparison; otherwise use whereDate
            if (strlen($this->startDate) > 10) {
                $start = Carbon::parse($this->startDate);
                $query->where('recorded_at', '>=', $start);
            } else {
                $query->whereDate('recorded_at', $this->startDate);
            }
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

        // Source canonical series colors from new config file; use sensible fallbacks
        $seriesColors = config('water_colors.series', []);
        // Ensure colors and series are constructed in the canonical order:
        // Temperature, Salinity, Dissolved Oxygen, pH
        $colors = [
            'Temperature' => $seriesColors['temperature'] ?? '#EF4444',
            'Salinity' => $seriesColors['salinity'] ?? '#14B8A6',
            'Dissolved Oxygen' => $seriesColors['do'] ?? '#2563EB',
            'pH' => $seriesColors['ph'] ?? '#8B5CF6',
        ];

        $series = [
            'Temperature' => $temperatureSeries,
            'Salinity' => $salinitySeries,
            'Dissolved Oxygen' => $doSeries,
            'pH' => $phSeries,
        ];

        $visible = [
            'Temperature' => true,
            'Salinity' => true,
            'Dissolved Oxygen' => true,
            'pH' => true,
        ];

        // Provide series as arrays of {x: ISO, y: value} objects for datetime charts
        $this->chartData = [
            'labels' => $labels,
            'series' => $series,
            'colors' => $colors,
            'visible' => $visible,
        ];

        // Expose configured thresholds so client-side can draw annotations
        $this->chartData['thresholds'] = config('water_quality.thresholds', []);
        // expose status color palette to the client so annotations and badges can use the same colors
        $this->chartData['status_colors'] = config('water_quality.status_colors', []);

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
        
        // CRITICAL FIX: Enforce chart dimensions EVERY time (prevents height collapse on Livewire updates)
        $this->lineChartModelArray['jsonConfig']['chart']['height'] = 540;
        $this->lineChartModelArray['jsonConfig']['chart']['width'] = '100%';
        $this->lineChartModelArray['jsonConfig']['chart']['id'] = $this->lineChartModelArray['jsonConfig']['chart']['id'] ?? 'water-quality-' . ($this->userId ?? '0');
        
        // CRITICAL: Force aspect ratio off to prevent responsive height collapse
        $this->lineChartModelArray['jsonConfig']['chart']['maintainAspectRatio'] = false;

        // Promote visual defaults into jsonConfig so the vendor bundle (ApexCharts) picks them up directly.
        // Colors: ensure an ordered array matching series order.
        $this->lineChartModelArray['jsonConfig']['colors'] = $this->lineChartModelArray['jsonConfig']['colors'] ?? array_values($colors);

        // CRITICAL FIX: Stroke configuration with smooth curves (wavy lines, not straight)
        $this->lineChartModelArray['jsonConfig']['stroke'] = $this->lineChartModelArray['jsonConfig']['stroke'] ?? [
            'curve' => 'smooth',  // This makes lines wavy instead of straight
            'width' => [2.6, 1.6, 1.6, 1.6]  // Temperature emphasized
        ];

        // CRITICAL FIX: Disable markers (no circle points on lines)
        $this->lineChartModelArray['jsonConfig']['markers'] = $this->lineChartModelArray['jsonConfig']['markers'] ?? [
            'size' => 0,  // Set to 0 to hide circle points completely
            'strokeWidth' => 0,
            'hover' => ['size' => 0]  // Also disable on hover
        ];

        // Mobile / readability defaults: hide inline data labels and prefer tooltips
        $this->lineChartModelArray['jsonConfig']['dataLabels'] = $this->lineChartModelArray['jsonConfig']['dataLabels'] ?? ['enabled' => false];

        // Tooltips: shared across series for a given x (tap to inspect all series values)
        $this->lineChartModelArray['jsonConfig']['tooltip'] = $this->lineChartModelArray['jsonConfig']['tooltip'] ?? [
            'enabled' => true,
            'shared' => true,
            'intersect' => false,
            // Use a readable x-axis display; front-end will format if needed
            'x' => ['show' => true]
        ];

        // Legend: place at the bottom for compact mobile layouts
        $this->lineChartModelArray['jsonConfig']['legend'] = $this->lineChartModelArray['jsonConfig']['legend'] ?? [
            'position' => 'bottom',
            'horizontalAlign' => 'center',
            'fontSize' => '12px',
            'offsetY' => 0,
        ];

        // Ensure chart toolbar is hidden for simpler mobile presentation
        $this->lineChartModelArray['jsonConfig']['chart']['toolbar'] = $this->lineChartModelArray['jsonConfig']['chart']['toolbar'] ?? ['show' => false];

        // Responsive adjustments to reduce clutter on small screens
        $this->lineChartModelArray['jsonConfig']['responsive'] = $this->lineChartModelArray['jsonConfig']['responsive'] ?? [
            [
                'breakpoint' => 640,
                'options' => [
                    'chart' => [ 
                        'height' => 360,  // Mobile height
                        'width' => '100%',  // Enforce width on mobile too
                        'maintainAspectRatio' => false  // CRITICAL: prevent height collapse
                    ],
                    'legend' => [ 'position' => 'bottom', 'horizontalAlign' => 'center' ],
                    'stroke' => [ 
                        'curve' => 'smooth',  // Keep smooth curves on mobile
                        'width' => [2.2, 1.2, 1.2, 1.2] 
                    ],
                    'markers' => [ 
                        'size' => 0,  // Keep markers disabled on mobile
                        'strokeWidth' => 0 
                    ],
                    'xaxis' => [ 'labels' => ['rotate' => -45] ],
                ],
            ],
        ];

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
        // canonical series_keys order used by client-side mapping
        $this->lineChartModelArray['meta']['series_keys'] = $this->lineChartModelArray['meta']['series_keys'] ?? ['temperature','salinity','dissolved_oxygen','ph_level'];
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
