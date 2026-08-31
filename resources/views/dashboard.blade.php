<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Good morning, :name! 👋', ['name' => $business->name]) }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __("Here's what's happening with your business today.") }}</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <x-flash-messages />

        <x-setup-reminder :business="$business" />

        <x-card class="!p-4 sm:!p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 min-w-0">
                <x-icon name="store" class="w-4 h-4 shrink-0" />
                <span class="shrink-0">{{ __('Storefront') }}:</span>
                <a href="{{ route('storefront.show', $business) }}" target="_blank" class="font-mono text-brand-700 dark:text-brand-400 hover:underline truncate">{{ url('/store/'.$business->slug) }}</a>
            </div>
            <a href="{{ route('storefront.show', $business) }}" target="_blank" class="shrink-0">
                <x-outline-button type="button"><x-icon name="external-link" class="w-4 h-4" /> {{ __('View store') }}</x-outline-button>
            </a>
        </x-card>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $todaysSales = $stats["Today's Sales"];
                $salesPercent = $trends['sales_change_percent'];
                $lowStockTotal = $stats['Low Stock'] + $stats['Out of Stock'];
            @endphp
            <x-stat-card :label="__('Today\'s Sales')" :value="$business->currencySymbol() . number_format($todaysSales, 2)" icon="trending-up" color="brand"
                :change="$salesPercent === null ? null : (($salesPercent >= 0 ? '↑ ' : '↓ ').abs($salesPercent).'% '.__('vs yesterday'))"
                :trend="$salesPercent === null ? null : ($salesPercent >= 0 ? 'up' : 'down')" />
            <x-stat-card :label="__('Orders')" :value="$stats['Total Orders']" icon="orders" color="success"
                :change="$trends['orders_this_week'] > 0 ? '+'.$trends['orders_this_week'].' '.__('this week') : null" trend="up" />
            <x-stat-card :label="__('Customers')" :value="$stats['Total Customers']" icon="customers" color="info"
                :change="$trends['customers_this_week'] > 0 ? '+'.$trends['customers_this_week'].' '.__('new this week') : null" trend="up" />
            <x-stat-card :label="__('Low stock items')" :value="$lowStockTotal" icon="alert-triangle" color="warning"
                :change="$lowStockTotal > 0 ? __('Needs attention') : __('All good')"
                :trend="$lowStockTotal > 0 ? 'warning' : null" />
        </div>

        <x-card>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('Sales & Fees') }}</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Total Sales') }}</p>
                    <p class="mt-1 text-lg font-semibold text-ink dark:text-gray-100">{{ $business->currencySymbol() }}{{ number_format($earnings['total_sales'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Platform Fees') }}</p>
                    <p class="mt-1 text-lg font-semibold text-warning-strong">-{{ $business->currencySymbol() }}{{ number_format($earnings['platform_fees'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Net Sales') }}</p>
                    <p class="mt-1 text-lg font-semibold text-success-strong">{{ $business->currencySymbol() }}{{ number_format($earnings['net_sales'], 2) }}</p>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card class="!p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-ink dark:text-gray-100">{{ __('Recent orders') }}</h3>
                    @can('view orders')
                        <a href="{{ route('orders.index') }}" class="text-xs font-medium text-brand-700 dark:text-brand-400 hover:underline">{{ __('View all') }}</a>
                    @endcan
                </div>
                @if ($recentOrders->isEmpty())
                    <x-empty-state icon="orders" :title="__('No orders yet')" :description="__('Orders from your storefront and WhatsApp will show up here.')" />
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recentOrders as $order)
                            <li>
                                <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-900/40 text-sm">
                                    <div class="min-w-0">
                                        <span class="font-mono text-xs text-gray-500">{{ $order->order_number }}</span>
                                        <p class="font-medium text-ink dark:text-gray-100 truncate">{{ $order->customer->name }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-gray-700 dark:text-gray-300">{{ $business->currencySymbol() }}{{ number_format($order->total, 2) }}</span>
                                        <x-payment-status-badge :status="$order->payment_status" />
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <x-card class="!p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-ink dark:text-gray-100">{{ __('Low stock products') }}</h3>
                    @can('view inventory')
                        <a href="{{ route('inventory.index') }}" class="text-xs font-medium text-brand-700 dark:text-brand-400 hover:underline">{{ __('View all') }}</a>
                    @endcan
                </div>
                @if ($lowStockProducts->isEmpty())
                    <x-empty-state icon="box" :title="__('Stock levels look healthy')" :description="__('Nothing needs your attention right now.')" />
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($lowStockProducts as $product)
                            <li class="flex items-center justify-between px-5 py-3 text-sm">
                                <span class="font-medium text-ink dark:text-gray-100 truncate">{{ $product->name }}</span>
                                <span class="shrink-0">
                                    @if ($product->isOutOfStock())
                                        <x-badge variant="danger">{{ __('Out of stock') }}</x-badge>
                                    @else
                                        <x-badge variant="warning">{{ __(':n in stock', ['n' => $product->stock_quantity]) }}</x-badge>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        <div>
            <h3 class="font-semibold text-ink dark:text-gray-100 mb-3">{{ __('Quick actions') }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @can('create products')
                    <a href="{{ route('products.create') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center text-center gap-2 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-card transition">
                        <span class="w-9 h-9 rounded-lg bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 flex items-center justify-center"><x-icon name="plus" class="w-5 h-5" /></span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Add product') }}</span>
                    </a>
                @endcan
                @can('view orders')
                    <a href="{{ route('orders.index') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center text-center gap-2 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-card transition">
                        <span class="w-9 h-9 rounded-lg bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 flex items-center justify-center"><x-icon name="orders" class="w-5 h-5" /></span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ __('View orders') }}</span>
                    </a>
                @endcan
                <button type="button" onclick="navigator.clipboard.writeText('{{ route('storefront.show', $business) }}'); this.querySelector('span:last-child').textContent = '{{ __('Copied!') }}'"
                        class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center text-center gap-2 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-card transition">
                    <span class="w-9 h-9 rounded-lg bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 flex items-center justify-center"><x-icon name="share" class="w-5 h-5" /></span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Share store') }}</span>
                </button>
                @can('manage settings')
                    <a href="{{ route('whatsapp.index') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center text-center gap-2 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-card transition">
                        <span class="w-9 h-9 rounded-lg bg-whatsapp/10 text-whatsapp flex items-center justify-center"><x-icon name="whatsapp" class="w-5 h-5" /></span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ __('WhatsApp link') }}</span>
                    </a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
