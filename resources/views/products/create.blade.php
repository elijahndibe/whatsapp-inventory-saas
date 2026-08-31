<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('New Product') }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @include('products._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
