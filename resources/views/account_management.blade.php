<x-app-layout>
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        {{-- Button moved to UserTable component --}}
        {{-- <div class="mb-4 flex justify-end"> ... button removed ... </div> --}}

        {{-- Include the User Table Livewire component --}}
            <livewire:user-table />
    </div>
</x-app-layout>
