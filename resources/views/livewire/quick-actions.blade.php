<div x-data="{ showConfirm:false }" x-init="$wire.load()" class="flex flex-col gap-2">
    @if(empty($ready))
        <div class="space-y-2">
            <div class="h-8 bg-gray-100 rounded"></div>
            <div class="h-8 bg-gray-100 rounded w-3/4"></div>
            <div class="h-8 bg-gray-100 rounded w-1/2"></div>
        </div>
    @else
        <button wire:click="exportCsv" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md border border-gray-200 bg-white text-sm hover:bg-gray-50">Export CSV</button>
        <button wire:click="refreshData" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md bg-cvsu text-white text-sm hover:bg-cvsu-700">Refresh Data</button>

        <button @click.prevent="showConfirm = true" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md border border-green-600 text-green-700 text-sm hover:bg-green-50">Train Model</button>

        <template x-if="showConfirm">
            <div class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="showConfirm=false"></div>
                <div class="bg-white rounded p-4 z-50 w-80">
                    <div class="text-sm font-medium">Confirm Train Model</div>
                    <div class="text-xs text-gray-600 mt-2">Training will queue a background job and may take several minutes. Proceed?</div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button @click.prevent="showConfirm=false" class="px-3 py-1 rounded bg-gray-100">Cancel</button>
                        <button @click.prevent="$wire.trainModelConfirmed(); showConfirm = false" class="px-3 py-1 rounded bg-cvsu text-white">Confirm</button>
                    </div>
                </div>
            </div>
        </template>

        <div class="mt-2 text-xs text-gray-500">Actions operate on the selected fishpond.</div>
    @endif
</div>
