<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
    <div class="p-6">
        {{-- Button to trigger create user form and Filters button --}}
        <div class="mb-4 flex items-center justify-between">
             {{-- Filters Button --}}
             <button wire:click="toggleFilters" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                Filters {{ $showFilters ? '▲' : '▼' }} {{-- Simple indicator --}}
            </button>
             {{-- Create User Button --}}
             <button wire:click="createUser" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                Create User
            </button>
        </div>

        {{-- Session Messages (Green for success, Red for errors) --}}
        @if (session()->has('message'))
            <div class="mb-4 rounded bg-green-100 p-4 text-sm text-green-700">
                {{ session('message') }}
            </div>
        @endif
         @if (session()->has('error'))
            <div class="mb-4 rounded bg-red-100 p-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Conditionally render the Filter Section --}}
        @if ($showFilters)
            {{-- Reduced vertical padding (py-3) and gap (gap-y-2) --}}
            <div class="mb-4 rounded border border-blue-200 bg-blue-50 px-4 py-3 shadow-inner">
                {{-- <h3 class="mb-3 text-lg font-semibold text-blue-800">Filters</h3> --}} {{-- Removed heading for more space --}}
                {{-- Reduced vertical gap (gap-y-2) --}}
                <div class="grid grid-cols-1 gap-x-4 gap-y-2 md:grid-cols-2 lg:grid-cols-3">
                    {{-- Name Filter --}}
                    <div>
                        {{-- <label for="filterName" class="block text-sm font-medium text-gray-700">Name</label> --}}
                        <input wire:model.live.debounce.300ms="filterName" type="text" id="filterName" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by name...">
                    </div>
                    {{-- Email Filter --}}
                     <div>
                        {{-- <label for="filterEmail" class="block text-sm font-medium text-gray-700">Email</label> --}}
                        <input wire:model.live.debounce.300ms="filterEmail" type="text" id="filterEmail" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by email...">
                    </div>
                    {{-- Fishpond Name Filter --}}
                    <div>
                        {{-- <label for="filterFishpondName" class="block text-sm font-medium text-gray-700">Fishpond Name</label> --}}
                        <input wire:model.live.debounce.300ms="filterFishpondName" type="text" id="filterFishpondName" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by fishpond...">
                    </div>
                    {{-- Account Type Filter --}}
                    <div>
                       {{-- <label for="filterAccountType" class="block text-sm font-medium text-gray-700">Account Type</label> --}}
                       <select wire:model.live="filterAccountType" id="filterAccountType" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" title="Filter by Account Type">
                           <option value="">All Account Types</option> {{-- More descriptive default --}}
                           <option value="admin">Admin</option>
                           <option value="user">User</option>
                       </select>
                   </div>
                   {{-- Barangay Filter --}}
                    <div>
                        {{-- <label for="filterBarangay" class="block text-sm font-medium text-gray-700">Barangay</label> --}}
                        <input wire:model.live.debounce.300ms="filterBarangay" type="text" id="filterBarangay" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by barangay...">
                    </div>
                    {{-- Municipality Filter --}}
                    <div>
                        {{-- <label for="filterMunicipality" class="block text-sm font-medium text-gray-700">Municipality</label> --}}
                        <input wire:model.live.debounce.300ms="filterMunicipality" type="text" id="filterMunicipality" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by municipality...">
                    </div>
                    {{-- Province Filter --}}
                    <div>
                        {{-- <label for="filterProvince" class="block text-sm font-medium text-gray-700">Province</label> --}}
                        <input wire:model.live.debounce.300ms="filterProvince" type="text" id="filterProvince" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by province...">
                    </div>
                    {{-- Water Type Filter --}}
                     <div>
                        {{-- <label for="filterWaterType" class="block text-sm font-medium text-gray-700">Water Type</label> --}}
                        <select wire:model.live="filterWaterType" id="filterWaterType" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" title="Filter by Water Type">
                            <option value="">All Water Types</option> {{-- More descriptive default --}}
                            <option value="Freshwater">Freshwater</option>
                            <option value="Brackish">Brackish</option>
                            <option value="Saltwater">Saltwater</option>
                        </select>
                    </div>
                    {{-- Species Cultured Filter --}}
                    <div>
                        {{-- <label for="filterSpecies" class="block text-sm font-medium text-gray-700">Species Cultured</label> --}}
                        <input wire:model.live.debounce.300ms="filterSpecies" type="text" id="filterSpecies" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by species...">
                    </div>

                   {{-- Reset Button - Centered --}}
                   {{-- Removed top margin, adjusted span --}}
                   <div class="md:col-span-2 lg:col-span-3 flex justify-center">
                       <button wire:click="resetFilters" class="rounded bg-blue-200 px-4 py-2 text-sm font-medium text-blue-800 hover:bg-blue-300">Reset Filters</button>
                   </div>
                </div>
            </div>
        @endif

        {{-- Conditionally render the User Form Modal --}}
        @if ($showForm)
            <livewire:user-form :userId="$editingUserId" :key="'user-form-'.$editingUserId" /> {{-- Added key for better state management --}}
        @endif

        {{-- Conditionally render the Delete Confirmation Modal --}}
        @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancelDelete"></div>

                {{-- Modal panel --}}
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                {{-- Heroicon name: outline/exclamation-triangle --}}
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Delete User</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to delete this user? This action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        {{-- Confirm Delete Button --}}
                        <button type="button" wire:click="destroyUser" class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        {{-- Cancel Button --}}
                        <button type="button" wire:click="cancelDelete" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- User Table Wrapper --}}
        <div class="overflow-x-auto"> {{-- This div handles the horizontal scroll for the table --}}
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Full Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Account Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fishpond Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Barangay</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Municipality</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Province</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fishpond Area</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Species Cultured</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Water Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Password</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">API Key</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($users as $user)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->user_id }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->account_type }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->fishpond_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->barangay }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->municipality }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->province }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->total_fishpond_area }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ is_array($user->species_cultured) ? implode(', ', $user->species_cultured) : $user->species_cultured }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->water_type }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">********</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->api_key ? '********' : '' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <button wire:click="editUser({{ $user->user_id }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                {{-- Changed wire:click, removed wire:confirm --}}
                                <button wire:click="confirmDelete({{ $user->user_id }})" class="ml-2 text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- Pagination links are moved OUTSIDE this div --}}
        </div> {{-- End of overflow-x-auto div --}}

        {{-- Pagination links - Placed after the scrolling table container --}}
        <div class="mt-4">
            {{ $users->links() }}
        </div>

    </div>
</div>
