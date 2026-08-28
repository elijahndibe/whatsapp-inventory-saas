<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;
        $product = $this->route('product');

        return [
            // 'new' is a sentinel, not a real id — see the "+ Add new
            // category" option in products/_form.blade.php and
            // ProductController::resolveCategoryId(), which turns it into
            // an actual Category before the product is ever saved.
            'category_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($businessId) {
                    if ($value === 'new' || blank($value)) {
                        return;
                    }
                    if (! \App\Models\Category::where('business_id', $businessId)->where('id', $value)->exists()) {
                        $fail('The selected category is invalid.');
                    }
                },
            ],
            'new_category_name' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->input('category_id') === 'new')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sku' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'sku')->where('business_id', $businessId)->ignore($product->id),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,archived'],
            'featured' => ['boolean'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
