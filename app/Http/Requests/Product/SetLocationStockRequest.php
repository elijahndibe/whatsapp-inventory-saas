<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetLocationStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('adjustStock', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'location_id' => [
                'required',
                Rule::exists('business_locations', 'id')->where('business_id', $this->user()->business_id),
            ],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
