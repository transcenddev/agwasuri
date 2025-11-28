{{-- Wrap in a modal or similar structure as needed --}}
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancel"></div>

        {{-- Modal panel --}}
        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
            <form wire:submit.prevent="save">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        {{ $isEditing ? 'Edit User' : 'Create User' }}
                    </h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {{-- First Name --}}
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" wire:model.defer="first_name" id="first_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('first_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                         {{-- Last Name --}}
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" wire:model.defer="last_name" id="last_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('last_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model.defer="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password {{ $isEditing ? '(Leave blank to keep current)' : '' }}</label>
                            <input type="password" wire:model.defer="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password Confirmation --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" wire:model.defer="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                         {{-- Account Type --}}
                         <div>
                             <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
                             <select wire:model.defer="account_type" id="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 <option value="user">User</option>
                                 <option value="admin">Admin</option>
                             </select>
                             @error('account_type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                         </div>

                         {{-- Fishpond Name --}}
                        <div>
                            <label for="fishpond_name" class="block text-sm font-medium text-gray-700">Fishpond Name</label>
                            <input type="text" wire:model.defer="fishpond_name" id="fishpond_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('fishpond_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Barangay --}}
                        <div>
                            <label for="barangay" class="block text-sm font-medium text-gray-700">Barangay</label>
                            <input type="text" wire:model.defer="barangay" id="barangay" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('barangay') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Municipality --}}
                        <div>
                            <label for="municipality" class="block text-sm font-medium text-gray-700">Municipality</label>
                            <input type="text" wire:model.defer="municipality" id="municipality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('municipality') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Province --}}
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Province</label>
                            <input type="text" wire:model.defer="province" id="province" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('province') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Total Fishpond Area --}}
                        <div>
                            <label for="total_fishpond_area" class="block text-sm font-medium text-gray-700">Total Fishpond Area (sqm)</label>
                            <input type="number" step="any" wire:model.defer="total_fishpond_area" id="total_fishpond_area" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('total_fishpond_area') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                         {{-- Species Cultured --}}
                        <div>
                            <label for="species_cultured_string" class="block text-sm font-medium text-gray-700">Species Cultured (comma-separated)</label>
                            <input type="text" wire:model.defer="species_cultured_string" id="species_cultured_string" placeholder="e.g., Tilapia, Bangus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('species_cultured_string') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                         {{-- Water Type --}}
                        <div>
                             <label for="water_type" class="block text-sm font-medium text-gray-700">Water Type</label>
                             <select wire:model.defer="water_type" id="water_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 <option value="Freshwater">Freshwater</option>
                                 <option value="Brackishwater">Brackishwater</option>
                                 <option value="Saltwater">Saltwater</option>
                             </select>
                             @error('water_type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                         </div>

                        {{-- API Key --}}
                        <div class="sm:col-span-2">
                            <label for="api_key" class="block text-sm font-medium text-gray-700">API Key</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" wire:model.defer="api_key" id="api_key" class="block w-full min-w-0 flex-1 rounded-none rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <button type="button" wire:click="generateApiKey" class="relative -ml-px inline-flex items-center space-x-2 rounded-r-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    Generate
                                </button>
                            </div>
                            @error('api_key') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-cvsu-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-cvsu-700 focus:outline-none focus:ring-2 focus:ring-cvsu-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" wire:click="cancel" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
