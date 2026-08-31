<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zwenko — {{ __('Sell online. Manage everything.') }}</title>
    <meta name="description" content="Zwenko helps small businesses turn their WhatsApp sales into a proper online business — storefront, inventory, orders, customers and payments in one place.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-ink">

    <header class="border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <x-zwenko-wordmark />
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#features" class="hover:text-ink">{{ __('Features') }}</a>
                <a href="#pricing" class="hover:text-ink">{{ __('Pricing') }}</a>
                <a href="{{ route('help.index') }}" class="hover:text-ink">{{ __('Resources') }}</a>
            </nav>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"><x-primary-button type="button">{{ __('Dashboard') }}</x-primary-button></a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-ink hidden sm:inline">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}"><x-primary-button type="button">{{ __('Start free') }}</x-primary-button></a>
                @endauth
            </div>
        </div>
    </header>

    <section class="bg-brand-50/60">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white text-brand-700 text-xs font-medium border border-brand-100">
                    <x-icon name="whatsapp" class="w-3.5 h-3.5" /> {{ __('Built for WhatsApp sellers') }}
                </span>
                <h1 class="mt-5 text-4xl sm:text-5xl font-bold tracking-tight leading-[1.1]">
                    {{ __('Sell on WhatsApp.') }}<br>{{ __('Run your business') }} <span class="text-brand-700">{{ __('like a pro.') }}</span>
                </h1>
                <p class="mt-5 text-lg text-gray-600 max-w-lg">
                    {{ __('Zwenko helps small businesses turn their WhatsApp sales into a proper online store with inventory, orders, payments and customers — all in one place.') }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('register') }}"><x-primary-button type="button" class="!px-6 !py-3.5 !text-base">{{ __('Start your store for free') }}</x-primary-button></a>
                    <a href="#features"><x-outline-button type="button" class="!px-6 !py-3.5 !text-base">{{ __('See how it works') }}</x-outline-button></a>
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500">
                    <span class="flex items-center gap-1.5"><x-icon name="check" class="w-4 h-4 text-success" /> {{ __('No monthly fees') }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="check" class="w-4 h-4 text-success" /> {{ __('1.5% on successful sales') }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="check" class="w-4 h-4 text-success" /> {{ __('Everything you need to grow') }}</span>
                </div>
            </div>

            <div class="bg-gray-900 rounded-2xl shadow-2xl p-3 lg:p-4">
                <div class="bg-white rounded-xl overflow-hidden">
                    <div class="bg-gray-900 px-4 py-3 flex items-center gap-2">
                        <x-zwenko-logo variant="white" class="h-6 w-6" />
                        <p class="text-white text-sm font-medium">{{ __('Good morning, Bella Fashion 👋') }}</p>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg border border-gray-100 p-3">
                                <p class="text-[10px] text-gray-500 uppercase">{{ __("Today's sales") }}</p>
                                <p class="text-lg font-semibold text-ink">₦185,000</p>
                                <p class="text-[11px] text-success-strong">↑ 12.5% {{ __('vs yesterday') }}</p>
                            </div>
                            <div class="rounded-lg border border-gray-100 p-3">
                                <p class="text-[10px] text-gray-500 uppercase">{{ __('Orders') }}</p>
                                <p class="text-lg font-semibold text-ink">24</p>
                                <p class="text-[11px] text-success-strong">↑ 8.2% {{ __('vs yesterday') }}</p>
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-100 p-3">
                            <p class="text-[11px] font-medium text-gray-600 mb-2">{{ __('Recent orders') }}</p>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between"><span class="text-gray-600">#ORD-1025 John Doe</span><span class="text-success-strong font-medium">{{ __('Paid') }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-600">#ORD-1024 Maryam Yusuf</span><span class="text-success-strong font-medium">{{ __('Paid') }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-600">#ORD-1023 James Okafor</span><span class="text-warning-strong font-medium">{{ __('Processing') }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-gray-100 py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-center gap-x-10 gap-y-3 text-sm text-gray-400">
            <span class="flex items-center gap-1.5 font-medium text-gray-500"><x-icon name="payments" class="w-4 h-4" /> {{ __('Secure Payments') }}</span>
            <span class="flex items-center gap-1.5 font-medium text-gray-500"><x-icon name="whatsapp" class="w-4 h-4" /> {{ __('WhatsApp') }}</span>
            <span>{{ __('Trusted by growing businesses across Africa') }}</span>
            <span>{{ __('Secured by bank-level security') }}</span>
        </div>
    </section>

    <section id="features" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl font-bold tracking-tight">{{ __('Everything you need to grow your business') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('Powerful features built for the way you actually sell.') }}</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ([
                ['icon' => 'whatsapp', 'title' => 'WhatsApp First', 'desc' => 'Let customers order instantly via WhatsApp.'],
                ['icon' => 'inventory', 'title' => 'Inventory Management', 'desc' => 'Track stock in real time and avoid running out.'],
                ['icon' => 'payments', 'title' => 'Secure Payments', 'desc' => 'Accept payments securely — cards, bank transfers & more.'],
                ['icon' => 'reports', 'title' => 'Sales Reports', 'desc' => 'Understand your business and make better decisions.'],
            ] as $feature)
                <div class="rounded-2xl border border-gray-100 p-5 hover:shadow-card transition">
                    <span class="w-10 h-10 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center"><x-icon :name="$feature['icon']" class="w-5 h-5" /></span>
                    <p class="mt-3 font-semibold">{{ __($feature['title']) }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __($feature['desc']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="pricing" class="bg-gray-50 py-20">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-semibold uppercase tracking-wide">{{ __('Simple pricing') }}</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold tracking-tight">{{ __('Pay when you sell.') }}<br><span class="text-brand-700">{{ __('No monthly fees.') }}</span></h2>
            <p class="mt-3 text-gray-600">{{ __('We only charge a small commission on successful sales. No hidden fees, no subscriptions required.') }}</p>

            <div class="mt-10 bg-white rounded-2xl border border-gray-100 shadow-card p-8 text-left">
                <p class="text-sm font-medium text-gray-500">{{ __('Commission') }}</p>
                <p class="mt-1 text-5xl font-bold text-brand-700">1.5%</p>
                <p class="text-sm text-gray-500">{{ __('on every successful sale') }}</p>
                <ul class="mt-6 space-y-2.5 text-sm text-gray-600">
                    @foreach (['No monthly fees', 'No setup fees', 'Online store & product catalogue', 'WhatsApp ordering (core, always free)', 'Secure online payments', 'Orders, customers & sales reports'] as $item)
                        <li class="flex items-center gap-2"><x-icon name="check" class="w-4 h-4 text-success shrink-0" /> {{ __($item) }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="block mt-8">
                    <x-primary-button type="button" class="w-full justify-center !py-3.5">{{ __('Get started for free') }}</x-primary-button>
                </a>
            </div>
        </div>
    </section>

    <section class="bg-gray-900 py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-white">{{ __('Ready to grow your business?') }}</h2>
            <p class="mt-2 text-gray-400">{{ __('Join business owners already selling smarter with Zwenko.') }}</p>
            <a href="{{ route('register') }}" class="inline-block mt-6">
                <x-primary-button type="button" class="!px-8 !py-3.5 !text-base">{{ __('Start your store for free') }}</x-primary-button>
            </a>
        </div>
    </section>

    <footer class="py-10 border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <x-zwenko-wordmark markClass="h-6 w-6" textClass="text-base" />
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} Zwenko. {{ __('All rights reserved.') }}</p>
        </div>
    </footer>

</body>
</html>
