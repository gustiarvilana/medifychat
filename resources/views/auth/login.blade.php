<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="w-full mb-lg text-center">
        <h1 class="font-title-md text-title-md text-on-surface mb-xs">Welcome Back</h1>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Please enter your clinical credentials to access the system.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="w-full space-y-lg">
        @csrf

        <!-- Email / Username Field -->
        <div class="space-y-xs">
            <label class="font-body-sm text-body-sm font-bold text-on-surface" for="login">Email or Username</label>
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">mail</span>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username"
                    class="w-full pl-xl pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                    placeholder="admin@medify.hospital" />
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password Field -->
        <div class="space-y-xs">
            <div class="flex justify-between items-center">
                <label class="font-body-sm text-body-sm font-bold text-on-surface" for="password">Password</label>
                @if (Route::has('password.request'))
                    <a class="font-body-sm text-body-sm text-primary hover:underline font-semibold" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">lock</span>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="w-full pl-xl pr-xl py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                    placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-base">
            <input id="remember_me" name="remember" type="checkbox"
                class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary transition-all cursor-pointer" />
            <label class="font-body-sm text-body-sm text-on-surface-variant cursor-pointer select-none" for="remember_me">Remember me on this device</label>
        </div>

        <!-- Sign In Button -->
        <button type="submit"
            class="w-full h-[48px] bg-primary text-on-primary font-title-md text-title-md rounded-xl hover:bg-primary-container active:scale-[0.98] transition-all duration-200 shadow-sm flex items-center justify-center gap-base group">
            Sign In
            <span class="material-symbols-outlined transition-transform group-hover:translate-x-1">arrow_forward</span>
        </button>
    </form>

    <div class="mt-xl pt-lg border-t border-outline-variant w-full text-center">
        <p class="font-body-sm text-body-sm text-on-surface-variant">
            Don't have an account?
            <a class="text-primary font-bold hover:underline transition-all" href="{{ route('register') }}">Register here</a>
        </p>
    </div>
</x-guest-layout>
