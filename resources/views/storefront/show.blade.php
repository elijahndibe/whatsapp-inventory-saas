<x-storefront-layout :business="$business">

    {{-- Hero: no product photography to build a banner around, so the hero
         itself is the brand mark — a real gradient + a decorative ring
         motif rather than a plain bordered info box. --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 via-brand-800 to-gray-900 p-6 sm:p-10 mb-6">
        <div class="pointer-events-none absolute -right-10 -top-16 w-64 h-64 rounded-full border-[24px] border-white/10"></div>
        <div class="pointer-events-none absolute -right-24 -bottom-24 w-72 h-72 rounded-full border-[24px] border-white/5"></div>

        <div class="relative">
            @if ($business->logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($business->logo) }}" alt=""
                     class="h-14 w-14 rounded-full object-cover ring-2 ring-white/40 mb-4">
            @endif
            <h1 class="text-3xl sm:text-4xl font-semibold text-white tracking-tight">{{ $business->name }}</h1>
            @if ($business->description)
                <p class="mt-2 text-sm sm:text-base text-white/80 max-w-lg">{{ $business->description }}</p>
            @endif
            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-white/70">
                @if ($business->phone)
                    <span class="inline-flex items-center gap-1.5"><x-icon name="whatsapp" class="w-4 h-4" aria-hidden="true" /> {{ $business->phone }}</span>
                @endif
                @if ($business->address)
                    <span>{{ collect([$business->address, $business->city, $business->state])->filter()->implode(', ') }}</span>
                @endif
            </div>
        </div>
    </div>

    <form method="GET" class="mb-6">
        <div class="relative">
            <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" aria-hidden="true" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search products...') }}"
                   class="block w-full pl-11 py-3 rounded-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-card text-sm focus:border-brand-500 focus:ring-brand-500" />
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
            <h2 class="text-xl font-semibold text-ink dark:text-gray-100 mb-4">{{ __('Best sellers') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach ($featured as $product)
                    @include('storefront._product-card', ['product' => $product, 'business' => $business])
                @endforeach
            </div>
        </div>
    @endif

    <div>
        @if ($featured->isEmpty())
            <h2 class="text-xl font-semibold text-ink dark:text-gray-100 mb-4">{{ __('Products') }}</h2>
        @else
            <h2 class="text-xl font-semibold text-ink dark:text-gray-100 mb-4">{{ __('All products') }}</h2>
        @endif

        @if ($products->isEmpty())
            <x-card>
                <x-empty-state icon="products" :title="__('No products found')" :description="__('Try a different search or check back soon.')" />
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
