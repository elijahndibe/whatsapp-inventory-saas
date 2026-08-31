<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Settings') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
             x-data="{
                tab: (['business', 'storefront', 'payments', 'whatsapp', 'locations', 'staff', 'notifications', 'security'].includes(window.location.hash.slice(1)))
                    ? window.location.hash.slice(1)
                    : 'business',
             }"
             x-init="$watch('tab', value => history.replaceState(null, '', '#' + value))">

            <x-flash-messages />

            {{-- Tab bar — a single settings.update form spans every panel below,
                 so switching tabs never drops unsaved edits in another tab. --}}
            <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <nav class="flex gap-1 min-w-max" role="tablist" aria-label="{{ __('Settings sections') }}">
                    @foreach ([
                        'business' => __('Business'),
                        'storefront' => __('Storefront'),
                        'payments' => __('Payments'),
                        'whatsapp' => __('WhatsApp'),
                        'locations' => __('Locations'),
                        'staff' => __('Staff'),
                        'notifications' => __('Notifications'),
                        'security' => __('Security'),
                    ] as $key => $label)
                        <button type="button" role="tab" :aria-selected="tab === '{{ $key }}'"
                                @click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}'
                                    ? 'border-brand-600 text-brand-700 dark:text-brand-400'
                                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300'"
                                class="px-3 py-2.5 text-sm font-medium border-b-2 whitespace-nowrap transition">
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Business: account-level identity and operational defaults. --}}
                <div x-show="tab === 'business'" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6 space-y-4">
                    <div>
                        <x-input-label for="name" :value="__('Business Name')" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $business->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Business Email')" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $business->email)" />
                    </div>

                    <x-geo-fields
                        :country="old('country', $business->country)"
                        :currency="old('currency', $business->currency)"
                        :timezone="old('timezone', $business->timezone)"
                        :phone="old('phone', $business->phone)"
                        :phone-label="__('Phone')" />

                    <div class="flex items-center">
                        <input id="allow_overselling" name="allow_overselling" type="checkbox" value="1" @checked(old('allow_overselling', $business->allow_overselling))
                               class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                        <label for="allow_overselling" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Allow selling below zero stock (overselling)') }}</label>
                    </div>
                </div>

                {{-- Storefront: exactly what a customer sees on the public store page. --}}
                <div x-show="tab === 'storefront'" x-cloak class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Your storefront') }}</p>
                                <a href="{{ route('storefront.show', $business) }}" target="_blank" class="font-mono text-sm text-brand-700 dark:text-brand-400 hover:underline truncate block">{{ url('/store/'.$business->slug) }}</a>
                            </div>
                            <a href="{{ route('storefront.show', $business) }}" target="_blank" class="shrink-0">
                                <x-outline-button type="button"><x-icon name="external-link" class="w-4 h-4" /> {{ __('View store') }}</x-outline-button>
                            </a>
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3"
                                      class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description', $business->description) }}</textarea>
                            <p class="mt-1 text-xs text-gray-400">{{ __('Shown at the top of your storefront, under your business name.') }}</p>
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

                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" name="address" type="text" class="block mt-1 w-full" :value="old('address', $business->address)" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="city" :value="__('City')" />
                                <x-text-input id="city" name="city" type="text" class="block mt-1 w-full" :value="old('city', $business->city)" />
                            </div>
                            <div>
                                <x-input-label for="state" :value="__('State')" />
                                <x-text-input id="state" name="state" type="text" class="block mt-1 w-full" :value="old('state', $business->state)" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- WhatsApp: the number used for ordering, plus the optional Cloud API connection. --}}
                <div x-show="tab === 'whatsapp'" x-cloak class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                        <x-input-label for="whatsapp_number" :value="__('WhatsApp Number (for the Order via WhatsApp button)')" />
                        <x-text-input id="whatsapp_number" name="whatsapp_number" type="text" class="block mt-1 w-full max-w-sm" :value="old('whatsapp_number', $business->whatsapp_number)" placeholder="+234..." />
                        <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
                        <p class="mt-2 text-xs text-gray-400">
                            {{ __('This basic click-to-chat ordering is always free. See the') }}
                            <a href="{{ route('whatsapp.index') }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('WhatsApp page') }}</a>
                            {{ __('to share your store link.') }}
                        </p>
                    </div>

                    <details class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                        <summary class="cursor-pointer select-none font-semibold text-gray-800 dark:text-gray-200">{{ __('Advanced: connect WhatsApp manually') }}</summary>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 mb-4">
                            {{ __('Most stores should use the "Connect WhatsApp" button below instead. Only use this if you already have your own Meta Cloud API credentials.') }}
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
                </div>

                <div x-show="tab === 'business' || tab === 'storefront' || tab === 'whatsapp'" x-cloak class="flex justify-end">
                    <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                </div>
            </form>

            {{-- Payments and the Cloud API connect button live outside the form above —
                 each posts to its own dedicated endpoint (a <form> can't nest inside
                 another) — but stay inside the same x-data scope so the tab switcher
                 above still controls them. --}}
            <div x-show="tab === 'whatsapp'" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('WhatsApp Cloud API Connection') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('Optional — connect your WhatsApp Business account to send and receive customer messages directly through the platform.') }}
                    </p>

                    @if ($business->hasWhatsAppCloudApi())
                        <div class="flex items-center gap-2 mb-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-success"></span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Connected') }}</span>
                        </div>
                        @if ($business->whatsapp_display_phone_number)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {{ __('Connected number') }}: <span class="font-mono">{{ $business->whatsapp_display_phone_number }}</span>
                            </p>
                        @endif
                        <ul class="text-sm text-success-strong space-y-1 mb-4">
                            <li>&check; {{ __('WhatsApp connected') }}</li>
                            <li>&check; {{ __('Messages enabled') }}</li>
                        </ul>
                        <form method="POST" action="{{ route('settings.whatsapp.disconnect') }}" onsubmit="return confirm('{{ __('Disconnect WhatsApp? Automated messages will stop until you reconnect.') }}')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-danger-bg dark:bg-red-900/30 text-danger-strong dark:text-red-300 rounded-md text-sm font-semibold hover:bg-red-100 dark:hover:bg-red-900/50">{{ __('Disconnect WhatsApp') }}</button>
                        </form>
                    @elseif (! config('services.whatsapp.app_id') || ! config('services.whatsapp.embedded_signup_config_id'))
                        <div class="text-sm text-warning-strong">
                            {{ __('WhatsApp connection isn\'t set up on this platform yet. Contact support.') }}
                        </div>
                    @else
                        <form method="POST" action="{{ route('settings.whatsapp.connect') }}" id="whatsapp-connect-form" class="hidden">
                            @csrf
                            <input type="hidden" name="code" id="whatsapp-connect-code">
                            <input type="hidden" name="waba_id" id="whatsapp-connect-waba-id">
                            <input type="hidden" name="phone_number_id" id="whatsapp-connect-phone-number-id">
                        </form>

                        <div id="whatsapp-connect-error" class="hidden mb-4 text-sm text-danger"></div>

                        @unless (request()->secure())
                            <div class="mb-4 text-sm text-warning-strong">
                                {{ __('WhatsApp connection requires a secure (HTTPS) connection — Meta blocks it on plain HTTP, including on localhost. This won\'t work until the site is served over HTTPS.') }}
                            </div>
                        @endunless

                        <button type="button" id="whatsapp-connect-button" onclick="launchWhatsAppSignup()" disabled
                                class="px-5 py-2.5 bg-whatsapp text-white rounded-md text-sm font-semibold hover:bg-whatsapp-dark disabled:opacity-60 disabled:cursor-not-allowed">
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

                {{-- Payments: where your share of every sale gets paid out to.
                     No payment-processor branding here on purpose — a seller
                     just needs to know this is their bank account, not what
                     powers it behind the scenes. --}}
                <div x-show="tab === 'payments'" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Payout Bank Account') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('Add your bank account so your share of every sale is paid out to you automatically. Zwenko\'s commission is deducted before it reaches your account.') }}
                    </p>

                    @if ($business->hasPaystackSubaccount())
                        <div class="text-sm text-success-strong">
                            {{ __('Connected') }} — {{ $business->paystack_account_name }} ({{ $business->paystack_account_number }})
                        </div>
                    @else
                        <div class="mb-4 text-xs text-warning-strong">{{ __('Not connected yet — your share of each sale is tracked and paid out to you manually until you add a bank account here.') }}</div>

                        <form method="POST" action="{{ route('settings.paystack.connect') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @csrf
                            <div>
                                <x-input-label for="settlement_bank" :value="__('Bank')" />
                                @if ($banks)
                                    <select id="settlement_bank" name="settlement_bank" required
                                            class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                        <option value="">{{ __('Select your bank') }}</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    {{-- Bank list couldn't be loaded — still let a seller
                                         connect a payout account if they already know
                                         their bank's settlement code, rather than
                                         blocking this entirely on an unrelated outage. --}}
                                    <x-text-input id="settlement_bank" name="settlement_bank" type="text" class="block mt-1 w-full" placeholder="{{ __('Your bank\'s settlement code') }}" required />
                                @endif
                            </div>
                            <div>
                                <x-input-label for="account_number" :value="__('Account Number')" />
                                <x-text-input id="account_number" name="account_number" type="text" class="block mt-1 w-full" required />
                            </div>
                            <div class="sm:col-span-2 flex justify-end">
                                <x-primary-button>{{ __('Connect Bank Account') }}</x-primary-button>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Locations and Staff already have full dedicated screens with their
                     own CRUD — link out instead of duplicating that UI here. --}}
                <div x-show="tab === 'locations'" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Locations') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Manage the warehouses, stores and pickup points you stock from.') }}</p>
                    </div>
                    <a href="{{ route('locations.index') }}" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800">
                        {{ __('Manage Locations') }}
                    </a>
                </div>

                <div x-show="tab === 'staff'" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Staff') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Invite teammates and control what they can access.') }}</p>
                    </div>
                    <a href="{{ route('staff.index') }}" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800">
                        {{ __('Manage Staff') }}
                    </a>
                </div>

            {{-- Notifications: per-user email toggles for the notification types
                 that already exist (App\Notifications\*). The in-app bell is
                 always on — only email delivery is optional. --}}
            <div x-show="tab === 'notifications'" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Email Notifications') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('You\'ll always see these in your notification bell — choose which ones also go to your email.') }}
                </p>

                <form method="POST" action="{{ route('notification-preferences.update') }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    @foreach ([
                        'new_order' => [__('New orders'), __('When a customer places an order via your storefront or WhatsApp.')],
                        'payment_received' => [__('Payments received'), __('When an order is paid for.')],
                        'low_stock' => [__('Low stock alerts'), __('When a product runs low or goes out of stock.')],
                    ] as $type => [$label, $description])
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-700 cursor-pointer">
                            <input type="checkbox" name="email[]" value="{{ $type }}" @checked(auth()->user()->wantsEmailNotification($type))
                                   class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                            <span>
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $description }}</span>
                            </span>
                        </label>
                    @endforeach

                    <div class="flex justify-end pt-2">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Security: reuses the exact same password form as the Profile page
                 (same route, same validation) rather than duplicating it. Signing
                 out other sessions and two-factor authentication aren't built —
                 both would need new, security-sensitive infrastructure (global
                 session-invalidation middleware; TOTP secrets, recovery codes and
                 a second login step) that deserves its own dedicated review
                 rather than being folded into this settings reorganization. --}}
            <div x-show="tab === 'security'" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                @include('profile.partials.update-password-form')
            </div>

        </div>
    </div>
</x-app-layout>
