{{-- Desktop sidebar — see resources/views/layouts/mobile-nav.blade.php for
     the small-screen bottom bar equivalent. Link list itself lives in
     sidebar-nav-items.blade.php, shared with the mobile slide-over. --}}
<aside class="hidden lg:flex lg:flex-col lg:w-64 lg:shrink-0 bg-gray-900 min-h-screen sticky top-0">
    <div class="px-4 py-5">
        <x-zwenko-wordmark variant="white" />
    </div>

    <nav class="flex-1 px-3 overflow-y-auto pb-4">
        @include('layouts.sidebar-nav-items')
    </nav>

    <div class="px-3 pb-4 pt-3 border-t border-white/10">
        <x-dropdown align="top" width="56">
            <x-slot name="trigger">
                <button class="w-full flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-white/10 transition">
                    <span class="w-8 h-8 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                        {{ strtoupper(substr(Auth::user()->business->name ?? Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0 text-left">
                        <span class="block text-sm font-medium text-white truncate">{{ Auth::user()->business->name ?? Auth::user()->name }}</span>
                        <span class="block text-xs text-gray-400 truncate">{{ Auth::user()->name }}</span>
                    </span>
                    <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 ml-auto shrink-0" />
                </button>
            </x-slot>
            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</aside>
