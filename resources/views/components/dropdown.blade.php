{{-- align="top" means "opens upward, anchored above the trigger" — for a
     trigger near the bottom of the viewport (e.g. the sidebar's account
     menu), where opening downward like the other two alignments would run
     the content off-screen/behind whatever sits below it. --}}
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'ltr:origin-bottom-left rtl:origin-bottom-right start-0',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$positionClasses = $align === 'top' ? 'bottom-full mb-2' : 'mt-2';

// Only '48' ever got the 'w-' prefix here before — any other value (e.g.
// notification-bell's width="80", the sidebar account menu's width="56")
// was used as a bare, invalid class ("80", "56"), applying no width
// utility at all. Prefix anything that isn't already a full class.
$width = str_starts_with($width, 'w-') ? $width : 'w-'.$width;
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false" @keydown.escape.window="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 {{ $positionClasses }} {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
            style="display: none;"
            role="menu"
            @click="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
