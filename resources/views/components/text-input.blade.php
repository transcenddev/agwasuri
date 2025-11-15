@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-600 bg-white dark:!bg-gray-900 text-gray-900 dark:!text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-cvsu-500 focus:ring-cvsu-500 dark:focus:border-cvsu-400 dark:focus:ring-cvsu-400 rounded-md shadow-sm']) }}>
