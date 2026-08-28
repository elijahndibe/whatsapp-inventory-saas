@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-brand-500 dark:focus:border-brand-500 focus:ring-brand-500 dark:focus:ring-brand-500 rounded-lg shadow-sm text-sm placeholder:text-gray-400 disabled:bg-gray-50 dark:disabled:bg-gray-800 disabled:text-gray-500']) }}>
