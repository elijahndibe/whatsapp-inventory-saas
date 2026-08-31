<a href="{{ route('storefront.products.show', [$business, $product->slug]) }}"
   class="group block bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-card hover:shadow-card-hover hover:-translate-y-0.5 hover:border-brand-200 dark:hover:border-brand-800 transition duration-200">
    <div class="aspect-square bg-gradient-to-br from-brand-50 to-gray-50 dark:from-gray-700 dark:to-gray-800 overflow-hidden">
        @if ($product->primaryImageUrl())
            <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-brand-200 dark:text-gray-600 text-4xl font-semibold">
                {{ Str::substr($product->name, 0, 1) }}
            </div>
        @endif
    </div>
    <div class="p-3.5">
        <div class="text-sm font-medium text-ink dark:text-gray-100 truncate">{{ $product->name }}</div>
        <div class="mt-1 text-base font-semibold text-brand-700 dark:text-brand-400">{{ $business->currencySymbol() }}{{ number_format($product->price, 2) }}</div>
        @if ($product->isOutOfStock())
            <div class="mt-1.5"><x-badge variant="danger">{{ __('Out of stock') }}</x-badge></div>
        @endif
    </div>
</a>
