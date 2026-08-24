<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Stock History') }} — {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <a href="{{ route('products.edit', $product) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">&larr; {{ __('Back to product') }}</a>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                @if ($transactions->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No stock movements recorded yet.') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Change') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('New Qty') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('By') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ ucfirst($transaction->type) }}</td>
                                        <td @class([
                                            'px-4 py-3 font-medium',
                                            'text-green-600 dark:text-green-400' => $transaction->quantity > 0,
                                            'text-red-600 dark:text-red-400' => $transaction->quantity < 0,
                                        ])>{{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $transaction->new_quantity }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $transaction->creator?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $transaction->notes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{ $transactions->links() }}
        </div>
    </div>
</x-app-layout>
