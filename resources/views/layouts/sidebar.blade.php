<!-- Sidebar -->
<div
    x-cloak
    :class="{ '!translate-x-0' : open }"
    class="fixed md:relative top-0 left-0 z-40 w-9/12 sm:w-64 h-screen flex flex-col
           bg-white/70 dark:bg-white/10 backdrop-blur-xl
           transform -translate-x-full md:translate-x-0 transition duration-300 ease-in-out
           md:flex-shrink-0">

    <!-- Header -->
    <div class="flex items-center h-16 px-2 border-b border-black/5 dark:border-white/10">
        <!-- Close (mobile) -->
        <button @click="open = false" class="md:hidden p-2 rounded hover:bg-black/5 dark:hover:bg-white/10">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </button>

        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 min-w-0">
            <img src="{{ asset('images/logos/logo_transparent.png') }}" alt="" class="h-9 w-auto" />
            <span class="text-lg sm:text-xl font-semibold tracking-tight text-[#0f5334] dark:text-white truncate">
                {{ config('app.name', 'Expendify') }}
            </span>
        </a>
    </div>

    <!-- Middle: Nav -->
    <div class="flex-1 overflow-y-auto">
        <nav> <x-sidebar-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
                        <path d="M3 12l9-7 9 7v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" />
                    </svg> </x-slot:icon> Dashboard </x-sidebar-nav-link> <x-sidebar-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.*')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
                        <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg> </x-slot:icon> Transactions </x-sidebar-nav-link> <x-sidebar-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
                        <path d="M4 6h6v6H4zM14 6h6v6h-6zM4 16h6v6H4zM14 16h6v6h-6z" stroke="currentColor" stroke-width="2" />
                    </svg> </x-slot:icon> Categories </x-sidebar-nav-link> <x-sidebar-nav-link :href="route('savings.index')" :active="request()->routeIs('savings.*')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
                        <path d="M12 3v18M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg> </x-slot:icon> Savings </x-sidebar-nav-link> <x-sidebar-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
                        <path d="M4 19h16M8 19V5m8 14V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg> </x-slot:icon> Reports </x-sidebar-nav-link> <x-sidebar-nav-link :href="route('reminders.index')" :active="request()->routeIs('reminders.*')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
                        <path d="M12 22a2 2 0 0 0 2-2H10a2 2 0 0 0 2 2zM6 16v-5a6 6 0 1 1 12 0v5l2 2H4l2-2z" stroke="currentColor" stroke-width="2" />
                    </svg> </x-slot:icon> Reminders </x-sidebar-nav-link> <x-sidebar-nav-link :href="route('settings')" :active="request()->routeIs('settings')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                    </svg> </x-slot:icon> Settings </x-sidebar-nav-link>
            <!-- TODO: To be checked -->
            <!-- <x-sidebar-nav-link :href="route('profile')" :active="request()->routeIs('profile')"> <x-slot:icon> <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm7 9H5a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4z" stroke="currentColor" stroke-width="2" />
                    </svg> </x-slot:icon> Profile </x-sidebar-nav-link>  -->
        </nav>
    </div>

    <!-- Bottom: Profile + Signout -->
    <div class="border-t border-black/5 dark:border-white/10">
        <!-- Profile -->
        <div class="px-4 py-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-[#0f5334]/10 dark:bg-white/10 grid place-content-center text-[#0f5334] dark:text-white font-semibold ring-1 ring-[#0f5334]/20">
                    {{ strtoupper(Str::substr(auth()->user()->name ?? 'U',0,1)) }}
                </div>
                <div class="min-w-0">
                    <div class="font-semibold truncate text-zinc-800 dark:text-zinc-100">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>

       <!-- Sign out -->
<form method="POST" action="{{ route('logout') }}" class="px-4 pb-4">
    @csrf
    <button type="submit" class="flex items-center w-full text-left px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
        <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="none">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <path d="M12 19a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h4a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        {{ __('Sign out') }}
    </button>
</form>

    </div>
</div>