<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-transparent border border-brand-700 rounded-lg font-semibold text-sm text-brand-700 dark:text-brand-300 dark:border-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950/40 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
