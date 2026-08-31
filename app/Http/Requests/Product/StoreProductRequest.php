<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Rules\ValidCategorySelection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            // 'new' and 'suggested:<name>' are sentinels, not real ids —
            // see products/_form.blade.php's category <select> and
            // ProductController::resolveCategoryId(), which turns either
            // into an actual Category before the product is ever saved.
            'category_id' => ['nullable', new ValidCategorySelection($businessId)],
            'new_category_name' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->input('category_id') === 'new')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sku' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'sku')->where('business_id', $businessId),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,archived'],
            'featured' => ['boolean'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
