@extends('layouts.app')

@section('content')
@php
    // lightweight emoji mapping (no extra libs)
    $iconMap = [
        'ph:money' => '💵','ph:briefcase'=>'💼','ph:fork-knife'=>'🍴','ph:house'=>'🏠','ph:car'=>'🚗',
        'ph:airplane'=>'✈️','ph:plug'=>'🔌','ph:film-strip'=>'🎞️','ph:heartbeat'=>'❤️','ph:book'=>'📚',
        'ph:piggy-bank'=>'🐷',
    ];
    $catEmoji = function($icon){ return $icon ? ($iconMap[$icon] ?? '🏷️') : '🏷️'; };
@endphp

<div class="max-w-5xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-semibold mb-2">Create Budget — Step 2</h1>
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
        Allocate your <b>{{ ucfirst($step1['period']) }}</b> budget of
        <b>KSh {{ number_format($step1['total_amount'], 2) }}</b>.
        <span class="ml-2">Unallocated: <b id="unallocated">KSh {{ number_format($step1['total_amount'], 2) }}</b></span>
    </p>

    <form method="POST" action="{{ route('budgets.wizard.step2') }}" id="items-form" class="space-y-6">
        @csrf
        <input type="hidden" id="total_amount" value="{{ (float)$step1['total_amount'] }}"/>

        {{-- TYPE FILTER --}}
        <div class="flex gap-2 mb-2">
            <button type="button" data-filter="all"
                    class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm"
                    onclick="filterType(this)">All</button>
            <button type="button" data-filter="income"
                    class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm"
                    onclick="filterType(this)">Income</button>
            <button type="button" data-filter="expense"
                    class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm"
                    onclick="filterType(this)">Expense</button>
            <button type="button" data-filter="saving"
                    class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm"
                    onclick="filterType(this)">Saving</button>
        </div>

        {{-- CATEGORY GRID --}}
        <div id="cat-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            @foreach($categories as $cat)
                <button type="button"
                        class="cat-card rounded-xl border bg-white dark:border-gray-700 p-3 text-left hover:shadow"
                        data-kind="category"
                        data-type="{{ $cat->type }}"
                        data-id="{{ $cat->id }}"
                        data-name="{{ $cat->name }}"
                        data-icon="{{ $cat->icon ?? '' }}"
                        onclick="addCategory(this)">
                    <div class="text-2xl">{{ $catEmoji($cat->icon) }}</div>
                    <div class="mt-1 font-medium">{!! e($cat->name) !!} {!! $cat->user_id ? '<span class="text-xs text-gray-500">(custom)</span>' : '' !!}</div>
                    <div class="text-xs mt-0.5 text-gray-500">
                        @if($cat->type==='both') Income &amp; Expense @else {{ ucfirst($cat->type) }} @endif
                    </div>
                </button>
            @endforeach

            @if(isset($savingsGoals) && $savingsGoals->count())
                @foreach($savingsGoals as $g)
                    <button type="button"
                            class="goal-card rounded-xl border bg-white dark:border-gray-700 p-3 text-left hover:shadow"
                            data-kind="goal"
                            data-type="saving"
                            data-id="{{ $g->id }}"
                            data-name="{{ $g->name }}"
                            onclick="addGoal(this)">
                        <div class="text-2xl">🐷</div>
                        <div class="mt-1 font-medium">{{ $g->name }}</div>
                        <div class="text-xs mt-0.5 text-gray-500">Saving</div>
                    </button>
                @endforeach
            @endif
        </div>

        {{-- ADD CUSTOM --}}
        <div class="mt-2">
            <button type="button"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-50"
                    onclick="addCustom()">
                Add custom category
            </button>
        </div>

        {{-- SELECTED ITEMS --}}
        <div class="rounded-lg border dark:border-gray-700 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr>
                    <th class="px-4 py-2 text-left text-xs uppercase">Type</th>
                    <th class="px-4 py-2 text-left text-xs uppercase">Item</th>
                    <th class="px-4 py-2 text-left text-xs uppercase">Amount</th>
                    <th class="px-4 py-2"></th>
                </tr>
                </thead>
                <tbody id="items-rows" class="divide-y divide-gray-100 dark:divide-gray-800"></tbody>
            </table>

            @error('items') <p class="px-4 pb-4 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('budgets.create') }}"
               class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Back
            </a>
            <button id="submitBtn"
                    class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-900">
                Review
            </button>
        </div>
    </form>
</div>

<script>
const rowsBody  = document.getElementById('items-rows');
const totalEl   = document.getElementById('total_amount');
const unallocEl = document.getElementById('unallocated');
const submitBtn = document.getElementById('submitBtn');

function filterType(btn){
  const t = btn.dataset.filter;
  document.querySelectorAll('#cat-grid .cat-card, #cat-grid .goal-card').forEach(el=>{
    const ty = el.dataset.type;
    el.classList.toggle('hidden', t !== 'all' && ty !== t && !(ty==='both' && (t==='income'||t==='expense')));
  });
}

let rowIndex = 0;
function nextIndex(){ return rowIndex++; }

