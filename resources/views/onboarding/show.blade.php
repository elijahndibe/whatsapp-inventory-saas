<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Welcome to Zwenko, :name 👋', ['name' => $business->name]) }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __("A few quick steps and you're ready to start selling.") }}</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        @php
            $doneCount = 2 + ($hasProducts ? 1 : 0) + ($hasPaystack ? 1 : 0);
        @endphp

        {{-- Progress indicator — 5 steps as specced; "Create business" is
             already satisfied by having registered, "Start selling" is
             satisfied by finishing/skipping this page. --}}
        <div class="flex items-center gap-2">
            @foreach ([
                true,
                true,
                $hasProducts,
                $hasPaystack,
            ] as $stepDone)
                <span @class([
                    'h-1.5 flex-1 rounded-full',
                    'bg-brand-600' => $stepDone,
                    'bg-gray-200 dark:bg-gray-700' => ! $stepDone,
                ])></span>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 -mt-4">{{ __(':done of :total steps done', ['done' => $doneCount, 'total' => 4]) }}</p>

        <div class="space-y-3">
            {{-- Step 1: Create business — always complete by the time this page loads. --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-4 flex items-center gap-4">
                <span class="shrink-0 w-9 h-9 rounded-full bg-success-bg dark:bg-green-900/30 text-success flex items-center justify-center">
                    <x-icon name="check" class="w-5 h-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-ink dark:text-gray-100">{{ __('Create your business') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Done — :name is set up.', ['name' => $business->name]) }}</p>
                </div>
            </div>

            {{-- Step 2: Add first products. --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-4 flex items-center gap-4">
                <span @class([
                    'shrink-0 w-9 h-9 rounded-full flex items-center justify-center',
                    'bg-success-bg dark:bg-green-900/30 text-success' => $hasProducts,
                    'bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300' => ! $hasProducts,
                ])>
                    <x-icon :name="$hasProducts ? 'check' : 'products'" class="w-5 h-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-ink dark:text-gray-100">{{ __('Add your first products') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Give customers something to buy — you can always add more later.') }}</p>
                </div>
                <a href="{{ route('products.create') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-semibold {{ $hasProducts ? 'text-brand-700 dark:text-brand-400 hover:underline' : 'bg-brand-700 text-white hover:bg-brand-800' }}">
                    {{ $hasProducts ? __('Add more') : __('Add product') }}
                </a>
            </div>

            {{-- Step 3: Connect Paystack. --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-4 flex items-center gap-4">
                <span @class([
                    'shrink-0 w-9 h-9 rounded-full flex items-center justify-center',
                    'bg-success-bg dark:bg-green-900/30 text-success' => $hasPaystack,
                    'bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300' => ! $hasPaystack,
                ])>
                    <x-icon :name="$hasPaystack ? 'check' : 'payments'" class="w-5 h-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-ink dark:text-gray-100">{{ __('Connect Paystack') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $hasPaystack ? __('Connected — your share of every sale pays out automatically.') : __('Optional — until then, your share is tracked for manual payout.') }}
                    </p>
                </div>
                @unless ($hasPaystack)
                    <a href="{{ route('settings.edit') }}#payments" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-700 text-white rounded-md text-sm font-semibold hover:bg-brand-800">
                        {{ __('Connect') }}
                    </a>
                @endunless
            </div>

            {{-- Step 4: Share store. --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-4 flex items-center gap-4">
                <span class="shrink-0 w-9 h-9 rounded-full bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 flex items-center justify-center">
                    <x-icon name="share" class="w-5 h-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-ink dark:text-gray-100">{{ __('Share your store') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Send your store link to customers on WhatsApp, Instagram, anywhere.') }}</p>
                </div>
                <a href="{{ route('whatsapp.index') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-700 text-white rounded-md text-sm font-semibold hover:bg-brand-800">
                    {{ __('Share') }}
                </a>
            </div>
        </div>

        {{-- Step 5: Start selling — "Skip" and "Finish" are the same action;
             this checklist is never a hard gate. A business can always come
             back and pick up loose ends from the Dashboard, Products, or
             Settings screens directly. --}}
        <form method="POST" action="{{ route('onboarding.finish') }}" class="pt-2 flex items-center justify-between">
            @csrf
            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                {{ __("I'll finish this later") }}
            </button>
            <x-primary-button type="submit">{{ __('Start selling') }} &rarr;</x-primary-button>
        </form>
    </div>
</x-app-layout>
