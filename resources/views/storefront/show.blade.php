<x-storefront-layout :business="$business">

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6 mb-6">
        <h1 class="text-2xl font-semibold text-ink dark:text-gray-100">{{ $business->name }}</h1>
        @if ($business->description)
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $business->description }}</p>
        @endif
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
            @if ($business->phone)
                <span>{{ __('Phone') }}: {{ $business->phone }}</span>
            @endif
            @if ($business->address)
                <span>{{ __('Location') }}: {{ collect([$business->address, $business->city, $business->state])->filter()->implode(', ') }}</span>
            @endif
        </div>
    </div>

    <form method="GET" class="mb-6">
        <div class="relative">
            <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search products...') }}"
                   class="block w-full pl-9 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm" />
        </div>
    </form>

    @if ($categories->isNotEmpty())
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('storefront.show', $business) }}"
               @class(['px-3 py-1.5 rounded-full text-xs font-medium transition', 'bg-brand-700 text-white' => ! request('category_id'), 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:border-brand-300' => request('category_id')])>
                {{ __('All') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('storefront.show', [$business, 'category_id' => $category->id]) }}"
                   @class(['px-3 py-1.5 rounded-full text-xs font-medium transition', 'bg-brand-700 text-white' => (int) request('category_id') === $category->id, 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:border-brand-300' => (int) request('category_id') !== $category->id])>
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if ($featured->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">{{ __('Featured') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach ($featured as $product)
                    @include('storefront._product-card', ['product' => $product, 'business' => $business])
                @endforeach
            </div>
        </div>
    @endif

    <div>
        @if ($featured->isEmpty())
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">{{ __('Products') }}</h2>
        @endif

        @if ($products->isEmpty())
            <x-card>
                <x-empty-state icon="products" title="{{ __('No products found') }}" description="{{ __('Try a different search or check back soon.') }}" />
            </x-card>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach ($products as $product)
                    @include('storefront._product-card', ['product' => $product, 'business' => $business])
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</x-storefront-layout>
