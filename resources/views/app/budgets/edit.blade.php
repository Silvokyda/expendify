@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    @php
        $expenseItems = $budget->items->where('type','expense')->values();
        $allocated = $expenseItems->sum('amount');
        $unallocated = max(0, (float)$budget->total_amount - (float)$allocated);
    @endphp

    <h1 class="text-2xl font-semibold mb-6">Edit Budget</h1>

    <form method="POST" action="{{ route('budgets.update', $budget) }}" class="space-y-6" id="editBudgetForm">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input name="name" value="{{ old('name', $budget->name) }}" required class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Period</label>
                <select name="period" id="period" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800">
                    <option value="monthly" @selected(old('period',$budget->period)==='monthly')>Monthly</option>
                    <option value="weekly"  @selected(old('period',$budget->period)==='weekly')>Weekly</option>
                    <option value="custom"  @selected(old('period',$budget->period)==='custom')>Custom</option>
                </select>
                @error('period') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Total amount</label>
                <input name="total_amount" id="total_amount" type="number" step="0.01" min="0" value="{{ old('total_amount', $budget->total_amount) }}" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
                @error('total_amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div id="custom-dates" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ old('period',$budget->period)==='custom' ? '' : 'hidden' }}">
            <div>
                <label class="block text-sm font-medium">Start date</label>
                <input name="start_date" type="date" value="{{ old('start_date', optional($budget->start_date)->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
            </div>
            <div>
                <label class="block text-sm font-medium">End date</label>
                <input name="end_date" type="date" value="{{ old('end_date', optional($budget->end_date)->toDateString()) }}" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
            </div>
        </div>

        <div class="rounded-lg border dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">Category allocations</h2>
                <div class="text-sm">
                    <span>Unallocated: </span>
                    <span id="unallocated" class="font-semibold">KSh {{ number_format($unallocated,2) }}</span>
                </div>
            </div>

            <div id="alloc-rows" class="space-y-2">
                @foreach($expenseItems as $idx => $it)
                    <div class="grid grid-cols-12 gap-2 alloc-row items-end">
                        <input type="hidden" name="allocs[{{ $idx }}][id]" value="{{ $it->id }}">
                        <div class="col-span-6">
                            <label class="block text-xs text-gray-600">Category</label>
                            <select name="allocs[{{ $idx }}][category_id]" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected($it->category_id==$cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-4">
                            <label class="block text-xs text-gray-600">Amount</label>
                            <input name="allocs[{{ $idx }}][amount]" type="number" step="0.01" min="0" value="{{ number_format($it->amount,2,'.','') }}" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800 alloc-amount">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-600">Remove</label>
                            <input type="checkbox" name="allocs[{{ $idx }}][_delete]" class="mt-2 h-5 w-5 remove-toggle">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 flex items-center gap-2">
                <button type="button" id="btnAddAlloc" class="px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-800 text-sm">Add allocation</button>
                <button type="button" id="btnAddNewCat" class="px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-800 text-sm">New category</button>
            </div>

            <div id="new-cats" class="mt-4 space-y-2 hidden"></div>

            @error('allocations') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $budget->is_active)) class="mr-2">
                <span class="text-sm">Active</span>
            </label>
            <div class="flex justify-end gap-2">
                <a href="{{ route('budgets.show',$budget) }}" class="px-4 py-2 text-sm rounded-md bg-gray-100 dark:bg-gray-800">Cancel</a>
                <button id="submitBtn" class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white">Save changes</button>
            </div>
        </div>
    </form>
</div>

<script>
const periodSel = document.getElementById('period');
const customDates = document.getElementById('custom-dates');
periodSel.addEventListener('change', () => {
    customDates.classList.toggle('hidden', periodSel.value !== 'custom');
});

const totalEl = document.getElementById('total_amount');
const unallocEl = document.getElementById('unallocated');
const form = document.getElementById('editBudgetForm');
const rowsWrap = document.getElementById('alloc-rows');
const newCatsWrap = document.getElementById('new-cats');
const submitBtn = document.getElementById('submitBtn');

