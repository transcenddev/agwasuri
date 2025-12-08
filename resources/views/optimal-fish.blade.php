@section('title', 'Optimal Fish')
<x-app-layout>
    <x-container class="pt-20 py-12">
                                                                                                                @php
            $activeUserId = request('user') ?? session('selected_user', Auth::id());
        @endphp

        {{-- Top Filters (same as dashboard) --}}
        <x-card class="mb-6 hero-light overflow-visible">
            <x-slot name="header">
                <div class="w-full">
                    {{-- filters live in the body --}}
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
            </div>
        </x-card>

        <x-card class="mb-6">
            <x-slot name="header">
                <x-card-header>
                    <x-slot name="title">Optimal Fish</x-slot>
                </x-card-header>
            </x-slot>

            <livewire:classify-fish :userId="$activeUserId" />
        </x-card>
    </x-container>
</x-app-layout>
