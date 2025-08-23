@extends('layouts.app')

@section('content')
@php
    $expenses = $budget->items->where('type','expense')->values();
    $savings  = $budget->items->where('type','saving')->values();
    $incomes  = $budget->items->where('type','income')->values();

    $allocated = $expenses->sum('amount') + $savings->sum('amount');
    $unallocated = max(0, (float)$budget->total_amount - (float)$allocated);
    $pct = (float)$budget->total_amount > 0 ? min(100, round(($allocated / $budget->total_amount) * 100)) : 0;

    $expenseLabels = $expenses->map(fn($it) => optional($it->category)->name ?? ($it->note ?? 'Item '.$it->id))->toArray();
    $expenseData   = $expenses->map(fn($it) => (float)$it->amount)->toArray();

    $expCount = $expenses->count();
    $savCount = $savings->count();
    $incCount = $incomes->count();
@endphp

<div class="max-w-6xl mx-auto py-8 space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold truncate">{{ $budget->name }}</h1>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-300">{{ ucfirst($budget->period) }}</span>
                @if($budget->period === 'custom' && ($budget->start_date || $budget->end_date))
                    <span>{{ optional($budget->start_date)->toDateString() }} — {{ optional($budget->end_date)->toDateString() }}</span>
                @endif
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $budget->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">{{ $budget->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('budgets.edit',$budget) }}" class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-white hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Edit budget
            </a>
            <a href="{{ route('budgets.index') }}" class="rounded-lg bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm hover:bg-gray-200 dark:hover:bg-gray-700">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl border p-5 dark:border-gray-700 bg-white dark:bg-gray-800/60">
            <div class="text-sm">Total</div>
            <div class="mt-1 text-2xl font-semibold">KSh {{ number_format($budget->total_amount, 2) }}</div>
        </div>
        <div class="rounded-xl border p-5 dark:border-gray-700 bg-white dark:bg-gray-800/60">
            <div class="text-sm">Allocated</div>
            <div class="mt-1 text-2xl font-semibold">KSh {{ number_format($allocated, 2) }}</div>
        </div>
        <div class="rounded-xl border p-5 dark:border-gray-700 bg-white dark:bg-gray-800/60">
            <div class="text-sm">Unallocated</div>
            <div class="mt-1 text-2xl font-semibold {{ $unallocated <= 0 ? 'text-red-600' : '' }}">{{ $unallocated > 0 ? 'KSh '.number_format($unallocated,2) : '0' }}</div>
        </div>
    </div>

    <div class="rounded-xl border p-5 dark:border-gray-700 bg-white dark:bg-gray-800/60">
        <div class="flex items-center justify-between text-xs mb-2">
            <span class="font-medium">{{ $pct }}% allocated</span>
            <span class="text-gray-600 dark:text-gray-300">KSh {{ number_format($allocated,2) }} / KSh {{ number_format($budget->total_amount,2) }}</span>
        </div>
        <div class="w-full h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
            <div class="h-2 rounded bg-gradient-to-r from-green-600 to-emerald-700" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border dark:border-gray-700 bg-white dark:bg-gray-800/60 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b dark:border-gray-700">
                    <h2 class="font-semibold">Category breakdown</h2>
                    <a href="{{ route('budgets.edit',$budget) }}" class="rounded-md bg-green-700 px-3 py-1.5 text-sm text-white hover:bg-green-900">Edit allocations</a>
                </div>
                <div class="p-5">
                    @if(count($expenseData))
                        <canvas id="chartPie" height="160"></canvas>
                    @else
                        <div class="text-sm text-gray-600 dark:text-gray-300">No expense allocations to visualize.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border dark:border-gray-700 bg-white dark:bg-gray-800/60 overflow-hidden">
                <div class="px-5 py-4 border-b dark:border-gray-700">
                    <h2 class="font-semibold">Expense items</h2>
                </div>
                <div class="px-5 py-4">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Category</th>
                                <th class="py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-800">
                            @foreach($expenses as $it)
                                <tr>
                                    <td class="py-2">{{ optional($it->category)->name ?? ($it->note ?? '—') }}</td>
                                    <td class="py-2 text-right">KSh {{ number_format($it->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="py-2 font-medium">Items count</td>
                                <td class="py-2 text-right font-medium">{{ $expCount }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border p-5 dark:border-gray-700 bg-white dark:bg-gray-800/60">
                <h3 class="font-semibold mb-3">Budget details</h3>
                <dl class="text-sm space-y-2">
                    <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">Period</dt><dd class="font-medium">{{ ucfirst($budget->period) }}</dd></div>
                    @if($budget->period === 'custom')
                        <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">Start</dt><dd class="font-medium">{{ optional($budget->start_date)->toDateString() ?: '—' }}</dd></div>
                        <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">End</dt><dd class="font-medium">{{ optional($budget->end_date)->toDateString() ?: '—' }}</dd></div>
                    @endif
                    <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">Expense items</dt><dd class="font-medium">{{ $expCount }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">Savings items</dt><dd class="font-medium">{{ $savCount }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">Income items</dt><dd class="font-medium">{{ $incCount }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">Created</dt><dd class="font-medium">{{ optional($budget->created_at)->toDayDateTimeString() ?: '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-600 dark:text-gray-300">Last updated</dt><dd class="font-medium">{{ optional($budget->updated_at)->toDayDateTimeString() ?: '—' }}</dd></div>
                </dl>

                <div class="mt-5">
                    <a href="{{ route('budgets.edit',$budget) }}" class="w-full inline-flex justify-center rounded-lg bg-green-700 px-4 py-2 text-white hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Edit budget
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
if (document.getElementById('chartPie')) {
    const expLabels = @json($expenseLabels);
    const expData   = @json($expenseData);

    if (expData.length) {
        Chart.register(ChartDataLabels);
        new Chart(document.getElementById('chartPie'), {
            type: 'pie',
            data: { labels: expLabels, datasets: [{ data: expData, borderWidth: 0 }] },
            options: {
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        formatter: (value, ctx) => {
                            const label = ctx.chart.data.labels[ctx.dataIndex] || '';
                            const val = Number(value).toLocaleString(undefined,{minimumFractionDigits:2});
                            return `${label}\nKSh ${val}`;
                        },
                        color: '#fff',
                        font: { weight: '600', size: 11 },
                        textAlign: 'center',
                        clamp: true,
                        anchor: 'center',
                        align: 'center',
                        padding: 2
                    },
                    tooltip: {
                        callbacks: { label: c => `${c.label}: KSh ${Number(c.raw).toLocaleString(undefined,{minimumFractionDigits:2})}` }
                    }
                }
            }
        });
    }
}
</script>
@endsection
