<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Application Log') }}</h2>
    </x-slot>

    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
        <pre class="text-xs text-gray-300 whitespace-pre-wrap">{{ $content }}</pre>
    </div>
</x-admin-layout>
