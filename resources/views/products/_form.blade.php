@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ categorySelection: '{{ old('category_id', $product->category_id ?? '') }}' }">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $product->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="category_id" :value="__('Category')" />
        <select id="category_id" name="category_id" x-model="categorySelection" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('Uncategorized') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
            <option value="new">{{ __('+ Add new category…') }}</option>
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />

        <div x-show="categorySelection === 'new'" x-cloak class="mt-2">
            <x-text-input id="new_category_name" name="new_category_name" type="text"
                          class="block w-full" :value="old('new_category_name')"
                          placeholder="{{ __('New category name') }}"
                          x-bind:required="categorySelection === 'new'" />
            <x-input-error :messages="$errors->get('new_category_name')" class="mt-2" />
        </div>
    </div>
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="3"
              class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $product->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
    <div>
        <x-input-label for="sku" :value="__('SKU')" />
        <x-text-input id="sku" name="sku" type="text" class="block mt-1 w-full" :value="old('sku', $product->sku ?? '')" />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="price" :value="__('Price')" />
        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('price', $product->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cost_price" :value="__('Cost Price (optional)')" />
        <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('cost_price', $product->cost_price ?? '')" />
        <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
    @unless (isset($product))
        <div>
            <x-input-label for="stock_quantity" :value="__('Initial Stock')" />
            <x-text-input id="stock_quantity" name="stock_quantity" type="number" min="0" class="block mt-1 w-full" :value="old('stock_quantity', 0)" required />
            <x-input-error :messages="$errors->get('stock_quantity')" class="mt-2" />
        </div>
    @endunless

    <div>
        <x-input-label for="low_stock_threshold" :value="__('Low Stock Threshold')" />
        <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" class="block mt-1 w-full" :value="old('low_stock_threshold', $product->low_stock_threshold ?? 5)" required />
        <x-input-error :messages="$errors->get('low_stock_threshold')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'archived' => 'Archived'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $product->status ?? 'active') === $value)>{{ __($label) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
</div>

<div class="mt-4 flex items-center">
    <input id="featured" name="featured" type="checkbox" value="1" @checked(old('featured', $product->featured ?? false))
           class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
    <label for="featured" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Feature this product on the storefront') }}</label>
</div>

@isset($product)
    @if ($product->images->isNotEmpty())
        <div class="mt-6">
            <x-input-label :value="__('Current Images')" />
            <div class="mt-2 grid grid-cols-4 sm:grid-cols-6 gap-3">
                @foreach ($product->images as $image)
                    <div class="relative group">
                        <img src="{{ $image->url() }}" class="h-20 w-20 rounded object-cover border border-gray-200 dark:border-gray-700" alt="">
                        @if ($image->is_primary)
                            <span class="absolute top-0 left-0 bg-indigo-600 text-white text-[10px] px-1 rounded-br">{{ __('Primary') }}</span>
                        @endif
                        <form method="POST" action="{{ route('products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('{{ __('Remove this image?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 text-xs leading-5 opacity-0 group-hover:opacity-100 transition">&times;</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endisset

<div class="mt-4">
    <x-input-label for="images" :value="__('Add Images')" />
    <input id="images" name="images[]" type="file" accept="image/*" multiple
           class="block mt-1 w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-200" />
    <x-input-error :messages="$errors->get('images')" class="mt-2" />
    <x-input-error :messages="$errors->get('images.0')" class="mt-2" />
</div>

<div class="flex items-center justify-end mt-6 gap-3">
    <a href="{{ route('products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    <x-primary-button>{{ __('Save Product') }}</x-primary-button>
</div>
