<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<nav x-data="{ open: false }" style="--header-height:64px; --nav-icon: var(--cvsu-500);" class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white dark:bg-black border-b border-gray-200 dark:border-gray-800">
    <!-- Primary Navigation Menu -->
    <div class="px-6 py-0 mx-auto max-w-7xl">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-3 h-full">
                <!-- (single mobile hamburger kept on right side; left duplicate removed) -->

                <!-- Logo -->
                <a href="{{ route('dashboard', ['user' => request('user')]) }}" wire:navigate class="flex items-center gap-2 text-2xl font-serif font-normal md:font-medium tracking-normal hover:opacity-80 transition-opacity">
                    <img src="{{ asset('assets/logo/agwasuri-logo.svg') }}" alt="AgwaSuri" class="w-6 h-6 object-contain" />
                    <span class="text-cvsu dark:text-cvsu-400">AgwaSuri</span>
                </a>
            </div>

            <!-- Mobile controls: profile, theme toggle, and hamburger -->
            <div class="lg:hidden flex items-center gap-2 -me-2">
                <a href="{{ route('profile') }}" wire:navigate title="Profile" aria-label="Profile" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>

                <div class="me-1">
                    <livewire:dark-mode-toggle variant="green" />
                </div>

                {{-- Mobile hamburger becomes a logout button with confirmation --}}
                <div class="relative">
                    <button onclick="if (confirm('Are you sure you want to logout?')) { document.getElementById('mobile-logout-form').submit(); }"
                        title="Logout" aria-label="Logout"
                        class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                        <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8v8a2 2 0 002 2h4" />
                        </svg>
                    </button>

                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" id="mobile-logout-form" style="display:none">@csrf</form>
                    @else
                        <form method="POST" action="{{ url('/logout') }}" id="mobile-logout-form" style="display:none">@csrf</form>
                    @endif
                </div>
            </div>
        </div>
    </div>


</nav>

<script>
    (function () {
        function setHeaderHeight() {
            var nav = document.querySelector('nav');
            if (!nav) return;
            var h = nav.getBoundingClientRect().height;
            document.documentElement.style.setProperty('--header-height', h + 'px');
        }
        if (typeof window !== 'undefined') {
            window.addEventListener('DOMContentLoaded', setHeaderHeight);
            window.addEventListener('resize', setHeaderHeight);
            // Use ResizeObserver when available to react to dynamic nav height changes
            if (window.ResizeObserver) {
                try {
                    var ro = new ResizeObserver(setHeaderHeight);
                    var navEl = document.querySelector('nav');
                    if (navEl) ro.observe(navEl);
                } catch (e) {
                    // ignore
                }
            }
        }
    })();
</script>
