<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-lg shadow-card ring-1 ring-slate-100 dark:ring-slate-700 overflow-hidden p-4 sm:p-4']) }}>
    @if (isset($header))
        <div class="mb-3">
            {{ $header }}
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="mt-3">
            {{ $footer }}
        </div>
    @endif
</div>
