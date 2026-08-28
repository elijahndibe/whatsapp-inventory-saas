<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Edit Category') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <form method="POST" action="{{ route('categories.update', $category) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @include('categories._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
