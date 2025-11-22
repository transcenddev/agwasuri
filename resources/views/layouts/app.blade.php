<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Agwasuri') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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
</html>
