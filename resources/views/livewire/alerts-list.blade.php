<div>
    <div id="alerts-live" aria-live="polite" class="flex flex-col gap-2">
        @if(!isset($alerts) || empty($alerts))
            <div class="text-sm text-gray-500">No active alerts</div>
        @else
            @foreach($alerts as $alert)
                <div class="flex items-start justify-between gap-3 p-2 rounded-md {{ ($alert['severity'] ?? '') === 'critical' ? 'bg-red-50 border border-red-100' : 'bg-yellow-50 border border-yellow-100' }}">
                    <div class="text-sm {{ ($alert['severity'] ?? '') === 'critical' ? 'text-red-700' : 'text-amber-700' }}">{{ $alert['message'] ?? 'Alert' }}</div>
                    <div>
                        <button wire:click="dismiss({{ $alert['id'] ?? 0 }})" class="text-xs text-gray-600">Dismiss</button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <script>
        // Listen for a global browser event to receive alerts and forward to Livewire
        window.addEventListener('alert:created', function(e){
            try { Livewire.emit('newAlert', e.detail); } catch (err) {}
        });
    </script>
</div>
