<x-storefront-layout :business="$business">

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $business->name }}</h1>
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

    <form method="GET" class="mb-6 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search products...') }}"
               class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm" />
        <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ __('Search') }}
        </button>
    </form>

    @if ($categories->isNotEmpty())
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('storefront.show', $business) }}"
               @class(['px-3 py-1.5 rounded-full text-xs font-medium', 'bg-indigo-600 text-white' => ! request('category_id'), 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700' => request('category_id')])>
                {{ __('All') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('storefront.show', [$business, 'category_id' => $category->id]) }}"
                   @class(['px-3 py-1.5 rounded-full text-xs font-medium', 'bg-indigo-600 text-white' => (int) request('category_id') === $category->id, 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700' => (int) request('category_id') !== $category->id])>
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('No products found.') }}
            </div>
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
