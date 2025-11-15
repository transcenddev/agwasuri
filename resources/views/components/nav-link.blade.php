@props(['active'])

@php
$classes = ($active ?? false)
            ? 'relative inline-flex items-center px-4 h-full text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none transition duration-150 ease-in-out after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-cvsu dark:after:bg-cvsu-400 after:rounded-t-sm'
            : 'relative inline-flex items-center px-4 h-full text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:text-gray-700 dark:focus:text-gray-200 transition duration-150 ease-in-out after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-transparent hover:after:bg-gray-300 dark:hover:after:bg-gray-600 after:rounded-t-sm after:transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
