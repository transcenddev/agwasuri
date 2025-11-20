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
            <div class="flex items-center space-x-8 h-full">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" wire:navigate class="text-2xl font-serif font-normal md:font-medium tracking-normal hover:opacity-80 transition-opacity">
                    <span class="text-cvsu dark:text-cvsu-400">AgwaSuri</span>
                </a>

                <!-- Navigation Links -->
                <div class="hidden md:flex md:items-center md:space-x-2 h-full">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        Dashboard
                    </x-nav-link>

                    <x-nav-link :href="route('optimal-fish')" :active="request()->routeIs('optimal-fish')" wire:navigate>
                        Optimal Fish
                    </x-nav-link>

                    <x-nav-link :href="route('historical-data')" :active="request()->routeIs('historical-data')" wire:navigate>
                        Historical Data
                    </x-nav-link>

                    @if (auth()->user()->isAdmin())
                        <x-nav-link :href="route('account_management')" :active="request()->routeIs('account_management')" wire:navigate>
                            Account Management
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Right Side: Dark Mode Toggle + User Dropdown -->
            <div class="hidden md:flex md:items-center md:space-x-4 h-full">
                <!-- Dark Mode Toggle -->
                <livewire:dark-mode-toggle variant="green" />
                
                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-700 dark:text-gray-300 transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name"></div>

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

            <!-- Hamburger -->
            <div class="flex items-center -me-2 sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                Dashboard
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-800">
            <div class="px-4">
                <div class="text-base font-medium text-gray-800 dark:text-gray-100" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    Profile
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        Log Out
                    </x-responsive-nav-link>
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
