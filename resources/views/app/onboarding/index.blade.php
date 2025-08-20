<style>
    [x-cloak] {
        display: none !important;
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                Onboarding
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center text-sm text-gray-600 dark:text-gray-300">
                @if($step === 1)
                <span class="font-medium text-emerald-700 dark:text-emerald-300">Step 1</span> of 2
                @else
                <span class="font-medium text-emerald-700 dark:text-emerald-300">Step 2</span> of 2
                @endif
            </div>

            {{-- ================= Step 1: Budgets ================= --}}
            @if($step === 1)
            <div
                x-data="budgetsComponent({ updateUrl: '{{ url('/budgets') }}', csrf: (window.csrfToken || '{{ csrf_token() }}') })"
                class="rounded-2xl bg-white dark:bg-white/5 p-8 shadow-sm ring-1 ring-black/5 dark:ring-white/10">

                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Your budgets</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Budgets are expense categories with limits.</p>
                    </div>
                    <button @click="open = true"
                        class="inline-flex items-center px-3 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                        + Create Budget
                    </button>
                </div>

                @if (session('status'))
                <div class="mt-4 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-2 text-sm">
                    {{ session('status') }}
                </div>
                @endif

                <div class="mt-6">
                    @if($categories->count() === 0)
                    <div class="rounded-lg bg-stone-50 dark:bg-white/5 p-6 text-center ring-1 ring-black/5 dark:ring-white/10">
                        <p class="text-gray-700 dark:text-gray-200 font-medium">No budgets yet</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Create your first budget to get started.</p>
                        <div class="mt-4">
                            <button @click="open = true"
                                class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                                Create Budget
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="overflow-hidden rounded-xl ring-1 ring-black/5 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Period</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Limit</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/10 bg-white dark:bg-transparent">
                                @forelse($budgetItems as $b)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">{{ $b->name }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($b->period) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">
                                        {{ $b->amount > 0 ? 'KSh '.number_format($b->amount,0) : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                @click="openEdit({
                            id: {{ $b->category_id }},
                            name: @js($b->name),
                            monthly_limit: {{ $b->amount }},
                            period: @js($b->period),
                            start_date: @js(optional($b->start_date)->format('Y-m-d')),
                            end_date: @js(optional($b->end_date)->format('Y-m-d'))
                        })"
                                                class="inline-flex items-center px-2 py-1 text-xs rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300">
                                                ✎ Edit
                                            </button>

                                            <button type="button"
                                                @click="destroyBudget({{ $b->category_id }})"
                                                class="inline-flex items-center px-2 py-1 text-xs rounded-md bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-300">
                                                🗑 Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No budgets yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                    @endif
                </div>

                <div class="mt-6 text-right">
                    <a href="{{ route('onboarding.show', ['step' => 2]) }}"
                        class="text-sm text-gray-600 dark:text-gray-300 underline">Next: Wallet →</a>
                </div>

                {{-- ============== Modal: Create Budget ============== --}}
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div @click="open=false" class="absolute inset-0 bg-black/40"></div>

                    <div @keydown.escape.window="open=false"
                        class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 p-6 shadow-xl ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Create budget</h4>

                        <form method="POST" action="{{ route('onboarding.budget') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input name="name" x-model="name" required
                                    class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100"
                                    placeholder="e.g., Groceries" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Period</label>
                                    <select name="period" x-model="period"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100">
                                        <option value="monthly">Monthly</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Limit (KSh)</label>
                                    <input name="amount" x-model="amount" type="number" step="0.01" min="0"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100"
                                        placeholder="e.g., 25000" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="period==='custom'">
                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Start date</label>
                                    <input type="date" name="start_date" x-model="start_date"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100" />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">End date</label>
                                    <input type="date" name="end_date" x-model="end_date"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="open=false"
                                    class="px-3 py-2 rounded-md bg-stone-100 dark:bg-white/10 text-gray-800 dark:text-gray-200 hover:bg-stone-200 dark:hover:bg-white/20">
                                    Cancel
                                </button>
                                <button class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                                    Save budget
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ============== Modal: Edit Budget ============== --}}
                <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div @click="editOpen=false" class="absolute inset-0 bg-black/40"></div>

                    <div @keydown.escape.window="editOpen=false"
                        class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 p-6 shadow-xl ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Edit budget</h4>

                        <form @submit.prevent="submitEdit" class="space-y-4">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input x-model="editForm.name" required
                                    class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Period</label>
                                    <select x-model="editForm.period"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100">
                                        <option value="monthly">Monthly</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Limit (KSh)</label>
                                    <input x-model="editForm.monthly_limit" type="number" step="0.01" min="0"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="editForm.period==='custom'">
                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Start date</label>
                                    <input type="date" x-model="editForm.start_date"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100" />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">End date</label>
                                    <input type="date" x-model="editForm.end_date"
                                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 p-2 text-gray-900 dark:text-gray-100" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="editOpen=false"
                                    class="px-3 py-2 rounded-md bg-stone-100 dark:bg-white/10 text-gray-800 dark:text-gray-200 hover:bg-stone-200 dark:hover:bg-white/20">
                                    Cancel
                                </button>
                                <button class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                                    Save changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            @endif

            {{-- ================= Step 2: Wallet choice ================= --}}
            @if($step === 2)
            <div class="grid gap-6">
                {{-- Option A: Create wallet --}}
                <form method="POST" action="{{ route('onboarding.wallet') }}"
                    class="rounded-2xl bg-white dark:bg-white/5 p-8 shadow-sm ring-1 ring-black/5 dark:ring-white/10 space-y-4">
                    @csrf
                    <input type="hidden" name="choice" value="wallet" />

                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Create Expendify Wallet</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Recommended: pay merchants (Till/Paybill) and send to family from Expendify.
                    </p>

                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Phone (M‑PESA MSISDN)</label>
                    <input name="msisdn"
                        value="{{ old('msisdn') }}"
                        inputmode="tel"
                        pattern="^0[0-9]{9}$|^\+254[0-9]{9}$"
                        class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/10 text-gray-900 dark:text-gray-100 p-2"
                        placeholder="07xxxxxxxx or +2547xxxxxxxx" />
                    @error('msisdn')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>We’ll link this number to your wallet for M‑PESA payments.</span>
                        <span>One number per wallet.</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                            Create Wallet
                        </button>
                        <a href="{{ route('onboarding.show', ['step' => 1]) }}" class="text-sm underline text-gray-600 dark:text-gray-300">
                            Back
                        </a>
                    </div>
                </form>

                {{-- Option B: Skip wallet (tracking only) --}}
                <form method="POST" action="{{ route('onboarding.wallet') }}"
                    class="rounded-2xl bg-white dark:bg-white/5 p-8 shadow-sm ring-1 ring-black/5 dark:ring-white/10 space-y-4">
                    @csrf
                    <input type="hidden" name="choice" value="skip" />

                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Skip Wallet</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        You’ll still get budgeting & reporting, but no in‑app sending to individuals.
                    </p>

                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 rounded-md bg-stone-100 dark:bg-white/10 text-gray-800 dark:text-gray-200 hover:bg-stone-200 dark:hover:bg-white/20">
                            Continue without wallet
                        </button>
                        <a href="{{ route('onboarding.show', ['step' => 1]) }}" class="text-sm underline text-gray-600 dark:text-gray-300">
                            Back
                        </a>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

