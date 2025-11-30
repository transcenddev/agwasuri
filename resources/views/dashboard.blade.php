<x-app-layout>
   
       <x-container class="pt-20 py-12">
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
                <x-card-header>
                    <x-slot name="title">Water Quality Summary</x-slot>
                </x-card-header>
            </x-slot>
            {{-- <livewire:water-quality-card :userId="Auth::id()" /> --}}
             <livewire:water-quality-card :userId="$activeUserId" wire:key="wq-card-{{ $activeUserId }}" />
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
