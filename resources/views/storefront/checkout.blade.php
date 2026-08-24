<x-storefront-layout :business="$business">

    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ __('Checkout') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <form method="POST" action="{{ route('storefront.checkout.store', $business) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                @csrf

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
                        <x-input-label for="email" :value="__('Email (optional)')" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" />
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
                              class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Placing your order opens WhatsApp with your order details pre-filled, ready to send to :business.', ['business' => $business->name]) }}
                </p>

                <x-primary-button class="w-full justify-center py-3">{{ __('Place Order via WhatsApp') }}</x-primary-button>
            </form>
        </div>

        <div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
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
