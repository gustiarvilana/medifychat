<x-guest-layout>
    <div class="w-full mb-lg text-center">
        <h1 class="font-title-md text-title-md text-on-surface mb-xs">Create your account</h1>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Enter your clinical credentials to begin.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="w-full space-y-lg">
        @csrf

        <!-- Full Name -->
        <div class="space-y-xs">
            <label class="font-body-sm text-body-sm font-bold text-on-surface" for="name">Full Name</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">person</span>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                    placeholder="John Doe" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="space-y-xs">
            <label class="font-body-sm text-body-sm font-bold text-on-surface" for="username">Username</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">badge</span>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="username"
                    class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                    placeholder="admin" />
            </div>
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="space-y-xs">
            <label class="font-body-sm text-body-sm font-bold text-on-surface" for="email">Email Address</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">mail</span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                    class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                    placeholder="admin@medify.hospital" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
            <div class="space-y-xs">
                <label class="font-body-sm text-body-sm font-bold text-on-surface" for="password">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">lock</span>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                        placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="space-y-xs">
                <label class="font-body-sm text-body-sm font-bold text-on-surface" for="password_confirmation">Confirm Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">shield_lock</span>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                        class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                        placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <!-- Primary Action -->
        <button type="submit"
            class="w-full bg-primary-container text-on-primary py-md rounded-xl font-title-md text-title-md hover:bg-primary transition-colors shadow-md active:scale-[0.98]">
            Create Account
        </button>
    </form>

    <div class="mt-xl pt-xl border-t border-outline-variant w-full text-center">
        <p class="font-body-sm text-body-sm text-on-surface-variant">
            Already have an account?
            <a class="text-primary font-bold hover:underline transition-all" href="{{ route('login') }}">Sign In</a>
        </p>
    </div>
</x-guest-layout>
