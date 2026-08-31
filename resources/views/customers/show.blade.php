<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ $customer->name }}</h2>
            @can('update', $customer)
                <a href="{{ route('customers.edit', $customer) }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-messages />

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Total Orders') }}</p>
                    <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $orders->total() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Total Spent') }}</p>
                    <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ auth()->user()->business->currencySymbol() }}{{ number_format($totalSpent / 100, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-4 col-span-2 sm:col-span-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Contact') }}</p>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->phone }}</p>
                    @if ($customer->email)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email }}</p>
                    @endif
                </div>
            </div>

            @if ($customer->address || $customer->notes)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6 text-sm">
                    @if ($customer->address)
                        <p class="text-gray-700 dark:text-gray-300"><span class="text-gray-400">{{ __('Address') }}:</span> {{ collect([$customer->address, $customer->city, $customer->state])->filter()->implode(', ') }}</p>
                    @endif
                    @if ($customer->notes)
                        <p class="mt-2 text-gray-700 dark:text-gray-300"><span class="text-gray-400">{{ __('Notes') }}:</span> {{ $customer->notes }}</p>
                    @endif
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">
                    {{ __('Order History') }}
                </div>
                @if ($orders->isEmpty())
                    <x-empty-state icon="orders" :title="__('No orders yet')" :description="__('Orders placed by this customer will show up here.')" />
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($orders as $order)
                            <li>
                                <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between px-6 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                    <span class="font-mono text-gray-900 dark:text-gray-100">{{ $order->order_number }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M Y') }}</span>
                                    <x-order-status-badge :status="$order->order_status" />
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $order->currencySymbol() }}{{ number_format($order->total, 2) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="p-4">{{ $orders->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
