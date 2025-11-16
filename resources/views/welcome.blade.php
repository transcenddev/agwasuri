<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="relative font-sans antialiased bg-fixed bg-center bg-no-repeat bg-cover"
    style="background-image: url('{{ asset('assets/welcome_bg.webp') }}');">

        <livewire:welcome.navigation />

        <div class="relative max-w-screen-xl px-4 py-20 mx-auto lg:flex lg:h-screen lg:items-start lg:px-8">
            <div class="text-left ltr:sm:text-left rtl:sm:text-right">

                <h1 class="text-4xl font-black text-white sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl">
                    Monitor

                    <strong class="block font-black">Water Resources. </strong>
                </h1>

                <p class="max-w-xl mt-4 text-white sm:text-base/relaxed">
                    Monitor and maintain water quality with Agwasuri, your reliable water quality data monitoring system. Track key parameters like
                    temperature, salinity, dissolved oxygen,
                    and pH levels to ensure a healthier environment.
                </p>

                <div class="flex flex-wrap gap-4 mt-8 text-center">
                    <a href="#"
                        class="block w-full px-12 py-3 text-sm font-medium text-white rounded shadow bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring active:bg-sky-500 sm:w-auto">
                        Get Started
                    </a>

                    {{-- <a href="#"
                        class="block w-full px-12 py-3 text-sm font-medium bg-white rounded shadow text-sky-600 hover:text-sky-700 focus:outline-none focus:ring active:text-sky-500 sm:w-auto">
                        Learn More
                    </a> --}}
                </div>
            </div>
        </div>
</body>

</html>
