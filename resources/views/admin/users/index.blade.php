<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Users') }}</h2>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or email...') }}"
                   class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm w-full max-w-xs" />
            <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm">{{ __('Search') }}</button>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
            @if ($users->isEmpty())
                <x-empty-state icon="staff" :title="__('No users found')" :description="__('Try a different search.')" />
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Email') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Business') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Role') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    {{ $user->name }}
                                    @if ($user->is_super_admin)
                                        <x-badge variant="brand" class="ml-1">{{ __('Admin') }}</x-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->business?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                <td class="px-4 py-3"><x-badge :variant="$user->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($user->status) }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{ $users->links() }}
    </div>
</x-admin-layout>
