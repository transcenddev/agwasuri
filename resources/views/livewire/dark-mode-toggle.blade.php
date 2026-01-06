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
        // default focus ring (can be overridden per-variant)
        $focusRing = 'focus:ring-2 focus:ring-white/50 dark:focus:ring-cvsu-500/40';

        if ($variant === 'green') {
            // Dashboard/nav variant: high-contrast green pill on light mode
            // - Light mode: use brand green background with white icons/text for strong contrast
            // - Dark mode: keep transparent background and use a lighter green text so it reads on dark
            $colorClass = 'text-white bg-cvsu dark:bg-transparent dark:text-cvsu-300';
            $hoverClass = 'hover:bg-cvsu-600 dark:hover:text-cvsu-200';
            $extraClasses = 'border border-transparent dark:border-gray-700';
            // stronger focus ring that uses the brand color with reduced opacity
            $focusRing = 'focus:ring-4 focus:ring-cvsu-300/25 dark:focus:ring-cvsu-500/30';
            $showLabel = false;
        } elseif ($variant === 'logo') {
            // Logo variant: keep high-contrast white by default (used on homepage header)
            $colorClass = 'text-white dark:text-white';
            $hoverClass = 'hover:text-emerald-400';
            $extraClasses = 'bg-transparent border-none shadow-none';
            $showLabel = false;
        } elseif ($variant === 'login') {
            // Login page: use the runtime `cvsu` color so it matches brand accent
            // in both light and dark themes.
            $colorClass = 'text-cvsu-500';
            $hoverClass = 'hover:text-cvsu-400';
            $extraClasses = 'bg-transparent border-none shadow-none';
            $showLabel = false;
        } else {
            $colorClass = 'text-white dark:text-gray-300';
            $hoverClass = 'hover:bg-white/20 dark:hover:bg-white/20 hover:text-white dark:hover:text-white hover:shadow-lg hover:backdrop-blur-sm';
            $extraClasses = '';
            $showLabel = false;
        }
        // Always prefer the nav-driven icon color; include nav background/border for the login variant.
        if ($variant === 'login') {
            $inlineStyle = 'style="background:var(--nav-bg); border-color:var(--nav-border); color:var(--nav-icon);"';
        } else {
            $inlineStyle = 'style="color:var(--nav-icon);"';
        }

        // For icons, default to the color class but override for specific variants
        // Dashboard ('green') uses a dark text color on light backgrounds for
        // improved legibility (text-gray-900) while keeping white icons in dark mode.
        $iconTextClass = $colorClass;
        if ($variant === 'green') {
            $iconTextClass = 'text-gray-900 dark:text-white';
        }
    @endphp

    <button
        data-role="theme-toggle-btn"
        type="button"
        data-variant="{{ $variant }}"
        onclick="window.toggleTheme && window.toggleTheme()"
        {!! $inlineStyle !!}
        class="relative inline-flex items-center justify-center w-10 h-10 transition-all duration-300 rounded-lg bg-transparent border-none shadow-none focus:outline-none {{ $focusRing }} {{ $colorClass }} {{ $hoverClass }} {{ $extraClasses }}"
        aria-label="Toggle dark mode"
        title="Toggle dark mode"
    >
        <!-- Sun Icon (Light Mode) -->
    <svg data-role="icon-sun" class="w-5 h-5 transition-all duration-300 opacity-100 scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>

        <!-- Render label so parent (sidebar) can toggle visibility via Alpine's `expanded` state -->
        <span class="text-sm whitespace-nowrap" x-show="(typeof expanded !== 'undefined' && expanded)" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Theme</span>

        <!-- Moon Icon (Dark Mode) -->
    <svg data-role="icon-moon" class="w-5 h-5 transition-all duration-300 opacity-0 scale-0 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg> 
    </button>
    <script>
    (function(){
        const buttons = document.querySelectorAll('[data-role="theme-toggle-btn"]');
        if (!buttons || buttons.length === 0) return;

        function initButton(btn) {
            const sun = btn.querySelector('[data-role="icon-sun"]');
            const moon = btn.querySelector('[data-role="icon-moon"]');

            // If the expected icons are missing, bail out to avoid null.classList errors
            if (!sun || !moon) return;

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

            // initial state: check shared localStorage key first, then document class
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

            // quick visual flip on click (immediate feedback)
            btn.addEventListener('click', () => {
                const cur = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                setIcons(cur);
            });

            // listen for theme change events from shared helper and update this button
            window.addEventListener('agwasuri:theme-changed', (e) => setIcons(e.detail));
        }

        buttons.forEach(initButton);
    })();
    </script>
</div>
