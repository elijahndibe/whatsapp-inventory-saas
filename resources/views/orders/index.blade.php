<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Orders') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
            @endif

            <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Order #, customer name or phone...') }}"
                       class="col-span-2 sm:col-span-2 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />

                <select name="order_status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach (\App\Models\Order::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('order_status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>

                <select name="payment_status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">{{ __('All Payment Statuses') }}</option>
                    @foreach (\App\Models\Order::PAYMENT_STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>

                <button class="col-span-2 sm:col-span-1 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ __('Filter') }}
                </button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                @if ($orders->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No orders found.') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Order') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Source') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Customer') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Total') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Payment') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($orders as $order)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 cursor-pointer" onclick="window.location='{{ route('orders.show', $order) }}'">
                                        <td class="px-4 py-3 font-mono text-gray-900 dark:text-gray-100">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3">
                                            @if ($order->isFromWhatsApp())
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-600 dark:text-green-400">&#9679; {{ __('WhatsApp') }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">{{ __('Storefront') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $order->customer->name }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $order->currencySymbol() }}{{ number_format($order->total, 2) }}</td>
                                        <td class="px-4 py-3"><x-order-status-badge :status="$order->order_status" /></td>
                                        <td class="px-4 py-3"><x-payment-status-badge :status="$order->payment_status" /></td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $order->created_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
