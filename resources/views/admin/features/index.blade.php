<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Feature Management') }}</h2>
    </x-slot>

    <div class="space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Global toggles override plan access — a globally disabled feature is unavailable to everyone regardless of plan. Blank limit values mean unlimited.') }}
        </p>

        <form method="POST" action="{{ route('admin.features.update') }}">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Feature') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Global') }}</th>
                                @foreach ($plans as $plan)
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ $plan->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($features as $feature)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $feature->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $feature->key }} &middot; {{ $feature->type }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="features[{{ $feature->id }}][global_enabled]" value="1" @checked($feature->is_enabled)
                                               class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                                    </td>
                                    @foreach ($plans as $plan)
                                        @php $pf = ($planFeatures["{$plan->id}-{$feature->id}"] ?? collect())->first(); @endphp
                                        <td class="px-4 py-3">
                                            @if ($feature->type === 'limit')
                                                <input type="number" min="0" name="features[{{ $feature->id }}][plans][{{ $plan->id }}][value]"
                                                       value="{{ $pf?->value }}" placeholder="{{ __('Unlimited') }}"
                                                       class="w-24 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm" />
                                            @else
                                                <input type="checkbox" name="features[{{ $feature->id }}][plans][{{ $plan->id }}][enabled]" value="1" @checked($pf?->enabled)
                                                       class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <x-primary-button>{{ __('Save Matrix') }}</x-primary-button>
                </div>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('Add Feature') }}</h3>
            <form method="POST" action="{{ route('admin.features.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <x-input-label for="key" :value="__('Key')" />
                    <x-text-input id="key" name="key" type="text" class="block mt-1 w-full" required />
                </div>
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" required />
                </div>
                <div>
                    <x-input-label for="type" :value="__('Type')" />
                    <select id="type" name="type" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        <option value="boolean">{{ __('Boolean') }}</option>
                        <option value="limit">{{ __('Limit') }}</option>
                    </select>
                </div>
                <div>
                    <x-primary-button>{{ __('Add') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
