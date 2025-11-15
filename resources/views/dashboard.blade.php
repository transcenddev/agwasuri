<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-2">
                <livewire:data-source-toggle />
                <livewire:demo-data-auto-insert />
            </div>
            <div class="p-6 mb-6 overflow-hidden bg-white dark:bg-black shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-800">
                <livewire:water-quality-card wire:poll.5s />
            </div>

            <div class="p-6 mb-6 overflow-hidden bg-white dark:bg-black shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-800">
                <livewire:water-quality-chart wire:poll.5s />
            </div>

            <div class="p-6 overflow-hidden bg-white dark:bg-black shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-800">
                <livewire:water-quality-data-table wire:poll.5s />
            </div>
        </div>
    </div>
</x-app-layout>
