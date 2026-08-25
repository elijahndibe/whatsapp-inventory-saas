@csrf

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $location->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="address" :value="__('Address')" />
    <x-text-input id="address" name="address" type="text" class="block mt-1 w-full" :value="old('address', $location->address ?? '')" />
</div>

<div class="mt-4">
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" class="block mt-1 w-full" :value="old('phone', $location->phone ?? '')" />
</div>

<div class="mt-4">
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm">
        <option value="active" @selected(old('status', $location->status ?? 'active') === 'active')>{{ __('Active') }}</option>
        <option value="inactive" @selected(old('status', $location->status ?? 'active') === 'inactive')>{{ __('Inactive') }}</option>
    </select>
</div>

<div class="flex items-center justify-end mt-6 gap-3">
    <a href="{{ route('locations.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    <x-primary-button>{{ __('Save Location') }}</x-primary-button>
</div>
