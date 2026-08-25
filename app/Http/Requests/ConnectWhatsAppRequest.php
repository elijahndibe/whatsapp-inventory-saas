<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConnectWhatsAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage settings');
    }

    public function rules(): array
    {
        return [
            // The authorization code from FB.login(), and the WABA/phone
            // number ids captured client-side from Meta's postMessage
            // events during the Embedded Signup dialog — see the JS in
            // resources/views/settings/edit.blade.php.
            'code' => ['required', 'string'],
            'waba_id' => ['required', 'string'],
            'phone_number_id' => ['required', 'string'],
        ];
    }
}
