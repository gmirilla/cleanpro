<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} Staff – {{ $title ?? 'Tasks' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
<div class="flex h-screen overflow-hidden">
    <aside class="hidden md:flex md:flex-shrink-0">
        <div class="flex flex-col w-60 bg-blue-900">
            <div class="flex items-center h-16 px-6 bg-blue-800 flex-shrink-0">
                <span class="text-white font-bold text-lg">🧹 Staff Portal</span>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1">
                @foreach([
                    ['route'=>'staff.dashboard','label'=>'Dashboard','icon'=>'📊'],
                    ['route'=>'staff.tasks','label'=>'My Tasks','icon'=>'✅'],
                ] as $item)
                    <a href="{{ route($item['route']) }}"
                       class="{{ request()->routeIs($item['route']) ? 'bg-blue-700 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <span class="mr-3">{{ $item['icon'] }}</span>{{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="p-4 border-t border-blue-700 flex-shrink-0">
                <p class="text-blue-200 text-sm truncate">{{ auth()->user()?->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-blue-300 text-xs hover:text-white mt-1">Logout</button>
                </form>
            </div>
        </div>
    </aside>
    <div class="flex flex-col flex-1 overflow-hidden">
        <header class="bg-white shadow-sm h-16 flex items-center px-6 flex-shrink-0">
            <h1 class="text-xl font-semibold text-gray-800">{{ $title ?? 'Tasks' }}</h1>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>
@livewireScripts
<div x-data="{ show:false, message:'', type:'success' }"
     x-on:notify.window="show=true; message=$event.detail[0]?.message??''; type=$event.detail[0]?.type??'success'; setTimeout(()=>show=false,4000)"
     x-show="show" x-transition style="display:none"
     class="fixed bottom-6 right-6 z-50 min-w-60">
    <div :class="{'bg-green-500':type==='success','bg-red-500':type==='error','bg-blue-500':type==='info'}"
         class="text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium" x-text="message"></div>
</div>
</body>
</html>
