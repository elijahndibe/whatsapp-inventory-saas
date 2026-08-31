<x-storefront-layout :business="$business">

    <h1 class="text-xl font-semibold text-ink dark:text-gray-100 mb-4">{{ __('Your Cart') }}</h1>

    @if ($items->isEmpty())
        <x-card>
            <x-empty-state icon="box" :title="__('Your cart is empty')" :description="__('Add a few things you like — they\'ll show up here.')">
                <x-slot name="action">
                    <a href="{{ route('storefront.show', $business) }}">
                        <x-primary-button type="button">{{ __('Browse Products') }}</x-primary-button>
                    </a>
                </x-slot>
            </x-empty-state>
        </x-card>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($items as $item)
                <div class="p-4 flex items-center gap-4">
                    <div class="h-16 w-16 rounded-lg bg-gray-50 dark:bg-gray-700 shrink-0 overflow-hidden">
                        @if ($item->product->primaryImageUrl())
                            <img src="{{ $item->product->primaryImageUrl() }}" class="w-full h-full object-cover" alt="">
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <a href="{{ route('storefront.products.show', [$business, $item->product->slug]) }}" class="font-medium text-ink dark:text-gray-100 hover:underline">
                            {{ $item->product->name }}
                        </a>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $business->currencySymbol() }}{{ number_format($item->product->price, 2) }} {{ __('each') }}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('storefront.cart.update', [$business, $item->product]) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="999"
                               class="w-16 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
                        <button type="submit" class="text-xs text-brand-700 dark:text-brand-400 hover:underline">{{ __('Update') }}</button>
                    </form>

                    <div class="w-24 text-right font-medium text-ink dark:text-gray-100">
                        {{ $business->currencySymbol() }}{{ number_format($item->subtotal, 2) }}
                    </div>

                    <form method="POST" action="{{ route('storefront.cart.destroy', [$business, $item->product]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-danger hover:underline text-sm">{{ __('Remove') }}</button>
                    </form>
                </div>
            @endforeach
        </div>

        @if ($couponsEnabled)
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
                @if ($appliedCouponCode)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm">
                            <x-icon name="tag" class="w-4 h-4 text-success" />
                            <span class="font-mono font-medium text-ink dark:text-gray-100">{{ $appliedCouponCode }}</span>
                            @if (! $couponError)
                                <span class="text-success-strong">&minus;{{ $business->currencySymbol() }}{{ number_format($couponDiscount, 2) }}</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('storefront.cart.coupon.remove', $business) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-gray-500 dark:text-gray-400 hover:underline">{{ __('Remove') }}</button>
                        </form>
                    </div>
                    @if ($couponError)
                        <p class="mt-2 text-xs text-danger">{{ $couponError }}</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('storefront.cart.coupon.apply', $business) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="{{ __('Coupon code') }}" maxlength="50"
                               class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm uppercase" />
                        <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                            {{ __('Apply') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif

        <div class="mt-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 space-y-1">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
                <span class="text-gray-700 dark:text-gray-300">{{ $business->currencySymbol() }}{{ number_format($subtotal, 2) }}</span>
            </div>
            @if ($appliedCouponCode && ! $couponError)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Discount') }}</span>
                    <span class="text-success-strong">&minus;{{ $business->currencySymbol() }}{{ number_format($couponDiscount, 2) }}</span>
                </div>
            @endif
            <div class="flex items-center justify-between pt-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total') }}</span>
                <span class="text-lg font-semibold text-ink dark:text-gray-100">{{ $business->currencySymbol() }}{{ number_format(max(0, $subtotal - ($couponError ? 0 : $couponDiscount)), 2) }}</span>
            </div>
        </div>

        <a href="{{ route('storefront.checkout.create', $business) }}" class="mt-4 block text-center w-full px-4 py-3 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 transition">
            {{ __('Proceed to Checkout') }}
        </a>
    @endif

</x-storefront-layout>
