<nav class="flex items-center justify-between flex-1 max-w-screen-xl px-4 mx-auto sm:px-6 lg:px-8 py-14">
    <div>
        <a href="{{ url('/') }}">
            <x-application-logo class="h-9 w-28" />
        </a>
    </div>

    <div>
        @auth
        <a href="{{ url('/dashboard') }}"
            class="rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
            Dashboard
        </a>
        @else

        <div class="flex items-center">
            <img src="{{ asset('assets/login_arrow.svg') }}" alt="" class="w-24 h-2.5">

            <a href="{{ route('login') }}"
                class="rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                Login
            </a>
        </div>
        @endauth
    </div>
</nav>
