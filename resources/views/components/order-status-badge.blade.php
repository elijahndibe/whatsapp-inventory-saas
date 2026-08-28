@props(['status'])

@php
$colors = [
    'pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    'awaiting_payment' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    'confirmed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    'processing' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
    'ready' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
    'shipped' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300',
    'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    'refunded' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
];
@endphp

<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$status] ?? $colors['pending'] }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
