<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Staff') }}</h2>
            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800">
                <x-icon name="plus" class="w-4 h-4" />
                {{ __('Add Staff') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <x-flash-messages />

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                @if ($staff->isEmpty())
                    <x-empty-state icon="staff" :title="__('No staff members yet')" :description="__('Invite teammates to help manage orders, products and inventory.')">
                        <x-slot name="action">
                            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                                {{ __('Add staff') }}
                            </a>
                        </x-slot>
                    </x-empty-state>
                @else
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
                                <x-badge variant="brand">{{ $user->roles->pluck('name')->first() ?? '—' }}</x-badge>
                                <x-badge :variant="$user->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($user->status) }}</x-badge>
                                @unless ($user->hasRole('Owner'))
                                    <a href="{{ route('staff.edit', $user) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
                                @endunless
                            </div>
                        </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
