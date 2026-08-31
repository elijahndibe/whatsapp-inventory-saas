<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">
                {{ __('Coupons') }}
            </h2>
            @can('create', \App\Models\Coupon::class)
                @if ($couponsEnabled)
                    <a href="{{ route('coupons.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                        <x-icon name="plus" class="w-4 h-4" />
                        {{ __('New Coupon') }}
                    </a>
                @endif
            @endcan
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <x-flash-messages />

            @unless ($couponsEnabled)
                <div class="rounded-lg border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                    {{ __('Coupon codes aren\'t available on your current plan. Existing coupons below still work — you just can\'t create new ones until you upgrade.') }}
                </div>
            @endunless

            <form method="GET" class="flex gap-2">
                <div class="relative flex-1 max-w-xs">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-icon name="search" class="w-4 h-4 text-gray-400" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search codes...') }}"
                           class="block w-full pl-9 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" />
                </div>
                <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ __('Search') }}
                </button>
            </form>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                @if ($coupons->isEmpty())
                    <x-empty-state icon="tag" :title="__('No coupons yet')" :description="__('Create a discount code customers can enter at checkout.')">
                        @if ($couponsEnabled)
                            <x-slot name="action">
                                @can('create', \App\Models\Coupon::class)
                                    <a href="{{ route('coupons.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                                        {{ __('Add coupon') }}
                                    </a>
                                @endcan
                            </x-slot>
                        @endif
                    </x-empty-state>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Code') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Discount') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Used') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Expires') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($coupons as $coupon)
                                    <tr>
                                        <td class="px-4 py-3 font-mono font-medium text-gray-900 dark:text-gray-100">{{ $coupon->code }}</td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $coupon->valueLabel(auth()->user()->business->currencySymbol()) }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $coupon->times_used }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $coupon->expires_at?->format('d M Y') ?? __('Never') }}</td>
                                        <td class="px-4 py-3">
                                            @if (! $coupon->is_active)
                                                <x-badge variant="neutral">{{ __('Inactive') }}</x-badge>
                                            @elseif ($coupon->isExpired())
                                                <x-badge variant="danger">{{ __('Expired') }}</x-badge>
                                            @elseif ($coupon->hasReachedUsageLimit())
                                                <x-badge variant="danger">{{ __('Limit reached') }}</x-badge>
                                            @elseif (! $coupon->hasStarted())
                                                <x-badge variant="warning">{{ __('Scheduled') }}</x-badge>
                                            @else
                                                <x-badge variant="success">{{ __('Active') }}</x-badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                            <a href="{{ route('coupons.edit', $coupon) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
                                            @can('delete', $coupon)
                                                <form method="POST" action="{{ route('coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('{{ __('Delete this coupon?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Delete') }}</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{ $coupons->links() }}
        </div>
    </div>
</x-app-layout>
