<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('adjustStock', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', 'in:increase,decrease,set'],
            'quantity' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:purchase,sale,adjustment,return,damage'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
