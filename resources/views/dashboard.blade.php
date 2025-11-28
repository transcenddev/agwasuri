<x-app-layout>
   
    <x-container class="pt-20 py-12">
        {{-- Top Filters — improved spacing and responsive layout --}}
        <x-card class="mb-6 hero-light overflow-visible">
            <x-slot name="header">
                <div class="w-full">
                    {{-- header left area is intentionally blank; filters live in the body --}}
                </div>
            </x-slot>

            <div class="flex flex-col md:flex-row md:items-center md:space-x-6 space-y-3 md:space-y-0">
                @if(Auth::check() && auth()->user()->isAdmin())
                    <div class="flex-1">
                        <livewire:user-filter />
                    </div>
                @endif

                <div class="w-full md:w-auto">
                    <livewire:date-filter />
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
            <livewire:water-quality-card :userId="Auth::id()" />
        </x-card>

        <x-card class="mb-6">
            <x-slot name="header">
                <x-card-header>
                    <x-slot name="title">Trends</x-slot>
                </x-card-header>
            </x-slot>
            <livewire:water-quality-chart :userId="Auth::id()" />
        </x-card>

        <x-card>
            <x-slot name="header">
                <x-card-header>
                    <x-slot name="title">Measurements</x-slot>
                </x-card-header>
            </x-slot>
            <livewire:water-quality-data-table :userId="Auth::id()" />
        </x-card>
    </x-container>
</x-app-layout>
