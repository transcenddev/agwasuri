@section('title', 'Optimal Fish')
<x-app-layout>
    <x-container class="py-6">
                                                                                                                @php
            $activeUserId = request('user') ?? session('selected_user', Auth::id());
        @endphp

        {{-- Global filters moved to topbar; local filter card removed to avoid duplication --}}

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
