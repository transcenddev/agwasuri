<aside x-data="{ open: false }" class="sidebar-container">
    
    {{-- Desktop: collapsible sidebar with toggle --}}
        <div x-data="{ expanded: false }" @mouseenter="expanded = true" @mouseleave="expanded = false" @toggle-sidebar-desktop.window="expanded = !expanded"
            class="hidden lg:flex flex-col fixed left-0 top-0 bottom-0 transition-all duration-300 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-gray-800 z-30"
            :class="expanded ? 'w-64' : 'w-16'">
        
        {{-- Logo section with toggle button --}}
        <div class="flex items-center gap-3 p-3 border-b border-transparent transition-all"
             :class="expanded ? 'px-5 justify-between' : 'justify-center'">
            <div class="absolute left-0 right-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gray-200 dark:via-gray-700 to-transparent"></div>
            <div class="flex items-center gap-3" :class="expanded ? '' : 'justify-center'">
                <div class="w-10 h-10 flex-shrink-0">
                    <img src="{{ asset('assets/agwasuri-logo.svg') }}" alt="AgwaSuri logo" class="w-10 h-10 object-contain" />
                </div>
                <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <a href="{{ route('dashboard', ['user' => request('user')]) }}" wire:navigate class="text-xl font-serif font-normal md:font-medium tracking-normal hover:opacity-80 transition-all duration-200">
                        <span class="text-cvsu dark:text-cvsu-400">AgwaSuri</span>
                    </a>
                </div>
            </div>
            {{-- Sidebar collapse button removed per design preference. --}}
        </div>

        {{-- Navigation links --}}
        <nav class="flex-1 overflow-y-auto p-2 space-y-1" role="navigation" aria-label="Main navigation">
            <a href="{{ route('dashboard', ['user' => request('user')]) }}" 
               wire:navigate
               title="Dashboard"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}"
               :class="expanded ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-sm whitespace-nowrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Dashboard</span>
            </a>

            <a href="{{ route('optimal-fish') }}" 
               wire:navigate
               title="Optimal Fish"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 {{ request()->routeIs('optimal-fish') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}"
               :class="expanded ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm whitespace-nowrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Optimal Fish</span>
            </a>

            <a href="{{ route('historical-data', ['user' => request('user')]) }}" 
               wire:navigate
               title="History"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 {{ request()->routeIs('historical-data') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}"
               :class="expanded ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm whitespace-nowrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">History</span>
            </a>

            @if(auth()->user()->isAdmin())
                {{-- Admin heading removed for cleaner sidebar UI --}}
                <a href="{{ route('account_management') }}" 
                       wire:navigate
                       title="Account Management"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 {{ request()->routeIs('account_management') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}"
                       :class="expanded ? 'justify-start' : 'justify-center'">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-sm whitespace-nowrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Accounts</span>
                    </a>
            @endif
        </nav>

        {{-- Theme toggle & Account --}}
        <div class="p-3 space-y-2">
            {{-- Theme toggle (styled like Profile/Logout) --}}
            <button
                type="button"
                onclick="window.toggleTheme && window.toggleTheme()"
                class="relative overflow-hidden flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800 w-full"
                :class="expanded ? 'justify-start' : 'justify-center'"
                title="Toggle theme"
                aria-label="Toggle dark mode"
                data-role="theme-toggle-sidebar">
                <!-- Sun Icon (Light Mode) -->
                <svg data-role="icon-sun" class="w-5 h-5 flex-shrink-0 transition-all duration-300 opacity-100 scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <!-- Moon Icon (Dark Mode) -->
                <svg data-role="icon-moon" class="w-5 h-5 flex-shrink-0 transition-all duration-300 opacity-0 scale-0 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <span class="text-sm whitespace-nowrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Theme</span>
            </button>

            {{-- Profile link --}}
            <a href="{{ route('profile') }}" 
               wire:navigate
               title="Profile"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}"
               :class="expanded ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-sm whitespace-nowrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Profile</span>
            </a>

            {{-- Logout link (styled like Profile) --}}
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 {{ request()->routeIs('logout') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}"
                        :class="expanded ? 'justify-start' : 'justify-center'"
                        title="Logout">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="text-sm whitespace-nowrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Logout</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Theme toggle icon sync script --}}
    <script>
    (function(){
        const btn = document.querySelector('[data-role="theme-toggle-sidebar"]');
        if (!btn) return;

        const sun = btn.querySelector('[data-role="icon-sun"]');
        const moon = btn.querySelector('[data-role="icon-moon"]');

        function setIcons(theme) {
            const dark = theme === 'dark' || (!theme && document.documentElement.classList.contains('dark'));
            if (dark) {
                sun.classList.remove('opacity-100','scale-100');
                sun.classList.add('opacity-0','scale-0','absolute');
                moon.classList.remove('opacity-0','scale-0','absolute');
                moon.classList.add('opacity-100','scale-100');
                btn.setAttribute('title','Switch to Light Mode');
            } else {
                moon.classList.remove('opacity-100','scale-100');
                moon.classList.add('opacity-0','scale-0','absolute');
                sun.classList.remove('opacity-0','scale-0','absolute');
                sun.classList.add('opacity-100','scale-100');
                btn.setAttribute('title','Switch to Dark Mode');
            }
        }

        // Initial state
        const saved = localStorage.getItem('agwasuri_theme');
        setIcons(saved);

        // Quick visual flip on click
        btn.addEventListener('click', () => {
            const cur = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            setIcons(cur);
        });

        // Listen for theme changes
        window.addEventListener('agwasuri:theme-changed', (e) => setIcons(e.detail));
    })();
    </script>

    {{-- Mobile: drawer overlay (keeping original mobile implementation) --}}
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Mobile theme toggle (parity with desktop) --}}
        <div class="px-4 py-2 lg:hidden">
            {{-- Mobile theme toggle: use the same partial but default variant for compact view --}}
            @include('livewire.dark-mode-toggle')
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="{{ route('dashboard', ['user' => request('user')]) }}" 
               wire:navigate
               @click="open = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('dashboard') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-sm">Dashboard</span>
            </a>

            <a href="{{ route('optimal-fish') }}" 
               wire:navigate
               @click="open = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('optimal-fish') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm">Optimal Fish</span>
            </a>

            <a href="{{ route('historical-data', ['user' => request('user')]) }}" 
               wire:navigate
               @click="open = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('historical-data') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">History</span>
            </a>

            @if(auth()->user()->isAdmin())
                {{-- Admin heading removed for cleaner sidebar UI --}}
                <a href="{{ route('account_management') }}" 
                              wire:navigate
                              @click="open = false"
                              class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('account_management') ? 'bg-cvsu-50 dark:bg-cvsu-900/20 text-cvsu-700 dark:text-cvsu-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-sm">Accounts</span>
                    </a>
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
