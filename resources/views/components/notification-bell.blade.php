@php
    $notifications = auth()->user()->notifications()->latest()->limit(10)->get();
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<x-dropdown align="right" width="80">
    <x-slot name="trigger">
        <button class="relative inline-flex items-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            @if ($unreadCount > 0)
                <span class="absolute top-1 right-1 inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-red-600 text-white text-[10px]">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-600">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Notifications') }}</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="text-xs text-brand-700 dark:text-brand-400 hover:underline">{{ __('Mark all read') }}</button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('No notifications yet.') }}</div>
        @else
            <div class="max-h-80 overflow-y-auto">
                @foreach ($notifications as $notification)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" @class([
                            'w-full text-left px-4 py-3 text-sm border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-600',
                            'bg-brand-50/50 dark:bg-brand-900/10' => is_null($notification->read_at),
                        ])>
                            <div class="text-gray-700 dark:text-gray-200">{{ $notification->data['message'] ?? 'Notification' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</div>
                        </button>
                    </form>
                @endforeach
            </div>
        @endif
    </x-slot>
</x-dropdown>
