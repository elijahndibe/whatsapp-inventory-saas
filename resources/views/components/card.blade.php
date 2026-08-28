@props(['padded' => true])
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card '.($padded ? 'p-5' : '')]) }}>
    {{ $slot }}
</div>
