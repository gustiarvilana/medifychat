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
        <style>
            body {
                background-color: #f8f9ff;
                background-image: radial-gradient(circle at 2px 2px, #e2e8f0 1px, transparent 0);
                background-size: 48px 48px;
            }
            .medical-pattern {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: -1;
                opacity: 0.4;
            }
            .login-card {
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            }
        </style>
    </head>
    <body class="font-body-md text-on-surface selection:bg-primary-container selection:text-white min-h-screen flex items-center justify-center p-md">
        <!-- Abstract Medical Themed Background Elements -->
        <div class="medical-pattern overflow-hidden">
            <svg class="absolute top-[-10%] right-[-5%] w-[40%] text-surface-container opacity-20" viewbox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path d="M44.7,-76.4C58.3,-69.2,70.1,-58.5,78.2,-45.5C86.3,-32.5,90.7,-17.2,90.1,-2.2C89.5,12.8,83.8,27.5,74.9,40.1C65.9,52.7,53.6,63.2,39.8,70.5C26,77.8,10.6,81.8,-4.2,88.9C-19,96.1,-33.1,106.4,-45.1,104.3C-57.2,102.2,-67.2,87.6,-74.6,73.4C-82.1,59.3,-87.1,45.6,-90.4,31.5C-93.7,17.4,-95.4,2.9,-92.8,-11.1C-90.2,-25.1,-83.4,-38.6,-73.4,-49.6C-63.5,-60.7,-50.4,-69.3,-37.1,-76.8C-23.8,-84.3,-10.3,-90.7,2.5,-94.9C15.3,-99.1,28.6,-101.2,44.7,-76.4Z" fill="currentColor" transform="translate(100 100)"></path>
            </svg>
            <svg class="absolute bottom-[-5%] left-[-10%] w-[35%] text-surface-container opacity-20" viewbox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path d="M39.9,-65.7C52.1,-59.1,62.6,-48.5,70.1,-35.9C77.6,-23.3,82.1,-8.7,80.8,5.4C79.5,19.5,72.4,33.1,62.8,44.4C53.2,55.7,41.2,64.7,27.8,70.4C14.4,76.1,-0.4,78.5,-15,76C-29.6,73.5,-44,66,-55,55.4C-66.1,44.8,-73.8,31.1,-77.8,16.4C-81.8,1.7,-82.1,-13.9,-77.4,-28.4C-72.7,-42.9,-62.9,-56.3,-50,-62.6C-37.1,-68.9,-21.1,-68.1,-4.9,-60.2C11.3,-52.3,27.7,-72.3,39.9,-65.7Z" fill="currentColor" transform="translate(100 100)"></path>
            </svg>
        </div>

        <main class="w-full max-w-[480px] z-10">
            <!-- Brand Identity Header -->
            <div class="text-center space-y-xs mb-lg">
                <div class="flex items-center justify-center gap-base text-primary">
                    <span class="material-symbols-outlined text-[40px]" style="font-variation-settings: 'FILL' 1;">medical_services</span>
                    <h1 class="font-headline-lg text-headline-lg font-bold tracking-tight">Medify Admin</h1>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Healthcare Enterprise Management</p>
            </div>

            <div class="bg-surface-container-lowest login-card rounded-xl border border-outline-variant p-xl flex flex-col items-center">
                {{ $slot }}
            </div>

            <!-- Footer / Trust Badges -->
            <div class="mt-xl flex flex-col items-center gap-md">
                <div class="flex items-center gap-lg opacity-40 grayscale hover:grayscale-0 transition-all duration-500">
                    <span class="flex items-center gap-xs font-label-caps text-label-caps text-on-surface-variant">
                        <span class="material-symbols-outlined text-[14px]">verified_user</span>
                        HIPAA COMPLIANT
                    </span>
                    <span class="flex items-center gap-xs font-label-caps text-label-caps text-on-surface-variant">
                        <span class="material-symbols-outlined text-[14px]">lock</span>
                        256-BIT AES
                    </span>
                </div>
                <p class="font-label-caps text-label-caps text-outline tracking-widest text-center px-lg">
                    SECURE ACCESS &bull; ENCRYPTED SESSION &bull; END-TO-END DATA PROTECTION
                </p>
                <p class="font-code-sm text-code-sm text-on-surface-variant opacity-60">© {{ date('Y') }} MedBot Clinical Systems. All rights reserved.</p>
            </div>
        </main>
    </body>
</html>
