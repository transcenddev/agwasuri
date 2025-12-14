<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WaterQualityData;

class MeasurementsPreview extends Component
{
    public $userId;
    public $items = [];

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

            // Apply centralized UI mapping from config if present
            $statusMap = config('water_quality.status_map', []);
            $mapEntry = $statusMap[$r->ui_status] ?? null;
            if ($mapEntry) {
                $r->badgeClass = $mapEntry['badge'] ?? ($r->ui_status === 'HARMFUL' ? 'text-red-700 bg-red-50' : ($r->ui_status === 'BAD' ? 'text-amber-700 bg-amber-50' : 'text-green-700 bg-green-50'));
                $r->statusBorderClass = $mapEntry['border'] ?? ($r->ui_status === 'HARMFUL' ? 'border-l-4 border-red-200' : ($r->ui_status === 'BAD' ? 'border-l-4 border-amber-100' : 'border-l-4 border-green-100'));
                // Optionally set valueClass from map if the developer prefers
                if (!empty($mapEntry['value'])) {
                    // prefer keeping per-parameter finer-grained classes, so only set a generic fallback
                    $r->genericValueClass = $mapEntry['value'];
                }
            } else {
                // fallback to present behavior
                $r->badgeClass = $r->ui_status === 'HARMFUL' ? 'text-red-700 bg-red-50' : ($r->ui_status === 'BAD' ? 'text-amber-700 bg-amber-50' : 'text-green-700 bg-green-50');
                $r->statusBorderClass = $r->ui_status === 'HARMFUL' ? 'border-l-4 border-red-200' : ($r->ui_status === 'BAD' ? 'border-l-4 border-amber-100' : 'border-l-4 border-green-100');
            }
            return $r;
        });

        return view('livewire.measurements-preview', [
            'items' => $this->items,
        ]);
    }

    protected function classifyRow($row)
    {
        // Read thresholds from config where available
        $cfg = config('water_quality.thresholds');

        $statusLevels = [];

        // pH
        $ph = $row->ph_level;
        if (is_numeric($ph)) {
            if ($ph < 5.0 || $ph > 9.0) {
                $statusLevels[] = 'HARMFUL';
            } elseif ($ph < ($cfg['ph'][0] ?? 6.5) || $ph > ($cfg['ph'][1] ?? 8.5)) {
                $statusLevels[] = 'BAD';
            } else {
                $statusLevels[] = 'GOOD';
            }
        }

        // Temperature
        $t = $row->temperature;
        if (is_numeric($t)) {
            if ($t < 20.0 || $t > 35.0) {
                $statusLevels[] = 'HARMFUL';
            } elseif ($t < ($cfg['temperature_c'][0] ?? 25.0) || $t > ($cfg['temperature_c'][1] ?? 31.0)) {
                $statusLevels[] = 'BAD';
            } else {
                $statusLevels[] = 'GOOD';
            }
        }

        // Dissolved oxygen
        $do = $row->dissolved_oxygen;
        if (is_numeric($do)) {
            if ($do < 2.0) {
                $statusLevels[] = 'HARMFUL';
            } elseif ($do < ($cfg['dissolved_oxygen_mg_l'] ?? 5.0)) {
                $statusLevels[] = 'BAD';
            } else {
                $statusLevels[] = 'GOOD';
            }
        }

        // Salinity
        $s = $row->salinity;
        if (is_numeric($s)) {
            if ($s > 35.0) {
                $statusLevels[] = 'HARMFUL';
            } elseif ($s > ($cfg['salinity_ppt'][1] ?? 25.0)) {
                $statusLevels[] = 'BAD';
            } else {
                $statusLevels[] = 'GOOD';
            }
        }

        // Aggregate: if any HARMFUL -> HARMFUL, else if any BAD -> BAD, else GOOD
        if (in_array('HARMFUL', $statusLevels, true)) return 'HARMFUL';
        if (in_array('BAD', $statusLevels, true)) return 'BAD';
        return 'GOOD';
    }

    protected function valueClass($type, $value)
    {
        if (!is_numeric($value)) return '';

        // Use config thresholds for sensible ranges
        $t = config('water_quality.thresholds', []);

        switch ($type) {
            case 'temperature':
                $low = $t['temperature_c'][0] ?? 25.0;
                $high = $t['temperature_c'][1] ?? 31.0;
                if ($value < 20.0 || $value > 35.0) return 'text-red-700 font-semibold';
                if ($value < $low || $value > $high) return 'text-amber-600';
                return 'text-green-700';

            case 'salinity':
                $low = $t['salinity_ppt'][0] ?? 7.0;
                $high = $t['salinity_ppt'][1] ?? 25.0;
                if ($value > 35.0) return 'text-red-700 font-semibold';
                if ($value < $low || $value > $high) return 'text-amber-600';
                return 'text-green-700';

            case 'dissolved_oxygen':
                $threshold = $t['dissolved_oxygen_mg_l'] ?? 5.0;
                if ($value < 2.0) return 'text-red-700 font-semibold';
                if ($value < $threshold) return 'text-amber-600';
                return 'text-green-700';

            case 'ph':
                $low = $t['ph'][0] ?? 6.5;
                $high = $t['ph'][1] ?? 8.5;
                if ($value < 5.0 || $value > 9.0) return 'text-red-700 font-semibold';
                if ($value < $low || $value > $high) return 'text-amber-600';
                return 'text-green-700';

            default:
                return '';
        }
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
