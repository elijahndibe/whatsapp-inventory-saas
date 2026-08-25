@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $plan->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="sort_order" :value="__('Sort Order')" />
        <x-text-input id="sort_order" name="sort_order" type="number" class="block mt-1 w-full" :value="old('sort_order', $plan->sort_order ?? 0)" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
    <div>
        <x-input-label for="price" :value="__('Price (0 = free)')" />
        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('price', $plan->price ?? 0)" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="currency" :value="__('Currency')" />
        <x-text-input id="currency" name="currency" type="text" maxlength="3" class="block mt-1 w-full uppercase" :value="old('currency', $plan->currency ?? 'NGN')" required />
    </div>
    <div>
        <x-input-label for="duration_days" :value="__('Duration (days)')" />
        <x-text-input id="duration_days" name="duration_days" type="number" min="1" class="block mt-1 w-full" :value="old('duration_days', $plan->duration_days ?? 30)" required />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-4">
    <div>
        <x-input-label for="max_products" :value="__('Max Products (blank = unlimited)')" />
        <x-text-input id="max_products" name="max_products" type="number" min="0" class="block mt-1 w-full" :value="old('max_products', $plan->max_products ?? '')" />
    </div>
    <div>
        <x-input-label for="max_orders_per_month" :value="__('Max Orders/Month')" />
        <x-text-input id="max_orders_per_month" name="max_orders_per_month" type="number" min="0" class="block mt-1 w-full" :value="old('max_orders_per_month', $plan->max_orders_per_month ?? '')" />
    </div>
    <div>
        <x-input-label for="max_staff" :value="__('Max Staff')" />
        <x-text-input id="max_staff" name="max_staff" type="number" min="0" class="block mt-1 w-full" :value="old('max_staff', $plan->max_staff ?? '')" />
    </div>
    <div>
        <x-input-label for="max_locations" :value="__('Max Locations')" />
        <x-text-input id="max_locations" name="max_locations" type="number" min="0" class="block mt-1 w-full" :value="old('max_locations', $plan->max_locations ?? '')" />
    </div>
</div>

<div class="mt-4">
    <x-input-label :value="__('Features')" />
    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach (\App\Models\Plan::FEATURES as $key => $label)
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="features[{{ $key }}]" value="1"
                       @checked(old("features.$key", $plan->features[$key] ?? false))
                       class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                {{ __($label) }}
            </label>
        @endforeach
    </div>
</div>

<div class="mt-4 flex items-center">
    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $plan->is_active ?? true))
           class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
    <label for="is_active" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Active (visible to businesses)') }}</label>
</div>

<div class="flex items-center justify-end mt-6 gap-3">
    <a href="{{ route('admin.plans.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    <x-primary-button>{{ __('Save Plan') }}</x-primary-button>
</div>
