<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Staff') }}</h2>
            <a href="{{ route('staff.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                {{ __('Add Staff') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300 break-words">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($staff as $user)
                        <li class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                @if ($user->locations->isNotEmpty())
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $user->locations->pluck('name')->join(', ') }}</div>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-300">
                                    {{ $user->roles->pluck('name')->first() ?? '—' }}
                                </span>
                                <span @class([
                                    'inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $user->status === 'active',
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => $user->status !== 'active',
                                ])>{{ ucfirst($user->status) }}</span>
                                @unless ($user->hasRole('Owner'))
                                    <a href="{{ route('staff.edit', $user) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
                                @endunless
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
