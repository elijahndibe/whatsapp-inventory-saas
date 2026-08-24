<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Customers') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or phone...') }}"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
                <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ __('Search') }}
                </button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                @if ($customers->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No customers yet.') }}</div>
                @else
                    <div class="overflow-x-auto">
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
                @endif
            </div>

            {{ $customers->links() }}
        </div>
    </div>
</x-app-layout>
