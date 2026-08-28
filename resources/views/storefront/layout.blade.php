<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $business->name }} — {{ __('Powered by Zwenko') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @isset($business->description)
            <meta name="description" content="{{ Str::limit($business->description, 160) }}">
        @endisset

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface dark:bg-gray-900">
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
                <a href="{{ route('storefront.show', $business) }}" class="flex items-center gap-2.5 min-w-0">
                    @if ($business->logo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($business->logo) }}" alt="" class="h-10 w-10 rounded-full object-cover shrink-0 border border-gray-100 dark:border-gray-700">
                    @else
                        <div class="h-10 w-10 rounded-full bg-brand-700 text-white flex items-center justify-center font-semibold shrink-0">
                            {{ Str::substr($business->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="font-semibold text-ink dark:text-gray-100 truncate">{{ $business->name }}</span>
                </a>

                <a href="{{ route('storefront.cart.index', $business) }}" class="relative inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('Cart') }}</span>
                    @if ($cartCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-brand-700 text-white text-[11px] font-semibold">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </header>

        <div class="max-w-5xl mx-auto px-4">
            <div class="pt-4">
                <x-flash-messages />
            </div>
        </div>

        <main class="max-w-5xl mx-auto px-4 py-6">
            {{ $slot }}
        </main>

        <footer class="max-w-5xl mx-auto px-4 py-8 text-center text-xs text-gray-400 dark:text-gray-500 flex items-center justify-center gap-1.5">
            <x-zwenko-logo class="h-4 w-4" />
            {{ __('Powered by Zwenko') }}
        </footer>
    </body>
</html>
