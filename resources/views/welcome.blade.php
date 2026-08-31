<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zwenko — {{ __('Sell online. Manage everything.') }}</title>
    <meta name="description" content="Zwenko turns your WhatsApp sales into a real online business — a storefront, inventory, orders, customers and secure payments, all in one place. Free to start, no monthly fees.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-ink">

    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 shrink-0">
                <x-zwenko-logo class="h-8 w-8" />
                <span class="font-semibold tracking-tight text-xl text-ink">Zwenko</span>
            </a>
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#how-it-works" class="hover:text-ink">{{ __('How it works') }}</a>
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

    {{-- Hero: names the exact, specific pain (the DM "is this still
         available?" cycle) rather than a generic value prop, since that's
         the thing every WhatsApp seller in this audience will recognize
         instantly. The mockup card + WhatsApp bubble tell the "chat →
         real store" story visually without needing invented testimonials. --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 to-white">
        <div class="pointer-events-none absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-100/50 blur-3xl"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center relative">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white text-brand-700 text-xs font-medium border border-brand-100 shadow-sm">
                    <x-icon name="whatsapp" class="w-3.5 h-3.5" /> {{ __('Built for WhatsApp sellers across Africa') }}
                </span>
                <h1 class="mt-5 text-4xl sm:text-5xl font-bold tracking-tight leading-[1.08]">
                    {{ __('Stop selling out of') }}<br>{{ __('your ') }}<span class="text-brand-700">{{ __('DMs.') }}</span>
                </h1>
                <p class="mt-5 text-lg text-gray-600 max-w-lg">
                    {{ __('Zwenko turns your WhatsApp sales into a real online store — a proper catalog, inventory that updates itself, and payments that land in your account. Customers still order the way they already do; everything behind it just stops being chaos.') }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('register') }}"><x-primary-button type="button" class="!px-6 !py-3.5 !text-base">{{ __('Start your store for free') }}</x-primary-button></a>
                    <a href="#how-it-works"><x-outline-button type="button" class="!px-6 !py-3.5 !text-base">{{ __('See how it works') }}</x-outline-button></a>
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500">
                    <span class="flex items-center gap-1.5"><x-icon name="check" class="w-4 h-4 text-success" /> {{ __('Free to start') }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="check" class="w-4 h-4 text-success" /> {{ __('No monthly fees, ever') }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="check" class="w-4 h-4 text-success" /> {{ __('Live in minutes') }}</span>
                </div>
            </div>

            <div class="relative">
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

                {{-- Floating WhatsApp chip — the visual thread tying the
                     "chat" origin story to the polished dashboard above it. --}}
                <div class="hidden sm:flex absolute -left-6 -bottom-6 items-center gap-2 bg-white rounded-xl shadow-xl border border-gray-100 pl-2 pr-4 py-2">
                    <span class="w-9 h-9 rounded-full bg-whatsapp flex items-center justify-center shrink-0">
                        <x-icon name="whatsapp" class="w-4 h-4 text-white" />
                    </span>
                    <div class="leading-tight">
                        <p class="text-[11px] text-gray-400">{{ __('New order via WhatsApp') }}</p>
                        <p class="text-xs font-medium text-ink">{{ __('"I\'ll take the blue one, size M"') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-gray-100 py-6 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-center gap-x-10 gap-y-3 text-sm text-gray-400">
            <span class="flex items-center gap-1.5 font-medium text-gray-500"><x-icon name="payments" class="w-4 h-4" /> {{ __('Secure payments') }}</span>
            <span class="flex items-center gap-1.5 font-medium text-gray-500"><x-icon name="whatsapp" class="w-4 h-4" /> {{ __('WhatsApp built-in') }}</span>
            <span>{{ __('Built for growing businesses across Africa') }}</span>
            <span>{{ __('Bank-level security') }}</span>
        </div>
    </section>

    {{-- Agitate → relieve, using the product's real functionality on both
         sides — no invented reviews or made-up numbers, just an honest
         before/after that any WhatsApp seller will recognize immediately. --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl font-bold tracking-tight">{{ __('If this is your Tuesday...') }}</h2>
            <p class="mt-3 text-gray-600">{{ __("You didn't start a business to become a full-time chat administrator.") }}</p>
        </div>
        <div class="grid md:grid-cols-2 gap-5">
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">{{ __('Selling out of your DMs') }}</p>
                <ul class="space-y-3.5 text-sm text-gray-600">
                    @foreach ([
                        'Scrolling back through chats to find who ordered what',
                        '"Is this still available?" — for the fifth time today',
                        'Typing your account number out again. And again.',
                        'Finding out you\'re out of stock after you\'ve already promised it',
                        'Customers going quiet right after you send your bank details',
                        'Looking like "just someone selling on WhatsApp"',
                    ] as $pain)
                        <li class="flex items-start gap-2.5"><x-icon name="x" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" /> {{ __($pain) }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl border border-brand-100 bg-brand-50/50 p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 mb-4">{{ __('Selling with Zwenko') }}</p>
                <ul class="space-y-3.5 text-sm text-ink">
                    @foreach ([
                        'Every order sitting in one organized list, always',
                        'A real catalog customers browse and order from themselves',
                        'One tap to send a secure payment link — no more copy-pasting',
                        'Stock that updates itself the moment something sells',
                        'A checkout customers actually trust, backed by real payment security',
                        'A storefront link that makes you look like the real business you are',
                    ] as $win)
                        <li class="flex items-start gap-2.5"><x-icon name="check" class="w-4 h-4 text-success shrink-0 mt-0.5" /> {{ __($win) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- How it works: reduces the "is this complicated?" fear up front,
         before the feature list even starts. --}}
    <section id="how-it-works" class="bg-gray-50 py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <h2 class="text-3xl font-bold tracking-tight">{{ __('Live in three steps, not three weeks') }}</h2>
                <p class="mt-3 text-gray-600">{{ __('No developer, no designer, no learning curve — if you can use WhatsApp, you can run this.') }}</p>
            </div>
            <div class="grid sm:grid-cols-3 gap-8 sm:gap-6">
                @foreach ([
                    ['n' => '1', 'title' => 'Create your store', 'desc' => 'Your business name and WhatsApp number. Under two minutes, no card required.'],
                    ['n' => '2', 'title' => 'Add what you\'re selling', 'desc' => 'Photos, prices and stock — organized into a real catalog, not a camera roll.'],
                    ['n' => '3', 'title' => 'Share your link', 'desc' => 'WhatsApp status, Instagram bio, your next chat. Customers browse, order and pay — you get notified instantly.'],
                ] as $step)
                    <div class="text-center sm:text-left">
                        <span class="inline-flex w-10 h-10 rounded-full bg-brand-700 text-white items-center justify-center font-semibold">{{ $step['n'] }}</span>
                        <h3 class="mt-4 font-semibold text-lg">{{ __($step['title']) }}</h3>
                        <p class="mt-1.5 text-sm text-gray-600">{{ __($step['desc']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="features" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl font-bold tracking-tight">{{ __('Everything a real store needs — nothing it doesn\'t') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('Built around how you actually sell, not a generic e-commerce checklist.') }}</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ([
                ['icon' => 'whatsapp', 'color' => 'success', 'title' => 'WhatsApp ordering', 'desc' => 'Customers order in one tap, no app to download. You confirm and request payment right inside the chat.'],
                ['icon' => 'store', 'color' => 'brand', 'title' => 'A real storefront', 'desc' => 'One clean, shareable link with your products, prices and photos — built to be sent, not scrolled through.'],
                ['icon' => 'inventory', 'color' => 'info', 'title' => 'Inventory that updates itself', 'desc' => 'Stock drops automatically with every sale, across as many locations as you sell from.'],
                ['icon' => 'payments', 'color' => 'warning', 'title' => 'Secure payments', 'desc' => 'Cards, bank transfer and USSD, handled securely — money lands in your account, not your inbox.'],
                ['icon' => 'customers', 'color' => 'brand', 'title' => 'Customers, remembered', 'desc' => 'Every buyer and their order history saved automatically — no more scrolling to find who ordered what.'],
                ['icon' => 'tag', 'color' => 'success', 'title' => 'Coupon codes', 'desc' => 'Launch a promo, reward repeat customers, or run a limited-time discount in minutes.'],
                ['icon' => 'reports', 'color' => 'info', 'title' => 'Real sales reports', 'desc' => 'See what\'s actually selling and what isn\'t — not just what you think is happening.'],
                ['icon' => 'staff', 'color' => 'warning', 'title' => 'Team accounts', 'desc' => 'Bring on staff with exactly the access they need to help run things — nothing more.'],
            ] as $feature)
                @php
                    $palette = [
                        'brand' => 'bg-brand-50 text-brand-700',
                        'success' => 'bg-green-50 text-success-strong',
                        'info' => 'bg-blue-50 text-info',
                        'warning' => 'bg-amber-50 text-warning-strong',
                    ][$feature['color']];
                @endphp
                <div class="rounded-2xl border border-gray-100 p-5 hover:shadow-card transition">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center {{ $palette }}"><x-icon :name="$feature['icon']" class="w-5 h-5" /></span>
                    <p class="mt-3 font-semibold">{{ __($feature['title']) }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __($feature['desc']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="pricing" class="bg-gray-50 py-20">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-semibold uppercase tracking-wide">{{ __('Simple pricing') }}</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold tracking-tight">{{ __('Pay when you sell.') }}<br><span class="text-brand-700">{{ __('Nothing when you don\'t.') }}</span></h2>
            <p class="mt-3 text-gray-600">{{ __('No monthly subscription, no setup cost, no card required to start. Just a small commission on sales that actually happen.') }}</p>

            <div class="mt-10 bg-white rounded-2xl border border-gray-100 shadow-card p-8 text-left">
                <p class="text-sm font-medium text-gray-500">{{ __('Commission') }}</p>
                <p class="mt-1 text-5xl font-bold text-brand-700">1.5%</p>
                <p class="text-sm text-gray-500">{{ __('on every successful sale — that\'s it') }}</p>
                <ul class="mt-6 space-y-2.5 text-sm text-gray-600">
                    @foreach (['No monthly fees, ever', 'No setup or hidden costs', 'Full storefront & product catalog', 'WhatsApp ordering, always free', 'Secure card, bank & USSD payments', 'Orders, customers, inventory & reports'] as $item)
                        <li class="flex items-center gap-2"><x-icon name="check" class="w-4 h-4 text-success shrink-0" /> {{ __($item) }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="block mt-8">
                    <x-primary-button type="button" class="w-full justify-center !py-3.5">{{ __('Get started for free') }}</x-primary-button>
                </a>
            </div>
        </div>
    </section>

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold tracking-tight">{{ __('Questions before you start') }}</h2>
        </div>
        <div class="divide-y divide-gray-100 border-t border-b border-gray-100">
            @foreach ([
                ['q' => 'Do I need a website already?', 'a' => 'No. Zwenko gives you a complete storefront out of the box — there\'s nothing to build, design or host yourself.'],
                ['q' => 'Is my money safe?', 'a' => 'Yes. Payments are processed through a secure, bank-grade payment gateway — your customers\' card details never touch Zwenko\'s servers, and your share of every sale goes straight to your own bank account.'],
                ['q' => 'Do I need to be techy to use this?', 'a' => 'If you can use WhatsApp, you can use Zwenko. Most sellers are fully set up and sharing their store link within minutes of signing up.'],
                ['q' => 'What if I don\'t sell anything this month?', 'a' => 'Then you pay nothing. There\'s no subscription to cancel or forget about — Zwenko only ever earns from a small commission on sales that actually happen.'],
                ['q' => 'Do my customers have to change how they order?', 'a' => 'Not at all. WhatsApp ordering works exactly the way your customers already expect — Zwenko just organizes everything that happens behind the scenes.'],
            ] as $faq)
                <details class="group py-5">
                    <summary class="flex items-center justify-between gap-4 cursor-pointer font-medium text-ink">
                        {{ __($faq['q']) }}
                        <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 shrink-0 group-open:rotate-180 transition" />
                    </summary>
                    <p class="mt-2.5 text-sm text-gray-600 leading-relaxed">{{ __($faq['a']) }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <section class="bg-gray-900 py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-white">{{ __('Ready to run a real business, not just a chat thread?') }}</h2>
            <p class="mt-2 text-gray-400">{{ __('Free to start. No card required, no contract, no monthly fees.') }}</p>
            <a href="{{ route('register') }}" class="inline-block mt-6">
                <x-primary-button type="button" class="!px-8 !py-3.5 !text-base">{{ __('Start your store for free') }}</x-primary-button>
            </a>
        </div>
    </section>

    <footer class="py-10 border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2">
                <x-zwenko-logo class="h-6 w-6" />
                <span class="font-semibold tracking-tight text-base text-ink">Zwenko</span>
            </a>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <a href="{{ route('legal.terms') }}" class="hover:text-gray-600">{{ __('Terms') }}</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-gray-600">{{ __('Privacy') }}</a>
                <span>&copy; {{ date('Y') }} Zwenko. {{ __('All rights reserved.') }}</span>
            </div>
        </div>
    </footer>

</body>
</html>
