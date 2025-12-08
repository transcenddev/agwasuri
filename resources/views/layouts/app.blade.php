<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(request()->cookie('agwasuri_theme') === 'dark' || request()->cookie('theme') === 'dark') class="dark" @endif>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @hasSection('title')
                @yield('title') - {{ config('app.name', 'Agwasuri') }}
            @else
                {{ config('app.name', 'Agwasuri') }}
            @endif
        </title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (view()->exists('components.theme-init-head'))
            @include('components.theme-init-head')
        @endif

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireChartsScripts
    </head>
    <body class="font-sans antialiased min-h-screen">
        {{-- Navigation is fixed; render it above the content wrapper so header height CSS var can be used globally --}}
        <livewire:layout.navigation />

        {{-- Wrap the slot with the shared x-container so every page receives the header offset --}}
        <x-container class="site-body">
            <main>
                {{ $slot }}
            </main>
        </x-container>
    </body>
        <!-- Livewire bridge: keep localStorage and document class in sync when Livewire toggles theme -->
        <script>
            (function(){
                try {
                    document.addEventListener('livewire:init', function(){
                        if (window.Livewire && typeof Livewire.on === 'function') {
                            Livewire.on('dark-mode-toggled', function(event){
                                try {
                                    if (event && event.darkMode) {
                                        document.documentElement.classList.add('dark');
                                        try { localStorage.setItem('agwasuri_theme','dark'); } catch(e){}
                                    } else {
                                        document.documentElement.classList.remove('dark');
                                        try { localStorage.setItem('agwasuri_theme','light'); } catch(e){}
                                    }
                                } catch(e){}
                            });
                        }
                    });
                    // Also listen for DOM-level helper events
                    window.addEventListener('agwasuri:theme-changed', function(e){
                        try {
                            if (e && e.detail === 'dark') { document.documentElement.classList.add('dark'); }
                            else { document.documentElement.classList.remove('dark'); }
                        } catch(e){}
                    });
                } catch(e){}
            })();
        </script>
</html>
