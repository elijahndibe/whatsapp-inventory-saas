<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Help Center') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Quick answers, and how to reach us.') }}</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <x-card>
            <h3 class="font-semibold text-ink dark:text-gray-100 mb-3">{{ __('Getting started') }}</h3>
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <p><strong class="text-ink dark:text-gray-100">{{ __('Add your first product') }}</strong> — {{ __('go to Products → Add product. Give it a name, price and starting stock.') }}</p>
                <p><strong class="text-ink dark:text-gray-100">{{ __('Share your store') }}</strong> — {{ __('your storefront link is on the WhatsApp page — send it to customers on WhatsApp, Instagram, anywhere.') }}</p>
                <p><strong class="text-ink dark:text-gray-100">{{ __('Get paid') }}</strong> — {{ __('connect Paystack from Settings so payments (and your share, after Zwenko\'s small commission) land straight in your bank account.') }}</p>
            </div>
        </x-card>

        <x-card>
            <h3 class="font-semibold text-ink dark:text-gray-100 mb-3">{{ __('Frequently asked') }}</h3>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <details class="py-3 group">
                    <summary class="cursor-pointer font-medium text-sm text-ink dark:text-gray-100 flex items-center justify-between">
                        {{ __('Does Zwenko charge a monthly fee?') }}
                        <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition" />
                    </summary>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('No — Zwenko is free to use. We only take a small commission (currently 1.5%) on successful sales made through Paystack.') }}</p>
                </details>
                <details class="py-3 group">
                    <summary class="cursor-pointer font-medium text-sm text-ink dark:text-gray-100 flex items-center justify-between">
                        {{ __('Is WhatsApp ordering really free?') }}
                        <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition" />
                    </summary>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Yes. Click-to-chat WhatsApp ordering is a core feature available to every seller, on every plan, forever.') }}</p>
                </details>
                <details class="py-3 group">
                    <summary class="cursor-pointer font-medium text-sm text-ink dark:text-gray-100 flex items-center justify-between">
                        {{ __('How do I get paid for a WhatsApp order?') }}
                        <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition" />
                    </summary>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Open the order, click "Confirm order & request payment" to generate a secure Paystack link, then "Send payment link on WhatsApp".') }}</p>
                </details>
            </div>
        </x-card>

        <x-card class="bg-brand-50 dark:bg-brand-950/30 border-brand-100 dark:border-brand-900">
            <h3 class="font-semibold text-ink dark:text-gray-100 mb-1">{{ __('Still need help?') }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">{{ __('Reach the Zwenko support team directly.') }}</p>
            @if (config('app.support_phone'))
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('app.support_phone')) }}" target="_blank" rel="noopener">
                    <x-success-button type="button"><x-icon name="whatsapp" class="w-4 h-4" /> {{ __('Chat with us on WhatsApp') }}</x-success-button>
                </a>
            @else
                <p class="text-sm text-gray-500">{{ __('Email support@zwenko.com') }}</p>
            @endif
        </x-card>
    </div>
</x-app-layout>
