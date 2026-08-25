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

<div class="mt-4 flex items-center gap-6">
    <div class="flex items-center">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $plan->is_active ?? true))
               class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
        <label for="is_active" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Active (visible to businesses)') }}</label>
    </div>
    <div class="flex items-center">
        <input id="is_default" name="is_default" type="checkbox" value="1" @checked(old('is_default', $plan->is_default ?? false))
               class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
        <label for="is_default" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Default plan (assigned to businesses with no active subscription)') }}</label>
    </div>
</div>

<p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
    {{ __('Feature access and numeric limits (products, staff, locations, etc.) for this plan are managed on the') }}
    <a href="{{ route('admin.features.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Features') }}</a> {{ __('page.') }}
</p>

<div class="flex items-center justify-end mt-6 gap-3">
    <a href="{{ route('admin.plans.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    <x-primary-button>{{ __('Save Plan') }}</x-primary-button>
</div>
