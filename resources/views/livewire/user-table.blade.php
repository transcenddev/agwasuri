<x-card>
    {{-- Stat Cards Row --}}
    @isset($totalUsers)
        <div class="px-6 pt-6 pb-4">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                {{-- Total Users --}}
                <div class="p-4 border rounded-lg bg-gradient-to-br from-cvsu-50 to-white dark:from-cvsu-900/20 dark:to-gray-800 border-cvsu-100 dark:border-cvsu-800">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-cvsu-100 dark:bg-cvsu-800">
                            <svg class="w-6 h-6 text-cvsu-600 dark:text-cvsu-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalUsers }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Users</div>
                        </div>
                    </div>
                </div>

                {{-- Farmers --}}
                <div class="p-4 border border-green-100 rounded-lg bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-gray-800 dark:border-green-800">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg dark:bg-green-800">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $regularCount }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Farmers</div>
                        </div>
                    </div>
                </div>

                {{-- Admins --}}
                <div class="p-4 border border-purple-100 rounded-lg bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800 dark:border-purple-800">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-800">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $adminCount }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Admins</div>
                        </div>
                    </div>
                </div>

                {{-- Active Today --}}
                <div class="p-4 border border-blue-100 rounded-lg bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800 dark:border-blue-800">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-800">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">--</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Active Today</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset

    <x-slot name="header">
        <x-card-header>
            <x-slot name="title">
                <div class="flex-1">
                    <h1 class="text-lg font-semibold text-gray-900">Account Management</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage accounts, roles, and API keys</p>
                </div>
            </x-slot>

            <x-slot name="actions">
                <div class="flex flex-col w-full gap-3 md:flex-row md:items-center md:justify-end md:space-x-4">
                    {{-- Search + actions wrapper: on mobile stack vertically, on tablet wrap, on desktop align horizontally --}}
                    <div class="flex flex-col w-full gap-2 md:w-auto sm:flex-row sm:items-center sm:space-x-2 md:gap-0">
                        <div x-data="{ q: @entangle('searchTerm') }">
                            <style>
                                /* Hide native search clear to avoid double “x” while keeping custom clear button */
                                .no-native-clear::-webkit-search-cancel-button { display: none; }
                                .no-native-clear::-webkit-search-decoration { display: none; }
                                .no-native-clear::-ms-clear { display: none; width: 0; height: 0; }
                            </style>
                            <div class="relative inline-flex items-center">
                                <input
                                    x-ref="search"
                                    x-model="q"
                                    @keydown.escape="q = ''; $nextTick(() => $refs.search.focus())"
                                    type="search"
                                    wire:model.live="searchTerm"
                                    placeholder="Search name, email, pond, location"
                                    class="w-full px-3 py-2 pr-10 text-sm border rounded-md no-native-clear md:w-64 border-cvsu-200 focus:border-cvsu-600 focus:ring-cvsu-300 focus:outline-none"
                                    aria-label="Search users"
                                />

                                <!-- Clear button: appears only when there is text -->
                                <button
                                    x-show="q && q.length > 0"
                                    x-transition.opacity.duration.200
                                    type="button"
                                    @click.prevent="q = ''; $nextTick(() => $refs.search.focus())"
                                    class="absolute right-0 inline-flex items-center justify-center mr-1 transition transform rounded h-11 w-11 focus:outline-none focus:ring-2 focus:ring-cvsu-300 focus:ring-offset-1 transform-gpu"
                                    aria-label="Clear search"
                                    title="Clear search"
                                    tabindex="0"
                                >
                                    <span class="sr-only">Clear search</span>
                                    <svg class="w-5 h-5 text-gray-500 transition-colors duration-150 transform-gpu hover:scale-110 group-focus:text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col w-full gap-2 md:w-auto sm:flex-row sm:items-center sm:space-x-2 md:gap-0 md:justify-end">
                        <button
                            wire:click="toggleFilters"
                            aria-expanded="{{ $showFilters ? 'true' : 'false' }}"
                            class="relative flex items-center justify-center w-full px-3 py-2 text-sm bg-white border rounded sm:w-auto border-cvsu-200 text-cvsu-600 hover:bg-cvsu-50"
                        >
                            <svg class="w-4 h-4 mr-2 text-cvsu-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01.29.71l-6 6V20l-4-2v-7.29L3.29 6.71A1 1 0 013 6V4z" />
                            </svg>
                            <span>Filters</span>
                            <span class="ml-2 text-xs text-gray-500">{{ $showFilters ? '▲' : '▼' }}</span>
                            @php
                                $activeFilterCount = collect([
                                    $filterName, $filterEmail, $filterFishpondName, $filterAccountType,
                                    $filterBarangay, $filterMunicipality, $filterProvince, $filterWaterType, $filterSpecies
                                ])->filter()->count();
                            @endphp
                            @if($activeFilterCount > 0)
                                <span class="absolute flex items-center justify-center w-5 h-5 text-xs font-semibold text-white rounded-full -top-1 -right-1 bg-rose-500">
                                    {{ $activeFilterCount }}
                                </span>
                            @endif
                        </button>

                        <button wire:click="createUser" class="flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-white rounded sm:w-auto bg-cvsu-600 hover:bg-cvsu-700">
                            <svg class="w-4 h-4 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create User
                        </button>
                    </div>
                </div>
            </x-slot>
        </x-card-header>
    </x-slot>

            <div class="relative p-6">
                {{-- Bulk actions bar: render client-side and bind to Livewire `selected` so
                     it appears immediately when checkboxes change (no extra click needed) --}}
                <div x-data="{ selected: @entangle('selected') }" x-cloak x-show="Array.isArray(selected) ? selected.length > 0 : false" x-transition class="mb-4">
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded bg-gray-50">
                        <div class="text-sm text-gray-700" x-text="Array.isArray(selected) ? selected.length + ' selected' : '0 selected'"></div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="bulkDeleteSelected" class="inline-flex items-center px-3 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">Delete Selected</button>
                            <button type="button" @click="selected = []" class="inline-flex items-center px-3 py-2 text-sm bg-white border border-gray-200 rounded">Clear</button>
                        </div>
                    </div>
                </div>
        {{-- Session Messages (Green for success, Red for errors) --}}
        @if (session()->has('message'))
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded">
                {{ session('message') }}
            </div>
        @elseif (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded">
                {{ session('error') }}
            </div>
        @endif

        {{-- Backdrop --}}
        @if ($showFilters)
            <div wire:click="toggleFilters" class="fixed inset-0 z-40 bg-black bg-opacity-50"></div>
        @endif

        {{-- Slide-out Drawer --}}
        <div x-data="{ open: @entangle('showFilters') }"
             x-show="open"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 z-50 w-full h-full overflow-y-auto bg-white shadow-2xl sm:w-96 dark:bg-gray-800"
             style="display: none;">

            {{-- Drawer Header --}}
            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 text-white border-b bg-cvsu-600 dark:bg-cvsu-700 border-cvsu-700 dark:border-cvsu-600">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01.29.71l-6 6V20l-4-2v-7.29L3.29 6.71A1 1 0 013 6V4z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-semibold">Filter Users</h3>
                </div>
                <button wire:click="toggleFilters"
                    class="p-1 transition-colors rounded hover:bg-cvsu-700 dark:hover:bg-cvsu-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Active Filters Chips --}}
            @php
                $formatAccountType = function ($value) {
                    $n = strtolower(trim((string) $value));
                    return $n === 'admin' ? 'Admin' : ($n === 'user' ? 'User' : $value);
                };

                $activeFiltersList = [
                    ['value' => $filterName, 'label' => 'Name', 'property' => 'filterName'],
                    ['value' => $filterEmail, 'label' => 'Email', 'property' => 'filterEmail'],
                    ['value' => $filterFishpondName, 'label' => 'Fishpond', 'property' => 'filterFishpondName'],
                    ['value' => $filterAccountType ? $formatAccountType($filterAccountType) : '', 'label' => 'Account Type', 'property' => 'filterAccountType'],
                    ['value' => $filterBarangay, 'label' => 'Barangay', 'property' => 'filterBarangay'],
                    ['value' => $filterMunicipality, 'label' => 'Municipality', 'property' => 'filterMunicipality'],
                    ['value' => $filterProvince, 'label' => 'Province', 'property' => 'filterProvince'],
                    ['value' => $filterWaterType, 'label' => 'Water Type', 'property' => 'filterWaterType'],
                    ['value' => $filterSpecies, 'label' => 'Species', 'property' => 'filterSpecies'],
                ];
                $hasActiveFilters = collect($activeFiltersList)->filter(fn($f) => !empty($f['value']))->isNotEmpty();
            @endphp

            @if($hasActiveFilters)
                <div class="px-6 py-4 border-b bg-sky-50 dark:bg-gray-700 border-sky-200 dark:border-gray-600">
                    <p class="mb-2 text-xs font-medium text-sky-900 dark:text-sky-200">Active Filters:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($activeFiltersList as $filter)
                            @if(!empty($filter['value']))
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-white border rounded-full dark:bg-gray-600 border-sky-300 dark:border-gray-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $filter['label'] }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ Str::limit($filter['value'], 15) }}</span>
                                    <button wire:click="$set('{{ $filter['property'] }}', '')"
                                        class="ml-1 text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Filter Form --}}
            <div class="p-6 space-y-6">
                {{-- User Information Section --}}
                <div>
                    <h4 class="flex items-center gap-2 mb-3 text-sm font-semibold tracking-wide uppercase text-cvsu-700 dark:text-cvsu-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        User Information
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label for="filterName" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Name
                            </label>
                            <input type="text" id="filterName" wire:model.live.debounce.300ms="filterName"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter name...">
                        </div>

                        <div>
                            <label for="filterFishpondName" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Fishpond Name
                            </label>
                            <input type="text" id="filterFishpondName" wire:model.live.debounce.300ms="filterFishpondName"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter fishpond name...">
                        </div>

                        <div>
                            <label for="filterAccountType" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Account Type
                            </label>
                            <select id="filterAccountType" wire:model.live="filterAccountType"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">All Account Types</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Contact Information Section --}}
                <div>
                    <h4 class="flex items-center gap-2 mb-3 text-sm font-semibold tracking-wide uppercase text-cvsu-700 dark:text-cvsu-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Contact Information
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label for="filterEmail" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email
                            </label>
                            <input type="text" id="filterEmail" wire:model.live.debounce.300ms="filterEmail"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter email...">
                        </div>
                    </div>
                </div>

                {{-- Location Information Section --}}
                <div>
                    <h4 class="flex items-center gap-2 mb-3 text-sm font-semibold tracking-wide uppercase text-cvsu-700 dark:text-cvsu-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Location
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label for="filterBarangay" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Barangay
                            </label>
                            <input type="text" id="filterBarangay" wire:model.live.debounce.300ms="filterBarangay"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter barangay...">
                        </div>

                        <div>
                            <label for="filterMunicipality" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Municipality
                            </label>
                            <input type="text" id="filterMunicipality" wire:model.live.debounce.300ms="filterMunicipality"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter municipality...">
                        </div>

                        <div>
                            <label for="filterProvince" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Province
                            </label>
                            <input type="text" id="filterProvince" wire:model.live.debounce.300ms="filterProvince"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter province...">
                        </div>
                    </div>
                </div>

                {{-- Aquaculture Information Section --}}
                <div>
                    <h4 class="flex items-center gap-2 mb-3 text-sm font-semibold tracking-wide uppercase text-cvsu-700 dark:text-cvsu-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Aquaculture Details
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label for="filterWaterType" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Water Type
                            </label>
                            <select id="filterWaterType" wire:model.live="filterWaterType"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">All Water Types</option>
                                <option value="Freshwater">Freshwater</option>
                                <option value="Brackish">Brackish</option>
                                <option value="Saltwater">Saltwater</option>
                            </select>
                        </div>

                        <div>
                            <label for="filterSpecies" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Species
                            </label>
                            <input type="text" id="filterSpecies" wire:model.live.debounce.300ms="filterSpecies"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-cvsu-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter species...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Drawer Footer --}}
            <div class="sticky bottom-0 flex gap-3 px-6 py-4 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <button wire:click="resetFilters"
                    class="flex-1 px-4 py-2 font-medium text-gray-700 transition-colors bg-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                    Reset All
                </button>
                <button wire:click="toggleFilters"
                    class="flex-1 px-4 py-2 font-medium text-white transition-colors rounded-lg bg-cvsu-600 hover:bg-cvsu-700">
                    Done
                </button>
            </div>
        </div>

        {{-- Conditionally render the User Form Modal --}}
        @if ($showForm)
            <livewire:user-form :userId="$editingUserId" :key="'user-form-'.$editingUserId" />
        @endif

        {{-- Conditionally render the Delete Confirmation Modal --}}
        @if ($showDeleteModal)
        <div x-data="deleteModal()" @keydown.escape.window="$wire.call('cancelDelete')" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="cancelDelete"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
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
                            <!-- Bind the typed confirmation directly to Livewire to avoid Alpine state-sync bugs -->
                            <input wire:model.defer="deleteConfirmName"
                                   id="delete-confirm-input"
                                   type="text"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="Type full name to confirm" />
                        </div>

                        <div class="mt-2 text-sm text-gray-600">
                            <p>Please type the full name shown above to confirm deletion. This action cannot be undone.</p>
                        </div>

                        <!-- Debug text removed: confirmation input is sufficient -->
                    </div>
                        <div class="px-4 py-3 bg-gray-50 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button"
                                wire:click="clientDestroyUserFromUI({{ $deletingUserId }})"
                                wire:loading.attr="disabled"
                                wire:target="clientDestroyUserFromUI"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
                                aria-label="Confirm delete">
                            Delete
                        </button>
                        <button type="button" wire:click="cancelDelete" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="relative overflow-x-auto overflow-y-visible"> {{-- This div handles the horizontal scroll for the table; allow vertical overflow for dropdowns --}}
            <table class="hidden min-w-full divide-y divide-gray-200 table-fixed md:table responsive table-condensed themed-table accounts--table-desktop">
                <colgroup>
                    <col style="width:3.5rem"> <!-- select -->
                    <col style="width:6rem"> <!-- ID -->
                    <col> <!-- Full name flexible -->
                    <col style="width:20rem"> <!-- Email -->
                    <col style="width:6.5rem"> <!-- Type -->
                    <col style="width:18rem"> <!-- Location -->
                    <col style="width:6.5rem"> <!-- Actions -->
                </colgroup>
                <thead class="bg-white">
                    <tr>
                        <th scope="col" class="sticky top-0 z-10 px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-white">
                            <input type="checkbox" wire:model="selectAll" aria-label="Select all" class="w-4 h-4 border-gray-300 rounded">
                        </th>
                        <th scope="col" class="sticky top-0 z-10 px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase bg-white">ID</th>
                        <th scope="col" class="sticky top-0 z-10 px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-white">Full Name</th>
                        <th scope="col" class="sticky top-0 z-10 px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-white">Email</th>
                        <th scope="col" class="sticky top-0 z-10 px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase bg-white">Type</th>
                        <th scope="col" class="sticky top-0 z-10 px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase bg-white">Location</th>
                        <th scope="col" class="sticky top-0 right-0 z-10 px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase bg-white">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm text-left text-gray-900 whitespace-nowrap">
                                <input type="checkbox" wire:model="selected" value="{{ $user->user_id }}" class="w-4 h-4 border-gray-300 rounded">
                            </td>
                            <td data-cell="ID" class="px-6 py-2 text-sm text-right text-gray-900 whitespace-nowrap">{{ $user->user_id }}</td>
                            <td data-cell="Full Name" class="px-6 py-2 text-sm text-left text-gray-900 whitespace-nowrap">{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td data-cell="Email" class="whitespace-nowrap px-6 py-2 text-sm text-gray-900 truncate max-w-[18rem] text-left" title="{{ $user->email }}">{{ $user->email }}</td>
                            <td data-cell="Type" class="px-6 py-2 text-sm text-center text-gray-900 whitespace-nowrap">
                                @if(strtolower($user->account_type) === 'admin')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cvsu-600 text-white">Admin</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">User</span>
                                @endif
                            </td>
                            <td data-cell="Location" class="px-6 py-2 text-sm text-center text-gray-700">
                                <div class="font-medium">{{ $user->fishpond_name ?: ($user->barangay ? $user->barangay : '—') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->municipality }}{{ $user->municipality && $user->province ? ', ' : '' }}{{ $user->province }}</div>
                            </td>
                            <td data-cell="Actions" class="sticky right-0 px-6 py-2 text-sm font-medium text-center bg-white whitespace-nowrap">
                                {{-- Primary inline actions (desktop) --}}
                                <div class="items-center justify-center hidden space-x-1 desktop-actions">
                                    <button type="button" wire:click="openUserDetails({{ $user->user_id }})"
                                        title="View details"

                                        aria-label="View details for {{ $user->first_name }} {{ $user->last_name }}"
                                        class="inline-flex items-center justify-center p-2 transition-colors rounded-md text-cvsu-600 hover:text-cvsu-700 hover:bg-cvsu-50 dark:hover:bg-cvsu-900/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>

                                    <button wire:click="editUser({{ $user->user_id }})"
                                        title="Edit user"
                                        aria-label="Edit {{ $user->first_name }} {{ $user->last_name }}"
                                        class="inline-flex items-center justify-center p-2 text-gray-600 transition-colors rounded-md hover:text-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.213l-4 1 1-4L16.862 3.487z"/>
                                        </svg>
                                    </button>

                                    <button wire:click="confirmDelete({{ $user->user_id }})"
                                        title="Delete user"
                                        aria-label="Delete {{ $user->first_name }} {{ $user->last_name }}"
                                        class="inline-flex items-center justify-center p-2 text-red-600 transition-colors rounded-md hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Hidden per-row Livewire hooks used as a robust fallback when global Livewire.emit is not available --}}
                                <button id="user-action-edit-{{ $user->user_id }}" wire:click="openUserDetails({{ $user->user_id }})" type="button" class="hidden" aria-hidden="true"></button>
                                <button id="user-action-edit-user-{{ $user->user_id }}" wire:click="editUser({{ $user->user_id }})" type="button" class="hidden" aria-hidden="true"></button>
                                <button id="user-action-delete-{{ $user->user_id }}" wire:click="confirmDelete({{ $user->user_id }})" type="button" class="hidden" aria-hidden="true"></button>

                                {{-- Kebab menu removed for mobile — use inline icons instead --}}
                                {{-- Previously used on small screens as a fallback; hidden to prefer explicit actions on mobile cards --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-2 text-sm text-center text-gray-500 whitespace-nowrap">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- Mobile: Card-list view for small screens (visible until md breakpoint) --}}
            <div class="mt-2 table-mobile md:hidden accounts--cards-desktop">
                <div class="space-y-3">
                    @forelse ($users as $user)
                        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $user->first_name }} {{ $user->last_name }}</div>
                                        @if(strtolower($user->account_type) === 'admin')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cvsu-600 text-white">Admin</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">User</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 truncate">{{ $user->email }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $user->fishpond_name ?: ($user->barangay ? $user->barangay : '—') }}
                                        @if($user->municipality), {{ $user->municipality }}@endif
                                        @if($user->province), {{ $user->province }}@endif
                                    </div>
                                </div>

                                <div class="ml-4">
                                    <!-- Mobile: single kebab menu that opens the shared global action menu -->
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center p-2 text-gray-600 rounded-full hover:bg-gray-100"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                        title="More actions"
                                        onclick="(function(e){
                                            try {
                                                const btn = (e.currentTarget || e.target);
                                                const rect = btn.getBoundingClientRect();
                                                const left = rect.left + (window.scrollX || 0);
                                                const top = rect.bottom + (window.scrollY || 0);
                                                window.dispatchEvent(new CustomEvent('open-user-action-menu',{detail:{userId: {{ $user->user_id }}, left: left, top: top, coordsArePage: true}}));
                                            } catch(err) {
                                                // fallback to page/client coords
                                                window.dispatchEvent(new CustomEvent('open-user-action-menu',{detail:{userId: {{ $user->user_id }}, x: (e.pageX || e.clientX || 0), y: (e.pageY || e.clientY || 0)}}));
                                            }
                                        })(event)"
                                    >
                                        <span class="sr-only">Open actions</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                </div>
                                {{-- Hidden hooks for mobile actions fallback (same ids as desktop fallback) --}}
                                <button id="user-action-edit-{{ $user->user_id }}" wire:click="openUserDetails({{ $user->user_id }})" type="button" class="hidden" aria-hidden="true"></button>
                                <button id="user-action-edit-user-{{ $user->user_id }}" wire:click="editUser({{ $user->user_id }})" type="button" class="hidden" aria-hidden="true"></button>
                                <button id="user-action-delete-{{ $user->user_id }}" wire:click="confirmDelete({{ $user->user_id }})" type="button" class="hidden" aria-hidden="true"></button>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-center text-gray-500">No users found.</div>
                    @endforelse
                </div>
            </div>
            {{-- Pagination links are moved OUTSIDE this div --}}
        </div> {{-- End of overflow-x-auto div --}}

        {{-- Pagination links - Placed after the scrolling table container --}}
        <div class="mt-4">
            {{ $users->links() }}
        </div>

        </div>

        {{-- Global action menu: rendered as a portal appended to `document.body` to avoid
             containment/transform issues and ensure fixed positioning. The portal is
             created on-demand when a row kebab is clicked and removed on click-away. --}}

        <script>
            (function(){
                let portalEl = null;
                let escHandler = null;
                let clickAwayHandler = null;
                let repositionHandler = null;

                function removePortal() {
                    if (!portalEl) return;
                    if (clickAwayHandler) document.removeEventListener('click', clickAwayHandler, true);
                    if (escHandler) document.removeEventListener('keydown', escHandler, true);
                    if (repositionHandler) {
                        window.removeEventListener('resize', repositionHandler);
                        window.removeEventListener('scroll', repositionHandler, true);
                        repositionHandler = null;
                    }
                    portalEl.remove();
                    portalEl = null;
                    clickAwayHandler = null;
                    escHandler = null;
                }

                function createPortalMenu({ left = 0, top = 0, userId = null, triggerRect = null, coordsArePage = false }) {
                    // Only show portal on small screens (mobile/tablet). On desktop we prefer inline actions.
                    if (window.innerWidth >= 1024) return;

                    removePortal();
                    portalEl = document.createElement('div');
                    portalEl.className = 'global-action-menu rounded-md bg-white border border-gray-200 shadow-lg';
                    portalEl.setAttribute('role', 'menu');
                    portalEl.style.position = 'fixed';
                    portalEl.style.zIndex = 9999;

                    // Compute position: prefer placing menu below the trigger; if not enough space, place above.
                    const menuWidth = 180;
                    const menuHeight = 96; // approximate; two items
                    const margin = 8;

                    let pageLeft = left;
                    let pageTop = top;
                    if (triggerRect && !coordsArePage) {
                        pageLeft = triggerRect.left + (window.scrollX || 0);
                        pageTop = triggerRect.bottom + (window.scrollY || 0);
                    }

                    // clamp horizontally
                    const maxLeft = window.scrollX + window.innerWidth - menuWidth - margin;
                    const clampedLeft = Math.min(Math.max(pageLeft, window.scrollX + margin), maxLeft);

                    // vertical placement: try below, else above
                    let placedTop = pageTop;
                    const viewportBottom = window.scrollY + window.innerHeight;
                    if ((pageTop + menuHeight) > viewportBottom - margin) {
                        // place above trigger if available
                        if (triggerRect) {
                            placedTop = (triggerRect.top + window.scrollY) - menuHeight - 6;
                        } else {
                            placedTop = Math.max(window.scrollY + margin, pageTop - menuHeight - 6);
                        }
                    }

                    portalEl.style.left = clampedLeft + 'px';
                    portalEl.style.top = (placedTop) + 'px';
                    portalEl.innerHTML = `
                        <div class="py-1">
                            <a href="#" data-action="edit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                            <a href="#" data-action="delete" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Delete</a>
                        </div>
                    `;

                    // Wire up actions
                    portalEl.querySelectorAll('[data-action]').forEach(el => {
                        el.addEventListener('click', (ev) => {
                            ev.preventDefault();
                            const action = el.getAttribute('data-action');
                            window.dispatchEvent(new CustomEvent('menuActionFromUI', { detail: { action: action, userId: userId } }));
                            removePortal();
                        });
                    });

                    document.body.appendChild(portalEl);

                    // Close on click-away
                    clickAwayHandler = function(e) {
                        if (!portalEl) return;
                        if (!portalEl.contains(e.target)) removePortal();
                    };
                    document.addEventListener('click', clickAwayHandler, true);

                    escHandler = function(e) {
                        if (e.key === 'Escape' || e.key === 'Esc') removePortal();
                    };
                    document.addEventListener('keydown', escHandler, true);

                    // Reposition when viewport changes
                    repositionHandler = function() {
                        try {
                            // If the portal was opened via a triggerRect stored on the element, attempt to reposition relative to that trigger
                            if (!portalEl) return;
                            // We conservatively do nothing complex here; closing is safer on big layout changes
                            removePortal();
                        } catch (err) {
                            console.warn('repositionHandler error', err);
                        }
                    };
                    window.addEventListener('resize', repositionHandler);
                    window.addEventListener('scroll', repositionHandler, true);
                }

                // Listen for open requests (dispatched by kebab buttons)
                window.addEventListener('open-user-action-menu', function(e){
                    const detail = e.detail || {};
                    // If the event supplied page coords (left/top already adjusted), use them directly.
                    const coordsArePage = Boolean(detail.coordsArePage);
                    const rawLeft = (detail.left !== undefined) ? detail.left : (detail.x !== undefined ? detail.x + (window.scrollX || 0) : 0);
                    const rawTop = (detail.top !== undefined) ? detail.top : (detail.y !== undefined ? detail.y + (window.scrollY || 0) : 0);

                    // If caller provided a rect-like object, pass it through so the menu can anchor above/below precisely
                    const triggerRect = detail.triggerRect || null;

                    createPortalMenu({ left: rawLeft, top: rawTop, userId: detail.userId || null, triggerRect: triggerRect, coordsArePage: coordsArePage });
                });
            })();
        </script>

        <script>
            // Handle menu actions dispatched from the portal menu. Some mobile
            // browsers or environments may not have Livewire global helpers wired
            // exactly the same as desktop, so we trigger the hidden per-row
            // buttons which are bound to Livewire actions. This keeps behavior
            // identical across desktop and mobile.
            (function(){
                if (window.__agwasuri_menu_action_handler_installed) return;
                window.__agwasuri_menu_action_handler_installed = true;

                window.addEventListener('menuActionFromUI', function(e){
                    try {
                        const d = e && e.detail ? e.detail : {};
                        const action = (d.action || '').toString();
                        const userId = d.userId || d.user_id || null;
                        if (!userId) return;

                        // Map the action to the hidden button id we defined in the
                        // template: user-action-edit-{id}, user-action-edit-user-{id}, user-action-delete-{id}
                        let btnId = null;
                        if (action === 'edit') btnId = 'user-action-edit-user-' + userId;
                        else if (action === 'delete') btnId = 'user-action-delete-' + userId;
                        else if (action === 'details' || action === 'view') btnId = 'user-action-edit-' + userId;

                        if (btnId) {
                            const btn = document.getElementById(btnId);
                            if (btn) {
                                try { btn.click(); return; } catch (err) { /* fallthrough */ }
                            }

                            // Fallback: try Livewire.find + call appropriate method
                            if (window.Livewire && typeof Livewire.find === 'function') {
                                try {
                                    // find nearest component root and call
                                    const root = document.querySelector('[wire\\:id]');
                                    if (root) {
                                        const lwId = root.getAttribute('wire:id');
                                        const comp = Livewire.find(lwId);
                                        if (comp) {
                                            if (action === 'edit') comp.call('editUser', userId);
                                            else if (action === 'delete') comp.call('confirmDelete', userId);
                                            else if (action === 'details') comp.call('showDetails', userId);
                                        }
                                    }
                                } catch (err) {
                                    console.warn('menuActionFromUI: Livewire fallback failed', err);
                                }
                            }
                        }
                    } catch (e) {
                        console.warn('menuActionFromUI handler error', e);
                    }
                });
            })();
        </script>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('deleteModal', () => ({
                    // local typed value; we'll send it to Livewire via $wire.call before destroy
                    typed: {!! json_encode($deleteConfirmName ?? '') !!},
                    deletingNameRaw: {!! json_encode($deletingUserName ?? '') !!},
                    deletingUserId: {!! json_encode($deletingUserId ?? null) !!},
                    isSelf: {!! json_encode(auth()->check() && optional(auth()->user())->user_id === $deletingUserId) !!},
                    deleting: false,
                    normalize(s) {
                        if (!s) return '';
                        return String(s).trim().replace(/[\s\u00A0]+/g, ' ').toLowerCase();
                    },
                    get canDelete() {
                        // Read the typed value from the input (prefer live ref) and the expected
                        // name from the DOM so we don't rely on Alpine's initial snapshot
                        const val = (this.$refs && this.$refs.confirmInput) ? this.$refs.confirmInput.value : this.typed;
                        // Prefer the server-rendered <strong> element content if present
                        let expected = this.deletingNameRaw || '';
                        try {
                            const strong = this.$el ? this.$el.querySelector('p strong') : null;
                            if (strong && strong.textContent) {
                                expected = String(strong.textContent).trim();
                            }
                        } catch (e) {
                            // ignore DOM errors and fall back to Alpine state
                        }
                        return this.normalize(val) === this.normalize(expected);
                    },
                    init() {
                        this.$nextTick(() => {
                            if (this.$refs.confirmInput) {
                                try { this.$refs.confirmInput.focus(); } catch (e) {}
                            }
                        });
                    },
                    // Centralized method called by the Delete button. Reads the typed value,
                    // sends it to Livewire explicitly via `clientDestroyUser`, and handles
                    // a small local loading state to avoid duplicate clicks.
                    async confirmAndDelete() {
                        if (this.deleting) return;
                        if (!this.canDelete) return;
                        if (this.isSelf) return;
                        const v = (this.$refs && this.$refs.confirmInput) ? this.$refs.confirmInput.value : this.typed;
                        this.deleting = true;
                        console.debug('confirmAndDelete: begin; typed=', v);
                        try {
                            // First, set the server-side property to be extra-safe
                            try {
                                $wire.set('deleteConfirmName', String(v));
                                console.debug('confirmAndDelete: $wire.set(deleteConfirmName) succeeded');
                            } catch (setErr) {
                                console.warn('confirmAndDelete: $wire.set failed, will continue to call method', setErr);
                            }

                            // Preferred explicit method call that passes the userId and typed value as arguments
                            try {
                                const idToDelete = this.deletingUserId;
                                await $wire.call('clientDestroyUser', idToDelete, String(v));
                                console.debug('confirmAndDelete: $wire.call(clientDestroyUser) completed');
                                return;
                            } catch (callErr) {
                                console.warn('confirmAndDelete: $wire.call failed, attempting Livewire.find fallback', callErr);
                            }

                            // Fallback: locate nearest Livewire component instance and call directly
                            try {
                                const root = (this.$el && this.$el.closest) ? this.$el.closest('[wire\:id]') : document.querySelector('[wire\:id]');
                                if (window.Livewire && root) {
                                    const id = root.getAttribute('wire:id');
                                    console.debug('confirmAndDelete: Livewire.find id=', id);
                                    const comp = Livewire.find(id);
                                    if (comp) {
                                        comp.set('deleteConfirmName', String(v));
                                        comp.call('clientDestroyUser', String(v));
                                        console.debug('confirmAndDelete: Livewire.find fallback invoked');
                                    } else {
                                        console.error('confirmAndDelete: Livewire.find returned no component for id', id);
                                    }
                                } else {
                                    console.error('confirmAndDelete: Livewire global or root element not found');
                                }
                            } catch (fbErr) {
                                console.error('confirmAndDelete: Livewire.find fallback failed', fbErr);
                            }
                        } finally {
                            this.deleting = false;
                        }
                    }
                }));
            });
        </script>

        {{-- Details Modal --}}
    @if($selectedUser)
        @php
            $modalId = 'user-details-' . ($selectedUser->user_id ?? '');
            $locationParts = array_filter([
                $selectedUser->barangay,
                $selectedUser->municipality,
                $selectedUser->province,
            ], fn($v) => filled($v));
            $locationText = $locationParts ? implode(', ', $locationParts) : 'Not set';
            $speciesValue = is_array($selectedUser->species_cultured)
                ? implode(', ', array_filter($selectedUser->species_cultured))
                : (filled($selectedUser->species_cultured) ? $selectedUser->species_cultured : 'Not set');
            $accountTypeLabel = function ($value) {
                $n = strtolower(trim((string) $value));
                if ($n === 'admin') {
                    return 'Admin';
                }
                if ($n === 'user') {
                    return 'User';
                }
                return $value ?: 'Not set';
            };
        @endphp
        <div id="{{ $modalId }}" x-data @keydown.escape.window="$wire.call('closeDetails')" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="closeDetails"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">User Details</h3>
                                <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2">
                                    <div>
                                        <div class="text-xs text-gray-500">ID</div>
                                        <div class="font-medium">{{ $selectedUser->user_id ?? 'Not set' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Full Name</div>
                                        <div class="font-medium">{{ trim(($selectedUser->first_name ?? '') . ' ' . ($selectedUser->last_name ?? '')) ?: 'Not set' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Email</div>
                                        <div class="font-medium">{{ $selectedUser->email ?? 'Not set' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Account Type</div>
                                        <div class="font-medium">{{ $accountTypeLabel($selectedUser->account_type ?? null) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Fishpond Name</div>
                                        <div class="font-medium">{{ $selectedUser->fishpond_name ?? 'Not set' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Location</div>
                                        <div class="font-medium">{{ $locationText }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Fishpond Area</div>
                                        <div class="font-medium">{{ isset($selectedUser->total_fishpond_area) ? $selectedUser->total_fishpond_area : 'Not set' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Species Cultured</div>
                                        <div class="font-medium">{{ $speciesValue }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Water Type</div>
                                        @php
                                            $waterMap = function ($value) {
                                                if (!$value) return 'Not set';
                                                $n = strtolower(trim($value));
                                                if (str_starts_with($n, 'fresh')) return 'Freshwater';
                                                if (str_starts_with($n, 'brack')) return 'Brackish';
                                                if (str_starts_with($n, 'salt')) return 'Saltwater';
                                                return $value;
                                            };
                                        @endphp
                                        <div class="font-medium">{{ $waterMap($selectedUser->water_type ?? null) }}</div>
                                    </div>
                                     <div class="sm:col-span-2">
                                         <div class="text-xs text-gray-500">API Key</div>
                                         <div
                                            class="flex items-center space-x-2"
                                            x-data="{
                                                key: {{ json_encode($selectedUser->api_key ?? '') }},
                                                masked: true,
                                                get displayKey(){ return this.key ? (this.masked ? '********' : this.key) : 'Not set'; },
                                                toggle(){ if(!this.key) return; this.masked = !this.masked; },
                                                async copy(){
                                                    if(!this.key) return;
                                                    const val = this.key;
                                                    try { await navigator.clipboard.writeText(val); this.copied = true; setTimeout(()=> this.copied=false, 1500); }
                                                    catch(e){
                                                        const ta = document.createElement('textarea');
                                                        ta.value = val; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
                                                        this.copied = true; setTimeout(()=> this.copied=false, 1500);
                                                    }
                                                },
                                                copied:false,
                                            }"
                                         >
                                            <div class="font-mono text-sm" x-text="displayKey"></div>
                                            @if($selectedUser->api_key)
                                                <button type="button" class="text-sm text-cvsu-600 hover:underline" @click="toggle" x-text="masked ? 'Reveal' : 'Hide'"></button>
                                                <button type="button" class="text-sm text-gray-700" @click="copy" x-text="copied ? 'Copied' : 'Copy'"></button>
                                            @endif
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeDetails" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Close</button>
                    </div>
                </div>
            </div>
        </div>

    @endif


    </div>
</x-card>
