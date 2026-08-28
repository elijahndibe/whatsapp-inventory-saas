@props(['icon' => 'box', 'title', 'description' => null])
<div {{ $attributes->merge(['class' => 'text-center py-12 px-6']) }}>
    <div class="mx-auto w-12 h-12 rounded-full bg-brand-50 dark:bg-brand-950/50 flex items-center justify-center">
        <x-icon :name="$icon" class="w-6 h-6 text-brand-700 dark:text-brand-300" />
    </div>
    <p class="mt-4 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
