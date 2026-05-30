<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="w-full mb-8 text-center">
        <h1 class="font-bold text-2xl text-[#0b1c30] mb-2">Selamat Datang Kembali</h1>
        <p class="text-[#444653]">Silakan masuk menggunakan kredensial klinis Anda untuk mengakses sistem.</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="w-full space-y-6">
        @csrf

        <!-- Email Field -->
        <div class="space-y-2">
            <label class="font-semibold text-[#0b1c30]" for="login">Email atau Nama Pengguna</label>
            <div class="relative group">
                <input class="w-full px-4 py-3 bg-[#f8f9ff] border border-[#cbd5e1] rounded-lg text-[#0b1c30] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#3755c3] focus:border-[#3755c3] transition-all" 
                    id="login" name="login" value="{{ old('login') }}" placeholder="admin@medify.hospital" type="text" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2 text-[#ba1a1a]" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-[#ba1a1a]" />
        </div>

        <!-- Password Field -->
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <label class="font-semibold text-[#0b1c30]" for="password">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-sm text-[#3755c3] hover:underline font-semibold" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                @endif
            </div>
            <div class="relative group">
                <input class="w-full px-4 py-3 bg-[#f8f9ff] border border-[#cbd5e1] rounded-lg text-[#0b1c30] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#3755c3] focus:border-[#3755c3] transition-all pr-12" 
                    id="password" name="password" placeholder="••••••••" type="password" required autocomplete="current-password" />
                <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#94a3b8] hover:text-[#3755c3]">
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="eye-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path id="eye-outline-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-[#ba1a1a]" />
        </div>

        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const eyePath = document.getElementById('eye-path');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyePath.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.325-3.675M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 6l3 3m-3-3l-3 3m3-3V9');
                } else {
                    passwordInput.type = 'password';
                    eyePath.setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
                }
            }
        </script>

        <!-- Remember Me -->
        <div class="flex items-center gap-2">
            <input class="w-5 h-5 rounded border-[#cbd5e1] text-[#3755c3] focus:ring-[#3755c3] transition-all cursor-pointer" id="remember" name="remember" type="checkbox"/>
            <label class="text-[#444653] cursor-pointer select-none" for="remember">Ingat saya di perangkat ini</label>
        </div>

        <!-- Sign In Button -->
        <button class="w-full py-4 bg-[#3755c3] text-white font-bold rounded-lg hover:bg-[#2d46a3] transition-all duration-200 shadow-sm flex items-center justify-center gap-2" type="submit">
            Masuk
        </button>
    </form>

    <!-- Secondary Actions -->
    <div class="mt-8 pt-6 border-t border-[#e2e8f0] w-full text-center">
        <p class="text-[#444653]">
            Belum punya akun? 
            <a class="text-[#3755c3] font-bold hover:underline transition-all" href="{{ route('register') }}">Daftar di sini</a>
        </p>
    </div>
</x-guest-layout>
