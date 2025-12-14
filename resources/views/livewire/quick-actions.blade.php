<div class="flex flex-col gap-2">
    <button wire:click="exportCsv" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md border border-gray-200 bg-white text-sm hover:bg-gray-50">Export CSV</button>
    <button wire:click="refreshData" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md bg-cvsu text-white text-sm hover:bg-cvsu-700">Refresh Data</button>
    <button wire:click="trainModel" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md border border-green-600 text-green-700 text-sm hover:bg-green-50">Train Model</button>
    <div class="mt-2 text-xs text-gray-500">Actions are context-aware and operate on the selected fishpond.</div>
</div>
