{{-- Country / Currency / Timezone selects, auto-detected from the browser
     via the geoPicker Alpine component (resources/js/geo.js) and always
     left editable. Shared between Registration and Settings so the two
     never drift out of sync — see config/countries.php for the dataset.

     class="contents" keeps this wrapper out of the parent's own layout
     (grid/flex/space-y) — its children lay out exactly as if they were
     direct children of whatever includes this component. --}}
@props([
    'country' => '',
    'currency' => '',
    'timezone' => '',
    'phone' => '',
    'showPhone' => true,
    'phoneLabel' => null,
])
<div x-data="geoPicker({
        countries: @js(config('countries')),
        country: @js($country),
        currency: @js($currency),
        timezone: @js($timezone),
        phone: @js($phone),
     })" class="contents">

    @if ($showPhone)
        <div>
            <x-input-label for="phone_number" :value="$phoneLabel ?? __('WhatsApp / Phone Number')" />
            <div class="mt-1 flex gap-2">
                <select x-model="dialCode" aria-label="{{ __('Country code') }}"
                        class="w-32 shrink-0 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    <template x-for="c in countries" :key="c.code">
                        <option :value="c.dial" x-text="c.dial + ' ' + c.code"></option>
                    </template>
                </select>
                <input id="phone_number" type="tel" x-model="phoneNumber" inputmode="numeric" autocomplete="tel-national"
                       class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500"
                       placeholder="{{ __('8012345678') }}">
            </div>
            <input type="hidden" name="phone" :value="fullPhone">
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>
    @endif

    <div @class(['grid grid-cols-1 sm:grid-cols-2 gap-4', 'mt-4' => $showPhone])>
        <div>
            <x-input-label for="country" :value="__('Country')" />
            <select id="country" name="country" x-model="country" @change="countryChanged()"
                    class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">{{ __('Select country') }}</option>
                <template x-for="c in countries" :key="c.code">
                    <option :value="c.name" x-text="c.name"></option>
                </template>
            </select>
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="currency" :value="__('Currency')" />
            <select id="currency" name="currency" x-model="currency"
                    class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">{{ __('Select currency') }}</option>
                <template x-for="cur in [...new Set(countries.map(c => c.currency))].sort()" :key="cur">
                    <option :value="cur" x-text="cur"></option>
                </template>
            </select>
            <x-input-error :messages="$errors->get('currency')" class="mt-2" />
        </div>
    </div>

    <div class="mt-4">
        <x-input-label for="timezone" :value="__('Timezone')" />
        <select id="timezone" name="timezone" x-model="timezone"
                class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">{{ __('Select timezone') }}</option>
            <template x-for="tz in timezoneOptions" :key="tz">
                <option :value="tz" x-text="tz"></option>
            </template>
        </select>
        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
    </div>

    <p x-show="autoDetected" x-cloak class="mt-2 flex items-center gap-1.5 text-xs text-brand-700 dark:text-brand-400">
        <x-icon name="check-circle" class="w-3.5 h-3.5 shrink-0" />
        {{ __("Detected automatically from your browser — change anything that's not right.") }}
    </p>
</div>
