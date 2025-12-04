<x-app-layout>
   
    <x-container class="pt-6 sm:pt-10 py-6">
            @php
                $activeUserId = request('user') ?? session('selected_user', Auth::id());
            @endphp
        {{-- Top Filters — improved spacing and responsive layout --}}
        <x-card class="mb-6 hero-light overflow-visible">
            <x-slot name="header">
                <div class="w-full">
                    {{-- header left area is intentionally blank; filters live in the body --}}
                </div>
            </x-slot>

                <div class="flex flex-col md:flex-row md:flex-wrap lg:flex-nowrap lg:items-center gap-3 lg:gap-6">
                @if(Auth::check() && auth()->user()->isAdmin())
                        <div class="w-full lg:flex-1">
                        <livewire:user-filter />
                    </div>
                @endif

                <div class="w-full md:w-auto">
                    <livewire:date-filter :user-id="$activeUserId" />
                </div>

                <!-- <div class="flex items-center">
                    <button type="button" class="ml-auto hidden md:inline-flex px-4 py-2 rounded-lg bg-cvsu text-white hover:bg-cvsu-700 transition">Apply</button>
                </div> -->
            </div>
        </x-card>

        <x-card class="mb-6">
            <x-slot name="header">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 w-full">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-gray-700">Water Quality Summary</h3>
                        {{-- Pond selector removed here to avoid duplicate search inputs; top filter remains --}}
                    </div>

                    <div class="flex items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Last updated</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="relative w-3 h-3 flex items-center justify-center" aria-hidden="true">
                                <span class="absolute inset-0 m-auto inline-flex h-3 w-3 rounded-full bg-rose-400 opacity-75 animate-ping"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-400"></span>
                            </div>
                            <span class="font-medium">Disconnected</span>
                            <span class="sr-only"> — Sensor offline</span>
                        </div>
                    </div>
                </div>
            </x-slot>

            {{-- KPI cards: mobile-first 2x2 grid, desktop single row --}}
            <div class="mt-4">
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

        <x-card class="mb-6">
            <x-slot name="header">
                <x-card-header>
                    <x-slot name="title">Trends</x-slot>
                </x-card-header>
            </x-slot>
            {{-- <livewire:water-quality-chart :userId="Auth::id()" /> --}}
             <livewire:water-quality-chart :userId="$activeUserId" wire:key="wq-chart-{{ $activeUserId }}" />
        </x-card>

        <x-card>
            <x-slot name="header">
                <x-card-header>
                    <x-slot name="title">Measurements</x-slot>
                </x-card-header>
            </x-slot>
            {{-- <livewire:water-quality-data-table :userId="Auth::id()" /> --}}
             <livewire:water-quality-data-table :userId="$activeUserId" wire:key="wq-table-{{ $activeUserId }}" />
        </x-card>
    </x-container>
</x-app-layout>
