@section('title', 'Dashboard')
<x-app-layout>
   
    <x-container x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'h-screen overflow-hidden pt-3 py-3' : 'pt-6 sm:pt-10 py-6'" class="transition-all duration-300">
            @php
                $activeUserId = request('user') ?? session('selected_user', Auth::id());
            @endphp
        {{-- Top Filters removed: topbar now contains the user/pond search; date filtering moved above chart --}}

        <div class="grid grid-cols-12" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" :class="compact ? 'gap-3' : 'gap-6'">

            <div class="col-span-12">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 w-full" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact">
                    <div class="flex items-center gap-3">
                        {{-- Water Quality Summary header removed per UX preference --}}
                        {{-- Pond selector removed here to avoid duplicate search inputs; top filter remains --}}
                    </div>
                    {{-- Connection status moved into WaterQualityCard component --}}
                </div>

                {{-- KPI cards: mobile-first 2x2 grid, desktop single row --}}
                <div class="transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'mt-1' : 'mt-2'">
                    <livewire:water-quality-card :userId="$activeUserId" wire:key="wq-card-{{ $activeUserId }}" />
                </div>

            </div>

            {{-- Main Content Row: Chart (55%) + Fish Recommendation (45%) --}}
            <x-card class="col-span-12 lg:col-span-7 lg:min-h-[600px] p-3" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? 'overflow-y-auto p-2' : ''">
                <livewire:water-quality-chart :userId="$activeUserId" wire:key="wq-trends-{{ $activeUserId }}" />
            </x-card>

            {{-- Fish Recommendation Card --}}
            <div class="col-span-12 lg:col-span-5 lg:min-h-[600px] lg:h-[600px] flex" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact">
                <livewire:recommendation-card class="h-full" :userId="$activeUserId" wire:key="rec-card-{{ $activeUserId }}" />
            </div>

            {{-- Recent Measurements - Full Width --}}
            <x-card class="col-span-12" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div class="font-medium text-gray-700 text-sm">Recent Measurements</div>
                        <a href="{{ route('historical-data') }}" class="text-xs text-cvsu hover:underline">See more →</a>
                    </div>
                </x-slot>

                <div class="mt-2">
                    <livewire:measurements-preview :userId="$activeUserId" wire:key="wq-preview-{{ $activeUserId }}" />
                </div>
            </x-card>

        {{-- Floating Compact View Toggle removed per UX decision --}}

    </x-container>
</x-app-layout>
