<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Inventory') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Out of Stock') }}</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $outOfStock->count() }}</span>
                </div>
                @if ($outOfStock->isEmpty())
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Nothing is out of stock. Nice.') }}</div>
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($outOfStock as $product)
                            <li class="px-6 py-3 flex items-center justify-between text-sm">
                                <a href="{{ route('products.edit', $product) }}" class="text-gray-900 dark:text-gray-100 hover:underline">{{ $product->name }}</a>
                                <span class="text-red-600 dark:text-red-400 font-medium">{{ $product->stock_quantity }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Low Stock') }}</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $lowStock->count() }}</span>
                </div>
                @if ($lowStock->isEmpty())
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">{{ __('No products are running low.') }}</div>
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($lowStock as $product)
                            <li class="px-6 py-3 flex items-center justify-between text-sm">
                                <a href="{{ route('products.edit', $product) }}" class="text-gray-900 dark:text-gray-100 hover:underline">{{ $product->name }}</a>
                                <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $product->stock_quantity }} / {{ $product->low_stock_threshold }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
