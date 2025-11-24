<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class WaterQualityChart extends Component
{
    public $water_quality_data;
    public $userId;
    public array $legendData = [];

    // Add properties to store date range
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Add property to track visible series state
    public array $visibleSeries = ['Temperature', 'pH Level', 'Dissolved Oxygen', 'Salinity'];

    #[On('user-selected')]
    public function updateUser($userId)
    {
        $this->userId = $userId;
        // Reset visibility when user changes? Optional, depends on desired UX.
        // $this->visibleSeries = ['Temperature', 'pH Level', 'Dissolved Oxygen', 'Salinity'];
        $this->loadData();
    }

    #[On('date-range-updated')]
    public function updateDateRange(?string $startDate, ?string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        // Keep current visibility state when date changes
        $this->loadData();
    }

    #[On('echo:water-quality-data-created,WaterQualityCreated')]
    public function handleNewDataCreated(array $event) {
        // Check if new data matches current user *and* is within current date range (if set)
        $matchesUser = isset($event['waterQualityData']['user_id']) && $event['waterQualityData']['user_id'] == $this->userId;
        if (!$matchesUser) {
            return;
        }

        // Check date range if filters are active
        $matchesDate = true; // Assume matches if no date filter is set
        if ($this->startDate || $this->endDate) {
             try {
                $recordDate = Carbon::parse($event['waterQualityData']['recorded_at']);
                if ($this->startDate && $this->endDate) {
                    $matchesDate = $recordDate->betweenIncluded(Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay());
                } elseif ($this->startDate) {
                    // For single day, check if it's on that specific day
                    $matchesDate = $recordDate->isSameDay(Carbon::parse($this->startDate));
                }
                // Note: No explicit case for only endDate, handled by how loadData works.
             } catch (\Exception $e) {
                 $matchesDate = false; // Error parsing date
             }
        }


        if ($matchesDate) {
            $this->loadData(); // Reload if all criteria match
        }
    }

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadData();
    }

    public function loadData()
    {
        $query = WaterQualityData::where('user_id', $this->userId);

        // Apply date filtering
        if ($this->startDate && $this->endDate) {
            // Range: Start of start day to end of end day
             $query->whereBetween('recorded_at', [
                 Carbon::parse($this->startDate)->startOfDay(),
                 Carbon::parse($this->endDate)->endOfDay()
             ]);
        } elseif ($this->startDate) {
            // Single Day: Filter for the specific date
            $query->whereDate('recorded_at', $this->startDate);
        }
        // If only endDate is set, it will show all data up to the end of that day (implicitly handled by latest()->get())
        // If neither is set, no date filter applied.

        $this->water_quality_data = $query->latest('recorded_at')->get(); // Apply ordering *after* filtering
        // Notify the frontend that chart data was (re)loaded so client can initialize or re-init charts.
        // Use the Livewire `dispatch` helper available via HandlesEvents trait which stores
        // a dispatched event that the Livewire runtime will forward to the browser.
        $this->dispatch('waterchart-mounted');
    }

    // Method to toggle series visibility via state change
    public function toggleSeries(string $seriesName)
    {
        if (($key = array_search($seriesName, $this->visibleSeries)) !== false) {
            // Remove if present
             unset($this->visibleSeries[$key]);
             $this->visibleSeries = array_values($this->visibleSeries); // Re-index array
        } else {
            // Add if not present
            $this->visibleSeries[] = $seriesName;
        }
        // Livewire's reactivity will trigger a re-render automatically
        // No need to dispatch event: $this->dispatch('toggle-chart-series', ...);
    }

    #[On('toggle-chart-series')]
    public function handleToggleChartSeries(string $seriesName)
    {
        $this->toggleSeries($seriesName);
    }

    #[On('set-series-visibility')]
    public function handleSetSeriesVisibility(string $mode)
    {
        if ($mode === 'all') {
            $this->visibleSeries = ['Temperature', 'pH Level', 'Dissolved Oxygen', 'Salinity'];
        }
        // Add other modes if needed
    }

    public function render()
    {
        $water_quality_data = $this->water_quality_data;

        // Define legend colors as requested
        $temperatureColor = '#ef4444'; // Red (Tailwind red-500)
        $phColor = '#eab308';         // Yellow (Tailwind yellow-500)
        $doColor = '#2563eb';         // Blue (Tailwind blue-600)
        $salinityColor = '#16a34a';     // Green (Tailwind green-600)

        // Map series names to their colors for easier lookup
        $allSeriesColors = [
            'Temperature' => $temperatureColor,
            'pH Level' => $phColor,
            'Dissolved Oxygen' => $doColor,
            'Salinity' => $salinityColor,
        ];

        // Prepare legend data (this remains the same)
        $this->legendData = [
            ['name' => 'Temperature', 'color' => $temperatureColor, 'visible' => in_array('Temperature', $this->visibleSeries)],
            ['name' => 'pH Level', 'color' => $phColor, 'visible' => in_array('pH Level', $this->visibleSeries)],
            ['name' => 'Dissolved Oxygen', 'color' => $doColor, 'visible' => in_array('Dissolved Oxygen', $this->visibleSeries)],
            ['name' => 'Salinity', 'color' => $salinityColor, 'visible' => in_array('Salinity', $this->visibleSeries)],
        ];

        // Filter colors based on visibility for ApexCharts config
        $visibleSeriesColors = array_values(array_intersect_key($allSeriesColors, array_flip($this->visibleSeries)));

        if ($water_quality_data->isEmpty() || empty($this->visibleSeries)) {
            $lineChartModel = null; // Show no data if source is empty OR no series are selected
        } else {
            // Initialize the model first
            $lineChartModel = (new LineChartModel)
                ->multiLine()
                ->setAnimated(false) // Disable animation during state changes for smoother toggling
                ->setXAxisVisible(true)
                ->setYAxisVisible(true)
                ->setJsonConfig([
                    'legend' => ['show' => false],
                    'chart' => ['id' => $this->getId()],
                    // Set colors based *only* on visible series to maintain correct order/mapping
                    'markers' => ['colors' => $visibleSeriesColors],
                    'colors' => $visibleSeriesColors
                ]);

            $hasAddedPoints = false; // Initialize flag

            // Add series points *only* if the series is visible
            foreach ($water_quality_data as $data) {
                $recorded_at = Carbon::parse($data->recorded_at)->format('M d H:i');

                if (in_array('Temperature', $this->visibleSeries)) {
                    $lineChartModel->addSeriesPoint('Temperature', $recorded_at, $data->temperature); // Color applied via JsonConfig
                    $hasAddedPoints = true;
                }
                if (in_array('pH Level', $this->visibleSeries)) {
                    $lineChartModel->addSeriesPoint('pH Level', $recorded_at, $data->ph_level);
                    $hasAddedPoints = true;
                }
                if (in_array('Dissolved Oxygen', $this->visibleSeries)) {
                    $lineChartModel->addSeriesPoint('Dissolved Oxygen', $recorded_at, $data->dissolved_oxygen);
                    $hasAddedPoints = true;
                }
                if (in_array('Salinity', $this->visibleSeries)) {
                    $lineChartModel->addSeriesPoint('Salinity', $recorded_at, $data->salinity);
                    $hasAddedPoints = true;
                }
            }

             // Check if any points were actually added using the flag
             if (!$hasAddedPoints) { // Use the flag instead of hasData()
                  $lineChartModel = null; // Treat as no data if filters resulted in empty visible series
             }
        }

        // Build chartData for Chart.js prototype (labels in ISO, series arrays)
        $chartData = ['labels' => [], 'series' => [], 'colors' => [], 'visible' => $this->visibleSeries];

        // Initialize series keys
        $seriesKeys = ['Temperature', 'pH Level', 'Dissolved Oxygen', 'Salinity'];
        foreach ($seriesKeys as $k) {
            $chartData['series'][$k] = [];
        }

        foreach ($water_quality_data as $data) {
            // Use ISO format for easier parsing in JS
            $iso = Carbon::parse($data->recorded_at)->toIso8601String();
            $chartData['labels'][] = $iso;
            $chartData['series']['Temperature'][] = $data->temperature;
            $chartData['series']['pH Level'][] = $data->ph_level;
            $chartData['series']['Dissolved Oxygen'][] = $data->dissolved_oxygen;
            $chartData['series']['Salinity'][] = $data->salinity;
        }

        // Colors mapping (keeps same palette used earlier)
        $chartData['colors'] = [
            'Temperature' => $temperatureColor,
            'pH Level' => $phColor,
            'Dissolved Oxygen' => $doColor,
            'Salinity' => $salinityColor,
        ];

        return view('livewire.water-quality-chart', [
            'lineChartModel' => $lineChartModel,
            'legendData' => $this->legendData, // Pass updated legend data with visibility info
            'chartData' => $chartData,
        ]);
    }
}
