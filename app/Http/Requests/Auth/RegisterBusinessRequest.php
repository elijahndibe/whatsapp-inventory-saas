<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\PhoneIsVerified;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            // Verified via WhatsApp one-time code before submission — see
            // PhoneIsVerified. That rule is a no-op (registration isn't
            // blocked) when phone verification isn't configured on this
            // platform, exactly like it is in this dev environment.
            'phone' => ['required', 'string', 'max:30', new PhoneIsVerified],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy to create a store.',
        ];
    }
}
