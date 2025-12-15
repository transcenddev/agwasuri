@section('title', 'Optimal Fish')
<x-app-layout>
    <x-container class="py-4">
        @php
            $activeUserId = request('user') ?? session('selected_user', Auth::id());
        @endphp

        {{-- Global filters moved to topbar; local filter card removed to avoid duplication --}}
        {{-- Species gallery removed to declutter page; gallery available elsewhere if needed --}}

        <livewire:classify-fish :userId="$activeUserId" />
    </x-container>
</x-app-layout>
