<div class="w-full lg:w-80 lg:pl-6">
    <div class="hidden lg:block lg:sticky lg:top-24 space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4">
            {{-- Recommendation component (existing Livewire) --}}
            <livewire:recommendation-card :userId="$activeUserId" />
        </div>

        <!-- Alerts and Quick Actions removed temporarily for presentation/QA -->
    </div>

    {{-- Mobile fallback: keep the existing recommendation card visible on small screens in its original place. --}}
</div>
