<div>
    @php
    // Support both direct include (e.g. @include) and Livewire tag attribute
    // - when included via @include('...',['variant'=>'login']) $variant will be set
    // - when rendered as <livewire:dark-mode-toggle variant="login" /> the attribute
    //   may be available via $attributes->get('variant') depending on Livewire render path
    // Prefer an explicitly provided $variant; fall back to the Livewire tag attribute or 'default'.
    // Use isset() to avoid undefined-variable notices when the view is rendered without $variant.
    // Prefer an explicitly provided $variant; if not provided try to read from $attributes
    // (guarded because some Livewire render paths don't expose $attributes), otherwise
    // derive a sensible default from the current request (login page -> 'login').
    $variant = isset($variant) ? $variant : (isset($attributes) ? ($attributes->get('variant') ?? null) : null);

    if (! $variant) {
        // If this is the login route/path, prefer the 'login' variant so the toggle
        // matches the navbar logo green even when Livewire didn't forward the attr.
        if (request()->is('login') || (function_exists('request') && request()->routeIs('login'))) {
            $variant = 'login';
        } else {
            $variant = 'default';
        }
    }

        // Determine color classes based on variant
        if ($variant === 'green') {
            // Green/nav variant: use navbar logo green in light mode and lighter green in dark mode
            $colorClass = 'text-[#1B651B] dark:text-[#47AF47]';
            $hoverClass = 'hover:text-[#47AF47]';
            $extraClasses = 'bg-transparent border-none shadow-none';
            $showLabel = false;
        } elseif ($variant === 'logo') {
            // Logo variant: keep high-contrast white by default (used on homepage header)
            $colorClass = 'text-white dark:text-white';
            $hoverClass = 'hover:text-emerald-400';
            $extraClasses = 'bg-transparent border-none shadow-none';
            $showLabel = false;
        } elseif ($variant === 'login') {
            // Login page: simple style like the homepage header but use the
            // navbar green for the icon so it matches the brand color on login.
            $colorClass = 'text-[#1B651B] dark:text-[#47AF47]';
            $hoverClass = 'hover:text-[#47AF47]';
            $extraClasses = 'bg-transparent border-none shadow-none';
            $showLabel = false;
        } else {
            $colorClass = 'text-white dark:text-gray-300';
            $hoverClass = 'hover:bg-white/20 dark:hover:bg-white/20 hover:text-white dark:hover:text-white hover:shadow-lg hover:backdrop-blur-sm';
            $extraClasses = '';
            $showLabel = false;
        }
        $inlineStyle = $variant === 'login' ? 'style="background:var(--nav-bg); border-color:var(--nav-border);"' : '';

        // For icons, when this is the login variant apply the color class so
        // stroke="currentColor" resolves to the correct icon color. For the
        // login pill we force white so the icon contrasts the brand background.
        $iconTextClass = $variant === 'login' ? $colorClass : $colorClass;
    @endphp

    <button
        id="theme-toggle-btn"
        type="button"
        data-variant="{{ $variant }}"
        onclick="window.toggleTheme && window.toggleTheme()"
        class="relative inline-flex items-center justify-center w-10 h-10 transition-all duration-300 rounded-lg bg-transparent border-none shadow-none focus:outline-none focus:ring-2 focus:ring-white/50 dark:focus:ring-cvsu-500 {{ $colorClass }} {{ $hoverClass }} {{ $extraClasses }}"
        aria-label="Toggle dark mode"
        title="Toggle dark mode"
    >
        <!-- Sun Icon (Light Mode) -->
    <svg data-role="icon-sun" class="w-5 h-5 transition-all duration-300 opacity-100 scale-100 {{ $iconTextClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>

        @if($showLabel)
            <span class="ml-2 hidden md:inline text-sm font-medium text-gray-700 dark:text-gray-300">Theme</span>
        @endif

        <!-- Moon Icon (Dark Mode) -->
    <svg data-role="icon-moon" class="w-5 h-5 transition-all duration-300 opacity-0 scale-0 absolute {{ $iconTextClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>

    <script>
    (function(){
        const btn = document.getElementById('theme-toggle-btn');
        if (!btn) return;

        const sun = btn.querySelector('[data-role="icon-sun"]');
        const moon = btn.querySelector('[data-role="icon-moon"]');

        function setIcons(theme) {
            const dark = theme === 'dark' || (!theme && document.documentElement.classList.contains('dark'));
            if (dark) {
                sun.classList.remove('opacity-100','scale-100');
                sun.classList.add('opacity-0','scale-0','absolute');
                moon.classList.remove('opacity-0','scale-0','absolute');
                moon.classList.add('opacity-100','scale-100');
                btn.setAttribute('title','Switch to Light Mode');
                btn.setAttribute('aria-pressed','true');
            } else {
                moon.classList.remove('opacity-100','scale-100');
                moon.classList.add('opacity-0','scale-0','absolute');
                sun.classList.remove('opacity-0','scale-0','absolute');
                sun.classList.add('opacity-100','scale-100');
                btn.setAttribute('title','Switch to Dark Mode');
                btn.setAttribute('aria-pressed','false');
            }
        }

        // initial state: check localStorage first, then document class
        const saved = localStorage.getItem('agwasuri_theme');
        setIcons(saved);

        // listen for theme change events
        window.addEventListener('agwasuri:theme-changed', (e) => setIcons(e.detail));

        // quick visual flip on click (immediate feedback)
        btn.addEventListener('click', () => {
            // determine what the next visual state should be (we've already toggled theme via toggleTheme)
            const cur = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            setIcons(cur);
        });
    })();
    </script>

    <script>
    (function(){
        const btn = document.getElementById('theme-toggle-btn');
        if (!btn) return;

        const sun = btn.querySelector('[data-role="icon-sun"]');
        const moon = btn.querySelector('[data-role="icon-moon"]');

        function setIcons(theme) {
            const dark = theme === 'dark' || (!theme && document.documentElement.classList.contains('dark'));
            if (dark) {
                sun.classList.remove('opacity-100','scale-100');
                sun.classList.add('opacity-0','scale-0','absolute');
                moon.classList.remove('opacity-0','scale-0','absolute');
                moon.classList.add('opacity-100','scale-100');
                btn.setAttribute('title','Switch to Light Mode');
                btn.setAttribute('aria-pressed','true');
            } else {
                moon.classList.remove('opacity-100','scale-100');
                moon.classList.add('opacity-0','scale-0','absolute');
                sun.classList.remove('opacity-0','scale-0','absolute');
                sun.classList.add('opacity-100','scale-100');
                btn.setAttribute('title','Switch to Dark Mode');
                btn.setAttribute('aria-pressed','false');
            }
        }

        // initial state: check localStorage first, then document class
        const saved = localStorage.getItem('agwasuri_theme');
        setIcons(saved);

        // If this is the login variant, show a one-time pulse hint to draw attention
        try {
            const isLogin = btn.dataset.variant === 'login';
            if (isLogin && !localStorage.getItem('themeHintShown')) {
                btn.classList.add('animate-pulse');
                setTimeout(() => {
                    btn.classList.remove('animate-pulse');
                    localStorage.setItem('themeHintShown', '1');
                }, 1600);
            }
        } catch (e) {}

        // listen for theme change events
        window.addEventListener('agwasuri:theme-changed', (e) => setIcons(e.detail));

        // quick visual flip on click (immediate feedback)
        btn.addEventListener('click', () => {
            // determine what the next visual state should be (we've already toggled theme via toggleTheme)
            const cur = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            setIcons(cur);
        });
    })();
    </script>
</div>
