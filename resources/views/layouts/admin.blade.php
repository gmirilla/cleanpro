<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} – {{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
<div class="flex h-screen overflow-hidden">
    <aside class="hidden md:flex md:flex-shrink-0">
        <div class="flex flex-col w-64 bg-gray-900">
            <div class="flex items-center h-16 px-6 bg-gray-800 flex-shrink-0"><img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="h-10 w-10"> 
                <span class="text-white font-bold text-lg">Spring Cleaning Services</span>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @php
                    $nav = [
                        ['route'=>'admin.dashboard',       'label'=>'Dashboard',      'icon'=>'📊'],
                        ['route'=>'admin.bookings',        'label'=>'Bookings',       'icon'=>'📋'],
                        ['route'=>'admin.calendar',        'label'=>'Calendar',       'icon'=>'📅'],
                        ['route'=>'admin.customers',       'label'=>'Customers',      'icon'=>'👥'],
                        ['route'=>'admin.staff',           'label'=>'Staff',          'icon'=>'👷'],
                        ['route'=>'admin.services',        'label'=>'Services',       'icon'=>'🧺'],
                        ['route'=>'admin.laundry-orders',  'label'=>'Laundry Orders', 'icon'=>'👕'],
                        ['route'=>'admin.invoices',        'label'=>'Invoices',       'icon'=>'🧾'],
                        ['route'=>'admin.reports',         'label'=>'Reports',        'icon'=>'📈'],
                    ];
                @endphp
                @foreach($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="{{ request()->routeIs($item['route']) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <span class="mr-3">{{ $item['icon'] }}</span>{{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="flex-shrink-0 p-4 border-t border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ str_replace('_',' ', auth()->user()?->role ?? '') }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-white text-xs">Out</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex flex-col flex-1 overflow-hidden">
        <header class="bg-white shadow-sm z-10 flex-shrink-0">
            <div class="flex items-center justify-between h-16 px-6">
                <h1 class="text-xl font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
                <span class="text-sm text-gray-400">{{ auth()->user()?->email }}</span>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts

<div x-data="{ show:false, message:'', type:'success' }"
     x-on:notify.window="show=true; message=$event.detail[0]?.message??''; type=$event.detail[0]?.type??'success'; setTimeout(()=>show=false,4000)"
     x-show="show" x-transition style="display:none"
     class="fixed bottom-6 right-6 z-50 min-w-60">
    <div :class="{'bg-green-500':type==='success','bg-red-500':type==='error','bg-blue-500':type==='info','bg-yellow-500':type==='warning'}"
         class="text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium" x-text="message"></div>
</div>
</body>
</html>
