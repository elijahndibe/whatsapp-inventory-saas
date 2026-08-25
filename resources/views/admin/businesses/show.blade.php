<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ $business->name }}</h2>
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
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">{{ __('Products') }}</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['products'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">{{ __('Orders') }}</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['orders'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">{{ __('Customers') }}</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['customers'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-sm text-gray-700 dark:text-gray-300 space-y-1">
            <div>{{ __('Slug') }}: <span class="font-mono">{{ $business->slug }}</span></div>
            <div>{{ __('Email') }}: {{ $business->email ?? '—' }}</div>
            <div>{{ __('Phone') }}: {{ $business->phone ?? '—' }}</div>
            <div>{{ __('Currency') }}: {{ $business->currency }}</div>
            <div>{{ __('Created') }}: {{ $business->created_at->format('d M Y') }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
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

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
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
