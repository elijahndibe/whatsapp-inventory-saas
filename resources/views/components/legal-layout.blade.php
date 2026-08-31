@props(['title'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Zwenko</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-ink">

    <header class="border-b border-gray-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('welcome') }}"><x-zwenko-wordmark /></a>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"><x-primary-button type="button">{{ __('Dashboard') }}</x-primary-button></a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-ink">{{ __('Log in') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">{{ $title }}</h1>
        <p class="mt-2 text-sm text-gray-500">{{ __('Last updated: :date', ['date' => $lastUpdated ?? now()->format('d F Y')]) }}</p>

        <div class="mt-10 space-y-8 text-sm sm:text-base text-gray-700 leading-relaxed">
            {{ $slot }}
        </div>
    </main>

    <footer class="py-10 border-t border-gray-100 mt-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <x-zwenko-wordmark markClass="h-6 w-6" textClass="text-base" />
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <a href="{{ route('legal.terms') }}" class="hover:text-gray-600">{{ __('Terms') }}</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-gray-600">{{ __('Privacy') }}</a>
                <span>&copy; {{ date('Y') }} Zwenko. {{ __('All rights reserved.') }}</span>
            </div>
        </div>
    </footer>

</body>
</html>
