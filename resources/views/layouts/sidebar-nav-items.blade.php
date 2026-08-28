{{-- Shared nav link list — included by both the desktop sidebar
     (layouts.sidebar-nav) and the mobile slide-over menu
     (layouts.mobile-nav) so there's exactly one place these links live. --}}
<div class="space-y-5">
    <div>
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
            {{ __('Overview') }}
        </x-sidebar-link>
    </div>

    @canany(['view orders', 'view customers'])
        <div>
            <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Sales') }}</p>
            <div class="space-y-1">
                @can('view orders')
                    <x-sidebar-link :href="route('orders.index')" :active="request()->routeIs('orders.*')" icon="orders">
                        {{ __('Orders') }}
                    </x-sidebar-link>
                @endcan
                @can('view customers')
                    <x-sidebar-link :href="route('customers.index')" :active="request()->routeIs('customers.*')" icon="customers">
                        {{ __('Customers') }}
                    </x-sidebar-link>
                @endcan
            </div>
        </div>
    @endcanany

    @can('view products')
        <div>
            <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Catalogue') }}</p>
            <div class="space-y-1">
                <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="products">
                    {{ __('Products') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" icon="categories">
                    {{ __('Categories') }}
                </x-sidebar-link>
            </div>
        </div>
    @endcan

    @can('view inventory')
        <div>
            <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Inventory') }}</p>
            <div class="space-y-1">
                <x-sidebar-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')" icon="inventory">
                    {{ __('Stock') }}
                </x-sidebar-link>
                @can('manage settings')
                    <x-sidebar-link :href="route('locations.index')" :active="request()->routeIs('locations.*')" icon="locations">
                        {{ __('Locations') }}
                    </x-sidebar-link>
                @endcan
            </div>
        </div>
    @endcan

    <div>
        <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Business') }}</p>
        <div class="space-y-1">
            @can('view orders')
                <x-sidebar-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" icon="payments">
                    {{ __('Payments') }}
                </x-sidebar-link>
            @endcan
            @can('manage settings')
                <x-sidebar-link :href="route('whatsapp.index')" :active="request()->routeIs('whatsapp.*')" icon="whatsapp">
                    {{ __('WhatsApp') }}
                </x-sidebar-link>
            @endcan
            @can('view reports')
                <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="reports">
                    {{ __('Reports') }}
                </x-sidebar-link>
            @endcan
            @can('manage staff')
                <x-sidebar-link :href="route('staff.index')" :active="request()->routeIs('staff.*')" icon="staff">
                    {{ __('Staff') }}
                </x-sidebar-link>
            @endcan
        </div>
    </div>

    <div class="pt-3 border-t border-white/10 space-y-1">
        @can('manage settings')
            <x-sidebar-link :href="route('settings.edit')" :active="request()->routeIs('settings.*') || request()->routeIs('billing.*')" icon="settings">
                {{ __('Settings') }}
            </x-sidebar-link>
        @endcan
        <x-sidebar-link :href="route('help.index')" :active="request()->routeIs('help.*')" icon="help">
            {{ __('Help') }}
        </x-sidebar-link>
    </div>
</div>
