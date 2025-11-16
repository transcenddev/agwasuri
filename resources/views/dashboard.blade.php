<x-app-layout>
   
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Top Filters — improved spacing and responsive layout --}}
        <div class="bg-white shadow rounded-lg p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:space-x-6 space-y-3 md:space-y-0">
                {{-- User Filter (Only for Admin) - flexible, takes remaining space --}}
                @if(Auth::check() && auth()->user()->isAdmin())
                    <div class="flex-1">
                        <livewire:user-filter />
                    </div>
                @endif

                {{-- Date Filter (For Everyone) — keeps natural width --}}
                <div class="w-full md:w-auto">
                    <livewire:date-filter />
                </div>

                {{-- Optional actions area (keeps layout consistent) --}}
                <div class="flex items-center">
                    <!-- Placeholder for action buttons (Apply / Reset) -->
                    <button type="button" class="ml-auto hidden md:inline-flex px-4 py-2 rounded-lg bg-cvsu text-white hover:bg-cvsu-700 transition">Apply</button>
                </div>
            </div>
        </div>


        {{-- Data Display Components --}}
        <div class="p-6 mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            {{-- Pass the initial user ID (either logged-in user or potentially admin-selected) --}}
            {{-- Note: The 'user-selected' event will override this later --}}
            <livewire:water-quality-card :userId="Auth::id()" />
        </div>

        <div class="p-6 mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <livewire:water-quality-chart :userId="Auth::id()" />
        </div>

        <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <livewire:water-quality-data-table :userId="Auth::id()" />
        </div>
    </div>
</x-app-layout>
