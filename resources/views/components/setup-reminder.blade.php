@props(['business'])
{{-- Persistent (not dismissible) until each item is actually resolved —
     matches how Stripe/Shopify-style "finish setting up" banners work:
     a nag that can be closed without being fixed defeats the point.
     Each line is gated by the permission that actually governs the
     linked page, so a staff member without that access sees only what
     they can act on — or nothing, if they can't act on any of it. --}}
@php
    $items = [];

    if (auth()->user()->can('manage settings') && ! $business->hasPaystackSubaccount()) {
        $items[] = [
            'text' => __('Connect a bank account so your share of every sale pays out automatically — until then it\'s only tracked, not paid out.'),
            'cta' => __('Connect bank account'),
            'url' => route('settings.edit').'#payments',
        ];
    }

    if (auth()->user()->can('create products') && $business->products()->count() === 0) {
        $items[] = [
            'text' => __('Add your first product — customers can\'t buy from an empty store.'),
            'cta' => __('Add a product'),
            'url' => route('products.create'),
        ];
    }
@endphp
@if (count($items))
    <div class="rounded-2xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/30 p-4 sm:p-5">
        <div class="flex items-start gap-3">
            <x-icon name="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-300">{{ __('Finish setting up your store') }}</h3>
                <ul class="mt-2 space-y-2.5">
                    @foreach ($items as $item)
                        <li class="flex items-center justify-between gap-3 flex-wrap">
                            <span class="text-sm text-amber-800 dark:text-amber-400">{{ $item['text'] }}</span>
                            <a href="{{ $item['url'] }}" class="shrink-0 text-sm font-semibold text-amber-900 dark:text-amber-200 hover:underline whitespace-nowrap">
                                {{ $item['cta'] }} &rarr;
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
