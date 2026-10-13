{{--
    REGULAR MODE - Production/Conservative Design

    This is the stable, client-ready landing page.
    Clean design that "masa" (general audience) easily understands.

    For experimental/creative designs, see landing.blade.php
    Route: http://127.0.0.1:8000/
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AgwaSuri - Water Quality Monitoring</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Fade-in animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Animation classes */
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Stagger delays */
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }

        /* Initially hidden for animation */
        .animate-on-load {
            opacity: 0;
        }

        /* Navbar scroll effect */
        .navbar-scrolled {
            background-color: rgba(255, 255, 255, 0.15);
            border-bottom-color: rgba(255, 255, 255, 0.3);
        }

        .dark .navbar-scrolled {
            background-color: rgba(17, 24, 39, 0.4);
            border-bottom-color: rgba(55, 65, 81, 0.4);
        }
    </style>
</head>

<body
    class="relative font-sans antialiased transition-colors duration-300 bg-fixed bg-center bg-no-repeat bg-cover dark:bg-[#0a0a0a]"
    style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), linear-gradient(rgba(27, 101, 27, 0.2), rgba(27, 101, 27, 0.2)), url('{{ asset('assets/backgrounds/ternate-fishpond-landscape.webp') }}');">

    <!-- Dark mode overlay (kept behind interactive elements so buttons stay bright) -->
    <div class="fixed inset-0 transition-opacity duration-300 bg-black pointer-events-none dark:opacity-60 opacity-0 z-0"></div>

    <div class="relative z-10">
        <livewire:welcome.navigation />

        <div class="relative flex items-center justify-center min-h-screen px-4 mx-auto max-w-screen-xl lg:px-8 pt-32">
            <div class="text-center max-w-4xl mx-auto">

                <!-- Tagline with Glassmorphism -->
                <div class="inline-block px-6 py-3 mb-6 backdrop-blur-md bg-white/10 dark:bg-gray-900/20 border border-white/30 dark:border-gray-700/40 rounded-full shadow-xl animate-on-load animate-fade-in-up delay-100">
                    <p class="text-cvsu-300 dark:text-cvsu-200 text-xs sm:text-sm font-semibold tracking-widest uppercase">
                        IoT-Powered Water Quality Monitoring
                    </p>
                </div>

                <!-- Heading -->
                <h1 class="text-3xl font-normal text-white font-serif sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl drop-shadow-lg animate-on-load animate-fade-in-up delay-200">
                    Monitor Water Quality.

                    <strong class="block font-normal">Maximize Fish Survival.</strong>
                </h1>

                <!-- Description -->
                <p class="max-w-2xl mx-auto mt-4 text-white text-base sm:text-lg/relaxed drop-shadow-md animate-on-load animate-fade-in-up delay-300">
                    Real-time monitoring of pH, dissolved oxygen, temperature, and salinity—helping Ternate fishpond operators reduce mortality and boost productivity.
                </p>

                <!-- Trust/Feature Icons -->
                <div class="flex flex-wrap justify-center gap-4 sm:gap-6 mt-6 text-white text-xs sm:text-sm animate-on-load animate-fade-in-up delay-400">
                    <div class="flex items-center gap-2 transition-transform duration-300 hover:scale-110 hover:text-cvsu-200">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-cvsu-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="font-medium">Solar Powered</span>
                    </div>
                    <div class="flex items-center gap-2 transition-transform duration-300 hover:scale-110 hover:text-cvsu-200">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-cvsu-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span class="font-medium">Machine Learning Fish Recommendations</span>
                    </div>
                    <div class="flex items-center gap-2 transition-transform duration-300 hover:scale-110 hover:text-cvsu-200">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-cvsu-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="font-medium">Real-Time Analytics</span>
                    </div>
                </div>

                <!-- CTAs -->
                <div class="flex justify-center mt-8 animate-on-load animate-fade-in-up delay-500">
                    <a href="{{ route('login') }}"
                        class="inline-block px-10 sm:px-14 py-3 sm:py-3.5 text-base font-semibold text-white transition-all duration-200 rounded-lg shadow-lg bg-cvsu hover:bg-cvsu-700 focus:outline-none focus:ring-2 focus:ring-cvsu-400 active:bg-cvsu-800 dark:bg-cvsu-600 dark:hover:bg-cvsu-700 hover:scale-105 transform">
                        Monitor Now
                    </a>
                </div>

                <!-- Scroll Indicator -->
                <div class="mt-8 sm:mt-12 animate-bounce animate-on-load animate-fade-in delay-600">
                    <a href="#features" class="block">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 mx-auto text-white/60 hover:text-white/80 transition-colors cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts

    <!-- Animation & Scroll Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Trigger animations on page load
            setTimeout(() => {
                document.querySelectorAll('.animate-on-load').forEach(el => {
                    el.style.opacity = '1';
                });
            }, 100);

            // Navbar scroll effect
            const navbar = document.querySelector('nav');
            let lastScroll = 0;

            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;

                if (currentScroll > 50) {
                    navbar.classList.add('navbar-scrolled');
                } else {
                    navbar.classList.remove('navbar-scrolled');
                }

                lastScroll = currentScroll;
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>

    <!-- Landing page does not sync global dark-mode by design -->
</body>

</html>
