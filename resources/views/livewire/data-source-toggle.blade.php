@php
    // Defensive defaults: livewire component may not expose $mode in some tests
    $mode = $mode ?? 'demo';
@endphp

<div class="flex items-center gap-4">
    <span class="font-semibold text-gray-700 dark:text-gray-200">Data Source:</span>
    <button wire:click="setMode('demo')" class="px-3 py-1 rounded-lg border text-sm font-medium transition-colors focus:outline-none {{ $mode === 'demo' ? 'bg-water-500 text-white' : 'bg-white dark:bg-black text-water-600 border-water-500' }}">
        Demo Mode
    </button>
    <button wire:click="setMode('live')" class="px-3 py-1 rounded-lg border text-sm font-medium transition-colors focus:outline-none {{ $mode === 'live' ? 'bg-water-500 text-white' : 'bg-white dark:bg-black text-water-600 border-water-500' }}">
        Live Mode
    </button>
</div>
<script>
    window.addEventListener('dataSourceModeChanged', e => {
        // Optionally reload or trigger Livewire updates
        window.location.reload();
    });
</script>
