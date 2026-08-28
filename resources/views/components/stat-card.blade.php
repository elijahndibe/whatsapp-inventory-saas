@props(['label', 'value', 'change' => null, 'trend' => null, 'icon' => null])
<x-card class="min-w-0">
    <div class="flex items-start justify-between gap-2">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide truncate">{{ $label }}</p>
        @if ($icon)
            <span class="shrink-0 w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 flex items-center justify-center">
                <x-icon :name="$icon" class="w-4 h-4" />
            </span>
        @endif
    </div>
    <p class="mt-1.5 text-2xl font-semibold text-ink dark:text-gray-100 truncate">{{ $value }}</p>
    @if ($change)
        <p @class([
            'mt-1 text-xs font-medium flex items-center gap-1',
            'text-success-strong' => $trend === 'up',
            'text-danger' => $trend === 'down',
            'text-gray-500 dark:text-gray-400' => ! in_array($trend, ['up', 'down']),
        ])>
            {{ $change }}
        </p>
    @endif
</x-card>
