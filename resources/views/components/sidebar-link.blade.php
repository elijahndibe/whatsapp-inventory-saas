@props(['href', 'active' => false, 'icon' => null])
<a href="{{ $href }}" {{ $attributes->merge(['class' =>
    'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition '.
    ($active
        ? 'bg-brand-700 text-white'
        : 'text-gray-300 hover:text-white hover:bg-white/10')
]) }}>
    @if ($icon)
        <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
