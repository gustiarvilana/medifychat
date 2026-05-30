<x-guest-layout>
    <div class="w-full mb-lg text-center">
        <h1 class="font-title-md text-title-md text-on-surface mb-xs">Create your account</h1>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Enter your clinical credentials to begin.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="w-full space-y-lg">
        @csrf

        <!-- Full Name Field -->
        <div class="space-y-xs">
            <label class="font-body-sm text-body-sm font-bold text-on-surface" for="name">Full Name</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">person</span>
                <input class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" 
                    id="name" name="name" value="{{ old('name') }}" placeholder="John Doe" type="text" required autofocus autocomplete="name" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username Field -->
        <div class="space-y-xs">
            <label class="font-body-sm text-body-sm font-bold text-on-surface" for="username">Username</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">badge</span>
                <input class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" 
                    id="username" name="username" value="{{ old('username') }}" placeholder="admin" type="text" required autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email Field -->
        <div class="space-y-xs">
            <label class="font-body-sm text-body-sm font-bold text-on-surface" for="email">Email Address</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">mail</span>
                <input class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" 
                    id="email" name="email" value="{{ old('email') }}" placeholder="admin@medify.com" type="email" required autocomplete="email" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
            <div class="space-y-xs">
                <label class="font-body-sm text-body-sm font-bold text-on-surface" for="password">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">lock</span>
                    <input class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" 
                        id="password" name="password" placeholder="••••••••" type="password" required autocomplete="new-password" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="space-y-xs">
                <label class="font-body-sm text-body-sm font-bold text-on-surface" for="password_confirmation">Confirm Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">shield_lock</span>
                    <input class="w-full pl-[48px] pr-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" 
                        id="password_confirmation" name="password_confirmation" placeholder="••••••••" type="password" required autocomplete="new-password" />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="flex items-start gap-md py-base">
            <input class="mt-xs w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary-container cursor-pointer" id="terms" type="checkbox" required />
            <label class="font-body-sm text-body-sm text-on-surface-variant leading-tight" for="terms">
                I agree to the <a class="text-primary font-semibold hover:underline" href="#">Terms of Service</a> and <a class="text-primary font-semibold hover:underline" href="#">Privacy Policy</a> of the healthcare network.
            </label>
        </div>

        <!-- Primary Action -->
        <button class="w-full bg-primary-container text-on-primary py-md rounded-xl font-title-md text-title-md hover:bg-primary transition-colors shadow-md active:scale-[0.98]" type="submit">
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
