<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Reports & Analytics') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Sales — Last 30 Days') }}</h3>
                    <canvas id="salesChart" height="220"></canvas>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Orders — Last 30 Days') }}</h3>
                    <canvas id="ordersChart" height="220"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">{{ __('Best-Selling Products') }}</div>
                @if ($bestSellers->isEmpty())
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">{{ __('No sales recorded yet.') }}</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Product') }}</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Units Sold') }}</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($bestSellers as $item)
                                <tr>
                                    <td class="px-6 py-3 text-gray-900 dark:text-gray-100">{{ $item['name'] }}</td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $item['units'] }}</td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ number_format($item['revenue'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if ($canUseAdvanced)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Sales by Category') }}</h3>
                        @if ($salesByCategory->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No data yet.') }}</p>
                        @else
                            <canvas id="categoryChart" height="220"></canvas>
                        @endif
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Payment Methods') }}</h3>
                        @if ($paymentMethods->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No data yet.') }}</p>
                        @else
                            <canvas id="paymentChart" height="220"></canvas>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Sales by category and payment method breakdowns are available on the Business plan.') }}
                    </p>
                    <a href="{{ route('billing.index') }}" class="mt-2 inline-block text-sm text-brand-700 dark:text-brand-400 hover:underline">{{ __('Upgrade') }} &rarr;</a>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timeline = @json($timeline);
            const labels = timeline.map(d => d.date.slice(5));

            new Chart(document.getElementById('salesChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{ label: 'Sales', data: timeline.map(d => d.sales), borderColor: '#6D28D9', backgroundColor: 'rgba(109,40,217,0.1)', fill: true, tension: 0.3 }],
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
            });

            new Chart(document.getElementById('ordersChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: 'Orders', data: timeline.map(d => d.orders), backgroundColor: '#8B5CF6' }],
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });

            @if ($canUseAdvanced && $salesByCategory->isNotEmpty())
                const categoryData = @json($salesByCategory);
                new Chart(document.getElementById('categoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: categoryData.map(c => c.category),
                        datasets: [{ data: categoryData.map(c => c.revenue), backgroundColor: ['#6D28D9','#8B5CF6','#A78BFA','#C4B5FD','#DDD6FE','#EDE9FE'] }],
                    },
                });
            @endif

            @if ($canUseAdvanced && $paymentMethods->isNotEmpty())
                const paymentData = @json($paymentMethods);
                new Chart(document.getElementById('paymentChart'), {
                    type: 'doughnut',
                    data: {
                        labels: paymentData.map(p => p.method),
                        datasets: [{ data: paymentData.map(p => p.count), backgroundColor: ['#059669','#10b981','#34d399','#6ee7b7'] }],
                    },
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
