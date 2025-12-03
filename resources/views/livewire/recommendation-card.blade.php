<div>
    <x-card class="mb-6">
        <x-slot name="header">
            <x-card-header>
                <x-slot name="title">Recommendation</x-slot>
            </x-card-header>
        </x-slot>

        <div class="p-4 sm:p-6" x-data="{ open: false }">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-sm text-gray-500">Recommended species</div>
                    <div class="mt-1 text-lg font-semibold text-cvsu-700 truncate" style="color: #1B651B;">{{ $this->species }}</div>

                    {{-- One-line reason on larger screens; truncated on mobile with 'View details' toggle --}}
                    <div class="mt-2 text-sm text-gray-600">
                        <div class="hidden sm:block">{{ $this->reason }}</div>
                        <div class="block sm:hidden">{{ Str::limit($this->reason, 80) }}</div>
                    </div>

                    {{-- One-line suggested action --}}
                    <div class="mt-2 text-sm text-gray-700 font-medium">{{ $this->action }}</div>
                </div>

                <div class="flex-shrink-0 text-right space-y-2">
                    <div class="text-sm text-gray-500">Confidence</div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-1" style="background: rgba(27,101,27,0.08); color: #1B651B;">
                        {{ $this->confidence }}
                    </div>

                    {{-- Action buttons --}}
                    <div class="mt-3 flex gap-2 justify-end">
                        <button wire:click="markAsDone" type="button" class="inline-flex items-center px-3 py-1 text-sm bg-cvsu text-white rounded hover:bg-cvsu-700" {{ $this->done ? 'disabled' : '' }}>
                            {{ $this->done ? 'Done' : 'Mark as done' }}
                        </button>
                        <button wire:click="remindLater" type="button" class="inline-flex items-center px-3 py-1 text-sm border rounded text-gray-700" {{ $this->reminded ? 'disabled' : '' }}>
                            {{ $this->reminded ? 'Reminded' : 'Remind later' }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile details toggle --}}
            <div class="mt-3 sm:hidden">
                <button type="button" x-on:click="open = !open" class="text-sm text-cvsu-700 hover:underline focus:outline-none" aria-expanded="false">
                    <span x-text="open ? 'Hide details' : 'View details'"></span>
                </button>

                <div x-show="open" x-cloak class="mt-3 text-sm text-gray-600">
                    <div class="font-medium">Reason</div>
                    <div class="mt-1">{{ $this->reason }}</div>
                    <div class="mt-3 text-xs text-gray-400">This is a placeholder recommendation. Integration with the ClassifyFish service will provide dynamic results here.</div>
                </div>
            </div>
        </div>
    </x-card>
</div>
