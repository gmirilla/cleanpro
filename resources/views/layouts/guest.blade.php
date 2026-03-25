<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{--
            ┌─────────────────────────────────────────────────────────┐
            │  CRITICAL CSS ONLY — nothing render-blocking beyond     │
            │  this point in <head>                                   │
            └─────────────────────────────────────────────────────────┘
        --}}

    {{-- 1. Preconnect so the DNS + TLS handshake starts immediately --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>

    {{-- 2. Preload the font stylesheet so it's fetched with high priority --}}
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap">

    {{-- 3. Load font stylesheet asynchronously — onload swaps media to 'all'
                 so it never blocks the initial render --}}
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" media="print"
        onload="this.media='all'">

    {{-- 4. Fallback for browsers with JS disabled --}}
    <noscript>
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap">
    </noscript>

    {{-- 5. CSS only — Vite injects app.css as a <link> which is non-blocking.
                 app.js is intentionally excluded here; it is loaded at end of <body>. --}}
    @vite(['resources/css/app.css'])

    {{-- 6. Livewire styles are inline <style> tags — safe to keep in <head> --}}
    @livewireStyles
</head>

<body class="font-sans text-gray-900 antialiased">

<div class="min-h-screen flex bg-gray-50">

    <!-- LEFT: Branding / Banner -->
    <div class="hidden lg:flex w-1/2 relative overflow-hidden">

        <!-- Background Image -->
        <img src="{{ asset('assets/img/Chibi laundry staff.webp') }}"
             class="absolute inset-0 w-full h-full object-cover" />

        <!-- Overlay Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-700/80 to-green-600/80"></div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col justify-between p-12 text-white w-full">

            <!-- Top: Logo -->
            <div class="flex items-center gap-3">
                <x-application-logo class="w-10 h-10 fill-white" />
                <span class="text-xl font-semibold tracking-wide">
                    SpringCleaning
                </span>
            </div>

            <!-- Middle: Tagline -->
            <div class="max-w-md backdrop-blur-md bg-white/10 border border-white/20 p-6 rounded-2xl shadow-lg">
                <h2 class="text-4xl font-bold leading-tight mb-4">
                    Clean Spaces.<br>Healthy Living.
                </h2>
                <p class="text-base text-white/90">
                    Book cleaning and laundry services with ease. Trusted professionals delivering spotless results every time.
                </p>
            </div>

            <!-- Bottom -->
            <div class="text-sm text-white/80">
                © {{ date('Y') }} Spring Cleaning Service
            </div>

        </div>
    </div>


    <!-- RIGHT: Auth Content -->
    <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12">

        <div class="w-full max-w-md">

            <!-- Mobile Header -->
            <div class="lg:hidden mb-8 text-center">
                <x-application-logo class="w-14 h-14 mx-auto fill-blue-600" />
                <h1 class="mt-3 text-xl font-bold text-gray-800">
                    SpringCleaning
                </h1>
                <p class="text-sm text-gray-500">
                    Professional Cleaning Services
                </p>
            </div>

            <!-- Card -->
            <div class="bg-white/90 backdrop-blur-md shadow-xl rounded-2xl px-8 py-8 border border-gray-100">

                {{ $slot }}

            </div>

        </div>
    </div>

</div>

    {{--
            ┌─────────────────────────────────────────────────────────┐
            │  JS AT END OF BODY                                      │
            │  Vite emits <script type="module"> which is deferred    │
            │  by the browser spec, but placing it here makes the     │
            │  intent explicit and avoids any parser blocking.        │
            └─────────────────────────────────────────────────────────┘
        --}}
    @vite(['resources/js/app.js'])

    {{-- Livewire scripts must come after app.js so Alpine is already loaded --}}
    @livewireScripts

</body>

</html>
