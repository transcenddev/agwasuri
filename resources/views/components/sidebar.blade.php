<aside x-data="{ open: false, mini: localStorage.getItem('sidebar-mini') === '1', hovered: false, _hoverTimeout: null }" 
    @keydown.window.prevent.ctrl.b="mini = !mini; localStorage.setItem('sidebar-mini', mini ? '1' : '0')"
    class="sidebar-container">
    
    {{-- Desktop: persistent sidebar --}}
        <div :class="(mini && !hovered) ? 'w-16' : 'w-64'" 
            @mouseenter="if(mini){ clearTimeout(_hoverTimeout); hovered = true; }"
            @mouseleave="if(mini){ _hoverTimeout = setTimeout(()=>{ hovered = false }, 120) }"
            class="hidden lg:flex flex-col fixed left-0 top-[var(--header-height)] h-[calc(100vh-var(--header-height))] bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-200 z-30">
        
        {{-- Mini toggle button --}}
        <div class="flex items-center justify-end p-3 border-b border-gray-100 dark:border-gray-800">
            <button @click="mini = !mini; localStorage.setItem('sidebar-mini', mini ? '1' : '0')" 
                    class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                    :aria-label="mini ? 'Expand sidebar' : 'Collapse sidebar'">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        {{-- Navigation links --}}
        <nav class="flex-1 overflow-y-auto p-3 space-y-1" role="navigation" aria-label="Main navigation">
            <a href="{{ route('dashboard', ['user' => request('user')]) }}" 
               wire:navigate
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="!mini" class="text-sm">Dashboard</span>
            </a>

            <a href="{{ route('optimal-fish') }}" 
               wire:navigate
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('optimal-fish') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                </svg>
                <span x-show="!mini" class="text-sm">Optimal Fish</span>
            </a>

            <a href="{{ route('historical-data', ['user' => request('user')]) }}" 
               wire:navigate
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('historical-data') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="!mini" class="text-sm">History</span>
            </a>

            @if(auth()->user()->isAdmin())
                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                    <div x-show="!mini" class="px-3 mb-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admin</div>
                    
                    <a href="{{ route('account_management') }}" 
                       wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('account_management') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-show="!mini" class="text-sm">Accounts</span>
                    </a>
                </div>
            @endif
        </nav>

        {{-- Keyboard hint (bottom) --}}
        <div x-show="!mini" class="p-3 border-t border-gray-100 dark:border-gray-800">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-slate-800 rounded text-xs">Ctrl+B</kbd> to toggle
            </div>
        </div>
    </div>

    {{-- Mobile: drawer overlay --}}
    <div x-show="open" 
         x-cloak 
         @click="open = false"
         class="lg:hidden fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <div x-show="open" 
         x-cloak
         x-trap="open"
         class="lg:hidden fixed left-0 top-0 bottom-0 w-64 bg-white dark:bg-slate-900 z-50 flex flex-col"
         x-transition:enter="transition-transform duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         @click.stop
         role="dialog"
         aria-modal="true"
         aria-label="Mobile navigation">
        
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
            <span class="text-lg font-serif text-cvsu dark:text-cvsu-400">AgwaSuri</span>
            <button @click="open = false" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="{{ route('dashboard', ['user' => request('user')]) }}" 
               wire:navigate
               @click="open = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('dashboard') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-sm">Dashboard</span>
            </a>

            <a href="{{ route('optimal-fish') }}" 
               wire:navigate
               @click="open = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('optimal-fish') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                </svg>
                <span class="text-sm">Optimal Fish</span>
            </a>

            <a href="{{ route('historical-data', ['user' => request('user')]) }}" 
               wire:navigate
               @click="open = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('historical-data') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm">History</span>
            </a>

            @if(auth()->user()->isAdmin())
                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="px-3 mb-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admin</div>
                    
                    <a href="{{ route('account_management') }}" 
                       wire:navigate
                       @click="open = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('account_management') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-sm">Accounts</span>
                    </a>
                </div>
            @endif
        </nav>
    </div>

    {{-- Mobile hamburger trigger (injected via Alpine event from nav) --}}
    <script>
        document.addEventListener('alpine:init', () => {
            window.addEventListener('toggle-sidebar', () => {
                Alpine.store('sidebar') && (Alpine.store('sidebar').open = !Alpine.store('sidebar').open);
            });
        });
    </script>
</aside>
