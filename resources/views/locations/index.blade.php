<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Locations') }}</h2>
            <a href="{{ route('locations.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                {{ __('New Location') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                @if ($locations->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No locations yet.') }}</div>
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
