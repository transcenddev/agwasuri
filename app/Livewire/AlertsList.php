<?php

namespace App\Livewire;

use Livewire\Component;

class AlertsList extends Component
{
    public $userId;
    public $alerts = [];
    public $ready = false;

    protected $listeners = [
        'newAlert' => 'handleNewAlert',
        // Listen to server broadcast on 'alerts' channel if used: event AlertCreated
        'echo:alerts,AlertCreated' => 'onAlertBroadcast',
        // Forward browser events
        'alert:created' => 'handleNewAlert',
        // allow wire:init to call load()
        'load' => 'load',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId;
    }

    public function load()
    {
        $this->ready = true;
        $this->fetchAlerts();
    }

    protected function fetchAlerts()
    {
        try {
            if (class_exists('\App\\Models\\Alert')) {
                $model = \App\Models\Alert::query();
                if ($this->userId) $model->where('user_id', $this->userId);
                $this->alerts = $model->latest()->limit(20)->get()->toArray();
                return;
            }

            // Fallback to DB table if present
            if (\Schema::hasTable('alerts')) {
                $q = \DB::table('alerts')->orderBy('created_at', 'desc')->limit(20);
                if ($this->userId) $q->where('user_id', $this->userId);
                $this->alerts = $q->get()->map(function($r){ return (array) $r; })->toArray();
                return;
            }
        } catch (\Exception $e) {
            // ignore and keep alerts empty
        }

        $this->alerts = [];
    }

    public function render()
    {
        return view('livewire.alerts-list', [
            'alerts' => $this->alerts,
        ]);
    }

    public function dismiss($id)
    {
        try {
            if (class_exists('\App\\Models\\Alert')) {
                \App\Models\Alert::where('id', $id)->update(['read' => true]);
            } elseif (\Schema::hasTable('alerts')) {
                \DB::table('alerts')->where('id', $id)->update(['read' => 1]);
            }
        } catch (\Exception $e) {
            // ignore
        }

        // remove locally
        $this->alerts = array_values(array_filter($this->alerts, function($a) use ($id){ return ($a['id'] ?? null) != $id; }));
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Alert dismissed']);
    }

    public function handleNewAlert($payload)
    {
        // prepend
        array_unshift($this->alerts, is_array($payload) ? $payload : (array) $payload);
        // keep max 20
        $this->alerts = array_slice($this->alerts, 0, 20);
    }

    public function onAlertBroadcast($payload)
    {
        // Expect broadcast payload contains alert data
        $this->handleNewAlert($payload);
    }
}
