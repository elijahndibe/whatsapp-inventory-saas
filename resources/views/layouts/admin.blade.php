<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Platform Admin — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen">
            <nav class="bg-gray-900 text-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <div class="flex items-center gap-8">
                            <span class="font-bold text-white tracking-wide">{{ __('Platform Admin') }}</span>
                            <div class="hidden sm:flex gap-6 text-sm">
                                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Dashboard') }}</a>
                                <a href="{{ route('admin.businesses.index') }}" class="{{ request()->routeIs('admin.businesses.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Businesses') }}</a>
                                <a href="{{ route('admin.monetization.index') }}" class="{{ request()->routeIs('admin.monetization.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Monetization') }}</a>
                                <a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Transactions') }}</a>
                                <a href="{{ route('admin.plans.index') }}" class="{{ request()->routeIs('admin.plans.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Plans') }}</a>
                                <a href="{{ route('admin.features.index') }}" class="{{ request()->routeIs('admin.features.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Features') }}</a>
                                <a href="{{ route('admin.subscriptions.index') }}" class="{{ request()->routeIs('admin.subscriptions.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Subscriptions') }}</a>
                                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Users') }}</a>
                                <a href="{{ route('admin.failed-jobs.index') }}" class="{{ request()->routeIs('admin.failed-jobs.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Failed Jobs') }}</a>
                                <a href="{{ route('admin.logs.index') }}" class="{{ request()->routeIs('admin.logs.*') ? 'text-white font-semibold' : 'text-gray-400 hover:text-white' }}">{{ __('Logs') }}</a>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <span class="text-gray-400">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="text-gray-400 hover:text-white">{{ __('Log Out') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
