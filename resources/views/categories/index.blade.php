<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">
                {{ __('Categories') }}
            </h2>
            @can('create', \App\Models\Category::class)
                <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                    <x-icon name="plus" class="w-4 h-4" />
                    {{ __('New Category') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <x-flash-messages />

            <form method="GET" class="flex gap-2">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-icon name="search" class="w-4 h-4 text-gray-400" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search categories...') }}"
                           class="block w-full pl-9 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" />
                </div>
                <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ __('Search') }}
                </button>
            </form>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card overflow-hidden">
                @if ($categories->isEmpty())
                    <x-empty-state icon="categories" :title="__('No categories yet')" :description="__('Create your first category to start organizing products.')">
                        <x-slot name="action">
                            @can('create', \App\Models\Category::class)
                                <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                                    {{ __('Add category') }}
                                </a>
                            @endcan
                        </x-slot>
                    </x-empty-state>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Products') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($categories as $category)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $category->products_count }}</td>
                                        <td class="px-4 py-3">
                                            <x-badge :variant="$category->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($category->status) }}</x-badge>
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                            <a href="{{ route('categories.edit', $category) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ __('Edit') }}</a>
                                            @can('delete', $category)
                                                <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline" onsubmit="return confirm('{{ __('Delete this category?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Delete') }}</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
