@props(['variant' => 'purple'])
{{--
    The Zwenko mark: a rounded square with a bold Z, cut at an angle on the
    top-right and bottom-left to suggest movement/flow (orders moving
    through the platform) rather than a static letterform.

    variant: 'purple' (default, brand purple bg / white mark — used on
    light surfaces), 'white' (white bg / purple mark — for placement on a
    dark or brand-coloured surface), 'mono' (currentColor throughout, for
    contexts that set their own colour, e.g. a dark navbar).
--}}
@php
    $bg = match ($variant) {
        'white' => '#FFFFFF',
        'mono' => 'currentColor',
        default => '#6D28D9',
    };
    $mark = match ($variant) {
        'white' => '#6D28D9',
        'mono' => 'currentColor',
        default => '#FFFFFF',
    };
@endphp
<svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <rect x="0" y="0" width="40" height="40" rx="10" fill="{{ $bg }}" @if($variant === 'mono') fill-opacity="0.12" @endif />
    <path d="M12 12.5C12 11.6716 12.6716 11 13.5 11H26.2C27.0672 11 27.7379 11.7615 27.6141 12.6198C27.5563 13.0209 27.3554 13.3878 27.0485 13.6516L16.9 22.4C16.5931 22.6638 16.3923 23.0307 16.3345 23.4318C16.2107 24.2901 16.8814 25.0516 17.7486 25.0516H26.5C27.3284 25.0516 28 25.7232 28 26.5516V27C28 27.8284 27.3284 28.5 26.5 28.5H13.8C12.9328 28.5 12.2621 27.7385 12.3859 26.8802C12.4437 26.4791 12.6446 26.1122 12.9515 25.8484L23.1 17.1C23.4069 16.8362 23.6077 16.4693 23.6655 16.0682C23.7893 15.2099 23.1186 14.4484 22.2514 14.4484H13.5C12.6716 14.4484 12 13.7768 12 12.9484V12.5Z" fill="{{ $mark }}" />
</svg>
