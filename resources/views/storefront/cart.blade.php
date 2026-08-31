<x-storefront-layout :business="$business">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-ink dark:text-gray-100">{{ __('Your Cart') }}</h1>
        @if ($items->isNotEmpty())
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ trans_choice(':count item|:count items', $items->sum('quantity'), ['count' => $items->sum('quantity')]) }}</span>
        @endif
    </div>

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
        {{-- Each item stacks: image + name/price/remove on top, a quantity
             stepper and line subtotal below — never a single row trying to
             fit six competing elements, which is what overlapped on
             narrow screens before. --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($items as $item)
                <div class="p-4 flex gap-3">
                    <a href="{{ route('storefront.products.show', [$business, $item->product->slug]) }}"
                       class="h-16 w-16 sm:h-20 sm:w-20 rounded-lg bg-gray-50 dark:bg-gray-700 shrink-0 overflow-hidden">
                        @if ($item->product->primaryImageUrl())
                            <img src="{{ $item->product->primaryImageUrl() }}" class="w-full h-full object-cover" alt="">
                        @endif
                    </a>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('storefront.products.show', [$business, $item->product->slug]) }}"
                               class="font-medium text-sm sm:text-base text-ink dark:text-gray-100 hover:underline line-clamp-2">
                                {{ $item->product->name }}
                            </a>
                            <form method="POST" action="{{ route('storefront.cart.destroy', [$business, $item->product]) }}" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="{{ __('Remove from cart') }}"
                                        class="p-1.5 -m-1.5 rounded-md text-gray-400 hover:text-danger hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>

                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $business->currencySymbol() }}{{ number_format($item->product->price, 2) }} {{ __('each') }}
                        </p>

                        <div class="mt-2.5 flex items-center justify-between gap-3">
                            <div class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-700">
                                <form method="POST" action="{{ route('storefront.cart.update', [$business, $item->product]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ max(0, $item->quantity - 1) }}">
                                    <button type="submit" aria-label="{{ __('Decrease quantity') }}" @disabled($item->quantity <= 1)
                                            class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-gray-100 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <x-icon name="minus" class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                                <span class="w-8 text-center text-sm font-medium text-ink dark:text-gray-100 tabular-nums">{{ $item->quantity }}</span>
                                <form method="POST" action="{{ route('storefront.cart.update', [$business, $item->product]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                    @php
                                        $atStockLimit = ! $business->allow_overselling && $item->quantity >= $item->product->stock_quantity;
                                    @endphp
                                    <button type="submit" aria-label="{{ __('Increase quantity') }}" @disabled($atStockLimit)
                                            class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-gray-100 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <x-icon name="plus" class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                            </div>

                            <span class="font-semibold text-sm sm:text-base text-ink dark:text-gray-100 tabular-nums">
                                {{ $business->currencySymbol() }}{{ number_format($item->subtotal, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($couponsEnabled)
            <div class="mt-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
                @if ($appliedCouponCode)
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2 text-sm min-w-0">
                            <x-icon name="tag" class="w-4 h-4 text-success shrink-0" />
                            <span class="font-mono font-medium text-ink dark:text-gray-100 truncate">{{ $appliedCouponCode }}</span>
                            @if (! $couponError)
                                <span class="text-success-strong shrink-0">&minus;{{ $business->currencySymbol() }}{{ number_format($couponDiscount, 2) }}</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('storefront.cart.coupon.remove', $business) }}" class="shrink-0">
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
                               class="min-w-0 flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm uppercase" />
                        <button type="submit" class="shrink-0 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                            {{ __('Apply') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif

        <div class="mt-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 space-y-1.5">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
                <span class="text-gray-700 dark:text-gray-300 tabular-nums">{{ $business->currencySymbol() }}{{ number_format($subtotal, 2) }}</span>
            </div>
            @if ($appliedCouponCode && ! $couponError)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Discount') }}</span>
                    <span class="text-success-strong tabular-nums">&minus;{{ $business->currencySymbol() }}{{ number_format($couponDiscount, 2) }}</span>
                </div>
            @endif
            <div class="flex items-center justify-between pt-1.5 border-t border-gray-100 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total') }}</span>
                <span class="text-lg font-semibold text-ink dark:text-gray-100 tabular-nums">{{ $business->currencySymbol() }}{{ number_format(max(0, $subtotal - ($couponError ? 0 : $couponDiscount)), 2) }}</span>
            </div>
        </div>

        <a href="{{ route('storefront.checkout.create', $business) }}" class="mt-4 block text-center w-full px-4 py-3.5 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 transition">
            {{ __('Proceed to Checkout') }}
        </a>
    @endif

</x-storefront-layout>
