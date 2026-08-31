<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('WhatsApp') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Turn conversations into orders.') }}</p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <x-flash-messages />

        <x-card class="!p-0 overflow-hidden">
            <div class="p-5 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 rounded-xl bg-whatsapp/10 text-whatsapp flex items-center justify-center">
                        <x-icon name="whatsapp" class="w-6 h-6" />
                    </span>
                    <div>
                        <p class="font-semibold text-ink dark:text-gray-100">{{ __('WhatsApp Ordering') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Included at no extra cost, for every store.') }}</p>
                    </div>
                </div>
                <x-badge variant="success">
                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                    {{ __('Active') }}
                </x-badge>
            </div>
        </x-card>

        <x-card>
            <h3 class="font-semibold text-ink dark:text-gray-100 mb-1">{{ __('Share your store') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('Send this link anywhere — customers can browse, add to cart, and order straight to your WhatsApp.') }}</p>

            @php $storeUrl = route('storefront.show', $business); @endphp

            <div class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1 min-w-0 px-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-600 dark:text-gray-300 font-mono truncate">
                    {{ $storeUrl }}
                </div>
                <div class="flex gap-2">
                    <x-outline-button type="button" onclick="navigator.clipboard.writeText('{{ $storeUrl }}'); this.querySelector('span').textContent = '{{ __('Copied!') }}'">
                        <x-icon name="copy" class="w-4 h-4" /> <span>{{ __('Copy link') }}</span>
                    </x-outline-button>
                    <a href="{{ $storeUrl }}" target="_blank" rel="noopener">
                        <x-secondary-button type="button">
                            <x-icon name="external-link" class="w-4 h-4" /> {{ __('Open store') }}
                        </x-secondary-button>
                    </a>
                </div>
            </div>
        </x-card>

        <x-card>
            <h3 class="font-semibold text-ink dark:text-gray-100 mb-1">{{ __('WhatsApp Connection') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                @if ($business->hasWhatsAppCloudApi())
                    {{ __('Connected — automated order and payment messages are enabled.') }}
                @else
                    {{ __('Connect your WhatsApp Business account to send automated order and payment updates.') }}
                @endif
            </p>
            <a href="{{ route('settings.edit') }}#whatsapp">
                <x-primary-button type="button">{{ $business->hasWhatsAppCloudApi() ? __('Manage connection') : __('Connect WhatsApp') }}</x-primary-button>
            </a>
        </x-card>

        <x-card class="bg-gray-50 dark:bg-gray-900/40 border-dashed">
            <div class="flex items-start gap-3">
                <x-icon name="info" class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Advanced WhatsApp Tools') }}</p>
                        <x-badge variant="neutral">{{ __('Coming soon') }}</x-badge>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ __('Broadcast messages, campaigns, and reusable message templates are on the way.') }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
