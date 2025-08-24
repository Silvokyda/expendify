@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    @php
        $expenseItems = $budget->items->where('type','expense')->values();
        $allocated = $expenseItems->sum('amount');
        $unallocated = max(0, (float)$budget->total_amount - (float)$allocated);
    @endphp

    <!-- Page header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-green-900">{{ $budget->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Update period, total, and category allocations.</p>
        </div>

        <a href="{{ route('budgets.show',$budget) }}"
           class="hidden sm:inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Cancel
        </a>
    </div>

    <!-- Card -->
    <form method="POST" action="{{ route('budgets.update', $budget) }}" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-8" id="editBudgetForm">
        @csrf @method('PUT')

        <!-- Top row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-medium text-gray-600">Name</label>
                <input name="name" value="{{ old('name', $budget->name) }}" required
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Period</label>
                <select name="period" id="period"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="monthly" @selected(old('period',$budget->period)==='monthly')>Monthly</option>
                    <option value="weekly"  @selected(old('period',$budget->period)==='weekly')>Weekly</option>
                    <option value="custom"  @selected(old('period',$budget->period)==='custom')>Custom</option>
                </select>
                @error('period') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600">Total amount</label>
                <input name="total_amount" id="total_amount" type="number" step="0.01" min="0"
                       value="{{ old('total_amount', $budget->total_amount) }}"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
                @error('total_amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Custom dates -->
        <div id="custom-dates" class="grid grid-cols-1 md:grid-cols-2 gap-5 {{ old('period',$budget->period)==='custom' ? '' : 'hidden' }}">
            <div>
                <label class="block text-xs font-medium text-gray-600">Start date</label>
                <input name="start_date" type="date"
                       value="{{ old('start_date', optional($budget->start_date)->toDateString()) }}"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">End date</label>
                <input name="end_date" type="date"
                       value="{{ old('end_date', optional($budget->end_date)->toDateString()) }}"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
            </div>
        </div>

        <!-- Allocations -->
        <div class="rounded-xl border border-green-800">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h2 class="font-semibold">Category allocations</h2>
                <div class="text-sm">
                    <span class="text-gray-500">Unallocated:</span>
                    <span id="unallocated" class="ml-2 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 font-semibold">
                        KSh {{ number_format($unallocated,2) }}
                    </span>
                </div>
            </div>

            <div id="alloc-rows" class="p-4 space-y-3">
                @foreach($expenseItems as $idx => $it)
                    <div class="alloc-row rounded-lg border border-gray-200/80 p-3 grid grid-cols-12 gap-3 items-end" data-deleted="0">
                        <input type="hidden" name="allocs[{{ $idx }}][id]" value="{{ $it->id }}">
                        <!-- hidden delete flag; toggled by red bin icon -->
                        <input type="hidden" name="allocs[{{ $idx }}][_delete]" class="delete-flag" value="0">

                        <div class="col-span-12 md:col-span-6">
                            <label class="block text-[11px] font-medium text-gray-500">Category</label>
                            <select name="allocs[{{ $idx }}][category_id]"
                                    class="row-input mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected($it->category_id==$cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-10 md:col-span-4">
                            <label class="block text-[11px] font-medium text-gray-500">Amount</label>
                            <input name="allocs[{{ $idx }}][amount]" type="number" step="0.01" min="0"
                                   value="{{ number_format($it->amount,2,'.','') }}"
                                   class="row-input mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 alloc-amount">
                        </div>

                        <div class="col-span-2 flex items-center justify-end md:justify-center">
                            <!-- red trash icon (toggle remove) -->
                            <button type="button" class="btn-delete-existing inline-flex items-center justify-center rounded-md p-2 text-red-600 hover:bg-red-50"
                                    title="Remove allocation" aria-label="Remove allocation">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <rect x="6" y="9" width="12" height="11" rx="2"></rect>
                                    <rect x="4" y="6" width="16" height="2"></rect>
                                    <rect x="9" y="3" width="6" height="2" rx="1"></rect>
                                </svg>
                            </button>
                            <!-- Undo appears here dynamically -->
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-4 pb-4 pt-2 flex flex-wrap items-center gap-2">
                <button type="button" id="btnAddAlloc"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">
                    Add allocation
                </button>
                <button type="button" id="btnAddNewCat"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">
                    New category
                </button>
            </div>

            <div id="new-cats" class="px-4 pb-4 space-y-3 hidden"></div>

            @error('allocations') <p class="px-4 pb-4 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $budget->is_active))
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Active</span>
            </label>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('budgets.show',$budget) }}"
                   class="sm:hidden inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button id="submitBtn"
                        class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-900">
                    Save changes
                </button>
            </div>
        </div>
    </form>
