<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} – {{ $title ?? 'My Account' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
<nav class="bg-white shadow-sm sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('customer.dashboard') }}" class="font-bold text-indigo-600 text-lg">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="h-10 w-10"></a>
            <div class="flex items-center gap-6 text-sm">
                <a href="{{ route('customer.dashboard') }}" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                <a href="{{ route('customer.bookings') }}" class="text-gray-600 hover:text-indigo-600">Bookings</a>
                <a href="{{ route('customer.invoices') }}" class="text-gray-600 hover:text-indigo-600">Invoices</a>
                <a href="{{ route('customer.book') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm">Book Now</a>
                <form method="POST" action="{{ route('logout') }}"> <!-- Need to fix Logout -->
                    @csrf
                    <button class="text-gray-500 hover:text-gray-700 text-sm">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif
    {{ $slot }}
</main>
@livewireScripts
<div x-data="{ show:false, message:'', type:'success' }"
     x-on:notify.window="show=true; message=$event.detail[0]?.message??''; type=$event.detail[0]?.type??'success'; setTimeout(()=>show=false,4000)"
     x-show="show" x-transition style="display:none"
     class="fixed bottom-6 right-6 z-50 min-w-60">
    <div :class="{'bg-green-500':type==='success','bg-red-500':type==='error','bg-indigo-500':type==='info'}"
         class="text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium" x-text="message"></div>
</div>
</body>
</html>
