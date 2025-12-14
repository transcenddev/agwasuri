@section('title', 'Account Management')
<x-app-layout>
    <x-container class="py-6">
        {{-- Button moved to UserTable component --}}
        {{-- The shared `x-container` provides consistent padding-top and max-width --}}

        <livewire:user-table />
    </x-container>
</x-app-layout>
