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

        {{-- Expose canonical series and status colors as CSS variables for use in Blade views and components --}}
        @php
            $wqSeries = config('water_colors.series', []);
            $wqStatus = config('water_quality.status_colors', []);
        @endphp
        <style>
            :root {
                /* Series colors (chart + sparklines) */
                --wq-series-temperature: {{ $wqSeries['temperature'] ?? '#EF4444' }};
                --wq-series-salinity: {{ $wqSeries['salinity'] ?? '#14B8A6' }};
                --wq-series-do: {{ $wqSeries['do'] ?? '#2563EB' }};
                --wq-series-ph: {{ $wqSeries['ph'] ?? '#8B5CF6' }};

                /* High-level status colors */
                --wq-status-good: {{ $wqStatus['good'] ?? '#16A34A' }};
                --wq-status-warning: {{ $wqStatus['warning'] ?? '#F59E0B' }};
                --wq-status-harmful: {{ $wqStatus['harmful'] ?? '#DC2626' }};
                --wq-status-unknown: {{ $wqStatus['unknown'] ?? '#6B7280' }};
            }
        </style>

        <style>
            /* Hide ApexCharts annotation/datalabel elements inserted by vendor code */
            .apexcharts-annotations,
            .apexcharts-annotation,
            .apexcharts-annotations-group,
            .apexcharts-point-annotations,
            .apexcharts-point-annotation,
            .apexcharts-point-annotation-label,
            .apexcharts-point-annotations-custom-svg,
            .apexcharts-datalabels,
            .apexcharts-datalabel,
            .apexcharts-series-annotations,
            .apexcharts-xaxis-annotations,
            .apexcharts-yaxis-annotations,
            .apexcharts-xaxis-annotation-label,
            .apexcharts-yaxis-annotation-label {
                display: none !important;
            }
        </style>

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireChartsScripts
    </head>
    <body class="font-sans antialiased min-h-screen" x-data="{ sidebarOpen: false, sidebarHovered: false, presentationMode: false }" @toggle-mobile-sidebar.window="sidebarOpen = !sidebarOpen" @toggle-compact-view.window="presentationMode = !presentationMode">
        {{-- Navigation is fixed; render it above the content wrapper so header height CSS var can be used globally --}}
        <livewire:layout.navigation />

        {{-- Sidebar (fixed on desktop, drawer on mobile) --}}
        <div x-data="{ open: sidebarOpen }" x-init="$watch('sidebarOpen', val => open = val)">
            @include('components.sidebar')
        </div>

        {{-- Bottom Navigation (mobile/tablet only) --}}
        @include('components.bottom-nav')

        {{-- Main content with fixed left offset for sidebar (16 = 4rem collapsed, 64 = 16rem expanded) --}}
        <div :class="sidebarHovered ? 'lg:ml-64' : 'lg:ml-16'" class="transition-all duration-300" style="margin-top: var(--header-height);">
            <x-container class="site-body w-full px-4 lg:px-8">
                {{-- Top bar: pond selector, last-updated and connection status --}}
                @include('components.topbar')

                <main>
                    {{ $slot }}
                </main>
            </x-container>
        </div>
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
