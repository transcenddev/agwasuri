<aside x-data="{ mini: (localStorage.getItem('sidebar-mini') === '1') }" class="hidden lg:block" role="navigation" aria-label="Main navigation">
    <div :class="mini ? 'w-16' : 'w-64'" class="transition-all duration-150 bg-white dark:bg-slate-900 rounded-md border p-3">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <img src="/assets/logo.svg" alt="AgwaSuri" class="h-6 w-auto" />
                <span x-show="!mini" class="font-medium text-sm text-gray-700 dark:text-gray-200">AgwaSuri</span>
            </div>
            <button @click="mini = !mini; localStorage.setItem('sidebar-mini', mini ? '1' : '0')" class="text-gray-500 hover:text-gray-700 dark:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <nav class="space-y-1">
            <a href="{{ route('dashboard') ?? '#' }}" class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-slate-800">
                <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/></svg>
                <span x-show="!mini" class="text-sm text-gray-700 dark:text-gray-200">Dashboard</span>
            </a>

            <a href="{{ route('optimal-fish') ?? '#' }}" class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-slate-800">
                <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M12 3v18"/></svg>
                <span x-show="!mini" class="text-sm text-gray-700 dark:text-gray-200">Optimal Fish</span>
            </a>

            <a href="{{ route('history') ?? '#' }}" class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-slate-800">
                <svg class="h-5 w-5 text-sky-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                <span x-show="!mini" class="text-sm text-gray-700 dark:text-gray-200">History</span>
            </a>

            <a href="{{ route('devices') ?? '#' }}" class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-slate-800">
                <svg class="h-5 w-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6"/></svg>
                <span x-show="!mini" class="text-sm text-gray-700 dark:text-gray-200">Devices</span>
            </a>

            <a href="{{ route('alerts') ?? '#' }}" class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-slate-800">
                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span x-show="!mini" class="text-sm text-gray-700 dark:text-gray-200">Alerts</span>
            </a>

            @if(auth()->check() && (isset(auth()->user()->role) && auth()->user()->role === 'admin'))
                <div class="mt-3 pt-3 border-t">
                    <h4 x-show="!mini" class="text-xs text-gray-500 uppercase">Admin</h4>
                    <a href="{{ route('admin.users') ?? '#' }}" class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-slate-800">
                        <svg class="h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 10-8 0v4"/></svg>
                        <span x-show="!mini" class="text-sm text-gray-700 dark:text-gray-200">Users</span>
                    </a>
                </div>
            @endif
        </nav>
    </div>
</aside>
