<x-guest-layout>
    <h1 class="text-xl font-semibold text-ink dark:text-gray-100 mb-1">{{ __('Create your store') }}</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('Free to start — no credit card required.') }}</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Business Name -->
        <div>
            <x-input-label for="business_name" :value="__('Business Name')" />
            <x-text-input id="business_name" class="block mt-1 w-full" type="text" name="business_name" :value="old('business_name')" required autofocus autocomplete="organization" />
            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
        </div>

        <!-- Owner Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Your Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- WhatsApp / Phone Number — verified via a WhatsApp code before the
             form can submit. Country/currency/timezone aren't collected
             here; a business sets those in Settings once it exists. -->
        <div class="mt-4">
            <x-geo-fields :phone="old('phone')" :show-locale="false" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Terms agreement -->
        <div class="mt-4">
            <label class="inline-flex items-start gap-2">
                <input type="checkbox" name="terms" value="1" @checked(old('terms'))
                       class="mt-0.5 rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500 dark:focus:ring-brand-600 dark:focus:ring-offset-gray-800" />
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('I agree to the') }}
                    <a href="{{ route('legal.terms') }}" target="_blank" class="text-brand-700 dark:text-brand-400 hover:underline">{{ __('Terms of Service') }}</a>
                    {{ __('and') }}
                    <a href="{{ route('legal.privacy') }}" target="_blank" class="text-brand-700 dark:text-brand-400 hover:underline">{{ __('Privacy Policy') }}</a>.
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center mt-6">
            {{ __('Create your store') }}
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" class="font-medium text-brand-700 dark:text-brand-400 hover:underline">{{ __('Sign in') }}</a>
    </p>
</x-guest-layout>
