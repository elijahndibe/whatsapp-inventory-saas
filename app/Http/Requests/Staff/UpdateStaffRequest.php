<?php

namespace App\Http\Requests\Staff;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage staff');
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:Admin,Staff'],
            'status' => ['required', 'in:active,inactive'],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(RolesAndPermissionsSeeder::PERMISSIONS)],
            'locations' => ['array'],
            'locations.*' => [
                Rule::exists('business_locations', 'id')->where('business_id', $this->user()->business_id),
            ],
        ];
    }
}
