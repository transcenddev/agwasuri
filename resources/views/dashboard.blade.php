@section('title', 'Dashboard')
<x-app-layout>
   
    <x-container class="pt-6 sm:pt-10 py-6">
            @php
                $activeUserId = request('user') ?? session('selected_user', Auth::id());
            @endphp
        {{-- Top Filters removed: topbar now contains the user/pond search; date filtering moved above chart --}}

        <div class="grid grid-cols-12 gap-6">

            <x-card class="col-span-12">
            <x-slot name="header">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 w-full">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-gray-700">Water Quality Summary</h3>
                        {{-- Pond selector removed here to avoid duplicate search inputs; top filter remains --}}
                    </div>
                    {{-- Connection status moved into WaterQualityCard component --}}
                </div>
            </x-slot>

            {{-- KPI cards: mobile-first 2x2 grid, desktop single row --}}
            <div class="mt-2">
                <livewire:water-quality-card :userId="$activeUserId" wire:key="wq-card-{{ $activeUserId }}" />
            </div>

            {{-- Recommendation placeholder card (separate Livewire component)
                 Hidden temporarily — re-enable by changing the condition below to true. --}}
            @if(false)
                <div class="mt-4">
                    <livewire:recommendation-card :userId="$activeUserId" wire:key="rec-card-{{ $activeUserId }}" />
                </div>
            @endif
        </x-card>

            <x-card class="col-span-12 lg:col-span-8">
                    <livewire:water-quality-chart :userId="$activeUserId" wire:key="wq-trends-{{ $activeUserId }}" />
            </x-card>

            <div class="col-span-12 lg:col-span-4">
                @include('components.right-panel')
            </div>

            <x-card class="col-span-12">
                <x-slot name="header">
                    <x-card-header>
                        <x-slot name="title">Measurements</x-slot>
                    </x-card-header>
                </x-slot>
                <livewire:water-quality-data-table :userId="$activeUserId" wire:key="wq-table-{{ $activeUserId }}" />
            </x-card>
    </x-container>
</x-app-layout>
