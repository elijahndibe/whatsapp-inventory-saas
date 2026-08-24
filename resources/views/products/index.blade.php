<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Products') }}
            </h2>
            @can('create', \App\Models\Product::class)
                <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    {{ __('New Product') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or SKU...') }}"
                       class="col-span-2 sm:col-span-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />

                <select name="category_id" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select name="stock" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">{{ __('All Stock Levels') }}</option>
                    <option value="low" @selected(request('stock') === 'low')>{{ __('Low Stock') }}</option>
                    <option value="out" @selected(request('stock') === 'out')>{{ __('Out of Stock') }}</option>
                </select>

                <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ __('Filter') }}
                </button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                @if ($products->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No products found.') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Product') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Category') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Price') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Stock') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                @if ($product->primaryImageUrl())
                                                    <img src="{{ $product->primaryImageUrl() }}" class="h-10 w-10 rounded object-cover shrink-0" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded bg-gray-100 dark:bg-gray-700 shrink-0"></div>
                                                @endif
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                                                    @if ($product->sku)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product->sku }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $product->category?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ number_format($product->price, 2) }}</td>
                                        <td class="px-4 py-3">
                                            <span @class([
                                                'font-medium',
                                                'text-red-600 dark:text-red-400' => $product->isOutOfStock(),
                                                'text-amber-600 dark:text-amber-400' => $product->isLowStock(),
                                                'text-gray-900 dark:text-gray-100' => ! $product->isOutOfStock() && ! $product->isLowStock(),
                                            ])>{{ $product->stock_quantity }}</span>
                                            @if ($product->isOutOfStock())
                                                <span class="ml-1 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">{{ __('Out') }}</span>
                                            @elseif ($product->isLowStock())
                                                <span class="ml-1 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Low') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span @class([
                                                'inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
                                                'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $product->status === 'active',
                                                'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => $product->status !== 'active',
                                            ])>{{ ucfirst($product->status) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                            <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Edit') }}</a>
                                            @can('delete', $product)
                                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline" onsubmit="return confirm('{{ __('Delete this product?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Delete') }}</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
