<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ __('Platform Dashboard') }}</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($stats as $label => $value)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                    <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Order Revenue (Platform)') }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($orderRevenue, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Subscription Revenue') }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">₦{{ number_format($subscriptionRevenue, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">{{ __('Active Subscriptions by Plan') }}</p>
                @forelse ($planCounts as $plan => $count)
                    <div class="text-sm text-gray-700 dark:text-gray-300 flex justify-between"><span>{{ $plan }}</span><span>{{ $count }}</span></div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('None yet') }}</p>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">{{ __('Recent Businesses') }}</div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($recentBusinesses as $business)
                        <li class="px-4 py-3 flex justify-between text-sm">
                            <a href="{{ route('admin.businesses.show', $business) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $business->name }}</a>
                            <span class="text-gray-400">{{ $business->created_at->format('d M Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">{{ __('Recent Orders (Platform)') }}</div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($recentOrders as $order)
                        <li class="px-4 py-3 flex justify-between text-sm">
                            <span class="font-mono text-gray-700 dark:text-gray-300">{{ $order->order_number }}</span>
                            <span class="text-gray-500">{{ $order->business?->name }}</span>
                            <x-order-status-badge :status="$order->order_status" />
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
