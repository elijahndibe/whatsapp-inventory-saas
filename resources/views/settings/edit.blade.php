<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Business Settings') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Business Profile') }}</h3>

                    <div>
                        <x-input-label for="name" :value="__('Business Name')" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $business->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3"
                                  class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $business->description) }}</textarea>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="logo" :value="__('Logo')" />
                        @if ($business->logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($business->logo) }}" class="h-16 w-16 rounded-full object-cover mt-2 mb-2" alt="">
                        @endif
                        <input id="logo" name="logo" type="file" accept="image/*"
                               class="block mt-1 w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-200" />
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-input-label for="email" :value="__('Business Email')" />
                            <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $business->email)" />
                        </div>
                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="block mt-1 w-full" :value="old('phone', $business->phone)" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="whatsapp_number" :value="__('WhatsApp Number (for the Order via WhatsApp button)')" />
                        <x-text-input id="whatsapp_number" name="whatsapp_number" type="text" class="block mt-1 w-full" :value="old('whatsapp_number', $business->whatsapp_number)" placeholder="+234..." />
                        <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="address" :value="__('Address')" />
                        <x-text-input id="address" name="address" type="text" class="block mt-1 w-full" :value="old('address', $business->address)" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                        <div>
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="block mt-1 w-full" :value="old('city', $business->city)" />
                        </div>
                        <div>
                            <x-input-label for="state" :value="__('State')" />
                            <x-text-input id="state" name="state" type="text" class="block mt-1 w-full" :value="old('state', $business->state)" />
                        </div>
                        <div>
                            <x-input-label for="country" :value="__('Country')" />
                            <x-text-input id="country" name="country" type="text" class="block mt-1 w-full" :value="old('country', $business->country)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-input-label for="currency" :value="__('Currency Code (e.g. NGN)')" />
                            <x-text-input id="currency" name="currency" type="text" maxlength="3" class="block mt-1 w-full uppercase" :value="old('currency', $business->currency)" required />
                            <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="timezone" :value="__('Timezone')" />
                            <x-text-input id="timezone" name="timezone" type="text" class="block mt-1 w-full" :value="old('timezone', $business->timezone)" required />
                        </div>
                    </div>

                    <div class="mt-4 flex items-center">
                        <input id="allow_overselling" name="allow_overselling" type="checkbox" value="1" @checked(old('allow_overselling', $business->allow_overselling))
                               class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <label for="allow_overselling" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Allow selling below zero stock (overselling)') }}</label>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('WhatsApp Cloud API') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('Configure this to send automated order and payment updates to customers. Get these values from your Meta for Developers app.') }}
                    </p>

                    @unless ($business->hasWhatsAppCloudApi())
                        <div class="mb-4 text-xs text-amber-600 dark:text-amber-400">{{ __('Not configured yet — automated WhatsApp messages are disabled until this is set up.') }}</div>
                    @endunless

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="whatsapp_phone_number_id" :value="__('Phone Number ID')" />
                            <x-text-input id="whatsapp_phone_number_id" name="whatsapp_phone_number_id" type="text" class="block mt-1 w-full" :value="old('whatsapp_phone_number_id', $business->whatsapp_phone_number_id)" />
                        </div>
                        <div>
                            <x-input-label for="whatsapp_business_account_id" :value="__('Business Account ID')" />
                            <x-text-input id="whatsapp_business_account_id" name="whatsapp_business_account_id" type="text" class="block mt-1 w-full" :value="old('whatsapp_business_account_id', $business->whatsapp_business_account_id)" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="whatsapp_access_token" :value="__('Access Token')" />
                        <x-text-input id="whatsapp_access_token" name="whatsapp_access_token" type="password" class="block mt-1 w-full" placeholder="{{ $business->whatsapp_access_token ? '•••••••• (unchanged — enter a new token to replace it)' : '' }}" />
                        <x-input-error :messages="$errors->get('whatsapp_access_token')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
