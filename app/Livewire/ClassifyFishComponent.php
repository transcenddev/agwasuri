<?php

namespace App\Livewire;

use App\Models\WaterQualityData;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\CSV;

class ClassifyFishComponent extends Component
{
    public $predictionResults = []; // Initialize the variable

    public function predictFishType()
    {
        // Step 1: Aggregate previous day's averages for water quality data
        $averageData = WaterQualityData::selectRaw(
            'AVG(temperature) as temperature, AVG(salinity) as salinity, AVG(dissolved_oxygen) as dissolved_oxygen, AVG(ph_level) as ph_level'
        )->whereDate('recorded_at', now()->subDay()->toDateString()) // Get previous day's date
          ->first()
          ->toArray();

        // Step 2: Load dataset and train the model
        $dataset = Labeled::fromIterator(new CSV(storage_path('app/public/pond.csv'), true));
        $estimator = new ClassificationTree(10);
        $estimator->train($dataset);

        // Step 3: Use the model for prediction
        $testInput = [$averageData['temperature'], $averageData['salinity'], $averageData['dissolved_oxygen'], $averageData['ph_level']];
        $probabilities = $estimator->proba(new Unlabeled([$testInput]))[0];

        // Sort and get the top 3 fish types
        arsort($probabilities);
        $this->predictionResults = array_slice($probabilities, 0, 3, true);
    }
    public function render()
    {
        return view('livewire.classify-fish-component');
    }
}
