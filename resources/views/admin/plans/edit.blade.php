<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Edit Plan') }}: {{ $plan->name }}</h2>
    </x-slot>

    <div class="max-w-3xl bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
            @method('PUT')
            @include('admin.plans._form')
        </form>
    </div>
</x-admin-layout>
