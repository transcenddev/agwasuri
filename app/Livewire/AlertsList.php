<?php

namespace App\Livewire;

use Livewire\Component;

class AlertsList extends Component
{
    public $userId;

    public function mount($userId = null)
    {
        $this->userId = $userId;
    }

    public function render()
    {
        // Placeholder: real implementation should query alerts for $userId
        return view('livewire.alerts-list', [
            'alerts' => [],
        ]);
    }

    public function dismiss($id)
    {
        // Placeholder dismiss action
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Alert dismissed']);
    }
}
