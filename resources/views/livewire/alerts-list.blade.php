<div>
    <div id="alerts-live" aria-live="polite" class="flex flex-col gap-2">
        @forelse($alerts as $alert)
            <div class="flex items-start justify-between gap-3 p-2 rounded-md bg-red-50 border border-red-100">
                <div class="text-sm text-red-700">{{ $alert['message'] ?? 'Alert' }}</div>
                <div>
                    <button wire:click="dismiss({{ $alert['id'] ?? 0 }})" class="text-xs text-red-600">Dismiss</button>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500">No active alerts</div>
        @endforelse
    </div>
</div>
