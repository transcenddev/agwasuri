@section('title', 'Historical Data')
<x-app-layout>
    <x-container class="py-6">
        @php
            $activeUserId = request('user') ?? session('selected_user', Auth::id());
        @endphp

        {{-- Global filters moved to topbar; local filter card removed to avoid duplication --}}

        <x-card class="mt-4 border-t border-gray-100 pt-4 shadow-sm">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <x-card-header>
                        <x-slot name="title">Historical Data</x-slot>
                    </x-card-header>

                    {{-- Export CSV removed temporarily --}}
                </div>
            </x-slot>

            <livewire:water-quality-data-table :userId="$activeUserId" wire:key="wq-table-{{ $activeUserId }}" />
        </x-card>
    </x-container>
</x-app-layout>
