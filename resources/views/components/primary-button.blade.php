<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-cvsu border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-cvsu-700 focus:bg-cvsu-700 active:bg-cvsu-900 focus:outline-none focus:ring-2 focus:ring-cvsu-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
