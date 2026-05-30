<x-guest-layout>
    <div class="w-full mb-8 text-center">
        <h1 class="font-bold text-2xl text-[#0b1c30] mb-2">Buat Akun Anda</h1>
        <p class="text-[#444653]">Masukkan kredensial klinis Anda untuk memulai.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="w-full space-y-6">
        @csrf

        <!-- Full Name Field -->
        <div class="space-y-2">
            <label class="font-semibold text-[#0b1c30]" for="name">Nama Lengkap</label>
            <input class="w-full px-4 py-3 bg-[#f8f9ff] border border-[#cbd5e1] rounded-lg text-[#0b1c30] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#3755c3] focus:border-[#3755c3] transition-all" 
                id="name" name="name" value="{{ old('name') }}" placeholder="John Doe" type="text" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-[#ba1a1a]" />
        </div>

        <!-- Username Field -->
        <div class="space-y-2">
            <label class="font-semibold text-[#0b1c30]" for="username">Nama Pengguna</label>
            <input class="w-full px-4 py-3 bg-[#f8f9ff] border border-[#cbd5e1] rounded-lg text-[#0b1c30] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#3755c3] focus:border-[#3755c3] transition-all" 
                id="username" name="username" value="{{ old('username') }}" placeholder="admin" type="text" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2 text-[#ba1a1a]" />
        </div>

        <!-- Email Field -->
        <div class="space-y-2">
            <label class="font-semibold text-[#0b1c30]" for="email">Alamat Email</label>
            <input class="w-full px-4 py-3 bg-[#f8f9ff] border border-[#cbd5e1] rounded-lg text-[#0b1c30] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#3755c3] focus:border-[#3755c3] transition-all" 
                id="email" name="email" value="{{ old('email') }}" placeholder="admin@medify.hospital" type="email" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-[#ba1a1a]" />
        </div>

        <!-- Password Field -->
        <div class="space-y-2">
            <label class="font-semibold text-[#0b1c30]" for="password">Kata Sandi</label>
            <div class="relative">
                <input class="w-full px-4 py-3 bg-[#f8f9ff] border border-[#cbd5e1] rounded-lg text-[#0b1c30] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#3755c3] focus:border-[#3755c3] transition-all pr-12" 
                    id="password" name="password" placeholder="••••••••" type="password" required autocomplete="new-password" />
                <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#94a3b8] hover:text-[#3755c3]">
                    <svg id="eye-icon-password" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="eye-path-password" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-[#ba1a1a]" />
        </div>

        <!-- Confirm Password Field -->
        <div class="space-y-2">
            <label class="font-semibold text-[#0b1c30]" for="password_confirmation">Konfirmasi Kata Sandi</label>
            <div class="relative">
                <input class="w-full px-4 py-3 bg-[#f8f9ff] border border-[#cbd5e1] rounded-lg text-[#0b1c30] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#3755c3] focus:border-[#3755c3] transition-all pr-12" 
                    id="password_confirmation" name="password_confirmation" placeholder="••••••••" type="password" required autocomplete="new-password" />
                <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#94a3b8] hover:text-[#3755c3]">
                    <svg id="eye-icon-password_confirmation" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="eye-path-password_confirmation" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-[#ba1a1a]" />
        </div>

        <!-- Terms and Conditions -->
        <div class="flex items-start gap-2 py-2">
            <input class="mt-1 w-5 h-5 rounded border-[#cbd5e1] text-[#3755c3] focus:ring-[#3755c3] cursor-pointer" id="terms" type="checkbox" required />
            <label class="text-[#444653] leading-tight" for="terms">
                Saya setuju dengan <a class="text-[#3755c3] font-semibold hover:underline" href="#">Ketentuan Layanan</a> dan <a class="text-[#3755c3] font-semibold hover:underline" href="#">Kebijakan Privasi</a>.
            </label>
        </div>

        <!-- Primary Action -->
        <button class="w-full py-4 bg-[#3755c3] text-white font-bold rounded-lg hover:bg-[#2d46a3] transition-all duration-200 shadow-sm" type="submit">
            Buat Akun
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-[#e2e8f0] w-full text-center">
        <p class="text-[#444653]">
            Sudah punya akun? 
            <a class="text-[#3755c3] font-bold hover:underline transition-all" href="{{ route('login') }}">Masuk di sini</a>
        </p>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyePath = document.getElementById('eye-path-' + inputId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyePath.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.325-3.675M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 6l3 3m-3-3l-3 3m3-3V9');
            } else {
                passwordInput.type = 'password';
                eyePath.setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
            }
        }
    </script>
</x-guest-layout>
