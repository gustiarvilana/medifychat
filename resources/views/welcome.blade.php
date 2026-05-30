<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Medify Chat - Presisi Klinis, Kenyamanan Pasien</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        :root {
            --primary: #3755c3;
            --secondary: #006c49;
            --surface: #f8f9ff;
            --on-surface: #0b1c30;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--surface); color: var(--on-surface); }
    </style>
</head>
<body class="antialiased bg-[#f8f9ff] min-h-screen">
    <!-- Header -->
    <header class="p-6 md:px-12 flex justify-between items-center max-w-7xl mx-auto">
        <div class="text-2xl font-bold tracking-tight text-[var(--primary)]">Medify<span class="text-[var(--secondary)]">Chat</span></div>
        <nav class="flex items-center gap-6">
            @auth
                <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-[var(--primary)] hover:bg-[#2d46a3] text-white rounded-md text-sm font-semibold transition-all">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-[#444653] hover:text-[var(--primary)] transition-colors">Masuk</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-5 py-2.5 bg-[var(--primary)] hover:bg-[#2d46a3] text-white rounded-md text-sm font-semibold transition-all">Daftar</a>
                @endif
            @endauth
        </nav>
    </header>

    <!-- Hero Section -->
    <main class="max-w-5xl mx-auto px-6 py-16 md:py-24 text-center">
        <h1 class="text-5xl md:text-7xl font-bold tracking-tighter mb-8 text-[var(--on-surface)]">
            Presisi Klinis,<br />
            <span class="text-[var(--primary)]">Kenyamanan Pasien</span>
        </h1>
        <p class="text-xl md:text-2xl text-[#444653] mb-12 max-w-2xl mx-auto leading-relaxed">
            Merevolusi pendaftaran pasien di Rumah Sakit Medify melalui sistem terintegrasi WhatsApp AI Bot dan manajemen SIMRS.
        </p>
        <div class="flex gap-4 justify-center">
            @auth
                <a href="{{ url('/dashboard') }}" class="px-8 py-4 bg-[var(--primary)] hover:bg-[#2d46a3] text-white rounded-lg font-bold text-lg transition-all shadow-lg hover:shadow-xl">Buka Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-8 py-4 bg-[var(--primary)] hover:bg-[#2d46a3] text-white rounded-lg font-bold text-lg transition-all shadow-lg hover:shadow-xl ring-2 ring-[var(--primary)] ring-offset-2">Mulai Sekarang</a>
            @endauth
        </div>
    </main>

    <!-- Features Section -->
    <section class="max-w-7xl mx-auto px-6 py-12 md:py-20 grid md:grid-cols-3 gap-8">
        <div class="p-8 bg-white rounded-lg border border-[#e2e8f0] shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-[#f1f5f9] rounded-lg mb-6 flex items-center justify-center text-[var(--primary)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
            </div>
            <h3 class="text-lg font-bold mb-2">WhatsApp Bot Cerdas</h3>
            <p class="text-[#444653] leading-relaxed">Pendaftaran pasien otomatis dan pencarian jadwal dokter menggunakan bahasa alami.</p>
        </div>
        <div class="p-8 bg-white rounded-lg border border-[#e2e8f0] shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-[#f1f5f9] rounded-lg mb-6 flex items-center justify-center text-[var(--primary)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <h3 class="text-lg font-bold mb-2">Integrasi SIMRS</h3>
            <p class="text-[#444653] leading-relaxed">Sinkronisasi waktu nyata dengan sistem inti Rumah Sakit Medify untuk ketersediaan tempat tidur dan janji temu.</p>
        </div>
        <div class="p-8 bg-white rounded-lg border border-[#e2e8f0] shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-[#f1f5f9] rounded-lg mb-6 flex items-center justify-center text-[var(--primary)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
            </div>
            <h3 class="text-lg font-bold mb-2">Pusat Kendali</h3>
            <p class="text-[#444653] leading-relaxed">Kendali penuh atas proses bot, pemantauan status, dan log aktivitas waktu nyata.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-12 text-sm text-[#757684]">
        &copy; {{ date('Y') }} Rumah Sakit Medify. Hak cipta dilindungi undang-undang.
    </footer>
</body>
</html>
