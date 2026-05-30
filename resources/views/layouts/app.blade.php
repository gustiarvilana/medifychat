<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Medify') }} - Admin Dashboard</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0..1" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-body-md text-on-surface antialiased flex min-h-screen bg-background">
        @include('layouts.navigation')

        <main class="flex-1 md:ml-[280px]">
            <!-- TopAppBar Component -->
            <header class="flex justify-between items-center w-full px-margin py-base sticky top-0 z-50 bg-surface-container-lowest border-b border-outline-variant">
                <div class="flex items-center gap-lg">
                    <span class="font-headline-lg text-headline-lg font-bold text-primary">{{ $header ?? 'Dashboard' }}</span>
                    <div class="relative hidden sm:block">
                        <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline">search</span>
                        <input class="pl-xl pr-md py-xs bg-surface-container-low border-none rounded-full text-body-sm focus:ring-2 focus:ring-primary w-64" placeholder="Search..." type="text"/>
                    </div>
                </div>
                <div class="flex items-center gap-md">
                    <button class="p-xs rounded-full hover:bg-surface-container-low transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                    <button class="p-xs rounded-full hover:bg-surface-container-low transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined">help</span>
                    </button>
                    <div class="flex items-center gap-xs ml-base p-xs pr-sm rounded-full hover:bg-surface-container-low cursor-pointer transition-colors" onclick="window.location.href='{{ route('profile.edit') }}'">
                        <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary font-bold text-sm shadow-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span class="font-body-sm font-semibold hidden lg:block">{{ Auth::user()->name }}</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-margin max-w-7xl mx-auto pb-24">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
