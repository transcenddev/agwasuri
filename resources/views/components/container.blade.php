@php
    $passedClasses = $attributes->get('class') ?? '';
    $hasPt = preg_match('/\bpt-/', $passedClasses);
@endphp
<div {{ $attributes->merge(['class' => 'mx-auto w-full px-4 sm:px-6 lg:px-8']) }} @if(!$hasPt) style="padding-top:var(--header-height,4rem)" @endif>
    <div class="max-w-content-xl mx-auto">
        {{ $slot }}
    </div>
</div>
