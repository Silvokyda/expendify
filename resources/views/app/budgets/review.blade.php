@extends('layouts.app')

@section('content')
<div class="max-w-7xl bg-white mx-auto py-8 px-4">
    <h1 class="text-2xl font-semibold mb-6">Review Budget</h1>

    <div class="mb-4 text-sm">
        <div><b>Name:</b> {{ $step1['name'] }}</div>
        <div><b>Period:</b> {{ ucfirst($step1['period']) }}</div>
        @if($step1['period']==='custom')
            <div><b>Dates:</b> {{ $step1['start_date'] }} → {{ $step1['end_date'] }}</div>
        @endif
        <div><b>Total:</b> KSh {{ number_format($step1['total_amount'],2) }}</div>
        <div class="mt-1"><b>Unallocated:</b> KSh {{ number_format($unallocated,2) }}</div>
    </div>

    <div class="rounded-lg border dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr>
                    <th class="px-4 py-2 text-left text-xs uppercase">Type</th>
                    <th class="px-4 py-2 text-left text-xs uppercase">Item</th>
                    <th class="px-4 py-2 text-left text-xs uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($items as $it)
                <tr>
                    <td class="px-4 py-2">{{ ucfirst($it['type']) }}</td>
                    <td class="px-4 py-2">
                        @if(!empty($it['category_id'])) Category #{{ $it['category_id'] }}
                        @elseif(!empty($it['savings_goal_id'])) Savings Goal #{{ $it['savings_goal_id'] }}
                        @else Custom
                        @endif
                    </td>
                    <td class="px-4 py-2">KSh {{ number_format($it['amount'],2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <a href="{{ route('budgets.create', ['step' => 2]) }}"
           class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Back to Step 2
        </a>

        <form method="POST" action="{{ route('budgets.store') }}">
            @csrf
            <button class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-900">
                Activate budget
            </button>
        </form>
    </div>
</div>
@endsection
