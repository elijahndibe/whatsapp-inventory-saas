<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Order') }} <span class="font-mono">{{ $order->order_number }}</span>
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ __('Order Status') }}</div>
                    <x-order-status-badge :status="$order->order_status" />
                    @can('update', $order)
                        <form method="POST" action="{{ route('orders.status.update', $order) }}" class="mt-3 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="order_status" class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                @foreach (\App\Models\Order::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($order->order_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <button class="px-3 py-1.5 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-xs font-semibold uppercase">{{ __('Update') }}</button>
                        </form>
                        @if ($order->inventory_deducted_at)
                            <p class="mt-2 text-xs text-gray-400">{{ __('Stock deducted :date', ['date' => $order->inventory_deducted_at->format('d M Y H:i')]) }}</p>
                        @endif
                    @endcan
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ __('Payment Status') }}</div>
                    <x-payment-status-badge :status="$order->payment_status" />
                    @can('update', $order)
                        <form method="POST" action="{{ route('orders.payment-status.update', $order) }}" class="mt-3 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="payment_status" class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                @foreach (\App\Models\Order::PAYMENT_STATUSES as $status)
                                    <option value="{{ $status }}" @selected($order->payment_status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <button class="px-3 py-1.5 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-xs font-semibold uppercase">{{ __('Update') }}</button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Customer') }}</h3>
                <a href="{{ route('customers.show', $order->customer) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ $order->customer->name }}</a>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 space-y-0.5">
                    <div>{{ __('Phone') }}: {{ $order->customer->phone }}</div>
                    @if ($order->customer->email)
                        <div>{{ __('Email') }}: {{ $order->customer->email }}</div>
                    @endif
                    @if ($order->shipping_address)
                        <div>{{ __('Delivery address') }}: {{ $order->shipping_address }}</div>
                    @endif
                    @if ($order->customer_notes)
                        <div>{{ __('Notes') }}: {{ $order->customer_notes }}</div>
                    @endif
                    @if ($order->payment_method)
                        <div>{{ __('Payment method') }}: {{ ucfirst($order->payment_method) }}</div>
                    @endif
                    @if ($order->payment_reference)
                        <div>{{ __('Payment reference') }}: <span class="font-mono">{{ $order->payment_reference }}</span></div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Product') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Qty') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Price') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $item->product_name }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $order->currencySymbol() }}{{ number_format($item->price, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $order->currencySymbol() }}{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 p-4 space-y-1 text-sm max-w-xs ml-auto">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>{{ __('Subtotal') }}</span><span>{{ $order->currencySymbol() }}{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if ($order->delivery_fee > 0)
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>{{ __('Delivery') }}</span><span>{{ $order->currencySymbol() }}{{ number_format($order->delivery_fee, 2) }}</span>
                        </div>
                    @endif
                    @if ($order->discount > 0)
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>{{ __('Discount') }}</span><span>-{{ $order->currencySymbol() }}{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-gray-100 text-base pt-1">
                        <span>{{ __('Total') }}</span><span>{{ $order->currencySymbol() }}{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
