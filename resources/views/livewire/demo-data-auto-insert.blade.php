@php
    // Defensive defaults for blade variables supplied by Livewire component
    $mode = $mode ?? 'demo';
    $interval = $interval ?? 10; // seconds
@endphp

<div>
    @if($mode === 'demo')
        <button wire:click="insertDemoData" class="px-3 py-1 mb-2 rounded-lg border text-sm font-medium transition-colors focus:outline-none bg-cvsu-500 text-white hover:bg-cvsu-600">
            Simulate Data (Manual)
        </button>
        <span class="ml-2 text-xs text-gray-500">Auto-inserting every {{ $interval }} seconds</span>
        <script>
            setInterval(function() {
                window.livewire.emit('insertDemoData');
            }, {{ $interval * 1000 }});
        </script>
    @else
        <span class="text-xs text-gray-500">Live Mode: Waiting for IoT device...</span>
    @endif
</div>
