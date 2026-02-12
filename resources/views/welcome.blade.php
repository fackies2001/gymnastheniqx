<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite('resources/css/app.css')
</head>

<body class="antialiased">
    <div class="sticky top-0 z-100 bg-violet-700 px-10 py-3 text-violet-200"><small>Beta.1</small></div>
    <nav class="sticky top-0 z-50 bg-violet-700 flex justify-between items-center text-violet-200 px-10 py-3 shadow">
        <div class="logo">
            <img src="{{ asset('logo.png') }}" alt="g-logo" class="h-10">
        </div>
        <ul class="flex gap-2">
            <li class="hover:text-white cursor-pointer">Contact Us</li>
            <span>|</span>
            <li class="hover:text-white cursor-pointer">Contact Us</li>
            <span>|</span>
            <li class="hover:text-white cursor-pointer">Demo</li>
            <span>|</span>
            <li class="hover:text-white cursor-pointer">Sign-in</li>
        </ul>
    </nav>
    <div class="container mx-auto w-[90vw]">
        <div class="relative flex gap-10 items-top justify-center min-h-screen sm:items-center py-4 sm:pt-0">

            <div class="w-auto flex justify-center items-center"><img class="rounded-md"
                    src="{{ asset('layout-1.jpg') }}" alt="layout">
            </div>
            <div>
                <div class="flex justify-center pt-8 sm:justify-start sm:pt-0 mb-3">
                    <h1 class="text-5xl font-bold text-violet-600">Take Control of Your Gym Inventory with Ease</h1>
                </div>
                <div class="leading-loose text-violet-600">
                    Gymnastheniqx is an all-in-one inventory management system built specifically for gyms and fitness
                    centers.
                </div>
            </div>

        </div>
    </div>
</body>

</html>
