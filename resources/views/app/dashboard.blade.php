{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl p-5 bg-white/80 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
            <p class="text-sm text-zinc-600 dark:text-zinc-300">This month’s spend</p>
            <p class="mt-1 text-2xl font-semibold text-[#0f5334]">Ksh. 28,420</p>
        </div>
        <div class="rounded-2xl p-5 bg-white/80 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
            <p class="text-sm text-zinc-600 dark:text-zinc-300">Savings progress</p>
            <p class="mt-1 text-2xl font-semibold text-[#0f5334]">62%</p>
        </div>
        <div class="rounded-2xl p-5 bg-white/80 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
            <p class="text-sm text-zinc-600 dark:text-zinc-300">Reminders</p>
            <p class="mt-1 text-2xl font-semibold text-[#0f5334]">2 active</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl p-6 bg-white/80 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
        <p class="text-gray-900 dark:text-white">{{ __("You're logged in!") }}</p>
    </div>
</x-app-layout>
