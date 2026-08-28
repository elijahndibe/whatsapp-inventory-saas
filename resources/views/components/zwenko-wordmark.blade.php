@props(['variant' => 'purple', 'markClass' => 'h-8 w-8', 'textClass' => 'text-xl'])
{{--
    Full lockup: mark + "Zwenko" text. Use variant="white" on a dark/brand
    surface (mark inverts, text turns white); default "purple" is for
    light surfaces.
--}}
<a href="{{ route('dashboard') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 shrink-0']) }}>
    <x-zwenko-logo :variant="$variant" :class="$markClass" />
    <span @class([
        'font-semibold tracking-tight',
        $textClass,
        'text-white' => $variant === 'white',
        'text-ink dark:text-white' => $variant !== 'white',
    ])>Zwenko</span>
</a>
