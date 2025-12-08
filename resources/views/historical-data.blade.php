@section('title', 'Historical Data')
<x-app-layout>
    <x-container class="pt-20 py-12">
        @php
            $activeUserId = request('user') ?? session('selected_user', Auth::id());
        @endphp

        {{-- Top Filters — show user selector for admins and date filter for all views --}}
            <x-card class="mb-6 hero-light overflow-visible shadow-sm">
            <x-slot name="header">
                <div class="w-full">
                    {{-- header left area is intentionally blank; filters live in the body --}}
                </div>
            </x-slot>

            <div class="flex flex-col md:flex-row items-center justify-center md:items-center md:space-x-6 space-y-3 md:space-y-0">
                @if(Auth::check() && auth()->user()->isAdmin())
                    <div class="w-full">
                        <livewire:user-filter />
                    </div>
                @endif

                <div class="w-full md:w-auto flex-shrink-0">
                    <livewire:date-filter :user-id="$activeUserId" />
                </div>
            </div>
        </x-card>

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
