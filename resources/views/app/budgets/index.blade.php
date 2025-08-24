@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Budgets</h1>
        <a href="{{ route('budgets.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-green-800 px-4 py-2 text-white hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Create budget
        </a>
    </div>

    @if($budgets->count())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach($budgets as $b)
            @php
                $allocated   = $b->items->whereIn('type', ['expense','saving'])->sum('amount');
                $unallocated = max(0, (float)$b->total_amount - (float)$allocated);
                $pct         = (float)$b->total_amount > 0 ? min(100, round(($allocated / $b->total_amount) * 100)) : 0;
            @endphp

            <div class="relative group rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/60 p-5 shadow-sm hover:shadow-md transition">
                <a href="{{ route('budgets.show', $b) }}" class="absolute inset-0" aria-label="Open {{ $b->name }}"></a>

                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold truncate">{{ $b->name }}</h2>
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-300">{{ ucfirst($b->period) }}</span>
                        </div>
                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            <span class="mr-2">Allocated</span>
                            <span class="font-medium">KSh {{ number_format($allocated,2) }}</span>
                            <span class="mx-1 text-gray-400">/</span>
                            <span>KSh {{ number_format($b->total_amount,2) }}</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-xs text-gray-500">Total</div>
                        <div class="text-lg font-semibold">KSh {{ number_format($b->total_amount, 2) }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span>{{ $pct }}%</span>
                        <span class="text-gray-600 dark:text-gray-300">Unallocated: <span class="font-semibold">KSh {{ number_format($unallocated, 2) }}</span></span>
                    </div>
                    <div class="w-full h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                        <div class="h-2 rounded bg-gradient-to-r from-green-600 to-emerald-600" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs
                        {{ $b->currently_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $b->currently_active ? 'Active' : 'Inactive' }}
                    </span>

                    <div class="relative z-10 flex items-center gap-2">
                        <a href="{{ route('budgets.edit', $b) }}"
                           class="px-3 py-1.5 rounded-md bg-gray-200 dark:bg-gray-800 text-sm hover:bg-gray-200 dark:hover:bg-gray-700">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('budgets.destroy', $b) }}" onsubmit="return confirm('Delete this budget?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-md bg-red-600 text-white text-sm hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @else
        <p class="text-sm text-gray-600 dark:text-gray-300">No budgets yet.</p>
    @endif

    <div class="mt-6">{{ $budgets->links() }}</div>
</div>
@endsection