</div>

<script>
/* ---------- period toggle ---------- */
const periodSel = document.getElementById('period');
const customDates = document.getElementById('custom-dates');
periodSel.addEventListener('change', () => {
  customDates.classList.toggle('hidden', periodSel.value !== 'custom');
});

/* ---------- elements ---------- */
const totalEl    = document.getElementById('total_amount');
const unallocEl  = document.getElementById('unallocated');
const form       = document.getElementById('editBudgetForm');
const rowsWrap   = document.getElementById('alloc-rows');
const newCatsWrap= document.getElementById('new-cats');
const submitBtn  = document.getElementById('submitBtn');

function money(n){ return isNaN(n) ? 0 : parseFloat(n); }

/* ---------- helpers ---------- */
function markDeletedRow(row, isDeleted, kind){
  // kind: 'existing' | 'new' | 'newcat'
  row.dataset.deleted = isDeleted ? '1' : '0';
  row.classList.toggle('opacity-50', isDeleted);

  // existing rows have a hidden delete flag that MUST remain enabled
  const flag = row.querySelector('.delete-flag');
  if (flag && kind === 'existing') flag.value = isDeleted ? '1' : '0';

  // disable all inputs/selects except the delete flag
  row.querySelectorAll('input, select, textarea').forEach(el=>{
    if (el === flag) return;
    el.disabled = isDeleted;
  });

  // add/remove Undo chip next to the bin
  const delBtn = row.querySelector('.btn-delete-existing, .btn-remove-row, .btn-delete-newcat');
  if (!delBtn) return;

  let undo = row.querySelector('.undo-chip');
  if (isDeleted){
    if (!undo){
      undo = document.createElement('button');
      undo.type = 'button';
      undo.className = 'undo-chip ml-2 text-xs text-blue-600 hover:underline';
      undo.textContent = 'Undo';
      delBtn.insertAdjacentElement('afterend', undo);
      undo.addEventListener('click', (ev)=>{
        ev.preventDefault();
        markDeletedRow(row, false, kind);
        recalc();
      });
    } else {
      undo.classList.remove('hidden');
    }
    delBtn.classList.add('bg-red-50');
  } else {
    if (undo) undo.remove();
    delBtn.classList.remove('bg-red-50');
  }
}

function isRowDeleted(row){
  const flag = row.querySelector('.delete-flag');
  if (flag) return flag.value === '1';
  return row.dataset.deleted === '1';
}

/* ---------- totals recalculation ---------- */
function recalc() {
  let sum = 0;

  // existing + newly added allocation rows
  rowsWrap.querySelectorAll('.alloc-row').forEach(row=>{
    if (isRowDeleted(row)) return;
    const amount = money(row.querySelector('.alloc-amount')?.value || 0);
    sum += amount;
  });

  // new category rows (skip soft-deleted)
  newCatsWrap.querySelectorAll('.new-cat-row').forEach(row=>{
    if (row.dataset.deleted === '1') return;
    sum += money(row.querySelector('.new-cat-amount')?.value || 0);
  });

  const total = money(totalEl.value);
  const un = Math.max(0, total - sum);
  unallocEl.textContent = 'KSh ' + un.toFixed(2);

  const exceeds = sum > total;
  submitBtn.disabled = exceeds;
  unallocEl.classList.toggle('text-red-600', exceeds);
  unallocEl.classList.toggle('bg-red-50', exceeds);
}

rowsWrap.addEventListener('input', recalc);
newCatsWrap.addEventListener('input', recalc);
totalEl.addEventListener('input', recalc);
document.addEventListener('change', recalc);
document.addEventListener('DOMContentLoaded', recalc);

