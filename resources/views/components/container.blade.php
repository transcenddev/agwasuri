<div {{ $attributes->merge(['class' => 'mx-auto w-full px-4 sm:px-6 lg:px-8']) }} style="padding-top:var(--header-height,4rem)">
    <div class="max-w-content-xl mx-auto">
        {{ $slot }}
    </div>
</div>