<script>
    function budgetsComponent({
        updateUrl,
        csrf
    }) {
        return {
            // Create modal
            open: false,
            period: 'monthly',
            name: '',
            amount: '',
            start_date: '',
            end_date: '',

            // Edit modal
            editOpen: false,
            editForm: {
                id: null,
                name: '',
                monthly_limit: '',
                period: 'monthly',
                start_date: null,
                end_date: null,
            },

            openEdit(b) {
                this.editForm = {
                    id: b.id,
                    name: b.name ?? '',
                    monthly_limit: b.monthly_limit ?? '',
                    period: b.period ?? 'monthly',
                    start_date: b.start_date ?? null,
                    end_date: b.end_date ?? null,
                };
                this.editOpen = true;
            },

            async submitEdit() {
                if (!this.editForm.id) return;
                const url = `${updateUrl}/${this.editForm.id}`;

                const body = new URLSearchParams();
                body.set('_method', 'PUT');
                body.set('_token', csrf);
                body.set('name', this.editForm.name);
                body.set('monthly_limit', this.editForm.monthly_limit ?? '');
                body.set('period', this.editForm.period ?? 'monthly');
                if (this.editForm.period === 'custom') {
                    body.set('start_date', this.editForm.start_date ?? '');
                    body.set('end_date', this.editForm.end_date ?? '');
                }

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body
                    });
                    if (!res.ok) throw new Error('Failed to update');
                    this.editOpen = false;
                    window.location.reload();
                } catch (e) {
                    alert('Could not update budget. ' + e.message);
                }
            },

            async destroyBudget(id) {
                if (!confirm('Delete this budget?')) return;
                const url = `${updateUrl}/${id}`;

                const body = new URLSearchParams();
                body.set('_method', 'DELETE');
                body.set('_token', csrf);

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body
                    });
                    if (!res.ok) throw new Error('Failed to delete');
                    window.location.reload();
                } catch (e) {
                    alert('Could not delete budget. ' + e.message);
                }
            }
        }
    }
</script>