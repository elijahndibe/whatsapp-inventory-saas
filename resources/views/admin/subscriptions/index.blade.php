<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Subscriptions') }}</h2>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="expired" @selected(request('status') === 'expired')>{{ __('Expired') }}</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('Cancelled') }}</option>
            </select>
            <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm">{{ __('Filter') }}</button>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
            @if ($subscriptions->isEmpty())
                <x-empty-state icon="payments" :title="__('No subscriptions found')" :description="__('Try a different status filter, or check back once businesses start subscribing.')" />
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Business') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Plan') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Starts') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Ends') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Paid') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($subscriptions as $subscription)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $subscription->business?->name }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $subscription->plan?->name }}</td>
                                <td class="px-4 py-3">
                                    <x-badge :variant="match($subscription->status) { 'active' => 'success', 'cancelled' => 'danger', default => 'neutral' }">{{ ucfirst($subscription->status) }}</x-badge>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $subscription->starts_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $subscription->ends_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $subscription->amount_paid ? number_format($subscription->amount_paid, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{ $subscriptions->links() }}
    </div>
</x-admin-layout>
