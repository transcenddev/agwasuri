<div {{ $attributes->merge(['class' => 'flex items-center justify-between']) }}>
    <div class="text-lg font-semibold text-slate-900 dark:text-slate-100 min-w-0">
        {{ $title ?? $slot }}
    </div>
    @if(isset($actions))
        <div class="ml-4 flex-shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
