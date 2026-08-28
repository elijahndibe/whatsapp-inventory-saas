<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Businesses') }}</h2>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name...') }}"
                   class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
            <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="suspended" @selected(request('status') === 'suspended')>{{ __('Suspended') }}</option>
            </select>
            <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm">{{ __('Filter') }}</button>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
            @if ($businesses->isEmpty())
                <x-empty-state icon="store" :title="__('No businesses found')" :description="__('Try a different search or status filter.')" />
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Business') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Plan') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Users') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($businesses as $business)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.businesses.show', $business) }}" class="font-medium text-brand-600 dark:text-brand-400 hover:underline">{{ $business->name }}</a>
                                    <div class="text-xs text-gray-400">{{ $business->slug }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $business->subscriptions->first()?->plan?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $business->users_count }}</td>
                                <td class="px-4 py-3">
                                    <x-badge :variant="$business->status === 'active' ? 'success' : 'danger'">{{ ucfirst($business->status) }}</x-badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($business->status === 'active')
                                        <form method="POST" action="{{ route('admin.businesses.suspend', $business) }}" onsubmit="return confirm('{{ __('Suspend this business?') }}')">
                                            @csrf
                                            <button class="text-red-600 dark:text-red-400 hover:underline text-xs">{{ __('Suspend') }}</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.businesses.activate', $business) }}">
                                            @csrf
                                            <button class="text-green-600 dark:text-green-400 hover:underline text-xs">{{ __('Activate') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{ $businesses->links() }}
    </div>
</x-admin-layout>
