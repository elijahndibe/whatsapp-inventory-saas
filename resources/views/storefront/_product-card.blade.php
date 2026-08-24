<a href="{{ route('storefront.products.show', [$business, $product->slug]) }}" class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
    <div class="aspect-square bg-gray-100 dark:bg-gray-700">
        @if ($product->primaryImageUrl())
            <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600 text-3xl font-semibold">
                {{ Str::substr($product->name, 0, 1) }}
            </div>
        @endif
    </div>
    <div class="p-3">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $product->name }}</div>
        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $business->currencySymbol() }}{{ number_format($product->price, 2) }}</div>
        @if ($product->isOutOfStock())
            <div class="mt-1 text-xs text-red-600 dark:text-red-400">{{ __('Out of stock') }}</div>
        @endif
    </div>
</a>
