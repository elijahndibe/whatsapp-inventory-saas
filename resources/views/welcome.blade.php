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
         instantly. The phone mockup + WhatsApp bubble tell the "chat →
         real store" story visually without needing invented testimonials.
         Phone frame, not a hand illustration — a hand-drawn/hand-coded
         hand risks looking amateurish with no image-generation tool or
         photo asset to work from; a clean device frame is both lower-risk
         and the more common pattern on real SaaS marketing sites anyway. --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-brand-50/20 to-white">
        <div class="pointer-events-none absolute inset-0 [background-image:radial-gradient(circle,theme(colors.brand.200)_1px,transparent_1px)] [background-size:28px_28px] opacity-[0.35] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,black_40%,transparent_100%)]"></div>
        <div class="pointer-events-none absolute -top-32 -right-32 w-[28rem] h-[28rem] rounded-full bg-brand-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute top-40 -left-32 w-80 h-80 rounded-full bg-purple-100/50 blur-3xl"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 grid lg:grid-cols-[1.15fr_1fr] gap-12 lg:gap-10 items-center relative">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white text-brand-700 text-xs font-medium border border-brand-100 shadow-sm">
                    <x-icon name="whatsapp" class="w-3.5 h-3.5" /> {{ __('Built for WhatsApp sellers across Africa') }}
                </span>
                <h1 class="mt-5 text-4xl sm:text-5xl lg:text-[3.25rem] font-bold tracking-tight leading-[1.08]">
                    {{ __('Stop selling out of') }}<br>{{ __('your ') }}<span class="text-brand-700">{{ __('DMs.') }}</span>
                </h1>
                <p class="mt-5 text-lg text-gray-600 max-w-lg">
                    {{ __('Zwenko turns your WhatsApp sales into a real online business — with your own storefront, organized orders, live inventory and secure payments.') }}
                    {{ __('Customers still order right where they already message you; everything behind it just stops being chaos.') }}
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

            <div class="relative flex justify-center lg:justify-end">
                {{-- Soft glow seated behind the photo for depth. The photo
                     already carries its own "New order" / "Payment
                     received" / "Low stock alert" toast cards, so no
                     separate floating chip is layered on top of it here —
                     that would just duplicate what's already in the shot. --}}
                <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-gradient-to-br from-brand-300/30 to-purple-300/20 blur-3xl"></div>
                <img src="{{ asset('images/marketing/dashboard-phone-mockup.webp') }}"
                     alt="{{ __('The Zwenko dashboard shown on a phone — today\'s sales, orders, a sales chart, and WhatsApp order notifications') }}"
                     class="relative w-full max-w-[380px] lg:max-w-[440px] h-auto" width="1199" height="1312" />
            </div>
        </div>
    </section>

    <section class="border-y border-gray-100 py-6 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-center gap-x-10 gap-y-3 text-sm text-gray-400">
            <span class="flex items-center gap-1.5 font-medium text-gray-500"><x-icon name="payments" class="w-4 h-4" /> {{ __('Secure payments') }}</span>
            <span class="flex items-center gap-1.5 font-medium text-gray-500"><x-icon name="whatsapp" class="w-4 h-4" /> {{ __('WhatsApp ordering') }}</span>
            <span>{{ __('Built for growing businesses across Africa') }}</span>
            <span>{{ __('Secure online payments') }}</span>
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
                        'Stock that updates itself the moment you confirm an order',
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

    {{-- The mechanism: this is Zwenko's actual differentiator (WhatsApp
         ordering wired straight into a real payment + inventory system),
         so it gets its own section rather than being buried in a single
         feature-grid card. Deliberately says "secure payment link", never
         the underlying processor by name — matches how every seller-facing
         screen in the app itself already talks about payments. --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-3xl font-bold tracking-tight">{{ __('From WhatsApp message to paid order') }}</h2>
            <p class="mt-3 text-gray-600">{{ __('The part that actually makes Zwenko different from just having a WhatsApp number.') }}</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-4">
            @foreach ([
                ['icon' => 'whatsapp', 'title' => 'Customer orders', 'desc' => 'They find a product on your store and tap "Order via WhatsApp" — no app to download.'],
                ['icon' => 'orders', 'title' => 'It lands in your dashboard', 'desc' => 'The order appears in Zwenko automatically — nothing for you to copy down by hand.'],
                ['icon' => 'payments', 'title' => 'You send a payment link', 'desc' => 'Confirm the order and Zwenko generates a secure payment link in one tap, ready to send in the same chat.'],
                ['icon' => 'check-circle', 'title' => 'You get paid', 'desc' => 'Once they pay, Zwenko verifies it, marks the order paid, and updates your stock — automatically.'],
            ] as $i => $step)
                <div class="relative">
                    <div class="rounded-2xl border border-gray-100 p-5 h-full">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center shrink-0"><x-icon :name="$step['icon']" class="w-4 h-4" /></span>
                            <span class="text-xs font-semibold text-gray-300">0{{ $i + 1 }}</span>
                        </div>
                        <p class="mt-3 font-semibold">{{ __($step['title']) }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __($step['desc']) }}</p>
                    </div>
                    @if (! $loop->last)
                        <x-icon name="chevron-right" class="hidden lg:block absolute top-1/2 -right-6 -translate-y-1/2 w-4 h-4 text-gray-300" />
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Shows the customer's side, not just the seller's dashboard — a CSS
         mockup matching the real storefront (not a captured screenshot),
         same treatment as the hero's dashboard mockup. --}}
    <section class="bg-gray-50 py-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ __('Give customers somewhere better to shop') }}</h2>
                <p class="mt-4 text-gray-600">{{ __('Share one link and your customers get a proper storefront — your products, your prices, your photos — where they can browse, order via WhatsApp, or pay online. Not a chat history they have to scroll through.') }}</p>
                <a href="{{ route('register') }}" class="inline-block mt-6">
                    <x-outline-button type="button">{{ __('Start your store for free') }}</x-outline-button>
                </a>
            </div>
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-br from-brand-700 via-brand-800 to-gray-900 p-5">
                    <div class="h-9 w-9 rounded-full bg-white/15 border border-white/30"></div>
                    <p class="mt-3 text-white font-semibold">{{ __('Bella Fashion') }}</p>
                    <p class="text-white/70 text-xs">{{ __('Lagos, Nigeria') }}</p>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    {{-- Illustrated tiles, not empty placeholders — no real
                         product photos or image-generation tool available,
                         so a large emoji on a soft gradient stands in for
                         a photo without pretending to be one. --}}
                    @foreach ([
                        ['n' => 'Ankara Maxi Dress', 'p' => '₦15,000', 'e' => '👗', 'from' => 'from-rose-100', 'to' => 'to-orange-100'],
                        ['n' => 'Leather Handbag', 'p' => '₦22,000', 'e' => '👜', 'from' => 'from-amber-100', 'to' => 'to-yellow-100'],
                        ['n' => 'Beaded Sandals', 'p' => '₦8,500', 'e' => '👡', 'from' => 'from-teal-100', 'to' => 'to-cyan-100'],
                        ['n' => 'Silk Headwrap', 'p' => '₦4,000', 'e' => '🧣', 'from' => 'from-purple-100', 'to' => 'to-pink-100'],
                    ] as $product)
                        <div class="rounded-lg border border-gray-100 overflow-hidden">
                            <div class="aspect-square bg-gradient-to-br {{ $product['from'] }} {{ $product['to'] }} flex items-center justify-center text-3xl">
                                {{ $product['e'] }}
                            </div>
                            <div class="p-2">
                                <p class="text-[11px] font-medium text-ink truncate">{{ $product['n'] }}</p>
                                <p class="text-[11px] text-gray-500">{{ $product['p'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Not a hard restriction — Zwenko is category-agnostic — but naming
         concrete categories helps a visitor place themselves ("this is for
         people like me") faster than an abstract capability list does. --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h2 class="text-3xl font-bold tracking-tight">{{ __('Made for businesses like yours') }}</h2>
        <p class="mt-3 text-gray-600 max-w-xl mx-auto">{{ __('Whatever you sell, if you\'re already selling it on WhatsApp, Zwenko can handle it.') }}</p>
        <div class="mt-10 flex flex-wrap justify-center gap-3">
            @foreach ([['e' => '👗', 't' => 'Fashion'], ['e' => '💄', 't' => 'Beauty'], ['e' => '📱', 't' => 'Electronics'], ['e' => '🍔', 't' => 'Food'], ['e' => '🏠', 't' => 'Home & Lifestyle'], ['e' => '🛍️', 't' => 'Retail']] as $cat)
                <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full border border-gray-200 text-sm font-medium text-gray-700 bg-white">
                    <span>{{ $cat['e'] }}</span> {{ __($cat['t']) }}
                </span>
            @endforeach
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
                ['icon' => 'inventory', 'color' => 'info', 'title' => 'Inventory that stays in sync', 'desc' => 'Stock updates automatically with every order you confirm — across as many locations as you sell from.'],
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
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold tracking-tight">{{ __('Pay when you sell.') }}<br><span class="text-brand-700">{{ __('No monthly subscription.') }}</span></h2>
            <p class="mt-3 text-gray-600">{{ __('Start free and pay a small Zwenko commission only when you receive a successful online payment. No setup cost, no card required to start, nothing to pay if you don\'t sell.') }}</p>

            <div class="mt-10 bg-white rounded-2xl border border-gray-100 shadow-card p-8 text-left">
                <p class="text-sm font-medium text-gray-500">{{ __('Zwenko commission') }}</p>
                <p class="mt-1 text-5xl font-bold text-brand-700">1.5%</p>
                <p class="text-sm text-gray-500">{{ __('on successful online sales — that\'s the only fee. We cover the rest.') }}</p>
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
