<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Business Settings') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
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

                <details class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <summary class="cursor-pointer select-none font-semibold text-gray-800 dark:text-gray-200">{{ __('Advanced: connect WhatsApp manually') }}</summary>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 mb-4">
                        {{ __('Most stores should use the "Connect WhatsApp" button above instead. Only use this if you already have your own Meta Cloud API credentials.') }}
                    </p>

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
                </details>

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                </div>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('WhatsApp Integration') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('Connect your WhatsApp Business account to send and receive customer messages directly through the platform.') }}
                </p>

                @if ($business->hasWhatsAppCloudApi())
                    <div class="flex items-center gap-2 mb-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Connected') }}</span>
                    </div>
                    @if ($business->whatsapp_display_phone_number)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {{ __('Connected number') }}: <span class="font-mono">{{ $business->whatsapp_display_phone_number }}</span>
                        </p>
                    @endif
                    <ul class="text-sm text-green-600 dark:text-green-400 space-y-1 mb-4">
                        <li>&check; {{ __('WhatsApp connected') }}</li>
                        <li>&check; {{ __('Messages enabled') }}</li>
                    </ul>
                    <form method="POST" action="{{ route('settings.whatsapp.disconnect') }}" onsubmit="return confirm('{{ __('Disconnect WhatsApp? Automated messages will stop until you reconnect.') }}')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-md text-sm font-semibold hover:bg-red-100 dark:hover:bg-red-900/50">{{ __('Disconnect WhatsApp') }}</button>
                    </form>
                @elseif (! config('services.whatsapp.app_id') || ! config('services.whatsapp.embedded_signup_config_id'))
                    <div class="text-sm text-amber-600 dark:text-amber-400">
                        {{ __('WhatsApp connection isn\'t set up on this platform yet. Contact support.') }}
                    </div>
                @else
                    <form method="POST" action="{{ route('settings.whatsapp.connect') }}" id="whatsapp-connect-form" class="hidden">
                        @csrf
                        <input type="hidden" name="code" id="whatsapp-connect-code">
                        <input type="hidden" name="waba_id" id="whatsapp-connect-waba-id">
                        <input type="hidden" name="phone_number_id" id="whatsapp-connect-phone-number-id">
                    </form>

                    <div id="whatsapp-connect-error" class="hidden mb-4 text-sm text-red-600 dark:text-red-400"></div>

                    @unless (request()->secure())
                        <div class="mb-4 text-sm text-amber-600 dark:text-amber-400">
                            {{ __('WhatsApp connection requires a secure (HTTPS) connection — Meta blocks it on plain HTTP, including on localhost. This won\'t work until the site is served over HTTPS.') }}
                        </div>
                    @endunless

                    <button type="button" id="whatsapp-connect-button" onclick="launchWhatsAppSignup()" disabled
                            class="px-5 py-2.5 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed">
                        {{ __('Connect WhatsApp') }}
                    </button>
                    <p id="whatsapp-connect-loading" class="mt-2 text-xs text-gray-400">{{ __('Loading…') }}</p>

                    @push('scripts')
                        <script>
                            window.fbAsyncInit = function () {
                                FB.init({
                                    appId: '{{ config('services.whatsapp.app_id') }}',
                                    autoLogAppEvents: true,
                                    xfbml: false,
                                    version: '{{ config('services.whatsapp.api_version') }}',
                                });

                                const button = document.getElementById('whatsapp-connect-button');
                                const loading = document.getElementById('whatsapp-connect-loading');
                                if (button) {
                                    button.disabled = false;
                                }
                                if (loading) {
                                    loading.remove();
                                }
                            };

                            (function (d, s, id) {
                                if (d.getElementById(id)) return;
                                const js = d.createElement(s);
                                js.id = id;
                                js.src = 'https://connect.facebook.net/en_US/sdk.js';
                                js.async = true;
                                js.defer = true;
                                d.body.appendChild(js);
                            })(document, 'script', 'facebook-jssdk');

                            let waCapturedWabaId = null;
                            let waCapturedPhoneNumberId = null;

                            const WA_TRUSTED_ORIGINS = ['https://www.facebook.com', 'https://web.facebook.com'];

                            window.addEventListener('message', (event) => {
                                // Exact match only — a suffix/substring check here would also
                                // trust a spoofed domain like https://evilfacebook.com.
                                if (!WA_TRUSTED_ORIGINS.includes(event.origin)) return;

                                let data;
                                try {
                                    data = JSON.parse(event.data);
                                } catch (e) {
                                    return;
                                }

                                if (data.type !== 'WA_EMBEDDED_SIGNUP') return;

                                if (data.event === 'FINISH' && data.data) {
                                    waCapturedWabaId = data.data.waba_id;
                                    waCapturedPhoneNumberId = data.data.phone_number_id;
                                } else if (data.event === 'CANCEL') {
                                    showWhatsAppConnectError('{{ __('WhatsApp connection was cancelled.') }}');
                                } else if (data.event === 'ERROR') {
                                    showWhatsAppConnectError('{{ __('Meta reported an error during setup. Please try again.') }}');
                                }
                            });

                            function showWhatsAppConnectError(message) {
                                const el = document.getElementById('whatsapp-connect-error');
                                if (!el) return;
                                el.textContent = message;
                                el.classList.remove('hidden');
                            }

                            function launchWhatsAppSignup() {
                                showWhatsAppConnectError('');
                                document.getElementById('whatsapp-connect-error')?.classList.add('hidden');

                                // Meta's SDK refuses to open the login dialog at all on plain
                                // HTTP (no exception for localhost) and fails silently — give a
                                // real message instead of a dead button.
                                if (location.protocol !== 'https:') {
                                    showWhatsAppConnectError('{{ __('WhatsApp connection requires a secure (HTTPS) connection. It won\'t work on this page.') }}');
                                    return;
                                }

                                if (typeof FB === 'undefined') {
                                    showWhatsAppConnectError('{{ __('WhatsApp connection could not load. Please refresh and try again.') }}');
                                    return;
                                }

                                FB.login(function (response) {
                                    if (!response.authResponse || !response.authResponse.code) {
                                        showWhatsAppConnectError('{{ __('WhatsApp connection was cancelled or denied.') }}');
                                        return;
                                    }

                                    if (!waCapturedWabaId || !waCapturedPhoneNumberId) {
                                        showWhatsAppConnectError('{{ __('We could not detect your WhatsApp Business account or phone number. Please try again.') }}');
                                        return;
                                    }

                                    document.getElementById('whatsapp-connect-code').value = response.authResponse.code;
                                    document.getElementById('whatsapp-connect-waba-id').value = waCapturedWabaId;
                                    document.getElementById('whatsapp-connect-phone-number-id').value = waCapturedPhoneNumberId;
                                    document.getElementById('whatsapp-connect-form').submit();
                                }, {
                                    config_id: '{{ config('services.whatsapp.embedded_signup_config_id') }}',
                                    response_type: 'code',
                                    override_default_response_type: true,
                                    extras: { sessionInfoVersion: '3' },
                                });
                            }
                        </script>
                    @endpush
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Paystack Marketplace Account') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('Connect your bank account so your share of every sale is paid out to you automatically. Platform commission is deducted before it reaches your account.') }}
                </p>

                @if ($business->hasPaystackSubaccount())
                    <div class="text-sm text-green-700 dark:text-green-400">
                        {{ __('Connected') }} — {{ $business->paystack_account_name }} ({{ $business->paystack_account_number }})
                    </div>
                @else
                    <div class="mb-4 text-xs text-amber-600 dark:text-amber-400">{{ __('Not connected yet — payments currently settle to the platform account and your share is tracked for manual payout.') }}</div>

                    <form method="POST" action="{{ route('settings.paystack.connect') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <x-input-label for="settlement_bank" :value="__('Bank Code')" />
                            <x-text-input id="settlement_bank" name="settlement_bank" type="text" class="block mt-1 w-full" placeholder="e.g. 058" required />
                        </div>
                        <div>
                            <x-input-label for="account_number" :value="__('Account Number')" />
                            <x-text-input id="account_number" name="account_number" type="text" class="block mt-1 w-full" required />
                        </div>
                        <div class="sm:col-span-2 flex justify-end">
                            <x-primary-button>{{ __('Connect Paystack Account') }}</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
