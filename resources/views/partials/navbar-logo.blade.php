<a href="{{ url('/') }}" wire:navigate class="flex items-center gap-2 h-10 text-2xl font-serif font-normal md:font-medium tracking-normal hover:opacity-80 transition-opacity" style="color:var(--nav-logo, #ffffff)">
    <img src="{{ asset('assets/agwasuri-logo.svg') }}" alt="AgwaSuri" class="w-10 h-10 object-contain" />
    <!-- Visible brand text (fixed height + stable line-height) -->
    <span class="nav-logo-text" aria-hidden="true">AgwaSuri</span>
    <span class="sr-only">Agwasuri home</span>
</a>
