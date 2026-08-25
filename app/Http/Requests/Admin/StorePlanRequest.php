<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by the 'super_admin' route middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_products' => ['nullable', 'integer', 'min:0'],
            'max_orders_per_month' => ['nullable', 'integer', 'min:0'],
            'max_staff' => ['nullable', 'integer', 'min:0'],
            'max_locations' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ];
    }
}
