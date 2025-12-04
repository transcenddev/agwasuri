<?php

namespace App\Livewire;

use Asantibanez\LivewireCharts\Models\LineChartModel;
use Livewire\Component;

class LivewireChartsLineWrapper extends Component
{
    /**
     * Serializable line chart model (array form) consumed by the vendor view.
     * Accepts either an array or a LineChartModel instance in mount().
     *
     * @var array
     */
    public array $lineChartModel = [];

    public function mount($lineChartModel = [])
    {
        if ($lineChartModel instanceof LineChartModel) {
            if (method_exists($lineChartModel, 'toArray')) {
                $this->lineChartModel = $lineChartModel->toArray();
            } else {
                $this->lineChartModel = [];
            }
            return;
        }

        if (is_array($lineChartModel)) {
            $this->lineChartModel = $lineChartModel;
            return;
        }

        // Last resort: try json decode string shapes
        if (is_string($lineChartModel)) {
            $decoded = json_decode($lineChartModel, true);
            $this->lineChartModel = is_array($decoded) ? $decoded : [];
            return;
        }

        $this->lineChartModel = [];
    }

    public function render()
    {
        // The vendor provides different small views for single/multi-line charts.
        $isMulti = isset($this->lineChartModel['isMultiLine']) && $this->lineChartModel['isMultiLine'];

        if ($isMulti) {
            return view('livewire-charts::livewire-multi-line-chart', ['lineChartModel' => $this->lineChartModel]);
        }

        return view('livewire-charts::livewire-line-chart', ['lineChartModel' => $this->lineChartModel]);
    }
}
