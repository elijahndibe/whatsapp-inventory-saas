<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            // Paystack requires an email to initialize a transaction.
            'email' => ['nullable', 'email', 'max:255', Rule::requiredIf($this->input('payment_method') === 'paystack')],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(['whatsapp', 'paystack'])],
        ];
    }
}
