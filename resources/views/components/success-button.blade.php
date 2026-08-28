{{-- bg-success-strong, not the DEFAULT token — white button text on the
     spec's #16A34A success tone is only ~3.3:1, below the 4.5:1 WCAG
     minimum for text (see tailwind.config.js). --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-success-strong border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-green-800 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
