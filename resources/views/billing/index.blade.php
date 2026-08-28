<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Billing & Plan') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
            @endif

            @unless ($subscriptionSystemEnabled)
                <div class="rounded-md bg-brand-50 dark:bg-brand-900/30 px-4 py-3 text-sm text-brand-700 dark:text-brand-300">
                    {{ __('Subscriptions are not required right now — your account already has full access to every feature listed below, free of charge. The platform earns a small commission on successful sales instead.') }}
                </div>
            @endunless

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Current Plan') }}</h3>
                @if ($currentPlan)
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentPlan->name }}</p>
                    @if ($subscription && $subscription->ends_at)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Renews or expires') }}: {{ $subscription->ends_at->format('d M Y') }}</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Does not expire') }}</p>
                    @endif
                @else
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No plan on record — currently unrestricted.') }}</p>
                @endif

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Products') }}</div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $usage['products'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Orders this month') }}</div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $usage['orders_this_month'] }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($plans as $plan)
                    <div @class([
                        'bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6 border-2',
                        'border-brand-500' => $currentPlan?->id === $plan->id,
                        'border-transparent' => $currentPlan?->id !== $plan->id,
                    ])>
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $plan->name }}</h4>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            @if ($plan->isFree())
                                {{ __('Free') }}
                            @else
                                {{ $plan->currencySymbol() }}{{ number_format($plan->price, 0) }}<span class="text-sm font-normal text-gray-500">/{{ $plan->duration_days }}d</span>
                            @endif
                        </p>

                        <ul class="mt-4 space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                            @foreach (($featuresByPlan[$plan->id] ?? collect()) as $planFeature)
                                @if ($planFeature->feature->type === 'limit')
                                    <li>{{ $planFeature->value ?? __('Unlimited') }} {{ __($planFeature->feature->name) }}</li>
                                @elseif ($planFeature->enabled)
                                    <li class="flex items-center gap-1.5 text-green-600 dark:text-green-400">
                                        <span>&check;</span> {{ __($planFeature->feature->name) }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        <div class="mt-6">
                            @if (! $subscriptionSystemEnabled)
                                <span class="block text-center text-xs font-semibold uppercase tracking-widest text-gray-400 py-2">{{ __('Included Free') }}</span>
                            @elseif ($currentPlan?->id === $plan->id)
                                <span class="block text-center text-xs font-semibold uppercase tracking-widest text-brand-600 dark:text-brand-400 py-2">{{ __('Current Plan') }}</span>
                            @else
                                <form method="POST" action="{{ route('billing.subscribe', $plan) }}">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2.5 bg-brand-700 text-white rounded-lg font-semibold text-sm hover:bg-brand-800">
                                        {{ $plan->isFree() ? __('Switch to Free') : __('Upgrade') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
