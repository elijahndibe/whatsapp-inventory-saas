<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('New Plan') }}</h2>
    </x-slot>

    <div class="max-w-3xl bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
        <form method="POST" action="{{ route('admin.plans.store') }}">
            @include('admin.plans._form')
        </form>
    </div>
</x-admin-layout>
