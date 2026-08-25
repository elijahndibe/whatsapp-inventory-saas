<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ __('Users') }}</h2>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or email...') }}"
                   class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm w-full max-w-xs" />
            <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm">{{ __('Search') }}</button>
        </form>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
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
                                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">ADMIN</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->business?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ ucfirst($user->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $users->links() }}
    </div>
</x-admin-layout>
