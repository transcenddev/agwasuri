<?php

namespace App\Livewire;

use Livewire\Component;

class RecommendationCard extends Component
{
    public ?int $userId = null;

    public string $species = 'Tilapia (Oreochromis niloticus)';
    public string $confidence = '82%';
    public string $reason = 'Water temperature and salinity are within the optimal range for Tilapia growth.';
    public string $action = 'Suggested action: Monitor dissolved oxygen and adjust aeration as needed.';
    public bool $done = false;
    public bool $reminded = false;

    public function mount(?int $userId = null)
    {
        if ($userId !== null) {
            $this->userId = (int) $userId;
        }

        // In future, fetch recommendation using ClassifyFish service using $this->userId
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
