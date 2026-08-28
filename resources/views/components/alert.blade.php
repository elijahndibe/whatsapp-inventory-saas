@props(['type' => 'info'])
@php
$styles = [
    'success' => ['bg' => 'bg-success-bg dark:bg-green-900/20', 'text' => 'text-green-800 dark:text-green-300', 'icon' => 'check-circle', 'iconColor' => 'text-success'],
    'error' => ['bg' => 'bg-danger-bg dark:bg-red-900/20', 'text' => 'text-red-800 dark:text-red-300', 'icon' => 'x-circle', 'iconColor' => 'text-danger'],
    // iconColor uses text-warning-strong, not the DEFAULT token — the spec's
    // #F59E0B warning tone is only ~2.1:1 against this bg, below even the
    // 3:1 WCAG minimum for a meaningful icon (see tailwind.config.js).
    'warning' => ['bg' => 'bg-warning-bg dark:bg-amber-900/20', 'text' => 'text-amber-800 dark:text-amber-300', 'icon' => 'alert-triangle', 'iconColor' => 'text-warning-strong'],
    'info' => ['bg' => 'bg-info-bg dark:bg-blue-900/20', 'text' => 'text-blue-800 dark:text-blue-300', 'icon' => 'info', 'iconColor' => 'text-info'],
][$type] ?? $styles['info'];
@endphp
<div {{ $attributes->merge(['class' => "rounded-lg {$styles['bg']} px-4 py-3 flex items-start gap-2.5 text-sm {$styles['text']}"]) }} role="alert">
    <x-icon :name="$styles['icon']" class="w-5 h-5 shrink-0 mt-0.5 {{ $styles['iconColor'] }}" />
    <div class="min-w-0">{{ $slot }}</div>
</div>
