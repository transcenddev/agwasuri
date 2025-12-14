<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<nav x-data="{ open: false }" style="--header-height:64px; --nav-icon: var(--cvsu-500);" class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-black border-b border-gray-200 dark:border-gray-800">
    <!-- Primary Navigation Menu -->
    <div class="px-6 py-0 mx-auto max-w-7xl">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-3 h-full">
                <!-- Mobile hamburger (for sidebar drawer) -->
                <button @click="$dispatch('toggle-mobile-sidebar')" 
                        class="lg:hidden p-2 rounded hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                        aria-label="Toggle navigation">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Logo -->
                <a href="{{ route('dashboard', ['user' => request('user')]) }}" wire:navigate class="text-2xl font-serif font-normal md:font-medium tracking-normal hover:opacity-80 transition-opacity">
                    <span class="text-cvsu dark:text-cvsu-400">AgwaSuri</span>
                </a>
            </div>

            <!-- Right Side: Dark Mode Toggle + User Dropdown (show only on large screens) -->
            <div class="hidden lg:flex lg:items-center lg:space-x-4 h-full">
                <!-- Dark Mode Toggle -->
                <livewire:dark-mode-toggle variant="green" />
                
                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-700 dark:text-gray-100 transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md hover:text-cvsu dark:hover:text-cvsu-400 focus:outline-none">
                            <div x-data='@json(["name" => (auth()->user()?->name ?? "User")])' x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name" aria-live="polite">User</div>

                            {{-- <div x-data="{{ json_encode(['name' => trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: 'User']) }}" x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name"></div> --}}


                            <div class="ms-1">
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            Profile
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                Log Out
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger + mobile theme toggle (visible on tablet and mobile) -->
            <div class="flex items-center gap-2 -me-2 lg:hidden">
                <div class="me-1">
                    <livewire:dark-mode-toggle variant="green" />
                </div>
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7h16M4 17h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
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
