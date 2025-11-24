<x-card>
    <x-slot name="header">
        <x-card-header>
            <x-slot name="title">Account Management</x-slot>
            <x-slot name="actions">
                <div class="flex items-center space-x-2">
                    <button wire:click="toggleFilters" class="rounded bg-cvsu-600 px-3 py-2 text-sm font-medium text-white hover:bg-cvsu-700">Filters {{ $showFilters ? '▲' : '▼' }}</button>
                    <button wire:click="createUser" class="rounded bg-cvsu-600 px-3 py-2 text-sm font-medium text-white hover:bg-cvsu-700">Create User</button>
                </div>
            </x-slot>
        </x-card-header>
    </x-slot>

    <div class="p-6">
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
            <div class="mb-4 rounded border border-cvsu-200 bg-cvsu-50 px-4 py-3 shadow-inner">
                <div class="grid grid-cols-1 gap-x-4 gap-y-2 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <input wire:model.live.debounce.300ms="filterName" type="text" id="filterName" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by name...">
                    </div>
                    <div>
                        <input wire:model.live.debounce.300ms="filterEmail" type="text" id="filterEmail" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by email...">
                    </div>
                    <div>
                        <input wire:model.live.debounce.300ms="filterFishpondName" type="text" id="filterFishpondName" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by fishpond...">
                    </div>
                    <div>
                       <select wire:model.live="filterAccountType" id="filterAccountType" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" title="Filter by Account Type">
                           <option value="">All Account Types</option>
                           <option value="admin">Admin</option>
                           <option value="user">User</option>
                       </select>
                   </div>
                   <div>
                        <input wire:model.live.debounce.300ms="filterBarangay" type="text" id="filterBarangay" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by barangay...">
                    </div>
                    <div>
                        <input wire:model.live.debounce.300ms="filterMunicipality" type="text" id="filterMunicipality" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by municipality...">
                    </div>
                    <div>
                        <input wire:model.live.debounce.300ms="filterProvince" type="text" id="filterProvince" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by province...">
                    </div>
                    <div>
                        <select wire:model.live="filterWaterType" id="filterWaterType" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" title="Filter by Water Type">
                            <option value="">All Water Types</option>
                            <option value="Freshwater">Freshwater</option>
                            <option value="Brackish">Brackish</option>
                            <option value="Saltwater">Saltwater</option>
                        </select>
                    </div>
                    <div>
                        <input wire:model.live.debounce.300ms="filterSpecies" type="text" id="filterSpecies" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Filter by species...">
                    </div>

                   <div class="md:col-span-2 lg:col-span-3 flex justify-center">
                       <button wire:click="resetFilters" class="rounded bg-cvsu-200 px-4 py-2 text-sm font-medium text-cvsu-700 hover:bg-cvsu-300">Reset Filters</button>
                   </div>
                </div>
            </div>
        @endif

        {{-- Conditionally render the User Form Modal --}}
        @if ($showForm)
            <livewire:user-form :userId="$editingUserId" :key="'user-form-'.$editingUserId" />
        @endif

        {{-- Conditionally render the Delete Confirmation Modal --}}
        @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancelDelete"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Delete User</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to delete this user? This action cannot be undone.</p>
                                    @if($deletingUserName)
                                        <p class="mt-2 text-sm text-gray-700">To confirm, type the full name: <strong>{{ $deletingUserName }}</strong></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <input wire:model.defer="deleteConfirmName" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Type full name to confirm">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        @php
                            $canDelete = trim($deleteConfirmName ?? '') === trim($deletingUserName ?? '');
                            $isSelf = auth()->check() && optional(auth()->user())->user_id === $deletingUserId;
                        @endphp
                        <button type="button" wire:click="destroyUser" @if(! $canDelete || $isSelf) disabled @endif class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button type="button" wire:click="cancelDelete" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="overflow-x-auto relative overflow-y-visible"> {{-- This div handles the horizontal scroll for the table; allow vertical overflow for dropdowns --}}
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Full Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Location</th>
                        <th scope="col" class="sticky right-0 z-10 bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($users as $user)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->user_id }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 truncate max-w-[18rem]" title="{{ $user->email }}">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $user->account_type }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="font-medium">{{ $user->fishpond_name ?: ($user->barangay ? $user->barangay : '—') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->municipality }}{{ $user->municipality && $user->province ? ', ' : '' }}{{ $user->province }}</div>
                            </td>
                            <td class="sticky right-0 bg-white whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <button wire:click="showDetails({{ $user->user_id }})" class="text-cvsu-600 hover:text-cvsu-700 mr-2">Details</button>

                                <div class="inline-block relative" x-data="{open:false, top:0, left:0, openMenu(e){ const r = e.target.getBoundingClientRect(); this.top = r.bottom + window.scrollY + 6; this.left = r.right + window.scrollX - 140; this.open = true }, closeMenu(){ this.open=false }}" @keydown.escape.window="open=false">
                                    <button @click.prevent="openMenu($event)" class="px-2 py-1 rounded hover:bg-gray-100" aria-haspopup="true" :aria-expanded="open">⋮</button>

                                    <template x-teleport="body">
                                        <div x-cloak x-show="open" x-transition style="position: absolute;" :style="`top: ${top}px; left: ${left}px;`" @click.away="closeMenu()" class="w-36 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                            <div class="py-1">
                                                <a href="#" @click.prevent="closeMenu(); $dispatch('livewire-action', {name: 'edit', id: {{ $user->user_id }}})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                                                <a href="#" @click.prevent="closeMenu(); $dispatch('livewire-action', {name: 'delete', id: {{ $user->user_id }}})" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Delete</a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">No users found.</td>
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

    {{-- Details Modal --}}
    @if($selectedUser)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeDetails"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">User Details</h3>
                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <div class="text-xs text-gray-500">ID</div>
                                        <div class="font-medium">{{ $selectedUser->user_id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Full Name</div>
                                        <div class="font-medium">{{ $selectedUser->first_name }} {{ $selectedUser->last_name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Email</div>
                                        <div class="font-medium">{{ $selectedUser->email }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Account Type</div>
                                        <div class="font-medium">{{ $selectedUser->account_type }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Fishpond Name</div>
                                        <div class="font-medium">{{ $selectedUser->fishpond_name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Location</div>
                                        <div class="font-medium">{{ $selectedUser->barangay }} {{ $selectedUser->municipality ? ', ' . $selectedUser->municipality : '' }}{{ $selectedUser->province ? ', ' . $selectedUser->province : '' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Fishpond Area</div>
                                        <div class="font-medium">{{ $selectedUser->total_fishpond_area }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Species Cultured</div>
                                        <div class="font-medium">{{ is_array($selectedUser->species_cultured) ? implode(', ', $selectedUser->species_cultured) : $selectedUser->species_cultured }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Water Type</div>
                                        <div class="font-medium">{{ $selectedUser->water_type }}</div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <div class="text-xs text-gray-500">API Key</div>
                                        <div class="flex items-center space-x-2">
                                            <div id="api-key-text" class="font-mono text-sm">{{ $selectedUser->api_key ? '********' : '' }}</div>
                                            @if($selectedUser->api_key)
                                                <button type="button" id="reveal-api" class="text-sm text-cvsu-600 hover:underline">Reveal</button>
                                                <button type="button" id="copy-api" class="text-sm text-gray-700">Copy</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeDetails" class="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('alpine:init', () => {});
        // Simple copy/reveal handlers for modal (only when modal exists)
        (function(){
            const reveal = document.getElementById('reveal-api');
            const copyBtn = document.getElementById('copy-api');
            const textEl = document.getElementById('api-key-text');
            if (reveal && textEl) {
                reveal.addEventListener('click', function(){
                    textEl.textContent = '{{ $selectedUser->api_key }}';
                    this.style.display = 'none';
                });
            }
            if (copyBtn && textEl) {
                copyBtn.addEventListener('click', function(){
                    const text = textEl.textContent;
                    navigator.clipboard?.writeText(text).then(()=>{
                        this.textContent = 'Copied';
                        setTimeout(()=> this.textContent = 'Copy', 1500);
                    }).catch(()=>{
                        // fallback
                        const ta = document.createElement('textarea');
                        ta.value = text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
                    });
                });
            }
        })();
        </script>
    @endif

    </div>
</x-card>
