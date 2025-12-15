<?php

namespace App\Livewire;

use Livewire\Component;

class RecommendationCard extends Component
{
    public ?int $userId = null;

    public string $species = 'Tilapia (Oreochromis niloticus)';
    public ?float $accuracy = 87.5;
    public string $confidence = '82%';
    public string $reason = 'Water temperature and salinity are within the optimal range for Tilapia growth.';
    public string $action = 'Suggested action: Monitor dissolved oxygen and adjust aeration as needed.';
    
    // Current water quality values for parameter breakdown
    public ?float $temperature = null;
    public ?float $dissolved_oxygen = null;
    public ?float $salinity = null;
    public ?float $ph_level = null;
    
    // Parameter status indicators
    public string $tempStatus = 'good';
    public string $doStatus = 'good';
    public string $salStatus = 'good';
    public string $phStatus = 'good';
    
    public bool $done = false;
    public bool $reminded = false;

    public function mount(?int $userId = null)
    {
        if ($userId !== null) {
            $this->userId = (int) $userId;
        }

        // Fetch latest water quality data for parameter breakdown
        $latest = \App\Models\WaterQualityData::where('user_id', $this->userId ?? auth()->id())
            ->latest('recorded_at')
            ->first();
            
        if ($latest) {
            $this->temperature = $latest->temperature;
            $this->dissolved_oxygen = $latest->dissolved_oxygen;
            $this->salinity = $latest->salinity;
            $this->ph_level = $latest->ph_level;
            
            // Determine status for each parameter (simplified logic - enhance based on species optimal ranges)
            $this->tempStatus = $this->getParameterStatus('temperature', $latest->temperature);
            $this->doStatus = $this->getParameterStatus('dissolved_oxygen', $latest->dissolved_oxygen);
            $this->salStatus = $this->getParameterStatus('salinity', $latest->salinity);
            $this->phStatus = $this->getParameterStatus('ph_level', $latest->ph_level);
        }

        // In future, fetch recommendation using ClassifyFish service using $this->userId
    }
    
    private function getParameterStatus(string $param, ?float $value): string
    {
        if ($value === null) return 'good';
        
        // Tilapia optimal ranges (adjust per species in production)
        $ranges = [
            'temperature' => ['min' => 25, 'max' => 32, 'warn_min' => 22, 'warn_max' => 35],
            'dissolved_oxygen' => ['min' => 5, 'max' => 10, 'warn_min' => 3, 'warn_max' => 12],
            'salinity' => ['min' => 0, 'max' => 20, 'warn_min' => 0, 'warn_max' => 30],
            'ph_level' => ['min' => 6.5, 'max' => 8.5, 'warn_min' => 6, 'warn_max' => 9],
        ];
        
        $range = $ranges[$param] ?? null;
        if (!$range) return 'good';
        
        if ($value >= $range['min'] && $value <= $range['max']) {
            return 'good';
        } elseif ($value >= $range['warn_min'] && $value <= $range['warn_max']) {
            return 'warning';
        }
        return 'danger';
    }

    public function markAsDone()
    {
        $this->done = true;
        $this->dispatchBrowserEvent('notification', ['message' => 'Recommendation marked as done.']);
    }

    public function remindLater()
    {
        $this->reminded = true;
        $this->dispatchBrowserEvent('notification', ['message' => 'Reminder scheduled for later.']);
    }

    public function render()
    {
        return view('livewire.recommendation-card');
    }
}
