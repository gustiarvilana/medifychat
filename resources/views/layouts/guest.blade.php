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
    <body class="font-body-md text-on-surface selection:bg-primary-container selection:text-white min-h-screen flex items-center justify-center p-md" style="background-color: #f8f9ff; background-image: radial-gradient(circle at 2px 2px, #e2e8f0 1px, transparent 0); background-size: 48px 48px;">
        <div class="medical-pattern overflow-hidden fixed top-0 left-0 w-full h-full pointer-events-none z-[-1] opacity-40">
            <svg class="absolute top-[-10%] right-[-5%] w-[40%] text-surface-container opacity-20" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path d="M44.7,-76.4C58.3,-69.2,70.1,-58.5,78.2,-45.5C86.3,-32.5,90.7,-17.2,90.1,-2.2C89.5,12.8,83.8,27.5,74.9,40.1C65.9,52.7,53.6,63.2,39.8,70.5C26,77.8,10.6,81.8,-4.2,88.9C-19,96.1,-33.1,106.4,-45.1,104.3C-57.2,102.2,-67.2,87.6,-74.6,73.4C-82.1,59.3,-87.1,45.6,-90.4,31.5C-93.7,17.4,-95.4,2.9,-92.8,-11.1C-90.2,-25.1,-83.4,-38.6,-73.4,-49.6C-63.5,-60.7,-50.4,-69.3,-37.1,-76.8C-23.8,-84.3,-10.3,-90.7,2.5,-94.9C15.3,-99.1,28.6,-101.2,44.7,-76.4Z" fill="currentColor" transform="translate(100 100)"></path>
            </svg>
            <svg class="absolute bottom-[-5%] left-[-10%] w-[35%] text-surface-container opacity-20" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path d="M39.9,-65.7C52.1,-59.1,62.6,-48.5,70.1,-35.9C77.6,-23.3,82.1,-8.7,80.8,5.4C79.5,19.5,72.4,33.1,62.8,44.4C53.2,55.7,41.2,64.7,27.8,70.4C14.4,76.1,-0.4,78.5,-15,76C-29.6,73.5,-44,66,-55,55.4C-66.1,44.8,-73.8,31.1,-77.8,16.4C-81.8,1.7,-82.1,-13.9,-77.4,-28.4C-72.7,-42.9,-62.9,-56.3,-50,-62.6C-37.1,-68.9,-21.1,-68.1,-4.9,-60.2C11.3,-52.3,27.7,-72.3,39.9,-65.7Z" fill="currentColor" transform="translate(100 100)"></path>
            </svg>
        </div>

        <main class="w-full max-w-[440px] z-10">
            <div class="bg-surface-container-lowest login-card rounded-xl border border-outline-variant p-xl flex flex-col items-center" style="box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);">
                <div class="flex items-center gap-base mb-xl">
                    <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-primary text-[28px]">medical_services</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-headline-lg text-headline-lg text-primary tracking-tight">Medify</span>
                        <span class="font-label-caps text-label-caps text-outline uppercase">Admin Dashboard</span>
                    </div>
                </div>

                {{ $slot }}
            </div>

            <div class="mt-xl flex flex-col items-center gap-md">
                <p class="font-label-caps text-label-caps text-outline tracking-widest text-center px-lg">
                    SECURE ACCESS &bull; ENCRYPTED SESSION &bull; END-TO-END DATA PROTECTION
                </p>
            </div>
        </main>
    </body>
</html>
