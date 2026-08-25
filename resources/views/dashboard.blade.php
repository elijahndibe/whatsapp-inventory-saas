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
                        <a href="{{ route('storefront.show', $business) }}" target="_blank" class="font-mono text-indigo-600 dark:text-indigo-400 hover:underline">{{ url('/store/'.$business->slug) }}</a>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ($stats as $label => $value)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-4">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __($label) }}</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            @if ($label === "Today's Sales")
                                {{ $business->currencySymbol() }}{{ number_format($value, 2) }}
                            @else
                                {{ $value }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('Sales & Fees') }}</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Total Sales') }}</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $business->currencySymbol() }}{{ number_format($earnings['total_sales'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Platform Fees') }}</p>
                        <p class="mt-1 text-lg font-semibold text-amber-600 dark:text-amber-400">-{{ $business->currencySymbol() }}{{ number_format($earnings['platform_fees'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Net Sales') }}</p>
                        <p class="mt-1 text-lg font-semibold text-green-600 dark:text-green-400">{{ $business->currencySymbol() }}{{ number_format($earnings['net_sales'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @can('view orders')
                    <a href="{{ route('orders.index') }}" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-900/40">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('View all orders') }}</span>
                        <span class="text-indigo-600 dark:text-indigo-400">&rarr;</span>
                    </a>
                @endcan
                @can('view inventory')
                    <a href="{{ route('inventory.index') }}" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-900/40">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Check inventory') }}</span>
                        <span class="text-indigo-600 dark:text-indigo-400">&rarr;</span>
                    </a>
                @endcan
            </div>

        </div>
    </div>
</x-app-layout>
