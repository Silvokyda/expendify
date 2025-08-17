@props(['active' => false, 'icon' => null])

@php
// Base styles
$base = 'relative flex items-center gap-3 px-4 py-3 text-lg md:text-sm w-full font-medium transition duration-300 ease-in-out';

// Inactive: right-only rounded pill, brand hover
$inactive = '  rounded-l-none hover:bg-[#0f5334] hover:text-white';

$activeCls = implode(' ', [
'text-[#0f5334] bg-[#f2ece3]',
]);

$classes = $base.' '.(($active ?? false) ? $activeCls : $inactive);
@endphp

<a {{ $attributes->merge(['class' => $classes, 'aria-current' => ($active ? 'page' : null)]) }}>
    @if ($icon)
    @if (is_string($icon))
    <x-dynamic-component :component="$icon" class="w-5 h-5 shrink-0" />
    @else
    {!! $icon !!}
    @endif
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>