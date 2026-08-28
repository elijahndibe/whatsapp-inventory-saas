<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Zwenko') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <div class="hidden lg:flex flex-col justify-between bg-gray-900 p-10 text-white">
                <x-zwenko-wordmark variant="white" mark-class="h-9 w-9" text-class="text-2xl" />
                <div>
                    <p class="text-3xl font-semibold leading-tight">{{ __('Sell online.') }}<br>{{ __('Manage everything.') }}</p>
                    <p class="mt-4 text-gray-400 max-w-sm">{{ __('Storefront, inventory, orders, customers and WhatsApp ordering — all in one place, built for small businesses.') }}</p>
                </div>
                <p class="text-xs text-gray-500">&copy; {{ date('Y') }} Zwenko. {{ __('All rights reserved.') }}</p>
            </div>

            <div class="flex flex-col justify-center items-center px-6 py-12 bg-surface dark:bg-gray-900 min-h-screen lg:min-h-0">
                <div class="w-full max-w-sm">
                    <div class="lg:hidden flex justify-center mb-8">
                        <x-zwenko-wordmark />
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
