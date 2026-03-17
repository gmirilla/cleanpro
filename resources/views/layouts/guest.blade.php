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
        <link rel="preload"
              as="style"
              href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap">

        {{-- 3. Load font stylesheet asynchronously — onload swaps media to 'all'
                 so it never blocks the initial render --}}
        <link rel="stylesheet"
              href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
              media="print"
              onload="this.media='all'">

        {{-- 4. Fallback for browsers with JS disabled --}}
        <noscript>
            <link rel="stylesheet"
                  href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap">
        </noscript>

        {{-- 5. CSS only — Vite injects app.css as a <link> which is non-blocking.
                 app.js is intentionally excluded here; it is loaded at end of <body>. --}}
        @vite(['resources/css/app.css'])

        {{-- 6. Livewire styles are inline <style> tags — safe to keep in <head> --}}
        @livewireStyles
    </head>

    <body class="font-sans text-gray-900 antialiased">

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
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
