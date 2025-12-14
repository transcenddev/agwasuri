<div class="p-3 sm:p-4" x-data="{ open: false }">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="min-w-0 sm:col-span-2">
            <div class="text-sm text-gray-500">Recommended species</div>
            <div class="mt-1 text-lg font-semibold text-cvsu-700 truncate" style="color: #1B651B;">{{ $species }}</div>

            <div class="mt-2 text-sm text-gray-600 leading-relaxed break-words hidden sm:block">{{ $reason }}</div>
            <div class="mt-2 text-sm text-gray-600 block sm:hidden">{{ Str::limit($reason, 80) }}</div>

            <div class="mt-2 text-sm text-gray-700 font-medium whitespace-normal break-words">{{ $action }}</div>
        </div>

        <div class="sm:col-span-1 flex flex-col items-end gap-2">
            <div class="text-sm text-gray-500">Confidence</div>
            <div class="inline-flex items-center px-2 py-0.5 rounded-full text-sm font-medium mt-1" style="background: rgba(27,101,27,0.08); color: #1B651B;">
                {{ $confidence }}
            </div>

            <div class="mt-3 w-full sm:w-auto flex gap-2">
                <button wire:click="markAsDone" type="button" class="inline-flex items-center px-3 py-1 text-sm bg-cvsu text-white rounded hover:bg-cvsu-700 w-full sm:w-auto" {{ $done ? 'disabled' : '' }}>
                    {{ $done ? 'Done' : 'Mark as done' }}
                </button>
                <button wire:click="remindLater" type="button" class="inline-flex items-center px-3 py-1 text-sm border rounded text-gray-700 w-full sm:w-auto" {{ $reminded ? 'disabled' : '' }}>
                    {{ $reminded ? 'Reminded' : 'Remind later' }}
                </button>
            </div>
        </div>
    </div>

    <div class="mt-3 sm:hidden">
        <button type="button" x-on:click="open = !open" class="text-sm text-cvsu-700 hover:underline focus:outline-none">
            <span x-text="open ? 'Hide details' : 'View details'"></span>
        </button>

        <div x-show="open" x-cloak class="mt-3 text-sm text-gray-600 leading-relaxed">
            <div class="font-medium">Reason</div>
            <div class="mt-1">{{ $reason }}</div>
            <div class="mt-3 text-xs text-gray-400">This is a placeholder recommendation. Integration with the ClassifyFish service will provide dynamic results here.</div>
        </div>
    </div>
</div>
