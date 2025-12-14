{{-- Bottom Navigation for Mobile/Tablet --}}
<nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 border-t border-gray-200 dark:border-gray-800 z-40 pb-safe">
    <div class="grid grid-cols-4 h-16">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard', ['user' => request('user')]) }}" 
           wire:navigate
           class="flex flex-col items-center justify-center gap-1 transition-colors {{ request()->routeIs('dashboard') ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs font-medium">Dashboard</span>
        </a>

        {{-- Optimal Fish --}}
        <a href="{{ route('optimal-fish') }}" 
           wire:navigate
           class="flex flex-col items-center justify-center gap-1 transition-colors {{ request()->routeIs('optimal-fish') ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
            </svg>
            <span class="text-xs font-medium">Fish</span>
        </a>

        {{-- History --}}
        <a href="{{ route('historical-data', ['user' => request('user')]) }}" 
           wire:navigate
           class="flex flex-col items-center justify-center gap-1 transition-colors {{ request()->routeIs('historical-data') ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs font-medium">History</span>
        </a>

        {{-- Profile / Menu --}}
        <button @click="sidebarOpen = !sidebarOpen"
                class="flex flex-col items-center justify-center gap-1 transition-colors text-gray-600 dark:text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span class="text-xs font-medium">More</span>
        </button>
    </div>
</nav>

{{-- Add bottom padding to content to prevent overlap --}}
<style>
    @media (max-width: 1023px) {
        body {
            padding-bottom: 4rem;
        }
    }
</style>
