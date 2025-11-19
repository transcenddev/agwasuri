<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-lg shadow-card ring-1 ring-slate-100 dark:ring-slate-700 overflow-hidden']) }}>
    @if (isset($header))
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                {{ $header }}
            </div>
            @if (isset($actions))
                <div class="ml-4">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
            {{ $footer }}
        </div>
    @endif
</div>
