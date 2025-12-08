@section('title', 'Account Management')
<x-app-layout>
    <x-container class="pt-20 py-12">
        {{-- Button moved to UserTable component --}}
        {{-- The shared `x-container` provides consistent padding-top and max-width --}}

        <livewire:user-table />
    </x-container>
</x-app-layout>
