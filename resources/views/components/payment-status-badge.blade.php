@props(['status'])

@php
$colors = [
    'pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    'refunded' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    'partially_refunded' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
];
@endphp

<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$status] ?? $colors['pending'] }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
