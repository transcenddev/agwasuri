<div>
    {{-- If there's no effective user selected (admin hasn't selected one), show hint --}}
    @if(! $currentUserId)
         <div class="flex items-center justify-center w-full h-12"> {{-- Match height of potential inputs --}}
            <p class="text-sm italic text-center text-gray-500">
                Please select a user above to enable date filtering.
            </p>
         </div>
    {{-- Otherwise show the filter UI --}}
    @else
        {{-- Main container for the filter group --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-end">

            {{-- Conditionally show inputs OR "No Data" message *for the current user context* --}}
            @if($minAllowedDate !== null)
                <div class="flex flex-col w-full gap-3 md:flex-1 md:min-w-[220px]">
                    {{-- Start Date/Time --}}
                    <div class="flex flex-col gap-2">
                        <label for="startDate" class="text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="startDate"
                               wire:model.defer="startDate"
                               class="block w-full h-12 px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               min="{{ $minAllowedDate }}"
                               max="{{ $endDate ?? $maxAllowedDate }}"
                               @if($currentUserId === null) disabled @endif
                        >
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="startTime" class="text-sm font-medium text-gray-700">Start Time</label>
                        <input type="time" id="startTime"
                               wire:model.defer="startTime"
                               class="block w-full h-12 px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               @if($currentUserId === null) disabled @endif
                        >
                    </div>
                </div>

                <div class="flex flex-col w-full gap-3 md:flex-1 md:min-w-[220px]">
                    {{-- End Date/Time --}}
                    <div class="flex flex-col gap-2">
                        <label for="endDate" class="text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="endDate"
                               wire:model.defer="endDate"
                               class="block w-full h-12 px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               min="{{ $startDate ?? $minAllowedDate }}"
                               max="{{ $maxAllowedDate }}"
                               @if($currentUserId === null) disabled @endif
                        >
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="endTime" class="text-sm font-medium text-gray-700">End Time</label>
                        <input type="time" id="endTime"
                               wire:model.defer="endTime"
                               class="block w-full h-12 px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               @if($currentUserId === null) disabled @endif
                        >
                    </div>
                </div>

                {{-- Action buttons (Apply / Clear) --}}
                <div class="flex flex-row items-center w-full gap-2 md:w-auto md:ml-auto">
                        <button type="button"
                            wire:click="applyFilters"
                            @if($currentUserId === null) disabled @endif
                            class="inline-flex items-center justify-center w-full px-4 py-2 text-white transition md:w-auto bg-cvsu rounded-xl hover:bg-cvsu-700 disabled:opacity-50">
                        Apply
                    </button>

                    <button type="button"
                            wire:click="clearFilters"
                            class="inline-flex items-center justify-center w-full px-3 py-2 text-gray-700 transition bg-gray-100 border border-gray-200 md:w-auto rounded-xl hover:bg-gray-200 dark:bg-transparent dark:text-white dark:border-white/20 hover:dark:bg-white/5">
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
