<div {{ $attributes->merge(['class' => 'bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] rounded-[var(--radius)] shadow-md overflow-hidden p-4 sm:p-4']) }}>
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
