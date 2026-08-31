@props(['label', 'value', 'change' => null, 'trend' => null, 'icon' => null, 'color' => 'brand', 'layout' => 'default'])
@php
// Each stat gets its own icon-badge tint so the row reads at a glance
// instead of four identical purple squares — purple stays reserved for
// the single most important figure (Today's Sales).
$badgeColorMap = [
    'brand' => 'bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300',
    'success' => 'bg-success-bg dark:bg-green-900/30 text-success-strong dark:text-green-400',
    'info' => 'bg-info-bg dark:bg-blue-900/30 text-info dark:text-blue-400',
    'warning' => 'bg-warning-bg dark:bg-amber-900/30 text-warning-strong dark:text-amber-400',
];
$badgeStyles = $badgeColorMap[$color] ?? $badgeColorMap['brand'];
$changeClasses = [
    'text-success-strong' => $trend === 'up',
    'text-danger' => $trend === 'down',
    'text-warning-strong' => $trend === 'warning',
    'text-gray-500 dark:text-gray-400' => ! in_array($trend, ['up', 'down', 'warning']),
];
@endphp
@if ($layout === 'leading-icon')
    {{-- Icon sits beside the label instead of opposite it — used only on
         the Dashboard's own top stats row to match a supplied reference
         design; every other stat-card usage (Payments, Inventory, Admin
         dashboard) keeps the default layout above, untouched. --}}
    <x-card class="min-w-0">
        <div class="flex items-center gap-2.5 min-w-0">
            @if ($icon)
                <span class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center {{ $badgeStyles }}">
                    <x-icon :name="$icon" class="w-4 h-4" />
                </span>
            @endif
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $label }}</p>
        </div>
        <p class="mt-2 text-2xl font-bold text-ink dark:text-gray-100 truncate">{{ $value }}</p>
        @if ($change)
            <p @class(array_merge(['mt-1 text-xs font-medium flex items-center gap-1'], $changeClasses))>
                {{ $change }}
            </p>
        @endif
    </x-card>
@else
    <x-card class="min-w-0">
        <div class="flex items-start justify-between gap-2">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide truncate">{{ $label }}</p>
            @if ($icon)
                <span class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center {{ $badgeStyles }}">
                    <x-icon :name="$icon" class="w-4 h-4" />
                </span>
            @endif
        </div>
        <p class="mt-1.5 text-2xl font-semibold text-ink dark:text-gray-100 truncate">{{ $value }}</p>
        @if ($change)
            <p @class(array_merge(['mt-1 text-xs font-medium flex items-center gap-1'], $changeClasses))>
                {{ $change }}
            </p>
        @endif
    </x-card>
@endif
