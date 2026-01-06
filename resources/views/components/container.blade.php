@php
    $passedClasses = $attributes->get('class') ?? '';
    $hasPt = preg_match('/\bpt-/', $passedClasses);
@endphp
<div {{ $attributes->merge(['class' => 'mx-auto w-full px-4 sm:px-6 lg:px-8 sm:max-w-3xl md:max-w-4xl lg:max-w-7xl xl:max-w-screen-xl']) }} @if(!$hasPt) style="padding-top:var(--header-height,4rem)" @endif>
    <div class="w-full">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
