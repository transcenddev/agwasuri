<div>
    {{-- Check if admin and no user is selected yet --}}
    @if($isAdminView && $currentUserId === null)
         <div class="flex items-center justify-center w-full h-12"> {{-- Match height of potential inputs --}}
            <p class="text-sm italic text-center text-gray-500">
                Please select a user above to enable date filtering.
            </p>
         </div>
    {{-- Otherwise (either not admin, or admin with a user selected), show the filter UI --}}
    @else
        {{-- Main container for the filter group --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-x-6 gap-y-3">

            {{-- Conditionally show inputs OR "No Data" message *for the current user context* --}}
            @if($minAllowedDate !== null)
                {{-- Start Date Group --}}
                <div class="flex items-center gap-2">
                    <label for="startDate" class="text-sm font-medium text-gray-700 whitespace-nowrap">Start Date</label>
                    <input type="date" id="startDate"
                           wire:model.defer="startDate"
                           class="block w-full h-12 px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           min="{{ $minAllowedDate }}"
                           max="{{ $endDate ?? $maxAllowedDate }}"
                           @if($currentUserId === null) disabled @endif
                           >
                </div>

                {{-- End Date Group --}}
                <div class="flex items-center gap-2">
                    <label for="endDate" class="text-sm font-medium text-gray-700 whitespace-nowrap">End Date</label>
                    <input type="date" id="endDate"
                           wire:model.defer="endDate"
                           class="block w-full h-12 px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           min="{{ $startDate ?? $minAllowedDate }}"
                           max="{{ $maxAllowedDate }}"
                           @if($currentUserId === null) disabled @endif
                           >
                </div>

                {{-- Action buttons (Apply / Clear) --}}
                <div class="flex items-center gap-2">
                    <!-- <button type="button"
                            wire:click="applyFilters"
                            @if($currentUserId === null) disabled @endif
                            class="inline-flex items-center px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 disabled:opacity-50">
                        Apply
                    </button> -->

                    <button type="button"
                            wire:click="applyFilters"
                            @if($currentUserId === null) disabled @endif
                            class="inline-flex items-center px-4 py-2 text-white bg-cvsu rounded-xl hover:bg-cvsu-700 disabled:opacity-50 transition">
                        Apply
                    </button>

                    <button type="button"
                            wire:click="clearFilters"
                            class="inline-flex items-center px-3 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                        Clear
                    </button>
                </div>
            @else
                 {{-- Show message if no data for the current user context (selected user, or non-admin self) --}}
                 <div class="flex items-center justify-center w-full h-12"> {{-- Match height of inputs --}}
                    <p class="text-sm italic text-center text-gray-500">
                        No historical data found {{ $isAdminView ? 'for the selected user' : '' }} to filter by date.
                    </p>
                 </div>
            @endif
            {{-- Clear button omitted --}}
        </div>
    @endif
</div>
