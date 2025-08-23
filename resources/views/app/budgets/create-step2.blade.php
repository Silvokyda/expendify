@extends('layouts.app')

@section('content')
@php
    // simple emoji mapping so icons look nice without extra libs
    $iconMap = [
        'ph:money' => '💵','ph:briefcase'=>'💼','ph:fork-knife'=>'🍴','ph:house'=>'🏠','ph:car'=>'🚗',
        'ph:airplane'=>'✈️','ph:plug'=>'🔌','ph:film-strip'=>'🎞️','ph:heartbeat'=>'❤️','ph:book'=>'📚',
        'ph:piggy-bank'=>'🐷',
    ];
    $catEmoji = function($icon){ return $icon ? ($iconMap[$icon] ?? '🏷️') : '🏷️'; };
@endphp

<div class="max-w-5xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-2">Create Budget — Step 2</h1>
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
        Allocate your <b>{{ ucfirst($step1['period']) }}</b> budget of
        <b>KSh {{ number_format($step1['total_amount'], 2) }}</b>.
        <span class="ml-2">Unallocated: <b id="unallocated">KSh {{ number_format($step1['total_amount'], 2) }}</b></span>
    </p>

    <form method="POST" action="{{ route('budgets.wizard.step2') }}" id="items-form">
        @csrf
        <input type="hidden" id="total_amount" value="{{ (float)$step1['total_amount'] }}"/>

        {{-- TYPE FILTER --}}
        <div class="flex gap-2 mb-4">
            <button type="button" data-filter="all" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm" onclick="filterType(this)">All</button>
            <button type="button" data-filter="income" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm" onclick="filterType(this)">Income</button>
            <button type="button" data-filter="expense" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm" onclick="filterType(this)">Expense</button>
            <button type="button" data-filter="saving" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-800 text-sm" onclick="filterType(this)">Saving</button>
        </div>

        {{-- CATEGORY GRID --}}
        <div id="cat-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            @foreach($categories as $cat)
                <button type="button"
                        class="cat-card rounded-xl border bg-white dark:border-gray-700 p-3 text-left hover:shadow"
                        data-type="{{ $cat->type }}"
                        data-id="{{ $cat->id }}"
                        data-name="{{ $cat->name }}"
                        data-icon="{{ $cat->icon ?? '' }}">
                    <div class="text-2xl">{{ $catEmoji($cat->icon) }}</div>
                    <div class="mt-1 font-medium">{{ $cat->name }} {!! $cat->user_id ? '<span class="text-xs text-gray-500">(custom)</span>' : '' !!}</div>
                    <div class="text-xs mt-0.5 text-gray-500">
                        @if($cat->type==='both') Income & Expense @else {{ ucfirst($cat->type) }} @endif
                    </div>
                </button>
            @endforeach

            {{-- Savings Goals as tiles --}}
            @foreach($savingsGoals as $g)
                <button type="button"
                        class="goal-card rounded-xl border dark:border-gray-700 p-3 text-left hover:shadow"
                        data-id="{{ $g->id }}"
                        data-name="{{ $g->name }}">
                    <div class="text-2xl">🐷</div>
                    <div class="mt-1 font-medium">{{ $g->name }}</div>
                    <div class="text-xs mt-0.5 text-gray-500">Saving Goal</div>
                </button>
            @endforeach

            {{-- Custom category tile --}}
            <button type="button" class="rounded-xl border-dashed border-2 dark:border-gray-700 p-3 text-left hover:shadow" onclick="openCustom()">
                <div class="text-2xl">➕</div>
                <div class="mt-1 font-medium">Custom</div>
                <div class="text-xs mt-0.5 text-gray-500">Create your own</div>
            </button>
        </div>

        {{-- SELECTED ITEMS LIST --}}
        <div class="mt-8 rounded-lg border dark:border-gray-700 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr>
                    <th class="px-4 py-2 text-left text-xs uppercase">Type</th>
                    <th class="px-4 py-2 text-left text-xs uppercase">Item</th>
                    <th class="px-4 py-2 text-left text-xs uppercase">Amount</th>
                    <th class="px-4 py-2 text-left text-xs uppercase">Action</th>
                </tr>
                </thead>
                <tbody id="rows" class="divide-y divide-gray-100 dark:divide-gray-800"></tbody>
            </table>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <a href="{{ route('budgets.create') }}" class="px-4 py-2 text-sm rounded-md bg-gray-100 dark:bg-gray-800">Back</a>
            <button class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white">Continue</button>
        </div>

        {{-- hidden index for POST --}}
        <input type="hidden" id="row-idx" value="0"/>
    </form>
</div>

{{-- Custom modal (minimal) --}}
<div id="custom-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-xl p-4 w-full max-w-md">
        <h3 class="font-semibold mb-3">Create Custom Category</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="text-sm">Name</label>
                <input id="custom-name" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800"/>
            </div>
            <div>
                <label class="text-sm">Type</label>
                <select id="custom-type" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800">
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                    <option value="saving">Saving</option>
                </select>
            </div>
            <div>
                <label class="text-sm">Icon (optional)</label>
                <select id="custom-icon" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800">
                    <option value="">—</option>
                    <option value="ph:house">🏠 House</option>
                    <option value="ph:fork-knife">🍴 Food</option>
                    <option value="ph:car">🚗 Transport</option>
                    <option value="ph:money">💵 Money</option>
                </select>
            </div>
            <div>
                <label class="text-sm">Amount</label>
                <input id="custom-amount" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800"/>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <button class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-800" onclick="closeCustom()">Cancel</button>
            <button class="px-3 py-2 rounded-md bg-blue-600 text-white" onclick="addCustom()">Add</button>
        </div>
    </div>
