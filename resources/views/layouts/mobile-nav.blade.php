{{-- Mobile: top bar (brand + notifications + menu) and a bottom tab bar
     for the actions a seller reaches for most. The "More" tab opens the
     full nav as a slide-over rather than trying to cram everything into
     five tabs. --}}
<header x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="lg:hidden sticky top-0 z-40 bg-gray-900">
    <div class="flex items-center justify-between h-14 px-4">
        <x-zwenko-wordmark variant="white" text-class="text-lg" mark-class="h-7 w-7" />
        <div class="flex items-center gap-1">
            <span class="text-gray-300"><x-notification-bell /></span>
            <button @click="menuOpen = true" class="p-2 rounded-md text-gray-300 hover:bg-white/10" aria-label="{{ __('Open menu') }}">
                <x-icon name="menu" class="w-6 h-6" />
            </button>
        </div>
    </div>

    <div x-show="menuOpen" x-cloak class="fixed inset-0 z-50" x-transition.opacity role="dialog" aria-modal="true" :aria-hidden="!menuOpen">
        <div class="absolute inset-0 bg-black/50" @click="menuOpen = false"></div>
        <div class="absolute inset-y-0 right-0 w-72 bg-gray-900 shadow-xl flex flex-col"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="shrink-0 flex items-center justify-between px-4 py-4 border-b border-white/10">
                <x-zwenko-wordmark variant="white" />
                <button @click="menuOpen = false" class="p-1 text-gray-300" aria-label="{{ __('Close menu') }}"><x-icon name="x" class="w-5 h-5" /></button>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto p-3">
                @include('layouts.sidebar-nav-items')
            </div>

            {{-- Same account section desktop shows behind its dropdown —
                 there's no need for a click-to-reveal trigger here since the
                 slide-over already has the room to show Profile/Log Out
                 directly. --}}
            <div class="shrink-0 px-3 pb-4 pt-3 border-t border-white/10">
                <div class="flex items-center gap-2.5 px-2 py-2">
                    <span class="w-8 h-8 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                        {{ strtoupper(substr(Auth::user()->business->name ?? Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0 text-left">
                        <span class="block text-sm font-medium text-white truncate">{{ Auth::user()->business->name ?? Auth::user()->name }}</span>
                        <span class="block text-xs text-gray-400 truncate">{{ Auth::user()->name }}</span>
                    </span>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition">
                    <x-icon name="user" class="w-5 h-5" /> {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition">
                        <x-icon name="logout" class="w-5 h-5" /> {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 pb-[env(safe-area-inset-bottom)]">
    <div class="grid grid-cols-5">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-0.5 py-2 {{ request()->routeIs('dashboard') ? 'text-brand-700 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-icon name="home" class="w-5 h-5" />
            <span class="text-[11px] font-medium">{{ __('Home') }}</span>
        </a>
        @can('view orders')
            <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center gap-0.5 py-2 {{ request()->routeIs('orders.*') ? 'text-brand-700 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400' }}">
                <x-icon name="orders" class="w-5 h-5" />
                <span class="text-[11px] font-medium">{{ __('Orders') }}</span>
            </a>
        @endcan
        @can('view products')
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center gap-0.5 py-2 {{ request()->routeIs('products.*') ? 'text-brand-700 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400' }}">
                <x-icon name="products" class="w-5 h-5" />
                <span class="text-[11px] font-medium">{{ __('Products') }}</span>
            </a>
        @endcan
        @can('manage settings')
            <a href="{{ route('whatsapp.index') }}" class="flex flex-col items-center justify-center gap-0.5 py-2 {{ request()->routeIs('whatsapp.*') ? 'text-brand-700 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400' }}">
                <x-icon name="whatsapp" class="w-5 h-5" />
                <span class="text-[11px] font-medium">{{ __('WhatsApp') }}</span>
            </a>
        @endcan
        <a href="{{ route('settings.edit') }}" class="flex flex-col items-center justify-center gap-0.5 py-2 {{ request()->routeIs('settings.*') ? 'text-brand-700 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-icon name="menu" class="w-5 h-5" />
            <span class="text-[11px] font-medium">{{ __('More') }}</span>
        </a>
    </div>
</nav>
