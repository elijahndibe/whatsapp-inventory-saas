<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Platform Admin') }} — Zwenko</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface dark:bg-gray-900 text-ink dark:text-gray-100">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-md focus:bg-brand-700 focus:text-white focus:text-sm focus:font-medium">
            {{ __('Skip to content') }}
        </a>

        @php
            $adminNav = [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'home'],
                ['label' => 'Businesses', 'route' => 'admin.businesses.index', 'match' => 'admin.businesses.*', 'icon' => 'store'],
                ['label' => 'Transactions', 'route' => 'admin.transactions.index', 'match' => 'admin.transactions.*', 'icon' => 'payments'],
                ['label' => 'Monetization', 'route' => 'admin.monetization.index', 'match' => 'admin.monetization.*', 'icon' => 'trending-up'],
                ['label' => 'Features', 'route' => 'admin.features.index', 'match' => 'admin.features.*', 'icon' => 'categories'],
                ['label' => 'Plans', 'route' => 'admin.plans.index', 'match' => 'admin.plans.*', 'icon' => 'box'],
                ['label' => 'Subscriptions', 'route' => 'admin.subscriptions.index', 'match' => 'admin.subscriptions.*', 'icon' => 'orders'],
                ['label' => 'Users', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'customers'],
                ['label' => 'Failed Jobs', 'route' => 'admin.failed-jobs.index', 'match' => 'admin.failed-jobs.*', 'icon' => 'alert-triangle'],
                ['label' => 'Logs', 'route' => 'admin.logs.index', 'match' => 'admin.logs.*', 'icon' => 'reports'],
            ];
        @endphp

        <div class="lg:flex min-h-screen">
            <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:shrink-0 bg-gray-900 h-screen sticky top-0">
                <div class="shrink-0 px-4 py-5 flex items-center justify-between">
                    <x-zwenko-wordmark variant="white" />
                </div>
                <p class="shrink-0 px-7 text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-2">{{ __('Platform Admin') }}</p>
                {{-- min-h-0 is what actually lets this scroll — flex-1 alone
                     defaults to min-height:auto, which stretches to fit
                     every nav link instead of respecting h-screen. --}}
                <nav class="flex-1 min-h-0 px-3 space-y-1 overflow-y-auto pb-4">
                    @foreach ($adminNav as $item)
                        <x-sidebar-link :href="route($item['route'])" :active="request()->routeIs($item['match'])" :icon="$item['icon']">
                            {{ __($item['label']) }}
                        </x-sidebar-link>
                    @endforeach
                </nav>
                <div class="shrink-0 px-3 pb-4 pt-3 border-t border-white/10">
                    <div class="flex items-center gap-2.5 px-2 py-2">
                        <span class="w-8 h-8 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-semibold shrink-0">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        <span class="min-w-0 text-left">
                            <span class="block text-sm font-medium text-white truncate">{{ Auth::user()->name }}</span>
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
            </aside>

            <div class="flex-1 min-w-0">
                <header x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="lg:hidden sticky top-0 z-40 bg-gray-900">
                    <div class="flex items-center justify-between h-14 px-4">
                        <x-zwenko-wordmark variant="white" text-class="text-lg" mark-class="h-7 w-7" />
                        <button @click="menuOpen = true" class="p-2 rounded-md text-gray-300 hover:bg-white/10" aria-label="{{ __('Open menu') }}"><x-icon name="menu" class="w-6 h-6" /></button>
                    </div>
                    <div x-show="menuOpen" x-cloak class="fixed inset-0 z-50" x-transition.opacity role="dialog" aria-modal="true" :aria-hidden="!menuOpen">
                        <div class="absolute inset-0 bg-black/50" @click="menuOpen = false"></div>
                        <div class="absolute inset-y-0 right-0 w-72 bg-gray-900 shadow-xl flex flex-col"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
                            <div class="shrink-0 flex items-center justify-between px-4 py-4 border-b border-white/10">
                                <x-zwenko-wordmark variant="white" />
                                <button @click="menuOpen = false" class="p-1 text-gray-300" aria-label="{{ __('Close menu') }}"><x-icon name="x" class="w-5 h-5" /></button>
                            </div>

                            <div class="flex-1 min-h-0 overflow-y-auto p-3 space-y-1">
                                @foreach ($adminNav as $item)
                                    <x-sidebar-link :href="route($item['route'])" :active="request()->routeIs($item['match'])" :icon="$item['icon']">
                                        {{ __($item['label']) }}
                                    </x-sidebar-link>
                                @endforeach
                            </div>

                            <div class="shrink-0 px-3 pb-4 pt-3 border-t border-white/10">
                                <div class="flex items-center gap-2.5 px-2 py-2">
                                    <span class="w-8 h-8 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-semibold shrink-0">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                    <span class="min-w-0 text-left">
                                        <span class="block text-sm font-medium text-white truncate">{{ Auth::user()->name }}</span>
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

                @isset($header)
                    <header class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main id="main-content" tabindex="-1" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <x-flash-messages />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
