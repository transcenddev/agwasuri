<aside x-data="{ open: false }" class="sidebar-container">
    
    {{-- Desktop: fixed sidebar --}}
    <div class="hidden lg:flex flex-col fixed left-0 top-0 bottom-0 w-64 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-gray-800 z-30">
        
        {{-- Logo section --}}
        <div class="flex items-center gap-3 p-4 border-b border-gray-200 dark:border-gray-800">
            <div class="w-10 h-10 flex-shrink-0">
                <img src="{{ asset('assets/agwasuri-logo.svg') }}" alt="AgwaSuri logo" class="w-10 h-10 object-contain" />
            </div>
            <div>
                <a href="{{ route('dashboard', ['user' => request('user')]) }}" wire:navigate class="text-2xl font-serif font-normal md:font-medium tracking-normal hover:opacity-80 transition-opacity">
                    <span class="text-cvsu dark:text-cvsu-400">AgwaSuri</span>
                </a>
                <div class="text-xs text-gray-500 dark:text-gray-400">Water Quality Monitor</div>
            </div>
        </div>

        {{-- Navigation links --}}
        <nav class="flex-1 overflow-y-auto p-3 space-y-1" role="navigation" aria-label="Main navigation">
            <a href="{{ route('dashboard', ['user' => request('user')]) }}" 
               wire:navigate
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-sm">Dashboard</span>
            </a>

            <a href="{{ route('optimal-fish') }}" 
               wire:navigate
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('optimal-fish') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                </svg>
                <span class="text-sm">Optimal Fish</span>
            </a>

            <a href="{{ route('historical-data', ['user' => request('user')]) }}" 
               wire:navigate
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('historical-data') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm">History</span>
            </a>

            @if(auth()->user()->isAdmin())
                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="px-3 mb-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admin</div>
                    
                    <a href="{{ route('account_management') }}" 
                       wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('account_management') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-sm">Accounts</span>
                    </a>
                </div>
            @endif
        </nav>

        {{-- Theme toggle & Account --}}
        <div class="p-3 space-y-2 border-t border-gray-200 dark:border-gray-800">
            {{-- Theme toggle --}}
            <div class="px-3">
                <div class="flex items-center justify-between" title="Toggle light / dark theme" aria-label="Theme">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Theme</div>
                    <div class="ml-2">
                        <livewire:dark-mode-toggle variant="green" />
                    </div>
                </div>
            </div>

            {{-- User info label --}}
            <div class="px-3 pt-2">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-5 h-5 flex-shrink-0 rounded-full bg-cvsu-100 dark:bg-cvsu-900 flex items-center justify-center">
                        <span class="text-xs font-medium text-cvsu-700 dark:text-cvsu-400">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->name }}</span>
                </div>
            </div>

            {{-- Profile link --}}
            <a href="{{ route('profile') }}" 
               wire:navigate
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ request()->routeIs('profile') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium border-l-2 border-cvsu-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-sm">Profile</span>
            </a>

            {{-- Logout link --}}
            <div class="px-3">
                @livewire('logout-link')
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

        {{-- Mobile theme toggle (parity with desktop) --}}
        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800 lg:hidden">
            <div class="flex items-center justify-between" title="Toggle light / dark theme" aria-label="Theme">
                <div class="text-sm text-gray-600 dark:text-gray-300">Theme</div>
                <div>
                    <livewire:dark-mode-toggle variant="green" />
                </div>
            </div>
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
