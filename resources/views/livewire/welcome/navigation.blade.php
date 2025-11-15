<nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 backdrop-blur-md bg-white/10 dark:bg-gray-900/30 border-b border-white/20 dark:border-gray-700/30 px-6 py-0" style="--nav-logo: #ffffff">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between h-16">
            <!-- Logo (shared partial) -->
            @include('partials.navbar-logo')

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
                    class="px-5 py-2 text-sm font-medium text-white/80 transition-all duration-200 rounded-lg border border-white/30 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/50 backdrop-blur-sm hover:scale-105 transform">
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
