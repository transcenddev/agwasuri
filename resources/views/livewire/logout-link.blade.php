<button wire:click="logout" 
        title="Logout"
        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-md transition-all duration-200 justify-center group-hover:justify-start text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400">
    <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
    </svg>
    <span class="text-sm hidden group-hover:block">Logout</span>
</button>
