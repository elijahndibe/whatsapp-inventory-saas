<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('adjustStock', $this->route('product'));
    }

    public function rules(): array
    {
        $businessId = $this->user()->business_id;

        return [
            'from_location_id' => [
                'required', 'different:to_location_id',
                Rule::exists('business_locations', 'id')->where('business_id', $businessId),
            ],
            'to_location_id' => [
                'required',
                Rule::exists('business_locations', 'id')->where('business_id', $businessId),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
