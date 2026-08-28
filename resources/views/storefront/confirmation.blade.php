<x-storefront-layout :business="$business">

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6 text-center">
        @if ($order->payment_status === 'paid')
            <div class="mx-auto h-12 w-12 rounded-full bg-success-bg dark:bg-green-900/40 flex items-center justify-center text-success dark:text-green-400">
                <x-icon name="check-circle" class="w-6 h-6" />
            </div>
            <h1 class="mt-4 text-xl font-semibold text-ink dark:text-gray-100">{{ __('Payment Received') }}</h1>
        @else
            <div class="mx-auto h-12 w-12 rounded-full bg-warning-bg dark:bg-amber-900/40 flex items-center justify-center text-warning-strong dark:text-amber-400">
                <x-icon name="info" class="w-6 h-6" />
            </div>
            <h1 class="mt-4 text-xl font-semibold text-ink dark:text-gray-100">{{ __('Order Received') }}</h1>
        @endif
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Order') }} <span class="font-mono">#{{ $order->order_number }}</span>
        </p>

        @if (session('error'))
            <p class="mt-4 text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
        @endif

        @if ($order->payment_method === 'paystack')
            @if ($order->payment_status === 'paid')
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Thank you — your payment was successful and :business has been notified.', ['business' => $business->name]) }}
                </p>
            @else
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __("We haven't received your payment yet. If you already paid, this page will update shortly — otherwise you can try again.") }}
                </p>
                <form method="POST" action="{{ route('storefront.payments.retry', [$business, $order->public_token]) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800">
                        {{ __('Try Payment Again') }}
                    </button>
                </form>
            @endif
        @elseif ($whatsappUrl)
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                {{ __('One last step — send your order to :business on WhatsApp to confirm it.', ['business' => $business->name]) }}
            </p>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
               class="mt-4 inline-flex items-center justify-center gap-2 px-6 py-3 bg-whatsapp border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-whatsapp-dark transition">
                <x-icon name="whatsapp" class="w-5 h-5" />
                {{ __('Send Order via WhatsApp') }}
            </a>
        @else
            <p class="mt-4 text-sm text-amber-600 dark:text-amber-400">
                {{ __('This business has not set up a WhatsApp number yet. Please contact them directly to confirm your order.') }}
            </p>
        @endif
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
        <h2 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Order Summary') }}</h2>
        <div class="space-y-2 text-sm">
            @foreach ($order->items as $item)
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>{{ $item->product_name }} &times;{{ $item->quantity }}</span>
                    <span>{{ $order->currencySymbol() }}{{ number_format($item->subtotal, 2) }}</span>
                </div>
            @endforeach
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3 space-y-1 text-sm">
            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                <span>{{ __('Subtotal') }}</span>
                <span>{{ $order->currencySymbol() }}{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if ($order->delivery_fee > 0)
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>{{ __('Delivery') }}</span>
                    <span>{{ $order->currencySymbol() }}{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between font-semibold text-gray-900 dark:text-gray-100 text-base pt-1">
                <span>{{ __('Total') }}</span>
                <span>{{ $order->currencySymbol() }}{{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <a href="{{ route('storefront.show', $business) }}" class="mt-6 block text-center text-sm text-brand-600 dark:text-brand-400 hover:underline">
        &larr; {{ __('Continue shopping') }}
    </a>

</x-storefront-layout>
