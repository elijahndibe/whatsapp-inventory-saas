<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Monetization') }}</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Transaction Commission') }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('The platform earns a commission on every successful payment. Individual sellers can be given a custom rate from their Business page.') }}</p>

            <form method="POST" action="{{ route('admin.monetization.commission.update') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                @csrf
                @method('PUT')
                <div class="flex items-center">
                    <input id="commission_enabled" name="commission_enabled" type="checkbox" value="1" @checked($commissionEnabled)
                           class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                    <label for="commission_enabled" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Enabled') }}</label>
                </div>
                <div>
                    <x-input-label for="commission_type" :value="__('Type')" />
                    <select id="commission_type" name="commission_type" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        <option value="percentage" @selected($commissionType === 'percentage')>{{ __('Percentage') }}</option>
                        <option value="fixed" @selected($commissionType === 'fixed')>{{ __('Fixed') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="commission_rate" :value="__('Default Rate (%)')" />
                    <x-text-input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" class="block mt-1 w-full" :value="$commissionRate" required />
                </div>
                <div>
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
                <div>
                    <x-input-label for="commission_min" :value="__('Minimum Rate')" />
                    <x-text-input id="commission_min" name="commission_min" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="$commissionMin" />
                </div>
                <div>
                    <x-input-label for="commission_max" :value="__('Maximum Rate')" />
                    <x-text-input id="commission_max" name="commission_max" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="$commissionMax" />
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Subscriptions') }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                {{ __('While OFF, every business gets full access to globally-enabled features with no plan-tier limits, and no seller is prompted to subscribe. Turn ON to start enforcing the Free/Pro/Business matrix configured on the Features page.') }}
            </p>
            <form method="POST" action="{{ route('admin.monetization.subscription-system.update') }}" class="flex items-center gap-4">
                @csrf
                @method('PUT')
                <div class="flex items-center">
                    <input id="subscription_enabled" name="subscription_enabled" type="checkbox" value="1" @checked($subscriptionEnabled)
                           class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                    <label for="subscription_enabled" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Subscription System ON') }}</label>
                </div>
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                <span class="text-xs text-gray-400">
                    {{ __('Currently:') }} <strong>{{ $subscriptionEnabled ? __('ON — tier limits enforced') : __('OFF — commission-only mode') }}</strong>
                </span>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Plans & Features') }}</h3>
                <div class="mt-2 flex gap-4 text-sm">
                    <a href="{{ route('admin.plans.index') }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Manage Plans') }}</a>
                    <a href="{{ route('admin.features.index') }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Manage Features') }}</a>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Payment Settings') }}</h3>
                <p class="text-sm mt-2 {{ $paystackConfigured ? 'text-success' : 'text-warning' }}">
                    {{ $paystackConfigured ? __('Paystack Connected') : __('Paystack Not Connected') }}
                </p>
            </div>
        </div>
    </div>
</x-admin-layout>
