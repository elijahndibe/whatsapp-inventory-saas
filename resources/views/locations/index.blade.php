<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Locations') }}</h2>
            <a href="{{ route('locations.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800">
                <x-icon name="plus" class="w-4 h-4" />
                {{ __('New Location') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <x-flash-messages />

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                @if ($locations->isEmpty())
                    <x-empty-state icon="locations" :title="__('No locations yet')" :description="__('Add a location to track stock across warehouses, stores or pickup points.')">
                        <x-slot name="action">
                            <a href="{{ route('locations.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                                {{ __('Add location') }}
                            </a>
                        </x-slot>
                    </x-empty-state>
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($locations as $location)
                            <li class="px-6 py-4 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $location->name }}
                                        @if ($location->is_default)
                                            <span class="ml-1 text-xs text-brand-600 dark:text-brand-400">({{ __('default') }})</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $location->address }}</div>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="text-gray-400">{{ $location->stock_count }} {{ __('products stocked') }}</span>
                                    <a href="{{ route('locations.edit', $location) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
                                    @unless ($location->is_default)
                                        <form method="POST" action="{{ route('locations.destroy', $location) }}" onsubmit="return confirm('{{ __('Delete this location?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 dark:text-red-400 hover:underline">{{ __('Delete') }}</button>
                                        </form>
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
