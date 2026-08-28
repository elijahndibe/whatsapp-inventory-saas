<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Customers') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <form method="GET" class="flex gap-2">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-icon name="search" class="w-4 h-4 text-gray-400" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or phone...') }}"
                           class="block w-full pl-9 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" />
                </div>
                <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ __('Search') }}
                </button>
            </form>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                @if ($customers->isEmpty())
                    <x-empty-state icon="customers" :title="__('No customers yet')" :description="__('Customers are added automatically the first time they place an order.')" />
                @else
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Phone') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Orders') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Total Spent') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Last Order') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($customers as $customer)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 cursor-pointer" onclick="window.location='{{ route('customers.show', $customer) }}'">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $customer->name }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $customer->phone }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $customer->orders_count }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ auth()->user()->business->currencySymbol() }}{{ number_format(($customer->orders_sum_total ?? 0) / 100, 2) }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $customer->orders_max_created_at ? \Illuminate\Support\Carbon::parse($customer->orders_max_created_at)->format('d M Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($customers as $customer)
                            <a href="{{ route('customers.show', $customer) }}" class="block px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->name }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ auth()->user()->business->currencySymbol() }}{{ number_format(($customer->orders_sum_total ?? 0) / 100, 2) }}</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $customer->phone }} &middot; {{ trans_choice(':count order|:count orders', $customer->orders_count, ['count' => $customer->orders_count]) }}</span>
                                    <span>{{ $customer->orders_max_created_at ? \Illuminate\Support\Carbon::parse($customer->orders_max_created_at)->format('d M Y') : '—' }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{ $customers->links() }}
        </div>
    </div>
</x-app-layout>
