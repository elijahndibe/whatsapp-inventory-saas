<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Inventory') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-stat-card :label="__('Total Products')" :value="$totalProducts" icon="products" />
                <x-stat-card :label="__('Low Stock')" :value="$lowStock->count()" icon="alert-triangle" />
                <x-stat-card :label="__('Out of Stock')" :value="$outOfStock->count()" icon="x-circle" />
                <x-stat-card :label="__('Inventory Value')" :value="auth()->user()->business->currencySymbol() . number_format($inventoryValue, 2)" icon="trending-up" />
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Out of Stock') }}</h3>
                    <x-badge variant="danger">{{ $outOfStock->count() }}</x-badge>
                </div>
                @if ($outOfStock->isEmpty())
                    <x-empty-state icon="check-circle" :title="__('Nothing is out of stock')" :description="__('Nice — every product currently has stock available.')" />
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($outOfStock as $product)
                            <li class="px-6 py-3 flex items-center justify-between text-sm">
                                <a href="{{ route('products.edit', $product) }}" class="text-gray-900 dark:text-gray-100 hover:underline">{{ $product->name }}</a>
                                <span class="text-danger font-medium">{{ $product->stock_quantity }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Low Stock') }}</h3>
                    <x-badge variant="warning">{{ $lowStock->count() }}</x-badge>
                </div>
                @if ($lowStock->isEmpty())
                    <x-empty-state icon="check-circle" :title="__('No products are running low')" :description="__('You will see items here once their stock drops below the threshold you set.')" />
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($lowStock as $product)
                            <li class="px-6 py-3 flex items-center justify-between text-sm">
                                <a href="{{ route('products.edit', $product) }}" class="text-gray-900 dark:text-gray-100 hover:underline">{{ $product->name }}</a>
                                <span class="text-warning-strong font-medium">{{ $product->stock_quantity }} / {{ $product->low_stock_threshold }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
