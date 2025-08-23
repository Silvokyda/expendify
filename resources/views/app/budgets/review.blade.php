@extends('layouts.app')

@section('content')
<div class="max-w-7xl bg-white mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-6">Review Budget</h1>

    <div class="mb-4 text-sm">
        <div><b>Name:</b> {{ $step1['name'] }}</div>
        <div><b>Period:</b> {{ ucfirst($step1['period']) }}</div>
        @if($step1['period']==='custom')
            <div><b>Dates:</b> {{ $step1['start_date'] }} → {{ $step1['end_date'] }}</div>
        @endif
        <div><b>Total:</b> KSh {{ number_format($step1['total_amount'],2) }}</div>
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
                        @php
                            $label = '—';
                            if ($it['savings_goal_id']) $label = 'Goal #'.$it['savings_goal_id'];
                            elseif ($it['category_id']) $label = 'Category #'.$it['category_id'];
                        @endphp
                        {{ $label }}
                    </td>
                    <td class="px-4 py-2">KSh {{ number_format($it['amount'],2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <p class="mt-4 text-sm">Unallocated: <b>KSh {{ number_format($unallocated,2) }}</b></p>

    <form method="POST" action="{{ route('budgets.store') }}" class="mt-6 flex justify-end gap-2">
        @csrf
        <a href="{{ route('budgets.create', ['step'=>2]) }}" class="px-4 py-2 text-sm rounded-md bg-gray-100 dark:bg-gray-800">Back</a>
        <button class="px-4 py-2 text-sm rounded-md bg-green-800 text-white">Activate Budget</button>
    </form>
</div>
@endsection
