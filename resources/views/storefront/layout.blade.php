<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $business->name }}</title>
        @isset($business->description)
            <meta name="description" content="{{ Str::limit($business->description, 160) }}">
        @endisset

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <a href="{{ route('storefront.show', $business) }}" class="flex items-center gap-2 min-w-0">
                    @if ($business->logo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($business->logo) }}" alt="" class="h-9 w-9 rounded-full object-cover shrink-0">
                    @else
                        <div class="h-9 w-9 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold shrink-0">
                            {{ Str::substr($business->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $business->name }}</span>
                </a>

                <a href="{{ route('storefront.cart.index', $business) }}" class="relative inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Cart') }}
                    @if ($cartCount > 0)
                        <span class="ml-1.5 inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-indigo-600 text-white text-xs">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </header>

        @if (session('status'))
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <main class="max-w-5xl mx-auto px-4 py-6">
            {{ $slot }}
        </main>

        <footer class="max-w-5xl mx-auto px-4 py-8 text-center text-xs text-gray-400 dark:text-gray-500">
            {{ __('Powered by') }} {{ config('app.name') }}
        </footer>
    </body>
</html>
