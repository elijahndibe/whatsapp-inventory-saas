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
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ];
    }
}
