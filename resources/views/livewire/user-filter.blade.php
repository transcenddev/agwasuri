{{-- Main container --}}
<div>
    {{-- Wrapper: make full-width by default so embedding in topbar fills available space --}}
    <div class="w-full">
        <div class="grid grid-cols-1 gap-x-4 gap-y-2 mb-1 md:grid-cols-2">

            {{-- Omnisearch (user or pond) --}}
            <div class="relative col-span-2" x-data @click.outside="$wire.showOmniResults = false">
                  <div class="relative">
                      <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                          </svg>
                      </div>
                      <input type="text" id="omniSearch"
                          wire:model.live.debounce.300ms="omni"
                          class="block w-full h-12 pl-12 pr-4 mt-1 text-left text-gray-900 bg-white border border-gray-300 rounded-2xl shadow-md focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                          placeholder="Search user or fishpond name..."
                          autocomplete="off">
                  </div>

                <div x-show="$wire.showOmniResults" x-transition style="display: none;">
                    @if($omniResults->isNotEmpty())
                        <ul class="absolute z-50 w-full mt-1 overflow-auto bg-white rounded-md shadow-lg max-h-60">
                            @foreach($omniResults as $r)
                                <li class="px-4 py-2 cursor-pointer hover:bg-indigo-100 flex items-center justify-between"
                                    wire:click="selectUser({{ $r['id'] }})">
                                    <div>
                                        <span class="font-medium">{{ $r['label'] }}</span>
                                        <span class="ml-2 text-xs text-gray-500">{{ ucfirst($r['type']) }}{{ $r['meta'] ? ' · ' . $r['meta'] : '' }}</span>
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $r['type'] === 'user' ? 'User' : 'Pond' }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @elseif(!empty($omni))
                        <div class="absolute z-50 w-full p-3 mt-1 text-sm text-center text-gray-500 bg-white rounded-md shadow-lg">
                            No results found.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Full Name Input and Dropdown Container (hidden while omnisearch is tested) --}}
            <div class="relative hidden" x-data @click.outside="$wire.showFullNameResults = false">
                {{-- Remove the label --}}
                {{-- <label for="fullName" class="block text-sm font-medium text-gray-700">Search by Full Name</label> --}}
                <input type="text" id="fullName"
                       wire:model.live.debounce.300ms="fullName"
                       {{-- Apply base and conditional placeholder classes --}}
                       class="block w-full h-12 px-4 mt-1 text-left text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm
                              {{-- Default placeholder style --}}
                              placeholder:text-gray-400 placeholder:font-normal
                              {{-- Apply different style if selected user placeholder is active --}}
                              @if($selectedUserNameForPlaceholder) placeholder:text-gray-800 placeholder:font-semibold @endif"
                       placeholder="{{ $selectedUserNameForPlaceholder ?? 'Name' }}"
                       autocomplete="off">

                {{-- Full Name Search Results Dropdown --}}
                {{-- Use x-show controlled by Livewire property --}}
                <div x-show="$wire.showFullNameResults" x-transition style="display: none;"> {{-- Wrap both ul and div in a single x-show --}}
                    @if($fullNameResults->isNotEmpty())
                        <ul class="absolute z-50 w-full mt-1 overflow-auto bg-white rounded-md shadow-lg max-h-60" >
                            @foreach($fullNameResults as $user)
                                <li class="px-4 py-2 cursor-pointer hover:bg-indigo-100"
                                    wire:click="selectUser({{ $user->getKey() }})">
                                    <span class="font-medium">{{ $user->first_name }} {{ $user->last_name }}</span>
                                    @if($user->fishpond_name)
                                        <span class="ml-2 text-sm text-gray-500">({{ $user->fishpond_name }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @elseif(!empty($fullName)) {{-- Show "No results" only if input is not empty --}}
                         <div class="absolute z-50 w-full p-3 mt-1 text-sm text-center text-gray-500 bg-white rounded-md shadow-lg">
                            No names found.
                         </div>
                    @endif
                </div>
            </div>

            {{-- Fishpond Name Input and Dropdown Container (hidden while omnisearch is tested) --}}
            <div class="relative hidden" x-data @click.outside="$wire.showFishpondNameResults = false">
                {{-- Removed the icon wrapper div --}}
                <input type="text" id="fishpondName"
                       wire:model.live.debounce.300ms="fishpondName"
                        {{-- Apply base and conditional placeholder classes --}}
                       class="block w-full h-12 px-4 mt-1 text-left text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm
                              {{-- Default placeholder style --}}
                              placeholder:text-gray-400 placeholder:font-normal
                              {{-- Apply different style if selected pond placeholder is active --}}
                              @if($selectedPondNameForPlaceholder) placeholder:text-gray-800 placeholder:font-semibold @endif"
                       placeholder="{{ $selectedPondNameForPlaceholder ?? 'Pond Name' }}"
                       autocomplete="off">

                {{-- Fishpond Name Search Results Dropdown --}}
                {{-- Use x-show controlled by Livewire property --}}
                <div x-show="$wire.showFishpondNameResults" x-transition style="display: none;"> {{-- Wrap both ul and div in a single x-show --}}
                    @if($fishpondNameResults->isNotEmpty())
                        <ul class="absolute z-50 w-full mt-1 overflow-auto bg-white rounded-md shadow-lg max-h-60">
                            @foreach($fishpondNameResults as $user)
                                 <li class="px-4 py-2 cursor-pointer hover:bg-indigo-100"
                                     wire:click="selectUser({{ $user->getKey() }})">
                                    <span class="font-medium">{{ $user->fishpond_name }}</span>
                                    <span class="ml-2 text-sm text-gray-500">({{ $user->first_name }} {{ $user->last_name }})</span>
                                 </li>
                            @endforeach
                        </ul>
                    @elseif(!empty($fishpondName)) {{-- Show "No results" only if input is not empty --}}
                         <div class="absolute z-50 w-full p-3 mt-1 text-sm text-center text-gray-500 bg-white rounded-md shadow-lg">
                             No fishponds found.
                         </div>
                    @endif
                </div>
            </div>
        </div>
    </div> {{-- End of md:w-1/2 wrapper --}}
</div>

{{-- Script: listen for URL update events and update query param without navigation --}}
<script>
    window.addEventListener('agwa:update-user-query', function (ev) {
        try {
            const user = ev.detail?.user;
            const url = new URL(window.location.href);
            if (user) {
                url.searchParams.set('user', user);
            } else {
                url.searchParams.delete('user');
            }
            history.replaceState({}, '', url.toString());
        } catch (e) {
            console.warn('Failed to update user query param', e);
        }
    });

    // Bridge Livewire dispatch back to a DOM CustomEvent so global listeners can react
    window.addEventListener('agwa:user-selected-browser', function (ev) {
        try {
            const userId = ev.detail?.userId;
            if (!userId) return;
            window.dispatchEvent(new CustomEvent('user-selected', { detail: { userId } }));
        } catch (e) {
            console.warn('Failed to fan-out user-selected', e);
        }
    });

    // Listen for client-side navigate fallback and perform a hard navigation
    window.addEventListener('agwa:client-navigate', function (ev) {
        try {
            const url = ev.detail?.url;
            if (!url) return;
            window.location.href = url;
        } catch (e) {
            console.warn('client navigate failed', e);
        }
    });
</script>
