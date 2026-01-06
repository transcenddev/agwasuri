<nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 backdrop-blur-md bg-white/10 dark:bg-gray-900/30 border-b border-white/20 dark:border-gray-700/30 px-6 py-0" style="--nav-logo: #ffffff; --nav-icon: #ffffff;">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between h-16">
            <!-- Logo + Brand -->
            <a href="{{ url('/') }}" wire:navigate class="flex items-center gap-2 hover:opacity-90 transition-all duration-200">
                <img src="{{ asset('assets/agwasuri-logo.svg') }}" alt="AgwaSuri" class="w-10 h-10 object-contain" />
                <span class="text-2xl font-serif font-normal md:font-medium tracking-normal hidden sm:block text-white">AgwaSuri</span>
                <span class="sr-only">Agwasuri home</span>
            </a>

            <!-- Right Side: Dark Mode Toggle + Login -->
            <div class="flex items-center gap-3">
                <!-- Dark Mode Toggle -->
                <livewire:dark-mode-toggle />

                @auth
                <a href="{{ url('/dashboard') }}"
                    class="px-6 py-2 text-sm font-medium text-white transition-all duration-200 rounded-lg bg-cvsu hover:bg-cvsu-700 focus:outline-none focus:ring-2 focus:ring-cvsu-400 active:bg-cvsu-800 dark:bg-cvsu-600 dark:hover:bg-cvsu-700 shadow-lg">
                    Dashboard
                </a>
                @else
                <a href="{{ route('login') }}"
                    class="px-5 py-2 text-sm font-medium text-white transition-all duration-200 rounded-lg border border-white/30 hover:border-cvsu-400 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/50 backdrop-blur-sm">
                    Login
                </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    // Navbar scroll effect
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('nav');
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    });
</script>
