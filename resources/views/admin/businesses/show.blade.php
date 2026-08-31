<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ $business->name }}</h2>
            @if ($business->status === 'active')
                <form method="POST" action="{{ route('admin.businesses.suspend', $business) }}" onsubmit="return confirm('{{ __('Suspend this business?') }}')">
                    @csrf
                    <button class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs font-semibold uppercase">{{ __('Suspend') }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.businesses.activate', $business) }}">
                    @csrf
                    <button class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-semibold uppercase">{{ __('Activate') }}</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-4">
                <p class="text-xs text-gray-500 uppercase">{{ __('Products') }}</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['products'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-4">
                <p class="text-xs text-gray-500 uppercase">{{ __('Orders') }}</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['orders'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-4">
                <p class="text-xs text-gray-500 uppercase">{{ __('Customers') }}</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['customers'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6 text-sm text-gray-700 dark:text-gray-300 space-y-1">
            <div>{{ __('Slug') }}: <span class="font-mono">{{ $business->slug }}</span></div>
            <div>{{ __('Email') }}: {{ $business->email ?? '—' }}</div>
            <div>{{ __('Phone') }}: {{ $business->phone ?? '—' }}</div>
            <div>{{ __('Currency') }}: {{ $business->currency }}</div>
            <div>{{ __('Created') }}: {{ $business->created_at->format('d M Y') }}</div>
            <div>{{ __('Paystack') }}: {{ $business->hasPaystackSubaccount() ? __('Connected') : __('Not connected') }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ __('Commission Rate') }}</h3>
            <p class="text-xs mb-4">
                @if ($business->hasCustomCommissionRate())
                    <span class="text-brand-600 dark:text-brand-400 font-semibold">{{ __('Custom seller commission') }}: {{ $business->commission_rate }}%</span>
                @else
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Default platform commission') }}</span>
                @endif
            </p>
            <form method="POST" action="{{ route('admin.businesses.commission.update', $business) }}" class="flex items-end gap-3">
                @csrf
                <div>
                    <x-input-label for="commission_rate" :value="__('Custom Rate % (blank = use default)')" />
                    <x-text-input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" class="block mt-1 w-40" :value="$business->commission_rate" />
                </div>
                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">{{ __('Users') }}</div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($business->users as $user)
                    <li class="px-4 py-3 flex justify-between text-sm">
                        <span class="text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                        <span class="text-gray-500">{{ $user->email }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">{{ __('Subscription History') }}</div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($business->subscriptions as $subscription)
                    <li class="px-4 py-3 flex justify-between text-sm">
                        <span class="text-gray-900 dark:text-gray-100">{{ $subscription->plan?->name }}</span>
                        <span class="text-gray-500">{{ ucfirst($subscription->status) }}</span>
                        <span class="text-gray-400">{{ $subscription->starts_at->format('d M Y') }}</span>
                    </li>
                @empty
                    <li class="px-4 py-3 text-sm text-gray-400">{{ __('No subscription on record.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-admin-layout>
