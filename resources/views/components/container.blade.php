@php
    $passedClasses = $attributes->get('class') ?? '';
    $hasPt = preg_match('/\bpt-/', $passedClasses);
@endphp
<div {{ $attributes->merge(['class' => 'mx-auto w-full px-4 sm:px-6 lg:px-8']) }} @if(!$hasPt) style="padding-top:var(--header-height,4rem)" @endif>
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
