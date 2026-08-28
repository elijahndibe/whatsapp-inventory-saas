<a href="{{ route('storefront.products.show', [$business, $product->slug]) }}" class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-card hover:border-brand-200 dark:hover:border-brand-800 transition">
    <div class="aspect-square bg-gray-50 dark:bg-gray-700">
        @if ($product->primaryImageUrl())
            <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600 text-3xl font-semibold">
                {{ Str::substr($product->name, 0, 1) }}
            </div>
        @endif
    </div>
    <div class="p-3">
        <div class="text-sm font-medium text-ink dark:text-gray-100 truncate">{{ $product->name }}</div>
        <div class="mt-1 text-sm font-semibold text-ink dark:text-gray-100">{{ $business->currencySymbol() }}{{ number_format($product->price, 2) }}</div>
        @if ($product->isOutOfStock())
            <div class="mt-1.5"><x-badge variant="danger">{{ __('Out of stock') }}</x-badge></div>
        @endif
    </div>
</a>