function addCategory(el){
  const kind = el.dataset.kind; // 'category'
  const type = el.dataset.type; // income|expense|saving|both
  const id   = el.dataset.id;
  const name = el.dataset.name;
  const icon = el.dataset.icon || '';

  // determine default + allowed
  let defaultType = (type === 'both') ? 'expense' : type;
  const allowsIncome  = (type === 'income' || type === 'both');
  const allowsExpense = (type === 'expense' || type === 'both');
  const allowsSaving  = (type === 'saving');

  const i = nextIndex();
  const disabled = {
    income:  !allowsIncome,
    expense: !allowsExpense,
    saving:  !allowsSaving
  };

  rowsBody.insertAdjacentHTML('beforeend', `
    <tr data-kind="category">
      <td class="px-4 py-2">
        <select name="items[${i}][type]" class="rounded-md border-gray-300 type" ${allowsSaving ? '' : ''}>
          <option value="income"  ${defaultType==='income'  ? 'selected' : ''} ${disabled.income  ? 'disabled' : ''}>Income</option>
          <option value="expense" ${defaultType==='expense' ? 'selected' : ''} ${disabled.expense ? 'disabled' : ''}>Expense</option>
          <option value="saving"  ${defaultType==='saving'  ? 'selected' : ''} ${disabled.saving  ? 'disabled' : ''}>Saving</option>
        </select>
      </td>
      <td class="px-4 py-2">
        <input type="hidden" name="items[${i}][kind]" value="${kind}">
        <input type="hidden" name="items[${i}][category_id]" value="${id}">
        <input type="hidden" name="items[${i}][icon]" value="${icon}">
        <div class="flex items-center gap-2">
          <span class="text-xl">${icon || '' ? '' : ''}</span>
          <span>${name}</span>
        </div>
      </td>
      <td class="px-4 py-2">
        <input name="items[${i}][amount]" type="number" step="0.01" min="0"
               class="amount mt-1 w-40 rounded-md border-gray-300" />
      </td>
      <td class="px-4 py-2 text-right">
        <button type="button" class="px-2 py-1 text-red-600 hover:underline" onclick="removeRow(this)">Remove</button>
      </td>
    </tr>
  `);
  recalc();
}

function addGoal(el){
  const kind = el.dataset.kind; // 'goal'
  const id   = el.dataset.id;
  const name = el.dataset.name;

  const i = nextIndex();
  rowsBody.insertAdjacentHTML('beforeend', `
    <tr data-kind="goal">
      <td class="px-4 py-2">
        <select name="items[${i}][type]" class="rounded-md border-gray-300 type" disabled>
          <option value="saving" selected>Saving</option>
        </select>
      </td>
      <td class="px-4 py-2">
        <input type="hidden" name="items[${i}][kind]" value="${kind}">
        <input type="hidden" name="items[${i}][savings_goal_id]" value="${id}">
        <div class="flex items-center gap-2">
          <span class="text-xl">🐷</span>
          <span>${name}</span>
        </div>
      </td>
      <td class="px-4 py-2">
        <input name="items[${i}][amount]" type="number" step="0.01" min="0"
               class="amount mt-1 w-40 rounded-md border-gray-300" />
      </td>
      <td class="px-4 py-2 text-right">
        <button type="button" class="px-2 py-1 text-red-600 hover:underline" onclick="removeRow(this)">Remove</button>
      </td>
    </tr>
  `);
  recalc();
}

function addCustom(){
  const i = nextIndex();
  rowsBody.insertAdjacentHTML('beforeend', `
    <tr data-kind="custom">
      <td class="px-4 py-2">
        <select name="items[${i}][type]" class="rounded-md border-gray-300 type">
          <option value="income">Income</option>
          <option value="expense" selected>Expense</option>
          <option value="saving">Saving</option>
        </select>
      </td>
      <td class="px-4 py-2">
        <input type="hidden" name="items[${i}][kind]" value="custom">
        <div class="flex items-center gap-2">
          <input name="items[${i}][name]" placeholder="New category name"
                 class="mt-1 w-56 rounded-md border-gray-300" />
          <input name="items[${i}][icon]" placeholder="icon (optional)"
                 class="mt-1 w-40 rounded-md border-gray-300" />
        </div>
      </td>
      <td class="px-4 py-2">
        <input name="items[${i}][amount]" type="number" step="0.01" min="0"
               class="amount mt-1 w-40 rounded-md border-gray-300" />
      </td>
      <td class="px-4 py-2 text-right">
        <button type="button" class="px-2 py-1 text-red-600 hover:underline" onclick="removeRow(this)">Remove</button>
      </td>
    </tr>
  `);
  recalc();
}

function removeRow(btn){
  const tr = btn.closest('tr');
  tr.parentNode.removeChild(tr);
  recalc();
}

function recalc(){
  const rows = [...document.querySelectorAll('#items-rows tr')];
  let sum = 0;
  rows.forEach(tr=>{
    const type = tr.querySelector('.type')?.value || tr.querySelector('select[name*="[type]"]')?.value || '';
    const amt  = parseFloat(tr.querySelector('.amount')?.value || '0');
    if (['expense','saving'].includes(type)) sum += isNaN(amt) ? 0 : amt;
  });
  const total = parseFloat(totalEl.value || '0');
  const left  = Math.max(0, total - sum);
  unallocEl.textContent = 'KSh ' + left.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
  const exceeds = sum > total;
  submitBtn.disabled = exceeds || rows.length === 0;
  unallocEl.classList.toggle('text-red-600', exceeds);
}

document.addEventListener('input', (e)=>{
  if (e.target.closest('#items-rows')) recalc();
});
document.addEventListener('DOMContentLoaded', recalc);
</script>
@endsection
