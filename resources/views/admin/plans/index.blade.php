<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Plans') }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.features.index') }}" class="text-xs font-semibold uppercase text-brand-600 dark:text-brand-400 hover:underline">{{ __('Manage Features') }}</a>
                <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 text-white rounded-md text-sm font-semibold hover:bg-brand-800">
                    <x-icon name="plus" class="w-4 h-4" />
                    {{ __('New Plan') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Name') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Price') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Default') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Subscribers') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Active') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($plans as $plan)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $plan->isFree() ? 'Free' : $plan->currencySymbol().number_format($plan->price, 0) }}</td>
                            <td class="px-4 py-3">
                                @if ($plan->is_default)
                                    <x-badge variant="brand">{{ __('Default') }}</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $plan->subscriptions_count }}</td>
                            <td class="px-4 py-3"><x-badge :variant="$plan->is_active ? 'success' : 'neutral'">{{ $plan->is_active ? __('Active') : __('Inactive') }}</x-badge></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-brand-600 dark:text-brand-400 hover:underline text-xs">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
