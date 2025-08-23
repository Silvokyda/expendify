{{-- resources/views/dashboard/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Dashboard') }}
            </h2>

            @if($hasWallet)
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="mr-2">Wallet:</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        KSh {{ number_format($walletBalance, 2) }}
                    </span>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ==================== NEW USER EMPTY STATE ==================== --}}
            @if($isNewUser)
                <div class="rounded-2xl bg-emerald-900 text-emerald-50 p-8 shadow-lg">
                    <div class="max-w-3xl">
                        <h1 class="text-3xl font-semibold mb-2">Start using budgets</h1>
                        <p class="text-emerald-100/90 mb-6">
                            Set limits for groceries, rent, and other spending categories to track expenses and payments.
                        </p>
                        <a href="{{ route('budgets.create') }}"
                           class="inline-flex items-center rounded-md bg-amber-400 hover:bg-amber-300 text-emerald-900 font-semibold px-4 py-2 transition">
                            Create a budget
                        </a>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @includeIf('app.dashboard.partials.charts')
                    {{-- Recent expenses --}}
                    <div class="rounded-xl bg-stone-50 dark:bg-white/5 p-5 ring-1 ring-black/5 dark:ring-white/10">
                        <h3 class="font-semibold text-stone-800 dark:text-stone-100">Recent expenses</h3>
                        <p class="mt-6 text-stone-500 dark:text-stone-400">No expenses yet</p>
                        <p class="text-sm text-stone-400 dark:text-stone-500">Add expenses as you go</p>
                    </div>

                    {{-- Upcoming payments --}}
                    <div class="rounded-xl bg-stone-50 dark:bg-white/5 p-5 ring-1 ring-black/5 dark:ring-white/10">
                        <h3 class="font-semibold text-stone-800 dark:text-stone-100">Upcoming payments</h3>
                        <p class="mt-6 text-stone-500 dark:text-stone-400">No scheduled payments</p>
                        <p class="text-sm text-stone-400 dark:text-stone-500">M‑PESA payments show here</p>
                    </div>

                    {{-- Savings goals --}}
                    <div class="rounded-xl bg-stone-50 dark:bg-white/5 p-5 ring-1 ring-black/5 dark:ring-white/10">
                        <h3 class="font-semibold text-stone-800 dark:text-stone-100">Savings goals</h3>
                        <p class="mt-6 text-stone-500 dark:text-stone-400">No savings goals</p>
                        <p class="text-sm text-stone-400 dark:text-stone-500">Set targets and grow savings</p>
                    </div>
                </div>
            @else
            {{-- ==================== RICH DASHBOARD ==================== --}}

                <div class="flex items-center justify-between mb-4">
                    <div>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400">This month</p>
                        @if($totalBudget > 0)
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
                                You’ve spent KSh {{ number_format($spentThisMonth,0) }}
                                of KSh {{ number_format($totalBudget,0) }}
                            </h3>
                        @else
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
                                Spent this month: KSh {{ number_format($spentThisMonth,0) }}
                            </h3>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('budgets.create') }}"
                           class="inline-flex items-center rounded-md bg-emerald-600 text-white hover:bg-emerald-500 px-3 py-2 text-sm font-medium">
                            + Create Budget
                        </a>
                        @if($hasWallet)
                            <a href="{{ route('payouts.create') }}"
                               class="inline-flex items-center rounded-md bg-white ring-1 ring-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-2 text-sm font-medium">
                               Pay with M‑PESA
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Top row --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Budget status card --}}
                    <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-white/5 p-6 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-gray-800 dark:text-white mb-4">Budget status</h4>
                        @if($totalBudget > 0)
                            <div class="mb-3">
                                <div class="h-3 w-full rounded-full bg-emerald-100 dark:bg-emerald-900/40 overflow-hidden">
                                    @php
                                        $pct = min(100, $totalBudget ? round(($spentThisMonth/$totalBudget)*100) : 0);
                                    @endphp
                                    <div class="h-3 bg-emerald-600 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300">
                                <div>Spent<br><span class="font-semibold">KSh {{ number_format($spentThisMonth,0) }}</span></div>
                                <div>Remaining<br><span class="font-semibold">KSh {{ number_format($remaining,0) }}</span></div>
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">No monthly limits set. <a class="underline" href="{{ route('categories.index') }}">Create budgets</a>.</p>
                        @endif
                    </div>

                    {{-- Recent transactions --}}
                    <div class="rounded-2xl bg-white dark:bg-white/5 p-6 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-gray-800 dark:text-white mb-4">Recent transactions</h4>
                        <div class="space-y-3">
                            @forelse($recentTx as $t)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-800 dark:text-gray-100 truncate">
                                            {{ $t->merchant ?: '—' }}
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400">
                                            {{ \Illuminate\Support\Carbon::parse($t->date)->format('M j') }}
                                            ·
                                            {{ optional($t->category)->name ?? 'Uncategorized' }}
                                        </div>
                                    </div>
                                    <div class="font-semibold {{ $t->type === 'expense' ? 'text-gray-900 dark:text-gray-100' : 'text-emerald-700 dark:text-emerald-300' }}">
                                        {{ $t->type === 'expense' ? '‑' : '+' }} KSh {{ number_format($t->amount,0) }}
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400">No transactions yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Bottom row --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Budget breakdown --}}
                    <div class="rounded-2xl bg-white dark:bg-white/5 p-6 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-gray-800 dark:text-white mb-4">Budget breakdown</h4>
                        <div class="space-y-3">
                            @forelse($categories->where('type','expense')->take(6) as $cat)
                                @php
                                    $spent = (float) ($spendByCategory[$cat->id] ?? 0);
                                    $limit = (float) ($cat->monthly_limit ?? 0);
                                    $pct   = $limit > 0 ? min(100, round(($spent/$limit)*100)) : 0;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            KSh {{ number_format($spent,0) }}
                                            @if($limit>0)
                                                / KSh {{ number_format($limit,0) }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="mt-1 h-2 w-full rounded bg-emerald-50 dark:bg-emerald-900/30 overflow-hidden">
                                        <div class="h-2 bg-emerald-600 rounded" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400">No expense categories yet.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Savings goal --}}
                    <div class="rounded-2xl bg-white dark:bg-white/5 p-6 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-gray-800 dark:text-white mb-4">Savings goal</h4>
                        @if($goalTarget > 0)
                            @php
                                $gpct = min(100, round(($goalSaved / $goalTarget) * 100));
                            @endphp
                            <div class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                KSh {{ number_format($goalSaved,0) }} of KSh {{ number_format($goalTarget,0) }}
                            </div>
                            <div class="h-2 w-full rounded bg-amber-100 dark:bg-amber-900/30 overflow-hidden">
                                <div class="h-2 bg-amber-400 rounded" style="width: {{ $gpct }}%"></div>
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">
                                No savings goal yet. <a href="{{ route('savings-goals.index') }}" class="underline">Create one</a>.
                            </p>
                        @endif
                    </div>

                    {{-- Monthly summary (very simple bars) --}}
                    <div class="rounded-2xl bg-white dark:bg-white/5 p-6 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-gray-800 dark:text-white mb-4">Monthly summary</h4>
                        @php
                            // quick static bars for MVP; swap with chart.js later
                            $bars = collect(range(5, 30, 5))->map(fn($x) => rand(20,90));
                        @endphp
                        <div class="flex items-end gap-2 h-24">
                            @foreach($bars as $h)
                                <div class="w-5 bg-emerald-600/80 rounded" style="height: {{ $h }}%"></div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Replace with real chart data later.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
