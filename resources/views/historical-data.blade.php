@section('title', 'Historical Data')
<x-app-layout>
    <x-container class="py-6">
        @php
            $activeUserId = request('user') ?? session('selected_user', Auth::id());
        @endphp

        {{-- Global filters moved to topbar; local filter card removed to avoid duplication --}}

        <x-card class="mt-4">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4 overflow-visible">
                        <x-card-header>
                            <x-slot name="title">
                                <h1 class="text-lg font-semibold text-gray-900">Historical Data</h1>
                            </x-slot>
                        </x-card-header>

                        {{-- Replace legend with compact status-key popover --}}
                        <div x-data="{ open:false }" class="relative ml-2">
                            <button @click="open = !open" @keydown.escape.window="open = false" aria-expanded="false" aria-controls="status-key-popover" type="button" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-cvsu">
                                <span class="sr-only">Status key</span>
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg p-3 text-sm text-gray-700 z-50" id="status-key-popover" @click.outside="open=false">
                                <div class="font-medium mb-2">Status key</div>
                                <div class="flex flex-col gap-2">
                                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-600"></span> <span class="text-xs text-gray-700">BEST</span></div>
                                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-sky-600"></span> <span class="text-xs text-gray-700">GOOD</span></div>
                                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span> <span class="text-xs text-gray-700">BAD</span></div>
                                    <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-600"></span> <span class="text-xs text-gray-700">HARM</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Export CSV removed temporarily --}}
                </div>
            </x-slot>

            <livewire:water-quality-data-table :userId="$activeUserId" wire:key="wq-table-{{ $activeUserId }}" />
        </x-card>
    </x-container>
</x-app-layout>
