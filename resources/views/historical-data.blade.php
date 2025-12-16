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
                    <div class="flex items-center gap-4">
                        <x-card-header>
                            <x-slot name="title">
                                <h1 class="text-lg font-semibold text-gray-900">Historical Data</h1>
                            </x-slot>
                        </x-card-header>
                        <div class="flex items-center gap-3 text-xs text-gray-600">
                            <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-600"></span> BEST</div>
                            <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-600"></span> GOOD</div>
                            <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-yellow-500"></span> BAD</div>
                            <div class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-600"></span> HARM</div>
                        </div>
                        
                    </div>

                    {{-- Export CSV removed temporarily --}}
                </div>
            </x-slot>

            <livewire:water-quality-data-table :userId="$activeUserId" wire:key="wq-table-{{ $activeUserId }}" />
        </x-card>
    </x-container>
</x-app-layout>
