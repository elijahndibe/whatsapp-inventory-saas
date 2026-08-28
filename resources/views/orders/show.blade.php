<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">
                {{ __('Order') }} <span class="font-mono">{{ $order->order_number }}</span>
            </h2>
            <div class="flex items-center gap-3 text-sm">
                @if ($canUseInvoices)
                    <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Invoice') }}</a>
                    @if ($order->payment_status === 'paid')
                        <a href="{{ route('orders.receipt', $order) }}" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Receipt') }}</a>
                    @endif
                @else
                    <a href="{{ route('billing.index') }}" class="text-xs text-amber-600 dark:text-amber-400 hover:underline">{{ __('Upgrade for invoices') }}</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
            @endif

            @if ($order->isFromWhatsApp())
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-4 border-l-4 border-green-500">
                    <span class="text-xs font-semibold uppercase tracking-wide text-green-600 dark:text-green-400">{{ __('WhatsApp Order') }}</span>

                    @if ($order->payment_status === 'paid')
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Paid — this order is settled.') }}</p>
                    @elseif ($order->order_status === 'cancelled')
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('This order was cancelled.') }}</p>
                    @else
                        @can('update', $order)
                            @if ($pendingPayment)
                                <p class="mt-1 mb-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ __('Payment link ready — send it to the customer to collect payment.') }}</p>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($paymentLinkWhatsAppUrl)
                                        <a href="{{ $paymentLinkWhatsAppUrl }}" target="_blank" rel="noopener"
                                           class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700">
                                            {{ __('Send payment link on WhatsApp') }}
                                        </a>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400">{{ __('Add a phone number for this customer to send the link via WhatsApp.') }}</span>
                                    @endif
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $pendingPayment->authorization_url }}'); this.textContent = '{{ __('Copied!') }}'"
                                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600">
                                        {{ __('Copy Payment Link') }}
                                    </button>
                                </div>
                            @elseif (! $order->business->hasPaystackSubaccount())
                                <p class="mt-1 mb-2 text-sm text-amber-600 dark:text-amber-400">{{ __('Connect Paystack to accept secure online payments and automatically process your commission.') }}</p>
                                <a href="{{ route('settings.edit') }}" class="inline-block px-4 py-2 bg-brand-700 text-white rounded-md text-sm font-semibold hover:bg-brand-800">
                                    {{ __('Connect Paystack') }}
                                </a>
                            @else
                                <p class="mt-1 mb-3 text-sm text-gray-600 dark:text-gray-400">{{ __('Once you and the customer have confirmed the order details on WhatsApp, request payment here.') }}</p>
                                <form method="POST" action="{{ route('orders.request-payment', $order) }}">
                                    @csrf
                                    <button class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700">
                                        {{ __('Confirm order & request payment') }}
                                    </button>
                                </form>
                            @endif
                        @else
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Awaiting confirmation.') }}</p>
                        @endcan
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ __('Order Status') }}</div>
                    <x-order-status-badge :status="$order->order_status" />
                    @can('update', $order)
                        <form method="POST" action="{{ route('orders.status.update', $order) }}" class="mt-3 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="order_status" class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                @foreach (\App\Models\Order::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($order->order_status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <button class="px-3 py-1.5 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-xs font-semibold uppercase">{{ __('Update') }}</button>
                        </form>
                        @if ($order->inventory_deducted_at)
                            <p class="mt-2 text-xs text-gray-400">{{ __('Stock deducted :date', ['date' => $order->inventory_deducted_at->format('d M Y H:i')]) }}</p>
                        @endif
                    @endcan
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-4">
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

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Customer') }}</h3>
                <a href="{{ route('customers.show', $order->customer) }}" class="text-brand-600 dark:text-brand-400 hover:underline font-medium">{{ $order->customer->name }}</a>
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

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
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
