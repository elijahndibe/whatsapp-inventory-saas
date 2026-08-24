<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">{{ $business->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        {{ __('Storefront') }}:
                        <span class="font-mono">{{ url('/store/'.$business->slug) }}</span>
                        <span class="italic text-gray-400">({{ __('coming in a later phase') }})</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ([
                    "Today's Sales" => 0,
                    'Pending Orders' => 0,
                    'Low Stock' => 0,
                    'Total Customers' => 0,
                ] as $label => $value)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-4">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __($label) }}</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Products, orders, customers and inventory tools will appear here as they are built out in the next phases.') }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
