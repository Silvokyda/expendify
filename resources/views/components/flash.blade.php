@php
    // Map typical laravel flash keys to styles
    $flash = [
        'success' => session('success'),
        'error'   => session('error') ?? session('danger'),
        'warning' => session('warning'),
        'info'    => session('info'),
        'status'  => session('status'), // Breeze/Jetstream uses this
    ];

    $type = null; $message = null;
    foreach ($flash as $k => $v) { if ($v) { $type = $k; $message = $v; break; } }

    $classes = [
        'success' => 'bg-emerald-50 text-emerald-900 ring-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:ring-emerald-800/40',
        'error'   => 'bg-red-50 text-red-900 ring-red-200 dark:bg-red-900/20 dark:text-red-200 dark:ring-red-800/40',
        'warning' => 'bg-amber-50 text-amber-900 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:ring-amber-800/40',
        'info'    => 'bg-blue-50 text-blue-900 ring-blue-200 dark:bg-blue-900/20 dark:text-blue-200 dark:ring-blue-800/40',
        'status'  => 'bg-slate-50 text-slate-900 ring-slate-200 dark:bg-white/10 dark:text-white dark:ring-white/10',
    ];
@endphp

@if($message)
<div
    x-data="{ show: true }"
    x-show="show"
    x-transition.opacity
    class="mx-auto px-4 sm:px-6 lg:px-8 mt-4"
>
    <div class="rounded-xl p-3 sm:p-3.5 ring-1 {{ $classes[$type] ?? $classes['status'] }}">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0a9 9 0 0118 0z"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="flex-1 text-sm leading-6">{{ $message }}</div>
            <button @click="show = false" class="p-2 -m-2 rounded hover:bg-black/5 dark:hover:bg-white/10">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        {{-- Validation errors (optional) --}}
        @if ($errors->any())
            <ul class="mt-2 list-disc list-inside text-sm/6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endif
