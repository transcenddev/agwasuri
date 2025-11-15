{{--
    UNCONVENTIONAL MODE - Creative/Experimental Design
    
    This is the experimental landing page for testing modern UI/UX ideas.
    Features: glassmorphism, gradients, micro-animations, creative layouts.
    Still professional and accessible - creative but not messy.
    
    For stable production design, see welcome.blade.php
    Preview Route: http://127.0.0.1:8000/preview
--}}
<!DOCTYPE html>
<html lang="en" class="{{ session('darkMode', false) ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgwaSuri – Monitor Your Pond. Grow Healthier Fish.</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-ocean-50 dark:bg-ocean-950 text-ocean-900 dark:text-ocean-100 transition-colors duration-300">
    {{-- Hero Section Only --}}
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        {{-- Background Image with Dark Gradient Overlay --}}
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('assets/fishpond-birdseyeview.svg') }}" alt="Fishpond aerial view" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-ocean-900/85 via-pond-900/80 to-water-900/85"></div>
        </div>

        {{-- Navigation --}}
        <nav class="absolute top-0 left-0 right-0 z-20 px-6 py-6 bg-gradient-to-b from-ocean-900/90 to-transparent backdrop-blur-sm">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/agwasuri_logo.svg') }}" alt="AgwaSuri Logo" class="h-12 drop-shadow-xl">
                    <span class="text-2xl font-bold text-white drop-shadow-lg">AgwaSuri</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="/login" class="px-6 py-2.5 bg-gradient-to-r from-water-500 to-water-600 hover:from-water-600 hover:to-water-700 text-white font-semibold rounded-lg transition-all shadow-xl hover:shadow-2xl hover:scale-105">
                        Login
                    </a>
                </div>
            </div>
        </nav>

        {{-- Hero Content --}}
        <div class="relative z-10 max-w-6xl mx-auto px-6 text-center">
            {{-- Main Headline --}}
            <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-extrabold text-white mb-6 leading-tight">
                <span class="block drop-shadow-2xl">Stop Guessing.</span>
                <span class="block drop-shadow-2xl bg-gradient-to-r from-water-300 via-aqua-300 to-water-400 bg-clip-text text-transparent">
                    Start Growing.
                </span>
            </h1>
            
            {{-- Subheadline --}}
            <p class="text-lg sm:text-xl md:text-2xl text-white/90 mb-16 drop-shadow-xl max-w-4xl mx-auto leading-relaxed font-normal">
                Know exactly when your pond needs attention. Get AI recommendations on the best fish for your water conditions.
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row gap-5 justify-center mb-24">
                <a href="/register" class="group px-14 py-6 bg-gradient-to-r from-water-500 via-water-600 to-aqua-500 hover:from-water-600 hover:via-water-700 hover:to-aqua-600 text-white text-xl font-bold rounded-xl shadow-2xl hover:shadow-water-500/50 transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                    <span class="flex items-center justify-center gap-3">
                        Try Free for 30 Days
                        <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </span>
                </a>
                <a href="/contact" class="px-14 py-6 bg-white/10 hover:bg-white/20 backdrop-blur-md border-2 border-white/50 hover:border-white text-white text-xl font-bold rounded-xl shadow-2xl transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                    See How It Works
                </a>
            </div>

            {{-- Metrics Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div class="group bg-white/95 backdrop-blur-md rounded-2xl p-7 shadow-2xl border-2 border-water-200 hover:border-water-400 hover:shadow-water-300/50 transition-all hover:scale-105 hover:-translate-y-1">
                    <div class="w-14 h-14 mb-5 bg-gradient-to-br from-water-400 to-water-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform mx-auto">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="text-xl font-extrabold text-ocean-800 mb-2">Check Your Pond</p>
                    <p class="text-sm text-ocean-600">From anywhere, anytime</p>
                </div>
                <div class="group bg-white/95 backdrop-blur-md rounded-2xl p-7 shadow-2xl border-2 border-pond-200 hover:border-pond-400 hover:shadow-pond-300/50 transition-all hover:scale-105 hover:-translate-y-1">
                    <div class="w-14 h-14 mb-5 bg-gradient-to-br from-pond-400 to-pond-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform mx-auto">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <p class="text-xl font-extrabold text-ocean-800 mb-2">Know What to Stock</p>
                    <p class="text-sm text-ocean-600">AI picks best fish for your water</p>
                </div>
                <div class="group bg-white/95 backdrop-blur-md rounded-2xl p-7 shadow-2xl border-2 border-aqua-200 hover:border-aqua-400 hover:shadow-aqua-300/50 transition-all hover:scale-105 hover:-translate-y-1">
                    <div class="w-14 h-14 mb-5 bg-gradient-to-br from-aqua-400 to-aqua-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform mx-auto">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-xl font-extrabold text-ocean-800 mb-2">Reduce Losses</p>
                    <p class="text-sm text-ocean-600">Catch problems early</p>
                </div>
            </div>
        </div>

        {{-- Scroll Indicator --}}
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 z-20 animate-bounce opacity-75 hover:opacity-100 transition-opacity">
            <svg class="w-10 h-10 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    @livewireScripts
</body>
</html>

