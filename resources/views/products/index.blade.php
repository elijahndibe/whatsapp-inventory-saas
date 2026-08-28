<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Products') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Manage your catalogue and inventory.') }}</p>
            </div>
            @can('create', \App\Models\Product::class)
                <a href="{{ route('products.create') }}" class="shrink-0">
                    <x-primary-button type="button"><x-icon name="plus" class="w-4 h-4" /> {{ __('Add product') }}</x-primary-button>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">
        <x-flash-messages />

        <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div class="col-span-2 sm:col-span-1 relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search products...') }}"
                       class="w-full pl-9 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
            </div>

            <select name="category_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                <option value="">{{ __('All Categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="stock" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                <option value="">{{ __('All Stock Levels') }}</option>
                <option value="low" @selected(request('stock') === 'low')>{{ __('Low Stock') }}</option>
                <option value="out" @selected(request('stock') === 'out')>{{ __('Out of Stock') }}</option>
            </select>

            <x-secondary-button type="submit"><x-icon name="filter" class="w-4 h-4" /> {{ __('Filter') }}</x-secondary-button>
        </form>

        <x-card class="!p-0 overflow-hidden">
            @if ($products->isEmpty())
                <x-empty-state icon="products" title="{{ __('No products yet') }}" description="{{ __('Add your first product and start building your online catalogue.') }}">
                    @can('create', \App\Models\Product::class)
                        <x-slot name="action">
                            <a href="{{ route('products.create') }}">
                                <x-primary-button type="button"><x-icon name="plus" class="w-4 h-4" /> {{ __('Add your first product') }}</x-primary-button>
                            </a>
                        </x-slot>
                    @endcan
                </x-empty-state>
            @else
                {{-- Desktop table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Product') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('SKU') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Price') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Stock') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($products as $product)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-900/30">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            @if ($product->primaryImageUrl())
                                                <img src="{{ $product->primaryImageUrl() }}" class="h-10 w-10 rounded-lg object-cover shrink-0" alt="">
                                            @else
                                                <div class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 shrink-0"></div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="font-medium text-ink dark:text-gray-100 truncate">{{ $product->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $product->category?->name ?? __('Uncategorized') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $product->sku ?? '—' }}</td>
                                    <td class="px-4 py-3 text-ink dark:text-gray-100 font-medium">{{ number_format($product->price, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'font-medium',
                                            'text-danger' => $product->isOutOfStock(),
                                            'text-warning' => $product->isLowStock(),
                                            'text-ink dark:text-gray-100' => ! $product->isOutOfStock() && ! $product->isLowStock(),
                                        ])>{{ $product->stock_quantity }}</span>
                                        @if ($product->isOutOfStock())
                                            <x-badge variant="danger" class="ml-1">{{ __('Out') }}</x-badge>
                                        @elseif ($product->isLowStock())
                                            <x-badge variant="warning" class="ml-1">{{ __('Low') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-badge :variant="$product->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($product->status) }}</x-badge>
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                        <a href="{{ route('products.edit', $product) }}" class="text-brand-700 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
                                        @can('delete', $product)
                                            <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline" onsubmit="return confirm('{{ __('Delete this product?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($products as $product)
                        <div class="p-4 flex items-center gap-3">
                            @if ($product->primaryImageUrl())
                                <img src="{{ $product->primaryImageUrl() }}" class="h-12 w-12 rounded-lg object-cover shrink-0" alt="">
                            @else
                                <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 shrink-0"></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('products.edit', $product) }}" class="font-medium text-ink dark:text-gray-100 truncate block">{{ $product->name }}</a>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($product->price, 2) }}</span>
                                    @if ($product->isOutOfStock())
                                        <x-badge variant="danger">{{ __('Out of stock') }}</x-badge>
                                    @elseif ($product->isLowStock())
                                        <x-badge variant="warning">{{ __(':n left', ['n' => $product->stock_quantity]) }}</x-badge>
                                    @else
                                        <x-badge variant="neutral">{{ __(':n in stock', ['n' => $product->stock_quantity]) }}</x-badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $products->links() }}</div>
            @endif
        </x-card>
    </div>
</x-app-layout>
