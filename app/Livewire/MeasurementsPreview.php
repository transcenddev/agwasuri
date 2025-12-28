<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Livewire\Attributes\On;
use Livewire\Component;

class MeasurementsPreview extends Component
{
    public $userId;
    public $items = [];

    #[On('user-selected')]
    public function updateUser($userId): void
    {
        $this->userId = $userId;
    }

    #[On('echo:water-quality-data-created,WaterQualityCreated')]
    public function handleNewDataCreated(array $event): void
    {
        $matchesUser = isset($event['waterQualityData']['user_id']) && $event['waterQualityData']['user_id'] == $this->userId;
        if ($matchesUser) {
            $this->render();
        }
    }

    public function mount($userId = null): void
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

        $this->items = $items->map(function ($r) {
            $labels = $this->metricLabels($r);
            $aggregate = water_status_from_metrics($labels);

            $r->ui_status = $aggregate['status'];
            $r->culprits = $aggregate['culprits'];
            $r->culpritsText = empty($aggregate['culprits']) ? '' : implode(', ', $aggregate['culprits']);

            $r->badgeClass = $aggregate['tokens']['pill'];
            $r->statusBorderClass = $aggregate['tokens']['border'];
            $r->statusDotClass = $aggregate['tokens']['dot'];

            // Per-metric pills (useful for detailed rendering)
            $r->tempLabel = $labels['temperature'];
            $r->salLabel = $labels['salinity'];
            $r->doLabel = $labels['dissolved_oxygen'];
            $r->phLabel = $labels['ph_level'];
            $r->tempPillClass = water_status_tokens($r->tempLabel)['pill'];
            $r->salPillClass = water_status_tokens($r->salLabel)['pill'];
            $r->doPillClass = water_status_tokens($r->doLabel)['pill'];
            $r->phPillClass = water_status_tokens($r->phLabel)['pill'];

            return $r;
        });

        return view('livewire.measurements-preview', [
            'items' => $this->items,
        ]);
    }

    protected function metricLabels($row): array
    {
        $labels = [];

        foreach ([
            'temperature' => $row->temperature,
            'salinity' => $row->salinity,
            'dissolved_oxygen' => $row->dissolved_oxygen,
            'ph_level' => $row->ph_level,
        ] as $metric => $value) {
            try {
                $labels[$metric] = strtoupper(classifyWaterParam($metric === 'ph_level' ? 'ph' : $metric, $value)['label'] ?? 'UNKNOWN');
            } catch (\Throwable $th) {
                $labels[$metric] = 'UNKNOWN';
            }
        }

        return $labels;
    }
}
