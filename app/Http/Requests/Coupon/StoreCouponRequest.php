<?php

namespace App\Http\Requests\Coupon;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Coupon::class);
    }

    /**
     * Normalized here, before the uniqueness check runs — Coupon itself
     * also uppercases on save (belt and braces for any caller that
     * bypasses this Request), but the uniqueness rule below must compare
     * against the same normalized value that will actually be stored, or
     * "save10" would validate as unique against an existing "SAVE10" and
     * then fail at the database with a raw constraint violation instead
     * of a clean validation error.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('coupons')->where('business_id', $this->user()->business_id),
            ],
            'type' => ['required', Rule::in(Coupon::TYPES)],
            'value' => ['required', 'numeric', 'min:0.01', $this->input('type') === Coupon::TYPE_PERCENTAGE ? 'max:100' : 'max:10000000'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Coupon codes can only contain letters, numbers and hyphens.',
            'code.unique' => 'You already have a coupon with this code.',
            'value.max' => 'A percentage discount can\'t be more than 100%.',
        ];
    }
}
