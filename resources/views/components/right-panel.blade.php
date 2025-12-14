<div class="w-full lg:w-80 lg:pl-6">
    <div class="hidden lg:block lg:sticky lg:top-24 space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
            {{-- Recommendation component (existing Livewire) --}}
            <livewire:recommendation-card :userId="$activeUserId" />
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Alerts</h4>
            <livewire:alerts-list :userId="$activeUserId" wire:init="load" />
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Quick Actions</h4>
            <livewire:quick-actions :userId="$activeUserId" wire:init="load" />
        </div>
    </div>

    {{-- Mobile fallback: keep the existing recommendation card visible on small screens in its original place. --}}
</div>
