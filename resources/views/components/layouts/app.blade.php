<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(request()->cookie('agwasuri_theme') === 'dark' || request()->cookie('theme') === 'dark') class="dark" @endif>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Prevent flash of wrong theme -->
            @if (view()->exists('components.theme-init-head'))
                @include('components.theme-init-head')
            @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
    <!-- Home page dark mode toggle: high-contrast, accessible button -->
        <button
            type="button"
            onclick="window.toggleTheme && window.toggleTheme()"
            aria-label="Toggle dark mode"
            class="fixed top-4 right-4 z-50 p-2 rounded-md bg-white/10 hover:bg-white/20 text-white dark:bg-gray-800/60 dark:hover:bg-gray-700/60 dark:text-gray-200 shadow-md ring-1 ring-white/10 transition-colors backdrop-blur-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500"
        >
            <svg data-role="icon-sun" aria-hidden="true" class="w-5 h-5 text-white transition-all duration-300 dark:opacity-0 dark:scale-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg data-role="icon-moon" aria-hidden="true" class="w-5 h-5 text-white transition-all duration-300 absolute opacity-0 scale-0 dark:opacity-100 dark:scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
        {{ $slot }}

        @livewireScripts
        @livewireChartsScripts
            @if (view()->exists('components.theme-init-listeners'))
                @include('components.theme-init-listeners')
            @endif
            <script type="module">
            // Expose toggleTheme globally for the button as a fallback if app bundle hasn't run yet
            import('./../../js/theme.js').then(m => { window.toggleTheme = m.toggleTheme; }).catch(()=>{});
            </script>
    </body>
</html>
