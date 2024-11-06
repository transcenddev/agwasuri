<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class WaterQualityDataTable extends Component
{
    use WithPagination;

    #[On('echo:water-quality-data-created,WaterQualityCreated')]
    public function refreshTable()
    {
        \Log::info("Refreshing RefreshWaterQualityTable");
    }

    public function render()
    {
        $waterqualitydata = WaterQualityData::latest()->paginate(perPage: 20);
        return view('livewire.water-quality-data-table', compact('waterqualitydata'));
    }
}
