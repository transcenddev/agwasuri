<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireChartsScripts
        
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
            .dark input[type="text"],
            .dark input[type="number"],
            .dark input[type="search"],
            .dark textarea,
            .dark select {
                background-color: #111827 !important;
                color: #ffffff !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-white dark:bg-black">
        <div class="min-h-screen">
            <livewire:layout.navigation />

            <!-- Page Content -->
            <main class="pt-16">
                {{ $slot }}
            </main>
        </div>
        
        @livewireScripts
        
        <!-- Dark Mode Toggle Script -->
        <script>
            // Initialize dark mode from localStorage on page load
            if (localStorage.getItem('darkMode') === 'true' || {{ session('darkMode', false) ? 'true' : 'false' }}) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
            }
            
            // Listen for dark mode toggle events from Livewire
            document.addEventListener('livewire:init', () => {
                Livewire.on('dark-mode-toggled', (event) => {
                    if (event.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('darkMode', 'true');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('darkMode', 'false');
                    }
                });
            });
        </script>
    <body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-white dark:bg-black">
        <div class="min-h-screen">
            <livewire:layout.navigation />

            <!-- Page Content -->
            <main class="pt-16">
                {{ $slot }}
            </main>
        </div>
        
        @livewireScripts
        
        <!-- Dark Mode Toggle Script -->
        <script>
            // Initialize dark mode from localStorage on page load
            if (localStorage.getItem('darkMode') === 'true' || {{ session('darkMode', false) ? 'true' : 'false' }}) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
            }
            
            // Listen for dark mode toggle events from Livewire
            document.addEventListener('livewire:init', () => {
                Livewire.on('dark-mode-toggled', (event) => {
                    if (event.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('darkMode', 'true');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('darkMode', 'false');
                    }
                });
            });
        </script>
    </body>
</html>
 