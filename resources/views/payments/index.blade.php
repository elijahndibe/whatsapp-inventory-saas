<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Payments') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Every transaction, and exactly what you take home.') }}</p>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <x-flash-messages />

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card label="{{ __('Total Sales') }}" value="{{ auth()->user()->business->currencySymbol() }}{{ number_format($totals['sales'], 2) }}" icon="trending-up" />
            <x-stat-card label="{{ __('Platform Fees') }}" value="-{{ auth()->user()->business->currencySymbol() }}{{ number_format($totals['fees'], 2) }}" icon="payments" />
            <x-stat-card label="{{ __('Net Sales') }}" value="{{ auth()->user()->business->currencySymbol() }}{{ number_format($totals['net'], 2) }}" icon="box" />
        </div>

        <x-card class="!p-0 overflow-hidden">
            <form method="GET" class="p-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-2">
                <select name="status" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach (['pending', 'success', 'failed', 'abandoned'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <x-secondary-button type="submit">{{ __('Filter') }}</x-secondary-button>
            </form>

            @if ($payments->isEmpty())
                <x-empty-state icon="payments" title="{{ __('No transactions yet') }}" description="{{ __('Payments from your storefront and WhatsApp orders will show up here.') }}" />
            @else
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                @foreach (['Order', 'Customer', 'Amount', 'Fee', 'Net', 'Gateway', 'Status', 'Date'] as $col)
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __($col) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $payment->order?->order_number }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $payment->order?->customer?->name }}</td>
                                    <td class="px-4 py-3 text-ink dark:text-gray-100 font-medium">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $payment->commission_amount !== null ? number_format($payment->commission_amount, 2) : '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $payment->seller_amount !== null ? number_format($payment->seller_amount, 2) : '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ ucfirst($payment->gateway) }}</td>
                                    <td class="px-4 py-3"><x-payment-status-badge :status="$payment->status" /></td>
                                    <td class="px-4 py-3 text-gray-400 whitespace-nowrap">{{ $payment->created_at?->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($payments as $payment)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs text-gray-500">{{ $payment->order?->order_number }}</span>
                                <x-payment-status-badge :status="$payment->status" />
                            </div>
                            <p class="mt-1 font-medium text-ink dark:text-gray-100">{{ $payment->order?->customer?->name }}</p>
                            <div class="mt-2 flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('Amount') }}</span>
                                <span class="font-medium">{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('Net') }}</span>
                                <span>{{ $payment->seller_amount !== null ? number_format($payment->seller_amount, 2) : '—' }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $payment->created_at?->format('d M Y') }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $payments->links() }}</div>
            @endif
        </x-card>
    </div>
</x-app-layout>