/* ---------- add allocation (new row) ---------- */
document.getElementById('btnAddAlloc').addEventListener('click', () => {
  const index = rowsWrap.querySelectorAll('.alloc-row').length + 1000;
  const tpl = `
  <div class="alloc-row rounded-lg border border-gray-200/80 p-3 grid grid-cols-12 gap-3 items-end" data-deleted="0">
    <div class="col-span-12 md:col-span-6">
      <label class="block text-[11px] font-medium text-gray-500">Category</label>
      <select name="allocs_new[${index}][category_id]"
              class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-span-10 md:col-span-4">
      <label class="block text-[11px] font-medium text-gray-500">Amount</label>
      <input name="allocs_new[${index}][amount]" type="number" step="0.01" min="0"
             class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 alloc-amount">
    </div>
    <div class="col-span-2 flex items-center justify-end md:justify-center">
      <button type="button"
              class="btn-remove-row inline-flex items-center justify-center rounded-md p-2 text-red-600 hover:bg-red-50"
              title="Remove" aria-label="Remove">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <rect x="6" y="9" width="12" height="11" rx="2"></rect>
          <rect x="4" y="6" width="16" height="2"></rect>
          <rect x="9" y="3" width="6" height="2" rx="1"></rect>
        </svg>
      </button>
    </div>
  </div>`;
  rowsWrap.insertAdjacentHTML('beforeend', tpl);
  recalc();
});

/* ---------- click handlers: delete / undo ---------- */
rowsWrap.addEventListener('click', (e)=>{
  // existing allocation rows (have hidden delete flag)
  const existingBtn = e.target.closest('.btn-delete-existing');
  if (existingBtn){
    const row = existingBtn.closest('.alloc-row');
    const toDelete = !isRowDeleted(row);
    markDeletedRow(row, toDelete, 'existing');
    recalc();
    return;
  }

  // newly added allocation rows
  const newBtn = e.target.closest('.btn-remove-row');
  if (newBtn){
    const row = newBtn.closest('.alloc-row');
    const toDelete = row.dataset.deleted !== '1';
    markDeletedRow(row, toDelete, 'new');
    recalc();
  }
});

// add new category row
document.getElementById('btnAddNewCat').addEventListener('click', () => {
  newCatsWrap.classList.remove('hidden');
  const idx = newCatsWrap.querySelectorAll('.new-cat-row').length + 1;
  const tpl = `
  <div class="new-cat-row rounded-lg border border-gray-200/80 p-3 grid grid-cols-12 gap-3 items-end" data-deleted="0">
    <div class="col-span-12 md:col-span-6">
      <label class="block text-[11px] font-medium text-gray-500">New category name</label>
      <input name="new_categories[${idx}][name]"
             class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div class="col-span-10 md:col-span-4">
      <label class="block text-[11px] font-medium text-gray-500">Amount</label>
      <input name="new_categories[${idx}][amount]" type="number" step="0.01" min="0"
             class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 new-cat-amount">
    </div>
    <div class="col-span-2 flex items-center justify-end md:justify-center">
      <button type="button"
              class="btn-delete-newcat inline-flex items-center justify-center rounded-md p-2 text-red-600 hover:bg-red-50"
              title="Remove" aria-label="Remove">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <rect x="6" y="9" width="12" height="11" rx="2"></rect>
          <rect x="4" y="6" width="16" height="2"></rect>
          <rect x="9" y="3" width="6" height="2" rx="1"></rect>
        </svg>
      </button>
    </div>
  </div>`;
  newCatsWrap.insertAdjacentHTML('beforeend', tpl);
  recalc();
});

newCatsWrap.addEventListener('click', (e)=>{
  const delBtn = e.target.closest('.btn-delete-newcat');
  if (delBtn){
    const row = delBtn.closest('.new-cat-row');
    const toDelete = row.dataset.deleted !== '1';
    markDeletedRow(row, toDelete, 'newcat');
    recalc();
  }
});

/* ---------- submit guard: ignore soft-deleted ---------- */
form.addEventListener('submit', (e)=>{
  let sum = 0;

  rowsWrap.querySelectorAll('.alloc-row').forEach(row=>{
    if (isRowDeleted(row)) return;
    sum += money(row.querySelector('.alloc-amount')?.value || 0);
  });

  newCatsWrap.querySelectorAll('.new-cat-row').forEach(row=>{
    if (row.dataset.deleted === '1') return;
    sum += money(row.querySelector('.new-cat-amount')?.value || 0);
  });

  const total = money(totalEl.value);
  if (sum > total) {
    e.preventDefault();
    alert('Allocated amount exceeds the budget total.');
  }
});
</script>
@endsection
