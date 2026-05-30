<!-- SideNavBar -->
<aside class="flex flex-col h-full py-xl px-base fixed left-0 top-0 z-40 bg-surface border-r border-outline-variant w-[280px] hidden md:flex">
    <div class="mb-2xl px-md">
        <div class="flex items-center gap-base">
            <div class="w-10 h-10 bg-primary-container rounded-xl flex items-center justify-center text-on-primary shadow-sm">
                <span class="material-symbols-outlined">health_metrics</span>
            </div>
            <div>
                <h1 class="font-headline-lg text-headline-lg font-bold text-primary tracking-tight">MedBot</h1>
                <p class="font-body-sm text-body-sm text-on-surface-variant opacity-70">Clinical Admin v1.0</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 space-y-xs">
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-md px-md py-sm cursor-pointer transition-all duration-200 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-surface-container text-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="font-body-sm text-body-sm">Beranda</span>
        </a>
        <a href="{{ route('settings') }}"
            class="flex items-center gap-md px-md py-sm cursor-pointer transition-all duration-200 rounded-xl {{ request()->routeIs('settings') && !request()->routeIs('context.*') ? 'bg-surface-container text-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">
            <span class="material-symbols-outlined">smart_toy</span>
            <span class="font-body-sm text-body-sm">Bot WhatsApp</span>
        </a>
        <a href="{{ route('context.index') }}"
            class="flex items-center gap-md px-md py-sm cursor-pointer transition-all duration-200 rounded-xl {{ request()->routeIs('context.*') ? 'bg-surface-container text-primary font-semibold shadow-sm' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">
            <span class="material-symbols-outlined">settings</span>
            <span class="font-body-sm text-body-sm">Pengaturan</span>
        </a>
    </nav>

    <div class="mt-auto px-md space-y-xs">
        <button class="w-full bg-primary text-white py-sm rounded-xl font-body-sm font-semibold mb-lg flex items-center justify-center gap-xs shadow-md hover:bg-primary-container transition-all">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Konfigurasi Baru
        </button>
        <a href="#" class="flex items-center gap-md px-md py-sm cursor-pointer text-on-surface-variant hover:text-primary transition-all duration-200">
            <span class="material-symbols-outlined">help</span>
            <span class="font-body-sm text-body-sm">Pusat Bantuan</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center gap-md px-md py-sm cursor-pointer text-on-surface-variant hover:text-primary transition-all duration-200">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-body-sm text-body-sm">Keluar</span>
            </button>
        </form>
    </div>
</aside>

<!-- Mobile Navigation (Bottom Nav) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-surface-container-lowest border-t border-outline-variant z-50 flex justify-around py-sm px-md">
    <a href="{{ route('dashboard') }}"
        class="flex flex-col items-center gap-xs {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-on-surface-variant' }}">
        <span class="material-symbols-outlined {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">dashboard</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Beranda</span>
    </a>
    <a href="{{ route('settings') }}"
        class="flex flex-col items-center gap-xs {{ request()->routeIs('settings') ? 'text-primary' : 'text-on-surface-variant' }}">
        <span class="material-symbols-outlined {{ request()->routeIs('settings') ? 'font-bold' : '' }}">smart_toy</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Bot</span>
    </a>
    <a href="{{ route('context.index') }}"
        class="flex flex-col items-center gap-xs {{ request()->routeIs('context.*') ? 'text-primary' : 'text-on-surface-variant' }}">
        <span class="material-symbols-outlined {{ request()->routeIs('context.*') ? 'font-bold' : '' }}">settings</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Pengaturan</span>
    </a>
    <a href="{{ route('profile.edit') }}"
        class="flex flex-col items-center gap-xs text-on-surface-variant">
        <span class="material-symbols-outlined">account_circle</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Profil</span>
    </a>
</nav>
