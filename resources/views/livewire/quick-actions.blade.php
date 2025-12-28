<div x-data="{ showConfirm:false, compact: false }" @toggle-compact-view.window="compact = !compact" x-init="$wire.load()" class="flex flex-col" x-bind:class="compact ? 'gap-1.5' : 'gap-2'">
    @if(empty($ready))
        <div class="space-y-2">
            <div class="h-9 bg-gray-100 rounded animate-pulse"></div>
            <div class="h-9 bg-gray-100 rounded animate-pulse"></div>
            <div class="h-9 bg-gray-100 rounded animate-pulse"></div>
        </div>
    @else
        {{-- Export CSV --}}
        <button wire:click="exportCsv" class="group w-full inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white text-sm font-medium hover:bg-gray-50 hover:border-gray-300 transition-all duration-200" x-bind:class="compact ? 'justify-center px-2 py-1.5' : 'justify-start px-3 py-2'">
            <svg class="flex-shrink-0 transition-all duration-200" x-bind:class="compact ? 'w-4 h-4' : 'w-5 h-5'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span x-show="!compact" class="text-gray-700 group-hover:text-gray-900">Export CSV</span>
        </button>

        {{-- Refresh Data --}}
        <button wire:click="refreshData" class="group w-full inline-flex items-center gap-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 shadow-sm hover:shadow transition-all duration-200" x-bind:class="compact ? 'justify-center px-2 py-1.5' : 'justify-start px-3 py-2'">
            <svg class="flex-shrink-0 transition-all duration-200" x-bind:class="compact ? 'w-4 h-4' : 'w-5 h-5'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span x-show="!compact">Refresh Data</span>
        </button>

        {{-- Train Model --}}
        <button @click.prevent="showConfirm = true" class="group w-full inline-flex items-center gap-2 rounded-lg border-2 border-green-600 text-green-700 text-sm font-medium hover:bg-green-50 transition-all duration-200" x-bind:class="compact ? 'justify-center px-2 py-1.5' : 'justify-start px-3 py-2'">
            <svg class="flex-shrink-0 transition-all duration-200" x-bind:class="compact ? 'w-4 h-4' : 'w-5 h-5'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <span x-show="!compact">Train Model</span>
        </button>

        {{-- Confirmation Modal --}}
        <template x-if="showConfirm">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4" @click.self="showConfirm=false">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showConfirm=false"></div>
                <div class="relative bg-white rounded-lg shadow-xl p-6 z-50 w-full max-w-sm transform transition-all">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900">Train ML Model?</h3>
                            <p class="mt-2 text-sm text-gray-600">Training will process recent water quality data and update fish recommendations. This may take a few minutes.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3 justify-end">
                        <button @click.prevent="showConfirm=false" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button @click.prevent="$wire.trainModelConfirmed(); showConfirm = false" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 shadow-sm transition-colors">
                            Start Training
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Helper text --}}
        <div class="text-center transition-all duration-300" x-bind:class="compact ? 'mt-1' : 'mt-2'" x-show="!compact" x-transition>
            <p class="text-xs text-gray-500">Actions apply to selected fishpond</p>
        </div>
    @endif
</div>