function money(n){ return isNaN(n) ? 0 : parseFloat(n); }

function recalc() {
    let sum = 0;
    document.querySelectorAll('.alloc-row').forEach(row => {
        const amount = money(row.querySelector('.alloc-amount')?.value || 0);
        const removed = row.querySelector('.remove-toggle')?.checked || false;
        if (!removed) sum += amount;
    });
    newCatsWrap.querySelectorAll('.new-cat-amount').forEach(inp => {
        sum += money(inp.value);
    });
    const total = money(totalEl.value);
    const un = Math.max(0, total - sum);
    unallocEl.textContent = 'KSh ' + un.toFixed(2);
    const exceeds = sum > total;
    submitBtn.disabled = exceeds;
    unallocEl.classList.toggle('text-red-600', exceeds);
}

rowsWrap.addEventListener('input', recalc);
newCatsWrap.addEventListener('input', recalc);
totalEl.addEventListener('input', recalc);
document.addEventListener('change', recalc);
document.addEventListener('DOMContentLoaded', recalc);

document.getElementById('btnAddAlloc').addEventListener('click', () => {
    const index = rowsWrap.querySelectorAll('.alloc-row').length + 1000;
    const tpl = `
    <div class="grid grid-cols-12 gap-2 alloc-row items-end">
        <div class="col-span-6">
            <label class="block text-xs text-gray-600">Category</label>
            <select name="allocs_new[${index}][category_id]" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-4">
            <label class="block text-xs text-gray-600">Amount</label>
            <input name="allocs_new[${index}][amount]" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800 alloc-amount">
        </div>
        <div class="col-span-2">
            <label class="block text-xs text-gray-600">Remove</label>
            <button type="button" class="mt-2 px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs btn-remove-row">Remove</button>
        </div>
    </div>`;
    rowsWrap.insertAdjacentHTML('beforeend', tpl);
});

rowsWrap.addEventListener('click', (e)=>{
    if (e.target.classList.contains('btn-remove-row')) {
        e.target.closest('.alloc-row').remove();
        recalc();
    }
});

document.getElementById('btnAddNewCat').addEventListener('click', () => {
    newCatsWrap.classList.remove('hidden');
    const idx = newCatsWrap.querySelectorAll('.new-cat-row').length + 1;
    const tpl = `
    <div class="grid grid-cols-12 gap-2 new-cat-row">
        <div class="col-span-6">
            <label class="block text-xs text-gray-600">New category name</label>
            <input name="new_categories[${idx}][name]" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800">
        </div>
        <div class="col-span-4">
            <label class="block text-xs text-gray-600">Amount</label>
            <input name="new_categories[${idx}][amount]" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800 new-cat-amount">
        </div>
        <div class="col-span-2">
            <label class="block text-xs text-gray-600">Remove</label>
            <button type="button" class="mt-2 px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs btn-remove-newcat">Remove</button>
        </div>
    </div>`;
    newCatsWrap.insertAdjacentHTML('beforeend', tpl);
});

newCatsWrap.addEventListener('click', (e)=>{
    if (e.target.classList.contains('btn-remove-newcat')) {
        e.target.closest('.new-cat-row').remove();
        recalc();
    }
});

form.addEventListener('submit', (e)=>{
    let sum = 0;
    document.querySelectorAll('.alloc-row').forEach(row => {
        const amount = money(row.querySelector('.alloc-amount')?.value || 0);
        const removed = row.querySelector('.remove-toggle')?.checked || false;
        if (!removed) sum += amount;
    });
    newCatsWrap.querySelectorAll('.new-cat-amount').forEach(inp => sum += money(inp.value));
    const total = money(totalEl.value);
    if (sum > total) {
        e.preventDefault();
        alert('Allocated amount exceeds the budget total.');
    }
});
</script>
@endsection