</div>

<script>
const total = parseFloat(document.getElementById('total_amount').value || '0');

function filterType(btn){
    const type = btn.getAttribute('data-filter');
    document.querySelectorAll('#cat-grid > button').forEach(el=>{
        const t = el.classList.contains('goal-card') ? 'saving' : el.getAttribute('data-type');
        el.classList.toggle('hidden', !(type==='all' || t===type || (t==='both' && (type==='income' || type==='expense'))));
    });
}

document.querySelectorAll('.cat-card').forEach(card=>{
    card.addEventListener('click', ()=>{
        const id   = card.getAttribute('data-id');
        const name = card.getAttribute('data-name');
        const icon = card.getAttribute('data-icon') || '';
        const ctype= card.getAttribute('data-type');

        // If 'both', ask user whether income or expense (quick prompt for now)
        let type = ctype;
        if (ctype === 'both') {
            type = prompt('Use as "income" or "expense"?', 'expense') || 'expense';
            if (!['income','expense'].includes(type)) type = 'expense';
        }
        // saving categories force saving
        if (ctype === 'saving') type = 'saving';

        addRow({kind:'category', type, category_id:id, name, icon, amount:0});
    });
});

document.querySelectorAll('.goal-card').forEach(card=>{
    card.addEventListener('click', ()=>{
        const id   = card.getAttribute('data-id');
        const name = card.getAttribute('data-name');
        addRow({kind:'goal', type:'saving', savings_goal_id:id, name, amount:0});
    });
});

function openCustom(){ document.getElementById('custom-modal').classList.remove('hidden'); document.getElementById('custom-modal').classList.add('flex'); }
function closeCustom(){ document.getElementById('custom-modal').classList.add('hidden'); document.getElementById('custom-modal').classList.remove('flex'); }
function addCustom(){
    const name  = document.getElementById('custom-name').value?.trim();
    const type  = document.getElementById('custom-type').value;
    const icon  = document.getElementById('custom-icon').value;
    const amount= parseFloat(document.getElementById('custom-amount').value || '0');
    if(!name) return alert('Enter a name.');
    addRow({kind:'custom', type, name, icon, amount});
    closeCustom();
    document.getElementById('custom-name').value='';
    document.getElementById('custom-amount').value='0';
}

function addRow(item){
    const rows = document.getElementById('rows');
    const idxI = document.getElementById('row-idx');
    const i    = parseInt(idxI.value,10);

    const tr = document.createElement('tr');

    tr.innerHTML = `
        <td class="px-4 py-2">
            <input type="hidden" name="items[${i}][kind]" value="${item.kind}"/>
            <select name="items[${i}][type]" class="type rounded-md border-gray-300 dark:bg-gray-800">
                <option value="income" ${item.type==='income'?'selected':''}>Income</option>
                <option value="expense" ${item.type==='expense'?'selected':''}>Expense</option>
                <option value="saving" ${item.type==='saving'?'selected':''}>Saving</option>
            </select>
        </td>
        <td class="px-4 py-2">
            <div class="flex items-center gap-2">
                <span class="text-xl">${item.icon ? ' ' : ''}</span>
                <span>${item.name || (item.kind==='goal'?'Goal #'+item.savings_goal_id:'Custom')}</span>
            </div>
            ${item.category_id ? `<input type="hidden" name="items[${i}][category_id]" value="${item.category_id}"/>` : ''}
            ${item.savings_goal_id ? `<input type="hidden" name="items[${i}][savings_goal_id]" value="${item.savings_goal_id}"/>` : ''}
            ${item.kind==='custom' ? `
                <input type="hidden" name="items[${i}][name]" value="${item.name}"/>
                <input type="hidden" name="items[${i}][icon]" value="${item.icon || ''}"/>
            ` : ''}
        </td>
        <td class="px-4 py-2">
            <input type="number" name="items[${i}][amount]" min="0" step="0.01" value="${item.amount}"
                   class="amount w-full rounded-md border-gray-300 dark:bg-gray-800" oninput="recalc()"/>
        </td>
        <td class="px-4 py-2 text-right">
            <button class="text-red-600 text-sm" type="button" onclick="this.closest('tr').remove(); recalc();">Remove</button>
        </td>
    `;

    // Disable type for goals & saving categories
    if (item.kind==='goal' || item.type==='saving') {
        setTimeout(()=>{ tr.querySelector('.type').value='saving'; tr.querySelector('.type').setAttribute('disabled','disabled'); },0);
    }

    rows.appendChild(tr);
    idxI.value = i+1;
    recalc();
}

function recalc() {
    const rows = [...document.querySelectorAll('#rows tr')];
    const allocated = rows.reduce((s,tr)=>{
        const type = tr.querySelector('.type')?.value;
        const amt  = parseFloat(tr.querySelector('.amount')?.value || '0');
        return s + (['expense','saving'].includes(type) ? amt : 0);
    },0);
    const left = Math.max(0, total - allocated);
    document.getElementById('unallocated').textContent = 'KSh ' + left.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
}
</script>
@endsection
