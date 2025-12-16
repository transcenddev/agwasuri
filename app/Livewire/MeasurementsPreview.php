<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WaterQualityData;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class MeasurementsPreview extends Component
{
    public $userId;
    public $items = [];

    #[On('user-selected')]
    public function updateUser($userId)
    {
        $this->userId = $userId;
    }

    #[On('echo:water-quality-data-created,WaterQualityCreated')]
    public function handleNewDataCreated(array $event)
    {
        $matchesUser = isset($event['waterQualityData']['user_id']) && $event['waterQualityData']['user_id'] == $this->userId;
        if ($matchesUser) {
            // Refresh the component when new data arrives for this user
            $this->render();
        }
    }

    public function mount($userId = null)
    {
        $this->userId = $userId ?? auth()->user()->user_id ?? null;
    }

    public function render()
    {
        $query = WaterQualityData::query();
        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        $items = $query->orderBy('recorded_at', 'desc')->limit(5)->get();

        // Attach UI status to each row for quick classification in the view
        $this->items = $items->map(function ($r) {
            $r->ui_status = $this->classifyRow($r);
            $r->tempValueClass = $this->valueClass('temperature', $r->temperature);
            $r->salValueClass = $this->valueClass('salinity', $r->salinity);
            $r->doValueClass = $this->valueClass('dissolved_oxygen', $r->dissolved_oxygen);
            $r->phValueClass = $this->valueClass('ph', $r->ph_level);
            $r->issues = $this->identifyIssues($r);
            $r->issuesText = empty($r->issues) ? '' : implode(', ', $r->issues);

            // Apply 4-tier badge and border classes (matching KPI cards)
            $r->badgeClass = match($r->ui_status) {
                'BEST' => 'text-green-800 bg-green-100',
                'GOOD' => 'text-blue-800 bg-blue-100',
                'BAD' => 'text-yellow-800 bg-yellow-100',
                'HARM' => 'text-rose-800 bg-rose-100',
                default => 'text-gray-700 bg-gray-100',
            };
            
            $r->statusBorderClass = match($r->ui_status) {
                'BEST' => 'border-l-4 border-green-500',
                'GOOD' => 'border-l-4 border-blue-500',
                'BAD' => 'border-l-4 border-yellow-500',
                'HARM' => 'border-l-4 border-rose-500',
                default => 'border-l-4 border-gray-400',
            };
            return $r;
        });

        return view('livewire.measurements-preview', [
            'items' => $this->items,
        ]);
    }

    protected function classifyRow($row)
    {
        // Use the same 4-tier classification system as KPI cards
        $labels = [];

        try {
            $tempLabel = strtoupper(classifyWaterParam('temperature', $row->temperature)['label'] ?? 'UNKNOWN');
        } catch (\Throwable $e) { $tempLabel = 'UNKNOWN'; }

        try {
            $phLabel = strtoupper(classifyWaterParam('ph', $row->ph_level)['label'] ?? 'UNKNOWN');
        } catch (\Throwable $e) { $phLabel = 'UNKNOWN'; }

        try {
            $doLabel = strtoupper(classifyWaterParam('do', $row->dissolved_oxygen)['label'] ?? 'UNKNOWN');
        } catch (\Throwable $e) { $doLabel = 'UNKNOWN'; }

        try {
            $salLabel = strtoupper(classifyWaterParam('salinity', $row->salinity)['label'] ?? 'UNKNOWN');
        } catch (\Throwable $e) { $salLabel = 'UNKNOWN'; }

        $labels = [$tempLabel, $phLabel, $doLabel, $salLabel];

        // Priority aggregation: harm > bad > good > best (same as KPI cards)
        if (in_array('HARM', $labels, true)) return 'HARM';
        if (in_array('BAD', $labels, true)) return 'BAD';
        if (in_array('GOOD', $labels, true)) return 'GOOD';

        // If all valid metrics are BEST
        $allBest = collect($labels)->filter(fn($l) => $l !== 'UNKNOWN')->every(fn($l) => $l === 'BEST');
        if ($allBest) return 'BEST';

        return 'UNKNOWN';
    }

    protected function valueClass($type, $value)
    {
        if (!is_numeric($value)) return '';

        // Use classifyWaterParam to get label, then map to color class (matching KPI cards)
        try {
            $label = strtoupper(classifyWaterParam($type, $value)['label'] ?? 'UNKNOWN');
        } catch (\Throwable $e) {
            return 'text-gray-600';
        }

        return match($label) {
            'BEST' => 'text-green-700',
            'GOOD' => 'text-blue-700',
            'BAD' => 'text-yellow-700',
            'HARM' => 'text-rose-700',
            default => 'text-gray-600',
        };
    }

    protected function identifyIssues($row)
    {
        $issues = [];

        // pH
        $ph = $row->ph_level;
        if (is_numeric($ph)) {
            if ($ph < 5.0 || $ph > 9.0) $issues[] = 'pH (critical)';
            elseif ($ph < 6.5 || $ph > 8.5) $issues[] = 'pH';
        }

        // Temperature
        $t = $row->temperature;
        if (is_numeric($t)) {
            if ($t < 20.0 || $t > 35.0) $issues[] = 'Temperature (critical)';
            elseif ($t < 25.0 || $t > 31.0) $issues[] = 'Temperature';
        }

        // Dissolved oxygen
        $do = $row->dissolved_oxygen;
        if (is_numeric($do)) {
            if ($do < 2.0) $issues[] = 'Dissolved oxygen (critical)';
            elseif ($do < 5.0) $issues[] = 'Dissolved oxygen';
        }

        // Salinity
        $s = $row->salinity;
        if (is_numeric($s)) {
            if ($s > 35.0) $issues[] = 'Salinity (critical)';
            elseif ($s < 7.0 || $s > 25.0) $issues[] = 'Salinity';
        }

        return $issues;
    }
}
