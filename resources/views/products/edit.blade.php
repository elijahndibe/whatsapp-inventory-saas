<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Edit Product') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @include('products._form')
                </form>
            </div>

            @can('adjustStock', $product)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ __('Stock') }}: <span class="font-mono">{{ $product->stock_quantity }}</span>
                        </h3>
                        <a href="{{ route('products.inventory.history', $product) }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">
                            {{ __('View history') }}
                        </a>
                    </div>

                    <form method="POST" action="{{ route('products.inventory.adjust', $product) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
                        @csrf
                        <div>
                            <x-input-label for="mode" :value="__('Action')" />
                            <select id="mode" name="mode" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                <option value="increase">{{ __('Add stock') }}</option>
                                <option value="decrease">{{ __('Remove stock') }}</option>
                                <option value="set">{{ __('Set exact count') }}</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="type" :value="__('Reason')" />
                            <select id="type" name="type" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                <option value="purchase">{{ __('Purchase') }}</option>
                                <option value="sale">{{ __('Sale') }}</option>
                                <option value="return">{{ __('Return') }}</option>
                                <option value="damage">{{ __('Damage') }}</option>
                                <option value="adjustment">{{ __('Adjustment') }}</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="quantity" :value="__('Quantity')" />
                            <x-text-input id="quantity" name="quantity" type="number" min="0" class="block mt-1 w-full text-sm" required />
                        </div>
                        <x-primary-button>{{ __('Apply') }}</x-primary-button>

                        <div class="col-span-2 sm:col-span-4">
                            <x-input-label for="notes" :value="__('Notes (optional)')" />
                            <x-text-input id="notes" name="notes" type="text" class="block mt-1 w-full text-sm" />
                        </div>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
