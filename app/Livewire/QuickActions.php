<?php

namespace App\Livewire;

use Livewire\Component;

class QuickActions extends Component
{
    public $userId;
    public $ready = false;

    public function mount($userId = null)
    {
        $this->userId = $userId;
    }

    public function render()
    {
        return view('livewire.quick-actions', ['ready' => $this->ready]);
    }

    public function exportCsv()
    {
        $this->dispatchBrowserEvent('notify', ['type' => 'info', 'message' => 'Export started']);
        // placeholder: trigger export logic or emit event
    }

    public function refreshData()
    {
        $this->emit('refresh-water-quality');
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Data refreshed']);
    }

    public function trainModel()
    {
        // safety: require confirmation via client side before proceeding
        $this->dispatchBrowserEvent('confirm-train', ['userId' => $this->userId]);
    }

    public function load()
    {
        $this->ready = true;
    }

    public function trainModelConfirmed()
    {
        $this->dispatchBrowserEvent('notify', ['type' => 'info', 'message' => 'Training requested']);
        $this->emit('train-model', $this->userId);
    }
}
