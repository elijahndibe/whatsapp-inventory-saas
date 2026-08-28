<x-storefront-layout :business="$business">

    <a href="{{ route('storefront.show', $business) }}" class="text-sm text-brand-700 dark:text-brand-400 hover:underline">&larr; {{ __('Back to store') }}</a>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <div class="aspect-square bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
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
                        <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-100 dark:border-gray-700">
                            <img src="{{ $image->url() }}" class="w-full h-full object-cover" alt="">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if ($product->category)
                <div class="text-xs font-medium text-brand-700 dark:text-brand-400 uppercase tracking-wide">{{ $product->category->name }}</div>
            @endif
            <h1 class="mt-1 text-2xl font-semibold text-ink dark:text-gray-100">{{ $product->name }}</h1>
            <div class="mt-2 text-2xl font-semibold text-ink dark:text-gray-100">
                {{ $business->currencySymbol() }}{{ number_format($product->price, 2) }}
            </div>

            <div class="mt-3">
                @if ($product->isOutOfStock())
                    <x-badge variant="danger">{{ __('Out of stock') }}</x-badge>
                @elseif ($product->isLowStock())
                    <x-badge variant="warning">{{ __('Only :n left', ['n' => $product->stock_quantity]) }}</x-badge>
                @else
                    <x-badge variant="success">{{ __('In stock') }}</x-badge>
                @endif
            </div>

            @if ($product->description)
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed">{{ $product->description }}</p>
            @endif

            @if (! $product->isOutOfStock() || $business->allow_overselling)
                <form method="POST" action="{{ route('storefront.cart.store', $business) }}" class="mt-6 flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="number" name="quantity" value="1" min="1" max="{{ $business->allow_overselling ? 999 : max(1, $product->stock_quantity) }}"
                           class="w-20 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm" />
                    <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-3 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 transition">
                        {{ __('Add to Cart') }}
                    </button>
                </form>

                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                       class="mt-3 flex items-center justify-center gap-2 w-full px-4 py-3 bg-whatsapp/10 border-2 border-whatsapp rounded-lg font-semibold text-sm text-whatsapp-dark dark:text-whatsapp hover:bg-whatsapp/20 transition">
                        <x-icon name="whatsapp" class="w-5 h-5" />
                        {{ __('Order via WhatsApp') }}
                    </a>
                @endif
            @endif

            <div class="mt-6 flex items-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1"><x-icon name="check-circle" class="w-4 h-4 text-success" /> {{ __('Secure checkout') }}</span>
                <span class="flex items-center gap-1"><x-icon name="whatsapp" class="w-4 h-4 text-whatsapp" /> {{ __('Order via chat') }}</span>
            </div>
        </div>
    </div>

</x-storefront-layout>
