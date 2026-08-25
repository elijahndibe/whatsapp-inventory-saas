<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ __('Edit Plan') }}: {{ $plan->name }}</h2>
    </x-slot>

    <div class="max-w-3xl bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
            @method('PUT')
            @include('admin.plans._form')
        </form>
    </div>
</x-admin-layout>
