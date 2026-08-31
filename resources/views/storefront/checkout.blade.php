<x-storefront-layout :business="$business">

    <h1 class="text-2xl font-semibold text-ink dark:text-gray-100 mb-4">{{ __('Checkout') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <form method="POST" action="{{ route('storefront.checkout.store', $business) }}"
                  x-data="{ paymentMethod: '{{ old('payment_method', 'whatsapp') }}' }"
                  class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 space-y-4">
                @csrf

                <div>
                    <x-input-label :value="__('Payment Method')" />
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label @class([
                            'flex items-center gap-2 border rounded-md px-4 py-3 text-sm cursor-pointer',
                            'border-brand-500 ring-1 ring-brand-500' => old('payment_method', 'whatsapp') === 'whatsapp',
                            'border-gray-300 dark:border-gray-700' => old('payment_method', 'whatsapp') !== 'whatsapp',
                        ])>
                            <input type="radio" name="payment_method" value="whatsapp" x-model="paymentMethod" class="text-brand-600">
                            <span class="text-gray-900 dark:text-gray-100">{{ __('Order via WhatsApp') }}</span>
                        </label>
                        @if ($canPayOnline)
                            <label @class([
                                'flex items-center gap-2 border rounded-md px-4 py-3 text-sm cursor-pointer',
                                'border-brand-500 ring-1 ring-brand-500' => old('payment_method') === 'paystack',
                                'border-gray-300 dark:border-gray-700' => old('payment_method') !== 'paystack',
                            ])>
                                <input type="radio" name="payment_method" value="paystack" x-model="paymentMethod" class="text-brand-600">
                                <span class="text-gray-900 dark:text-gray-100">{{ __('Pay Online Now') }}</span>
                            </label>
                        @endif
                    </div>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="name" :value="__('Full Name')" />
                    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="phone" :value="__('WhatsApp / Phone Number')" />
                        <x-text-input id="phone" name="phone" type="text" class="block mt-1 w-full" :value="old('phone')" required placeholder="+234..." />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email">
                            <span x-text="paymentMethod === 'paystack' ? '{{ __('Email') }}' : '{{ __('Email (optional)') }}'"></span>
                        </x-input-label>
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" :required="false" x-bind:required="paymentMethod === 'paystack'" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address" :value="__('Delivery Address')" />
                    <x-text-input id="address" name="address" type="text" class="block mt-1 w-full" :value="old('address')" required />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="city" :value="__('City (optional)')" />
                        <x-text-input id="city" name="city" type="text" class="block mt-1 w-full" :value="old('city')" />
                    </div>
                    <div>
                        <x-input-label for="state" :value="__('State (optional)')" />
                        <x-text-input id="state" name="state" type="text" class="block mt-1 w-full" :value="old('state')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Order Notes (optional)')" />
                    <textarea id="notes" name="notes" rows="2"
                              class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400" x-show="paymentMethod === 'whatsapp'" x-cloak>
                    {{ __('Placing your order opens WhatsApp with your order details pre-filled, ready to send to :business.', ['business' => $business->name]) }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400" x-show="paymentMethod === 'paystack'" x-cloak>
                    {{ __("You'll be redirected to Paystack to pay by card, bank transfer, or USSD.") }}
                </p>

                <x-primary-button class="w-full justify-center py-3">
                    <span x-show="paymentMethod === 'whatsapp'" x-cloak>{{ __('Place Order via WhatsApp') }}</span>
                    <span x-show="paymentMethod === 'paystack'" x-cloak>{{ __('Continue to Payment') }}</span>
                </x-primary-button>
            </form>
        </div>

        <div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Order Summary') }}</h2>
                <div class="space-y-2 text-sm">
                    @foreach ($items as $item)
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>{{ $item->product->name }} &times;{{ $item->quantity }}</span>
                            <span>{{ $business->currencySymbol() }}{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3 flex justify-between font-semibold text-gray-900 dark:text-gray-100">
                    <span>{{ __('Total') }}</span>
                    <span>{{ $business->currencySymbol() }}{{ number_format($subtotal, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

</x-storefront-layout>
