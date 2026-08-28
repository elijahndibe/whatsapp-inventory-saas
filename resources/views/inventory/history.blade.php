<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">
            {{ __('Stock History') }} — {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <a href="{{ route('products.edit', $product) }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">&larr; {{ __('Back to product') }}</a>

            <x-flash-messages />

            @if ($businessLocations->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Stock by Location') }}</h3>

                    <table class="min-w-full text-sm mb-4">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($businessLocations as $location)
                                @php $qty = $locationStock->firstWhere('location_id', $location->id)?->quantity ?? 0; @endphp
                                <tr>
                                    <td class="py-2 text-gray-700 dark:text-gray-300">{{ $location->name }}</td>
                                    <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ $qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <form method="POST" action="{{ route('products.inventory.location-stock', $product) }}" class="space-y-2">
                            @csrf
                            <x-input-label :value="__('Set Location Allocation')" />
                            <select name="location_id" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                @foreach ($businessLocations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <input type="number" name="quantity" min="0" placeholder="{{ __('Quantity') }}" required
                                       class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
                                <button class="px-3 py-1.5 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-xs font-semibold uppercase">{{ __('Set') }}</button>
                            </div>
                        </form>

                        @if ($businessLocations->count() > 1)
                            <form method="POST" action="{{ route('products.inventory.transfer', $product) }}" class="space-y-2">
                                @csrf
                                <x-input-label :value="__('Transfer Between Locations')" />
                                <div class="flex gap-2">
                                    <select name="from_location_id" class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                        @foreach ($businessLocations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="to_location_id" class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                        @foreach ($businessLocations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <input type="number" name="quantity" min="1" placeholder="{{ __('Quantity') }}" required
                                           class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
                                    <button class="px-3 py-1.5 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-xs font-semibold uppercase">{{ __('Transfer') }}</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                @if ($transactions->isEmpty())
                    <x-empty-state icon="inventory" :title="__('No stock movements recorded yet')" :description="__('Stock increases, decreases and transfers for this product will show up here.')" />
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
