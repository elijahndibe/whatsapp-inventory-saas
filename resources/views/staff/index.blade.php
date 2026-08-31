<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Staff') }}</h2>
            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800">
                <x-icon name="plus" class="w-4 h-4" />
                {{ __('Add Staff') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <x-flash-messages />

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                @if ($staff->isEmpty())
                    <x-empty-state icon="staff" :title="__('No staff members yet')" :description="__('Invite teammates to help manage orders, products and inventory.')">
                        <x-slot name="action">
                            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                                {{ __('Add staff') }}
                            </a>
                        </x-slot>
                    </x-empty-state>
                @else
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($staff as $user)
                        <li class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                @if ($user->locations->isNotEmpty())
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $user->locations->pluck('name')->join(', ') }}</div>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <x-badge variant="brand">{{ $user->roles->pluck('name')->first() ?? '—' }}</x-badge>
                                <x-badge :variant="$user->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($user->status) }}</x-badge>
                                @unless ($user->hasRole('Owner'))
                                    <a href="{{ route('staff.edit', $user) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
                                    @if (auth()->user()->hasRole('Owner') && $user->status === 'active')
                                        <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'transfer-ownership-{{ $user->id }}')"
                                                class="text-gray-500 dark:text-gray-400 hover:underline">{{ __('Make Owner') }}</button>

                                        <x-modal name="transfer-ownership-{{ $user->id }}" focusable>
                                            <form method="POST" action="{{ route('staff.transfer-ownership', $user) }}" class="p-6">
                                                @csrf
                                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                    {{ __('Transfer ownership to :name?', ['name' => $user->name]) }}
                                                </h2>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ __(':name will become the Owner of this business, with full access to staff, settings and payments. You\'ll be moved to Admin — you keep everything except those three.', ['name' => $user->name]) }}
                                                </p>

                                                <div class="mt-6">
                                                    <x-input-label for="transfer_password_{{ $user->id }}" :value="__('Confirm your password')" />
                                                    <x-text-input id="transfer_password_{{ $user->id }}" name="password" type="password" class="mt-1 block w-3/4" />
                                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                                </div>

                                                <div class="mt-6 flex justify-end gap-3">
                                                    <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                                    <x-primary-button>{{ __('Transfer Ownership') }}</x-primary-button>
                                                </div>
                                            </form>
                                        </x-modal>
                                    @endif
                                @endunless
                            </div>
                        </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
