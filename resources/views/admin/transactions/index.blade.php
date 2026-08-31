<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Transactions') }}</h2>
            <a href="{{ route('admin.transactions.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 text-white rounded-md text-sm font-semibold hover:bg-brand-800">
                <x-icon name="external-link" class="w-4 h-4" />
                {{ __('Export CSV') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-4 grid grid-cols-2 sm:grid-cols-6 gap-3 text-sm">
            <select name="business_id" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                <option value="">{{ __('All Sellers') }}</option>
                @foreach ($businesses as $business)
                    <option value="{{ $business->id }}" @selected(request('business_id') == $business->id)>{{ $business->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach (['pending', 'success', 'failed', 'abandoned'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="gateway" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                <option value="">{{ __('All Gateways') }}</option>
                <option value="paystack" @selected(request('gateway') === 'paystack')>Paystack</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
            <button class="px-3 py-1.5 bg-brand-700 text-white rounded-md text-sm font-semibold hover:bg-brand-800">{{ __('Filter') }}</button>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            @foreach (['Transaction ID', 'Seller', 'Order', 'Source', 'Customer', 'Gross', 'Rate', 'Commission', 'Fee', 'Seller Amount', 'Gateway', 'Status', 'Date'] as $col)
                                <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __($col) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="px-3 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $payment->reference }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->business?->name }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->order?->order_number }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->order?->source ? ucfirst($payment->order->source) : '—' }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->order?->customer?->name }}</td>
                                <td class="px-3 py-3 text-gray-900 dark:text-gray-100">{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->commission_rate !== null ? $payment->commission_rate.'%' : '—' }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->commission_amount !== null ? number_format($payment->commission_amount, 2) : '—' }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->payment_fee !== null ? number_format($payment->payment_fee, 2) : '—' }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->seller_amount !== null ? number_format($payment->seller_amount, 2) : '—' }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $payment->gateway }}</td>
                                <td class="px-3 py-3"><x-payment-status-badge :status="$payment->effectiveStatus()" /></td>
                                <td class="px-3 py-3 text-gray-500 dark:text-gray-400">{{ $payment->created_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="13" class="px-3 py-6 text-center text-gray-400">{{ __('No transactions yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $payments->links() }}</div>
        </div>
    </div>
</x-admin-layout>
