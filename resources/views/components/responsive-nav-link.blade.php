@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-cvsu dark:border-cvsu-400 text-start text-base font-medium text-cvsu dark:text-cvsu-400 bg-cvsu-50 dark:bg-gray-800 focus:outline-none focus:text-cvsu-800 dark:focus:text-cvsu-300 focus:bg-cvsu-100 dark:focus:bg-gray-700 focus:border-cvsu-700 dark:focus:border-cvsu-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-800 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
