@props(['variant' => 'neutral'])
@php
$styles = [
    'success' => 'bg-success-bg text-success dark:bg-green-900/30 dark:text-green-400',
    'warning' => 'bg-warning-bg text-warning dark:bg-amber-900/30 dark:text-amber-400',
    'danger' => 'bg-danger-bg text-danger dark:bg-red-900/30 dark:text-red-400',
    'info' => 'bg-info-bg text-info dark:bg-blue-900/30 dark:text-blue-400',
    'brand' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300',
    'neutral' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
][$variant] ?? 'bg-gray-100 text-gray-600';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {$styles}"]) }}>
    {{ $slot }}
</span>
