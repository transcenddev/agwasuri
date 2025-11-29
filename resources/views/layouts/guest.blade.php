<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(request()->cookie('agwasuri_theme') === 'dark' || request()->cookie('theme') === 'dark') class="dark" @endif>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        <!-- Nav background CSS variable (single source of truth) -->
        <style>
            :root{
                --nav-bg-light: #ffffff;
                --nav-bg-dark: #000000;
                --nav-border-light: #e5e7eb; /* gray-200 */
                --nav-border-dark: #1f2937;  /* gray-800 */
                /* current nav color -- will be switched by .dark */
                --nav-bg: var(--nav-bg-light);
                --nav-border: var(--nav-border-light);
                /* Nav icon color variables (defaults). Override per-header as needed. */
                --nav-icon-light: #1B651B; /* brand green for light mode by default */
                --nav-icon-dark: #1B651B;  /*  brand green for dark mode too8 */
                --nav-icon: var(--nav-icon-light);
            }

            /* Default header uses light nav background; .dark will override the vars */
            .dark { --nav-bg: var(--nav-bg-dark); --nav-border: var(--nav-border-dark); --nav-icon: var(--nav-icon-dark); }
            .site-header{ background: var(--nav-bg); border-color: var(--nav-border); }                                                     
        </style>
        
        <!-- Override browser autofill styles -->
        <style>
            /* Force dark mode autofill styles - matches gray-900 (#111827) */
            .dark input:-webkit-autofill,
            .dark input:-webkit-autofill:hover,
            .dark input:-webkit-autofill:focus,
            .dark input:-webkit-autofill:active {
                -webkit-box-shadow: 0 0 0 1000px #111827 inset !important;
                -webkit-text-fill-color: #ffffff !important;
                box-shadow: 0 0 0 1000px #111827 inset !important;
                border: 1px solid #4b5563 !important; /* gray-600 border */
                transition: background-color 5000s ease-in-out 0s;
            }
            
            /* Light mode autofill */
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus,
            input:-webkit-autofill:active {
                -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
                -webkit-text-fill-color: #111827 !important;
                box-shadow: 0 0 0 1000px #ffffff inset !important;
                border: 1px solid #d1d5db !important; /* gray-300 border */
                transition: background-color 5000s ease-in-out 0s;
            }
            
            /* Ensure all inputs have consistent background in dark mode */
            .dark input[type="email"],
            .dark input[type="password"],
            .dark input[type="text"] {
                background-color: #111827 !important;
                color: #ffffff !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-white dark:bg-black">
        <!-- Minimal Header: Logo + Dark Mode -->
        <header class="site-header fixed top-0 left-0 right-0 z-50 border-b border-gray-200 dark:border-gray-800">
            <nav class="px-6 py-0">
                <div class="flex items-center justify-between h-16 max-w-7xl mx-auto" style="--nav-logo: #1B651B">
                    <!-- Logo (shared partial) -->
                        @include('partials.navbar-logo')
                    
                    <!-- Dark Mode Toggle (styled to match navbar logo) -->
                    <livewire:dark-mode-toggle variant="login " />
                </div>
            </nav>
        </header>

        <!-- Main Content with top padding for fixed header -->
<div class="flex flex-col items-center justify-center min-h-screen px-6">
            <!-- Card -->
            <div class="w-full px-6 py-8 overflow-hidden sm:max-w-md sm:rounded-2xl bg-white dark:bg-black shadow-xl border border-gray-200 dark:border-gray-800">
                {{ $slot }}
            </div>
        </div>
        
        @livewireScripts
        
        @if (view()->exists('components.theme-init-listeners'))
            @include('components.theme-init-listeners')
        @endif
    </body>
</html>
