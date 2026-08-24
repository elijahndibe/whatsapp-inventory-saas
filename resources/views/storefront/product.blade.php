<x-storefront-layout :business="$business">

    <a href="{{ route('storefront.show', $business) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">&larr; {{ __('Back to store') }}</a>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <div class="aspect-square bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                @if ($product->images->isNotEmpty())
                    <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600 text-5xl font-semibold">
                        {{ Str::substr($product->name, 0, 1) }}
                    </div>
                @endif
            </div>

            @if ($product->images->count() > 1)
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach ($product->images as $image)
                        <div class="aspect-square rounded overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <img src="{{ $image->url() }}" class="w-full h-full object-cover" alt="">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if ($product->category)
                <div class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">{{ $product->category->name }}</div>
            @endif
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $product->name }}</h1>
            <div class="mt-2 text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $business->currencySymbol() }}{{ number_format($product->price, 2) }}
            </div>

            <div class="mt-2">
                @if ($product->isOutOfStock())
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">{{ __('Out of stock') }}</span>
                @elseif ($product->isLowStock())
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Only :n left', ['n' => $product->stock_quantity]) }}</span>
                @else
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">{{ __('In stock') }}</span>
                @endif
            </div>

            @if ($product->description)
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $product->description }}</p>
            @endif

            @if (! $product->isOutOfStock() || $business->allow_overselling)
                <form method="POST" action="{{ route('storefront.cart.store', $business) }}" class="mt-6 flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="number" name="quantity" value="1" min="1" max="{{ $business->allow_overselling ? 999 : max(1, $product->stock_quantity) }}"
                           class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm" />
                    <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700">
                        {{ __('Add to Cart') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

</x-storefront-layout>
