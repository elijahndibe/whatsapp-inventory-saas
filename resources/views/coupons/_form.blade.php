@csrf

<div x-data="{ type: '{{ old('type', $coupon->type ?? 'percentage') }}' }">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="code" :value="__('Code')" />
            <x-text-input id="code" name="code" type="text" class="block mt-1 w-full uppercase" :value="old('code', $coupon->code ?? '')" required autofocus placeholder="SAVE10" />
            <p class="mt-1 text-xs text-gray-400">{{ __('Letters, numbers and hyphens only — always shown to customers in capitals.') }}</p>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="type" :value="__('Discount type')" />
            <select id="type" name="type" x-model="type" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm">
                <option value="percentage" @selected(old('type', $coupon->type ?? 'percentage') === 'percentage')>{{ __('Percentage off') }}</option>
                <option value="fixed" @selected(old('type', $coupon->type ?? '') === 'fixed')>{{ __('Fixed amount off') }}</option>
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
            <x-input-label for="value">
                <span x-text="type === 'percentage' ? '{{ __('Percentage (%)') }}' : '{{ __('Amount off') }}'"></span>
            </x-input-label>
            <x-text-input id="value" name="value" type="number" step="0.01" min="0.01" class="block mt-1 w-full" :value="old('value', $coupon->value ?? '')" required />
            <x-input-error :messages="$errors->get('value')" class="mt-2" />
        </div>

        <div x-show="type === 'percentage'" x-cloak>
            <x-input-label for="max_discount_amount" :value="__('Max discount amount (optional)')" />
            <x-text-input id="max_discount_amount" name="max_discount_amount" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('max_discount_amount', $coupon->max_discount_amount ?? '')" />
            <p class="mt-1 text-xs text-gray-400">{{ __('Caps how much a percentage code can take off a single order.') }}</p>
            <x-input-error :messages="$errors->get('max_discount_amount')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
            <x-input-label for="minimum_order_amount" :value="__('Minimum order amount (optional)')" />
            <x-text-input id="minimum_order_amount" name="minimum_order_amount" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('minimum_order_amount', $coupon->minimum_order_amount ?? '')" />
            <x-input-error :messages="$errors->get('minimum_order_amount')" class="mt-2" />
        </div>
        <div></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
            <x-input-label for="usage_limit" :value="__('Total uses allowed (optional)')" />
            <x-text-input id="usage_limit" name="usage_limit" type="number" min="1" class="block mt-1 w-full" :value="old('usage_limit', $coupon->usage_limit ?? '')" placeholder="{{ __('Unlimited') }}" />
            <x-input-error :messages="$errors->get('usage_limit')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="usage_limit_per_customer" :value="__('Uses per customer (optional)')" />
            <x-text-input id="usage_limit_per_customer" name="usage_limit_per_customer" type="number" min="1" class="block mt-1 w-full" :value="old('usage_limit_per_customer', $coupon->usage_limit_per_customer ?? '')" placeholder="{{ __('Unlimited') }}" />
            <x-input-error :messages="$errors->get('usage_limit_per_customer')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
            <x-input-label for="starts_at" :value="__('Starts (optional)')" />
            <x-text-input id="starts_at" name="starts_at" type="date" class="block mt-1 w-full" :value="old('starts_at', optional($coupon->starts_at ?? null)->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="expires_at" :value="__('Expires (optional)')" />
            <x-text-input id="expires_at" name="expires_at" type="date" class="block mt-1 w-full" :value="old('expires_at', optional($coupon->expires_at ?? null)->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('expires_at')" class="mt-2" />
        </div>
    </div>

    <div class="mt-4 flex items-center">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $coupon->is_active ?? true))
               class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
        <label for="is_active" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Active') }}</label>
    </div>

    <div class="flex items-center justify-end mt-6 gap-3">
        <a href="{{ route('coupons.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
        <x-primary-button>{{ __('Save Coupon') }}</x-primary-button>
    </div>
</div>
